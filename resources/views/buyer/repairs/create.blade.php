@extends('buyer.layouts.app')

@section('content')
<div class="container px-4 my-4">
    <h2>Add New Repair / Sample Request</h2>
    
    <!-- Display Logged-in Buyer Info -->
    @if(isset($currentBuyer))
        <div class="alert alert-info py-2 my-3">
            <strong>Buyer Name:</strong> {{ $currentBuyer->name }} | <strong>BP Code:</strong> {{ $currentBuyer->bp_code }}
        </div>
    @endif

    <form action="{{ route('buyer.repairs.store') }}" method="POST" class="mt-3">
        @csrf
        <div class="mb-3">
            <label class="form-label">Date <span class="text-danger">*</span></label>
            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Type <span class="text-danger">*</span></label>
            <select name="repair_or_sample" class="form-select" required>
                <option value="repair">Repair</option>
                <option value="sample">Sample</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Product Name <span class="text-danger">*</span></label>
            <input type="text" name="product_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Order Number <span class="text-danger">*</span></label>
            <input type="text" name="order_number" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Weight</label>
            <input type="number" step="0.01" name="weight" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Repair Details</label>
            <textarea name="repair_details" class="form-control" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Request</button>
        <a href="{{ route('buyer.repairs.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection