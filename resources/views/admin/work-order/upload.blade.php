@extends('admin.layouts.app')

@section('title', 'Bulk Upload Work Orders')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Bulk Upload Work Orders</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="{{ route('admin.work-order.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Work Orders
                        </a>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h4>Upload Work Orders from Excel/CSV</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.work-order.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="file" class="form-label">Select Excel/CSV File</label>
                            <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">
                                Supported formats: .xlsx, .xls, .csv
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Upload and Import
                        </button>
                        
                        <a href="{{ route('admin.work-order.download-template') }}" class="btn btn-success">
                            <i class="bi bi-download"></i> Download Template
                        </a>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h4>Instructions</h4>
                </div>
                <div class="card-body">
                    <ol>
                        <li>Download the template CSV file using the "Download Template" button above</li>
                        <li>Fill in your work order data in the CSV file</li>
                        <li>Save the file</li>
                        <li>Click "Choose File" and select your filled CSV file</li>
                        <li>Click "Upload and Import" to import the work orders</li>
                    </ol>
                    
                    <h5>Required Columns:</h5>
                    <ul>
                        <li><strong>customer_name</strong> - Customer name (required)</li>
                        <li><strong>product_name</strong> - Product name (required)</li>
                        <li><strong>quantity</strong> - Quantity (required, numeric)</li>
                        <li><strong>due_date</strong> - Due date (required, format: YYYY-MM-DD)</li>
                    </ul>
                    
                    <h5>Optional Columns:</h5>
                    <ul>
                        <li>product_category</li>
                        <li>subcategory</li>
                        <li>bp_code</li>
                        <li>reference_no</li>
                        <li>type</li>
                        <li>open_close</li>
                        <li>weight_from</li>
                        <li>weight_to</li>
                        <li>hallmark</li>
                        <li>rodium</li>
                        <li>hook</li>
                        <li>size</li>
                        <li>stone</li>
                        <li>enamel</li>
                        <li>length</li>
                        <li>product_code</li>
                        <li>relabel_code</li>
                        <li>craftsman_due_date</li>
                        <li>narration_craftsman</li>
                        <li>narration_admin</li>
                        <li>allocated_craftsman_bp_code</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
