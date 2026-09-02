@extends('super-admin.layouts.app')

@section('title', 'Details ALL Craftsman & Clients')

@section('content')
<div class="container-fluid py-4">
    
    <!-- ==========================================
         TOP TRIGGER CARDS (SIDE BY SIDE IN ONE ROW)
    =========================================== -->
    <div class="row g-3 mb-4">
        <!-- Craftsman Card -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card shadow-sm border-0 h-100 bg-white" onclick="toggleDetailsTable()" style="cursor: pointer;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="bg-warning-subtle text-warning rounded-3 p-2">
                            <i class="bi bi-star-fill fs-5"></i>
                        </div>
                        <i class="bi {{ $status != 'all' || request()->has('sort_by') ? 'bi-chevron-up' : 'bi-chevron-down' }} text-muted" id="toggleIcon"></i>
                    </div>
                    <h3 class="fw-bold mb-1">{{ count($craftsmenData) }}</h3>
                    <p class="small text-muted mb-0 fw-semibold">TOP PICKS CRAFTSMEN (CLICK TO VIEW)</p>
                </div>
            </div>
        </div>

        <!-- Client Card -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card shadow-sm border-0 h-100 bg-white" onclick="toggleClientsTable()" style="cursor: pointer;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="bg-primary-subtle text-primary rounded-3 p-2">
                            <i class="bi bi-building fs-5"></i>
                        </div>
                        <i class="bi bi-chevron-down text-muted" id="toggleClientsIcon"></i>
                    </div>
                    <h3 class="fw-bold mb-1">{{ count($topPicksClientsFull) }}</h3>
                    <p class="small text-muted mb-0 fw-semibold">TOP PICKS CLIENTS (CLICK TO VIEW)</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ==========================================
         COLLAPSIBLE CRAFTSMEN CONTAINER
    =========================================== -->
    <div id="detailsTableContainer" style="{{ ($status != 'all' || request()->has('sort_by')) ? 'display: block;' : 'display: none;' }}">
        
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-4 col-md-6">
                <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1 fw-bold">WO TOTAL WEIGHT</p>
                                <h4 class="fw-bold text-primary mb-0">{{ number_format(collect($craftsmenData)->sum('wa_total_weight'), 2) }} g</h4>
                            </div>
                            <div class="bg-primary-subtle text-primary rounded-3 p-2">
                                <i class="bi bi-box-seam fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card shadow-sm border-0 border-start border-info border-4 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1 fw-bold">PO TOTAL WEIGHT</p>
                                <h4 class="fw-bold text-info mb-0">{{ number_format(collect($craftsmenData)->sum('po_total_weight'), 2) }} g</h4>
                            </div>
                            <div class="bg-info-subtle text-info rounded-3 p-2">
                                <i class="bi bi-cart-check fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-12">
                <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1 fw-bold">COMBINED CRAFTSMEN WEIGHT</p>
                                <h4 class="fw-bold text-success mb-0">{{ number_format(collect($craftsmenData)->sum('total_weight'), 2) }} g</h4>
                            </div>
                            <div class="bg-success-subtle text-success rounded-3 p-2">
                                <i class="bi bi-gem fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Craftsmen Filters & Action Bar -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body bg-light rounded">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
                    <form method="GET" action="{{ route('super-admin.details-all') }}" class="row g-3 flex-grow-1 align-items-end mb-0">
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">Status Filter</label>
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Statuses</option>
                                <option value="in_process" {{ $status == 'in_process' ? 'selected' : '' }}>In Process</option>
                                <option value="for_approval" {{ $status == 'for_approval' ? 'selected' : '' }}>For Approval</option>
                                <option value="allocated" {{ $status == 'allocated' ? 'selected' : '' }}>Allocated</option>
                                <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="overdue" {{ $status == 'overdue' ? 'selected' : '' }}>Overdue Only (Overdue > 0)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">Sort By</label>
                            <select name="sort_by" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="allocated" {{ $sortBy == 'allocated' ? 'selected' : '' }}>Allocated</option>
                                <option value="in_process" {{ $sortBy == 'in_process' ? 'selected' : '' }}>In Process</option>
                                <option value="completed" {{ $sortBy == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="overdue" {{ $sortBy == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                <option value="total_weight" {{ $sortBy == 'total_weight' ? 'selected' : '' }}>Total Combined Weight</option>
                                <option value="wa_total_weight" {{ $sortBy == 'wa_total_weight' ? 'selected' : '' }}>WO Weight</option>
                                <option value="po_total_weight" {{ $sortBy == 'po_total_weight' ? 'selected' : '' }}>PO Weight</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small fw-bold">Order</label>
                            <select name="sort_order" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="desc" {{ $sortOrder == 'desc' ? 'selected' : '' }}>Descending</option>
                                <option value="asc" {{ $sortOrder == 'asc' ? 'selected' : '' }}>Ascending</option>
                            </select>
                        </div>
                    </form>
                    <div>
                        <button type="button" class="btn btn-outline-warning btn-sm me-2 text-dark" onclick="openPrintColumnModal('selected')">
                            <i class="bi bi-printer"></i> Print Selected Craftsmen
                        </button>
                        <button type="button" class="btn btn-warning btn-sm text-dark" onclick="openPrintColumnModal('all')">
                            <i class="bi bi-printer-fill"></i> Print All Craftsmen
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Craftsmen Table -->
        <div class="card shadow-sm border-0 mb-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="detailsTable">
                        <thead class="bg-light">
                            <tr>
                                <th rowspan="2" class="text-center border-end" style="width: 40px; vertical-align: middle;">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </th>
                                <th rowspan="2" class="border-end" style="vertical-align: middle;">{{ __('messages.craftsman') }}</th>
                                <th rowspan="2" class="text-center border-end" style="vertical-align: middle;">Code</th>
                                <th rowspan="2" class="text-center border-end bg-light-subtle" style="vertical-align: middle;">Total Weight (g)</th>
                                <th colspan="4" class="text-center bg-primary text-white border-end" style="font-size: 0.8rem;">WORK ORDERS (WO)</th>
                                <th colspan="4" class="text-center bg-info text-white" style="font-size: 0.8rem;">PURCHASE ORDERS (PO)</th>
                            </tr>
                            <tr class="bg-white">
                                <th class="text-center py-2 border-end" style="font-size: 0.75rem;">IN PROCESS (C | W)</th>
                                <th class="text-center py-2 border-end text-success" style="font-size: 0.75rem;">COMPLETED (C | W)</th>
                                <th class="text-center py-2 border-end" style="font-size: 0.75rem;">FOR APPROVAL (C | W)</th>
                                <th class="text-center py-2 text-danger border-end" style="font-size: 0.75rem;">OVERDUE (C | W)</th>
                                <th class="text-center py-2 border-end" style="font-size: 0.75rem;">IN PROCESS (C | W)</th>
                                <th class="text-center py-2 border-end text-success" style="font-size: 0.75rem;">COMPLETED (C | W)</th>
                                <th class="text-center py-2 border-end" style="font-size: 0.75rem;">FOR APPROVAL (C | W)</th>
                                <th class="text-center py-2 text-danger" style="font-size: 0.75rem;">OVERDUE (C | W)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($craftsmenData as $stat)
                            <tr class="data-row" data-code="{{ $stat['code'] }}">
                                <td class="text-center border-end">
                                    <input class="form-check-input row-checkbox" type="checkbox" value="{{ $stat['code'] }}">
                                </td>
                                <td class="border-end">
                                    <div class="fw-semibold text-dark">{{ $stat['name'] }}</div>
                                </td>
                                <td class="text-center border-end"><span class="badge bg-secondary">{{ $stat['code'] }}</span></td>
                                <td class="text-center border-end fw-bold text-dark">{{ number_format($stat['total_weight'], 2) }}</td>
                                
                                {{-- Work Orders --}}
                                <td class="text-center text-primary border-end fw-medium">
                                    @if($stat['wo']['in_process']['count'] > 0)
                                        <span class="text-decoration-underline" style="cursor: pointer;" onclick="showOrdersList(this, 'wo')" data-title="In Process (WO) - {{ $stat['name'] }}" data-orders="{{ json_encode($stat['wo']['in_process']['orders']) }}">
                                            {{ $stat['wo']['in_process']['count'] }} | {{ number_format($stat['wo']['in_process']['weight'], 2) }}
                                        </span>
                                    @else - @endif
                                </td>
                                <td class="text-center text-success border-end fw-medium">
                                    @if($stat['wo']['completed']['count'] > 0)
                                        <span class="text-decoration-underline" style="cursor: pointer;" onclick="showOrdersList(this, 'wo')" data-title="Completed (WO) - {{ $stat['name'] }}" data-orders="{{ json_encode($stat['wo']['completed']['orders']) }}">
                                            {{ $stat['wo']['completed']['count'] }} | {{ number_format($stat['wo']['completed']['weight'], 2) }}
                                        </span>
                                    @else - @endif
                                </td>
                                <td class="text-center border-end fw-medium text-dark">
                                    @if($stat['wo']['for_approval']['count'] > 0)
                                        <span class="text-decoration-underline" style="cursor: pointer;" onclick="showOrdersList(this, 'wo')" data-title="For Approval (WO) - {{ $stat['name'] }}" data-orders="{{ json_encode($stat['wo']['for_approval']['orders']) }}">
                                            {{ $stat['wo']['for_approval']['count'] }} | {{ number_format($stat['wo']['for_approval']['weight'], 2) }}
                                        </span>
                                    @else - @endif
                                </td>
                                <td class="text-center text-danger fw-bold border-end">
                                    @if($stat['wo']['overdue']['count'] > 0)
                                        <span class="text-decoration-underline" style="cursor: pointer;" onclick="showOrdersList(this, 'wo')" data-title="Overdue (WO) - {{ $stat['name'] }}" data-orders="{{ json_encode($stat['wo']['overdue']['orders']) }}">
                                            {{ $stat['wo']['overdue']['count'] }} | {{ number_format($stat['wo']['overdue']['weight'], 2) }}
                                        </span>
                                    @else - @endif
                                </td>
                                
                                {{-- Purchase Orders --}}
                                <td class="text-center text-info border-end fw-medium">
                                    @if($stat['po']['in_process']['count'] > 0)
                                        <span class="text-decoration-underline" style="cursor: pointer;" onclick="showOrdersList(this, 'po')" data-title="In Process (PO) - {{ $stat['name'] }}" data-orders="{{ json_encode($stat['po']['in_process']['orders']) }}">
                                            {{ $stat['po']['in_process']['count'] }} | {{ number_format($stat['po']['in_process']['weight'], 2) }}
                                        </span>
                                    @else - @endif
                                </td>
                                <td class="text-center text-success border-end fw-medium">
                                    @if($stat['po']['completed']['count'] > 0)
                                        <span class="text-decoration-underline" style="cursor: pointer;" onclick="showOrdersList(this, 'po')" data-title="Completed (PO) - {{ $stat['name'] }}" data-orders="{{ json_encode($stat['po']['completed']['orders']) }}">
                                            {{ $stat['po']['completed']['count'] }} | {{ number_format($stat['po']['completed']['weight'], 2) }}
                                        </span>
                                    @else - @endif
                                </td>
                                <td class="text-center border-end fw-medium text-dark">
                                    @if($stat['po']['for_approval']['count'] > 0)
                                        <span class="text-decoration-underline" style="cursor: pointer;" onclick="showOrdersList(this, 'po')" data-title="For Approval (PO) - {{ $stat['name'] }}" data-orders="{{ json_encode($stat['po']['for_approval']['orders']) }}">
                                            {{ $stat['po']['for_approval']['count'] }} | {{ number_format($stat['po']['for_approval']['weight'], 2) }}
                                        </span>
                                    @else - @endif
                                </td>
                                <td class="text-center text-danger fw-bold">
                                    @if($stat['po']['overdue']['count'] > 0)
                                        <span class="text-decoration-underline" style="cursor: pointer;" onclick="showOrdersList(this, 'po')" data-title="Overdue (PO) - {{ $stat['name'] }}" data-orders="{{ json_encode($stat['po']['overdue']['orders']) }}">
                                            {{ $stat['po']['overdue']['count'] }} | {{ number_format($stat['po']['overdue']['weight'], 2) }}
                                        </span>
                                    @else - @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    No craftsmen found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ==========================================
         COLLAPSIBLE CLIENTS CONTAINER
    =========================================== -->
    <div id="clientsTableContainer" style="display: none;">
        
        <!-- Summary Cards Inside Clients Container -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1 fw-bold">NEW ORDERS WEIGHT</p>
                                <h4 class="fw-bold text-primary mb-0">{{ number_format(collect($topPicksClientsFull)->sum(fn($c) => $c['new']['weight'] ?? 0), 2) }} g</h4>
                            </div>
                            <div class="bg-primary-subtle text-primary rounded-3 p-2">
                                <i class="bi bi-file-earmark-plus fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm border-0 border-start border-info border-4 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1 fw-bold">PROCESS WEIGHT</p>
                                <h4 class="fw-bold text-info mb-0">{{ number_format(collect($topPicksClientsFull)->sum(fn($c) => $c['in_process']['weight'] ?? 0), 2) }} g</h4>
                            </div>
                            <div class="bg-info-subtle text-info rounded-3 p-2">
                                <i class="bi bi-hourglass-split fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm border-0 border-start border-danger border-4 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1 fw-bold">OVERDUE WEIGHT</p>
                                <h4 class="fw-bold text-danger mb-0">{{ number_format(collect($topPicksClientsFull)->sum(fn($c) => $c['overdue']['weight'] ?? 0), 2) }} g</h4>
                            </div>
                            <div class="bg-danger-subtle text-danger rounded-3 p-2">
                                <i class="bi bi-exclamation-octagon fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1 fw-bold">TOTAL CLIENT ORDERS</p>
                                <h4 class="fw-bold text-success mb-0">{{ collect($topPicksClientsFull)->sum('orders') }}</h4>
                            </div>
                            <div class="bg-success-subtle text-success rounded-3 p-2">
                                <i class="bi bi-receipt-cutoff fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clients Actions Bar -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-dark mb-0">Client Breakdown (WA)</h5>
            <div>
                <button class="btn btn-outline-primary btn-sm me-2" onclick="openClientPrintModal('selected')">
                    <i class="bi bi-printer"></i> Print Selected Clients
                </button>
                <button class="btn btn-primary btn-sm" onclick="openClientPrintModal('all')">
                    <i class="bi bi-printer-fill"></i> Print All Clients
                </button>
            </div>
        </div>

        <!-- Top Picks Clients Table -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="clientsTable">
                        <thead class="bg-light">
                            <tr>
                                <th rowspan="2" class="text-center border-end" style="width: 40px; vertical-align: middle;">
                                    <input class="form-check-input" type="checkbox" id="selectAllClients">
                                </th>
                                <th rowspan="2" class="border-end" style="vertical-align: middle;">{{ __('messages.business_partner') }}</th>
                                <th rowspan="2" class="text-center border-end" style="vertical-align: middle;">{{ __('messages.bp_code') }}</th>
                                <th colspan="6" class="text-center bg-secondary-subtle text-dark border-end" style="font-size: 0.8rem;">WORK ORDERS (WA)</th>
                                <th rowspan="2" class="text-center bg-light" style="vertical-align: middle;">Total Orders</th>
                            </tr>
                            <tr class="bg-white">
                                <th class="text-center py-2 border-end fw-bold" style="font-size: 0.75rem;">NEW (C | W)</th>
                                <th class="text-center py-2 border-end fw-bold" style="font-size: 0.75rem;">PROCESS (C | W)</th>
                                <th class="text-center py-2 border-end fw-bold text-info" style="font-size: 0.75rem;">FOR APPROVAL (C | W)</th>
                                <th class="text-center py-2 border-end fw-bold text-danger" style="font-size: 0.75rem;">OVERDUE (C | W)</th>
                                <th class="text-center py-2 border-end fw-bold text-danger" style="font-size: 0.75rem;">REJECTED (C | W)</th>
                                <th class="text-center py-2 border-end fw-bold text-success" style="font-size: 0.75rem;">COMPLETED (C | W)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPicksClientsFull as $code => $stat)
                            <tr class="client-data-row" data-code="{{ $code }}">
                                <td class="text-center border-end">
                                    <input class="form-check-input client-row-checkbox" type="checkbox" value="{{ $code }}">
                                </td>
                                <td class="border-end">
                                    <div class="fw-semibold text-dark">{{ $stat['name'] }}</div>
                                </td>
                                <td class="text-center border-end"><span class="badge bg-light text-dark border">{{ $code }}</span></td>
                                
                                <td class="text-center border-end fw-medium text-primary">
                                    @if(isset($stat['new']) && ($stat['new']['count'] > 0 || $stat['new']['weight'] > 0))
                                        <span class="text-decoration-underline" style="cursor: pointer;" onclick="showOrdersList(this, 'wo')" data-title="New Orders - {{ $stat['name'] }}" data-orders="{{ json_encode($stat['new']['orders'] ?? []) }}">
                                            {{ $stat['new']['count'] }} | {{ number_format($stat['new']['weight'], 2) }}
                                        </span>
                                    @else - @endif
                                </td>
                                <td class="text-center border-end fw-medium text-primary">
                                    @if(isset($stat['in_process']) && ($stat['in_process']['count'] > 0 || $stat['in_process']['weight'] > 0))
                                        <span class="text-decoration-underline" style="cursor: pointer;" onclick="showOrdersList(this, 'wo')" data-title="In Process Orders - {{ $stat['name'] }}" data-orders="{{ json_encode($stat['in_process']['orders'] ?? []) }}">
                                            {{ $stat['in_process']['count'] }} | {{ number_format($stat['in_process']['weight'], 2) }}
                                        </span>
                                    @else - @endif
                                </td>
                                <td class="text-center border-end fw-medium text-info">
                                    @if(isset($stat['for_approval']) && ($stat['for_approval']['count'] > 0 || $stat['for_approval']['weight'] > 0))
                                        <span class="text-decoration-underline" style="cursor: pointer;" onclick="showOrdersList(this, 'wo')" data-title="For Approval Orders - {{ $stat['name'] }}" data-orders="{{ json_encode($stat['for_approval']['orders'] ?? []) }}">
                                            {{ $stat['for_approval']['count'] }} | {{ number_format($stat['for_approval']['weight'], 2) }}
                                        </span>
                                    @else - @endif
                                </td>
                                <td class="text-center border-end fw-bold text-danger">
                                    @if(isset($stat['overdue']) && ($stat['overdue']['count'] > 0 || $stat['overdue']['weight'] > 0))
                                        <span class="text-decoration-underline" style="cursor: pointer;" onclick="showOrdersList(this, 'wo')" data-title="Overdue Orders - {{ $stat['name'] }}" data-orders="{{ json_encode($stat['overdue']['orders'] ?? []) }}">
                                            {{ $stat['overdue']['count'] }} | {{ number_format($stat['overdue']['weight'], 2) }}
                                        </span>
                                    @else - @endif
                                </td>
                                <td class="text-center border-end fw-bold text-danger">
                                    @if(isset($stat['rejected']) && ($stat['rejected']['count'] > 0 || $stat['rejected']['weight'] > 0))
                                        <span class="text-decoration-underline" style="cursor: pointer;" onclick="showOrdersList(this, 'wo')" data-title="Rejected Orders - {{ $stat['name'] }}" data-orders="{{ json_encode($stat['rejected']['orders'] ?? []) }}">
                                            {{ $stat['rejected']['count'] }} | {{ number_format($stat['rejected']['weight'], 2) }}
                                        </span>
                                    @else - @endif
                                </td>
                                <td class="text-center border-end fw-medium text-success">
                                    @if(isset($stat['completed']) && ($stat['completed']['count'] > 0 || $stat['completed']['weight'] > 0))
                                        <span class="text-decoration-underline" style="cursor: pointer;" onclick="showOrdersList(this, 'wo')" data-title="Completed Orders - {{ $stat['name'] }}" data-orders="{{ json_encode($stat['completed']['orders'] ?? []) }}">
                                            {{ $stat['completed']['count'] }} | {{ number_format($stat['completed']['weight'], 2) }}
                                        </span>
                                    @else - @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary fs-6">{{ $stat['orders'] }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="10" class="text-center py-4">{{ __('messages.no_data_found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     MODALS
=========================================== -->

<!-- Main Craftsmen Table Print Column Modal -->
<div class="modal fade" id="printFieldsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Select Columns to Print (Craftsmen)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Choose the specific metrics and columns to display on the printed sheet:</p>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input print-col-check" type="checkbox" value="1" id="colName" checked disabled>
                            <label class="form-check-label small" for="colName">Craftsman Name</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input print-col-check" type="checkbox" value="2" id="colCode" checked>
                            <label class="form-check-label small" for="colCode">Code</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input print-col-check" type="checkbox" value="3" id="colTotalWeight" checked>
                            <label class="form-check-label small" for="colTotalWeight">Total Combined Weight</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input print-col-check" type="checkbox" value="4" id="colWoInProc" checked>
                            <label class="form-check-label small" for="colWoInProc">WO In Process</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input print-col-check" type="checkbox" value="5" id="colWoComp" checked>
                            <label class="form-check-label small" for="colWoComp">WO Completed</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input print-col-check" type="checkbox" value="6" id="colWoAppr" checked>
                            <label class="form-check-label small" for="colWoAppr">WO For Approval</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input print-col-check" type="checkbox" value="7" id="colWoOverdue" checked>
                            <label class="form-check-label small text-danger" for="colWoOverdue">WO Overdue</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input print-col-check" type="checkbox" value="8" id="colPoInProc" checked>
                            <label class="form-check-label small" for="colPoInProc">PO In Process</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input print-col-check" type="checkbox" value="9" id="colPoComp" checked>
                            <label class="form-check-label small" for="colPoComp">PO Completed</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input print-col-check" type="checkbox" value="10" id="colPoAppr" checked>
                            <label class="form-check-label small" for="colPoAppr">PO For Approval</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input print-col-check" type="checkbox" value="11" id="colPoOverdue" checked>
                            <label class="form-check-label small text-danger" for="colPoOverdue">PO Overdue</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="executeCustomPrint()">
                    <i class="bi bi-printer-fill"></i> Proceed to Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Clients Table Print Column Modal -->
<div class="modal fade" id="printClientFieldsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Select Columns to Print (Clients)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Choose the specific metrics and columns to display on the printed sheet:</p>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input client-print-col-check" type="checkbox" value="1" id="colClientName" checked disabled>
                            <label class="form-check-label small" for="colClientName">Business Partner</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input client-print-col-check" type="checkbox" value="2" id="colClientCode" checked>
                            <label class="form-check-label small" for="colClientCode">BP Code</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input client-print-col-check" type="checkbox" value="3" id="colClientNew" checked>
                            <label class="form-check-label small" for="colClientNew">New</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input client-print-col-check" type="checkbox" value="4" id="colClientProcess" checked>
                            <label class="form-check-label small" for="colClientProcess">Process</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input client-print-col-check" type="checkbox" value="5" id="colClientForAppr" checked>
                            <label class="form-check-label small" for="colClientForAppr">For Approval</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input client-print-col-check" type="checkbox" value="6" id="colClientOverdue" checked>
                            <label class="form-check-label small text-danger" for="colClientOverdue">Overdue</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input client-print-col-check" type="checkbox" value="7" id="colClientRejected" checked>
                            <label class="form-check-label small text-danger" for="colClientRejected">Rejected</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input client-print-col-check" type="checkbox" value="8" id="colClientCompleted" checked>
                            <label class="form-check-label small text-success" for="colClientCompleted">Completed</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input client-print-col-check" type="checkbox" value="9" id="colClientTotalOrders" checked>
                            <label class="form-check-label small" for="colClientTotalOrders">Total Orders</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="executeClientCustomPrint()">
                    <i class="bi bi-printer-fill"></i> Proceed to Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Orders List Breakdown Modal -->
<div class="modal fade" id="ordersListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="ordersListModalTitle">Orders List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-primary btn-sm" onclick="openOrderFieldsModal()">
                        <i class="bi bi-printer-fill"></i> Print Selected Orders
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="modalOrdersTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width: 40px;">
                                    <input class="form-check-input" type="checkbox" id="modalSelectAll">
                                </th>
                                <th>Order Number</th>
                                <th class="bp-col">BP Code</th>
                                <th class="business-col">Business Name</th>
                                <th class="craftsman-col">Craftsman Code</th>
                                <th class="craftsman-col">Craftsman Name</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Weight (g)</th>
                                <th class="text-center">Due Date</th>
                                <th class="text-center overdue-col" style="display: none;">Overdue Days</th>
                            </tr>
                        </thead>
                        <tbody id="ordersListModalBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Orders Modal Print Field Selection Modal -->
<div class="modal fade" id="orderPrintFieldsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Select Order Fields to Print</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Choose the order details you want to include in the print output:</p>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input order-col-check" type="checkbox" value="1" id="ordColNumber" checked disabled>
                            <label class="form-check-label small" for="ordColNumber">Order Number</label>
                        </div>
                    </div>
                    <div class="col-6" id="fieldBpCodeContainer">
                        <div class="form-check">
                            <input class="form-check-input order-col-check" type="checkbox" value="2" id="ordColBpCode" checked>
                            <label class="form-check-label small" for="ordColBpCode">BP Code</label>
                        </div>
                    </div>
                    <div class="col-6" id="fieldBusinessContainer">
                        <div class="form-check">
                            <input class="form-check-input order-col-check" type="checkbox" value="3" id="ordColBusiness" checked>
                            <label class="form-check-label small" for="ordColBusiness">Business Name</label>
                        </div>
                    </div>
                    <div class="col-6" id="fieldCraftsmanCodeContainer">
                        <div class="form-check">
                            <input class="form-check-input order-col-check" type="checkbox" value="4" id="ordColCraftsmanCode" checked>
                            <label class="form-check-label small" for="ordColCraftsmanCode">Craftsman Code</label>
                        </div>
                    </div>
                    <div class="col-6" id="fieldCraftsmanNameContainer">
                        <div class="form-check">
                            <input class="form-check-input order-col-check" type="checkbox" value="5" id="ordColCraftsmanName" checked>
                            <label class="form-check-label small" for="ordColCraftsmanName">Craftsman Name</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input order-col-check" type="checkbox" value="6" id="ordColQty" checked>
                            <label class="form-check-label small" for="ordColQty">Quantity</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input order-col-check" type="checkbox" value="7" id="ordColWeight" checked>
                            <label class="form-check-label small" for="ordColWeight">Weight (g)</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input order-col-check" type="checkbox" value="8" id="ordColDueDate" checked>
                            <label class="form-check-label small" for="ordColDueDate">Due Date</label>
                        </div>
                    </div>
                    <div class="col-6" id="fieldOverdueContainer" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input order-col-check" type="checkbox" value="9" id="ordColOverdue" checked>
                            <label class="form-check-label small text-danger" for="ordColOverdue">Overdue Days</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="executeModalCustomPrint()">
                    <i class="bi bi-printer-fill"></i> Print Orders
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // --- Craftsman Collapse Toggle ---
    function toggleDetailsTable() {
        const container = document.getElementById('detailsTableContainer');
        const icon = document.getElementById('toggleIcon');
        if (container.style.display === 'none') {
            container.style.display = 'block';
            icon.classList.replace('bi-chevron-down', 'bi-chevron-up');
        } else {
            container.style.display = 'none';
            icon.classList.replace('bi-chevron-up', 'bi-chevron-down');
        }
    }

    // --- Clients Collapse Toggle ---
    function toggleClientsTable() {
        const container = document.getElementById('clientsTableContainer');
        const icon = document.getElementById('toggleClientsIcon');
        if (container.style.display === 'none') {
            container.style.display = 'block';
            icon.classList.replace('bi-chevron-down', 'bi-chevron-up');
        } else {
            container.style.display = 'none';
            icon.classList.replace('bi-chevron-up', 'bi-chevron-down');
        }
    }

    // --- Checkbox Handlers ---
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('selectAllClients').addEventListener('change', function() {
        document.querySelectorAll('.client-row-checkbox').forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('modalSelectAll').addEventListener('change', function() {
        document.querySelectorAll('.modal-row-checkbox').forEach(cb => cb.checked = this.checked);
    });

    // --- Dynamic Order Popup Modal ---
    let currentOrderType = 'wo'; 

    function showOrdersList(el, type) {
        currentOrderType = type;
        const title = el.getAttribute('data-title');
        const orders = JSON.parse(el.getAttribute('data-orders') || '[]');
        
        document.getElementById('ordersListModalTitle').innerText = title;
        const body = document.getElementById('ordersListModalBody');
        body.innerHTML = '';
        
        const bpCols = document.querySelectorAll('.bp-col');
        const businessCols = document.querySelectorAll('.business-col');
        const craftsmanCols = document.querySelectorAll('.craftsman-col');
        
        if (type === 'po') {
            bpCols.forEach(col => col.style.display = 'none');
            businessCols.forEach(col => col.style.display = 'none');
            craftsmanCols.forEach(col => col.style.display = 'none');
        } else {
            bpCols.forEach(col => col.style.display = '');
            businessCols.forEach(col => col.style.display = '');
            craftsmanCols.forEach(col => col.style.display = '');
        }

        const isOverdue = title.toLowerCase().includes('overdue');
        const overdueCols = document.querySelectorAll('.overdue-col');
        overdueCols.forEach(col => col.style.display = isOverdue ? '' : 'none');

        if (orders.length === 0) {
            body.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">No orders found.</td></tr>';
        } else {
            orders.forEach(order => {
                let html = `
                    <tr class="modal-data-row">
                        <td class="text-center">
                            <input class="form-check-input modal-row-checkbox" type="checkbox">
                        </td>
                        <td class="fw-semibold text-dark">${order.number || '-'}</td>`;
                        
                if (type !== 'po') {
                    html += `
                        <td class="bp-col"><span class="badge bg-light text-dark border">${order.bp_code || '-'}</span></td>
                        <td class="business-col">${order.business_name || '-'}</td>
                        <td class="craftsman-col"><span class="badge bg-info text-dark border">${order.craftsman_code || '-'}</span></td>
                        <td class="craftsman-col text-muted">${order.craftsman_name || '-'}</td>`;
                } else {
                    html += `
                        <td class="bp-col" style="display:none;"><span class="badge bg-light text-dark border">${order.bp_code || '-'}</span></td>
                        <td class="business-col" style="display:none;">${order.business_name || '-'}</td>
                        <td class="craftsman-col" style="display:none;"><span class="badge bg-info text-dark border">${order.craftsman_code || '-'}</span></td>
                        <td class="craftsman-col text-muted" style="display:none;">${order.craftsman_name || '-'}</td>`;
                }

                html += `
                        <td class="text-center">${order.qty ?? '-'}</td>
                        <td class="text-center fw-medium">${order.weight !== undefined ? parseFloat(order.weight).toFixed(2) : '0.00'}</td>
                        <td class="text-center">${order.due_date || '-'}</td>
                        <td class="text-center overdue-col text-danger fw-bold" style="${isOverdue ? '' : 'display:none;'}">${order.overdue_days || 0} days</td>
                    </tr>`;
                
                body.insertAdjacentHTML('beforeend', html);
            });
        }
        
        new bootstrap.Modal(document.getElementById('ordersListModal')).show();
    }

    // --- Helper function for cleaner print windows ---
    function openReportPrintWindow(title, tableHeaderHtml, tableBodyHtml) {
        const printWindow = window.open('', '_blank');
        const printDocument = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>${title}</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; padding: 20px; }
                    .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                    .table th, .table td { border: 1px solid #dee2e6; padding: 6px 10px; font-size: 11px; vertical-align: middle; }
                    .table th { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; }
                    .text-danger { color: #dc3545 !important; }
                    .text-success { color: #198754 !important; }
                    .text-primary { color: #0d6efd !important; }
                    .text-center { text-align: center; }
                    @media print {
                        body { margin: 0; padding: 10px; -webkit-print-color-adjust: exact; }
                    }
                </style>
            </head>
            <body>
                <h4 class="text-center mb-4">${title}</h4>
                <table class="table table-bordered">
                    <thead>${tableHeaderHtml}</thead>
                    <tbody>${tableBodyHtml}</tbody>
                </table>
                <script>
                    window.onload = function() { 
                        window.print(); 
                        window.close(); 
                    };
                <\/script>
            </body>
            </html>
        `;
        printWindow.document.open();
        printWindow.document.write(printDocument);
        printWindow.document.close();
    }

    // --- Craftsman Table Print ---
    let printMode = 'all';

    function openPrintColumnModal(mode) {
        printMode = mode;
        if (mode === 'selected') {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            if (checked.length === 0) {
                alert('Please select at least one craftsman to print.');
                return;
            }
        }
        new bootstrap.Modal(document.getElementById('printFieldsModal')).show();
    }

    function executeCustomPrint() {
        const columnMap = {
            1: 'Craftsman Name',
            2: 'Code',
            3: 'Total Weight',
            4: 'WO In Process',
            5: 'WO Completed',
            6: 'WO For Approval',
            7: 'WO Overdue',
            8: 'PO In Process',
            9: 'PO Completed',
            10: 'PO For Approval',
            11: 'PO Overdue'
        };

        const activeIndices = [];
        document.querySelectorAll('.print-col-check:checked').forEach(cb => {
            activeIndices.push(parseInt(cb.value));
        });

        let rowsToPrint = [];
        if (printMode === 'selected') {
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            rowsToPrint = Array.from(checkboxes).map(cb => cb.closest('tr'));
        } else {
            rowsToPrint = Array.from(document.querySelectorAll('.data-row'));
        }

        if (rowsToPrint.length === 0) {
            alert('No data available to print.');
            return;
        }

        let theadHtml = '<tr>';
        activeIndices.forEach(idx => {
            theadHtml += `<th class="text-center">${columnMap[idx]}</th>`;
        });
        theadHtml += '</tr>';

        let tbodyHtml = '';
        rowsToPrint.forEach(row => {
            const cells = row.querySelectorAll('td');
            tbodyHtml += '<tr>';
            activeIndices.forEach(idx => {
                const cell = cells[idx];
                const text = cell ? cell.innerText.trim() : '-';
                tbodyHtml += `<td class="${cell ? cell.className : ''}">${text}</td>`;
            });
            tbodyHtml += '</tr>';
        });

        bootstrap.Modal.getInstance(document.getElementById('printFieldsModal')).hide();
        openReportPrintWindow('Craftsman Details Report', theadHtml, tbodyHtml);
    }

    // --- Clients Table Print ---
    let clientPrintMode = 'all';

    function openClientPrintModal(mode) {
        clientPrintMode = mode;
        if (mode === 'selected') {
            const checked = document.querySelectorAll('.client-row-checkbox:checked');
            if (checked.length === 0) {
                alert('Please select at least one client to print.');
                return;
            }
        }
        new bootstrap.Modal(document.getElementById('printClientFieldsModal')).show();
    }

    function executeClientCustomPrint() {
        const clientColumnMap = {
            1: 'Business Partner',
            2: 'BP Code',
            3: 'New (C | W)',
            4: 'Process (C | W)',
            5: 'For Approval (C | W)',
            6: 'Overdue (C | W)',
            7: 'Rejected (C | W)',
            8: 'Completed (C | W)',
            9: 'Total Orders'
        };

        const activeIndices = [];
        document.querySelectorAll('.client-print-col-check:checked').forEach(cb => {
            activeIndices.push(parseInt(cb.value));
        });

        let rowsToPrint = [];
        if (clientPrintMode === 'selected') {
            const checkboxes = document.querySelectorAll('.client-row-checkbox:checked');
            rowsToPrint = Array.from(checkboxes).map(cb => cb.closest('tr'));
        } else {
            rowsToPrint = Array.from(document.querySelectorAll('.client-data-row'));
        }

        if (rowsToPrint.length === 0) {
            alert('No clients available to print.');
            return;
        }

        let theadHtml = '<tr>';
        activeIndices.forEach(idx => {
            theadHtml += `<th class="text-center">${clientColumnMap[idx]}</th>`;
        });
        theadHtml += '</tr>';

        let tbodyHtml = '';
        rowsToPrint.forEach(row => {
            const cells = row.querySelectorAll('td');
            tbodyHtml += '<tr>';
            activeIndices.forEach(idx => {
                const cell = cells[idx];
                const text = cell ? cell.innerText.trim() : '-';
                tbodyHtml += `<td class="${cell ? cell.className : ''}">${text}</td>`;
            });
            tbodyHtml += '</tr>';
        });

        bootstrap.Modal.getInstance(document.getElementById('printClientFieldsModal')).hide();
        openReportPrintWindow('Top Picks Clients Report (Work Orders)', theadHtml, tbodyHtml);
    }

    // --- Order Detail Pop-Up Print ---
    function openOrderFieldsModal() {
        const checkboxes = document.querySelectorAll('.modal-row-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select at least one order to print.');
            return;
        }

        const title = document.getElementById('ordersListModalTitle').innerText;
        const isOverdue = title.toLowerCase().includes('overdue');
        const isPO = currentOrderType === 'po';

        document.getElementById('fieldBpCodeContainer').style.display = isPO ? 'none' : 'block';
        document.getElementById('fieldBusinessContainer').style.display = isPO ? 'none' : 'block';
        document.getElementById('fieldCraftsmanCodeContainer').style.display = isPO ? 'none' : 'block';
        document.getElementById('fieldCraftsmanNameContainer').style.display = isPO ? 'none' : 'block';
        document.getElementById('fieldOverdueContainer').style.display = isOverdue ? 'block' : 'none';

        if (isPO) {
            document.getElementById('ordColBpCode').checked = false;
            document.getElementById('ordColBusiness').checked = false;
            document.getElementById('ordColCraftsmanCode').checked = false;
            document.getElementById('ordColCraftsmanName').checked = false;
        }

        new bootstrap.Modal(document.getElementById('orderPrintFieldsModal')).show();
    }

    function executeModalCustomPrint() {
        const isPO = currentOrderType === 'po';
        
        // Exact 1-to-1 matching with td indices in #modalOrdersTable
        const orderColumnMap = {
            1: 'Order Number',
            2: 'BP Code',
            3: 'Business Name',
            4: 'Craftsman Code',
            5: 'Craftsman Name',
            6: 'Quantity',
            7: 'Weight (g)',
            8: 'Due Date',
            9: 'Overdue Days'
        };

        const activeIndices = [];
        document.querySelectorAll('.order-col-check:checked').forEach(cb => {
            const val = parseInt(cb.value);
            // Skip PO columns if PO mode is active
            if (isPO && [2, 3, 4, 5].includes(val)) {
                return;
            }
            activeIndices.push(val);
        });

        const checkboxes = document.querySelectorAll('.modal-row-checkbox:checked');
        const rowsToPrint = Array.from(checkboxes).map(cb => cb.closest('tr'));
        const title = document.getElementById('ordersListModalTitle').innerText;

        let theadHtml = '<tr>';
        activeIndices.forEach(idx => {
            theadHtml += `<th class="text-center">${orderColumnMap[idx]}</th>`;
        });
        theadHtml += '</tr>';

        let tbodyHtml = '';
        rowsToPrint.forEach(row => {
            const cells = row.querySelectorAll('td');
            tbodyHtml += '<tr>';
            activeIndices.forEach(idx => {
                const cell = cells[idx];
                const text = cell ? cell.innerText.trim() : '-';
                tbodyHtml += `<td class="${cell ? cell.className.replace('bp-col', '').replace('business-col', '').replace('craftsman-col', '') : ''}">${text}</td>`;
            });
            tbodyHtml += '</tr>';
        });

        bootstrap.Modal.getInstance(document.getElementById('orderPrintFieldsModal')).hide();
        openReportPrintWindow(title, theadHtml, tbodyHtml);
    }
</script>
@endsection