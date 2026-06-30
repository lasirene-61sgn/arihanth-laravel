@extends('buyer.layouts.app')

@section('title', 'Buyer Dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
            <p class="text-sm text-slate-500">Welcome back to your ERP panel.</p>
        </div>
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm font-medium">
                <li class="text-slate-500">Buyer</li>
                <li class="flex items-center text-slate-400">
                    <i class="bi bi-chevron-right mx-2 text-[10px]"></i>
                    <span style="color:#7c3aed;">Dashboard</span>
                </li>
            </ol>
        </nav>
    </div>

    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden" style="border-color:#c4b5fd;">
        <div class="p-6 md:p-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Welcome, {{ $buyer->business_name ?? $buyer->name }}</h2>
                    <p class="text-slate-500 mt-1 flex items-center">
                        <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-xs font-bold mr-2">BP CODE</span>
                        {{ $buyer->bp_code }}
                    </p>
                </div>

                <div class="flex-shrink-0">
                    @if($canManageKeyUsers)
                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            <div class="flex items-center text-green-600 bg-green-50 px-4 py-2 rounded-lg text-sm font-medium border border-green-100">
                                <i class="bi bi-check-circle-fill mr-2"></i>
                                Key User Management Active
                            </div>
                            <a href="{{ route('buyer.key-user-management.index') }}" 
                               class="inline-flex items-center justify-center px-5 py-2.5 text-white text-sm font-bold rounded-xl transition-all shadow-sm" style="background: linear-gradient(135deg, #6d28d9, #7c3aed);">
                                <i class="bi bi-person-badge mr-2"></i> Manage Key Users ({{ $keyUsersCount }})
                            </a>
                        </div>
                    @else
                        <div class="flex items-center text-slate-500 bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium border border-slate-100">
                            <i class="bi bi-info-circle-fill mr-2 text-slate-400"></i>
                            Key User Management Restricted
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-all group text-center" style="border: 1px solid #c4b5fd;">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-4 group-hover:scale-110 transition-transform" style="background:#ede9fe; color:#7c3aed;">
                <i class="bi bi-person-badge text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800">{{ number_format($keyUsersCount) }} Key User</h3>
            <p class="text-slate-500 text-sm mb-5">Manage your Key Users</p>
           
            <a href="{{ route('buyer.key-user-management.index') }}" 
               class="inline-block w-full py-2 px-4 rounded-xl text-sm font-bold transition-all" style="border: 1px solid #7c3aed; color:#7c3aed;" onmouseover="this.style.background='#7c3aed';this.style.color='white';" onmouseout="this.style.background='transparent';this.style.color='#7c3aed';">
               View Key User
            </a>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-all group text-center" style="border: 1px solid #c4b5fd;">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-4 group-hover:scale-110 transition-transform" style="background:#ede9fe; color:#7c3aed;">
                <i class="bi bi-people text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800">{{ number_format($usersCount) }} User</h3>
            <p class="text-slate-500 text-sm mb-5">Manage your Users</p>
            <a href="{{ route('buyer.user-management.index') }}" 
               class="inline-block w-full py-2 px-4 rounded-xl text-sm font-bold transition-all" style="border: 1px solid #7c3aed; color:#7c3aed;" onmouseover="this.style.background='#7c3aed';this.style.color='white';" onmouseout="this.style.background='transparent';this.style.color='#7c3aed';">
               View User
            </a>
        </div>
        
        <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-all group text-center" style="border: 1px solid #c4b5fd;">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-4 group-hover:scale-110 transition-transform" style="background:#ede9fe; color:#7c3aed;">
                <i class="bi bi-box-seam text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800">{{ number_format($productsCount) }} Products</h3>
            <p class="text-slate-500 text-sm mb-5">Manage your inventory items</p>
            <a href="{{ route('buyer.product.index') }}" 
               class="inline-block w-full py-2 px-4 rounded-xl text-sm font-bold transition-all" style="border: 1px solid #7c3aed; color:#7c3aed;" onmouseover="this.style.background='#7c3aed';this.style.color='white';" onmouseout="this.style.background='transparent';this.style.color='#7c3aed';">
               View Products
            </a>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-all group text-center" style="border: 1px solid #c4b5fd;">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-4 group-hover:scale-110 transition-transform" style="background:#f5f3ff; color:#6d28d9;">
                <i class="bi bi-file-earmark-text text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800">{{ number_format($workOrdersCount) }} Work Orders</h3>
            
            <div class="flex items-center justify-center gap-2 mt-2 mb-4">
                <a href="{{ route('buyer.work-order.index', ['tab' => 'in-process-orders']) }}" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-100 hover:bg-blue-100 transition-colors" title="In Process">
                    {{ $woInProcessCount }} PROC
                </a>
                <a href="{{ route('buyer.work-order.index', ['tab' => 'completed-orders']) }}" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-50 text-green-600 border border-green-100 hover:bg-green-100 transition-colors" title="Completed">
                    {{ $woCompletedCount }} DONE
                </a>
                <a href="{{ route('buyer.work-order.index', ['tab' => 'overdue-orders']) }}" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 transition-colors" title="Overdue">
                    {{ $woOverdueCount }} LATE
                </a>
            </div>

            <a href="{{ route('buyer.work-order.index') }}" 
               class="inline-block w-full py-2 px-4 rounded-xl text-sm font-bold transition-all" style="border: 1px solid #6d28d9; color:#6d28d9;" onmouseover="this.style.background='#6d28d9';this.style.color='white';" onmouseout="this.style.background='transparent';this.style.color='#6d28d9';">
               View Orders
            </a>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:border-amber-300 transition-colors group text-center flex flex-col justify-between">
            <div>
                <div class="inline-flex items-center justify-center w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl mb-4 group-hover:scale-110 transition-transform">
                    <i class="bi bi-palette text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">{{ number_format($designsCount) }} Designs</h3>
                <p class="text-slate-500 text-xs mb-5">View your design submissions and requests</p>
            </div>
            <a href="{{ route('buyer.design.index') }}" 
               class="inline-block w-full py-2 px-4 rounded-xl border border-amber-500 text-amber-600 text-sm font-bold hover:bg-amber-500 hover:text-white transition-all">
               View Designs
            </a>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:border-cyan-300 transition-colors group text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-cyan-50 text-cyan-600 rounded-2xl mb-4 group-hover:scale-110 transition-transform">
                <i class="bi bi-book text-2xl"></i>
            </div>
            
            <h3 class="text-lg font-bold text-slate-800">{{ number_format($cataloguesCount) }} Catalogues</h3>
            <p class="text-slate-500 text-sm mb-5">Browse your item collections</p>
            
            <a href="{{ route('buyer.catalogue.index') }}" 
               class="inline-block w-full py-2 px-4 rounded-xl border border-cyan-600 text-cyan-600 text-sm font-bold hover:bg-cyan-600 hover:text-white transition-all">
               View Catalogues
            </a>
        </div>

        {{-- Progress Button/Card --}}
        <!-- <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-all group text-center" style="border: 1px solid #10b981;">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-4 group-hover:scale-110 transition-transform" style="background:#ecfdf5; color:#10b981;">
                <i class="bi bi-graph-up-arrow text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Progress Analytics</h3>
            
            <div class="space-y-2 mt-4 mb-5">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500 font-medium">New Orders:</span>
                    <span class="text-slate-900 font-bold">{{ number_format($woNewWeight, 2) }} g</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500 font-medium">In Process:</span>
                    <span class="text-slate-900 font-bold text-blue-600">{{ number_format($woInProcessWeight, 2) }} g</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500 font-medium">Overdue:</span>
                    <span class="text-slate-900 font-bold text-red-600">{{ number_format($woOverdueWeight, 2) }} g</span>
                </div>
            </div>

            <button class="inline-block w-full py-2 px-4 rounded-xl text-sm font-bold transition-all" style="border: 1px solid #10b981; color:#10b981;" onmouseover="this.style.background='#10b981';this.style.color='white';" onmouseout="this.style.background='transparent';this.style.color='#10b981';" data-bs-toggle="modal" data-bs-target="#progressAnalyticsModal">
               Update Progress
            </button>
        </div> -->

        {{-- Progress Analytics Modal --}}
        <div class="modal fade" id="progressAnalyticsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content rounded-3xl border-0 shadow-2xl overflow-hidden">
                    <div class="modal-header border-0 bg-slate-50 px-8 py-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                <i class="bi bi-graph-up-arrow text-xl"></i>
                            </div>
                            <div>
                                <h5 class="modal-title font-bold text-slate-800">Progress Analytics (WA & PA)</h5>
                                <p class="text-slate-500 text-xs mb-0">Detailed craftsman production status</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0 align-middle">
                                <thead>
                                    <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                                        <th rowspan="2" class="px-6 py-4 align-middle">Craftsman</th>
                                        <th rowspan="2" class="px-6 py-4 align-middle text-center">BP Code</th>
                                        <th colspan="2" class="px-6 py-3 text-center bg-blue-600 text-white border-0">Work Orders (WA)</th>
                                        <th colspan="2" class="px-6 py-3 text-center bg-cyan-500 text-white border-0">Purchase Orders (PA)</th>
                                    </tr>
                                    <tr class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                        <th class="px-6 py-3 text-center">Process (C/W)</th>
                                        <th class="px-6 py-3 text-center text-red-600">Overdue (C/W)</th>
                                        <th class="px-6 py-3 text-center">Process (C/W)</th>
                                        <th class="px-6 py-3 text-center text-red-600">Overdue (C/W)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($craftsmanStats as $code => $stat)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-800">{{ $stat['name'] }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 py-1 rounded bg-slate-100 text-slate-700 text-[10px] font-black border border-slate-200">
                                                {{ $code }}
                                            </span>
                                        </td>
                                        {{-- WA Stats --}}
                                        <td class="px-6 py-4 text-center text-blue-600 font-bold text-[13px]">
                                            {{ $stat['wa']['process']['count'] }} | {{ number_format($stat['wa']['process']['weight'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-center text-red-600 font-bold text-[13px]">
                                            {{ $stat['wa']['overdue']['count'] }} | {{ number_format($stat['wa']['overdue']['weight'], 2) }}
                                        </td>
                                        {{-- PA Stats --}}
                                        <td class="px-6 py-4 text-center text-cyan-600 font-bold text-[13px]">
                                            {{ $stat['po']['process']['count'] }} | {{ number_format($stat['po']['process']['weight'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-center text-red-600 font-bold text-[13px]">
                                            {{ $stat['po']['overdue']['count'] }} | {{ number_format($stat['po']['overdue']['weight'], 2) }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center gap-2">
                                                <i class="bi bi-inbox text-4xl text-slate-200"></i>
                                                <p class="text-slate-400 text-sm font-medium">No craftsman data available</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
    </div>
</div>
@endsection