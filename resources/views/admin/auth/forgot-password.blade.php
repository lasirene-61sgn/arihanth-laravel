<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - {{ ucfirst(str_replace('_', ' ', $role)) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .login-container { max-width: 400px; margin: 100px auto; }
        .login-card { border-radius: 10px; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); }
        .login-header { background-color: #0d6efd; color: white; border-top-left-radius: 10px; border-top-right-radius: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card login-card">
                <div class="card-header text-center login-header">
                    <h3><i class="bi bi-shield-lock me-2"></i>Forgot Password</h3>
                    <p class="mb-0">Enter your credentials to recover your account</p>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route("$role.password.select-method") }}">
                        @csrf
                        <div class="mb-4">
                            <label for="email_or_user_code" class="form-label">Email or User Identity</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                <input type="text" class="form-control" id="email_or_user_code" name="email_or_user_code" 
                                       value="{{ old('email_or_user_code') }}" required autofocus placeholder="Enter your email or code">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                Next <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                             @php
                                 $loginRoute = $role === 'admin' ? 'admin.login' : 
                                              ($role === 'super-admin' ? 'super-admin.login' : 
                                              ($role === 'craftsman' ? 'craftsman.login' : 'key-user.login'));
                             @endphp
                            <a href="{{ route($loginRoute) }}" class="btn btn-link">Back to Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
