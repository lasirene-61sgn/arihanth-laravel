@extends('admin.layouts.app')

@section('title', 'Details ALL Craftsman & Clients')

@section('content')
<style>
    .kpi-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid #edf2f9;
        border-radius: 12px;
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.08) !important;
    }
    .status-badge-soft-primary { background-color: #e7f1ff; color: #0d6efd; }
    .status-badge-soft-info { background-color: #e8f7fa; color: #0dcaf0; }
</style>

<div class="container-fluid py-4">
    <!-- Top Action Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Craftsman & Client Workflows</h4>
            <p class="text-muted small mb-0">Overview of Craftsman allocations, client orders, and performance metrics</p>
        </div>
    </div>

    <!-- KPI CARDS -->
    <div class="row g-3 mb-4">
        <!-- Top Picks Craftsmen Card -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card kpi-card shadow-sm h-100 bg-white" onclick="toggleDetailsTable()" style="cursor: pointer;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="rounded-3 p-2 status-badge-soft-primary">
                            <i class="bi bi-people-fill fs-5"></i>
                        </div>
                        <i class="bi bi-chevron-down text-muted" id="toggleIcon"></i>
                    </div>
                    <h3 class="fw-bold my-1 text-dark">{{ count($craftsmenData) }}</h3>
                    <p class="small text-muted mb-0 fw-semibold">TOP PICKS CRAFTSMEN (CLICK TO VIEW)</p>
                </div>
            </div>
        </div>

        <!-- Top Picks Clients Card -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card kpi-card shadow-sm h-100 bg-white" onclick="toggleClientsTable()" style="cursor: pointer;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="rounded-3 p-2 status-badge-soft-info">
                            <i class="bi bi-building fs-5"></i>
                        </div>
                        <i class="bi bi-chevron-down text-muted" id="toggleClientsIcon"></i>
                    </div>
                    <h3 class="fw-bold my-1 text-dark">{{ count($topPicksClientsFull) }}</h3>
                    <p class="small text-muted mb-0 fw-semibold">TOP PICKS CLIENTS (CLICK TO VIEW)</p>
                </div>
            </div>
        </div>

        <!-- Craftsman Designs Card -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card kpi-card shadow-sm h-100 bg-white" onclick="toggleCraftsmanDesignsTable()" style="cursor: pointer;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="bg-success-subtle text-success rounded-3 p-2">
                            <i class="bi bi-palette fs-5"></i>
                        </div>
                        <i class="bi bi-chevron-down text-muted" id="toggleCraftsmanDesignsIcon"></i>
                    </div>
                    <h3 class="fw-bold mb-1">{{ count($craftsmanDesignStats) }}</h3>
                    <p class="small text-muted mb-0 fw-semibold">CRAFTSMAN DESIGNS (CLICK TO VIEW)</p>
                </div>
            </div>
        </div>

        <!-- Buyer Designs Card -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card kpi-card shadow-sm h-100 bg-white" onclick="toggleBuyerDesignsTable()" style="cursor: pointer;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="bg-danger-subtle text-danger rounded-3 p-2">
                            <i class="bi bi-image fs-5"></i>
                        </div>
                        <i class="bi bi-chevron-down text-muted" id="toggleBuyerDesignsIcon"></i>
                    </div>
                    <h3 class="fw-bold mb-1">{{ count($buyerDesignStats) }}</h3>
                    <p class="small text-muted mb-0 fw-semibold">BUYER DESIGNS (CLICK TO VIEW)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CRAFTSMEN DETAILS CONTAINER -->
    <div id="detailsTableContainer" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-dark mb-0">Craftsman Details Table</h5>
            <div>
                <button class="btn btn-outline-primary btn-sm px-3 shadow-sm rounded-pill me-2" onclick="printSelected()">
                    <i class="bi bi-printer me-1"></i> Print Selected
                </button>
                <button class="btn btn-primary btn-sm px-3 shadow-sm rounded-pill" onclick="printAllFiltered()">
                    <i class="bi bi-printer-fill me-1"></i> Print All
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card shadow-sm border-0 mb-4 rounded-3">
            <div class="card-body bg-light rounded-3 py-3">
                <form method="GET" action="{{ route('admin.details-all') }}" id="filterForm" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold mb-1">Status Workflow</label>
                        <select name="status" id="statusFilterSelect" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all" {{ ($status ?? 'all') == 'all' ? 'selected' : '' }}>All Statuses</option>
                            <option value="in_process" {{ ($status ?? '') == 'in_process' ? 'selected' : '' }}>In Process</option>
                            <option value="for_approval" {{ ($status ?? '') == 'for_approval' ? 'selected' : '' }}>For Approval</option>
                            <option value="completed" {{ ($status ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="overdue" {{ ($status ?? '') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold mb-1">Sort Metric</label>
                        <select name="sort_by" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="allocated" {{ ($sortBy ?? '') == 'allocated' ? 'selected' : '' }}>Allocated Count</option>
                            <option value="in_process" {{ ($sortBy ?? '') == 'in_process' ? 'selected' : '' }}>In Process</option>
                            <option value="completed" {{ ($sortBy ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="overdue" {{ ($sortBy ?? '') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="total_weight" {{ ($sortBy ?? '') == 'total_weight' ? 'selected' : '' }}>Total Weight</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted small fw-bold mb-1">Sort Order</label>
                        <select name="sort_order" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="desc" {{ ($sortOrder ?? 'desc') == 'desc' ? 'selected' : '' }}>Descending (High to Low)</option>
                            <option value="asc" {{ ($sortOrder ?? '') == 'asc' ? 'selected' : '' }}>Ascending (Low to High)</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Craftsman Breakdown Table -->
        <div class="card shadow-sm border-0 rounded-3 overflow-hidden mb-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="detailsTable">
                        <thead class="bg-light border-bottom">
                            <tr>
                                <th rowspan="2" class="text-center border-end" style="width: 48px; vertical-align: middle;">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </th>
                                <th rowspan="2" class="border-end ps-3" style="vertical-align: middle;">Craftsman Profile</th>
                                <th rowspan="2" class="text-center border-end" style="vertical-align: middle;">Code</th>
                                <th colspan="3" class="text-center bg-primary text-white border-end py-2" style="font-size: 0.8rem; letter-spacing: 0.5px;">WORK ORDERS (WO)</th>
                                <th colspan="3" class="text-center bg-info text-white py-2" style="font-size: 0.8rem; letter-spacing: 0.5px;">PURCHASE ORDERS (PO)</th>
                            </tr>
                            <tr class="bg-white">
                                <th class="text-center py-2 border-end text-muted small" style="font-size: 0.75rem;">IN PROCESS (C | Wt)</th>
                                <th class="text-center py-2 border-end text-muted small" style="font-size: 0.75rem;">FOR APPROVAL (C | Wt)</th>
                                <th class="text-center py-2 text-danger border-end small" style="font-size: 0.75rem;">OVERDUE (C | Wt)</th>
                                <th class="text-center py-2 border-end text-muted small" style="font-size: 0.75rem;">IN PROCESS (C | Wt)</th>
                                <th class="text-center py-2 border-end text-muted small" style="font-size: 0.75rem;">FOR APPROVAL (C | Wt)</th>
                                <th class="text-center py-2 text-danger small" style="font-size: 0.75rem;">OVERDUE (C | Wt)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($craftsmenData as $stat)
                            <tr class="data-row">
                                <td class="text-center border-end">
                                    <input class="form-check-input row-checkbox" type="checkbox" value="{{ $stat['code'] }}">
                                </td>
                                <td class="border-end ps-3">
                                    <span class="fw-semibold text-dark">{{ $stat['name'] }}</span>
                                </td>
                                <td class="text-center border-end">
                                    <span class="badge bg-light text-dark border">{{ $stat['code'] }}</span>
                                </td>

                                {{-- WO In Process --}}
                                <td class="text-center border-end">
                                    @if(($stat['wo']['in_process']['count'] ?? 0) > 0)
                                        <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none p-0 fw-semibold" 
                                            onclick="showOrdersList(this, 'wo')" 
                                            data-title="In Process (WO) - {{ $stat['name'] }}" 
                                            data-orders='@json($stat['wo']['in_process']['orders'] ?? [])'>
                                            {{ $stat['wo']['in_process']['count'] }} | {{ number_format($stat['wo']['in_process']['weight'], 2) }}
                                        </button>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                {{-- WO For Approval --}}
                                <td class="text-center border-end">
                                    @if(($stat['wo']['for_approval']['count'] ?? 0) > 0)
                                        <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold" style="color: #6f42c1;"
                                            onclick="showOrdersList(this, 'wo')" 
                                            data-title="For Approval (WO) - {{ $stat['name'] }}" 
                                            data-orders='@json($stat['wo']['for_approval']['orders'] ?? [])'>
                                            {{ $stat['wo']['for_approval']['count'] }} | {{ number_format($stat['wo']['for_approval']['weight'], 2) }}
                                        </button>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                {{-- WO Overdue --}}
                                <td class="text-center border-end">
                                    @if(($stat['wo']['overdue']['count'] ?? 0) > 0)
                                        <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0 fw-bold" 
                                            onclick="showOrdersList(this, 'wo')" 
                                            data-title="Overdue (WO) - {{ $stat['name'] }}" 
                                            data-orders='@json($stat['wo']['overdue']['orders'] ?? [])'>
                                            {{ $stat['wo']['overdue']['count'] }} | {{ number_format($stat['wo']['overdue']['weight'], 2) }}
                                        </button>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                {{-- PO In Process --}}
                                <td class="text-center border-end">
                                    @if(($stat['po']['in_process']['count'] ?? 0) > 0)
                                        <button type="button" class="btn btn-sm btn-link text-info text-decoration-none p-0 fw-semibold" 
                                            onclick="showOrdersList(this, 'po')" 
                                            data-title="In Process (PO) - {{ $stat['name'] }}" 
                                            data-orders='@json($stat['po']['in_process']['orders'] ?? [])'>
                                            {{ $stat['po']['in_process']['count'] }} | {{ number_format($stat['po']['in_process']['weight'], 2) }}
                                        </button>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                {{-- PO For Approval --}}
                                <td class="text-center border-end">
                                    @if(($stat['po']['for_approval']['count'] ?? 0) > 0)
                                        <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold" style="color: #6f42c1;"
                                            onclick="showOrdersList(this, 'po')" 
                                            data-title="For Approval (PO) - {{ $stat['name'] }}" 
                                            data-orders='@json($stat['po']['for_approval']['orders'] ?? [])'>
                                            {{ $stat['po']['for_approval']['count'] }} | {{ number_format($stat['po']['for_approval']['weight'], 2) }}
                                        </button>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                {{-- PO Overdue --}}
                                <td class="text-center">
                                    @if(($stat['po']['overdue']['count'] ?? 0) > 0)
                                        <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0 fw-bold" 
                                            onclick="showOrdersList(this, 'po')" 
                                            data-title="Overdue (PO) - {{ $stat['name'] }}" 
                                            data-orders='@json($stat['po']['overdue']['orders'] ?? [])'>
                                            {{ $stat['po']['overdue']['count'] }} | {{ number_format($stat['po']['overdue']['weight'], 2) }}
                                        </button>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    No craftsman records found matching the criteria.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- CLIENTS DETAILS CONTAINER -->
    <div id="clientsTableContainer" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-dark mb-0">Top Picks Clients Table</h5>
            <div>
                <button class="btn btn-outline-primary btn-sm px-3 shadow-sm rounded-pill me-2" onclick="printSelectedClients()">
                    <i class="bi bi-printer me-1"></i> Print Selected Clients
                </button>
                <button class="btn btn-primary btn-sm px-3 shadow-sm rounded-pill" onclick="printAllClients()">
                    <i class="bi bi-printer-fill me-1"></i> Print All Clients
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-3 overflow-hidden mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle text-center mb-0" id="clientsTable">
                        <thead class="bg-light">
                            <tr>
                                <th rowspan="2" class="align-middle border-0" style="width: 48px;">
                                    <input class="form-check-input" type="checkbox" id="selectAllClients">
                                </th>
                                <th rowspan="2" class="align-middle border-0 text-start ps-3">{{ __('messages.business_partner') }}</th>
                                <th rowspan="2" class="align-middle border-0">{{ __('messages.bp_code') }}</th>
                                <th colspan="6" class="border-0 bg-secondary-subtle">WORK ORDERS (WA)</th>
                                <th rowspan="2" class="align-middle border-0 bg-light">Total Orders</th>
                            </tr>
                            <tr class="bg-light">
                                <th class="border-0 fw-bold">NEW</th>
                                <th class="border-0 fw-bold">PROCESS</th>
                                <th class="border-0 fw-bold">FOR APPROVAL</th>
                                <th class="border-0 fw-bold text-danger">OVERDUE</th>
                                <th class="border-0 fw-bold text-danger">REJECTED</th>
                                <th class="border-0 fw-bold text-success">COMPLETED</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPicksClientsFull as $code => $stat)
                            <tr class="client-data-row">
                                <td>
                                    <input class="form-check-input client-row-checkbox" type="checkbox" value="{{ $code }}">
                                </td>
                                <td class="text-start ps-3">
                                    <div class="fw-semibold text-dark">{{ $stat['name'] }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $code }}</span></td>
                                
                                {{-- Client Status Columns --}}
                                @foreach(['new' => 'text-primary', 'in_process' => 'text-primary', 'for_approval' => 'text-purple', 'overdue' => 'text-danger', 'rejected' => 'text-danger', 'completed' => 'text-success'] as $type => $textClass)
                                    <td>
                                        @if(isset($stat[$type]['weight']) && $stat[$type]['weight'] > 0)
                                            <button type="button" class="btn btn-sm btn-link {{ $textClass }} text-decoration-none p-0 fw-semibold" 
                                                onclick="showOrdersList(this, 'wo')" 
                                                data-title="{{ ucfirst(str_replace('_', ' ', $type)) }} Orders - {{ $stat['name'] }}" 
                                                data-orders='@json($stat[$type]['orders'] ?? [])'>
                                                {{ $stat[$type]['count'] }} | {{ number_format($stat[$type]['weight'], 2) }}
                                            </button>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                @endforeach

                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary fs-6">{{ $stat['orders'] ?? 0 }}</span>
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

    <!-- CRAFTSMAN DESIGNS CONTAINER -->
    <div id="craftsmanDesignsTableContainer" style="display: none;">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 fw-bold text-success"><i class="bi bi-palette me-2"></i>Craftsman Accepted Designs</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Craftsman</th>
                                <th>Code</th>
                                @foreach($craftsmanAllCategories as $cat)
                                    <th class="text-center">{{ $cat }}</th>
                                @endforeach
                                <th class="text-center">Total Accepted</th>
                                <th>Last Accepted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalCraftsmanAccepted = 0;
                                $categoryTotals = array_fill_keys($craftsmanAllCategories, 0);
                            @endphp
                            @forelse($craftsmanDesignStats as $code => $stat)
                            <tr>
                                <td><div class="fw-semibold text-dark">{{ $stat['name'] }}</div></td>
                                <td><span class="badge bg-secondary">{{ $code }}</span></td>
                                @foreach($craftsmanAllCategories as $cat)
                                    <td class="text-center">
                                        @if(isset($stat['categories'][$cat]))
                                            @php
                                                $categoryTotals[$cat] += $stat['categories'][$cat];
                                            @endphp
                                            <span class="text-decoration-underline text-success fw-bold" style="cursor: pointer; font-size:1.1rem;" onclick="showDesignsList(this, 'Craftsman', '{{ $cat }}')" data-title="Accepted Designs ({{ $cat }}) - {{ $stat['name'] }}" data-bpcode="{{ $code }}">
                                                {{ $stat['categories'][$cat] }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-center">
                                    @php
                                        $totalCraftsmanAccepted += $stat['total_accepted'];
                                    @endphp
                                    <span class="text-decoration-underline text-primary fw-bold" style="cursor: pointer; font-size:1.1rem;" onclick="showDesignsList(this, 'Craftsman')" data-title="Accepted Designs (All) - {{ $stat['name'] }}" data-bpcode="{{ $code }}">
                                        {{ $stat['total_accepted'] }}
                                    </span>
                                </td>
                                <td><span class="text-muted small"><i class="bi bi-clock me-1"></i>{{ $stat['last_accepted_date'] ?? '-' }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="{{ count($craftsmanAllCategories) + 4 }}" class="text-center py-4 text-muted">No accepted designs found.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="2" class="text-end">Total:</td>
                                @foreach($craftsmanAllCategories as $cat)
                                    <td class="text-center text-success">{{ $categoryTotals[$cat] ?? 0 }}</td>
                                @endforeach
                                <td class="text-center text-primary fs-5">{{ $totalCraftsmanAccepted }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- BUYER DESIGNS CONTAINER -->
    <div id="buyerDesignsTableContainer" style="display: none;">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 fw-bold text-danger"><i class="bi bi-image me-2"></i>Buyer Accepted Designs</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Buyer</th>
                                <th>Code</th>
                                @foreach($buyerAllCategories as $cat)
                                    <th class="text-center">{{ $cat }}</th>
                                @endforeach
                                <th class="text-center">Total Accepted</th>
                                <th>Last Accepted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalBuyerAccepted = 0;
                                $buyerCategoryTotals = array_fill_keys($buyerAllCategories, 0);
                            @endphp
                            @forelse($buyerDesignStats as $code => $stat)
                            <tr>
                                <td><div class="fw-semibold text-dark">{{ $stat['name'] }}</div></td>
                                <td><span class="badge bg-light text-dark border">{{ $code }}</span></td>
                                @foreach($buyerAllCategories as $cat)
                                    <td class="text-center">
                                        @if(isset($stat['categories'][$cat]))
                                            @php
                                                $buyerCategoryTotals[$cat] += $stat['categories'][$cat];
                                            @endphp
                                            <span class="text-decoration-underline text-danger fw-bold" style="cursor: pointer; font-size:1.1rem;" onclick="showDesignsList(this, 'Buyer', '{{ $cat }}')" data-title="Accepted Designs ({{ $cat }}) - {{ $stat['name'] }}" data-bpcode="{{ $code }}">
                                                {{ $stat['categories'][$cat] }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-center">
                                    @php
                                        $totalBuyerAccepted += $stat['total_accepted'];
                                    @endphp
                                    <span class="text-decoration-underline text-danger fw-bold" style="cursor: pointer; font-size:1.1rem;" onclick="showDesignsList(this, 'Buyer')" data-title="Accepted Designs (All) - {{ $stat['name'] }}" data-bpcode="{{ $code }}">
                                        {{ $stat['total_accepted'] }}
                                    </span>
                                </td>
                                <td><span class="text-muted small"><i class="bi bi-clock me-1"></i>{{ $stat['last_accepted_date'] ?? '-' }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="{{ count($buyerAllCategories) + 4 }}" class="text-center py-4 text-muted">No accepted designs found.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="2" class="text-end">Total:</td>
                                @foreach($buyerAllCategories as $cat)
                                    <td class="text-center text-danger">{{ $buyerCategoryTotals[$cat] ?? 0 }}</td>
                                @endforeach
                                <td class="text-center text-primary fs-5">{{ $totalBuyerAccepted }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ORDERS MODAL -->
<div class="modal fade" id="ordersListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="ordersListModalTitle">Order Items</h5>
                    <span class="text-muted small" id="ordersModalSubtitle">Detailed breakdown</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <div class="bg-light p-3 rounded-3 mb-3 border">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold small text-secondary"><i class="bi bi-sliders me-1"></i> Customize Print Columns:</span>
                        <div class="d-flex align-items-center gap-2">
                            <select id="ordersPerPage" class="form-select form-select-sm" style="width: auto;">
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="150">150</option>
                                <option value="200">200</option>
                                <option value="500">500</option>
                            </select>
                            <span class="small text-muted">per page</span>
                            
                            <button class="btn btn-outline-secondary btn-sm ms-2" id="ordersPrevPage">Prev</button>
                            <span class="small fw-bold" id="ordersPageInfo">Page 1 of 1</span>
                            <button class="btn btn-outline-secondary btn-sm" id="ordersNextPage">Next</button>
                            
                            <button class="btn btn-primary btn-sm px-3 shadow-sm rounded-pill ms-3" onclick="printModalSelected()">
                                <i class="bi bi-printer-fill me-1"></i> Print Selected
                            </button>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-3 small">
                        <div class="form-check">
                            <input class="form-check-input print-col-toggle" type="checkbox" id="printCol_number" value="col-order-num" checked>
                            <label class="form-check-label" for="printCol_number">Order Number</label>
                        </div>
                        <div class="form-check bp-print-toggle">
                            <input class="form-check-input print-col-toggle" type="checkbox" id="printCol_bp" value="col-bp" checked>
                            <label class="form-check-label" for="printCol_bp">BP Code</label>
                        </div>
                        <div class="form-check bp-print-toggle">
                            <input class="form-check-input print-col-toggle" type="checkbox" id="printCol_business" value="col-business" checked>
                            <label class="form-check-label" for="printCol_business">Business Name</label>
                        </div>
                        <div class="form-check craftsman-print-toggle">
                            <input class="form-check-input print-col-toggle" type="checkbox" id="printCol_craftsman_code" value="col-craftsman-code" checked>
                            <label class="form-check-label" for="printCol_craftsman_code">Craftsman Code</label>
                        </div>
                        <div class="form-check craftsman-print-toggle">
                            <input class="form-check-input print-col-toggle" type="checkbox" id="printCol_craftsman_name" value="col-craftsman-name" checked>
                            <label class="form-check-label" for="printCol_craftsman_name">Craftsman Name</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input print-col-toggle" type="checkbox" id="printCol_qty" value="col-qty" checked>
                            <label class="form-check-label" for="printCol_qty">Quantity</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input print-col-toggle" type="checkbox" id="printCol_weight" value="col-weight" checked>
                            <label class="form-check-label" for="printCol_weight">Weight</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input print-col-toggle" type="checkbox" id="printCol_due" value="col-due" checked>
                            <label class="form-check-label" for="printCol_due">Due Date</label>
                        </div>
                        <div class="form-check overdue-print-toggle">
                            <input class="form-check-input print-col-toggle" type="checkbox" id="printCol_overdue" value="col-overdue" checked>
                            <label class="form-check-label" for="printCol_overdue">Overdue Days</label>
                        </div>
                    </div>
                </div>

                <div class="table-responsive border rounded">
                    <table class="table table-hover align-middle mb-0" id="modalOrdersTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width: 40px;">
                                    <input class="form-check-input" type="checkbox" id="modalSelectAll" checked>
                                </th>
                                <th class="col-order-num">Order Number</th>
                                <th class="bp-col col-bp">BP Code</th>
                                <th class="business-col col-business">Business Name</th>
                                <th class="craftsman-col col-craftsman-code">Craftsman Code</th>
                                <th class="craftsman-col col-craftsman-name">Craftsman Name</th>
                                <th class="text-center col-qty">Quantity</th>
                                <th class="text-center col-weight">Weight</th>
                                <th class="text-center col-due">Due Date</th>
                                <th class="text-center overdue-col col-overdue" style="display: none;">Overdue Days</th>
                            </tr>
                        </thead>
                        <tbody id="ordersListModalBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DESIGNS MODAL -->
<div class="modal fade" id="designsListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="designsListModalTitle">Accepted Designs</h5>
                    <span class="text-muted small">Design details and remarks</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <select id="designsPerPage" class="form-select form-select-sm me-2" style="width: auto;">
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="150">150</option>
                            <option value="200">200</option>
                            <option value="500">500</option>
                        </select>
                        <span class="small text-muted me-3">per page</span>
                        
                        <button class="btn btn-outline-secondary btn-sm" id="designsPrevPage">Prev</button>
                        <span class="mx-2 small fw-bold" id="designsPageInfo">Page 1 of 1</span>
                        <button class="btn btn-outline-secondary btn-sm" id="designsNextPage">Next</button>
                    </div>
                    <button class="btn btn-primary btn-sm px-3 shadow-sm rounded-pill" onclick="printSelectedDesigns()">
                        <i class="bi bi-printer-fill me-1"></i> Print Selected
                    </button>
                </div>
                <div class="table-responsive border rounded">
                    <table class="table table-hover align-middle mb-0" id="modalDesignsTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width: 40px;">
                                    <input class="form-check-input" type="checkbox" id="modalDesignsSelectAll">
                                </th>
                                <th class="text-center">Image</th>
                                <th>Design Code</th>
                                <th>Design Name</th>
                                <th>Category</th>
                                <th class="text-center">Weight From (g)</th>
                                <th>Notes / Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="designsListModalBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let ordersModalInstance = null;
    let designsModalInstance = null;

    document.addEventListener('DOMContentLoaded', function() {
        ordersModalInstance = new bootstrap.Modal(document.getElementById('ordersListModal'));
        designsModalInstance = new bootstrap.Modal(document.getElementById('designsListModal'));
    });

    // Helper to toggle container visibility and icon state
    function toggleSection(containerId, iconId) {
        const sections = [
            { id: 'detailsTableContainer', icon: 'toggleIcon' },
            { id: 'clientsTableContainer', icon: 'toggleClientsIcon' },
            { id: 'craftsmanDesignsTableContainer', icon: 'toggleCraftsmanDesignsIcon' },
            { id: 'buyerDesignsTableContainer', icon: 'toggleBuyerDesignsIcon' }
        ];

        sections.forEach(sec => {
            const el = document.getElementById(sec.id);
            const ic = document.getElementById(sec.icon);
            if (!el) return;

            if (sec.id === containerId) {
                const isCurrentlyHidden = el.style.display === 'none' || el.style.display === '';
                el.style.display = isCurrentlyHidden ? 'block' : 'none';
                if (ic) {
                    ic.classList.toggle('bi-chevron-down', !isCurrentlyHidden);
                    ic.classList.toggle('bi-chevron-up', isCurrentlyHidden);
                }
            } else {
                el.style.display = 'none';
                if (ic) {
                    ic.classList.remove('bi-chevron-up');
                    ic.classList.add('bi-chevron-down');
                }
            }
        });
    }

    function toggleDetailsTable() { toggleSection('detailsTableContainer', 'toggleIcon'); }
    function toggleClientsTable() { toggleSection('clientsTableContainer', 'toggleClientsIcon'); }
    function toggleCraftsmanDesignsTable() { toggleSection('craftsmanDesignsTableContainer', 'toggleCraftsmanDesignsIcon'); }
    function toggleBuyerDesignsTable() { toggleSection('buyerDesignsTableContainer', 'toggleBuyerDesignsIcon'); }

    // Checkbox toggles
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('selectAllClients')?.addEventListener('change', function() {
        document.querySelectorAll('.client-row-checkbox').forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('modalSelectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.modal-row-checkbox').forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('modalDesignsSelectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.modal-design-checkbox').forEach(cb => cb.checked = this.checked);
    });

    // Orders Modal State
    let currentOrderType = 'wo';
    let currentModalOrders = [];
    let currentOrdersPage = 1;
    let ordersPerPage = 50;

    function showOrdersList(el, type) {
        currentOrderType = type;
        const title = el.getAttribute('data-title') || 'Orders List';
        const orders = JSON.parse(el.getAttribute('data-orders') || '[]');
        currentModalOrders = orders;
        
        document.getElementById('ordersListModalTitle').innerText = title;
        document.getElementById('ordersModalSubtitle').innerText = `${orders.length} total item(s) found`;
        
        const isPO = (type === 'po');
        const isOverdue = title.toLowerCase().includes('overdue');

        document.querySelectorAll('.bp-col, .business-col, .craftsman-col, .bp-print-toggle, .craftsman-print-toggle')
            .forEach(col => col.style.display = isPO ? 'none' : '');

        document.querySelectorAll('.overdue-col, .overdue-print-toggle')
            .forEach(col => col.style.display = isOverdue ? '' : 'none');

        currentOrdersPage = 1;
        renderOrdersPage();
        ordersModalInstance.show();
    }

    function renderOrdersPage() {
        const body = document.getElementById('ordersListModalBody');
        body.innerHTML = '';
        
        const totalPages = Math.ceil(currentModalOrders.length / ordersPerPage) || 1;
        document.getElementById('ordersPageInfo').innerText = `Page ${currentOrdersPage} of ${totalPages}`;
        document.getElementById('ordersPrevPage').disabled = (currentOrdersPage === 1);
        document.getElementById('ordersNextPage').disabled = (currentOrdersPage === totalPages);

        if (currentModalOrders.length === 0) {
            body.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">No orders found.</td></tr>';
            return;
        }

        const startIndex = (currentOrdersPage - 1) * ordersPerPage;
        const pageItems = currentModalOrders.slice(startIndex, startIndex + ordersPerPage);
        const title = document.getElementById('ordersListModalTitle').innerText;
        const isOverdue = title.toLowerCase().includes('overdue');
        const isPO = (currentOrderType === 'po');

        let rowsHtml = '';
        pageItems.forEach(order => {
            rowsHtml += `
                <tr class="modal-data-row">
                    <td class="text-center">
                        <input class="form-check-input modal-row-checkbox" type="checkbox" checked>
                    </td>
                    <td class="fw-semibold text-dark col-order-num">${order.number || '-'}</td>
                    <td class="bp-col col-bp" style="${isPO ? 'display:none;' : ''}"><span class="badge bg-light text-dark border">${order.bp_code || '-'}</span></td>
                    <td class="business-col col-business text-muted" style="${isPO ? 'display:none;' : ''}">${order.business_name || '-'}</td>
                    <td class="craftsman-col col-craftsman-code" style="${isPO ? 'display:none;' : ''}"><span class="badge bg-info text-dark border">${order.craftsman_code || '-'}</span></td>
                    <td class="craftsman-col col-craftsman-name text-muted" style="${isPO ? 'display:none;' : ''}">${order.craftsman_name || '-'}</td>
                    <td class="text-center col-qty">${order.qty || 0}</td>
                    <td class="text-center fw-medium col-weight">${parseFloat(order.weight || 0).toFixed(2)}</td>
                    <td class="text-center col-due">${order.due_date || '-'}</td>
                    <td class="text-center overdue-col col-overdue text-danger fw-bold" style="${isOverdue ? '' : 'display:none;'}">${order.overdue_days || 0} days</td>
                </tr>`;
        });
        body.innerHTML = rowsHtml;
    }

    document.getElementById('ordersPerPage')?.addEventListener('change', function() {
        ordersPerPage = parseInt(this.value);
        currentOrdersPage = 1;
        renderOrdersPage();
    });

    document.getElementById('ordersPrevPage')?.addEventListener('click', function() {
        if (currentOrdersPage > 1) {
            currentOrdersPage--;
            renderOrdersPage();
        }
    });

    document.getElementById('ordersNextPage')?.addEventListener('click', function() {
        const totalPages = Math.ceil(currentModalOrders.length / ordersPerPage) || 1;
        if (currentOrdersPage < totalPages) {
            currentOrdersPage++;
            renderOrdersPage();
        }
    });

    // Print Modal Orders
    function printModalSelected() {
        const checkboxes = document.querySelectorAll('.modal-row-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select at least one order to print.');
            return;
        }

        const title = document.getElementById('ordersListModalTitle').innerText;
        const isOverdue = title.toLowerCase().includes('overdue');
        const isPO = currentOrderType === 'po';

        const showNum = document.getElementById('printCol_number').checked;
        const showBP = !isPO && document.getElementById('printCol_bp').checked;
        const showBusiness = !isPO && document.getElementById('printCol_business').checked;
        const showCraftsmanCode = !isPO && document.getElementById('printCol_craftsman_code').checked;
        const showCraftsmanName = !isPO && document.getElementById('printCol_craftsman_name').checked;
        const showQty = document.getElementById('printCol_qty').checked;
        const showWeight = document.getElementById('printCol_weight').checked;
        const showDue = document.getElementById('printCol_due').checked;
        const showOverdueDays = isOverdue && document.getElementById('printCol_overdue').checked;

        let headersHtml = '';
        if (showNum) headersHtml += '<th>Order Number</th>';
        if (showBP) headersHtml += '<th>BP Code</th>';
        if (showBusiness) headersHtml += '<th>Business Name</th>';
        if (showCraftsmanCode) headersHtml += '<th>Craftsman Code</th>';
        if (showCraftsmanName) headersHtml += '<th>Craftsman Name</th>';
        if (showQty) headersHtml += '<th class="text-center">Quantity</th>';
        if (showWeight) headersHtml += '<th class="text-center">Weight</th>';
        if (showDue) headersHtml += '<th class="text-center">Due Date</th>';
        if (showOverdueDays) headersHtml += '<th class="text-center">Overdue Days</th>';

        let rowsHtml = '';
        checkboxes.forEach(cb => {
            const row = cb.closest('tr');
            rowsHtml += '<tr>';
            if (showNum) rowsHtml += `<td>${row.querySelector('.col-order-num')?.innerText || ''}</td>`;
            if (showBP) rowsHtml += `<td>${row.querySelector('.col-bp')?.innerText || ''}</td>`;
            if (showBusiness) rowsHtml += `<td>${row.querySelector('.col-business')?.innerText || ''}</td>`;
            if (showCraftsmanCode) rowsHtml += `<td>${row.querySelector('.col-craftsman-code')?.innerText || ''}</td>`;
            if (showCraftsmanName) rowsHtml += `<td>${row.querySelector('.col-craftsman-name')?.innerText || ''}</td>`;
            if (showQty) rowsHtml += `<td class="text-center">${row.querySelector('.col-qty')?.innerText || ''}</td>`;
            if (showWeight) rowsHtml += `<td class="text-center">${row.querySelector('.col-weight')?.innerText || ''}</td>`;
            if (showDue) rowsHtml += `<td class="text-center">${row.querySelector('.col-due')?.innerText || ''}</td>`;
            if (showOverdueDays) rowsHtml += `<td class="text-center text-danger font-bold">${row.querySelector('.col-overdue')?.innerText || ''}</td>`;
            rowsHtml += '</tr>';
        });

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print - ${title}</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    @media print {
                        body { -webkit-print-color-adjust: exact; margin: 15mm; font-family: system-ui, sans-serif; }
                        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                        .table th, .table td { border: 1px solid #dee2e6; padding: 6px 10px; font-size: 11px; }
                        .text-danger { color: #dc3545 !important; }
                        .text-center { text-align: center; }
                        .header-title { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
                    }
                </style>
            </head>
            <body>
                <div class="text-center mb-3">
                    <div class="header-title">${title}</div>
                    <div class="text-muted small">Generated on ${new Date().toLocaleDateString()}</div>
                </div>
                <table class="table table-bordered">
                    <thead class="table-light"><tr>${headersHtml}</tr></thead>
                    <tbody>${rowsHtml}</tbody>
                </table>
                <script>window.onload = function() { window.print(); window.close(); }<\/script>
            </body>
            </html>`);
        printWindow.document.close();
    }

    // Designs Modal State & Logic
    let currentDesigns = [];
    let currentDesignsPage = 1;
    let designsPerPage = 50;

    function renderDesignsPage() {
        const body = document.getElementById('designsListModalBody');
        body.innerHTML = '';
        
        const totalPages = Math.ceil(currentDesigns.length / designsPerPage) || 1;
        document.getElementById('designsPageInfo').innerText = `Page ${currentDesignsPage} of ${totalPages}`;
        document.getElementById('designsPrevPage').disabled = (currentDesignsPage === 1);
        document.getElementById('designsNextPage').disabled = (currentDesignsPage === totalPages);

        if (currentDesigns.length === 0) {
            body.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No designs found.</td></tr>';
            return;
        }

        const startIndex = (currentDesignsPage - 1) * designsPerPage;
        const pageItems = currentDesigns.slice(startIndex, startIndex + designsPerPage);

        let rowsHtml = '';
        pageItems.forEach(design => {
            rowsHtml += `
                <tr class="modal-design-row">
                    <td class="text-center" style="vertical-align: middle;">
                        <input class="form-check-input modal-design-checkbox" type="checkbox">
                    </td>
                    <td class="text-center">
                        <img src="${design.image || ''}" alt="Design" style="height: 60px; object-fit: contain; border-radius: 4px; border: 1px solid #ddd;" class="design-img-preview" onerror="this.src='';">
                    </td>
                    <td style="vertical-align: middle;">${design.design_code || '-'}</td>
                    <td style="vertical-align: middle;">${design.design_name || '-'}</td>
                    <td style="vertical-align: middle;"><span class="badge bg-secondary-subtle text-secondary">${design.category || '-'}</span></td>
                    <td class="text-center fw-bold" style="vertical-align: middle;">${design.weight ? Number(design.weight).toFixed(2) : '-'}</td>
                    <td style="vertical-align: middle;">
                        <input type="text" class="form-control form-control-sm design-notes" placeholder="Notes / Remarks">
                    </td>
                </tr>`;
        });
        body.innerHTML = rowsHtml;
    }

    document.getElementById('designsPerPage')?.addEventListener('change', function() {
        designsPerPage = parseInt(this.value);
        currentDesignsPage = 1;
        renderDesignsPage();
    });

    document.getElementById('designsPrevPage')?.addEventListener('click', function() {
        if (currentDesignsPage > 1) {
            currentDesignsPage--;
            renderDesignsPage();
        }
    });

    document.getElementById('designsNextPage')?.addEventListener('click', function() {
        const totalPages = Math.ceil(currentDesigns.length / designsPerPage) || 1;
        if (currentDesignsPage < totalPages) {
            currentDesignsPage++;
            renderDesignsPage();
        }
    });

    function showDesignsList(el, type, categoryFilter = null) {
        const title = el.getAttribute('data-title');
        const bpcode = el.getAttribute('data-bpcode');
        
        document.getElementById('designsListModalTitle').innerText = title;
        const body = document.getElementById('designsListModalBody');
        body.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
        
        designsModalInstance.show();

        const fetchUrl = "{{ route('admin.details-all.accepted-designs', ':bpcode') }}".replace(':bpcode', encodeURIComponent(bpcode));
        fetch(fetchUrl)
            .then(response => response.json())
            .then(data => {
                let designs = data.designs || [];
                if (categoryFilter) {
                    designs = designs.filter(d => d.category === categoryFilter);
                }
                currentDesigns = designs;
                currentDesignsPage = 1;
                renderDesignsPage();
            })
            .catch(error => {
                console.error('Error fetching designs:', error);
                body.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-danger">Failed to load designs. Please try again.</td></tr>';
            });
    }

    function printSelectedDesigns() {
        const checkboxes = document.querySelectorAll('.modal-design-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select at least one design to print.');
            return;
        }

        const title = document.getElementById('designsListModalTitle').innerText;
        const rowsToPrint = Array.from(checkboxes).map(cb => cb.closest('tr'));

        let tbodyHtml = '';
        rowsToPrint.forEach(row => {
            const imgEl = row.querySelector('.design-img-preview');
            const imgSrc = imgEl ? imgEl.src : '';
            const code = row.cells[2]?.innerText || '';
            const name = row.cells[3]?.innerText || '';
            const category = row.cells[4]?.innerText || '';
            const weight = row.cells[5]?.innerText || '';
            const notes = row.querySelector('.design-notes')?.value || '';

            tbodyHtml += `
                <tr>
                    <td style="text-align: center; vertical-align: middle; padding: 8px;">
                        ${imgSrc ? `<img src="${imgSrc}" style="max-height: 80px; max-width: 80px; object-fit: contain;">` : '-'}
                    </td>
                    <td style="vertical-align: middle; font-weight: bold;">${code}</td>
                    <td style="vertical-align: middle;">${name}</td>
                    <td style="vertical-align: middle;">${category}</td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold;">${weight}</td>
                    <td style="vertical-align: middle;">${notes}</td>
                </tr>`;
        });

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print - ${title}</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    @media print {
                        body { margin: 15mm; font-family: system-ui, sans-serif; -webkit-print-color-adjust: exact; }
                        .table { width: 100%; border-collapse: collapse; }
                        .table th, .table td { border: 1px solid #dee2e6; padding: 8px; font-size: 12px; }
                        .text-center { text-align: center; }
                    }
                </style>
            </head>
            <body>
                <h4 class="text-center mb-4 fw-bold">${title}</h4>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th style="text-align: center; width: 100px;">Image</th>
                            <th>Design Code</th>
                            <th>Design Name</th>
                            <th>Category</th>
                            <th style="text-align: center;">Weight From (g)</th>
                            <th>Notes / Remarks</th>
                        </tr>
                    </thead>
                    <tbody>${tbodyHtml}</tbody>
                </table>
                <script>
                    window.onload = function() { 
                        setTimeout(() => { window.print(); window.close(); }, 400); 
                    }
                <\/script>
            </body>
            </html>`);
        printWindow.document.close();
    }

    // Craftsmen and Clients Main Table Print logic
    function generatePrintHtml(rowsToPrint, title, isClients = false) {
        let rowsHtml = '';
        rowsToPrint.forEach(row => {
            const cells = row.querySelectorAll('td');
            rowsHtml += '<tr>';
            for (let i = 1; i < cells.length; i++) {
                rowsHtml += `<td class="${cells[i].className}">${cells[i].innerText.trim()}</td>`;
            }
            rowsHtml += '</tr>';
        });

        let headersHtml = isClients ? `
            <tr class="table-light">
                <th rowspan="2" class="align-middle">Business Partner</th>
                <th rowspan="2" class="align-middle text-center">BP Code</th>
                <th colspan="6" class="text-center">WORK ORDERS (WA)</th>
                <th rowspan="2" class="align-middle text-center">Total Orders</th>
            </tr>
            <tr class="table-light">
                <th class="text-center">NEW</th>
                <th class="text-center">PROCESS</th>
                <th class="text-center">FOR APPROVAL</th>
                <th class="text-center text-danger">OVERDUE</th>
                <th class="text-center text-danger">REJECTED</th>
                <th class="text-center text-success">COMPLETED</th>
            </tr>` : `
            <tr>
                <th rowspan="2" style="vertical-align: middle;">Craftsman Profile</th>
                <th rowspan="2" class="text-center" style="vertical-align: middle;">Code</th>
                <th colspan="3" class="text-center bg-primary text-white">WORK ORDERS (WO)</th>
                <th colspan="3" class="text-center bg-info text-white">PURCHASE ORDERS (PO)</th>
            </tr>
            <tr class="table-light">
                <th class="text-center">IN PROCESS</th>
                <th class="text-center">FOR APPROVAL</th>
                <th class="text-center text-danger">OVERDUE</th>
                <th class="text-center">IN PROCESS</th>
                <th class="text-center">FOR APPROVAL</th>
                <th class="text-center text-danger">OVERDUE</th>
            </tr>`;

        return `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print - ${title}</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    @media print {
                        body { -webkit-print-color-adjust: exact; margin: 15mm; font-family: system-ui, sans-serif; }
                        .table { width: 100%; border-collapse: collapse; }
                        .table th, .table td { border: 1px solid #dee2e6; padding: 6px 8px; font-size: 11px; }
                        .bg-primary { background-color: #0d6efd !important; color: white !important; }
                        .bg-info { background-color: #0dcaf0 !important; color: white !important; }
                        .text-danger { color: #dc3545 !important; }
                        .text-success { color: #198754 !important; }
                        .text-center { text-align: center; }
                    }
                </style>
            </head>
            <body>
                <h4 class="text-center mb-3 fw-bold">${title}</h4>
                <table class="table table-bordered">
                    <thead>${headersHtml}</thead>
                    <tbody>${rowsHtml}</tbody>
                </table>
                <script>window.onload = function() { window.print(); window.close(); }<\/script>
            </body>
            </html>`;
    }

    function printSelected() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        if (checkboxes.length === 0) return alert('Please select at least one row to print.');
        const rowsToPrint = Array.from(checkboxes).map(cb => cb.closest('tr'));
        const w = window.open('', '_blank');
        w.document.write(generatePrintHtml(rowsToPrint, 'Craftsman Workflow Overview', false));
        w.document.close();
    }

    function printAllFiltered() {
        const rows = document.querySelectorAll('#detailsTable tbody tr.data-row');
        if (rows.length === 0) return alert('No data to print.');
        const w = window.open('', '_blank');
        w.document.write(generatePrintHtml(Array.from(rows), 'Craftsman Workflow Overview', false));
        w.document.close();
    }

    function printSelectedClients() {
        const checkboxes = document.querySelectorAll('.client-row-checkbox:checked');
        if (checkboxes.length === 0) return alert('Please select at least one client to print.');
        const rowsToPrint = Array.from(checkboxes).map(cb => cb.closest('tr'));
        const w = window.open('', '_blank');
        w.document.write(generatePrintHtml(rowsToPrint, 'Top Picks Clients Report (Work Orders)', true));
        w.document.close();
    }

    function printAllClients() {
        const rows = document.querySelectorAll('#clientsTable tbody tr.client-data-row');
        if (rows.length === 0) return alert('No clients to print.');
        const w = window.open('', '_blank');
        w.document.write(generatePrintHtml(Array.from(rows), 'Top Picks Clients Report (Work Orders)', true));
        w.document.close();
    }
</script>
@endsection