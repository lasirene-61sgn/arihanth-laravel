@extends('buyer.layouts.app')

@section('title', 'Key User Details')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Key User Details</h1>
            <p class="text-sm text-slate-500">Comprehensive profile view for user code: <span class="font-mono font-bold text-blue-600">{{ $keyUser->user_code }}</span></p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('buyer.key-user-management.index') }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-50 transition-all shadow-sm">
                <i class="bi bi-arrow-left mr-2"></i> Back to List
            </a>
            <a href="{{ route('buyer.key-user-management.edit', $keyUser) }}" class="inline-flex items-center px-4 py-2.5 bg-amber-50 border border-amber-200 text-amber-600 text-sm font-bold rounded-xl hover:bg-amber-100 transition-all shadow-sm">
                <i class="bi bi-pencil mr-2"></i> Edit
            </a>
            <form action="{{ route('buyer.key-user-management.destroy', $keyUser) }}" method="POST" class="m-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-red-50 border border-red-200 text-red-600 text-sm font-bold rounded-xl hover:bg-red-100 transition-all shadow-sm"
                        onclick="return confirm('Are you sure you want to delete this key user?')">
                    <i class="bi bi-trash mr-2"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-8 text-center border-b border-slate-50">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-600 text-white rounded-2xl text-2xl font-bold mb-4 shadow-lg shadow-blue-100">
                        {{ substr($keyUser->full_name, 0, 2) }}
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 leading-tight">{{ $keyUser->full_name }}</h3>
                    <p class="text-sm text-slate-500 mt-1">{{ $keyUser->email_id }}</p>
                    
                    <div class="mt-4">
                        @if($keyUser->status === 'active')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                                <span class="w-2 h-2 mr-2 rounded-full bg-green-500"></span> Active Account
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                                <span class="w-2 h-2 mr-2 rounded-full bg-red-500"></span> Inactive Account
                            </span>
                        @endif
                    </div>
                </div>
                <div class="p-6 bg-slate-50/50">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-slate-500">User Code:</span>
                        <span class="font-mono font-bold text-slate-700">{{ $keyUser->user_code }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Join Date:</span>
                        <span class="font-semibold text-slate-700">{{ $keyUser->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                    <h4 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Account Details</h4>
                </div>
                <div class="p-6 md:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Full Name</label>
                                <p class="text-sm font-bold text-slate-800">{{ $keyUser->full_name }}</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Business Partner Code</label>
                                <p class="text-sm font-bold text-slate-800">{{ $keyUser->bp_code }}</p>
                            </div>
                        </div>

                        <div class="space-y-6 border-l border-slate-100 pl-0 md:pl-8">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Email Address</label>
                                <div class="flex items-center text-sm font-semibold text-slate-700 mt-1">
                                    <i class="bi bi-envelope mr-2 text-slate-400"></i>
                                    {{ $keyUser->email_id }}
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Mobile Number</label>
                                <div class="flex items-center text-sm font-semibold text-slate-700 mt-1">
                                    <i class="bi bi-phone mr-2 text-slate-400"></i>
                                    {{ $keyUser->mobile_no }}
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(method_exists($keyUser, 'getPermissionsArray'))
                    <div class="mt-10 pt-8 border-t border-slate-100">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-4">Assigned Permissions</label>
                        <div class="flex flex-wrap gap-2">
                            @forelse($keyUser->getPermissionsArray() as $permission)
                                <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[11px] font-bold rounded-lg border border-blue-100 shadow-sm capitalize">
                                    {{ str_replace('_', ' ', $permission) }}
                                </span>
                            @empty
                                <span class="text-xs text-slate-400 italic">No special permissions assigned.</span>
                            @endforelse
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection