<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Registration | ARIHANTH Jewellers</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background: #0f0f0f;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            overflow-x: hidden;
        }
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.1;
            pointer-events: none;
            z-index: 0;
        }
        .orb-1 { width: 600px; height: 600px; background: #a855f7; top: -200px; left: -100px; }
        .orb-2 { width: 500px; height: 500px; background: #ec4899; bottom: -100px; right: -100px; }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 32px;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.6);
            width: 100%;
            max-width: 800px;
            position: relative;
            z-index: 10;
        }
        .input-field {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            padding: 12px 16px;
            width: 100%;
            transition: all 0.2s;
            outline: none;
            font-size: 14px;
        }
        .input-field:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #a855f7;
            box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.15);
        }
        label {
            color: rgba(255, 255, 255, 0.4);
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 6px;
            display: block;
        }
        .section-title {
            color: rgba(255, 255, 255, 0.2);
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        .btn-submit {
            background: linear-gradient(135deg, #a855f7, #ec4899);
            color: white;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 16px;
            border-radius: 14px;
            width: 100%;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(168, 85, 247, 0.3);
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(168, 85, 247, 0.5);
        }
        .logo-ring {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: linear-gradient(135deg, #a855f7, #ec4899);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(168, 85, 247, 0.4);
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="glass-card p-8 md:p-12">
        <div class="flex flex-col items-center mb-10 text-center">
            <div class="logo-ring">
                <i class="bi bi-person-plus-fill text-white text-2xl"></i>
            </div>
            <h1 class="text-white text-2xl font-black uppercase tracking-tight">Partner Registration</h1>
            <p class="text-white/40 text-sm mt-1">Join the Arihanth Jewellers network</p>
        </div>

        @if(session('success'))
            <div class="mb-8 p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl flex items-center gap-3">
                <i class="bi bi-check-circle-fill text-emerald-400 text-xl"></i>
                <p class="text-emerald-300 text-sm font-bold">{{ session('success') }}</p>
            </div>
        @endif

        <form action="{{ route('register.store') }}" method="POST" class="space-y-10">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                <!-- Personal -->
                <div class="space-y-6">
                    <h3 class="section-title">Personal Details</h3>
                    <div>
                        <label>Full Name</label>
                        <input type="text" name="name" required class="input-field" placeholder="John Doe">
                    </div>
                    <div>
                        <label>Email Address</label>
                        <input type="email" name="email" required class="input-field" placeholder="john@example.com">
                    </div>
                    <div>
                        <label>Mobile Number</label>
                        <input type="text" name="mobile" required class="input-field" placeholder="+91 98765 43210">
                    </div>
                    <div>
                        <label>Create Password</label>
                        <div class="relative">
                            <input type="password" id="reg_password" name="password" required class="input-field pr-12" placeholder="••••••••">
                            <button type="button" onclick="toggleRegPassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60">
                                <i class="bi bi-eye" id="reg_eye_icon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Business -->
                <div class="space-y-6">
                    <h3 class="section-title">Business Details</h3>
                    <div>
                        <label>Business Name</label>
                        <input type="text" name="business_name" required class="input-field" placeholder="Arihanth Gems">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label>City</label>
                            <input type="text" name="city" class="input-field" placeholder="Mumbai">
                        </div>
                        <div>
                            <label>Pincode</label>
                            <input type="text" name="pincode" class="input-field" placeholder="400001">
                        </div>
                    </div>
                    <div>
                        <label>State</label>
                        <input type="text" name="state" class="input-field" placeholder="Maharashtra">
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-white/5 space-y-4">
                <button type="submit" class="btn-submit">Submit Registration</button>
                <div class="text-center">
                    <a href="/" class="text-white/30 text-[10px] font-black uppercase tracking-widest hover:text-white transition-colors">
                        <i class="bi bi-arrow-left mr-1"></i> Back to Login
                    </a>
                </div>
            </div>
        </form>
    </div>
    <script>
        function toggleRegPassword() {
            const pw = document.getElementById('reg_password');
            const ico = document.getElementById('reg_eye_icon');
            if (pw.type === 'password') {
                pw.type = 'text';
                ico.className = 'bi bi-eye-slash';
            } else {
                pw.type = 'password';
                ico.className = 'bi bi-eye';
            }
        }
    </script>
</body>
</html>
