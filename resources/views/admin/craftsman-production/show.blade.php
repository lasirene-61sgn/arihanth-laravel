@extends('admin.layouts.app')

@section('title', 'Craftsman Production - ' . $craftsman->name)

@section('content')
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
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white">{{ count($workOrders) }}</h2>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md border-l-4 border-blue-500">
            <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Purchase Orders</p>
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white">{{ count($purchaseOrders) }}</h2>
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

    <!-- Buyer Filter -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md mb-8">
        <h3 class="font-bold text-gray-800 dark:text-white flex items-center mb-4">
            <i class="bi bi-person-badge mr-2 text-magenta-800"></i> Filter by Buyer
        </h3>
        <form action="{{ request()->url() }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="flex-grow">
                <select name="buyer_code" class="w-full p-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All Buyers</option>
                    @foreach($buyers as $buyer)
                        <option value="{{ $buyer->bp_code }}" {{ $buyerCode == $buyer->bp_code ? 'selected' : '' }}>
                            {{ $buyer->business_name ?? $buyer->name }} ({{ $buyer->bp_code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-magenta-800 text-white rounded-lg font-bold hover:bg-magenta-900 transition-colors">
                Filter
            </button>
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
        <a href="{{ request()->fullUrlWithQuery(['tab' => $status]) }}" 
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
            <span class="bg-magenta-100 text-magenta-800 px-3 py-1 rounded-full text-xs font-bold">{{ count($workOrders) }} Orders</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
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
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <td class="px-6 py-4 font-bold text-gray-800 dark:text-white">{{ $wo->work_order_number }}</td>
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $wo->product_name }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400 text-sm">{{ $wo->customer_name }}</td>
                        <td class="px-6 py-4 text-center font-medium">{{ $wo->quantity }}</td>
                        <td class="px-6 py-4 text-sm">{{ $wo->craftsman_due_date ? $wo->craftsman_due_date->format('d M Y') : 'N/A' }}</td>
                        <td class="px-6 py-4">
                            @php
                                $badgeClass = $wo->status == 'completed' ? 'bg-green-100 text-green-700' : ($wo->isOverdue() ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700');
                            @endphp
                            <span class="{{ $badgeClass }} px-3 py-1 rounded-full text-[10px] font-black uppercase">
                                {{ $wo->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">No work orders found in this category.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Purchase Orders Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-750">
            <h3 class="font-bold text-gray-800 dark:text-white flex items-center">
                <i class="bi bi-cart3 mr-2 text-blue-500"></i> Purchase Orders
            </h3>
            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-bold">{{ count($purchaseOrders) }} Orders</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
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
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <td class="px-6 py-4 font-bold text-gray-800 dark:text-white">{{ $po->purchase_order_code }}</td>
                        <td class="px-6 py-4 text-center">{{ count($po->items ?? []) }}</td>
                        <td class="px-6 py-4 text-right font-mono font-bold">{{ number_format($po->total_calculated_weight, 3) }} g</td>
                        <td class="px-6 py-4 text-sm">{{ $po->due_date ? $po->due_date->format('d M Y') : 'N/A' }}</td>
                        <td class="px-6 py-4">
                            @php
                                $badgeClass = $po->status == 'approved' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700';
                            @endphp
                            <span class="{{ $badgeClass }} px-3 py-1 rounded-full text-[10px] font-black uppercase">
                                {{ $po->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">No purchase orders found in this category.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
