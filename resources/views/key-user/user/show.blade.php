@extends('key-user.layouts.app')

@section('title', 'View User')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">User Profile</h1>
            <nav class="flex text-sm text-gray-500 mt-1">
                <a href="{{ route('key-user.dashboard') }}" class="hover:text-indigo-600 transition">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('key-user.user.index') }}" class="hover:text-indigo-600 transition">Users</a>
                <span class="mx-2">/</span>
                <span class="text-gray-800 font-medium">View Profile</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('key-user.user.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="bi bi-arrow-left mr-2"></i> Back
            </a>
            <a href="{{ route('key-user.user.edit', $user) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition shadow-sm">
                <i class="bi bi-pencil mr-2"></i> Edit Profile
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-6 lg:p-10">
            <div class="flex flex-col lg:flex-row gap-12">
                
                <div class="w-full lg:w-1/3 flex flex-col items-center text-center">
                    <div class="relative group">
                        @if($user->profile_picture)
                            <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                 alt="Profile Picture" 
                                 class="w-40 h-40 rounded-full object-cover border-4 border-white shadow-xl ring-1 ring-gray-200">
                        @else
                            <div class="w-40 h-40 rounded-full bg-indigo-50 border-4 border-white shadow-lg ring-1 ring-gray-200 flex items-center justify-center">
                                <i class="bi bi-person-fill text-indigo-300 text-6xl"></i>
                            </div>
                        @endif
                        
                        <div class="absolute bottom-2 right-2 h-6 w-6 rounded-full border-4 border-white shadow-sm {{ $user->status ? 'bg-green-500' : 'bg-red-500' }}"></div>
                    </div>

                    <div class="mt-6">
                        <h2 class="text-2xl font-bold text-gray-900 leading-tight">{{ $user->full_name }}</h2>
                        <p class="text-sm font-mono font-bold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full mt-2 inline-block">
                            {{ $user->user_code }}
                        </p>
                        <div class="mt-4 flex flex-wrap justify-center gap-2">
                             <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-md border border-gray-200 uppercase tracking-widest">
                                BP: {{ $user->bp_code }}
                             </span>
                        </div>
                    </div>
                </div>

                <div class="flex-1">
                    <div class="bg-gray-50/50 rounded-2xl p-6 md:p-8 border border-gray-100">
                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-8 border-b border-gray-200 pb-3 flex items-center">
                            <i class="bi bi-person-badge mr-2 text-lg"></i> Account Details
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                            
                            <div class="space-y-1">
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Account Name</span>
                                <p class="text-sm font-semibold text-gray-800">{{ $user->name }}</p>
                            </div>

                            <div class="space-y-1">
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Full Name</span>
                                <p class="text-sm font-semibold text-gray-800">{{ $user->full_name }}</p>
                            </div>

                            <div class="space-y-1">
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Primary Email</span>
                                <p class="text-sm font-semibold text-indigo-600">{{ $user->email }}</p>
                            </div>

                            <div class="space-y-1">
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Alt. Email ID</span>
                                <p class="text-sm font-semibold text-gray-800">{{ $user->email_id }}</p>
                            </div>

                            <div class="space-y-1">
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Mobile Number</span>
                                <p class="text-sm font-semibold text-gray-800">{{ $user->mobile_no }}</p>
                            </div>

                            <div class="space-y-1">
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Date of Birth</span>
                                <p class="text-sm font-semibold text-gray-800">{{ $user->dob ? $user->dob->format('d M, Y') : 'N/A' }}</p>
                            </div>

                            <div class="col-span-1 md:col-span-2 pt-4">
                                <h4 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-6 border-b border-gray-200 pb-3 flex items-center">
                                    <i class="bi bi-geo-alt mr-2 text-lg"></i> Identity & Location
                                </h4>
                            </div>

                            <div class="space-y-1">
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Aadhar Number</span>
                                <p class="text-sm font-semibold text-gray-800">{{ $user->aadhar_number ?? 'N/A' }}</p>
                            </div>

                            <div class="space-y-1">
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Aadhar Proof</span>
                                @if($user->aadhar_photo)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $user->aadhar_photo) }}" alt="Aadhar" class="h-16 w-24 object-cover rounded-lg border border-gray-200 shadow-sm cursor-zoom-in hover:opacity-80 transition" onclick="window.open(this.src)">
                                    </div>
                                @else
                                    <p class="text-sm font-semibold text-gray-400 italic">Not Uploaded</p>
                                @endif
                            </div>

                            <div class="space-y-1">
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Location</span>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $user->city ?? '—' }}, {{ $user->state ?? '—' }}
                                </p>
                            </div>

                            <div class="space-y-1">
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Country / Pincode</span>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $user->country ?? '—' }} ({{ $user->pincode ?? '—' }})
                                </p>
                            </div>

                            <div class="space-y-1">
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Record Tracking</span>
                                <div class="text-[11px] text-gray-500 leading-tight space-y-1">
                                    <div>Created: <span class="font-bold">{{ $user->created_at->format('d M, Y | h:i A') }}</span></div>
                                    <div>Updated: <span class="font-bold">{{ $user->updated_at->format('d M, Y | h:i A') }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection