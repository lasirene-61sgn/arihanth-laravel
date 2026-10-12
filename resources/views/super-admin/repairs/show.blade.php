@extends('super-admin.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h2 class="mb-1">Repair / Sample Details</h2>
            <p class="text-muted mb-0">Record #{{ $repair->id }} &bull; {{ $repair->order_number }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('super-admin.repairs.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
            <a href="{{ route('super-admin.repairs.edit', $repair->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <form action="{{ route('super-admin.repairs.destroy', $repair->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this record?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row g-4">
        <!-- Main Details Card -->
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light py-3">
                    <h5 class="card-title mb-0">Record Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Product Name</label>
                            <div class="fw-bold fs-5 text-dark">{{ $repair->product_name }}</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Type</label>
                            <div>
                                <span class="badge bg-{{ $repair->repair_or_sample === 'repair' ? 'warning' : 'info' }} fs-6">
                                    {{ ucfirst($repair->repair_or_sample) }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Order Number</label>
                            <div class="fw-semibold">{{ $repair->order_number }}</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Reference Number</label>
                            <div class="fw-semibold">{{ $repair->reference_number ?: 'N/A' }}</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Weight</label>
                            <div class="fw-semibold">{{ $repair->weight ? number_format($repair->weight, 2) . ' g' : 'N/A' }}</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Date</label>
                            <div class="fw-semibold">{{ $repair->date }}</div>
                        </div>

                        <div class="col-12">
                            <hr class="my-2">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Item Delivered By</label>
                            <div class="fw-semibold">{{ $repair->item_delivered_by ?: 'N/A' }}</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Item Received By</label>
                            <div class="fw-semibold">{{ $repair->item_received_by ?: 'N/A' }}</div>
                        </div>

                        <div class="col-12">
                            <hr class="my-2">
                        </div>

                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Repair Details</label>
                            <div class="p-3 bg-light rounded border text-secondary" style="white-space: pre-wrap;">{{ $repair->repair_details ?: 'No repair details provided.' }}</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Notes</label>
                            <div class="p-3 bg-light rounded border text-secondary" style="white-space: pre-wrap;">{{ $repair->notes ?: 'No notes provided.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Details -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="card-title mb-0">Buyer Details</h5>
                </div>
                <div class="card-body">
                    @if($repair->buyer)
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Buyer Name</label>
                            <div class="fw-bold">{{ $repair->buyer->name }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">BP Code</label>
                            <div class="fw-semibold">{{ $repair->buyer->bp_code ?: 'N/A' }}</div>
                        </div>
                        @if(!empty($repair->buyer->email))
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Email</label>
                            <div>{{ $repair->buyer->email }}</div>
                        </div>
                        @endif
                        @if(!empty($repair->buyer->mobile))
                        <div class="mb-0">
                            <label class="form-label text-muted small mb-1">Mobile</label>
                            <div>{{ $repair->buyer->mobile }}</div>
                        </div>
                        @endif
                    @else
                        <p class="text-muted mb-0">No buyer associated with this record.</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light py-3">
                    <h5 class="card-title mb-0">Timestamps</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Created At</label>
                        <div>{{ $repair->created_at ? $repair->created_at->format('Y-m-d H:i:s') : 'N/A' }}</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-muted small mb-1">Last Updated</label>
                        <div>{{ $repair->updated_at ? $repair->updated_at->format('Y-m-d H:i:s') : 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
