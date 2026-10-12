<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApnsVoipService
{
    protected ?string $keyId;
    protected ?string $teamId;
    protected ?string $bundleId;
    protected ?string $keyPath;
    protected string $environment;

    public function __construct()
    {
        $this->keyId       = config('services.apns.key_id') ?? env('APNS_KEY_ID', 'VCBWFP7W26');
        $this->teamId      = config('services.apns.team_id') ?? env('APNS_TEAM_ID');
        $this->bundleId    = config('services.apns.bundle_id') ?? env('APNS_BUNDLE_ID');
        $this->keyPath     = config('services.apns.key_path') ?? env('APNS_KEY_PATH');
        $this->environment = config('services.apns.environment') ?? env('APNS_ENVIRONMENT', 'sandbox');
    }

    /**
     * Send a VoIP push notification to an iOS device token (PushKit).
     *
     * @param string $deviceToken 64-character hexadecimal Apple VoIP token
     * @param array $payload Additional payload data (caller_name, room_id, channel, etc.)
     * @param array $options Optional override for sound, expiration, etc.
     * @return array ['success' => bool, 'status' => int, 'response' => string, 'reason' => ?string]
     */
    public function sendVoipNotification(string $deviceToken, array $payload = [], array $options = []): array
    {
        // Clean token (strip spaces, angle brackets)
        $cleanToken = trim(str_replace(['<', '>', ' '], '', $deviceToken));
        if (empty($cleanToken)) {
            Log::warning('APNs VoIP Push skipped: Empty device token.');
            return ['success' => false, 'status' => 0, 'response' => 'Empty device token', 'reason' => 'EmptyToken'];
        }

        // Generate or get cached JWT authorization token
        $jwt = $this->generateJwt();
        if (!$jwt) {
            Log::error('APNs VoIP Push failed: Could not generate JWT authorization token.');
            return ['success' => false, 'status' => 0, 'response' => 'Could not generate APNs JWT', 'reason' => 'JwtError'];
        }

        $bundleId = $options['bundle_id'] ?? $this->bundleId;
        if (empty($bundleId)) {
            Log::error('APNs VoIP Push failed: APNS_BUNDLE_ID is not configured in .env / config.');
            return ['success' => false, 'status' => 0, 'response' => 'APNS_BUNDLE_ID not configured', 'reason' => 'MissingBundleId'];
        }

        // Apple PushKit VoIP topic MUST have suffix .voip
        $topic = str_ends_with($bundleId, '.voip') ? $bundleId : ($bundleId . '.voip');

        // Build PushKit VoIP payload
        $body = [
            'aps' => [
                'sound' => $options['sound'] ?? 'default',
            ],
        ];

        // Merge call & meeting metadata
        foreach ($payload as $k => $v) {
            $body[$k] = is_bool($v) || is_numeric($v) ? $v : (string) $v;
        }

        $jsonPayload = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Determine host based on environment
        $preferredEnv = strtolower($this->environment);
        $primaryHost = ($preferredEnv === 'production')
            ? 'api.push.apple.com'
            : 'api.sandbox.push.apple.com';

        $result = $this->dispatchHttp2Push($primaryHost, $cleanToken, $jwt, $topic, $jsonPayload);

        // Auto-fallback: If sandbox fails with BadDeviceToken or TopicDisallowed and we're in sandbox,
        // or production fails with BadDeviceToken, try the opposite environment.
        if (!$result['success'] && in_array($result['reason'] ?? '', ['BadDeviceToken', 'DeviceTokenNotForTopic'], true)) {
            $fallbackHost = ($primaryHost === 'api.push.apple.com')
                ? 'api.sandbox.push.apple.com'
                : 'api.push.apple.com';
            
            Log::info("APNs VoIP: Retrying token against alternative endpoint: {$fallbackHost}");
            $fallbackResult = $this->dispatchHttp2Push($fallbackHost, $cleanToken, $jwt, $topic, $jsonPayload);
            if ($fallbackResult['success']) {
                return $fallbackResult;
            }
        }

        return $result;
    }

    /**
     * Dispatch HTTP/2 POST to Apple APNs server via cURL.
     */
    protected function dispatchHttp2Push(string $host, string $token, string $jwt, string $topic, string $jsonPayload): array
    {
        $url = "https://{$host}/3/device/{$token}";

        $headers = [
            'apns-push-type: voip',
            "apns-topic: {$topic}",
            'apns-priority: 10',     // 10 = Immediate delivery
            'apns-expiration: 0',   // 0 = Deliver immediately or discard
            "authorization: bearer {$jwt}",
            'content-type: application/json',
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $jsonPayload,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_2_0,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HEADER         => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            Log::error("APNs VoIP Push cURL Error: {$curlError} (Host: {$host})");
            return ['success' => false, 'status' => 0, 'response' => $curlError, 'reason' => 'CurlError'];
        }

        $responseBody = substr($response, $headerSize);
        $decodedBody = json_decode($responseBody, true);
        $reason = $decodedBody['reason'] ?? null;

        if ($httpCode === 200) {
            Log::info("APNs VoIP Push Delivered Successfully to token: " . substr($token, 0, 15) . "... on host: {$host}");
            return ['success' => true, 'status' => 200, 'response' => 'OK', 'reason' => null];
        }

        Log::warning("APNs VoIP Push Failed (HTTP {$httpCode}, Host: {$host}, Reason: " . ($reason ?? $responseBody) . ")");
        return [
            'success'  => false,
            'status'   => $httpCode,
            'response' => $responseBody,
            'reason'   => $reason,
        ];
    }

    /**
     * Generate or retrieve cached Apple ES256 JWT.
     */
    public function generateJwt(): ?string
    {
        $cached = Cache::get('apns_voip_jwt_token');
        if (!empty($cached)) {
            return $cached;
        }

        $jwt = $this->createJwtToken();
        if ($jwt) {
            Cache::put('apns_voip_jwt_token', $jwt, 3000); // 50 mins
        }

        return $jwt;
    }

    /**
     * Create an ES256 JWT signed with the Apple .p8 key.
     */
    protected function createJwtToken(): ?string
    {
        $keyContent = $this->resolveKeyContent();
        if (!$keyContent) {
            Log::error('APNs VoIP: Private key content could not be found.');
            return null;
        }

        if (empty($this->keyId) || empty($this->teamId)) {
            Log::error("APNs VoIP: Missing APNS_KEY_ID ('{$this->keyId}') or APNS_TEAM_ID ('{$this->teamId}').");
            return null;
        }

        $privateKey = openssl_pkey_get_private($keyContent);
        if (!$privateKey) {
            Log::error('APNs VoIP: Unable to parse Apple .p8 private key with OpenSSL: ' . openssl_error_string());
            return null;
        }

        $header = [
            'alg' => 'ES256',
            'kid' => $this->keyId,
        ];

        $claims = [
            'iss' => $this->teamId,
            'iat' => time(),
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $claimsEncoded = $this->base64UrlEncode(json_encode($claims));
        $dataToSign = "{$headerEncoded}.{$claimsEncoded}";

        $derSignature = '';
        if (!openssl_sign($dataToSign, $derSignature, $privateKey, OPENSSL_ALGO_SHA256)) {
            Log::error('APNs VoIP: OpenSSL signing failed: ' . openssl_error_string());
            return null;
        }

        $rawSignature = $this->derToRaw($derSignature);
        $signatureEncoded = $this->base64UrlEncode($rawSignature);

        return "{$dataToSign}.{$signatureEncoded}";
    }

    /**
     * Convert ASN.1 DER signature to raw 64-byte IEEE P1363 format (R + S).
     */
    protected function derToRaw(string $der): string
    {
        $pos = 2;
        if (ord($der[1]) & 0x80) {
            $pos = 2 + (ord($der[1]) & 0x7f);
        }

        // Parse R integer
        $pos++;
        $lenR = ord($der[$pos++]);
        $r = substr($der, $pos, $lenR);
        $pos += $lenR;

        // Parse S integer
        $pos++;
        $lenS = ord($der[$pos++]);
        $s = substr($der, $pos, $lenS);

        // Strip leading null bytes if 33 bytes
        $r = ltrim($r, "\x00");
        $s = ltrim($s, "\x00");

        // Pad to exactly 32 bytes each
        $r = str_pad($r, 32, "\x00", STR_PAD_LEFT);
        $s = str_pad($s, 32, "\x00", STR_PAD_LEFT);

        return $r . $s;
    }

    /**
     * URL-safe Base64 encode without padding.
     */
    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Find the Apple .p8 private key file.
     */
    protected function resolveKeyContent(): ?string
    {
        $candidates = [
            $this->keyPath,
            storage_path('app/AuthKey_' . ($this->keyId ?? 'VCBWFP7W26') . '.p8'),
            base_path('storage/app/AuthKey_' . ($this->keyId ?? 'VCBWFP7W26') . '.p8'),
            resource_path('views/super-admin/AuthKey_VCBWFP7W26 (1).p8'),
        ];

        foreach ($candidates as $path) {
            if ($path && file_exists($path)) {
                return file_get_contents($path);
            }
        }

        // Also check if raw private key was passed in environment variable
        $envKey = env('APNS_PRIVATE_KEY');
        if (!empty($envKey)) {
            return str_replace('\n', "\n", $envKey);
        }

        return null;
    }
}
