@extends('buyer.layouts.app')

@section('title', 'My Repairs')

@section('content')
<style>
    .repair-wrapper {
        background-color: #f8fafc;
        min-height: 100vh;
    }
    .card-modern {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    }
    .table-custom {
        vertical-align: middle;
    }
    .table-custom thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        padding: 0.875rem 1rem;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .table-custom tbody td {
        padding: 1rem;
        color: #1e293b;
        font-size: 0.875rem;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .table-custom tbody tr:hover {
        background-color: #f8fafc;
    }
    .status-pill {
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.35em 0.75em;
        border-radius: 9999px;
        display: inline-block;
    }
    .btn-action {
        padding: 0.35rem 0.65rem;
        font-size: 0.775rem;
        font-weight: 600;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* Mobile & Tablet Card Layout Enhancements */
    @media (max-width: 991.98px) {
        .desktop-table-view {
            display: none !important;
        }
        .mobile-card-view {
            display: block !important;
        }
    }
    @media (min-width: 992px) {
        .desktop-table-view {
            display: block !important;
        }
        .mobile-card-view {
            display: none !important;
        }
    }
</style>

<div class="repair-wrapper py-4 px-2 px-md-4">
    <!-- Top Action Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h3 fw-bold text-slate-900 mb-1">My Repairs</h1>
            <p class="text-muted small mb-0">Track, manage, and review all your submitted repair requests</p>
        </div>
        <div>
            <a href="{{ route('buyer.repairs.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2 shadow-sm px-3 py-2 fw-medium">
                <i class="bi bi-plus-circle fs-6"></i> Add New Repair
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Main Container -->
    <div class="card-modern">
        <div class="card-body p-0">
            
            <!-- DESKTOP TABLE VIEW (PC / Large Displays) -->
            <div class="desktop-table-view">
                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Created At</th>
                                <th>Completed At</th>
                                <th>Product Name</th>
                                <th>Weight</th>
                                <th>Item Given To</th>
                                <th>Status</th>
                                <th>Rejection Reason</th>
                                <th>Proof</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($repairs as $repair)
                                <tr>
                                    <td class="fw-bold text-primary">#{{ $repair->id }}</td>
                                    <td>
                                        <i class="bi bi-calendar3 text-muted me-1 small"></i>
                                        {{ $repair->created_at ? \Carbon\Carbon::parse($repair->created_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') : \Carbon\Carbon::parse($repair->repair_date)->format('d M Y') }}
                                    </td>
                                    <td class="text-muted">
                                        {{ $repair->approved_at ? \Carbon\Carbon::parse($repair->approved_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') : '-' }}
                                    </td>
                                    <td class="fw-semibold text-dark">{{ $repair->product_name }}</td>
                                    <td>{{ $repair->weight }}</td>
                                    <td class="fst-italic text-muted">{{ $repair->item_given_to ?? 'N/A' }}</td>
                                    <td>
                                        @if($repair->status == 'Pending')
                                            <span class="status-pill bg-warning-subtle text-warning-emphasis">Pending</span>
                                        @elseif(in_array($repair->status, ['Accepted', 'Allocated']))
                                            <span class="status-pill bg-info-subtle text-info-emphasis">In Process</span>
                                        @elseif($repair->status == 'Rejected_by_Admin')
                                            <span class="status-pill bg-danger-subtle text-danger">Rejected</span>
                                        @elseif($repair->status == 'Craftsman_Completed')
                                            <span class="status-pill bg-info-subtle text-info-emphasis">In Process</span>
                                        @elseif($repair->status == 'Completed')
                                            <span class="status-pill bg-success-subtle text-success">Completed - Awaiting Your Approval</span>
                                        @elseif($repair->status == 'Buyer_Accepted')
                                            <span class="status-pill bg-success-subtle text-success">Accepted</span>
                                        @elseif($repair->status == 'Buyer_Rejected')
                                            <span class="status-pill bg-danger-subtle text-danger">Rejected by You</span>
                                        @elseif($repair->status == 'Craftsman_Rejected')
                                            <span class="status-pill bg-info-subtle text-info-emphasis">In Process</span>
                                        @else
                                            <span class="status-pill bg-secondary-subtle text-secondary">{{ $repair->status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-wrap small" style="max-width: 200px;">
                                        @if($repair->status == 'Rejected_by_Admin' && $repair->reject_reason)
                                            <span class="text-danger fw-medium"><i class="bi bi-info-circle me-1"></i>{{ $repair->reject_reason }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($repair->image_proof)
                                            <a href="{{ asset($repair->image_proof) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 small">
                                                <i class="bi bi-image me-1"></i>View
                                            </a>
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-1 align-items-center">
                                            <a href="{{ route('buyer.repairs.show', $repair->id) }}" class="btn btn-action btn-outline-secondary" title="View Details">
                                                <i class="bi bi-eye"></i> View
                                            </a>

                                            @if($repair->status == 'Completed')
                                                <form action="{{ route('buyer.repairs.accept-completed', $repair->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-action btn-success" title="Accept">
                                                        <i class="bi bi-check-lg"></i> Accept
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-action btn-outline-danger" title="Reject" data-bs-toggle="modal" data-bs-target="#buyerRejectModal{{ $repair->id }}">
                                                    <i class="bi bi-x-lg"></i> Reject
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox fs-1 opacity-25 d-block mb-2"></i>
                                            <span class="fw-medium">No repairs found.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MOBILE & TABLET CARD VIEW -->
            <div class="mobile-card-view p-3">
                @forelse($repairs as $repair)
                    <div class="card border rounded-3 p-3 mb-3 shadow-sm bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="fw-bold text-primary">Order #{{ $repair->id }}</span>
                            <div>
                                @if($repair->status == 'Pending')
                                    <span class="status-pill bg-warning-subtle text-warning-emphasis">Pending</span>
                                @elseif(in_array($repair->status, ['Accepted', 'Allocated']))
                                    <span class="status-pill bg-info-subtle text-info-emphasis">In Process</span>
                                @elseif($repair->status == 'Rejected_by_Admin')
                                    <span class="status-pill bg-danger-subtle text-danger">Rejected</span>
                                @elseif($repair->status == 'Craftsman_Completed')
                                    <span class="status-pill bg-info-subtle text-info-emphasis">In Process</span>
                                @elseif($repair->status == 'Completed')
                                    <span class="status-pill bg-success-subtle text-success">Action Required</span>
                                @elseif($repair->status == 'Buyer_Accepted')
                                    <span class="status-pill bg-success-subtle text-success">Accepted</span>
                                @elseif($repair->status == 'Buyer_Rejected')
                                    <span class="status-pill bg-danger-subtle text-danger">Rejected by You</span>
                                @elseif($repair->status == 'Craftsman_Rejected')
                                    <span class="status-pill bg-info-subtle text-info-emphasis">In Process</span>
                                @else
                                    <span class="status-pill bg-secondary-subtle text-secondary">{{ $repair->status }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="row g-2 mb-3 small text-slate-700">
                            <div class="col-6">
                                <span class="text-muted d-block fs-7">Product Name</span>
                                <span class="fw-semibold text-dark">{{ $repair->product_name }}</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block fs-7">Weight</span>
                                <span class="fw-semibold text-dark">{{ $repair->weight }}</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block fs-7">Created At</span>
                                <span>{{ $repair->created_at ? \Carbon\Carbon::parse($repair->created_at)->timezone('Asia/Kolkata')->format('d M Y') : \Carbon\Carbon::parse($repair->repair_date)->format('d M Y') }}</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block fs-7">Given To</span>
                                <span>{{ $repair->item_given_to ?? 'N/A' }}</span>
                            </div>
                            @if($repair->status == 'Rejected_by_Admin' && $repair->reject_reason)
                            <div class="col-12 mt-2">
                                <div class="p-2 rounded bg-danger-subtle text-danger small">
                                    <strong>Rejection Reason:</strong> {{ $repair->reject_reason }}
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <div>
                                @if($repair->image_proof)
                                    <a href="{{ asset($repair->image_proof) }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2 small">
                                        <i class="bi bi-image me-1"></i> Proof
                                    </a>
                                @endif
                            </div>
                            <div class="d-flex gap-1">
                                <a href="{{ route('buyer.repairs.show', $repair->id) }}" class="btn btn-action btn-outline-secondary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                @if($repair->status == 'Completed')
                                    <form action="{{ route('buyer.repairs.accept-completed', $repair->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-action btn-success">
                                            <i class="bi bi-check-lg"></i> Accept
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-action btn-outline-danger" data-bs-toggle="modal" data-bs-target="#buyerRejectModal{{ $repair->id }}">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 opacity-25 d-block mb-2"></i>
                        <span class="fw-medium">No repairs found.</span>
                    </div>
                @endforelse
            </div>

            <!-- Modals Block -->
            @foreach($repairs as $repair)
                @if($repair->status == 'Completed')
                    <div class="modal fade" id="buyerRejectModal{{ $repair->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow rounded-3">
                                <form action="{{ route('buyer.repairs.reject-completed', $repair->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-header border-bottom-0 pb-0">
                                        <h5 class="modal-title fw-bold text-slate-900">Reject Repair #{{ $repair->id }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body py-3">
                                        <p class="text-muted small mb-3">Please provide a reason why this completed repair does not meet your expectations.</p>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold small text-slate-700">Rejection Reason</label>
                                            <textarea name="reject_reason" class="form-control rounded-2" rows="3" placeholder="Describe the issue..." required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top-0 pt-0">
                                        <button type="button" class="btn btn-light btn-sm fw-medium px-3" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger btn-sm fw-medium px-3">Confirm Rejection</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            <!-- Pagination Container -->
            @if($repairs->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $repairs->links() }}
                </div>
            @endif

        </div>
    </div>
</div>
@endsection 