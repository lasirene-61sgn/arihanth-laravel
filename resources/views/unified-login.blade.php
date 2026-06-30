<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ARIHANTH Jewellers ERP</title>
    <meta name="description" content="Secure login portal for ARIHANTH Jewellers ERP system.">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        * {
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
        }

        body {
            background: #0f0f0f;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            overflow-y: auto;
            position: relative;
        }

        /* Animated background orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.12;
            animation: float 8s ease-in-out infinite;
            pointer-events: none;
        }

        .orb-1 {
            width: 500px;
            height: 500px;
            background: #a855f7;
            top: -150px;
            left: -100px;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: #ec4899;
            bottom: -100px;
            right: -80px;
            animation-delay: 2s;
        }

        .orb-3 {
            width: 300px;
            height: 300px;
            background: #6366f1;
            top: 40%;
            left: 60%;
            animation-delay: 4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) scale(1);
            }

            50% {
                transform: translateY(-30px) scale(1.05);
            }
        }

        /* Glass card */
        .glass-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 28px;
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        /* Input */
        .inp {
            width: 100%;
            padding: 14px 16px 14px 46px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 14px;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            outline: none;
        }

        .inp::placeholder {
            color: rgba(255, 255, 255, 0.25);
        }

        .inp:focus {
            border-color: rgba(168, 85, 247, 0.6);
            background: rgba(255, 255, 255, 0.09);
            box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.12);
        }

        .inp-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.3);
            font-size: 16px;
            pointer-events: none;
            transition: color 0.2s;
        }

        .inp-wrap:focus-within .inp-icon {
            color: rgba(168, 85, 247, 0.8);
        }

        /* Toggle password eye */
        .eye-btn {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.3);
            cursor: pointer;
            font-size: 16px;
            padding: 0;
            transition: color 0.2s;
        }

        .eye-btn:hover {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #a855f7, #ec4899);
            border: none;
            border-radius: 14px;
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 8px 24px rgba(168, 85, 247, 0.35);
            position: relative;
            overflow: hidden;
        }

        .btn-login::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), transparent);
            opacity: 0;
            transition: opacity 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(168, 85, 247, 0.5);
        }

        .btn-login:hover::after {
            opacity: 1;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Role badge pills */
        .role-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.04em;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.10);
            color: rgba(255, 255, 255, 0.5);
        }

        /* Error box */
        .error-box {
            background: rgba(239, 68, 68, 0.10);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 14px;
            padding: 14px 16px;
        }

        /* Logo wrapper */
        .logo-ring {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: linear-gradient(135deg, #a855f7, #ec4899);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 32px rgba(168, 85, 247, 0.4);
            margin: 0 auto 20px;
        }

        /* Divider */
        .divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.07);
            margin: 24px 0;
        }

        label {
            color: rgba(255, 255, 255, 0.5);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            display: block;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>
    <!-- Background orbs -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div style="width: 100%; max-width: 420px; position: relative; z-index: 10;">

        <!-- Logo & Brand -->
        <div style="text-align: center; margin-bottom: 28px;">
            <div class="logo-ring">
                <img src="{{ asset('images/tara.jpeg') }}" alt="ARIHANTH Logo"
                    style="width: 84px; height: 84px; object-fit: contain;"
                    onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'bi bi-gem\' style=\'font-size:28px; color:white;\'></i>'">
            </div>
            <h1 style="color: #fff; font-size: 22px; font-weight: 900; letter-spacing: -0.03em; margin: 0 0 6px;">ARIHANTH JEWELLERS</h1>
            <!--<p style="color: rgba(255,255,255,0.35); font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase;">Enterprise Portal</p>-->
        </div>

        <!-- Card -->
        <div class="glass-card" style="padding: 36px 36px 28px;">

            <!-- Title -->
            <div style="margin-bottom: 24px;">
                <h2 style="color: #fff; font-size: 20px; font-weight: 800; margin: 0 0 4px;">Welcome back</h2>
                <p style="color: rgba(255,255,255,0.35); font-size: 13px; margin: 0;">Sign in to your account</p>
            </div>

            <!-- Error Messages -->
            @if ($errors->any() || session('error'))
            <div class="error-box" style="margin-bottom: 20px;">
                <div style="display: flex; gap: 10px; align-items: flex-start;">
                    <i class="bi bi-exclamation-circle-fill" style="color: #f87171; font-size: 15px; margin-top: 1px; flex-shrink: 0;"></i>
                    <div>
                        @if(session('error'))
                        <p style="color: #fca5a5; font-size: 12px; font-weight: 600; margin: 0;">{{ session('error') }}</p>
                        @endif
                        @foreach ($errors->all() as $error)
                        <p style="color: #fca5a5; font-size: 12px; font-weight: 600; margin: 0;">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('unified.login') }}" id="unifiedLoginForm">
                @csrf
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">

                <!-- Identifier Field -->
                <div style="margin-bottom: 16px;">
                    <label for="login">Email / User Code / BP Code</label>
                    <div class="inp-wrap" style="position: relative;">
                        <i class="bi bi-person-circle inp-icon"></i>
                        <input type="text"
                            id="login"
                            name="login"
                            class="inp"
                            value="{{ old('login') }}"
                            placeholder="e.g. SA0001 or you@email.com"
                            autocomplete="username"
                            required>
                    </div>
                </div>

                <!-- Password Field -->
                <div style="margin-bottom: 24px;">
                    <label for="password">Password</label>
                    <div class="inp-wrap" style="position: relative;">
                        <i class="bi bi-shield-lock inp-icon"></i>
                        <input type="password"
                            id="password"
                            name="password"
                            class="inp"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                            style="padding-right: 44px;">
                        <button type="button" class="eye-btn" onclick="togglePassword()" id="eyeBtn">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-login" id="loginBtn">
                    <span id="btnText">Sign In &nbsp;<i class="bi bi-arrow-right-short" style="font-size: 16px; vertical-align: middle;"></i></span>
                    <span id="btnLoading" style="display:none;">
                        <i class="bi bi-arrow-repeat" style="animation: spin 1s linear infinite; display: inline-block;"></i>
                        &nbsp;Signing in...
                    </span>
                </button>
            </form>

            <div style="margin-top: 20px; text-align: center;">
                <p style="color: rgba(255,255,255,0.3); font-size: 12px; margin-bottom: 8px;">Don't have an account?</p>
                <a href="{{ route('register') }}" 
                   style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #fff; text-decoration: none; font-size: 13px; font-weight: 700; transition: all 0.2s;"
                   onmouseover="this.style.background='rgba(255,255,255,0.1)'; this.style.borderColor='rgba(255,255,255,0.2)';"
                   onmouseout="this.style.background='rgba(255,255,255,0.05)'; this.style.borderColor='rgba(255,255,255,0.1)';"
                >
                    <i class="bi bi-person-plus-fill" style="font-size: 16px; color: #a855f7;"></i>
                    Partner Registration
                </a>
            </div>

            <div style="margin-top: 32px; padding: 24px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 24px; text-align: center;">
                <p style="color: rgba(255,255,255,0.3); font-size: 10px; font-weight: 800; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 20px;">Experience the App</p>
                
                <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                    <!-- Google Play -->
                    <div style="flex: 1; max-width: 140px;">
                        <div style="background: #fff; padding: 10px; border-radius: 16px; margin-bottom: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.4); transition: transform 0.3s ease;" 
                             onmouseover="this.style.transform='translateY(-5px)'" 
                             onmouseout="this.style.transform='translateY(0)'">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://play.google.com/store/apps/details?id=com.ajpl.arianth"
                                 alt="Google Play QR"
                                 style="width: 100%; height: auto; display: block; filter: contrast(1.1);">
                        </div>
                        <a href="https://play.google.com/store/apps/details?id=com.ajpl.arianth"
                           style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 4px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #fff; text-decoration: none; font-size: 11px; font-weight: 600; transition: all 0.2s;"
                           onmouseover="this.style.background='rgba(255,255,255,0.1)'; this.style.borderColor='rgba(255,255,255,0.2)';"
                           onmouseout="this.style.background='rgba(255,255,255,0.05)'; this.style.borderColor='rgba(255,255,255,0.1)';"
                        >
                            <i class="bi bi-google-play" style="color: #34d399;"></i> Play Store
                        </a>
                    </div>

                    <!-- App Store -->
                    <div style="flex: 1; max-width: 140px;">
                        <div style="background: #fff; padding: 10px; border-radius: 16px; margin-bottom: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.4); transition: transform 0.3s ease;"
                             onmouseover="this.style.transform='translateY(-5px)'" 
                             onmouseout="this.style.transform='translateY(0)'">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://apps.apple.com/us/app/arihanth-tara/id6762273408"
                                 alt="App Store QR"
                                 style="width: 100%; height: auto; display: block; filter: contrast(1.1);">
                        </div>
                        <a href="https://apps.apple.com/us/app/arihanth-tara/id6762273408"
                           style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 4px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #fff; text-decoration: none; font-size: 11px; font-weight: 600; transition: all 0.2s;"
                           onmouseover="this.style.background='rgba(255,255,255,0.1)'; this.style.borderColor='rgba(255,255,255,0.2)';"
                           onmouseout="this.style.background='rgba(255,255,255,0.05)'; this.style.borderColor='rgba(255,255,255,0.1)';"
                        >
                            <i class="bi bi-apple"></i> App Store
                        </a>
                    </div>
                </div>
            </div>


            <div class="divider"></div>

            <!-- Role hints -->
            <!--<p style="color: rgba(255,255,255,0.25); font-size: 10px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; text-align: center; margin-bottom: 10px;">Access Levels</p>-->
            <!--<div class="role-pills">-->
            <!--    <span class="pill"><i class="bi bi-star-fill" style="color: #fbbf24;"></i> Super Admin</span>-->
            <!--    <span class="pill"><i class="bi bi-shield-fill" style="color: #60a5fa;"></i> Admin</span>-->
            <!--    <span class="pill"><i class="bi bi-bag-fill" style="color: #34d399;"></i> Buyer</span>-->
            <!--    <span class="pill"><i class="bi bi-tools" style="color: #fb923c;"></i> Craftsman</span>-->
            <!--    <span class="pill"><i class="bi bi-key-fill" style="color: #a78bfa;"></i> Key User</span>-->
            <!--    <span class="pill"><i class="bi bi-person-fill" style="color: #f472b6;"></i> User</span>-->
            <!--</div>-->
        </div>

        <!-- Footer -->
        <p style="color: rgba(255,255,255,0.2); font-size: 10px; font-weight: 600; letter-spacing: 0.15em; text-transform: uppercase; text-align: center; margin-top: 24px;">
            &copy; {{ date('Y') }} ARIHANTH JEWELLERS &mdash; All rights reserved
        </p>
    </div>

    <style>
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <script>
        function togglePassword() {
            const pw = document.getElementById('password');
            const ico = document.getElementById('eyeIcon');
            if (pw.type === 'password') {
                pw.type = 'text';
                ico.className = 'bi bi-eye-slash';
            } else {
                pw.type = 'password';
                ico.className = 'bi bi-eye';
            }
        }

        // Show loading state on submit
        document.querySelector('form').addEventListener('submit', function() {
            document.getElementById('btnText').style.display = 'none';
            document.getElementById('btnLoading').style.display = 'inline';
            document.getElementById('loginBtn').disabled = true;
        });
        // Request GPS Location on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        document.getElementById('latitude').value = position.coords.latitude;
                        document.getElementById('longitude').value = position.coords.longitude;
                    },
                    function(error) {
                        console.warn("Geolocation failed or was denied: ", error);
                    },
                    { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
                );
            }
        });
    </script>
</body>

</html>