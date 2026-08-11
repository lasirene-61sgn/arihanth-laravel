@extends('craftsman.layouts.app')

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
    
    /* Timeline Styling for Craftsman View */
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
    <!-- Header Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('craftsman.repairs.index') }}" class="text-decoration-none text-muted small fw-medium">
                    <i class="bi bi-arrow-left"></i> Allocated Repairs
                </a>
                <span class="text-muted small">/</span>
                <span class="text-primary small fw-semibold">#{{ $repair->id }}</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <h2 class="h3 fw-bold text-slate-900 mb-0">Repair Details #{{ $repair->id }}</h2>
                <div>
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
                </div>
            </div>
        </div>
        <div>
            <a href="{{ route('craftsman.repairs.index') }}" class="btn btn-white btn-sm border shadow-sm px-3 fw-medium">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content (Left Column) -->
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
                            <div class="info-label">Sample Details</div>
                            <div class="p-3 rounded-2 bg-light border text-slate-800 small leading-relaxed">
                                {{ $repair->sample_details ?? 'No sample details' }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Item Given To</div>
                            <div class="text-slate-700 fw-medium small">{{ $repair->item_given_to ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Notes</div>
                            <div class="text-slate-700 fw-medium small">{{ $repair->notes ?? 'No notes' }}</div>
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
                       

                        <!-- Craftsman Allocated Event -->
                        @if($repair->allocated_craftsman_code)
                        <div class="timeline-item">
                            <div class="timeline-dot" style="background-color: #0dcaf0;"></div>
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <!-- <span class="badge bg-info-subtle text-info-emphasis fw-semibold">Craftsman Allocated</span> -->
                                    <div class="fw-bold text-dark mt-1">
                                        {{ $repair->craftsman->name ?? 'N/A' }} 
                                        <span class="text-muted fw-normal small">({{ $repair->allocated_craftsman_code }})</span>
                                    </div>
                                </div>
                                <div class="text-muted small">
                                    {{ $repair->allocated_at ? \Carbon\Carbon::parse($repair->allocated_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') : 'N/A' }}
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Craftsman Accepted Event -->
                        @if($repair->craftsman_accepted_at)
                        <div class="timeline-item">
                            <div class="timeline-dot" style="background-color: #ffc107;"></div>
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <span class="badge bg-warning-subtle text-warning-emphasis fw-semibold">Craftsman Accepted</span>
                                    <div class="fw-bold text-dark mt-1">{{ $repair->craftsman->name ?? 'N/A' }}</div>
                                </div>
                                <div class="text-muted small">
                                    {{ \Carbon\Carbon::parse($repair->craftsman_accepted_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Craftsman Completed Event -->
                        @if($repair->craftsman_completed_at)
                        <div class="timeline-item">
                            <div class="timeline-dot" style="background-color: #198754;"></div>
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <span class="badge bg-success-subtle text-success fw-semibold">Craftsman Completed</span>
                                    <div class="fw-bold text-dark mt-1">{{ $repair->craftsman->name ?? 'N/A' }}</div>
                                </div>
                                <div class="text-muted small">
                                    {{ \Carbon\Carbon::parse($repair->craftsman_completed_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                </div>
                            </div>
                        </div>
                        @endif

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
                            <div class="timeline-dot" style="background-color: #0d6efd;"></div>
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <span class="badge bg-primary-subtle text-primary fw-semibold">Buyer Completed</span>
                                    <div class="fw-bold text-dark mt-1">{{ $repair->buyer->business_name ?? $repair->buyer->name ?? 'N/A' }}</div>
                                </div>
                                <div class="text-muted small">
                                    {{ \Carbon\Carbon::parse($repair->buyer_accepted_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar (Right Column) -->
        <div class="col-lg-4">
            <!-- Image Proof Card -->
            <div class="card-modern">
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
        </div>
    </div>
</div>
@endsection