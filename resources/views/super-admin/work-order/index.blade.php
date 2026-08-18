@extends('super-admin.layouts.app')

@section('title', __('messages.work_order_management'))

@section('content')
<div class="tw-container tw-mx-auto tw-px-4 tw-py-6">
    <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-4 tw-mb-8">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">{{ __('messages.work_order_management') }}</h1>
            <nav class="tw-flex tw-text-sm tw-text-gray-500 tw-mt-1" aria-label="Breadcrumb">
                <ol class="tw-inline-flex tw-items-center tw-space-x-1 md:tw-space-x-3">
                    <li><a href="{{ route('super-admin.dashboard') }}" class="hover:tw-text-primary tw-transition-colors">Dashboard</a></li>
                    <li class="tw-flex tw-items-center">
                        <i class="bi bi-chevron-right tw-text-[10px] tw-mx-2"></i>
                        <span class="tw-font-medium tw-text-gray-700">Work Orders</span>
                    </li>
                </ol>
            </nav>
        </div>
        <div class="tw-flex tw-flex-wrap tw-gap-2">
            <a href="{{ route('super-admin.work-order.create') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2.5 tw-bg-[#800000] hover:tw-bg-[#600000] tw-text-white tw-text-sm tw-font-semibold tw-rounded-lg tw-shadow-sm tw-transition-all tw-duration-200">
                <i class="bi bi-plus-lg tw-mr-2"></i> {{ __('messages.add_new') }}
            </a>
            <a href="{{ route('super-admin.work-order.bulk-upload') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2.5 tw-bg-amber-600 hover:tw-bg-amber-700 tw-text-white tw-text-sm tw-font-semibold tw-rounded-lg tw-shadow-sm tw-transition-all tw-duration-200">
                <i class="bi bi-file-earmark-spreadsheet tw-mr-2"></i> Add Excel
            </a>
            <button type="button" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2.5 tw-bg-emerald-600 hover:tw-bg-emerald-700 tw-text-white tw-text-sm tw-font-semibold tw-rounded-lg tw-shadow-sm tw-transition-all tw-duration-200" onclick="exportSelectedWorkOrders()">
                <i class="bi bi-file-earmark-spreadsheet tw-mr-2"></i> Export
            </button>
            <button type="button" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2.5 tw-bg-slate-800 hover:tw-bg-slate-900 tw-text-white tw-text-sm tw-font-semibold tw-rounded-lg tw-shadow-sm tw-transition-all tw-duration-200" onclick="submitBulkPrintWorkOrders()">
                <i class="bi bi-printer tw-mr-2"></i> Print
            </button>
        </div>
    </div>

    <!-- Filter Toggle Section -->
    <div class="tw-mb-6">
        <button type="button" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-shadow-sm tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-primary tw-transition-all" onclick="toggleFilters()">
            <i class="bi bi-funnel tw-mr-2"></i> {{ __('messages.toggle_filters') }}
        </button>
    </div>

    <!-- Filters and Search Section (Initially Hidden) -->
    <div id="filterSection" class="tw-bg-gray-50 tw-p-6 tw-rounded-xl tw-border tw-border-gray-200 tw-mb-8 tw-shadow-sm tw-hidden">
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 lg:tw-grid-cols-6 tw-gap-4">
            <!-- Search -->
            <div>
                <form method="GET" class="tw-relative">
                    <input type="hidden" name="tab" value="{{ request('tab', 'new-orders') }}">
                    <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                    <input type="hidden" name="bp_code_filter" value="{{ request('bp_code_filter') }}">
                    <input type="hidden" name="category_filter" value="{{ request('category_filter') }}">
                    <input type="hidden" name="subcategory_filter" value="{{ request('subcategory_filter') }}">
                    <input type="hidden" name="craftsman_filter" value="{{ request('craftsman_filter') }}">

                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search...') }}"
                        class="tw-w-full tw-text-sm tw-border-gray-300 tw-rounded-lg focus:tw-ring-primary focus:tw-border-primary tw-pl-3 tw-pr-10 tw-py-2">
                    <button type="submit" class="tw-absolute tw-right-2 tw-top-1/2 -tw-translate-y-1/2 tw-text-gray-400 hover:tw-text-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>

            <!-- BP Code Filter -->
            <div>
                <form method="GET">
                    <input type="hidden" name="tab" value="{{ request('tab', 'new-orders') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                    <input type="hidden" name="category_filter" value="{{ request('category_filter') }}">
                    <input type="hidden" name="subcategory_filter" value="{{ request('subcategory_filter') }}">
                    <input type="hidden" name="craftsman_filter" value="{{ request('craftsman_filter') }}">

                    <div class="tw-relative tw-w-full" id="bp_code_filter_container">
                        <div class="tw-w-full tw-min-h-[38px] tw-px-3 tw-py-2 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-flex tw-justify-between tw-items-center tw-cursor-pointer" id="bp_code_filter_display">{{ __('messages.all_bp_codes') }}</div>
                        <div class="tw-absolute tw-top-full tw-left-0 tw-w-full tw-bg-white tw-border tw-border-gray-300 tw-rounded-b-lg tw-shadow-lg tw-z-50 tw-hidden tw-p-2" id="bp_code_filter_menu">
                            <input type="text" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-200 tw-rounded-lg tw-mb-2 focus:tw-outline-none tw-text-sm" id="bp_code_filter_search" placeholder="Search for an item...">
                            <ul class="tw-max-h-60 tw-overflow-y-auto tw-list-none tw-p-0 tw-m-0" id="bp_code_filter_list">
                                <li class="tw-px-3 tw-py-2 hover:tw-bg-gray-50 tw-cursor-pointer tw-text-sm tw-rounded" data-value="">{{ __('messages.all_bp_codes') }}</li>
                                @foreach($bpCodes as $bp)
                                <li class="tw-px-3 tw-py-2 hover:tw-bg-gray-50 tw-cursor-pointer tw-text-sm tw-rounded" data-value="{{ $bp->bp_code }}" {{ request('bp_code_filter') == $bp->bp_code ? 'selected' : '' }}>
                                    {{ $bp->bp_code }}{{ $bp->business_name ? ' - ' . $bp->business_name : '' }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <select name="bp_code_filter" id="bp_code_filter_select" style="display: none;">
                            <option value="">{{ __('messages.all_bp_codes') }}</option>
                            @foreach($bpCodes as $bp)
                            <option value="{{ $bp->bp_code }}" {{ request('bp_code_filter') == $bp->bp_code ? 'selected' : '' }}>
                                {{ $bp->bp_code }}{{ $bp->business_name ? ' - ' . $bp->business_name : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            
            <!-- Category Filter -->
            <div class="">
                
                <form method="GET">
                    <input type="hidden" name="tab" value="{{ request('tab', 'new-orders') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                    <input type="hidden" name="bp_code_filter" value="{{ request('bp_code_filter') }}">
                    <input type="hidden" name="subcategory_filter" value="{{ request('subcategory_filter') }}">
                    <input type="hidden" name="craftsman_filter" value="{{ request('craftsman_filter') }}">
                    <input type="hidden" name="design_code_filter" value="{{ request('design_code_filter') }}">
                    <input type="hidden" name="product_code_filter" value="{{ request('product_code_filter') }}">
                    <input type="hidden" name="return_filter" value="{{ request('return_filter') }}">
                    <div class="tw-relative tw-w-full" id="category_filter_container">
                        <div class="tw-w-full tw-min-h-[38px] tw-px-3 tw-py-2 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-flex tw-justify-between tw-items-center tw-cursor-pointer" id="category_filter_display">All Categorys</div>
                        <div class="tw-absolute tw-top-full tw-left-0 tw-w-full tw-bg-white tw-border tw-border-gray-300 tw-rounded-b-lg tw-shadow-lg tw-z-50 tw-hidden tw-p-2" id="category_filter_menu">
                            <input type="text" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-200 tw-rounded-lg tw-mb-2 focus:tw-outline-none tw-text-sm" id="category_filter_search" placeholder="Search for an item...">
                            <ul class="tw-max-h-60 tw-overflow-y-auto tw-list-none tw-p-0 tw-m-0" id="category_filter_list">
                                <li class="tw-px-3 tw-py-2 hover:tw-bg-gray-50 tw-cursor-pointer tw-text-sm tw-rounded" data-value="">All Categorys</li>
                                @foreach($categories as $item)
                                <li class="tw-px-3 tw-py-2 hover:tw-bg-gray-50 tw-cursor-pointer tw-text-sm tw-rounded" data-value="{{ $item->id }}" {{ request('category_filter') == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <select name="category_filter" id="category_filter_select" style="display: none;">
                            <option value="">All Categorys</option>
                            @foreach($categories as $item)
                            <option value="{{ $item->id }}" {{ request('category_filter') == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <!-- Subcategory Filter -->
            <div class="">
                
                <form method="GET">
                    <input type="hidden" name="tab" value="{{ request('tab', 'new-orders') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                    <input type="hidden" name="bp_code_filter" value="{{ request('bp_code_filter') }}">
                    <input type="hidden" name="category_filter" value="{{ request('category_filter') }}">
                    <input type="hidden" name="craftsman_filter" value="{{ request('craftsman_filter') }}">
                    <input type="hidden" name="design_code_filter" value="{{ request('design_code_filter') }}">
                    <input type="hidden" name="product_code_filter" value="{{ request('product_code_filter') }}">
                    <input type="hidden" name="return_filter" value="{{ request('return_filter') }}">
                    <div class="tw-relative tw-w-full" id="subcategory_filter_container">
                        <div class="tw-w-full tw-min-h-[38px] tw-px-3 tw-py-2 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-flex tw-justify-between tw-items-center tw-cursor-pointer" id="subcategory_filter_display">All Subcategorys</div>
                        <div class="tw-absolute tw-top-full tw-left-0 tw-w-full tw-bg-white tw-border tw-border-gray-300 tw-rounded-b-lg tw-shadow-lg tw-z-50 tw-hidden tw-p-2" id="subcategory_filter_menu">
                            <input type="text" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-200 tw-rounded-lg tw-mb-2 focus:tw-outline-none tw-text-sm" id="subcategory_filter_search" placeholder="Search for an item...">
                            <ul class="tw-max-h-60 tw-overflow-y-auto tw-list-none tw-p-0 tw-m-0" id="subcategory_filter_list">
                                <li class="tw-px-3 tw-py-2 hover:tw-bg-gray-50 tw-cursor-pointer tw-text-sm tw-rounded" data-value="">All Subcategorys</li>
                                @foreach($subcategories as $item)
                                <li class="tw-px-3 tw-py-2 hover:tw-bg-gray-50 tw-cursor-pointer tw-text-sm tw-rounded" data-value="{{ $item->id }}" {{ request('subcategory_filter') == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <select name="subcategory_filter" id="subcategory_filter_select" style="display: none;">
                            <option value="">All Subcategorys</option>
                            @foreach($subcategories as $item)
                            <option value="{{ $item->id }}" {{ request('subcategory_filter') == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <!-- Design Code Filter -->
            <div class="">
                
                <form method="GET">
                    <input type="hidden" name="tab" value="{{ request('tab', 'new-orders') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                    <input type="hidden" name="bp_code_filter" value="{{ request('bp_code_filter') }}">
                    <input type="hidden" name="category_filter" value="{{ request('category_filter') }}">
                    <input type="hidden" name="subcategory_filter" value="{{ request('subcategory_filter') }}">
                    <input type="hidden" name="craftsman_filter" value="{{ request('craftsman_filter') }}">
                    <input type="hidden" name="product_code_filter" value="{{ request('product_code_filter') }}">
                    <input type="hidden" name="return_filter" value="{{ request('return_filter') }}">
                    <div class="tw-relative tw-w-full" id="design_code_filter_container">
                        <div class="tw-w-full tw-min-h-[38px] tw-px-3 tw-py-2 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-flex tw-justify-between tw-items-center tw-cursor-pointer" id="design_code_filter_display">All Design Codes</div>
                        <div class="tw-absolute tw-top-full tw-left-0 tw-w-full tw-bg-white tw-border tw-border-gray-300 tw-rounded-b-lg tw-shadow-lg tw-z-50 tw-hidden tw-p-2" id="design_code_filter_menu">
                            <input type="text" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-200 tw-rounded-lg tw-mb-2 focus:tw-outline-none tw-text-sm" id="design_code_filter_search" placeholder="Search for an item...">
                            <ul class="tw-max-h-60 tw-overflow-y-auto tw-list-none tw-p-0 tw-m-0" id="design_code_filter_list">
                                <li class="tw-px-3 tw-py-2 hover:tw-bg-gray-50 tw-cursor-pointer tw-text-sm tw-rounded" data-value="">All Design Codes</li>
                                @foreach($designCodes as $item)
                                <li class="tw-px-3 tw-py-2 hover:tw-bg-gray-50 tw-cursor-pointer tw-text-sm tw-rounded" data-value="{{ $item }}" {{ request('design_code_filter') == $item ? 'selected' : '' }}>
                                    {{ $item }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <select name="design_code_filter" id="design_code_filter_select" style="display: none;">
                            <option value="">All Design Codes</option>
                            @foreach($designCodes as $item)
                            <option value="{{ $item }}" {{ request('design_code_filter') == $item ? 'selected' : '' }}>
                                {{ $item }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <!-- Product Code Filter -->
            <div class="">
                
                <form method="GET">
                    <input type="hidden" name="tab" value="{{ request('tab', 'new-orders') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                    <input type="hidden" name="bp_code_filter" value="{{ request('bp_code_filter') }}">
                    <input type="hidden" name="category_filter" value="{{ request('category_filter') }}">
                    <input type="hidden" name="subcategory_filter" value="{{ request('subcategory_filter') }}">
                    <input type="hidden" name="craftsman_filter" value="{{ request('craftsman_filter') }}">
                    <input type="hidden" name="design_code_filter" value="{{ request('design_code_filter') }}">
                    <input type="hidden" name="return_filter" value="{{ request('return_filter') }}">
                    <div class="tw-relative tw-w-full" id="product_code_filter_container">
                        <div class="tw-w-full tw-min-h-[38px] tw-px-3 tw-py-2 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-flex tw-justify-between tw-items-center tw-cursor-pointer" id="product_code_filter_display">All Product Codes</div>
                        <div class="tw-absolute tw-top-full tw-left-0 tw-w-full tw-bg-white tw-border tw-border-gray-300 tw-rounded-b-lg tw-shadow-lg tw-z-50 tw-hidden tw-p-2" id="product_code_filter_menu">
                            <input type="text" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-200 tw-rounded-lg tw-mb-2 focus:tw-outline-none tw-text-sm" id="product_code_filter_search" placeholder="Search for an item...">
                            <ul class="tw-max-h-60 tw-overflow-y-auto tw-list-none tw-p-0 tw-m-0" id="product_code_filter_list">
                                <li class="tw-px-3 tw-py-2 hover:tw-bg-gray-50 tw-cursor-pointer tw-text-sm tw-rounded" data-value="">All Product Codes</li>
                                @foreach($productCodes as $item)
                                <li class="tw-px-3 tw-py-2 hover:tw-bg-gray-50 tw-cursor-pointer tw-text-sm tw-rounded" data-value="{{ $item }}" {{ request('product_code_filter') == $item ? 'selected' : '' }}>
                                    {{ $item }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <select name="product_code_filter" id="product_code_filter_select" style="display: none;">
                            <option value="">All Product Codes</option>
                            @foreach($productCodes as $item)
                            <option value="{{ $item }}" {{ request('product_code_filter') == $item ? 'selected' : '' }}>
                                {{ $item }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <!-- Return Status Filter -->
            <div class="">
                
                <form method="GET">
                    <input type="hidden" name="tab" value="{{ request('tab', 'new-orders') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                    <input type="hidden" name="bp_code_filter" value="{{ request('bp_code_filter') }}">
                    <input type="hidden" name="category_filter" value="{{ request('category_filter') }}">
                    <input type="hidden" name="subcategory_filter" value="{{ request('subcategory_filter') }}">
                    <input type="hidden" name="craftsman_filter" value="{{ request('craftsman_filter') }}">
                    <input type="hidden" name="design_code_filter" value="{{ request('design_code_filter') }}">
                    <input type="hidden" name="product_code_filter" value="{{ request('product_code_filter') }}">
                    <div class="tw-relative">
                        <select name="return_filter" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-200 tw-rounded-lg tw-text-sm focus:tw-outline-none" onchange="this.form.submit()">
                            <option value="">All Orders</option>
                            <option value="returned" {{ request('return_filter') == 'returned' ? 'selected' : '' }}>Returned Orders Only</option>
                        </select>
                    </div>
                </form>
            </div>


            <!-- Craftsman Filter -->
            <div>
                <form method="GET">
                    <input type="hidden" name="tab" value="{{ request('tab', 'new-orders') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                    <input type="hidden" name="bp_code_filter" value="{{ request('bp_code_filter') }}">
                    <input type="hidden" name="category_filter" value="{{ request('category_filter') }}">
                    <input type="hidden" name="subcategory_filter" value="{{ request('subcategory_filter') }}">

                    <div class="tw-relative tw-w-full" id="craftsman_filter_container">
                        <div class="tw-w-full tw-min-h-[38px] tw-px-3 tw-py-2 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-flex tw-justify-between tw-items-center tw-cursor-pointer" id="craftsman_filter_display">{{ __('messages.all_craftsmen') }}</div>
                        <div class="tw-absolute tw-top-full tw-left-0 tw-w-full tw-bg-white tw-border tw-border-gray-300 tw-rounded-b-lg tw-shadow-lg tw-z-50 tw-hidden tw-p-2" id="craftsman_filter_menu">
                            <input type="text" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-200 tw-rounded-lg tw-mb-2 focus:tw-outline-none tw-text-sm" id="craftsman_filter_search" placeholder="Search for an item...">
                            <ul class="tw-max-h-60 tw-overflow-y-auto tw-list-none tw-p-0 tw-m-0" id="craftsman_filter_list">
                                <li class="tw-px-3 tw-py-2 hover:tw-bg-gray-50 tw-cursor-pointer tw-text-sm tw-rounded" data-value="">{{ __('messages.all_craftsmen') }}</li>
                                @foreach($craftsmen as $craftsman)
                                <li class="tw-px-3 tw-py-2 hover:tw-bg-gray-50 tw-cursor-pointer tw-text-sm tw-rounded" data-value="{{ $craftsman->craftman_code }}" {{ request('craftsman_filter') == $craftsman->craftman_code ? 'selected' : '' }}>
                                    {{ $craftsman->craftman_code }} - {{ $craftsman->name }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <select name="craftsman_filter" id="craftsman_filter_select" style="display: none;">
                            <option value="">{{ __('messages.all_craftsmen') }}</option>
                            @foreach($craftsmen as $craftsman)
                            <option value="{{ $craftsman->craftman_code }}" {{ request('craftsman_filter') == $craftsman->craftman_code ? 'selected' : '' }}>
                                {{ $craftsman->craftman_code }} - {{ $craftsman->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <!-- Per Page & Clear -->
            <div class="tw-flex tw-gap-2">
                <form method="GET" class="tw-flex-1">
                    <input type="hidden" name="tab" value="{{ request('tab', 'new-orders') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                    <input type="hidden" name="bp_code_filter" value="{{ request('bp_code_filter') }}">
                    <input type="hidden" name="category_filter" value="{{ request('category_filter') }}">
                    <input type="hidden" name="subcategory_filter" value="{{ request('subcategory_filter') }}">
                    <input type="hidden" name="craftsman_filter" value="{{ request('craftsman_filter') }}">
                    <select name="per_page" onchange="this.form.submit()" class="tw-w-full tw-text-sm tw-border-gray-300 tw-rounded-lg focus:tw-ring-primary focus:tw-border-primary tw-py-2 tw-pl-2 tw-pr-8">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 Per Page</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 Per Page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 Per Page</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 Per Page</option>
                    </select>
                </form>
                <div class="tw-flex tw-items-center">
                    <a href="{{ route('super-admin.work-order.index', ['tab' => request('tab', 'new-orders')]) }}"
                        class="tw-p-2 tw-text-gray-400 hover:tw-text-gray-600 tw-border tw-border-gray-300 tw-rounded-lg" title="Clear Filters">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>


    <div class="tw-mb-6">
        @if (session('success'))
        <div class="tw-flex tw-items-center tw-p-4 tw-mb-4 tw-text-emerald-800 tw-rounded-lg tw-bg-emerald-50 tw-border tw-border-emerald-100 tw-shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill tw-flex-shrink-0 tw-mr-2"></i>
            <div class="tw-text-sm tw-font-medium">{{ session('success') }}</div>
            <button type="button" class="tw-ml-auto -tw-mx-1.5 -tw-my-1.5 tw-bg-emerald-50 tw-text-emerald-500 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-emerald-400 tw-p-1.5 hover:tw-bg-emerald-100 tw-inline-flex tw-h-8 tw-w-8 tw-transition-all" data-bs-dismiss="alert" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        @endif

        @if (session('error'))
        <div class="tw-flex tw-items-center tw-p-4 tw-mb-4 tw-text-rose-800 tw-rounded-lg tw-bg-rose-50 tw-border tw-border-rose-100 tw-shadow-sm" role="alert">
            <i class="bi bi-exclamation-circle-fill tw-flex-shrink-0 tw-mr-2"></i>
            <div class="tw-text-sm tw-font-medium">{{ session('error') }}</div>
            <button type="button" class="tw-ml-auto -tw-mx-1.5 -tw-my-1.5 tw-bg-rose-50 tw-text-rose-500 tw-rounded-lg focus:tw-ring-2 focus:tw-ring-rose-400 tw-p-1.5 hover:tw-bg-rose-100 tw-inline-flex tw-h-8 tw-w-8 tw-transition-all" data-bs-dismiss="alert" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        @endif
    </div>

    <!-- Modern Navigation Tabs -->
    <div class="tw-border-b tw-border-gray-200 tw-mb-6">
        <ul class="tw-flex tw-flex-wrap tw-text-sm tw-font-medium tw-text-center tw-text-gray-500" id="workOrderTabs">
            @php
            $tabs = [
            'all-orders' => ['label' => 'All Orders', 'count' => $counts['all-orders'], 'icon' => 'bi-list-ul'],
            'new-orders' => ['label' => __('messages.new_orders'), 'count' => $counts['new-orders'], 'icon' => 'bi-plus-circle'],
            'allocated-orders' => ['label' => __('messages.allocated_orders'), 'count' => $counts['allocated-orders'], 'icon' => 'bi-people'],
            'in-process-orders' => ['label' => __('messages.in_process'), 'count' => $counts['in-process-orders'], 'icon' => 'bi-gear'],
            'overdue-orders' => ['label' => __('messages.overdue_orders'), 'count' => $counts['overdue-orders'], 'icon' => 'bi-clock-history'],
            'for-approval-orders' => ['label' => __('messages.for_approval'), 'count' => $counts['for-approval-orders'], 'icon' => 'bi-check2-square'],
            'completed-orders' => ['label' => __('messages.completed_orders'), 'count' => $counts['completed-orders'], 'icon' => 'bi-check-all'],
            'rejected-orders' => ['label' => __('messages.rejected'), 'count' => $counts['rejected-orders'], 'icon' => 'bi-x-circle'],
            ];
            $activeTab = request('tab', 'new-orders');
            @endphp

            @foreach($tabs as $id => $tab)
            <li class="tw-mr-2">
                <a href="{{ route('super-admin.work-order.index', array_merge(request()->query(), ['tab' => $id])) }}"
                    class="tw-inline-flex tw-items-center tw-px-4 tw-py-4 tw-border-b-2 tw-rounded-t-lg tw-transition-all tw-duration-200 {{ $activeTab == $id ? 'tw-text-primary tw-border-primary' : 'tw-border-transparent hover:tw-text-gray-600 hover:tw-border-gray-300' }}"
                    id="{{ $id }}-tab">
                    <i class="bi {{ $tab['icon'] }} tw-mr-2"></i>
                    {{ $tab['label'] }}
                    <span class="tw-ml-2 tw-px-2 tw-py-0.5 tw-text-xs tw-font-semibold tw-rounded-full {{ $activeTab == $id ? 'tw-bg-primary/10 tw-text-primary' : 'tw-bg-gray-100 tw-text-gray-600' }}">
                        {{ $tab['count'] }}
                    </span>
                </a>
            </li>
            @endforeach
        </ul>
    </div>

    <div class="tab-content" id="workOrderTabsContent">
        @if($activeTab == 'new-orders')
        <div class="tab-pane fade show active" id="new-orders" role="tabpanel">
            <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-shadow-sm tw-overflow-hidden">
                <!-- Tab Content Header -->
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-100 tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-4">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">{{ __('messages.new_orders') }}</h3>

                    <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-3">
                        <!-- Sort By -->
                        <form method="GET" class="tw-flex tw-items-center tw-gap-2">
                            <input type="hidden" name="tab" value="new-orders">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                            <span class="tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Sort:</span>
                            <select name="sort_by" onchange="this.form.submit()" class="tw-text-xs tw-border-gray-300 tw-rounded-lg focus:tw-ring-primary focus:tw-border-primary tw-py-1 tw-pl-2 tw-pr-8">
                                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Recent First</option>
                                <option value="work_order_number" {{ request('sort_by') == 'work_order_number' ? 'selected' : '' }}>WO Number</option>
                                <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>Due Date</option>
                                <option value="quantity" {{ request('sort_by') == 'quantity' ? 'selected' : '' }}>Quantity</option>
                            </select>
                            <select name="sort_order" onchange="this.form.submit()" class="tw-text-xs tw-border-gray-300 tw-rounded-lg focus:tw-ring-primary focus:tw-border-primary tw-py-1 tw-pl-2 tw-pr-8">
                                <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>DESC</option>
                                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>ASC</option>
                            </select>
                        </form>

                        <!-- Page Size -->
                        <form method="GET" class="tw-flex tw-items-center tw-gap-2">
                            <input type="hidden" name="tab" value="new-orders">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                            <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                            <span class="tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Show:</span>
                            <select name="per_page" onchange="this.form.submit()" class="tw-text-xs tw-border-gray-300 tw-rounded-lg focus:tw-ring-primary focus:tw-border-primary tw-py-1 tw-pl-2 tw-pr-8">
                                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </form>
                    </div>
                </div>

                <div class="tw-p-0">
                    @if($newOrders->count() > 0)
                    <form id="bulk-allocate-form" method="GET" action="{{ route('super-admin.work-order.bulk-allocate-form') }}">
                        <div class="tw-px-6 tw-py-4 tw-bg-gray-50/50 tw-border-t tw-border-gray-100 tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-4">
                            <div class="tw-flex tw-flex-wrap tw-gap-2">
                                <button type="submit" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-[#800000] hover:tw-bg-[#600000] tw-text-white tw-text-sm tw-font-semibold tw-rounded-lg tw-shadow-sm tw-transition-all tw-duration-200 disabled:tw-opacity-50 disabled:tw-cursor-not-allowed" id="bulk-allocate-btn" disabled>
                                    <i class="bi bi-people tw-mr-2"></i> {{ __('messages.bulk_allocate_selected') }}
                                </button>
                                <button type="button" onclick="submitBulkComplete()" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-emerald-600 hover:tw-bg-emerald-700 tw-text-white tw-text-sm tw-font-semibold tw-rounded-lg tw-shadow-sm tw-transition-all tw-duration-200">
                                    <i class="bi bi-check-all tw-mr-2"></i> Bulk Complete
                                </button>
                            </div>
                        </div>
                        <div class="tw-overflow-x-auto">
                            <table class="tw-w-full tw-text-sm tw-text-left">
                                <thead class="tw-text-sm tw-text-gray-900 tw-uppercase tw-bg-slate-100 tw-border-b-2 tw-border-gray-200">
                                    <tr>
                                        <th class="tw-px-6 tw-py-5">
                                            <input type="checkbox" id="select-all-new-orders"
                                                class="tw-w-4 tw-h-4 tw-rounded tw-border-gray-300 tw-text-primary focus:tw-ring-primary">
                                        </th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Images</th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Order Details</th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Customer</th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Dates</th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Specs</th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider tw-text-right">
                                            {{ __('messages.actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="tw-divide-y tw-divide-gray-50">
                                    @foreach($newOrders as $order)
                                    @php
                                    $rowStyle = '';
                                    $isOverdue = false;
                                    $isDueWithin48Hours = false;
                                    $allocatedWithin48Hours = false;
                                    $now = \Carbon\Carbon::now();

                                    // For PO and WorkOrder
                                    $dueDateValue = null;
                                    if (isset($order) && isset($order->craftsman_due_date)) {
                                    $dueDateValue = $order->craftsman_due_date;
                                    } elseif (isset($order) && isset($order->due_date)) {
                                    $dueDateValue = $order->due_date;
                                    } elseif (isset($po) && isset($po->due_date)) {
                                    $dueDateValue = $po->due_date;
                                    }

                                    if ($dueDateValue) {
                                    $dueDate = \Carbon\Carbon::parse($dueDateValue);
                                    if ($dueDate->lt($now->startOfDay()) || ($dueDate->isToday() && $now->hour >= 12)) {
                                    $isOverdue = true;
                                    } else {
                                    $hoursDiff = $now->diffInHours($dueDate, false);
                                    if ($hoursDiff >= 0 && $hoursDiff <= 48) {
                                        $isDueWithin48Hours=true;
                                        }
                                        }
                                        }

                                        // Handle updated_at for allocated within 48h
                                        $updatedAtValue=null;
                                        if (isset($order) && isset($order->updated_at)) {
                                        $updatedAtValue = $order->updated_at;
                                        } elseif (isset($po) && isset($po->updated_at)) {
                                        $updatedAtValue = $po->updated_at;
                                        }

                                        $currentTabString = '';
                                        if (isset($activeTab)) {
                                        $currentTabString = $activeTab;
                                        } elseif (isset($currentTab)) {
                                        $currentTabString = $currentTab;
                                        } elseif (isset($tab['id'])) {
                                        $currentTabString = $tab['id'];
                                        }

                                        if (($currentTabString == 'allocated-orders' || $currentTabString == 'allocated') && $updatedAtValue) {
                                        if (\Carbon\Carbon::parse($updatedAtValue)->diffInHours($now) <= 48) {
                                            $allocatedWithin48Hours=true;
                                            }
                                            }

                                            if ($isOverdue) {
                                            $rowStyle='background-color: rgba(255, 228, 230, 0.8) !important;' ; // rose
                                            } elseif ($isDueWithin48Hours) {
                                            $rowStyle='background-color: rgba(255, 237, 213, 0.8) !important;' ; // orange
                                            } elseif ($currentTabString=='in-process-orders' || $currentTabString=='in-process' || $currentTabString=='in_process' ) {
                                            $rowStyle='background-color: rgba(220, 252, 231, 0.8) !important;' ; // green
                                            } elseif (($currentTabString=='allocated-orders' || $currentTabString=='allocated' ) && $allocatedWithin48Hours) {
                                            $rowStyle='background-color: rgba(219, 234, 254, 0.8) !important;' ; // blue
                                            } elseif ($currentTabString=='new-orders' || $currentTabString=='created' ) {
                                            $rowStyle='background-color: rgba(254, 252, 232, 0.8) !important;' ; // yellow
                                            }
                                            @endphp
                                            <tr class="hover:tw-bg-gray-50 tw-transition-colors  " style="{{ $rowStyle }}">
                                            <td class="tw-px-6 tw-py-4">
                                                <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="new-order-checkbox tw-rounded tw-border-gray-300 tw-text-primary focus:tw-ring-primary">
                                            </td>
                                            <td class="tw-px-4 tw-py-4">
                                                @php
                                                $displayImage = $order->product_image;
                                                $isPdf = false;

                                                if ($displayImage) {
                                                $isPdf = Str::endsWith(strtolower($displayImage), '.pdf');
                                                if (!Str::startsWith($displayImage, ['http', 'storage/', 'images/', 'uploads/'])) {
                                                $displayImage = 'storage/' . $displayImage;
                                                }
                                                } elseif ($order->product && $order->product->images->isNotEmpty()) {
                                                $displayImage = $order->product->images->first()->path;
                                                $isPdf = Str::endsWith(strtolower($displayImage), '.pdf');
                                                if (!Str::startsWith($displayImage, ['http', 'storage/', 'images/', 'uploads/'])) {
                                                $displayImage = 'storage/' . $displayImage;
                                                }
                                                }
                                                @endphp
                                                @if($displayImage)
                                                <div class="tw-group tw-relative tw-w-12 tw-h-12 tw-rounded-lg tw-border tw-border-gray-200 tw-bg-gray-50 tw-overflow-hidden tw-cursor-pointer tw-flex tw-items-center tw-justify-center"
                                                    onclick="openUniversalPreview('{{ asset($displayImage) }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                    @if($isPdf)
                                                    <canvas class="pdf-canvas tw-w-full tw-h-full tw-object-contain" data-url="{{ asset($displayImage) }}"></canvas>
                                                    <div class="tw-absolute tw-inset-0 tw-flex tw-items-center tw-justify-center tw-bg-black/0 group-hover:tw-bg-black/10 tw-transition-all">

                                                    </div>
                                                    @else
                                                    <img src="{{ asset($displayImage) }}" class="tw-w-full tw-h-full tw-object-cover group-hover:tw-scale-110 tw-transition-transform tw-duration-300" alt="Product">
                                                    @endif

                                                    @if($order->product && $order->product->images->count() > 1)
                                                    <span class="tw-absolute tw-bottom-0.5 tw-right-0.5 tw-bg-black/60 tw-text-white tw-text-[10px] tw-px-1 tw-rounded-sm tw-font-bold">+{{ $order->product->images->count() - 1 }}</span>
                                                    @endif
                                                </div>
                                                @else
                                                <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-bg-gray-100 tw-flex tw-items-center tw-justify-center">
                                                    <i class="bi bi-image tw-text-gray-300"></i>
                                                </div>
                                                @endif
                                            </td>
                                            <td class="tw-px-4 tw-py-4">
                                                <div class="tw-font-extrabold tw-text-gray-900 tw-uppercase tw-tracking-tight tw-text-lg">
                                                    {{ $order->work_order_number }}
                                                </div>

                                                <div class="tw-mt-2">
                                                    <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-1 tw-rounded-md tw-bg-gray-100 tw-text-xs tw-font-bold tw-text-gray-700 tw-border tw-border-gray-200">
                                                        <span class="tw-text-gray-400 tw-mr-1">REF:</span>
                                                        {{ $order_details->reference_no ?? ($order->reference_no ?? '-') }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="tw-px-4 tw-py-4">
                                                <div class="tw-text-gray-900 tw-font-bold tw-text-xs">{{ $order->customer_name }}</div>
                                                <div class="tw-text-[14px] tw-text-primary tw-font-bold tw-mt-1">{{ $order->bp_code ?? 'NO BP' }}</div>
                                                <!-- @if($order->buyer)
                                            <div class="tw-text-[10px] tw-text-gray-400 tw-mt-0.5">{{ $order->buyer->dear }}</div>
                                            @endif -->
                                            </td>
                                            <td class="tw-px-4 tw-py-4">
                                                <div class="tw-flex tw-flex-col tw-gap-2">
                                                    <div class="tw-flex tw-items-center tw-gap-1.5 tw-text-[11px]">
                                                        <span class="tw-text-gray-400 tw-w-8 tw-font-bold">ORD:</span>
                                                        <span class="tw-text-gray-600 tw-font-medium">
                                                            {{ $order->created_at ? $order->created_at->format('d M, Y') : '-' }}
                                                        </span>
                                                    </div>

                                                    <div class="tw-flex tw-items-center tw-gap-1.5">
                                                        <span class="tw-text-gray-400 tw-w-8 tw-text-[11px] tw-font-bold">DUE:</span>
                                                        <span class="tw-px-2 tw-py-0.5 tw-rounded tw-text-sm tw-font-extrabold shadow-sm
                {{ $isOverdue 
                    ? 'tw-bg-rose-50 tw-text-rose-700 tw-border tw-border-rose-200' 
                    : 'tw-bg-emerald-50 tw-text-emerald-700 tw-border tw-border-emerald-200' 
                }}">
                                                            {{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'NOT SET' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="tw-px-4 tw-py-4">
                                                <div class="tw-flex tw-flex-col tw-gap-2.5">

                                                    <div class="tw-bg-blue-50 tw-border tw-border-blue-100 tw-rounded-md tw-p-2">
                                                        <div class="tw-text-[11px] tw-leading-tight">
                                                            <span class="tw-text-blue-400 tw-font-bold tw-uppercase">CAT:</span>
                                                            <span class="tw-text-blue-900 tw-font-extrabold tw-uppercase tw-text-xs">{{ $order->product_category ?: '-' }}</span>
                                                        </div>
                                                        <div class="tw-text-[11px] tw-mt-0.5">
                                                            <span class="tw-text-blue-400 tw-font-bold tw-uppercase">SUB:</span>
                                                            <span class="tw-text-blue-800 tw-font-semibold">{{ $order->subcategory ?: '-' }}</span>
                                                        </div>
                                                        <div class="tw-text-[11px] tw-mt-0.5">
                                                            <span class="tw-text-blue-400 tw-font-bold tw-uppercase">SIZE:</span>
                                                            <span class="tw-text-blue-800 tw-font-semibold">{{ $order->size ?: '-' }}</span>
                                                        </div>
                                                        <div class="tw-text-[11px] tw-mt-0.5">
                                                            <span class="tw-text-blue-400 tw-font-bold tw-uppercase">LENGTH:</span>
                                                            <span class="tw-text-blue-800 tw-font-semibold">{{ $order->length ?: '-' }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="tw-flex tw-items-center tw-gap-2 tw-bg-amber-50 tw-border tw-border-amber-200 tw-rounded tw-px-2 tw-py-1.5 tw-w-fit">
                                                        <span class="tw-text-sm tw-font-black tw-text-amber-700">
                                                            {{ $order->quantity }} {{ $order->type ?: 'Piece' }}
                                                        </span>
                                                        <span class="tw-text-amber-300">|</span>
                                                        <span class="tw-text-xs tw-font-bold tw-text-amber-600">
                                                            {{ $order->weight_from ?: '-' }} g
                                                        </span>
                                                    </div>

                                                    <div class="tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded tw-p-2">
                                                        <div class="tw-text-[10px] tw-text-slate-400 tw-font-bold tw-uppercase tw-mb-1">Craftsman Notes:</div>
                                                        <div class="tw-text-sm tw-text-slate-700 tw-font-medium tw-italic tw-leading-snug">
                                                            "{{ $order->narration_craftsman ?: '-' }}"
                                                        </div>
                                                    </div>

                                                </div>
                                            </td>
                                            <td class="tw-px-4 tw-py-4 tw-text-right">
                                                <div class="tw-flex tw-justify-end tw-gap-1">
                                                    <a href="{{ route('super-admin.work-order.show', $order) }}" class="tw-p-2 tw-text-sky-600 hover:tw-bg-sky-50 tw-rounded-lg tw-transition-colors" title="View">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('super-admin.work-order.edit', ['workOrder' => $order->id, 'return_url' => url()->full()]) }}" class="tw-p-2 tw-text-amber-600 hover:tw-bg-amber-50 tw-rounded-lg tw-transition-colors" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="{{ route('super-admin.work-order.allocate.form', $order) }}" class="tw-p-2 tw-text-emerald-600 hover:tw-bg-emerald-50 tw-rounded-lg tw-transition-colors" title="Allocate">
                                                        <i class="bi bi-person-plus"></i>
                                                    </a>
                                                    <form action="{{ route('super-admin.work-order.destroy', $order) }}" method="POST" class="tw-inline" onsubmit="return confirm('Are you sure you want to delete this work order?');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="tw-p-2 tw-text-rose-600 hover:tw-bg-rose-50 tw-rounded-lg tw-transition-colors" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                            </tr>
                                            @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="tw-px-6 tw-py-4 tw-bg-gray-50/50 tw-border-t tw-border-gray-100 tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-4">


                            <div class="tw-flex-1 tw-flex tw-justify-end">
                                {{ $newOrders->appends(array_merge(request()->query(), ['tab' => 'new-orders']))->links('vendor.pagination.custom-pagination') }}
                            </div>
                        </div>
                    </form>
                    @else
                    <div class="tw-text-center tw-py-16">
                        <div class="tw-w-16 tw-h-16 tw-bg-gray-50 tw-text-gray-300 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                            <i class="bi bi-file-earmark-text tw-text-3xl"></i>
                        </div>
                        <h4 class="tw-text-gray-900 tw-font-semibold tw-mb-1">{{ __('messages.no_records_found') }}</h4>
                        <p class="tw-text-gray-500 tw-text-sm tw-mb-6">Start by creating your first work order.</p>
                        <a href="{{ route('super-admin.work-order.create') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-primary tw-text-white tw-text-sm tw-font-semibold tw-rounded-lg tw-shadow-sm hover:tw-bg-primary/90 tw-transition-all">
                            <i class="bi bi-plus-lg tw-mr-2"></i> {{ __('messages.add_work_order') }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Allocated Orders Tab -->
        @if($activeTab == 'allocated-orders')
        <div class="tab-pane fade show active" id="allocated-orders" role="tabpanel">
            <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-shadow-sm tw-overflow-hidden">
                <!-- Tab Content Header -->
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-100 tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-4">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">{{ __('messages.allocated_orders') }}</h3>

                    <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-3">
                        <!-- Sort By -->
                        <form method="GET" class="tw-flex tw-items-center tw-gap-2">
                            <input type="hidden" name="tab" value="allocated-orders">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                            <span class="tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Sort:</span>
                            <select name="sort_by" onchange="this.form.submit()" class="tw-text-xs tw-border-gray-300 tw-rounded-lg focus:tw-ring-primary focus:tw-border-primary tw-py-1 tw-pl-2 tw-pr-8">
                                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Recent First</option>
                                <option value="work_order_number" {{ request('sort_by') == 'work_order_number' ? 'selected' : '' }}>WO Number</option>
                                <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>Due Date</option>
                                <option value="quantity" {{ request('sort_by') == 'quantity' ? 'selected' : '' }}>Quantity</option>
                            </select>
                            <select name="sort_order" onchange="this.form.submit()" class="tw-text-xs tw-border-gray-300 tw-rounded-lg focus:tw-ring-primary focus:tw-border-primary tw-py-1 tw-pl-2 tw-pr-8">
                                <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>DESC</option>
                                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>ASC</option>
                            </select>
                        </form>

                        <!-- Page Size -->
                        <form method="GET" class="tw-flex tw-items-center tw-gap-2">
                            <input type="hidden" name="tab" value="allocated-orders">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                            <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                            <span class="tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">Show:</span>
                            <select name="per_page" onchange="this.form.submit()" class="tw-text-xs tw-border-gray-300 tw-rounded-lg focus:tw-ring-primary focus:tw-border-primary tw-py-1 tw-pl-2 tw-pr-8">
                                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </form>
                    </div>
                </div>

                <div class="tw-p-0">
                    @if($allocatedOrders->count() > 0)
                    <div class="tw-px-6 tw-py-4 tw-bg-gray-50/50 tw-border-b tw-border-gray-100 tw-flex tw-items-center tw-gap-3">
                        <button type="button" onclick="submitBulkComplete()" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-emerald-600 hover:tw-bg-emerald-700 tw-text-white tw-text-sm tw-font-semibold tw-rounded-lg tw-shadow-sm tw-transition-all tw-duration-200">
                            <i class="bi bi-check-all tw-mr-2"></i> Bulk Complete
                        </button>
                    </div>
                    <div class="">
                        <table class="tw-w-full tw-text-sm tw-text-left">
                            <thead class="tw-text-sm tw-text-gray-900 tw-uppercase tw-bg-slate-100 tw-border-b-2 tw-border-gray-200">
                                <tr>
                                    <th class="tw-px-6 tw-py-5">
                                        <input type="checkbox" id="select-all-allocated-orders"
                                            class="tw-w-4 tw-h-4 tw-rounded tw-border-gray-300 tw-text-primary focus:tw-ring-primary">
                                    </th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Images</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Order Details</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Customer</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Dates</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Specs</th>
                                    <!-- <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Craftsman</th> -->
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider tw-text-right">
                                        {{ __('messages.actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <!-- <thead class="tw-text-xs tw-text-gray-500 tw-uppercase tw-bg-gray-50 tw-border-b tw-border-gray-100">
                                <tr>
                                    <th class="tw-px-6 tw-py-4 tw-font-medium">
                                        <input type="checkbox" id="select-all-allocated-orders" class="tw-rounded tw-border-gray-300 tw-text-primary focus:tw-ring-primary">
                                    </th>
                                    <th class="tw-px-4 tw-py-4 tw-font-medium">{{ __('messages.image') }}</th>
                                    <th class="tw-px-4 tw-py-4 tw-font-medium">{{ __('messages.order_details') }}</th>
                                    <th class="tw-px-4 tw-py-4 tw-font-medium">{{ __('messages.customer') }}</th>
                                    <th class="tw-px-4 tw-py-4 tw-font-medium">Dates</th>
                                    <th class="tw-px-4 tw-py-4 tw-font-medium">Specs</th>
                                    <th class="tw-px-4 tw-py-4 tw-font-medium">{{ __('messages.allocated_craftsman') }}</th>
                                    <th class="tw-px-4 tw-py-4 tw-font-medium tw-text-right">{{ __('messages.actions') }}</th>
                                </tr>
                            </thead> -->
                            <tbody class="tw-divide-y tw-divide-gray-50">
                                @foreach($allocatedOrders as $order)
                                @php
                                $rowStyle = '';
                                $isOverdue = false;
                                $isDueWithin48Hours = false;
                                $allocatedWithin48Hours = false;
                                $now = \Carbon\Carbon::now();

                                // For PO and WorkOrder
                                $dueDateValue = null;
                                if (isset($order) && isset($order->craftsman_due_date)) {
                                $dueDateValue = $order->craftsman_due_date;
                                } elseif (isset($order) && isset($order->due_date)) {
                                $dueDateValue = $order->due_date;
                                } elseif (isset($po) && isset($po->due_date)) {
                                $dueDateValue = $po->due_date;
                                }

                                if ($dueDateValue) {
                                $dueDate = \Carbon\Carbon::parse($dueDateValue);
                                if ($dueDate->lt($now->startOfDay()) || ($dueDate->isToday() && $now->hour >= 12)) {
                                $isOverdue = true;
                                } else {
                                $hoursDiff = $now->diffInHours($dueDate, false);
                                if ($hoursDiff >= 0 && $hoursDiff <= 48) {
                                    $isDueWithin48Hours=true;
                                    }
                                    }
                                    }

                                    // Handle updated_at for allocated within 48h
                                    $updatedAtValue=null;
                                    if (isset($order) && isset($order->updated_at)) {
                                    $updatedAtValue = $order->updated_at;
                                    } elseif (isset($po) && isset($po->updated_at)) {
                                    $updatedAtValue = $po->updated_at;
                                    }

                                    $currentTabString = '';
                                    if (isset($activeTab)) {
                                    $currentTabString = $activeTab;
                                    } elseif (isset($currentTab)) {
                                    $currentTabString = $currentTab;
                                    } elseif (isset($tab['id'])) {
                                    $currentTabString = $tab['id'];
                                    }

                                    if (($currentTabString == 'allocated-orders' || $currentTabString == 'allocated') && $updatedAtValue) {
                                    if (\Carbon\Carbon::parse($updatedAtValue)->diffInHours($now) <= 48) {
                                        $allocatedWithin48Hours=true;
                                        }
                                        }

                                        if ($isOverdue) {
                                        $rowStyle='background-color: rgba(255, 228, 230, 0.8) !important;' ; // rose
                                        } elseif ($isDueWithin48Hours) {
                                        $rowStyle='background-color: rgba(255, 237, 213, 0.8) !important;' ; // orange
                                        } elseif ($currentTabString=='in-process-orders' || $currentTabString=='in-process' || $currentTabString=='in_process' ) {
                                        $rowStyle='background-color: rgba(220, 252, 231, 0.8) !important;' ; // green
                                        } elseif (($currentTabString=='allocated-orders' || $currentTabString=='allocated' ) && $allocatedWithin48Hours) {
                                        $rowStyle='background-color: rgba(219, 234, 254, 0.8) !important;' ; // blue
                                        } elseif ($currentTabString=='new-orders' || $currentTabString=='created' ) {
                                        $rowStyle='background-color: rgba(254, 252, 232, 0.8) !important;' ; // yellow
                                        }
                                        @endphp
                                        <tr class="hover:tw-bg-gray-50 tw-transition-colors  " style="{{ $rowStyle }}">
                                        <td class="tw-px-6 tw-py-4">
                                            <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="allocated-order-checkbox tw-rounded tw-border-gray-300 tw-text-primary focus:tw-ring-primary">
                                        </td>
                                        <td class="tw-px-4 tw-py-4">
                                            @php
                                            $displayImage = $order->product_image;
                                            $isPdf = false;

                                            if ($displayImage) {
                                            $isPdf = Str::endsWith(strtolower($displayImage), '.pdf');
                                            if (!Str::startsWith($displayImage, ['http', 'storage/', 'images/', 'uploads/'])) {
                                            $displayImage = 'storage/' . $displayImage;
                                            }
                                            } elseif ($order->product && $order->product->images->isNotEmpty()) {
                                            $displayImage = $order->product->images->first()->path;
                                            $isPdf = Str::endsWith(strtolower($displayImage), '.pdf');
                                            if (!Str::startsWith($displayImage, ['http', 'storage/', 'images/', 'uploads/'])) {
                                            $displayImage = 'storage/' . $displayImage;
                                            }
                                            }
                                            @endphp
                                            @if($displayImage)
                                            <div class="tw-group tw-relative tw-w-12 tw-h-12 tw-rounded-lg tw-border tw-border-gray-200 tw-bg-gray-50 tw-overflow-hidden tw-cursor-pointer tw-flex tw-items-center tw-justify-center"
                                                onclick="openUniversalPreview('{{ asset($displayImage) }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                @if($isPdf)
                                                <canvas class="pdf-canvas tw-w-full tw-h-full tw-object-contain" data-url="{{ asset($displayImage) }}"></canvas>
                                                <div class="tw-absolute tw-inset-0 tw-flex tw-items-center tw-justify-center tw-bg-black/0 group-hover:tw-bg-black/10 tw-transition-all">

                                                </div>
                                                @else
                                                <img src="{{ asset($displayImage) }}" class="tw-w-full tw-h-full tw-object-cover group-hover:tw-scale-110 tw-transition-transform tw-duration-300" alt="Product">
                                                @endif
                                                @if($order->product && $order->product->images->count() > 1)
                                                <span class="tw-absolute tw-bottom-0.5 tw-right-0.5 tw-bg-black/60 tw-text-white tw-text-[10px] tw-px-1 tw-rounded-sm tw-font-bold">+{{ $order->product->images->count() - 1 }}</span>
                                                @endif
                                            </div>
                                            @else
                                            <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-bg-gray-100 tw-flex tw-items-center tw-justify-center">
                                                <i class="bi bi-image tw-text-gray-300"></i>
                                            </div>
                                            @endif
                                        </td>
                                        <td class="tw-px-4 tw-py-4">
                                            <div class="tw-font-extrabold tw-text-gray-900 tw-uppercase tw-tracking-tight tw-text-lg">
                                                {{ $order->work_order_number }}
                                            </div>

                                            <div class="tw-mt-2">
                                                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-1 tw-rounded-md tw-bg-gray-100 tw-text-xs tw-font-bold tw-text-gray-700 tw-border tw-border-gray-200">
                                                    <span class="tw-text-gray-400 tw-mr-1">REF:</span>
                                                    {{ $order_details->reference_no ?? ($order->reference_no ?? '-') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="tw-px-4 tw-py-4">
                                            <div class="tw-text-[14px] tw-text-red-600 tw-font-bold tw-mt-1">{{ $order->customer_name }}</div>
                                            <div class="tw-text-[14px] tw-text-red-600 tw-font-bold tw-mt-1">{{ $order->bp_code ?? 'NO BP' }}</div>
                                            <div class="tw-text-[14px] tw-text-black-600 tw-font-bold tw-mt-1">{{ $order->craftsman ? $order->craftsman->craftman_code : 'No Code' }}</div>
                                            <div class="tw-text-[14px] tw-text-black-600 tw-font-bold tw-mt-1">{{ $order->craftsman ? $order->craftsman->name : 'No name' }}</div>
                                            <!-- @if($order->buyer)
                                            <div class="tw-text-[10px] tw-text-gray-400 tw-mt-0.5">{{ $order->buyer->dear }}</div>
                                            @endif -->
                                        </td>
                                        <td class="tw-px-4 tw-py-4">
                                            <div class="tw-flex tw-flex-col tw-gap-2">
                                                <div class="tw-flex tw-items-center tw-gap-1.5 tw-text-[11px]">
                                                    <span class="tw-text-gray-400 tw-w-8 tw-font-bold">ORD:</span>
                                                    <span class="tw-text-gray-600 tw-font-medium">
                                                        {{ $order->created_at ? $order->created_at->format('d M, Y') : '-' }}
                                                    </span>
                                                </div>

                                                <div class="tw-flex tw-items-center tw-gap-1.5">
                                                    <span class="tw-text-gray-400 tw-w-8 tw-text-[11px] tw-font-bold">DUE:</span>
                                                    <span class="tw-px-2 tw-py-0.5 tw-rounded tw-text-sm tw-font-extrabold shadow-sm
                {{ $isOverdue 
                    ? 'tw-bg-rose-50 tw-text-rose-700 tw-border tw-border-rose-200' 
                    : 'tw-bg-emerald-50 tw-text-emerald-700 tw-border tw-border-emerald-200' 
                }}">
                                                        {{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'NOT SET' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="tw-px-4 tw-py-4">
                                            <div class="tw-flex tw-flex-col tw-gap-2.5">

                                                <div class="tw-bg-blue-50 tw-border tw-border-blue-100 tw-rounded-md tw-p-2">
                                                    <div class="tw-text-[11px] tw-leading-tight">
                                                        <span class="tw-text-blue-400 tw-font-bold tw-uppercase">CAT:</span>
                                                        <span class="tw-text-blue-900 tw-font-extrabold tw-uppercase tw-text-xs">{{ $order->product_category ?: '-' }}</span>
                                                    </div>
                                                    <div class="tw-text-[11px] tw-mt-0.5">
                                                        <span class="tw-text-blue-400 tw-font-bold tw-uppercase">SUB:</span>
                                                        <span class="tw-text-blue-800 tw-font-semibold">{{ $order->subcategory ?: '-' }}</span>
                                                    </div>
                                                    <div class="tw-text-[11px] tw-mt-0.5">
                                                        <span class="tw-text-blue-400 tw-font-bold tw-uppercase">SIZR:</span>
                                                        <span class="tw-text-blue-800 tw-font-semibold">{{ $order->size ?: '-' }}</span>
                                                    </div>
                                                </div>

                                                <div class="tw-flex tw-items-center tw-gap-2 tw-bg-amber-50 tw-border tw-border-amber-200 tw-rounded tw-px-2 tw-py-1.5 tw-w-fit">
                                                    <span class="tw-text-sm tw-font-black tw-text-amber-700">
                                                        {{ $order->quantity }} {{ $order->type ?: 'Piece' }}
                                                    </span>
                                                    <span class="tw-text-amber-300">|</span>
                                                    <span class="tw-text-xs tw-font-bold tw-text-amber-600">
                                                        {{ $order->weight_from ?: '-' }} g
                                                    </span>
                                                </div>

                                                <div class="tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded tw-p-2">
                                                    <div class="tw-text-[10px] tw-text-slate-400 tw-font-bold tw-uppercase tw-mb-1">Craftsman Notes:</div>
                                                    <div class="tw-text-sm tw-text-slate-700 tw-font-medium tw-italic tw-leading-snug">
                                                        "{{ $order->narration_craftsman ?: '-' }}"
                                                    </div>
                                                </div>
                                                @if($order->return_note)
<div class="tw-bg-amber-50 tw-border tw-border-amber-200 tw-rounded tw-p-2 tw-mb-2">
    <div class="tw-text-[10px] tw-text-amber-700 tw-font-bold tw-uppercase tw-mb-1">Return Notes:</div>
    <div class="tw-text-sm tw-text-amber-900 tw-font-medium tw-italic tw-leading-snug">
        "{{ $order->return_note }}"
    </div>
</div>
@endif

@if($order->return_due_date)
<div class="tw-flex tw-items-center tw-gap-1.5 tw-text-[11px]">
    <span class="tw-text-slate-500 tw-w-8 tw-font-bold">RDUE:</span>
    <span class="tw-text-rose-600 tw-font-semibold">
        {{ $order->return_due_date->format('d M, Y') }}
    </span>
</div>
@endif
                                            </div>
                                        </td>
                                        <!-- <td class="tw-px-4 tw-py-4">
                                        <div class="tw-flex tw-items-center tw-gap-2">
                                            <div class="tw-w-8 tw-h-8 tw-rounded-full tw-bg-gray-100 tw-flex tw-items-center tw-justify-center tw-text-gray-600 tw-font-bold tw-text-xs">
                                                {{ $order->craftsman ? substr($order->craftsman->name, 0, 1) : '?' }}
                                            </div>
                                            <div>
                                                <div class="tw-text-xs tw-font-semibold tw-text-gray-900">{{ $order->craftsman ? $order->craftsman->name : 'N/A' }}</div>
                                                <div class="tw-text-[10px] tw-text-gray-500">{{ $order->craftsman ? $order->craftsman->craftman_code : 'No Code' }}</div>
                                            </div>
                                        </div>
                                    </td> -->
                                        <td class="tw-px-4 tw-py-4 tw-text-right">
                                            <div class="tw-flex tw-justify-end tw-gap-1">
                                                <a href="{{ route('super-admin.work-order.show', $order) }}" class="tw-p-2 tw-text-sky-600 hover:tw-bg-sky-50 tw-rounded-lg tw-transition-colors" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('super-admin.work-order.edit', ['workOrder' => $order->id, 'return_url' => url()->full()]) }}" class="tw-p-2 tw-text-amber-600 hover:tw-bg-amber-50 tw-rounded-lg tw-transition-colors" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="{{ route('super-admin.work-order.reallocate.form', $order) }}" class="tw-p-2 tw-text-orange-600 hover:tw-bg-orange-50 tw-rounded-lg tw-transition-colors" title="Reallocate">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </a>
                                            </div>
                                        </td>
                                        </tr>
                                        @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="tw-px-6 tw-py-4 tw-bg-gray-50/50 tw-border-t tw-border-gray-100 tw-flex tw-justify-end">
                        {{ $allocatedOrders->appends(array_merge(request()->query(), ['tab' => 'allocated-orders']))->links('vendor.pagination.custom-pagination') }}
                    </div>
                    @else
                    <div class="tw-text-center tw-py-16">
                        <div class="tw-w-16 tw-h-16 tw-bg-gray-50 tw-text-gray-300 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                            <i class="bi bi-person-check tw-text-3xl"></i>
                        </div>
                        <h4 class="tw-text-gray-900 tw-font-semibold tw-mb-1">{{ __('messages.no_records_found') }}</h4>
                        <p class="tw-text-gray-500 tw-text-sm">No allocated work orders found.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- In Process Orders Tab -->
        @if($activeTab == 'in-process-orders')
        <div class="tab-pane fade show active" id="in-process-orders" role="tabpanel">
            <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-shadow-sm tw-overflow-hidden tw-mt-4">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-100 tw-bg-gray-50/50 tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-4">
                    <div class="tw-flex tw-items-center tw-gap-4 tw-flex-1">
                        <form method="GET" class="tw-relative tw-max-w-xs tw-w-full">
                            <input type="hidden" name="tab" value="in-process-orders">
                            <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                            <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                            <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                                <i class="bi bi-search tw-text-gray-400"></i>
                            </div>
                            <input type="text" name="search" class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-2 tw-border tw-border-gray-200 tw-rounded-lg tw-leading-5 tw-bg-white placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-sky-500 focus:tw-border-transparent tw-text-sm tw-transition-all" placeholder="Search in process..." value="{{ request('search') }}">
                        </form>

                        <div class="tw-flex tw-items-center tw-gap-2">
                            <span class="tw-text-xs tw-font-medium tw-text-gray-500">Sort:</span>
                            <form method="GET" class="tw-flex tw-items-center tw-gap-1">
                                <input type="hidden" name="tab" value="in-process-orders">
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                                <select name="sort_by" class="tw-text-xs tw-border-gray-200 tw-rounded-lg tw-bg-white focus:tw-ring-sky-500 tw-py-1" onchange="this.form.submit()">
                                    <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>ID</option>
                                    <option value="work_order_number" {{ request('sort_by') == 'work_order_number' ? 'selected' : '' }}>WO #</option>
                                    <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>Order Date</option>
                                    <option value="craftsman_due_date" {{ request('sort_by') == 'craftsman_due_date' ? 'selected' : '' }}>Due Date</option>
                                </select>
                                <select name="sort_order" class="tw-text-xs tw-border-gray-200 tw-rounded-lg tw-bg-white focus:tw-ring-sky-500 tw-py-1" onchange="this.form.submit()">
                                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Asc</option>
                                    <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Desc</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <div class="tw-flex tw-items-center tw-gap-3">
                        <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-amber-100 tw-text-amber-800">
                            {{ $inProcessOrders->total() }} Orders
                        </span>
                        <form method="GET" class="tw-flex tw-items-center tw-gap-2">
                            <input type="hidden" name="tab" value="allocated-orders">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                            <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                            <select name="per_page" class="tw-text-xs tw-border-gray-200 tw-rounded-lg tw-bg-white focus:tw-ring-sky-500 tw-py-1" onchange="this.form.submit()">
                                @foreach([25, 50, 75, 100, 150, 200] as $size)
                                <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <div class="tw-p-0">
                    @if($inProcessOrders->count() > 0)
                    <div class="tw-px-6 tw-py-4 tw-bg-gray-50/50 tw-border-b tw-border-gray-100 tw-flex tw-items-center tw-gap-3">
                        <button type="button" onclick="submitBulkComplete()" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-emerald-600 hover:tw-bg-emerald-700 tw-text-white tw-text-sm tw-font-semibold tw-rounded-lg tw-shadow-sm tw-transition-all tw-duration-200">
                            <i class="bi bi-check-all tw-mr-2"></i> Bulk Complete
                        </button>
                    </div>
                    <div class="">
                        <table class="tw-w-full tw-text-sm tw-text-left">
                            <thead class="tw-text-sm tw-text-gray-900 tw-uppercase tw-bg-slate-100 tw-border-b-2 tw-border-gray-200">
                                <tr>
                                    <th class="tw-px-6 tw-py-5">
                                        <input type="checkbox" id="select-all-in-process-orders"
                                            class="tw-w-4 tw-h-4 tw-rounded tw-border-gray-300 tw-text-primary focus:tw-ring-primary">
                                    </th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Images</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Order Details</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Customer</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Dates</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Specs</th>
                                    <!--<th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Craftsman</th>-->
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider tw-text-right">
                                        {{ __('messages.actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <!-- <thead class="tw-text-xs tw-text-gray-500 tw-uppercase tw-bg-gray-50 tw-border-b tw-border-gray-100">
                                <tr>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold" width="40">
                                        <input type="checkbox" id="select-all-in-process-orders" class="tw-rounded tw-border-gray-300 tw-text-sky-600 focus:tw-ring-sky-500">
                                    </th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">{{ __('messages.image') }}</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">{{ __('messages.order_details') }}</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">{{ __('messages.customer') }}</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">Dates</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">Specs</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">Craftsman</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold tw-text-right">Action</th>
                                </tr>
                            </thead> -->
                            <tbody class="tw-divide-y tw-divide-gray-50">
                                @foreach($inProcessOrders as $order)
                                @php
                                $rowStyle = '';
                                $isOverdue = false;
                                $isDueWithin48Hours = false;
                                $allocatedWithin48Hours = false;
                                $now = \Carbon\Carbon::now();

                                // For PO and WorkOrder
                                $dueDateValue = null;
                                if (isset($order) && isset($order->craftsman_due_date)) {
                                $dueDateValue = $order->craftsman_due_date;
                                } elseif (isset($order) && isset($order->due_date)) {
                                $dueDateValue = $order->due_date;
                                } elseif (isset($po) && isset($po->due_date)) {
                                $dueDateValue = $po->due_date;
                                }

                                if ($dueDateValue) {
                                $dueDate = \Carbon\Carbon::parse($dueDateValue);
                                if ($dueDate->lt($now->startOfDay()) || ($dueDate->isToday() && $now->hour >= 12)) {
                                $isOverdue = true;
                                } else {
                                $hoursDiff = $now->diffInHours($dueDate, false);
                                if ($hoursDiff >= 0 && $hoursDiff <= 48) {
                                    $isDueWithin48Hours=true;
                                    }
                                    }
                                    }

                                    // Handle updated_at for allocated within 48h
                                    $updatedAtValue=null;
                                    if (isset($order) && isset($order->updated_at)) {
                                    $updatedAtValue = $order->updated_at;
                                    } elseif (isset($po) && isset($po->updated_at)) {
                                    $updatedAtValue = $po->updated_at;
                                    }

                                    $currentTabString = '';
                                    if (isset($activeTab)) {
                                    $currentTabString = $activeTab;
                                    } elseif (isset($currentTab)) {
                                    $currentTabString = $currentTab;
                                    } elseif (isset($tab['id'])) {
                                    $currentTabString = $tab['id'];
                                    }

                                    if (($currentTabString == 'allocated-orders' || $currentTabString == 'allocated') && $updatedAtValue) {
                                    if (\Carbon\Carbon::parse($updatedAtValue)->diffInHours($now) <= 48) {
                                        $allocatedWithin48Hours=true;
                                        }
                                        }

                                        if ($isOverdue) {
                                        $rowStyle='background-color: rgba(255, 228, 230, 0.8) !important;' ; // rose
                                        } elseif ($isDueWithin48Hours) {
                                        $rowStyle='background-color: rgba(255, 237, 213, 0.8) !important;' ; // orange
                                        } elseif ($currentTabString=='in-process-orders' || $currentTabString=='in-process' || $currentTabString=='in_process' ) {
                                        $rowStyle='background-color: rgba(220, 252, 231, 0.8) !important;' ; // green
                                        } elseif (($currentTabString=='allocated-orders' || $currentTabString=='allocated' ) && $allocatedWithin48Hours) {
                                        $rowStyle='background-color: rgba(219, 234, 254, 0.8) !important;' ; // blue
                                        } elseif ($currentTabString=='new-orders' || $currentTabString=='created' ) {
                                        $rowStyle='background-color: rgba(254, 252, 232, 0.8) !important;' ; // yellow
                                        }
                                        @endphp
                                        <tr class="hover:tw-bg-gray-50 tw-transition-colors  " style="{{ $rowStyle }}">
                                        <td class="tw-px-4 tw-py-4">
                                            <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="in-process-order-checkbox tw-rounded tw-border-gray-300 tw-text-sky-600 focus:tw-ring-sky-500">
                                        </td>
                                        <td class="tw-px-4 tw-py-4">
                                            <div class="tw-flex tw-items-center tw-gap-3">
                                                @php
                                                $displayImage = $order->product_image;
                                                $isPdf = false;

                                                if ($displayImage) {
                                                $isPdf = Str::endsWith(strtolower($displayImage), '.pdf');
                                                if (!Str::startsWith($displayImage, ['http', 'storage/', 'images/', 'uploads/'])) {
                                                $displayImage = 'storage/' . $displayImage;
                                                }
                                                } elseif ($order->product && $order->product->images->isNotEmpty()) {
                                                $displayImage = $order->product->images->first()->path;
                                                $isPdf = Str::endsWith(strtolower($displayImage), '.pdf');
                                                if (!Str::startsWith($displayImage, ['http', 'storage/', 'images/', 'uploads/'])) {
                                                $displayImage = 'storage/' . $displayImage;
                                                }
                                                }
                                                @endphp
                                                @if($displayImage)
                                                <div class="tw-relative tw-w-12 tw-h-12 tw-rounded-lg tw-border tw-border-gray-100 tw-overflow-hidden tw-bg-white tw-cursor-pointer tw-flex-shrink-0" onclick="openUniversalPreview('{{ asset($displayImage) }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                    @if($isPdf)
                                                    <canvas class="pdf-canvas tw-w-full tw-h-full tw-object-contain" data-url="{{ asset($displayImage) }}"></canvas>
                                                    @else
                                                    <img src="{{ asset($displayImage) }}" class="tw-w-full tw-h-full tw-object-cover">
                                                    @endif
                                                    @if($order->product && $order->product->images->count() > 1)
                                                    <div class="tw-absolute tw-bottom-0 tw-right-0 tw-bg-black/60 tw-text-white tw-text-[8px] tw-px-1 tw-rounded-tl-md">+{{ $order->product->images->count() - 1 }}</div>
                                                    @endif
                                                </div>
                                                @else
                                                <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-bg-gray-50 tw-flex tw-items-center tw-justify-center tw-text-gray-300 tw-border tw-border-dashed tw-border-gray-200">
                                                    <i class="bi bi-image tw-text-xl"></i>
                                                </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="tw-px-4 tw-py-4">
                                            <div class="tw-font-extrabold tw-text-gray-900 tw-uppercase tw-tracking-tight tw-text-lg">
                                                {{ $order->work_order_number }}
                                            </div>

                                            <div class="tw-mt-2">
                                                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-1 tw-rounded-md tw-bg-gray-100 tw-text-xs tw-font-bold tw-text-gray-700 tw-border tw-border-gray-200">
                                                    <span class="tw-text-gray-400 tw-mr-1">REF:</span>
                                                    {{ $order_details->reference_no ?? ($order->reference_no ?? '-') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="tw-px-4 tw-py-4">
                                            <div class="tw-text-[14px] tw-text-red-600 tw-font-bold tw-mt-1">{{ $order->customer_name }}</div>
                                            <div class="tw-text-[14px] tw-text-red-600 tw-font-bold tw-mt-1">{{ $order->bp_code ?? 'NO BP' }}</div>
                                            <div class="tw-text-[14px] tw-text-black-600 tw-font-bold tw-mt-1">{{ $order->craftsman ? $order->craftsman->craftman_code : 'No Code' }}</div>
                                            <div class="tw-text-[14px] tw-text-black-600 tw-font-bold tw-mt-1">{{ $order->craftsman ? $order->craftsman->name : 'No name' }}</div>
                                        </td>
                                        <td class="tw-px-4 tw-py-4">
                                            <div class="tw-flex tw-flex-col tw-gap-2">
                                                <div class="tw-flex tw-items-center tw-gap-1.5 tw-text-[11px]">
                                                    <span class="tw-text-gray-400 tw-w-8 tw-font-bold">ORD:</span>
                                                    <span class="tw-text-gray-600 tw-font-medium">
                                                        {{ $order->created_at ? $order->created_at->format('d M, Y') : '-' }}
                                                    </span>
                                                </div>

                                                <div class="tw-flex tw-items-center tw-gap-1.5">
                                                    <span class="tw-text-gray-400 tw-w-8 tw-text-[11px] tw-font-bold">DUE:</span>
                                                    <span class="tw-px-2 tw-py-0.5 tw-rounded tw-text-sm tw-font-extrabold shadow-sm
                {{ $isOverdue 
                    ? 'tw-bg-rose-50 tw-text-rose-700 tw-border tw-border-rose-200' 
                    : 'tw-bg-emerald-50 tw-text-emerald-700 tw-border tw-border-emerald-200' 
                }}">
                                                        {{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'NOT SET' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="tw-px-4 tw-py-4">
                                            <div class="tw-flex tw-flex-col tw-gap-2.5">

                                                <div class="tw-bg-blue-50 tw-border tw-border-blue-100 tw-rounded-md tw-p-2">
                                                    <div class="tw-text-[11px] tw-leading-tight">
                                                        <span class="tw-text-blue-400 tw-font-bold tw-uppercase">CAT:</span>
                                                        <span class="tw-text-blue-900 tw-font-extrabold tw-uppercase tw-text-xs">{{ $order->product_category ?: '-' }}</span>
                                                    </div>
                                                    <div class="tw-text-[11px] tw-mt-0.5">
                                                        <span class="tw-text-blue-400 tw-font-bold tw-uppercase">SUB:</span>
                                                        <span class="tw-text-blue-800 tw-font-semibold">{{ $order->subcategory ?: '-' }}</span>
                                                    </div>
                                                    <div class="tw-text-[11px] tw-mt-0.5">
                                                        <span class="tw-text-blue-400 tw-font-bold tw-uppercase">SIZE:</span>
                                                        <span class="tw-text-blue-800 tw-font-semibold">{{ $order->size ?: '-' }}</span>
                                                    </div>
                                                </div>

                                                <div class="tw-flex tw-items-center tw-gap-2 tw-bg-amber-50 tw-border tw-border-amber-200 tw-rounded tw-px-2 tw-py-1.5 tw-w-fit">
                                                    <span class="tw-text-sm tw-font-black tw-text-amber-700">
                                                        {{ $order->quantity }} {{ $order->type ?: 'Piece' }}
                                                    </span>
                                                    <span class="tw-text-amber-300">|</span>
                                                    <span class="tw-text-xs tw-font-bold tw-text-amber-600">
                                                        {{ $order->weight_from ?: '-' }} g
                                                    </span>
                                                </div>

                                                <div class="tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded tw-p-2">
                                                    <div class="tw-text-[10px] tw-text-slate-400 tw-font-bold tw-uppercase tw-mb-1">Craftsman Notes:</div>
                                                    <div class="tw-text-sm tw-text-slate-700 tw-font-medium tw-italic tw-leading-snug">
                                                        "{{ $order->narration_craftsman ?: '-' }}"
                                                    </div>
                                                </div>
@if($order->return_note)
<div class="tw-bg-amber-50 tw-border tw-border-amber-200 tw-rounded tw-p-2 tw-mb-2">
    <div class="tw-text-[10px] tw-text-amber-700 tw-font-bold tw-uppercase tw-mb-1">Return Notes:</div>
    <div class="tw-text-sm tw-text-amber-900 tw-font-medium tw-italic tw-leading-snug">
        "{{ $order->return_note }}"
    </div>
</div>
@endif

@if($order->return_due_date)
<div class="tw-flex tw-items-center tw-gap-1.5 tw-text-[11px]">
    <span class="tw-text-slate-500 tw-w-8 tw-font-bold">RDUE:</span>
    <span class="tw-text-rose-600 tw-font-semibold">
        {{ $order->return_due_date->format('d M, Y') }}
    </span>
</div>
@endif
                                            </div>
                                        </td>
                                        <!--<td class="tw-px-4 tw-py-4">-->
                                        <!--    <div class="tw-flex tw-items-center tw-gap-2">-->
                                        <!--        <div class="tw-w-8 tw-h-8 tw-rounded-full tw-bg-amber-100 tw-text-amber-700 tw-flex tw-items-center tw-justify-center tw-font-bold tw-text-xs">-->
                                        <!--            {{ $order->craftsman ? substr($order->craftsman->name, 0, 1) : '?' }}-->
                                        <!--        </div>-->
                                        <!--        <div>-->
                                        <!--            <div class="tw-text-xs tw-font-semibold tw-text-gray-900">{{ $order->craftsman ? $order->craftsman->name : 'N/A' }}</div>-->
                                        <!--            <div class="tw-text-[10px] tw-text-gray-500">{{ $order->craftsman ? $order->craftsman->craftman_code : 'No Code' }}</div>-->
                                        <!--        </div>-->
                                        <!--    </div>-->
                                        <!--</td>-->
                                        <td class="tw-px-4 tw-py-4 tw-text-right">
                                            <div class="tw-flex tw-justify-end tw-gap-1">
                                                <button type="button" onclick="openSuperAdminUndoModal({{ $order->id }}, {{ $order->superadmin_undo_count }})" class="tw-p-2 tw-text-purple-600 hover:tw-bg-purple-50 tw-rounded-lg tw-transition-colors" title="Undo Status">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                                <a href="{{ route('super-admin.work-order.show', $order) }}" class="tw-p-2 tw-text-sky-600 hover:tw-bg-sky-50 tw-rounded-lg tw-transition-colors tw-inline-block" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                        </tr>
                                        @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="tw-px-6 tw-py-4 tw-bg-gray-50/50 tw-border-t tw-border-gray-100 tw-flex tw-justify-end">
                        {{ $inProcessOrders->appends(array_merge(request()->query(), ['tab' => 'in-process-orders']))->links('vendor.pagination.custom-pagination') }}
                    </div>
                    @else
                    <div class="tw-text-center tw-py-16">
                        <div class="tw-w-16 tw-h-16 tw-bg-gray-50 tw-text-gray-300 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                            <i class="bi bi-play-circle tw-text-3xl"></i>
                        </div>
                        <h4 class="tw-text-gray-900 tw-font-semibold tw-mb-1">{{ __('messages.no_records_found') }}</h4>
                        <p class="tw-text-gray-500 tw-text-sm">No work orders in process.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Rejected Orders Tab -->
        @if($activeTab == 'rejected-orders')
        <div class="tab-pane fade show active" id="rejected-orders" role="tabpanel">
            <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-shadow-sm tw-overflow-hidden tw-mt-4">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-100 tw-bg-gray-50/50 tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-4">
                    <div class="tw-flex tw-items-center tw-gap-4 tw-flex-1">
                        <form method="GET" class="tw-relative tw-max-w-xs tw-w-full">
                            <input type="hidden" name="tab" value="rejected-orders">
                            <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                            <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                            <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                                <i class="bi bi-search tw-text-gray-400"></i>
                            </div>
                            <input type="text" name="search" class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-2 tw-border tw-border-gray-200 tw-rounded-lg tw-leading-5 tw-bg-white placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-rose-500 focus:tw-border-transparent tw-text-sm tw-transition-all" placeholder="Search rejected..." value="{{ request('search') }}">
                        </form>
                    </div>

                    <div class="tw-flex tw-items-center tw-gap-3">
                        <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-rose-100 tw-text-rose-800">
                            {{ $rejectedOrders->total() }} Rejected
                        </span>
                        <form method="GET" class="tw-flex tw-items-center tw-gap-2">
                            <input type="hidden" name="tab" value="rejected-orders">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <select name="per_page" class="tw-text-xs tw-border-gray-200 tw-rounded-lg tw-bg-white focus:tw-ring-rose-500 tw-py-1" onchange="this.form.submit()">
                                @foreach([25, 50, 75, 100, 150, 200] as $size)
                                <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <div class="tw-relative">
                    @if($rejectedOrders->count() > 0)
                    <form id="bulk-reallocate-form" method="GET" action="{{ route('super-admin.work-order.bulk-allocate-form') }}">
                        <div class="">
                            <table class="tw-w-full tw-text-sm tw-text-left">
                                <thead class="tw-text-sm tw-text-gray-900 tw-uppercase tw-bg-slate-100 tw-border-b-2 tw-border-gray-200">
                                    <tr>
                                        <th class="tw-px-6 tw-py-5">
                                            <input type="checkbox" id="select-all-rejected-orders"
                                                class="tw-w-4 tw-h-4 tw-rounded tw-border-gray-300 tw-text-primary focus:tw-ring-primary">
                                        </th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Images</th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Order Details</th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Customer</th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Dates</th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Specs</th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Rejected By</th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Reason</th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider tw-text-right">
                                            {{ __('messages.actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <!-- <thead class="tw-text-xs tw-text-gray-500 tw-uppercase tw-bg-gray-50 tw-border-b tw-border-gray-100">
                                    <tr>
                                        <th class="tw-px-4 tw-py-3 tw-font-semibold" width="40">
                                            <input type="checkbox" id="select-all-rejected-orders" class="tw-rounded tw-border-gray-300 tw-text-rose-600 focus:tw-ring-rose-500">
                                        </th>
                                        <th class="tw-px-4 tw-py-3 tw-font-semibold">{{ __('messages.image') }}</th>
                                        <th class="tw-px-4 tw-py-3 tw-font-semibold">{{ __('messages.order_details') }}</th>
                                        <th class="tw-px-4 tw-py-3 tw-font-semibold">{{ __('messages.customer') }}</th>
                                        <th class="tw-px-4 tw-py-3 tw-font-semibold">Dates</th>
                                        <th class="tw-px-4 tw-py-3 tw-font-semibold">Specs</th>
                                        <th class="tw-px-4 tw-py-3 tw-font-semibold">Rejected By</th>
                                        <th class="tw-px-4 tw-py-3 tw-font-semibold">Reason</th>
                                        <th class="tw-px-4 tw-py-3 tw-font-semibold tw-text-right">Actions</th>
                                    </tr>
                                </thead> -->
                                <tbody class="tw-divide-y tw-divide-gray-50">
                                    @foreach($rejectedOrders as $order)
                                    <tr class="hover:tw-bg-gray-50/80 tw-transition-colors">
                                        <td class="tw-px-4 tw-py-4">
                                            <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="rejected-order-checkbox tw-rounded tw-border-gray-300 tw-text-rose-600 focus:tw-ring-rose-500">
                                        </td>
                                        <td class="tw-px-4 tw-py-4">
                                            <div class="tw-flex tw-items-center tw-gap-3">
                                                @php
                                                $displayImage = $order->product_image;
                                                $isPdf = false;

                                                if ($displayImage) {
                                                $isPdf = Str::endsWith(strtolower($displayImage), '.pdf');
                                                if (!Str::startsWith($displayImage, ['http', 'storage/', 'images/', 'uploads/'])) {
                                                $displayImage = 'storage/' . $displayImage;
                                                }
                                                } elseif ($order->product && $order->product->images->isNotEmpty()) {
                                                $displayImage = $order->product->images->first()->path;
                                                $isPdf = Str::endsWith(strtolower($displayImage), '.pdf');
                                                if (!Str::startsWith($displayImage, ['http', 'storage/', 'images/', 'uploads/'])) {
                                                $displayImage = 'storage/' . $displayImage;
                                                }
                                                }
                                                @endphp
                                                @if($displayImage)
                                                <div class="tw-relative tw-w-12 tw-h-12 tw-rounded-lg tw-border tw-border-gray-100 tw-overflow-hidden tw-bg-white tw-cursor-pointer tw-flex-shrink-0" onclick="openUniversalPreview('{{ asset($displayImage) }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                    @if($isPdf)
                                                    <canvas class="pdf-canvas tw-w-full tw-h-full tw-object-contain" data-url="{{ asset($displayImage) }}"></canvas>
                                                    @else
                                                    <img src="{{ asset($displayImage) }}" class="tw-w-full tw-h-full tw-object-cover">
                                                    @endif
                                                </div>
                                                @else
                                                <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-bg-gray-50 tw-flex tw-items-center tw-justify-center tw-text-gray-300 tw-border tw-border-dashed tw-border-gray-200">
                                                    <i class="bi bi-image tw-text-xl"></i>
                                                </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="tw-px-4 tw-py-4">
                                            <div class="tw-font-extrabold tw-text-gray-900 tw-uppercase tw-tracking-tight tw-text-lg">
                                                {{ $order->work_order_number }}
                                            </div>

                                            <div class="tw-mt-2">
                                                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-1 tw-rounded-md tw-bg-gray-100 tw-text-xs tw-font-bold tw-text-gray-700 tw-border tw-border-gray-200">
                                                    <span class="tw-text-gray-400 tw-mr-1">REF:</span>
                                                    {{ $order_details->reference_no ?? ($order->reference_no ?? '-') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="tw-px-4 tw-py-4">
                                            <div class="tw-text-gray-900 tw-font-bold tw-text-xs">{{ $order->customer_name }}</div>
                                            <div class="tw-text-[14px] tw-text-primary tw-font-bold tw-mt-1">{{ $order->bp_code ?? 'NO BP' }}</div>
                                            <!-- @if($order->buyer)
                                            <div class="tw-text-[10px] tw-text-gray-400 tw-mt-0.5">{{ $order->buyer->dear }}</div>
                                            @endif -->
                                        </td>
                                        <td class="tw-px-4 tw-py-4">
                                            <div class="tw-flex tw-flex-col tw-gap-2">
                                                <div class="tw-flex tw-items-center tw-gap-1.5 tw-text-[11px]">
                                                    <span class="tw-text-gray-400 tw-w-8 tw-font-bold">ORD:</span>
                                                    <span class="tw-text-gray-600 tw-font-medium">
                                                        {{ $order->created_at ? $order->created_at->format('d M, Y') : '-' }}
                                                    </span>
                                                </div>

                                                <div class="tw-flex tw-items-center tw-gap-1.5">
                                                    <span class="tw-text-gray-400 tw-w-8 tw-text-[11px] tw-font-bold">DUE:</span>

                                                    {{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'NOT SET' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="tw-px-4 tw-py-4">
                                            <div class="tw-flex tw-flex-col tw-gap-2.5">

                                                <div class="tw-bg-blue-50 tw-border tw-border-blue-100 tw-rounded-md tw-p-2">
                                                    <div class="tw-text-[11px] tw-leading-tight">
                                                        <span class="tw-text-blue-400 tw-font-bold tw-uppercase">CAT:</span>
                                                        <span class="tw-text-blue-900 tw-font-extrabold tw-uppercase tw-text-xs">{{ $order->product_category ?: '-' }}</span>
                                                    </div>
                                                    <div class="tw-text-[11px] tw-mt-0.5">
                                                        <span class="tw-text-blue-400 tw-font-bold tw-uppercase">SUB:</span>
                                                        <span class="tw-text-blue-800 tw-font-semibold">{{ $order->subcategory ?: '-' }}</span>
                                                    </div>
                                                    <div class="tw-text-[11px] tw-mt-0.5">
                                                        <span class="tw-text-blue-400 tw-font-bold tw-uppercase">SIZE:</span>
                                                        <span class="tw-text-blue-800 tw-font-semibold">{{ $order->size ?: '-' }}</span>
                                                    </div>
                                                </div>

                                                <div class="tw-flex tw-items-center tw-gap-2 tw-bg-amber-50 tw-border tw-border-amber-200 tw-rounded tw-px-2 tw-py-1.5 tw-w-fit">
                                                    <span class="tw-text-sm tw-font-black tw-text-amber-700">
                                                        {{ $order->quantity }} {{ $order->type ?: 'Piece' }}
                                                    </span>
                                                    <span class="tw-text-amber-300">|</span>
                                                    <span class="tw-text-xs tw-font-bold tw-text-amber-600">
                                                        {{ $order->weight_from ?: '-' }} g
                                                    </span>
                                                </div>

                                                <div class="tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded tw-p-2">
                                                    <div class="tw-text-[10px] tw-text-slate-400 tw-font-bold tw-uppercase tw-mb-1">Craftsman Notes:</div>
                                                    <div class="tw-text-sm tw-text-slate-700 tw-font-medium tw-italic tw-leading-snug">
                                                        "{{ $order->narration_craftsman ?: '-' }}"
                                                    </div>
                                                </div>

                                            </div>
                                        </td>
                                        <td class="tw-px-4 tw-py-4">
                                            <div class="tw-flex tw-items-center tw-gap-2">
                                                <div class="tw-w-7 tw-h-7 tw-rounded-full tw-bg-rose-100 tw-text-rose-700 tw-flex tw-items-center tw-justify-center tw-font-bold tw-text-[10px]">
                                                    {{ $order->craftsman ? substr($order->craftsman->name, 0, 1) : '?' }}
                                                </div>
                                                <div class="tw-text-xs tw-text-gray-700">{{ $order->craftsman ? $order->craftsman->name : 'N/A' }}</div>
                                            </div>
                                        </td>
                                        <td class="tw-px-4 tw-py-4">
                                            <div class="tw-text-xs tw-text-rose-500 tw-italic tw-font-medium">"{{ $order->rejection_reason ?? 'No reason provided' }}"</div>
                                        </td>
                                        <td class="tw-px-4 tw-py-4 tw-text-right">
                                            <div class="tw-flex tw-justify-end tw-gap-1">
                                                <a href="{{ route('super-admin.work-order.show', $order) }}" class="tw-p-2 tw-text-sky-600 hover:tw-bg-sky-50 tw-rounded-lg tw-transition-colors" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('super-admin.work-order.reallocate.form', $order) }}" class="tw-p-2 tw-text-amber-600 hover:tw-bg-amber-50 tw-rounded-lg tw-transition-colors" title="Reallocate">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="tw-px-6 tw-py-4 tw-bg-white tw-border-t tw-border-gray-100 tw-flex tw-items-center tw-justify-between">
                            <div>
                                <button type="submit" class="tw-inline-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2 tw-bg-amber-600 tw-text-white tw-text-sm tw-font-semibold tw-rounded-lg hover:tw-bg-amber-700 tw-transition-all disabled:tw-opacity-50 disabled:tw-cursor-not-allowed tw-shadow-sm" id="bulk-reallocate-btn" disabled>
                                    <i class="bi bi-arrow-repeat"></i> Bulk Reallocate
                                </button>
                            </div>
                            <div>
                                {{ $rejectedOrders->appends(array_merge(request()->query(), ['tab' => 'rejected-orders']))->links('vendor.pagination.custom-pagination') }}
                            </div>
                        </div>
                    </form>
                    @else
                    <div class="tw-text-center tw-py-16">
                        <div class="tw-w-16 tw-h-16 tw-bg-gray-50 tw-text-gray-300 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                            <i class="bi bi-x-circle tw-text-3xl"></i>
                        </div>
                        <h4 class="tw-text-gray-900 tw-font-semibold tw-mb-1">{{ __('messages.no_records_found') }}</h4>
                        <p class="tw-text-gray-500 tw-text-sm">No rejected work orders found.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Overdue Orders Tab -->
        @if($activeTab == 'overdue-orders')
        <div class="tab-pane fade show active" id="overdue-orders" role="tabpanel">
            <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-shadow-sm tw-overflow-hidden tw-mt-4">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-100 tw-bg-gray-50/50 tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-4">
                    <div class="tw-flex tw-items-center tw-gap-4 tw-flex-1">
                        <form method="GET" class="tw-relative tw-max-w-xs tw-w-full">
                            <input type="hidden" name="tab" value="overdue-orders">
                            <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                            <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                            <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                                <i class="bi bi-search tw-text-gray-400"></i>
                            </div>
                            <input type="text" name="search" class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-2 tw-border tw-border-gray-200 tw-rounded-lg tw-leading-5 tw-bg-white placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-red-500 focus:tw-border-transparent tw-text-sm tw-transition-all" placeholder="Search overdue..." value="{{ request('search') }}">
                        </form>
                    </div>

                    <div class="tw-flex tw-items-center tw-gap-3">
                        <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-red-100 tw-text-red-800 tw-animate-pulse">
                            {{ $overdueOrders->total() }} Overdue
                        </span>
                        <form method="GET" class="tw-flex tw-items-center tw-gap-2">
                            <input type="hidden" name="tab" value="overdue-orders">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <select name="per_page" class="tw-text-xs tw-border-gray-200 tw-rounded-lg tw-bg-white focus:tw-ring-red-500 tw-py-1" onchange="this.form.submit()">
                                @foreach([25, 50, 75, 100, 150, 200] as $size)
                                <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <div class="tw-relative">
                    @if($overdueOrders->count() > 0)
                    <div class="tw-px-6 tw-py-4 tw-bg-gray-50/50 tw-border-b tw-border-gray-100 tw-flex tw-items-center tw-gap-3">
                        <button type="button" onclick="submitBulkComplete()" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-emerald-600 hover:tw-bg-emerald-700 tw-text-white tw-text-sm tw-font-semibold tw-rounded-lg tw-shadow-sm tw-transition-all tw-duration-200">
                            <i class="bi bi-check-all tw-mr-2"></i> Bulk Complete
                        </button>
                    </div>
                    <div class="">
                        <table class="tw-w-full tw-text-sm tw-text-left">
                            <thead class="tw-text-sm tw-text-gray-900 tw-uppercase tw-bg-slate-100 tw-border-b-2 tw-border-gray-200">
                                <tr>
                                    <th class="tw-px-6 tw-py-5">
                                        <input type="checkbox" id="select-all-overdue-orders"
                                            class="tw-w-4 tw-h-4 tw-rounded tw-border-gray-300 tw-text-primary focus:tw-ring-primary">
                                    </th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Images</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Order Details</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Customer</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Dates</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Specs</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Craftsman</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider tw-text-right">
                                        {{ __('messages.actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <!-- <thead class="tw-text-xs tw-text-gray-500 tw-uppercase tw-bg-gray-50 tw-border-b tw-border-gray-100">
                                <tr>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold" width="40">
                                        <input type="checkbox" id="select-all-overdue-orders" class="tw-rounded tw-border-gray-300 tw-text-red-600 focus:tw-ring-red-500">
                                    </th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">{{ __('messages.image') }}</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">{{ __('messages.order_details') }}</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">{{ __('messages.customer') }}</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">Dates</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">Specs</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">Status</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">Craftsman</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold tw-text-right">Actions</th>
                                </tr>
                            </thead> -->
                            <tbody class="tw-divide-y tw-divide-red-50">
                                @foreach($overdueOrders as $order)
                                @php
                                $daysOverdue = 0;
                                if ($order->craftsman_due_date) {
                                $due = \Carbon\Carbon::parse($order->craftsman_due_date);
                                $daysOverdue = max(0, $due->diffInDays(\Carbon\Carbon::now(), false));
                                }
                                @endphp
                                <tr class="tw-bg-red-500/20 hover:tw-bg-red-500/40 tw-transition-colors duration-300">
                                    <td class="tw-px-4 tw-py-4">
                                        <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="overdue-order-checkbox tw-rounded tw-border-gray-300 tw-text-red-600 focus:tw-ring-red-500">
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-flex tw-items-center tw-gap-3">
                                            @php
                                            $displayImage = $order->product_image;
                                            $isPdf = false;

                                            if ($displayImage) {
                                            $isPdf = Str::endsWith(strtolower($displayImage), '.pdf');
                                            if (!Str::startsWith($displayImage, ['http', 'storage/', 'images/', 'uploads/'])) {
                                            $displayImage = 'storage/' . $displayImage;
                                            }
                                            } elseif ($order->product && $order->product->images->isNotEmpty()) {
                                            $displayImage = $order->product->images->first()->path;
                                            $isPdf = Str::endsWith(strtolower($displayImage), '.pdf');
                                            if (!Str::startsWith($displayImage, ['http', 'storage/', 'images/', 'uploads/'])) {
                                            $displayImage = 'storage/' . $displayImage;
                                            }
                                            }
                                            @endphp
                                            @if($displayImage)
                                            <div class="tw-relative tw-w-12 tw-h-12 tw-rounded-lg tw-border tw-border-gray-100 tw-overflow-hidden tw-bg-white tw-cursor-pointer tw-flex-shrink-0" onclick="openUniversalPreview('{{ asset($displayImage) }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                @if($isPdf)
                                                <canvas class="pdf-canvas tw-w-full tw-h-full tw-object-contain" data-url="{{ asset($displayImage) }}"></canvas>
                                                @else
                                                <img src="{{ asset($displayImage) }}" class="tw-w-full tw-h-full tw-object-cover">
                                                @endif
                                            </div>
                                            @else
                                            <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-bg-gray-50 tw-flex tw-items-center tw-justify-center tw-text-gray-300 tw-border tw-border-dashed tw-border-gray-200">
                                                <i class="bi bi-image tw-text-xl"></i>
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-font-extrabold tw-text-gray-900 tw-uppercase tw-tracking-tight tw-text-lg">
                                            {{ $order->work_order_number }}
                                        </div>

                                        <div class="tw-mt-2">
                                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-1 tw-rounded-md tw-bg-gray-100 tw-text-xs tw-font-bold tw-text-gray-700 tw-border tw-border-gray-200">
                                                <span class="tw-text-gray-400 tw-mr-1">REF:</span>
                                                {{ $order_details->reference_no ?? ($order->reference_no ?? '-') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-text-gray-900 tw-font-bold tw-text-xs">{{ $order->customer_name }}</div>
                                        <div class="tw-text-[14px] tw-text-primary tw-font-bold tw-mt-1">{{ $order->bp_code ?? 'NO BP' }}</div>
                                        <!-- @if($order->buyer)
                                            <div class="tw-text-[10px] tw-text-gray-400 tw-mt-0.5">{{ $order->buyer->dear }}</div>
                                            @endif -->
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-flex tw-flex-col tw-gap-2">
                                            <div class="tw-flex tw-items-center tw-gap-1.5 tw-text-[11px]">
                                                <span class="tw-text-gray-400 tw-w-8 tw-font-bold">ORD:</span>
                                                <span class="tw-text-gray-600 tw-font-medium">
                                                    {{ $order->created_at ? $order->created_at->format('d M, Y') : '-' }}
                                                </span>
                                            </div>

                                            <div class="tw-flex tw-items-center tw-gap-1.5">
                                                <span class="tw-text-gray-400 tw-w-8 tw-text-[11px] tw-font-bold">DUE:</span>

                                                {{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'NOT SET' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-flex tw-flex-col tw-gap-2.5">

                                            <div class="tw-bg-blue-50 tw-border tw-border-blue-100 tw-rounded-md tw-p-2">
                                                <div class="tw-text-[11px] tw-leading-tight">
                                                    <span class="tw-text-blue-400 tw-font-bold tw-uppercase">CAT:</span>
                                                    <span class="tw-text-blue-900 tw-font-extrabold tw-uppercase tw-text-xs">{{ $order->product_category ?: '-' }}</span>
                                                </div>
                                                <div class="tw-text-[11px] tw-mt-0.5">
                                                    <span class="tw-text-blue-400 tw-font-bold tw-uppercase">SUB:</span>
                                                    <span class="tw-text-blue-800 tw-font-semibold">{{ $order->subcategory ?: '-' }}</span>
                                                </div>
                                                <div class="tw-text-[11px] tw-mt-0.5">
                                                    <span class="tw-text-blue-400 tw-font-bold tw-uppercase">SIZE:</span>
                                                    <span class="tw-text-blue-800 tw-font-semibold">{{ $order->size ?: '-' }}</span>
                                                </div>
                                            </div>

                                            <div class="tw-flex tw-items-center tw-gap-2 tw-bg-amber-50 tw-border tw-border-amber-200 tw-rounded tw-px-2 tw-py-1.5 tw-w-fit">
                                                <span class="tw-text-sm tw-font-black tw-text-amber-700">
                                                    {{ $order->quantity }} {{ $order->type ?: 'Piece' }}
                                                </span>
                                                <span class="tw-text-amber-300">|</span>
                                                <span class="tw-text-xs tw-font-bold tw-text-amber-600">
                                                    {{ $order->weight_from ?: '-' }} g
                                                </span>
                                            </div>

                                            <div class="tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded tw-p-2">
                                                <div class="tw-text-[10px] tw-text-slate-400 tw-font-bold tw-uppercase tw-mb-1">Craftsman Notes:</div>
                                                <div class="tw-text-sm tw-text-slate-700 tw-font-medium tw-italic tw-leading-snug">
                                                    "{{ $order->narration_craftsman ?: '-' }}"
                                                </div>
                                            </div>

                                        </div>
                                    </td>

                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-flex tw-items-center tw-gap-2">
                                            <div class="tw-w-7 tw-h-7 tw-rounded-full tw-bg-gray-100 tw-text-gray-700 tw-flex tw-items-center tw-justify-center tw-font-bold tw-text-[10px]">
                                                {{ $order->craftsman ? substr($order->craftsman->name, 0, 1) : '?' }}
                                            </div>
                                            <div class="tw-text-xs tw-text-gray-700">{{ $order->craftsman ? $order->craftsman->name : 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4 tw-text-right">
                                        <div class="tw-flex tw-justify-end tw-gap-1">
                                            <a href="{{ route('super-admin.work-order.show', $order) }}" class="tw-p-2 tw-text-sky-600 hover:tw-bg-sky-50 tw-rounded-lg tw-transition-colors" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('super-admin.work-order.edit', ['workOrder' => $order->id, 'return_url' => url()->full()]) }}" class="tw-p-2 tw-text-amber-600 hover:tw-bg-amber-50 tw-rounded-lg tw-transition-colors" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="tw-px-6 tw-py-4 tw-bg-gray-50/50 tw-border-t tw-border-gray-100 tw-flex tw-justify-end">
                        {{ $overdueOrders->appends(array_merge(request()->query(), ['tab' => 'overdue-orders']))->links('vendor.pagination.custom-pagination') }}
                    </div>
                    @else
                    <div class="tw-text-center tw-py-16">
                        <div class="tw-w-16 tw-h-16 tw-bg-emerald-50 tw-text-emerald-300 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                            <i class="bi bi-check2-circle tw-text-3xl"></i>
                        </div>
                        <h4 class="tw-text-gray-900 tw-font-semibold tw-mb-1">Great Job!</h4>
                        <p class="tw-text-gray-500 tw-text-sm">No overdue work orders found.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- For Approval Orders Tab -->
        @if($activeTab == 'for-approval-orders')
        <div class="tab-pane fade show active" id="for-approval-orders" role="tabpanel">
            <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-shadow-sm tw-overflow-hidden tw-mt-4">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-100 tw-bg-gray-50/50 tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-4">
                    <div class="tw-flex tw-items-center tw-gap-4 tw-flex-1">
                        <form method="GET" class="tw-relative tw-max-w-xs tw-w-full">
                            <input type="hidden" name="tab" value="for-approval-orders">
                            <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                            <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                            <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                                <i class="bi bi-search tw-text-gray-400"></i>
                            </div>
                            <input type="text" name="search" class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-2 tw-border tw-border-gray-200 tw-rounded-lg tw-leading-5 tw-bg-white placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-emerald-500 focus:tw-border-transparent tw-text-sm tw-transition-all" placeholder="Search pending approval..." value="{{ request('search') }}">
                        </form>
                    </div>

                    <div class="tw-flex tw-items-center tw-gap-3">
                        <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-emerald-100 tw-text-emerald-800">
                            {{ $forApprovalOrders->total() }} Pending
                        </span>
                        <form method="GET" class="tw-flex tw-items-center tw-gap-2">
                            <input type="hidden" name="tab" value="for-approval-orders">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <select name="per_page" class="tw-text-xs tw-border-gray-200 tw-rounded-lg tw-bg-white focus:tw-ring-emerald-500 tw-py-1" onchange="this.form.submit()">
                                @foreach([25, 50, 75, 100, 150, 200] as $size)
                                <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <div class="tw-relative">
                    @if($forApprovalOrders->count() > 0)
                    <form id="bulk-approve-form" method="POST" action="{{ route('super-admin.work-order.bulk-approve') }}">
                        @csrf
                        <div class="tw-px-6 tw-py-4 tw-bg-gray-50/50 tw-border-t tw-border-gray-100 tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-4">
                            <button type="submit" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-emerald-600 hover:tw-bg-emerald-700 tw-text-white tw-text-sm tw-font-semibold tw-rounded-lg tw-shadow-sm tw-transition-all tw-duration-200 disabled:tw-opacity-50 disabled:tw-cursor-not-allowed" id="bulk-approve-btn" disabled>
                                <i class="bi bi-check-circle tw-mr-2"></i> Bulk Approve
                            </button>
                        </div>
                        <div class="tw-overflow-x-auto">
                            <table class="tw-w-full tw-text-sm tw-text-left">
                                <thead class="tw-text-sm tw-text-gray-900 tw-uppercase tw-bg-slate-100 tw-border-b-2 tw-border-gray-200">
                                    <tr>
                                        <th class="tw-px-6 tw-py-5">
                                            <input type="checkbox" id="select-all-for-approval-orders"
                                                class="tw-w-4 tw-h-4 tw-rounded tw-border-gray-300 tw-text-primary focus:tw-ring-primary">
                                        </th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Images</th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Order Details</th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Customer</th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Dates</th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Specs</th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Craftsman</th>
                                        <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider tw-text-right">
                                            {{ __('messages.actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <!-- <thead class="tw-text-xs tw-text-gray-500 tw-uppercase tw-bg-gray-50 tw-border-b tw-border-gray-100">
                                    <tr>
                                        <th class="tw-px-4 tw-py-3 tw-font-semibold" width="40">
                                            <input type="checkbox" id="select-all-for-approval-orders" class="tw-rounded tw-border-gray-300 tw-text-emerald-600 focus:tw-ring-emerald-500">
                                        </th>
                                        <th class="tw-px-4 tw-py-3 tw-font-semibold">{{ __('messages.image') }}</th>
                                        <th class="tw-px-4 tw-py-3 tw-font-semibold">{{ __('messages.order_details') }}</th>
                                        <th class="tw-px-4 tw-py-3 tw-font-semibold">{{ __('messages.customer') }}</th>
                                        <th class="tw-px-4 tw-py-3 tw-font-semibold">Dates</th>
                                        <th class="tw-px-4 tw-py-3 tw-font-semibold">Specs</th>
                                        <th class="tw-px-4 tw-py-3 tw-font-semibold">Craftsman</th>
                                        <th class="tw-px-4 tw-py-3 tw-font-semibold tw-text-right">Actions</th>
                                    </tr>
                                </thead> -->
                                <tbody class="tw-divide-y tw-divide-gray-50">
                                    @foreach($forApprovalOrders as $order)
                                    @php
                                    $rowStyle = '';
                                    $isOverdue = false;
                                    $isDueWithin48Hours = false;
                                    $allocatedWithin48Hours = false;
                                    $now = \Carbon\Carbon::now();

                                    // For PO and WorkOrder
                                    $dueDateValue = null;
                                    if (isset($order) && isset($order->craftsman_due_date)) {
                                    $dueDateValue = $order->craftsman_due_date;
                                    } elseif (isset($order) && isset($order->due_date)) {
                                    $dueDateValue = $order->due_date;
                                    } elseif (isset($po) && isset($po->due_date)) {
                                    $dueDateValue = $po->due_date;
                                    }

                                    if ($dueDateValue) {
                                    $dueDate = \Carbon\Carbon::parse($dueDateValue);
                                    if ($dueDate->lt($now->startOfDay()) || ($dueDate->isToday() && $now->hour >= 12)) {
                                    $isOverdue = true;
                                    } else {
                                    $hoursDiff = $now->diffInHours($dueDate, false);
                                    if ($hoursDiff >= 0 && $hoursDiff <= 48) {
                                        $isDueWithin48Hours=true;
                                        }
                                        }
                                        }

                                        // Handle updated_at for allocated within 48h
                                        $updatedAtValue=null;
                                        if (isset($order) && isset($order->updated_at)) {
                                        $updatedAtValue = $order->updated_at;
                                        } elseif (isset($po) && isset($po->updated_at)) {
                                        $updatedAtValue = $po->updated_at;
                                        }

                                        $currentTabString = '';
                                        if (isset($activeTab)) {
                                        $currentTabString = $activeTab;
                                        } elseif (isset($currentTab)) {
                                        $currentTabString = $currentTab;
                                        } elseif (isset($tab['id'])) {
                                        $currentTabString = $tab['id'];
                                        }

                                        if (($currentTabString == 'allocated-orders' || $currentTabString == 'allocated') && $updatedAtValue) {
                                        if (\Carbon\Carbon::parse($updatedAtValue)->diffInHours($now) <= 48) {
                                            $allocatedWithin48Hours=true;
                                            }
                                            }

                                            if ($isOverdue) {
                                            $rowStyle='background-color: rgba(255, 228, 230, 0.8) !important;' ; // rose
                                            } elseif ($isDueWithin48Hours) {
                                            $rowStyle='background-color: rgba(255, 237, 213, 0.8) !important;' ; // orange
                                            } elseif ($currentTabString=='in-process-orders' || $currentTabString=='in-process' || $currentTabString=='in_process' ) {
                                            $rowStyle='background-color: rgba(220, 252, 231, 0.8) !important;' ; // green
                                            } elseif (($currentTabString=='allocated-orders' || $currentTabString=='allocated' ) && $allocatedWithin48Hours) {
                                            $rowStyle='background-color: rgba(219, 234, 254, 0.8) !important;' ; // blue
                                            } elseif ($currentTabString=='new-orders' || $currentTabString=='created' ) {
                                            $rowStyle='background-color: rgba(254, 252, 232, 0.8) !important;' ; // yellow
                                            }
                                            @endphp
                                            <tr class="hover:tw-bg-gray-50 tw-transition-colors  " style="{{ $rowStyle }}">
                                            <td class="tw-px-4 tw-py-4">
                                                <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="for-approval-order-checkbox tw-rounded tw-border-gray-300 tw-text-emerald-600 focus:tw-ring-emerald-500">
                                            </td>
                                            <td class="tw-px-4 tw-py-4">
                                                <div class="tw-flex tw-items-center tw-gap-3">
                                                    @php
                                                    $displayImage = $order->product_image;
                                                    $isPdf = false;
                                                    if ($displayImage) {
                                                    $isPdf = Str::endsWith(strtolower($displayImage), '.pdf');
                                                    } elseif ($order->product && $order->product->images->count() > 0) {
                                                    $displayImage = $order->product->images->first()->path;
                                                    $isPdf = Str::endsWith(strtolower($displayImage), '.pdf');
                                                    }
                                                    @endphp
                                                    @if($displayImage)
                                                    <div class="tw-relative tw-w-12 tw-h-12 tw-rounded-lg tw-border tw-border-gray-100 tw-overflow-hidden tw-bg-white tw-cursor-pointer tw-flex-shrink-0" onclick="openUniversalPreview('{{ asset($displayImage) }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                        @if($isPdf)
                                                        <canvas class="pdf-canvas tw-w-full tw-h-full tw-object-contain" data-url="{{ asset($displayImage) }}"></canvas>
                                                        @else
                                                        <img src="{{ asset($displayImage) }}" class="tw-w-full tw-h-full tw-object-cover">
                                                        @endif
                                                    </div>
                                                    @else
                                                    <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-bg-gray-50 tw-flex tw-items-center tw-justify-center tw-text-gray-300 tw-border tw-border-dashed tw-border-gray-200">
                                                        <i class="bi bi-image tw-text-xl"></i>
                                                    </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="tw-px-4 tw-py-4">
                                                <div class="tw-font-extrabold tw-text-gray-900 tw-uppercase tw-tracking-tight tw-text-lg">
                                                    {{ $order->work_order_number }}
                                                </div>

                                                <div class="tw-mt-2">
                                                    <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-1 tw-rounded-md tw-bg-gray-100 tw-text-xs tw-font-bold tw-text-gray-700 tw-border tw-border-gray-200">
                                                        <span class="tw-text-gray-400 tw-mr-1">REF:</span>
                                                        {{ $order->reference_no ?? '-' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="tw-px-4 tw-py-4">
                                                <div class="tw-text-gray-900 tw-font-bold tw-text-xs">{{ $order->customer_name }}</div>
                                                <div class="tw-text-[14px] tw-text-primary tw-font-bold tw-mt-1">{{ $order->bp_code ?? 'NO BP' }}</div>
                                                <!-- @if($order->buyer)
                                            <div class="tw-text-[10px] tw-text-gray-400 tw-mt-0.5">{{ $order->buyer->dear }}</div>
                                            @endif -->
                                            </td>
                                            <td class="tw-px-4 tw-py-4">
                                                <div class="tw-flex tw-flex-col tw-gap-2">
                                                    <div class="tw-flex tw-items-center tw-gap-1.5 tw-text-[11px]">
                                                        <span class="tw-text-gray-400 tw-w-8 tw-font-bold">ORD:</span>
                                                        <span class="tw-text-gray-600 tw-font-medium">
                                                            {{ $order->created_at ? $order->created_at->format('d M, Y') : '-' }}
                                                        </span>
                                                    </div>

                                                    <div class="tw-flex tw-items-center tw-gap-1.5">
                                                        <span class="tw-text-gray-400 tw-w-8 tw-text-[11px] tw-font-bold">DUE:</span>
                                                        <span class="tw-px-2 tw-py-0.5 tw-rounded tw-text-sm tw-font-extrabold shadow-sm
                {{ $isOverdue 
                    ? 'tw-bg-rose-50 tw-text-rose-700 tw-border tw-border-rose-200' 
                    : 'tw-bg-emerald-50 tw-text-emerald-700 tw-border tw-border-emerald-200' 
                }}">
                                                            {{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'NOT SET' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="tw-px-4 tw-py-4">
                                                <div class="tw-flex tw-flex-col tw-gap-2.5">

                                                    <div class="tw-bg-blue-50 tw-border tw-border-blue-100 tw-rounded-md tw-p-2">
                                                        <div class="tw-text-[11px] tw-leading-tight">
                                                            <span class="tw-text-blue-400 tw-font-bold tw-uppercase">CAT:</span>
                                                            <span class="tw-text-blue-900 tw-font-extrabold tw-uppercase tw-text-xs">{{ $order->product_category ?: '-' }}</span>
                                                        </div>
                                                        <div class="tw-text-[11px] tw-mt-0.5">
                                                            <span class="tw-text-blue-400 tw-font-bold tw-uppercase">SUB:</span>
                                                            <span class="tw-text-blue-800 tw-font-semibold">{{ $order->subcategory ?: '-' }}</span>
                                                        </div>
                                                        <div class="tw-text-[11px] tw-mt-0.5">
                                                            <span class="tw-text-blue-400 tw-font-bold tw-uppercase">SIZE:</span>
                                                            <span class="tw-text-blue-800 tw-font-semibold">{{ $order->size ?: '-' }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="tw-flex tw-items-center tw-gap-2 tw-bg-amber-50 tw-border tw-border-amber-200 tw-rounded tw-px-2 tw-py-1.5 tw-w-fit">
                                                        <span class="tw-text-sm tw-font-black tw-text-amber-700">
                                                            {{ $order->quantity }} {{ $order->type ?: 'Piece' }}
                                                        </span>
                                                        <span class="tw-text-amber-300">|</span>
                                                        <span class="tw-text-xs tw-font-bold tw-text-amber-600">
                                                            {{ $order->weight_from ?: '-' }} g
                                                        </span>
                                                    </div>

                                                    <div class="tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded tw-p-2">
                                                        <div class="tw-text-[10px] tw-text-slate-400 tw-font-bold tw-uppercase tw-mb-1">Craftsman Notes:</div>
                                                        <div class="tw-text-sm tw-text-slate-700 tw-font-medium tw-italic tw-leading-snug">
                                                            "{{ $order->narration_craftsman ?: '-' }}"
                                                        </div>
                                                    </div>

                                                </div>
                                            </td>
                                            <td class="tw-px-4 tw-py-4">
                                                <div class="tw-flex tw-items-center tw-gap-2">
                                                    <div class="tw-w-7 tw-h-7 tw-rounded-full tw-bg-amber-100 tw-text-amber-700 tw-flex tw-items-center tw-justify-center tw-font-bold tw-text-[10px]">
                                                        {{ $order->craftsman ? substr($order->craftsman->name, 0, 1) : '?' }}
                                                    </div>
                                                    <div class="tw-text-xs tw-text-gray-700">{{ $order->craftsman ? $order->craftsman->name : 'N/A' }}</div>
                                                </div>
                                            </td>
                                            <td class="tw-px-4 tw-py-4 tw-text-right">
                                                <div class="tw-flex tw-justify-end tw-gap-1">
                                                    <button type="button" onclick="openSuperAdminUndoModal({{ $order->id }}, {{ $order->superadmin_undo_count }})" class="tw-p-2 tw-text-purple-600 hover:tw-bg-purple-50 tw-rounded-lg tw-transition-colors" title="Undo Status">
                                                        <i class="bi bi-arrow-counterclockwise"></i>
                                                    </button>
                                                    <a href="{{ route('super-admin.work-order.show', $order) }}" class="tw-p-2 tw-text-sky-600 hover:tw-bg-sky-50 tw-rounded-lg tw-transition-colors" title="View">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                            </tr>
                                            @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="tw-px-6 tw-py-4 tw-bg-white tw-border-t tw-border-gray-100 tw-flex tw-items-center tw-justify-between">
                            <!-- <div>
                                <button type="submit" class="tw-inline-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2 tw-bg-emerald-600 tw-text-white tw-text-sm tw-font-semibold tw-rounded-lg hover:tw-bg-emerald-700 tw-transition-all disabled:tw-opacity-50 disabled:tw-cursor-not-allowed tw-shadow-sm" id="bulk-approve-btn" disabled>
                                    <i class="bi bi-check-all"></i> Bulk Approve Selected
                                </button>
                            </div> -->
                            <div>
                                {{ $forApprovalOrders->appends(array_merge(request()->query(), ['tab' => 'for-approval-orders']))->links('vendor.pagination.custom-pagination') }}
                            </div>
                        </div>
                    </form>
                    @else
                    <div class="tw-text-center tw-py-16">
                        <div class="tw-w-16 tw-h-16 tw-bg-gray-50 tw-text-gray-300 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                            <i class="bi bi-check2-circle tw-text-3xl"></i>
                        </div>
                        <h4 class="tw-text-gray-900 tw-font-semibold tw-mb-1">All Clear!</h4>
                        <p class="tw-text-gray-500 tw-text-sm">No work orders pending approval.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if($activeTab == 'completed-orders')
        <div class="tab-pane fade show active" id="completed-orders" role="tabpanel">
            <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-shadow-sm tw-overflow-hidden tw-mt-4">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-100 tw-bg-gray-50/50 tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-4">
                    <div class="tw-flex tw-items-center tw-gap-4 tw-flex-1">
                        <form method="GET" class="tw-relative tw-max-w-xs tw-w-full">
                            <input type="hidden" name="tab" value="completed-orders">
                            <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                            <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                            <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                                <i class="bi bi-search tw-text-gray-400"></i>
                            </div>
                            <input type="text" name="search" class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-2 tw-border tw-border-gray-200 tw-rounded-lg tw-leading-5 tw-bg-white placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-emerald-500 focus:tw-border-transparent tw-text-sm tw-transition-all" placeholder="Search completed..." value="{{ request('search') }}">
                        </form>
                    </div>

                    <div class="tw-flex tw-items-center tw-gap-3">
                        <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-emerald-100 tw-text-emerald-800">
                            {{ $completedOrders->total() }} Completed
                        </span>
                        <form method="GET" class="tw-flex tw-items-center tw-gap-2">
                            <input type="hidden" name="tab" value="completed-orders">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <select name="completed_filter" onchange="this.form.submit()" class="tw-text-xs tw-border-gray-200 tw-rounded-lg tw-bg-white focus:tw-ring-emerald-500 tw-py-1">
                                <option value="">All Time</option>
                                <option value="day" {{ request('completed_filter') == 'day' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ request('completed_filter') == 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ request('completed_filter') == 'month' ? 'selected' : '' }}>This Month</option>
                            </select>
                            <select name="per_page" class="tw-text-xs tw-border-gray-200 tw-rounded-lg tw-bg-white focus:tw-ring-emerald-500 tw-py-1" onchange="this.form.submit()">
                                @foreach([25, 50, 75, 100, 150, 200] as $size)
                                <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <div class="tw-relative">
                    @if($completedOrders->count() > 0)
                    <div class="tw-overflow-x-auto">
                        <table class="tw-w-full tw-text-sm tw-text-left">
                            <thead class="tw-text-sm tw-text-gray-900 tw-uppercase tw-bg-slate-100 tw-border-b-2 tw-border-gray-200">
                                <tr>
                                    <th class="tw-px-6 tw-py-5">
                                        <input type="checkbox" id="select-all-completed-orders"
                                            class="tw-w-4 tw-h-4 tw-rounded tw-border-gray-300 tw-text-primary focus:tw-ring-primary">
                                    </th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Images</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Order Details</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Customer</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Dates</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Specs</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Craftsman</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider tw-text-right">
                                        {{ __('messages.actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <!-- <thead class="tw-text-xs tw-text-gray-500 tw-uppercase tw-bg-gray-50 tw-border-b tw-border-gray-100">
                                <tr>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold" width="40">
                                        <input type="checkbox" id="select-all-completed-orders" class="tw-rounded tw-border-gray-300 tw-text-emerald-600 focus:tw-ring-emerald-500">
                                    </th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">{{ __('messages.image') }}</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">{{ __('messages.order_details') }}</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">{{ __('messages.customer') }}</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">Dates</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">Specs</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">Craftsman</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold tw-text-right">Actions</th>
                                </tr>
                            </thead> -->
                            <tbody class="tw-divide-y tw-divide-gray-50">
                                @foreach($completedOrders as $order)
                                <tr class="hover:tw-bg-gray-50/80 tw-transition-colors">
                                    <td class="tw-px-4 tw-py-4">
                                        <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="completed-order-checkbox tw-rounded tw-border-gray-300 tw-text-emerald-600 focus:tw-ring-emerald-500">
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-flex tw-items-center tw-gap-3">
                                            @php
                                            $displayImage = $order->product_image;
                                            $isPdf = false;

                                            if ($displayImage) {
                                            $isPdf = Str::endsWith(strtolower($displayImage), '.pdf');
                                            if (!Str::startsWith($displayImage, ['http', 'storage/', 'images/', 'uploads/'])) {
                                            $displayImage = 'storage/' . $displayImage;
                                            }
                                            } elseif ($order->product && $order->product->images->isNotEmpty()) {
                                            $displayImage = $order->product->images->first()->path;
                                            $isPdf = Str::endsWith(strtolower($displayImage), '.pdf');
                                            if (!Str::startsWith($displayImage, ['http', 'storage/', 'images/', 'uploads/'])) {
                                            $displayImage = 'storage/' . $displayImage;
                                            }
                                            }
                                            @endphp
                                            @if($displayImage)
                                            <div class="tw-relative tw-w-12 tw-h-12 tw-rounded-lg tw-border tw-border-gray-100 tw-overflow-hidden tw-bg-white tw-cursor-pointer tw-flex-shrink-0" onclick="openUniversalPreview('{{ asset($displayImage) }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                @if($isPdf)
                                                <canvas class="pdf-canvas tw-w-full tw-h-full tw-object-contain" data-url="{{ asset($displayImage) }}"></canvas>
                                                @else
                                                <img src="{{ asset($displayImage) }}" class="tw-w-full tw-h-full tw-object-cover">
                                                @endif
                                            </div>
                                            @else
                                            <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-bg-gray-50 tw-flex tw-items-center tw-justify-center tw-text-gray-300 tw-border tw-border-dashed tw-border-gray-200">
                                                <i class="bi bi-image tw-text-xl"></i>
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-font-extrabold tw-text-gray-900 tw-uppercase tw-tracking-tight tw-text-lg">
                                            {{ $order->work_order_number }}
                                        </div>

                                        <div class="tw-mt-2">
                                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-1 tw-rounded-md tw-bg-gray-100 tw-text-xs tw-font-bold tw-text-gray-700 tw-border tw-border-gray-200">
                                                <span class="tw-text-gray-400 tw-mr-1">REF:</span>
                                                {{ $order_details->reference_no ?? ($order->reference_no ?? '-') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-text-gray-900 tw-font-bold tw-text-xs">{{ $order->customer_name }}</div>
                                        <div class="tw-text-[14px] tw-text-primary tw-font-bold tw-mt-1">{{ $order->bp_code ?? 'NO BP' }}</div>
                                        <!-- @if($order->buyer)
                                            <div class="tw-text-[10px] tw-text-gray-400 tw-mt-0.5">{{ $order->buyer->dear }}</div>
                                            @endif -->
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-flex tw-flex-col tw-gap-2">
                                            <div class="tw-flex tw-items-center tw-gap-1.5 tw-text-[11px]">
                                                <span class="tw-text-gray-400 tw-w-8 tw-font-bold">ORD:</span>
                                                <span class="tw-text-gray-600 tw-font-medium">
                                                    {{ $order->created_at ? $order->created_at->format('d M, Y') : '-' }}
                                                </span>
                                            </div>

                                            <div class="tw-flex tw-items-center tw-gap-1.5">
                                                <span class="tw-text-gray-400 tw-w-8 tw-text-[11px] tw-font-bold">DUE:</span>

                                                {{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'NOT SET' }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-sm {{ strtolower($order->status) === 'completed' ? 'text-green-600 font-bold' : ($isOverdue ? 'text-red-700 font-bold' : 'text-red-600 font-bold') }}">
                                                @if(strtolower($order->status) === 'completed')
                                                <i class="bi bi-check-circle text-xs"></i>
                                                {{ $order->updated_at ? $order->updated_at->format('d M, Y') : 'N/A' }}
                                                @else
                                                <i class="bi bi-alarm text-xs"></i>
                                                Not Completed
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-flex tw-flex-col tw-gap-2.5">

                                            <div class="tw-bg-blue-50 tw-border tw-border-blue-100 tw-rounded-md tw-p-2">
                                                <div class="tw-text-[11px] tw-leading-tight">
                                                    <span class="tw-text-blue-400 tw-font-bold tw-uppercase">CAT:</span>
                                                    <span class="tw-text-blue-900 tw-font-extrabold tw-uppercase tw-text-xs">{{ $order->product_category ?: '-' }}</span>
                                                </div>
                                                <div class="tw-text-[11px] tw-mt-0.5">
                                                    <span class="tw-text-blue-400 tw-font-bold tw-uppercase">SUB:</span>
                                                    <span class="tw-text-blue-800 tw-font-semibold">{{ $order->subcategory ?: '-' }}</span>
                                                </div>
                                                <div class="tw-text-[11px] tw-mt-0.5">
                                                    <span class="tw-text-blue-400 tw-font-bold tw-uppercase">SIZR:</span>
                                                    <span class="tw-text-blue-800 tw-font-semibold">{{ $order->size ?: '-' }}</span>
                                                </div>
                                            </div>

                                            <div class="tw-flex tw-items-center tw-gap-2 tw-bg-amber-50 tw-border tw-border-amber-200 tw-rounded tw-px-2 tw-py-1.5 tw-w-fit">
                                                <span class="tw-text-sm tw-font-black tw-text-amber-700">
                                                    {{ $order->quantity }} {{ $order->type ?: 'Piece' }}
                                                </span>
                                                <span class="tw-text-amber-300">|</span>
                                                <span class="tw-text-xs tw-font-bold tw-text-amber-600">
                                                    {{ $order->weight_from ?: '-' }} g
                                                </span>
                                            </div>

                                            <div class="tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded tw-p-2">
                                                <div class="tw-text-[10px] tw-text-slate-400 tw-font-bold tw-uppercase tw-mb-1">Craftsman Notes:</div>
                                                <div class="tw-text-sm tw-text-slate-700 tw-font-medium tw-italic tw-leading-snug">
                                                    "{{ $order->narration_craftsman ?: '-' }}"
                                                </div>
                                            </div>

                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-flex tw-items-center tw-gap-2">
                                            <div class="tw-w-7 tw-h-7 tw-rounded-full tw-bg-emerald-100 tw-text-emerald-700 tw-flex tw-items-center tw-justify-center tw-font-bold tw-text-[10px]">
                                                {{ $order->craftsman ? substr($order->craftsman->name, 0, 1) : '?' }}
                                            </div>
                                            <div class="tw-text-xs tw-text-gray-700">{{ $order->craftsman ? $order->craftsman->name : 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4 tw-text-right">
                                        <div class="tw-flex tw-justify-end tw-gap-1">
                                            <button type="button" onclick="openSuperAdminUndoModal({{ $order->id }}, {{ $order->superadmin_undo_count }})" class="tw-p-2 tw-text-purple-600 hover:tw-bg-purple-50 tw-rounded-lg tw-transition-colors" title="Undo Status">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                            <a href="{{ route('super-admin.work-order.show', $order) }}" class="tw-p-2 tw-text-sky-600 hover:tw-bg-sky-50 tw-rounded-lg tw-transition-colors" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('super-admin.work-order.copy', $order) }}" class="tw-p-2 tw-text-emerald-600 hover:tw-bg-emerald-50 tw-rounded-lg tw-transition-colors" title="Copy">
                                                <i class="bi bi-copy"></i>
                                            </a>
                                            <form action="{{ route('super-admin.work-order.destroy', $order) }}" method="POST" class="tw-inline-block" onsubmit="return confirm('Are you sure you want to delete this completed order?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="tw-p-2 tw-text-rose-600 hover:tw-bg-rose-50 tw-rounded-lg tw-transition-colors" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="tw-px-6 tw-py-4 tw-bg-white tw-border-t tw-border-gray-100 tw-flex tw-justify-end">
                        {{ $completedOrders->appends(array_merge(request()->query(), ['tab' => 'completed-orders']))->links('vendor.pagination.custom-pagination') }}
                    </div>
                    @else
                    <div class="tw-text-center tw-py-16">
                        <div class="tw-w-16 tw-h-16 tw-bg-gray-50 tw-text-gray-300 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                            <i class="bi bi-check2-all tw-text-3xl"></i>
                        </div>
                        <h4 class="tw-text-gray-900 tw-font-semibold tw-mb-1">No Completed Orders</h4>
                        <p class="tw-text-gray-500 tw-text-sm">Completed orders will appear here.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if($activeTab == 'all-orders')
        <div class="tab-pane fade show active" id="all-orders" role="tabpanel">
            <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-shadow-sm tw-overflow-hidden tw-mt-4">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-100 tw-bg-gray-50/50 tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-4">
                    <div class="tw-flex tw-items-center tw-gap-4 tw-flex-1">
                        <form method="GET" class="tw-relative tw-max-w-xs tw-w-full">
                            <input type="hidden" name="tab" value="all-orders">
                            <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                            <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                            <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                                <i class="bi bi-search tw-text-gray-400"></i>
                            </div>
                            <input type="text" name="search" class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-2 tw-border tw-border-gray-200 tw-rounded-lg tw-leading-5 tw-bg-white placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-transparent tw-text-sm tw-transition-all" placeholder="Search all work orders..." value="{{ request('search') }}">
                        </form>
                    </div>

                    <div class="tw-flex tw-items-center tw-gap-3">
                        <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-gray-100 tw-text-gray-800">
                            {{ $allOrders->total() }} Total
                        </span>
                        <form method="GET" class="tw-flex tw-items-center tw-gap-2">
                            <input type="hidden" name="tab" value="all-orders">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <select name="per_page" class="tw-text-xs tw-border-gray-200 tw-rounded-lg tw-bg-white focus:tw-ring-primary-500 tw-py-1" onchange="this.form.submit()">
                                @foreach([25, 50, 75, 100, 150, 200] as $size)
                                <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <div class="tw-relative">
                    @if($allOrders->count() > 0)
                    <div class="">
                        <table class="tw-w-full tw-text-sm tw-text-left">
                            <thead class="tw-text-sm tw-text-gray-900 tw-uppercase tw-bg-slate-100 tw-border-b-2 tw-border-gray-200">
                                <tr>
                                    <th class="tw-px-6 tw-py-5">
                                        <input type="checkbox" id="select-all-all-orders"
                                            class="tw-w-4 tw-h-4 tw-rounded tw-border-gray-300 tw-text-primary focus:tw-ring-primary">
                                    </th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Images</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Order Details</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Customer</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Dates</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Specs</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider">Status</th>
                                    <th class="tw-px-4 tw-py-5 tw-font-black tw-tracking-wider tw-text-right">
                                        {{ __('messages.actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <!-- <thead class="tw-text-xs tw-text-gray-500 tw-uppercase tw-bg-gray-50 tw-border-b tw-border-gray-100">
                                <tr>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold" width="40">
                                        <input type="checkbox" id="select-all-all-orders" class="tw-rounded tw-border-gray-300 tw-text-primary-600 focus:tw-ring-primary-500">
                                    </th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">{{ __('messages.image') }}</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">{{ __('messages.order_details') }}</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">{{ __('messages.customer') }}</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">Dates</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">Specs</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold">Status</th>
                                    <th class="tw-px-4 tw-py-3 tw-font-semibold tw-text-right">Actions</th>
                                </tr>
                            </thead> -->
                            <tbody class="tw-divide-y tw-divide-gray-50">
                                @foreach($allOrders as $order)
                                <tr class="hover:tw-bg-gray-50/80 tw-transition-colors">
                                    <td class="tw-px-4 tw-py-4">
                                        <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="all-order-checkbox tw-rounded tw-border-gray-300 tw-text-primary-600 focus:tw-ring-primary-500">
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-flex tw-items-center tw-gap-3">
                                            @php
                                            $displayImage = $order->product_image;
                                            $isPdf = false;

                                            if ($displayImage) {
                                            $isPdf = Str::endsWith(strtolower($displayImage), '.pdf');
                                            if (!Str::startsWith($displayImage, ['http', 'storage/', 'images/', 'uploads/'])) {
                                            $displayImage = 'storage/' . $displayImage;
                                            }
                                            } elseif ($order->product && $order->product->images->isNotEmpty()) {
                                            $displayImage = $order->product->images->first()->path;
                                            $isPdf = Str::endsWith(strtolower($displayImage), '.pdf');
                                            if (!Str::startsWith($displayImage, ['http', 'storage/', 'images/', 'uploads/'])) {
                                            $displayImage = 'storage/' . $displayImage;
                                            }
                                            }
                                            @endphp
                                            @if($displayImage)
                                            <div class="tw-relative tw-w-12 tw-h-12 tw-rounded-lg tw-border tw-border-gray-100 tw-overflow-hidden tw-bg-white tw-cursor-pointer tw-flex-shrink-0" onclick="openUniversalPreview('{{ asset($displayImage) }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                @if($isPdf)
                                                <canvas class="pdf-canvas tw-w-full tw-h-full tw-object-contain" data-url="{{ asset($displayImage) }}"></canvas>
                                                @else
                                                <img src="{{ asset($displayImage) }}" class="tw-w-full tw-h-full tw-object-cover">
                                                @endif
                                            </div>
                                            @else
                                            <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-bg-gray-50 tw-flex tw-items-center tw-justify-center tw-text-gray-300 tw-border tw-border-dashed tw-border-gray-200">
                                                <i class="bi bi-image tw-text-xl"></i>
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-font-extrabold tw-text-gray-900 tw-uppercase tw-tracking-tight tw-text-lg">
                                            {{ $order->work_order_number }}
                                        </div>

                                        <div class="tw-mt-2">
                                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-1 tw-rounded-md tw-bg-gray-100 tw-text-xs tw-font-bold tw-text-gray-700 tw-border tw-border-gray-200">
                                                <span class="tw-text-gray-400 tw-mr-1">REF:</span>
                                                {{ $order_details->reference_no ?? ($order->reference_no ?? '-') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-text-gray-900 tw-font-bold tw-text-xs">{{ $order->customer_name }}</div>
                                        <div class="tw-text-[14px] tw-text-primary tw-font-bold tw-mt-1">{{ $order->bp_code ?? 'NO BP' }}</div>
                                        <!-- @if($order->buyer)
                                            <div class="tw-text-[10px] tw-text-gray-400 tw-mt-0.5">{{ $order->buyer->dear }}</div>
                                            @endif -->
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-flex tw-flex-col tw-gap-2">
                                            <div class="tw-flex tw-items-center tw-gap-1.5 tw-text-[11px]">
                                                <span class="tw-text-gray-400 tw-w-8 tw-font-bold">ORD:</span>
                                                <span class="tw-text-gray-600 tw-font-medium">
                                                    {{ $order->created_at ? $order->created_at->format('d M, Y') : '-' }}
                                                </span>
                                            </div>

                                            <div class="tw-flex tw-items-center tw-gap-1.5">
                                                <span class="tw-text-gray-400 tw-w-8 tw-text-[11px] tw-font-bold">DUE:</span>

                                                {{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'NOT SET' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-flex tw-flex-col tw-gap-2.5">

                                            <div class="tw-bg-blue-50 tw-border tw-border-blue-100 tw-rounded-md tw-p-2">
                                                <div class="tw-text-[11px] tw-leading-tight">
                                                    <span class="tw-text-blue-400 tw-font-bold tw-uppercase">CAT:</span>
                                                    <span class="tw-text-blue-900 tw-font-extrabold tw-uppercase tw-text-xs">{{ $order->product_category ?: '-' }}</span>
                                                </div>
                                                <div class="tw-text-[11px] tw-mt-0.5">
                                                    <span class="tw-text-blue-400 tw-font-bold tw-uppercase">SUB:</span>
                                                    <span class="tw-text-blue-800 tw-font-semibold">{{ $order->subcategory ?: '-' }}</span>
                                                </div>
                                                <div class="tw-text-[11px] tw-mt-0.5">
                                                    <span class="tw-text-blue-400 tw-font-bold tw-uppercase">SIZE:</span>
                                                    <span class="tw-text-blue-800 tw-font-semibold">{{ $order->size ?: '-' }}</span>
                                                </div>
                                            </div>

                                            <div class="tw-flex tw-items-center tw-gap-2 tw-bg-amber-50 tw-border tw-border-amber-200 tw-rounded tw-px-2 tw-py-1.5 tw-w-fit">
                                                <span class="tw-text-sm tw-font-black tw-text-amber-700">
                                                    {{ $order->quantity }} {{ $order->type ?: 'Piece' }}
                                                </span>
                                                <span class="tw-text-amber-300">|</span>
                                                <span class="tw-text-xs tw-font-bold tw-text-amber-600">
                                                    {{ $order->weight_from ?: '-' }} g
                                                </span>
                                            </div>

                                            <div class="tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded tw-p-2">
                                                <div class="tw-text-[10px] tw-text-slate-400 tw-font-bold tw-uppercase tw-mb-1">Craftsman Notes:</div>
                                                <div class="tw-text-sm tw-text-slate-700 tw-font-medium tw-italic tw-leading-snug">
                                                    "{{ $order->narration_craftsman ?: '-' }}"
                                                </div>
                                            </div>

                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-flex tw-items-center">
                                            @php
                                            $statusClasses = [
                                            'completed' => 'tw-bg-emerald-100 tw-text-emerald-700',
                                            'new' => 'tw-bg-sky-100 tw-text-sky-700',
                                            'allocated' => 'tw-bg-amber-100 tw-text-amber-700',
                                            'in_process' => 'tw-bg-indigo-100 tw-text-indigo-700',
                                            'rejected' => 'tw-bg-rose-100 tw-text-rose-700',
                                            'approval_pending' => 'tw-bg-purple-100 tw-text-purple-700',
                                            ];
                                            $statusClass = $statusClasses[$order->status] ?? 'tw-bg-gray-100 tw-text-gray-700';
                                            @endphp
                                            <span class="tw-px-2 tw-py-0.5 tw-rounded-full tw-text-[10px] tw-font-bold {{ $statusClass }}">
                                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4 tw-text-right">
                                        <div class="tw-flex tw-justify-end tw-gap-1">
                                            <a href="{{ route('super-admin.work-order.show', $order) }}" class="tw-p-2 tw-text-sky-600 hover:tw-bg-sky-50 tw-rounded-lg tw-transition-colors" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="tw-px-6 tw-py-4 tw-bg-white tw-border-t tw-border-gray-100 tw-flex tw-justify-end">
                        {{ $allOrders->appends(array_merge(request()->query(), ['tab' => 'all-orders']))->links('vendor.pagination.custom-pagination') }}
                    </div>
                    @else
                    <div class="tw-text-center tw-py-16">
                        <div class="tw-w-16 tw-h-16 tw-bg-gray-50 tw-text-gray-300 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                            <i class="bi bi-inbox tw-text-3xl"></i>
                        </div>
                        <h4 class="tw-text-gray-900 tw-font-semibold tw-mb-1">No Orders Found</h4>
                        <p class="tw-text-gray-500 tw-text-sm">No work orders match your criteria.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
</div>
</div>

<!-- Script to automatically switch to the requested tab -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // 2. Select All Checkbox logic
        const tabConfigs = [{
                id: 'new',
                checkboxClass: 'new-order-checkbox',
                selectAllId: 'select-all-new-orders',
                bulkBtnIds: ['bulk-allocate-btn']
            },
            {
                id: 'allocated',
                checkboxClass: 'allocated-order-checkbox',
                selectAllId: 'select-all-allocated-orders',
                bulkBtnIds: []
            },
            {
                id: 'in-process',
                checkboxClass: 'in-process-order-checkbox',
                selectAllId: 'select-all-in-process-orders',
                bulkBtnIds: []
            },
            {
                id: 'rejected',
                checkboxClass: 'rejected-order-checkbox',
                selectAllId: 'select-all-rejected-orders',
                bulkBtnIds: ['bulk-reallocate-btn']
            },
            {
                id: 'overdue',
                checkboxClass: 'overdue-order-checkbox',
                selectAllId: 'select-all-overdue-orders',
                bulkBtnIds: []
            },
            {
                id: 'for-approval',
                checkboxClass: 'for-approval-order-checkbox',
                selectAllId: 'select-all-for-approval-orders',
                bulkBtnIds: ['bulk-approve-btn']
            },
            {
                id: 'completed',
                checkboxClass: 'completed-order-checkbox',
                selectAllId: 'select-all-completed-orders',
                bulkBtnIds: []
            },
            {
                id: 'all',
                checkboxClass: 'all-order-checkbox',
                selectAllId: 'select-all-all-orders',
                bulkBtnIds: []
            }
        ];

        function updateBulkButtons(config) {
            const checkedCount = document.querySelectorAll(`.${config.checkboxClass}:checked`).length;
            config.bulkBtnIds.forEach(btnId => {
                const btn = document.getElementById(btnId);
                if (btn) btn.disabled = checkedCount === 0;
            });
        }

        tabConfigs.forEach(config => {
            const selectAll = document.getElementById(config.selectAllId);
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll(`.${config.checkboxClass}`);
                    checkboxes.forEach(cb => cb.checked = this.checked);
                    updateBulkButtons(config);
                });
            }

            document.addEventListener('change', function(e) {
                if (e.target.classList.contains(config.checkboxClass)) {
                    updateBulkButtons(config);
                    if (selectAll) {
                        const cbList = document.querySelectorAll(`.${config.checkboxClass}`);
                        const allChecked = Array.from(cbList).every(c => c.checked);
                        const someChecked = Array.from(cbList).some(c => c.checked);
                        selectAll.checked = allChecked;
                        selectAll.indeterminate = !allChecked && someChecked;
                    }
                }
            });
        });

        // Initialize Searchable Dropdowns
        initSearchableDropdown('bp_code_filter_container', 'bp_code_filter_display', 'bp_code_filter_menu', 'bp_code_filter_search', 'bp_code_filter_list', 'bp_code_filter_select', '{{ __("messages.all_bp_codes") }}');
        initSearchableDropdown('craftsman_filter_container', 'craftsman_filter_display', 'craftsman_filter_menu', 'craftsman_filter_search', 'craftsman_filter_list', 'craftsman_filter_select', '{{ __("messages.all_craftsmen") }}');

        initSearchableDropdown('category_filter_container', 'category_filter_display', 'category_filter_menu', 'category_filter_search', 'category_filter_list', 'category_filter_select', 'All Categories');
        initSearchableDropdown('subcategory_filter_container', 'subcategory_filter_display', 'subcategory_filter_menu', 'subcategory_filter_search', 'subcategory_filter_list', 'subcategory_filter_select', 'All Subcategories');
        initSearchableDropdown('design_code_filter_container', 'design_code_filter_display', 'design_code_filter_menu', 'design_code_filter_search', 'design_code_filter_list', 'design_code_filter_select', 'All Design Codes');
        initSearchableDropdown('product_code_filter_container', 'product_code_filter_display', 'product_code_filter_menu', 'product_code_filter_search', 'product_code_filter_list', 'product_code_filter_select', 'All Product Codes');
    });

    // GENERIC SEARCHABLE DROPDOWN
    function initSearchableDropdown(containerId, displayId, menuId, searchInputId, listId, hiddenSelectId, placeholder) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const display = document.getElementById(displayId);
        const menu = document.getElementById(menuId);
        const searchInput = document.getElementById(searchInputId);
        const listContainer = document.getElementById(listId);
        const hiddenSelect = document.getElementById(hiddenSelectId);

        function getListItems() {
            return listContainer.querySelectorAll('li');
        }

        display.addEventListener('click', function(e) {
            e.stopPropagation();
            const isVisible = !menu.classList.contains('tw-hidden');

            document.querySelectorAll('[id$="_menu"]').forEach(m => {
                if (m !== menu) m.classList.add('tw-hidden');
            });

            if (isVisible) {
                menu.classList.add('tw-hidden');
            } else {
                menu.classList.remove('tw-hidden');
                searchInput.focus();
                searchInput.value = '';
                filterItems('');
            }
        });

        searchInput.addEventListener('input', function() {
            filterItems(this.value.toLowerCase());
        });

        function filterItems(query) {
            getListItems().forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(query)) {
                    item.classList.remove('tw-hidden');
                } else {
                    item.classList.add('tw-hidden');
                }
            });
        }

        listContainer.addEventListener('click', function(e) {
            const item = e.target.closest('li');
            if (!item) return;

            const val = item.dataset.value;
            const text = item.textContent.trim();

            display.textContent = val ? text : placeholder;
            hiddenSelect.value = val;

            hiddenSelect.dispatchEvent(new Event('change', {
                bubbles: true
            }));

            getListItems().forEach(i => i.classList.remove('tw-bg-gray-100', 'tw-font-bold'));
            item.classList.add('tw-bg-gray-100', 'tw-font-bold');

            menu.classList.add('tw-hidden');

            hiddenSelect.form.submit();
        });

        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                menu.classList.add('tw-hidden');
            }
        });

        if (hiddenSelect.value) {
            const selectedItem = Array.from(getListItems()).find(i => i.dataset.value === hiddenSelect.value);
            if (selectedItem) {
                display.textContent = selectedItem.textContent.trim();
                selectedItem.classList.add('tw-bg-gray-100', 'tw-font-bold');
            }
        }
    }

    // Bulk Printing
    window.submitBulkPrintWorkOrders = function() {
        const activePane = document.querySelector('.tab-pane.active');
        if (!activePane) return;

        const selectedIds = Array.from(activePane.querySelectorAll('input[name="work_order_ids[]"]:checked')).map(cb => cb.value);
        if (selectedIds.length === 0) {
            alert('Please select at least one work order to print.');
            return;
        }

        const form = document.getElementById('bulkPrintWorkOrdersForm');
        const container = document.getElementById('print-work-order-ids-container');
        container.innerHTML = '';
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'work_order_ids[]';
            input.value = id;
            container.appendChild(input);
        });
        form.submit();
    };

    // Export Selected
    window.exportSelectedWorkOrders = function() {
        const activePane = document.querySelector('.tab-pane.active');
        const selectedIds = Array.from(activePane.querySelectorAll('input[name="work_order_ids[]"]:checked')).map(cb => cb.value);

        if (selectedIds.length === 0) {
            alert('Please select at least one work order to export.');
            return;
        }

        const url = new URL(window.location.href);
        url.searchParams.set('export', 'excel');
        url.searchParams.set('work_order_ids', selectedIds.join(','));
        url.searchParams.set('tab', '{{ $activeTab }}');
        window.location.href = url.toString();
    };

    // Filter Refresh Subcategories
    window.refreshSubcategories = function(categoryId) {
        const subSelect = document.querySelector('select[name="subcategory_filter"]');
        if (!subSelect) return;

        subSelect.innerHTML = '<option value="">All Subcategories</option>';
        if (!categoryId) return;

        fetch(`/super-admin/product/get-subcategories?category_id=${categoryId}`)
            .then(res => res.json())
            .then(list => {
                list.forEach(sub => {
                    const opt = document.createElement('option');
                    opt.value = sub.id;
                    opt.textContent = sub.name;
                    subSelect.appendChild(opt);
                });
            });
    };

    const categorySelect = document.querySelector('select[name="category_filter"]');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            refreshSubcategories(this.value);
        });
    }

    // Toggle filter section visibility
    function toggleFilters() {
        const filterSection = document.getElementById('filterSection');
        if (filterSection) {
            filterSection.classList.toggle('tw-hidden');
        }
    }

    // Submit Bulk Complete
    window.submitBulkComplete = function() {
        const activePane = document.querySelector('.tab-pane.active');
        const selectedIds = Array.from(activePane.querySelectorAll('input[name="work_order_ids[]"]:checked')).map(cb => cb.value);

        if (selectedIds.length === 0) {
            alert('Please select at least one work order to complete.');
            return;
        }

        if (!confirm('Are you sure you want to mark ' + selectedIds.length + ' selected work orders as completed?')) {
            return;
        }

        const form = document.getElementById('bulkCompleteForm');
        const container = document.getElementById('complete-work-order-ids-container');
        container.innerHTML = '';

        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'work_order_ids[]';
            input.value = id;
            container.appendChild(input);
        });

        form.submit();
    };
</script>

<!-- Bulk Print Form (Hidden) -->
<form id="bulkPrintWorkOrdersForm" action="{{ route('super-admin.work-order.bulk-print') }}" method="POST" target="_blank" style="display:none;">
    @csrf
    <div id="print-work-order-ids-container"></div>
</form>

<!-- Bulk Complete Form (Hidden) -->
<form id="bulkCompleteForm" action="{{ route('super-admin.work-order.bulk-complete') }}" method="POST" style="display:none;">
    @csrf
    <div id="complete-work-order-ids-container"></div>
</form>
@endsection

@section('styles')
<style>
    tr[style*="background-color"]>td,
    tr[style*="background-color"]>th {
        background-color: transparent !important;
    }
</style>
<!-- SuperAdmin Undo Modal -->
<div id="superAdminUndoModal" class="tw-fixed tw-inset-0 tw-z-[9999] tw-hidden tw-bg-slate-900/50 tw-backdrop-blur-sm tw-overflow-y-auto">
    <div class="tw-min-h-screen tw-px-4 tw-text-center tw-flex tw-items-center tw-justify-center">
        <div class="tw-inline-block tw-w-full tw-max-w-md tw-p-6 tw-my-8 tw-text-left tw-align-middle tw-transition-all tw-transform tw-bg-white tw-shadow-xl tw-rounded-2xl tw-relative">
            <h3 class="tw-text-lg tw-font-bold tw-text-slate-900 tw-mb-4">Undo Work Order Status</h3>
            <p class="tw-text-sm tw-text-slate-500 tw-mb-4" id="superAdminUndoModalMsg"></p>
            
            <form id="superAdminUndoForm" method="POST" action="">
                @csrf
                
                <div id="superAdminUndoOtpSection" class="tw-hidden">
                    <div class="tw-mb-4 tw-p-3 tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded-lg tw-text-sm tw-text-left">
                        <div class="tw-font-medium tw-text-slate-700 tw-mb-1">Send OTP To:</div>
                        <div class="tw-text-slate-600">
                            {{ auth('super_admin')->user()->user_code }} - {{ auth('super_admin')->user()->name }} - {{ auth('super_admin')->user()->mobile_no }}
                        </div>
                    </div>
                    <div class="tw-mb-4 tw-text-left tw-flex tw-items-center tw-gap-2">
                        <button type="button" onclick="sendSuperAdminUndoOtp('sms')" class="tw-px-3 tw-py-1.5 tw-bg-slate-100 tw-text-xs tw-text-pink-600 hover:tw-text-pink-700 tw-font-medium tw-rounded tw-border tw-border-slate-200"><i class="bi bi-chat-left-text tw-mr-1"></i> SMS</button>
                        <button type="button" onclick="sendSuperAdminUndoOtp('whatsapp')" class="tw-px-3 tw-py-1.5 tw-bg-emerald-50 tw-text-xs tw-text-emerald-600 hover:tw-text-emerald-700 tw-font-medium tw-rounded tw-border tw-border-emerald-200"><i class="bi bi-whatsapp tw-mr-1"></i> WhatsApp</button>
                        <span id="superAdminOtpStatus" class="tw-ml-2 tw-text-xs tw-text-green-600 tw-hidden">OTP Sent!</span>
                    </div>
                    
                    <div class="tw-mb-4 tw-text-left">
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Enter OTP</label>
                        <input type="text" name="otp" id="superAdminUndoOtpInput" class="tw-w-full tw-px-3 tw-py-2 tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded-lg tw-text-sm" placeholder="6-digit OTP">
                    </div>
                </div>

                <div class="tw-mt-6 tw-flex tw-justify-end tw-gap-3">
                    <button type="button" onclick="closeSuperAdminUndoModal()" class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-slate-700 tw-bg-slate-100 hover:tw-bg-slate-200 tw-rounded-lg tw-transition-colors">Cancel</button>
                    <button type="submit" class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-white tw-bg-purple-600 hover:tw-bg-purple-700 tw-rounded-lg tw-transition-colors tw-shadow-sm">Confirm Undo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentSuperAdminUndoWoId = null;

function openSuperAdminUndoModal(woId, undoCount) {
    currentSuperAdminUndoWoId = woId;
    document.getElementById('superAdminUndoForm').action = `/super-admin/work-order/${woId}/undo`;
    
    if (undoCount >= 6) {
        document.getElementById('superAdminUndoOtpSection').classList.remove('tw-hidden');
        document.getElementById('superAdminUndoOtpInput').required = true;
        document.getElementById('superAdminUndoModalMsg').innerText = "You have reached the maximum undo limit without OTP (6 times). OTP is required to undo again.";
    } else {
        document.getElementById('superAdminUndoOtpSection').classList.add('tw-hidden');
        document.getElementById('superAdminUndoOtpInput').required = false;
        document.getElementById('superAdminUndoModalMsg').innerText = `Are you sure you want to undo the status of this work order? (Used: ${undoCount}/6 before OTP is required)`;
    }
    
    document.getElementById('superAdminUndoModal').classList.remove('tw-hidden');
}

function closeSuperAdminUndoModal() {
    document.getElementById('superAdminUndoModal').classList.add('tw-hidden');
}

function sendSuperAdminUndoOtp(method) {
    document.getElementById('superAdminOtpStatus').classList.remove('tw-hidden');
    document.getElementById('superAdminOtpStatus').innerText = "Sending...";
    document.getElementById('superAdminOtpStatus').className = "tw-ml-2 tw-text-xs tw-text-amber-600";
    
    fetch(`/super-admin/work-order/${currentSuperAdminUndoWoId}/send-undo-otp`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ delivery_method: method })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('superAdminOtpStatus').innerText = "OTP Sent!";
            document.getElementById('superAdminOtpStatus').className = "tw-ml-2 tw-text-xs tw-text-emerald-600";
        } else {
            document.getElementById('superAdminOtpStatus').innerText = "Failed: " + data.message;
            document.getElementById('superAdminOtpStatus').className = "tw-ml-2 tw-text-xs tw-text-rose-600";
        }
    })
    .catch(err => {
        document.getElementById('superAdminOtpStatus').innerText = "Error sending OTP";
        document.getElementById('superAdminOtpStatus').className = "tw-ml-2 tw-text-xs tw-text-rose-600";
    });
}
</script>
@endsection


