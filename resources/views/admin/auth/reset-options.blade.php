<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Recovery Method - {{ ucfirst(str_replace('_', ' ', $role)) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .login-container { max-width: 450px; margin: 100px auto; }
        .login-card { border-radius: 10px; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); }
        .login-header { background-color: #0d6efd; color: white; border-top-left-radius: 10px; border-top-right-radius: 10px; }
        .method-card { cursor: pointer; transition: transform 0.2s, border-color 0.2s; border: 2px solid transparent; }
        .method-card:hover { transform: translateY(-3px); border-color: #0d6efd; }
        .method-radio { display: none; }
        .method-radio:checked + .method-card { border-color: #0d6efd; background-color: #f0f7ff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card login-card">
                <div class="card-header text-center login-header">
                    <h3><i class="bi bi-send-check me-2"></i>Recovery Method</h3>
                    <p class="mb-0">Choose how you want to receive the reset link</p>
                </div>
                <div class="card-body">
                    @php
                        $userName = $user->full_name ?? $user->name ?? $user->user_code ?? $user->craftman_code ?? $user->bp_code;
                        $email = $user->email_id ?? $user->email ?? $user->business_email;
                        $mobile = $user->mobile_no ?? $user->mobile;
                    @endphp
                    <p class="text-center mb-4">Account found for: <strong>{{ $userName }}</strong></p>
                    
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route("$role.password.send-reset") }}">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        
                        <div class="mb-3">
                            @if($email)
                            <label class="d-block">
                                <input type="radio" name="method" value="email" class="method-radio" checked>
                                <div class="card method-card mb-2">
                                    <div class="card-body d-flex align-items-center">
                                        <i class="bi bi-envelope-at size-1 fs-3 text-primary me-3"></i>
                                        <div>
                                            <h6 class="mb-0">Via Email</h6>
                                            <small class="text-muted">{{ Str::mask($email, '*', 3) }}</small>
                                        </div>
                                        <i class="bi bi-check-circle-fill ms-auto text-primary check-icon"></i>
                                    </div>
                                </div>
                            </label>
                            @endif

                            @if($mobile)
                            <label class="d-block">
                                <input type="radio" name="method" value="sms" class="method-radio" {{ !$email ? 'checked' : '' }}>
                                <div class="card method-card mb-2">
                                    <div class="card-body d-flex align-items-center">
                                        <i class="bi bi-chat-left-dots fs-3 text-info me-3"></i>
                                        <div>
                                            <h6 class="mb-0">Via SMS</h6>
                                            <small class="text-muted">{{ Str::mask($mobile, '*', 2, 6) }}</small>
                                        </div>
                                        <i class="bi bi-check-circle-fill ms-auto text-primary check-icon {{ $email ? 'd-none' : '' }}"></i>
                                    </div>
                                </div>
                            </label>
                            @endif

                            @if(!$email && !$mobile)
                                <div class="alert alert-warning">
                                    No recovery contact information (Email or Mobile) found for this account. Please contact the administrator.
                                </div>
                            @endif
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            @if($email || $mobile)
                            <button type="submit" class="btn btn-primary">
                                Send Recovery OTP <i class="bi bi-paper-plane ms-2"></i>
                            </button>
                            @endif
                            <a href="{{ route("$role.password.request") }}" class="btn btn-link">Change Identity</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
