@extends('super-admin.layouts.app')

@section('title', 'Live Stock Orders')

@section('content')
<div class="tw-max-w-7xl tw-mx-auto">
    <!-- Header -->
    <div class="tw-mb-8 tw-flex tw-justify-between tw-items-center">
        <div>
            <h1 class="tw-text-2xl tw-font-black tw-text-slate-900 tw-tracking-tight tw-uppercase">Live Stock Orders</h1>
            <p class="tw-text-slate-500 tw-text-sm tw-font-medium">Manage and allocate live stock orders from buyers</p>
        </div>
        <a href="{{ route('super-admin.stock-order.create') }}" class="tw-bg-indigo-600 tw-text-white tw-px-6 tw-py-3 tw-rounded-xl tw-font-black tw-text-xs tw-uppercase tw-tracking-widest hover:tw-bg-indigo-700 tw-transition-all tw-shadow-lg tw-shadow-indigo-100 tw-flex tw-items-center tw-gap-2">
            <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
            Add New Stock Order
        </a>
    </div>

    <!-- Filters -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-100 tw-p-5 tw-mb-6">
        <form action="{{ route('super-admin.stock-order.index') }}" method="GET" class="tw-flex tw-flex-wrap tw-gap-4 tw-items-end">
            <div class="tw-flex-1 tw-min-w-[200px]">
                <label class="tw-block tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-2">Search Order / Buyer</label>
                <div class="tw-relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Order ID or Business Name..." 
                           class="tw-w-full tw-bg-slate-50 tw-border-none tw-rounded-xl tw-px-4 tw-py-3 tw-text-sm tw-font-bold tw-text-slate-700 placeholder:tw-text-slate-400 focus:tw-ring-2 focus:tw-ring-indigo-500/10 tw-transition-all">
                    <svg class="tw-w-4 tw-h-4 tw-text-slate-400 tw-absolute tw-right-4 tw-top-1/2 -tw-translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <div class="tw-w-48">
                <label class="tw-block tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-2">Status</label>
                <select name="status" class="tw-w-full tw-bg-slate-50 tw-border-none tw-rounded-xl tw-px-4 tw-py-3 tw-text-sm tw-font-bold tw-text-slate-700 focus:tw-ring-2 focus:tw-ring-indigo-500/10 tw-transition-all">
                    <option value="">All Statuses</option>
                    @foreach(['Pending', 'Allocated', 'Completed', 'Cancelled'] as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <div class="tw-w-40">
                <label class="tw-block tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-2">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                       class="tw-w-full tw-bg-slate-50 tw-border-none tw-rounded-xl tw-px-4 tw-py-3 tw-text-sm tw-font-bold tw-text-slate-700 focus:tw-ring-2 focus:tw-ring-indigo-500/10 tw-transition-all">
            </div>

            <div class="tw-w-40">
                <label class="tw-block tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-2">To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                       class="tw-w-full tw-bg-slate-50 tw-border-none tw-rounded-xl tw-px-4 tw-py-3 tw-text-sm tw-font-bold tw-text-slate-700 focus:tw-ring-2 focus:tw-ring-indigo-500/10 tw-transition-all">
            </div>

            <div class="tw-flex tw-gap-2">
                <button type="submit" class="tw-bg-slate-900 tw-text-white tw-px-6 tw-py-3 tw-rounded-xl tw-font-black tw-text-xs tw-uppercase tw-tracking-widest hover:tw-bg-slate-800 tw-transition-all">
                    Filter
                </button>
                <a href="{{ route('super-admin.stock-order.index') }}" class="tw-bg-slate-100 tw-text-slate-600 tw-px-6 tw-py-3 tw-rounded-xl tw-font-black tw-text-xs tw-uppercase tw-tracking-widest hover:tw-bg-slate-200 tw-transition-all">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Success Notification -->
    @if(session('success'))
    <div class="tw-mb-6 tw-p-4 tw-bg-emerald-50 tw-border tw-border-emerald-100 tw-text-emerald-600 tw-rounded-xl tw-font-bold tw-text-sm tw-shadow-sm tw-flex tw-items-center tw-gap-3">
        <div class="tw-w-2 tw-h-2 tw-rounded-full tw-bg-emerald-500 tw-animate-pulse"></div>
        {{ session('success') }}
    </div>
    @endif

    <!-- Modern Navigation Tabs -->
    <div class="tw-border-b tw-border-slate-200 tw-mb-6">
        <ul class="tw-flex tw-flex-wrap tw-text-sm tw-font-medium tw-text-center tw-text-slate-500" id="stockOrderTabs">
            @php
            $tabs = [
                'new-orders' => ['label' => 'New Orders', 'count' => $counts['new-orders'] ?? 0, 'icon' => 'bi-plus-circle'],
                'allocated-orders' => ['label' => 'Allocated', 'count' => $counts['allocated-orders'] ?? 0, 'icon' => 'bi-people'],
                'in-process-orders' => ['label' => 'In Process', 'count' => $counts['in-process-orders'] ?? 0, 'icon' => 'bi-gear'],
                'for-approval-orders' => ['label' => 'For Approval', 'count' => $counts['for-approval-orders'] ?? 0, 'icon' => 'bi-check2-square'],
                'completed-orders' => ['label' => 'Completed', 'count' => $counts['completed-orders'] ?? 0, 'icon' => 'bi-check-all'],
                'rejected-orders' => ['label' => 'Rejected', 'count' => $counts['rejected-orders'] ?? 0, 'icon' => 'bi-x-circle'],
                'all-orders' => ['label' => 'All Orders', 'count' => $counts['all-orders'] ?? 0, 'icon' => 'bi-list-ul'],
            ];
            $activeTab = $activeTab ?? 'new-orders';
            @endphp

            @foreach($tabs as $id => $tab)
            <li class="tw-mr-2">
                <a href="{{ route('super-admin.stock-order.index', array_merge(request()->query(), ['tab' => $id])) }}"
                    class="tw-inline-flex tw-items-center tw-px-4 tw-py-4 tw-border-b-2 tw-rounded-t-lg tw-transition-all tw-duration-200 {{ $activeTab == $id ? 'tw-text-indigo-600 tw-border-indigo-600' : 'tw-border-transparent hover:tw-text-slate-600 hover:tw-border-slate-300' }}"
                    id="{{ $id }}-tab">
                    <i class="bi {{ $tab['icon'] }} tw-mr-2"></i>
                    {{ $tab['label'] }}
                    <span class="tw-ml-2 tw-px-2 tw-py-0.5 tw-text-xs tw-font-semibold tw-rounded-full {{ $activeTab == $id ? 'tw-bg-indigo-100 tw-text-indigo-600' : 'tw-bg-slate-100 tw-text-slate-600' }}">
                        {{ $tab['count'] }}
                    </span>
                </a>
            </li>
            @endforeach
        </ul>
    </div>

    <!-- Data Table Container -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-100 tw-overflow-hidden">
        <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-left tw-border-collapse">
                <thead>
                    <tr class="tw-bg-slate-50/50 tw-border-b tw-border-slate-100">
                        <th class="tw-px-6 tw-py-4 tw-w-10">
                            <input type="checkbox" id="select-all" class="tw-rounded tw-border-slate-300 tw-text-indigo-600 focus:tw-ring-indigo-500">
                        </th>
                        <th class="tw-px-6 tw-py-4 tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest">Order ID</th>
                        <th class="tw-px-6 tw-py-4 tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest">Buyer</th>
                        <th class="tw-px-6 tw-py-4 tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest">Items</th>
                        <th class="tw-px-6 tw-py-4 tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest">Status</th>
                        <th class="tw-px-6 tw-py-4 tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest">Allocation</th>
                        <th class="tw-px-6 tw-py-4 tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest">Date</th>
                        <th class="tw-px-6 tw-py-4 tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="tw-divide-y tw-divide-slate-50">
                    @forelse($orders as $order)
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
                    <tr class="hover:tw-bg-slate-50/50 tw-transition-colors tw-group " style="{{ $rowStyle }}">
                        <td class="tw-px-6 tw-py-4">
                            @if($order->status == 'Pending' || $activeTab == 'in-process-orders' || $activeTab == 'for-approval-orders')
                            <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="order-checkbox tw-rounded tw-border-slate-300 tw-text-indigo-600 focus:tw-ring-indigo-500">
                            @endif
                        </td>
                        <td class="tw-px-6 tw-py-4">
                            <div class="tw-font-mono tw-font-bold tw-text-indigo-600 tw-text-sm tw-tracking-tighter">{{ $order->order_number }}</div>
                        </td>
                        <td class="tw-px-6 tw-py-4">
                            <div class="tw-font-bold tw-text-slate-900 tw-text-sm">{{ $order->buyer->business_name }}</div>
                            <div class="tw-text-[10px] tw-text-slate-400 tw-font-bold tw-uppercase tw-tracking-wider">{{ $order->buyer->bp_code }}</div>
                        </td>
                        <td class="tw-px-6 tw-py-4">
                            <span class="tw-bg-slate-100 tw-text-slate-600 tw-px-2.5 tw-py-1 tw-rounded-md tw-text-[11px] tw-font-bold">
                                {{ $order->items_count }} Items
                            </span>
                        </td>
                        <td class="tw-px-6 tw-py-4">
                            @php
                                $statusColors = [
                                    'Pending' => 'tw-bg-amber-50 tw-text-amber-600 tw-border-amber-100',
                                    'Allocated' => 'tw-bg-blue-50 tw-text-blue-600 tw-border-blue-100',
                                    'Completed' => 'tw-bg-emerald-50 tw-text-emerald-600 tw-border-emerald-100',
                                    'Cancelled' => 'tw-bg-red-50 tw-text-red-600 tw-border-red-100',
                                ];
                                $color = $statusColors[$order->status] ?? 'tw-bg-slate-50 tw-text-slate-600 tw-border-slate-100';
                            @endphp
                            <span class="tw-px-2 tw-py-1 tw-rounded tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest tw-border {{ $color }}">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="tw-px-6 tw-py-4">
                            @if($order->craftsman)
                            <div class="tw-flex tw-items-center tw-gap-2">
                                <div class="tw-w-7 tw-h-7 tw-rounded-full tw-bg-indigo-100 tw-border tw-border-indigo-200 tw-flex tw-items-center tw-justify-center tw-text-[10px] tw-font-bold tw-text-indigo-600 tw-uppercase">
                                    {{ substr($order->craftsman->name, 0, 2) }}
                                </div>
                                <div>
                                    <div class="tw-text-sm tw-font-bold tw-text-slate-700">{{ $order->craftsman->name }}</div>
                                    <div class="tw-text-xs tw-text-slate-500 tw-font-medium">{{ $order->craftsman->craftman_code }}</div>
                                </div>
                            </div>
                            @else
                            <div class="tw-flex tw-items-center tw-gap-1.5 tw-text-amber-500">
                                <span class="tw-relative tw-flex tw-h-2 tw-w-2">
                                    <span class="tw-animate-ping tw-absolute tw-inline-flex tw-h-full tw-w-full tw-rounded-full tw-bg-amber-400 tw-opacity-75"></span>
                                    <span class="tw-relative tw-inline-flex tw-rounded-full tw-h-2 tw-w-2 tw-bg-amber-500"></span>
                                </span>
                                <span class="tw-text-[11px] tw-font-bold tw-italic">Waiting Allocation</span>
                            </div>
                            @endif
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-text-xs tw-text-slate-500 tw-font-medium">
                            {{ $order->created_at->format('d M, Y') }}
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-text-right">
                            <a href="{{ route('super-admin.stock-order.show', $order->id) }}" 
                               class="tw-inline-block tw-bg-slate-900 tw-text-white tw-px-5 tw-py-2 tw-rounded-xl tw-font-bold tw-text-[10px] tw-uppercase tw-tracking-widest hover:tw-bg-indigo-600 hover:tw-shadow-lg hover:tw-shadow-indigo-100 tw-transition-all tw-duration-200">
                                Manage
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="tw-px-6 tw-py-16 tw-text-center">
                            <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-12 tw-h-12 tw-rounded-full tw-bg-slate-100 tw-mb-4">
                                <svg class="tw-w-6 tw-h-6 tw-text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                            </div>
                            <p class="tw-text-slate-400 tw-text-sm tw-font-medium tw-italic">No live stock orders found at the moment.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="tw-p-6 tw-border-t tw-border-slate-100">
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

<!-- Bulk Action Bar -->
<div id="bulk-action-bar" class="tw-fixed tw-bottom-6 tw-left-1/2 -tw-translate-x-1/2 tw-bg-slate-900 tw-text-white tw-px-6 tw-py-4 tw-rounded-2xl tw-shadow-2xl tw-border tw-border-slate-800 tw-flex tw-items-center tw-gap-6 tw-z-50 tw-transition-all tw-duration-300 tw-translate-y-24 tw-opacity-0">
    <div class="tw-flex tw-items-center tw-gap-3 tw-border-r tw-border-slate-700 tw-pr-6">
        <span id="selected-count" class="tw-bg-indigo-500 tw-text-white tw-w-6 tw-h-6 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-[10px] tw-font-black">0</span>
        <span class="tw-text-xs tw-font-black tw-uppercase tw-tracking-widest">Orders Selected</span>
    </div>

    <form id="bulk-action-form" method="POST" class="tw-flex tw-items-center tw-gap-4">
        @csrf
        <div id="bulk-order-inputs"></div>
        
        @if($activeTab == 'new-orders' || $activeTab == 'all-orders')
        <div class="tw-flex tw-items-center tw-gap-2">
            <label class="tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest">Allocate To:</label>
            <select name="craftsman_id" class="tw-bg-slate-800 tw-border-none tw-text-white tw-text-xs tw-font-bold tw-rounded-xl tw-px-4 tw-py-2 focus:tw-ring-2 focus:tw-ring-indigo-500 tw-transition-all tw-min-w-[150px]">
                <option value="">Select Craftsman...</option>
                @foreach($craftsmen as $craftsman)
                    <option value="{{ $craftsman->id }}">{{ $craftsman->name }} ({{ $craftsman->bp_code }})</option>
                @endforeach
            </select>
        </div>
        <button type="submit" formaction="{{ route('super-admin.stock-order.bulk-allocate') }}" class="tw-bg-indigo-600 tw-text-white tw-px-6 tw-py-2 tw-rounded-xl tw-font-black tw-text-xs tw-uppercase tw-tracking-widest hover:tw-bg-indigo-700 tw-transition-all tw-shadow-lg tw-shadow-indigo-900/20">
            Bulk Allocate
        </button>
        @endif

        @if($activeTab == 'for-approval-orders' || $activeTab == 'all-orders' || $activeTab == 'new-orders' || $activeTab == 'in-process-orders')
        <button type="submit" formaction="{{ route('super-admin.stock-order.bulk-complete') }}" class="tw-bg-emerald-600 tw-text-white tw-px-6 tw-py-2 tw-rounded-xl tw-font-black tw-text-xs tw-uppercase tw-tracking-widest hover:tw-bg-emerald-700 tw-transition-all tw-shadow-lg tw-shadow-emerald-900/20">
            Bulk Complete
        </button>
        @endif
    </form>

    <button onclick="clearSelection()" class="tw-text-slate-400 hover:tw-text-white tw-transition-colors">
        <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.order-checkbox');
        const actionBar = document.getElementById('bulk-action-bar');
        const selectedCount = document.getElementById('selected-count');
        const bulkOrderInputs = document.getElementById('bulk-order-inputs');

        function updateActionBar() {
            const checked = document.querySelectorAll('.order-checkbox:checked');
            const count = checked.length;
            
            if (count > 0) {
                actionBar.classList.remove('tw-translate-y-24', 'tw-opacity-0');
                selectedCount.innerText = count;
                
                // Update hidden inputs
                bulkOrderInputs.innerHTML = '';
                checked.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'order_ids[]';
                    input.value = cb.value;
                    bulkOrderInputs.appendChild(input);
                });
            } else {
                actionBar.classList.add('tw-translate-y-24', 'tw-opacity-0');
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateActionBar();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateActionBar);
        });
    });

    function clearSelection() {
        document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = false);
        const selectAll = document.getElementById('select-all');
        if (selectAll) selectAll.checked = false;
        
        const actionBar = document.getElementById('bulk-action-bar');
        actionBar.classList.add('tw-translate-y-24', 'tw-opacity-0');
    }
</script>
@endsection

@section('styles')
<style>
  tr[style*="background-color"] > td, tr[style*="background-color"] > th {
      background-color: transparent !important;
  }
</style>
@endsection
