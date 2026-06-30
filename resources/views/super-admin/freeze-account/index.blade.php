@extends('super-admin.layouts.app')

@section('title', __('messages.freeze_management_title'))

@section('content')
<div class="tw-container tw-mx-auto tw-pb-8">
    <!-- Header Section -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-100 tw-p-6 tw-mb-6">
        <div class="tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-items-start md:tw-items-center tw-gap-4">
            <div>
                <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-flex tw-items-center tw-gap-3">
                    <div class="tw-p-2 tw-bg-red-50 tw-rounded-lg">
                        <i class="bi bi-lock-fill tw-text-red-600"></i>
                    </div>
                    {{ __('messages.freeze_management_title') }}
                </h1>
                <p class="tw-text-gray-500 tw-mt-1 tw-text-sm">{{ __('messages.manage_account_access') }}</p>
            </div>
            <div class="tw-bg-red-50 tw-px-4 tw-py-2 tw-rounded-full tw-border tw-border-red-100">
                <span class="tw-text-red-600 tw-font-semibold tw-text-sm">
                    <i class="bi bi-shield-lock tw-mr-2"></i>
                    {{ $frozenBuyers->count() + $frozenCraftsmen->count() + $frozenAdmins->count() + $frozenKeyUsers->count() + $frozenUsers->count() }} {{ __('messages.accounts_frozen') }}
                </span>
            </div>
        </div>

        <hr class="tw-my-6 tw-border-gray-100">

        <!-- Search Bar Section -->
        <form action="{{ route('super-admin.freeze-account.index') }}" method="GET" class="tw-flex tw-flex-col md:tw-flex-row tw-gap-4">
            <div class="tw-flex-1 tw-relative">
                <i class="bi bi-search tw-absolute tw-left-4 tw-top-1/2 -tw-translate-y-1/2 tw-text-gray-400"></i>
                <input type="text" 
                       name="search" 
                       class="tw-w-full tw-pl-11 tw-pr-4 tw-py-2.5 tw-bg-gray-50 tw-border tw-border-gray-200 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-red-500 focus:tw-border-red-500 tw-transition-all tw-text-sm" 
                       placeholder="Search by BP Code, User Code, or Name..." 
                       value="{{ request('search') }}">
            </div>
            <div class="tw-flex tw-gap-2">
                <button type="submit" class="tw-px-6 tw-py-2.5 tw-bg-red-600 tw-text-white tw-font-medium tw-rounded-lg hover:tw-bg-red-700 tw-transition-colors tw-text-sm tw-flex tw-items-center tw-gap-2">
                    <i class="bi bi-funnel"></i> {{ __('messages.search') }}
                </button>
                <a href="{{ route('super-admin.freeze-account.index') }}" class="tw-px-6 tw-py-2.5 tw-bg-white tw-text-gray-600 tw-font-medium tw-border tw-border-gray-200 tw-rounded-lg hover:tw-bg-gray-50 tw-transition-colors tw-text-sm tw-flex tw-items-center tw-gap-2">
                    <i class="bi bi-arrow-clockwise"></i> {{ __('messages.reset') }}
                </a>
            </div>
        </form>
    </div>

    <!-- Tabs Navigation -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-100 tw-overflow-hidden">
        <div class="tw-border-b tw-border-gray-100 tw-bg-gray-50/50 tw-px-2">
            <ul class="tw-flex tw-flex-wrap tw-list-none tw-mb-0" id="freezeTabs" role="tablist">
                @php
                    $tabs = [
                        ['id' => 'buyers', 'label' => __('messages.buyers'), 'icon' => 'bi-person', 'count' => $allBuyers->count()],
                        ['id' => 'craftsmen', 'label' => __('messages.craftsmen'), 'icon' => 'bi-person-workspace', 'count' => $allCraftsmen->count()],
                        ['id' => 'admins', 'label' => __('messages.admins'), 'icon' => 'bi-person-badge', 'count' => $allAdmins->count()],
                        ['id' => 'key-users', 'label' => __('messages.key_users_tab'), 'icon' => 'bi-key', 'count' => $allKeyUsers->count()],
                        ['id' => 'users', 'label' => __('messages.users'), 'icon' => 'bi-person-circle', 'count' => $allUsers->count()],
                    ];
                @endphp
                @foreach($tabs as $index => $tab)
                    <li class="tw-mr-1" role="presentation">
                        <button class="nav-link {{ $index === 0 ? 'active' : '' }} tw-flex tw-items-center tw-gap-2 tw-px-6 tw-py-4 tw-text-sm tw-font-medium tw-transition-all tw-border-b-2 tw-border-transparent hover:tw-text-red-600" 
                                id="{{ $tab['id'] }}-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#{{ $tab['id'] }}" 
                                type="button" 
                                role="tab">
                            <i class="bi {{ $tab['icon'] }} tw-text-base"></i>
                            {{ $tab['label'] }}
                            <span class="tw-bg-gray-200 tw-text-gray-600 tw-px-2 tw-py-0.5 tw-rounded-full tw-text-[10px]">{{ $tab['count'] }}</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="tab-content" id="freezeTabsContent">
            <!-- Buyers Tab -->
            <div class="tab-pane fade show active" id="buyers" role="tabpanel">
                <div class="tw-overflow-x-auto">
                    <table class="tw-w-full tw-text-left tw-border-collapse">
                        <thead>
                            <tr class="tw-bg-gray-50/50">
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">{{ __('messages.bp_code') }}</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">{{ __('messages.dear') ?? 'Dear' }}</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">{{ __('messages.business_name') }}</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">{{ __('messages.contact') }}</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">{{ __('messages.status') }}</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100 tw-text-right">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="tw-divide-y tw-divide-gray-100">
                            @forelse($allBuyers as $buyer)
                                <tr class="hover:tw-bg-gray-50/50 tw-transition-colors">
                                    <td class="tw-px-6 tw-py-4">
                                        <span class="tw-font-bold tw-text-gray-900 tw-bg-gray-100 tw-px-2 tw-py-1 tw-rounded tw-text-xs">{{ $buyer->bp_code ?? 'N/A' }}</span>
                                    </td>
                                    <td class="tw-px-6 tw-py-4 tw-text-sm tw-text-gray-600">{{ $buyer->dear ?? 'N/A' }}</td>
                                    <td class="tw-px-6 tw-py-4">
                                        <div class="tw-text-sm tw-font-medium tw-text-gray-900">{{ $buyer->business_name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        <div class="tw-text-sm tw-text-gray-900">{{ $buyer->name ?? 'N/A' }}</div>
                                        <div class="tw-text-xs tw-text-gray-500">{{ $buyer->email ?? 'N/A' }}</div>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        @if($buyer->is_frozen)
                                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-red-100 tw-text-red-800">
                                                <i class="bi bi-snow tw-mr-1"></i> {{ __('messages.frozen') }}
                                            </span>
                                        @else
                                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">
                                                <i class="bi bi-check-circle tw-mr-1"></i> {{ __('messages.active') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="tw-px-6 tw-py-4 tw-text-right">
                                        @if($buyer->is_frozen)
                                            <button class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-bg-green-600 tw-text-white tw-text-xs tw-font-medium tw-rounded-md hover:tw-bg-green-700 tw-transition-colors unfreeze-btn" 
                                                    data-model-type="buyer" 
                                                    data-model-id="{{ $buyer->id }}">
                                                <i class="bi bi-unlock"></i> {{ __('messages.unfreeze') }}
                                            </button>
                                        @else
                                            <button class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-bg-amber-500 tw-text-white tw-text-xs tw-font-medium tw-rounded-md hover:tw-bg-amber-600 tw-transition-colors freeze-btn" 
                                                    data-model-type="buyer" 
                                                    data-model-id="{{ $buyer->id }}">
                                                <i class="bi bi-lock"></i> {{ __('messages.freeze') }}
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="tw-px-6 tw-py-8 tw-text-center tw-text-gray-500 tw-text-sm">{{ __('messages.no_buyers_in_system') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Craftsmen Tab -->
            <div class="tab-pane fade" id="craftsmen" role="tabpanel">
                <div class="tw-overflow-x-auto">
                    <table class="tw-w-full tw-text-left tw-border-collapse">
                        <thead>
                            <tr class="tw-bg-gray-50/50">
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">Code</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">Dear</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">Name</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">Contact</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">Status</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100 tw-text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="tw-divide-y tw-divide-gray-100">
                            @forelse($allCraftsmen as $craftsman)
                                <tr class="hover:tw-bg-gray-50/50 tw-transition-colors">
                                    <td class="tw-px-6 tw-py-4">
                                        <span class="tw-font-bold tw-text-gray-900 tw-bg-gray-100 tw-px-2 tw-py-1 tw-rounded tw-text-xs">{{ $craftsman->craftman_code ?? 'N/A' }}</span>
                                    </td>
                                    <td class="tw-px-6 tw-py-4 tw-text-sm tw-text-gray-600">{{ $craftsman->dear ?? 'N/A' }}</td>
                                    <td class="tw-px-6 tw-py-4">
                                        <div class="tw-text-sm tw-font-medium tw-text-gray-900">{{ $craftsman->name ?? 'N/A' }}</div>
                                        <div class="tw-text-xs tw-text-gray-500">{{ $craftsman->specialization ?? 'N/A' }}</div>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        <div class="tw-text-sm tw-text-gray-900">{{ $craftsman->email ?? 'N/A' }}</div>
                                        <div class="tw-text-xs tw-text-gray-500">{{ $craftsman->mobile ?? 'N/A' }}</div>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        @if($craftsman->is_frozen)
                                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-red-100 tw-text-red-800">
                                                <i class="bi bi-snow tw-mr-1"></i> {{ __('messages.frozen') }}
                                            </span>
                                        @else
                                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">
                                                <i class="bi bi-check-circle tw-mr-1"></i> {{ __('messages.active') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="tw-px-6 tw-py-4 tw-text-right">
                                        @if($craftsman->is_frozen)
                                            <button class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-bg-green-600 tw-text-white tw-text-xs tw-font-medium tw-rounded-md hover:tw-bg-green-700 tw-transition-colors unfreeze-btn" 
                                                    data-model-type="craftsman" 
                                                    data-model-id="{{ $craftsman->id }}">
                                                <i class="bi bi-unlock"></i> {{ __('messages.unfreeze') }}
                                            </button>
                                        @else
                                            <button class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-bg-amber-500 tw-text-white tw-text-xs tw-font-medium tw-rounded-md hover:tw-bg-amber-600 tw-transition-colors freeze-btn" 
                                                    data-model-type="craftsman" 
                                                    data-model-id="{{ $craftsman->id }}">
                                                <i class="bi bi-lock"></i> {{ __('messages.freeze') }}
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="tw-px-6 tw-py-8 tw-text-center tw-text-gray-500 tw-text-sm">{{ __('messages.no_craftsmen_in_system') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Admins Tab -->
            <div class="tab-pane fade" id="admins" role="tabpanel">
                <div class="tw-overflow-x-auto">
                    <table class="tw-w-full tw-text-left tw-border-collapse">
                        <thead>
                            <tr class="tw-bg-gray-50/50">
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">{{ __('messages.user_code') }}</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">{{ __('messages.name') }}</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">{{ __('messages.role') }}</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">{{ __('messages.status') }}</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100 tw-text-right">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="tw-divide-y tw-divide-gray-100">
                            @forelse($allAdmins as $admin)
                                <tr class="hover:tw-bg-gray-50/50 tw-transition-colors">
                                    <td class="tw-px-6 tw-py-4">
                                        <span class="tw-font-bold tw-text-gray-900 tw-bg-gray-100 tw-px-2 tw-py-1 tw-rounded tw-text-xs">{{ $admin->user_code ?? 'N/A' }}</span>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        <div class="tw-text-sm tw-font-medium tw-text-gray-900">{{ $admin->full_name ?? 'N/A' }}</div>
                                        <div class="tw-text-xs tw-text-gray-500">{{ $admin->email_id ?? 'N/A' }}</div>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-blue-50 tw-text-blue-700">
                                            {{ $admin->role ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        @if($admin->is_frozen)
                                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-red-100 tw-text-red-800">
                                                <i class="bi bi-snow tw-mr-1"></i> Frozen
                                            </span>
                                        @else
                                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">
                                                <i class="bi bi-check-circle tw-mr-1"></i> Active
                                            </span>
                                        @endif
                                    </td>
                                    <td class="tw-px-6 tw-py-4 tw-text-right">
                                        @if($admin->is_frozen)
                                            <button class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-bg-green-600 tw-text-white tw-text-xs tw-font-medium tw-rounded-md hover:tw-bg-green-700 tw-transition-colors unfreeze-btn" 
                                                    data-model-type="admin" 
                                                    data-model-id="{{ $admin->id }}">
                                                <i class="bi bi-unlock"></i> Unfreeze
                                            </button>
                                        @else
                                            <button class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-bg-amber-500 tw-text-white tw-text-xs tw-font-medium tw-rounded-md hover:tw-bg-amber-600 tw-transition-colors freeze-btn" 
                                                    data-model-type="admin" 
                                                    data-model-id="{{ $admin->id }}">
                                                <i class="bi bi-lock"></i> Freeze
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="tw-px-6 tw-py-8 tw-text-center tw-text-gray-500 tw-text-sm">{{ __('messages.no_admins_in_system') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Key Users Tab -->
            <div class="tab-pane fade" id="key-users" role="tabpanel">
                <div class="tw-overflow-x-auto">
                    <table class="tw-w-full tw-text-left tw-border-collapse">
                        <thead>
                            <tr class="tw-bg-gray-50/50">
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">User Code</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">Name</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">BP Code</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">Status</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100 tw-text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="tw-divide-y tw-divide-gray-100">
                            @forelse($allKeyUsers as $keyUser)
                                <tr class="hover:tw-bg-gray-50/50 tw-transition-colors">
                                    <td class="tw-px-6 tw-py-4">
                                        <span class="tw-font-bold tw-text-gray-900 tw-bg-gray-100 tw-px-2 tw-py-1 tw-rounded tw-text-xs">{{ $keyUser->user_code ?? 'N/A' }}</span>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        <div class="tw-text-sm tw-font-medium tw-text-gray-900">{{ $keyUser->full_name ?? 'N/A' }}</div>
                                        <div class="tw-text-xs tw-text-gray-500">{{ $keyUser->email_id ?? 'N/A' }}</div>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        <span class="tw-text-sm tw-text-gray-600">{{ $keyUser->bp_code ?? 'N/A' }}</span>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        @if($keyUser->is_frozen)
                                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-red-100 tw-text-red-800">
                                                <i class="bi bi-snow tw-mr-1"></i> Frozen
                                            </span>
                                        @else
                                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">
                                                <i class="bi bi-check-circle tw-mr-1"></i> Active
                                            </span>
                                        @endif
                                    </td>
                                    <td class="tw-px-6 tw-py-4 tw-text-right">
                                        @if($keyUser->is_frozen)
                                            <button class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-bg-green-600 tw-text-white tw-text-xs tw-font-medium tw-rounded-md hover:tw-bg-green-700 tw-transition-colors unfreeze-btn" 
                                                    data-model-type="key_user" 
                                                    data-model-id="{{ $keyUser->id }}">
                                                <i class="bi bi-unlock"></i> Unfreeze
                                            </button>
                                        @else
                                            <button class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-bg-amber-500 tw-text-white tw-text-xs tw-font-medium tw-rounded-md hover:tw-bg-amber-600 tw-transition-colors freeze-btn" 
                                                    data-model-type="key_user" 
                                                    data-model-id="{{ $keyUser->id }}">
                                                <i class="bi bi-lock"></i> Freeze
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="tw-px-6 tw-py-8 tw-text-center tw-text-gray-500 tw-text-sm">No key users found in the system.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Users Tab -->
            <div class="tab-pane fade" id="users" role="tabpanel">
                <div class="tw-overflow-x-auto">
                    <table class="tw-w-full tw-text-left tw-border-collapse">
                        <thead>
                            <tr class="tw-bg-gray-50/50">
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">User Code</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">Name</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">BP Code</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">Status</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100 tw-text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="tw-divide-y tw-divide-gray-100">
                            @forelse($allUsers as $user)
                                <tr class="hover:tw-bg-gray-50/50 tw-transition-colors">
                                    <td class="tw-px-6 tw-py-4">
                                        <span class="tw-font-bold tw-text-gray-900 tw-bg-gray-100 tw-px-2 tw-py-1 tw-rounded tw-text-xs">{{ $user->user_code ?? 'N/A' }}</span>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        <div class="tw-text-sm tw-font-medium tw-text-gray-900">{{ $user->full_name ?? 'N/A' }}</div>
                                        <div class="tw-text-xs tw-text-gray-500">{{ $user->email_id ?? 'N/A' }}</div>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        <span class="tw-text-sm tw-text-gray-600">{{ $user->bp_code ?? 'N/A' }}</span>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        @if($user->is_frozen)
                                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-red-100 tw-text-red-800">
                                                <i class="bi bi-snow tw-mr-1"></i> Frozen
                                            </span>
                                        @else
                                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">
                                                <i class="bi bi-check-circle tw-mr-1"></i> Active
                                            </span>
                                        @endif
                                    </td>
                                    <td class="tw-px-6 tw-py-4 tw-text-right">
                                        @if($user->is_frozen)
                                            <button class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-bg-green-600 tw-text-white tw-text-xs tw-font-medium tw-rounded-md hover:tw-bg-green-700 tw-transition-colors unfreeze-btn" 
                                                    data-model-type="user" 
                                                    data-model-id="{{ $user->id }}">
                                                <i class="bi bi-unlock"></i> Unfreeze
                                            </button>
                                        @else
                                            <button class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-bg-amber-500 tw-text-white tw-text-xs tw-font-medium tw-rounded-md hover:tw-bg-amber-600 tw-transition-colors freeze-btn" 
                                                    data-model-type="user" 
                                                    data-model-id="{{ $user->id }}">
                                                <i class="bi bi-lock"></i> Freeze
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="tw-px-6 tw-py-8 tw-text-center tw-text-gray-500 tw-text-sm">No users found in the system.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics Grid -->
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 lg:tw-grid-cols-5 tw-gap-4 tw-mt-8">
        @php
            $stats = [
                ['label' => 'Total Buyers', 'val' => $allBuyers->count(), 'frozen' => $frozenBuyers->count(), 'icon' => 'bi-person', 'color' => 'blue'],
                ['label' => 'Total Craftsmen', 'val' => $allCraftsmen->count(), 'frozen' => $frozenCraftsmen->count(), 'icon' => 'bi-person-workspace', 'color' => 'indigo'],
                ['label' => 'Total Admins', 'val' => $allAdmins->count(), 'frozen' => $frozenAdmins->count(), 'icon' => 'bi-person-badge', 'color' => 'purple'],
                ['label' => 'Total Key Users', 'val' => $allKeyUsers->count(), 'frozen' => $frozenKeyUsers->count(), 'icon' => 'bi-key', 'color' => 'teal'],
                ['label' => 'Total Users', 'val' => $allUsers->count(), 'frozen' => $frozenUsers->count(), 'icon' => 'bi-person-circle', 'color' => 'cyan'],
            ];
        @endphp

        @foreach($stats as $stat)
            <div class="tw-bg-white tw-rounded-xl tw-p-5 tw-shadow-sm tw-border tw-border-gray-100">
                <div class="tw-flex tw-items-center tw-gap-4">
                    <div class="tw-p-3 tw-bg-{{ $stat['color'] }}-50 tw-text-{{ $stat['color'] }}-600 tw-rounded-xl">
                        <i class="bi {{ $stat['icon'] }} tw-text-xl"></i>
                    </div>
                    <div>
                        <p class="tw-text-xs tw-font-bold tw-text-gray-400 tw-uppercase tw-tracking-wider">{{ $stat['label'] }}</p>
                        <div class="tw-flex tw-items-baseline tw-gap-2">
                            <span class="tw-text-xl tw-font-bold tw-text-gray-900">{{ $stat['val'] }}</span>
                            <span class="tw-text-xs tw-text-red-600 tw-font-medium">{{ $stat['frozen'] }} Frozen</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Total Frozen Summary -->
    <div class="tw-mt-4 tw-bg-red-600 tw-rounded-xl tw-p-6 tw-text-white tw-flex tw-items-center tw-justify-between tw-shadow-lg tw-shadow-red-200">
        <div class="tw-flex tw-items-center tw-gap-4">
            <div class="tw-p-3 tw-bg-white/20 tw-backdrop-blur-sm tw-rounded-xl">
                <i class="bi bi-exclamation-triangle-fill tw-text-2xl"></i>
            </div>
            <div>
                <p class="tw-text-red-100 tw-text-sm tw-font-medium tw-uppercase tw-tracking-wider">{{ __('messages.platform_health') }}</p>
                <h3 class="tw-text-2xl tw-font-bold">{{ __('messages.total_frozen_accounts') }}</h3>
            </div>
        </div>
        <div class="tw-text-4xl tw-font-bold">
            {{ $frozenBuyers->count() + $frozenCraftsmen->count() + $frozenAdmins->count() + $frozenKeyUsers->count() + $frozenUsers->count() }}
        </div>
    </div>
    
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle freeze/unfreeze button clicks via delegation for better performance and dynamic content support
    document.addEventListener('click', function(e) {
        const target = e.target.closest('.freeze-btn, .unfreeze-btn');
        if (!target) return;

        const modelType = target.getAttribute('data-model-type');
        const modelId = target.getAttribute('data-model-id');
        const isFreeze = target.classList.contains('freeze-btn');
        
        if (isFreeze) {
            freezeAccount(modelType, modelId, target);
        } else {
            unfreezeAccount(modelType, modelId, target);
        }
    });

    function freezeAccount(modelType, modelId, buttonElement) {
        if (!confirm('Are you sure you want to freeze this account? The user will not be able to login and their products/designs/catalogues will not be visible.')) {
            return;
        }

        const originalHTML = buttonElement.innerHTML;
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<i class="bi bi-hourglass-split tw-animate-spin"></i> Processing...';

        fetch('{{ route("super-admin.freeze-account.toggle-freeze") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                model_type: modelType,
                model_id: modelId,
                action: 'freeze'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update button to unfreeze
                buttonElement.className = 'tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-bg-green-600 tw-text-white tw-text-xs tw-font-medium tw-rounded-md hover:tw-bg-green-700 tw-transition-colors unfreeze-btn';
                buttonElement.innerHTML = '<i class="bi bi-unlock"></i> Unfreeze';
                
                // Update status badge in the row
                const row = buttonElement.closest('tr');
                const statusCell = row.querySelector('td:nth-last-child(2)');
                statusCell.innerHTML = '<span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-red-100 tw-text-red-800"><i class="bi bi-snow tw-mr-1"></i> Frozen</span>';
                
                // Optional: Toast notification instead of alert could be better, but keeping alert for consistency unless layout has a toast system
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
                buttonElement.innerHTML = originalHTML;
            }
            buttonElement.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while freezing the account.');
            buttonElement.innerHTML = originalHTML;
            buttonElement.disabled = false;
        });
    }

    function unfreezeAccount(modelType, modelId, buttonElement) {
        if (!confirm('Are you sure you want to unfreeze this account? The user will be able to login and their products/designs/catalogues will become visible again.')) {
            return;
        }

        const originalHTML = buttonElement.innerHTML;
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<i class="bi bi-hourglass-split tw-animate-spin"></i> Processing...';

        fetch('{{ route("super-admin.freeze-account.toggle-freeze") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                model_type: modelType,
                model_id: modelId,
                action: 'unfreeze'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update button to freeze
                buttonElement.className = 'tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-bg-amber-500 tw-text-white tw-text-xs tw-font-medium tw-rounded-md hover:tw-bg-amber-600 tw-transition-colors freeze-btn';
                buttonElement.innerHTML = '<i class="bi bi-lock"></i> Freeze';
                
                // Update status badge in the row
                const row = buttonElement.closest('tr');
                const statusCell = row.querySelector('td:nth-last-child(2)');
                statusCell.innerHTML = '<span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800"><i class="bi bi-check-circle tw-mr-1"></i> Active</span>';
                
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
                buttonElement.innerHTML = originalHTML;
            }
            buttonElement.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while unfreezing the account.');
            buttonElement.innerHTML = originalHTML;
            buttonElement.disabled = false;
        });
    }
});
</script>
@endsection