@extends('key-user.layouts.app')

@section('content')
<div class="container-fluid space-y-6">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between mb-4">
                <h4 class="mb-0 font-bold text-slate-800">
                    @if(Auth::guard('key_user')->check())
                        Key User Dashboard - ({{ Auth::guard('key_user')->user()->bp_code }})
                    @elseif(Auth::guard('buyer')->check())
                        Buyer Dashboard - {{ Auth::guard('buyer')->user()->name ?? Auth::guard('buyer')->user()->business_name }} ({{ Auth::guard('buyer')->user()->bp_code }})
                    @else
                        Dashboard
                    @endif
                </h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0 text-sm">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                        <li class="breadcrumb-item active text-amber-700">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @php
        $activeGuard = Auth::guard('key_user')->check() ? 'key_user' : (Auth::guard('buyer')->check() ? 'buyer' : null);
        $user = $activeGuard ? Auth::guard($activeGuard)->user() : null;
    @endphp

    {{-- Top Feature Cards --}}
    <div class="row g-3 mb-4">
        @if($user && $user->hasPermission('product'))
        <div class="col-xl-3 col-md-6">
            <div class="card text-white h-100" style="background: linear-gradient(135deg, #78350f 0%, #b45309 100%); border:none; box-shadow:0 4px 15px rgba(120,53,15,0.25); border-radius:1rem;">
                <div class="card-body p-4">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2 opacity-75">Total Products</p>
                            <h3 class="mb-0 fw-bold">{{ number_format($productsCount) }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle font-size-24 d-inline-flex align-items-center justify-content-center" style="width:48px; height:48px; background:rgba(255,255,255,0.2);">
                                <i class="bi bi-box"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($user && $user->hasPermission('work_order'))
        <div class="col-xl-3 col-md-6">
            <div class="card text-white h-100" style="background: linear-gradient(135deg, #92400e 0%, #d97706 100%); border:none; box-shadow:0 4px 15px rgba(146,64,14,0.25); border-radius:1rem;">
                <div class="card-body p-4">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2 opacity-75">Total Work Orders</p>
                            <h3 class="mb-0 fw-bold">{{ number_format($workOrdersCount) }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle font-size-24 d-inline-flex align-items-center justify-content-center" style="width:48px; height:48px; background:rgba(255,255,255,0.2);">
                                <i class="bi bi-clipboard-check"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($user && $user->hasPermission('design'))
        <div class="col-xl-3 col-md-6">
            <div class="card text-white h-100" style="background: linear-gradient(135deg, #a16207 0%, #eab308 100%); border:none; box-shadow:0 4px 15px rgba(161,98,7,0.25); border-radius:1rem;">
                <div class="card-body p-4">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2 opacity-75">Total Designs</p>
                            <h3 class="mb-0 fw-bold">{{ number_format($designsCount) }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle font-size-24 d-inline-flex align-items-center justify-content-center" style="width:48px; height:48px; background:rgba(255,255,255,0.2);">
                                <i class="bi bi-pencil-square"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($user && $user->hasPermission('catalogue'))
        <div class="col-xl-3 col-md-6">
            <div class="card text-white h-100" style="background: linear-gradient(135deg, #78350f 0%, #f59e0b 100%); border:none; box-shadow:0 4px 15px rgba(245,158,11,0.25); border-radius:1rem;">
                <div class="card-body p-4">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2 opacity-75">Total Catalogue</p>
                            <h3 class="mb-0 fw-bold">{{ number_format($cataloguesCount) }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle font-size-24 d-inline-flex align-items-center justify-content-center" style="width:48px; height:48px; background:rgba(255,255,255,0.2);">
                                <i class="bi bi-journal-text"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Work Order Progress Analytics Section --}}
    @if($user && $user->hasPermission('work_order'))
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                        <span style="width:10px; height:10px; border-radius:50%; background:#d97706; display:inline-block;"></span>
                        Work Order Progress Analytics
                    </h5>
                    <p class="text-muted small mb-0">Click any card below to view matching orders and print.</p>
                </div>
            </div>

            <div class="row g-3">
                {{-- New Orders --}}
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <div onclick="openStatusDetailsModal('new', 'New Orders')" 
                         class="p-3 rounded-3 border transition-all cursor-pointer h-100" 
                         style="background:#f8fafc; border-color:#e2e8f0; cursor:pointer;" 
                         onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#cbd5e1';" 
                         onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0';">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold text-secondary small">New Orders</span>
                            <span class="badge bg-amber-100 text-amber-800 rounded-pill"><i class="bi bi-file-earmark-plus"></i></span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">{{ $woNewCount }}</h3>
                        <div class="d-flex justify-content-between text-muted" style="font-size: 11px;">
                            <span>Total Wt:</span>
                            <span class="fw-bold text-dark">{{ number_format($woNewWeight, 2) }} g</span>
                        </div>
                    </div>
                </div>

                {{-- Allocated --}}
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <div onclick="openStatusDetailsModal('allocated', 'Allocated Orders')" 
                         class="p-3 rounded-3 border transition-all cursor-pointer h-100" 
                         style="background:#f8fafc; border-color:#e2e8f0; cursor:pointer;" 
                         onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#cbd5e1';" 
                         onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0';">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold text-secondary small">Allocated</span>
                            <span class="badge bg-info-subtle text-info rounded-pill"><i class="bi bi-check-circle"></i></span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">{{ $woAllocatedCount }}</h3>
                        <div class="d-flex justify-content-between text-muted" style="font-size: 11px;">
                            <span>Total Wt:</span>
                            <span class="fw-bold text-dark">{{ number_format($woAllocatedWeight, 2) }} g</span>
                        </div>
                    </div>
                </div>

                {{-- In Process --}}
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <div onclick="openStatusDetailsModal('in_process', 'In Process Orders')" 
                         class="p-3 rounded-3 border transition-all cursor-pointer h-100" 
                         style="background:#f8fafc; border-color:#e2e8f0; cursor:pointer;" 
                         onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#cbd5e1';" 
                         onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0';">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold text-secondary small">In Process</span>
                            <span class="badge bg-primary-subtle text-primary rounded-pill"><i class="bi bi-gear-wide-connected"></i></span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">{{ $woInProcessCount }}</h3>
                        <div class="d-flex justify-content-between text-muted" style="font-size: 11px;">
                            <span>Total Wt:</span>
                            <span class="fw-bold text-dark">{{ number_format($woInProcessWeight, 2) }} g</span>
                        </div>
                    </div>
                </div>

                {{-- Completed --}}
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <div onclick="openStatusDetailsModal('completed', 'Completed Orders')" 
                         class="p-3 rounded-3 border transition-all cursor-pointer h-100" 
                         style="background:#f8fafc; border-color:#e2e8f0; cursor:pointer;" 
                         onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#cbd5e1';" 
                         onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0';">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold text-secondary small">Completed</span>
                            <span class="badge bg-success-subtle text-success rounded-pill"><i class="bi bi-check2-circle"></i></span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">{{ $woCompletedCount }}</h3>
                        <div class="d-flex justify-content-between text-muted" style="font-size: 11px;">
                            <span>Total Wt:</span>
                            <span class="fw-bold text-dark">{{ number_format($woCompletedWeight, 2) }} g</span>
                        </div>
                    </div>
                </div>

                {{-- Overdue --}}
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <div onclick="openStatusDetailsModal('overdue', 'Overdue Orders')" 
                         class="p-3 rounded-3 border transition-all cursor-pointer h-100" 
                         style="background:#fef2f2; border-color:#fecaca; cursor:pointer;" 
                         onmouseover="this.style.background='#fee2e2';" 
                         onmouseout="this.style.background='#fef2f2';">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold text-danger small">Overdue</span>
                            <span class="badge bg-danger text-white rounded-pill"><i class="bi bi-exclamation-octagon-fill"></i></span>
                        </div>
                        <h3 class="fw-bold text-danger mb-1">{{ $woOverdueCount }}</h3>
                        <div class="d-flex justify-content-between text-danger" style="font-size: 11px; opacity:0.85;">
                            <span>Total Wt:</span>
                            <span class="fw-bold">{{ number_format($woOverdueWeight, 2) }} g</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Interactive Status Work Orders Detail Modal --}}
<div class="modal fade" id="statusWorkOrdersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom bg-light px-4 py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 mb-0">
                        <span id="modalCategoryBadge" class="badge bg-warning text-dark text-uppercase px-2 py-1">
                            Status
                        </span>
                        <span id="modalCategoryTitle">Work Orders</span>
                    </h5>
                    <small class="text-muted" id="modalCategorySubtitle">Viewing filtered orders</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                {{-- Column Toggles & Print Button --}}
                <div class="p-3 rounded-3 bg-light border mb-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <span class="small fw-bold text-dark text-uppercase">
                            <i class="bi bi-layout-three-columns mr-1"></i> Columns:
                        </span>
                        <label class="small text-muted mb-0 cursor-pointer d-flex align-items-center gap-1">
                            <input type="checkbox" class="col-toggle" data-col="wo_number" checked> WO#
                        </label>
                        <label class="small text-muted mb-0 cursor-pointer d-flex align-items-center gap-1">
                            <input type="checkbox" class="col-toggle" data-col="due_date" checked> Due Date
                        </label>
                        
                        {{-- Overdue Days (Visible exclusively on Overdue Tab) --}}
                        <label class="small text-muted mb-0 cursor-pointer align-items-center gap-1" id="toggleColOverdueDaysWrapper" style="display:none;">
                            <input type="checkbox" class="col-toggle" data-col="overdue_days" checked> Overdue Days
                        </label>

                        <label class="small text-muted mb-0 cursor-pointer d-flex align-items-center gap-1">
                            <input type="checkbox" class="col-toggle" data-col="qty" checked> Qty
                        </label>
                        <label class="small text-muted mb-0 cursor-pointer d-flex align-items-center gap-1">
                            <input type="checkbox" class="col-toggle" data-col="weight_from" checked> Wt From
                        </label>
                        <label class="small text-muted mb-0 cursor-pointer d-flex align-items-center gap-1">
                            <input type="checkbox" class="col-toggle" data-col="weight_to" checked> Wt To
                        </label>
                        <label class="small text-muted mb-0 cursor-pointer d-flex align-items-center gap-1">
                            <input type="checkbox" class="col-toggle" data-col="status" checked> Status
                        </label>
                    </div>

                    <div>
                        <button type="button" onclick="printFilteredWorkOrders()" class="btn btn-sm btn-dark fw-bold px-3 py-1.5 rounded-3">
                            <i class="bi bi-printer me-1"></i> Print Selected Orders
                        </button>
                    </div>
                </div>

                {{-- Table View --}}
                <div class="table-responsive border rounded-3">
                    <table class="table table-hover align-middle mb-0 small" id="statusWorkOrdersTable">
                        <thead class="table-light text-uppercase fw-bold text-muted border-bottom">
                            <tr>
                                <th class="text-center" style="width: 40px;">
                                    <input type="checkbox" id="modalSelectAll" class="form-check-input" checked>
                                </th>
                                <th class="col-cell col-wo_number">Work Order #</th>
                                <th class="col-cell col-due_date">Due Date</th>
                                <th class="col-cell col-overdue_days text-center" id="thOverdueDays" style="display:none;">Overdue (Days)</th>
                                <th class="col-cell col-qty text-center">Qty</th>
                                <th class="col-cell col-weight_from text-end">Weight From (g)</th>
                                <th class="col-cell col-weight_to text-end">Weight To (g)</th>
                                <th class="col-cell col-status text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="statusWorkOrdersBody" class="bg-white">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer border-top bg-light px-4 py-2 d-flex justify-content-between">
                <span class="small text-muted fw-semibold" id="modalSelectedCountLabel">0 rows selected</span>
                <button type="button" class="btn btn-sm btn-secondary fw-bold px-4 rounded-3" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const allWorkOrders = @json($modalWorkOrders ?? []);
    let filteredWorkOrders = [];
    let currentCategoryKey = '';
    let currentCategoryTitle = '';

    function openStatusDetailsModal(statusKey, title) {
        currentCategoryKey = statusKey;
        currentCategoryTitle = title;

        document.getElementById('modalCategoryTitle').textContent = title;
        document.getElementById('modalCategoryBadge').textContent = statusKey.toUpperCase().replace('_', ' ');

        // Strict category segregation
        filteredWorkOrders = allWorkOrders.filter(wo => wo.category === statusKey);

        const isOverdueTab = (statusKey === 'overdue');
        const overdueDaysToggleWrapper = document.getElementById('toggleColOverdueDaysWrapper');
        const thOverdueDays = document.getElementById('thOverdueDays');

        // Toggle overdue column controls only for overdue status
        if (isOverdueTab) {
            overdueDaysToggleWrapper.style.display = 'inline-flex';
            thOverdueDays.style.display = '';
        } else {
            overdueDaysToggleWrapper.style.display = 'none';
            thOverdueDays.style.display = 'none';
        }

        renderWorkOrderTable(filteredWorkOrders, isOverdueTab);

        const modalEl = document.getElementById('statusWorkOrdersModal');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }

    function renderWorkOrderTable(data, isOverdueTab) {
        const tbody = document.getElementById('statusWorkOrdersBody');
        const totalColumns = isOverdueTab ? 8 : 7;

        if (!data.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="${totalColumns}" class="p-4 text-center text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-2 opacity-50"></i>
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
                ? `<td class="text-center col-cell col-overdue_days">
                     <span class="badge bg-danger-subtle text-danger fw-bold">
                         ${item.days_overdue} Days
                     </span>
                   </td>` 
                : ``;

            html += `
                <tr>
                    <td class="text-center">
                        <input type="checkbox" class="wo-row-select form-check-input" value="${item.id}" checked>
                    </td>
                    <td class="fw-bold font-monospace col-cell col-wo_number">${item.wo_number}</td>
                    <td class="text-muted col-cell col-due_date">${item.due_date}</td>
                    ${overdueColHtml}
                    <td class="text-center fw-bold col-cell col-qty">${item.qty}</td>
                    <td class="text-end text-muted col-cell col-weight_from">${item.weight_from}</td>
                    <td class="text-end fw-bold text-dark col-cell col-weight_to">${item.weight_to}</td>
                    <td class="text-center col-cell col-status">
                        <span class="badge bg-light text-dark border">
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
                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #b45309; padding-bottom:8px;">
                        <div>
                            <h2>${currentCategoryTitle} Report</h2>
                            <p>BP Code: {{ $keyUser->bp_code }}</p>
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