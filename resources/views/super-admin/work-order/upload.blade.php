@extends('super-admin.layouts.app')

@section('title', 'Upload Work Orders')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Upload Work Orders</h1>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>Upload Work Orders via Excel/CSV</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('super-admin.work-order.import') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                
                                <div class="mb-3">
                                    <label for="file" class="form-label">Choose File (Excel/CSV)</label>
                                    <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                           id="file" name="file" accept=".xlsx,.xls,.csv" required>
                                    @error('file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Supported formats: Excel (.xlsx, .xls) and CSV (.csv)
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('super-admin.work-order.download-template') }}" class="btn btn-outline-primary">
                                        <i class="bi bi-download"></i> Download Template
                                    </a>
                                    <div>
                                        <a href="{{ route('super-admin.work-order.index') }}" class="btn btn-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-upload"></i> Upload Work Orders
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h4>Instructions</h4>
                        </div>
                        <div class="card-body">
                            <ol>
                                <li><a href="{{ route('super-admin.work-order.download-template') }}">Download the template</a> to see the required format</li>
                                <li>Fill in your work order data according to the template</li>
                                <li>Save the file in Excel (.xlsx, .xls) or CSV (.csv) format</li>
                                <li>Upload the file using the form on the left</li>
                            </ol>
                            
                            <h5 class="mt-3">Required Fields:</h5>
                            <ul class="small">
                                <li><strong>customer_name</strong> - Name of the customer</li>
                                <li><strong>product_name</strong> - Name of the product</li>
                                <li><strong>quantity</strong> - Quantity required</li>
                                <li><strong>due_date</strong> - Order date (YYYY-MM-DD format)</li>
                            </ul>
                            
                            <h5 class="mt-3">Optional Fields:</h5>
                            <ul class="small">
                                <li>product_category, subcategory, bp_code, reference_no, type, etc.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection