@extends('buyer.layouts.app')

@section('title', 'Edit Key User')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Edit Key User</h1>
            <p class="text-sm text-slate-500">Modify access and profile for <span class="font-bold text-blue-600">{{ $keyUser->full_name }}</span></p>
        </div>
        <a href="{{ route('buyer.key-user-management.index') }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-50 transition-all shadow-sm">
            <i class="bi bi-arrow-left mr-2"></i> Back to List
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h4 class="text-lg font-bold text-slate-800 uppercase tracking-tight">User Configuration</h4>
            <span class="text-[10px] font-mono font-bold bg-slate-200 text-slate-700 px-2 py-1 rounded">ID: {{ $keyUser->user_code }}</span>
        </div>

        <div class="p-6 md:p-8">
            @if($errors->any())
                <div class="mb-6 flex items-start p-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-r-lg">
                    <i class="bi bi-exclamation-triangle-fill text-xl mr-3 opacity-80"></i>
                    <div>
                        <span class="font-bold text-sm uppercase tracking-wide">Validation Errors</span>
                        <ul class="mt-1 list-disc list-inside text-sm opacity-90">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('buyer.key-user-management.update', $keyUser) }}" class="space-y-8">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <div class="space-y-2">
                        <label for="bp_code" class="text-sm font-bold text-slate-700">BP Code <span class="text-red-500">*</span></label>
                        <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all appearance-none" 
                                id="bp_code" name="bp_code" required>
                            <option value="">Select BP Code</option>
                            @foreach($buyers as $buyer)
                                <option value="{{ $buyer->bp_code }}" 
                                        {{ (old('bp_code', $keyUser->bp_code) == $buyer->bp_code) ? 'selected' : '' }}>
                                    {{ $buyer->bp_code }} - {{ $buyer->business_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="full_name" class="text-sm font-bold text-slate-700">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all" 
                               id="full_name" name="full_name" value="{{ old('full_name', $keyUser->full_name) }}" required>
                    </div>

                    <div class="space-y-2">
                        <label for="email_id" class="text-sm font-bold text-slate-700">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all" 
                               id="email_id" name="email_id" value="{{ old('email_id', $keyUser->email_id) }}" required>
                    </div>

                    <div class="space-y-2">
                        <label for="mobile_no" class="text-sm font-bold text-slate-700">Mobile Number <span class="text-red-500">*</span></label>
                        <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all" 
                               id="mobile_no" name="mobile_no" value="{{ old('mobile_no', $keyUser->mobile_no) }}" required>
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="text-sm font-bold text-slate-700">New Password</label>
                        <input type="password" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all" 
                               id="password" name="password" placeholder="Leave blank to keep current">
                        <p class="text-[10px] text-slate-400 italic mt-1">Only fill if you want to change the password.</p>
                    </div>

                    <div class="space-y-2">
                        <label for="password_confirmation" class="text-sm font-bold text-slate-700">Confirm New Password</label>
                        <input type="password" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all" 
                               id="password_confirmation" name="password_confirmation" placeholder="Re-type new password">
                    </div>

                    <!-- <div class="space-y-2">
                        <label for="status" class="text-sm font-bold text-slate-700">Account Status</label>
                        <div class="relative">
                            <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none appearance-none" 
                                    id="status" name="status">
                                <option value="active" {{ old('status', $keyUser->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $keyUser->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <i class="bi bi-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div> -->

                    <div class="md:col-span-2 pt-6">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="bi bi-shield-check text-blue-600"></i>
                            <label class="text-sm font-bold text-slate-800 uppercase tracking-widest">Update User Permissions</label>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-6 bg-slate-50 rounded-3xl border border-slate-100">
                            @foreach(App\Models\KeyUser::getAllPermissions() as $permission)
                                <label for="permission_{{ $permission }}" class="flex items-center group cursor-pointer p-3 bg-white rounded-xl border border-transparent hover:border-blue-200 hover:shadow-sm transition-all">
                                    <div class="relative flex items-center">
                                        <input class="w-5 h-5 text-blue-600 bg-white border-slate-300 rounded focus:ring-blue-500 cursor-pointer" 
                                               type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $permission }}" 
                                               id="permission_{{ $permission }}"
                                               {{ in_array($permission, old('permissions', $keyUser->getPermissionsArray())) ? 'checked' : '' }}>
                                    </div>
                                    <span class="ml-3 text-sm font-semibold text-slate-600 group-hover:text-blue-700 transition-colors">
                                        {{ ucfirst(str_replace('_', ' ', $permission)) }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-8 border-t border-slate-100">
                    <a href="{{ route('buyer.key-user-management.index') }}" 
                       class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">
                        Cancel Changes
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all shadow-md shadow-blue-200">
                        <i class="bi bi-save mr-2"></i> Update Key User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection