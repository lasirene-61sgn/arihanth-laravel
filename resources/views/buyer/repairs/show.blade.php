@extends('buyer.layouts.app')

@section('title', 'Repair Details #' . $repair->id)

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
        transition: all 0.2s ease;
    }
    .card-header-clean {
        background: transparent;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.25rem 1.5rem;
    }
    .info-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 0.25rem;
    }
    .info-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #0f172a;
    }
    .info-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.875rem 1rem;
    }
    
    /* Responsive Vertical Timeline */
    .timeline-container {
        position: relative;
        padding-left: 1.5rem;
    }
    .timeline-container::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0.5rem;
        bottom: 0.5rem;
        width: 2px;
        background-color: #e2e8f0;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    .timeline-dot {
        position: absolute;
        left: -1.5rem;
        top: 0.25rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #3b82f6;
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 2px #e2e8f0;
    }
    .status-pill {
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.35em 0.75em;
        border-radius: 9999px;
    }
</style>

<div class="repair-wrapper py-4 px-2 px-md-4">
    <!-- Navigation & Header Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('buyer.repairs.index') }}" class="text-decoration-none text-muted small fw-medium">
                    <i class="bi bi-arrow-left"></i> My Repairs
                </a>
                <span class="text-muted small">/</span>
                <span class="text-primary small fw-semibold">#{{ $repair->id }}</span>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <h2 class="h3 fw-bold text-slate-900 mb-0">Repair Details #{{ $repair->id }}</h2>
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
                </div>
            </div>
        </div>
        <div>
            <a href="{{ route('buyer.repairs.index') }}" class="btn btn-white btn-sm border shadow-sm px-3 fw-medium">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content Column (Left Side) -->
        <div class="col-lg-8">
            <!-- General Information -->
            <div class="card-modern mb-4">
                <div class="card-header-clean d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-slate-800 fs-6">
                        <i class="bi bi-info-circle text-primary me-2"></i>General Information
                    </h5>
                    <span class="badge bg-slate-100 text-slate-700 fw-normal">ID: #{{ $repair->id }}</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="info-box">
                                <div class="info-label">Order ID</div>
                                <div class="info-value">#{{ $repair->id }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-box">
                                <div class="info-label">Repair Date</div>
                                <div class="info-value">
                                    {{ \Carbon\Carbon::parse($repair->repair_date)->format('d M Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product & Repair Details -->
            <div class="card-modern mb-4">
                <div class="card-header-clean">
                    <h5 class="fw-bold mb-0 text-slate-800 fs-6">
                        <i class="bi bi-box-seam text-primary me-2"></i>Product & Repair Details
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <div class="info-box">
                                <div class="info-label">Product Name</div>
                                <div class="info-value text-truncate" title="{{ $repair->product_name }}">
                                    {{ $repair->product_name }}
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-box">
                                <div class="info-label">Weight</div>
                                <div class="info-value">{{ $repair->weight ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="info-label">Repair Details</div>
                            <div class="p-3 rounded-2 bg-light border text-slate-800 small leading-relaxed">
                                {{ $repair->repair_details ?? 'No details provided' }}
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-label">Item Given To</div>
                            <div class="text-slate-700 fw-medium small">{{ $repair->item_given_to ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Log Timeline -->
            <div class="card-modern">
                <div class="card-header-clean">
                    <h5 class="fw-bold mb-0 text-slate-800 fs-6">
                        <i class="bi bi-clock-history text-primary me-2"></i>History Log Timeline
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="timeline-container">
                        <!-- Created Event -->
                        <div class="timeline-item">
                            <div class="timeline-dot" style="background-color: #64748b;"></div>
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <span class="badge bg-secondary-subtle text-secondary fw-semibold">Created</span>
                                    <div class="fw-bold text-dark mt-1">
                                        {{ $repair->creator_details['name'] ?? 'N/A' }} 
                                        <span class="text-muted fw-normal small">({{ $repair->creator_details['type'] ?? 'N/A' }})</span>
                                    </div>
                                </div>
                                <div class="text-muted small">
                                    {{ $repair->created_at ? \Carbon\Carbon::parse($repair->created_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') : 'N/A' }}
                                </div>
                            </div>
                        </div>

                       

                        <!-- Approved Event -->
                        @if($repair->approved_by)
                        <div class="timeline-item">
                            <div class="timeline-dot" style="background-color: #198754;"></div>
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <span class="badge bg-success-subtle text-success fw-semibold">Approved</span>
                                    <div class="fw-bold text-dark mt-1">
                                        {{ $repair->approver_details['name'] ?? 'N/A' }} 
                                        <span class="text-muted fw-normal small">({{ $repair->approver_details['type'] ?? 'N/A' }})</span>
                                    </div>
                                </div>
                                <div class="text-muted small">
                                    {{ $repair->approved_at ? \Carbon\Carbon::parse($repair->approved_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') : 'N/A' }}
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Buyer Completed Event -->
                        @if($repair->buyer_accepted_at)
                        <div class="timeline-item">
                            <div class="timeline-dot" style="background-color: #fd7e14;"></div>
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <span class="badge bg-warning-subtle text-warning-emphasis fw-semibold">Buyer Completed</span>
                                    <div class="fw-bold text-dark mt-1">
                                        {{ $repair->buyer->business_name ?? $repair->buyer->customer_name ?? 'N/A' }} 
                                        <span class="text-muted fw-normal small">(Buyer)</span>
                                    </div>
                                </div>
                                <div class="text-muted small">
                                    <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($repair->buyer_accepted_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Column (Right Side) -->
        <div class="col-lg-4">
            <!-- Image Proof Card -->
            <div class="card-modern mb-4">
                <div class="card-header-clean">
                    <h5 class="fw-bold mb-0 text-slate-800 fs-6">
                        <i class="bi bi-image text-primary me-2"></i>Image Proof
                    </h5>
                </div>
                <div class="card-body p-4 text-center">
                    @if($repair->image_proof)
                        <div class="p-2 border rounded-3 bg-light mb-3">
                            <a href="{{ asset($repair->image_proof) }}" target="_blank">
                                <img src="{{ asset($repair->image_proof) }}" class="img-fluid rounded" alt="Repair Proof" style="max-height: 280px; width: 100%; object-fit: contain;">
                            </a>
                        </div>
                        <a href="{{ asset($repair->image_proof) }}" target="_blank" class="btn btn-outline-primary btn-sm w-100 fw-medium">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Open Full Image
                        </a>
                    @else
                        <div class="py-5 text-muted bg-light rounded-3 border-dashed">
                            <i class="bi bi-image-alt fs-1 opacity-25 d-block mb-2"></i>
                            <span class="small fw-medium">No image proof uploaded</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Rejection Reason Card -->
            @if($repair->status == 'Rejected_by_Admin' && $repair->reject_reason)
            <div class="card-modern border-danger-subtle bg-danger-subtle bg-opacity-10">
                <div class="card-header-clean bg-transparent border-bottom-0 pt-4">
                    <h5 class="fw-bold mb-0 text-danger fs-6">
                        <i class="bi bi-x-circle me-2"></i>Rejection Reason
                    </h5>
                </div>
                <div class="card-body p-4 pt-1">
                    <p class="mb-0 text-danger-emphasis small leading-relaxed">{{ $repair->reject_reason }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection