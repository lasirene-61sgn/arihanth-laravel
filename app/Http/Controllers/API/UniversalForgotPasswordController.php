<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetOTP;
use App\Models\ProcessOwner;
use App\Models\Buyer;
use App\Models\Craftman;
use App\Models\KeyUser;
use App\Models\User;

class UniversalForgotPasswordController extends Controller
{
    /**
     * Send OTP for password reset
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required', // email or mobile
            'method' => 'required|in:email,sms',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $identifier = $request->identifier;
        $method = $request->method;

        // 1. Find user and role
        $user = null;
        $role = null;

        // Check ProcessOwner (Super Admin / Admin)
        $user = ProcessOwner::where('email_id', $identifier)->orWhere('mobile_no', $identifier)->first();
        if ($user) {
            $role = $user->role; // 'super_admin' or 'admin'
        } else {
            // Check Buyer
            $user = Buyer::where('email', $identifier)->orWhere('mobile', $identifier)->first();
            if ($user) {
                $role = 'buyer';
            } else {
                // Check Craftsman
                $user = Craftman::where('email', $identifier)->orWhere('mobile', $identifier)->first();
                if ($user) {
                    $role = 'craftsman';
                } else {
                    // Check Key User
                    $user = KeyUser::where('email_id', $identifier)->orWhere('mobile_no', $identifier)->first();
                    if ($user) {
                        $role = 'key_user';
                    } else {
                        // Check User
                        $user = User::where('email', $identifier)->orWhere('mobile_no', $identifier)->first();
                        if ($user) {
                            $role = 'user';
                        }
                    }
                }
            }
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        // 2. Generate and Cache OTP
        $otp = rand(100000, 999999);
        $cacheKey = "api_otp_{$role}_{$identifier}";
        Cache::put($cacheKey, $otp, 600); // 10 minutes

        // 3. Send OTP
        $success = false;
        if ($method === 'email') {
            $email = ($role === 'super_admin' || $role === 'admin' || $role === 'key_user') ? $user->email_id : $user->email;
            $name = ($role === 'super_admin' || $role === 'admin') ? $user->full_name : $user->name;
            $success = $this->sendEmailOTP($email, $name, $otp);
        } else {
            $mobile = ($role === 'super_admin' || $role === 'admin' || $role === 'key_user' || $role === 'user') ? $user->mobile_no : $user->mobile;
            $success = $this->sendSMSOTP($mobile, $otp);
        }

        if ($success) {
            return response()->json([
                'success' => true, 
                'message' => "OTP sent successfully via $method.",
                'role' => $role
            ]);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to send OTP.'], 500);
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required',
            'role' => 'required',
            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $cacheKey = "api_otp_{$request->role}_{$request->identifier}";
        $cachedOtp = Cache::get($cacheKey);

        if ($cachedOtp && $cachedOtp == $request->otp) {
            // Store a temporary reset token in cache to authorize the password reset call
            $resetToken = bin2hex(random_bytes(16));
            Cache::put("api_reset_token_{$request->role}_{$request->identifier}", $resetToken, 300); // 5 minutes

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully.',
                'reset_token' => $resetToken
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.'], 400);
    }

    /**
     * Reset Password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required',
            'role' => 'required',
            'reset_token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $tokenKey = "api_reset_token_{$request->role}_{$request->identifier}";
        $cachedToken = Cache::get($tokenKey);

        if (!$cachedToken || $cachedToken !== $request->reset_token) {
            return response()->json(['success' => false, 'message' => 'Unauthorized or expired reset token.'], 401);
        }

        // Find user again to update
        $user = $this->findUserByRole($request->role, $request->identifier);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $user->password = Hash::make($request->password);
        if (isset($user->password_plain)) {
            $user->password_plain = $request->password;
        }
        $user->save();

        // Clear cache
        Cache::forget($tokenKey);
        Cache::forget("api_otp_{$request->role}_{$request->identifier}");

        return response()->json(['success' => true, 'message' => 'Password reset successfully.']);
    }

    private function findUserByRole($role, $identifier)
    {
        switch ($role) {
            case 'super_admin':
            case 'admin':
                return ProcessOwner::where('email_id', $identifier)->orWhere('mobile_no', $identifier)->first();
            case 'buyer':
                return Buyer::where('email', $identifier)->orWhere('mobile', $identifier)->first();
            case 'craftsman':
                return Craftman::where('email', $identifier)->orWhere('mobile', $identifier)->first();
            case 'key_user':
                return KeyUser::where('email_id', $identifier)->orWhere('mobile_no', $identifier)->first();
            case 'user':
                return User::where('email', $identifier)->orWhere('mobile_no', $identifier)->first();
            default:
                return null;
        }
    }

    private function sendEmailOTP($recipientEmail, $recipientName, $otp)
    {
        try {
            // Using MSG91 HTTP API instead of SMTP to avoid IP whitelisting issues
            $authKey = env('MSG91_AUTH_KEY', '501083ALvR76fSppV69ba6e94P1');
            $domain = env('MSG91_EMAIL_DOMAIN', 'qppk7f.mailer91.com');
            $fromEmail = env('MSG91_EMAIL_FROM_ADDRESS', 'no-reply@qppk7f.mailer91.com');
            $templateId = env('MSG91_EMAIL_TEMPLATE_ID', 'password_reset_otp');

            $payload = [
                'to' => [
                    ['email' => $recipientEmail, 'name' => $recipientName]
                ],
                'from' => [
                    'email' => $fromEmail,
                    'name' => env('APP_NAME', 'Lasirene ERP')
                ],
                'domain' => $domain,
                'template_id' => $templateId,
                'variables' => [
                    'otp' => (string)$otp
                ]
            ];

            $response = Http::withHeaders([
                'authkey' => $authKey,
                'accept'  => 'application/json',
            ])->post('https://api.msg91.com/api/v5/email/send', $payload);

            if ($response->successful()) {
                Log::info('API Password Reset OTP Sent via MSG91 HTTP Email:', [
                    'target' => $recipientEmail,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('API MSG91 HTTP Email Failed:', [
                    'target' => $recipientEmail,
                    'response' => $response->json()
                ]);

                // Try fallback to Laravel Mail if MSG91 API fails
                try {
                    Mail::to($recipientEmail)->send(new PasswordResetOTP($otp));
                    Log::info('API Password Reset OTP Sent via Fallback Laravel Mail:', [
                        'target' => $recipientEmail
                    ]);
                    return true;
                } catch (\Exception $fallbackException) {
                    Log::error('API SMTP Fallback also failed:', [
                        'error' => $fallbackException->getMessage()
                    ]);
                    return false; // Return false so the user sees the error message
                }
            }
        } catch (\Exception $e) {
            Log::error('API Failed to send Password Reset OTP:', [
                'target' => $recipientEmail,
                'error'  => $e->getMessage()
            ]);
            return false;
        }
    }

    private function sendSMSOTP($mobile, $otp)
    {
        try {
            // 1. Clean and format the phone number
            $mobile = trim($mobile);
            if (strlen($mobile) === 10) {
                $mobile = '91' . $mobile;
            }

            $authKey = '501083AjcyWEDYv69ba6085P1';
            $templateId = '69ba5f87a27ca7c5ac011655';

            // 2. Updated Payload: Using 'var' to match ##var## in your template
            $payload = [
                'template_id' => $templateId,
                'short_url'   => '0',
                'recipients'  => [
                    [
                        'mobiles' => $mobile,
                        'var'     => (string)$otp
                    ]
                ]
            ];

            // 3. Send request
            $response = Http::withHeaders([
                'authkey' => $authKey,
                'accept'  => 'application/json',
            ])->post('https://api.msg91.com/api/v5/flow/', $payload);

            // 4. Log result for verification
            Log::info('API MSG91 OTP Sent:', [
                'phone' => $mobile,
                'response' => $response->json()
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('API SMS OTP Failed: ' . $e->getMessage());
            return false;
        }
    }
}
