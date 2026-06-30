<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

    /**
     * Get the access token from the service account
     */
    protected function getAccessToken()
    {
        $jsonPath = $this->resolveCredentialsPath();

        if (!$jsonPath) {
            Log::error('Firebase credentials not found. Set FIREBASE_CREDENTIALS in .env or place file at storage/app/firebase-service-account.json');
            return null;
        }

        try {
            $credentials = new ServiceAccountCredentials($this->scopes, $jsonPath);
            $token = $credentials->fetchAuthToken();
            return $token['access_token'];
        } catch (\Exception $e) {
            Log::error('Failed to get Firebase access token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send a notification to a specific device token
     * 
     * @param string $token
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public function sendNotification($token, $title, $body, $data = [])
    {
        // 1. Static cache to prevent sending to the exact same token twice within a single script execution lifecycle
        static $processedTokens = [];

        if (in_array($token, $processedTokens, true)) {
            Log::warning('FCM Duplicate Prevented: This token has already been sent a notification in this request lifecycle.', [
                'token_prefix' => substr($token, 0, 20)
            ]);
            return ['success' => false, 'unregistered' => false];
        }
        $processedTokens[] = $token;

        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error('Firebase Access Token is empty.');
            return ['success' => false, 'unregistered' => false];
        }

        // Generate a unique Request ID so you can track this specific execution in your logs
        $requestId = uniqid('fcm_', true);
        Log::info("[{$requestId}] Firebase Access Token obtained (first 10 chars): " . substr($accessToken, 0, 10) . '...');

        $projectId = $this->getProjectId();

        if (!$projectId) {
            Log::error("[{$requestId}] Firebase Project ID could not be determined.");
            return ['success' => false, 'unregistered' => false];
        }

        Log::info("[{$requestId}] Sending FCM to Project ID: {$projectId} for token: " . substr($token, 0, 20) . '...');
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data'    => array_map('strval', $data),
                'android' => [
                    'priority'     => 'high',
                    'notification' => ['sound' => 'default'],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            // 'content-available' => 1, // Removed to prevent double notifications on iOS
                            'sound'             => 'default',
                            'badge'             => 1,
                        ],
                    ],
                ],
            ],
        ];

        try {
            $client   = new Client();
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . trim($accessToken),
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'json'        => $payload,
                'http_errors' => false,
            ]);

            $statusCode   = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();

            if ($statusCode >= 200 && $statusCode < 300) {
                Log::info("[{$requestId}] FCM Notification sent successfully to token: " . substr($token, 0, 20) . '...');
                return ['success' => true, 'unregistered' => false];
            }

            // UNREGISTERED: token is stale/expired — not a server error, just housekeeping needed
            $decoded = json_decode($responseBody, true);
            $errorCode = $decoded['error']['details'][0]['errorCode'] ?? '';

            if ($statusCode === 404 && $errorCode === 'UNREGISTERED') {
                Log::warning("[{$requestId}] FCM token is UNREGISTERED (stale/expired). Auto-clearing from database.", [
                    'token_prefix' => substr($token, 0, 20),
                ]);
                return ['success' => false, 'unregistered' => true];
            }

            Log::error("[{$requestId}] FCM Send Error (Status {$statusCode}): " . $responseBody);
            return ['success' => false, 'unregistered' => false];
        } catch (\Exception $e) {
            Log::error("[{$requestId}] FCM Exception: " . $e->getMessage());
            return ['success' => false, 'unregistered' => false];
        }
    }

    /**
     * Extract project_id from service account JSON file.
     * Tries multiple path candidates in order so it always finds the file.
     */
    protected function getProjectId()
    {
        $jsonPath = $this->resolveCredentialsPath();

        if (!$jsonPath) {
            return null;
        }

        $json = json_decode(file_get_contents($jsonPath), true);
        return $json['project_id'] ?? null;
    }

    /**
     * Resolve the absolute path to the Firebase service account JSON file.
     * Reused by both getAccessToken() and getProjectId() to keep logic DRY.
     */
    protected function resolveCredentialsPath(): ?string
    {
        $envCredentials = env('FIREBASE_CREDENTIALS') ?? env('FIREBASE_SERVICE_ACCOUNT');

        if ($envCredentials) {
            // 1. Treat env value as relative to project root (most common: "storage/app/...")
            if (file_exists(base_path($envCredentials))) {
                return base_path($envCredentials);
            }

            // 2. Treat env value as relative to storage/ (e.g. "app/firebase-service-account.json")
            if (file_exists(storage_path($envCredentials))) {
                return storage_path($envCredentials);
            }

            // 3. Treat env value as an absolute path
            if (file_exists($envCredentials)) {
                return $envCredentials;
            }
        }

        // 4. Well-known fallback path
        $fallback = storage_path('app/firebase-service-account.json');
        if (file_exists($fallback)) {
            return $fallback;
        }

        Log::error('Firebase credentials file not found. Checked: base_path, storage_path, absolute path, and fallback.');
        return null;
    }
}
