@extends('admin.layouts.app')

@section('title', 'Work Order Management')

@section('content')
<div class="p-6 space-y-6" x-data="{ showFilters: false, activeTab: '{{ request('tab', 'new-orders') }}' }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Work Order Management</h1>
            <p class="text-sm text-slate-500 mt-1">Manage, allocate, and track work orders across all stages.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.work-order.create') }}" class="inline-flex items-center px-4 py-2 bg-pink-700 hover:bg-pink-800 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <i class="bi bi-plus-lg mr-2"></i> Add New
            </a>
            <a href="{{ route('admin.work-order.bulk-upload') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <i class="bi bi-file-earmark-spreadsheet mr-2"></i> Excel Import
            </a>
            <button type="button" onclick="exportSelectedWorkOrders()" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <i class="bi bi-file-earmark-spreadsheet mr-2"></i> Export
            </button>
            <button type="button" onclick="submitBulkPrintWorkOrders()" class="inline-flex items-center px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <i class="bi bi-printer mr-2"></i> Print / Share
            </button>
        </div>
    </div>

    <!-- Filter Toggle -->
    <div>
        <button @click="showFilters = !showFilters" class="inline-flex items-center px-4 py-2 border border-pink-200 text-pink-700 bg-pink-50 hover:bg-pink-100 rounded-lg text-sm font-medium transition-colors">
            <i class="bi bi-funnel mr-2"></i> Toggle Advanced Filters
        </button>
    </div>

    <!-- Filter Section -->
    <div :class="{ 'hidden': !showFilters }"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm"
        x-cloak>
        <form method="GET" action="{{ route('admin.work-order.index') }}" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <input type="hidden" name="tab" x-bind:value="activeTab">
            <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
            <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">

            <!-- Per Page -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Per Page</label>
                <select name="per_page" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all">
                    @foreach([25, 50, 75, 100, 150, 200] as $size)
                    <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Search</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search orders, clients..."
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="bi bi-search text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- BP Code -->
            <div class="relative">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">BP Code</label>
                <div class="relative w-full" id="bp_code_filter_container">
                    <div class="w-full min-h-[40px] px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm flex justify-between items-center cursor-pointer" id="bp_code_filter_display">All BP Codes</div>
                    <div class="absolute top-full left-0 w-full bg-white border border-slate-200 rounded-b-lg shadow-lg z-50 hidden p-2" id="bp_code_filter_menu">
                        <input type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg mb-2 focus:outline-none text-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500" id="bp_code_filter_search" placeholder="Search for an item...">
                        <ul class="max-h-60 overflow-y-auto list-none p-0 m-0" id="bp_code_filter_list">
                            <li class="px-3 py-2 hover:bg-slate-50 cursor-pointer text-sm rounded" data-value="">All BP Codes</li>
                            @foreach($bpCodes as $bpCode)
                            <li class="px-3 py-2 hover:bg-slate-50 cursor-pointer text-sm rounded" data-value="{{ $bpCode->bp_code }}" {{ request('bp_code_filter') == $bpCode->bp_code ? 'selected' : '' }}>
                                {{ $bpCode->bp_code }} - {{ $bpCode->customer_name }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <select name="bp_code_filter" id="bp_code_filter_select" style="display: none;">
                        <option value="">All BP Codes</option>
                        @foreach($bpCodes as $bpCode)
                        <option value="{{ $bpCode->bp_code }}" {{ request('bp_code_filter') == $bpCode->bp_code ? 'selected' : '' }}>{{ $bpCode->bp_code }} - {{ $bpCode->customer_name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Icons for display consistency -->
                <div class="absolute right-3 top-[34px] pointer-events-none text-slate-400">
                    <i class="bi bi-chevron-down text-xs"></i>
                </div>
            </div>

            <!-- Category -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Category</label>
                <select name="category_filter" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem_1.25rem] bg-[right_0.5rem_center] bg-no-repeat transition-all">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_filter') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Craftsman -->
            <div class="relative">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Craftsman</label>
                <div class="relative w-full" id="craftsman_filter_container">
                    <div class="w-full min-h-[40px] px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm flex justify-between items-center cursor-pointer" id="craftsman_filter_display">All Craftsmen</div>
                    <div class="absolute top-full left-0 w-full bg-white border border-slate-200 rounded-b-lg shadow-lg z-50 hidden p-2" id="craftsman_filter_menu">
                        <input type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg mb-2 focus:outline-none text-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500" id="craftsman_filter_search" placeholder="Search for an item...">
                        <ul class="max-h-60 overflow-y-auto list-none p-0 m-0" id="craftsman_filter_list">
                            <li class="px-3 py-2 hover:bg-slate-50 cursor-pointer text-sm rounded" data-value="">All Craftsmen</li>
                            @foreach($craftsmen as $craftsman)
                            <li class="px-3 py-2 hover:bg-slate-50 cursor-pointer text-sm rounded" data-value="{{ $craftsman->craftman_code }}" {{ request('craftsman_filter') == $craftsman->craftman_code ? 'selected' : '' }}>
                                {{ $craftsman->craftman_code }} - {{ $craftsman->name }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <select name="craftsman_filter" id="craftsman_filter_select" style="display: none;">
                        <option value="">All Craftsmen</option>
                        @foreach($craftsmen as $craftsman)
                        <option value="{{ $craftsman->craftman_code }}" {{ request('craftsman_filter') == $craftsman->craftman_code ? 'selected' : '' }}>
                            {{ $craftsman->craftman_code }} - {{ $craftsman->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <!-- Icons for display consistency -->
                <div class="absolute right-3 top-[34px] pointer-events-none text-slate-400">
                    <i class="bi bi-chevron-down text-xs"></i>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium rounded-lg transition-colors">
                    Apply
                </button>
                <a x-bind:href="'{{ route('admin.work-order.index') }}?tab=' + activeTab"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-lg transition-colors">
                    Clear
                </a>
            </div>
        </form>
    </div>

    @if (session('success'))
    <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center gap-3 text-emerald-700 shadow-sm animate-fade-in-down">
        <i class="bi bi-check-circle-fill text-lg"></i>
        <p class="text-sm font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if (session('error'))
    <div class="p-4 rounded-lg bg-rose-50 border border-rose-100 flex items-center gap-3 text-rose-700 shadow-sm animate-fade-in-down">
        <i class="bi bi-exclamation-triangle-fill text-lg"></i>
        <p class="text-sm font-medium">{{ session('error') }}</p>
    </div>
    @endif

    <!-- Tab Navigation -->
    <div id="work-order-tabs">
        <div class="border-b border-slate-200 overflow-x-auto scrollbar-hide">
            <nav class="flex space-x-8 min-w-max px-2" aria-label="Tabs">
                @php
                $tabs = [
                ['id' => 'all-orders', 'label' => 'All Orders', 'count' => $allOrders->total()],
                ['id' => 'new-orders', 'label' => 'New Orders', 'count' => $newOrders->total()],
                ['id' => 'allocated-orders', 'label' => 'Allocated', 'count' => $allocatedOrders->total()],
                ['id' => 'in-process-orders', 'label' => 'In Process', 'count' => $inProcessOrders->total()],
                ['id' => 'overdue-orders', 'label' => 'Overdue', 'count' => $overdueOrders->total()],
                ['id' => 'for-approval-orders', 'label' => 'For Approval', 'count' => $forApprovalOrders->total()],
                ['id' => 'completed-orders', 'label' => 'Completed', 'count' => $completedOrders->total()],
                ['id' => 'rejected-orders', 'label' => 'Rejected', 'count' => $rejectedOrders->total()],
                ];
                @endphp
                @foreach($tabs as $tab)
                <button @click="activeTab = '{{ $tab['id'] }}'; let u = new URL(window.location.href); u.searchParams.set('tab', '{{ $tab['id'] }}'); history.replaceState(null, '', u.toString());"
                    :class="activeTab === '{{ $tab['id'] }}' ? 'border-pink-600 text-pink-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all flex items-center gap-2">
                    {{ $tab['label'] }}
                    <span :class="activeTab === '{{ $tab['id'] }}' ? 'bg-pink-100 text-pink-700' : 'bg-slate-100 text-slate-500'"
                        class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-bold transition-colors">
                        {{ $tab['count'] }}
                    </span>
                </button>
                @endforeach
            </nav>
        </div>

        <div class="mt-6">
            <!-- All Orders Tab Content -->
            <div x-show="activeTab === 'all-orders'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden border-l-4 border-l-slate-400">
                    <!-- Tab Header / Actions -->
                    <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4 flex-1">
                            <form method="GET" class="relative group flex-1 max-w-xs">
                                <input type="hidden" name="tab" value="all-orders">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search all orders..."
                                    class="w-full pl-9 pr-4 py-1.5 bg-white border border-slate-200 rounded-lg text-sm transition-all focus:ring-2 focus:ring-pink-500 focus:border-pink-500 group-hover:border-slate-300">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="bi bi-search text-xs"></i>
                                </div>
                            </form>
                            <div class="h-6 w-px bg-slate-200 hidden md:block"></div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-slate-500">Sort:</span>
                                <form method="GET" class="flex items-center gap-2">
                                    <input type="hidden" name="tab" value="all-orders">
                                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                                    @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                    <select name="sort_by" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                        <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>ID</option>
                                        <option value="work_order_number" {{ request('sort_by') == 'work_order_number' ? 'selected' : '' }}>WO #</option>
                                        <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>Order Date</option>
                                        <option value="quantity" {{ request('sort_by') == 'quantity' ? 'selected' : '' }}>Qty</option>
                                    </select>
                                    <select name="sort_order" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Asc</option>
                                        <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Desc</option>
                                    </select>
                                </form>
                            </div>
                            <div class="h-6 w-px bg-slate-200 hidden md:block"></div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-slate-500">Show:</span>
                                <form method="GET" class="flex items-center gap-2">
                                    <input type="hidden" name="tab" value="all-orders">
                                    @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                    @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
                                    @if(request('sort_order')) <input type="hidden" name="sort_order" value="{{ request('sort_order') }}"> @endif
                                    <select name="per_page" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                        @foreach([ 25, 50, 75, 100, 150, 200] as $size)
                                        <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-full border border-slate-200 shadow-sm">{{ $allOrders->total() }} Total Orders</span>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/20">
                                    <th class="p-4 border-b border-slate-100 w-10 text-center">
                                        <input type="checkbox" id="select-all-all-orders" class="rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                                    </th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Image</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Order Details</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Dates</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Specs</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($allOrders as $order)
                                @php
                                $rowStyle = '';
                                $displayImage = $order->product_image ?? null;
                                $isPdf = $displayImage && \Illuminate\Support\Str::endsWith(strtolower($displayImage), '.pdf');
                                if (!$displayImage && !empty($order->product) && $order->product->images && $order->product->images->count() > 0) {
                                $displayImage = $order->product->images->first()->path;
                                $isPdf = \Illuminate\Support\Str::endsWith(strtolower($displayImage), '.pdf');
                                }

                                $statusClass = match($order->status ?? '') {
                                'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                'allocated' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'in_process' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                'completed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'rejected' => 'bg-rose-100 text-rose-700 border-rose-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };

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
                                        <tr class="hover:tw-bg-gray-50 tw-transition-colors  hover:bg-slate-50/50 transition-colors" style="{{ $rowStyle }}">
                                        <td class="p-4 text-center">
                                            <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="all-order-checkbox rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                                        </td>
                                        <td class="p-4">
                                            @if($displayImage)
                                            <div class="relative w-12 h-12 rounded-lg border border-slate-200 bg-white overflow-hidden shadow-sm hover:ring-2 hover:ring-pink-500 transition-all cursor-zoom-in group"
                                                onclick="openUniversalPreview('{{ asset($displayImage) }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                @if($isPdf)
                                                <canvas class="pdf-canvas w-full h-full object-contain" data-url="{{ asset($displayImage) }}"></canvas>
                                                @else
                                                <img src="{{ asset($displayImage) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" alt="WO">
                                                @endif
                                            </div>
                                            @else
                                            <div class="w-12 h-12 rounded-lg bg-slate-50 flex items-center justify-center border border-dashed border-slate-200">
                                                <i class="bi bi-image text-slate-300 text-lg"></i>
                                            </div>
                                            @endif
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-slate-900">{{ $order->work_order_number }}</span>
                                                <span class="px-2 py-0.5 bg-cyan-100 text-cyan-700 rounded text-[12px] font-bold uppercase">REF : {{ $order->reference_no }}</span>
                                                <!-- <span class="text-[10px] font-bold text-slate-500 uppercase">{{ $order->bp_code ?? 'NO BP' }}</span> -->
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-slate-800">{{ $order->bp_code }}</span>
                                                <span class="text-sm font-medium text-slate-800">{{ $order->customer_name }}</span>
                                                <!-- <span class="text-xs text-slate-600 italic">{{ $order->product_category }} | {{ $order->quantity }} {{ $order->type }}</span>
                                            <span class="text-sm font-medium text-slate-800">Craftsman Notes:{{ $order->narration_craftsman }}</span> -->
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="space-y-1">
                                                <div class="text-sm font-bold text-slate-800">
                                                    <i class="bi bi-calendar-event text-xs"></i>
                                                    {{ $order->due_date ? $order->created_at->format('d M, Y') : 'N/A' }}
                                                </div>
                                                <div class="flex items-center gap-1.5 text-sm {{ $isOverdue ? 'text-red-700 font-bold' : 'text-red-600 font-bold' }}">
                                                    <i class="bi bi-alarm text-xs"></i>
                                                    {{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'N/A' }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-wrap gap-1.5">
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Category : {{ $order->product_category }}</span>
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Sub Category : {{ $order->subcategory }}</span>
                                            </div>
                                            <br>
                                            <div class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold uppercase">
                                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded text-[12px] font-bold">{{ $order->quantity }} {{ $order->type }}</span>
                                                <!-- <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] font-bold capitalize">{{ $order->type}}</span> -->
                                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[12px] font-bold">Weight : {{ $order->weight_from }}</span>
                                                @if($order->size)
                                                <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[12px] font-bold">Size: {{ $order->size }}</span>
                                                @endif
                                                <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[12px] font-bold">Length: {{ $order->length ?: 'N/A' }}</span>
                                            </div>
                                            <br>
                                            <div class="flex flex-wrap gap-1">
                                                @if($order->narration_craftsman)
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Craftsman Notes: {{ $order->narration_craftsman }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClass }} uppercase tracking-wider">
                                                {{ str_replace('_', ' ', $order->status) }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-right">
                                            <div class="inline-flex items-center gap-1">
                                                <a href="{{ route('admin.work-order.show', $order) }}" class="p-1.5 bg-sky-50 text-sky-600 hover:bg-sky-100 rounded-lg transition-colors" title="View">
                                                    <i class="bi bi-eye text-sm text-[16px]"></i>
                                                </a>
                                            </div>
                                        </td>
                                        </tr>
                                        @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer / Pagination -->
                    <div class="p-4 bg-light border-top d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <span class="small fw-medium text-secondary">
                            Showing {{ $allOrders->firstItem() }} to {{ $allOrders->lastItem() }} of {{ $allOrders->total() }} entries
                        </span>
                        <div class="pagination-container custom-pagination">
                            {{ $allOrders->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
            <!-- New Orders Tab Content -->
            <div x-show="activeTab === 'new-orders'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <!-- Tab Header / Actions -->
                    <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4 flex-1">
                            <form method="GET" class="relative group flex-1 max-w-xs">
                                <input type="hidden" name="tab" value="new-orders">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search nested orders..."
                                    class="w-full pl-9 pr-4 py-1.5 bg-white border border-slate-200 rounded-lg text-sm transition-all focus:ring-2 focus:ring-pink-500 focus:border-pink-500 group-hover:border-slate-300">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="bi bi-search text-xs"></i>
                                </div>
                            </form>
                            <div class="h-6 w-px bg-slate-200 hidden md:block"></div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-slate-500">Sort:</span>
                                <form method="GET" class="flex items-center gap-2">
                                    <input type="hidden" name="tab" value="new-orders">
                                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                                    @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                    <select name="sort_by" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                        <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>ID</option>
                                        <option value="work_order_number" {{ request('sort_by') == 'work_order_number' ? 'selected' : '' }}>WO #</option>
                                        <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>Order Date</option>
                                        <option value="quantity" {{ request('sort_by') == 'quantity' ? 'selected' : '' }}>Qty</option>
                                    </select>
                                    <select name="sort_order" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Asc</option>
                                        <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Desc</option>
                                    </select>
                                </form>
                            </div>
                            <div class="h-6 w-px bg-slate-200 hidden md:block"></div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-slate-500">Show:</span>
                                <form method="GET" class="flex items-center gap-2">
                                    <input type="hidden" name="tab" value="new-orders">
                                    @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                    @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
                                    @if(request('sort_order')) <input type="hidden" name="sort_order" value="{{ request('sort_order') }}"> @endif
                                    <select name="per_page" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                        @foreach([25, 50, 75, 100, 150, 200] as $size)
                                        <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <form id="bulk-allocate-form" method="GET" action="{{ route('admin.work-order.bulk-allocate-form') }}">
                                <button type="submit" id="bulk-allocate-btn" disabled class="inline-flex items-center px-3 py-1.5 bg-pink-700 hover:bg-pink-800 disabled:bg-slate-300 disabled:cursor-not-allowed text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                                    <i class="bi bi-people-fill mr-1.5"></i> Bulk Allocate
                                </button>
                            </form>
                            <button type="button" onclick="submitBulkComplete()" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                                <i class="bi bi-check-all mr-1.5"></i> Bulk Complete
                            </button>
                            <div class="h-6 w-px bg-slate-200"></div>
                            <span class="text-xs font-medium text-slate-600 bg-slate-100 px-2 py-1 rounded-md">{{ $newOrders->total() }} Total</span>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="p-4 border-b border-slate-100 w-10 text-center">
                                        <input type="checkbox" id="select-all-new-orders" class="rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                                    </th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Image</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Order Details</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Dates</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Specs</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($newOrders as $order)
                                @php
                                $rowStyle = '';
                                $displayImage = $order->product_image ?? null;
                                $isPdf = $displayImage && \Illuminate\Support\Str::endsWith(strtolower($displayImage), '.pdf');
                                if (!$displayImage && !empty($order->product) && $order->product->images && $order->product->images->count() > 0) {
                                $displayImage = $order->product->images->first()->path;
                                $isPdf = \Illuminate\Support\Str::endsWith(strtolower($displayImage), '.pdf');
                                }

                                $statusClass = match($order->status ?? '') {
                                'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                'allocated' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'in_process' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                'completed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'rejected' => 'bg-rose-100 text-rose-700 border-rose-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };

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
                                        <tr class="hover:tw-bg-gray-50 tw-transition-colors  transition-all" style="{{ $rowStyle }}">
                                        <td class="p-4 text-center">
                                            <input type="checkbox" name="work_order_ids[]" form="bulk-allocate-form" value="{{ $order->id }}" class="new-order-checkbox rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                                        </td>
                                        <td class="p-4">
                                            @if($displayImage)
                                            <div class="relative w-12 h-12 rounded-lg border border-slate-200 bg-white overflow-hidden shadow-sm hover:ring-2 hover:ring-pink-500 transition-all cursor-zoom-in group"
                                                onclick="openUniversalPreview('{{ asset($displayImage) }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                @if($isPdf)
                                                <canvas class="pdf-canvas w-full h-full object-contain" data-url="{{ asset($displayImage) }}"></canvas>
                                                @else
                                                <img src="{{ asset($displayImage) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" alt="WO">
                                                @endif
                                                @if($order->product && $order->product->images->count() > 1)
                                                <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <span class="text-white text-[10px] font-bold">+{{ $order->product->images->count() - 1 }}</span>
                                                </div>
                                                @endif
                                            </div>
                                            @else
                                            <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center border border-dashed border-slate-300">
                                                <i class="bi bi-image text-slate-400 text-lg"></i>
                                            </div>
                                            @endif
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-slate-800">{{ $order->work_order_number }}</span>
                                                <!-- <span class="text-[10px] font-medium text-slate-500 uppercase">{{ $order->bp_code ?? 'NO BP' }}</span> -->
                                                <span class="px-2 py-0.5 bg-cyan-100 text-cyan-700 rounded text-[12px] font-bold uppercase">REF : {{ $order->reference_no }}</span>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-slate-700">{{ $order->bp_code ?? 'NO BP' }}</span>
                                                <span class="text-sm font-medium text-slate-700">{{ $order->customer_name }}</span>
                                                <!-- <span class="text-xs text-slate-500 italic">via {{ $order->buyer->dear ?? '-' }}</span> -->
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="space-y-1">
                                                <div class="text-sm font-bold text-slate-800">
                                                    <i class="bi bi-calendar-event text-xs"></i>
                                                    {{ $order->due_date ? $order->created_at->format('d M, Y') : 'N/A' }}
                                                </div>
                                                <div class="flex items-center gap-1.5 text-sm {{ $isOverdue ? 'text-red-700 font-bold' : 'text-red-600 font-bold' }}">
                                                    <i class="bi bi-alarm text-xs"></i>
                                                    {{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'N/A' }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-wrap gap-1.5">
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Category : {{ $order->product_category }}</span>
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Sub Category : {{ $order->subcategory }}</span>
                                            </div>
                                            <br>
                                            <div class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold uppercase">
                                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded text-[12px] font-bold">{{ $order->quantity }} {{ $order->type }}</span>
                                                <!-- <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] font-bold capitalize">{{ $order->type}}</span> -->
                                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[12px] font-bold">Weight : {{ $order->weight_from }}</span>
                                                @if($order->size)
                                                <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[12px] font-bold">Size: {{ $order->size }}</span>
                                                @endif
                                                <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[12px] font-bold">Length: {{ $order->length ?: 'N/A' }}</span>
                                            </div>
                                            <br>
                                            <div class="flex flex-wrap gap-1">
                                                @if($order->narration_craftsman)
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Craftsman Notes: {{ $order->narration_craftsman }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClass }} uppercase tracking-wider">
                                                {{ str_replace('_', ' ', $order->status) }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-right">
                                            <div class="inline-flex items-center gap-1">
                                                <a href="{{ route('admin.work-order.show', $order) }}" class="p-1.5 bg-sky-50 text-sky-600 hover:bg-sky-100 rounded-lg transition-colors" title="View">
                                                    <i class="bi bi-eye text-sm text-[16px]"></i>
                                                </a>
                                                <a href="{{ route('admin.work-order.edit', ['workOrder' => $order->id, 'return_url' => url()->full()]) }}" class="p-1.5 bg-pink-50 text-pink-600 hover:bg-pink-100 rounded-lg transition-colors" title="Edit">
                                                    <i class="bi bi-pencil text-sm text-[16px]"></i>
                                                </a>
                                                <a href="{{ route('admin.work-order.allocate.form', $order) }}" class="p-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 rounded-lg transition-colors" title="Allocate">
                                                    <i class="bi bi-person-plus text-sm text-[16px]"></i>
                                                </a>
                                                <form action="{{ route('admin.work-order.destroy', $order) }}" method="POST" class="inline" onsubmit="return confirm('Delete this order?');">
                                                    @csrf @method('DELETE')
                                                    <input type="hidden" name="tab" x-bind:value="activeTab">
                                                    <button class="p-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg transition-colors">
                                                        <i class="bi bi-trash text-sm text-[16px]"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        </tr>
                                        @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer / Pagination -->
                    <div class="p-4 bg-light border-top d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <span class="small fw-medium text-secondary">
                            Showing {{ $newOrders->firstItem() }} to {{ $newOrders->lastItem() }} of {{ $newOrders->total() }} entries
                        </span>
                        <div class="pagination-container custom-pagination">
                            {{ $newOrders->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Allocated Orders Tab Content -->
            <div x-show="activeTab === 'allocated-orders'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <!-- Tab Header / Actions -->
                    <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4 flex-1">
                            <form method="GET" class="relative group flex-1 max-w-xs">
                                <input type="hidden" name="tab" value="allocated-orders">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search allocated..."
                                    class="w-full pl-9 pr-4 py-1.5 bg-white border border-slate-200 rounded-lg text-sm transition-all focus:ring-2 focus:ring-pink-500 focus:border-pink-500 group-hover:border-slate-300">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="bi bi-search text-xs"></i>
                                </div>
                            </form>
                            <div class="h-6 w-px bg-slate-200 hidden md:block"></div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-slate-500">Sort:</span>
                                <form method="GET" class="flex items-center gap-2">
                                    <input type="hidden" name="tab" value="allocated-orders">
                                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                                    @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                    <select name="sort_by" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                        <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>ID</option>
                                        <option value="work_order_number" {{ request('sort_by') == 'work_order_number' ? 'selected' : '' }}>WO #</option>
                                        <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>Order Date</option>
                                        <option value="quantity" {{ request('sort_by') == 'quantity' ? 'selected' : '' }}>Qty</option>
                                    </select>
                                    <select name="sort_order" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Asc</option>
                                        <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Desc</option>
                                    </select>
                                </form>
                            </div>
                            <div class="h-6 w-px bg-slate-200 hidden md:block"></div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-slate-500">Show:</span>
                                <form method="GET" class="flex items-center gap-2">
                                    <input type="hidden" name="tab" value="allocated-orders">
                                    @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                    @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
                                    @if(request('sort_order')) <input type="hidden" name="sort_order" value="{{ request('sort_order') }}"> @endif
                                    <select name="per_page" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                        @foreach([25, 50, 75, 100, 150, 200] as $size)
                                        <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="submitBulkComplete()" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                                <i class="bi bi-check-all mr-1.5"></i> Bulk Complete
                            </button>
                            <span class="text-xs font-bold text-blue-700 bg-blue-100 px-2.5 py-1 rounded-full border border-blue-200 shadow-sm">{{ $allocatedOrders->total() }} Allocated Orders</span>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="p-4 border-b border-slate-100 w-10 text-center">
                                        <input type="checkbox" id="select-all-allocated-orders" class="rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                                    </th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Image</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Order Details</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Dates</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Specs</th>
                                    <!--<th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Craftsman</th>-->
                                    <!-- <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th> -->
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($allocatedOrders as $order)
                                @php
                                $rowStyle = '';
                                $displayImage = $order->product_image ?? null;
                                $isPdf = $displayImage && \Illuminate\Support\Str::endsWith(strtolower($displayImage), '.pdf');
                                if (!$displayImage && !empty($order->product) && $order->product->images && $order->product->images->count() > 0) {
                                $displayImage = $order->product->images->first()->path;
                                $isPdf = \Illuminate\Support\Str::endsWith(strtolower($displayImage), '.pdf');
                                }

                                $statusClass = match($order->status ?? '') {
                                'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                'allocated' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'in_process' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                'completed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'rejected' => 'bg-rose-100 text-rose-700 border-rose-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };

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
                                        <tr class="hover:tw-bg-gray-50 tw-transition-colors  transition-all" style="{{ $rowStyle }}">
                                        <td class="p-4 text-center">
                                            <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="allocated-order-checkbox rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                                        </td>
                                        <td class="p-4">
                                            @if($displayImage)
                                            <div class="relative w-12 h-12 rounded-lg border border-slate-200 bg-white overflow-hidden shadow-sm hover:ring-2 hover:ring-pink-500 transition-all cursor-zoom-in group"
                                                onclick="openUniversalPreview('{{ asset($displayImage) }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                @if($isPdf)
                                                <canvas class="pdf-canvas w-full h-full object-contain" data-url="{{ asset($displayImage) }}"></canvas>
                                                @else
                                                <img src="{{ asset($displayImage) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" alt="WO">
                                                @endif
                                            </div>
                                            @else
                                            <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center border border-dashed border-slate-300">
                                                <i class="bi bi-image text-slate-400 text-lg"></i>
                                            </div>
                                            @endif
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-slate-800">{{ $order->work_order_number }}</span>
                                                <!-- <span class="text-[10px] font-medium text-slate-500 uppercase">{{ $order->bp_code ?? 'NO BP' }}</span> -->
                                                <span class="px-2 py-0.5 bg-cyan-100 text-cyan-700 rounded text-[12px] font-bold uppercase">REF : {{ $order->reference_no }}</span>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-slate-800">{{ $order->bp_code ?? 'NO BP' }}</span>
                                                <span class="text-sm font-bold text-slate-800">{{ $order->customer_name }}</span>
                                                <span class="text-sm font-bold text-red-500">{{ $order->craftsman->craftman_code ?? 'N/A' }}</span>
                                                <span class="text-sm font-bold text-red-500">{{ $order->craftsman ? $order->craftsman->name : 'No name'}}</span>
                                                <!-- <span class="text-xs text-slate-500 italic">via {{ $order->buyer->dear ?? '-' }}</span> -->
                                            </div>
                                            <!-- <span class="text-xs text-slate-500 italic">Craftsman Notes:{{ $order->narration_craftsman }}</span> -->
                    </div>
                    </td>
                    <td class="p-4">
                        <div class="space-y-1">
                            <div class="text-sm font-bold text-slate-800">
                                <i class="bi bi-calendar-event text-xs"></i>
                                {{ $order->due_date ? $order->created_at->format('d M, Y') : 'N/A' }}
                            </div>
                            <div class="flex items-center gap-1.5 text-sm {{ $isOverdue ? 'text-red-700 font-bold' : 'text-red-600 font-bold' }}">
                                <i class="bi bi-alarm text-xs"></i>
                                {{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'N/A' }}
                            </div>
                        </div>
                    </td>
                    <td class="p-4">
                        <div class="flex flex-wrap gap-1.5">
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Category : {{ $order->product_category }}</span>
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Sub Category : {{ $order->subcategory }}</span>
                        </div>
                        <br>
                        <div class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold uppercase">
                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded text-[12px] font-bold">{{ $order->quantity }} {{ $order->type }}</span>
                            <!-- <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] font-bold capitalize">{{ $order->type}}</span> -->
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[12px] font-bold">Weight : {{ $order->weight_from }}</span>
                            @if($order->size)
                            <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[12px] font-bold">Size: {{ $order->size }}</span>
                            @endif
                                                <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[12px] font-bold">Length: {{ $order->length ?: 'N/A' }}</span>
                        </div>
                        <br>
                        <div class="flex flex-wrap gap-1">
                            @if($order->narration_craftsman)
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Craftsman Notes: {{ $order->narration_craftsman }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="p-4">
                        <!--<div class="flex flex-col">-->
                        <!--    <span class="text-sm font-semibold text-slate-700">{{ $order->craftsman->craftman_code ?? 'N/A' }}</span>-->
                        <!--    <span class="text-[10px] text-slate-500 uppercase">Allocated</span>-->
                        <!--</div>-->
                    </td>
                    <!-- <td class="p-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClass }} uppercase tracking-wider">
                                            {{ str_replace('_', ' ', $order->status) }}
                                        </span>
                                    </td> -->
                    <td class="p-4 text-right">
                        <div class="inline-flex items-center gap-1">
                            <a href="{{ route('admin.work-order.show', $order) }}" class="p-1.5 bg-sky-50 text-sky-600 hover:bg-sky-100 rounded-lg transition-colors" title="View">
                                <i class="bi bi-eye text-sm text-[16px]"></i>
                            </a>
                            <a href="{{ route('admin.work-order.edit', ['workOrder' => $order->id, 'return_url' => url()->full()]) }}" class="p-1.5 bg-pink-50 text-pink-600 hover:bg-pink-100 rounded-lg transition-colors" title="Edit">
                                <i class="bi bi-pencil text-sm text-[16px]"></i>
                            </a>
                            <a href="{{ route('admin.work-order.reallocate.form', $order) }}" class="p-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg transition-colors" title="Reallocate">
                                <i class="bi bi-arrow-repeat text-sm text-[16px]"></i>
                            </a>
                        </div>
                    </td>
                    </tr>
                    @endforeach
                    </tbody>
                    </table>
                </div>

                <!-- Footer / Pagination -->
                <div class="p-4 bg-light border-top d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <span class="small fw-medium text-secondary">
                        Showing {{ $allocatedOrders->firstItem() }} to {{ $allocatedOrders->lastItem() }} of {{ $allocatedOrders->total() }} entries
                    </span>
                    <div class="pagination-container custom-pagination">
                        {{ $allocatedOrders->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- In Process Orders Tab Content -->
        <div x-show="activeTab === 'in-process-orders'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <!-- Tab Header / Actions -->
                <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4 flex-1">
                        <form method="GET" class="relative group flex-1 max-w-xs">
                            <input type="hidden" name="tab" value="in-process-orders">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search in process..."
                                class="w-full pl-9 pr-4 py-1.5 bg-white border border-slate-200 rounded-lg text-sm transition-all focus:ring-2 focus:ring-pink-500 focus:border-pink-500 group-hover:border-slate-300">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="bi bi-search text-xs"></i>
                            </div>
                        </form>
                        <div class="h-6 w-px bg-slate-200 hidden md:block"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-slate-500">Sort:</span>
                            <form method="GET" class="flex items-center gap-2">
                                <input type="hidden" name="tab" value="in-process-orders">
                                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                <select name="sort_by" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                    <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>ID</option>
                                    <option value="work_order_number" {{ request('sort_by') == 'work_order_number' ? 'selected' : '' }}>WO #</option>
                                    <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>Order Date</option>
                                    <option value="quantity" {{ request('sort_by') == 'quantity' ? 'selected' : '' }}>Qty</option>
                                </select>
                                <select name="sort_order" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Asc</option>
                                    <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Desc</option>
                                </select>
                            </form>
                        </div>
                        <div class="h-6 w-px bg-slate-200 hidden md:block"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-slate-500">Show:</span>
                            <form method="GET" class="flex items-center gap-2">
                                <input type="hidden" name="tab" value="in-process-orders">
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
                                @if(request('sort_order')) <input type="hidden" name="sort_order" value="{{ request('sort_order') }}"> @endif
                                <select name="per_page" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                    @foreach([25, 50, 75, 100, 150, 200] as $size)
                                    <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="submitBulkComplete()" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                            <i class="bi bi-check-all mr-1.5"></i> Bulk Complete
                        </button>
                        <span class="text-xs font-medium text-slate-600 bg-sky-50 text-sky-700 px-2.5 py-1 rounded-full border border-sky-100">{{ $inProcessOrders->total() }} In Process</span>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="p-4 border-b border-slate-100 w-10 text-center">
                                    <input type="checkbox" id="select-all-in-process-orders" class="rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                                </th>
                                <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Image</th>
                                <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Order Details</th>
                                <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
                                <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Dates</th>
                                <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Specs</th>
                                <!--<th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Craftsman</th>-->
                                <!-- <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th> -->
                                <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($inProcessOrders as $order)
                            @php
                            $rowStyle = '';
                            $displayImage = $order->product_image ?? null;
                            $isPdf = $displayImage && \Illuminate\Support\Str::endsWith(strtolower($displayImage), '.pdf');
                            if (!$displayImage && !empty($order->product) && $order->product->images && $order->product->images->count() > 0) {
                            $displayImage = $order->product->images->first()->path;
                            $isPdf = \Illuminate\Support\Str::endsWith(strtolower($displayImage), '.pdf');
                            }

                            $statusClass = match($order->status ?? '') {
                            'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                            'allocated' => 'bg-blue-100 text-blue-700 border-blue-200',
                            'in_process' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                            'completed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            'rejected' => 'bg-rose-100 text-rose-700 border-rose-200',
                            default => 'bg-slate-100 text-slate-700 border-slate-200'
                            };

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
                                    <tr class="hover:tw-bg-gray-50 tw-transition-colors  transition-all" style="{{ $rowStyle }}">
                                    <td class="p-4 text-center">
                                        <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="in-process-order-checkbox rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                                    </td>
                                    <td class="p-4">
                                        @if($displayImage)
                                        <div class="relative w-12 h-12 rounded-lg border border-slate-200 bg-white overflow-hidden shadow-sm hover:ring-2 hover:ring-pink-500 transition-all cursor-zoom-in group"
                                            onclick="openUniversalPreview('{{ asset($displayImage) }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                            @if($isPdf)
                                            <canvas class="pdf-canvas w-full h-full object-contain" data-url="{{ asset($displayImage) }}"></canvas>
                                            @else
                                            <img src="{{ asset($displayImage) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" alt="WO">
                                            @endif
                                        </div>
                                        @else
                                        <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center border border-dashed border-slate-300">
                                            <i class="bi bi-image text-slate-400 text-lg"></i>
                                        </div>
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-800">{{ $order->work_order_number }}</span>
                                            <!-- <span class="text-[10px] font-medium text-slate-500 uppercase">{{ $order->bp_code ?? 'NO BP' }}</span> -->
                                            <span class="px-2 py-0.5 bg-cyan-100 text-cyan-700 rounded text-[12px] font-bold uppercase">REF : {{ $order->reference_no }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-800">{{ $order->bp_code ?? 'NO BP' }}</span>
                                            <span class="text-sm font-bold text-slate-800">{{ $order->customer_name }}</span>
                                            <span class="text-sm font-bold text-red-500">{{ $order->craftsman->craftman_code ?? 'N/A' }}</span>
                                            <span class="text-sm font-bold text-red-500">{{ $order->craftsman ? $order->craftsman->name : 'No name'}}</span>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="space-y-1">
                                            <div class="text-sm font-bold text-slate-800">
                                                <i class="bi bi-calendar-event text-xs"></i>
                                                {{ $order->due_date ? $order->created_at->format('d M, Y') : 'N/A' }}
                                            </div>
                                            <div class="flex items-center gap-1.5 text-sm {{ $isOverdue ? 'text-red-700 font-bold' : 'text-red-600 font-bold' }}">
                                                <i class="bi bi-alarm text-xs"></i>
                                                {{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'N/A' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-wrap gap-1.5">
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Category : {{ $order->product_category }}</span>
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Sub Category : {{ $order->subcategory }}</span>
                                        </div>
                                        <br>
                                        <div class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold uppercase">
                                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded text-[12px] font-bold">{{ $order->quantity }} {{ $order->type }}</span>
                                            <!-- <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] font-bold capitalize">{{ $order->type}}</span> -->
                                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[12px] font-bold">Weight : {{ $order->weight_from }}</span>
                                            @if($order->size)
                                            <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[12px] font-bold">Size: {{ $order->size }}</span>
                                            @endif
                                                <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[12px] font-bold">Length: {{ $order->length ?: 'N/A' }}</span>
                                        </div>
                                        <br>
                                        <div class="flex flex-wrap gap-1">
                                            @if($order->narration_craftsman)
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Craftsman Notes: {{ $order->narration_craftsman }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <!--<td class="p-4">-->
                                    <!--    <div class="flex flex-col">-->
                                    <!--        <span class="text-sm font-semibold text-slate-700">{{ $order->craftsman->craftman_code ?? 'N/A' }}</span>-->
                                    <!--        <span class="text-[10px] text-slate-500 uppercase">In Process</span>-->
                                    <!--    </div>-->
                                    <!--</td>-->
                                    <!-- <td class="p-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider">
                                            IN Process
                                        </span>
                                    </td> -->
                                    <td class="p-4 text-right">
                                        <div class="inline-flex items-center gap-1">
                                            <button type="button" onclick="openAdminUndoModal({{ $order->id }}, {{ $order->admin_undo_count }})" class="p-1.5 bg-purple-50 text-purple-600 hover:bg-purple-100 rounded-lg transition-colors" title="Undo Status">
                                                <i class="bi bi-arrow-counterclockwise text-sm text-[16px]"></i>
                                            </button>
                                            <a href="{{ route('admin.work-order.show', $order) }}" class="p-1.5 bg-sky-50 text-sky-600 hover:bg-sky-100 rounded-lg transition-colors" title="View">
                                                <i class="bi bi-eye text-sm text-[16px]"></i>
                                            </a>
                                        </div>
                                    </td>
                                    </tr>
                                    @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer / Pagination -->
                <div class="p-4 bg-light border-top d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <span class="small fw-medium text-secondary">
                        Showing {{ $inProcessOrders->firstItem() }} to {{ $inProcessOrders->lastItem() }} of {{ $inProcessOrders->total() }} entries
                    </span>
                    <div class="pagination-container custom-pagination">
                        {{ $inProcessOrders->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Orders Tab Content -->
        <div x-show="activeTab === 'overdue-orders'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden border-l-4 border-l-rose-500">
                <!-- Tab Header / Actions -->
                <div class="p-4 border-b border-slate-100 bg-rose-50/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4 flex-1">
                        <form method="GET" class="relative group flex-1 max-w-xs">
                            <input type="hidden" name="tab" value="overdue-orders">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search overdue..."
                                class="w-full pl-9 pr-4 py-1.5 bg-white border border-slate-200 rounded-lg text-sm transition-all focus:ring-2 focus:ring-rose-500 focus:border-rose-500 group-hover:border-slate-300">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-rose-400">
                                <i class="bi bi-search text-xs"></i>
                            </div>
                        </form>
                        <div class="h-6 w-px bg-rose-200 hidden md:block"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-slate-500">Sort:</span>
                            <form method="GET" class="flex items-center gap-2">
                                <input type="hidden" name="tab" value="overdue-orders">
                                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                <select name="sort_by" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-rose-500">
                                    <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>ID</option>
                                    <option value="work_order_number" {{ request('sort_by') == 'work_order_number' ? 'selected' : '' }}>WO #</option>
                                    <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>Order Date</option>
                                    <option value="quantity" {{ request('sort_by') == 'quantity' ? 'selected' : '' }}>Qty</option>
                                </select>
                                <select name="sort_order" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-rose-500">
                                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Asc</option>
                                    <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Desc</option>
                                </select>
                            </form>
                        </div>
                        <div class="h-6 w-px bg-rose-200 hidden md:block"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-slate-500">Show:</span>
                            <form method="GET" class="flex items-center gap-2">
                                <input type="hidden" name="tab" value="overdue-orders">
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
                                @if(request('sort_order')) <input type="hidden" name="sort_order" value="{{ request('sort_order') }}"> @endif
                                <select name="per_page" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-rose-500">
                                    @foreach([25, 50, 75, 100, 150, 200] as $size)
                                    <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="submitBulkComplete()" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                            <i class="bi bi-check-all mr-1.5"></i> Bulk Complete
                        </button>
                        <span class="text-xs font-bold text-rose-700 bg-rose-100 px-2.5 py-1 rounded-full border border-rose-200 shadow-sm animate-pulse">{{ $overdueOrders->total() }} Critical Overdue</span>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-red-50/20">
                                <th class="p-4 border-b border-rose-100 w-10 text-center">
                                    <input type="checkbox" id="select-all-overdue-orders" class="rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                                </th>
                                <th class="p-4 border-b border-rose-100 text-xs font-semibold text-rose-700 uppercase tracking-wider">Image</th>
                                <th class="p-4 border-b border-rose-100 text-xs font-semibold text-rose-700 uppercase tracking-wider">Order Details</th>
                                <th class="p-4 border-b border-rose-100 text-xs font-semibold text-rose-700 uppercase tracking-wider">Customer</th>
                                <th class="p-4 border-b border-rose-100 text-xs font-semibold text-rose-700 uppercase tracking-wider">Dates</th>
                                <th class="p-4 border-b border-rose-100 text-xs font-semibold text-rose-700 uppercase tracking-wider">Specs</th>
                                <!-- <th class="p-4 border-b border-rose-100 text-xs font-semibold text-rose-700 uppercase tracking-wider">Status</th> -->
                                <th class="p-4 border-b border-rose-100 text-xs font-semibold text-rose-700 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rose-100">
                            @foreach($overdueOrders as $order)
                            @php
                            $rowStyle = '';
                            $displayImage = $order->product_image ?? null;
                            $isPdf = $displayImage && \Illuminate\Support\Str::endsWith(strtolower($displayImage), '.pdf');
                            if (!$displayImage && !empty($order->product) && $order->product->images && $order->product->images->count() > 0) {
                            $displayImage = $order->product->images->first()->path;
                            $isPdf = \Illuminate\Support\Str::endsWith(strtolower($displayImage), '.pdf');
                            }

                            $statusClass = match($order->status ?? '') {
                            'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                            'allocated' => 'bg-blue-100 text-blue-700 border-blue-200',
                            'in_process' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                            'completed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            'rejected' => 'bg-rose-100 text-rose-700 border-rose-200',
                            default => 'bg-slate-100 text-slate-700 border-slate-200'
                            };

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
                                    <tr class="hover:tw-bg-gray-50 tw-transition-colors  transition-all" style="{{ $rowStyle }}">
                                    <td class="p-4 text-center">
                                        <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="overdue-order-checkbox rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                                    </td>
                                    <td class="p-4">
                                        @if($displayImage)
                                        <div class="relative w-12 h-12 rounded-lg border border-rose-200 bg-white overflow-hidden shadow-sm hover:ring-2 hover:ring-rose-500 transition-all cursor-zoom-in group"
                                            onclick="openUniversalPreview('{{ asset($displayImage) }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                            @if($isPdf)
                                            <canvas class="pdf-canvas w-full h-full object-contain" data-url="{{ asset($displayImage) }}"></canvas>
                                            @else
                                            <img src="{{ asset($displayImage) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" alt="WO">
                                            @endif
                                        </div>
                                        @else
                                        <div class="w-12 h-12 rounded-lg bg-rose-50 flex items-center justify-center border border-dashed border-rose-300">
                                            <i class="bi bi-image text-rose-300 text-lg"></i>
                                        </div>
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-rose-900">{{ $order->work_order_number }}</span>
                                            <span class="px-2 py-0.5 bg-cyan-100 text-cyan-700 rounded text-[12px] font-bold uppercase">REF : {{ $order->reference_no }}</span>
                                            <!-- <span class="text-[10px] font-bold text-rose-600 uppercase">{{ $order->bp_code ?? 'NO BP' }}</span> -->
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-slate-700">{{ $order->bp_code ?? 'NO BP' }}</span>
                                            <span class="text-sm font-medium text-rose-800">{{ $order->customer_name }}</span>
                                            <!-- <span class="text-xs text-rose-600 italic">{{ $order->product_category }} | {{ $order->quantity }} {{ $order->type }}</span>
                                        <span class="text-sm font-medium text-rose-800">Craftsman Notes:{{ $order->narration_craftsman }}</span> -->
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="space-y-1">
                                            <div class="text-sm font-bold text-slate-800">
                                                <i class="bi bi-calendar-event text-xs"></i>
                                                {{ $order->due_date ? $order->created_at->format('d M, Y') : 'N/A' }}
                                            </div>
                                            <div class="flex items-center gap-1.5 text-sm {{ $isOverdue ? 'text-red-700 font-bold' : 'text-red-600 font-bold' }}">
                                                <i class="bi bi-alarm text-xs"></i>
                                                {{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'N/A' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-wrap gap-1.5">
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Category : {{ $order->product_category }}</span>
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Sub Category : {{ $order->subcategory }}</span>
                                        </div>
                                        <br>
                                        <div class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold uppercase">
                                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded text-[12px] font-bold">{{ $order->quantity }} {{ $order->type }}</span>
                                            <!-- <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] font-bold capitalize">{{ $order->type}}</span> -->
                                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[12px] font-bold">Weight : {{ $order->weight_from }}</span>
                                            @if($order->size)
                                            <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[12px] font-bold">Size: {{ $order->size }}</span>
                                            @endif
                                                <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[12px] font-bold">Length: {{ $order->length ?: 'N/A' }}</span>
                                        </div>
                                        <br>
                                        <div class="flex flex-wrap gap-1">
                                            @if($order->narration_craftsman)
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Craftsman Notes: {{ $order->narration_craftsman }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-rose-700">{{ $order->status }}</span>
                                            <span class="text-[10px] text-rose-500 uppercase">{{ $order->craftsman->craftman_code ?? 'NO CRAFTSMAN' }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4 text-right">
                                        <div class="inline-flex items-center gap-1">
                                            <a href="{{ route('admin.work-order.show', $order) }}" class="p-1.5 bg-sky-50 text-sky-600 hover:bg-sky-100 rounded-lg transition-colors" title="View">
                                                <i class="bi bi-eye text-sm text-[16px]"></i>
                                            </a>
                                            <a href="{{ route('admin.work-order.edit', ['workOrder' => $order->id, 'return_url' => url()->full()]) }}" class="p-1.5 bg-pink-50 text-pink-600 hover:bg-pink-100 rounded-lg transition-colors" title="Edit">
                                                <i class="bi bi-pencil text-sm text-[16px]"></i>
                                            </a>
                                        </div>
                                    </td>
                                    </tr>
                                    @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer / Pagination -->
                <div class="p-4 bg-light border-top d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <span class="small fw-medium text-secondary">
                        Showing {{ $overdueOrders->firstItem() }} to {{ $overdueOrders->lastItem() }} of {{ $overdueOrders->total() }} entries
                    </span>
                    <div class="pagination-container custom-pagination">
                        {{ $overdueOrders->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- For Approval Orders Tab Content -->
        <div x-show="activeTab === 'for-approval-orders'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden border-l-4 border-l-emerald-500">
                <!-- Tab Header / Actions -->
                <div class="p-4 border-b border-slate-100 bg-emerald-50/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4 flex-1">
                        <form method="GET" class="relative group flex-1 max-w-xs">
                            <input type="hidden" name="tab" value="for-approval-orders">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search for approval..."
                                class="w-full pl-9 pr-4 py-1.5 bg-white border border-slate-200 rounded-lg text-sm transition-all focus:ring-2 focus:ring-emerald-500 focus:border-pink-500 group-hover:border-slate-300">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-emerald-400">
                                <i class="bi bi-search text-xs"></i>
                            </div>
                        </form>
                        <div class="h-6 w-px bg-emerald-200 hidden md:block"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-slate-500">Sort:</span>
                            <form method="GET" class="flex items-center gap-2">
                                <input type="hidden" name="tab" value="for-approval-orders">
                                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                <select name="sort_by" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-emerald-500">
                                    <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>ID</option>
                                    <option value="work_order_number" {{ request('sort_by') == 'work_order_number' ? 'selected' : '' }}>WO #</option>
                                    <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>Order Date</option>
                                    <option value="quantity" {{ request('sort_by') == 'quantity' ? 'selected' : '' }}>Qty</option>
                                </select>
                                <select name="sort_order" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-emerald-500">
                                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Asc</option>
                                    <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Desc</option>
                                </select>
                            </form>
                        </div>
                        <div class="h-6 w-px bg-emerald-200 hidden md:block"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-slate-500">Show:</span>
                            <form method="GET" class="flex items-center gap-2">
                                <input type="hidden" name="tab" value="for-approval-orders">
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
                                @if(request('sort_order')) <input type="hidden" name="sort_order" value="{{ request('sort_order') }}"> @endif
                                <select name="per_page" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-emerald-500">
                                    @foreach([25, 50, 75, 100, 150, 200] as $size)
                                    <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-full border border-emerald-200 shadow-sm">{{ $forApprovalOrders->total() }} Pending Approval</span>
                    </div>
                </div>

                <form id="bulk-approve-form" method="POST" action="{{ route('admin.work-order.bulk-approve') }}">
                    @csrf
                    <input type="hidden" name="tab" x-bind:value="activeTab">
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-emerald-50/20">
                                    <th class="p-4 border-b border-emerald-100 w-10 text-center">
                                        <input type="checkbox" id="select-all-approval-orders" class="rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                                    </th>
                                    <th class="p-4 border-b border-emerald-100 text-xs font-semibold text-emerald-700 uppercase tracking-wider">Image</th>
                                    <th class="p-4 border-b border-emerald-100 text-xs font-semibold text-emerald-700 uppercase tracking-wider">Order Details</th>
                                    <th class="p-4 border-b border-emerald-100 text-xs font-semibold text-emerald-700 uppercase tracking-wider">Customer</th>
                                    <th class="p-4 border-b border-emerald-100 text-xs font-semibold text-emerald-700 uppercase tracking-wider">Dates</th>
                                    <th class="p-4 border-b border-emerald-100 text-xs font-semibold text-emerald-700 uppercase tracking-wider">Specs</th>
                                    <th class="p-4 border-b border-emerald-100 text-xs font-semibold text-emerald-700 uppercase tracking-wider">Craftsman</th>
                                    <!-- <th class="p-4 border-b border-emerald-100 text-xs font-semibold text-emerald-700 uppercase tracking-wider">Status</th> -->
                                    <th class="p-4 border-b border-emerald-100 text-xs font-semibold text-emerald-700 uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-100">
                                @foreach($forApprovalOrders as $order)
                                @php
                                $rowStyle = '';
                                $displayImage = $order->product_image ?? null;
                                $isPdf = $displayImage && \Illuminate\Support\Str::endsWith(strtolower($displayImage), '.pdf');
                                if (!$displayImage && !empty($order->product) && $order->product->images && $order->product->images->count() > 0) {
                                $displayImage = $order->product->images->first()->path;
                                $isPdf = \Illuminate\Support\Str::endsWith(strtolower($displayImage), '.pdf');
                                }

                                $statusClass = match($order->status ?? '') {
                                'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                'allocated' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'in_process' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                'completed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'rejected' => 'bg-rose-100 text-rose-700 border-rose-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };

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
                                        <tr class="hover:tw-bg-gray-50 tw-transition-colors  transition-all" style="{{ $rowStyle }}">
                                        <td class="p-4 text-center">
                                            <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="approval-order-checkbox rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                                        </td>
                                        <td class="p-4">
                                            @if($displayImage)
                                            <div class="relative w-12 h-12 rounded-lg border border-emerald-200 bg-white overflow-hidden shadow-sm hover:ring-2 hover:ring-emerald-500 transition-all cursor-zoom-in group"
                                                onclick="openUniversalPreview('{{ asset($displayImage) }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                @if($isPdf)
                                                <canvas class="pdf-canvas w-full h-full object-contain" data-url="{{ asset($displayImage) }}"></canvas>
                                                @else
                                                <img src="{{ asset($displayImage) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" alt="WO">
                                                @endif
                                            </div>
                                            @else
                                            <div class="w-12 h-12 rounded-lg bg-emerald-50 flex items-center justify-center border border-dashed border-emerald-200">
                                                <i class="bi bi-image text-emerald-300 text-lg"></i>
                                            </div>
                                            @endif
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-emerald-900">{{ $order->work_order_number }}</span>
                                                <!-- <span class="text-sm font-medium text-slate-700">{{ $order->bp_code ?? 'NO BP' }}</span> -->
                                                <span class="px-2 py-0.5 bg-cyan-100 text-cyan-700 rounded text-[12px] font-bold uppercase">REF : {{ $order->reference_no }}</span>
                                                <!-- <span class="text-[10px] font-bold text-emerald-600 uppercase">{{ $order->bp_code ?? 'NO BP' }}</span> -->
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-slate-700">{{ $order->bp_code ?? 'NO BP' }}</span>
                                                <span class="text-sm font-medium text-emerald-800">{{ $order->customer_name }}</span>
                                                <!-- <span class="text-xs text-emerald-600 italic">{{ $order->product_category }} | {{ $order->quantity }} {{ $order->type }}</span>
                                                <span class="text-sm font-medium text-emerald-800">Craftsman Notes:{{ $order->narration_craftsman }}</span> -->
                                            </div>
                                        </td>

                                        <td class="p-4">
                                            <div class="space-y-1">
                                                <div class="text-sm font-bold text-slate-800">
                                                    <i class="bi bi-calendar-event text-xs"></i>
                                                    {{ $order->due_date ? $order->created_at->format('d M, Y') : 'N/A' }}
                                                </div>
                                                <div class="flex items-center gap-1.5 text-sm {{ $isOverdue ? 'text-red-700 font-bold' : 'text-red-600 font-bold' }}">
                                                    <i class="bi bi-alarm text-xs"></i>
                                                    {{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'N/A' }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-wrap gap-1.5">
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Category : {{ $order->product_category }}</span>
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Sub Category : {{ $order->subcategory }}</span>
                                            </div>
                                            <br>
                                            <div class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold uppercase">
                                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded text-[12px] font-bold">{{ $order->quantity }} {{ $order->type }}</span>
                                                <!-- <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] font-bold capitalize">{{ $order->type}}</span> -->
                                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[12px] font-bold">Weight : {{ $order->weight_from }}</span>
                                                @if($order->size)
                                                <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[12px] font-bold">Size: {{ $order->size }}</span>
                                                @endif
                                                <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[12px] font-bold">Length: {{ $order->length ?: 'N/A' }}</span>
                                            </div>
                                            <br>
                                            <div class="flex flex-wrap gap-1">
                                                @if($order->narration_craftsman)
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Craftsman Notes: {{ $order->narration_craftsman }}</span>
                                                @endif
                                            </div>
                                        </td>

                                        <td class="p-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-semibold text-emerald-700">{{ $order->craftsman->craftman_code ?? 'N/A' }}</span>
                                                <span class="text-[10px] text-emerald-500 uppercase">Wait Approval</span>
                                            </div>
                                        </td>

                                        <td class="p-4 text-right">
                                            <div class="inline-flex items-center gap-1">
                                                <button type="button" onclick="openAdminUndoModal({{ $order->id }}, {{ $order->admin_undo_count }})" class="p-1.5 bg-purple-50 text-purple-600 hover:bg-purple-100 rounded-lg transition-colors" title="Undo Status">
                                                    <i class="bi bi-arrow-counterclockwise text-sm text-[16px]"></i>
                                                </button>
                                                <a href="{{ route('admin.work-order.show', $order) }}" class="p-1.5 bg-sky-50 text-sky-600 hover:bg-sky-100 rounded-lg transition-colors" title="View">
                                                    <i class="bi bi-eye text-sm text-[16px]"></i>
                                                </a>
                                                <form method="POST" action="{{ route('admin.work-order.approve', $order) }}" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="tab" x-bind:value="activeTab">
                                                    <button type="submit" class="p-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 rounded-lg transition-colors" title="Approve">
                                                        <i class="bi bi-check2-circle text-sm text-[16px]"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        </tr>
                                        @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-t border-slate-100">
                        <button type="submit" id="bulk-approve-btn" disabled
                            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold shadow-sm hover:bg-emerald-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="bi bi-check2-circle"></i>
                            Bulk Approve Selected
                        </button>
                    </div>
                </form>

                <!-- Footer / Pagination -->
                <div class="p-4 bg-light border-top d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <span class="small fw-medium text-secondary">
                        Showing {{ $forApprovalOrders->firstItem() }} to {{ $forApprovalOrders->lastItem() }} of {{ $forApprovalOrders->total() }} entries
                    </span>
                    <div class="pagination-container custom-pagination">
                        {{ $forApprovalOrders->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Orders Tab Content -->
        <div x-show="activeTab === 'completed-orders'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <!-- Tab Header / Actions -->
                <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4 flex-1">
                        <form method="GET" class="relative group flex-1 max-w-xs">
                            <input type="hidden" name="tab" value="completed-orders">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search completed..."
                                class="w-full pl-9 pr-4 py-1.5 bg-white border border-slate-200 rounded-lg text-sm transition-all focus:ring-2 focus:ring-pink-500 focus:border-pink-500 group-hover:border-slate-300">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="bi bi-search text-xs"></i>
                            </div>
                        </form>
                        <div class="h-6 w-px bg-slate-200 hidden md:block"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-slate-500">Sort:</span>
                            <form method="GET" class="flex items-center gap-2">
                                <input type="hidden" name="tab" value="completed-orders">
                                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                <select name="sort_by" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                    <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>ID</option>
                                    <option value="work_order_number" {{ request('sort_by') == 'work_order_number' ? 'selected' : '' }}>WO #</option>
                                    <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>Order Date</option>
                                    <option value="quantity" {{ request('sort_by') == 'quantity' ? 'selected' : '' }}>Qty</option>
                                </select>
                                <select name="sort_order" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Asc</option>
                                    <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Desc</option>
                                </select>
                            </form>
                        </div>
                        <div class="h-6 w-px bg-slate-200 hidden md:block"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-slate-500">Show:</span>
                            <form method="GET" class="flex items-center gap-2">
                                <input type="hidden" name="tab" value="completed-orders">
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
                                @if(request('sort_order')) <input type="hidden" name="sort_order" value="{{ request('sort_order') }}"> @endif
                                <select name="completed_filter" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                    <option value="">All Time</option>
                                    <option value="day" {{ request('completed_filter') == 'day' ? 'selected' : '' }}>Today</option>
                                    <option value="week" {{ request('completed_filter') == 'week' ? 'selected' : '' }}>This Week</option>
                                    <option value="month" {{ request('completed_filter') == 'month' ? 'selected' : '' }}>This Month</option>
                                </select>
                                <select name="per_page" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                    @foreach([25, 50, 75, 100, 150, 200] as $size)
                                    <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-600 bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full border border-emerald-100">{{ $completedOrders->total() }} Completed</span>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="p-4 border-b border-slate-100 w-10 text-center">
                                    <input type="checkbox" id="select-all-completed-orders" class="rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                                </th>
                                <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Image</th>
                                <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Order Details</th>
                                <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
                                <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Dates</th>
                                <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Specs</th>
                                <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Craftsman</th>
                                <!-- <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th> -->
                                <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($completedOrders as $order)
                            @php
                            $rowStyle = '';
                            $displayImage = $order->product_image ?? null;
                            $isPdf = $displayImage && \Illuminate\Support\Str::endsWith(strtolower($displayImage), '.pdf');
                            if (!$displayImage && !empty($order->product) && $order->product->images && $order->product->images->count() > 0) {
                            $displayImage = $order->product->images->first()->path;
                            $isPdf = \Illuminate\Support\Str::endsWith(strtolower($displayImage), '.pdf');
                            }

                            $statusClass = match($order->status ?? '') {
                            'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                            'allocated' => 'bg-blue-100 text-blue-700 border-blue-200',
                            'in_process' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                            'completed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            'rejected' => 'bg-rose-100 text-rose-700 border-rose-200',
                            default => 'bg-slate-100 text-slate-700 border-slate-200'
                            };

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
                                    <tr class="hover:tw-bg-gray-50 tw-transition-colors  hover:bg-slate-50 transition-colors" style="{{ $rowStyle }}">
                                    <td class="p-4 text-center">
                                        <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="completed-order-checkbox rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                                    </td>
                                    <td class="p-4">
                                        @if($displayImage)
                                        <div class="relative w-12 h-12 rounded-lg border border-slate-200 bg-white overflow-hidden shadow-sm hover:ring-2 hover:ring-pink-500 transition-all cursor-zoom-in group"
                                            onclick="openUniversalPreview('{{ asset($displayImage) }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                            @if($isPdf)
                                            <canvas class="pdf-canvas w-full h-full object-contain" data-url="{{ asset($displayImage) }}"></canvas>
                                            @else
                                            <img src="{{ asset($displayImage) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" alt="WO">
                                            @endif
                                        </div>
                                        @else
                                        <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center border border-dashed border-slate-300">
                                            <i class="bi bi-image text-slate-400 text-lg"></i>
                                        </div>
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-800">{{ $order->work_order_number }}</span>
                                            <span class="px-2 py-0.5 bg-cyan-100 text-cyan-700 rounded text-[12px] font-bold uppercase">REF : {{ $order->reference_no }}</span>
                                            <!-- <span class="text-[10px] font-medium text-slate-500 uppercase">{{ $order->bp_code ?? 'NO BP' }}</span> -->
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-col">
                                            <!-- <span class="text-sm font-medium text-slate-700">{{ $order->bp_code }}</span> -->
                                            <span class="text-sm font-medium text-slate-700">{{ $order->bp_code ?? 'NO BP' }}</span>
                                            <span class="text-sm font-medium text-slate-700">{{ $order->customer_name }}</span>
                                            <!-- <span class="text-xs text-slate-500 italic">{{ $order->product_category }} | {{ $order->quantity }} {{ $order->type }}</span>
                                        <span class="text-sm font-medium text-slate-700">Craftsman Notes:{{ $order->narration_craftsman }}</span> -->
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="space-y-1">
                                            <div class="text-sm font-bold text-slate-800">
                                                <i class="bi bi-calendar-event text-xs"></i>
                                                {{ $order->due_date ? $order->created_at->format('d M, Y') : 'N/A' }}
                                            </div>
                                            <div class="flex items-center gap-1.5 text-sm {{ $isOverdue ? 'text-red-700 font-bold' : 'text-red-600 font-bold' }}">
                                                <i class="bi bi-alarm text-xs"></i>
                                                {{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'N/A' }}
                                            </div>
                                            <div class="flex items-center gap-1.5 text-sm {{ strtolower($order->status) === 'completed' ? 'text-green-600 font-bold' : ($isOverdue ? 'text-red-700 font-bold' : 'text-red-600 font-bold') }}">
                                                @if(strtolower($order->status) === 'completed')
                                                <i class="bi bi-check-circle text-xs"></i>
                                                {{ $order->updated_at ? $order->updated_at->format('d M, Y h:i A') : 'N/A' }}
                                                @else
                                                <i class="bi bi-alarm text-xs"></i>
                                                Not Completed
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-wrap gap-1.5">
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Category : {{ $order->product_category }}</span>
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Sub Category : {{ $order->subcategory }}</span>
                                        </div>
                                        <br>
                                        <div class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold uppercase">
                                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded text-[12px] font-bold">{{ $order->quantity }} {{ $order->type }}</span>
                                            <!-- <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] font-bold capitalize">{{ $order->type}}</span> -->
                                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[12px] font-bold">Weight : {{ $order->weight_from }}</span>
                                            @if($order->size)
                                            <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[12px] font-bold">Size: {{ $order->size }}</span>
                                            @endif
                                                <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[12px] font-bold">Length: {{ $order->length ?: 'N/A' }}</span>
                                        </div>
                                        <br>
                                        <div class="flex flex-wrap gap-1">
                                            @if($order->narration_craftsman)
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Craftsman Notes: {{ $order->narration_craftsman }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-slate-700">{{ $order->craftsman->craftman_code ?? 'N/A' }}</span>
                                            <span class="text-[10px] text-emerald-600 font-bold uppercase">Completed</span>
                                        </div>
                                    </td>
                                    <!-- <td class="p-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClass }} uppercase tracking-wider">
                                        {{ str_replace('_', ' ', $order->status) }}
                                    </span>
                                </td> -->
                                    <td class="p-4 text-right">
                                        <div class="inline-flex items-center gap-1">
                                            <button type="button" onclick="openAdminUndoModal({{ $order->id }}, {{ $order->admin_undo_count }})" class="p-1.5 bg-purple-50 text-purple-600 hover:bg-purple-100 rounded-lg transition-colors" title="Undo Status">
                                                <i class="bi bi-arrow-counterclockwise text-sm text-[16px]"></i>
                                            </button>
                                            <a href="{{ route('admin.work-order.show', $order) }}" class="p-1.5 bg-sky-50 text-sky-600 hover:bg-sky-100 rounded-lg transition-colors" title="View">
                                                <i class="bi bi-eye text-sm text-[16px]"></i>
                                            </a>
                                            <!-- <a href="{{ route('admin.work-order.reallocate.form', $order) }}" class="p-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg transition-colors" title="Reallocate">
                                            <i class="bi bi-arrow-repeat text-sm text-[16px]"></i>
                                        </a> -->
                                            <a href="{{ route('admin.work-order.copy', $order) }}" class="p-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg transition-colors" title="Copy">
                                                <i class="bi bi-clipboard text-[16px] text-amber-600"></i>
                                            </a>
                                            <!-- <a href="{{ route('admin.work-order.copy', $order) }}" class="p-1.5 bg-emerald-50 text-emerald-600 hover:bg-black-100 rounded-lg transition-colors" title="Copy">
                                            <i class="bi bi-copy text-sm text-[16px]"></i>
                                        </a> -->
                                        </div>
                                    </td>
                                    </tr>
                                    @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer / Pagination -->
                <div class="p-4 bg-light border-top d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <span class="small fw-medium text-secondary">
                        Showing {{ $completedOrders->firstItem() }} to {{ $completedOrders->lastItem() }} of {{ $completedOrders->total() }} entries
                    </span>
                    <div class="pagination-container custom-pagination">
                        {{ $completedOrders->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejected Orders Tab Content -->
        <div x-show="activeTab === 'rejected-orders'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <!-- Tab Header / Actions -->
                <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4 flex-1">
                        <form method="GET" class="relative group flex-1 max-w-xs">
                            <input type="hidden" name="tab" value="rejected-orders">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search rejected..."
                                class="w-full pl-9 pr-4 py-1.5 bg-white border border-slate-200 rounded-lg text-sm transition-all focus:ring-2 focus:ring-pink-500 focus:border-pink-500 group-hover:border-slate-300">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="bi bi-search text-xs"></i>
                            </div>
                        </form>
                        <div class="h-6 w-px bg-slate-200 hidden md:block"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-slate-500">Sort:</span>
                            <form method="GET" class="flex items-center gap-2">
                                <input type="hidden" name="tab" value="rejected-orders">
                                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                <select name="sort_by" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                    <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>ID</option>
                                    <option value="work_order_number" {{ request('sort_by') == 'work_order_number' ? 'selected' : '' }}>WO #</option>
                                    <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>Order Date</option>
                                    <option value="quantity" {{ request('sort_by') == 'quantity' ? 'selected' : '' }}>Qty</option>
                                </select>
                                <select name="sort_order" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Asc</option>
                                    <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Desc</option>
                                </select>
                            </form>
                        </div>
                        <div class="h-6 w-px bg-slate-200 hidden md:block"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-slate-500">Show:</span>
                            <form method="GET" class="flex items-center gap-2">
                                <input type="hidden" name="tab" value="rejected-orders">
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
                                @if(request('sort_order')) <input type="hidden" name="sort_order" value="{{ request('sort_order') }}"> @endif
                                <select name="per_page" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                    @foreach([25, 50, 75, 100, 150, 200] as $size)
                                    <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-600 bg-rose-50 text-rose-700 px-2.5 py-1 rounded-full border border-rose-100">{{ $rejectedOrders->total() }} Rejected</span>
                    </div>
                </div>

                <form id="bulk-reallocate-form" method="GET" action="{{ route('admin.work-order.bulk-allocate-form') }}">
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="p-4 border-b border-slate-100 w-10 text-center">
                                        <input type="checkbox" id="select-all-rejected-orders" class="rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                                    </th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Image</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Order Details</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Dates</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Specs</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">Reason</th>
                                    <th class="p-4 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($rejectedOrders as $order)
                                @php
                                $rowStyle = '';
                                $displayImage = $order->product_image ?? null;
                                $isPdf = $displayImage && \Illuminate\Support\Str::endsWith(strtolower($displayImage), '.pdf');
                                if (!$displayImage && !empty($order->product) && $order->product->images && $order->product->images->count() > 0) {
                                $displayImage = $order->product->images->first()->path;
                                $isPdf = \Illuminate\Support\Str::endsWith(strtolower($displayImage), '.pdf');
                                }

                                $statusClass = match($order->status ?? '') {
                                'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                'allocated' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'in_process' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                'completed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'rejected' => 'bg-rose-100 text-rose-700 border-rose-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };

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
                                        <tr class="hover:tw-bg-gray-50 tw-transition-colors  transition-all" style="{{ $rowStyle }}">
                                        <td class="p-4 text-center">
                                            <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="rejected-order-checkbox rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                                        </td>
                                        <td class="p-4">
                                            @if($displayImage)
                                            <div class="relative w-12 h-12 rounded-lg border border-slate-200 bg-white overflow-hidden shadow-sm hover:ring-2 hover:ring-pink-500 transition-all cursor-zoom-in group"
                                                onclick="openUniversalPreview('{{ asset($displayImage) }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                @if($isPdf)
                                                <canvas class="pdf-canvas w-full h-full object-contain" data-url="{{ asset($displayImage) }}"></canvas>
                                                @else
                                                <img src="{{ asset($displayImage) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" alt="WO">
                                                @endif
                                            </div>
                                            @else
                                            <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center border border-dashed border-slate-300">
                                                <i class="bi bi-image text-slate-400 text-lg"></i>
                                            </div>
                                            @endif
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-slate-800">{{ $order->work_order_number }}</span>
                                                <span class="px-2 py-0.5 bg-cyan-100 text-cyan-700 rounded text-[12px] font-bold uppercase">REF : {{ $order->reference_no }}</span>
                                                <!-- <span class="text-[10px] font-medium text-slate-500 uppercase">{{ $order->bp_code ?? 'NO BP' }}</span> -->
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-slate-700">{{ $order->bp_code ?? 'NO BP' }}</span>
                                                <span class="text-sm font-medium text-slate-700">{{ $order->customer_name }}</span>
                                                <!-- <span class="text-xs text-slate-500 italic">{{ $order->product_category }} | {{ $order->quantity }} {{ $order->type }}</span>
                                            <span class="text-sm font-medium text-slate-700">Craftsman Notes :{{ $order->narration_craftsman }}</span> -->
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="space-y-1">
                                                <div class="text-sm font-bold text-slate-800">
                                                    <i class="bi bi-calendar-event text-xs"></i>
                                                    {{ $order->due_date ? $order->created_at->format('d M, Y') : 'N/A' }}
                                                </div>
                                                <div class="flex items-center gap-1.5 text-sm {{ $isOverdue ? 'text-red-700 font-bold' : 'text-red-600 font-bold' }}">
                                                    <i class="bi bi-alarm text-xs"></i>
                                                    {{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'N/A' }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-wrap gap-1.5">
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Category : {{ $order->product_category }}</span>
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Sub Category : {{ $order->subcategory }}</span>
                                            </div>
                                            <br>
                                            <div class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold uppercase">
                                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded text-[12px] font-bold">{{ $order->quantity }} {{ $order->type }}</span>
                                                <!-- <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] font-bold capitalize">{{ $order->type}}</span> -->
                                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[12px] font-bold">Weight : {{ $order->weight_from }}</span>
                                                @if($order->size)
                                                <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[12px] font-bold">Size: {{ $order->size }}</span>
                                                @endif
                                                <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[12px] font-bold">Length: {{ $order->length ?: 'N/A' }}</span>
                                            </div>
                                            <br>
                                            <div class="flex flex-wrap gap-1">
                                                @if($order->narration_craftsman)
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[12px] font-bold uppercase">Craftsman Notes: {{ $order->narration_craftsman }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <p class="text-xs text-slate-600 italic line-clamp-2 max-w-[200px]" title="{{ $order->rejection_reason ?? 'No reason' }}">
                                                {{ $order->rejection_reason ?? '-' }}
                                            </p>
                                        </td>
                                        <td class="p-4 text-right">
                                            <div class="inline-flex items-center gap-1">
                                                <a href="{{ route('admin.work-order.show', $order) }}" class="p-1.5 bg-sky-50 text-sky-600 hover:bg-sky-100 rounded-lg transition-colors" title="View">
                                                    <i class="bi bi-eye text-sm text-[16px]"></i>
                                                </a>
                                                <a href="{{ route('admin.work-order.reallocate.form', $order) }}" class="p-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg transition-colors" title="Reallocate">
                                                    <i class="bi bi-arrow-repeat text-sm text-[16px]"></i>
                                                </a>
                                            </div>
                                        </td>
                                        </tr>
                                        @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-t border-slate-100">
                        <button type="submit" id="bulk-reallocate-btn" disabled
                            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-semibold shadow-sm hover:bg-amber-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="bi bi-arrow-repeat"></i>
                            Bulk Reallocate Selected
                        </button>
                    </div>
                </form>

                <!-- Footer / Pagination -->
                <div class="p-4 bg-light border-top d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <span class="small fw-medium text-secondary">
                        Showing {{ $rejectedOrders->firstItem() }} to {{ $rejectedOrders->lastItem() }} of {{ $rejectedOrders->total() }} entries
                    </span>
                    <div class="pagination-container custom-pagination">
                        {{ $rejectedOrders->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    // Global Update Bulk Buttons Function
    function updateBulkButtons() {
        // New Orders
        const newChecked = document.querySelectorAll('.new-order-checkbox:checked').length;
        const bulkAllocateBtn = document.getElementById('bulk-allocate-btn');
        if (bulkAllocateBtn) bulkAllocateBtn.disabled = newChecked === 0;

        // Rejected Orders
        const rejectedChecked = document.querySelectorAll('.rejected-order-checkbox:checked').length;
        const bulkReallocateBtn = document.getElementById('bulk-reallocate-btn');
        if (bulkReallocateBtn) bulkReallocateBtn.disabled = rejectedChecked === 0;

        // For Approval Orders
        const approvalChecked = document.querySelectorAll('.approval-order-checkbox:checked').length;
        const bulkApproveBtn = document.getElementById('bulk-approve-btn');
        if (bulkApproveBtn) bulkApproveBtn.disabled = approvalChecked === 0;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Tab configurations for bulk selection
        const tabConfigs = [{
                id: 'new',
                checkboxClass: 'new-order-checkbox',
                selectAllId: 'select-all-new-orders',
                formId: 'bulk-allocate-form'
            },
            {
                id: 'rejected',
                checkboxClass: 'rejected-order-checkbox',
                selectAllId: 'select-all-rejected-orders',
                formId: 'bulk-reallocate-form'
            },
            {
                id: 'for-approval',
                checkboxClass: 'approval-order-checkbox',
                selectAllId: 'select-all-approval-orders',
                formId: 'bulk-approve-form'
            },
            {
                id: 'all',
                checkboxClass: 'all-order-checkbox',
                selectAllId: 'select-all-all-orders',
                formId: null
            },
            {
                id: 'allocated',
                checkboxClass: 'allocated-order-checkbox',
                selectAllId: 'select-all-allocated-orders',
                formId: null
            },
            {
                id: 'in-process',
                checkboxClass: 'in-process-order-checkbox',
                selectAllId: 'select-all-in-process-orders',
                formId: null
            },
            {
                id: 'overdue',
                checkboxClass: 'overdue-order-checkbox',
                selectAllId: 'select-all-overdue-orders',
                formId: null
            },
            {
                id: 'completed',
                checkboxClass: 'completed-order-checkbox',
                selectAllId: 'select-all-completed-orders',
                formId: null
            }
        ];

        tabConfigs.forEach(config => {
            const selectAll = document.getElementById(config.selectAllId);
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll(`.${config.checkboxClass}`);
                    checkboxes.forEach(cb => cb.checked = this.checked);
                    updateBulkButtons();
                });
            }

            // Using event delegation for checkboxes since they might be in different containers
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains(config.checkboxClass)) {
                    updateBulkButtons();
                    if (selectAll) {
                        const checkboxes = document.querySelectorAll(`.${config.checkboxClass}`);
                        const checkedCount = document.querySelectorAll(`.${config.checkboxClass}:checked`).length;
                        selectAll.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
                        selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
                    }
                }
            });

            const form = document.getElementById(config.formId);
            if (form) {
                form.addEventListener('submit', function(e) {
                    const checked = document.querySelectorAll(`.${config.checkboxClass}:checked`).length;
                    if (checked === 0) {
                        e.preventDefault();
                        alert('Please select at least one work order.');
                        return false;
                    }
                    if (config.id === 'for-approval') {
                        if (!confirm(`Are you sure you want to approve ${checked} selected work order(s)?`)) {
                            e.preventDefault();
                            return false;
                        }
                    }
                });
            }
        });

        // Initialize
        updateBulkButtons();
    });

    // Print current tab view
    function printCurrentTab() {
        const activeTabEl = document.querySelector('[x-show*="activeTab"]');
        if (!activeTabEl) return;

        const table = activeTabEl.querySelector('table');
        if (!table) {
            alert('No table found to print');
            return;
        }

        const win = window.open('', '_blank');
        const style = `
            body { font-family: system-ui, -apple-system, sans-serif; padding: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; font-size: 11px; }
            th { background-color: #f8fafc; color: #64748b; text-transform: uppercase; font-weight: bold; }
            h2 { color: #97144d; margin-bottom: 5px; }
            p { color: #64748b; font-size: 11px; margin-bottom: 20px; }
            @media print { .no-print { display: none; } }
        `;

        win.document.write(`
            <html>
                <head>
                    <title>Work Orders Print</title>
                    <style>${style}</style>
                </head>
                <body>
                    <h2>Work Orders Report</h2>
                    <p>Generated on ${new Date().toLocaleString()}</p>
                    ${table.outerHTML}
                </body>
            </html>
        `);

        win.document.close();
        win.print();
    }

    // Submit Bulk Print
    function submitBulkPrintWorkOrders() {
        // Collect all checked boxes from any tab (or specifically active one)
        const checkedBoxes = document.querySelectorAll('input[name="work_order_ids[]"]:checked');

        if (checkedBoxes.length === 0) {
            alert('Please select at least one work order to print.');
            return;
        }

        const container = document.getElementById('print-work-order-ids-container');
        container.innerHTML = '';

        checkedBoxes.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'work_order_ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });

        document.getElementById('bulkPrintWorkOrdersForm').submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        initSearchableDropdown('bp_code_filter_container', 'bp_code_filter_display', 'bp_code_filter_menu', 'bp_code_filter_search', 'bp_code_filter_list', 'bp_code_filter_select', 'All BP Codes');
        initSearchableDropdown('craftsman_filter_container', 'craftsman_filter_display', 'craftsman_filter_menu', 'craftsman_filter_search', 'craftsman_filter_list', 'craftsman_filter_select', 'All Craftsmen');
    });

    // GENERIC SEARCHABLE DROPDOWN
    function initSearchableDropdown(containerId, displayId, menuId, searchInputId, listId, hiddenSelectId, placeholder, autoSubmit = false) {
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
            const isVisible = !menu.classList.contains('hidden');

            document.querySelectorAll('[id$="_menu"]').forEach(m => {
                if (m !== menu) m.classList.add('hidden');
            });

            if (isVisible) {
                menu.classList.add('hidden');
            } else {
                menu.classList.remove('hidden');
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
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
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

            getListItems().forEach(i => i.classList.remove('bg-slate-100', 'font-bold'));
            item.classList.add('bg-slate-100', 'font-bold');

            menu.classList.add('hidden');

            if (autoSubmit) {
                hiddenSelect.form.submit();
            }
        });

        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

        if (hiddenSelect.value) {
            const selectedItem = Array.from(getListItems()).find(i => i.dataset.value === hiddenSelect.value);
            if (selectedItem) {
                display.textContent = selectedItem.textContent.trim();
                selectedItem.classList.add('bg-slate-100', 'font-bold');
            }
        }
    }

    function exportSelectedWorkOrders() {
        const tabEl = document.getElementById('work-order-tabs');
        let activeTab = 'new-orders';

        if (tabEl && window.Alpine) {
            activeTab = Alpine.$data(tabEl).activeTab;
        } else if (tabEl && tabEl.__x) {
            activeTab = tabEl.__x.$data.activeTab;
        } else {
            const params = new URLSearchParams(window.location.search);
            activeTab = params.get('tab') || 'new-orders';
        }

        const url = new URL("{{ route('admin.work-order.export') }}");
        const params = new URLSearchParams(window.location.search);

        url.searchParams.set('tab', activeTab);

        ['search', 'sort_by', 'sort_order', 'bp_code_filter', 'category_filter', 'subcategory_filter'].forEach(param => {
            if (params.get(param)) url.searchParams.set(param, params.get(param));
        });

        const checkboxClassMap = {
            'new-orders': 'new-order-checkbox',
            'allocated-orders': 'allocated-order-checkbox',
            'in-process-orders': 'in-process-order-checkbox',
            'for-approval-orders': 'approval-order-checkbox',
            'completed-orders': 'completed-order-checkbox',
            'rejected-orders': 'rejected-order-checkbox',
            'overdue-orders': 'overdue-order-checkbox',
            'all-orders': 'all-order-checkbox'
        };

        const checkboxClass = checkboxClassMap[activeTab];
        if (checkboxClass) {
            const checkedBoxes = document.querySelectorAll(`.${checkboxClass}:checked`);
            if (checkedBoxes.length > 0) {
                const ids = Array.from(checkedBoxes).map(cb => cb.value);
                url.searchParams.set('work_order_ids', ids.join(','));
            }
        }

        window.location.href = url.toString();
    }

    // Submit Bulk Complete
    function submitBulkComplete() {
        const tabEl = document.getElementById('work-order-tabs');
        let activeTab = 'new-orders';

        if (tabEl && window.Alpine) {
            try {
                activeTab = Alpine.$data(tabEl).activeTab;
            } catch (ex) {
                const params = new URLSearchParams(window.location.search);
                activeTab = params.get('tab') || 'new-orders';
            }
        } else {
            const params = new URLSearchParams(window.location.search);
            activeTab = params.get('tab') || 'new-orders';
        }

        const checkboxClassMap = {
            'new-orders': 'new-order-checkbox',
            'allocated-orders': 'allocated-order-checkbox',
            'in-process-orders': 'in-process-order-checkbox',
            'for-approval-orders': 'approval-order-checkbox',
            'completed-orders': 'completed-order-checkbox',
            'rejected-orders': 'rejected-order-checkbox',
            'overdue-orders': 'overdue-order-checkbox',
            'all-orders': 'all-order-checkbox'
        };

        const checkboxClass = checkboxClassMap[activeTab];
        const checkedBoxes = document.querySelectorAll(`.${checkboxClass}:checked`);

        if (checkedBoxes.length === 0) {
            alert('Please select at least one work order to complete.');
            return;
        }

        if (!confirm('Are you sure you want to mark ' + checkedBoxes.length + ' selected work orders as completed?')) {
            return;
        }

        const form = document.getElementById('bulkCompleteForm');
        const container = document.getElementById('complete-work-order-ids-container');
        container.innerHTML = '';

        checkedBoxes.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'work_order_ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });

        form.submit();
    }
</script>

<!-- Bulk Print Form (Hidden) -->
<form id="bulkPrintWorkOrdersForm" action="{{ route('admin.work-order.bulk-print') }}" method="POST" target="_blank" style="display:none;">
    @csrf
    <div id="print-work-order-ids-container"></div>
</form>

<!-- Bulk Complete Form (Hidden) -->
<form id="bulkCompleteForm" action="{{ route('admin.work-order.bulk-complete') }}" method="POST" style="display:none;">
    @csrf
    <div id="complete-work-order-ids-container"></div>
</form>

<script>
    // Fix: Preserve active tab when clicking pagination links
    document.addEventListener('click', function(e) {
        const paginationLink = e.target.closest('.pagination a, .custom-pagination a');
        if (paginationLink) {
            e.preventDefault();
            const url = new URL(paginationLink.href, window.location.origin);
            // Get current activeTab from Alpine.js or fallback to URL
            let activeTab = 'new-orders';
            const tabEl = document.getElementById('work-order-tabs');
            if (tabEl && window.Alpine) {
                try {
                    activeTab = Alpine.$data(tabEl.closest('[x-data]')).activeTab;
                } catch (ex) {}
            }
            if (activeTab === 'new-orders') {
                const params = new URLSearchParams(window.location.search);
                activeTab = params.get('tab') || 'new-orders';
            }
            url.searchParams.set('tab', activeTab);
            window.location.href = url.toString();
        }
    });
</script>

@endsection

@section('styles')
<style>
    tr[style*="background-color"]>td,
    tr[style*="background-color"]>th {
        background-color: transparent !important;
    }
</style>
<!-- Admin Undo Modal -->
<div id="adminUndoModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm overflow-y-auto">
    <div class="min-h-screen px-4 text-center flex items-center justify-center">
        <div class="inline-block w-full max-w-md p-6 my-8 text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl relative">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Undo Work Order Status</h3>
            <p class="text-sm text-slate-500 mb-4" id="adminUndoModalMsg"></p>
            
            <form id="adminUndoForm" method="POST" action="">
                @csrf
                
                <div id="adminUndoOtpSection" class="hidden">
                    <div class="mb-4 text-left">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Select SuperAdmin to Receive OTP</label>
                        <select id="superAdminSelect" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm mb-2">
                            @foreach($superAdmins as $sa)
                                <option value="{{ $sa->id }}">{{ $sa->user_code }} - {{ $sa->name }} - {{ $sa->mobile_no }}</option>
                            @endforeach
                        </select>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="sendAdminUndoOtp('sms')" class="px-3 py-1.5 bg-slate-100 text-xs text-pink-600 hover:text-pink-700 font-medium rounded border border-slate-200">
                                <i class="bi bi-chat-left-text me-1"></i> SMS
                            </button>
                            <button type="button" onclick="sendAdminUndoOtp('whatsapp')" class="px-3 py-1.5 bg-emerald-50 text-xs text-emerald-600 hover:text-emerald-700 font-medium rounded border border-emerald-200">
                                <i class="bi bi-whatsapp me-1"></i> WhatsApp
                            </button>
                            <span id="adminOtpStatus" class="ml-2 text-xs text-green-600 hidden">OTP Sent!</span>
                        </div>
                    </div>
                    
                    <div class="mb-4 text-left">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Enter OTP</label>
                        <input type="text" name="otp" id="adminUndoOtpInput" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm" placeholder="6-digit OTP">
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="closeAdminUndoModal()" class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors shadow-sm">Confirm Undo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentUndoWoId = null;

function openAdminUndoModal(woId, undoCount) {
    currentUndoWoId = woId;
    document.getElementById('adminUndoForm').action = `/admin/work-order/${woId}/undo`;
    
    if (undoCount >= 1) {
        document.getElementById('adminUndoOtpSection').classList.remove('hidden');
        document.getElementById('adminUndoOtpInput').required = true;
        document.getElementById('adminUndoModalMsg').innerText = "You have already undone this work order once. OTP is required to undo again.";
    } else {
        document.getElementById('adminUndoOtpSection').classList.add('hidden');
        document.getElementById('adminUndoOtpInput').required = false;
        document.getElementById('adminUndoModalMsg').innerText = "Are you sure you want to undo the status of this work order?";
    }
    
    document.getElementById('adminUndoModal').classList.remove('hidden');
}

function closeAdminUndoModal() {
    document.getElementById('adminUndoModal').classList.add('hidden');
}

function sendAdminUndoOtp(method) {
    const superAdminId = document.getElementById('superAdminSelect').value;
    if (!superAdminId) return;
    
    document.getElementById('adminOtpStatus').classList.remove('hidden');
    document.getElementById('adminOtpStatus').innerText = "Sending...";
    document.getElementById('adminOtpStatus').className = "ml-2 text-xs text-amber-600";
    
    fetch(`/admin/work-order/${currentUndoWoId}/send-undo-otp`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ superadmin_id: superAdminId, delivery_method: method })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('adminOtpStatus').innerText = "OTP Sent!";
            document.getElementById('adminOtpStatus').className = "ml-2 text-xs text-emerald-600";
        } else {
            document.getElementById('adminOtpStatus').innerText = "Failed: " + data.message;
            document.getElementById('adminOtpStatus').className = "ml-2 text-xs text-rose-600";
        }
    })
    .catch(err => {
        document.getElementById('adminOtpStatus').innerText = "Error sending OTP";
        document.getElementById('adminOtpStatus').className = "ml-2 text-xs text-rose-600";
    });
}
</script>

@endsection



