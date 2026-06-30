@extends('admin.layouts.app')

@section('title', 'Live Stock Orders')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Live Stock Orders</h1>
        <p class="text-slate-500 text-sm font-medium">Manage and allocate live stock orders from buyers</p>
    </div>
    <a href="{{ route('admin.stock-order.create') }}" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
        Add New Stock Order
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-6">
    <form action="{{ route('admin.stock-order.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Search Order / Buyer</label>
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Order ID or Business Name..." 
                       class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/10 transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute right-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>

        <div class="w-48">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Status</label>
            <select name="status" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/10 transition-all">
                <option value="">All Statuses</option>
                @foreach(['Pending', 'Allocated', 'Completed', 'Cancelled'] as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-40">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">From Date</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                   class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/10 transition-all">
        </div>

        <div class="w-40">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">To Date</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                   class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/10 transition-all">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-slate-900 text-white px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-slate-800 transition-all">
                Filter
            </button>
            <a href="{{ route('admin.stock-order.index') }}" class="bg-slate-100 text-slate-600 px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-slate-200 transition-all">
                Clear
            </a>
        </div>
    </form>
</div>

@if(session('success'))
<div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-xl font-bold text-sm">
    {{ session('success') }}
</div>
@endif

<!-- Modern Navigation Tabs -->
<div class="border-b border-slate-200 mb-6">
    <ul class="flex flex-wrap text-sm font-medium text-center text-slate-500" id="stockOrderTabs">
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
        <li class="mr-2">
            <a href="{{ route('admin.stock-order.index', array_merge(request()->query(), ['tab' => $id])) }}"
                class="inline-flex items-center px-4 py-4 border-b-2 rounded-t-lg transition-all duration-200 {{ $activeTab == $id ? 'text-indigo-600 border-indigo-600' : 'border-transparent hover:text-slate-600 hover:border-slate-300' }}"
                id="{{ $id }}-tab">
                <i class="bi {{ $tab['icon'] }} mr-2"></i>
                {{ $tab['label'] }}
                <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full {{ $activeTab == $id ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-600' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        </li>
        @endforeach
    </ul>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-6 py-4 w-10">
                        <input type="checkbox" id="select-all" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    </th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Order ID</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Buyer</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Items</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Allocation</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($orders as $order)
                @php
                    $rowStyle = '';
                    $allocatedWithin48Hours = false;
                    $now = \Carbon\Carbon::now();

                    $updatedAtValue = isset($order->updated_at) ? $order->updated_at : null;
                    $currentTabString = $activeTab ?? 'new-orders';

                    if (($currentTabString == 'allocated-orders' || $currentTabString == 'allocated') && $updatedAtValue) {
                        if (\Carbon\Carbon::parse($updatedAtValue)->diffInHours($now) <= 48) {
                            $allocatedWithin48Hours = true;
                        }
                    }

                    if ($currentTabString == 'in-process-orders' || $currentTabString == 'in-process' || $currentTabString == 'in_process') {
                        $rowStyle = 'background-color: rgba(220, 252, 231, 0.8) !important;'; // green
                    } elseif (($currentTabString == 'allocated-orders' || $currentTabString == 'allocated') && $allocatedWithin48Hours) {
                        $rowStyle = 'background-color: rgba(219, 234, 254, 0.8) !important;'; // blue
                    } elseif ($currentTabString == 'new-orders' || $currentTabString == 'created') {
                        $rowStyle = 'background-color: rgba(254, 252, 232, 0.8) !important;'; // yellow
                    }
                @endphp
                <tr class="hover:bg-slate-50/50 transition-colors" style="{{ $rowStyle }}">
                    <td class="px-6 py-4">
                        @if($order->status == 'Pending' || $activeTab == 'in-process-orders' || $activeTab == 'for-approval-orders')
                        <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="order-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-mono font-bold text-indigo-600 text-sm">{{ $order->order_number }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-900 text-sm">{{ $order->buyer->business_name }}</div>
                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ $order->buyer->bp_code }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded-md text-xs font-bold">
                            {{ $order->items_count }} Items
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $statusColors = [
                                'Pending' => 'bg-amber-50 text-amber-600 border-amber-100',
                                'Allocated' => 'bg-blue-50 text-blue-600 border-blue-100',
                                'Completed' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                'Cancelled' => 'bg-red-50 text-red-600 border-red-100',
                            ];
                            $color = $statusColors[$order->status] ?? 'bg-slate-50 text-slate-600 border-slate-100';
                        @endphp
                        <span class="px-2 py-1 rounded text-[10px] font-black uppercase tracking-widest border {{ $color }}">
                            {{ $order->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($order->craftsman)
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-[10px] font-bold text-indigo-600 uppercase">
                                {{ substr($order->craftsman->name, 0, 2) }}
                                
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-700">{{ $order->craftsman->name }}</div>
                                <div class="text-xs text-slate-500 font-medium">{{ $order->craftsman->craftman_code }}</div>
                            </div>
                        </div>
                        @else
                        <span class="text-amber-500 text-xs font-bold italic">Waiting Allocation</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-500 font-medium">
                        {{ $order->created_at->format('d M, Y') }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.stock-order.show', $order->id) }}" class="bg-slate-900 text-white px-4 py-2 rounded-lg font-bold text-xs uppercase tracking-wider hover:bg-slate-800 transition-all">
                            Manage
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-400 italic">No stock orders found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="p-6 border-t border-slate-100">
        {{ $orders->links() }}
    </div>
    @endif
</div>

<!-- Bulk Action Bar -->
<div id="bulk-action-bar" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-slate-900 text-white px-6 py-4 rounded-2xl shadow-2xl border border-slate-800 flex items-center gap-6 z-50 transition-all duration-300 translate-y-24 opacity-0">
    <div class="flex items-center gap-3 border-r border-slate-700 pr-6">
        <span id="selected-count" class="bg-indigo-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black">0</span>
        <span class="text-xs font-black uppercase tracking-widest">Orders Selected</span>
    </div>

    <form id="bulk-action-form" method="POST" class="flex items-center gap-4">
        @csrf
        <div id="bulk-order-inputs"></div>
        
        @if($activeTab == 'new-orders' || $activeTab == 'all-orders')
        <div class="flex items-center gap-2">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Allocate To:</label>
            <select name="craftsman_id" class="bg-slate-800 border-none text-white text-xs font-bold rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 transition-all min-w-[150px]">
                <option value="">Select Craftsman...</option>
                @foreach($craftsmen as $craftsman)
                    <option value="{{ $craftsman->id }}">{{ $craftsman->name }} ({{ $craftsman->bp_code }})</option>
                @endforeach
            </select>
        </div>
        <button type="submit" formaction="{{ route('admin.stock-order.bulk-allocate') }}" class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-900/20">
            Bulk Allocate
        </button>
        @endif

        @if($activeTab == 'for-approval-orders' || $activeTab == 'all-orders' || $activeTab == 'new-orders' || $activeTab == 'in-process-orders')
        <button type="submit" formaction="{{ route('admin.stock-order.bulk-complete') }}" class="bg-emerald-600 text-white px-6 py-2 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-900/20">
            Bulk Complete
        </button>
        @endif
    </form>

    <button onclick="clearSelection()" class="text-slate-400 hover:text-white transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
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
                actionBar.classList.remove('translate-y-24', 'opacity-0');
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
                actionBar.classList.add('translate-y-24', 'opacity-0');
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
        actionBar.classList.add('translate-y-24', 'opacity-0');
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
