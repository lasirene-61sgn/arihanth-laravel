@extends('buyer.layouts.app')

@section('title', 'Live Stock Orders')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Live Stock Orders</h1>
        <p class="text-slate-500 text-sm font-medium">Manage and track your live stock orders</p>
    </div>
    <a href="{{ route('buyer.stock-order.create') }}" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all flex items-center gap-2">
        <i class="bi bi-camera text-xl"></i>
        <span>Scan & Order</span>
    </a>
</div>

<!-- Modern Navigation Tabs -->
<div class="border-b border-slate-200 mb-6">
    <ul class="flex flex-wrap text-sm font-medium text-center text-slate-500" id="stockOrderTabs">
        @php
        $tabs = [
            'new-orders' => ['label' => 'New Orders', 'count' => $counts['new-orders'] ?? 0, 'icon' => 'bi-plus-circle'],
            'allocated-orders' => ['label' => 'Allocated', 'count' => $counts['allocated-orders'] ?? 0, 'icon' => 'bi-people'],
            'in-process-orders' => ['label' => 'In Process', 'count' => $counts['in-process-orders'] ?? 0, 'icon' => 'bi-gear'],
            'for-approval-orders' => ['label' => 'For Approval', 'count' => $counts['for-approval-orders'] ?? 0, 'icon' => 'bi-hourglass-split'],
            'completed-orders' => ['label' => 'Completed', 'count' => $counts['completed-orders'] ?? 0, 'icon' => 'bi-check-all'],
            'all-orders' => ['label' => 'All Orders', 'count' => $counts['all-orders'] ?? 0, 'icon' => 'bi-list-ul'],
        ];
        $activeTab = $activeTab ?? 'new-orders';
        @endphp

        @foreach($tabs as $id => $tab)
        <li class="mr-2">
            <a href="{{ route('buyer.stock-order.index', array_merge(request()->query(), ['tab' => $id])) }}"
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
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Order ID</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Items</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                    <!-- <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Craftsman</th> -->
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($orders as $order)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-mono font-bold text-indigo-600 text-sm">{{ $order->order_number }}</div>
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
                    <!-- <td class="px-6 py-4">
                        @if($order->craftsman)
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-[10px] font-bold text-indigo-600 uppercase">
                                {{ substr($order->craftsman->name, 0, 2) }}
                            </div>
                            <span class="text-sm font-bold text-slate-700">{{ $order->craftsman->name }}</span>
                        </div>
                        @else
                        <span class="text-slate-400 text-xs italic">Not Allocated</span>
                        @endif
                    </td> -->
                    <td class="px-6 py-4">
                        <div class="text-xs font-bold text-slate-600">{{ $order->created_at->format('d M, Y') }}</div>
                        <div class="text-[10px] text-slate-400">{{ $order->created_at->format('h:i A') }}</div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('buyer.stock-order.show', $order->id) }}" class="text-indigo-600 hover:text-indigo-800 font-bold text-xs uppercase tracking-wider">
                            View Details
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="bi bi-box2 text-4xl text-slate-200 mb-3"></i>
                            <p class="text-slate-400 text-sm font-medium">No stock orders found</p>
                            <a href="{{ route('buyer.stock-order.create') }}" class="text-indigo-600 text-xs font-bold uppercase mt-2 hover:underline">Create First Order</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection
