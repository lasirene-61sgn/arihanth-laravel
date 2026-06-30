@extends('buyer.layouts.app')

@section('title', 'Create Key User')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Create Key User</h1>
            <p class="text-sm text-slate-500">Register a new administrative user for your business account.</p>
        </div>
        <a href="{{ route('buyer.key-user-management.index') }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-50 transition-all shadow-sm">
            <i class="bi bi-arrow-left mr-2"></i> Back to List
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <h4 class="text-lg font-bold text-slate-800">New Key User Information</h4>
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

            <form method="POST" action="{{ route('buyer.key-user-management.store') }}" class="space-y-8">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <div class="space-y-2">
                        <label for="bp_code" class="text-sm font-bold text-slate-700">BP Code <span class="text-red-500">*</span></label>
                        <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all appearance-none" 
                                id="bp_code" name="bp_code" required>
                            <option value="">Select BP Code</option>
                            @foreach($buyers as $buyer)
                                <option value="{{ $buyer->bp_code }}" {{ old('bp_code') == $buyer->bp_code ? 'selected' : '' }}>
                                    {{ $buyer->bp_code }} - {{ $buyer->business_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="full_name" class="text-sm font-bold text-slate-700">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all" 
                               id="full_name" name="full_name" value="{{ old('full_name') }}" required placeholder="Enter user's full name">
                    </div>

                    <div class="space-y-2">
                        <label for="email_id" class="text-sm font-bold text-slate-700">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all" 
                               id="email_id" name="email_id" value="{{ old('email_id') }}" required placeholder="email@example.com">
                    </div>

                    <div class="space-y-2">
                        <label for="mobile_no" class="text-sm font-bold text-slate-700">Mobile Number <span class="text-red-500">*</span></label>
                        <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all" 
                               id="mobile_no" name="mobile_no" value="{{ old('mobile_no') }}" required placeholder="Enter 10 digit number">
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="text-sm font-bold text-slate-700">Password <span class="text-red-500">*</span></label>
                        <input type="password" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all" 
                               id="password" name="password" required placeholder="Minimum 8 characters">
                    </div>

                    <div class="space-y-2">
                        <label for="password_confirmation" class="text-sm font-bold text-slate-700">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all" 
                               id="password_confirmation" name="password_confirmation" required placeholder="Re-type password">
                    </div>

                    <div class="md:col-span-2 pt-4">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="bi bi-shield-lock text-blue-600"></i>
                            <label class="text-sm font-bold text-slate-800 uppercase tracking-widest">Assign Permissions</label>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-100">
                            @foreach(App\Models\KeyUser::getAllPermissions() as $permission)
                                <label for="permission_{{ $permission }}" class="flex items-center group cursor-pointer p-2 rounded-lg hover:bg-white transition-all">
                                    <div class="relative flex items-center">
                                        <input class="w-5 h-5 text-blue-600 bg-white border-slate-300 rounded focus:ring-blue-500 cursor-pointer" 
                                               type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $permission }}" 
                                               id="permission_{{ $permission }}"
                                               {{ in_array($permission, old('permissions', [])) ? 'checked' : '' }}>
                                    </div>
                                    <span class="ml-3 text-sm font-medium text-slate-600 group-hover:text-slate-900 transition-colors">
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
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all shadow-md shadow-blue-200">
                        <i class="bi bi-plus-circle mr-2"></i> Create Key User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection