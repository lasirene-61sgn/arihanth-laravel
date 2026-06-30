@extends('super-admin.layouts.app')

@section('title', 'User Detail - ' . $user->display_name)

@section('content')
<div class="tw-bg-gray-50 tw-min-h-screen tw-p-4 md:tw-p-6">
    <!-- Back Button & Breadcrumbs -->
    <div class="tw-mb-6">
        <a href="{{ route('super-admin.user-credentials.index') }}" class="tw-inline-flex tw-items-center tw-text-sm tw-text-gray-500 hover:tw-text-[#8B3B16] tw-transition-colors tw-no-underline">
            <i class="bi bi-arrow-left tw-mr-2"></i>
            Back to User Directory
        </a>
    </div>

    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-6">
        <!-- Sidebar: User Summary -->
        <div class="tw-col-span-1">
            <div class="tw-bg-white tw-border tw-border-gray-200 tw-rounded-2xl tw-shadow-sm tw-overflow-hidden">
                <div class="tw-h-24 tw-bg-gradient-to-r tw-from-[#8B3B16] tw-to-[#B55D33]"></div>
                <div class="tw-px-6 tw-pb-6">
                    <div class="tw--mt-12 tw-mb-4">
                        @if($user->profile_picture)
                            <img src="{{ $user->profile_picture }}" alt="{{ $user->display_name }}" class="tw-w-24 tw-h-24 tw-rounded-2xl tw-border-4 tw-border-white tw-shadow-md tw-object-cover">
                        @else
                            <div class="tw-w-24 tw-h-24 tw-rounded-2xl tw-border-4 tw-border-white tw-shadow-md tw-bg-gray-100 tw-flex tw-items-center tw-justify-center">
                                <i class="bi bi-person tw-text-4xl tw-text-gray-300"></i>
                            </div>
                        @endif
                    </div>
                    
                    <h2 class="tw-text-xl tw-font-bold tw-text-gray-800 tw-mb-1">{{ $user->display_name }}</h2>
                    <span class="tw-px-2.5 tw-py-1 tw-rounded-full tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-border
                        {{ $user->role_display == 'Admin' ? 'tw-bg-purple-50 tw-text-purple-700 tw-border-purple-100' : '' }}
                        {{ $user->role_display == 'Buyer' ? 'tw-bg-blue-50 tw-text-blue-700 tw-border-blue-100' : '' }}
                        {{ $user->role_display == 'Craftsman' ? 'tw-bg-orange-50 tw-text-orange-700 tw-border-orange-100' : '' }}
                        {{ $user->role_display == 'Key User' ? 'tw-bg-cyan-50 tw-text-cyan-700 tw-border-cyan-100' : '' }}
                        {{ $user->role_display == 'User' ? 'tw-bg-green-50 tw-text-green-700 tw-border-green-100' : '' }}">
                        {{ $user->role_display }}
                    </span>

                    <div class="tw-mt-6 tw-space-y-4">
                        <div class="tw-flex tw-justify-between tw-text-sm">
                            <span class="tw-text-gray-500">User Code</span>
                            <span class="tw-font-mono tw-font-bold tw-text-gray-800">{{ $user->display_code }}</span>
                        </div>
                        <div class="tw-flex tw-justify-between tw-text-sm">
                            <span class="tw-text-gray-500">BP Code</span>
                            <span class="tw-font-mono tw-font-bold tw-text-gray-800">{{ $user->display_bp_code }}</span>
                        </div>
                        <div class="tw-flex tw-justify-between tw-text-sm">
                            <span class="tw-text-gray-500">Status</span>
                            @if($user->is_frozen)
                                <span class="tw-text-red-600 tw-font-bold">Frozen</span>
                            @else
                                <span class="tw-text-green-600 tw-font-bold">Active</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Stats -->
            <div class="tw-mt-6 tw-bg-white tw-border tw-border-gray-200 tw-rounded-2xl tw-shadow-sm tw-p-6">
                <h3 class="tw-text-sm tw-font-bold tw-text-gray-800 tw-uppercase tw-tracking-widest tw-mb-4">Security Insights</h3>
                <div class="tw-space-y-6">
                    <div class="tw-flex tw-items-center tw-gap-4">
                        <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-orange-50 tw-text-orange-600 tw-flex tw-items-center tw-justify-center">
                            <i class="bi bi-key tw-text-lg"></i>
                        </div>
                        <div>
                            <p class="tw-text-[10px] tw-text-gray-400 tw-uppercase tw-font-bold tw-mb-0">Password Updates</p>
                            <p class="tw-text-lg tw-font-bold tw-text-gray-800 tw-mb-0">{{ $user->password_update_count ?? 0 }} Times</p>
                        </div>
                    </div>
                    <div class="tw-flex tw-items-center tw-gap-4">
                        <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-blue-50 tw-text-blue-600 tw-flex tw-items-center tw-justify-center">
                            <i class="bi bi-geo-alt tw-text-lg"></i>
                        </div>
                        <div>
                            <p class="tw-text-[10px] tw-text-gray-400 tw-uppercase tw-font-bold tw-mb-0">Last Known Location</p>
                            <p class="tw-text-sm tw-font-mono tw-font-bold tw-text-gray-800 tw-mb-0">
                                {{ $user->last_login_ip ?? 'None recorded' }}
                                @if($user->last_login_country)
                                    <span class="tw-text-xs tw-text-gray-500 tw-block tw-font-sans tw-mt-0.5">{{ $user->last_login_location ? $user->last_login_location . ', ' : '' }}{{ $user->last_login_country }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="tw-col-span-1 lg:tw-col-span-2 tw-space-y-6">
            <!-- Account Credentials -->
            <div class="tw-bg-white tw-border tw-border-gray-200 tw-rounded-2xl tw-shadow-sm tw-overflow-hidden">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-100 tw-flex tw-items-center tw-justify-between">
                    <h3 class="tw-text-base tw-font-bold tw-text-gray-800 tw-m-0">Access Credentials</h3>
                    <i class="bi bi-lock tw-text-gray-300"></i>
                </div>
                <div class="tw-p-6">
                    <div class="tw-bg-gray-50 tw-rounded-xl tw-p-4 tw-border tw-border-gray-100">
                        <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-6">
                            <div class="tw-flex-1">
                                <label class="tw-text-[10px] tw-font-bold tw-text-gray-400 tw-uppercase tw-tracking-widest">Login Password</label>
                                <div class="tw-flex tw-items-center tw-gap-3 tw-mt-1">
                                    <input type="password" value="{{ $user->password_plain ?? 'Not Captured' }}" readonly
                                        class="tw-bg-transparent tw-border-none tw-text-lg tw-font-mono tw-font-bold tw-text-gray-800 tw-outline-none tw-w-full" id="detail-pwd">
                                    <div class="tw-flex tw-gap-2">
                                        <button type="button" onclick="togglePasswordDetail(this, 'detail-pwd')" class="tw-w-8 tw-h-8 tw-rounded-lg tw-bg-white tw-border tw-border-gray-200 tw-text-gray-400 hover:tw-text-orange-600 tw-transition-colors tw-shadow-sm">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" onclick="copyPasswordDetail('detail-pwd')" class="tw-w-8 tw-h-8 tw-rounded-lg tw-bg-white tw-border tw-border-gray-200 tw-text-gray-400 hover:tw-text-orange-600 tw-transition-colors tw-shadow-sm">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="tw-text-[10px] tw-text-gray-400 tw-mt-4 tw-flex tw-items-center tw-gap-1">
                        <i class="bi bi-info-circle"></i>
                        Passwords are shown in plain text as captured during creation/reset for administrative reference.
                    </p>
                </div>
            </div>

            <!-- Permissions -->
            <div class="tw-bg-white tw-border tw-border-gray-200 tw-rounded-2xl tw-shadow-sm tw-overflow-hidden">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-100 tw-flex tw-items-center tw-justify-between">
                    <h3 class="tw-text-base tw-font-bold tw-text-gray-800 tw-m-0">Granted Permissions</h3>
                    <span class="tw-text-xs tw-bg-green-50 tw-text-green-700 tw-px-2 tw-py-0.5 tw-rounded tw-font-bold">Verified</span>
                </div>
                <div class="tw-p-6">
                    @php
                        $permissions = $user->getPermissionsArray();
                    @endphp

                    @if(count($permissions) > 0)
                        <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-3">
                            @foreach($permissions as $perm)
                                <div class="tw-flex tw-items-center tw-gap-3 tw-p-3 tw-bg-gray-50 tw-rounded-xl tw-border tw-border-gray-100 hover:tw-border-orange-200 tw-transition-colors">
                                    <div class="tw-w-2 tw-h-2 tw-rounded-full tw-bg-orange-500"></div>
                                    <span class="tw-text-sm tw-font-bold tw-text-gray-700 tw-capitalize">{{ str_replace('_', ' ', $perm) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="tw-text-center tw-py-8">
                            <i class="bi bi-shield-slash tw-text-3xl tw-text-gray-200 tw-mb-2"></i>
                            <p class="tw-text-gray-400 tw-text-sm">No specific permissions assigned.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Device & Connection Log Placeholder -->
            <div class="tw-bg-white tw-border tw-border-gray-200 tw-rounded-2xl tw-shadow-sm tw-overflow-hidden">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-100">
                    <h3 class="tw-text-base tw-font-bold tw-text-gray-800 tw-m-0">Access Logs</h3>
                </div>
                <div class="tw-p-0">
                    <table class="tw-w-full tw-text-sm">
                        <thead class="tw-bg-gray-50">
                            <tr>
                                <th class="tw-px-6 tw-py-3 tw-text-left tw-text-[10px] tw-font-bold tw-text-gray-400 tw-uppercase tw-tracking-widest">Event</th>
                                <th class="tw-px-6 tw-py-3 tw-text-left tw-text-[10px] tw-font-bold tw-text-gray-400 tw-uppercase tw-tracking-widest">IP Address</th>
                                <th class="tw-px-6 tw-py-3 tw-text-right tw-text-[10px] tw-font-bold tw-text-gray-400 tw-uppercase tw-tracking-widest">Date</th>
                            </tr>
                        </thead>
                        <tbody class="tw-divide-y tw-divide-gray-50">
                            @if($user->password_update_count > 0)
                            <tr>
                                <td class="tw-px-6 tw-py-4">
                                    <div class="tw-flex tw-items-center tw-gap-2">
                                        <i class="bi bi-arrow-repeat tw-text-blue-500"></i>
                                        <span class="tw-font-bold tw-text-gray-700">Password Updated</span>
                                    </div>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-font-mono tw-text-xs">-</td>
                                <td class="tw-px-6 tw-py-4 tw-text-right tw-text-gray-500">Recent</td>
                            </tr>
                            @endif

                            @forelse($loginLogs as $log)
                            <tr>
                                <td class="tw-px-6 tw-py-4">
                                    <div class="tw-flex tw-items-center tw-gap-2">
                                        <i class="bi bi-box-arrow-in-right tw-text-green-500"></i>
                                        <div class="tw-flex tw-flex-col">
                                            <span class="tw-font-bold tw-text-gray-700">Login Successful</span>
                                            <span class="tw-text-[10px] tw-text-gray-400 tw-truncate tw-max-w-[150px]" title="{{ $log->user_agent }}">
                                                @if(str_contains(strtolower($log->user_agent), 'mobile') || str_contains(strtolower($log->user_agent), 'android') || str_contains(strtolower($log->user_agent), 'iphone'))
                                                    <i class="bi bi-phone tw-mr-1"></i> Mobile
                                                @elseif(str_contains(strtolower($log->user_agent), 'windows') || str_contains(strtolower($log->user_agent), 'macintosh') || str_contains(strtolower($log->user_agent), 'linux'))
                                                    <i class="bi bi-laptop tw-mr-1"></i> PC / Desktop
                                                @else
                                                    <i class="bi bi-globe tw-mr-1"></i> Web Browser
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-font-mono tw-text-xs">
                                    {{ $log->ip_address ?? 'N/A' }}
                                    @if($log->country)
                                        <br><span class="tw-font-sans tw-text-gray-500 tw-text-[10px]">{{ $log->location ? $log->location . ', ' : '' }}{{ $log->country }}</span>
                                    @endif
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-text-right tw-text-gray-500">{{ $log->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td class="tw-px-6 tw-py-4">
                                    <div class="tw-flex tw-items-center tw-gap-2">
                                        <i class="bi bi-box-arrow-in-right tw-text-green-500"></i>
                                        <span class="tw-font-bold tw-text-gray-700">Last Login (Legacy)</span>
                                    </div>
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-font-mono tw-text-xs">
                                    {{ $user->last_login_ip ?? 'N/A' }}
                                    @if($user->last_login_country)
                                        <br><span class="tw-font-sans tw-text-gray-500 tw-text-[10px]">{{ $user->last_login_location ? $user->last_login_location . ', ' : '' }}{{ $user->last_login_country }}</span>
                                    @endif
                                </td>
                                <td class="tw-px-6 tw-py-4 tw-text-right tw-text-gray-500">{{ $user->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePasswordDetail(btn, inputId) {
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

    function copyPasswordDetail(inputId) {
        const input = document.getElementById(inputId);
        const originalType = input.type;

        input.type = 'text';
        input.select();
        document.execCommand('copy');
        input.type = originalType;

        window.getSelection().removeAllRanges();
        
        // Simple notification
        const originalHtml = document.body.innerHTML;
        alert('Credentials copied to clipboard!');
    }
</script>
@endsection
