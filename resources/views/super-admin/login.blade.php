<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Login | ARIHANTH</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .erp-shadow { box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02); }
    </style>
</head>
<body class="h-full flex items-center justify-center p-6 bg-[#f8fafc]">

    <div class="max-w-md w-full">
        <div class="text-center mb-8 flex flex-col items-center">
            <div class="bg-[#FFD700] p-2 rounded-xl mb-4 shadow-sm">
                <img src="{{ asset('images/tara.jpeg') }}" alt="ARIHANTH Logo" class="h-16 w-16 object-contain">
            </div>
            
            <h1 class="text-3xl font-[900] text-gray-900 tracking-tighter uppercase">ARIHANTH JEWELLERS</h1>
            <div class="h-1 w-12 bg-gray-900 mt-1 rounded-full"></div>
            <p class="text-gray-400 text-[14px] font-bold uppercase tracking-[0.7em] mt-3">Super Admin Portal</p>
        </div>

        <div class="bg-white rounded-3xl border border-gray-100 erp-shadow p-8 md:p-10">
            <div class="mb-8">
                <h2 class="text-2xl font-black text-gray-800">Sign In</h2>
                <p class="text-gray-400 text-sm mt-1">Access secure control panel</p>
            </div>

            @if ($errors->any() || session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
                <div class="flex">
                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-3"></i>
                    <ul class="text-xs text-red-700 font-bold space-y-1">
                        @if(session('error'))
                            <li>{{ session('error') }}</li>
                        @endif
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('super-admin.login') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label for="email_or_user_code" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">{{ __('messages.email') }} / ID</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 group-focus-within:text-gray-900 transition-colors">
                            <i class="bi bi-person-circle"></i>
                        </span>
                        <input type="text" 
                               id="email_or_user_code" 
                               name="email_or_user_code" 
                               value="{{ old('email_or_user_code') }}" 
                               required 
                               placeholder="admin_id_001"
                               class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 placeholder-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-900/5 focus:border-gray-900 transition-all text-sm font-medium">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2 ml-1">
                        <label for="password" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ __('messages.password') }}</label>
                        <a href="{{ route('super-admin.password.request') }}" class="text-[10px] font-bold text-gray-400 hover:text-gray-900 transition-colors uppercase tracking-tighter">Forgot?</a>
                    </div>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 group-focus-within:text-gray-900 transition-colors">
                            <i class="bi bi-shield-lock"></i>
                        </span>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required 
                               placeholder="••••••••"
                               class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 placeholder-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-900/5 focus:border-gray-900 transition-all text-sm font-medium">
                    </div>
                </div>

                <div class="flex items-center">
                    <input id="remember" type="checkbox" class="h-4 w-4 text-gray-900 focus:ring-gray-900 border-gray-300 rounded-md cursor-pointer">
                    <label for="remember" class="ml-2 block text-xs font-bold text-gray-500 uppercase tracking-tight cursor-pointer">{{ __('messages.remember_me') }}</label>
                </div>

                <button type="submit" 
                        class="w-full flex justify-center items-center py-4 px-4 rounded-2xl shadow-lg text-sm font-black text-white bg-gray-900 hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-all transform active:scale-[0.97] uppercase tracking-widest">
                    {{ __('messages.login') }}
                    <i class="bi bi-arrow-right-short text-xl ml-2"></i>
                </button>
            </form>
        </div>

        <p class="text-center mt-8 text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em]">
            &copy; 2026 ARIHANTH JEWELLERS. All rights reserved.
        </p>
    </div>

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