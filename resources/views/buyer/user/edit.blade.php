@extends('buyer.layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('buyer.user-management.index') }}" class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors mb-2">
                <i class="bi bi-arrow-left mr-1"></i> Back to User List
            </a>
            <h1 class="text-2xl font-bold text-slate-800">Edit User: <span class="text-blue-600">{{ $user->full_name }}</span></h1>
            <p class="text-sm text-slate-500">Update profile details and access permissions for this user.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden text-slate-900">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <h4 class="text-lg font-bold text-slate-800">Account Configuration</h4>
        </div>

        <div class="p-6 md:p-8">
            <form action="{{ route('buyer.user-management.update', $user->id) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="full_name" 
                               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none" 
                               value="{{ old('full_name', $user->full_name) }}" required>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" 
                               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none" 
                               value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700">Mobile Number <span class="text-red-500">*</span></label>
                        <input type="text" name="mobile_no" 
                               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none" 
                               value="{{ old('mobile_no', $user->mobile_no) }}" required>
                    </div>
                </div>

                <div class="pt-4">
                    <h5 class="text-xs font-bold text-blue-600 uppercase tracking-widest border-b border-blue-50 pb-2 mb-6">Security Update</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">New Password</label>
                            <input type="password" name="password" 
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 transition-all outline-none" 
                                   placeholder="Leave blank to keep current">
                            <p class="text-[10px] text-slate-400 italic">Only enter if you wish to change the user's password.</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Confirm New Password</label>
                            <input type="password" name="password_confirmation" 
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 transition-all outline-none" 
                                   placeholder="Re-type new password">
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <div class="flex items-center gap-2 mb-4 text-slate-800">
                        <i class="bi bi-shield-check text-blue-600"></i>
                        <h5 class="text-sm font-bold uppercase tracking-widest">Adjust Permissions</h5>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-100">
                        @foreach(\App\Models\User::getAllPermissions() as $permission)
                        <label for="perm_{{ $permission }}" class="flex items-center p-3 bg-white rounded-xl border border-transparent hover:border-blue-200 hover:shadow-sm transition-all cursor-pointer group">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="permissions[]" value="{{ $permission }}" id="perm_{{ $permission }}"
                                       class="w-5 h-5 text-blue-600 bg-white border-slate-300 rounded focus:ring-blue-500 cursor-pointer"
                                       {{ in_array($permission, $user->getPermissionsArray()) ? 'checked' : '' }}>
                            </div>
                            <span class="ml-3 text-sm font-medium text-slate-600 group-hover:text-slate-900 capitalize transition-colors">
                                {{ str_replace('_', ' ', $permission) }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-8 border-t border-slate-100">
                    <a href="{{ route('buyer.user-management.index') }}" 
                       class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all shadow-md shadow-blue-200">
                        <i class="bi bi-check-lg mr-2"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection