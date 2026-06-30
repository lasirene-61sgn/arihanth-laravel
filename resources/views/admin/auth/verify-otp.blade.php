<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - {{ ucfirst(str_replace('_', ' ', $role)) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .login-container { max-width: 400px; margin: 100px auto; }
        .login-card { border-radius: 10px; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); }
        .login-header { background-color: #0d6efd; color: white; border-top-left-radius: 10px; border-top-right-radius: 10px; }
        .otp-input { letter-spacing: 1rem; text-align: center; font-size: 2rem; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card login-card">
                <div class="card-header text-center login-header">
                    <h3><i class="bi bi-shield-check me-2"></i>Verify OTP</h3>
                    <p class="mb-0">Enter the 6-digit code sent to you</p>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route("$role.password.verify") }}">
                        @csrf
                        <div class="mb-4">
                            <label for="otp" class="form-label">Verification Code</label>
                            <input type="text" class="form-control otp-input" id="otp" name="otp" 
                                   maxlength="6" placeholder="------" required autofocus>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                Verify & Continue <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                            <a href="{{ route("$role.password.request") }}" class="btn btn-link text-decoration-none">Resend OTP</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
