@extends('buyer.layouts.app')

@section('title', 'User Details')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('buyer.user-management.index') }}" class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors mb-2">
                <i class="bi bi-arrow-left mr-1"></i> Back to User List
            </a>
            <h1 class="text-2xl font-bold text-slate-800">User Details: <span class="text-blue-600">{{ $user->full_name }}</span></h1>
            <p class="text-sm text-slate-500">Overview of staff member profile and access rights.</p>
        </div>
        <div class="flex items-center">
            <a href="{{ route('buyer.user-management.edit', $user->id) }}" 
               class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-all shadow-md shadow-blue-100">
                <i class="bi bi-pencil mr-2"></i> Edit User
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-8 text-center border-b border-slate-50">
                    <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-slate-50 border border-slate-100 mb-4 shadow-inner overflow-hidden">
                        @if($user->profile_picture)
                            <img src="{{ asset($user->profile_picture) }}" class="w-full h-full object-cover">
                        @else
                            <i class="bi bi-person text-slate-300 text-5xl"></i>
                        @endif
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 leading-tight">{{ $user->full_name }}</h3>
                    <p class="text-sm text-slate-500 mt-1 mb-4">{{ $user->email }}</p>
                    
                    <span class="inline-flex px-3 py-1 bg-blue-50 text-blue-700 font-mono text-xs font-bold rounded-lg border border-blue-100">
                        {{ $user->user_code }}
                    </span>
                </div>

                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500">Status</span>
                        @if($user->status)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-green-500"></span> Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-red-500"></span> Inactive
                            </span>
                        @endif
                    </div>

                    @if($user->is_frozen)
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500">Security</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                            <i class="bi bi-snow mr-1"></i> Frozen
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6 text-slate-900">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center">
                    <i class="bi bi-info-circle text-blue-600 mr-2"></i>
                    <h4 class="text-sm font-bold uppercase tracking-widest text-slate-800">Account Information</h4>
                </div>
                <div class="p-6 md:p-8">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Mobile Number</label>
                            <p class="text-sm font-bold text-slate-700 flex items-center">
                                <i class="bi bi-phone mr-2 text-slate-300"></i>
                                {{ $user->mobile_no }}
                            </p>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Account Created</label>
                            <p class="text-sm font-bold text-slate-700 flex items-center">
                                <i class="bi bi-calendar-event mr-2 text-slate-300"></i>
                                {{ $user->created_at->format('d M Y, h:i A') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center">
                    <i class="bi bi-shield-lock text-blue-600 mr-2"></i>
                    <h4 class="text-sm font-bold uppercase tracking-widest text-slate-800">Assigned Permissions</h4>
                </div>
                <div class="p-6 md:p-8">
                    <div class="flex flex-wrap gap-2">
                        @forelse($user->getPermissionsArray() as $perm)
                            <span class="inline-flex px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 text-xs font-bold border border-blue-100 capitalize shadow-sm">
                                <i class="bi bi-check2-circle mr-1.5 opacity-60"></i>
                                {{ str_replace('_', ' ', $perm) }}
                            </span>
                        @empty
                            <div class="flex flex-col items-center justify-center py-6 w-full text-slate-400 italic">
                                <i class="bi bi-shield-slash text-3xl mb-2 opacity-20"></i>
                                <p class="text-xs">No specific permissions assigned to this user.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection