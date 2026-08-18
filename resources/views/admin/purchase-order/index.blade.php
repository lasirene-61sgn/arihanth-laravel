@extends('admin.layouts.app')

@section('title', 'Purchase Order Management')

@section('styles')
<style>
    /* Custom scrollbar for horizontal tabs */
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    
    /* Dense table styles */
    .table-dense th, .table-dense td { padding: 0.5rem 0.75rem !important; }
    
    /* Active tab underline */
    .tab-active { border-bottom: 2px solid #97144d; color: #97144d; }
    tr[style*="background-color"] > td, tr[style*="background-color"] > th {
        background-color: transparent !important;
    }
</style>
@endsection

@section('content')
@section('content')
<div class="p-6 bg-slate-50 min-h-screen" x-data="{ 
    showFilters: false, 
    activeTab: '{{ request('tab', 'created') }}' 
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Purchase Order Management</h1>
            <p class="text-slate-500 text-sm mt-1">Manage and track manufacturing purchase orders</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a :href="`{{ route('admin.purchase-order.export') }}?${new URLSearchParams({ ...@json(request()->query()), tab: activeTab }).toString()}`" 
               class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-all shadow-sm">
                <i class="bi bi-file-earmark-excel mr-2"></i> Export Excel
            </a>
            <button type="button" 
                    @click="showFilters = !showFilters"
                    class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-semibold rounded-lg transition-all shadow-sm">
                <i class="bi bi-funnel mr-2"></i> Filters
            </button>
            <a href="{{ route('admin.purchase-order.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-magenta-600 hover:bg-magenta-700 text-white text-sm font-semibold rounded-lg transition-all shadow-sm">
                <i class="bi bi-plus-lg mr-2"></i> Add New PO
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 flex justify-between items-center rounded-r-lg shadow-sm animate-fade-in">
            <div class="flex items-center">
                <i class="bi bi-check-circle-fill mr-3 text-emerald-500"></i>
                <span class="font-medium font-bold">{{ session('success') }}</span>
            </div>
            <button class="text-emerald-500 hover:text-emerald-700 transition-colors" onclick="this.parentElement.remove()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    <!-- Filters Section -->
    <div x-show="showFilters" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="bg-white border border-slate-200 rounded-xl shadow-sm mb-6 overflow-hidden">
        <div class="p-6">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Advanced Filters</h3>
            <form action="{{ route('admin.purchase-order.index') }}" method="GET">
                <input type="hidden" name="tab" x-bind:value="activeTab">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <!-- Search -->
                    <div class="col-span-1 md:col-span-2 lg:col-span-1">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Search Orders</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 text-sm transition-all" 
                                   placeholder="PO Code / Items...">
                        </div>
                    </div>

                    <!-- PO Code Filter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">PO Code</label>
                        <input type="text" name="filter_po_code" value="{{ request('filter_po_code') }}" 
                               class="block w-full px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 text-sm transition-all">
                    </div>

                    <!-- Design Code Filter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Design Code</label>
                        <input type="text" name="filter_design_code" value="{{ request('filter_design_code') }}" 
                               class="block w-full px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 text-sm transition-all"
                               placeholder="e.g. DS0001">
                    </div>

                    
                    <!-- Category Filter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Category</label>
                        <select name="category_filter" class="block w-full px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 text-sm transition-all">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_filter') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sub Category Filter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Sub Category</label>
                        <select name="sub_category_filter" class="block w-full px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 text-sm transition-all">
                            <option value="">All Sub Categories</option>
                            @foreach($subCategories as $subCategory)
                                <option value="{{ $subCategory->id }}" {{ request('sub_category_filter') == $subCategory->id ? 'selected' : '' }}>{{ $subCategory->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Craftsman Filter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Craftsman Code</label>
                        <select name="filter_craftsman" class="block w-full px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 text-sm transition-all">
                            <option value="">All Craftsmen</option>
                            @foreach($craftsmen as $c)
                                <option value="{{ $c->craftman_code }}" {{ request('filter_craftsman') == $c->craftman_code ? 'selected' : '' }}>{{ $c->craftman_code }} - {{ $c->business_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Status</label>
                        <select name="filter_status" 
                                class="block w-full px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 text-sm transition-all">
                            <option value="">All Statuses</option>
                            <option value="created" {{ request('filter_status') == 'created' ? 'selected' : '' }}>Created</option>
                            <option value="allocated" {{ request('filter_status') == 'allocated' ? 'selected' : '' }}>Allocated</option>
                            <option value="in_process" {{ request('filter_status') == 'in_process' ? 'selected' : '' }}>In Process</option>
                            <option value="for_approval" {{ request('filter_status') == 'for_approval' ? 'selected' : '' }}>For Approval</option>
                            <option value="completed" {{ request('filter_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="rejected" {{ request('filter_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="overdue" {{ request('filter_status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">From Date</label>
                        <input type="date" name="filter_date_from" value="{{ request('filter_date_from') }}" 
                               class="block w-full px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 text-sm transition-all">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">To Date</label>
                        <input type="date" name="filter_date_to" value="{{ request('filter_date_to') }}" 
                               class="block w-full px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 text-sm transition-all">
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-span-1 lg:col-span-2 flex items-end gap-3 pt-2">
                        <button type="submit" class="flex-1 bg-magenta-600 hover:bg-magenta-700 text-white font-bold py-2 px-4 rounded-lg transition-colors text-sm shadow-sm">
                            Apply Filters
                        </button>
                        <a href="{{ route('admin.purchase-order.index', ['tab' => request('tab', 'created')]) }}" 
                           class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 px-4 rounded-lg transition-colors text-sm text-center border border-slate-200">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tab Navigation -->
    @php
        $overdueOrders = collect();
        $nowGlobal = \Carbon\Carbon::now();
        
        $checkOverdue = function($po) use ($nowGlobal, &$overdueOrders) {
            $dueDateValue = $po->craftsman_due_date ?? $po->due_date ?? null;
            if ($dueDateValue) {
                $dueDate = \Carbon\Carbon::parse($dueDateValue);
                if ($dueDate->lt($nowGlobal->startOfDay()) || ($dueDate->isToday() && $nowGlobal->hour >= 12)) {
                    if(!$overdueOrders->contains('id', $po->id)) {
                        $overdueOrders->push($po);
                    }
                }
            }
        };

        foreach($createdOrders as $po) $checkOverdue($po);
        foreach($allocatedOrders as $po) $checkOverdue($po);
        foreach($inProcessOrders as $po) $checkOverdue($po);
        foreach($forApprovalOrders as $po) $checkOverdue($po);

        $tabDefinitions = [
            ['id' => 'overdue', 'label' => 'Overdue', 'data' => $overdueOrders],
            ['id' => 'created', 'label' => 'Created', 'data' => $createdOrders],
            ['id' => 'allocated', 'label' => 'Allocated', 'data' => $allocatedOrders],
            ['id' => 'in_process', 'label' => 'In Process', 'data' => $inProcessOrders],
            ['id' => 'for_approval', 'label' => 'For Approval', 'data' => $forApprovalOrders],
            ['id' => 'completed', 'label' => 'Completed', 'data' => $completedOrders],
            ['id' => 'rejected', 'label' => 'Rejected', 'data' => $rejectedOrders],
        ];
    @endphp
    <div class="mb-6">
        <div class="flex border-b border-slate-200 overflow-x-auto hide-scrollbar">
            @foreach($tabDefinitions as $index => $tab)
                <button type="button"
                        @click="activeTab = '{{ $tab['id'] }}'"
                        :class="activeTab === '{{ $tab['id'] }}' ? 'border-magenta-600 text-magenta-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="whitespace-nowrap py-4 px-6 border-b-2 font-bold text-sm transition-all duration-200">
                    {{ $tab['label'] }}
                    <span :class="activeTab === '{{ $tab['id'] }}' ? 'bg-magenta-100 text-magenta-700' : 'bg-slate-100 text-slate-600'"
                          class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-bold">
                        {{ $tab['data']->count() }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Tab Content -->
    <div class="space-y-6">
        @foreach($tabDefinitions as $index => $tab)
        <div x-show="activeTab === '{{ $tab['id'] }}'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-4">
                <h3 class="font-bold text-slate-800 flex items-center">
                    <span class="w-1.5 h-6 bg-magenta-600 rounded-full mr-3"></span>
                    {{ $tab['label'] }} Orders
                </h3>
                
                <div class="flex items-center gap-2">
                    @if(($tab['id'] == 'created' || $tab['id'] == 'allocated') && $tab['data']->count() > 0)
                        <button type="button" 
                                class="bulk-start-btn hidden inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm">
                            <i class="bi bi-play-fill mr-1.5"></i> Bulk Start
                        </button>
                    @endif

                    @if($tab['id'] == 'created' && $tab['data']->count() > 0)
                        <button type="button" 
                                class="bulk-allocate-btn hidden inline-flex items-center px-3 py-1.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm"
                                data-bs-toggle="modal" data-bs-target="#bulkAllocateModal">
                            <i class="bi bi-people mr-1.5"></i> Bulk Allocate
                        </button>
                    @endif
                    
                    @if(($tab['id'] == 'in_process' || $tab['id'] == 'for_approval') && $tab['data']->count() > 0)
                        <button type="button" 
                                class="bulk-complete-btn hidden inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm">
                            <i class="bi bi-check-circle mr-1.5"></i> Bulk Complete
                        </button>
                    @endif

                    <button type="button" 
                            class="bulk-print-btn hidden inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm"
                            onclick="submitBulkPrint()">
                        <i class="bi bi-printer mr-1.5"></i> Bulk Print
                    </button>

                    @if($tab['id'] == 'for_approval' && $tab['data']->count() > 0)
                        <button type="button" 
                                class="bulk-approve-btn hidden inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm">
                            <i class="bi bi-check-all mr-1.5"></i> Bulk Approve
                        </button>
                    @endif

                    @if($tab['id'] == 'completed')
                    <form method="GET" action="{{ route('admin.purchase-order.index') }}" class="inline-block" id="completed-filter-form">
                        <input type="hidden" name="tab" value="completed">
                        <select name="completed_filter" onchange="document.getElementById('completed-filter-form').submit();" class="border-slate-200 rounded-lg px-3 py-1.5 text-xs focus:ring-magenta-500 font-bold text-slate-600 bg-white shadow-sm">
                            <option value="">All Time</option>
                            <option value="day" {{ request('completed_filter') == 'day' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ request('completed_filter') == 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ request('completed_filter') == 'month' ? 'selected' : '' }}>This Month</option>
                        </select>
                    </form>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse po-datatable table-dense" id="table-{{ $tab['id'] }}">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-4 py-3 text-center">
                                <input type="checkbox" class="select-all w-4 h-4 text-magenta-600 border-slate-300 rounded focus:ring-magenta-500">
                            </th>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">PO Code</th>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Order Date</th>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Due Date</th>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Items</th>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Weight</th>
                            @if($tab['id'] != 'created')
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Craftsman</th>
                            @endif
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($tab['data'] as $po)
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
                                            $isDueWithin48Hours = true;
                                        }
                                    }
                                }

                                // Handle updated_at for allocated within 48h
                                $updatedAtValue = null;
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
                                        $allocatedWithin48Hours = true;
                                    }
                                }

                                if ($isOverdue) {
                                    $rowStyle = 'background-color: rgba(255, 228, 230, 0.8) !important;'; // rose
                                } elseif ($isDueWithin48Hours) {
                                    $rowStyle = 'background-color: rgba(255, 237, 213, 0.8) !important;'; // orange
                                } elseif ($currentTabString == 'in-process-orders' || $currentTabString == 'in-process' || $currentTabString == 'in_process') {
                                    $rowStyle = 'background-color: rgba(220, 252, 231, 0.8) !important;'; // green
                                } elseif (($currentTabString == 'allocated-orders' || $currentTabString == 'allocated') && $allocatedWithin48Hours) {
                                    $rowStyle = 'background-color: rgba(219, 234, 254, 0.8) !important;'; // blue
                                } elseif ($currentTabString == 'new-orders' || $currentTabString == 'created') {
                                    $rowStyle = 'background-color: rgba(254, 252, 232, 0.8) !important;'; // yellow
                                }
@endphp
                                <tr class="hover:tw-bg-gray-50 tw-transition-colors  hover:bg-slate-50 transition-colors duration-150" style="{{ $rowStyle }}">
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" class="po-checkbox w-4 h-4 text-magenta-600 border-slate-300 rounded focus:ring-magenta-500" value="{{ $po->id }}">
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-bold text-magenta-600">{{ $po->purchase_order_code }}</span>
                                    @if($isOverdue)
                                        <span class="ml-1 text-[10px] bg-red-100 text-red-700 px-1 rounded font-bold uppercase">Overdue</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600 text-sm italic">{{ $po->created_at->format('d M, Y H:i') }}</td>
                                <td class="px-4 py-3 text-slate-700 text-sm">
                                    <span class="{{ $isOverdue ? 'text-red-600 font-bold' : '' }}">
                                        {{ $po->due_date ? $po->due_date->format('d M, Y') : 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 bg-slate-800 text-white text-[0.7rem] font-bold rounded-full">
                                        {{ count($po->items ?? []) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-800">
                                    {{ number_format(collect($po->items)->sum('total'), 2) }} <small class="text-slate-400">g</small>
                                </td>
                                @if($tab['id'] != 'created')
                                    <td class="px-4 py-3">
                                        <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-1 rounded">
                                            {{ $po->allocated_craftsman_code ?? 'N/A' }}
@if(isset($po) && $po->staff_completed_at && $po->craftsmanStaff)
    <br><span style="font-size: 11px; color: #7e22ce; font-weight: bold;">Staff(C): {{ $po->craftsmanStaff->name }}</span>
@elseif(isset($po) && $po->staff_accepted_at && $po->acceptedByStaff)
    <br><span style="font-size: 11px; color: #2563eb; font-weight: bold;">Staff(A): {{ $po->acceptedByStaff->name }}</span>
@endif
                                        </span>
                                    </td>
                                @endif
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all duration-200 toggle-items-btn" title="Show Items">
                                            <i class="bi bi-chevron-down"></i>
                                        </button>
                                        <template class="items-template">
                                            <div class="p-4 bg-slate-50 border-b border-slate-200 shadow-inner">
                                                <h6 class="mb-3 font-bold text-slate-800 text-sm">Items Added:</h6>
                                                @if(is_array($po->items) && count($po->items) > 0)
                                                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                                                        <table class="w-full text-left text-sm whitespace-nowrap bg-white">
                                                            <thead class="bg-slate-100 text-slate-600 text-xs uppercase font-semibold">
                                                                <tr>
                                                                    <th class="px-4 py-3">Category</th>
                                                                    <th class="px-4 py-3">Product / Design</th>
                                                                    <th class="px-4 py-3">Grams calculation</th>
                                                                    <th class="px-4 py-3">Total Weight</th>
                                                                    <th class="px-4 py-3">Image</th>
                                                                    <th class="px-4 py-3">Notes</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-slate-100 text-slate-600">
                                                                @foreach($po->items as $item)
                                                                    @php
                                                                        $productId = $item['product_id'] ?? null;
                                                                        $prodModel = $productId ? \App\Models\Product::with(['images', 'subcategory', 'category'])->find($productId) : null;
                                                                        
                                                                        $prodName = $prodModel ? $prodModel->product_name : ($item['product_name'] ?? $item['manual_product'] ?? 'N/A');

                                                                        $catName = 'N/A';
                                                                        if (!empty($item['category_name']) && $item['category_name'] !== 'N/A') {
                                                                            $catName = $item['category_name'];
                                                                        } elseif (!empty($item['produts_category']) && $item['produts_category'] !== 'N/A') {
                                                                            $catName = $item['produts_category'];
                                                                        } elseif (!empty($item['category'])) {
                                                                            if (is_numeric($item['category'])) {
                                                                                $cat = \App\Models\ProductCategory::find($item['category']);
                                                                                $catName = $cat ? $cat->name : 'N/A';
                                                                            } else {
                                                                                $catName = $item['category'];
                                                                            }
                                                                        }
                                                                        if (($catName === 'N/A' || empty($catName)) && $prodModel && $prodModel->category) {
                                                                            $catName = $prodModel->category->name;
                                                                        }

                                                                        $subName = 'N/A';
                                                                        if (!empty($item['subcategory_name']) && $item['subcategory_name'] !== 'N/A') {
                                                                            $subName = $item['subcategory_name'];
                                                                        } elseif (!empty($item['sub_category_name']) && $item['sub_category_name'] !== 'N/A') {
                                                                            $subName = $item['sub_category_name'];
                                                                        } elseif ($prodModel && $prodModel->subcategory) {
                                                                            $subName = $prodModel->subcategory->name;
                                                                        } elseif (!empty($item['subcategory'])) {
                                                                            if (is_numeric($item['subcategory'])) {
                                                                                $sub = \App\Models\ProductSubcategory::find($item['subcategory']);
                                                                                $subName = $sub ? $sub->name : 'N/A';
                                                                            } else {
                                                                                $subName = $item['subcategory'];
                                                                            }
                                                                        }

                                                                        $designModel = $productId ? \App\Models\Design::where('product_id', $productId)->first() : null;
                                                                        $designCode = $designModel ? $designModel->design_code : ($item['design_code'] ?? 'N/A');

                                                                        $imageSrc = null;
                                                                        if (!empty($item['image'])) {
                                                                            $imageSrc = str_contains($item['image'], 'images/') ? asset($item['image']) : asset('storage/' . $item['image']);
                                                                        } elseif ($designModel && !empty($designModel->image)) {
                                                                            $imageSrc = str_starts_with($designModel->image, 'storage/') || str_starts_with($designModel->image, 'images/') ? asset($designModel->image) : asset('storage/' . $designModel->image);
                                                                        } elseif ($prodModel && $prodModel->images->count() > 0) {
                                                                            $path = $prodModel->images[0]->path;
                                                                            $imageSrc = str_starts_with($path, 'storage/') || str_starts_with($path, 'images/') ? asset($path) : asset('storage/' . $path);
                                                                        }
                                                                    @endphp
                                                                    <tr class="hover:bg-slate-50">
                                                                        <td class="px-4 py-3">{{ $catName }}</td>
                                                                        <td class="px-4 py-3">
                                                                            <span class="font-bold text-slate-800">{{ $prodName }}</span>
                                                                            <span class="text-xs text-indigo-600 ml-1">(Sub: {{ $subName }})</span>
                                                                            <br><span class="text-xs text-slate-500">Design: {{ $designCode }}</span>
                                                                        </td>
                                                                        <td class="px-4 py-3 text-xs">
                                                                            @if(isset($item['grams']) && is_array($item['grams']))
                                                                                @foreach($item['grams'] as $i => $gram)
                                                                                    <div>{{ $gram }}g × {{ is_array($item['quantity'] ?? null) ? ($item['quantity'][$i] ?? 1) : 1 }} = <strong class="text-slate-800">{{ number_format(is_array($item['individual_totals'] ?? null) ? ($item['individual_totals'][$i] ?? 0) : ($item['individual_totals'] ?? 0), 2) }}g</strong></div>
                                                                                @endforeach
                                                                            @else
                                                                                {{ $item['grams'] ?? 0 }}g × {{ $item['quantity'] ?? 0 }} = <strong class="text-slate-800">{{ number_format((float)($item['grams'] ?? 0) * (float)($item['quantity'] ?? 0), 2) }}g</strong>
                                                                            @endif
                                                                        </td>
                                                                        <td class="px-4 py-3 font-bold text-slate-800">{{ number_format((float)($item['total'] ?? 0), 2) }}g</td>
                                                                        <td class="px-4 py-3">
                                                                            @if($imageSrc)
                                                                                <img src="{{ $imageSrc }}" class="w-12 h-12 object-cover rounded shadow-sm cursor-pointer border border-slate-200" onclick="window.open(this.src, '_blank')" alt="Item Image">
                                                                            @else
                                                                                <span class="text-xs text-slate-400 italic">No Image</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="px-4 py-3 text-xs max-w-xs truncate" title="{{ $item['item_notes'] ?? '-' }}">{{ $item['item_notes'] ?? '-' }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <p class="text-slate-500 text-sm italic">No items found.</p>
                                                @endif
                                            </div>
                                        </template>
                                        <a href="{{ route('admin.purchase-order.show', $po) }}" 
                                           class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all duration-200" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        
                                        @if($tab['id'] == 'created')
                                            <a href="{{ route('admin.purchase-order.edit', ['purchaseOrder' => $po->id, 'return_url' => url()->full()]) }}" 
                                               class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all duration-200" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('admin.purchase-order.allocate', $po) }}" 
                                               class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all duration-200" title="Allocate">
                                                <i class="bi bi-person-plus"></i>
                                            </a>
                                        @endif

                                        @if($tab['id'] == 'rejected')
                                            <form action="{{ route('admin.purchase-order.reallocate', $po) }}" method="POST" class="inline-block" onsubmit="return confirm('Reset this order and move it back to Created tab?');">
                                                @csrf
                                                <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white transition-all duration-200" title="Reallocate">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if($tab['id'] == 'completed')
                                            <a href="{{ route('admin.purchase-order.copy', $po) }}" 
                                               class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all duration-200" title="Copy">
                                                <i class="bi bi-copy"></i>
                                            </a>
                                        @endif

                                        @if($tab['id'] == 'for_approval')
                                            <form action="{{ route('admin.purchase-order.approve', $po) }}" method="POST" class="inline-block">
                                                @csrf 
                                                <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all duration-200" title="Approve">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('admin.purchase-order.destroy', $po) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this PO?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all duration-200" title="Delete">
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
        </div>
        @endforeach
    </div>
</div>

{{-- MODALS --}}
<!-- Bulk Allocate Modal -->
<div class="modal fade" id="bulkAllocateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl rounded-2xl overflow-hidden">
            <form action="{{ route('admin.purchase-order.bulk-allocate') }}" method="POST">
                @csrf
                <div id="selected-ids-container"></div>
                <div class="bg-magenta-600 p-6 flex justify-between items-center text-white">
                    <h5 class="text-xl font-bold flex items-center">
                        <i class="bi bi-people mr-3"></i> Bulk Allocate Orders
                    </h5>
                    <button type="button" class="text-white/80 hover:text-white transition-colors" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg text-lg"></i>
                    </button>
                </div>
                <div class="p-8 bg-white">
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-tight">Assign to Craftsman</label>
                        <select name="craftsman_code" class="select2-bulk !w-full" required>
                            <option value="">Select Craftsman</option>
                            @foreach($craftsmen as $c)
                                <option value="{{ $c->craftman_code }}">{{ $c->craftman_code }} - {{ $c->business_name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-slate-500 italic">This will move selected orders to "Allocated" status.</p>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-tight">Craftsman Due Date</label>
                        <input type="date" name="craftsman_due_date" class="w-full border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-magenta-500">
                    </div>
                </div>
                <div class="p-6 bg-slate-50 border-t border-slate-100 flex gap-3">
                    <button type="button" class="flex-1 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-50 transition-all" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-magenta-600 text-white font-bold rounded-xl hover:bg-magenta-700 transition-all shadow-md shadow-magenta-200">Allocate Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="bulkApproveForm" action="{{ route('admin.purchase-order.bulk-approve') }}" method="POST" style="display:none;">
    @csrf
    <div id="bulk-approve-ids"></div>
</form>

<form id="bulkCompleteForm" action="{{ route('admin.purchase-order.bulk-complete') }}" method="POST" style="display:none;">
    @csrf
    <div id="bulk-complete-ids"></div>
</form>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl rounded-2xl overflow-hidden">
            <div class="bg-emerald-600 p-6 flex justify-between items-center text-white">
                <h5 class="text-xl font-bold flex items-center">
                    <i class="bi bi-download mr-3"></i> Custom Export
                </h5>
                <button type="button" class="text-white/80 hover:text-white transition-colors" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>
            <div class="p-8 bg-white">
                <div class="grid grid-cols-2 gap-4 mb-8" id="exportFields">
                    <div class="space-y-3">
                        <label class="flex items-center p-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:border-emerald-300 transition-all group">
                            <input type="checkbox" value="0" checked class="w-5 h-5 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500 mr-3">
                            <span class="text-sm font-bold text-slate-700 group-hover:text-emerald-700">PO Code</span>
                        </label>
                        <label class="flex items-center p-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:border-emerald-300 transition-all group">
                            <input type="checkbox" value="1" checked class="w-5 h-5 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500 mr-3">
                            <span class="text-sm font-bold text-slate-700 group-hover:text-emerald-700">Due Date</span>
                        </label>
                    </div>
                    <div class="space-y-3">
                        <label class="flex items-center p-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:border-emerald-300 transition-all group">
                            <input type="checkbox" value="2" checked class="w-5 h-5 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500 mr-3">
                            <span class="text-sm font-bold text-slate-700 group-hover:text-emerald-700">Items Count</span>
                        </label>
                        <label class="flex items-center p-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:border-emerald-300 transition-all group">
                            <input type="checkbox" value="3" checked class="w-5 h-5 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500 mr-3">
                            <span class="text-sm font-bold text-slate-700 group-hover:text-emerald-700">Total Weight</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex flex-col gap-3">
                    <button type="button" class="w-full flex items-center justify-center gap-3 px-6 py-4 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100" onclick="triggerExport('excel')">
                        <i class="bi bi-file-earmark-excel text-xl text-white/50"></i> Download Excel
                    </button>
                    <button type="button" class="w-full flex items-center justify-center gap-3 px-6 py-4 bg-white border border-rose-200 text-rose-600 font-bold rounded-xl hover:bg-rose-50 transition-all" onclick="triggerExport('pdf')">
                        <i class="bi bi-file-earmark-pdf text-xl text-rose-300"></i> Download PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Print Form (Hidden) -->
<form id="bulkPrintForm" action="{{ route('admin.purchase-order.bulk-print') }}" method="POST" target="_blank">
    @csrf
    <div id="print-ids-container"></div>
</form>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 with modern styling
    $('.select2-bulk').select2({
        dropdownParent: $('#bulkAllocateModal'),
        placeholder: "Select Craftsman",
        allowClear: true,
        width: '100%',
        theme: "default"
    });

    // Initialize DataTables with minimal controls
    $('.po-datatable').each(function() {
        if (!$.fn.DataTable.isDataTable(this)) {
            $(this).DataTable({
                "order": [
                    [0, "desc"]
                ],
                "pageLength": 10,
                "dom": 'rtip',
                "language": {
                    "emptyTable": "No orders found in this category"
                }
            });
        }
    });

    // Toggle child rows
    $('.po-datatable tbody').on('click', '.toggle-items-btn', function () {
        var tr = $(this).closest('tr');
        var table = $(this).closest('table').DataTable();
        var row = table.row(tr);
        var icon = $(this).find('i');

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
        } else {
            var templateContent = tr.find('.items-template').html();
            row.child(templateContent).show();
            tr.addClass('shown');
            icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
        }
    });

    // Checkbox Logic - Targets visible tab
    $(document).on('change', '.select-all', function() {
        const isChecked = this.checked;
        const subContainer = $(this).closest('.overflow-hidden');
        subContainer.find('.po-checkbox').each(function() {
            $(this).prop('checked', isChecked);
        });
        updateBulkBtns(subContainer);
    });

    $(document).on('change', '.po-checkbox', function() {
        const subContainer = $(this).closest('.overflow-hidden');
        updateBulkBtns(subContainer);
    });

    function updateBulkBtns(container) {
        const count = container.find('.po-checkbox:checked').length;
        if(count > 0) {
            if (container.find('.bulk-allocate-btn').length) container.find('.bulk-allocate-btn').removeClass('hidden');
            if (container.find('.bulk-print-btn').length) container.find('.bulk-print-btn').removeClass('hidden');
            if (container.find('.bulk-approve-btn').length) container.find('.bulk-approve-btn').removeClass('hidden');
            if (container.find('.bulk-start-btn').length) container.find('.bulk-start-btn').removeClass('hidden');
            if (container.find('.bulk-complete-btn').length) container.find('.bulk-complete-btn').removeClass('hidden');
        } else {
            if (container.find('.bulk-allocate-btn').length) container.find('.bulk-allocate-btn').addClass('hidden');
            if (container.find('.bulk-print-btn').length) container.find('.bulk-print-btn').addClass('hidden');
            if (container.find('.bulk-approve-btn').length) container.find('.bulk-approve-btn').addClass('hidden');
            if (container.find('.bulk-start-btn').length) container.find('.bulk-start-btn').addClass('hidden');
            if (container.find('.bulk-complete-btn').length) container.find('.bulk-complete-btn').addClass('hidden');
        }
    }

    // Modal ID Handling
    $('.bulk-allocate-btn').on('click', function() {
        const container = $('#selected-ids-container');
        container.empty();
        const subContainer = $(this).closest('.overflow-hidden');
        subContainer.find('.po-checkbox:checked').each(function() {
            container.append('<input type="hidden" name="order_ids[]" value="'+$(this).val()+'">');
        });
    });

    $('.bulk-approve-btn').on('click', function() {
        if(!confirm('Are you sure you want to approve selected orders?')) return;
        
        const container = $('#bulk-approve-ids');
        container.empty();
        const subContainer = $(this).closest('.overflow-hidden');
        subContainer.find('.po-checkbox:checked').each(function() {
            container.append('<input type="hidden" name="order_ids[]" value="'+$(this).val()+'">');
        });
        $('#bulkApproveForm').submit();
    });

    $('.bulk-complete-btn').on('click', function() {
        if(!confirm('Are you sure you want to mark selected orders as completed?')) return;
        
        const container = $('#bulk-complete-ids');
        container.empty();
        const subContainer = $(this).closest('.overflow-hidden');
        subContainer.find('.po-checkbox:checked').each(function() {
            container.append('<input type="hidden" name="order_ids[]" value="'+$(this).val()+'">');
        });
        $('#bulkCompleteForm').submit();
    });

    $('.bulk-start-btn').on('click', function() {
        if(!confirm('Are you sure you want to start selected orders? This will move them to In Process.')) return;
        
        const container = $('#bulk-complete-ids'); // Use same form but it moves to in_process if status is created
        container.empty();
        const subContainer = $(this).closest('.overflow-hidden');
        subContainer.find('.po-checkbox:checked').each(function() {
            container.append('<input type="hidden" name="order_ids[]" value="'+$(this).val()+'">');
        });
        $('#bulkCompleteForm').submit();
    });
});

function submitBulkPrint() {
    const container = $('#print-ids-container');
    container.empty();
    
    // Find active tab container (visible one)
    const activeSection = $('div[x-show^="activeTab"]:visible');
    const checkedBoxes = activeSection.find('.po-checkbox:checked');
    
    if (checkedBoxes.length === 0) {
        alert('Please select at least one order to print.');
        return;
    }
    
    checkedBoxes.each(function() {
         container.append('<input type="hidden" name="order_ids[]" value="'+$(this).val()+'">');
    });
    
    $('#bulkPrintForm').submit();
}

function triggerExport(type) {
    const selectedFields = [];
    $('#exportFields input:checked').each(function() {
        selectedFields.push($(this).val());
    });
    
    const params = new URLSearchParams(window.location.search);
    params.set('export_type', type);
    params.set('fields', selectedFields.join(','));
    
    window.location.href = "{{ route('admin.purchase-order.export') }}?" + params.toString();
}
</script>
@endsection


