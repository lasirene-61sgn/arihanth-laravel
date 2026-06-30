@extends('user.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Work Order Details</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('user.work-order.index') }}">Work Orders</a></li>
                        <li class="breadcrumb-item active">View</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Work Order #{{ $workOrder->work_order_number }}</h4>
                    <div>
                        <a href="{{ route('user.work-order.edit', $workOrder) }}" class="btn btn-primary me-2">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="{{ route('user.work-order.print', $workOrder) }}" class="btn btn-secondary" target="_blank">
                            <i class="bi bi-printer"></i> Print
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Work Order Number:</strong>
                                <p>{{ $workOrder->work_order_number }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>BP Code:</strong>
                                <p>{{ $workOrder->bp_code }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Customer Name:</strong>
                                <p>{{ $workOrder->customer_name }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Reference No:</strong>
                                <p>{{ $workOrder->reference_no ?: 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Due Date:</strong>
                                <p>{{ $workOrder->due_date ? \Carbon\Carbon::parse($workOrder->due_date)->format('d M, Y') : 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Product Category:</strong>
                                <p>{{ $workOrder->product_category ?: 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Subcategory:</strong>
                                <p>{{ $workOrder->subcategory ?: 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Quantity:</strong>
                                <p>{{ $workOrder->quantity }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Product Name:</strong>
                                <p>{{ $workOrder->product_name }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Product Code:</strong>
                                <p>{{ $workOrder->product_code ?: 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Type:</strong>
                                <p>{{ $workOrder->type ?: 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Open/Close:</strong>
                                <p>{{ $workOrder->open_close ?: 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Weight Range:</strong>
                                <p>{{ $workOrder->weight_from ?: 'N/A' }} - {{ $workOrder->weight_to ?: 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Hallmark:</strong>
                                <p>{{ $workOrder->hallmark ?: 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Rodium:</strong>
                                <p>{{ $workOrder->rodium ?: 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Created By:</strong>
                                <p>{{ $workOrder->creator_name }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Status:</strong>
                                <p>
                                    @if($workOrder->status == 'new')
                                        <span class="badge bg-info">New</span>
                                    @elseif($workOrder->status == 'allocated')
                                        <span class="badge bg-warning">Allocated</span>
                                    @elseif($workOrder->craftsman_status == 'in_process')
                                        <span class="badge bg-primary">In Process</span>
                                    @elseif($workOrder->craftsman_status == 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($workOrder->craftsman_status == 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary">Unknown</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Hook:</strong>
                                <p>{{ $workOrder->hook ?: 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Size:</strong>
                                <p>{{ $workOrder->size ?: 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Stone:</strong>
                                <p>{{ $workOrder->stone ?: 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Enamel:</strong>
                                <p>{{ $workOrder->enamel ?: 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Length:</strong>
                                <p>{{ $workOrder->length ?: 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Relabel Code:</strong>
                                <p>{{ $workOrder->relabel_code ?: 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Narration:</strong>
                                <p>{{ $workOrder->narration_admin ?: 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <strong>Order Gallery:</strong>
                        <div class="row g-3 mt-1">
                            @php
                                $gallery = $workOrder->gallery_images;
                            @endphp

                            @if(count($gallery) > 0)
                                @foreach($gallery as $imageUrl)
                                    @php $isPdf = str_ends_with(strtolower($imageUrl), '.pdf'); @endphp
                                    <div class="col-6 col-md-3">
                                        <div class="position-relative border rounded p-1 bg-white shadow-sm pointer-on-hover" 
                                             style="cursor: pointer; height: 180px;"
                                             onclick="openUniversalPreview('{{ $imageUrl }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                            @if($isPdf)
                                                <div class="h-100 w-100 bg-light d-flex align-items-center justify-content-center position-relative">
                                                    <canvas class="pdf-canvas w-100 h-100 object-fit-cover" 
                                                            data-url="{{ $imageUrl }}" 
                                                            data-desired-width="200"></canvas>
                                                    <i class="bi bi-file-pdf text-danger fs-2 position-absolute opacity-50"></i>
                                                </div>
                                            @else
                                                <img src="{{ $imageUrl }}" class="w-100 h-100 object-fit-cover rounded">
                                            @endif
                                            <div class="position-absolute inset-0 bg-dark opacity-0 hover-opacity-10 transition-all rounded"></div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="col-12">
                                    <div class="alert alert-light border">
                                        <i class="bi bi-info-circle me-2"></i> No design images available for this order.
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>


                    <div class="d-flex justify-content-end">
                        <a href="{{ route('user.work-order.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection