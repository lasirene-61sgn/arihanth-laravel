<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProcessOwner;
use App\Models\Craftman;
use App\Models\Buyer;
use App\Models\KeyUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetOTP;

class ForgotPasswordController extends Controller
{
    /**
     * Get role configuration mapping.
     */
    private function getRoleConfig($role)
    {
        $configs = [
            'admin' => [
                'model' => ProcessOwner::class,
                'guard' => 'admin',
                'identity_fields' => ['user_code', 'email_id'],
                'email_field' => 'email_id',
                'mobile_field' => 'mobile_no',
                'where' => ['role' => 'admin'],
                'login_route' => 'admin.login',
                'prefix' => 'admin'
            ],
            'super-admin' => [
                'model' => ProcessOwner::class,
                'guard' => 'super_admin',
                'identity_fields' => ['user_code', 'email_id'],
                'email_field' => 'email_id',
                'mobile_field' => 'mobile_no',
                'where' => ['role' => 'super_admin'],
                'login_route' => 'super-admin.login',
                'prefix' => 'super-admin'
            ],
            'craftsman' => [
                'model' => Craftman::class,
                'guard' => 'craftsman',
                'identity_fields' => ['craftman_code', 'email'],
                'email_field' => 'email',
                'mobile_field' => 'mobile',
                'login_route' => 'craftsman.login',
                'prefix' => 'craftsman'
            ],
            'key-user' => [
                'model' => KeyUser::class,
                'guard' => 'key_user',
                'identity_fields' => ['user_code', 'email_id'],
                'email_field' => 'email_id',
                'mobile_field' => 'mobile_no',
                'login_route' => 'key-user.login',
                'prefix' => 'key-user'
            ],
            'buyer' => [
                'model' => Buyer::class,
                'guard' => 'buyer',
                'identity_fields' => ['bp_code', 'email'],
                'email_field' => 'email',
                'mobile_field' => 'mobile',
                'login_route' => 'key-user.login', // Buyers also login here
                'prefix' => 'buyer'
            ],
            'user' => [
                'model' => User::class,
                'guard' => 'user',
                'identity_fields' => ['user_code', 'email'],
                'email_field' => 'email',
                'mobile_field' => 'mobile_no',
                'login_route' => 'key-user.login', // Assuming shared login or update later
                'prefix' => 'user'
            ],
        ];

        return $configs[$role] ?? $configs['admin'];
    }

    /**
     * Show the forgot password form.
     */
    public function showLinkRequestForm(Request $request, $role = 'admin')
    {
        return view('admin.auth.forgot-password', compact('role'));
    }

    /**
     * Verify identity and show recovery method selection.
     */
    public function selectMethod(Request $request, $role = 'admin')
    {
        $request->validate([
            'email_or_user_code' => 'required',
        ]);

        $config = $this->getRoleConfig($role);
        $model = $config['model'];

        $user = $model::where(function ($query) use ($request, $config) {
            foreach ($config['identity_fields'] as $field) {
                $query->orWhere($field, $request->email_or_user_code);
            }
        });

        if (isset($config['where'])) {
            $user->where($config['where']);
        }

        $user = $user->first();

        if (!$user) {
            return back()->withErrors(['email_or_user_code' => "User not found or not authorized as $role."]);
        }

        return view('admin.auth.reset-options', compact('user', 'role'));
    }

    /**
     * Handle sending recovery information (OTP).
     */
    public function sendResetLink(Request $request, $role = 'admin')
    {
        $config = $this->getRoleConfig($role);
        $tableName = (new $config['model'])->getTable();

        $request->validate([
            'user_id' => "required|exists:$tableName,id",
            'method' => 'required|in:email,sms',
        ]);

        $user = $config['model']::findOrFail($request->user_id);

        // Generate a 6-digit OTP
        $otp = rand(100000, 999999);

        // Store OTP and user ID and role in session for verification
        session([
            'reset_user_id' => $user->id,
            'reset_user_role' => $role,
            'reset_otp' => $otp,
            'reset_otp_expires_at' => now()->addMinutes(10)
        ]);

        $emailField = $config['email_field'];
        $mobileField = $config['mobile_field'];
        $method = $request->method;
        $success = false;
        $target = '';

        if ($method === 'email') {
            $target = $user->$emailField;
            if (!$target) {
                return back()->withErrors(['method' => 'Email address not found for this account.']);
            }
            $recipientName = $user->full_name ?? $user->name ?? $user->user_code ?? $user->craftman_code ?? $user->bp_code ?? 'User';
            $success = $this->sendDirectEmailOTP($target, $recipientName, $otp);
        } else {
            $target = $user->$mobileField;
            if (!$target) {
                return back()->withErrors(['method' => 'Mobile number not found for this account.']);
            }
            $success = $this->sendSMSMsg($target, $otp);
        }

        if (!$success) {
            return back()->withErrors(['method' => 'Failed to send OTP. Please try again later.']);
        }

        return redirect()->route("$role.password.verify-form")->with('success', "OTP has been sent to your $method ($target).");
    }

    /**
     * Show the OTP verification form.
     */
    public function showVerifyForm(Request $request, $role = 'admin')
    {
        if (!session()->has('reset_user_id') || session('reset_user_role') !== $role) {
            return redirect()->route("$role.password.request");
        }
        return view('admin.auth.verify-otp', compact('role'));
    }

    /**
     * Verify the entered OTP.
     */
    public function verifyOTP(Request $request, $role = 'admin')
    {
        $request->validate([
            'otp' => 'required|numeric',
        ]);

        $storedOtp = session('reset_otp');
        $expiresAt = session('reset_otp_expires_at');
        $sessionRole = session('reset_user_role');

        if (!$storedOtp || now()->gt($expiresAt) || $sessionRole !== $role) {
            return back()->withErrors(['otp' => 'OTP has expired or session is invalid. Please request a new one.']);
        }

        if ($request->otp != $storedOtp) {
            return back()->withErrors(['otp' => 'Invalid OTP. Please try again.']);
        }

        // OTP verified, mark it in session
        session(['otp_verified' => true]);

        return redirect()->route("$role.password.reset-form");
    }

    /**
     * Show the password reset form.
     */
    public function showResetForm(Request $request, $role = 'admin')
    {
        if (!session('otp_verified') || session('reset_user_role') !== $role) {
            return redirect()->route("$role.password.verify-form");
        }
        return view('admin.auth.reset-password', compact('role'));
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request, $role = 'admin')
    {
        if (!session('otp_verified') || session('reset_user_role') !== $role) {
            return redirect()->route("$role.password.verify-form");
        }

        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $config = $this->getRoleConfig($role);
        $userId = session('reset_user_id');
        $user = $config['model']::findOrFail($userId);

        $user->password = Hash::make($request->password);
        $user->save();

        // Clear session
        session()->forget(['reset_user_id', 'reset_user_role', 'reset_otp', 'reset_otp_expires_at', 'otp_verified']);

        return redirect()->route($config['login_route'])->with('success', 'Your password has been reset successfully. Please login with your new password.');
    }

    private function sendDirectEmailOTP($recipientEmail, $recipientName, $otp)
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

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'authkey' => $authKey,
                'accept'  => 'application/json',
            ])->post('https://api.msg91.com/api/v5/email/send', $payload);

            if ($response->successful()) {
                Log::info('Password Reset OTP Sent via MSG91 HTTP Email:', [
                    'target' => $recipientEmail,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('MSG91 HTTP Email Failed:', [
                    'target' => $recipientEmail,
                    'response' => $response->json()
                ]);
                
                // Try fallback to Laravel Mail if MSG91 API fails
                try {
                    Mail::to($recipientEmail)->send(new PasswordResetOTP($otp));
                    Log::info('Password Reset OTP Sent via Fallback Laravel Mail:', [
                        'target' => $recipientEmail
                    ]);
                    return true;
                } catch (\Exception $fallbackException) {
                    Log::error('SMTP Fallback also failed:', [
                        'error' => $fallbackException->getMessage()
                    ]);
                    return false; // Return false so the user sees the error message
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send Password Reset OTP:', [
                'target' => $recipientEmail,
                'error'  => $e->getMessage()
            ]);
            return false;
        }
    }
    /**
     * Send SMS via MSG91 (Flow API Version).
     */
    private function sendSMSMsg($phone, $otp)
    {
        // 1. Clean and format the phone number
        $phone = trim($phone);
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        $authKey = '501083AjcyWEDYv69ba6085P1';
        $templateId = '69ba5f87a27ca7c5ac011655';

        // 2. Updated Payload: Using 'var' to match ##var## in your template
        $payload = [
            'template_id' => $templateId,
            'short_url'   => '0',
            'recipients'  => [
                [
                    'mobiles' => $phone,
                    'var'     => (string)$otp // Changed from 'otp' to 'var'
                ]
            ]
        ];

        // 3. Send request
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'authkey' => $authKey,
            'accept'  => 'application/json',
        ])->post('https://api.msg91.com/api/v5/flow/', $payload);

        // 4. Log result for verification
        Log::info('MSG91 OTP Sent:', [
            'phone' => $phone,
            'response' => $response->json()
        ]);

        return $response->successful();
    }
}
