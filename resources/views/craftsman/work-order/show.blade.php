@extends('craftsman.layouts.app')

@section('title', 'View Work Order')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">View Work Order</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <!-- Share Buttons -->
                        <!-- <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-share"></i> Share
                        </button> -->
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('craftsman.work-order.print', $workOrder) }}" target="_blank">
                                    <i class="bi bi-file-earmark-pdf text-danger"></i> Generate PDF/Image
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="window.open('{{ route('craftsman.work-order.print', $workOrder) }}', '_blank', 'width=800,height=600'); return false;">
                                    <i class="bi bi-image text-primary"></i> View as Image
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="https://wa.me/?text=Work Order: {{ $workOrder->work_order_number }} - Customer: {{ $workOrder->customer_name }}" target="_blank">
                                    <i class="bi bi-whatsapp text-success"></i> WhatsApp
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="mailto:?subject=Work Order: {{ $workOrder->work_order_number }}&body=Work Order Details:%0A%0ACustomer: {{ $workOrder->customer_name }}%0AProduct: {{ $workOrder->product_name }}%0AQuantity: {{ $workOrder->quantity }}%0AStatus: {{ ucfirst($workOrder->craftsman_status ?? $workOrder->status) }}%0ADue Date: {{ $workOrder->due_date ? $workOrder->due_date->format('d M, Y') : 'N/A' }}">
                                    <i class="bi bi-envelope text-primary"></i> Email
                                </a>
                            </li>
                        </ul>
                        <a href="{{ route('craftsman.work-order.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>Work Order Details</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Work Order Number</label>
                                    <p>{{ $workOrder->work_order_number }}</p>
                                </div>

                                <!--<div class="col-md-6 mb-3">
                                    <label class="text-muted">Customer Name</label>
                                    <p>{{ $workOrder->customer_name }}</p>
                                </div>-->

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Product Name</label>
                                    <p>{{ $workOrder->product_name }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Quantity</label>
                                    <p>{{ $workOrder->quantity }}</p>
                                </div>

                                <!--<div class="col-md-6 mb-3">
                                    <label class="text-muted">Due Date</label>
                                    <p>{{ $workOrder->due_date ? $workOrder->due_date->format('d M, Y') : 'N/A' }}</p>
                                </div>-->

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Status</label>
                                    <p>
                                        @if($workOrder->craftsman_status == 'allocated')
                                        <span class="badge bg-primary">Allocated</span>
                                        @elseif($workOrder->craftsman_status == 'in_process')
                                        <span class="badge bg-warning">In Process</span>
                                        @elseif($workOrder->craftsman_status == 'completed')
                                        <span class="badge bg-info">Completed</span>
                                        @elseif($workOrder->craftsman_status == 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                        @else
                                        <span class="badge bg-secondary">{{ ucfirst($workOrder->craftsman_status ?? 'Unknown') }}</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Approval Status</label>
                                    <p>
                                        @if($workOrder->status == 'new')
                                        <span class="badge bg-primary">New</span>
                                        @elseif($workOrder->status == 'allocated')
                                        <span class="badge bg-success">Allocated</span>
                                        @elseif($workOrder->status == 'for_approval')
                                        <span class="badge bg-warning">For Approval</span>
                                        @elseif($workOrder->status == 'completed')
                                        <span class="badge bg-success">Approved</span>
                                        @else
                                        <span class="badge bg-secondary">{{ ucfirst($workOrder->status) }}</span>
                                        @endif
                                    </p>
                                </div>

                                @if($workOrder->return_note)
                                <div class="col-12 mb-3">
                                    <label class="text-muted d-block">Return Note</label>
                                    <div class="p-2 bg-light border rounded small">{{ $workOrder->return_note }}</div>
                                </div>
                                @endif
                                @if($workOrder->damaged_image)
                                <div class="col-12 mb-3">
                                    <label class="text-muted d-block">Damaged Image</label>
                                    <div class="d-block border rounded shadow-sm" style="cursor: pointer; max-height: 200px; width: fit-content; overflow: hidden;" onclick="openUniversalPreview('{{ asset('storage/' . $workOrder->damaged_image) }}', 'image')">
                                        <img src="{{ asset('storage/' . $workOrder->damaged_image) }}" class="img-fluid" style="max-height: 200px; object-fit: contain;">
                                    </div>
                                </div>
                                @endif

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Product Category</label>
                                    <p>{{ $workOrder->productCategory->name ?? (is_array($workOrder->product_category) ? implode(', ', $workOrder->product_category) : ($workOrder->product_category ?? 'N/A')) }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Product Subcategory</label>
                                    <p>{{ $workOrder->subcategoryRelation->name ?? (is_array($workOrder->subcategory) ? implode(', ', $workOrder->subcategory) : ($workOrder->subcategory ?? 'N/A')) }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Type</label>
                                    <p>{{ $workOrder->type ?? 'N/A' }}</p>
                                </div>

                                <!-- <div class="col-md-6 mb-3">
                                    <label class="text-muted">Order Type</label>
                                    <p>{{ $workOrder->order_type ?? 'N/A' }}</p>
                                </div> -->

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Weight From</label>
                                    <p>{{ $workOrder->weight_from ?? 'N/A' }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Weight To</label>
                                    <p>{{ $workOrder->weight_to ?? 'N/A' }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Hallmark</label>
                                    <p>{{ $workOrder->hallmark ?? 'N/A' }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Rodium</label>
                                    <p>{{ $workOrder->rodium ?? 'N/A' }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Hook</label>
                                    <p>{{ $workOrder->hook ?? 'N/A' }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Size</label>
                                    <p>{{ $workOrder->size ?? 'N/A' }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Stone</label>
                                    <p>{{ $workOrder->stone ?? 'N/A' }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Enamel</label>
                                    <p>{{ $workOrder->enamel ?? 'N/A' }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Length</label>
                                    <p>{{ $workOrder->length ?? 'N/A' }}</p>
                                </div>
                                @if($workOrder->screw_name)
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Screw</label>
                                    <p>{{ $workOrder->screw_name }}</p>
                                </div>
                                @endif
                                <!--<div class="col-md-6 mb-3">-->
                                <!--    <label class="text-muted">Order Date</label>-->
                                <!--    <p>{{ $workOrder->due_date ? $workOrder->due_date->format('d M, Y') : 'N/A' }}</p>-->
                                <!--</div>-->

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Due Date</label>
                                    <p>{{ $workOrder->craftsman_due_date ? $workOrder->craftsman_due_date->format('d M, Y') : 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h4>Narrations</h4>
                        </div>
                        <div class="card-body">
                            <!--<div class="mb-3">
                                <label class="text-muted">Narration for Craftsman</label>
                                <p>{{ $workOrder->narration_craftsman ?? 'N/A' }}</p>
                            </div>-->

                            <div class="mb-3">
                                <label class="text-muted">Narration</label>
                                <p>{{ is_array($workOrder->narration_craftsman) ? implode(', ', $workOrder->narration_craftsman) : ($workOrder->narration_craftsman ?? 'N/A') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons Based on Status -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h4>Actions</h4>
                        </div>
                        <div class="card-body">
                            @if($workOrder->craftsman_status == 'allocated')
                            <form action="{{ route('craftsman.work-order.accept', $workOrder) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-play-circle me-1"></i> Accept & Start Work
                                </button>
                            </form>
                            <form action="{{ route('craftsman.work-order.reject', $workOrder) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-x-circle me-1"></i> Reject Work Order
                                </button>
                            </form>
                            @elseif($workOrder->craftsman_status == 'completed')
                            <button type="button" onclick="showSingleCompleteModal('{{ route('craftsman.work-order.complete', $workOrder) }}')" class="btn btn-primary">
                                <i class="bi bi-check-all me-1"></i> Mark as Completed
                            </button>
                            @else
                            <p>No actions available for this work order status.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center">
                            <i class="bi bi-images me-2 text-primary"></i>
                            <h4 class="mb-0">Product Gallery</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @php
                                $productGallery = $workOrder->product_gallery_images;
                                @endphp

                                @if(count($productGallery) > 0)
                                @foreach($productGallery as $imageUrl)
                                @php $isPdf = str_ends_with(strtolower($imageUrl), '.pdf'); @endphp
                                <div class="col-6">
                                    <div class="position-relative border rounded p-1 bg-white shadow-sm"
                                        style="cursor: pointer; height: 160px;"
                                        onclick="openUniversalPreview('{{ $imageUrl }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                        @if($isPdf)
                                        <div class="h-100 w-100 bg-light d-flex align-items-center justify-content-center">
                                            <canvas class="pdf-canvas w-100 h-100 rounded"
                                                data-url="{{ $imageUrl }}"
                                                data-desired-width="200"
                                                style="object-fit: cover;"></canvas>
                                            <i class="bi bi-file-pdf text-danger fs-3 position-absolute opacity-50"></i>
                                        </div>
                                        @else
                                        <img src="{{ $imageUrl }}" class="w-100 h-100 rounded" style="object-fit: cover;">
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                                @else
                                <div class="col-12 text-center py-4">
                                    <div class="bg-light p-4 rounded">
                                        <i class="bi bi-image" style="font-size: 3rem; color: #dee2e6;"></i>
                                        <p class="mt-2 text-muted">No images available</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @php
                    $completionProofs = $workOrder->completion_proof_images;
                    // Check if proofs exist AND if the status is strictly 'completed'
                    $isOrderCompleted = strtolower($workOrder->craftsman_status) === 'completed';
                    @endphp

                    @if(count($completionProofs) > 0 && $isOrderCompleted)
                    <div class="card border-info">
                        <div class="card-header bg-info text-white d-flex align-items-center">
                            <i class="bi bi-patch-check me-2"></i>
                            <h4 class="mb-0 text-white">Your Completion Proofs</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach($completionProofs as $imageUrl)
                                @php $isPdf = str_ends_with(strtolower($imageUrl), '.pdf'); @endphp
                                <div class="col-6">
                                    <div class="position-relative border rounded p-1 bg-white shadow-sm"
                                        style="cursor: pointer; height: 160px;"
                                        onclick="openUniversalPreview('{{ $imageUrl }}', '{{ $isPdf ? 'pdf' : 'image' }}')">

                                        @if($isPdf)
                                        <div class="h-100 w-100 bg-light d-flex align-items-center justify-content-center">
                                            <canvas class="pdf-canvas w-100 h-100 rounded"
                                                data-url="{{ $imageUrl }}"
                                                data-desired-width="200"
                                                style="object-fit: cover;"></canvas>
                                            <i class="bi bi-file-pdf text-danger fs-3 position-absolute opacity-50"></i>
                                        </div>
                                        @else
                                        <img src="{{ $imageUrl }}" class="w-100 h-100 rounded" style="object-fit: cover;">
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
    </div>
</div>

<!-- Complete Order Modal -->
<div class="modal fade" id="completeOrderModal" tabindex="-1" aria-labelledby="completeOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="completeOrderForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="completeOrderModalLabel">Complete Work Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="completeModalMessage">Upload images to document your completed work (optional):</p>
                    <div class="mb-3">
                        <label for="completion_images" class="form-label">Upload Images (Single/Multiple)</label>
                        <input type="file" class="form-control" name="images[]" id="completion_images" multiple accept="image/*">
                        <div class="form-text">Supported formats: JPG, PNG, WEBP. Max size: 5MB per image.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Completion</button>
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