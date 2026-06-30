@extends('key-user.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Work Orders</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('key-user.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Work Orders</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Work Order Management</h4>
                    <a href="{{ route('key-user.work-order.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Work Order
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Filters and Search Section -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-outline-primary" onclick="toggleFilters()">
                                <i class="bi bi-funnel"></i> Toggle Filters
                            </button>
                        </div>
                    </div>

                    <div id="filterSection" class="row mb-3" {{ request('search') || request('category_filter') || request('subcategory_filter') ? '' : 'style=display:none;' }}>
                        <div class="col-md-3">
                            <form method="GET" class="d-flex">
                                <input type="hidden" name="tab" value="{{ request('tab', 'new-orders') }}">
                                <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                                <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                                <input type="hidden" name="category_filter" value="{{ request('category_filter') }}">
                                <input type="hidden" name="subcategory_filter" value="{{ request('subcategory_filter') }}">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="form-control me-2">
                                <button type="submit" class="btn btn-outline-primary">Search</button>
                                <a href="{{ route('key-user.work-order.index', ['tab' => request('tab', 'new-orders')]) }}" class="btn btn-outline-secondary ms-1">Clear</a>
                            </form>
                        </div>
                        <div class="col-md-3">
                            <form method="GET" class="d-flex">
                                <input type="hidden" name="tab" value="{{ request('tab', 'new-orders') }}">
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                                <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                                <input type="hidden" name="subcategory_filter" value="{{ request('subcategory_filter') }}">
                                <select name="category_filter" onchange="this.form.submit()" class="form-select me-2">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_filter') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="col-md-3">
                            <form method="GET" class="d-flex">
                                <input type="hidden" name="tab" value="{{ request('tab', 'new-orders') }}">
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                                <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                                <input type="hidden" name="category_filter" value="{{ request('category_filter') }}">
                                <select name="subcategory_filter" onchange="this.form.submit()" class="form-select me-2">
                                    <option value="">All Subcategories</option>
                                    @foreach($subcategories as $subcategory)
                                        <option value="{{ $subcategory->id }}" {{ request('subcategory_filter') == $subcategory->id ? 'selected' : '' }}>{{ $subcategory->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>

                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="workOrderTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="new-orders-tab" data-bs-toggle="tab" data-bs-target="#new-orders" type="button" role="tab">New Orders</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="allocated-orders-tab" data-bs-toggle="tab" data-bs-target="#allocated-orders" type="button" role="tab">Allocated</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="in-process-orders-tab" data-bs-toggle="tab" data-bs-target="#in-process-orders" type="button" role="tab">In Process</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="completed-orders-tab" data-bs-toggle="tab" data-bs-target="#completed-orders" type="button" role="tab">Completed</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="rejected-orders-tab" data-bs-toggle="tab" data-bs-target="#rejected-orders" type="button" role="tab">Rejected</button>
                        </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content" id="workOrderTabsContent">
                        <!-- New Orders Tab -->
                        <div class="tab-pane fade show active" id="new-orders" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mt-3 mb-1">
                                <h5 class="mb-0">New Orders</h5>
                                <div class="d-flex align-items-center">
                                    <button type="button" onclick="submitBulkPrint('new-orders')" class="btn btn-dark btn-sm me-2">
                                        <i class="bi bi-printer"></i> Print Selected
                                    </button>
                                    <form method="GET" class="d-flex align-items-center">
                                        <input type="hidden" name="tab" value="new-orders">
                                        <label for="per_page_new" class="me-2 mb-0">Page Size:</label>
                                        <select name="per_page" id="per_page_new" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                                            <option value="10" {{ request('per_page') == 10 || !request('per_page') ? 'selected' : '' }}>10</option>
                                            <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                                            <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                            <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                                            <option value="40" {{ request('per_page') == 40 ? 'selected' : '' }}>40</option>
                                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                            <form action="{{ route('key-user.work-order.bulk-print') }}" method="POST" id="form-new-orders" target="_blank">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-centered table-nowrap mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width: 40px;">
                                                    <input type="checkbox" class="form-check-input select-all-btn" data-target-tab="new-orders">
                                                </th>
                                                <th>WO Number</th>
                                                <th>Image</th>
                                                <th>BP Code</th>
                                                <th>Product Category</th>
                                                <th>Type</th>
                                                <th>Quantity</th>
                                                <th>Created At</th>
                                                <th>Due Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($newOrders as $workOrder)
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" name="selected_orders[]" value="{{ $workOrder->id }}" class="form-check-input order-checkbox-new-orders">
                                                </td>
                                                <td>{{ $workOrder->work_order_number }}</td>
                                                <td style="width: 80px;">
                                                    @php
                                                        $displayImage = null;
                                                        $isPdf = false;
                                                        
                                                        if ($workOrder->product_image) {
                                                            $isPdf = strtolower(pathinfo($workOrder->product_image, PATHINFO_EXTENSION)) === 'pdf';
                                                            if (strpos($workOrder->product_image, 'images/') === 0 || strpos($workOrder->product_image, 'storage/') === 0) {
                                                                $displayImage = asset($workOrder->product_image);
                                                            } else {
                                                                $displayImage = asset('storage/' . $workOrder->product_image);
                                                            }
                                                        } elseif ($workOrder->product && $workOrder->product->images->count() > 0) {
                                                            $firstImg = $workOrder->product->images->first()->path;
                                                            $isPdf = strtolower(pathinfo($firstImg, PATHINFO_EXTENSION)) === 'pdf';
                                                            if (strpos($firstImg, 'http') === 0 || strpos($firstImg, 'storage/') === 0 || strpos($firstImg, 'images/') === 0) {
                                                                $displayImage = asset($firstImg);
                                                            } else {
                                                                $displayImage = asset('storage/' . $firstImg);
                                                            }
                                                        }
                                                    @endphp

                                                    @if($displayImage)
                                                        <div class="position-relative" style="width: 60px; height: 60px; cursor: pointer;" 
                                                             onclick="openUniversalPreview('{{ $displayImage }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                            @if($isPdf)
                                                                <canvas class="pdf-canvas border rounded shadow-sm" 
                                                                        data-url="{{ $displayImage }}" 
                                                                        data-desired-width="60" 
                                                                        style="width: 60px; height: 60px; object-fit: cover;"></canvas>
                                                                <div class="position-absolute top-50 start-50 translate-middle">
                                                                    <i class="bi bi-file-pdf text-danger fs-4"></i>
                                                                </div>
                                                            @else
                                                                <img src="{{ $displayImage }}" alt="Product" 
                                                                     class="rounded border shadow-sm" 
                                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 60px;">
                                                            <i class="bi bi-image fs-4"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>{{ $workOrder->bp_code }}</td>
                                                <td>{{ $workOrder->product_category }}</td>
                                                <td>{{ $workOrder->type }}</td>
                                                <td>{{ $workOrder->quantity }}</td>
                                                <td>{{ $workOrder->created_at->format('d M, Y') }}</td>
                                                <td>{{ $workOrder->due_date->format('d M, Y') }}</td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('key-user.work-order.show', $workOrder) }}" 
                                                           class="btn btn-info btn-sm">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="{{ route('key-user.work-order.edit', $workOrder) }}" 
                                                           class="btn btn-primary btn-sm">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="{{ route('key-user.work-order.print', $workOrder) }}" 
                                                           class="btn btn-secondary btn-sm" target="_blank">
                                                            <i class="bi bi-printer"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="10" class="text-center">No new work orders found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                            <div class="d-flex justify-content-end mt-3">
                                {{ $newOrders->links() }}
                            </div>
                        </div>

                        <!-- Allocated Orders Tab -->
                        <div class="tab-pane fade" id="allocated-orders" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mt-3 mb-1">
                                <h5 class="mb-0">Allocated Orders</h5>
                                <div class="d-flex align-items-center">
                                    <button type="button" onclick="submitBulkPrint('allocated-orders')" class="btn btn-dark btn-sm me-2">
                                        <i class="bi bi-printer"></i> Print Selected
                                    </button>
                                    <form method="GET" class="d-flex align-items-center">
                                        <input type="hidden" name="tab" value="allocated-orders">
                                        <label for="per_page_allocated" class="me-2 mb-0">Page Size:</label>
                                        <select name="per_page" id="per_page_allocated" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                                            <option value="10" {{ request('per_page') == 10 || !request('per_page') ? 'selected' : '' }}>10</option>
                                            <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                                            <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                            <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                                            <option value="40" {{ request('per_page') == 40 ? 'selected' : '' }}>40</option>
                                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                            <form action="{{ route('key-user.work-order.bulk-print') }}" method="POST" id="form-allocated-orders" target="_blank">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-centered table-nowrap mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width: 40px;">
                                                    <input type="checkbox" class="form-check-input select-all-btn" data-target-tab="allocated-orders">
                                                </th>
                                                <th>WO Number</th>
                                                <th>Image</th>
                                                <th>BP Code</th>
                                                <th>Product Category</th>
                                                <th>Type</th>
                                                <th>Quantity</th>
                                                <th>Created At</th>
                                                <th>Due Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($allocatedOrders as $workOrder)
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" name="selected_orders[]" value="{{ $workOrder->id }}" class="form-check-input order-checkbox-allocated-orders">
                                                </td>
                                                <td>{{ $workOrder->work_order_number }}</td>
                                                <td style="width: 80px;">
                                                    @php
                                                        $displayImage = null;
                                                        $isPdf = false;
                                                        
                                                        if ($workOrder->product_image) {
                                                            $isPdf = strtolower(pathinfo($workOrder->product_image, PATHINFO_EXTENSION)) === 'pdf';
                                                            if (strpos($workOrder->product_image, 'images/') === 0 || strpos($workOrder->product_image, 'storage/') === 0) {
                                                                $displayImage = asset($workOrder->product_image);
                                                            } else {
                                                                $displayImage = asset('storage/' . $workOrder->product_image);
                                                            }
                                                        } elseif ($workOrder->product && $workOrder->product->images->count() > 0) {
                                                            $firstImg = $workOrder->product->images->first()->path;
                                                            $isPdf = strtolower(pathinfo($firstImg, PATHINFO_EXTENSION)) === 'pdf';
                                                            if (strpos($firstImg, 'http') === 0 || strpos($firstImg, 'storage/') === 0 || strpos($firstImg, 'images/') === 0) {
                                                                $displayImage = asset($firstImg);
                                                            } else {
                                                                $displayImage = asset('storage/' . $firstImg);
                                                            }
                                                        }
                                                    @endphp

                                                    @if($displayImage)
                                                        <div class="position-relative" style="width: 60px; height: 60px; cursor: pointer;" 
                                                             onclick="openUniversalPreview('{{ $displayImage }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                            @if($isPdf)
                                                                <canvas class="pdf-canvas border rounded shadow-sm" 
                                                                        data-url="{{ $displayImage }}" 
                                                                        data-desired-width="60" 
                                                                        style="width: 60px; height: 60px; object-fit: cover;"></canvas>
                                                                <div class="position-absolute top-50 start-50 translate-middle">
                                                                    <i class="bi bi-file-pdf text-danger fs-4"></i>
                                                                </div>
                                                            @else
                                                                <img src="{{ $displayImage }}" alt="Product" 
                                                                     class="rounded border shadow-sm" 
                                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 60px;">
                                                            <i class="bi bi-image fs-4"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>{{ $workOrder->bp_code }}</td>
                                                <td>{{ $workOrder->product_category }}</td>
                                                <td>{{ $workOrder->type }}</td>
                                                <td>{{ $workOrder->quantity }}</td>
                                                <td>{{ $workOrder->created_at->format('d M, Y') }}</td>
                                                <td>{{ $workOrder->due_date->format('d M, Y') }}</td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('key-user.work-order.show', $workOrder) }}" 
                                                           class="btn btn-info btn-sm">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="{{ route('key-user.work-order.print', $workOrder) }}" 
                                                           class="btn btn-secondary btn-sm" target="_blank">
                                                            <i class="bi bi-printer"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="11" class="text-center">No allocated work orders found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                            <div class="d-flex justify-content-end mt-3">
                                @if(method_exists($allocatedOrders, 'links'))
                                    {{ $allocatedOrders->links() }}
                                @endif
                            </div>
                        </div>

                        <!-- In Process Orders Tab -->
                        <div class="tab-pane fade" id="in-process-orders" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mt-3 mb-1">
                                <h5 class="mb-0">In Process Orders</h5>
                                <div class="d-flex align-items-center">
                                    <button type="button" onclick="submitBulkPrint('in-process-orders')" class="btn btn-dark btn-sm me-2">
                                        <i class="bi bi-printer"></i> Print Selected
                                    </button>
                                    <form method="GET" class="d-flex align-items-center">
                                        <input type="hidden" name="tab" value="in-process-orders">
                                        <label for="per_page_in_process" class="me-2 mb-0">Page Size:</label>
                                        <select name="per_page" id="per_page_in_process" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                                            <option value="10" {{ request('per_page') == 10 || !request('per_page') ? 'selected' : '' }}>10</option>
                                            <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                                            <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                            <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                                            <option value="40" {{ request('per_page') == 40 ? 'selected' : '' }}>40</option>
                                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                            <form action="{{ route('key-user.work-order.bulk-print') }}" method="POST" id="form-in-process-orders" target="_blank">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-centered table-nowrap mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width: 40px;">
                                                    <input type="checkbox" class="form-check-input select-all-btn" data-target-tab="in-process-orders">
                                                </th>
                                                <th>WO Number</th>
                                                <th>Image</th>
                                                <th>BP Code</th>
                                                <th>Product Category</th>
                                                <th>Type</th>
                                                <th>Quantity</th>
                                                <th>Created At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($inProcessOrders as $workOrder)
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" name="selected_orders[]" value="{{ $workOrder->id }}" class="form-check-input order-checkbox-in-process-orders">
                                                </td>
                                                <td>{{ $workOrder->work_order_number }}</td>
                                                <td style="width: 80px;">
                                                    @php
                                                        $displayImage = null;
                                                        $isPdf = false;
                                                        
                                                        if ($workOrder->product_image) {
                                                            $isPdf = strtolower(pathinfo($workOrder->product_image, PATHINFO_EXTENSION)) === 'pdf';
                                                            if (strpos($workOrder->product_image, 'images/') === 0 || strpos($workOrder->product_image, 'storage/') === 0) {
                                                                $displayImage = asset($workOrder->product_image);
                                                            } else {
                                                                $displayImage = asset('storage/' . $workOrder->product_image);
                                                            }
                                                        } elseif ($workOrder->product && $workOrder->product->images->count() > 0) {
                                                            $firstImg = $workOrder->product->images->first()->path;
                                                            $isPdf = strtolower(pathinfo($firstImg, PATHINFO_EXTENSION)) === 'pdf';
                                                            if (strpos($firstImg, 'http') === 0 || strpos($firstImg, 'storage/') === 0 || strpos($firstImg, 'images/') === 0) {
                                                                $displayImage = asset($firstImg);
                                                            } else {
                                                                $displayImage = asset('storage/' . $firstImg);
                                                            }
                                                        }
                                                    @endphp

                                                    @if($displayImage)
                                                        <div class="position-relative" style="width: 60px; height: 60px; cursor: pointer;" 
                                                             onclick="openUniversalPreview('{{ $displayImage }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                            @if($isPdf)
                                                                <canvas class="pdf-canvas border rounded shadow-sm" 
                                                                        data-url="{{ $displayImage }}" 
                                                                        data-desired-width="60" 
                                                                        style="width: 60px; height: 60px; object-fit: cover;"></canvas>
                                                                <div class="position-absolute top-50 start-50 translate-middle">
                                                                    <i class="bi bi-file-pdf text-danger fs-4"></i>
                                                                </div>
                                                            @else
                                                                <img src="{{ $displayImage }}" alt="Product" 
                                                                     class="rounded border shadow-sm" 
                                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 60px;">
                                                            <i class="bi bi-image fs-4"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>{{ $workOrder->bp_code }}</td>
                                                <td>{{ $workOrder->product_category }}</td>
                                                <td>{{ $workOrder->type }}</td>
                                                <td>{{ $workOrder->quantity }}</td>
                                                <td>{{ $workOrder->created_at->format('d M, Y') }}</td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('key-user.work-order.show', $workOrder) }}" 
                                                           class="btn btn-info btn-sm">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="{{ route('key-user.work-order.print', $workOrder) }}" 
                                                           class="btn btn-secondary btn-sm" target="_blank">
                                                            <i class="bi bi-printer"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="11" class="text-center">No work orders in process found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                            <div class="d-flex justify-content-end mt-3">
                                @if(method_exists($inProcessOrders, 'links'))
                                    {{ $inProcessOrders->links() }}
                                @endif
                            </div>
                        </div>

                        <!-- Completed Orders Tab -->
                        <div class="tab-pane fade" id="completed-orders" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mt-3 mb-1">
                                <h5 class="mb-0">Completed Orders</h5>
                                <div class="d-flex align-items-center">
                                    <button type="button" onclick="submitBulkPrint('completed-orders')" class="btn btn-dark btn-sm me-2">
                                        <i class="bi bi-printer"></i> Print Selected
                                    </button>
                                    <form method="GET" class="d-flex align-items-center">
                                        <input type="hidden" name="tab" value="completed-orders">
                                        <label for="per_page_completed" class="me-2 mb-0">Page Size:</label>
                                        <select name="per_page" id="per_page_completed" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                                            <option value="10" {{ request('per_page') == 10 || !request('per_page') ? 'selected' : '' }}>10</option>
                                            <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                                            <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                            <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                                            <option value="40" {{ request('per_page') == 40 ? 'selected' : '' }}>40</option>
                                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                            <form action="{{ route('key-user.work-order.bulk-print') }}" method="POST" id="form-completed-orders" target="_blank">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-centered table-nowrap mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width: 40px;">
                                                    <input type="checkbox" class="form-check-input select-all-btn" data-target-tab="completed-orders">
                                                </th>
                                                <th>WO Number</th>
                                                <th>Image</th>
                                                <th>BP Code</th>
                                                <th>Product Category</th>
                                                <th>Type</th>
                                                <th>Quantity</th>
                                                <th>Created At</th>
                                                <th>Due Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($completedOrders as $workOrder)
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" name="selected_orders[]" value="{{ $workOrder->id }}" class="form-check-input order-checkbox-completed-orders">
                                                </td>
                                                <td>{{ $workOrder->work_order_number }}</td>
                                                <td style="width: 80px;">
                                                    @php
                                                        $displayImage = null;
                                                        $isPdf = false;
                                                        
                                                        if ($workOrder->product_image) {
                                                            $isPdf = strtolower(pathinfo($workOrder->product_image, PATHINFO_EXTENSION)) === 'pdf';
                                                            if (strpos($workOrder->product_image, 'images/') === 0 || strpos($workOrder->product_image, 'storage/') === 0) {
                                                                $displayImage = asset($workOrder->product_image);
                                                            } else {
                                                                $displayImage = asset('storage/' . $workOrder->product_image);
                                                            }
                                                        } elseif ($workOrder->product && $workOrder->product->images->count() > 0) {
                                                            $firstImg = $workOrder->product->images->first()->path;
                                                            $isPdf = strtolower(pathinfo($firstImg, PATHINFO_EXTENSION)) === 'pdf';
                                                            if (strpos($firstImg, 'http') === 0 || strpos($firstImg, 'storage/') === 0 || strpos($firstImg, 'images/') === 0) {
                                                                $displayImage = asset($firstImg);
                                                            } else {
                                                                $displayImage = asset('storage/' . $firstImg);
                                                            }
                                                        }
                                                    @endphp

                                                    @if($displayImage)
                                                        <div class="position-relative" style="width: 60px; height: 60px; cursor: pointer;" 
                                                             onclick="openUniversalPreview('{{ $displayImage }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                            @if($isPdf)
                                                                <canvas class="pdf-canvas border rounded shadow-sm" 
                                                                        data-url="{{ $displayImage }}" 
                                                                        data-desired-width="60" 
                                                                        style="width: 60px; height: 60px; object-fit: cover;"></canvas>
                                                                <div class="position-absolute top-50 start-50 translate-middle">
                                                                    <i class="bi bi-file-pdf text-danger fs-4"></i>
                                                                </div>
                                                            @else
                                                                <img src="{{ $displayImage }}" alt="Product" 
                                                                     class="rounded border shadow-sm" 
                                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 60px;">
                                                            <i class="bi bi-image fs-4"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>{{ $workOrder->bp_code }}</td>
                                                <td>{{ $workOrder->product_category }}</td>
                                                <td>{{ $workOrder->type }}</td>
                                                <td>{{ $workOrder->quantity }}</td>
                                                <td>{{ $workOrder->created_at->format('d M, Y') }}</td>
                                                <td>{{ $workOrder->due_date->format('d M, Y') }}</td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('key-user.work-order.show', $workOrder) }}" 
                                                           class="btn btn-info btn-sm">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="{{ route('key-user.work-order.print', $workOrder) }}" 
                                                           class="btn btn-secondary btn-sm" target="_blank">
                                                            <i class="bi bi-printer"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="10" class="text-center">No completed work orders found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                            <div class="d-flex justify-content-end mt-3">
                                @if(method_exists($completedOrders, 'links'))
                                    {{ $completedOrders->links() }}
                                @endif
                            </div>
                        </div>

                        <!-- Rejected Orders Tab -->
                        <div class="tab-pane fade" id="rejected-orders" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mt-3 mb-1">
                                <h5 class="mb-0">Rejected Orders</h5>
                                <div class="d-flex align-items-center">
                                    <button type="button" onclick="submitBulkPrint('rejected-orders')" class="btn btn-dark btn-sm me-2">
                                        <i class="bi bi-printer"></i> Print Selected
                                    </button>
                                    <form method="GET" class="d-flex align-items-center">
                                        <input type="hidden" name="tab" value="rejected-orders">
                                        <label for="per_page_rejected" class="me-2 mb-0">Page Size:</label>
                                        <select name="per_page" id="per_page_rejected" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                                            <option value="10" {{ request('per_page') == 10 || !request('per_page') ? 'selected' : '' }}>10</option>
                                            <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                                            <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                            <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                                            <option value="40" {{ request('per_page') == 40 ? 'selected' : '' }}>40</option>
                                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                            <form action="{{ route('key-user.work-order.bulk-print') }}" method="POST" id="form-rejected-orders" target="_blank">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-centered table-nowrap mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width: 40px;">
                                                    <input type="checkbox" class="form-check-input select-all-btn" data-target-tab="rejected-orders">
                                                </th>
                                                <th>WO Number</th>
                                                <th>Image</th>
                                                <th>BP Code</th>
                                                <th>Product Category</th>
                                                <th>Type</th>
                                                <th>Quantity</th>
                                                <th>Created At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($rejectedOrders as $workOrder)
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" name="selected_orders[]" value="{{ $workOrder->id }}" class="form-check-input order-checkbox-rejected-orders">
                                                </td>
                                                <td>{{ $workOrder->work_order_number }}</td>
                                                <td style="width: 80px;">
                                                    @php
                                                        $displayImage = null;
                                                        $isPdf = false;
                                                        
                                                        if ($workOrder->product_image) {
                                                            $isPdf = strtolower(pathinfo($workOrder->product_image, PATHINFO_EXTENSION)) === 'pdf';
                                                            if (strpos($workOrder->product_image, 'images/') === 0 || strpos($workOrder->product_image, 'storage/') === 0) {
                                                                $displayImage = asset($workOrder->product_image);
                                                            } else {
                                                                $displayImage = asset('storage/' . $workOrder->product_image);
                                                            }
                                                        } elseif ($workOrder->product && $workOrder->product->images->count() > 0) {
                                                            $firstImg = $workOrder->product->images->first()->path;
                                                            $isPdf = strtolower(pathinfo($firstImg, PATHINFO_EXTENSION)) === 'pdf';
                                                            if (strpos($firstImg, 'http') === 0 || strpos($firstImg, 'storage/') === 0 || strpos($firstImg, 'images/') === 0) {
                                                                $displayImage = asset($firstImg);
                                                            } else {
                                                                $displayImage = asset('storage/' . $firstImg);
                                                            }
                                                        }
                                                    @endphp

                                                    @if($displayImage)
                                                        <div class="position-relative" style="width: 60px; height: 60px; cursor: pointer;" 
                                                             onclick="openUniversalPreview('{{ $displayImage }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                            @if($isPdf)
                                                                <canvas class="pdf-canvas border rounded shadow-sm" 
                                                                        data-url="{{ $displayImage }}" 
                                                                        data-desired-width="60" 
                                                                        style="width: 60px; height: 60px; object-fit: cover;"></canvas>
                                                                <div class="position-absolute top-50 start-50 translate-middle">
                                                                    <i class="bi bi-file-pdf text-danger fs-4"></i>
                                                                </div>
                                                            @else
                                                                <img src="{{ $displayImage }}" alt="Product" 
                                                                     class="rounded border shadow-sm" 
                                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 60px;">
                                                            <i class="bi bi-image fs-4"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>{{ $workOrder->bp_code }}</td>
                                                <td>{{ $workOrder->product_category }}</td>
                                                <td>{{ $workOrder->type }}</td>
                                                <td>{{ $workOrder->quantity }}</td>
                                                <td>{{ $workOrder->created_at->format('d M, Y') }}</td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('key-user.work-order.show', $workOrder) }}" 
                                                           class="btn btn-info btn-sm">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="{{ route('key-user.work-order.edit', $workOrder) }}" 
                                                           class="btn btn-primary btn-sm">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="{{ route('key-user.work-order.print', $workOrder) }}" 
                                                           class="btn btn-secondary btn-sm" target="_blank">
                                                            <i class="bi bi-printer"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="10" class="text-center">No rejected work orders found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                            <div class="d-flex justify-content-end mt-3">
                                @if(method_exists($rejectedOrders, 'links'))
                                    {{ $rejectedOrders->links() }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    function submitBulkPrint(tabId) {
        const selected = document.querySelectorAll(`.order-checkbox-${tabId}:checked`);
        if (selected.length === 0) {
            alert('Please select at least one work order to print.');
            return;
        }
        document.getElementById(`form-${tabId}`).submit();
    }

    document.querySelectorAll('.select-all-btn').forEach(btn => {
        btn.addEventListener('change', function() {
            const target = this.getAttribute('data-target-tab');
            const checkboxes = document.querySelectorAll(`.order-checkbox-${target}`);
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    });

    function toggleFilters() {
        var filterSection = document.getElementById('filterSection');
        if (filterSection.style.display === 'none') {
            filterSection.style.display = 'flex';
            localStorage.setItem('workOrderFilterVisible', 'true');
        } else {
            filterSection.style.display = 'none';
            localStorage.setItem('workOrderFilterVisible', 'false');
        }
    }

    function refreshSubcategories(categoryId) {
        const subcategorySelect = document.querySelector('select[name="subcategory_filter"]');
        if (!subcategorySelect) return;

        subcategorySelect.innerHTML = '<option value="">All Subcategories</option>';
        if (!categoryId) {
            return;
        }
        
        fetch(`/key-user/product/get-subcategories?category_id=${categoryId}`)
            .then(response => response.json())
            .then(list => {
                list.forEach(sub => {
                    const opt = document.createElement('option');
                    opt.value = sub.id;
                    opt.textContent = sub.name;
                    subcategorySelect.appendChild(opt);
                });
            })
            .catch(error => {
                console.error('Error fetching subcategories:', error);
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Toggle Filters State
        const filterVisible = localStorage.getItem('workOrderFilterVisible');
        if (filterVisible === 'true') {
            const section = document.getElementById('filterSection');
            if (section) section.style.display = 'flex';
        }

        // Add category change listener for dynamic subcategories
        const categorySelect = document.querySelector('select[name="category_filter"]');
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                refreshSubcategories(this.value);
            });
        }

        // Activate tab based on URL hash
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        
        if (tab) {
            const tabButton = document.querySelector(`[data-bs-target="#${tab}"]`);
            if (tabButton) {
                const tabObj = new bootstrap.Tab(tabButton);
                tabObj.show();
            }
        }
        
        // Add click event to tabs to update URL
        const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabId = this.getAttribute('data-bs-target').substring(1);
                const url = new URL(window.location);
                url.searchParams.set('tab', tabId);
                window.history.pushState({}, '', url);
            });
        });
    });
</script>
@endsection
@endsection