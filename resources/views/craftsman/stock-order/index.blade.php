@extends('craftsman.layouts.app')

@section('title', 'Live Stock Orders')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-black text-emerald-900 tracking-tight uppercase">Live Stock Orders</h1>
    <p class="text-emerald-600 text-sm font-medium">Manage stock orders allocated to you</p>
</div>

@if(session('success'))
<div class="mb-6 p-4 bg-emerald-100 border border-emerald-200 text-emerald-800 rounded-xl font-bold text-sm">
    {{ session('success') }}
</div>
@endif

<!-- Modern Navigation Tabs -->
<div class="border-b border-emerald-200 mb-6">
    <ul class="flex flex-wrap text-sm font-medium text-center text-emerald-500" id="stockOrderTabs">
        @php
        $tabs = [
            'allocated-orders' => ['label' => 'Allocated', 'count' => $counts['allocated-orders'] ?? 0, 'icon' => 'bi-people'],
            'in-process-orders' => ['label' => 'In Process', 'count' => $counts['in-process-orders'] ?? 0, 'icon' => 'bi-gear'],
            'for-approval-orders' => ['label' => 'For Approval', 'count' => $counts['for-approval-orders'] ?? 0, 'icon' => 'bi-hourglass-split'],
            'completed-orders' => ['label' => 'Completed', 'count' => $counts['completed-orders'] ?? 0, 'icon' => 'bi-check-all'],
            'rejected-orders' => ['label' => 'Rejected', 'count' => $counts['rejected-orders'] ?? 0, 'icon' => 'bi-x-circle'],
            'all-orders' => ['label' => 'All Orders', 'count' => $counts['all-orders'] ?? 0, 'icon' => 'bi-list-ul'],
        ];
        $activeTab = $activeTab ?? 'allocated-orders';
        @endphp

        @foreach($tabs as $id => $tab)
        <li class="mr-2">
            <a href="{{ route('craftsman.stock-order.index', array_merge(request()->query(), ['tab' => $id])) }}"
                class="inline-flex items-center px-4 py-4 border-b-2 rounded-t-lg transition-all duration-200 {{ $activeTab == $id ? 'text-emerald-600 border-emerald-600' : 'border-transparent hover:text-emerald-600 hover:border-emerald-300' }}"
                id="{{ $id }}-tab">
                <i class="bi {{ $tab['icon'] }} mr-2"></i>
                {{ $tab['label'] }}
                <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full {{ $activeTab == $id ? 'bg-emerald-100 text-emerald-600' : 'bg-emerald-50 text-emerald-600' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        </li>
        @endforeach
    </ul>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-emerald-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-emerald-50/50 border-b border-emerald-100">
                    <th class="px-6 py-4 w-10">
                        <input type="checkbox" id="select-all" class="rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                    </th>
                    <th class="px-6 py-4 text-[10px] font-black text-emerald-400 uppercase tracking-widest">Order ID</th>
                    <th class="px-6 py-4 text-[10px] font-black text-emerald-400 uppercase tracking-widest">Buyer</th>
                    <th class="px-6 py-4 text-[10px] font-black text-emerald-400 uppercase tracking-widest">Items</th>
                    <th class="px-6 py-4 text-[10px] font-black text-emerald-400 uppercase tracking-widest">Status</th>
                    <th class="px-6 py-4 text-[10px] font-black text-emerald-400 uppercase tracking-widest">Date</th>
                    <th class="px-6 py-4 text-[10px] font-black text-emerald-400 uppercase tracking-widest text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-emerald-50">
                @forelse($orders as $order)
                @php
                    $rowStyle = '';
                    $allocatedWithin48Hours = false;
                    $now = \Carbon\Carbon::now();

                    $updatedAtValue = isset($order->updated_at) ? $order->updated_at : null;
                    $currentTabString = $activeTab ?? 'allocated-orders';

                    if ($currentTabString == 'in-process-orders' || $currentTabString == 'in-process' || $currentTabString == 'in_process') {
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
                <tr class="hover:bg-emerald-50/30 transition-colors" style="{{ $rowStyle }}">
                    <td class="px-6 py-4">
                        @if($activeTab == 'allocated-orders')
                        <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="order-checkbox rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-mono font-bold text-emerald-700 text-sm">{{ $order->order_number }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-emerald-900 text-sm">{{ $order->buyer->business_name }}</div>
                        <div class="text-[10px] text-emerald-500 font-bold uppercase tracking-wider">{{ $order->buyer->bp_code }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="bg-emerald-50 text-emerald-700 px-2 py-1 rounded-md text-xs font-bold">
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
                    <td class="px-6 py-4 text-xs text-emerald-600 font-medium">
                        {{ $order->created_at->format('d M, Y') }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('craftsman.stock-order.show', $order->id) }}" class="bg-emerald-900 text-white px-4 py-2 rounded-lg font-bold text-xs uppercase tracking-wider hover:bg-emerald-800 transition-all">
                            View Details
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-emerald-400 italic">No stock orders found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="px-6 py-4 bg-emerald-50/50 border-t border-emerald-100">
        {{ $orders->links() }}
    </div>
    @endif
</div>

<!-- Bulk Action Bar -->
<div id="bulk-action-bar" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-emerald-900 text-white px-6 py-4 rounded-2xl shadow-2xl border border-emerald-800 flex items-center gap-6 z-50 transition-all duration-300 translate-y-24 opacity-0">
    <div class="flex items-center gap-3 border-r border-emerald-700 pr-6">
        <span id="selected-count" class="bg-emerald-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black">0</span>
        <span class="text-xs font-black uppercase tracking-widest">Orders Selected</span>
    </div>

    <form id="bulk-action-form" method="POST" class="flex items-center gap-4">
        @csrf
        <div id="bulk-order-inputs"></div>
        
        @if($activeTab == 'allocated-orders')
        <button type="submit" formaction="{{ route('craftsman.stock-order.bulk-accept') }}" class="bg-emerald-600 text-white px-6 py-2 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-900/20">
            Bulk Accept
        </button>
        
        <div class="flex items-center gap-2">
            <input type="text" name="rejection_reason" placeholder="Reason for rejection..." class="bg-emerald-800 border-none text-white text-xs font-bold rounded-xl px-4 py-2 placeholder:text-emerald-400 focus:ring-2 focus:ring-emerald-500 transition-all min-w-[200px]">
            <button type="submit" formaction="{{ route('craftsman.stock-order.bulk-reject') }}" class="bg-red-600 text-white px-6 py-2 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-red-700 transition-all shadow-lg shadow-red-900/20">
                Bulk Reject
            </button>
        </div>
        @endif
    </form>

    <button onclick="clearSelection()" class="text-emerald-400 hover:text-white transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
</div>

<!-- Scripts for Bulk Action -->
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
