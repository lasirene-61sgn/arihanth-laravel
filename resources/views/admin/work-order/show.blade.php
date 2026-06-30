@extends('admin.layouts.app')

@section('title', 'View Work Order')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">View Work Order</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="{{ route('admin.work-order.edit', $workOrder) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit Work Order
                        </a>
                        <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-share"></i> Share
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('admin.work-order.print', $workOrder) }}" target="_blank">Generate PDF</a></li>
                            <li><a class="dropdown-item" href="https://wa.me/?text=Work Order: {{ $workOrder->work_order_number }}">WhatsApp</a></li>
                        </ul>
                        <a href="{{ route('admin.work-order.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white"><h4>Work Order Information</h4></div>
                        <div class="card-body">
                             <div class="row">
                                 <div class="col-md-6 mb-3"><label class="text-muted small d-block">Work Order Number</label><p class="fw-bold">{{ $workOrder->work_order_number }}</p></div>
                                 <div class="col-md-6 mb-3">
                                     <label class="text-muted small d-block">Created By</label>
                                     <div class="fw-bold">
                                         @php $creator = $workOrder->creator_details; @endphp
                                         <span class="badge bg-info">{{ $creator['user_code'] !== 'N/A' ? $creator['user_code'] : ($creator['bp_code'] !== 'N/A' ? $creator['bp_code'] : $creator['type']) }}</span>
                                         <span class="ms-1">{{ $creator['name'] }}</span>
                                     </div>
                                 </div>
                                 <div class="col-md-6 mb-3">
                                     <label class="text-muted small d-block">Allocated By</label>
                                     <div class="fw-bold">
                                         @php $allocator = $workOrder->allocator_details; @endphp
                                         @if($allocator['name'] !== 'N/A')
                                             <span class="badge bg-warning">{{ $allocator['user_code'] !== 'N/A' ? $allocator['user_code'] : $allocator['type'] }}</span>
                                             <span class="ms-1">{{ $allocator['name'] }}</span>
                                         @else
                                             <span class="text-muted">Not Allocated</span>
                                         @endif
                                     </div>
                                 </div>
                                 <div class="col-md-6 mb-3">
                                     <label class="text-muted small d-block">Allocated To (Craftsman)</label>
                                     <div class="fw-bold">
                                         @if($workOrder->craftsman)
                                             <span class="badge bg-secondary">{{ $workOrder->craftsman->craftman_code }}</span>
                                             <span class="ms-1">{{ $workOrder->craftsman->full_name ?? $workOrder->craftsman->name }}</span>
                                         @else
                                             <span class="text-muted">Not Allocated</span>
                                         @endif
                                     </div>
                                 </div>
                                 <div class="col-md-6 mb-3">
                                     <label class="text-muted small d-block">Approved By</label>
                                     <div class="fw-bold">
                                         @php $approver = $workOrder->approver_details; @endphp
                                         @if($approver['name'] !== 'N/A')
                                             <span class="badge bg-success">{{ $approver['user_code'] !== 'N/A' ? $approver['user_code'] : $approver['type'] }}</span>
                                             <span class="ms-1">{{ $approver['name'] }}</span>
                                         @else
                                             <span class="text-muted">Pending Approval</span>
                                         @endif
                                     </div>
                                 </div>
                                 <div class="col-md-6 mb-3"><label class="text-muted small d-block">Completed Date</label><div class="fw-bold">@if(strtolower($workOrder->status) === 'completed') <span class="text-success">{{ $workOrder->updated_at ? $workOrder->updated_at->format('d M, Y h:i A') : 'N/A' }}</span> @else <span class="text-muted">Not Completed</span> @endif</div></div>
                                 <div class="col-md-6 mb-3"><label class="text-muted small d-block">Customer Name</label><p class="fw-bold">{{ $workOrder->customer_name }}</p></div>
                                 <div class="col-md-6 mb-3"><label class="text-muted small d-block">BP Code</label><p class="fw-bold">{{ $workOrder->bp_code ?? '-' }}</p></div>
                                 <div class="col-md-6 mb-3"><label class="text-muted small d-block">Product Name</label><p class="fw-bold">{{ $workOrder->product_name }}</p></div>
                                 <div class="col-md-6 mb-3"><label class="text-muted small d-block">Quantity</label><p class="fw-bold">{{ $workOrder->quantity }} ({{ $workOrder->type }})</p></div>
                                 <div class="col-md-6 mb-3"><label class="text-muted small d-block">Order Date</label><p class="fw-bold text-danger">{{ $workOrder->due_date ? $workOrder->due_date->format('d M, Y') : 'N/A' }}</p></div>
                                 <div class="col-md-6 mb-3"><label class="text-muted small d-block">Category</label><p class="fw-bold">{{ $workOrder->product_category ?? 'N/A' }}</p></div>
                                 <div class="col-md-6 mb-3"><label class="text-muted small d-block">Subcategory</label><p class="fw-bold">{{ $workOrder->subcategory ?? 'N/A' }}</p></div>
                             </div></div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-white"><h4>Technical Specifications</h4></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-2"><strong>Weight:</strong> {{ $workOrder->weight_from }} - {{ $workOrder->weight_to }} g</div>
                                <div class="col-md-4 mb-2"><strong>Hook:</strong> {{ $workOrder->hook ?? 'N/A' }}</div>
                                <div class="col-md-4 mb-2"><strong>Stone:</strong> {{ $workOrder->stone ?? 'N/A' }}</div>
                                <div class="col-md-4 mb-2"><strong>HUID:</strong> {{ $workOrder->hallmark ?? 'N/A' }}</div>
                                <div class="col-md-4 mb-2"><strong>Size:</strong> {{ $workOrder->size ?? 'N/A' }}</div>
                                <div class="col-md-4 mb-2"><strong>Rodium:</strong> {{ $workOrder->rodium ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white d-flex align-items-center">
                            <i class="bi bi-images me-2 text-primary"></i>
                            <h4 class="mb-0">Product Gallery</h4>
                        </div>
                        <div class="card-body">
                            @php
                                $productGallery = $workOrder->product_gallery_images;
                            @endphp

                            @if(count($productGallery) > 0)
                                <div class="row g-2">
                                    @foreach($productGallery as $imageUrl)
                                        @php $isPdf = str_ends_with(strtolower($imageUrl), '.pdf'); @endphp
                                        <div class="col-6 mb-2">
                                            @if($isPdf)
                                                <div class="d-block border rounded shadow-sm" onclick="openUniversalPreview('{{ $imageUrl }}', 'pdf')" style="cursor: pointer; height: 120px; overflow: hidden;">
                                                    <canvas class="pdf-canvas img-fluid" data-url="{{ $imageUrl }}" style="width: 100%; height: 100%; object-fit: contain;"></canvas>
                                                </div>
                                            @else
                                                <div class="d-block border rounded shadow-sm" onclick="openUniversalPreview('{{ $imageUrl }}', 'image')" style="cursor: pointer; height: 120px; overflow: hidden;">
                                                    <img src="{{ $imageUrl }}" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 bg-light rounded border text-muted">
                                    <i class="bi bi-image" style="font-size: 2rem;"></i>
                                    <p class="mt-2 small">No Product Images</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    @php $completionProofs = $workOrder->completion_proof_images; @endphp
                    @if(count($completionProofs) > 0)
                        <div class="card shadow-sm mb-4 border-info">
                            <div class="card-header bg-info text-white d-flex align-items-center">
                                <i class="bi bi-patch-check me-2"></i>
                                <h4 class="mb-0 text-white">Craftsman Completion Proof</h4>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    @foreach($completionProofs as $imageUrl)
                                        @php $isPdf = str_ends_with(strtolower($imageUrl), '.pdf'); @endphp
                                        <div class="col-6 mb-2">
                                            @if($isPdf)
                                                <div class="d-block border rounded shadow-sm" onclick="openUniversalPreview('{{ $imageUrl }}', 'pdf')" style="cursor: pointer; height: 120px; overflow: hidden;">
                                                    <canvas class="pdf-canvas img-fluid" data-url="{{ $imageUrl }}" style="width: 100%; height: 100%; object-fit: contain;"></canvas>
                                                </div>
                                            @else
                                                <div class="d-block border rounded shadow-sm" onclick="openUniversalPreview('{{ $imageUrl }}', 'image')" style="cursor: pointer; height: 120px; overflow: hidden;">
                                                    <img src="{{ $imageUrl }}" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif


                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body">
                            <h6>Narrations</h6>
                            <hr>
                            <label class="text-muted small">Admin Note:</label>
                            <p class="small mb-3">{{ $workOrder->narration_admin ?? 'No admin notes.' }}</p>
                            <label class="text-muted small">Craftsman Note:</label>
                            <p class="small">{{ $workOrder->narration_craftsman ?? 'No craftsman notes.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection