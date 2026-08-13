@extends('craftsman.layouts.app')

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
                        <span class="text-muted text-uppercase fw-semibold tracking-wide small d-block">Craftsman Work Order</span>
                        @if(!empty($workOrder->work_order_number))
                            <h2 class="h3 mb-0 text-dark fw-bold">#{{ $workOrder->work_order_number }}</h2>
                        @endif
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="dropdown">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-2 px-3 shadow-sm" data-bs-toggle="dropdown">
                            <i class="bi bi-share"></i>
                            <span>Share</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('craftsman.work-order.print', $workOrder) }}" target="_blank">
                                    <i class="bi bi-file-earmark-pdf text-danger fs-5"></i>
                                    <span>Generate PDF/Image</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="#" onclick="window.open('{{ route('craftsman.work-order.print', $workOrder) }}', '_blank', 'width=800,height=600'); return false;">
                                    <i class="bi bi-image text-primary fs-5"></i>
                                    <span>View as Image</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="https://wa.me/?text=Work Order: {{ $workOrder->work_order_number }} - Customer: {{ $workOrder->customer_name }}" target="_blank">
                                    <i class="bi bi-whatsapp text-success fs-5"></i>
                                    <span>WhatsApp</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="mailto:?subject=Work Order: {{ $workOrder->work_order_number }}&body=Work Order Details:%0A%0ACustomer: {{ $workOrder->customer_name }}%0AProduct: {{ $workOrder->product_name }}%0AQuantity: {{ $workOrder->quantity }}%0AStatus: {{ ucfirst($workOrder->craftsman_status ?? $workOrder->status) }}%0ADue Date: {{ $workOrder->due_date ? $workOrder->due_date->format('d M, Y') : '' }}">
                                    <i class="bi bi-envelope text-primary fs-5"></i>
                                    <span>Email</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <a href="{{ route('craftsman.work-order.index') }}" class="btn btn-light border d-inline-flex align-items-center gap-2 px-3 shadow-sm">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to List</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content (Left Column) -->
        <div class="col-lg-8">
            <!-- Work Order Details Card -->
            <div class="card shadow-sm border-0 mb-4 rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle text-primary fs-5"></i>
                    <h5 class="card-title mb-0 fw-bold">Work Order Details</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        @if(!empty($workOrder->work_order_number))
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Work Order Number</label>
                            <span class="fw-bold text-dark fs-6">{{ $workOrder->work_order_number }}</span>
                        </div>
                        @endif

                        @if(!empty($workOrder->product_name))
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Product Name</label>
                            <span class="fw-bold text-dark fs-6">{{ $workOrder->product_name }}</span>
                        </div>
                        @endif

                        @if(!empty($workOrder->quantity))
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Quantity</label>
                            <span class="fw-semibold text-dark fs-6">{{ $workOrder->quantity }}</span>
                        </div>
                        @endif

                        @if(!empty($workOrder->craftsman_status))
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Status</label>
                            <div>
                                @if($workOrder->craftsman_status == 'allocated')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fs-7">Allocated</span>
                                @elseif($workOrder->craftsman_status == 'in_process')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 fs-7">In Process</span>
                                @elseif($workOrder->craftsman_status == 'completed')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 fs-7">Completed</span>
                                @elseif($workOrder->craftsman_status == 'rejected')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-7">Rejected</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fs-7">{{ ucfirst($workOrder->craftsman_status) }}</span>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if(!empty($workOrder->status))
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Approval Status</label>
                            <div>
                                @if($workOrder->status == 'new')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fs-7">New</span>
                                @elseif($workOrder->status == 'allocated')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-7">Allocated</span>
                                @elseif($workOrder->status == 'for_approval')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 fs-7">For Approval</span>
                                @elseif($workOrder->status == 'completed')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-7">Approved</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fs-7">{{ ucfirst($workOrder->status) }}</span>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if(!empty($workOrder->craftsman_due_date))
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Due Date</label>
                            <span class="fw-bold text-danger"><i class="bi bi-calendar-event me-1"></i>{{ $workOrder->craftsman_due_date->format('d M, Y') }}</span>
                        </div>
                        @endif

                        @if(!empty($workOrder->return_note))
                        <div class="col-12">
                            <div class="alert alert-warning mb-0 border-warning-subtle rounded-3 p-3">
                                <label class="fw-semibold text-warning-emphasis small d-block mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Return Note</label>
                                @if($workOrder->return_due_date)
                                    <p class="mb-2 small text-danger fw-bold"><i class="bi bi-calendar-event me-1"></i>Return Due Date: {{ $workOrder->return_due_date->format('d M, Y') }}</p>
                                @endif
                                <p class="mb-0 small text-dark">{{ $workOrder->return_note }}</p>
                            </div>
                        </div>
                        @endif

                        @if(!empty($workOrder->damaged_image))
                        <div class="col-12">
                            <label class="text-muted small fw-medium d-block mb-2">Damaged Image</label>
                            <div class="border rounded-3 p-1 shadow-sm d-inline-block bg-light overflow-hidden position-relative" style="cursor: pointer;" onclick="openUniversalPreview('{{ asset('storage/' . $workOrder->damaged_image) }}', 'image')">
                                <img src="{{ asset('storage/' . $workOrder->damaged_image) }}" class="rounded-2" style="max-height: 180px; width: auto; object-fit: contain;">
                            </div>
                        </div>
                        @endif

                        @php
                            $categoryName = $workOrder->productCategory->name ?? (is_array($workOrder->product_category) ? implode(', ', $workOrder->product_category) : $workOrder->product_category);
                            $subcategoryName = $workOrder->subcategoryRelation->name ?? (is_array($workOrder->subcategory) ? implode(', ', $workOrder->subcategory) : $workOrder->subcategory);
                        @endphp

                        @if(!empty($categoryName) && $categoryName !== 'N/A' || !empty($subcategoryName) && $subcategoryName !== 'N/A' || !empty($workOrder->type) && $workOrder->type !== 'N/A' || !empty($workOrder->weight_from) && $workOrder->weight_from !== 'N/A' || !empty($workOrder->weight_to) && $workOrder->weight_to !== 'N/A' || !empty($workOrder->hallmark) && $workOrder->hallmark !== 'N/A' || !empty($workOrder->rodium) && $workOrder->rodium !== 'N/A' || !empty($workOrder->hook) && $workOrder->hook !== 'N/A' || !empty($workOrder->size) && $workOrder->size !== 'N/A' || !empty($workOrder->stone) && $workOrder->stone !== 'N/A' || !empty($workOrder->enamel) && $workOrder->enamel !== 'N/A' || !empty($workOrder->length) && $workOrder->length !== 'N/A' || !empty($workOrder->screw_name))
                            <div class="col-12"><hr class="my-2 border-light"></div>
                        @endif

                        @if(!empty($categoryName) && $categoryName !== 'N/A')
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Product Category</label>
                            <span class="fw-semibold text-dark">{{ $categoryName }}</span>
                        </div>
                        @endif

                        @if(!empty($subcategoryName) && $subcategoryName !== 'N/A')
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Product Subcategory</label>
                            <span class="fw-semibold text-dark">{{ $subcategoryName }}</span>
                        </div>
                        @endif

                        @if(!empty($workOrder->type) && $workOrder->type !== 'N/A')
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Type</label>
                            <span class="fw-semibold text-dark">{{ $workOrder->type }}</span>
                        </div>
                        @endif

                        @if(!empty($workOrder->weight_from) && $workOrder->weight_from !== 'N/A')
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Weight From</label>
                            <span class="fw-semibold text-dark">{{ $workOrder->weight_from }}</span>
                        </div>
                        @endif

                        @if(!empty($workOrder->weight_to) && $workOrder->weight_to !== 'N/A')
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Weight To</label>
                            <span class="fw-semibold text-dark">{{ $workOrder->weight_to }}</span>
                        </div>
                        @endif

                        @if(!empty($workOrder->hallmark) && $workOrder->hallmark !== 'N/A')
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Hallmark</label>
                            <span class="fw-semibold text-dark">{{ $workOrder->hallmark }}</span>
                        </div>
                        @endif

                        @if(!empty($workOrder->rodium) && $workOrder->rodium !== 'N/A')
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Rodium</label>
                            <span class="fw-semibold text-dark">{{ $workOrder->rodium }}</span>
                        </div>
                        @endif

                        @if(!empty($workOrder->hook) && $workOrder->hook !== 'N/A')
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Hook</label>
                            <span class="fw-semibold text-dark">{{ $workOrder->hook }}</span>
                        </div>
                        @endif

                        @if(!empty($workOrder->size) && $workOrder->size !== 'N/A')
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Size</label>
                            <span class="fw-semibold text-dark">{{ $workOrder->size }}</span>
                        </div>
                        @endif

                        @if(!empty($workOrder->stone) && $workOrder->stone !== 'N/A')
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Stone</label>
                            <span class="fw-semibold text-dark">{{ $workOrder->stone }}</span>
                        </div>
                        @endif

                        @if(!empty($workOrder->enamel) && $workOrder->enamel !== 'N/A')
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Enamel</label>
                            <span class="fw-semibold text-dark">{{ $workOrder->enamel }}</span>
                        </div>
                        @endif

                        @if(!empty($workOrder->length) && $workOrder->length !== 'N/A')
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Length</label>
                            <span class="fw-semibold text-dark">{{ $workOrder->length }}</span>
                        </div>
                        @endif

                        @if(!empty($workOrder->screw_name))
                        <div class="col-sm-6 col-md-4">
                            <label class="text-muted small fw-medium d-block mb-1">Screw</label>
                            <span class="fw-semibold text-dark">{{ $workOrder->screw_name }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Narrations Card -->
            @php
                $narrationText = is_array($workOrder->narration_craftsman) ? implode(', ', array_filter($workOrder->narration_craftsman)) : $workOrder->narration_craftsman;
            @endphp

            @if(!empty($narrationText) && $narrationText !== 'N/A')
            <div class="card shadow-sm border-0 mb-4 rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light d-flex align-items-center gap-2">
                    <i class="bi bi-chat-left-text text-primary fs-5"></i>
                    <h5 class="card-title mb-0 fw-bold">Narrations</h5>
                </div>
                <div class="card-body p-4">
                    <div class="p-3 bg-light rounded-3 border border-light text-dark small">
                        {{ $narrationText }}
                    </div>
                </div>
            </div>
            @endif

            <!-- Action Buttons Based on Status -->
            <div class="card shadow-sm border-0 mb-4 rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light d-flex align-items-center gap-2">
                    <i class="bi bi-gear text-primary fs-5"></i>
                    <h5 class="card-title mb-0 fw-bold">Actions</h5>
                </div>
                <div class="card-body p-4">
                    @if($workOrder->craftsman_status == 'allocated')
                        <div class="d-flex flex-wrap gap-2">
                            <form action="{{ route('craftsman.work-order.accept', $workOrder) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2 px-4 shadow-sm">
                                    <i class="bi bi-play-circle"></i>
                                    <span>Accept & Start Work</span>
                                </button>
                            </form>
                            <form action="{{ route('craftsman.work-order.reject', $workOrder) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger d-inline-flex align-items-center gap-2 px-4 shadow-sm">
                                    <i class="bi bi-x-circle"></i>
                                    <span>Reject Work Order</span>
                                </button>
                            </form>
                        </div>
                    @elseif($workOrder->craftsman_status == 'completed')
                        <button type="button" onclick="showSingleCompleteModal('{{ route('craftsman.work-order.complete', $workOrder) }}')" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-sm">
                            <i class="bi bi-check-all"></i>
                            <span>Mark as Completed</span>
                        </button>
                    @else
                        <p class="text-muted mb-0 fst-italic">No actions available for this work order status.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar (Right Column) -->
        <div class="col-lg-4">
            <!-- Timeline Card -->
            <div class="card shadow-sm border-0 mb-4 rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-primary fs-5"></i>
                    <h5 class="card-title mb-0 fw-bold">Order Tracking Timeline</h5>
                </div>
                <div class="card-body p-4">
                    <div class="position-relative ps-4 ms-2" style="border-left: 2px dashed #dee2e6;">
                        <!-- Allocated -->
                        @if($workOrder->allocated_craftsman_bp_code)
                        <!-- <div class="position-relative mb-4">
                            <span class="position-absolute bg-info rounded-circle shadow-sm" style="width: 14px; height: 14px; left: -32px; top: 4px;"></span>
                            <h6 class="mb-1 fw-bold text-dark">Allocated to Craftsman</h6>
                            @php 
                                $allocator = $workOrder->allocator_details;
                                $allocatorCode = !empty($allocator['user_code']) && $allocator['user_code'] !== 'N/A' ? $allocator['user_code'] : (!empty($allocator['bp_code']) && $allocator['bp_code'] !== 'N/A' ? $allocator['bp_code'] : $allocator['type']);
                            @endphp
                            <p class="mb-1 text-muted small">Allocated By: <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle me-1">{{ $allocatorCode }}</span> <span class="fw-semibold text-dark">{{ $allocator['name'] ?? 'Admin' }}</span></p>
                            <small class="text-secondary d-inline-flex align-items-center gap-1"><i class="bi bi-clock"></i> {{ $workOrder->allocated_at ? \Carbon\Carbon::parse($workOrder->allocated_at)->timezone('Asia/Kolkata')->format('d M Y h:i A') : 'N/A' }}</small>
                        </div> -->
                        @endif

                        <!-- Craftsman Due Date -->
                        @if($workOrder->craftsman_due_date)
                        <div class="position-relative mb-4">
                            <span class="position-absolute bg-danger rounded-circle shadow-sm" style="width: 14px; height: 14px; left: -32px; top: 4px;"></span>
                            <h6 class="mb-1 fw-bold text-dark">Due Date</h6>
                            <small class="text-secondary d-inline-flex align-items-center gap-1"><i class="bi bi-calendar-event"></i> {{ $workOrder->craftsman_due_date->format('d M Y') }}</small>
                        </div>
                        @endif

                        <!-- Accepted by Craftsman -->
                        @if(in_array($workOrder->craftsman_status, ['in_process', 'completed']) && $workOrder->allocated_craftsman_bp_code)
                        <div class="position-relative mb-4">
                            <span class="position-absolute bg-primary rounded-circle shadow-sm" style="width: 14px; height: 14px; left: -32px; top: 4px;"></span>
                            <h6 class="mb-1 fw-bold text-dark">Accepted by Craftsman</h6>
                            <small class="text-secondary d-inline-flex align-items-center gap-1"><i class="bi bi-clock"></i> {{ $workOrder->craftsman_accepted_at ? \Carbon\Carbon::parse($workOrder->craftsman_accepted_at)->timezone('Asia/Kolkata')->format('d M Y h:i A') : ($workOrder->updated_at ? $workOrder->updated_at->timezone('Asia/Kolkata')->format('d M Y h:i A') : 'N/A') }}</small>
                              @if($workOrder->acceptedByStaff && $workOrder->staff_accepted_at)
                                  <p class="mb-1 text-muted small mt-2">Accepted By Staff: <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle me-1">{{ $workOrder->acceptedByStaff->staff_code ?? 'N/A' }}</span> <span class="fw-semibold text-dark">{{ $workOrder->acceptedByStaff->name ?? 'N/A' }}</span></p>
                                  <small class="text-secondary d-inline-flex align-items-center gap-1"><i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($workOrder->staff_accepted_at)->timezone('Asia/Kolkata')->format('d M Y h:i A') }}</small>
                              @endif
                        </div>
                        @endif

                        <!-- Completed by Craftsman -->
                        @if(strtolower($workOrder->craftsman_status) === 'completed' && $workOrder->allocated_craftsman_bp_code)
                        <div class="position-relative mb-4">
                            <span class="position-absolute bg-success rounded-circle shadow-sm" style="width: 14px; height: 14px; left: -32px; top: 4px;"></span>
                            <h6 class="mb-1 fw-bold text-dark">Completed by Craftsman</h6>
                            <small class="text-secondary d-inline-flex align-items-center gap-1"><i class="bi bi-clock"></i> {{ $workOrder->craftsman_completed_at ? \Carbon\Carbon::parse($workOrder->craftsman_completed_at)->timezone('Asia/Kolkata')->format('d M Y h:i A') : ($workOrder->updated_at ? $workOrder->updated_at->timezone('Asia/Kolkata')->format('d M Y h:i A') : 'N/A') }}</small>
                              @if($workOrder->craftsmanStaff && $workOrder->staff_completed_at)
                                  <p class="mb-1 text-muted small mt-2">Completed By Staff: <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle me-1">{{ $workOrder->craftsmanStaff->staff_code ?? 'N/A' }}</span> <span class="fw-semibold text-dark">{{ $workOrder->craftsmanStaff->name ?? 'N/A' }}</span></p>
                                  <small class="text-secondary d-inline-flex align-items-center gap-1"><i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($workOrder->staff_completed_at)->timezone('Asia/Kolkata')->format('d M Y h:i A') }}</small>
                              @endif
                        </div>
                        @endif

                        <!-- Returned -->
                        @if($workOrder->admin_return_count > 0 || $workOrder->superadmin_return_count > 0)
                        <div class="position-relative mb-4">
                            <span class="position-absolute bg-danger rounded-circle shadow-sm" style="width: 14px; height: 14px; left: -32px; top: 4px;"></span>
                            <h6 class="mb-1 fw-bold text-danger">Work Order Returned</h6>
                            <p class="mb-1 text-muted small">Returned <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $workOrder->admin_return_count + $workOrder->superadmin_return_count }} time(s)</span></p>
                            @if($workOrder->return_due_date)
                                <p class="mb-1 text-muted small">Due By: <span class="fw-semibold text-danger">{{ $workOrder->return_due_date->format('d M Y') }}</span></p>
                            @endif
                            @if($workOrder->return_note)
                                <div class="p-2 bg-light border rounded-2 small text-dark mt-1">{{ $workOrder->return_note }}</div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <!-- Product Gallery Card -->
            @php $productGallery = array_filter($workOrder->product_gallery_images ?? []); @endphp

            @if(!empty($productGallery) && count($productGallery) > 0)
            <div class="card shadow-sm border-0 mb-4 rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light d-flex align-items-center gap-2">
                    <i class="bi bi-images text-primary fs-5"></i>
                    <h5 class="card-title mb-0 fw-bold">Product Gallery</h5>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        @foreach($productGallery as $imageUrl)
                            @php $isPdf = str_ends_with(strtolower($imageUrl), '.pdf'); @endphp
                            <div class="col-6">
                                <div class="border rounded-3 shadow-sm bg-light overflow-hidden position-relative" style="cursor: pointer; height: 140px;" onclick="openUniversalPreview('{{ $imageUrl }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                    @if($isPdf)
                                        <div class="h-100 w-100 d-flex align-items-center justify-content-center">
                                            <canvas class="pdf-canvas w-100 h-100 rounded" data-url="{{ $imageUrl }}" data-desired-width="200" style="object-fit: cover;"></canvas>
                                            <i class="bi bi-file-pdf text-danger fs-2 position-absolute opacity-75"></i>
                                        </div>
                                    @else
                                        <img src="{{ $imageUrl }}" class="w-100 h-100 rounded" style="object-fit: cover; transition: transform 0.2s;">
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Completion Proof Gallery -->
            @php
                $completionProofs = array_filter($workOrder->completion_proof_images ?? []);
                $isOrderCompleted = strtolower($workOrder->craftsman_status ?? '') === 'completed';
            @endphp

            @if(!empty($completionProofs) && count($completionProofs) > 0 && $isOrderCompleted)
            <div class="card shadow-sm border-0 mb-4 rounded-3 border-start border-4 border-info">
                <div class="card-header bg-info bg-opacity-10 py-3 border-bottom border-info-subtle d-flex align-items-center gap-2">
                    <i class="bi bi-patch-check-fill text-info fs-5"></i>
                    <h5 class="card-title mb-0 fw-bold text-info-emphasis">Your Completion Proofs</h5>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        @foreach($completionProofs as $imageUrl)
                            @php $isPdf = str_ends_with(strtolower($imageUrl), '.pdf'); @endphp
                            <div class="col-6">
                                <div class="border rounded-3 shadow-sm bg-light overflow-hidden position-relative" style="cursor: pointer; height: 140px;" onclick="openUniversalPreview('{{ $imageUrl }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                    @if($isPdf)
                                        <div class="h-100 w-100 d-flex align-items-center justify-content-center">
                                            <canvas class="pdf-canvas w-100 h-100 rounded" data-url="{{ $imageUrl }}" data-desired-width="200" style="object-fit: cover;"></canvas>
                                            <i class="bi bi-file-pdf text-danger fs-2 position-absolute opacity-75"></i>
                                        </div>
                                    @else
                                        <img src="{{ $imageUrl }}" class="w-100 h-100 rounded" style="object-fit: cover; transition: transform 0.2s;">
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Complete Order Modal -->
<div class="modal fade" id="completeOrderModal" tabindex="-1" aria-labelledby="completeOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="completeOrderForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom border-light py-3">
                    <h5 class="modal-title fw-bold text-dark" id="completeOrderModalLabel">Complete Work Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p id="completeModalMessage" class="text-muted small mb-3">Upload images to document your completed work (optional):</p>
                    <div class="mb-3">
                        <label for="completion_images" class="form-label small fw-semibold text-secondary">Upload Images (Single/Multiple)</label>
                        <input type="file" class="form-control rounded-3" name="images[]" id="completion_images" multiple accept="image/*">
                        <div class="form-text small">Supported formats: JPG, PNG, WEBP. Max size: 5MB per image.</div>
                    </div>
                </div>
                <div class="modal-footer border-top border-light py-3">
                    <button type="button" class="btn btn-light px-4 rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4 rounded-3">Confirm Completion</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const completeOrderModal = new bootstrap.Modal(document.getElementById('completeOrderModal'));
    const completeOrderForm = document.getElementById('completeOrderForm');

    window.showSingleCompleteModal = function(actionUrl) {
        completeOrderForm.action = actionUrl;
        document.getElementById('completion_images').value = "";
        completeOrderModal.show();
    };
</script>
@endsection