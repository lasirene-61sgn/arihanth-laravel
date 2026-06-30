@extends('super-admin.layouts.app')

@section('title', 'Bulk Upload Work Orders')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Bulk Upload Work Orders</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="{{ route('super-admin.work-order.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Work Orders
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Upload Order List (ZIP)</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('super-admin.work-order.import-order-list') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="file" class="form-label">Choose ZIP File (Excel + Images/PDFs)</label>
                                    <input type="file" class="form-control" id="file" name="file" accept=".zip" required>
                                    <div class="form-text">
                                        Upload a single <strong>ZIP file</strong> containing your <strong>Excel OrderList</strong> and your <strong>Images/PDFs</strong>.<br>
                                        <small class="text-muted">Supported format: .zip only</small>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Upload ZIP</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Instructions</h5>
                        </div>
                        <div class="card-body">
                            <ol>
                                <li><strong>Prepare your files:</strong>
                                    <ul>
                                        <li><strong>Excel File:</strong> Must contain the Order List data. <br><span class="text-danger"><strong>IMPORTANT:</strong> Please save your Excel file as <strong>.xls (Excel 97-2003)</strong> or <strong>.csv</strong>. Do NOT use .xlsx (Modern Excel) as your server cannot read it.</span></li>
                                        <li><strong>Images/PDFs:</strong> Can be in a folder or the root of the ZIP.</li>
                                    </ul>
                                </li>
                                <li><strong>Create ZIP:</strong> Select both the Excel file and your Images folder -> Right Click -> Send to -> <strong>Compressed (zipped) folder</strong>.</li>
                                <li><strong>Auto-Matching:</strong> The system matches the <strong>'Design'</strong> column code to the filename (e.g. Design '123' matches '123.pdf' or '123.jpg').</li>
                                
                                <li class="mt-3"><strong>Excel Structure:</strong> The first row must contain these headers:</li>
                                <ul>
                                    <li><strong>Order No</strong></li>
                                    <li><strong>Due Date</strong></li>
                                    <li><strong>Product</strong></li>
                                    <li><strong>Design</strong> (Matched to Image/PDF Filename)</li>
                                    <li><strong>Weight</strong></li>
                                    <li><strong>Size</strong></li>
                                    <li><strong>Quantity</strong></li>
                                </ul>
                            </ol>
                            
                            <div class="alert alert-info mt-3">
                                <strong>Note:</strong> Ensure your Design codes in Excel exactly match your PDF/Image filenames (excluding the extension).
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection