@extends('super-admin.layouts.app')

@section('title', 'Craftsman Production - ' . $craftsman->name)

@section('content')
<style>
    .highlight-term {
        background-color: #ffeb3b !important;
        color: #000 !important;
        font-weight: 700;
        padding: 1px 4px;
        border-radius: 3px;
        display: inline-block;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Production Dashboard: {{ $craftsman->name }} ({{ $craftsman->craftman_code }})</h1>
                <a href="{{ route('super-admin.craftsman-production.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title opacity-75">Work Orders</h6>
                            <h2 class="mb-0">{{ $workOrders->total() }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title opacity-75">Purchase Orders</h6>
                            <h2 class="mb-0">{{ $purchaseOrders->total() }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title opacity-75">Total PO Weight</h6>
                            <h2 class="mb-0">{{ number_format($purchaseOrders->sum('total_calculated_weight'), 3) }} g</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title opacity-75">Current Tab</h6>
                            <h2 class="mb-0 text-capitalize">{{ str_replace('_', ' ', $tab) }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter and Live Search Bar -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter & Live Search Orders</h5>
                </div>
                <div class="card-body">
                    <form action="{{ request()->url() }}" method="GET" class="row g-3" id="showFilterForm">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        
                        <!-- Live Search Input -->
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Live Search Orders</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" id="liveShowSearch" name="search" class="form-control" placeholder="Type order #, product, or status to live filter..." value="{{ request('search', $search ?? '') }}" autocomplete="off">
                                <button type="button" id="clearShowSearchBtn" class="btn btn-outline-secondary d-none" title="Clear">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Buyer Filter Dropdown -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Filter by Buyer</label>
                            <select name="buyer_code" class="form-select" onchange="document.getElementById('showFilterForm').submit();">
                                <option value="">All Buyers</option>
                                @foreach($buyers as $buyer)
                                    <option value="{{ $buyer->bp_code }}" {{ ($buyerCode ?? request('buyer_code')) == $buyer->bp_code ? 'selected' : '' }}>
                                        {{ $buyer->business_name ?? $buyer->name }} ({{ $buyer->bp_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Per Page Dropdown -->
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Per Page</label>
                            <select name="per_page" class="form-select" onchange="document.getElementById('showFilterForm').submit();">
                                <option value="10" {{ request('per_page', $perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="20" {{ request('per_page', $perPage ?? 10) == 20 ? 'selected' : '' }}>20</option>
                                <option value="50" {{ request('per_page', $perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page', $perPage ?? 10) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>

                        <!-- Reset Button -->
                        <div class="col-md-1 d-flex align-items-end">
                            <a href="{{ request()->url() }}?tab={{ $tab }}" class="btn btn-outline-secondary w-100">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Buyer Metrics Summary -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Buyer Metrics Summary</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order Type</th>
                                    <th>Allocated</th>
                                    <th>In Process</th>
                                    <th>Overdue</th>
                                    <th>Completed</th>
                                    <th>Rejected</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Work Orders</strong></td>
                                    <td>{{ $buyerMetrics['work_orders']['allocated'] }}</td>
                                    <td>{{ $buyerMetrics['work_orders']['in_process'] }}</td>
                                    <td>{{ $buyerMetrics['work_orders']['overdue'] }}</td>
                                    <td>{{ $buyerMetrics['work_orders']['completed'] }}</td>
                                    <td>{{ $buyerMetrics['work_orders']['rejected'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Purchase Orders</strong></td>
                                    <td>{{ $buyerMetrics['purchase_orders']['allocated'] }}</td>
                                    <td>{{ $buyerMetrics['purchase_orders']['in_process'] }}</td>
                                    <td>{{ $buyerMetrics['purchase_orders']['overdue'] }}</td>
                                    <td>{{ $buyerMetrics['purchase_orders']['completed'] }}</td>
                                    <td>{{ $buyerMetrics['purchase_orders']['rejected'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Status Tabs (Retains Filters and resets pages) -->
            <ul class="nav nav-pills mb-4 bg-white p-2 rounded shadow-sm">
                <li class="nav-item">
                    <a class="nav-link {{ $tab == 'new' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'new', 'wo_page' => 1, 'po_page' => 1]) }}">New</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab == 'in_process' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'in_process', 'wo_page' => 1, 'po_page' => 1]) }}">In Process</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab == 'completed' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'completed', 'wo_page' => 1, 'po_page' => 1]) }}">Completed</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab == 'overdue' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'overdue', 'wo_page' => 1, 'po_page' => 1]) }}">Overdue</a>
                </li>
            </ul>

            <!-- Work Orders Section -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Work Orders</h5>
                    <span class="badge bg-primary rounded-pill">{{ $workOrders->total() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="woTable">
                            <thead class="table-light">
                                <tr>
                                    <th>WO Number</th>
                                    <th>Product</th>
                                    <th>Customer</th>
                                    <th>Quantity</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($workOrders as $wo)
                                <tr class="wo-row">
                                    <td><strong class="show-item" data-text="{{ $wo->work_order_number }}">{{ $wo->work_order_number }}</strong></td>
                                    <td class="show-item" data-text="{{ $wo->product_name }}">{{ $wo->product_name }}</td>
                                    <td class="show-item" data-text="{{ $wo->customer_name }}">{{ $wo->customer_name }}</td>
                                    <td>{{ $wo->quantity }}</td>
                                    <td>{{ $wo->craftsman_due_date ? Carbon\Carbon::parse($wo->craftsman_due_date)->format('d M Y') : 'N/A' }}</td>
                                    <td>
                                        <span class="badge show-item {{ $wo->status == 'completed' ? 'bg-success' : ($wo->isOverdue() ? 'bg-danger' : 'bg-info') }}" data-text="{{ strtoupper($wo->status) }}">
                                            {{ strtoupper($wo->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">No work orders found.</td>
                                </tr>
                                @endforelse
                                <tr id="woNoMatch" class="d-none">
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-info-circle me-1"></i> No matching work orders found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($workOrders->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center pagination-container">
                    <small class="text-muted">
                        Showing {{ $workOrders->firstItem() }} to {{ $workOrders->lastItem() }} of {{ $workOrders->total() }} entries
                    </small>
                    <div>
                        {{ $workOrders->links() }}
                    </div>
                </div>
                @endif
            </div>

            <!-- Purchase Orders Section -->
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-cart3 me-2"></i>Purchase Orders</h5>
                    <span class="badge bg-info rounded-pill">{{ $purchaseOrders->total() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="poTable">
                            <thead class="table-light">
                                <tr>
                                    <th>PO Code</th>
                                    <th>Items Count</th>
                                    <th>Total Weight</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchaseOrders as $po)
                                <tr class="po-row">
                                    <td><strong class="show-item" data-text="{{ $po->purchase_order_code ?? $po->po_number }}">{{ $po->purchase_order_code ?? $po->po_number }}</strong></td>
                                    <td>{{ count($po->items ?? []) }}</td>
                                    <td>{{ number_format($po->total_calculated_weight ?? 0, 3) }} g</td>
                                    <td>{{ $po->due_date ? Carbon\Carbon::parse($po->due_date)->format('d M Y') : 'N/A' }}</td>
                                    <td>
                                        <span class="badge show-item {{ in_array($po->status, ['approved', 'completed']) ? 'bg-success' : 'bg-info' }}" data-text="{{ strtoupper($po->status) }}">
                                            {{ strtoupper($po->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">No purchase orders found.</td>
                                </tr>
                                @endforelse
                                <tr id="poNoMatch" class="d-none">
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-info-circle me-1"></i> No matching purchase orders found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($purchaseOrders->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center pagination-container">
                    <small class="text-muted">
                        Showing {{ $purchaseOrders->firstItem() }} to {{ $purchaseOrders->lastItem() }} of {{ $purchaseOrders->total() }} entries
                    </small>
                    <div>
                        {{ $purchaseOrders->links() }}
                    </div>
                </div>
                @endif
            </div>

        </div>
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
            clearBtn.classList.toggle('d-none', rawTerm === '');
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
            if (woNoMatch) woNoMatch.classList.add('d-none');
            if (poNoMatch) poNoMatch.classList.add('d-none');
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
                noMatchElem.classList.toggle('d-none', matched > 0 || rows.length === 0);
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