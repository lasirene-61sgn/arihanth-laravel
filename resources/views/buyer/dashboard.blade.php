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

    {{-- Welcome Card --}}
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

    {{-- Work Order Progress Analytics Section --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="mb-6">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                Work Order Progress Analytics
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Click any card below to view matching orders and print.</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            {{-- New Orders --}}
            <div onclick="openStatusDetailsModal('new', 'New Orders')" 
                 class="group relative bg-slate-50 hover:bg-indigo-50/60 p-4 rounded-xl border border-slate-200 hover:border-indigo-300 transition-all cursor-pointer shadow-xs hover:shadow-md">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-700">New Orders</span>
                    <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs">
                        <i class="bi bi-file-earmark-plus"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 group-hover:text-indigo-600">{{ $woNewCount }}</div>
                <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                    <span>Total Wt:</span>
                    <span class="font-bold text-slate-700">{{ number_format($woNewWeight, 2) }} g</span>
                </div>
            </div>

            {{-- Allocated --}}
            <div onclick="openStatusDetailsModal('allocated', 'Allocated Orders')" 
                 class="group relative bg-slate-50 hover:bg-cyan-50/60 p-4 rounded-xl border border-slate-200 hover:border-cyan-300 transition-all cursor-pointer shadow-xs hover:shadow-md">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-600 group-hover:text-cyan-700">Allocated</span>
                    <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs">
                        <i class="bi bi-check-circle"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 group-hover:text-cyan-600">{{ $woAllocatedCount }}</div>
                <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                    <span>Total Wt:</span>
                    <span class="font-bold text-slate-700">{{ number_format($woAllocatedWeight, 2) }} g</span>
                </div>
            </div>

            {{-- In Process --}}
            <div onclick="openStatusDetailsModal('in_process', 'In Process Orders')" 
                 class="group relative bg-slate-50 hover:bg-blue-50/60 p-4 rounded-xl border border-slate-200 hover:border-blue-300 transition-all cursor-pointer shadow-xs hover:shadow-md">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-600 group-hover:text-blue-700">In Process</span>
                    <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs">
                        <i class="bi bi-gear-wide-connected"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 group-hover:text-blue-600">{{ $woInProcessCount }}</div>
                <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                    <span>Total Wt:</span>
                    <span class="font-bold text-slate-700">{{ number_format($woInProcessWeight, 2) }} g</span>
                </div>
            </div>

            {{-- Completed --}}
            <div onclick="openStatusDetailsModal('completed', 'Completed Orders')" 
                 class="group relative bg-slate-50 hover:bg-emerald-50/60 p-4 rounded-xl border border-slate-200 hover:border-emerald-300 transition-all cursor-pointer shadow-xs hover:shadow-md">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-600 group-hover:text-emerald-700">Completed</span>
                    <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs">
                        <i class="bi bi-check2-circle"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 group-hover:text-emerald-600">{{ $woCompletedCount }}</div>
                <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                    <span>Total Wt:</span>
                    <span class="font-bold text-slate-700">{{ number_format($woCompletedWeight, 2) }} g</span>
                </div>
            </div>

            {{-- Overdue --}}
            <div onclick="openStatusDetailsModal('overdue', 'Overdue Orders')" 
                 class="group relative bg-red-50/60 hover:bg-red-100/70 p-4 rounded-xl border border-red-200 hover:border-red-300 transition-all cursor-pointer shadow-xs hover:shadow-md col-span-2 sm:col-span-1">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-red-700">Overdue</span>
                    <span class="w-7 h-7 rounded-lg bg-red-200/80 text-red-700 flex items-center justify-center text-xs">
                        <i class="bi bi-exclamation-octagon-fill"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-red-600">{{ $woOverdueCount }}</div>
                <div class="text-[11px] text-red-600/80 mt-1 flex items-center justify-between font-medium">
                    <span>Total Wt:</span>
                    <span class="font-bold text-red-700">{{ number_format($woOverdueWeight, 2) }} g</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Modules Cards --}}
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
            <p class="text-slate-500 text-sm mb-5">Manage your Products</p>
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
            <p class="text-slate-500 text-sm mb-5">Manage your Orders</p>
            
            <!-- <div class="flex items-center justify-center gap-2 mt-2 mb-4">
                <a href="{{ route('buyer.work-order.index', ['tab' => 'in-process-orders']) }}" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-100 hover:bg-blue-100 transition-colors">
                    {{ $woInProcessCount }} PROC
                </a>
                <a href="{{ route('buyer.work-order.index', ['tab' => 'completed-orders']) }}" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-50 text-green-600 border border-green-100 hover:bg-green-100 transition-colors">
                    {{ $woCompletedCount }} DONE
                </a>
                <a href="{{ route('buyer.work-order.index', ['tab' => 'overdue-orders']) }}" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 transition-colors">
                    {{ $woOverdueCount }} LATE
                </a>
            </div> -->

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
    </div>
</div>

{{-- Status Detail Modal --}}
<div class="modal fade" id="statusWorkOrdersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-3xl border-0 shadow-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-100 bg-slate-50 px-6 py-4 flex items-center justify-between">
                <div>
                    <h5 class="modal-title font-bold text-slate-800 text-lg flex items-center gap-2">
                        <span id="modalCategoryBadge" class="px-2.5 py-0.5 rounded-lg text-xs font-extrabold uppercase tracking-wide bg-indigo-100 text-indigo-700">
                            Status
                        </span>
                        <span id="modalCategoryTitle">Work Orders</span>
                    </h5>
                    <p class="text-xs text-slate-500 mt-0.5" id="modalCategorySubtitle">Viewing filtered orders</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-6 space-y-4">
                {{-- Column Choosers & Print Actions --}}
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wide flex items-center gap-1">
                            <i class="bi bi-layout-three-columns"></i> Columns:
                        </span>
                        <label class="inline-flex items-center gap-1 text-xs text-slate-600 cursor-pointer">
                            <input type="checkbox" class="col-toggle rounded text-indigo-600 focus:ring-indigo-500" data-col="wo_number" checked> WO#
                        </label>
                        <label class="inline-flex items-center gap-1 text-xs text-slate-600 cursor-pointer">
                            <input type="checkbox" class="col-toggle rounded text-indigo-600 focus:ring-indigo-500" data-col="due_date" checked> Due Date
                        </label>
                        
                        {{-- Overdue Days Checkbox (Appears strictly on Overdue Tab) --}}
                        <label class="inline-flex items-center gap-1 text-xs text-slate-600 cursor-pointer" id="toggleColOverdueDaysWrapper">
                            <input type="checkbox" class="col-toggle rounded text-indigo-600 focus:ring-indigo-500" data-col="overdue_days" checked> Overdue Days
                        </label>

                        <label class="inline-flex items-center gap-1 text-xs text-slate-600 cursor-pointer">
                            <input type="checkbox" class="col-toggle rounded text-indigo-600 focus:ring-indigo-500" data-col="qty" checked> Qty
                        </label>
                        <label class="inline-flex items-center gap-1 text-xs text-slate-600 cursor-pointer">
                            <input type="checkbox" class="col-toggle rounded text-indigo-600 focus:ring-indigo-500" data-col="weight_from" checked> Wt From
                        </label>
                        <label class="inline-flex items-center gap-1 text-xs text-slate-600 cursor-pointer">
                            <input type="checkbox" class="col-toggle rounded text-indigo-600 focus:ring-indigo-500" data-col="weight_to" checked> Wt To
                        </label>
                        <label class="inline-flex items-center gap-1 text-xs text-slate-600 cursor-pointer">
                            <input type="checkbox" class="col-toggle rounded text-indigo-600 focus:ring-indigo-500" data-col="status" checked> Status
                        </label>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="printFilteredWorkOrders()" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all">
                            <i class="bi bi-printer mr-1.5"></i> Print Selected Orders
                        </button>
                    </div>
                </div>

                {{-- Table view --}}
                <div class="overflow-x-auto border border-slate-200 rounded-2xl">
                    <table class="w-full text-left border-collapse text-xs" id="statusWorkOrdersTable">
                        <thead class="bg-slate-100 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="p-3.5 text-center w-10">
                                    <input type="checkbox" id="modalSelectAll" class="rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer" checked>
                                </th>
                                <th class="p-3.5 col-cell col-wo_number">Work Order #</th>
                                <th class="p-3.5 col-cell col-due_date">Due Date</th>
                                <th class="p-3.5 col-cell col-overdue_days text-center" id="thOverdueDays">Overdue (Days)</th>
                                <th class="p-3.5 col-cell col-qty text-center">Qty</th>
                                <th class="p-3.5 col-cell col-weight_from text-right">Weight From (g)</th>
                                <th class="p-3.5 col-cell col-weight_to text-right">Weight To (g)</th>
                                <th class="p-3.5 col-cell col-status text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="statusWorkOrdersBody" class="divide-y divide-slate-100 bg-white">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer border-t border-slate-100 bg-slate-50 px-6 py-3 flex items-center justify-between">
                <span class="text-xs text-slate-500 font-medium" id="modalSelectedCountLabel">0 rows selected</span>
                <button type="button" class="px-5 py-2 text-xs font-bold rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 transition-colors" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const allBuyerWorkOrders = @json($modalWorkOrders ?? []);
    let filteredWorkOrders = [];
    let currentCategoryKey = '';
    let currentCategoryTitle = '';

    function openStatusDetailsModal(statusKey, title) {
        currentCategoryKey = statusKey;
        currentCategoryTitle = title;

        document.getElementById('modalCategoryTitle').textContent = title;
        document.getElementById('modalCategoryBadge').textContent = statusKey.toUpperCase().replace('_', ' ');

        filteredWorkOrders = allBuyerWorkOrders.filter(wo => wo.category === statusKey);

        const isOverdueTab = (statusKey === 'overdue');
        const overdueDaysToggleWrapper = document.getElementById('toggleColOverdueDaysWrapper');
        const thOverdueDays = document.getElementById('thOverdueDays');

        if (isOverdueTab) {
            overdueDaysToggleWrapper.style.display = 'inline-flex';
            thOverdueDays.style.display = '';
        } else {
            overdueDaysToggleWrapper.style.display = 'none';
            thOverdueDays.style.display = 'none';
        }

        renderWorkOrderTable(filteredWorkOrders, isOverdueTab);

        const modal = new bootstrap.Modal(document.getElementById('statusWorkOrdersModal'));
        modal.show();
    }

    function renderWorkOrderTable(data, isOverdueTab) {
        const tbody = document.getElementById('statusWorkOrdersBody');
        const totalColumns = isOverdueTab ? 8 : 7;

        if (!data.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="${totalColumns}" class="p-8 text-center text-slate-400">
                        <i class="bi bi-inbox text-3xl block mb-2 opacity-40"></i>
                        No work orders found under this status.
                    </td>
                </tr>
            `;
            updateSelectionCount();
            return;
        }

        let html = '';
        data.forEach(item => {
            const overdueColHtml = isOverdueTab 
                ? `<td class="p-3.5 text-center col-cell col-overdue_days">
                     <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-red-100 text-red-700 animate-pulse">
                         ${item.days_overdue} Days
                     </span>
                   </td>` 
                : ``;

            html += `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-3.5 text-center">
                        <input type="checkbox" class="wo-row-select rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer" value="${item.id}" checked>
                    </td>
                    <td class="p-3.5 font-bold font-mono text-slate-800 col-cell col-wo_number">${item.wo_number}</td>
                    <td class="p-3.5 text-slate-600 col-cell col-due_date">${item.due_date}</td>
                    ${overdueColHtml}
                    <td class="p-3.5 text-center font-bold text-slate-700 col-cell col-qty">${item.qty}</td>
                    <td class="p-3.5 text-right font-medium text-slate-600 col-cell col-weight_from">${item.weight_from}</td>
                    <td class="p-3.5 text-right font-bold text-slate-900 col-cell col-weight_to">${item.weight_to}</td>
                    <td class="p-3.5 text-center col-cell col-status">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            ${item.status_label}
                        </span>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        document.getElementById('modalSelectAll').checked = true;
        applyColumnVisibility();
        updateSelectionCount();
        attachSelectionEvents();
    }

    function attachSelectionEvents() {
        const selectAll = document.getElementById('modalSelectAll');
        const checkboxes = document.querySelectorAll('.wo-row-select');

        selectAll.onchange = function() {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateSelectionCount();
        };

        checkboxes.forEach(cb => {
            cb.onchange = function() {
                selectAll.checked = Array.from(checkboxes).every(c => c.checked);
                updateSelectionCount();
            };
        });
    }

    function updateSelectionCount() {
        const total = document.querySelectorAll('.wo-row-select').length;
        const selected = document.querySelectorAll('.wo-row-select:checked').length;
        document.getElementById('modalSelectedCountLabel').textContent = `${selected} of ${total} orders selected for print`;
    }

    function applyColumnVisibility() {
        const isOverdueTab = (currentCategoryKey === 'overdue');

        document.querySelectorAll('.col-toggle').forEach(chk => {
            const colName = chk.getAttribute('data-col');

            if (colName === 'overdue_days' && !isOverdueTab) {
                document.querySelectorAll('.col-overdue_days').forEach(el => el.style.display = 'none');
                return;
            }

            const isVisible = chk.checked;
            document.querySelectorAll(`.col-${colName}`).forEach(el => {
                el.style.display = isVisible ? '' : 'none';
            });
        });
    }

    document.querySelectorAll('.col-toggle').forEach(chk => {
        chk.addEventListener('change', applyColumnVisibility);
    });

    function printFilteredWorkOrders() {
        const checkedBoxes = Array.from(document.querySelectorAll('.wo-row-select:checked'));
        if (!checkedBoxes.length) {
            alert('Please select at least one work order to print.');
            return;
        }

        const selectedIds = new Set(checkedBoxes.map(c => parseInt(c.value)));
        const printableOrders = filteredWorkOrders.filter(o => selectedIds.has(o.id));
        const isOverdueTab = (currentCategoryKey === 'overdue');

        const activeColumns = [];
        document.querySelectorAll('.col-toggle:checked').forEach(c => {
            const key = c.getAttribute('data-col');
            if (key === 'overdue_days' && !isOverdueTab) {
                return;
            }
            activeColumns.push({
                key: key,
                label: c.parentElement.textContent.trim()
            });
        });

        let tableHeaderHtml = activeColumns.map(col => `<th style="border:1px solid #cbd5e1; padding:8px 10px; background:#f1f5f9; font-size:11px; text-transform:uppercase; text-align:left;">${col.label}</th>`).join('');

        let tableRowsHtml = printableOrders.map(item => {
            let rowCols = activeColumns.map(col => {
                let val = item[col.key] ?? '';
                if (col.key === 'status') val = item.status_label;
                if (col.key === 'overdue_days') val = `${item.days_overdue} Days Late`;
                
                let align = (col.key === 'weight_from' || col.key === 'weight_to') ? 'right' : (col.key === 'qty' || col.key === 'overdue_days' || col.key === 'status') ? 'center' : 'left';
                return `<td style="border:1px solid #cbd5e1; padding:6px 10px; font-size:11px; text-align:${align};">${val}</td>`;
            }).join('');
            return `<tr>${rowCols}</tr>`;
        }).join('');

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Work Order Report - ${currentCategoryTitle}</title>
                    <style>
                        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; padding: 25px; color: #0f172a; }
                        h2 { margin-bottom: 2px; }
                        p { font-size: 12px; color: #475569; margin-top: 0; }
                        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                        @media print {
                            body { padding: 0; }
                        }
                    </style>
                </head>
                <body>
                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #4f46e5; padding-bottom:8px;">
                        <div>
                            <h2>${currentCategoryTitle} Report</h2>
                            <p>Buyer: {{ $buyer->business_name ?? $buyer->name }} (BP: {{ $buyer->bp_code }})</p>
                        </div>
                        <div style="text-align:right; font-size:11px; color:#475569;">
                            <div>Generated: ${new Date().toLocaleString()}</div>
                            <div>Total Records: ${printableOrders.length}</div>
                        </div>
                    </div>
                    <table>
                        <thead>
                            <tr>${tableHeaderHtml}</tr>
                        </thead>
                        <tbody>
                            ${tableRowsHtml}
                        </tbody>
                    </table>
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 300);
    }
</script>
@endsection