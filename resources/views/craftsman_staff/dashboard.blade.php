@extends('craftsman_staff.layouts.app')

@section('title', 'Craftsman Staff Dashboard')

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
    @media print {
        body * {
            visibility: hidden !important;
        }
        #modalPrintContent, #modalPrintContent * {
            visibility: visible !important;
        }
        #modalPrintContent {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            background: white !important;
            padding: 20px !important;
        }
        .no-print {
            display: none !important;
        }
    }
</style>

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-indigo-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-indigo-900">Craftsman Staff Dashboard</h1>
            <p class="text-xs text-indigo-600 font-medium">Craftsman: {{ $craftsman->business_name ?: $craftsman->name }} ({{ $craftsman->craftman_code }}) | Staff: {{ $staff->name }}</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" class="px-3 py-1.5 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg text-xs font-bold hover:bg-amber-100 transition flex items-center" data-bs-toggle="modal" data-bs-target="#progressAnalyticsModal">
                <i class="bi bi-graph-up-arrow mr-1.5 text-amber-600"></i> Analytics Summary
            </button>
            <div class="text-sm text-indigo-700 font-semibold bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100">
                <i class="bi bi-calendar3 mr-1"></i> {{ date('D, d M Y') }}
            </div>
        </div>
    </div>

    {{-- TOP 3 MAIN GRID BOXES --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- 1. Work Orders Box (Allocated Only) -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-indigo-100 border-l-4 border-l-indigo-600 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-indigo-100 text-indigo-700 rounded-xl flex items-center justify-center font-bold">
                    <i class="bi bi-clipboard-check text-2xl"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full">Allocated Only</span>
            </div>
            <div class="text-4xl font-black text-indigo-900">{{ $woStats['allocated'] }}</div>
            <div class="text-sm font-bold text-indigo-700 mt-1">Work Orders (Allocated)</div>
            <div class="text-xs text-indigo-600 mt-2">Total Weight: <strong>{{ number_format($woStats['allocated_weight'], 2) }} g</strong></div>
        </div>

        <!-- 2. Purchase Orders Box (Allocated Only) -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-indigo-100 border-l-4 border-l-blue-600 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-blue-100 text-blue-700 rounded-xl flex items-center justify-center font-bold">
                    <i class="bi bi-cart-check text-2xl"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full">Allocated Only</span>
            </div>
            <div class="text-4xl font-black text-blue-900">{{ $poStats['allocated'] }}</div>
            <div class="text-sm font-bold text-blue-700 mt-1">Purchase Orders (Allocated)</div>
            <div class="text-xs text-blue-600 mt-2">Total Weight: <strong>{{ number_format($poStats['allocated_weight'], 2) }} g</strong></div>
        </div>

        <!-- 3. ORDERS GRID BOX (Triggers Status Explorer Pop-up) -->
        <div onclick="openOrdersStatusModal()" class="bg-white p-6 rounded-2xl shadow-sm border-2 border-indigo-500 cursor-pointer transition hover:shadow-xl hover:bg-indigo-50/40 group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-indigo-800 text-white rounded-xl flex items-center justify-center font-bold group-hover:scale-105 transition">
                    <i class="bi bi-box-seam-fill text-2xl"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-white bg-indigo-800 px-3 py-1 rounded-full shadow-sm flex items-center">
                    <i class="bi bi-window-stack mr-1"></i> Open Pop-up
                </span>
            </div>
            <div class="text-4xl font-black text-indigo-950">{{ $woStats['total'] + $poStats['total'] }}</div>
            <div class="text-sm font-bold text-indigo-900 mt-1">Orders (All Statuses)</div>
            <div class="text-xs text-indigo-600 mt-2 flex items-center justify-between font-semibold">
                <span>Click to view status categories</span>
                <i class="bi bi-arrow-right-circle-fill text-indigo-700 text-base"></i>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 1: Orders Status Breakdown (Allocated, In Process, Overdue, Completed) --}}
<div class="modal fade" id="ordersStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-3xl border-0 shadow-2xl overflow-hidden">
            <div class="modal-header border-0 bg-indigo-800 text-white px-8 py-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-700 text-white flex items-center justify-center text-xl">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-black text-xl mb-0">Production Orders by Status</h5>
                        <p class="text-indigo-200 text-xs mb-0">Click any status box to see order numbers, qty, weight & due dates</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-8 bg-slate-50">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Allocated Card -->
                    <div onclick="openOrdersDetailModal('allocated')" class="bg-white p-5 rounded-2xl border-2 border-slate-200 hover:border-slate-500 cursor-pointer transition hover:shadow-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-slate-500 uppercase">Allocated</span>
                            <i class="bi bi-box-arrow-in-down text-slate-600 text-lg"></i>
                        </div>
                        <div class="text-3xl font-black text-slate-900">{{ $woStats['allocated'] + $poStats['allocated'] }}</div>
                        <div class="flex items-center gap-2 mt-3 text-xs font-bold">
                            <span class="bg-indigo-50 text-indigo-800 px-2 py-0.5 rounded border border-indigo-100">Work Orders: {{ $woStats['allocated'] }}</span>
                            <span class="bg-blue-50 text-blue-800 px-2 py-0.5 rounded border border-blue-100">Purchase Orders: {{ $poStats['allocated'] }}</span>
                        </div>
                    </div>

                    <!-- In Process Card -->
                    <div onclick="openOrdersDetailModal('in_process')" class="bg-white p-5 rounded-2xl border-2 border-blue-200 hover:border-blue-500 cursor-pointer transition hover:shadow-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-blue-600 uppercase">In Process</span>
                            <i class="bi bi-gear-wide-connected text-blue-600 text-lg"></i>
                        </div>
                        <div class="text-3xl font-black text-blue-900">{{ $woStats['in_process'] + $poStats['in_process'] }}</div>
                        <div class="flex items-center gap-2 mt-3 text-xs font-bold">
                            <span class="bg-indigo-50 text-indigo-800 px-2 py-0.5 rounded border border-indigo-100">Work Orders: {{ $woStats['in_process'] }}</span>
                            <span class="bg-blue-50 text-blue-800 px-2 py-0.5 rounded border border-blue-100">Purchase Orders: {{ $poStats['in_process'] }}</span>
                        </div>
                    </div>

                    <!-- Overdue Card -->
                    <div onclick="openOrdersDetailModal('overdue')" class="bg-white p-5 rounded-2xl border-2 border-red-200 hover:border-red-500 cursor-pointer transition hover:shadow-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-red-600 uppercase">Overdue (Delayed)</span>
                            <i class="bi bi-exclamation-octagon text-red-600 text-lg"></i>
                        </div>
                        <div class="text-3xl font-black text-red-700">{{ $woStats['overdue'] + $poStats['overdue'] }}</div>
                        <div class="flex items-center gap-2 mt-3 text-xs font-bold">
                            <span class="bg-red-50 text-red-800 px-2 py-0.5 rounded border border-red-100">Work Orders: {{ $woStats['overdue'] }}</span>
                            <span class="bg-red-50 text-red-800 px-2 py-0.5 rounded border border-red-100">Purchase Orders: {{ $poStats['overdue'] }}</span>
                        </div>
                    </div>

                    <!-- Completed Card -->
                    <div onclick="openOrdersDetailModal('completed')" class="bg-white p-5 rounded-2xl border-2 border-green-200 hover:border-green-500 cursor-pointer transition hover:shadow-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-green-700 uppercase">Completed</span>
                            <i class="bi bi-check2-circle text-green-600 text-lg"></i>
                        </div>
                        <div class="text-3xl font-black text-green-900">{{ $woStats['completed'] + $poStats['completed'] }}</div>
                        <div class="flex items-center gap-2 mt-3 text-xs font-bold">
                            <span class="bg-indigo-50 text-indigo-800 px-2 py-0.5 rounded border border-indigo-100">Work Orders: {{ $woStats['completed'] }}</span>
                            <span class="bg-blue-50 text-blue-800 px-2 py-0.5 rounded border border-blue-100">Purchase Orders: {{ $poStats['completed'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 2: Status Detailed Table with Live Search & Print --}}
<div class="modal fade" id="ordersDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-3xl border-0 shadow-2xl overflow-hidden" id="modalPrintContent">
            {{-- Modal Header --}}
            <div class="modal-header border-0 bg-indigo-900 text-white px-8 py-5 flex justify-between items-center">
                <div>
                    <h5 class="modal-title font-black text-xl mb-0 flex items-center">
                        <span id="modalActiveStatusTitle">Allocated Orders</span>
                        <span id="modalActiveStatusBadge" class="ml-3 text-xs font-bold bg-white text-indigo-900 px-3 py-0.5 rounded-full uppercase">Status</span>
                    </h5>
                    <p class="text-indigo-300 text-xs mb-0">Craftsman: {{ $craftsman->name }} ({{ $craftsman->craftman_code }})</p>
                </div>
                <div class="flex items-center gap-2 no-print">
                    <button type="button" onclick="openPrintModal()" class="px-3 py-1.5 bg-indigo-700 hover:bg-indigo-600 text-white rounded-lg text-xs font-bold transition flex items-center">
                        <i class="bi bi-printer-fill mr-1.5"></i> Print List
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            {{-- Filter & Tab Nav inside Modal --}}
            <div class="p-6 bg-slate-50 border-b border-slate-200 no-print">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- WO / PO Tabs -->
                    <div class="flex space-x-2 bg-indigo-100 p-1 rounded-xl w-fit">
                        <button type="button" id="modalTabWoBtn" onclick="switchModalTab('wo')" class="px-5 py-2 rounded-lg text-xs font-bold transition bg-indigo-900 text-white shadow">
                            <i class="bi bi-clipboard-check mr-1"></i> Work Orders (<span id="modalWoCount">0</span>)
                        </button>
                        <button type="button" id="modalTabPoBtn" onclick="switchModalTab('po')" class="px-5 py-2 rounded-lg text-xs font-bold transition text-indigo-800 hover:bg-indigo-200">
                            <i class="bi bi-cart3 mr-1"></i> Purchase Orders (<span id="modalPoCount">0</span>)
                        </button>
                    </div>

                    <!-- Live Keystroke Search -->
                    <div class="relative flex-grow md:max-w-md">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-indigo-600">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               id="modalLiveSearch" 
                               class="w-full pl-9 pr-9 py-2 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-700 focus:outline-none" 
                               placeholder="Type to filter order #, product, due date..." 
                               autocomplete="off">
                        <button type="button" 
                                id="clearModalSearchBtn" 
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-700 hidden">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Print Header (Visible during print only) --}}
            <div class="hidden print:block p-6 border-b border-gray-300">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $craftsman->business_name ?: $craftsman->name }}</h2>
                        <p class="text-sm text-gray-600">Craftsman Code: {{ $craftsman->craftman_code }} | Staff: {{ $staff->name }}</p>
                    </div>
                    <div class="text-right">
                        <h3 class="text-lg font-bold text-gray-800" id="printReportTitle">Status Report</h3>
                        <p class="text-xs text-gray-500">Generated: {{ date('d M Y, h:i A') }}</p>
                    </div>
                </div>
            </div>

            {{-- Modal Content: Work Orders Table --}}
            <div class="modal-body p-0 max-h-[60vh] overflow-y-auto bg-white" id="modalWoTableWrapper">
                <table class="w-full text-left border-collapse" id="modalWoTable">
                    <thead class="bg-indigo-50 text-indigo-900 uppercase text-xs font-bold sticky top-0 border-b border-indigo-100">
                        <tr>
                            <th class="px-6 py-4 col-wo_number">Work Order #</th>
                            <th class="px-6 py-4 col-product">Product</th>
                            <th class="px-6 py-4 text-center col-qty">Qty</th>
                            <th class="px-6 py-4 text-right col-weight">Weight From</th>
                            <th class="px-6 py-4 col-due_date">Due Date</th>
                            <th class="px-6 py-4 col-status">Status</th>
                            <th class="px-6 py-4 col-delay">Delay / Overdue</th>
                            <th class="px-6 py-4 text-center no-print col-action">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($allWorkOrders as $wo)
                        <tr class="modal-wo-row hover:bg-indigo-50/40 transition" 
                            data-status="{{ strtolower($wo->craftsman_status ?? $wo->status) }}" 
                            data-is-overdue="{{ $wo->is_delayed ? '1' : '0' }}">
                            <td class="px-6 py-4 font-bold text-indigo-950 col-wo_number">
                                <span class="modal-search-item" data-text="{{ $wo->work_order_number }}">{{ $wo->work_order_number }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-700 col-product">
                                <span class="modal-search-item" data-text="{{ $wo->product_name }}">{{ $wo->product_name }}</span>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-900 col-qty">{{ $wo->quantity }}</td>
                            <td class="px-6 py-4 text-right font-mono font-bold text-indigo-800 col-weight">
                                {{ number_format($wo->weight_from ?? 0, 3) }} g
                            </td>
                            <td class="px-6 py-4 col-due_date">
                                <span class="whitespace-nowrap {{ $wo->is_delayed ? 'text-red-600 font-bold' : 'text-slate-700' }}">
                                    {{ $wo->craftsman_due_date ? Carbon\Carbon::parse($wo->craftsman_due_date)->format('d M Y') : ($wo->due_date ? Carbon\Carbon::parse($wo->due_date)->format('d M Y') : 'N/A') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 col-status">
                                @php
                                    $statusVal = strtolower($wo->craftsman_status ?? $wo->status);
                                    $badge = $statusVal === 'completed' ? 'bg-green-100 text-green-800' : ($wo->is_delayed ? 'bg-red-100 text-red-800' : 'bg-indigo-100 text-indigo-800');
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase modal-search-item {{ $badge }}" data-text="{{ $wo->craftsman_status ?? $wo->status }}">
                                    {{ $wo->craftsman_status ?? $wo->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 col-delay">
                                @if($wo->is_delayed)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-700">
                                        <i class="bi bi-clock-history mr-1"></i> {{ $wo->days_delayed }} {{ Str::plural('Day', $wo->days_delayed) }} Overdue
                                    </span>
                                @else
                                    <span class="text-xs text-indigo-600 font-medium"><i class="bi bi-check-circle mr-1"></i> On Track</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center no-print col-action">
                                <a href="{{ route('craftsman_staff.work-order.show', $wo) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-700 hover:text-white transition">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-400">No work orders recorded.</td>
                        </tr>
                        @endforelse
                        <tr id="modalWoNoMatch" class="hidden">
                            <td colspan="8" class="text-center py-8 text-slate-500">No matching work orders found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Modal Content: Purchase Orders Table --}}
            <div class="modal-body p-0 max-h-[60vh] overflow-y-auto bg-white hidden" id="modalPoTableWrapper">
                <table class="w-full text-left border-collapse" id="modalPoTable">
                    <thead class="bg-blue-50 text-blue-900 uppercase text-xs font-bold sticky top-0 border-b border-blue-100">
                        <tr>
                            <th class="px-6 py-4 col-po_number">Purchase Order #</th>
                            <th class="px-6 py-4 col-product">PO Details</th>
                            <th class="px-6 py-4 text-center col-qty">Items Qty</th>
                            <th class="px-6 py-4 text-right col-weight">Weight From</th>
                            <th class="px-6 py-4 col-due_date">Due Date</th>
                            <th class="px-6 py-4 col-status">Status</th>
                            <th class="px-6 py-4 col-delay">Delay / Overdue</th>
                            <th class="px-6 py-4 text-center no-print col-action">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($allPurchaseOrders as $po)
                        <tr class="modal-po-row hover:bg-blue-50/40 transition" 
                            data-status="{{ strtolower($po->craftsman_status ?? $po->status) }}" 
                            data-is-overdue="{{ $po->is_delayed ? '1' : '0' }}">
                            <td class="px-6 py-4 font-bold text-blue-950 col-po_number">
                                <span class="modal-search-item" data-text="{{ $po->purchase_order_code ?? $po->po_number }}">{{ $po->purchase_order_code ?? $po->po_number }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-700 col-product">
                                <span class="modal-search-item" data-text="{{ count($po->items ?? []) }} Items">{{ count($po->items ?? []) }} Distinct Item(s)</span>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-900 col-qty">{{ $po->calculated_qty }}</td>
                            <td class="px-6 py-4 text-right font-mono font-bold text-blue-800 col-weight">
                                {{ number_format($po->calculated_weight ?? 0, 3) }} g
                            </td>
                            <td class="px-6 py-4 col-due_date">
                                <span class="whitespace-nowrap {{ $po->is_delayed ? 'text-red-600 font-bold' : 'text-slate-700' }}">
                                    {{ $po->due_date ? Carbon\Carbon::parse($po->due_date)->format('d M Y') : 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 col-status">
                                @php
                                    $statusVal = strtolower($po->craftsman_status ?? $po->status);
                                    $badge = in_array($statusVal, ['completed', 'approved']) ? 'bg-green-100 text-green-800' : ($po->is_delayed ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800');
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase modal-search-item {{ $badge }}" data-text="{{ $po->craftsman_status ?? $po->status }}">
                                    {{ $po->craftsman_status ?? $po->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 col-delay">
                                @if($po->is_delayed)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-700">
                                        <i class="bi bi-clock-history mr-1"></i> {{ $po->days_delayed }} {{ Str::plural('Day', $po->days_delayed) }} Overdue
                                    </span>
                                @else
                                    <span class="text-xs text-blue-600 font-medium"><i class="bi bi-check-circle mr-1"></i> On Track</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center no-print col-action">
                                <a href="{{ route('craftsman_staff.purchase-order.index') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-700 hover:text-white transition">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-400">No purchase orders recorded.</td>
                        </tr>
                        @endforelse
                        <tr id="modalPoNoMatch" class="hidden">
                            <td colspan="8" class="text-center py-8 text-slate-500">No matching purchase orders found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 3: Print Column Selection --}}
<div class="modal fade" id="printCustomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3xl border-0 shadow-2xl p-6">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <h5 class="text-lg font-bold text-gray-900"><i class="bi bi-printer me-2 text-indigo-700"></i>Select Fields to Print</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="py-4">
                <p class="text-xs text-gray-500 mb-3">Choose the columns to include in your printed report:</p>
                <div class="grid grid-cols-2 gap-3 text-sm" id="columnCheckboxes">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" checked value="col-wo_number,col-po_number" class="rounded text-indigo-600 focus:ring-indigo-500">
                        <span>Order Number</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" checked value="col-product" class="rounded text-indigo-600 focus:ring-indigo-500">
                        <span>Product / Details</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" checked value="col-qty" class="rounded text-indigo-600 focus:ring-indigo-500">
                        <span>Quantity</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" checked value="col-weight" class="rounded text-indigo-600 focus:ring-indigo-500">
                        <span>Weight From</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" checked value="col-due_date" class="rounded text-indigo-600 focus:ring-indigo-500">
                        <span>Due Date</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" checked value="col-status" class="rounded text-indigo-600 focus:ring-indigo-500">
                        <span>Status</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" checked value="col-delay" class="rounded text-indigo-600 focus:ring-indigo-500">
                        <span>Delay / Overdue</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 font-bold rounded-lg text-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" onclick="executePrint()" class="px-5 py-2 bg-indigo-800 hover:bg-indigo-900 text-white font-bold rounded-lg text-sm">
                    <i class="bi bi-printer-fill mr-1"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Analytics Summary Modal --}}
<div class="modal fade" id="progressAnalyticsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-3xl border-0 shadow-2xl overflow-hidden">
            <div class="modal-header border-0 bg-indigo-800 text-white px-8 py-5">
                <h5 class="modal-title font-bold">Personal Production Analytics</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 align-middle">
                        <thead class="bg-slate-100 text-[11px] font-bold uppercase text-slate-700">
                            <tr>
                                <th class="px-6 py-4">Category</th>
                                <th class="px-6 py-4 text-center">In Process Weight</th>
                                <th class="px-6 py-4 text-center">Overdue Weight</th>
                                <th class="px-6 py-4 text-center">Total Weight</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($craftsmanStats as $code => $stat)
                            <tr>
                                <td class="px-6 py-4 font-black text-indigo-900">Work Orders (WA)</td>
                                <td class="px-6 py-4 text-center text-blue-600 font-bold text-lg">{{ number_format($stat['wa']['process']['weight'], 2) }} g</td>
                                <td class="px-6 py-4 text-center text-red-600 font-bold text-lg">{{ number_format($stat['wa']['overdue']['weight'], 2) }} g</td>
                                <td class="px-6 py-4 text-center text-slate-900 font-black text-lg bg-slate-50">{{ number_format($stat['wa']['process']['weight'] + $stat['wa']['overdue']['weight'], 2) }} g</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 font-black text-blue-900">Purchase Orders (PA)</td>
                                <td class="px-6 py-4 text-center text-blue-600 font-bold text-lg">{{ number_format($stat['po']['process']['weight'], 2) }} g</td>
                                <td class="px-6 py-4 text-center text-red-600 font-bold text-lg">{{ number_format($stat['po']['overdue']['weight'], 2) }} g</td>
                                <td class="px-6 py-4 text-center text-slate-900 font-black text-lg bg-slate-50">{{ number_format($stat['po']['process']['weight'] + $stat['po']['overdue']['weight'], 2) }} g</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentModalTab = 'wo';
let currentModalStatus = 'allocated';

function openOrdersStatusModal() {
    const modal = new bootstrap.Modal(document.getElementById('ordersStatusModal'));
    modal.show();
}

function openOrdersDetailModal(status) {
    currentModalStatus = status;

    // Hide status modal if open
    const statusModalEl = document.getElementById('ordersStatusModal');
    const statusModal = bootstrap.Modal.getInstance(statusModalEl);
    if (statusModal) statusModal.hide();

    // Update modal title and badge
    const label = status.replace('_', ' ').toUpperCase();
    document.getElementById('modalActiveStatusTitle').textContent = `${label} Orders`;
    document.getElementById('modalActiveStatusBadge').textContent = label;
    document.getElementById('printReportTitle').textContent = `${label} Production Report`;

    // Clear search input
    const searchInput = document.getElementById('modalLiveSearch');
    if (searchInput) searchInput.value = '';

    // Show Detail Modal
    const detailModal = new bootstrap.Modal(document.getElementById('ordersDetailModal'));
    detailModal.show();

    applyModalFilter();
}

function switchModalTab(type) {
    currentModalTab = type;
    const woWrapper = document.getElementById('modalWoTableWrapper');
    const poWrapper = document.getElementById('modalPoTableWrapper');
    const tabWoBtn = document.getElementById('modalTabWoBtn');
    const tabPoBtn = document.getElementById('modalTabPoBtn');

    if (type === 'wo') {
        woWrapper.classList.remove('hidden');
        poWrapper.classList.add('hidden');
        tabWoBtn.className = "px-5 py-2 rounded-lg text-xs font-bold transition bg-indigo-900 text-white shadow";
        tabPoBtn.className = "px-5 py-2 rounded-lg text-xs font-bold transition text-indigo-800 hover:bg-indigo-200";
    } else {
        woWrapper.classList.add('hidden');
        poWrapper.classList.remove('hidden');
        tabPoBtn.className = "px-5 py-2 rounded-lg text-xs font-bold transition bg-blue-900 text-white shadow";
        tabWoBtn.className = "px-5 py-2 rounded-lg text-xs font-bold transition text-indigo-800 hover:bg-indigo-200";
    }

    applyModalFilter();
}

function applyModalFilter() {
    const searchInput = document.getElementById('modalLiveSearch');
    const clearBtn = document.getElementById('clearModalSearchBtn');
    const rawTerm = searchInput ? searchInput.value.trim() : '';
    const term = rawTerm.toLowerCase();

    if (clearBtn) {
        clearBtn.classList.toggle('hidden', rawTerm === '');
    }

    const regex = rawTerm !== '' ? new RegExp(`(${rawTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi') : null;

    function filterTable(rows, noMatchElem, counterElemId) {
        let count = 0;

        rows.forEach(row => {
            const rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
            const isOverdue = row.getAttribute('data-is-overdue') === '1';

            // Status match
            let statusMatches = false;
            if (currentModalStatus === 'all') {
                statusMatches = true;
            } else if (currentModalStatus === 'overdue') {
                statusMatches = isOverdue;
            } else {
                statusMatches = (rowStatus === currentModalStatus);
            }

            // Keyword match
            const cells = row.querySelectorAll('.modal-search-item');
            let searchMatches = (term === '');

            cells.forEach(cell => {
                const originalText = cell.getAttribute('data-text') || '';
                if (term !== '' && originalText.toLowerCase().includes(term)) {
                    searchMatches = true;
                    cell.innerHTML = originalText.replace(regex, '<span class="highlight-term">$1</span>');
                } else {
                    cell.innerHTML = originalText;
                }
            });

            if (statusMatches && searchMatches) {
                row.style.display = '';
                count++;
            } else {
                row.style.display = 'none';
            }
        });

        if (noMatchElem) {
            noMatchElem.classList.toggle('hidden', count > 0 || rows.length === 0);
        }

        const counter = document.getElementById(counterElemId);
        if (counter) counter.textContent = count;
    }

    const woRows = document.querySelectorAll('#modalWoTable tbody tr.modal-wo-row');
    const poRows = document.querySelectorAll('#modalPoTable tbody tr.modal-po-row');
    const woNoMatch = document.getElementById('modalWoNoMatch');
    const poNoMatch = document.getElementById('modalPoNoMatch');

    filterTable(woRows, woNoMatch, 'modalWoCount');
    filterTable(poRows, poNoMatch, 'modalPoCount');
}

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('modalLiveSearch');
    const clearBtn = document.getElementById('clearModalSearchBtn');

    if (searchInput) {
        searchInput.addEventListener('input', applyModalFilter);
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            applyModalFilter();
            searchInput.focus();
        });
    }
});

function openPrintModal() {
    const modal = new bootstrap.Modal(document.getElementById('printCustomModal'));
    modal.show();
}

function executePrint() {
    const checkboxes = document.querySelectorAll('#columnCheckboxes input[type="checkbox"]');
    
    checkboxes.forEach(cb => {
        const classNames = cb.value.split(',');
        classNames.forEach(cls => {
            const elements = document.querySelectorAll(`.${cls.trim()}`);
            elements.forEach(el => {
                if (cb.checked) {
                    el.classList.remove('hidden');
                } else {
                    el.classList.add('hidden');
                }
            });
        });
    });

    const modalEl = document.getElementById('printCustomModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();

    setTimeout(() => {
        window.print();
        checkboxes.forEach(cb => {
            const classNames = cb.value.split(',');
            classNames.forEach(cls => {
                const elements = document.querySelectorAll(`.${cls.trim()}`);
                elements.forEach(el => el.classList.remove('hidden'));
            });
        });
    }, 500);
}
</script>
@endsection