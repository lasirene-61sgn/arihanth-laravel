@extends('admin.layouts.app')

@section('title', 'Details ALL Craftsman')

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
    .kpi-card.active-kpi {
        border-color: #0d6efd;
        background-color: #f8faff;
    }
    .status-badge-soft-primary { background-color: #e7f1ff; color: #0d6efd; }
    .status-badge-soft-warning { background-color: #fff8e6; color: #b78103; }
    .status-badge-soft-danger { background-color: #feecef; color: #dc3545; }
    .status-badge-soft-purple { background-color: #f3ebff; color: #6f42c1; }
</style>

<div class="container-fluid py-4">
    <!-- Top Action Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Craftsman Details & Workflows</h4>
            <p class="text-muted small mb-0">Overview of Craftsman allocations, statuses, and performance metrics</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm px-3 shadow-sm rounded-pill" onclick="toggleDetailsTable()">
                <i class="bi bi-layout-text-window-reverse me-1"></i> <span id="toggleTableBtnText">Hide Table</span>
            </button>
            <button class="btn btn-outline-primary btn-sm px-3 shadow-sm rounded-pill" onclick="printSelected()">
                <i class="bi bi-printer me-1"></i> Print Selected
            </button>
            <button class="btn btn-primary btn-sm px-3 shadow-sm rounded-pill" onclick="printAllFiltered()">
                <i class="bi bi-printer-fill me-1"></i> Print All
            </button>
        </div>
    </div>

    <!-- Analytics / Top Picks Grid Boxes -->
    <div class="row g-3 mb-4">
        <!-- Total Craftsmen Card -->
        <div class="col-xl-3 col-sm-6">
            <div class="card kpi-card shadow-sm h-100 cursor-pointer {{ $status == 'all' ? 'active-kpi' : '' }}" onclick="applyStatusFilter('all')">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase tracking-wider">Top Craftsmen</span>
                            <h3 class="fw-bold my-1 text-dark">{{ count($craftsmenData) }}</h3>
                            <span class="badge bg-light text-dark border">Total Listed</span>
                        </div>
                        <div class="rounded-3 p-2 status-badge-soft-primary">
                            <i class="bi bi-people-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Table Container -->
    <div id="detailsTableContainer">
        <!-- Filter Bar -->
        <div class="card shadow-sm border-0 mb-4 rounded-3">
            <div class="card-body bg-light rounded-3 py-3">
                <form method="GET" action="{{ route('admin.details-all') }}" id="filterForm" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold mb-1">Status Workflow</label>
                        <select name="status" id="statusFilterSelect" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Statuses</option>
                            <option value="in_process" {{ $status == 'in_process' ? 'selected' : '' }}>In Process</option>
                            <option value="for_approval" {{ $status == 'for_approval' ? 'selected' : '' }}>For Approval</option>
                            <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="overdue" {{ $status == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold mb-1">Sort Metric</label>
                        <select name="sort_by" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="allocated" {{ $sortBy == 'allocated' ? 'selected' : '' }}>Allocated Count</option>
                            <option value="in_process" {{ $sortBy == 'in_process' ? 'selected' : '' }}>In Process</option>
                            <option value="completed" {{ $sortBy == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="overdue" {{ $sortBy == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="total_weight" {{ $sortBy == 'total_weight' ? 'selected' : '' }}>Total Weight</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted small fw-bold mb-1">Sort Order</label>
                        <select name="sort_order" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="desc" {{ $sortOrder == 'desc' ? 'selected' : '' }}>Descending (High to Low)</option>
                            <option value="asc" {{ $sortOrder == 'asc' ? 'selected' : '' }}>Ascending (Low to High)</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Craftsman Breakdown Table -->
        <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
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
                                
                                {{-- Work Orders --}}
                                <td class="text-center border-end">
                                    @if($stat['wo']['in_process']['count'] > 0)
                                        <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none p-0 fw-semibold" 
                                            onclick="showOrdersList(this, 'wo')" 
                                            data-title="In Process (WO) - {{ $stat['name'] }}" 
                                            data-orders="{{ json_encode($stat['wo']['in_process']['orders']) }}">
                                            {{ $stat['wo']['in_process']['count'] }} | {{ number_format($stat['wo']['in_process']['weight'], 2) }}
                                        </button>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                
                                <td class="text-center border-end">
                                    @if($stat['wo']['for_approval']['count'] > 0)
                                        <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold" style="color: #6f42c1;"
                                            onclick="showOrdersList(this, 'wo')" 
                                            data-title="For Approval (WO) - {{ $stat['name'] }}" 
                                            data-orders="{{ json_encode($stat['wo']['for_approval']['orders']) }}">
                                            {{ $stat['wo']['for_approval']['count'] }} | {{ number_format($stat['wo']['for_approval']['weight'], 2) }}
                                        </button>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                <td class="text-center border-end">
                                    @if($stat['wo']['overdue']['count'] > 0)
                                        <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0 fw-bold" 
                                            onclick="showOrdersList(this, 'wo')" 
                                            data-title="Overdue (WO) - {{ $stat['name'] }}" 
                                            data-orders="{{ json_encode($stat['wo']['overdue']['orders']) }}">
                                            {{ $stat['wo']['overdue']['count'] }} | {{ number_format($stat['wo']['overdue']['weight'], 2) }}
                                        </button>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                
                                {{-- Purchase Orders --}}
                                <td class="text-center border-end">
                                    @if($stat['po']['in_process']['count'] > 0)
                                        <button type="button" class="btn btn-sm btn-link text-info text-decoration-none p-0 fw-semibold" 
                                            onclick="showOrdersList(this, 'po')" 
                                            data-title="In Process (PO) - {{ $stat['name'] }}" 
                                            data-orders="{{ json_encode($stat['po']['in_process']['orders']) }}">
                                            {{ $stat['po']['in_process']['count'] }} | {{ number_format($stat['po']['in_process']['weight'], 2) }}
                                        </button>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                
                                <td class="text-center border-end">
                                    @if($stat['po']['for_approval']['count'] > 0)
                                        <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold" style="color: #6f42c1;"
                                            onclick="showOrdersList(this, 'po')" 
                                            data-title="For Approval (PO) - {{ $stat['name'] }}" 
                                            data-orders="{{ json_encode($stat['po']['for_approval']['orders']) }}">
                                            {{ $stat['po']['for_approval']['count'] }} | {{ number_format($stat['po']['for_approval']['weight'], 2) }}
                                        </button>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if($stat['po']['overdue']['count'] > 0)
                                        <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0 fw-bold" 
                                            onclick="showOrdersList(this, 'po')" 
                                            data-title="Overdue (PO) - {{ $stat['name'] }}" 
                                            data-orders="{{ json_encode($stat['po']['overdue']['orders']) }}">
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
</div>

<!-- Orders List Modal -->
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
                <!-- Print Field Customization Accordion/Section -->
                <div class="bg-light p-3 rounded-3 mb-3 border">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold small text-secondary"><i class="bi bi-sliders me-1"></i> Customize Print Columns:</span>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm px-3 shadow-sm rounded-pill" onclick="printModalSelected()">
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

                <!-- Modal Table -->
                <div class="table-responsive border rounded">
                    <table class="table table-hover align-middle mb-0" id="modalOrdersTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width: 40px;">
                                    <input class="form-check-input" type="checkbox" id="modalSelectAll">
                                </th>
                                <th class="col-order-num">Order Number</th>
                                <th class="bp-col col-bp">BP Code</th>
                                <th class="business-col col-business">Business Name</th>
                                <th class="text-center col-qty">Quantity</th>
                                <th class="text-center col-weight">Weight</th>
                                <th class="text-center col-due">Due Date</th>
                                <th class="text-center overdue-col col-overdue" style="display: none;">Overdue Days</th>
                            </tr>
                        </thead>
                        <tbody id="ordersListModalBody">
                            <!-- Injected by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleDetailsTable() {
        const container = document.getElementById('detailsTableContainer');
        const btnText = document.getElementById('toggleTableBtnText');
        if (container.style.display === 'none') {
            container.style.display = 'block';
            btnText.innerText = 'Hide Table';
        } else {
            container.style.display = 'none';
            btnText.innerText = 'Show Table';
        }
    }

    function applyStatusFilter(statusVal) {
        const select = document.getElementById('statusFilterSelect');
        if (select) {
            select.value = statusVal;
            document.getElementById('filterForm').submit();
        }
    }

    // Modal Orders Logic
    document.getElementById('modalSelectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.modal-row-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    let currentOrderType = 'wo';
    let currentModalOrders = [];

    function showOrdersList(el, type) {
        currentOrderType = type;
        const title = el.getAttribute('data-title');
        const orders = JSON.parse(el.getAttribute('data-orders') || '[]');
        currentModalOrders = orders;
        
        document.getElementById('ordersListModalTitle').innerText = title;
        document.getElementById('ordersModalSubtitle').innerText = `${orders.length} total item(s) found`;
        
        const body = document.getElementById('ordersListModalBody');
        body.innerHTML = '';
        
        const bpCols = document.querySelectorAll('.bp-col');
        const businessCols = document.querySelectorAll('.business-col');
        const bpPrintToggles = document.querySelectorAll('.bp-print-toggle');
        
        if (type === 'po') {
            bpCols.forEach(col => col.style.display = 'none');
            businessCols.forEach(col => col.style.display = 'none');
            bpPrintToggles.forEach(t => t.style.display = 'none');
        } else {
            bpCols.forEach(col => col.style.display = '');
            businessCols.forEach(col => col.style.display = '');
            bpPrintToggles.forEach(t => t.style.display = '');
        }

        const isOverdue = title.toLowerCase().includes('overdue');
        const overdueCols = document.querySelectorAll('.overdue-col');
        const overduePrintToggles = document.querySelectorAll('.overdue-print-toggle');
        
        overdueCols.forEach(col => col.style.display = isOverdue ? '' : 'none');
        overduePrintToggles.forEach(t => t.style.display = isOverdue ? '' : 'none');

        if (orders.length === 0) {
            body.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No orders found.</td></tr>';
        } else {
            orders.forEach(order => {
                let html = `
                    <tr class="modal-data-row">
                        <td class="text-center">
                            <input class="form-check-input modal-row-checkbox" type="checkbox" checked>
                        </td>
                        <td class="fw-semibold text-dark col-order-num">${order.number || '-'}</td>`;
                        
                if (type !== 'po') {
                    html += `
                        <td class="bp-col col-bp"><span class="badge bg-light text-dark border">${order.bp_code || '-'}</span></td>
                        <td class="business-col col-business text-muted">${order.business_name || '-'}</td>`;
                } else {
                    html += `
                        <td class="bp-col col-bp" style="display:none;"><span class="badge bg-light text-dark border">${order.bp_code || '-'}</span></td>
                        <td class="business-col col-business text-muted" style="display:none;">${order.business_name || '-'}</td>`;
                }

                html += `
                        <td class="text-center col-qty">${order.qty || 0}</td>
                        <td class="text-center fw-medium col-weight">${parseFloat(order.weight || 0).toFixed(2)}</td>
                        <td class="text-center col-due">${order.due_date || '-'}</td>`;
                        
                if (isOverdue) {
                    html += `<td class="text-center overdue-col col-overdue text-danger fw-bold">${order.overdue_days || 0} days</td>`;
                } else {
                    html += `<td class="text-center overdue-col col-overdue text-danger fw-bold" style="display:none;">${order.overdue_days || 0} days</td>`;
                }

                html += `</tr>`;
                body.insertAdjacentHTML('beforeend', html);
            });
        }
        
        const modal = new bootstrap.Modal(document.getElementById('ordersListModal'));
        modal.show();
    }

    function printModalSelected() {
        const checkboxes = document.querySelectorAll('.modal-row-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select at least one order to print.');
            return;
        }

        const title = document.getElementById('ordersListModalTitle').innerText;
        const isOverdue = title.toLowerCase().includes('overdue');
        const isPO = currentOrderType === 'po';

        // Read active column toggles
        const showNum = document.getElementById('printCol_number').checked;
        const showBP = !isPO && document.getElementById('printCol_bp').checked;
        const showBusiness = !isPO && document.getElementById('printCol_business').checked;
        const showQty = document.getElementById('printCol_qty').checked;
        const showWeight = document.getElementById('printCol_weight').checked;
        const showDue = document.getElementById('printCol_due').checked;
        const showOverdueDays = isOverdue && document.getElementById('printCol_overdue').checked;

        let headersHtml = '';
        if (showNum) headersHtml += '<th>Order Number</th>';
        if (showBP) headersHtml += '<th>BP Code</th>';
        if (showBusiness) headersHtml += '<th>Business Name</th>';
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
            if (showQty) rowsHtml += `<td class="text-center">${row.querySelector('.col-qty')?.innerText || ''}</td>`;
            if (showWeight) rowsHtml += `<td class="text-center">${row.querySelector('.col-weight')?.innerText || ''}</td>`;
            if (showDue) rowsHtml += `<td class="text-center">${row.querySelector('.col-due')?.innerText || ''}</td>`;
            if (showOverdueDays) rowsHtml += `<td class="text-center text-danger font-bold">${row.querySelector('.col-overdue')?.innerText || ''}</td>`;
            rowsHtml += '</tr>';
        });

        const printContent = `
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
                <thead class="table-light">
                    <tr>${headersHtml}</tr>
                </thead>
                <tbody>${rowsHtml}</tbody>
            </table>
            <script>
                window.onload = function() { window.print(); window.close(); }
            <\/script>
        </body>
        </html>`;

        const printWindow = window.open('', '_blank');
        printWindow.document.write(printContent);
        printWindow.document.close();
    }

    // Select All Craftsmen rows
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    function generatePrintHtml(rowsToPrint) {
        let html = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print - Craftsman Details</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                @media print {
                    body { -webkit-print-color-adjust: exact; margin: 15mm; font-family: system-ui, sans-serif; }
                    .table { width: 100%; border-collapse: collapse; }
                    .table th, .table td { border: 1px solid #dee2e6; padding: 6px 8px; font-size: 11px; }
                    .bg-primary { background-color: #0d6efd !important; color: white !important; }
                    .bg-info { background-color: #0dcaf0 !important; color: white !important; }
                    .text-danger { color: #dc3545 !important; }
                    .text-center { text-align: center; }
                }
            </style>
        </head>
        <body>
            <h4 class="text-center mb-3 fw-bold">Craftsman Workflow Overview</h4>
            <table class="table table-bordered">
                <thead>
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
                    </tr>
                </thead>
                <tbody>`;

        rowsToPrint.forEach(row => {
            const cells = row.querySelectorAll('td');
            html += '<tr>';
            for (let i = 1; i < cells.length; i++) { // Skip checkbox column
                html += `<td class="${cells[i].className}">${cells[i].innerText.trim()}</td>`;
            }
            html += '</tr>';
        });

        html += `
                </tbody>
            </table>
            <script>
                window.onload = function() { window.print(); window.close(); }
            <\/script>
        </body>
        </html>`;

        return html;
    }

    function printSelected() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select at least one row to print.');
            return;
        }
        const rowsToPrint = Array.from(checkboxes).map(cb => cb.closest('tr'));
        const printWindow = window.open('', '_blank');
        printWindow.document.write(generatePrintHtml(rowsToPrint));
        printWindow.document.close();
    }

    function printAllFiltered() {
        const rows = document.querySelectorAll('.data-row');
        if (rows.length === 0) {
            alert('No data to print.');
            return;
        }
        const printWindow = window.open('', '_blank');
        printWindow.document.write(generatePrintHtml(Array.from(rows)));
        printWindow.document.close();
    }
</script>
@endsection