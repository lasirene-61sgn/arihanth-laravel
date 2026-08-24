@extends('super-admin.layouts.app')

@section('title', __('messages.dashboard_title'))

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
<style>
    .roboto-font {
        font-family: 'Roboto', sans-serif !important;
    }
</style>
<!-- Top Picks Clients Modal -->
<div class="modal fade" id="topPicksClientsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl roboto-font" style="max-width: 98%;">
        <div class="modal-content dark:tw-bg-slate-900 tw-border-0 tw-rounded-2xl tw-shadow-2xl">
            <div class="modal-header tw-border-0 tw-pb-0">
                <h5 class="modal-title tw-font-extrabold tw-text-emerald-700 dark:tw-text-emerald-400">{{ __('messages.top_picks_clients') }} (Top 15)</h5>
                <button type="button" class="btn-close dark:tw-invert" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body tw-p-9">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle dark:tw-text-gray-300">
                        <thead class="tw-bg-gray-100 dark:tw-bg-slate-800">
                            <tr>
                                <th rowspan="2" class="tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">Client Name</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">BP Code</th>
                                <th colspan="6" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-bg-emerald-50 dark:tw-bg-emerald-900/20 tw-text-emerald-700 dark:tw-text-emerald-300">WORK ORDERS (WA)</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle tw-bg-gray-50">Total Orders</th>
                            </tr>
                            <tr class="tw-bg-gray-50 dark:tw-bg-slate-800/50">
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">NEW (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">PROCESS (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">FOR APPROVAL (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-orange-500">OVERDUE (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-red-500">REJECTED (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-emerald-600">COMPLETED (C/W)</th>
                            </tr>
                        </thead>
                        <tbody>
                                                        @forelse($topPicksClientsFull as $code => $stat)
                            <tr style="background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;" class="hover: tw-transition-colors">
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{{ $stat['name'] }}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{{ $code }}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{!! $stat['new']['weight'] > 0 ? $stat['new']['count'] . ' - ' . number_format($stat['new']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-blue-600 dark:tw-text-blue-400" style="color: #2563eb !important;">{!! $stat['in_process']['weight'] > 0 ? $stat['in_process']['count'] . ' - ' . number_format($stat['in_process']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-purple-600 dark:tw-text-purple-400" style="color: #9333ea !important;">{!! $stat['for_approval']['weight'] > 0 ? $stat['for_approval']['count'] . ' - ' . number_format($stat['for_approval']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-orange-600 dark:tw-text-orange-400 tw-font-bold" style="color: #ea580c !important;">{!! $stat['overdue']['weight'] > 0 ? $stat['overdue']['count'] . ' - ' . number_format($stat['overdue']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-red-600 dark:tw-text-red-400 tw-font-bold" style="color: #dc2626 !important;">{!! $stat['rejected']['weight'] > 0 ? $stat['rejected']['count'] . ' - ' . number_format($stat['rejected']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-emerald-600 dark:tw-text-emerald-400" style="color: #059669 !important;">{!! $stat['completed']['weight'] > 0 ? $stat['completed']['count'] . ' - ' . number_format($stat['completed']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-font-black tw-text-emerald-700 dark:tw-text-emerald-400 tw-py-3 tw-text-[15px] " style="color: #047857 !important;">{{ $stat['orders'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="tw-text-center tw-py-4 tw-text-gray-500 dark:tw-text-gray-400">{{ __('messages.no_data_found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Least Picks Clients Modal -->
<div class="modal fade" id="leastPicksClientsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl roboto-font" style="max-width: 98%;">
        <div class="modal-content dark:tw-bg-slate-900 tw-border-0 tw-rounded-2xl tw-shadow-2xl">
            <div class="modal-header tw-border-0 tw-pb-0">
                <h5 class="modal-title tw-font-extrabold tw-text-gray-600 dark:tw-text-gray-400">{{ __('messages.least_pick_clients') }} (Top 15)</h5>
                <button type="button" class="btn-close dark:tw-invert" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body tw-p-9">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle dark:tw-text-gray-300">
                        <thead class="tw-bg-gray-100 dark:tw-bg-slate-800">
                            <tr>
                                <th rowspan="2" class="tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">Client Name</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">BP Code</th>
                                <th colspan="6" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-bg-gray-200 dark:tw-bg-gray-700 tw-text-gray-700 dark:tw-text-gray-300">WORK ORDERS (WA)</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle tw-bg-gray-50">Total Orders</th>
                            </tr>
                            <tr class="tw-bg-gray-50 dark:tw-bg-slate-800/50">
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">NEW (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">PROCESS (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">FOR APPROVAL (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-orange-500">OVERDUE (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-red-500">REJECTED (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-emerald-600">COMPLETED (C/W)</th>
                            </tr>
                        </thead>
                        <tbody>
                                                        @forelse($leastPicksClientsFull as $code => $stat)
                            <tr style="background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;" class="hover:tw-bg-gray-100 tw-transition-colors">
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{{ $stat['name'] }}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{{ $code }}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{!! $stat['new']['weight'] > 0 ? $stat['new']['count'] . ' - ' . number_format($stat['new']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-blue-600 dark:tw-text-blue-400" style="color: #2563eb !important;">{!! $stat['in_process']['weight'] > 0 ? $stat['in_process']['count'] . ' - ' . number_format($stat['in_process']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-purple-600 dark:tw-text-purple-400" style="color: #9333ea !important;">{!! $stat['for_approval']['weight'] > 0 ? $stat['for_approval']['count'] . ' - ' . number_format($stat['for_approval']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-orange-600 dark:tw-text-orange-400 tw-font-bold" style="color: #ea580c !important;">{!! $stat['overdue']['weight'] > 0 ? $stat['overdue']['count'] . ' - ' . number_format($stat['overdue']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-red-600 dark:tw-text-red-400 tw-font-bold" style="color: #dc2626 !important;">{!! $stat['rejected']['weight'] > 0 ? $stat['rejected']['count'] . ' - ' . number_format($stat['rejected']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-emerald-600 dark:tw-text-emerald-400" style="color: #059669 !important;">{!! $stat['completed']['weight'] > 0 ? $stat['completed']['count'] . ' - ' . number_format($stat['completed']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-font-black tw-text-gray-700 dark:tw-text-gray-400 tw-py-3 tw-text-[15px] " style="color: #374151 !important;">{{ $stat['orders'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="tw-text-center tw-py-4 tw-text-gray-500 dark:tw-text-gray-400">{{ __('messages.no_data_found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('content')
<div class="tw-p-4 md:tw-p-6">
    <div class="tw-flex tw-flex-col tw-gap-6">
        <!-- Welcome Header -->
        <div class="tw-flex tw-justify-between tw-items-center">
            <h1 class="tw-text-xl md:tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white">
                {{ __('messages.welcome') }}, <span class="tw-text-maroon dark:tw-text-maroon-light tw-font-extrabold">{{ Auth::guard('super_admin')->user()->full_name }}</span>
            </h1>
        </div>

            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-12 tw-gap-6">
            <!-- Left Content - Stats Cards -->
            <div class="lg:tw-col-span-9 tw-space-y-6">
                <!-- Top Stats Grid -->
                <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 md:tw-grid-cols-3 xl:tw-grid-cols-4 tw-gap-4">
                    <!-- Business Partner Card -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <a href="{{ route('super-admin.business-partner.index') }}" class="tw-no-underline">
                            <div class="tw-p-4">
                                <div class="tw-flex tw-justify-between tw-items-start">
                                    <div>
                                        <h2 id="stat-total-bp" class="tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ number_format($buyersCount + $craftsmenCount) }}</h2>
                                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.business_partner') }}</p>
                                    </div>
                                    <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-maroon tw-text-white tw-flex tw-items-center tw-justify-center tw-text-lg">
                                        <i class="bi bi-people"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Buyers Card -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <a href="{{ route('super-admin.business-partner.buyer') }}" class="tw-no-underline">
                            <div class="tw-p-4">
                                <div class="tw-flex tw-justify-between tw-items-start">
                                    <div>
                                        <h2 id="stat-total-buyers" class="tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ number_format($buyersCount) }}</h2>
                                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.buyers') }}</p>
                                    </div>
                                    <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-maroon tw-text-white tw-flex tw-items-center tw-justify-center tw-text-lg">
                                        <i class="bi bi-person-check"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Craftsman Card -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <a href="{{ route('super-admin.business-partner.craftman') }}" class="tw-no-underline">
                            <div class="tw-p-4">
                                <div class="tw-flex tw-justify-between tw-items-start">
                                    <div>
                                        <h2 id="stat-total-craftsmen" class="tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ number_format($craftsmenCount) }}</h2>
                                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.craftsmen') }}</p>
                                    </div>
                                    <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-maroon tw-text-white tw-flex tw-items-center tw-justify-center tw-text-lg">
                                        <i class="bi bi-hammer"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- KYC Pending Card -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <a href="{{ route('super-admin.kyc-pending.index') }}" class="tw-no-underline">
                            <div class="tw-p-4">
                                <div class="tw-flex tw-justify-between tw-items-start">
                                    <div>
                                        <h2 id="stat-pending-kyc" class="tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ number_format($kycPendingCount) }}</h2>
                                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.kyc_pending') }}</p>
                                    </div>
                                    <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-maroon tw-text-white tw-flex tw-items-center tw-justify-center tw-text-lg">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Admins Card -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <a href="{{ route('super-admin.admin.index') }}" class="tw-no-underline">
                            <div class="tw-p-4">
                                <div class="tw-flex tw-justify-between tw-items-start">
                                    <div>
                                        <h2 id="stat-total-admins" class="tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ number_format($adminsCount) }}</h2>
                                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.admins') }}</p>
                                    </div>
                                    <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-maroon tw-text-white tw-flex tw-items-center tw-justify-center tw-text-lg">
                                        <i class="bi bi-shield-check"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Key Users Card -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <a href="{{ route('super-admin.key-user.index') }}" class="tw-no-underline">
                            <div class="tw-p-4">
                                <div class="tw-flex tw-justify-between tw-items-start">
                                    <div>
                                        <h2 id="stat-total-key-users" class="tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ number_format($keyUsersCount) }}</h2>
                                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.key_users') }}</p>
                                    </div>
                                    <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-maroon tw-text-white tw-flex tw-items-center tw-justify-center tw-text-lg">
                                        <i class="bi bi-key"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Users Card -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <a href="{{ route('super-admin.user.index') }}" class="tw-no-underline">
                            <div class="tw-p-4">
                                <div class="tw-flex tw-justify-between tw-items-start">
                                    <div>
                                        <h2 id="stat-total-users" class="tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ number_format($usersCount) }}</h2>
                                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.users') }}</p>
                                    </div>
                                    <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-maroon tw-text-white tw-flex tw-items-center tw-justify-center tw-text-lg">
                                        <i class="bi bi-person"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Finance Card -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <a href="{{ route('super-admin.finance.index') }}" class="tw-no-underline">
                            <div class="tw-p-4">
                                <div class="tw-flex tw-justify-between tw-items-start">
                                    <div>
                                        <h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">₹{{ number_format($financeTotal) }}</h2>
                                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.finance') }}</p>
                                    </div>
                                    <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-maroon tw-text-white tw-flex tw-items-center tw-justify-center tw-text-lg">
                                        <i class="bi bi-currency-rupee"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Work Orders Card -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <a href="{{ route('super-admin.work-order.index') }}" class="tw-no-underline">
                            <div class="tw-p-4">
                                <div class="tw-flex tw-justify-between tw-items-start">
                                    <div>
                                        <h2 id="stat-total-work-orders" class="tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ number_format($workOrdersCount) }}</h2>
                                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.work_orders') }}</p>
                                    </div>
                                    <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-maroon tw-text-white tw-flex tw-items-center tw-justify-center tw-text-lg">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Products Card -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <a href="{{ route('super-admin.product.index') }}" class="tw-no-underline">
                            <div class="tw-p-4">
                                <div class="tw-flex tw-justify-between tw-items-start">
                                    <div>
                                        <h2 id="stat-total-products" class="tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ number_format($productsCount) }}</h2>
                                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.products') }}</p>
                                    </div>
                                    <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-maroon tw-text-white tw-flex tw-items-center tw-justify-center tw-text-lg">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Designs Card -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <a href="{{ route('super-admin.design.index') }}" class="tw-no-underline">
                            <div class="tw-p-4">
                                <div class="tw-flex tw-justify-between tw-items-start">
                                    <div>
                                        <h2 id="stat-total-designs" class="tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ number_format($designsCount) }}</h2>
                                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.designs') }}</p>
                                    </div>
                                    <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-maroon tw-text-white tw-flex tw-items-center tw-justify-center tw-text-lg">
                                        <i class="bi bi-palette"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Catalogue Card -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <a href="{{ route('super-admin.catalogue.index') }}" class="tw-no-underline">
                            <div class="tw-p-4">
                                <div class="tw-flex tw-justify-between tw-items-start">
                                    <div>
                                        <h2 id="stat-total-catalogues" class="tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ number_format($cataloguesCount) }}</h2>
                                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.catalogue') }}</p>
                                    </div>
                                    <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-maroon tw-text-white tw-flex tw-items-center tw-justify-center tw-text-lg">
                                        <i class="bi bi-book"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Purchase Orders Card -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <a href="{{ route('super-admin.purchase-order.index') }}" class="tw-no-underline">
                            <div class="tw-p-4">
                                <div class="tw-flex tw-justify-between tw-items-start">
                                    <div>
                                        <h2 id="stat-total-purchase-orders" class="tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ number_format($purchaseOrdersCount) }}</h2>
                                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.purchase_order') }}</p>
                                    </div>
                                    <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-maroon tw-text-white tw-flex tw-items-center tw-justify-center tw-text-lg">
                                        <i class="bi bi-cart-check"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Live Stock Orders Card -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <a href="{{ route('super-admin.stock-order.index') }}" class="tw-no-underline">
                            <div class="tw-p-4">
                                <div class="tw-flex tw-justify-between tw-items-start">
                                    <div>
                                        <h2 id="stat-total-stock-orders" class="tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ number_format($stockOrdersCount) }}</h2>
                                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.live_stock_order') }}</p>
                                    </div>
                                    <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-maroon tw-text-white tw-flex tw-items-center tw-justify-center tw-text-lg">
                                        <i class="bi bi-box2-heart"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Repairs Card -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <a href="{{ route('super-admin.repairs.index') }}" class="tw-no-underline">
                            <div class="tw-p-4">
                                <div class="tw-flex tw-justify-between tw-items-start">
                                    <div>
                                        <h2 id="stat-total-repairs" class="tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ number_format($repairsCount) }}</h2>
                                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.repairs') }}</p>
                                    </div>
                                    <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-maroon tw-text-white tw-flex tw-items-center tw-justify-center tw-text-lg">
                                        <i class="bi bi-tools"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Overdue Work Orders Card -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <a href="{{ route('super-admin.work-order.index', ['tab' => 'overdue-orders']) }}" class="tw-no-underline">
                            <div class="tw-p-4">
                                <div class="tw-flex tw-justify-between tw-items-start">
                                    <div>
                                        <h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ number_format($workOrdersOverdueCount) }}</h2>
                                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">Overdue Work Orders</p>
                                    </div>
                                    <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-red-100 tw-text-red-600 tw-flex tw-items-center tw-justify-center tw-text-lg">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Overdue Purchase Orders Card -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <a href="{{ route('super-admin.purchase-order.index', ['overdue' => 1]) }}" class="tw-no-underline">
                            <div class="tw-p-4">
                                <div class="tw-flex tw-justify-between tw-items-start">
                                    <div>
                                        <h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ number_format($purchaseOrdersOverdueCount) }}</h2>
                                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">Overdue Purchase Orders</p>
                                    </div>
                                    <div class="tw-w-10 tw-h-10 tw-rounded-lg tw-bg-red-100 tw-text-red-600 tw-flex tw-items-center tw-justify-center tw-text-lg">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Analytics Cards Row -->
                <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-4">
                    <!-- Top Picks Craftsman -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-p-4 tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg tw-cursor-pointer roboto-font" data-bs-toggle="modal" data-bs-target="#topPicksCraftsmanModal">
                        <div class="tw-flex tw-justify-between tw-items-center tw-mb-2">
                            <i class="bi bi-star tw-text-yellow-500 tw-text-xl"></i>
                            <i class="bi bi-arrow-right tw-text-gray-400"></i>
                        </div>
                        <h3 class="tw-text-xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ !empty($topPicksCraftsmanFull) ? collect($topPicksCraftsmanFull)->first()['allocated'] : 0 }}</h3>
                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.top_picks_craftsman') }}</p>
                    </div>

                    <!-- Least Picks Craftsman -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-p-4 tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg tw-cursor-pointer" data-bs-toggle="modal" data-bs-target="#leastPicksCraftsmanModal">
                        <div class="tw-flex tw-justify-between tw-items-center tw-mb-2">
                            <i class="bi bi-clock tw-text-blue-500 tw-text-xl"></i>
                            <i class="bi bi-arrow-right tw-text-gray-400"></i>
                        </div>
                        <h3 class="tw-text-xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ !empty($leastPicksCraftsmanFull) ? collect($leastPicksCraftsmanFull)->first()['allocated'] : 0 }}</h3>
                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.least_picks_craftsman') }}</p>
                    </div>

                    <!-- Most Selling Products -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-p-4 tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg tw-cursor-pointer" data-bs-toggle="modal" data-bs-target="#mostSellingProductsModal">
                        <div class="tw-flex tw-justify-between tw-items-center tw-mb-2">
                            <i class="bi bi-graph-up-arrow tw-text-green-500 tw-text-xl"></i>
                            <i class="bi bi-arrow-right tw-text-gray-400"></i>
                        </div>
                        <h3 class="tw-text-xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ !empty($mostSellingProductsFull) ? collect($mostSellingProductsFull)->first()['count'] : 0 }}</h3>
                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.most_selling_products') }}</p>
                    </div>

                    <!-- Least Selling Products -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-p-4 tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg tw-cursor-pointer" data-bs-toggle="modal" data-bs-target="#leastSellingProductsModal">
                        <div class="tw-flex tw-justify-between tw-items-center tw-mb-2">
                            <i class="bi bi-graph-down-arrow tw-text-red-500 tw-text-xl"></i>
                            <i class="bi bi-arrow-right tw-text-gray-400"></i>
                        </div>
                        <h3 class="tw-text-xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ !empty($leastSellingProductsFull) ? collect($leastSellingProductsFull)->first()['count'] : 0 }}</h3>
                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.least_selling_products') }}</p>
                    </div>

                    <!-- Quick Payments -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-p-4 tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <div class="tw-flex tw-justify-between tw-items-center tw-mb-2">
                            <i class="bi bi-credit-card tw-text-maroon tw-text-xl"></i>
                            <i class="bi bi-arrow-right tw-text-gray-400"></i>
                        </div>
                        <h3 class="tw-text-xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ $quickPayments }}</h3>
                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.quick_payments') }}</p>
                    </div>

                    <!-- Overdue Payments -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-p-4 tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg">
                        <div class="tw-flex tw-justify-between tw-items-center tw-mb-2">
                            <i class="bi bi-exclamation-circle tw-text-orange-500 tw-text-xl"></i>
                            <i class="bi bi-arrow-right tw-text-gray-400"></i>
                        </div>
                        <h3 class="tw-text-xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ $overduePayments }}</h3>
                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.overdue_payments') }}</p>
                    </div>

                    <!-- Top Picks Clients -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-p-4 tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg tw-cursor-pointer" data-bs-toggle="modal" data-bs-target="#topPicksClientsModal">
                        <div class="tw-flex tw-justify-between tw-items-center tw-mb-2">
                            <i class="bi bi-people-fill tw-text-emerald-500 tw-text-xl"></i>
                            <i class="bi bi-arrow-right tw-text-gray-400"></i>
                        </div>
                        <h3 class="tw-text-xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ !empty($topPicksClients) ? collect($topPicksClients)->first() : 0 }}</h3>
                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.top_picks_clients') }}</p>
                    </div>

                    <!-- Least Pick Clients -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-p-4 tw-transition-all hover:-tw-translate-y-1 hover:tw-shadow-lg tw-cursor-pointer" data-bs-toggle="modal" data-bs-target="#leastPicksClientsModal">
                        <div class="tw-flex tw-justify-between tw-items-center tw-mb-2">
                            <i class="bi bi-person-x tw-text-gray-500 tw-text-xl"></i>
                            <i class="bi bi-arrow-right tw-text-gray-400"></i>
                        </div>
                        <h3 class="tw-text-xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-mb-1">{{ !empty($leastPicksClients) ? collect($leastPicksClients)->first() : 0 }}</h3>
                        <p class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-0">{{ __('messages.least_pick_clients') }}</p>
                    </div>
                </div>

                <!-- Quick Links Section -->
                <div class="tw-space-y-4">
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-overflow-hidden tw-shadow-sm">
                        <div class="tw-bg-gray-50 dark:tw-bg-slate-800/50 tw-px-4 tw-py-3 tw-border-b tw-border-gray-200 dark:tw-border-slate-800">
                            <h6 class="tw-mb-0 tw-font-bold tw-text-gray-900 dark:tw-text-white tw-text-sm">
                                <i class="bi bi-link-45deg tw-mr-2 tw-text-maroon"></i>{{ __('messages.quick_links') }}
                            </h6>
                        </div>
                        <div class="tw-p-4">
                            <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-3 xl:tw-grid-cols-5 tw-gap-3">
                                <a href="{{ route('super-admin.business-partner.buyer') }}" class="tw-flex tw-items-center tw-gap-3 tw-p-3 tw-bg-gray-50 dark:tw-bg-slate-800/50 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-lg tw-text-gray-700 dark:tw-text-gray-200 hover:tw-bg-maroon hover:tw-text-white hover:tw-border-maroon tw-transition-all tw-no-underline tw-group">
                                    <i class="bi bi-person-plus-fill tw-text-lg tw-text-maroon group-hover:tw-text-white"></i>
                                    <span class="tw-text-xs tw-font-bold">{{ __('messages.add_user') }}</span>
                                </a>
                                <a href="#" class="tw-flex tw-items-center tw-gap-3 tw-p-3 tw-bg-gray-50 dark:tw-bg-slate-800/50 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-lg tw-text-gray-700 dark:tw-text-gray-200 hover:tw-bg-maroon hover:tw-text-white hover:tw-border-maroon tw-transition-all tw-no-underline tw-group">
                                    <i class="bi bi-receipt tw-text-lg tw-text-maroon group-hover:tw-text-white"></i>
                                    <span class="tw-text-xs tw-font-bold">{{ __('messages.overdue_payments') }}</span>
                                </a>
                                <a href="#" class="tw-flex tw-items-center tw-gap-3 tw-p-3 tw-bg-gray-50 dark:tw-bg-slate-800/50 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-lg tw-text-gray-700 dark:tw-text-gray-200 hover:tw-bg-maroon hover:tw-text-white hover:tw-border-maroon tw-transition-all tw-no-underline tw-group">
                                    <i class="bi bi-brush-fill tw-text-lg tw-text-maroon group-hover:tw-text-white"></i>
                                    <span class="tw-text-xs tw-font-bold">{{ __('messages.designs') }}</span>
                                </a>
                                <a href="#" class="tw-flex tw-items-center tw-gap-3 tw-p-3 tw-bg-gray-50 dark:tw-bg-slate-800/50 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-lg tw-text-gray-700 dark:tw-text-gray-200 hover:tw-bg-maroon hover:tw-text-white hover:tw-border-maroon tw-transition-all tw-no-underline tw-group">
                                    <i class="bi bi-newspaper tw-text-lg tw-text-maroon group-hover:tw-text-white"></i>
                                    <span class="tw-text-xs tw-font-bold">{{ __('messages.craftsmen') }}</span>
                                </a>
                                <a href="#" class="tw-flex tw-items-center tw-gap-3 tw-p-3 tw-bg-gray-50 dark:tw-bg-slate-800/50 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-lg tw-text-gray-700 dark:tw-text-gray-200 hover:tw-bg-maroon hover:tw-text-white hover:tw-border-maroon tw-transition-all tw-no-underline tw-group">
                                    <i class="bi bi-book-fill tw-text-lg tw-text-maroon group-hover:tw-text-white"></i>
                                    <span class="tw-text-xs tw-font-bold">{{ __('messages.catalogue') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- External Links Section -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-overflow-hidden tw-shadow-sm">
                        <div class="tw-bg-gray-50 dark:tw-bg-slate-800/50 tw-px-4 tw-py-3 tw-border-b tw-border-gray-200 dark:tw-border-slate-800">
                            <h6 class="tw-mb-0 tw-font-bold tw-text-gray-900 dark:tw-text-white tw-text-sm">
                                <i class="bi bi-box-arrow-up-right tw-mr-2 tw-text-maroon"></i>{{ __('messages.external_links') }}
                            </h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar - Calendar -->
            <div class="lg:tw-col-span-3">
                <div class="tw-sticky tw-top-6 tw-bg-white dark:tw-bg-slate-900 tw-border tw-border-gray-200 dark:tw-border-slate-800 tw-rounded-xl tw-overflow-hidden tw-shadow-sm">
                    <!-- Calendar Header -->
                    <div class="tw-bg-white dark:tw-bg-slate-900 tw-px-4 tw-py-3 tw-border-b tw-border-gray-100 dark:tw-border-slate-800">
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <button class="tw-p-1.5 tw-rounded-lg hover:tw-bg-gray-100 dark:hover:tw-bg-slate-800 tw-text-gray-500 dark:tw-text-gray-400 tw-transition-all" id="prevMonth">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <h6 class="tw-mb-0 tw-font-bold tw-text-gray-900 dark:tw-text-white tw-text-sm" id="currentMonth">January 2026</h6>
                            <button class="tw-p-1.5 tw-rounded-lg hover:tw-bg-gray-100 dark:hover:tw-bg-slate-800 tw-text-gray-500 dark:tw-text-gray-400 tw-transition-all" id="nextMonth">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="tw-p-3">
                        <table class="tw-w-full tw-text-center tw-text-[11px] tw-border-separate tw-border-spacing-y-1">
                            <thead>
                                <tr class="tw-text-gray-400 tw-font-semibold tw-uppercase">
                                    <th class="tw-pb-3">Mon</th>
                                    <th class="tw-pb-3">Tue</th>
                                    <th class="tw-pb-3">Wed</th>
                                    <th class="tw-pb-3">Thu</th>
                                    <th class="tw-pb-3">Fri</th>
                                    <th class="tw-pb-3">Sat</th>
                                    <th class="tw-pb-3">Sun</th>
                                </tr>
                            </thead>
                            <tbody id="calendarBody" class="dark:tw-text-gray-300">
                                <!-- Calendar days will be generated by JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Today's Meetings Section -->
                    <div id="eventsSection" class="tw-p-4 tw-bg-gray-50/50 dark:tw-bg-slate-800/30 tw-border-t tw-border-gray-100 dark:tw-border-slate-800">
                        <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                            <h6 class="tw-mb-0 tw-font-bold tw-text-gray-900 dark:tw-text-white tw-text-[11px] tw-uppercase tw-tracking-wider">{{ __('messages.todays_meetings') }}</h6>
                            <button class="tw-bg-maroon hover:tw-bg-maroon/90 tw-text-white tw-text-[10px] tw-font-bold tw-px-3 tw-py-1.5 tw-rounded-lg tw-transition-all tw-shadow-[0_2px_10px_-3px_rgba(128,0,0,0.3)] hover:tw-shadow-[0_4px_15px_-3px_rgba(128,0,0,0.4)] tw-flex tw-items-center tw-gap-1" id="addMeeting">
                                <i class="bi bi-plus-lg"></i> {{ __('messages.add_event') }}
                            </button>
                        </div>
                        <div class="tw-text-center tw-text-gray-400 tw-py-8" id="noMeetings">
                            <div class="tw-w-12 tw-h-12 tw-rounded-full tw-bg-gray-100 dark:tw-bg-slate-800 tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-3">
                                <i class="bi bi-calendar2-x tw-text-xl tw-opacity-50"></i>
                            </div>
                            <p class="tw-mb-0 tw-text-xs tw-font-medium">{{ __('messages.no_meetings') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals for Statistics -->

<!-- Orders List Modal -->
<div class="modal fade" id="ordersListModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content dark:tw-bg-slate-900 tw-border-0 tw-rounded-xl tw-shadow-2xl">
            <div class="modal-header tw-border-0 tw-pb-0">
                <h6 class="modal-title tw-font-bold tw-text-gray-900 dark:tw-text-white" id="ordersListModalTitle">Order Numbers</h6>
                <button type="button" class="btn-close dark:tw-invert" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body tw-pt-3 tw-max-h-96 tw-overflow-y-auto">
                <div id="ordersListModalBody" class="tw-flex tw-flex-col tw-gap-2">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Picks Craftsman Modal -->
<div class="modal fade" id="topPicksCraftsmanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl roboto-font" style="max-width: 98%;">
        <div class="modal-content dark:tw-bg-slate-900 tw-border-0 tw-rounded-2xl tw-shadow-2xl">
            <div class="modal-header tw-border-0 tw-pb-0 tw-flex tw-justify-between tw-items-center">
                <h5 class="modal-title tw-font-extrabold tw-text-indigo-700 dark:tw-text-indigo-400">{{ __('messages.top_picks_craftsman') }}</h5>
                <div class="tw-flex tw-items-center tw-gap-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="printTopPicksCraftsman()">
                        <i class="bi bi-printer"></i> Print
                    </button>
                    <button type="button" class="btn-close dark:tw-invert" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="tw-px-9 tw-pt-4">
                <div class="tw-flex tw-flex-wrap tw-gap-4 tw-items-center tw-justify-between tw-bg-gray-50 dark:tw-bg-slate-800 tw-p-3 tw-rounded-lg">
                    <!-- Type Filter -->
                    <div class="tw-flex tw-items-center tw-gap-2">
                        <span class="tw-text-sm tw-font-bold tw-text-gray-700 dark:tw-text-gray-300">Type:</span>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="tpmTypeFilter" id="tpmTypeBoth" value="both" checked>
                            <label class="btn btn-outline-secondary btn-sm" for="tpmTypeBoth">Both</label>

                            <input type="radio" class="btn-check" name="tpmTypeFilter" id="tpmTypeWA" value="wa">
                            <label class="btn btn-outline-secondary btn-sm" for="tpmTypeWA">Work Orders (WA)</label>

                            <input type="radio" class="btn-check" name="tpmTypeFilter" id="tpmTypePA" value="pa">
                            <label class="btn btn-outline-secondary btn-sm" for="tpmTypePA">Purchase Orders (PA)</label>
                        </div>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="tw-flex tw-items-center tw-gap-2">
                        <span class="tw-text-sm tw-font-bold tw-text-gray-700 dark:tw-text-gray-300">Status:</span>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="tpmStatusFilter" id="tpmStatusAll" value="all" checked>
                            <label class="btn btn-outline-secondary btn-sm" for="tpmStatusAll">All</label>

                            <input type="radio" class="btn-check" name="tpmStatusFilter" id="tpmStatusActive" value="active">
                            <label class="btn btn-outline-secondary btn-sm" for="tpmStatusActive">Active (In Process / For Approval / Overdue)</label>

                            <input type="radio" class="btn-check" name="tpmStatusFilter" id="tpmStatusCompleted" value="completed">
                            <label class="btn btn-outline-secondary btn-sm" for="tpmStatusCompleted">Completed</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body tw-p-9">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle dark:tw-text-gray-300">
                        <thead class="tw-bg-gray-100 dark:tw-bg-slate-800">
                            <tr>
                                <th rowspan="2" class="tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">{{ __('messages.craftsman') }}</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">{{ __('messages.bp_code') }}</th>
                                <th colspan="5" class="tpm-col-wa tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-bg-blue-50 dark:tw-bg-blue-900/20 tw-text-blue-700 dark:tw-text-blue-300">WORK ORDERS (WA)</th>
                                <th colspan="6" class="tpm-col-pa tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-bg-indigo-50 dark:tw-bg-indigo-900/20 tw-text-indigo-700 dark:tw-text-indigo-300">PURCHASE ORDERS (PA)</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">{{ __('messages.total_weight') }}</th>
                            </tr>
                            <tr class="tw-bg-gray-50 dark:tw-bg-slate-800/50">
                                <th class="tpm-col-wa tw-text-center tw-text-[14px] tw-font-bold">INPROCESS (C/W)</th>
                                <th class="tpm-col-wa tw-text-center tw-text-[14px] tw-font-bold">FOR APPROVAL (C/W)</th>
                                <th class="tpm-col-wa tw-text-center tw-text-[14px] tw-font-bold">OVERDUE (C/W)</th>
                                <th class="tpm-col-wa tw-text-center tw-text-[14px] tw-font-bold">COMPLETED (C/W)</th>
                                <th class="tpm-col-wa tw-text-center tw-text-[14px] tw-font-bold tw-bg-blue-100/50 dark:tw-bg-blue-800/30">WA TOTAL</th>
                                <th class="tpm-col-pa tw-text-center tw-text-[14px] tw-font-bold">ALLOC (C/W)</th>
                                <th class="tpm-col-pa tw-text-center tw-text-[14px] tw-font-bold">PROCESS (C/W)</th>
                                <th class="tpm-col-pa tw-text-center tw-text-[14px] tw-font-bold">DONE (C/W)</th>
                                <th class="tpm-col-pa tw-text-center tw-text-[14px] tw-font-bold">FOR APPROVAL (C/W)</th>
                                <th class="tpm-col-pa tw-text-center tw-text-[14px] tw-font-bold tw-text-orange-500">OVERDUE(C/W)</th>
                                <th class="tpm-col-pa tw-text-center tw-text-[14px] tw-font-bold dark:tw-bg-indigo-800/30">PA TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPicksCraftsmanFull as $code => $stat)
                            @php
                                $hasInProcess = ($stat['wo']['in_process']['count'] > 0 || $stat['po']['in_process']['count'] > 0) ? 'true' : 'false';
                                $hasForApproval = ($stat['wo']['for_approval']['count'] > 0 || $stat['po']['for_approval']['count'] > 0) ? 'true' : 'false';
                                $hasOverdue = ($stat['wo']['overdue']['count'] > 0 || $stat['po']['overdue']['count'] > 0) ? 'true' : 'false';
                                $hasCompleted = ($stat['wo']['completed']['count'] > 0 || $stat['po']['completed']['count'] > 0) ? 'true' : 'false';
                            @endphp
                            <tr class="tpm-row hover:tw-bg-blue-50/50 tw-transition-colors" 
                                data-in-process="{{ $hasInProcess }}" 
                                data-for-approval="{{ $hasForApproval }}" 
                                data-overdue="{{ $hasOverdue }}" 
                                data-completed="{{ $hasCompleted }}">
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{{ $stat['name'] }}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{{ $code }}</td>
                                
                                {{-- Work Orders --}}


                                <td class="tpm-col-wa tw-text-center tw-text-[14px] tw-text-blue-600 dark:tw-text-blue-400" style="color: #2563eb !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['wo']['in_process']['weight'] > 0 ? '<span class="tw-cursor-pointer hover:tw-opacity-80" style="text-decoration-line: underline; text-decoration-style: dashed; text-underline-offset: 3px;" onclick="showOrdersList(this)" data-orders="' . htmlspecialchars(json_encode($stat['wo']['in_process']['orders'] ?? [])) . '" data-title="In Process (WO)">' . $stat['wo']['in_process']['count'] . ' - ' . number_format($stat['wo']['in_process']['weight'], 2) . '</span>' : '' !!}</td>
                                <td class="tpm-col-wa tw-text-center tw-text-[14px] tw-text-purple-600 dark:tw-text-purple-400" style="color: #9333ea !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['wo']['for_approval']['weight'] > 0 ? '<span class="tw-cursor-pointer hover:tw-opacity-80" style="text-decoration-line: underline; text-decoration-style: dashed; text-underline-offset: 3px;" onclick="showOrdersList(this)" data-orders="' . htmlspecialchars(json_encode($stat['wo']['for_approval']['orders'] ?? [])) . '" data-title="For Approval (WO)">' . $stat['wo']['for_approval']['count'] . ' - ' . number_format($stat['wo']['for_approval']['weight'], 2) . '</span>' : '' !!}</td>
                                <td class="tpm-col-wa tw-text-center tw-text-[14px] tw-text-orange-600 dark:tw-text-orange-400 tw-font-bold" style="color: #ea580c !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['wo']['overdue']['weight'] > 0 ? '<span class="tw-cursor-pointer hover:tw-opacity-80" style="text-decoration-line: underline; text-decoration-style: dashed; text-underline-offset: 3px;" onclick="showOrdersList(this)" data-orders="' . htmlspecialchars(json_encode($stat['wo']['overdue']['orders'] ?? [])) . '" data-title="Overdue (WO)">' . $stat['wo']['overdue']['count'] . ' - ' . number_format($stat['wo']['overdue']['weight'], 2) . '</span>' : '' !!}</td>
                                <td class="tpm-col-wa tw-text-center tw-text-[14px] tw-text-green-600 dark:tw-text-green-400" style="color: #16a34a !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['wo']['completed']['weight'] > 0 ? '<span class="tw-cursor-pointer hover:tw-opacity-80" style="text-decoration-line: underline; text-decoration-style: dashed; text-underline-offset: 3px;" onclick="showOrdersList(this)" data-orders="' . htmlspecialchars(json_encode($stat['wo']['completed']['orders'] ?? [])) . '" data-title="Completed (WO)">' . $stat['wo']['completed']['count'] . ' - ' . number_format($stat['wo']['completed']['weight'], 2) . '</span>' : '' !!}</td>
                                <td class="tpm-col-wa tw-text-center tw-text-[14px] tw-font-bold dark:tw-bg-blue-900/10 tw-text-blue-800 dark:tw-text-blue-300" style="color: #1e40af !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{{ number_format($stat['wa_total_weight'], 2) }}</td>
                                
                                {{-- Purchase Orders --}}
                                <td class="tpm-col-pa tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['po']['allocated']['weight'] > 0 ? '<span class="tw-cursor-pointer hover:tw-opacity-80" style="text-decoration-line: underline; text-decoration-style: dashed; text-underline-offset: 3px;" onclick="showOrdersList(this)" data-orders="' . htmlspecialchars(json_encode($stat['po']['allocated']['orders'] ?? [])) . '" data-title="Allocated (PO)">' . $stat['po']['allocated']['count'] . ' - ' . number_format($stat['po']['allocated']['weight'], 2) . '</span>' : '' !!}</td>
                                <td class="tpm-col-pa tw-text-center tw-text-[14px] tw-text-indigo-600 dark:tw-text-indigo-400" style="color: #4f46e5 !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['po']['in_process']['weight'] > 0 ? '<span class="tw-cursor-pointer hover:tw-opacity-80" style="text-decoration-line: underline; text-decoration-style: dashed; text-underline-offset: 3px;" onclick="showOrdersList(this)" data-orders="' . htmlspecialchars(json_encode($stat['po']['in_process']['orders'] ?? [])) . '" data-title="In Process (PO)">' . $stat['po']['in_process']['count'] . ' - ' . number_format($stat['po']['in_process']['weight'], 2) . '</span>' : '' !!}</td>
                                <td class="tpm-col-pa tw-text-center tw-text-[14px] tw-text-emerald-600 dark:tw-text-emerald-400" style="color: #059669 !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['po']['completed']['weight'] > 0 ? '<span class="tw-cursor-pointer hover:tw-opacity-80" style="text-decoration-line: underline; text-decoration-style: dashed; text-underline-offset: 3px;" onclick="showOrdersList(this)" data-orders="' . htmlspecialchars(json_encode($stat['po']['completed']['orders'] ?? [])) . '" data-title="Completed (PO)">' . $stat['po']['completed']['count'] . ' - ' . number_format($stat['po']['completed']['weight'], 2) . '</span>' : '' !!}</td>
                                <td class="tpm-col-pa tw-text-center tw-text-[14px] tw-text-purple-600 dark:tw-text-purple-400" style="color: #9333ea !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['po']['for_approval']['weight'] > 0 ? '<span class="tw-cursor-pointer hover:tw-opacity-80" style="text-decoration-line: underline; text-decoration-style: dashed; text-underline-offset: 3px;" onclick="showOrdersList(this)" data-orders="' . htmlspecialchars(json_encode($stat['po']['for_approval']['orders'] ?? [])) . '" data-title="For Approval (PO)">' . $stat['po']['for_approval']['count'] . ' - ' . number_format($stat['po']['for_approval']['weight'], 2) . '</span>' : '' !!}</td>
                                <td class="tpm-col-pa tw-text-center tw-text-[14px] tw-text-orange-600 dark:tw-text-orange-400 tw-font-bold" style="color: #ea580c !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['po']['overdue']['weight'] > 0 ? '<span class="tw-cursor-pointer hover:tw-opacity-80" style="text-decoration-line: underline; text-decoration-style: dashed; text-underline-offset: 3px;" onclick="showOrdersList(this)" data-orders="' . htmlspecialchars(json_encode($stat['po']['overdue']['orders'] ?? [])) . '" data-title="Overdue (PO)">' . $stat['po']['overdue']['count'] . ' - ' . number_format($stat['po']['overdue']['weight'], 2) . '</span>' : '' !!}</td>
                                <td class="tpm-col-pa tw-text-center tw-text-[14px] tw-font-bold dark:tw-bg-indigo-900/10 tw-text-indigo-800 dark:tw-text-indigo-300" style="color: #3730a3 !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{{ number_format($stat['po_total_weight'], 2) }}</td>

                                <td class="tw-text-center tw-font-black tw-text-blue-700 dark:tw-text-blue-400 tw-py-3 tw-text-xs" style="color: #1d4ed8 !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{{ number_format($stat['total_weight'], 3) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="14" class="tw-text-center tw-py-4 tw-text-gray-500 dark:tw-text-gray-400">{{ __('messages.no_data_found') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Least Picks Craftsman Modal -->
<div class="modal fade" id="leastPicksCraftsmanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 98%;">
        <div class="modal-content dark:tw-bg-slate-900 tw-border-0 tw-rounded-2xl tw-shadow-2xl">
            <div class="modal-header tw-border-0 tw-pb-0">
                <h5 class="modal-title tw-font-bold tw-text-gray-900 dark:tw-text-white">{{ __('messages.least_picks_craftsman') }} (Top 15)</h5>
                <button type="button" class="btn-close dark:tw-invert" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body tw-p-6">
                <div class="table-responsive">
                    <table class="table table-hover align-middle dark:tw-text-gray-300">
                        <thead>
                            <tr class="tw-bg-gray-50 dark:tw-bg-slate-800/50">
                                <th class="tw-border-0 tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-py-3 dark:tw-text-gray-400">{{ __('messages.craftsman') }}</th>
                                <th class="tw-border-0 tw-text-center tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-py-3 dark:tw-text-gray-400">{{ __('messages.bp_code') }}</th>
                                <th class="tw-border-0 tw-text-center tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-py-3 dark:tw-text-gray-400">{{ __('messages.allocated') }}</th>
                                <th class="tw-border-0 tw-text-center tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-py-3 dark:tw-text-gray-400">{{ __('messages.completed') }}</th>
                                <th class="tw-border-0 tw-text-center tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-py-3 dark:tw-text-gray-400">{{ __('messages.total_weight') }}</th>
                                <th class="tw-border-0 tw-text-center tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-py-3 dark:tw-text-gray-400">{{ __('messages.total_amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                                                        @forelse($leastPicksCraftsmanFull as $code => $stat)
                            <tr style="background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;" class="hover:tw-bg-gray-100 tw-transition-colors">
                                <td class="tw-py-4">
                                    <div class="tw-font-semibold tw-text-gray-900 dark:tw-text-white">{{ $stat['name'] }}</div>
                                </td>
                                <td class="tw-text-center tw-py-4"><span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-gray-100 dark:tw-bg-slate-800 tw-text-gray-800 dark:tw-text-gray-200">{{ $code }}</span></td>
                                <td class="tw-text-center tw-font-bold tw-py-4 tw-text-gray-900 dark:tw-text-white">{{ $stat['allocated'] }}</td>
                                <td class="tw-text-center tw-text-green-600 dark:tw-text-green-400 tw-py-4">{{ $stat['completed'] }}</td>
                                <td class="tw-text-center tw-font-semibold tw-text-blue-600 dark:tw-text-blue-400 tw-py-4">{{ number_format($stat['total_weight'], 3) }}</td>
                                <td class="tw-text-center tw-font-semibold tw-text-red-600 dark:tw-text-red-400 tw-py-4">₹{{ number_format($stat['total_amount'], 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">{{ __('messages.no_data_found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Most Selling Products Modal -->
<div class="modal fade" id="mostSellingProductsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 98%;">
        <div class="modal-content dark:tw-bg-slate-900 tw-border-0 tw-rounded-2xl tw-shadow-2xl">
            <div class="modal-header tw-border-0 tw-pb-0">
                <h5 class="modal-title tw-font-bold tw-text-gray-900 dark:tw-text-white">{{ __('messages.most_selling_products') }} (Top 15)</h5>
                <button type="button" class="btn-close dark:tw-invert" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body tw-p-6">
                <div class="table-responsive">
                    <table class="table table-hover align-middle dark:tw-text-gray-300">
                        <thead>
                            <tr class="tw-bg-gray-50 dark:tw-bg-slate-800/50">
                                <th class="tw-border-0 tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-py-3 dark:tw-text-gray-400">{{ __('messages.product') }}</th>
                                <th class="tw-border-0 tw-text-center tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-py-3 dark:tw-text-gray-400">{{ __('messages.total_usage') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mostSellingProductsFull as $key => $stat)
                            <tr>
                                <td class="tw-py-4">
                                    <div class="tw-font-semibold tw-text-gray-900 dark:tw-text-white">{{ $stat['product_category'] }}</div>
                                </td>
                                <td class="tw-text-center tw-py-4">
                                    <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-sm tw-font-medium tw-bg-green-100 dark:tw-bg-green-900/30 tw-text-green-800 dark:tw-text-green-400">{{ $stat['count'] }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4">{{ __('messages.no_data_found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Least Selling Products Modal -->
<div class="modal fade" id="leastSellingProductsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 98%;">
        <div class="modal-content dark:tw-bg-slate-900 tw-border-0 tw-rounded-2xl tw-shadow-2xl">
            <div class="modal-header tw-border-0 tw-pb-0">
                <h5 class="modal-title tw-font-bold tw-text-gray-900 dark:tw-text-white">{{ __('messages.least_selling_products') }} (Top 15)</h5>
                <button type="button" class="btn-close dark:tw-invert" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body tw-p-6">
                <div class="table-responsive">
                    <table class="table table-hover align-middle dark:tw-text-gray-300">
                        <thead>
                            <tr class="tw-bg-gray-50 dark:tw-bg-slate-800/50">
                                <th class="tw-border-0 tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-py-3 dark:tw-text-gray-400">{{ __('messages.product') }}</th>
                                <th class="tw-border-0 tw-text-center tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-py-3 dark:tw-text-gray-400">{{ __('messages.total_usage') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leastSellingProductsFull as $key => $stat)
                            <tr>
                                <td class="tw-py-4">
                                    <div class="tw-font-semibold tw-text-gray-900 dark:tw-text-white">{{ $stat['product_category'] }}</div>
                                </td>
                                <td class="tw-text-center tw-py-4">
                                    <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-sm tw-font-medium tw-bg-red-100 dark:tw-bg-red-900/30 tw-text-red-800 dark:tw-text-red-400">{{ $stat['count'] }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4">{{ __('messages.no_data_found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>






<!-- Top Picks Clients Modal -->
<div class="modal fade" id="topPicksClientsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl roboto-font" style="max-width: 98%;">
        <div class="modal-content dark:tw-bg-slate-900 tw-border-0 tw-rounded-2xl tw-shadow-2xl">
            <div class="modal-header tw-border-0 tw-pb-0">
                <h5 class="modal-title tw-font-extrabold tw-text-emerald-700 dark:tw-text-emerald-400">{{ __('messages.top_picks_clients') }} (Top 15)</h5>
                <button type="button" class="btn-close dark:tw-invert" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body tw-p-9">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle dark:tw-text-gray-300">
                        <thead class="tw-bg-gray-100 dark:tw-bg-slate-800">
                            <tr>
                                <th rowspan="2" class="tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">Client Name</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">BP Code</th>
                                <th colspan="6" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-bg-emerald-50 dark:tw-bg-emerald-900/20 tw-text-emerald-700 dark:tw-text-emerald-300">WORK ORDERS (WA)</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle tw-bg-gray-50">Total Orders</th>
                            </tr>
                            <tr class="tw-bg-gray-50 dark:tw-bg-slate-800/50">
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">NEW (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">PROCESS (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">FOR APPROVAL (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-orange-500">OVERDUE (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-red-500">REJECTED (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-emerald-600">COMPLETED (C/W)</th>
                            </tr>
                        </thead>
                        <tbody>
                                                        @forelse($topPicksClientsFull as $code => $stat)
                            <tr style="background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;" class="hover: tw-transition-colors">
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{{ $stat['name'] }}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{{ $code }}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{!! $stat['new']['weight'] > 0 ? $stat['new']['count'] . ' - ' . number_format($stat['new']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-blue-600 dark:tw-text-blue-400" style="color: #2563eb !important;">{!! $stat['in_process']['weight'] > 0 ? $stat['in_process']['count'] . ' - ' . number_format($stat['in_process']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-purple-600 dark:tw-text-purple-400" style="color: #9333ea !important;">{!! $stat['for_approval']['weight'] > 0 ? $stat['for_approval']['count'] . ' - ' . number_format($stat['for_approval']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-orange-600 dark:tw-text-orange-400 tw-font-bold" style="color: #ea580c !important;">{!! $stat['overdue']['weight'] > 0 ? $stat['overdue']['count'] . ' - ' . number_format($stat['overdue']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-red-600 dark:tw-text-red-400 tw-font-bold" style="color: #dc2626 !important;">{!! $stat['rejected']['weight'] > 0 ? $stat['rejected']['count'] . ' - ' . number_format($stat['rejected']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-emerald-600 dark:tw-text-emerald-400" style="color: #059669 !important;">{!! $stat['completed']['weight'] > 0 ? $stat['completed']['count'] . ' - ' . number_format($stat['completed']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-font-black tw-text-emerald-700 dark:tw-text-emerald-400 tw-py-3 tw-text-[15px] " style="color: #047857 !important;">{{ $stat['orders'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="tw-text-center tw-py-4 tw-text-gray-500 dark:tw-text-gray-400">{{ __('messages.no_data_found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Least Picks Clients Modal -->
<div class="modal fade" id="leastPicksClientsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl roboto-font" style="max-width: 98%;">
        <div class="modal-content dark:tw-bg-slate-900 tw-border-0 tw-rounded-2xl tw-shadow-2xl">
            <div class="modal-header tw-border-0 tw-pb-0">
                <h5 class="modal-title tw-font-extrabold tw-text-gray-600 dark:tw-text-gray-400">{{ __('messages.least_pick_clients') }} (Top 15)</h5>
                <button type="button" class="btn-close dark:tw-invert" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body tw-p-9">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle dark:tw-text-gray-300">
                        <thead class="tw-bg-gray-100 dark:tw-bg-slate-800">
                            <tr>
                                <th rowspan="2" class="tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">Client Name</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">BP Code</th>
                                <th colspan="6" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-bg-gray-200 dark:tw-bg-gray-700 tw-text-gray-700 dark:tw-text-gray-300">WORK ORDERS (WA)</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle tw-bg-gray-50">Total Orders</th>
                            </tr>
                            <tr class="tw-bg-gray-50 dark:tw-bg-slate-800/50">
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">NEW (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">PROCESS (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">FOR APPROVAL (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-orange-500">OVERDUE (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-red-500">REJECTED (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-emerald-600">COMPLETED (C/W)</th>
                            </tr>
                        </thead>
                        <tbody>
                                                        @forelse($leastPicksClientsFull as $code => $stat)
                            <tr style="background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;" class="hover:tw-bg-gray-100 tw-transition-colors">
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{{ $stat['name'] }}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{{ $code }}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{!! $stat['new']['weight'] > 0 ? $stat['new']['count'] . ' - ' . number_format($stat['new']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-blue-600 dark:tw-text-blue-400" style="color: #2563eb !important;">{!! $stat['in_process']['weight'] > 0 ? $stat['in_process']['count'] . ' - ' . number_format($stat['in_process']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-purple-600 dark:tw-text-purple-400" style="color: #9333ea !important;">{!! $stat['for_approval']['weight'] > 0 ? $stat['for_approval']['count'] . ' - ' . number_format($stat['for_approval']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-orange-600 dark:tw-text-orange-400 tw-font-bold" style="color: #ea580c !important;">{!! $stat['overdue']['weight'] > 0 ? $stat['overdue']['count'] . ' - ' . number_format($stat['overdue']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-red-600 dark:tw-text-red-400 tw-font-bold" style="color: #dc2626 !important;">{!! $stat['rejected']['weight'] > 0 ? $stat['rejected']['count'] . ' - ' . number_format($stat['rejected']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-emerald-600 dark:tw-text-emerald-400" style="color: #059669 !important;">{!! $stat['completed']['weight'] > 0 ? $stat['completed']['count'] . ' - ' . number_format($stat['completed']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-font-black tw-text-gray-700 dark:tw-text-gray-400 tw-py-3 tw-text-[15px] " style="color: #374151 !important;">{{ $stat['orders'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="tw-text-center tw-py-4 tw-text-gray-500 dark:tw-text-gray-400">{{ __('messages.no_data_found') }}</td>
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
    // Calendar JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        const calendarBody = document.getElementById('calendarBody');
        const currentMonthElement = document.getElementById('currentMonth');
        const prevMonthBtn = document.getElementById('prevMonth');
        const nextMonthBtn = document.getElementById('nextMonth');

        let currentDate = new Date();
        let currentMonth = currentDate.getMonth();
        let currentYear = currentDate.getFullYear();

        const months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        window.calendarEvents = {};

        async function loadCalendarData(month, year) {
            try {
                const response = await fetch(`{{ route('super-admin.dashboard.calendar-data') }}?month=${month + 1}&year=${year}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (response.ok) {
                    window.calendarEvents = await response.json();
                    generateCalendar(month, year);
                    
                    // Show events for today by default
                    let today = new Date();
                    let todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
                    showEventsForDate(todayStr);
                }
            } catch (error) {
                console.error('Error loading calendar data:', error);
                generateCalendar(month, year);
            }
        }

        function generateCalendar(month, year) {
            calendarBody.innerHTML = '';
            currentMonthElement.textContent = `${months[month]} ${year}`;

            let firstDay = new Date(year, month, 1);
            let startingDayOfWeek = firstDay.getDay();
            startingDayOfWeek = startingDayOfWeek === 0 ? 6 : startingDayOfWeek - 1;
            let monthLength = new Date(year, month + 1, 0).getDate();
            let prevMonthLength = new Date(year, month, 0).getDate();

            let day = 1;
            let nextMonthDay = 1;

            for (let i = 0; i < 6; i++) {
                let row = document.createElement('tr');
                for (let j = 0; j < 7; j++) {
                    let cell = document.createElement('td');

                    if (i === 0 && j < startingDayOfWeek) {
                        cell.textContent = prevMonthLength - startingDayOfWeek + j + 1;
                        cell.classList.add('tw-py-2', 'tw-text-gray-300', 'dark:tw-text-gray-600');
                    } else if (day > monthLength) {
                        cell.textContent = nextMonthDay;
                        cell.classList.add('tw-py-2', 'tw-text-gray-300', 'dark:tw-text-gray-600');
                        nextMonthDay++;
                    } else {
                        cell.textContent = day;
                        cell.classList.add('tw-py-2', 'tw-cursor-pointer', 'tw-rounded-lg', 'hover:tw-bg-gray-200', 'dark:hover:tw-bg-slate-700', 'tw-transition-all');
                        
                        // Alternating grey background for odd days (1, 3, 5, 7...)
                        if (day % 2 !== 0) {
                            cell.style.backgroundColor = '#e5e7eb';
                            cell.classList.add('dark:tw-bg-slate-800/50');
                        }

                        let today = new Date();
                        if (day === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                            cell.classList.add('tw-bg-maroon', 'tw-text-white', 'tw-font-bold');
                            cell.style.borderRadius = '50%';
                            cell.style.width = '32px';
                            cell.style.height = '32px';
                            cell.style.lineHeight = '32px';
                            cell.style.padding = '0';
                            cell.style.display = 'inline-block';
                            cell.style.backgroundColor = '#800000'; // Maroon
                        }

                        let dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                        
                        if (window.calendarEvents && window.calendarEvents[dateStr]) {
                            const data = window.calendarEvents[dateStr];
                            const hasHoliday = data.holidays && data.holidays.length > 0;
                            const hasOrder = (data.work_orders && (data.work_orders.new > 0 || data.work_orders.allocated > 0 || data.work_orders.in_process > 0 || data.work_orders.completed > 0)) || 
                                            (data.purchase_orders && (data.purchase_orders.new > 0 || data.purchase_orders.allocated > 0 || data.purchase_orders.in_process > 0 || data.purchase_orders.completed > 0)) || 
                                            data.stock_orders > 0;

                            if (hasHoliday) {
                                cell.classList.add('tw-text-red-500', 'tw-font-bold');
                            }
                            
                            if (hasOrder) {
                                let dot = document.createElement('div');
                                dot.classList.add('tw-w-1', 'tw-h-1', 'tw-bg-maroon', 'tw-rounded-full', 'tw-mx-auto', 'tw-mt-0.5');
                                if (cell.classList.contains('tw-bg-maroon')) {
                                    dot.classList.add('tw-bg-white');
                                }
                                cell.appendChild(dot);
                            }
                        }

                        cell.addEventListener('click', function() {
                            document.querySelectorAll('#calendarBody td').forEach(td => {
                                td.classList.remove('tw-bg-maroon/10', 'tw-text-maroon', 'tw-font-bold');
                            });
                            if (!this.classList.contains('tw-bg-maroon')) {
                                this.classList.add('tw-bg-maroon/10', 'tw-text-maroon', 'tw-font-bold', 'tw-rounded-full');
                            }
                            showEventsForDate(dateStr);
                        });

                        day++;
                    }
                    row.appendChild(cell);
                }
                calendarBody.appendChild(row);
                if (day > monthLength) break;
            }
        }

        prevMonthBtn.addEventListener('click', function() {
            currentMonth--;
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            loadCalendarData(currentMonth, currentYear);
        });

        nextMonthBtn.addEventListener('click', function() {
            currentMonth++;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            loadCalendarData(currentMonth, currentYear);
        });

        function showEventsForDate(dateStr) {
            const meetingsSection = document.getElementById('eventsSection');
            if (window.calendarEvents && window.calendarEvents[dateStr]) {
                const data = window.calendarEvents[dateStr];
                let html = '<div class="tw-flex tw-justify-between tw-items-center tw-mb-4">';
                html += '<h6 class="tw-mb-0 tw-font-bold tw-text-gray-900 dark:tw-text-white tw-text-[11px] tw-uppercase tw-tracking-wider">Events for ' + dateStr + '</h6>';
                html += '</div>';
                html += '<div class="meetings-list tw-space-y-3">';
                
                if (data.holidays && data.holidays.length > 0) {
                    data.holidays.forEach(holiday => {
                        html += '<div class="tw-flex tw-items-center tw-p-3 tw-rounded-xl tw-bg-red-50 dark:tw-bg-red-900/10 tw-border tw-border-red-100 dark:tw-border-red-900/30">';
                        html += '<div class="tw-w-8 tw-h-8 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-bg-red-100 tw-text-red-600 tw-mr-3"><i class="bi bi-calendar-x"></i></div>';
                        html += '<div class="tw-font-bold tw-text-red-900 dark:tw-text-red-400 tw-text-sm">' + holiday + '</div></div>';
                    });
                }

                const wo = data.work_orders;
                if (wo && (wo.new > 0 || wo.allocated > 0 || wo.in_process > 0 || wo.completed > 0 || wo.overdue > 0 || wo.for_approval > 0 || wo.rejected > 0)) {
                    html += '<div class="tw-p-3 tw-rounded-xl  dark:tw-bg-blue-900/10 tw-border tw-border-blue-100 dark:tw-border-blue-900/30">';
                    html += '<div class="tw-flex tw-items-center tw-mb-2"><div class="tw-w-6 tw-h-6 tw-rounded tw-bg-blue-100 tw-text-blue-600 tw-flex tw-items-center tw-justify-center tw-mr-2"><i class="bi bi-clipboard-check"></i></div><span class="tw-font-bold tw-text-blue-900 dark:tw-text-blue-400 tw-text-sm">Work Orders</span></div>';
                    html += '<div class="tw-grid tw-grid-cols-2 tw-gap-x-2 tw-gap-y-1 tw-text-[10px] tw-text-blue-800 dark:tw-text-blue-300">';
                    if (wo.new > 0) html += '<div>New: <span class="tw-font-bold">' + wo.new + '</span></div>';
                    if (wo.allocated > 0) html += '<div>Allocated: <span class="tw-font-bold">' + wo.allocated + '</span></div>';
                    if (wo.in_process > 0) html += '<div>In Process: <span class="tw-font-bold">' + wo.in_process + '</span></div>';
                    if (wo.for_approval > 0) html += '<div>For Approval: <span class="tw-font-bold">' + wo.for_approval + '</span></div>';
                    if (wo.overdue > 0) html += '<div class="tw-text-red-600">Overdue: <span class="tw-font-bold">' + wo.overdue + '</span></div>';
                    if (wo.completed > 0) html += '<div class="tw-text-emerald-600">Completed: <span class="tw-font-bold">' + wo.completed + '</span></div>';
                    if (wo.rejected > 0) html += '<div class="tw-text-red-600">Rejected: <span class="tw-font-bold">' + wo.rejected + '</span></div>';
                    html += '</div></div>';
                }

                const po = data.purchase_orders;
                if (po && (po.new > 0 || po.allocated > 0 || po.in_process > 0 || po.completed > 0 || po.overdue > 0 || po.for_approval > 0 || po.rejected > 0)) {
                    html += '<div class="tw-p-3 tw-rounded-xl  dark:tw-bg-indigo-900/10 tw-border tw-border-indigo-100 dark:tw-border-indigo-900/30">';
                    html += '<div class="tw-flex tw-items-center tw-mb-2"><div class="tw-w-6 tw-h-6 tw-rounded tw-bg-indigo-100 tw-text-indigo-600 tw-flex tw-items-center tw-justify-center tw-mr-2"><i class="bi bi-bag-check"></i></div><span class="tw-font-bold tw-text-indigo-900 dark:tw-text-indigo-400 tw-text-sm">Purchase Orders</span></div>';
                    html += '<div class="tw-grid tw-grid-cols-2 tw-gap-x-2 tw-gap-y-1 tw-text-[10px] tw-text-indigo-800 dark:tw-text-indigo-300">';
                    if (po.new > 0) html += '<div>New: <span class="tw-font-bold">' + po.new + '</span></div>';
                    if (po.allocated > 0) html += '<div>Allocated: <span class="tw-font-bold">' + po.allocated + '</span></div>';
                    if (po.in_process > 0) html += '<div>In Process: <span class="tw-font-bold">' + po.in_process + '</span></div>';
                    if (po.for_approval > 0) html += '<div>For Approval: <span class="tw-font-bold">' + po.for_approval + '</span></div>';
                    if (po.overdue > 0) html += '<div class="tw-text-red-600">Overdue: <span class="tw-font-bold">' + po.overdue + '</span></div>';
                    if (po.completed > 0) html += '<div class="tw-text-emerald-600">Completed: <span class="tw-font-bold">' + po.completed + '</span></div>';
                    if (po.rejected > 0) html += '<div class="tw-text-red-600">Rejected: <span class="tw-font-bold">' + po.rejected + '</span></div>';
                    html += '</div></div>';
                }

                if (data.stock_orders > 0) {
                    html += '<div class="tw-flex tw-items-center tw-justify-between tw-p-3 tw-rounded-xl tw-bg-amber-50/50 dark:tw-bg-amber-900/10 tw-border tw-border-amber-100 dark:tw-border-amber-900/30">';
                    html += '<div class="tw-flex tw-items-center"><div class="tw-w-6 tw-h-6 tw-rounded tw-bg-amber-100 tw-text-amber-600 tw-flex tw-items-center tw-justify-center tw-mr-2"><i class="bi bi-box-seam"></i></div><span class="tw-font-bold tw-text-amber-900 dark:tw-text-amber-400 tw-text-sm">Stock Orders</span></div>';
                    html += '<div class="tw-text-amber-800 dark:tw-text-amber-300 tw-font-bold">' + data.stock_orders + '</div></div>';
                }

                html += '</div>';
                meetingsSection.innerHTML = html;
            } else {
                meetingsSection.innerHTML = '<div class="tw-flex tw-justify-between tw-items-center tw-mb-4"><h6 class="tw-mb-0 tw-font-bold tw-text-gray-900 dark:tw-text-white tw-text-[11px] tw-uppercase tw-tracking-wider">Events for ' + dateStr + '</h6></div><div class="tw-text-center tw-text-gray-400 tw-py-8"><div class="tw-w-12 tw-h-12 tw-rounded-full tw-bg-gray-100 dark:tw-bg-slate-800 tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-3"><i class="bi bi-calendar2-x tw-text-xl tw-opacity-50"></i></div><p class="tw-mb-0 tw-text-xs tw-font-medium">No events scheduled</p></div>';
            }
        }

        loadCalendarData(currentMonth, currentYear);
    });
</script>

<!-- Real-time Updates Script -->




<script>
    // Function to update dashboard statistics in real-time
    async function updateDashboardStats() {
        try {
            // Make an AJAX request to get updated stats
            const response = await fetch('{{ route("super-admin.dashboard.stats") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                const data = await response.json();

                // Update all the stat counters with animation
                if(document.getElementById('stat-total-bp')) document.getElementById('stat-total-bp').textContent = data.totalBusinessPartners.toLocaleString();
                if(document.getElementById('stat-total-buyers')) document.getElementById('stat-total-buyers').textContent = data.totalBuyers.toLocaleString();
                if(document.getElementById('stat-total-craftsmen')) document.getElementById('stat-total-craftsmen').textContent = data.totalCraftsmen.toLocaleString();
                if(document.getElementById('stat-pending-kyc')) document.getElementById('stat-pending-kyc').textContent = data.pendingKycCount.toLocaleString();
                if(document.getElementById('stat-total-admins')) document.getElementById('stat-total-admins').textContent = data.totalAdmins.toLocaleString();
                if(document.getElementById('stat-total-key-users')) document.getElementById('stat-total-key-users').textContent = data.totalKeyUsers.toLocaleString();
                if(document.getElementById('stat-total-users')) document.getElementById('stat-total-users').textContent = data.totalUsers.toLocaleString();
                if(document.getElementById('stat-total-work-orders')) document.getElementById('stat-total-work-orders').textContent = data.totalWorkOrders.toLocaleString();
                if(document.getElementById('stat-total-products')) document.getElementById('stat-total-products').textContent = data.totalProducts.toLocaleString();
                if(document.getElementById('stat-total-designs')) document.getElementById('stat-total-designs').textContent = data.totalDesigns.toLocaleString();
                if(document.getElementById('stat-total-catalogues')) document.getElementById('stat-total-catalogues').textContent = data.totalCatalogues.toLocaleString();
                if(document.getElementById('stat-total-purchase-orders')) document.getElementById('stat-total-purchase-orders').textContent = data.totalPurchaseOrders.toLocaleString();
                if(document.getElementById('stat-total-stock-orders')) document.getElementById('stat-total-stock-orders').textContent = data.totalStockOrders.toLocaleString();
                if(document.getElementById('stat-total-repairs')) document.getElementById('stat-total-repairs').textContent = data.totalRepairs.toLocaleString();
            }
        } catch (error) {
            console.error('Error updating dashboard stats:', error);
        }
    }

    // Update stats every 30 seconds
    setInterval(updateDashboardStats, 30000);

    // Also update on page load
    setTimeout(updateDashboardStats, 5000); // Update after 5 seconds to let initial data load
</script>




<!-- Top Picks Clients Modal -->
<div class="modal fade" id="topPicksClientsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl roboto-font" style="max-width: 98%;">
        <div class="modal-content dark:tw-bg-slate-900 tw-border-0 tw-rounded-2xl tw-shadow-2xl">
            <div class="modal-header tw-border-0 tw-pb-0">
                <h5 class="modal-title tw-font-extrabold tw-text-emerald-700 dark:tw-text-emerald-400">{{ __('messages.top_picks_clients') }} (Top 15)</h5>
                <button type="button" class="btn-close dark:tw-invert" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body tw-p-9">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle dark:tw-text-gray-300">
                        <thead class="tw-bg-gray-100 dark:tw-bg-slate-800">
                            <tr>
                                <th rowspan="2" class="tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">Client Name</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">BP Code</th>
                                <th colspan="6" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-bg-emerald-50 dark:tw-bg-emerald-900/20 tw-text-emerald-700 dark:tw-text-emerald-300">WORK ORDERS (WA)</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle tw-bg-gray-50">Total Orders</th>
                            </tr>
                            <tr class="tw-bg-gray-50 dark:tw-bg-slate-800/50">
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">NEW (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">PROCESS (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">FOR APPROVAL (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-orange-500">OVERDUE (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-red-500">REJECTED (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-emerald-600">COMPLETED (C/W)</th>
                            </tr>
                        </thead>
                        <tbody>
                                                        @forelse($topPicksClientsFull as $code => $stat)
                            <tr style="background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;" class="hover: tw-transition-colors">
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{{ $stat['name'] }}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{{ $code }}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{!! $stat['new']['weight'] > 0 ? $stat['new']['count'] . ' - ' . number_format($stat['new']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-blue-600 dark:tw-text-blue-400" style="color: #2563eb !important;">{!! $stat['in_process']['weight'] > 0 ? $stat['in_process']['count'] . ' - ' . number_format($stat['in_process']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-purple-600 dark:tw-text-purple-400" style="color: #9333ea !important;">{!! $stat['for_approval']['weight'] > 0 ? $stat['for_approval']['count'] . ' - ' . number_format($stat['for_approval']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-orange-600 dark:tw-text-orange-400 tw-font-bold" style="color: #ea580c !important;">{!! $stat['overdue']['weight'] > 0 ? $stat['overdue']['count'] . ' - ' . number_format($stat['overdue']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-red-600 dark:tw-text-red-400 tw-font-bold" style="color: #dc2626 !important;">{!! $stat['rejected']['weight'] > 0 ? $stat['rejected']['count'] . ' - ' . number_format($stat['rejected']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-emerald-600 dark:tw-text-emerald-400" style="color: #059669 !important;">{!! $stat['completed']['weight'] > 0 ? $stat['completed']['count'] . ' - ' . number_format($stat['completed']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-font-black tw-text-emerald-700 dark:tw-text-emerald-400 tw-py-3 tw-text-[15px] " style="color: #047857 !important;">{{ $stat['orders'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="tw-text-center tw-py-4 tw-text-gray-500 dark:tw-text-gray-400">{{ __('messages.no_data_found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Least Picks Clients Modal -->
<div class="modal fade" id="leastPicksClientsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl roboto-font" style="max-width: 98%;">
        <div class="modal-content dark:tw-bg-slate-900 tw-border-0 tw-rounded-2xl tw-shadow-2xl">
            <div class="modal-header tw-border-0 tw-pb-0">
                <h5 class="modal-title tw-font-extrabold tw-text-gray-600 dark:tw-text-gray-400">{{ __('messages.least_pick_clients') }} (Top 15)</h5>
                <button type="button" class="btn-close dark:tw-invert" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body tw-p-9">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle dark:tw-text-gray-300">
                        <thead class="tw-bg-gray-100 dark:tw-bg-slate-800">
                            <tr>
                                <th rowspan="2" class="tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">Client Name</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">BP Code</th>
                                <th colspan="6" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-bg-gray-200 dark:tw-bg-gray-700 tw-text-gray-700 dark:tw-text-gray-300">WORK ORDERS (WA)</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle tw-bg-gray-50">Total Orders</th>
                            </tr>
                            <tr class="tw-bg-gray-50 dark:tw-bg-slate-800/50">
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">NEW (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">PROCESS (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">FOR APPROVAL (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-orange-500">OVERDUE (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-red-500">REJECTED (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-emerald-600">COMPLETED (C/W)</th>
                            </tr>
                        </thead>
                        <tbody>
                                                        @forelse($leastPicksClientsFull as $code => $stat)
                            <tr style="background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;" class="hover:tw-bg-gray-100 tw-transition-colors">
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{{ $stat['name'] }}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{{ $code }}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{!! $stat['new']['weight'] > 0 ? $stat['new']['count'] . ' - ' . number_format($stat['new']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-blue-600 dark:tw-text-blue-400" style="color: #2563eb !important;">{!! $stat['in_process']['weight'] > 0 ? $stat['in_process']['count'] . ' - ' . number_format($stat['in_process']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-purple-600 dark:tw-text-purple-400" style="color: #9333ea !important;">{!! $stat['for_approval']['weight'] > 0 ? $stat['for_approval']['count'] . ' - ' . number_format($stat['for_approval']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-orange-600 dark:tw-text-orange-400 tw-font-bold" style="color: #ea580c !important;">{!! $stat['overdue']['weight'] > 0 ? $stat['overdue']['count'] . ' - ' . number_format($stat['overdue']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-red-600 dark:tw-text-red-400 tw-font-bold" style="color: #dc2626 !important;">{!! $stat['rejected']['weight'] > 0 ? $stat['rejected']['count'] . ' - ' . number_format($stat['rejected']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-emerald-600 dark:tw-text-emerald-400" style="color: #059669 !important;">{!! $stat['completed']['weight'] > 0 ? $stat['completed']['count'] . ' - ' . number_format($stat['completed']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-font-black tw-text-gray-700 dark:tw-text-gray-400 tw-py-3 tw-text-[15px] " style="color: #374151 !important;">{{ $stat['orders'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="tw-text-center tw-py-4 tw-text-gray-500 dark:tw-text-gray-400">{{ __('messages.no_data_found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function printTopPicksCraftsman() {
        const modal = document.getElementById('topPicksCraftsmanModal');
        const tableHtml = modal.querySelector('.table-responsive').innerHTML;
        const printWindow = window.open('', '', 'width=1200,height=800');
        printWindow.document.write('<html><head><title>Print - Top Picks Craftsman</title>');
        printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
        printWindow.document.write('<style>body { padding: 20px; font-family: "Roboto", sans-serif; } table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid #ddd !important; padding: 8px; text-align: center; } .tpm-col-wa { display: ' + (document.getElementById('tpmTypePA').checked ? 'none' : 'table-cell') + '; } .tpm-col-pa { display: ' + (document.getElementById('tpmTypeWA').checked ? 'none' : 'table-cell') + '; }</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<h2 class="text-center mb-4">Top Picks Craftsman</h2>');
        printWindow.document.write(tableHtml);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        setTimeout(function() {
            printWindow.print();
            printWindow.close();
        }, 500);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const typeRadios = document.querySelectorAll('input[name="tpmTypeFilter"]');
        const statusRadios = document.querySelectorAll('input[name="tpmStatusFilter"]');
        const rows = document.querySelectorAll('.tpm-row');

        
    window.showOrdersList = function(el) {
        const title = el.getAttribute('data-title');
        const orders = JSON.parse(el.getAttribute('data-orders') || '[]');
        
        document.getElementById('ordersListModalTitle').innerText = title + ' Orders';
        
        const body = document.getElementById('ordersListModalBody');
        body.innerHTML = '';
        
        if (orders.length === 0) {
            body.innerHTML = '<div class="tw-text-center tw-text-gray-500 tw-py-4 tw-text-sm">No orders found.</div>';
        } else {
            orders.forEach(order => {
                const div = document.createElement('div');
                div.className = 'tw-bg-gray-100 dark:tw-bg-slate-800 tw-px-3 tw-py-2 tw-rounded-lg tw-text-sm tw-font-semibold tw-text-center tw-text-gray-700 dark:tw-text-gray-300';
                div.innerText = order;
                body.appendChild(div);
            });
        }
        
        const modal = new bootstrap.Modal(document.getElementById('ordersListModal'));
        modal.show();
    };

        function filterTable() {
            const selectedType = document.querySelector('input[name="tpmTypeFilter"]:checked').value;
            const selectedStatus = document.querySelector('input[name="tpmStatusFilter"]:checked').value;

            // Toggle WA / PA Columns
            document.querySelectorAll('.tpm-col-wa').forEach(el => {
                el.style.display = (selectedType === 'pa') ? 'none' : '';
            });
            document.querySelectorAll('.tpm-col-pa').forEach(el => {
                el.style.display = (selectedType === 'wa') ? 'none' : '';
            });

            // Filter Rows
            rows.forEach(row => {
                let showRow = false;
                
                if (selectedStatus === 'all') {
                    showRow = true;
                } else if (selectedStatus === 'active') {
                    showRow = row.dataset.inProcess === 'true' || row.dataset.forApproval === 'true' || row.dataset.overdue === 'true';
                } else if (selectedStatus === 'completed') {
                    showRow = row.dataset.completed === 'true';
                }

                row.style.display = showRow ? '' : 'none';
            });
        }

        typeRadios.forEach(radio => radio.addEventListener('change', filterTable));
        statusRadios.forEach(radio => radio.addEventListener('change', filterTable));
    });
</script>
@endsection