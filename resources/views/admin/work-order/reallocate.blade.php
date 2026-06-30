@extends('admin.layouts.app')

@section('title', 'Reallocate Work Order')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Reallocate Work Order</h1>
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
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Customer Name</label>
                                    <p>{{ $workOrder->customer_name }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Product Name</label>
                                    <p>{{ $workOrder->product_name }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Quantity</label>
                                    <p>{{ $workOrder->quantity }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Order Date</label>
                                    <p>{{ $workOrder->due_date ? $workOrder->due_date->format('d M, Y') : 'N/A' }}</p>
                                </div>
                                
                                @if($workOrder->craftsman)
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Currently Allocated To</label>
                                    <p>{{ $workOrder->craftsman->business_name }} ({{ $workOrder->craftsman->craftman_code }})</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h4>Reallocate to Craftsman</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.work-order.reallocate', $workOrder) }}" method="POST">
                                @csrf
                                
                                <div class="mb-3">
                                    <label for="allocated_craftsman_bp_code" class="form-label">Select Craftsman *</label>
                                    <select class="form-select @error('allocated_craftsman_bp_code') is-invalid @enderror" 
                                            id="allocated_craftsman_bp_code" name="allocated_craftsman_bp_code" required>
                                        <option value="">Choose a craftsman</option>
                                        @foreach($craftsmen as $craftsman)
                                            <option value="{{ $craftsman->craftman_code }}" 
                                                    {{ old('allocated_craftsman_bp_code') == $craftsman->craftman_code ? 'selected' : '' }}>
                                                {{ $craftsman->business_name }} ({{ $craftsman->craftman_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('allocated_craftsman_bp_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.work-order.index') }}" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-warning">Reallocate Work Order</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h4>Product Image</h4>
                        </div>
                        <div class="card-body text-center">
                            @if($workOrder->product_image)
                                <img src="{{ asset('storage/' . $workOrder->product_image) }}" 
                                     alt="Product Image" class="img-fluid rounded" style="max-height: 200px;">
                            @else
                                <div class="bg-light p-5 rounded">
                                    <i class="bi bi-image" style="font-size: 4rem;"></i>
                                    <p class="mt-2">No product image</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection