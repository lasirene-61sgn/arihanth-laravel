<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Craftsman Login | ARIHANTH JEWELLERS</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-6 bg-[#f8fafc]">

    <div class="max-w-md w-full">
        <div class="text-center mb-8 flex flex-col items-center">
            <div class="bg-[#FFD700] p-3 rounded-2xl mb-4 shadow-md border border-yellow-400">
                <img src="{{ asset('images/tara.jpeg') }}" alt="ARIHANTH Logo" class="h-16 w-16 object-contain">
            </div>
            
            <h1 class="text-3xl font-[900] text-gray-900 tracking-tighter uppercase">ARIHANTH</h1>
            <div class="flex items-center gap-2 mt-1">
                <div class="h-[2px] w-6 bg-[#c5a059]"></div>
                <p class="text-gray-500 text-[11px] font-black uppercase tracking-[0.4em]">Jewellers</p>
                <div class="h-[2px] w-6 bg-[#c5a059]"></div>
            </div>
            <p class="text-gray-400 text-[14px] font-bold uppercase tracking-[0.2em] mt-3 italic">Craftsman Portal</p>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-gray-200 border border-gray-100 overflow-hidden">
            <div class="p-8 md:p-10">
                <div class="mb-8">
                    <h2 class="text-2xl font-black text-gray-800 tracking-tight">Sign In</h2>
                    <p class="text-gray-400 text-sm mt-1">Enter your craftsman code to access jobs</p>
                </div>

                @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
                    <div class="flex">
                        <i class="bi bi-exclamation-triangle-fill text-red-500 mr-3"></i>
                        <ul class="text-xs text-red-700 font-bold space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <form method="POST" action="{{ route('craftsman.login') }}" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label for="craftman_code" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">
                            Craftsman Code *
                        </label>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 group-focus-within:text-[#c5a059] transition-colors">
                                <i class="bi bi-tools"></i>
                            </span>
                            <input type="text" 
                                   id="craftman_code" 
                                   name="craftman_code" 
                                   value="{{ old('craftman_code') }}" 
                                   required 
                                   placeholder="Enter Code"
                                   class="block w-full pl-11 pr-4 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-[#c5a059]/20 focus:border-[#c5a059] transition-all text-sm font-medium">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2 ml-1">
                            <label for="password" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Password *
                            </label>
                            <a href="{{ route('craftsman.password.request') }}" class="text-[10px] font-bold text-[#c5a059] hover:text-[#8e6d31] transition-colors uppercase tracking-tighter">
                                Forgot?
                            </a>
                        </div>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 group-focus-within:text-[#c5a059] transition-colors">
                                <i class="bi bi-shield-lock-fill"></i>
                            </span>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   placeholder="••••••••"
                                   class="block w-full pl-11 pr-4 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-[#c5a059]/20 focus:border-[#c5a059] transition-all text-sm font-medium">
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full flex justify-center items-center py-4 px-4 rounded-2xl shadow-xl text-sm font-black text-[#c5a059] bg-gray-900 hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-all transform active:scale-[0.97] uppercase tracking-[0.2em] border border-[#c5a059]/30">
                        Login to Portal
                        <i class="bi bi-arrow-right-short text-xl ml-2"></i>
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center mt-10 text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em]">
            &copy; {{ date('Y') }} ARIHANTH JEWELLERS. <br>
            <span class="text-gray-300 font-medium tracking-normal">Craftsmanship & Excellence</span>
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