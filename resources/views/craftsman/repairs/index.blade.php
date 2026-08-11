@extends('craftsman.layouts.app')

@section('title', 'Allocated Repairs')

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
</style>

<div class="repair-wrapper py-4 px-2 px-md-4">
    <!-- Header Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h3 fw-bold text-slate-900 mb-1">Allocated Repairs</h1>
            <p class="text-muted small mb-0">Manage and update status for tasks assigned to you</p>
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

    <!-- Main Card -->
    <div class="card-modern">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <!-- <th>ID</th> -->
                             <th>Order No</th>
                            <th>Date</th>
                            <!--<th>BP Code</th>-->
                            <th>Product Name</th>
                            <th>Weight</th>
                            <th style="min-width: 200px;">Repair Details</th>
                            
                            <!-- <th>Repairs</th> -->
                            <th>Notes</th>
                            
                            <th>Proof</th>
                            <th>Status</th>
                            <!-- <th>Due Date</th> -->
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($repairs as $repair)
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $repair->order_no }}</span>
                                </td>
                                <!-- <td class="fw-bold text-primary">#{{ $repair->id }}</td> -->
                                <td>{{ \Carbon\Carbon::parse($repair->repair_date)->format('d M Y') }}</td>
                                <!--<td>{{ $repair->buyer ? $repair->buyer->bp_code : 'N/A' }}</td>-->
                                <td class="fw-semibold text-dark">{{ $repair->product_name }}</td>
                                <td>{{ $repair->weight }}</td>
                                <td class="text-wrap" style="max-width: 250px;">
                                    @if($repair->allocation_notes)
                                        <div class="p-2 mb-1 rounded bg-warning-subtle text-warning-emphasis small border border-warning-subtle">
                                            <i class="bi bi-sticky me-1"></i><strong>Alloc. Notes:</strong> {{ $repair->allocation_notes }}
                                        </div>
                                    @endif
                                    <span class="text-muted small">{{ Str::limit($repair->repair_details, 50) }}</span>
                                </td>
                                
                                <!-- <td>{{ $repair->repair }}</td> -->
                                <td class="text-wrap small text-muted" style="max-width: 180px;">{{ $repair->notes }}</td>
                                
                                <td>
                                    @if($repair->image_proof)
                                        <a href="{{ asset($repair->image_proof) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 small">
                                            <i class="bi bi-image me-1"></i>View
                                        </a>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($repair->craftsman_status == 'Pending')
                                        <span class="status-pill bg-warning-subtle text-warning-emphasis">Pending</span>
                                    @elseif($repair->craftsman_status == 'Accepted')
                                        <span class="status-pill bg-info-subtle text-info-emphasis">In Process</span>
                                    @elseif($repair->craftsman_status == 'Completed')
                                        <span class="status-pill bg-success-subtle text-success">Completed</span>
                                    @elseif($repair->craftsman_status == 'Rejected')
                                        <span class="status-pill bg-danger-subtle text-danger">Rejected</span>
                                    @else
                                        <span class="status-pill bg-secondary-subtle text-secondary">{{ $repair->craftsman_status }}</span>
                                    @endif
                                </td>
                                
                                
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        <a href="{{ route('craftsman.repairs.show', $repair->id) }}" class="btn btn-action btn-outline-secondary" title="View Details">
                                            <i class="bi bi-eye"></i> View
                                        </a>

                                        @if($repair->craftsman_status == 'Pending')
                                            {{-- Accept --}}
                                            <form action="{{ route('craftsman.repairs.accept', $repair->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-action btn-success" title="Accept">
                                                    <i class="bi bi-check-lg"></i> Accept
                                                </button>
                                            </form>
                                            {{-- Reject --}}
                                            <form action="{{ route('craftsman.repairs.reject', $repair->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-action btn-outline-danger" title="Reject">
                                                    <i class="bi bi-x-lg"></i> Reject
                                                </button>
                                            </form>
                                        @elseif($repair->craftsman_status == 'Accepted')
                                            {{-- Complete --}}
                                            <form action="{{ route('craftsman.repairs.complete', $repair->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-action btn-success" title="Mark Complete">
                                                    <i class="bi bi-check-circle"></i> Complete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-1 opacity-25 d-block mb-2"></i>
                                        <span class="fw-medium">No allocated repairs found.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($repairs->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $repairs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection