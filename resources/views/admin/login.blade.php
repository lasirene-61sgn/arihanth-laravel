<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARIHANTH - Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-6">

    <div class="max-w-md w-full">
        <div class="bg-white rounded-[2rem] shadow-2xl shadow-blue-900/10 overflow-hidden border border-slate-100">
            
            <div class="bg-[#1e293b] p-10 text-center relative">
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-[#c5a059] via-[#f4ece1] to-[#c5a059]"></div>
                
                <div class="inline-block bg-white p-2 rounded-2xl shadow-lg mb-4 border-2 border-[#c5a059]">
                    <img src="{{ asset('images/tara.jpeg') }}" alt="ARIHANTH Logo" class="h-16 w-16 object-contain">
                </div>
                
                <h3 class="text-white text-2xl font-extrabold tracking-[0.2em] uppercase">ARIHANTH JEWELLERS</h3>
                <p class="text-slate-400 text-[10px] font-bold tracking-[0.3em] uppercase mt-2">Admin Portal</p>
            </div>

            <div class="p-8 md:p-10">
                @if (session('error'))
                    <div class="mb-6 flex items-center gap-3 p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 text-sm">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ session('error') }}
                    </div>
                @endif
                
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 text-sm">
                        <ul class="list-none p-0 m-0">
                            @foreach ($errors->all() as $error)
                                <li class="flex items-center gap-2"><i class="bi bi-dot"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('admin.login') }}" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label for="email_or_user_code" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Email or User Code</label>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-[#c5a059] transition-colors">
                                <i class="bi bi-person-fill"></i>
                            </span>
                            <input type="text" id="email_or_user_code" name="email_or_user_code" value="{{ old('email_or_user_code') }}" required autofocus 
                                placeholder="Enter your credentials"
                                class="block w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#c5a059]/20 focus:border-[#c5a059] transition-all">
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-2 ml-1">
                            <label for="password" class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Password</label>
                            <a href="{{ route('admin.password.request') }}" class="text-[10px] font-bold text-[#c5a059] hover:text-[#8e6d31] uppercase tracking-tighter">Forgot?</a>
                        </div>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-[#c5a059] transition-colors">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input type="password" id="password" name="password" required placeholder="••••••••"
                                class="block w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#c5a059]/20 focus:border-[#c5a059] transition-all">
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-[#1e293b] hover:bg-[#0f172a] text-[#c5a059] font-black py-4 rounded-xl shadow-lg shadow-blue-900/20 transition-all transform active:scale-[0.98] tracking-widest text-xs uppercase border border-[#c5a059]/30">
                        SIGN IN SECURELY
                    </button>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-8">
            <p class="text-slate-400 text-[10px] font-bold tracking-[0.2em] uppercase">
                &copy; {{ date('Y') }} <span class="text-slate-600">ARIHANTH JEWELLERS</span>
            </p>
        </div>
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