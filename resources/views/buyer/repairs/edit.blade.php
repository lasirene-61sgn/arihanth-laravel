@extends('buyer.layouts.app')

@section('content')
<div class="container px-4 my-4">
    <h2>Edit Repair / Sample Request (#{{ $repair->order_number }})</h2>
    <form action="{{ route('buyer.repairs.update', $repair->id) }}" method="POST" enctype="multipart/form-data" class="mt-3">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label">Date <span class="text-danger">*</span></label>
            <input type="date" name="date" class="form-control" value="{{ $repair->date }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Type <span class="text-danger">*</span></label>
            <select name="repair_or_sample" class="form-select" required>
                <option value="repair" {{ $repair->repair_or_sample === 'repair' ? 'selected' : '' }}>Repair</option>
                <option value="sample" {{ $repair->repair_or_sample === 'sample' ? 'selected' : '' }}>Sample</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Product Name <span class="text-danger">*</span></label>
            <input type="text" name="product_name" class="form-control" value="{{ $repair->product_name }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Order Number <span class="text-danger">*</span></label>
            <input type="text" name="order_number" class="form-control" value="{{ $repair->order_number }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Weight</label>
            <input type="number" step="0.01" name="weight" class="form-control" value="{{ $repair->weight }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Repair Details</label>
            <textarea name="repair_details" class="form-control" rows="3">{{ $repair->repair_details }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Repair Image</label>
            <input type="file" name="repair_image" class="form-control" accept="image/*">
            @if(isset($repair) && $repair->repair_image)
            <div class="mt-2">
                <p class="text-muted small mb-1">Current Image:</p>
                <a href="{{ asset('storage/' . $repair->repair_image) }}" target="_blank">
                    <img src="{{ asset('storage/' . $repair->repair_image) }}" alt="Repair Image" width="100" class="img-thumbnail">
                </a>
            </div>
            @endif
        </div>
        <button type="submit" class="btn btn-primary">Update Request</button>
        <a href="{{ route('buyer.repairs.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection