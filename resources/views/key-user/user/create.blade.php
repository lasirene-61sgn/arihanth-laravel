@extends('key-user.layouts.app')

@section('title', 'Create User')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Create User</h1>
            <nav class="flex text-sm text-gray-500 mt-1">
                <a href="{{ route('key-user.dashboard') }}" class="hover:text-indigo-600 transition">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('key-user.user.index') }}" class="hover:text-indigo-600 transition">Users</a>
                <span class="mx-2">/</span>
                <span class="text-gray-800 font-medium">Create</span>
            </nav>
        </div>
        <a href="{{ route('key-user.user.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
            <i class="bi bi-arrow-left mr-2"></i> Back to List
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h4 class="text-lg font-bold text-gray-800">User Details</h4>
        </div>

        <div class="p-6">
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                    <div class="flex">
                        <i class="bi bi-exclamation-triangle-fill text-red-500 mr-3"></i>
                        <ul class="text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('key-user.user.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    
                    <div class="space-y-1">
                        <label for="bp_name" class="block text-sm font-semibold text-gray-700">Business Partner</label>
                        <input type="text" id="bp_name" value="{{ $buyer->bp_code }} - {{ $buyer->business_name }}" readonly disabled
                               class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-500 text-sm cursor-not-allowed">
                        <input type="hidden" name="bp_code" value="{{ $buyer->bp_code }}">
                    </div>

                    <div class="space-y-1">
                        <label for="name" class="block text-sm font-semibold text-gray-700">Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition @error('name') border-red-500 @enderror">
                    </div>

                    <div class="space-y-1">
                        <label for="full_name" class="block text-sm font-semibold text-gray-700">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition @error('full_name') border-red-500 @enderror">
                    </div>

                    <div class="space-y-1">
                        <label for="email" class="block text-sm font-semibold text-gray-700">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition @error('email') border-red-500 @enderror">
                    </div>

                    <div class="space-y-1">
                        <label for="email_id" class="block text-sm font-semibold text-gray-700">Email ID <span class="text-red-500">*</span></label>
                        <input type="email" id="email_id" name="email_id" value="{{ old('email_id') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition @error('email_id') border-red-500 @enderror">
                    </div>

                    <div class="space-y-1">
                        <label for="mobile_no" class="block text-sm font-semibold text-gray-700">Mobile Number <span class="text-red-500">*</span></label>
                        <input type="text" id="mobile_no" name="mobile_no" value="{{ old('mobile_no') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition @error('mobile_no') border-red-500 @enderror">
                    </div>

                    <div class="space-y-1">
                        <label for="password" class="block text-sm font-semibold text-gray-700">Password <span class="text-red-500">*</span></label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition @error('password') border-red-500 @enderror">
                    </div>

                    <div class="space-y-1">
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1">
                        <label for="status" class="block text-sm font-semibold text-gray-700">Status <span class="text-red-500">*</span></label>
                        <select id="status" name="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="dob" class="block text-sm font-semibold text-gray-700">Date of Birth</label>
                        <input type="date" id="dob" name="dob" value="{{ old('dob') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1">
                        <label for="profile_picture" class="block text-sm font-semibold text-gray-700">Profile Picture</label>
                        <input type="file" id="profile_picture" name="profile_picture"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition">
                    </div>

                    <div class="space-y-1">
                        <label for="city" class="block text-sm font-semibold text-gray-700">City</label>
                        <input type="text" id="city" name="city" value="{{ old('city') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1">
                        <label for="state" class="block text-sm font-semibold text-gray-700">State</label>
                        <input type="text" id="state" name="state" value="{{ old('state') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1">
                        <label for="country" class="block text-sm font-semibold text-gray-700">Country</label>
                        <input type="text" id="country" name="country" value="{{ old('country') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1">
                        <label for="pincode" class="block text-sm font-semibold text-gray-700">Pincode</label>
                        <input type="text" id="pincode" name="pincode" value="{{ old('pincode') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1">
                        <label for="aadhar_number" class="block text-sm font-semibold text-gray-700">Aadhar Number</label>
                        <input type="text" id="aadhar_number" name="aadhar_number" value="{{ old('aadhar_number') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1">
                        <label for="aadhar_photo" class="block text-sm font-semibold text-gray-700">Aadhar Photo</label>
                        <input type="file" id="aadhar_photo" name="aadhar_photo"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition">
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                    <a href="{{ route('key-user.user.index') }}" class="px-6 py-2 text-sm font-semibold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition shadow-md">
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection