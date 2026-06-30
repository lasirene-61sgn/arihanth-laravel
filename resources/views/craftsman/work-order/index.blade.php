@extends('craftsman.layouts.app')

@section('title', 'Work Orders')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-emerald-100 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-emerald-900 tracking-tight">My Work Orders</h1>
            @if(Auth::guard('craftsman')->user()->dear)
                <p class="text-emerald-600 mt-1">Welcome back, <span class="font-semibold text-emerald-800">{{ Auth::guard('craftsman')->user()->dear }}</span></p>
            @endif
        </div>
    </div>
    
    <!-- Notifications -->
    @if (session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded shadow-sm flex justify-between items-center transition-all duration-300">
            <div class="flex items-center gap-3">
                <i class="bi bi-check-circle-fill text-emerald-500 text-xl"></i>
                <p class="text-emerald-800 font-medium">{{ session('success') }}</p>
            </div>
            <button type="button" class="text-emerald-400 hover:text-emerald-600" onclick="this.parentElement.remove()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded shadow-sm flex justify-between items-center transition-all duration-300">
            <div class="flex items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill text-red-500 text-xl"></i>
                <p class="text-red-800 font-medium">{{ session('error') }}</p>
            </div>
            <button type="button" class="text-red-400 hover:text-red-600" onclick="this.parentElement.remove()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white p-6 rounded-2xl border border-emerald-100 shadow-sm">
        <form action="{{ route('craftsman.work-order.index') }}" method="GET" class="space-y-4">
            <input type="hidden" name="tab" id="current_tab_input" value="{{ request('tab', 'allocated') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-emerald-600 mb-2">Search Work Orders</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-emerald-400 group-focus-within:text-emerald-600 transition-colors">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" 
                               class="w-full pl-10 pr-4 py-2 bg-emerald-50 border border-emerald-100 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none" 
                               placeholder="Number, customer, product..." 
                               value="{{ request('search') }}">
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-emerald-600 mb-2">Category</label>
                    <div id="category_filter_container" class="relative">
                        <div id="category_filter_display" class="w-full pl-4 pr-10 py-2 bg-emerald-50 border border-emerald-100 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none cursor-pointer flex items-center justify-between text-sm">
                            <span class="truncate">All Categories</span>
                            <i class="bi bi-chevron-down text-emerald-400 text-[10px]"></i>
                        </div>
                        <div id="category_filter_menu" class="absolute z-50 w-full mt-2 bg-white border border-emerald-100 rounded-xl shadow-xl hidden overflow-hidden">
                            <div class="p-2 border-b border-emerald-50 bg-emerald-50/30">
                                <input type="text" id="category_filter_search" class="w-full px-3 py-1.5 text-xs bg-white border border-emerald-100 rounded-lg focus:outline-none focus:border-emerald-500" placeholder="Search categories...">
                            </div>
                            <ul id="category_filter_list" class="max-h-60 overflow-y-auto no-scrollbar py-1">
                                <li class="px-4 py-2 text-sm text-emerald-600 hover:bg-emerald-50 cursor-pointer transition-colors font-medium border-b border-emerald-50/50" data-value="">
                                    All Categories
                                </li>
                                @foreach($productCategories as $cat)
                                    <li class="px-4 py-2 text-sm text-emerald-900 hover:bg-emerald-50 cursor-pointer transition-colors" data-value="{{ $cat->id }}">
                                        {{ $cat->name }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <select name="product_category_filter" id="product_category_filter_select" class="hidden">
                            <option value="">All Categories</option>
                            @foreach($productCategories as $cat)
                                <option value="{{ $cat->id }}" {{ request('product_category_filter') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-emerald-600 mb-2">Sort By</label>
                    <select name="sort_by" class="w-full px-3 py-2 bg-emerald-50 border border-emerald-100 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all appearance-none cursor-pointer text-sm">
                        <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>ID</option>
                        <option value="work_order_number" {{ request('sort_by') == 'work_order_number' ? 'selected' : '' }}>Order #</option>
                        <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>Due Date</option>
                        <option value="customer_name" {{ request('sort_by') == 'customer_name' ? 'selected' : '' }}>Customer</option>
                    </select>
                </div>

                <div class="lg:col-span-1">
                    <label class="block text-xs font-bold uppercase tracking-wider text-emerald-600 mb-2">Order</label>
                    <select name="sort_order" class="w-full px-3 py-2 bg-emerald-50 border border-emerald-100 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all appearance-none cursor-pointer text-sm">
                        <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>DESC</option>
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>ASC</option>
                    </select>
                </div>

                <div class="lg:col-span-1">
                    <label class="block text-xs font-bold uppercase tracking-wider text-emerald-600 mb-2">Size</label>
                    <select name="per_page" class="w-full px-3 py-2 bg-emerald-50 border border-emerald-100 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all appearance-none cursor-pointer text-sm">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-3 rounded-xl transition-all shadow-sm flex items-center justify-center gap-1.5 text-xs">
                        <i class="bi bi-filter"></i> Apply
                    </button>
                    <a href="{{ route('craftsman.work-order.index') }}" class="flex-1 bg-white border border-emerald-200 text-emerald-700 hover:bg-emerald-50 font-bold py-2 px-3 rounded-xl transition-all flex items-center justify-center gap-1.5 text-xs">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                </div>
            </div>

            <div class="pt-4 border-t border-emerald-100 flex justify-end">
                <a href="{{ route('craftsman.work-order.export', request()->all()) }}" 
                   class="bg-emerald-100 text-emerald-700 hover:bg-emerald-200 font-bold py-2 px-6 rounded-xl transition-all flex items-center justify-center gap-2 text-sm">
                    <i class="bi bi-file-earmark-excel"></i> Export Work Orders
                </a>
            </div>
        </form>
    </div>
    <!-- Navigation Tabs -->
    <div class="border-b border-emerald-100 mb-4">
        <div class="flex overflow-x-auto no-scrollbar -mb-px gap-1" id="workOrderTabs" role="tablist">
            <button class="whitespace-nowrap px-6 py-3 font-bold text-sm border-b-2 transition-all {{ request('tab', 'allocated') == 'allocated' ? 'active' : '' }}" 
                    id="allocated-tab" data-bs-toggle="tab" data-bs-target="#allocated" type="button" role="tab">
                Allocated ({{ $allocatedOrders->total() }})
            </button>
            <button class="whitespace-nowrap px-6 py-3 font-bold text-sm border-b-2 transition-all {{ request('tab') == 'in-process' ? 'active' : '' }}" 
                    id="in-process-tab" data-bs-toggle="tab" data-bs-target="#in-process" type="button" role="tab">
                In Process ({{ $inProcessOrders->total() }})
            </button>
            <button class="whitespace-nowrap px-6 py-3 font-bold text-sm border-b-2 transition-all {{ request('tab') == 'completed' ? 'active' : '' }}" 
                    id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                Completed ({{ $completedOrders->total() }})
            </button>
            <button class="whitespace-nowrap px-6 py-3 font-bold text-sm border-b-2 transition-all {{ request('tab') == 'rejected' ? 'active' : '' }}" 
                    id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
                Rejected ({{ $rejectedOrders->total() }})
            </button>
            <button class="whitespace-nowrap px-6 py-3 font-bold text-sm border-b-2 transition-all {{ request('tab') == 'overdue' ? 'active' : '' }}" 
                    id="overdue-tab" data-bs-toggle="tab" data-bs-target="#overdue" type="button" role="tab">
                Overdue ({{ $overdueOrders->total() }})
            </button>
        </div>
    </div>

    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        #workOrderTabs button {
            border-bottom-width: 2px;
            border-color: transparent;
            color: #10b981; /* emerald-500 */
        }
        
        #workOrderTabs button:hover {
            color: #047857; /* emerald-700 */
        }
        
        #workOrderTabs button.active {
            border-bottom-color: #059669; /* emerald-600 */
            color: #064e3b; /* emerald-900 */
            background-color: rgba(236, 253, 245, 0.5); /* emerald-50/50 */
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
        }
    </style>

    <div class="tab-content" id="workOrderTabsContent">

            <!-- Allocated Orders Tab -->
            <div class="tab-pane fade {{ request('tab', 'allocated') == 'allocated' ? 'show active' : '' }}" id="allocated" role="tabpanel">
                <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm overflow-hidden mt-4">
                    <div class="p-4 bg-emerald-50/50 border-b border-emerald-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-3">
                            <h4 class="font-bold text-emerald-900">Allocated Work Orders</h4>
                            <span class="bg-emerald-600 text-white text-xs font-bold px-2 py-1 rounded-full">{{ $allocatedOrders->total() }}</span>
                        </div>
                    </div>

                    <div class="p-0">
                        @if($allocatedOrders->count() > 0)
                            <form action="{{ route('craftsman.work-order.bulk-accept') }}" method="POST" id="bulkAcceptForm">
                                @csrf
                                <div class="px-6 py-4 bg-white border-b border-emerald-50 flex flex-wrap justify-between items-center gap-3">
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold py-2 px-4 rounded-xl transition-all shadow-sm flex items-center gap-2" 
                                                onclick="if(validateBulkSelection()) document.getElementById('bulkAcceptForm').submit();">
                                            <i class="bi bi-check-all"></i> Bulk Accept
                                        </button>
                                        <button type="button" class="bg-red-50 text-red-600 hover:bg-red-100 text-sm font-bold py-2 px-4 rounded-xl transition-all flex items-center gap-2" 
                                                onclick="showBulkRejectModal()">
                                            <i class="bi bi-x-circle"></i> Bulk Reject
                                        </button>
                                    </div>
                                    <div>
                                        <button type="submit" formaction="{{ route('craftsman.work-order.print-selected') }}" formmethod="POST" 
                                                class="bg-blue-50 text-blue-600 hover:bg-blue-100 text-sm font-bold py-2 px-4 rounded-xl transition-all flex items-center gap-2">
                                            <i class="bi bi-printer"></i> Print Selected
                                        </button>
                                    </div>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr class="bg-emerald-50/30 text-emerald-800 text-xs font-bold uppercase tracking-wider">
                                                <th class="px-6 py-4 text-center"><input type="checkbox" id="selectAllAllocated" class="rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500"></th>
                                                <th class="px-6 py-4">Image</th>
                                                <th class="px-6 py-4">Work Order </th>
                                                <th class="px-6 py-4">Category</th>
                                                <th class="px-6 py-4">Subcategory</th>
                                                <!--<th class="px-6 py-4">Order Date</th>-->
                                                <th class="px-6 py-4">Due Date</th>
                                                <th class="px-6 py-4">Weight</th>
                                                <th class="px-6 py-4">Qty</th>
                                                <th class="px-6 py-4">Size</th>
                                                <th class="px-6 py-4">Notes</th>
                                                <th class="px-6 py-4 text-right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-emerald-50">
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
                                <tr class="hover:tw-bg-gray-50 tw-transition-colors  hover:bg-emerald-50/30 transition-colors" style="{{ $rowStyle }}">
                                                    <td class="px-6 py-4 text-center">
                                                        <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="allocated-checkbox rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        @if($order->preview_image_url)
                                                            <div class="w-12 h-12 rounded-lg border border-emerald-100 overflow-hidden cursor-pointer hover:ring-2 hover:ring-emerald-500 transition-all bg-white"
                                                                 onclick="openUniversalPreview('{{ $order->preview_image_url }}', '{{ $order->file_type }}')">
                                                                @if($order->file_type === 'pdf')
                                                                    <canvas class="pdf-canvas w-full h-full object-cover" data-url="{{ $order->preview_image_url }}"></canvas>
                                                                @else
                                                                    <img src="{{ $order->preview_image_url }}" alt="Img" class="w-full h-full object-cover">
                                                                @endif
                                                            </div>
                                                        @else
                                                            <div class="w-12 h-12 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-300">
                                                                <i class="bi bi-image text-xl"></i>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <span class="font-bold text-emerald-900">{{ $order->work_order_number }}</span>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->productCategory->name ?? (is_array($order->product_category) ? implode(', ', $order->product_category) : ($order->product_category ?? '-')) }}</td>
                                                    <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->subcategoryRelation->name ?? (is_array($order->subcategory) ? implode(', ', $order->subcategory) : ($order->subcategory ?? '-')) }}</td>
                                                    <!--<td class="px-6 py-4 text-sm text-emerald-700 whitespace-nowrap">{{ $order->due_date ? $order->due_date->format('d M, Y') : 'N/A' }}</td>-->
                                                    <td class="px-6 py-4 text-sm {{ $isOverdue ? 'text-red-600 font-bold' : 'text-emerald-700' }} whitespace-nowrap">{{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'N/A' }}</td>
                                                    <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->weight_from }}g</td>
                                                    <td class="px-6 py-4 text-sm text-emerald-700 font-bold">{{ $order->quantity }}</td>
                                                    <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->size }}</td>
                                                    <td class="px-6 py-4 text-sm text-emerald-600 truncate max-w-[150px]" title="{{ $order->narration_craftsman }}">{{ $order->narration_craftsman ?? '-' }}</td>
                                                    <td class="px-6 py-4 text-right">
                                                        <a href="{{ route('craftsman.work-order.show', $order) }}" 
                                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="View Details">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                            
                            <div class="p-6 border-t border-emerald-50">
                                {{ $allocatedOrders->appends(array_merge(request()->except('allocated_orders_page'), ['tab' => 'allocated']))->links() }}
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="bi bi-inbox text-2xl text-emerald-300"></i>
                                </div>
                                <h3 class="text-emerald-900 font-bold">No Allocated Orders</h3>
                                <p class="text-emerald-500 text-sm">You have no new work orders allocated to you.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

                <!-- In Process Orders Tab -->
                <div class="tab-pane fade {{ request('tab') == 'in-process' ? 'show active' : '' }}" id="in-process" role="tabpanel">
                    <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm overflow-hidden mt-4">
                        <div class="p-4 bg-emerald-50/50 border-b border-emerald-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div class="flex items-center gap-3">
                                <h4 class="font-bold text-emerald-900">In Process Work Orders</h4>
                                <span class="bg-amber-500 text-white text-xs font-bold px-2 py-1 rounded-full">{{ $inProcessOrders->total() }}</span>
                            </div>
                        </div>

                        <div class="p-0">
                            @if($inProcessOrders->count() > 0)
                                <form action="{{ route('craftsman.work-order.bulk-complete') }}" method="POST" id="bulkCompleteForm">
                                    @csrf
                                    <div class="px-6 py-4 bg-white border-b border-emerald-50 flex flex-wrap justify-between items-center gap-3">
                                        <div>
                                            <button type="button" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold py-2 px-4 rounded-xl transition-all shadow-sm flex items-center gap-2" 
                                                    onclick="showBulkCompleteModal()">
                                                <i class="bi bi-check-all"></i> Bulk Complete
                                            </button>
                                        </div>
                                        <div>
                                            <button type="submit" formaction="{{ route('craftsman.work-order.print-selected') }}" formmethod="POST" 
                                                    class="bg-blue-50 text-blue-600 hover:bg-blue-100 text-sm font-bold py-2 px-4 rounded-xl transition-all flex items-center gap-2">
                                                <i class="bi bi-printer"></i> Print Selected
                                            </button>
                                        </div>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left">
                                            <thead>
                                                <tr class="bg-emerald-50/30 text-emerald-800 text-xs font-bold uppercase tracking-wider">
                                                    <th class="px-6 py-4 text-center"><input type="checkbox" id="selectAllInProcess" class="rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500"></th>
                                                    <th class="px-6 py-4">Image</th>
                                                    <th class="px-6 py-4">Work Order</th>
                                                    <th class="px-6 py-4">Category</th>
                                                    <th class="px-6 py-4">Subcategory</th>
                                                    <!--<th class="px-6 py-4">Order Date</th>-->
                                                    <th class="px-6 py-4">Due Date</th>
                                                    <th class="px-6 py-4">Weight</th>
                                                    <th class="px-6 py-4">Qty</th>
                                                    <th class="px-6 py-4">Size</th>
                                                    <th class="px-6 py-4">Notes</th>
                                                    <th class="px-6 py-4 text-right">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-emerald-50">
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
                                <tr class="hover:tw-bg-gray-50 tw-transition-colors  hover:bg-emerald-50/30 transition-colors" style="{{ $rowStyle }}">
                                                        <td class="px-6 py-4 text-center">
                                                            <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="in-process-checkbox rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            @if($order->preview_image_url)
                                                                <div class="w-12 h-12 rounded-lg border border-emerald-100 overflow-hidden cursor-pointer hover:ring-2 hover:ring-emerald-500 transition-all bg-white"
                                                                     onclick="openUniversalPreview('{{ $order->preview_image_url }}', '{{ $order->file_type }}')">
                                                                    @if($order->file_type === 'pdf')
                                                                        <canvas class="pdf-canvas w-full h-full object-cover" data-url="{{ $order->preview_image_url }}"></canvas>
                                                                    @else
                                                                        <img src="{{ $order->preview_image_url }}" alt="Img" class="w-full h-full object-cover">
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <div class="w-12 h-12 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-300">
                                                                    <i class="bi bi-image text-xl"></i>
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <span class="font-bold text-emerald-900">{{ $order->work_order_number }}</span>
                                                        </td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->productCategory->name ?? $order->product_category ?? '-' }}</td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->subcategoryRelation->name ?? $order->subcategory ?? '-' }}</td>
                                                        <!--<td class="px-6 py-4 text-sm text-emerald-700 whitespace-nowrap">{{ $order->due_date ? $order->due_date->format('d M, Y') : 'N/A' }}</td>-->
                                                        <td class="px-6 py-4 text-sm {{ $isOverdue ? 'text-red-600 font-bold' : 'text-emerald-700' }} whitespace-nowrap">{{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'N/A' }}</td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->weight_from }}g</td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700 font-bold">{{ $order->quantity }}</td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->size }}</td>
                                                        <td class="px-6 py-4 text-sm text-emerald-600 truncate max-w-[150px]" title="{{ $order->narration_craftsman }}">{{ $order->narration_craftsman ?? '-' }}</td>
                                                        <td class="px-6 py-4 text-right">
                                                            <div class="flex justify-end gap-2">
                                                                <a href="{{ route('craftsman.work-order.show', $order) }}" 
                                                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="View Details">
                                                                    <i class="bi bi-eye"></i>
                                                                </a>
                                                                <button type="button" onclick="showSingleCompleteModal('{{ route('craftsman.work-order.complete', $order) }}')" 
                                                                        class="inline-flex items-center justify-center h-8 px-3 rounded-lg bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition-all shadow-sm gap-2" title="Mark as Completed">
                                                                    <i class="bi bi-check-all"></i> Complete
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                                
                                <div class="p-6 border-t border-emerald-50">
                                    {{ $inProcessOrders->appends(array_merge(request()->except('in_process_orders_page'), ['tab' => 'in-process']))->links() }}
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="bi bi-play-circle text-2xl text-emerald-300"></i>
                                    </div>
                                    <h3 class="text-emerald-900 font-bold">No In-Process Orders</h3>
                                    <p class="text-emerald-500 text-sm">You have no work orders currently in process.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Completed Orders Tab -->
                <div class="tab-pane fade {{ request('tab') == 'completed' ? 'show active' : '' }}" id="completed" role="tabpanel">
                    <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm overflow-hidden mt-4">
                        <div class="p-4 bg-emerald-50/50 border-b border-emerald-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div class="flex items-center gap-3">
                                <h4 class="font-bold text-emerald-900">Completed Work Orders</h4>
                                <span class="bg-emerald-600 text-white text-xs font-bold px-2 py-1 rounded-full">{{ $completedOrders->total() }}</span>
                            </div>
                        </div>

                        <div class="p-0">
                            @if($completedOrders->count() > 0)
                                <form action="{{ route('craftsman.work-order.print-selected') }}" method="POST" id="bulkPrintCompletedForm">
                                    @csrf
                                    <div class="px-6 py-4 bg-white border-b border-emerald-50 flex justify-end">
                                        <button type="submit" class="bg-blue-50 text-blue-600 hover:bg-blue-100 text-sm font-bold py-2 px-4 rounded-xl transition-all flex items-center gap-2">
                                            <i class="bi bi-printer"></i> Print Selected
                                        </button>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left">
                                            <thead>
                                                <tr class="bg-emerald-50/30 text-emerald-800 text-xs font-bold uppercase tracking-wider">
                                                    <th class="px-6 py-4 text-center"><input type="checkbox" id="selectAllCompleted" class="rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500"></th>
                                                    <th class="px-6 py-4">Image</th>
                                                    <th class="px-6 py-4">Work Order</th>
                                                    <th class="px-6 py-4">Category</th>
                                                    <th class="px-6 py-4">Subcategory</th>
                                                    <!--<th class="px-6 py-4">Order Date</th>-->
                                                    <th class="px-6 py-4">Due Date</th>
                                                    <th class="px-6 py-4">Weight</th>
                                                    <th class="px-6 py-4">Qty</th>
                                                    <th class="px-6 py-4">Size</th>
                                                    <th class="px-6 py-4">Status</th>
                                                    <th class="px-6 py-4 text-right">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-emerald-50">
                                                @foreach($completedOrders as $order)
                                                    <tr class="hover:bg-emerald-50/30 transition-colors">
                                                        <td class="px-6 py-4 text-center">
                                                            <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="completed-checkbox rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            @if($order->preview_image_url)
                                                                <div class="w-12 h-12 rounded-lg border border-emerald-100 overflow-hidden cursor-pointer hover:ring-2 hover:ring-emerald-500 transition-all bg-white"
                                                                     onclick="openUniversalPreview('{{ $order->preview_image_url }}', '{{ $order->file_type }}')">
                                                                    @if($order->file_type === 'pdf')
                                                                        <canvas class="pdf-canvas w-full h-full object-cover" data-url="{{ $order->preview_image_url }}"></canvas>
                                                                    @else
                                                                        <img src="{{ $order->preview_image_url }}" alt="Img" class="w-full h-full object-cover">
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <div class="w-12 h-12 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-300">
                                                                    <i class="bi bi-image text-xl"></i>
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <span class="font-bold text-emerald-900">{{ $order->work_order_number }}</span>
                                                        </td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->productCategory->name ?? $order->product_category ?? '-' }}</td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->subcategoryRelation->name ?? $order->subcategory ?? '-' }}</td>
                                                        <!--<td class="px-6 py-4 text-sm text-emerald-700 whitespace-nowrap">{{ $order->due_date ? $order->due_date->format('d M, Y') : 'N/A' }}</td>-->
                                                        <td class="px-6 py-4 text-sm text-emerald-700 whitespace-nowrap">{{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'N/A' }}</td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->weight_from }}g</td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700 font-bold">{{ $order->quantity }}</td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->size }}</td>
                                                        <td class="px-6 py-4">
                                                            @if($order->status == 'for_approval')
                                                                <span class="px-2 py-1 bg-amber-100 text-amber-700 text-[10px] font-bold uppercase tracking-wider rounded-md">For Approval</span>
                                                            @elseif($order->status == 'completed')
                                                                <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase tracking-wider rounded-md">Approved</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4 text-right">
                                                            <a href="{{ route('craftsman.work-order.show', $order) }}" 
                                                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="View Details">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                                
                                <div class="p-6 border-t border-emerald-50">
                                    {{ $completedOrders->appends(array_merge(request()->except('completed_orders_page'), ['tab' => 'completed']))->links() }}
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="bi bi-check-all text-2xl text-emerald-300"></i>
                                    </div>
                                    <h3 class="text-emerald-900 font-bold">No Completed Orders</h3>
                                    <p class="text-emerald-500 text-sm">You have no completed work orders yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Rejected Orders Tab -->
                <div class="tab-pane fade {{ request('tab') == 'rejected' ? 'show active' : '' }}" id="rejected" role="tabpanel">
                    <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm overflow-hidden mt-4">
                        <div class="p-4 bg-emerald-50/50 border-b border-emerald-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div class="flex items-center gap-3">
                                <h4 class="font-bold text-emerald-900">Rejected Work Orders</h4>
                                <span class="bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-full">{{ $rejectedOrders->total() }}</span>
                            </div>
                        </div>

                        <div class="p-0">
                            @if($rejectedOrders->count() > 0)
                                <form action="{{ route('craftsman.work-order.print-selected') }}" method="POST" id="bulkPrintRejectedForm">
                                    @csrf
                                    <div class="px-6 py-4 bg-white border-b border-emerald-50 flex justify-end">
                                        <button type="submit" class="bg-blue-50 text-blue-600 hover:bg-blue-100 text-sm font-bold py-2 px-4 rounded-xl transition-all flex items-center gap-2">
                                            <i class="bi bi-printer"></i> Print Selected
                                        </button>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left">
                                            <thead>
                                                <tr class="bg-emerald-50/30 text-emerald-800 text-xs font-bold uppercase tracking-wider">
                                                    <th class="px-6 py-4 text-center"><input type="checkbox" id="selectAllRejected" class="rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500"></th>
                                                    <th class="px-6 py-4">Image</th>
                                                    <th class="px-6 py-4">Work Order</th>
                                                    <th class="px-6 py-4">Category</th>
                                                    <th class="px-6 py-4">Subcategory</th>
                                                    <!--<th class="px-6 py-4">Order Date</th>-->
                                                    <th class="px-6 py-4">Due Date</th>
                                                    <th class="px-6 py-4">Weight</th>
                                                    <th class="px-6 py-4">Qty</th>
                                                    <th class="px-6 py-4">Size</th>
                                                    <th class="px-6 py-4">Reject Reason</th>
                                                    <th class="px-6 py-4 text-right">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-emerald-50">
                                                @foreach($rejectedOrders as $order)
                                                    <tr class="hover:bg-emerald-50/30 transition-colors">
                                                        <td class="px-6 py-4 text-center">
                                                            <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="rejected-checkbox rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            @if($order->preview_image_url)
                                                                <div class="w-12 h-12 rounded-lg border border-emerald-100 overflow-hidden cursor-pointer hover:ring-2 hover:ring-emerald-500 transition-all bg-white"
                                                                     onclick="openUniversalPreview('{{ $order->preview_image_url }}', '{{ $order->file_type }}')">
                                                                    @if($order->file_type === 'pdf')
                                                                        <canvas class="pdf-canvas w-full h-full object-cover" data-url="{{ $order->preview_image_url }}"></canvas>
                                                                    @else
                                                                        <img src="{{ $order->preview_image_url }}" alt="Img" class="w-full h-full object-cover">
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <div class="w-12 h-12 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-300">
                                                                    <i class="bi bi-image text-xl"></i>
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <span class="font-bold text-emerald-900">{{ $order->work_order_number }}</span>
                                                        </td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->productCategory->name ?? $order->product_category ?? '-' }}</td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->subcategoryRelation->name ?? $order->subcategory ?? '-' }}</td>
                                                        <!--<td class="px-6 py-4 text-sm text-emerald-700 whitespace-nowrap">{{ $order->due_date ? $order->due_date->format('d M, Y') : 'N/A' }}</td>-->
                                                        <td class="px-6 py-4 text-sm text-emerald-700 whitespace-nowrap">{{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'N/A' }}</td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->weight_from }}g</td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700 font-bold">{{ $order->quantity }}</td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->size }}</td>
                                                        <td class="px-6 py-4 text-sm text-red-600 italic truncate max-w-[150px]" title="{{ $order->rejection_reason }}">{{ $order->rejection_reason ?? '-' }}</td>
                                                        <td class="px-6 py-4 text-right">
                                                            <a href="{{ route('craftsman.work-order.show', $order) }}" 
                                                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="View Details">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                                
                                <div class="p-6 border-t border-emerald-50">
                                    {{ $rejectedOrders->appends(array_merge(request()->except('rejected_orders_page'), ['tab' => 'rejected']))->links() }}
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="bi bi-x-circle text-2xl text-emerald-300"></i>
                                    </div>
                                    <h3 class="text-emerald-900 font-bold">No Rejected Orders</h3>
                                    <p class="text-emerald-500 text-sm">You have no rejected work orders.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Overdue Orders Tab -->
                <div class="tab-pane fade {{ request('tab') == 'overdue' ? 'show active' : '' }}" id="overdue" role="tabpanel">
                    <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm overflow-hidden mt-4">
                        <div class="p-4 bg-emerald-50/50 border-b border-emerald-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div class="flex items-center gap-3">
                                <h4 class="font-bold text-emerald-900">Overdue Work Orders</h4>
                                <span class="bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-full">{{ $overdueOrders->total() }}</span>
                            </div>
                        </div>

                        <div class="p-0">
                            @if($overdueOrders->count() > 0)
                                <form action="{{ route('craftsman.work-order.print-selected') }}" method="POST" id="bulkPrintOverdueForm">
                                    @csrf
                                    <div class="px-6 py-4 bg-white border-b border-emerald-50 flex justify-end">
                                        <button type="submit" class="bg-blue-50 text-blue-600 hover:bg-blue-100 text-sm font-bold py-2 px-4 rounded-xl transition-all flex items-center gap-2">
                                            <i class="bi bi-printer"></i> Print Selected
                                        </button>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left">
                                            <thead>
                                                <tr class="bg-emerald-50/30 text-emerald-800 text-xs font-bold uppercase tracking-wider">
                                                    <th class="px-6 py-4 text-center"><input type="checkbox" id="selectAllOverdue" class="rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500"></th>
                                                    <th class="px-6 py-4">Image</th>
                                                    <th class="px-6 py-4">Work Order</th>
                                                    <th class="px-6 py-4">Category</th>
                                                    <th class="px-6 py-4">Subcategory</th>
                                                    <!--<th class="px-6 py-4">Order Date</th>-->
                                                    <th class="px-6 py-4">Due Date</th>
                                                    <th class="px-6 py-4">Weight</th>
                                                    <th class="px-6 py-4">Qty</th>
                                                    <th class="px-6 py-4">Size</th>
                                                    <th class="px-6 py-4">Status</th>
                                                    <th class="px-6 py-4 text-right">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-emerald-50">
                                                @foreach($overdueOrders as $order)
                                                    <tr class="hover:bg-emerald-50/30 transition-colors bg-red-50/30">
                                                        <td class="px-6 py-4 text-center">
                                                            <input type="checkbox" name="work_order_ids[]" value="{{ $order->id }}" class="overdue-checkbox rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            @if($order->preview_image_url)
                                                                <div class="w-12 h-12 rounded-lg border border-emerald-100 overflow-hidden cursor-pointer hover:ring-2 hover:ring-emerald-500 transition-all bg-white"
                                                                     onclick="openUniversalPreview('{{ $order->preview_image_url }}', '{{ $order->file_type }}')">
                                                                    @if($order->file_type === 'pdf')
                                                                        <canvas class="pdf-canvas w-full h-full object-cover" data-url="{{ $order->preview_image_url }}"></canvas>
                                                                    @else
                                                                        <img src="{{ $order->preview_image_url }}" alt="Img" class="w-full h-full object-cover">
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <div class="w-12 h-12 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-300">
                                                                    <i class="bi bi-image text-xl"></i>
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <span class="font-bold text-red-700">{{ $order->work_order_number }}</span>
                                                        </td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->productCategory->name ?? $order->product_category ?? '-' }}</td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->subcategoryRelation->name ?? $order->subcategory ?? '-' }}</td>
                                                        <!--<td class="px-6 py-4 text-sm text-emerald-700 whitespace-nowrap">{{ $order->due_date ? $order->due_date->format('d M, Y') : 'N/A' }}</td>-->
                                                        <td class="px-6 py-4 text-sm text-red-600 font-bold whitespace-nowrap">{{ $order->craftsman_due_date ? $order->craftsman_due_date->format('d M, Y') : 'N/A' }}</td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->weight_from }}g</td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700 font-bold">{{ $order->quantity }}</td>
                                                        <td class="px-6 py-4 text-sm text-emerald-700">{{ $order->size }}</td>
                                                        <td class="px-6 py-4">
                                                            <span class="px-2 py-1 bg-red-100 text-red-700 text-[10px] font-bold uppercase tracking-wider rounded-md">{{ ucfirst(str_replace('_', ' ', $order->craftsman_status)) }}</span>
                                                        </td>
                                                        <td class="px-6 py-4 text-right">
                                                            <a href="{{ route('craftsman.work-order.show', $order) }}" 
                                                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="View Details">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                                
                                <div class="p-6 border-t border-emerald-50">
                                    {{ $overdueOrders->appends(array_merge(request()->except('overdue_orders_page'), ['tab' => 'overdue']))->links() }}
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="bi bi-calendar-check text-2xl text-emerald-300"></i>
                                    </div>
                                    <h3 class="text-emerald-900 font-bold">No Overdue Orders</h3>
                                    <p class="text-emerald-500 text-sm">Great job! You have no overdue work orders.</p>
                                </div>
                            @endif
                </div> <!-- Overdue Tab Card -->
            </div> <!-- Overdue Tab Pane -->
        </div> <!-- Tab Content Wrapper -->
    </div> <!-- Space-y-6 Wrapper -->

    <!-- Complete Order Modal -->
    <div class="modal fade" id="completeOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content !rounded-2xl border-none shadow-2xl overflow-hidden">
                <form id="completeOrderForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-emerald-600 text-white border-none py-4 px-6">
                        <h5 class="modal-title font-bold text-lg">Complete Work Order</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-6 space-y-4">
                        <p id="completeModalMessage" class="text-emerald-800 font-medium">Upload images to document your completed work (optional):</p>
                        
                        <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                            <label for="completion_images" class="block text-xs font-bold uppercase tracking-wider text-emerald-600 mb-2">Upload Images</label>
                            <input type="file" class="w-full text-sm text-emerald-700 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer" 
                                   name="images[]" id="completion_images" multiple accept="image/*">
                            <p class="text-[10px] text-emerald-500 mt-2 italic">Supported: JPG, PNG, WEBP. Max 5MB per image.</p>
                        </div>
                        
                        <div id="bulkCompleteSelectedIds"></div>
                    </div>
                    <div class="modal-footer bg-gray-50 p-4 border-t border-gray-100 flex gap-2">
                        <button type="button" class="flex-1 py-2 px-4 rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-100 transition-all" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-700 transition-all shadow-md">Confirm Completion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Reason Modal -->
    <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content !rounded-2xl border-none shadow-2xl overflow-hidden">
                <form id="rejectReasonForm" method="POST">
                    @csrf
                    <div class="modal-header bg-red-600 text-white border-none py-4 px-6">
                        <h5 class="modal-title font-bold text-lg">Reject Work Order</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-6 space-y-4">
                        <p id="rejectModalMessage" class="text-red-800 font-medium font-bold italic">Please provide a reason for rejecting the selected order(s):</p>
                        
                        <div class="space-y-2">
                            <label for="rejection_reason" class="block text-xs font-bold uppercase tracking-wider text-red-600">Rejection Reason <span class="text-red-500">*</span></label>
                            <textarea class="w-full p-4 bg-red-50 border border-red-100 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all placeholder:text-red-300" 
                                      name="rejection_reason" id="rejection_reason" rows="3" required placeholder="Enter detailed reason for rejection..."></textarea>
                        </div>
                        
                        <div id="bulkSelectedIds"></div>
                    </div>
                    <div class="modal-footer bg-gray-50 p-4 border-t border-gray-100 flex gap-2">
                        <button type="button" class="flex-1 py-2 px-4 rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-100 transition-all" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-red-600 text-white font-bold hover:bg-red-700 transition-all shadow-md">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select All functionality for Allocated Orders
        const selectAllCheckbox = document.getElementById('selectAllAllocated');
        const checkboxes = document.querySelectorAll('.allocated-checkbox');

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }

        // Select All functionality for In Process Orders
        const selectAllInProcessCheckbox = document.getElementById('selectAllInProcess');
        const inProcessCheckboxes = document.querySelectorAll('.in-process-checkbox');

        if (selectAllInProcessCheckbox) {
            selectAllInProcessCheckbox.addEventListener('change', function() {
                inProcessCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }

        // Initialize Category Dropdown
        initSearchableDropdown('category_filter_container', 'category_filter_display', 'category_filter_menu', 'category_filter_search', 'category_filter_list', 'product_category_filter_select', 'All Categories');
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
            const isVisible = !menu.classList.contains('hidden');
            
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
            
            display.querySelector('span').textContent = val ? text : placeholder;
            hiddenSelect.value = val;
            
            getListItems().forEach(i => i.classList.remove('bg-emerald-50', 'font-bold'));
            item.classList.add('bg-emerald-50', 'font-bold');
            
            menu.classList.add('hidden');
        });

        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

        if (hiddenSelect.value) {
            const selectedItem = Array.from(getListItems()).find(i => i.dataset.value === hiddenSelect.value);
            if (selectedItem) {
                display.querySelector('span').textContent = selectedItem.textContent.trim();
                selectedItem.classList.add('bg-emerald-50', 'font-bold');
            }
        }
    }
</script>
<script>
    // Reject Reason Modal Functionality
    const rejectReasonModal = new bootstrap.Modal(document.getElementById('rejectReasonModal'));
    const rejectReasonForm = document.getElementById('rejectReasonForm');
    const rejectModalMessage = document.getElementById('rejectModalMessage');
    const bulkSelectedIdsDiv = document.getElementById('bulkSelectedIds');
    const rejectionReasonTextarea = document.getElementById('rejection_reason');

    window.showSingleRejectModal = function(actionUrl) {
        rejectReasonForm.action = actionUrl;
        rejectModalMessage.textContent = "Are you sure you want to reject this work order?";
        bulkSelectedIdsDiv.innerHTML = "";
        rejectionReasonTextarea.value = "";
        rejectReasonModal.show();
    };

    window.showBulkRejectModal = function() {
        const selectedIds = Array.from(document.querySelectorAll('.allocated-checkbox:checked')).map(cb => cb.value);
        
        if (selectedIds.length === 0) {
            alert('Please select at least one work order to reject.');
            return;
        }

        rejectReasonForm.action = "{{ route('craftsman.work-order.bulk-reject') }}";
        rejectModalMessage.textContent = `Are you sure you want to reject ${selectedIds.length} selected work order(s)?`;
        
        // Add hidden inputs for selected IDs
        bulkSelectedIdsDiv.innerHTML = "";
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'work_order_ids[]';
            input.value = id;
            bulkSelectedIdsDiv.appendChild(input);
        });

        rejectionReasonTextarea.value = "";
        rejectReasonModal.show();
    };

    window.validateBulkSelection = function() {
        const selectedIds = document.querySelectorAll('.allocated-checkbox:checked');
        if (selectedIds.length === 0) {
            alert('Please select at least one work order.');
            return false;
        }
        return confirm('Are you sure you want to accept selected work orders?');
    };

    // Completion Modal Functionality
    const completeOrderModal = new bootstrap.Modal(document.getElementById('completeOrderModal'));
    const completeOrderForm = document.getElementById('completeOrderForm');
    const completeModalMessage = document.getElementById('completeModalMessage');
    const bulkCompleteSelectedIdsDiv = document.getElementById('bulkCompleteSelectedIds');

    window.showSingleCompleteModal = function(actionUrl) {
        completeOrderForm.action = actionUrl;
        completeModalMessage.textContent = "Upload images to document your completed work (optional):";
        bulkCompleteSelectedIdsDiv.innerHTML = "";
        document.getElementById('completion_images').value = "";
        completeOrderModal.show();
    };

    window.showBulkCompleteModal = function() {
        const selectedIds = Array.from(document.querySelectorAll('.in-process-checkbox:checked')).map(cb => cb.value);
        
        if (selectedIds.length === 0) {
            alert('Please select at least one work order to complete.');
            return;
        }

        completeOrderForm.action = "{{ route('craftsman.work-order.bulk-complete') }}";
        completeModalMessage.textContent = `Are you sure you want to complete ${selectedIds.length} selected work order(s)? Upload images (optional):`;
        
        // Add hidden inputs for selected IDs
        bulkCompleteSelectedIdsDiv.innerHTML = "";
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'work_order_ids[]';
            input.value = id;
            bulkCompleteSelectedIdsDiv.appendChild(input);
        });

        document.getElementById('completion_images').value = "";
        completeOrderModal.show();
    };
</script>
@if(request('tab'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show the requested tab
    var tabName = "{{ request('tab') }}";
    var tabTrigger = document.querySelector('#' + tabName + '-tab');
    if (tabTrigger) {
        var tab = new bootstrap.Tab(tabTrigger);
        tab.show();
    }
});
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update hidden input and URL when tabs are switched
    var tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabButtons.forEach(function(button) {
        button.addEventListener('shown.bs.tab', function(event) {
            var tabName = event.target.getAttribute('id').replace('-tab', '');
            
            // Update hidden input for filters
            var tabInput = document.getElementById('current_tab_input');
            if (tabInput) tabInput.value = tabName;
            
            // Update URL without reloading to preserve state for pagination/filters
            var url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.pushState({}, '', url);
        });
    });
});
</script>
@endsection

@section('styles')
<style>
  tr[style*="background-color"] > td, tr[style*="background-color"] > th {
      background-color: transparent !important;
  }
</style>
@endsection
