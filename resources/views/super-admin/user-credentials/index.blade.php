@extends('super-admin.layouts.app')

@section('title', 'User Credentials List')

@section('content')
<div class="tw-bg-white tw-min-h-screen tw-p-4 md:tw-p-6">
    <!-- Page Header -->
    <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-items-start sm:tw-items-center tw-gap-4 tw-pb-4 tw-mb-6 tw-border-b tw-border-gray-200">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-800">User Credentials</h1>
            <p class="tw-text-sm tw-text-gray-500 tw-mt-1">Overview of all system users and their login passwords.</p>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="tw-bg-white tw-border tw-border-gray-200 tw-rounded-xl tw-shadow-sm tw-mb-6">
        <div class="tw-p-4">
            <form method="GET" action="{{ route('super-admin.user-credentials.index') }}" class="tw-flex tw-flex-col lg:tw-row tw-gap-4">
                <!-- Search -->
                <div class="tw-flex-1 tw-relative">
                    <i class="bi bi-search tw-absolute tw-left-3 tw-top-1/2 tw--translate-y-1/2 tw-text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, code, BP code..."
                        class="tw-w-full tw-pl-10 tw-pr-4 tw-py-2.5 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-ring-2 focus:tw-ring-[#8B3B16] focus:tw-border-[#8B3B16] tw-outline-none">
                </div>

                <!-- Role Filter -->
                <div class="tw-w-full lg:tw-w-48">
                    <select name="role" class="tw-w-full tw-px-4 tw-py-2.5 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-ring-2 focus:tw-ring-[#8B3B16] focus:tw-border-[#8B3B16] tw-outline-none">
                        <option value="">All Roles</option>
                        <option value="Admin" {{ request('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                        <option value="Buyer" {{ request('role') == 'Buyer' ? 'selected' : '' }}>Buyer</option>
                        <option value="Craftsman" {{ request('role') == 'Craftsman' ? 'selected' : '' }}>Craftsman</option>
                        <option value="Craftsman Staff" {{ request('role') == 'Craftsman Staff' ? 'selected' : '' }}>Craftsman Staff</option>
                        <option value="Key User" {{ request('role') == 'Key User' ? 'selected' : '' }}>Key User</option>
                        <option value="User" {{ request('role') == 'User' ? 'selected' : '' }}>User</option>
                    </select>
                </div>

                <div class="tw-flex tw-gap-2">
                    <button type="submit" class="tw-flex-1 lg:tw-flex-none tw-px-8 tw-py-2.5 tw-bg-[#8B3B16] tw-text-white tw-rounded-lg tw-text-sm tw-font-bold hover:tw-bg-[#722F11] tw-transition-all tw-shadow-md tw-shadow-orange-100">
                        Apply Filters
                    </button>
                    @if(request('search') || request('role'))
                    <a href="{{ route('super-admin.user-credentials.index') }}" class="tw-px-4 tw-py-2.5 tw-bg-gray-100 tw-text-gray-600 tw-rounded-lg tw-text-sm tw-font-bold hover:tw-bg-gray-200 tw-transition-colors tw-no-underline tw-flex tw-items-center">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="tw-bg-white tw-border tw-border-gray-200 tw-rounded-xl tw-shadow-sm tw-overflow-hidden">
        <div class="tw-flex tw-items-center tw-justify-between tw-px-6 tw-py-4 tw-border-b tw-border-gray-100 bg-gray-50/50">
            <div>
                <h4 class="tw-text-base tw-font-bold tw-text-gray-800 tw-m-0">User Directory</h4>
                <p class="tw-text-[10px] tw-text-gray-400 tw-uppercase tw-tracking-widest tw-font-bold tw-mt-1">Credentials & Access Control</p>
            </div>
            <span class="tw-px-3 tw-py-1 tw-bg-orange-50 tw-text-orange-700 tw-border tw-border-orange-100 tw-rounded-full tw-text-xs tw-font-bold">{{ $users->total() }} Users</span>
        </div>

        <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-sm">
                <thead>
                    <tr class="tw-bg-gray-50/80 tw-border-b tw-border-gray-200">
                        <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider">Role</th>
                        <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider">Name</th>
                        <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider">User Code</th>
                        <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider">BP Code</th>
                        <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider">Permissions</th>
                        <th class="tw-px-6 tw-py-4 tw-text-left tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider">Password</th>
                        <th class="tw-px-6 tw-py-4 tw-text-right tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="tw-divide-y tw-divide-gray-100">
                    @forelse($users as $user)
                    <tr class="hover:tw-bg-gray-50/80 tw-transition-colors group">
                        <td class="tw-px-6 tw-py-4">
                            <span class="tw-px-2.5 tw-py-1 tw-rounded-full tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-tighter tw-border
                                {{ $user->role == 'Admin' ? 'tw-bg-purple-50 tw-text-purple-700 tw-border-purple-100' : '' }}
                                {{ $user->role == 'Buyer' ? 'tw-bg-blue-50 tw-text-blue-700 tw-border-blue-100' : '' }}
                                {{ $user->role == 'Craftsman' ? 'tw-bg-orange-50 tw-text-orange-700 tw-border-orange-100' : '' }}
                                {{ $user->role == 'Craftsman Staff' ? 'tw-bg-amber-50 tw-text-amber-700 tw-border-amber-100' : '' }}
                                {{ $user->role == 'Key User' ? 'tw-bg-cyan-50 tw-text-cyan-700 tw-border-cyan-100' : '' }}
                                {{ $user->role == 'User' ? 'tw-bg-green-50 tw-text-green-700 tw-border-green-100' : '' }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-font-bold tw-text-gray-800">{{ $user->name }}</td>
                        <td class="tw-px-6 tw-py-4 tw-text-gray-600">
                            <span class="tw-font-mono tw-text-xs tw-bg-gray-100 tw-px-2 tw-py-0.5 tw-rounded">{{ $user->code }}</span>
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-text-gray-600">
                            <span class="tw-font-mono tw-text-xs tw-bg-gray-100 tw-px-2 tw-py-0.5 tw-rounded">{{ $user->bp_code }} {{$user->business_name ?? ''}} {{$user->city ?? ''}}</span>
                        </td>
                        <td class="tw-px-6 tw-py-4">
                            <div class="tw-flex tw-flex-wrap tw-gap-1 tw-max-w-[250px]">
                                @php
                                $perms = is_array($user->permissions) ? $user->permissions : json_decode($user->permissions, true) ?? [];
                                $defaults = ['work_order', 'product', 'design', 'catalogue'];
                                $allPerms = array_unique(array_merge($defaults, $perms));
                                @endphp
                                @foreach($allPerms as $p)
                                <span class="tw-px-1.5 tw-py-0.5 tw-bg-gray-50 tw-text-gray-500 tw-border tw-border-gray-200 tw-rounded tw-text-[9px] tw-font-bold tw-uppercase">{{ str_replace('_', ' ', $p) }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="tw-px-6 tw-py-4">
                            <div class="tw-flex tw-items-center tw-gap-2">
                                <div class="tw-relative">
                                    <input type="password" value="{{ $user->password }}" readonly
                                        class="tw-password-field tw-bg-transparent tw-border-none tw-text-gray-800 tw-font-mono tw-text-xs tw-outline-none tw-w-24" id="pwd-{{ $loop->index }}">
                                </div>
                                <div class="tw-flex tw-gap-1.5 tw-items-center">
                                    <button type="button" onclick="togglePassword(this, 'pwd-{{ $loop->index }}')" 
                                        class="tw-p-1.5 tw-rounded-lg tw-bg-blue-50 tw-border tw-border-blue-200 tw-text-blue-600 hover:tw-bg-blue-600 hover:tw-text-white tw-transition-all tw-shadow-xs" 
                                        title="View / Hide Password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" onclick="copyPassword('pwd-{{ $loop->index }}')" 
                                        class="tw-p-1.5 tw-rounded-lg tw-bg-indigo-50 tw-border tw-border-indigo-200 tw-text-indigo-600 hover:tw-bg-indigo-600 hover:tw-text-white tw-transition-all tw-shadow-xs" 
                                        title="Copy Password">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-text-right">
                            <a href="{{ route('super-admin.user-credentials.show', ['role' => strtolower(str_replace(' ', '-', $user->role)), 'id' => $user->id]) }}"
                                class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-bg-gray-50 tw-text-gray-700 tw-rounded-lg tw-text-xs tw-font-bold hover:tw-bg-orange-600 hover:tw-text-white tw-transition-all tw-border tw-border-gray-200 hover:tw-border-orange-600">
                                <i class="bi bi-shield-lock"></i>
                                View Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="tw-px-6 tw-py-16 tw-text-center tw-text-gray-500">
                            <div class="tw-flex tw-flex-col tw-items-center">
                                <i class="bi bi-person-x tw-text-5xl tw-mb-4 tw-opacity-20 tw-text-[#8B3B16]"></i>
                                <p class="tw-text-lg tw-font-bold tw-text-gray-400">No users found matching your criteria.</p>
                                <p class="tw-text-sm tw-mt-1">Try adjusting your search or filters.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="tw-px-6 tw-py-4 tw-border-t tw-border-gray-100 tw-bg-gray-50/50">
            {{ $users->links('vendor.pagination.custom-pagination') }}
        </div>
        @endif
    </div>
</div>

<script>
    function togglePassword(btn, inputId) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }

    function copyPassword(inputId) {
        const input = document.getElementById(inputId);
        const originalType = input.type;

        // Temporarily change to text to ensure value is copied correctly in all browsers
        input.type = 'text';
        input.select();
        document.execCommand('copy');
        input.type = originalType;

        // Clear selection
        window.getSelection().removeAllRanges();

        // Show brief notification
        alert('Password copied to clipboard!');
    }
</script>
@endsection