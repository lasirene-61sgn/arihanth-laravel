@extends('admin.layouts.app')

@section('title', 'Craftsman Production - ' . $craftsman->name)

@section('content')
<style>
    .highlight-term {
        background-color: #ffeb3b !important;
        color: #000000 !important;
        font-weight: 700;
        padding: 1px 4px;
        border-radius: 3px;
        display: inline-block;
    }
</style>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Production Dashboard: {{ $craftsman->name }}</h1>
            <p class="text-gray-500 dark:text-gray-400">BP Code: {{ $craftsman->craftman_code }}</p>
        </div>
        <a href="{{ route('admin.craftsman-production.index') }}" class="flex items-center text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
            <i class="bi bi-arrow-left mr-2"></i> Back to List
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md border-l-4 border-magenta-800">
            <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Work Orders</p>
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white">{{ $workOrders->total() }}</h2>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md border-l-4 border-blue-500">
            <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Purchase Orders</p>
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white">{{ $purchaseOrders->total() }}</h2>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md border-l-4 border-green-500">
            <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Total PO Weight</p>
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white">{{ number_format($purchaseOrders->sum('total_calculated_weight'), 3) }} g</h2>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md border-l-4 border-yellow-500">
            <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Active Tab</p>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white capitalize">{{ str_replace('_', ' ', $tab) }}</h2>
        </div>
    </div>

    <!-- Filter and Live Search Controls -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md mb-8">
        <h3 class="font-bold text-gray-800 dark:text-white flex items-center mb-4">
            <i class="bi bi-funnel mr-2 text-magenta-800"></i> Filter & Live Search
        </h3>
        <form action="{{ request()->url() }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4" id="showFilterForm">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <!-- Live Search Input -->
            <div class="md:col-span-5">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           id="liveShowSearch" 
                           name="search" 
                           class="w-full pl-10 pr-10 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-magenta-800 focus:outline-none" 
                           placeholder="Type to search orders, products, customers..." 
                           value="{{ request('search', $search ?? '') }}" 
                           autocomplete="off">
                    <button type="button" 
                            id="clearShowSearchBtn" 
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hidden" 
                            title="Clear Search">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
            </div>

            <!-- Buyer Filter Dropdown -->
            <div class="md:col-span-4">
                <select name="buyer_code" 
                        class="w-full p-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-magenta-800 focus:outline-none" 
                        onchange="document.getElementById('showFilterForm').submit();">
                    <option value="">All Buyers</option>
                    @foreach($buyers as $buyer)
                        <option value="{{ $buyer->bp_code }}" {{ ($buyerCode ?? request('buyer_code')) == $buyer->bp_code ? 'selected' : '' }}>
                            {{ $buyer->business_name ?? $buyer->name }} ({{ $buyer->bp_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Per Page Dropdown -->
            <div class="md:col-span-2">
                <select name="per_page" 
                        class="w-full p-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-magenta-800 focus:outline-none" 
                        onchange="document.getElementById('showFilterForm').submit();">
                    <option value="10" {{ request('per_page', $perPage ?? 10) == 10 ? 'selected' : '' }}>10 Per Page</option>
                    <option value="20" {{ request('per_page', $perPage ?? 10) == 20 ? 'selected' : '' }}>20 Per Page</option>
                    <option value="50" {{ request('per_page', $perPage ?? 10) == 50 ? 'selected' : '' }}>50 Per Page</option>
                    <option value="100" {{ request('per_page', $perPage ?? 10) == 100 ? 'selected' : '' }}>100 Per Page</option>
                </select>
            </div>

            <!-- Reset Button -->
            <div class="md:col-span-1">
                <a href="{{ request()->url() }}?tab={{ $tab }}" class="block text-center py-2 px-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg font-medium transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Buyer Metrics Summary -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-750">
            <h3 class="font-bold text-gray-800 dark:text-white flex items-center">
                <i class="bi bi-bar-chart mr-2 text-magenta-800"></i> Buyer Metrics Summary
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Order Type</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase text-center">Allocated</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase text-center">In Process</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase text-center">Overdue</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase text-center">Completed</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase text-center">Rejected</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <td class="px-6 py-4 font-bold text-gray-800 dark:text-white">Work Orders</td>
                        <td class="px-6 py-4 text-center">{{ $buyerMetrics['work_orders']['allocated'] }}</td>
                        <td class="px-6 py-4 text-center">{{ $buyerMetrics['work_orders']['in_process'] }}</td>
                        <td class="px-6 py-4 text-center">{{ $buyerMetrics['work_orders']['overdue'] }}</td>
                        <td class="px-6 py-4 text-center">{{ $buyerMetrics['work_orders']['completed'] }}</td>
                        <td class="px-6 py-4 text-center">{{ $buyerMetrics['work_orders']['rejected'] }}</td>
                    </tr>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <td class="px-6 py-4 font-bold text-gray-800 dark:text-white">Purchase Orders</td>
                        <td class="px-6 py-4 text-center">{{ $buyerMetrics['purchase_orders']['allocated'] }}</td>
                        <td class="px-6 py-4 text-center">{{ $buyerMetrics['purchase_orders']['in_process'] }}</td>
                        <td class="px-6 py-4 text-center">{{ $buyerMetrics['purchase_orders']['overdue'] }}</td>
                        <td class="px-6 py-4 text-center">{{ $buyerMetrics['purchase_orders']['completed'] }}</td>
                        <td class="px-6 py-4 text-center">{{ $buyerMetrics['purchase_orders']['rejected'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex space-x-2 mb-8 bg-gray-100 dark:bg-gray-900 p-1 rounded-xl w-fit">
        @foreach(['new', 'in_process', 'completed', 'overdue'] as $status)
        <a href="{{ request()->fullUrlWithQuery(['tab' => $status, 'wo_page' => 1, 'po_page' => 1]) }}" 
           class="px-6 py-2 rounded-lg text-sm font-bold transition-all {{ $tab == $status ? 'bg-magenta-800 text-white shadow-lg' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800' }}">
            {{ ucwords(str_replace('_', ' ', $status)) }}
        </a>
        @endforeach
    </div>

    <!-- Work Orders Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-750">
            <h3 class="font-bold text-gray-800 dark:text-white flex items-center">
                <i class="bi bi-clipboard-check mr-2 text-magenta-800"></i> Work Orders
            </h3>
            <span class="bg-magenta-100 text-magenta-800 px-3 py-1 rounded-full text-xs font-bold">{{ $workOrders->total() }} Orders</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="woTable">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">WO Number</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Product</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Customer</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase text-center">Qty</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Due Date</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($workOrders as $wo)
                    <tr class="wo-row hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <td class="show-item px-6 py-4 font-bold text-gray-800 dark:text-white" data-text="{{ $wo->work_order_number }}">
                            {{ $wo->work_order_number }}
                        </td>
                        <td class="show-item px-6 py-4 text-gray-700 dark:text-gray-300" data-text="{{ $wo->product_name }}">
                            {{ $wo->product_name }}
                        </td>
                        <td class="show-item px-6 py-4 text-gray-600 dark:text-gray-400 text-sm" data-text="{{ $wo->customer_name }}">
                            {{ $wo->customer_name }}
                        </td>
                        <td class="px-6 py-4 text-center font-medium">{{ $wo->quantity }}</td>
                        <td class="px-6 py-4 text-sm">{{ $wo->craftsman_due_date ? Carbon\Carbon::parse($wo->craftsman_due_date)->format('d M Y') : 'N/A' }}</td>
                        <td class="px-6 py-4">
                            @php
                                $badgeClass = $wo->status == 'completed' ? 'bg-green-100 text-green-700' : ($wo->isOverdue() ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700');
                            @endphp
                            <span class="show-item {{ $badgeClass }} px-3 py-1 rounded-full text-[10px] font-black uppercase" data-text="{{ strtoupper($wo->status) }}">
                                {{ strtoupper($wo->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">No work orders found in this category.</td>
                    </tr>
                    @endforelse

                    <tr id="woNoMatch" class="hidden">
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">
                            <i class="bi bi-info-circle mr-1"></i> No matching work orders found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if($workOrders->hasPages())
        <div class="p-6 border-t border-gray-200 dark:border-gray-700 pagination-container">
            {{ $workOrders->links() }}
        </div>
        @endif
    </div>

    <!-- Purchase Orders Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-750">
            <h3 class="font-bold text-gray-800 dark:text-white flex items-center">
                <i class="bi bi-cart3 mr-2 text-blue-500"></i> Purchase Orders
            </h3>
            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-bold">{{ $purchaseOrders->total() }} Orders</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="poTable">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">PO Code</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase text-center">Items</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase text-right">Weight</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Due Date</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($purchaseOrders as $po)
                    <tr class="po-row hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <td class="show-item px-6 py-4 font-bold text-gray-800 dark:text-white" data-text="{{ $po->purchase_order_code ?? $po->po_number }}">
                            {{ $po->purchase_order_code ?? $po->po_number }}
                        </td>
                        <td class="px-6 py-4 text-center">{{ count($po->items ?? []) }}</td>
                        <td class="px-6 py-4 text-right font-mono font-bold">{{ number_format($po->total_calculated_weight ?? 0, 3) }} g</td>
                        <td class="px-6 py-4 text-sm">{{ $po->due_date ? Carbon\Carbon::parse($po->due_date)->format('d M Y') : 'N/A' }}</td>
                        <td class="px-6 py-4">
                            @php
                                $badgeClass = in_array($po->status, ['approved', 'completed']) ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700';
                            @endphp
                            <span class="show-item {{ $badgeClass }} px-3 py-1 rounded-full text-[10px] font-black uppercase" data-text="{{ strtoupper($po->status) }}">
                                {{ strtoupper($po->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">No purchase orders found in this category.</td>
                    </tr>
                    @endforelse

                    <tr id="poNoMatch" class="hidden">
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">
                            <i class="bi bi-info-circle mr-1"></i> No matching purchase orders found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if($purchaseOrders->hasPages())
        <div class="p-6 border-t border-gray-200 dark:border-gray-700 pagination-container">
            {{ $purchaseOrders->links() }}
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('liveShowSearch');
    const clearBtn = document.getElementById('clearShowSearchBtn');
    
    const woRows = document.querySelectorAll('#woTable tbody tr.wo-row');
    const poRows = document.querySelectorAll('#poTable tbody tr.po-row');
    
    const woNoMatch = document.getElementById('woNoMatch');
    const poNoMatch = document.getElementById('poNoMatch');
    const paginations = document.querySelectorAll('.pagination-container');

    function performLiveSearch(query) {
        const rawTerm = query.trim();
        const term = rawTerm.toLowerCase();

        if (clearBtn) {
            clearBtn.classList.toggle('hidden', rawTerm === '');
        }

        if (term === '') {
            woRows.forEach(row => {
                row.style.display = '';
                row.querySelectorAll('.show-item').forEach(cell => {
                    cell.innerHTML = cell.getAttribute('data-text');
                });
            });
            poRows.forEach(row => {
                row.style.display = '';
                row.querySelectorAll('.show-item').forEach(cell => {
                    cell.innerHTML = cell.getAttribute('data-text');
                });
            });
            if (woNoMatch) woNoMatch.classList.add('hidden');
            if (poNoMatch) poNoMatch.classList.add('hidden');
            paginations.forEach(p => p.style.display = '');
            return;
        }

        paginations.forEach(p => p.style.display = 'none');
        const regex = new RegExp(`(${rawTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');

        function filterList(rows, noMatchElem) {
            let matched = 0;
            rows.forEach(row => {
                const cells = row.querySelectorAll('.show-item');
                let rowMatched = false;

                cells.forEach(cell => {
                    const original = cell.getAttribute('data-text') || '';
                    if (original.toLowerCase().includes(term)) {
                        rowMatched = true;
                        cell.innerHTML = original.replace(regex, '<span class="highlight-term">$1</span>');
                    } else {
                        cell.innerHTML = original;
                    }
                });

                if (rowMatched) {
                    row.style.display = '';
                    matched++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (noMatchElem) {
                noMatchElem.classList.toggle('hidden', matched > 0 || rows.length === 0);
            }
        }

        filterList(woRows, woNoMatch);
        filterList(poRows, poNoMatch);
    }

    searchInput.addEventListener('input', function () {
        performLiveSearch(this.value);
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            performLiveSearch('');
            searchInput.focus();
        });
    }

    if (searchInput.value.trim() !== '') {
        performLiveSearch(searchInput.value);
    }
});
</script>
@endsection