@extends('buyer.layouts.app')

@section('title', 'Create Repair')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Add New Repair</h1>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Repair Details</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('buyer.repairs.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="repair_date" class="form-label">Date (Auto Generated)</label>
                                <input type="text" class="form-control" id="repair_date" value="{{ now()->format('Y-m-d') }}" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="product_name" class="form-label">Product Name *</label>
                                <input type="text" class="form-control" id="product_name" name="product_name" value="{{ old('product_name') }}" required>
                                @error('product_name')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="order_no" class="form-label">Order Number</label>
                                <input type="text" class="form-control" id="order_no" name="order_no" value="{{ old('order_no') }}">
                                @error('order_no')
                                <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="ref" class="form-label">Reference Number</label>
                                <input type="text" class="form-control" id="ref" name="ref" value="{{ old('ref') }}">
                                @error('ref')
                                <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="repair" class="form-label">Repairs/Samples Type</label>
                                <select class="form-select" id="repair" name="repair">
                                    <option value="" selected disabled>Please Select</option>
                                    <option value="Repair" {{ old('repair') == 'Repair' ? 'selected' : '' }}>Repair</option>
                                    <option value="Samples" {{ old('repair') == 'Samples' ? 'selected' : '' }}>Samples</option>
                                </select>

                                @error('repair')
                                <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="weight" class="form-label">Weight</label>
                                <input type="number" step="0.001" class="form-control" id="weight" name="weight" value="{{ old('weight') }}">
                                @error('weight')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="item_given_to" class="form-label">Item Given To</label>
                                <input type="text" class="form-control" id="item_given_to" name="item_given_to" value="{{ old('item_given_to') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="image_proof" class="form-label">Image Proof</label>
                                <input type="file" class="form-control" id="image_proof" name="image_proof" accept="image/*">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="repair_details" class="form-label">Repair Details</label>
                                <textarea class="form-control" id="repair_details" name="repair_details" rows="3">{{ old('repair_details') }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="sample_details" class="form-label">Sample Details</label>
                                <textarea class="form-control" id="sample_details" name="sample_details" rows="3">{{ old('sample_details') }}</textarea>
                            </div>

                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('buyer.repairs.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Repair</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
