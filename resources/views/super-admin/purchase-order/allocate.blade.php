@extends('super-admin.layouts.app')

@section('title', 'Allocate Purchase Order')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.375rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 12px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Allocate Purchase Order</h1>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Allocate Purchase Order: {{ $purchaseOrder->purchase_order_code }}</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('super-admin.purchase-order.allocate.store', $purchaseOrder) }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="allocated_craftsman_code" class="form-label">Select Craftsman</label>
                                <select class="form-select select2" id="allocated_craftsman_code" name="allocated_craftsman_code" required>
                                    <option value="">Select a Craftsman</option>
                                    @foreach($craftsmen as $craftsman)
                                        <option value="{{ $craftsman->craftman_code }}" {{ old('allocated_craftsman_code') == $craftsman->craftman_code ? 'selected' : '' }}>
                                            {{ $craftsman->craftman_code }} - {{ $craftsman->business_name }} ({{ $craftsman->name }}) {{ $craftsman->dear ? ' - '.$craftsman->dear : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="craftsman_due_date" class="form-label">Craftsman Due Date</label>
                                <input type="date" class="form-control" id="craftsman_due_date" name="craftsman_due_date" value="{{ old('craftsman_due_date') }}">
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Purchase Order Items Summary</h5>
                            </div>
                            <div class="card-body">
                                @if($itemsWithDetails && count($itemsWithDetails) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>S.No</th>
                                                    <th>Image</th>
                                                    <th>Category</th>
                                                    <th>Product / Sub Category</th>
                                                    <th>Design Code</th>
                                                    <th>Grams & Quantity</th>
                                                    <th>Total Weight</th>
                                                    <th>Item Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($itemsWithDetails as $index => $item)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td class="text-center">
                                                            @if(!empty($item['image']))
                                                                <div class="saved-preview mb-2">
                                                                    <small class="text-primary d-block fw-bold">Current Order Image:</small>
                                                                    <img src="{{ asset($item['image']) }}" class="img-thumbnail" style="max-height: 60px;">
                                                                </div>
                                                            @else
                                                                <span class="text-muted small">No Image</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $item['category_name'] ?? ($item['category']->name ?? 'N/A') }}</td>
                                                        <td>
                                                            @if(isset($item['product']))
                                                                <span class="fw-bold">{{ $item['product']->subcategory->name ?? 'N/A' }}</span><br>
                                                                <small class="text-muted">{{ $item['product']->product_name }}</small>
                                                            @else
                                                                <span class="text-danger">Manual: {{ $item['manual_product'] ?? ($item['product_name'] ?? 'N/A') }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                {{ $item['product']->design_code ?? 'N/A' }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @if(isset($item['grams']) && is_array($item['grams']))
                                                                @foreach($item['grams'] as $i => $gram)
                                                                    <div class="border-bottom mb-1 pb-1">
                                                                        {{ $gram }}g × {{ $item['quantity'][$i] }} nos
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                {{ $item['grams'] ?? 0 }}g × {{ $item['quantity'] ?? 0 }}
                                                            @endif
                                                        </td>
                                                        <td class="fw-bold text-primary">
                                                            {{ number_format($item['total'], 2) }}g
                                                        </td>
                                                        <td>
                                                            <small>{{ $item['item_notes'] ?: '-' }}</small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <td colspan="6" class="text-end fw-bold">Grand Total Weight:</td>
                                                    <td class="fw-bold text-success" style="font-size: 1.1rem;">
                                                        @php
                                                            $grandTotal = collect($itemsWithDetails)->sum('total');
                                                        @endphp
                                                        {{ number_format($grandTotal, 2) }}g
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-warning mb-0">
                                        No items found for this purchase order.
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('super-admin.purchase-order.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </a>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-check-circle"></i> Confirm Allocation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select a Craftsman",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection
