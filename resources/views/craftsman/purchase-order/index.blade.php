@extends('craftsman.layouts.app')

@section('title', 'Purchase Orders')

@section('content')
<div class="">
    <!-- Header Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-emerald-900 tracking-tight">My Purchase Orders</h1>
        @if(Auth::guard('craftsman')->user()->dear)
        <p class="text-emerald-600 mt-1">Welcome back, <span class="font-semibold text-emerald-800">{{ Auth::guard('craftsman')->user()->dear }}</span></p>
        @endif
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 shadow-sm rounded-r-lg flex items-center justify-between" role="alert">
        <div class="flex items-center">
            <i class="bi bi-check-circle-fill mr-3 text-emerald-500 text-xl"></i>
            <p class="font-medium">{{ session('success') }}</p>
        </div>
        <button type="button" class="text-emerald-500 hover:text-emerald-700" onclick="this.parentElement.remove()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    @endif

    @if (session('error'))
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 shadow-sm rounded-r-lg flex items-center justify-between" role="alert">
        <div class="flex items-center">
            <i class="bi bi-exclamation-triangle-fill mr-3 text-red-500 text-xl"></i>
            <p class="font-medium">{{ session('error') }}</p>
        </div>
        <button type="button" class="text-red-500 hover:text-red-700" onclick="this.parentElement.remove()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    @endif

    <!-- Search & Filter Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 mb-8 relative z-20">
        <div class="bg-emerald-50/50 px-6 py-4 border-b border-emerald-100">
            <h3 class="font-bold text-emerald-900 flex items-center">
                <i class="bi bi-filter-left mr-2 text-emerald-600"></i> Search & Filters
            </h3>
        </div>
        <div class="p-6">
            <form action="{{ route('craftsman.purchase-order.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-emerald-600 mb-2">Search POs</label>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-emerald-400 group-focus-within:text-emerald-600 transition-colors">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" class="w-full pl-10 pr-3 py-2 bg-emerald-50/30 border border-emerald-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-emerald-900 placeholder-emerald-300 transition outline-none text-sm" placeholder="Search PO code..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-emerald-600 mb-2">Category</label>
                        <div id="category_search_container" class="relative">
                            <!-- Display -->
                            <div id="category_search_display" class="w-full pl-4 pr-10 py-2 bg-emerald-50/30 border border-emerald-200 rounded-xl focus-within:ring-2 focus-within:ring-emerald-500 focus-within:border-emerald-500 transition-all cursor-pointer flex items-center justify-between text-sm">
                                <span class="truncate text-emerald-900">All Categories</span>
                                <i class="bi bi-chevron-down text-emerald-400 text-[10px]"></i>
                            </div>

                            <!-- Menu -->
                            <div id="category_search_menu" class="absolute z-50 w-full mt-2 bg-white border border-emerald-100 rounded-xl shadow-xl hidden overflow-hidden">
                                <div class="p-2 border-b border-emerald-50 bg-emerald-50/30">
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-emerald-400">
                                            <i class="bi bi-search text-xs"></i>
                                        </span>
                                        <input type="text" id="category_search_input" class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-emerald-100 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Search categories...">
                                    </div>
                                </div>
                                <ul id="category_search_list" class="max-h-60 overflow-y-auto py-1 text-sm no-scrollbar">
                                    <li class="px-4 py-2 text-emerald-600 hover:bg-emerald-50 cursor-pointer transition-colors font-medium border-b border-emerald-50/50" data-value="">
                                        All Categories
                                    </li>
                                    @foreach($productCategories as $cat)
                                    <li class="px-4 py-2 text-emerald-900 hover:bg-emerald-50 cursor-pointer transition-colors" data-value="{{ $cat->id }}">
                                        {{ $cat->name }}
                                    </li>
                                    @endforeach
                                </ul>
                            </div>

                            <!-- Hidden Select -->
                            <input type="hidden" name="product_category_filter" id="product_category_filter_hidden" value="{{ request('product_category_filter') }}">
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-emerald-600 mb-2">Sort By</label>
                        <select name="sort_by" class="w-full px-3 py-2 bg-emerald-50/30 border border-emerald-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-emerald-900 transition cursor-pointer outline-none text-sm appearance-none">
                            <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>ID</option>
                            <option value="purchase_order_code" {{ request('sort_by') == 'purchase_order_code' ? 'selected' : '' }}>PO Code</option>
                            <option value="updated_at" {{ request('sort_by') == 'updated_at' ? 'selected' : '' }}>Date</option>
                        </select>
                    </div>
                    <div class="md:col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-emerald-600 mb-2">Order</label>
                        <select name="sort_order" class="w-full px-3 py-2 bg-emerald-50/30 border border-emerald-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-emerald-900 transition cursor-pointer outline-none text-sm appearance-none">
                            <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>DESC</option>
                            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>ASC</option>
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 rounded-xl shadow-sm transition-all duration-200 flex items-center justify-center text-xs">
                                <i class="bi bi-filter mr-1.5"></i> Apply
                            </button>
                            <a href="{{ route('craftsman.purchase-order.index') }}" class="flex-1 bg-white border border-emerald-200 hover:bg-emerald-50 text-emerald-700 font-bold py-2 rounded-xl transition-all duration-200 flex items-center justify-center text-xs">
                                <i class="bi bi-arrow-counterclockwise mr-1.5"></i> Reset
                            </a>
                            <a href="{{ route('craftsman.purchase-order.export', request()->all()) }}" class="bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-bold p-2 rounded-xl transition-all duration-200 flex items-center justify-center" title="Export to Excel">
                                <i class="bi bi-file-earmark-excel text-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="mb-6 flex flex-wrap gap-2">
         
        <a href="{{ route('craftsman.purchase-order.index', array_merge(request()->except('tab'), ['tab' => 'allocated'])) }}"
            class="px-6 py-3 rounded-full font-bold text-sm transition-all duration-300 {{ request('tab') == 'allocated' || !request('tab') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-100' }}">
            Allocated <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] {{ request('tab') == 'allocated' || !request('tab') ? 'bg-emerald-500/50' : 'bg-emerald-100' }}">{{ $allocatedOrders->total() }}</span>
        </a>
        <a href="{{ route('craftsman.purchase-order.index', array_merge(request()->except('tab'), ['tab' => 'in-process'])) }}"
            class="px-6 py-3 rounded-full font-bold text-sm transition-all duration-300 {{ request('tab') == 'in-process' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-100' }}">
            In Process <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] {{ request('tab') == 'in-process' ? 'bg-emerald-500/50' : 'bg-emerald-100' }}">{{ $inProcessOrders->total() }}</span>
        </a>
               <a href="{{ route('craftsman.purchase-order.index', array_merge(request()->except('tab'), ['tab' => 'overdue'])) }}"
            class="px-6 py-3 rounded-full font-bold text-sm transition-all duration-300 {{ request('tab') == 'overdue' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-100' }}">
            Overdue <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] {{ request('tab') == 'overdue' ? 'bg-emerald-500/50' : 'bg-emerald-100' }}">{{ $overdueOrders->total() }}</span>
        </a>
        <a href="{{ route('craftsman.purchase-order.index', array_merge(request()->except('tab'), ['tab' => 'completed'])) }}"
            class="px-6 py-3 rounded-full font-bold text-sm transition-all duration-300 {{ request('tab') == 'completed' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-100' }}">
            Completed <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] {{ request('tab') == 'completed' ? 'bg-emerald-500/50' : 'bg-emerald-100' }}">{{ $completedOrders->total() }}</span>
        </a>
        <a href="{{ route('craftsman.purchase-order.index', array_merge(request()->except('tab'), ['tab' => 'rejected'])) }}"
            class="px-6 py-3 rounded-full font-bold text-sm transition-all duration-300 {{ request('tab') == 'rejected' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-100' }}">
            Rejected <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] {{ request('tab') == 'rejected' ? 'bg-emerald-500/50' : 'bg-emerald-100' }}">{{ $rejectedOrders->total() }}</span>
        </a>
    </div>

    <!-- Tab Content -->
    <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 overflow-hidden min-h-[400px]">
        @php
        $currentTab = request('tab', 'allocated');
        $orders = match($currentTab) {
        'overdue' => $overdueOrders,
        'in-process' => $inProcessOrders,
        'completed' => $completedOrders,
        'rejected' => $rejectedOrders,
        default => $allocatedOrders,
        };
        $tabTitle = match($currentTab) {
        'overdue' => 'Overdue',
        'in-process' => 'In Process',
        'completed' => 'Completed',
        'rejected' => 'Rejected',
        default => 'Allocated',
        };
        $accentColor = match($currentTab) {
        'overdue' => 'orange',
        'in-process' => 'yellow',
        'completed' => 'green',
        'rejected' => 'red',
        default => 'emerald',
        };
        @endphp

        <div class="bg-emerald-50/50 px-6 py-4 border-b border-emerald-100 flex justify-between items-center">
            <h4 class="font-extrabold text-emerald-900 uppercase tracking-wider text-sm">{{ $tabTitle }} Purchase Orders</h4>
            <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold">{{ $orders->total() }} Total</span>
        </div>

        <div class="p-0">
            @if($orders->count() > 0)
            <form action="{{ route('craftsman.purchase-order.bulk-accept') }}" method="POST" id="bulkActionForm">
                @csrf
                <div class="p-4 border-b border-emerald-50 bg-emerald-50/20 flex flex-wrap justify-between items-center gap-4">
                    <div class="flex gap-2">
                        @if($currentTab == 'allocated')
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-lg shadow-sm transition-all flex items-center" onclick="return confirm('Are you sure you want to accept selected purchase orders?')">
                            <i class="bi bi-check-all mr-2"></i> Bulk Accept
                        </button>
                        <button type="submit" formaction="{{ route('craftsman.purchase-order.bulk-reject') }}" class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-4 py-2 rounded-lg shadow-sm transition-all flex items-center" onclick="return confirm('Are you sure you want to reject selected purchase orders?')">
                            <i class="bi bi-x-circle mr-2"></i> Bulk Reject
                        </button>
                        @endif

                        @if($currentTab == 'in-process')
                        <button type="submit" formaction="{{ route('craftsman.purchase-order.bulk-complete') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-lg shadow-sm transition-all flex items-center" onclick="return confirm('Are you sure you want to mark selected orders as complete? All items in these orders will be sent for approval.')">
                            <i class="bi bi-check-circle mr-2"></i> Bulk Complete
                        </button>
                        @endif
                    </div>
                    <div>
                        <button type="submit" formaction="{{ route('craftsman.purchase-order.print-selected') }}" formmethod="POST" class="bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold px-4 py-2 rounded-lg shadow-sm transition-all flex items-center">
                            <i class="bi bi-printer mr-2"></i> Print Selected
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-emerald-50/30 text-emerald-900 border-b border-emerald-100 uppercase text-[10px] font-bold tracking-widest">
                                <th class="px-6 py-4 w-12 text-center">
                                    <input type="checkbox" id="selectAllOrders" class="w-4 h-4 text-emerald-600 bg-emerald-50 border-emerald-200 rounded focus:ring-emerald-500 cursor-pointer">
                                </th>
                                <th class="px-6 py-4 w-8"></th>
                                <th class="px-6 py-4">PO Code</th>
                                <th class="px-6 py-4">Items Count</th>
                                <th class="px-6 py-4">{{ $currentTab == 'allocated' || $currentTab == 'in-process' ? 'Allocated' : $tabTitle }} Date</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-50">
                            @foreach($orders as $order)
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

                                if ($isOverdue) {
                                    $rowStyle = 'background-color: rgba(255, 228, 230, 0.8) !important;'; // rose
                                } elseif ($isDueWithin48Hours) {
                                    $rowStyle = 'background-color: rgba(255, 237, 213, 0.8) !important;'; // orange
                                } elseif ($currentTabString == 'in-process-orders' || $currentTabString == 'in-process' || $currentTabString == 'in_process') {
                                    $hoursSinceUpdate = $updatedAtValue ? \Carbon\Carbon::parse($updatedAtValue)->diffInHours($now) : 999;
                                    if ($hoursSinceUpdate <= 12) {
                                        $rowStyle = 'background-color: rgba(220, 252, 231, 0.8) !important;'; // green (Recent)
                                    } else {
                                        $rowStyle = 'background-color: rgba(254, 243, 199, 0.8) !important;'; // amber (Older)
                                    }
                                } elseif ($currentTabString == 'allocated-orders' || $currentTabString == 'allocated') {
                                    $hoursSinceUpdate = $updatedAtValue ? \Carbon\Carbon::parse($updatedAtValue)->diffInHours($now) : 999;
                                    if ($hoursSinceUpdate <= 12) {
                                        $rowStyle = 'background-color: rgba(219, 234, 254, 0.8) !important;'; // blue (Recent)
                                    } else {
                                        $rowStyle = 'background-color: rgba(238, 242, 255, 0.8) !important;'; // indigo (Older)
                                    }
                                } elseif ($currentTabString == 'new-orders' || $currentTabString == 'created') {
                                    $rowStyle = 'background-color: rgba(254, 252, 232, 0.8) !important;'; // yellow
                                }
@endphp
                                <tr class="hover:tw-bg-gray-50 tw-transition-colors  hover:bg-emerald-50/50 transition-colors group" style="{{ $rowStyle }}">
                                <td class="px-6 py-4 text-center">
                                    <input type="checkbox" name="purchase_order_ids[]" value="{{ $order->id }}" class="order-checkbox w-4 h-4 text-emerald-600 bg-emerald-100 border-emerald-200 rounded focus:ring-emerald-500 cursor-pointer">
                                </td>
                                <td class="px-6 py-2 text-center">
                                    <button type="button" class="toggle-items-btn w-8 h-8 rounded-full hover:bg-emerald-100 text-emerald-600 transition-all flex items-center justify-center" data-order-id="{{ $order->id }}">
                                        <i class="bi bi-chevron-down transition-transform duration-300"></i>
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-emerald-950 block">{{ $order->purchase_order_code }}</span>
                                    @if($isOverdue)
                                    <span class="text-[10px] font-bold bg-red-100 text-red-700 px-2 py-0.5 rounded-full uppercase">Overdue</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        {{ $order->items ? count($order->items) : 0 }} items
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-emerald-600">
                                    {{ $order->updated_at->format('d M, Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('craftsman.purchase-order.show', $order) }}"
                                        class="inline-flex items-center justify-center p-2 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-800 rounded-lg transition-all" title="View Details">
                                        <i class="bi bi-eye-fill text-lg"></i>
                                    </a>
                                </td>
                            </tr>
                            
                            {{-- Expandable Items Row --}}
                            <tr id="items-row-{{ $order->id }}" class="hidden bg-emerald-50/10">
                                <td colspan="6" class="px-6 py-4">
                                    <div class="bg-white rounded-xl border border-emerald-100 shadow-sm overflow-hidden mb-4">
                                        <div class="bg-emerald-50/50 px-4 py-2 border-b border-emerald-100 flex justify-between items-center">
                                            <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-widest">Order Items</span>
                                            @if($currentTab == 'in-process')
                                            <span class="text-[10px] text-emerald-500 font-medium italic">Select items to mark as completed</span>
                                            @endif
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-left text-xs">
                                                <thead>
                                                    <tr class="bg-white text-emerald-900/60 uppercase text-[9px] font-bold tracking-widest">
                                                        @if($currentTab == 'in-process')
                                                        <th class="px-4 py-3 w-8 text-center">
                                                            <input type="checkbox" class="select-all-items w-3 h-3 text-emerald-600 rounded" data-order-id="{{ $order->id }}">
                                                        </th>
                                                        @endif
                                                        <th class="px-4 py-3">Product / Design</th>
                                                        <th class="px-4 py-3 text-right">Quantity</th>
                                                        <th class="px-4 py-3 text-right">Weight (g)</th>
                                                        <th class="px-4 py-3 text-center">Image</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-emerald-50">
                                                    @foreach($order->items as $idx => $item)
                                                    <tr class="hover:bg-emerald-50/20 transition-colors">
                                                        @if($currentTab == 'in-process')
                                                        <td class="px-4 py-3 text-center">
                                                            <input type="checkbox" name="selected_items[]" value="{{ $idx }}" class="item-checkbox-{{ $order->id }} w-3 h-3 text-emerald-600 rounded" form="itemActionForm-{{ $order->id }}">
                                                        </td>
                                                        @endif
                                                        <td class="px-4 py-3">
                                                            <div class="font-bold text-emerald-950">{{ $item['product_name'] ?? 'N/A' }}</div>
                                                            <div class="text-[10px] text-emerald-500 font-mono">{{ $item['design_code'] ?? 'N/A' }}</div>
                                                        </td>
                                                        <td class="px-4 py-3 text-right">
                                                            <span class="font-medium">{{ is_array($item['quantity'] ?? null) ? array_sum($item['quantity']) : ($item['quantity'] ?? 0) }}</span>
                                                        </td>
                                                        <td class="px-4 py-3 text-right">
                                                            <span class="font-bold text-emerald-700">{{ number_format($item['total'] ?? 0, 2) }}</span>
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            @if(!empty($item['image']))
                                                            <img src="{{ asset($item['image']) }}" class="h-8 w-8 object-cover rounded-md mx-auto shadow-sm border border-emerald-100">
                                                            @else
                                                            <div class="h-8 w-8 bg-emerald-50 rounded-md flex items-center justify-center mx-auto">
                                                                <i class="bi bi-image text-emerald-200"></i>
                                                            </div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        @if($currentTab == 'in-process')
                                        <div class="bg-emerald-50/30 px-4 py-3 border-t border-emerald-100 flex justify-end">
                                            <form id="itemActionForm-{{ $order->id }}" action="{{ route('craftsman.purchase-order.complete-items', $order) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold px-4 py-2 rounded-lg shadow-sm transition-all flex items-center" onclick="return confirm('Complete selected items? This will create a completed part and keep remaining in process.')">
                                                    <i class="bi bi-check-circle-fill mr-1.5"></i> Mark Selected as Complete
                                                </button>
                                            </form>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </form>

            <div class="p-6 border-t border-emerald-50">
                {{ $orders->links('vendor.pagination.tailwind') }}
            </div>

            @else
            <div class="flex flex-col items-center justify-center py-20 px-4 text-center">
                <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-300 mb-4">
                    <i class="bi bi-file-earmark-x text-3xl"></i>
                </div>
                <h5 class="text-emerald-950 font-bold">No purchase orders found</h5>
                <p class="text-emerald-500 text-sm mt-1 max-w-sm">No items matching your criteria were found in the {{ $tabTitle }} tab.</p>
                <a href="{{ route('craftsman.purchase-order.index') }}" class="mt-6 text-emerald-600 font-bold hover:text-emerald-800 transition text-sm">Clear all filters</a>
            </div>
            @endif
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Searchable Dropdown Logic
        const container = document.getElementById('category_search_container');
        const display = document.getElementById('category_search_display');
        const menu = document.getElementById('category_search_menu');
        const input = document.getElementById('category_search_input');
        const list = document.getElementById('category_search_list');
        const hiddenInput = document.getElementById('product_category_filter_hidden');
        const options = list.querySelectorAll('li');

        if (container && display && menu && input && list && hiddenInput) {
            // Set initial display text
            const initialValue = hiddenInput.value;
            if (initialValue) {
                const selectedOption = Array.from(options).find(opt => opt.dataset.value === initialValue);
                if (selectedOption) {
                    display.querySelector('span').textContent = selectedOption.textContent.trim();
                    display.querySelector('span').classList.add('font-bold', 'text-emerald-700');
                }
            }

            display.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.toggle('hidden');
                container.classList.toggle('z-50'); // Ensure container is on top of everything
                if (!menu.classList.contains('hidden')) {
                    input.focus();
                    input.value = ''; // Reset search on open
                    options.forEach(opt => opt.style.display = 'block');
                }
            });

            input.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                options.forEach(opt => {
                    const text = opt.textContent.toLowerCase();
                    opt.style.display = text.includes(searchTerm) ? 'block' : 'none';
                });
            });

            options.forEach(opt => {
                opt.addEventListener('click', () => {
                    const value = opt.dataset.value;
                    const text = opt.textContent.trim();

                    hiddenInput.value = value;
                    display.querySelector('span').textContent = text;

                    if (value === "") {
                        display.querySelector('span').classList.remove('font-bold', 'text-emerald-700');
                        display.querySelector('span').classList.add('text-emerald-900');
                    } else {
                        display.querySelector('span').classList.remove('text-emerald-900');
                        display.querySelector('span').classList.add('font-bold', 'text-emerald-700');
                    }

                    menu.classList.add('hidden');
                });
            });

            document.addEventListener('click', (e) => {
                if (!container.contains(e.target)) {
                    menu.classList.add('hidden');
                }
            });
        }

        // Select All functionality for Orders
        const selectAllCheckbox = document.getElementById('selectAllOrders');
        const checkboxes = document.querySelectorAll('.order-checkbox');

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            // Update Select All if individual checkboxes change
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const checkedCount = Array.from(checkboxes).filter(c => c.checked).length;
                    selectAllCheckbox.checked = checkedCount === checkboxes.length;
                    selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
                });
            });
        }
    });
</script>
@endsection
@endsection
@section('styles')
<style>
  tr[style*="background-color"] > td, tr[style*="background-color"] > th {
      background-color: transparent !important;
  }
</style>
@endsection
