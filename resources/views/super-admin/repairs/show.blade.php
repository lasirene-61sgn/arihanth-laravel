@extends('super-admin.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="mb-0">Repair & Sample Details (#{{ $repair->order_number }})</h2>
        <a href="{{ route('super-admin.repairs.index') }}" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Order Information</h5>
            <div>
                <span class="badge bg-{{ $repair->repair_or_sample === 'repair' ? 'warning text-dark' : 'info text-dark' }}">
                    {{ ucfirst($repair->repair_or_sample) }}
                </span>
                <span class="badge bg-{{ $repair->status === 'completed' ? 'success' : 'primary' }} ms-2">
                    Status: {{ ucfirst($repair->status) }}
                </span>
                @if($repair->craftsman_status)
                <span class="badge bg-secondary ms-1">Craftsman State: {{ str_replace('_', ' ', $repair->craftsman_status) }}</span>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Record ID:</div>
                <div class="col-md-3">{{ $repair->id }}</div>
                <div class="col-md-3 fw-bold">Date:</div>
                <div class="col-md-3">{{ $repair->date }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Buyer Name:</div>
                <div class="col-md-3">{{ $repair->buyer->name ?? 'N/A' }}</div>
                <div class="col-md-3 fw-bold">Product Name:</div>
                <div class="col-md-3">{{ $repair->product_name }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Order Number:</div>
                <div class="col-md-3">{{ $repair->order_number }}</div>
                <div class="col-md-3 fw-bold">Reference Number:</div>
                <div class="col-md-3">{{ $repair->reference_number ?? 'N/A' }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Weight:</div>
                <div class="col-md-3">{{ $repair->weight ?? 'N/A' }}</div>
                <div class="col-md-3 fw-bold">Assigned Craftsman:</div>
                <div class="col-md-3">
                    @if($repair->craftman)
                    <strong>{{ $repair->craftman->craftman_code }}</strong> - {{ $repair->craftman->business_name }}
                    @else
                    <span class="text-muted">Not Allocated</span>
                    @endif
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Repair Details:</div>
                <div class="col-md-9">{{ $repair->repair_details ?? 'N/A' }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Notes:</div>
                <div class="col-md-9">{{ $repair->notes ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Workflow Lifecycle Personnel Tracking Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Personnel & Delivery Tracking</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Item Delivered By:</div>
                <div class="col-md-3">{{ $repair->item_delivered_by ?? 'Not Recorded' }}</div>
                <div class="col-md-3 fw-bold">Item Received By:</div>
                <div class="col-md-3">{{ $repair->item_received_by ?? 'Not Recorded' }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Item Returned By (Craftsman):</div>
                <div class="col-md-3">{{ $repair->item_returned_by ?? 'Not Recorded' }}</div>
                <div class="col-md-3 fw-bold">Item Collected By (Craftsman):</div>
                <div class="col-md-3">{{ $repair->item_collected_by ?? 'Not Recorded' }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Item Sent By (Superadmin):</div>
                <div class="col-md-3">{{ $repair->item_sent_by ?? 'Not Recorded' }}</div>
                <div class="col-md-3 fw-bold">Item Accepted By (Buyer):</div>
                <div class="col-md-3">{{ $repair->item_accepted_by ?? 'Not Recorded' }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Completed At:</div>
                <div class="col-md-9">{{ $repair->completed_at ? \Carbon\Carbon::parse($repair->completed_at)->format('d-m-Y H:i:s') : 'In Progress / Not Completed' }}</div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3 fw-bold">Repair Image:</div>
        <div class="col-md-9">
            @if($repair->repair_image)
            <a href="{{ asset('storage/' . $repair->repair_image) }}" target="_blank">
                <img src="{{ asset('storage/' . $repair->repair_image) }}" alt="Repair Image" class="img-thumbnail" style="max-height: 200px;">
            </a>
            @else
            <span class="text-muted">No Image Uploaded</span>
            @endif
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3 fw-bold">Craftsman Voucher Image:</div>
        <div class="col-md-3">
            @if($repair->craftsman_voucher_image)
            <a href="{{ asset('storage/' . $repair->craftsman_voucher_image) }}" target="_blank">
                <img src="{{ asset('storage/' . $repair->craftsman_voucher_image) }}" alt="Voucher Image" class="img-thumbnail" style="max-height: 120px;">
            </a>
            @else
            <span class="text-muted">Not Uploaded</span>
            @endif
        </div>

        <div class="col-md-3 fw-bold">Completion Proof Image:</div>
        <div class="col-md-3">
            @if($repair->craftsman_completion_proof_image)
            <a href="{{ asset('storage/' . $repair->craftsman_completion_proof_image) }}" target="_blank">
                <img src="{{ asset('storage/' . $repair->craftsman_completion_proof_image) }}" alt="Proof Image" class="img-thumbnail" style="max-height: 120px;">
            </a>
            @else
            <span class="text-muted">Not Uploaded</span>
            @endif
        </div>
    </div>
</div>
@endsection