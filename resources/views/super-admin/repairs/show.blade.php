@extends('super-admin.layouts.app')

@section('title', 'Repair Details #' . $repair->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Repair Details #{{ $repair->id }}</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="{{ route('super-admin.repairs.index') }}" class="btn btn-sm btn-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                    @if(in_array($repair->status, ['Pending', 'Accepted']))
                    <a href="{{ route('super-admin.repairs.edit', $repair->id) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-pencil"></i> Edit Repair
                    </a>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <!-- Basic Information -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">General Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Order ID</div>
                                <div class="col-sm-8 font-weight-bold">#{{ $repair->id }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Repair Date</div>
                                <div class="col-sm-8">{{ \Carbon\Carbon::parse($repair->repair_date)->format('d M Y') }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Status</div>
                                <div class="col-sm-8">
                                    @if($repair->status == 'Pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($repair->status == 'Accepted')
                                    <span class="badge bg-info">Accepted</span>
                                    @elseif($repair->status == 'In_Process')
                                    <span class="badge bg-info">In Process</span>
                                    @elseif($repair->status == 'Allocated')
                                    <span class="badge bg-primary">Allocated</span>
                                    @elseif($repair->status == 'Craftsman_Completed')
                                    <span class="badge bg-success">Craftsman Completed</span>
                                    @elseif($repair->status == 'Craftsman_Rejected')
                                    <span class="badge bg-danger">Craftsman Rejected</span>
                                    @elseif($repair->status == 'Completed')
                                    <span class="badge bg-success">Completed</span>
                                    @elseif($repair->status == 'Rejected_by_Admin')
                                    <span class="badge bg-danger">Rejected</span>
                                    @elseif($repair->status == 'Buyer_Accepted')
                                    <span class="badge bg-success">Buyer Accepted</span>
                                    @elseif($repair->status == 'Buyer_Rejected')
                                    <span class="badge bg-danger">Buyer Rejected</span>
                                    @else
                                    <span class="badge bg-secondary">{{ $repair->status }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product & Repair Details -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Product & Repair Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Product Name</div>
                                <div class="col-sm-8 font-weight-bold">{{ $repair->product_name }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Weight</div>
                                <div class="col-sm-8">{{ $repair->weight ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Repair Details</div>
                                <div class="col-sm-8">{{ $repair->repair_details ?? 'No details provided' }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Sample Details</div>
                                <div class="col-sm-8">{{ $repair->sample_details ?? 'No sample details' }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Item Given To</div>
                                <div class="col-sm-8 font-italic">{{ $repair->item_given_to ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Notes</div>
                                <div class="col-sm-8 font-italic">{{ $repair->notes ?? 'No notes' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Buyer & Craftsman Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Buyer Info</h5>
                                </div>
                                <div class="card-body">
                                    @if($repair->buyer)
                                    <div class="row mb-2">
                                        <div class="col-4 text-muted small">BP Code</div>
                                        <div class="col-8">{{ $repair->buyer->bp_code }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4 text-muted small">Name</div>
                                        <div class="col-8 font-weight-bold">{{ $repair->buyer->customer_name }}</div>
                                    </div>
                                    @else
                                    <p class="text-danger mb-0">Buyer information missing</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Craftsman Info</h5>
                                </div>
                                <div class="card-body">
                                    @if($repair->craftsman)
                                    <div class="row mb-2">
                                        <div class="col-5 text-muted small">Code</div>
                                        <div class="col-7">{{ $repair->allocated_craftsman_code }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-5 text-muted small">Name</div>
                                        <div class="col-7 font-weight-bold">{{ $repair->craftsman->name }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-5 text-muted small">Alloc. Notes</div>
                                        <div class="col-7 font-italic small">{{ $repair->allocation_notes ?? '-' }}</div>
                                    </div>
                                    @else
                                    <p class="text-muted mb-0">Not yet allocated</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($repair->reject_reason)
                    <div class="card mb-4 border-danger shadow-sm">
                        <div class="card-header bg-danger text-white">
                            <h5 class="card-title mb-0">Rejection Reason</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $repair->reject_reason }}</p>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="col-md-4">
                    <!-- Image Proof -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Image Proof</h5>
                        </div>
                        <div class="card-body text-center">
                            @if($repair->image_proof)
                            <a href="{{ asset($repair->image_proof) }}" target="_blank">
                                <img src="{{ asset($repair->image_proof) }}" class="img-fluid rounded border" alt="Repair Proof" style="max-height: 400px; object-fit: contain;">
                            </a>
                            <div class="mt-2">
                                <a href="{{ asset($repair->image_proof) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-box-arrow-up-right"></i> Open Full Image
                                </a>
                            </div>
                            @else
                            <div class="py-5 text-muted">
                                <i class="bi bi-image text-3xl d-block mb-3"></i>
                                No image proof uploaded
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Danger Zone -->
                    <div class="card border-danger shadow-sm">
                        <div class="card-header bg-danger text-white">
                            <h5 class="card-title mb-0">Danger Zone</h5>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted">Deleting a repair order is permanent and cannot be undone.</p>
                            <form action="{{ route('super-admin.repairs.destroy', $repair->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this repair order? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-block w-100">
                                    <i class="bi bi-trash"></i> Delete Repair Order
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
