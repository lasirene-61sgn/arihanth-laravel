<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARIHANTH - Key User Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --brand-dark: #1a1a1a;
            --brand-gold: #c5a059;
            --brand-gold-light: #f4ece1;
        }

        body {
            background: linear-gradient(135deg, #8e6d31 0%, #8e6d31 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            background: white;
            width: 100%;
        }
        
        .login-header {
            background: var(--brand-dark);
            color: white;
            padding: 40px 20px;
            text-align: center;
            position: relative;
        }

        /* Gold accent line */
        .login-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #8e6d31, var(--brand-gold), #8e6d31);
        }

        .brand-logo-container {
            width: 80px;
            height: 80px;
            background-color: #FFD700; /* Yellow background like your logo image */
            padding: 8px;
            border-radius: 12px;
            margin-bottom: 15px;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        
        .login-body {
            padding: 40px;
        }
        
        .form-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #666;
        }

        .input-group-text {
            background-color: var(--brand-gold-light);
            border: 1px solid rgba(197, 160, 89, 0.2);
            color: var(--brand-gold);
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e9ecef;
        }
        
        .form-control:focus {
            border-color: var(--brand-gold);
            box-shadow: 0 0 0 0.25rem rgba(197, 160, 89, 0.15);
        }
        
        .btn-login {
            background: var(--brand-dark);
            border: 1px solid var(--brand-gold);
            border-radius: 10px;
            padding: 14px;
            font-weight: 700;
            color: var(--brand-gold);
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: var(--brand-gold);
            color: var(--brand-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(197, 160, 89, 0.3);
        }
        
        .login-footer {
            text-align: center;
            padding: 20px;
            border-top: 1px solid #f1f1f1;
            background: #fafafa;
            font-size: 0.8rem;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-card">
                    <div class="login-header">
                        <div class="brand-logo-container shadow-sm">
                            <img src="{{ asset('images/tara.jpeg') }}" alt="ARIHANTH Logo" class="img-fluid">
                        </div>
                        
                        <h2 class="fw-black text-uppercase tracking-wider mb-1" style="font-weight: 900;">ARIHANTH JEWELLERS</h2>
                        <p class="medium opacity-75 mb-0 uppercase tracking-widest">KEY USER PORTAL</p>
                    </div>
                    
                    <div class="login-body">
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                                <i class="bi bi-exclamation-octagon me-2"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if ($errors->any())
                            <div class="alert alert-danger small">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('key-user.login') }}">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="user_code" class="form-label fw-bold">User Code</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person-badge-fill"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control @error('user_code') is-invalid @enderror" 
                                           id="user_code" 
                                           name="user_code" 
                                           value="{{ old('user_code') }}" 
                                           placeholder="Enter your user code" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label fw-bold d-flex justify-content-between">
                                    Password
                                    <a href="{{ route('key-user.password.request') }}" class="text-decoration-none" style="color: var(--brand-gold); font-size: 0.75rem;">Forgot Password?</a>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-shield-lock-fill"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           placeholder="••••••••" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-login">
                                    <i class="bi bi-door-open-fill me-2"></i>Secure Login
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="login-footer">
                        <p class="mb-0 uppercase tracking-widest">
                            &copy; {{ date('Y') }} ARIHANTH JEWELLERS
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('form');
    if(form) {
        var latInput = document.createElement('input');
        latInput.type = 'hidden';
        latInput.name = 'latitude';
        latInput.id = 'latitude';
        
        var lonInput = document.createElement('input');
        lonInput.type = 'hidden';
        lonInput.name = 'longitude';
        lonInput.id = 'longitude';
        
        form.appendChild(latInput);
        form.appendChild(lonInput);
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    latInput.value = position.coords.latitude;
                    lonInput.value = position.coords.longitude;
                },
                function(error) {
                    console.warn('Geolocation failed or was denied: ', error);
                },
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
            );
        }
    }
});
</script>