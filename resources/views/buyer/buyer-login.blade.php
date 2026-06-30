@extends('layouts.app')

@section('title', 'Buyer Login | ARIHANTH')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<div class="min-h-[80vh] flex items-center justify-center p-6 bg-gray-50">
    <div class="max-w-md w-full">
        
        <div class="text-center mb-8 flex flex-col items-center">
            <div class="bg-[#FFD700] p-3 rounded-2xl mb-4 shadow-md border border-yellow-400 flex items-center justify-center">
                <img src="{{ asset('images/tara.jpeg') }}" 
                     alt="ARIHANTH Logo" 
                     class="h-16 w-16 object-contain">
            </div>
            
            <div class="flex flex-col items-center">
                <h1 class="text-4xl font-[900] text-slate-900 tracking-tighter uppercase leading-none">
                    ARIHANTH
                </h1>
                <div class="flex items-center gap-2 mt-2">
                    <div class="h-[2px] w-8 bg-[#c5a059]"></div>
                    <p class="text-slate-500 text-[11px] font-black uppercase tracking-[0.4em] whitespace-nowrap">
                        Jewellers
                    </p>
                    <div class="h-[2px] w-8 bg-[#c5a059]"></div>
                </div>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-2 italic">
                    Buyer Portal
                </p>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200 border border-slate-100 overflow-hidden">
            <div class="p-8 md:p-12">
                <div class="mb-10 text-center">
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Welcome Back</h2>
                    <p class="text-slate-400 text-sm mt-1">Authorized access for registered buyers only</p>
                </div>

                <form method="POST" action="{{ route('buyer.login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="bp_code" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">
                            {{ __('BP Code') }}
                        </label>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-[#c5a059] transition-colors">
                                <i class="bi bi-person-vcard-fill"></i>
                            </span>
                            <input id="bp_code" type="text" name="bp_code" value="{{ old('bp_code') }}" required 
                                class="block w-full pl-11 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#c5a059]/20 focus:border-[#c5a059] transition-all @error('bp_code') border-red-500 @enderror" 
                                placeholder="Enter your BP Code" autofocus>
                        </div>
                        @error('bp_code')
                            <p class="text-red-500 text-[11px] font-bold mt-1 ml-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2 ml-1">
                            <label for="password" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                {{ __('Password') }}
                            </label>
                            <a href="{{ route('buyer.password.request') }}" class="text-[10px] font-bold text-[#c5a059] hover:text-[#8e6d31] transition-colors uppercase tracking-tighter">
                                Forgot Password?
                            </a>
                        </div>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-[#c5a059] transition-colors">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input id="password" type="password" name="password" required 
                                class="block w-full pl-11 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#c5a059]/20 focus:border-[#c5a059] transition-all @error('password') border-red-500 @enderror" 
                                placeholder="••••••••">
                        </div>
                        @error('password')
                            <p class="text-red-500 text-[11px] font-bold mt-1 ml-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full bg-slate-900 hover:bg-black text-[#c5a059] font-black py-4 rounded-2xl shadow-xl shadow-slate-200 transition-all transform active:scale-[0.98] tracking-[0.2em] text-xs uppercase border border-[#c5a059]/30 mt-4">
                        {{ __('Login to Portal') }}
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center mt-10 text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em]">
            &copy; {{ date('Y') }} ARIHANTH JEWELLERS. <br> 
            <span class="text-slate-300 font-medium">Mumbai • Jaipur • Surat</span>
        </p>
    </div>
</div>
@endsection