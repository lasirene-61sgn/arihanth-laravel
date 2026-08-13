@extends('super-admin.layouts.app')

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
    
    /* Modern Vertical Timeline for History Log */
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
                <a href="{{ route('super-admin.repairs.index') }}" class="text-decoration-none text-muted small fw-medium">
                    <i class="bi bi-arrow-left"></i> Repairs
                </a>
                <span class="text-muted small">/</span>
                <span class="text-primary small fw-semibold">#{{ $repair->id }}</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <h2 class="h3 fw-bold text-slate-900 mb-0">Repair Order #{{ $repair->id }}</h2>
                <div>
                    @if($repair->status == 'Pending')
                        <span class="status-pill bg-warning-subtle text-warning-emphasis">Pending</span>
                    @elseif($repair->status == 'Accepted')
                        <span class="status-pill bg-info-subtle text-info-emphasis">Accepted</span>
                    @elseif($repair->status == 'In_Process')
                        <span class="status-pill bg-info-subtle text-info-emphasis">In Process</span>
                    @elseif($repair->status == 'Allocated')
                        <span class="status-pill bg-primary-subtle text-primary">Allocated</span>
                    @elseif($repair->status == 'Craftsman_Completed')
                        <span class="status-pill bg-success-subtle text-success">Craftsman Completed</span>
                    @elseif($repair->status == 'Craftsman_Rejected')
                        <span class="status-pill bg-danger-subtle text-danger">Craftsman Rejected</span>
                    @elseif($repair->status == 'Completed')
                        <span class="status-pill bg-success-subtle text-success">Completed</span>
                    @elseif($repair->status == 'Rejected_by_Admin')
                        <span class="status-pill bg-danger-subtle text-danger">Rejected</span>
                    @elseif($repair->status == 'Buyer_Accepted')
                        <span class="status-pill bg-success-subtle text-success">Buyer Accepted</span>
                    @elseif($repair->status == 'Buyer_Rejected')
                        <span class="status-pill bg-danger-subtle text-danger">Buyer Rejected</span>
                    @else
                        <span class="status-pill bg-secondary-subtle text-secondary">{{ $repair->status }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('super-admin.repairs.index') }}" class="btn btn-white btn-sm border shadow-sm px-3 fw-medium">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            @if(in_array($repair->status, ['Pending', 'Accepted']))
            <a href="{{ route('super-admin.repairs.edit', $repair->id) }}" class="btn btn-primary btn-sm shadow-sm px-3 fw-medium">
                <i class="bi bi-pencil me-1"></i> Edit Repair
            </a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content Column (Left) -->
        <div class="col-lg-8">
            <!-- General & Product Summary -->
            <div class="card-modern mb-4">
                <div class="card-header-clean d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-slate-800 fs-6">
                        <i class="bi bi-file-earmark-text text-primary me-2"></i>General & Product Details
                    </h5>
                    <span class="badge bg-slate-100 text-slate-700 fw-normal">ID: #{{ $repair->id }}</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-md-4">
                            <div class="info-box">
                                <div class="info-label">Repair Date</div>
                                <div class="info-value">
                                    {{ $repair->repair_date ? \Carbon\Carbon::parse($repair->repair_date)->format('d M Y') : 'N/A' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-box">
                                <div class="info-label">Product Name</div>
                                <div class="info-value text-truncate" title="{{ $repair->product_name }}">
                                    {{ $repair->product_name }}
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
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

            <!-- Stakeholders (Buyer & Craftsman Grid) -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card-modern h-100">
                        <div class="card-header-clean">
                            <h5 class="fw-bold mb-0 text-slate-800 fs-6">
                                <i class="bi bi-person-circle text-primary me-2"></i>Buyer Info
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            @if($repair->buyer)
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                    <span class="info-label mb-0">BP Code</span>
                                    <span class="badge bg-light text-dark border">{{ $repair->buyer->bp_code }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="info-label mb-0">Customer Name</span>
                                    <span class="fw-bold text-slate-900">{{ $repair->buyer->customer_name }}</span>
                                </div>
                            @else
                                <div class="p-3 rounded-2 bg-danger-subtle text-danger small fw-medium">
                                    <i class="bi bi-exclamation-circle me-1"></i> Buyer information missing
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card-modern h-100">
                        <div class="card-header-clean">
                            <h5 class="fw-bold mb-0 text-slate-800 fs-6">
                                <i class="bi bi-tools text-primary me-2"></i>Craftsman Info
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            @if($repair->craftsman)
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <span class="info-label mb-0">Craftsman Code</span>
                                    <span class="badge bg-light text-dark border">{{ $repair->allocated_craftsman_code }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <span class="info-label mb-0">Name</span>
                                    <span class="fw-bold text-slate-900">{{ $repair->craftsman->name }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="info-label mb-0">Alloc. Notes</span>
                                    <span class="fst-italic small text-muted">{{ $repair->allocation_notes ?? '-' }}</span>
                                </div>
                            @else
                                <div class="p-3 bg-light rounded-2 text-center text-muted small">
                                    <i class="bi bi-person-x d-block fs-5 mb-1 opacity-50"></i>
                                    Not yet allocated
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Log Timeline View -->
            <div class="card-modern mb-4">
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
                                    @if(!empty($repair->created_at))
                                        <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($repair->created_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Allocated Event -->
                        @if($repair->allocated_by)
                        <div class="timeline-item">
                            <div class="timeline-dot" style="background-color: #0d6efd;"></div>
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <span class="badge bg-primary-subtle text-primary fw-semibold">Allocated</span>
                                    <div class="fw-bold text-dark mt-1">
                                        {{ $repair->allocator_details['name'] ?? 'N/A' }} 
                                        <span class="text-muted fw-normal small">({{ $repair->allocator_details['type'] ?? 'N/A' }})</span>
                                    </div>
                                </div>
                                <div class="text-muted small">
                                    @if(!empty($repair->allocated_at))
                                        <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($repair->allocated_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Craftsman Allocated Event -->
                        @if($repair->allocated_craftsman_code)
                        <div class="timeline-item">
                            <div class="timeline-dot" style="background-color: #0dcaf0;"></div>
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <span class="badge bg-info-subtle text-info-emphasis fw-semibold">Craftsman Allocated</span>
                                    <div class="fw-bold text-dark mt-1">
                                        {{ $repair->craftsman->name ?? 'N/A' }} 
                                        <span class="text-muted fw-normal small">({{ $repair->allocated_craftsman_code }})</span>
                                    </div>
                                </div>
                                <div class="text-muted small">
                                    @if(!empty($repair->allocated_at))
                                        <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($repair->allocated_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                    @else
                                        N/A
                                    @endif
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
                                    <span class="badge bg-warning-subtle text-warning-emphasis fw-semibold">
                                        @if($repair->accepted_by_staff_id && $repair->staff_accepted_at)
                                            Craftsman Staff Accepted
                                        @else
                                            Craftsman Accepted
                                        @endif
                                    </span>
                                    @if($repair->accepted_by_staff_id && $repair->staff_accepted_at)
                                        {{-- Staff accepted: show staff only --}}
                                        <div class="mt-1" style="background:#f0f0ff; padding:5px 9px; border-radius:6px; border-left:3px solid #4f46e5;">
                                            <div class="fw-semibold" style="color:#4f46e5; font-size:0.85rem;"><i class="bi bi-person-badge me-1"></i>{{ $repair->acceptedByStaff->staff_code ?? 'N/A' }} - {{ $repair->acceptedByStaff->name ?? 'N/A' }}</div>
                                        </div>
                                    @else
                                        {{-- Craftsman accepted: show craftsman only --}}
                                        <div class="mt-1">
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle me-1">{{ $repair->craftsman->craftman_code ?? 'N/A' }}</span>
                                            <span class="fw-bold text-dark">{{ $repair->craftsman->full_name ?? $repair->craftsman->name ?? 'N/A' }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-muted small text-end">
                                    @if($repair->accepted_by_staff_id && $repair->staff_accepted_at)
                                        <span style="color:#6d28d9;"><i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($repair->staff_accepted_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span>
                                    @elseif(!empty($repair->craftsman_accepted_at))
                                        <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($repair->craftsman_accepted_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                    @elseif(!empty($repair->updated_at))
                                        <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($repair->updated_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                    @else
                                        N/A
                                    @endif
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
                                    <span class="badge bg-success-subtle text-success fw-semibold">
                                        @if($repair->staff_completed_at)
                                            Craftsman Staff Completed
                                        @else
                                            Craftsman Completed
                                        @endif
                                    </span>
                                    @if($repair->staff_completed_at)
                                        {{-- Staff completed: show staff only --}}
                                        <div class="mt-1" style="background:#f0f0ff; padding:5px 9px; border-radius:6px; border-left:3px solid #4f46e5;">
                                            <div class="fw-semibold" style="color:#4f46e5; font-size:0.85rem;"><i class="bi bi-person-badge me-1"></i>{{ $repair->craftsmanStaff->staff_code ?? 'N/A' }} - {{ $repair->craftsmanStaff->name ?? 'N/A' }}</div>
                                        </div>
                                    @else
                                        {{-- Craftsman completed: show craftsman only --}}
                                        <div class="mt-1">
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle me-1">{{ $repair->craftsman->craftman_code ?? 'N/A' }}</span>
                                            <span class="fw-bold text-dark">{{ $repair->craftsman->full_name ?? $repair->craftsman->name ?? 'N/A' }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-muted small text-end">
                                    @if($repair->staff_completed_at)
                                        <span style="color:#6d28d9;"><i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($repair->staff_completed_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span>
                                    @elseif(!empty($repair->craftsman_completed_at))
                                        <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($repair->craftsman_completed_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                    @elseif(!empty($repair->updated_at))
                                        <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($repair->updated_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                    @else
                                        N/A
                                    @endif
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
                                    @if(!empty($repair->approved_at))
                                        <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($repair->approved_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                    @else
                                        N/A
                                    @endif
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

            <!-- Rejection Reason Card -->
            @if($repair->reject_reason)
            <div class="card-modern border-danger-subtle bg-danger-subtle bg-opacity-10 mb-4">
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

        <!-- Sidebar Column (Right) -->
        <div class="col-lg-4">
            <!-- Image Proof Box -->
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
                            <i class="bi bi-box-arrow-up-right me-1"></i> View Full Resolution
                        </a>
                    @else
                        <div class="py-5 text-muted bg-light rounded-3 border-dashed">
                            <i class="bi bi-image-alt fs-1 opacity-25 d-block mb-2"></i>
                            <span class="small fw-medium">No image proof uploaded</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card-modern border-danger-subtle">
                <div class="card-header-clean bg-danger-subtle bg-opacity-25 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-danger fs-6">
                        <i class="bi bi-exclamation-triangle me-2"></i>Danger Zone
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="small text-muted mb-3">Deleting a repair order permanently removes all history logs and attached media.</p>
                    <form action="{{ route('super-admin.repairs.destroy', $repair->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this repair order? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100 btn-sm fw-bold py-2 shadow-sm">
                            <i class="bi bi-trash me-1"></i> Delete Repair Order
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection