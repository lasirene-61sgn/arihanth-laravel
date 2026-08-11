@extends('super-admin.layouts.app')

@section('title', __('messages.purchase_order_management'))

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .nav-tabs .nav-link {
        font-weight: bold;
        color: #495057;
        border: none;
    }

    .nav-tabs .nav-link.active {
        color: #0d6efd;
        border-bottom: 3px solid #0d6efd;
        background: transparent;
    }

    .table-responsive {
        padding-top: 10px;
    }

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
    tr[style*="background-color"] > td, tr[style*="background-color"] > th {
        background-color: transparent !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">{{ __('messages.purchase_order_management') }}</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a id="export-btn" href="{{ route('super-admin.purchase-order.index', array_merge(request()->all(), ['export' => 'excel'])) }}" class="btn btn-success">
                            <i class="bi bi-file-earmark-spreadsheet"></i> {{ __('messages.export') }}
                        </a>
                        <!-- <button onclick="printCurrentTab()" class="btn btn-info ms-1">
                            <i class="bi bi-printer"></i> {{ __('messages.print') }}
                        </button> -->
                        <button type="button" class="btn btn-outline-primary ms-1" onclick="toggleFilters()">
                            <i class="bi bi-funnel"></i> {{ __('messages.toggle_filters') }}
                        </button>
                    </div>
                    <a href="{{ route('super-admin.purchase-order.create') }}" class="btn btn-primary ms-1">
                        <i class="bi bi-plus-lg"></i> {{ __('messages.add_purchase_order') }}
                    </a>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="row mb-3">
                <div class="col-md-12">
                        <div id="filterSection" class="row mb-3" style="display: none;">
                            <!-- Search -->
                            <div class="col-md-3 mb-2">
                                <form method="GET" class="d-flex">
                                    <input type="hidden" name="tab" value="{{ request('tab', 'created') }}">
                                    <input type="hidden" name="category_filter" value="{{ request('category_filter') }}">
                                    <input type="hidden" name="design_code_filter" value="{{ request('design_code_filter') }}">
                                    <input type="hidden" name="filter_craftsman" value="{{ request('filter_craftsman') }}">
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search PO / Notes..." class="form-control me-2">
                                    <button type="submit" class="btn btn-outline-primary btn-sm">Search</button>
                                </form>
                            </div>

                            <!-- Category -->
                            <div class="col-md-3 mb-2">
                                <form method="GET">
                                    <input type="hidden" name="tab" value="{{ request('tab', 'created') }}">
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                    <input type="hidden" name="design_code_filter" value="{{ request('design_code_filter') }}">
                                    <input type="hidden" name="filter_craftsman" value="{{ request('filter_craftsman') }}">
                                    <select name="category_filter" onchange="this.form.submit()" class="form-select">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_filter') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>

                            <!-- Design Code -->
                            <div class="col-md-3 mb-2">
                                <form method="GET" class="d-flex">
                                    <input type="hidden" name="tab" value="{{ request('tab', 'created') }}">
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                    <input type="hidden" name="category_filter" value="{{ request('category_filter') }}">
                                    <input type="hidden" name="filter_craftsman" value="{{ request('filter_craftsman') }}">
                                    <input type="text" name="design_code_filter" value="{{ request('design_code_filter') }}" placeholder="Design Code..." class="form-control me-2">
                                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                                </form>
                            </div>

                            <!-- Craftsman -->
                            <div class="col-md-3 mb-2">
                                <form method="GET">
                                    <input type="hidden" name="tab" value="{{ request('tab', 'created') }}">
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                    <input type="hidden" name="category_filter" value="{{ request('category_filter') }}">
                                    <input type="hidden" name="design_code_filter" value="{{ request('design_code_filter') }}">
                                    <div class="d-flex">
                                        <select name="filter_craftsman" onchange="this.form.submit()" class="form-select me-2">
                                            <option value="">All Craftsmen</option>
                                            @foreach($craftsmen as $c)
                                            <option value="{{ $c->craftman_code }}" {{ request('filter_craftsman') == $c->craftman_code ? 'selected' : '' }}>{{ $c->craftman_code }} - {{ $c->business_name }}</option>
                                            @endforeach
                                        </select>
                                        <a href="{{ route('super-admin.purchase-order.index', ['tab' => request('tab', 'created')]) }}" class="btn btn-outline-secondary text-nowrap">Clear</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <ul class="nav nav-tabs" id="poTabs" role="tablist">
                @php
                $overdueOrders = collect();
                $nowGlobal = \Carbon\Carbon::now();
                
                $checkOverdue = function($po) use ($nowGlobal, &$overdueOrders) {
                    $dueDateValue = $po->craftsman_due_date ?? $po->due_date ?? null;
                    if ($dueDateValue) {
                        $dueDate = \Carbon\Carbon::parse($dueDateValue);
                        if ($dueDate->lt($nowGlobal->startOfDay()) || ($dueDate->isToday() && $nowGlobal->hour >= 12)) {
                            if(!$overdueOrders->contains('id', $po->id)) {
                                $overdueOrders->push($po);
                            }
                        }
                    }
                };

                foreach($createdOrders as $po) $checkOverdue($po);
                foreach($allocatedOrders as $po) $checkOverdue($po);
                foreach($inProcessOrders as $po) $checkOverdue($po);
                foreach($forApprovalOrders as $po) $checkOverdue($po);

                $tabDefinitions = [
                ['id' => 'overdue', 'label' => 'Overdue', 'data' => $overdueOrders],
                ['id' => 'created', 'label' => __('messages.created'), 'data' => $createdOrders],
                ['id' => 'allocated', 'label' => __('messages.allocated'), 'data' => $allocatedOrders],
                ['id' => 'in_process', 'label' => __('messages.in_process'), 'data' => $inProcessOrders],
                ['id' => 'for_approval', 'label' => __('messages.for_approval'), 'data' => $forApprovalOrders],
                ['id' => 'completed', 'label' => __('messages.completed'), 'data' => $completedOrders],
                ['id' => 'rejected', 'label' => __('messages.rejected'), 'data' => $rejectedOrders],
                ];
                @endphp
                @foreach($tabDefinitions as $index => $tab)
                <li class="nav-item">
                    <button class="nav-link {{ request('tab', 'created') == $tab['id'] ? 'active' : '' }}"
                        id="tab-{{ $tab['id'] }}"
                        data-bs-toggle="tab"
                        data-bs-target="#content-{{ $tab['id'] }}"
                        type="button" role="tab">
                        {{ $tab['label'] }} ({{ $tab['data']->count() }})
                    </button>
                </li>
                @endforeach
            </ul>

            <div class="tab-content" id="poTabsContent">
                @foreach($tabDefinitions as $index => $tab)
                <div class="tab-pane fade {{ request('tab', 'created') == $tab['id'] ? 'show active' : '' }}"
                    id="content-{{ $tab['id'] }}"
                    role="tabpanel"
                    aria-labelledby="tab-{{ $tab['id'] }}">

                    <div class="card mt-3 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center bg-white">
                            <h5 class="mb-0">{{ $tab['label'] }} Orders</h5>
                            @if(($tab['id'] == 'created' || $tab['id'] == 'allocated') && $tab['data']->count() > 0)
                            <button type="button" class="btn btn-sm btn-indigo bulk-start-btn ms-2" style="display:none; background-color: #6610f2; color: white;">
                                <i class="bi bi-play-fill"></i> Bulk Start
                            </button>
                            @endif

                            @if($tab['id'] == 'created' && $tab['data']->count() > 0)
                            <button type="button" class="btn btn-sm btn-success bulk-allocate-btn ms-2" style="display:none;" data-bs-toggle="modal" data-bs-target="#bulkAllocateModal">
                                <i class="bi bi-people"></i> {{ __('messages.bulk_allocate_selected') }}
                            </button>
                            @endif

                            @if(($tab['id'] == 'in_process' || $tab['id'] == 'for_approval') && $tab['data']->count() > 0)
                            <button type="button" class="btn btn-sm btn-success bulk-complete-btn ms-2" style="display:none; background-color: #198754; color: white;">
                                <i class="bi bi-check-circle"></i> Bulk Complete
                            </button>
                            @endif

                            @if($tab['id'] == 'for_approval' && $tab['data']->count() > 0)
                            <button type="button" class="btn btn-sm btn-success bulk-approve-btn ms-2" style="display:none;">
                                <i class="bi bi-check-all"></i> {{ __('messages.bulk_approve_selected') }}
                            </button>
                            @endif

                            @if($tab['id'] == 'completed')
                            <form method="GET" action="{{ route('super-admin.purchase-order.index') }}" class="d-inline-block ms-2" id="completed-filter-form">
                                <input type="hidden" name="tab" value="completed">
                                <select name="completed_filter" onchange="document.getElementById('completed-filter-form').submit();" class="form-select form-select-sm">
                                    <option value="">All Time</option>
                                    <option value="day" {{ request('completed_filter') == 'day' ? 'selected' : '' }}>Today</option>
                                    <option value="week" {{ request('completed_filter') == 'week' ? 'selected' : '' }}>This Week</option>
                                    <option value="month" {{ request('completed_filter') == 'month' ? 'selected' : '' }}>This Month</option>
                                </select>
                            </form>
                            @endif

                            <button type="button" class="btn btn-sm btn-dark bulk-print-btn ms-2" style="display:none;" onclick="submitBulkPrint()">
                                <i class="bi bi-printer"></i> {{ __('messages.bulk_print_share') }}
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered po-datatable" id="table-{{ $tab['id'] }}">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="30"><input type="checkbox" class="select-all"></th>
                                            <th>{{ __('messages.po_order') }}</th>
                                            <th>{{ __('messages.created_at') }}</th>
                                            <th>{{ __('messages.due_date') }}</th>
                                            <th>{{ __('messages.items') }}</th>
                                            <th>{{ __('messages.total_weight') }}</th>
                                            @if($tab['id'] != 'created')
                                            <th>{{ __('messages.craftsman') }}</th>
                                            @endif
                                            <th>{{ __('messages.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tab['data'] as $po)
                                                                                                        @php
                                $rowStyle = '';
                                $isOverdue = false;
                                $isDueWithin48Hours = false;
                                $allocatedWithin48Hours = false;
                                $now = \Carbon\Carbon::now();

                                // For PO and WorkOrder
                                $dueDateValue = null;
                                if (isset($order) && isset($order->craftsman_due_date)) {
                                    $dueDateValue = $order->craftsman_due_date;
                                } elseif (isset($order) && isset($order->due_date)) {
                                    $dueDateValue = $order->due_date;
                                } elseif (isset($po) && isset($po->due_date)) {
                                    $dueDateValue = $po->due_date;
                                }

                                if ($dueDateValue) {
                                    $dueDate = \Carbon\Carbon::parse($dueDateValue);
                                    if ($dueDate->lt($now->startOfDay()) || ($dueDate->isToday() && $now->hour >= 12)) {
                                        $isOverdue = true;
                                    } else {
                                        $hoursDiff = $now->diffInHours($dueDate, false);
                                        if ($hoursDiff >= 0 && $hoursDiff <= 48) {
                                            $isDueWithin48Hours = true;
                                        }
                                    }
                                }

                                // Handle updated_at for allocated within 48h
                                $updatedAtValue = null;
                                if (isset($order) && isset($order->updated_at)) {
                                    $updatedAtValue = $order->updated_at;
                                } elseif (isset($po) && isset($po->updated_at)) {
                                    $updatedAtValue = $po->updated_at;
                                }

                                $currentTabString = '';
                                if (isset($activeTab)) {
                                    $currentTabString = $activeTab;
                                } elseif (isset($currentTab)) {
                                    $currentTabString = $currentTab;
                                } elseif (isset($tab['id'])) {
                                    $currentTabString = $tab['id'];
                                }

                                if (($currentTabString == 'allocated-orders' || $currentTabString == 'allocated') && $updatedAtValue) {
                                    if (\Carbon\Carbon::parse($updatedAtValue)->diffInHours($now) <= 48) {
                                        $allocatedWithin48Hours = true;
                                    }
                                }

                                if ($isOverdue) {
                                    $rowStyle = 'background-color: rgba(255, 228, 230, 0.8) !important;'; // rose
                                } elseif ($isDueWithin48Hours) {
                                    $rowStyle = 'background-color: rgba(255, 237, 213, 0.8) !important;'; // orange
                                } elseif ($currentTabString == 'in-process-orders' || $currentTabString == 'in-process' || $currentTabString == 'in_process') {
                                    $rowStyle = 'background-color: rgba(220, 252, 231, 0.8) !important;'; // green
                                } elseif (($currentTabString == 'allocated-orders' || $currentTabString == 'allocated') && $allocatedWithin48Hours) {
                                    $rowStyle = 'background-color: rgba(219, 234, 254, 0.8) !important;'; // blue
                                } elseif ($currentTabString == 'new-orders' || $currentTabString == 'created') {
                                    $rowStyle = 'background-color: rgba(254, 252, 232, 0.8) !important;'; // yellow
                                }
@endphp
                                <tr class="hover:tw-bg-gray-50 tw-transition-colors  " style="{{ $rowStyle }}">
                                            <td><input type="checkbox" class="po-checkbox" value="{{ $po->id }}"></td>
                                            <td class="fw-bold text-primary">{{ $po->purchase_order_code }}</td>
                                            <td class="fw-bold text-primary">{{ $po->created_at ? $po->created_at->format('d M, Y') : 'N/A'}}</td>
                                            <td>{{ $po->due_date ? $po->due_date->format('d M, Y') : 'N/A' }}</td>
                                            <td><span class="badge bg-dark rounded-pill">{{ count($po->items ?? []) }}</span></td>
                                            <td>{{ number_format(collect($po->items)->sum('total'), 2) }}g</td>
                                            @if($tab['id'] != 'created')
                                            <td>{{ $po->allocated_craftsman_code ?? 'N/A' }}</td>
                                            @endif
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-secondary toggle-items-btn" title="Show Items">
                                                        <i class="bi bi-chevron-down"></i>
                                                    </button>
                                                    <template class="items-template">
                                                        <div class="p-3 bg-light border-bottom">
                                                            <h6 class="mb-2"><strong>Items Added:</strong></h6>
                                                            @if(is_array($po->items) && count($po->items) > 0)
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-bordered mb-0 bg-white">
                                                                        <thead class="table-secondary">
                                                                            <tr>
                                                                                <th>Category</th>
                                                                                <th>Product / Design</th>
                                                                                <th>Grams calculation</th>
                                                                                <th>Total Weight</th>
                                                                                <th>Image</th>
                                                                                <th>Notes</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($po->items as $item)
                                                                                @php
                                                                                    $productId = $item['product_id'] ?? null;
                                                                                    $prodModel = $productId ? \App\Models\Product::with(['images', 'subcategory', 'category'])->find($productId) : null;
                                                                                    
                                                                                    $prodName = $prodModel ? $prodModel->product_name : ($item['product_name'] ?? $item['manual_product'] ?? 'N/A');

                                                                                    $catName = 'N/A';
                                                                                    if (!empty($item['category_name']) && $item['category_name'] !== 'N/A') {
                                                                                        $catName = $item['category_name'];
                                                                                    } elseif (!empty($item['produts_category']) && $item['produts_category'] !== 'N/A') {
                                                                                        $catName = $item['produts_category'];
                                                                                    } elseif (!empty($item['category'])) {
                                                                                        if (is_numeric($item['category'])) {
                                                                                            $cat = \App\Models\ProductCategory::find($item['category']);
                                                                                            $catName = $cat ? $cat->name : 'N/A';
                                                                                        } else {
                                                                                            $catName = $item['category'];
                                                                                        }
                                                                                    }
                                                                                    if (($catName === 'N/A' || empty($catName)) && $prodModel && $prodModel->category) {
                                                                                        $catName = $prodModel->category->name;
                                                                                    }

                                                                                    $subName = 'N/A';
                                                                                    if (!empty($item['subcategory_name']) && $item['subcategory_name'] !== 'N/A') {
                                                                                        $subName = $item['subcategory_name'];
                                                                                    } elseif (!empty($item['sub_category_name']) && $item['sub_category_name'] !== 'N/A') {
                                                                                        $subName = $item['sub_category_name'];
                                                                                    } elseif ($prodModel && $prodModel->subcategory) {
                                                                                        $subName = $prodModel->subcategory->name;
                                                                                    } elseif (!empty($item['subcategory'])) {
                                                                                        if (is_numeric($item['subcategory'])) {
                                                                                            $sub = \App\Models\ProductSubcategory::find($item['subcategory']);
                                                                                            $subName = $sub ? $sub->name : 'N/A';
                                                                                        } else {
                                                                                            $subName = $item['subcategory'];
                                                                                        }
                                                                                    }

                                                                                    $designModel = $productId ? \App\Models\Design::where('product_id', $productId)->first() : null;
                                                                                    $designCode = $designModel ? $designModel->design_code : ($item['design_code'] ?? 'N/A');

                                                                                    $imageSrc = null;
                                                                                    if (!empty($item['image'])) {
                                                                                        $imageSrc = str_contains($item['image'], 'images/') ? asset($item['image']) : asset('storage/' . $item['image']);
                                                                                    } elseif ($designModel && !empty($designModel->image)) {
                                                                                        $imageSrc = str_starts_with($designModel->image, 'storage/') || str_starts_with($designModel->image, 'images/') ? asset($designModel->image) : asset('storage/' . $designModel->image);
                                                                                    } elseif ($prodModel && $prodModel->images->count() > 0) {
                                                                                        $path = $prodModel->images[0]->path;
                                                                                        $imageSrc = str_starts_with($path, 'storage/') || str_starts_with($path, 'images/') ? asset($path) : asset('storage/' . $path);
                                                                                    }
                                                                                @endphp
                                                                                <tr>
                                                                                    <td>{{ $catName }}</td>
                                                                                    <td>
                                                                                        <span class="fw-bold d-block">{{ $prodName }}</span>
                                                                                        <small class="text-blue-600">Sub: {{ $subName }}</small>
                                                                                        <br><small class="text-muted">Design: {{ $designCode }}</small>
                                                                                    </td>
                                                                                    <td>
                                                                                        @if(isset($item['grams']) && is_array($item['grams']))
                                                                                            @foreach($item['grams'] as $i => $gram)
                                                                                                <div>{{ $gram }}g × {{ is_array($item['quantity'] ?? null) ? ($item['quantity'][$i] ?? 1) : 1 }} = <strong>{{ number_format(is_array($item['individual_totals'] ?? null) ? ($item['individual_totals'][$i] ?? 0) : ($item['individual_totals'] ?? 0), 2) }}g</strong></div>
                                                                                            @endforeach
                                                                                        @else
                                                                                            {{ $item['grams'] ?? 0 }}g × {{ $item['quantity'] ?? 0 }} = <strong>{{ number_format((float)($item['grams'] ?? 0) * (float)($item['quantity'] ?? 0), 2) }}g</strong>
                                                                                        @endif
                                                                                    </td>
                                                                                    <td class="fw-bold">{{ number_format((float)($item['total'] ?? 0), 2) }}g</td>
                                                                                    <td>
                                                                                        @if($imageSrc)
                                                                                            <img src="{{ $imageSrc }}" class="img-thumbnail" style="max-height: 50px; cursor: pointer;" onclick="window.open(this.src, '_blank')" alt="Item Image">
                                                                                        @else
                                                                                            <span class="text-muted small">No Image</span>
                                                                                        @endif
                                                                                    </td>
                                                                                    <td><small>{{ $item['item_notes'] ?? '-' }}</small></td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            @else
                                                                <p class="text-muted mb-0">No items found.</p>
                                                            @endif
                                                        </div>
                                                    </template>
                                                    <a href="{{ route('super-admin.purchase-order.show', $po) }}" class="btn btn-outline-info" title="View"><i class="bi bi-eye"></i></a>

                                                    @if($tab['id'] == 'created')
                                                    <a href="{{ route('super-admin.purchase-order.edit', ['purchaseOrder' => $po->id, 'return_url' => url()->full()]) }}" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                                    <a href="{{ route('super-admin.purchase-order.allocate', $po) }}" class="btn btn-outline-success" title="Allocate"><i class="bi bi-person-plus"></i></a>
                                                    @endif

                                                    @if($tab['id'] == 'rejected')
                                                    <form action="{{ route('super-admin.purchase-order.reallocate', $po) }}" method="POST" class="d-inline" onsubmit="return confirm('Reset this order and move it back to Created tab?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-warning" title="Reallocate">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                    </form>
                                                    @endif

                                                    @if($tab['id'] == 'for_approval')
                                                    <form action="{{ route('super-admin.purchase-order.approve', $po) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success" title="Approve"><i class="bi bi-check-lg"></i></button>
                                                    </form>
                                                    <button type="submit" class="btn btn-outline-warning" title="Reallocate">
                                                        <i class="bi bi-arrow-repeat"></i>
                                                    </button>
                                                    @endif

                                                    @if($tab['id'] == 'completed')
                                                    <form action="{{ route('super-admin.purchase-order.reallocate', $po) }}" method="POST" class="d-inline" onsubmit="return confirm('Reset this order and move it back to Created tab?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-warning" title="Reallocate">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                    </form>
                                                    <a href="{{ route('super-admin.purchase-order.copy', $po) }}"
                                                        class="btn btn-outline-success" title="Copy">
                                                        <i class="bi bi-copy"></i>
                                                    </a>
                                                    @endif


                                                    <form action="{{ route('super-admin.purchase-order.destroy', $po) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this PO?');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- MODALS --}}
<div class="modal fade" id="bulkAllocateModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('super-admin.purchase-order.bulk-allocate') }}" method="POST">
            @csrf
            <div id="selected-ids-container"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.bulk_allocate_orders') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.select_craftsman') }}</label>
                        <select name="craftsman_code" class="form-select select2-bulk" required>
                            <option value="">{{ __('messages.select_craftsman') }}</option>
                            @foreach($craftsmen as $c)
                            <option value="{{ $c->craftman_code }}">{{ $c->craftman_code }} - {{ $c->business_name }} {{ $c->dear ? '('.$c->dear.')' : '' }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">This will move selected orders to "Allocated" status.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Craftsman Due Date</label>
                        <input type="date" name="craftsman_due_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('messages.allocate_now') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<form id="bulkApproveForm" action="{{ route('super-admin.purchase-order.bulk-approve') }}" method="POST" style="display:none;">
    @csrf
    <div id="bulk-approve-ids"></div>
</form>

<form id="bulkCompleteForm" action="{{ route('super-admin.purchase-order.bulk-complete') }}" method="POST" style="display:none;">
    @csrf
    <div id="bulk-complete-ids"></div>
</form>

<!-- Bulk Print Form (Hidden) -->
<form id="bulkPrintForm" action="{{ route('super-admin.purchase-order.bulk-print') }}" method="POST" target="_blank">
    @csrf
    <div id="print-ids-container"></div>
</form>


@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Update export link on tab change
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("id").replace('tab-', '');
            var url = new URL($('#export-btn').attr('href'));
            url.searchParams.set('tab', target);
            $('#export-btn').attr('href', url.toString());
        });

        // Initialize Select2 for Bulk Modal
        $('#bulkAllocateModal').on('shown.bs.modal', function() {
            $('.select2-bulk').select2({
                dropdownParent: $('#bulkAllocateModal'),
                placeholder: "Select Craftsman",
                allowClear: true,
                width: '100%'
            });
        });

        // Initialize DataTables
        $('.po-datatable').each(function() {
            $(this).DataTable({
                "order": [
                    [0, "desc"]
                ],
                "pageLength": 10,
                "dom": 'rtip'
            });
        });

        // Toggle child rows
        $('.po-datatable tbody').on('click', '.toggle-items-btn', function () {
            var tr = $(this).closest('tr');
            var table = $(this).closest('table').DataTable();
            var row = table.row(tr);
            var icon = $(this).find('i');

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
                icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
            } else {
                var templateContent = tr.find('.items-template').html();
                row.child(templateContent).show();
                tr.addClass('shown');
                icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
            }
        });

        // Checkbox Logic
        $('.select-all').on('change', function() {
            var pane = $(this).closest('.tab-pane');
            pane.find('.po-checkbox').prop('checked', this.checked);
            updateBulkBtns(pane);
        });

        $(document).on('change', '.po-checkbox', function() {
            updateBulkBtns($(this).closest('.tab-pane'));
        });

        function updateBulkBtns(pane) {
            var count = pane.find('.po-checkbox:checked').length;
            if (count > 0) {
                // Only show buttons if they exist in the current pane (prevents showing wrong buttons in wrong tabs)
                if (pane.find('.bulk-allocate-btn').length) pane.find('.bulk-allocate-btn').show();
                if (pane.find('.bulk-print-btn').length) pane.find('.bulk-print-btn').show();
                if (pane.find('.bulk-approve-btn').length) pane.find('.bulk-approve-btn').show();
                if (pane.find('.bulk-start-btn').length) pane.find('.bulk-start-btn').show();
                if (pane.find('.bulk-complete-btn').length) pane.find('.bulk-complete-btn').show();
            } else {
                if (pane.find('.bulk-allocate-btn').length) pane.find('.bulk-allocate-btn').hide();
                if (pane.find('.bulk-print-btn').length) pane.find('.bulk-print-btn').hide();
                if (pane.find('.bulk-approve-btn').length) pane.find('.bulk-approve-btn').hide();
                if (pane.find('.bulk-start-btn').length) pane.find('.bulk-start-btn').hide();
                if (pane.find('.bulk-complete-btn').length) pane.find('.bulk-complete-btn').hide();
            }
        }

        $('.bulk-allocate-btn').on('click', function() {
            var container = $('#selected-ids-container');
            container.empty();
            var pane = $(this).closest('.tab-pane');
            pane.find('.po-checkbox:checked').each(function() {
                container.append('<input type="hidden" name="order_ids[]" value="' + $(this).val() + '">');
            });
        });

        $('.bulk-approve-btn').on('click', function() {
            if (!confirm('Are you sure you want to approve selected orders?')) return;

            var container = $('#bulk-approve-ids');
            container.empty();
            var pane = $(this).closest('.tab-pane');
            pane.find('.po-checkbox:checked').each(function() {
                container.append('<input type="hidden" name="order_ids[]" value="' + $(this).val() + '">');
            });
            $('#bulkApproveForm').submit();
        });

        $('.bulk-complete-btn').on('click', function() {
            if (!confirm('Are you sure you want to mark selected orders as completed?')) return;

            var container = $('#bulk-complete-ids');
            container.empty();
            var pane = $(this).closest('.tab-pane');
            pane.find('.po-checkbox:checked').each(function() {
                container.append('<input type="hidden" name="order_ids[]" value="' + $(this).val() + '">');
            });
            $('#bulkCompleteForm').submit();
        });

        $('.bulk-start-btn').on('click', function() {
            if (!confirm('Are you sure you want to start selected orders? This will move them to In Process.')) return;

            var container = $('#bulk-complete-ids');
            container.empty();
            var pane = $(this).closest('.tab-pane');
            pane.find('.po-checkbox:checked').each(function() {
                container.append('<input type="hidden" name="order_ids[]" value="' + $(this).val() + '">');
            });
            $('#bulkCompleteForm').submit();
        });
    });

    function submitBulkPrint() {
        var container = $('#print-ids-container');
        container.empty();

        // Find active tab and get checked checkboxes
        var activePane = $('.tab-pane.active');
        var checkedBoxes = activePane.find('.po-checkbox:checked');

        if (checkedBoxes.length === 0) {
            alert('Please select at least one order to print.');
            return;
        }

        checkedBoxes.each(function() {
            container.append('<input type="hidden" name="order_ids[]" value="' + $(this).val() + '">');
        });

        $('#bulkPrintForm').submit();
    }

    // Toggle filter section visibility


    // Toggle filter section visibility
    function toggleFilters() {
        const filterSection = document.getElementById('filterSection');
        if (filterSection) {
            filterSection.style.display = filterSection.style.display === 'none' ? 'block' : 'none';
        }
    }

    // Print only the current tab's table data
    function printCurrentTab() {
        // Get the currently active tab
        const activeTab = document.querySelector('.tab-pane.active');
        if (!activeTab) return;

        // Clone the active tab content
        const printWindow = window.open('', '_blank');
        const tabTitle = activeTab.previousElementSibling?.textContent?.trim() || 'Purchase Orders';

        // Get the table from the active tab
        const table = activeTab.querySelector('table');
        if (!table) {
            alert('No table found to print');
            return;
        }

        // Create print content
        const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print Purchase Orders - ${tabTitle}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                h2 { color: #333; }
                .print-header { text-align: center; margin-bottom: 20px; }
                @media print {
                    body { margin: 0; }
                    table { page-break-inside: auto; }
                    tr { page-break-inside: avoid; page-break-after: auto; }
                }
            </style>
        </head>
        <body>
            <div class="print-header">
                <h2>${tabTitle}</h2>
                <p>Printed on: ${new Date().toLocaleDateString()}</p>
            </div>
            ${table.outerHTML}
        </body>
        </html>
    `;

        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }
</script>
@endsection
