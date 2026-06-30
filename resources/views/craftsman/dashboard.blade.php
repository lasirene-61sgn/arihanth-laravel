@extends('craftsman.layouts.app')

@section('title', 'Craftsman Dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-emerald-200 pb-4">
        <h1 class="text-2xl font-bold text-emerald-900">Craftsman Dashboard</h1>
        <div class="text-sm text-emerald-600 font-medium">
            <i class="bi bi-calendar3 me-1"></i> {{ date('D, d M Y') }}
        </div>
    </div>

    {{-- Summary Overview --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-emerald-100 hover:shadow-md transition">
            <div class="flex items-center justify-center w-12 h-12 bg-emerald-100 text-emerald-700 rounded-lg mb-4">
                <i class="bi bi-clipboard-data text-xl"></i>
            </div>
            <div class="text-2xl font-bold text-emerald-900">{{ $woStats['total'] }}</div>
            <div class="text-sm font-medium text-emerald-600">Work Orders</div>
            <div class="flex flex-wrap gap-1 mt-3">
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                    {{ $woStats['allocated'] }} ALLOC
                </span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-50 text-blue-600 border border-blue-100">
                    {{ $woStats['in_process'] }} PROC
                </span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-green-50 text-green-600 border border-green-100">
                    {{ $woStats['completed'] }} DONE
                </span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-50 text-red-600 border border-red-100">
                    {{ $woStats['overdue'] }} LATE
                </span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-emerald-100 hover:shadow-md transition">
            <div class="flex items-center justify-center w-12 h-12 bg-emerald-100 text-emerald-700 rounded-lg mb-4">
                <i class="bi bi-file-earmark-bar-graph text-xl"></i>
            </div>
            <div class="text-2xl font-bold text-emerald-900">{{ $poStats['total'] }}</div>
            <div class="text-sm font-medium text-emerald-600">Purchase Orders</div>
            <div class="flex flex-wrap gap-1 mt-3">
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                    {{ $poStats['allocated'] }} ALLOC
                </span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-50 text-indigo-600 border border-indigo-100">
                    {{ $poStats['in_process'] }} PROC
                </span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                    {{ $poStats['completed'] }} DONE
                </span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-orange-50 text-orange-600 border border-orange-100">
                    {{ $poStats['overdue'] }} LATE
                </span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-emerald-100 hover:shadow-md transition">
            <div class="flex items-center justify-center w-12 h-12 bg-teal-100 text-teal-700 rounded-lg mb-4">
                <i class="bi bi-box-seam text-xl"></i>
            </div>
            <div class="text-2xl font-bold text-emerald-900">{{ $totalProducts }}</div>
            <div class="text-sm font-medium text-emerald-600">My Products</div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-amber-100 hover:shadow-md transition tw-cursor-pointer" data-bs-toggle="modal" data-bs-target="#progressAnalyticsModal">
            <div class="flex items-center justify-center w-12 h-12 bg-amber-100 text-amber-700 rounded-lg mb-4">
                <i class="bi bi-graph-up-arrow text-xl"></i>
            </div>
            <div class="text-sm font-bold text-emerald-900 mb-2">Progress Analytics</div>
            <div class="space-y-1">
                <div class="flex justify-between text-[10px] uppercase tracking-wider">
                    <span class="text-emerald-600 font-bold">Allocated:</span>
                    <span class="font-black text-emerald-900">{{ number_format($woStats['allocated_weight'] + $poStats['allocated_weight'], 2) }}g</span>
                </div>
                <div class="flex justify-between text-[10px] uppercase tracking-wider">
                    <span class="text-emerald-600 font-bold">In Process:</span>
                    <span class="font-black text-blue-600">{{ number_format($woStats['in_process_weight'] + $poStats['in_process_weight'], 2) }}g</span>
                </div>
                <div class="flex justify-between text-[10px] uppercase tracking-wider">
                    <span class="text-emerald-600 font-bold">Overdue:</span>
                    <span class="font-black text-red-600">{{ number_format($woStats['overdue_weight'] + $poStats['overdue_weight'], 2) }}g</span>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-emerald-100 hover:shadow-md transition">
            <div class="flex items-center justify-center w-12 h-12 bg-emerald-800 text-white rounded-lg mb-4">
                <i class="bi bi-journal-text text-xl"></i>
            </div>
            <div class="text-2xl font-bold text-emerald-900">{{ $totalDesigns }}</div>
            <div class="text-sm font-medium text-emerald-600">My Catalogue</div>
        </div>
    </div>

    {{-- Work Order Statistics --}}
    <!-- <div>
        <h2 class="text-xl font-bold text-emerald-900 mb-4 flex items-center">
            <i class="bi bi-clipboard-data me-2"></i> Work Order Statistics
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100">
                <div class="text-sm font-medium text-emerald-600 mb-1">Total</div>
                <div class="text-2xl font-bold text-emerald-900">{{ $woStats['total'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100">
                <div class="text-sm font-medium text-emerald-600 mb-1">Allocated</div>
                <div class="text-2xl font-bold text-emerald-700">{{ $woStats['allocated'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100">
                <div class="text-sm font-medium text-emerald-600 mb-1">In Process</div>
                <div class="text-2xl font-bold text-amber-600">{{ $woStats['in_process'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100">
                <div class="text-sm font-medium text-emerald-600 mb-1">Completed</div>
                <div class="text-2xl font-bold text-green-600">{{ $woStats['completed'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100">
                <div class="text-sm font-medium text-emerald-600 mb-1">For Approval</div>
                <div class="text-2xl font-bold text-teal-600">{{ $woStats['for_approval'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100">
                <div class="text-sm font-medium text-emerald-600 mb-1">Rejected</div>
                <div class="text-2xl font-bold text-red-600">{{ $woStats['rejected'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100">
                <div class="text-sm font-medium text-emerald-600 mb-1">Overdue</div>
                <div class="text-2xl font-bold text-rose-800">{{ $woStats['overdue'] }}</div>
            </div>
        </div>
    </div> -->

    {{-- Purchase Order Statistics --}}
    <!-- <div>
        <h2 class="text-xl font-bold text-emerald-900 mb-4 flex items-center">
            <i class="bi bi-file-earmark-bar-graph me-2"></i> Purchase Order Statistics
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100">
                <div class="text-sm font-medium text-emerald-600 mb-1">Total</div>
                <div class="text-2xl font-bold text-emerald-900">{{ $poStats['total'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100">
                <div class="text-sm font-medium text-emerald-600 mb-1">Allocated</div>
                <div class="text-2xl font-bold text-emerald-700">{{ $poStats['allocated'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100">
                <div class="text-sm font-medium text-emerald-600 mb-1">In Process</div>
                <div class="text-2xl font-bold text-amber-600">{{ $poStats['in_process'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100">
                <div class="text-sm font-medium text-emerald-600 mb-1">Completed</div>
                <div class="text-2xl font-bold text-green-600">{{ $poStats['completed'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100">
                <div class="text-sm font-medium text-emerald-600 mb-1">For Approval</div>
                <div class="text-2xl font-bold text-teal-600">{{ $poStats['for_approval'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100">
                <div class="text-sm font-medium text-emerald-600 mb-1">Rejected</div>
                <div class="text-2xl font-bold text-red-600">{{ $poStats['rejected'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100">
                <div class="text-sm font-medium text-emerald-600 mb-1">Overdue</div>
                <div class="text-2xl font-bold text-rose-800">{{ $poStats['overdue'] }}</div>
            </div>
        </div>
    </div> -->

    <!-- <div class="bg-white rounded-xl shadow-sm border border-emerald-100 overflow-hidden">
        <div class="bg-emerald-50 px-6 py-4 border-b border-emerald-100">
            <h4 class="text-lg font-bold text-emerald-900 mb-0">Welcome, {{ $craftsman->business_name }}</h4>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                <div class="space-y-3">
                    <div class="flex justify-between border-b border-emerald-50 py-2">
                        <span class="text-emerald-600 font-medium">Craftsman Code</span>
                        <span class="text-emerald-900 font-bold">{{ $craftsman->craftman_code }}</span>
                    </div>
                    <div class="flex justify-between border-b border-emerald-50 py-2">
                        <span class="text-emerald-600 font-medium">Email</span>
                        <span class="text-emerald-900">{{ $craftsman->email }}</span>
                    </div>
                    <div class="flex justify-between border-b border-emerald-50 py-2">
                        <span class="text-emerald-600 font-medium">Mobile</span>
                        <span class="text-emerald-900">{{ $craftsman->mobile }}</span>
                    </div>
                    <div class="flex justify-between border-b border-emerald-50 py-2">
                        <span class="text-emerald-600 font-medium">Business Type</span>
                        <span class="text-emerald-900">{{ $craftsman->business_type }}</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between border-b border-emerald-50 py-2">
                        <span class="text-emerald-600 font-medium">City</span>
                        <span class="text-emerald-900">{{ $craftsman->city }}</span>
                    </div>
                    <div class="flex justify-between border-b border-emerald-50 py-2">
                        <span class="text-emerald-600 font-medium">State</span>
                        <span class="text-emerald-900">{{ $craftsman->state }}</span>
                    </div>
                    <div class="flex justify-between border-b border-emerald-50 py-2">
                        <span class="text-emerald-600 font-medium">Address</span>
                        <span class="text-emerald-900 text-right">{{ $craftsman->address }}</span>
                    </div>
                    <div class="flex justify-between border-b border-emerald-50 py-2">
                        <span class="text-emerald-600 font-medium">Status</span>
                        @if($craftsman->status == 'active')
                            <span class="px-2 py-1 text-xs font-bold bg-emerald-100 text-emerald-700 rounded">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs font-bold bg-slate-100 text-slate-600 rounded">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <div class="bg-white rounded-xl shadow-sm border border-emerald-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-emerald-100 flex items-center justify-between">
            <h4 class="text-lg font-bold text-emerald-900">Allocated Work Orders</h4>
            <span class="bg-emerald-600 text-white text-xs font-bold px-3 py-1 rounded-full">{{ $workOrders->count() }}</span>
        </div>
        <div class="p-0 overflow-x-auto">
            @if($workOrders->count() > 0)
            <table class="w-full text-left border-collapse">
                <thead class="bg-emerald-50 text-emerald-800 uppercase text-xs font-bold">
                    <tr>
                        <th class="px-6 py-4">Work Order #</th>
                        <!-- <th class="px-6 py-4">Customer</th> -->
                        <th class="px-6 py-4">Product</th>
                        <th class="px-6 py-4 text-center">Qty</th>
                        <th class="px-6 py-4">Due Date</th>
                        <th class="px-6 py-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-emerald-50">
                    @foreach($workOrders as $order)
                    <tr class="hover:bg-emerald-50/50 transition">
                        <td class="px-6 py-4 font-bold text-emerald-900">{{ $order->work_order_number }}</td>
                        <!-- <td class="px-6 py-4 text-emerald-800">{{ $order->customer_name }}</td> -->
                        <td class="px-6 py-4 text-emerald-800">{{ $order->product_name }}</td>
                        <td class="px-6 py-4 text-center font-semibold text-emerald-900">{{ $order->quantity }}</td>
                        <td class="px-6 py-4">
                            <span class="text-emerald-700 whitespace-nowrap">
                                {{ $order->due_date ? $order->due_date->format('d M, Y') : 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('craftsman.work-order.show', $order) }}"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white transition">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-clipboard text-emerald-200 text-3xl"></i>
                </div>
                <h5 class="text-emerald-900 font-bold text-lg">No work orders assigned</h5>
                <p class="text-emerald-500">When orders are allocated to you, they will appear here.</p>
            </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-emerald-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-emerald-100">
            <h4 class="text-lg font-bold text-emerald-900 mb-0">Quick Action Links</h4>
        </div>
        <div class="p-6">
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('craftsman.work-order.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-emerald-900 text-white rounded-lg text-sm font-semibold hover:bg-emerald-800 transition shadow-sm">
                    <i class="bi bi-list-task me-2"></i> View All Work Orders
                </a>
                <a href="{{ route('craftsman.product.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-500 transition shadow-sm">
                    <i class="bi bi-box-seam me-2"></i> My Products
                </a>
                <!--<a href="{{ route('craftsman.design.index') }}"-->
                <!--    class="inline-flex items-center px-4 py-2 bg-teal-700 text-white rounded-lg text-sm font-semibold hover:bg-teal-600 transition shadow-sm">-->
                <!--    <i class="bi bi-brush me-2"></i> My Designs-->
                <!--</a>-->
                <a href="{{ route('craftsman.purchase-order.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-slate-600 text-white rounded-lg text-sm font-semibold hover:bg-slate-700 transition shadow-sm">
                    <i class="bi bi-file-earmark-text me-2"></i> Purchase Orders
                </a>
            </div>
        </div>
    </div>

    {{-- Progress Analytics Modal --}}
    <div class="modal fade" id="progressAnalyticsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content rounded-2xl border-0 shadow-2xl overflow-hidden">
                <div class="modal-header border-0 bg-emerald-50 px-8 py-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <i class="bi bi-graph-up-arrow text-xl"></i>
                        </div>
                        <div>
                            <h5 class="modal-title font-bold text-emerald-900">My Progress Analytics (WA & PA)</h5>
                            <p class="text-emerald-600 text-xs mb-0">Personal production summary</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 align-middle">
                            <thead>
                                <tr class="bg-emerald-50 text-[11px] font-bold uppercase tracking-wider text-emerald-700">
                                    <th class="px-6 py-4 align-middle">Category</th>
                                    <th class="px-6 py-4 text-center bg-blue-50">In Process Weight</th>
                                    <th class="px-6 py-4 text-center bg-red-50 text-red-700">Overdue Weight</th>
                                    <th class="px-6 py-4 text-center bg-slate-100 text-slate-800">Total Weight</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($craftsmanStats as $code => $stat)
                                {{-- Work Orders Row --}}
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-black text-blue-700 text-lg uppercase">Work Orders (WA)</div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-blue-600 font-bold text-xl">
                                        {{ number_format($stat['wa']['process']['weight'], 2) }} g
                                    </td>
                                    <td class="px-6 py-4 text-center text-red-600 font-bold text-xl">
                                        {{ number_format($stat['wa']['overdue']['weight'], 2) }} g
                                    </td>
                                    <td class="px-6 py-4 text-center text-slate-900 font-black text-xl bg-slate-50">
                                        {{ number_format($stat['wa']['process']['weight'] + $stat['wa']['overdue']['weight'], 2) }} g
                                    </td>
                                </tr>
                                {{-- Purchase Orders Row --}}
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-black text-cyan-700 text-lg uppercase">Purchase Orders (PA)</div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-cyan-600 font-bold text-xl">
                                        {{ number_format($stat['po']['process']['weight'], 2) }} g
                                    </td>
                                    <td class="px-6 py-4 text-center text-red-600 font-bold text-xl">
                                        {{ number_format($stat['po']['overdue']['weight'], 2) }} g
                                    </td>
                                    <td class="px-6 py-4 text-center text-slate-900 font-black text-xl bg-slate-50">
                                        {{ number_format($stat['po']['process']['weight'] + $stat['po']['overdue']['weight'], 2) }} g
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection