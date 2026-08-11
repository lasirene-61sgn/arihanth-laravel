@extends('super-admin.layouts.app')

@section('title', 'View Work Order')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="card shadow-sm border-0 mb-4 rounded-3">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-file-earmark-text fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fw-semibold tracking-wide small d-block">Work Order Details</span>
                        <h2 class="h3 mb-0 text-dark fw-bold">#{{ $workOrder->work_order_number }}</h2>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @if(!in_array($workOrder->status, ['in_process', 'completed']))
                    <a href="{{ route('super-admin.work-order.edit', $workOrder) }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 shadow-sm">
                        <i class="bi bi-pencil-square"></i>
                        <span>Edit Work Order</span>
                    </a>
                    @endif
                    
                    @if(in_array($workOrder->status, ['in_process', 'for_approval']))
                    <button type="button" class="btn btn-warning text-white d-inline-flex align-items-center gap-2 px-3 shadow-sm" onclick="openSuperAdminReturnModal({{ $workOrder->id }}, {{ $workOrder->superadmin_return_count }})">
                        <i class="bi bi-arrow-return-left"></i>
                        <span>Return Order</span>
                    </button>
                    @endif

                    <div class="dropdown">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-2 px-3 shadow-sm" data-bs-toggle="dropdown">
                            <i class="bi bi-share"></i>
                            <span>Share</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('super-admin.work-order.print', $workOrder) }}" target="_blank">
                                    <i class="bi bi-file-earmark-pdf text-danger fs-5"></i>
                                    <span>Generate PDF</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="https://wa.me/?text=Work Order: {{ $workOrder->work_order_number }}">
                                    <i class="bi bi-whatsapp text-success fs-5"></i>
                                    <span>WhatsApp</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <a href="{{ route('super-admin.work-order.index') }}" class="btn btn-light border d-inline-flex align-items-center gap-2 px-3 shadow-sm">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Work Order Information Card -->
            <div class="card shadow-sm border-0 mb-4 rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle text-primary fs-5"></i>
                    <h5 class="card-title mb-0 fw-bold">General Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Work Order Number</label>
                            <span class="fw-bold text-dark fs-6">{{ $workOrder->work_order_number }}</span>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Product Code</label>
                            <span class="fw-bold text-dark">{{ $product->product_code }}</span>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Design Code</label>
                            <span class="fw-bold text-dark">{{ $product->design_code }}</span>
                        </div>

                        

                        @if($workOrder->return_due_date)
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Return Due Date</label>
                            <span class="fw-bold text-danger"><i class="bi bi-calendar-event me-1"></i>{{ $workOrder->return_due_date->format('d M, Y') }}</span>
                        </div>
                        @endif

                        @if($workOrder->return_note)
                        <div class="col-12">
                            <div class="alert alert-warning mb-0 border-warning-subtle rounded-3 p-3">
                                <label class="fw-semibold text-warning-emphasis small d-block mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Return Note</label>
                                <p class="mb-0 small text-dark">{{ $workOrder->return_note }}</p>
                            </div>
                        </div>
                        @endif

                        @if($workOrder->damaged_image)
                        <div class="col-12">
                            <label class="text-muted small fw-medium d-block mb-2">Damaged Image</label>
                            <div class="border rounded-3 p-1 shadow-sm d-inline-block bg-light overflow-hidden position-relative" style="cursor: pointer;" onclick="openUniversalPreview('{{ asset('storage/' . $workOrder->damaged_image) }}', 'image')">
                                <img src="{{ asset('storage/' . $workOrder->damaged_image) }}" class="rounded-2" style="max-height: 180px; width: auto; object-fit: contain;">
                            </div>
                        </div>
                        @endif

                        <div class="col-12"><hr class="my-2 border-light"></div>

                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Customer Name</label>
                            <span class="fw-bold text-dark">{{ $workOrder->customer_name }}</span>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">BP Code</label>
                            <span class="fw-semibold text-dark">{{ $workOrder->bp_code ?? '-' }}</span>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Product Name</label>
                            <span class="fw-bold text-dark">{{ $workOrder->product_name }}</span>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Quantity</label>
                            <span class="fw-semibold text-dark">{{ $workOrder->quantity }} <span class="text-muted">({{ $workOrder->type }})</span></span>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Category</label>
                            <span class="fw-semibold text-dark">{{ $workOrder->productCategory->name ?? $workOrder->product_category ?? 'N/A' }}</span>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Sub Category</label>
                            <span class="fw-semibold text-dark">{{ $workOrder->subcategoryRelation->name ?? $workOrder->subcategory ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Physical Properties Card -->
            <div class="card shadow-sm border-0 mb-4 rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light d-flex align-items-center gap-2">
                    <i class="bi bi-sliders text-primary fs-5"></i>
                    <h5 class="card-title mb-0 fw-bold">Physical Properties</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 border border-light">
                                <span class="text-muted small d-block mb-1">Weight</span>
                                <span class="fw-bold text-dark fs-6">{{ $workOrder->weight_from }} - {{ $workOrder->weight_to }} g</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 border border-light">
                                <span class="text-muted small d-block mb-1">Hook</span>
                                <span class="fw-bold text-dark fs-6">{{ $workOrder->hook ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 border border-light">
                                <span class="text-muted small d-block mb-1">Stone</span>
                                <span class="fw-bold text-dark fs-6">{{ $workOrder->stone ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 border border-light">
                                <span class="text-muted small d-block mb-1">HUID</span>
                                <span class="fw-bold text-dark fs-6">{{ $workOrder->hallmark ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 border border-light">
                                <span class="text-muted small d-block mb-1">Size</span>
                                <span class="fw-bold text-dark fs-6">{{ $workOrder->size ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 border border-light">
                                <span class="text-muted small d-block mb-1">Length</span>
                                <span class="fw-bold text-dark fs-6">{{ $workOrder->length ?: 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 border border-light">
                                <span class="text-muted small d-block mb-1">Rodium</span>
                                <span class="fw-bold text-dark fs-6">{{ $workOrder->rodium ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline Card -->
            <div class="card shadow-sm border-0 mb-4 rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-primary fs-5"></i>
                    <h5 class="card-title mb-0 fw-bold">Order Tracking Timeline</h5>
                </div>
                <div class="card-body p-4">
                    <div class="position-relative ps-4 ms-2" style="border-left: 2px dashed #dee2e6;">
                        <!-- Created -->
                        <div class="position-relative mb-4">
                            <span class="position-absolute bg-primary rounded-circle shadow-sm" style="width: 14px; height: 14px; left: -32px; top: 4px;"></span>
                            <h6 class="mb-1 fw-bold text-dark">Order Created</h6>
                            @php 
                                $creator = $workOrder->creator_details;
                                $creatorCode = !empty($creator['user_code']) && $creator['user_code'] !== 'N/A' ? $creator['user_code'] : (!empty($creator['bp_code']) && $creator['bp_code'] !== 'N/A' ? $creator['bp_code'] : $creator['type']);
                            @endphp
                            <p class="mb-1 text-muted small">Created By: <span class="badge bg-info-subtle text-info border border-info-subtle me-1">{{ $creatorCode }}</span> <span class="fw-semibold text-dark">{{ $creator['name'] ?? 'N/A' }}</span></p>
                            <small class="text-secondary d-inline-flex align-items-center gap-1"><i class="bi bi-clock"></i> {{ $workOrder->created_at ? $workOrder->created_at->timezone('Asia/Kolkata')->format('d M Y h:i A') : 'N/A' }}</small>
                        </div>

                        <!-- Due Date -->
                        <div class="position-relative mb-4">
                            <span class="position-absolute bg-warning rounded-circle shadow-sm" style="width: 14px; height: 14px; left: -32px; top: 4px;"></span>
                            <h6 class="mb-1 fw-bold text-dark">Customer Due Date</h6>
                            <small class="text-secondary d-inline-flex align-items-center gap-1"><i class="bi bi-calendar-check"></i> {{ $workOrder->due_date ? $workOrder->due_date->format('d M Y') : 'Not Set' }}</small>
                        </div>

                        <!-- Allocated -->
                        @if($workOrder->allocated_craftsman_bp_code)
                        <div class="position-relative mb-4">
                            <span class="position-absolute bg-info rounded-circle shadow-sm" style="width: 14px; height: 14px; left: -32px; top: 4px;"></span>
                            <h6 class="mb-1 fw-bold text-dark">Allocated to Craftsman</h6>
                            @php 
                                $allocator = $workOrder->allocator_details;
                                $allocatorCode = !empty($allocator['user_code']) && $allocator['user_code'] !== 'N/A' ? $allocator['user_code'] : (!empty($allocator['bp_code']) && $allocator['bp_code'] !== 'N/A' ? $allocator['bp_code'] : $allocator['type']);
                            @endphp
                            <p class="mb-1 text-muted small">Allocated By: <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle me-1">{{ $allocatorCode }}</span> <span class="fw-semibold text-dark">{{ $allocator['name'] ?? 'Admin' }}</span></p>
                            <p class="mb-1 text-muted small">Allocated To: <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle me-1">{{ $workOrder->craftsman->craftman_code ?? 'N/A' }}</span> <span class="fw-semibold text-dark">{{ $workOrder->craftsman->full_name ?? $workOrder->craftsman->name ?? 'N/A' }}</span></p>
                            <small class="text-secondary d-inline-flex align-items-center gap-1"><i class="bi bi-clock"></i> {{ $workOrder->allocated_at ? \Carbon\Carbon::parse($workOrder->allocated_at)->timezone('Asia/Kolkata')->format('d M Y h:i A') : 'N/A' }}</small>
                        </div>
                        @endif

                        <!-- Craftsman Due Date -->
                        @if($workOrder->craftsman_due_date)
                        <div class="position-relative mb-4">
                            <span class="position-absolute bg-danger rounded-circle shadow-sm" style="width: 14px; height: 14px; left: -32px; top: 4px;"></span>
                            <h6 class="mb-1 fw-bold text-dark">Craftsman Due Date</h6>
                            <small class="text-secondary d-inline-flex align-items-center gap-1"><i class="bi bi-calendar-event"></i> {{ $workOrder->craftsman_due_date->format('d M Y') }}</small>
                        </div>
                        @endif

                        <!-- Accepted by Craftsman -->
                        @if(in_array($workOrder->craftsman_status, ['in_process', 'completed']) && $workOrder->allocated_craftsman_bp_code)
                        <div class="position-relative mb-4">
                            <span class="position-absolute bg-primary rounded-circle shadow-sm" style="width: 14px; height: 14px; left: -32px; top: 4px;"></span>
                            <h6 class="mb-1 fw-bold text-dark">Accepted by Craftsman</h6>
                            <p class="mb-1 text-muted small">Accepted By: <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle me-1">{{ $workOrder->craftsman->craftman_code ?? 'N/A' }}</span> <span class="fw-semibold text-dark">{{ $workOrder->craftsman->full_name ?? $workOrder->craftsman->name ?? 'N/A' }}</span></p>
                            <small class="text-secondary d-inline-flex align-items-center gap-1"><i class="bi bi-clock"></i> {{ $workOrder->craftsman_accepted_at ? \Carbon\Carbon::parse($workOrder->craftsman_accepted_at)->timezone('Asia/Kolkata')->format('d M Y h:i A') : ($workOrder->updated_at ? $workOrder->updated_at->timezone('Asia/Kolkata')->format('d M Y h:i A') : 'N/A') }}</small>
                        </div>
                        @endif

                        <!-- Completed by Craftsman -->
                        @if(strtolower($workOrder->craftsman_status) === 'completed' && $workOrder->allocated_craftsman_bp_code)
                        <div class="position-relative mb-4">
                            <span class="position-absolute bg-info rounded-circle shadow-sm" style="width: 14px; height: 14px; left: -32px; top: 4px;"></span>
                            <h6 class="mb-1 fw-bold text-dark">Completed by Craftsman</h6>
                            <p class="mb-1 text-muted small">Completed By: <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle me-1">{{ $workOrder->craftsman->craftman_code ?? 'N/A' }}</span> <span class="fw-semibold text-dark">{{ $workOrder->craftsman->full_name ?? $workOrder->craftsman->name ?? 'Craftsman' }}</span></p>
                            <small class="text-secondary d-inline-flex align-items-center gap-1"><i class="bi bi-clock"></i> {{ $workOrder->craftsman_completed_at ? \Carbon\Carbon::parse($workOrder->craftsman_completed_at)->timezone('Asia/Kolkata')->format('d M Y h:i A') : ($workOrder->updated_at ? $workOrder->updated_at->timezone('Asia/Kolkata')->format('d M Y h:i A') : 'N/A') }}</small>
                        </div>
                        @endif

                        <!-- Returned -->
                        @if($workOrder->admin_return_count > 0 || $workOrder->superadmin_return_count > 0)
                        <div class="position-relative mb-4">
                            <span class="position-absolute bg-danger rounded-circle shadow-sm" style="width: 14px; height: 14px; left: -32px; top: 4px;"></span>
                            <h6 class="mb-1 fw-bold text-danger">Work Order Returned</h6>
                            <p class="mb-1 text-muted small">Returned <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $workOrder->admin_return_count + $workOrder->superadmin_return_count }} time(s)</span></p>
                            @if($workOrder->return_note)
                                <div class="p-2 bg-light border rounded-2 small text-dark mt-1">{{ $workOrder->return_note }}</div>
                            @endif
                        </div>
                        @endif

                        <!-- Approved & Completed -->
                        @if(strtolower($workOrder->status) === 'completed')
                        <div class="position-relative">
                            <span class="position-absolute bg-success rounded-circle shadow-sm" style="width: 14px; height: 14px; left: -32px; top: 4px;"></span>
                            <h6 class="mb-1 fw-bold text-success">Order Approved & Completed</h6>
                            @php 
                                $approver = $workOrder->approver_details;
                                $approverCode = !empty($approver['user_code']) && $approver['user_code'] !== 'N/A' ? $approver['user_code'] : (!empty($approver['bp_code']) && $approver['bp_code'] !== 'N/A' ? $approver['bp_code'] : $approver['type']);
                            @endphp
                            <p class="mb-1 text-muted small">Approved By: <span class="badge bg-success-subtle text-success border border-success-subtle me-1">{{ $approverCode }}</span> <span class="fw-semibold text-dark">{{ $approver['name'] ?? 'Admin' }}</span></p>
                            <small class="text-secondary d-inline-flex align-items-center gap-1"><i class="bi bi-clock"></i> {{ $workOrder->updated_at ? $workOrder->updated_at->timezone('Asia/Kolkata')->format('d M Y h:i A') : 'N/A' }}</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column (Sidebar) -->
        <div class="col-lg-4">
            <!-- Product Gallery Card -->
            <div class="card shadow-sm border-0 mb-4 rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light d-flex align-items-center gap-2">
                    <i class="bi bi-images text-primary fs-5"></i>
                    <h5 class="card-title mb-0 fw-bold">Product Gallery</h5>
                </div>
                <div class="card-body p-3">
                    @php $productGallery = $workOrder->product_gallery_images; @endphp

                    @if(count($productGallery) > 0)
                        <div class="row g-2">
                            @foreach($productGallery as $imageUrl)
                                @php $isPdf = str_ends_with(strtolower($imageUrl), '.pdf'); @endphp
                                <div class="col-6">
                                    @if($isPdf)
                                        <div class="border rounded-3 shadow-sm bg-light d-flex align-items-center justify-content-center p-1 overflow-hidden" onclick="openUniversalPreview('{{ $imageUrl }}', 'pdf')" style="cursor: pointer; height: 120px;">
                                            <canvas class="pdf-canvas img-fluid rounded" data-url="{{ $imageUrl }}" style="width: 100%; height: 100%; object-fit: contain;"></canvas>
                                        </div>
                                    @else
                                        <div class="border rounded-3 shadow-sm bg-light overflow-hidden" onclick="openUniversalPreview('{{ $imageUrl }}', 'image')" style="cursor: pointer; height: 120px;">
                                            <img src="{{ $imageUrl }}" class="w-100 h-100 rounded" style="object-fit: cover; transition: transform 0.2s;">
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 bg-light rounded-3 border border-dashed text-muted">
                            <i class="bi bi-image text-secondary" style="font-size: 2.5rem;"></i>
                            <p class="mt-2 mb-0 small fw-medium">No Product Images Available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Completion Proof Gallery -->
            @php $completionProofs = $workOrder->completion_proof_images; @endphp
            @if(count($completionProofs) > 0)
                <div class="card shadow-sm border-0 mb-4 rounded-3 border-start border-4 border-info">
                    <div class="card-header bg-info bg-opacity-10 py-3 border-bottom border-info-subtle d-flex align-items-center gap-2">
                        <i class="bi bi-patch-check-fill text-info fs-5"></i>
                        <h5 class="card-title mb-0 fw-bold text-info-emphasis">Completion Proof</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2">
                            @foreach($completionProofs as $imageUrl)
                                @php $isPdf = str_ends_with(strtolower($imageUrl), '.pdf'); @endphp
                                <div class="col-6">
                                    @if($isPdf)
                                        <div class="border rounded-3 shadow-sm bg-light d-flex align-items-center justify-content-center p-1 overflow-hidden" onclick="openUniversalPreview('{{ $imageUrl }}', 'pdf')" style="cursor: pointer; height: 120px;">
                                            <canvas class="pdf-canvas img-fluid rounded" data-url="{{ $imageUrl }}" style="width: 100%; height: 100%; object-fit: contain;"></canvas>
                                        </div>
                                    @else
                                        <div class="border rounded-3 shadow-sm bg-light overflow-hidden" onclick="openUniversalPreview('{{ $imageUrl }}', 'image')" style="cursor: pointer; height: 120px;">
                                            <img src="{{ $imageUrl }}" class="w-100 h-100 rounded" style="object-fit: cover; transition: transform 0.2s;">
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Narrations Card -->
            <div class="card shadow-sm border-0 mb-4 rounded-3 bg-light">
                <div class="card-header bg-transparent py-3 border-bottom border-light d-flex align-items-center gap-2">
                    <i class="bi bi-chat-left-text text-primary fs-5"></i>
                    <h5 class="card-title mb-0 fw-bold">Narrations</h5>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="text-muted small fw-semibold d-block mb-1">Admin Note</label>
                        <div class="p-3 bg-white border rounded-3 text-dark small">
                            {{ $workOrder->narration_admin ?? 'No admin notes recorded.' }}
                        </div>
                    </div>
                    <div>
                        <label class="text-muted small fw-semibold d-block mb-1">Craftsman Note</label>
                        <div class="p-3 bg-white border rounded-3 text-dark small">
                            {{ $workOrder->narration_craftsman ?? 'No craftsman notes recorded.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SuperAdmin Return Modal -->
<div id="superAdminReturnModal" class="modal fade" tabindex="-1" aria-labelledby="modal-title" aria-hidden="true" style="display: none; background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                    <i class="bi bi-arrow-return-left fs-2"></i>
                </div>

                <h4 class="fw-bold text-dark mb-2">Return Work Order</h4>
                <p class="text-muted small mb-4" id="superAdminReturnModalMsg"></p>

                <form id="superAdminReturnForm" method="POST" action="{{ route('super-admin.work-order.return', $workOrder->id) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="text-start mb-3">
                        <label class="form-label small fw-semibold text-secondary">Return Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="return_due_date" required class="form-control rounded-3">
                    </div>

                    <div class="text-start mb-3">
                        <label class="form-label small fw-semibold text-secondary">Return Note</label>
                        <textarea name="return_note" rows="2" class="form-control rounded-3" placeholder="Optional return reason/note"></textarea>
                    </div>

                    <div class="text-start mb-3">
                        <label class="form-label small fw-semibold text-secondary">Damaged Image</label>
                        <input type="file" name="damaged_image" accept="image/*" class="form-control rounded-3">
                    </div>

                    <div id="superAdminReturnOtpSection" class="text-start border-top pt-3 mt-3" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-2">Request OTP to Return</label>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" onclick="sendSuperAdminReturnOtp('sms')" class="btn btn-sm btn-outline-danger rounded-2">
                                    <i class="bi bi-chat-left-text me-1"></i> SMS
                                </button>
                                <button type="button" onclick="sendSuperAdminReturnOtp('whatsapp')" class="btn btn-sm btn-outline-success rounded-2">
                                    <i class="bi bi-whatsapp me-1"></i> WhatsApp
                                </button>
                                <span id="superAdminReturnOtpStatus" class="ms-2 text-success small" style="display: none;">OTP Sent!</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Enter OTP <span class="text-danger">*</span></label>
                            <input type="text" name="otp" id="superAdminReturnOtpInput" class="form-control rounded-3" placeholder="6-digit OTP">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-2 border-top">
                        <button type="button" onclick="closeSuperAdminReturnModal()" class="btn btn-light px-4 rounded-3">Cancel</button>
                        <button type="submit" class="btn btn-warning text-white px-4 rounded-3">Confirm Return</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let currentReturnWoId = null;

    function openSuperAdminReturnModal(woId, returnCount) {
        currentReturnWoId = woId;

        if (returnCount >= 6) {
            document.getElementById('superAdminReturnOtpSection').style.display = 'block';
            document.getElementById('superAdminReturnOtpInput').required = true;
            document.getElementById('superAdminReturnModalMsg').innerText = "You have reached the maximum return limit. OTP is required to return again.";
        } else {
            document.getElementById('superAdminReturnOtpSection').style.display = 'none';
            document.getElementById('superAdminReturnOtpInput').required = false;
            document.getElementById('superAdminReturnModalMsg').innerText = "Please provide the due date and an optional note for returning this work order. (Returns remaining before OTP: " + (6 - returnCount) + ")";
        }

        document.getElementById('superAdminReturnModal').style.display = 'block';
        document.getElementById('superAdminReturnModal').classList.add('show');
    }

    function closeSuperAdminReturnModal() {
        document.getElementById('superAdminReturnModal').style.display = 'none';
        document.getElementById('superAdminReturnModal').classList.remove('show');
    }

    function sendSuperAdminReturnOtp(method) {
        document.getElementById('superAdminReturnOtpStatus').style.display = 'inline-block';
        document.getElementById('superAdminReturnOtpStatus').innerText = "Sending...";
        document.getElementById('superAdminReturnOtpStatus').className = "ms-2 text-warning small";

        fetch(`/super-admin/work-order/${currentReturnWoId}/send-return-otp`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    delivery_method: method
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('superAdminReturnOtpStatus').innerText = "OTP Sent!";
                    document.getElementById('superAdminReturnOtpStatus').className = "ms-2 text-success small";
                } else {
                    document.getElementById('superAdminReturnOtpStatus').innerText = "Failed: " + data.message;
                    document.getElementById('superAdminReturnOtpStatus').className = "ms-2 text-danger small";
                }
            })
            .catch(err => {
                document.getElementById('superAdminReturnOtpStatus').innerText = "Error sending OTP";
                document.getElementById('superAdminReturnOtpStatus').className = "ms-2 text-danger small";
            });
    }
</script>
@endsection