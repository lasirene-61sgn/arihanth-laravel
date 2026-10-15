@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="mb-0">Repair & Sample Management</h2>
        <a href="{{ route('admin.repairs.create') }}" class="btn btn-primary">Add New Record</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item"><a class="nav-link {{ ($tab ?? 'new') === 'new' ? 'active fw-bold' : '' }}" href="{{ route('admin.repairs.index', array_merge(request()->except('page'), ['tab' => 'new'])) }}">New Orders (Pending)</a></li>
        <li class="nav-item"><a class="nav-link {{ ($tab ?? '') === 'accepted' ? 'active fw-bold' : '' }}" href="{{ route('admin.repairs.index', array_merge(request()->except('page'), ['tab' => 'accepted'])) }}">Accepted</a></li>
        <li class="nav-item"><a class="nav-link {{ ($tab ?? '') === 'allocated' ? 'active fw-bold' : '' }}" href="{{ route('admin.repairs.index', array_merge(request()->except('page'), ['tab' => 'allocated'])) }}">Allocated to Craftman</a></li>
        <li class="nav-item"><a class="nav-link {{ ($tab ?? '') === 'craftsman_approval' ? 'active fw-bold' : '' }}" href="{{ route('admin.repairs.index', array_merge(request()->except('page'), ['tab' => 'craftsman_approval'])) }}">Craftsman Approval</a></li>
        <li class="nav-item"><a class="nav-link {{ ($tab ?? '') === 'approval' ? 'active fw-bold' : '' }}" href="{{ route('admin.repairs.index', array_merge(request()->except('page'), ['tab' => 'approval'])) }}">Approval</a></li>
        <li class="nav-item"><a class="nav-link {{ ($tab ?? '') === 'buyer_approval' ? 'active fw-bold' : '' }}" href="{{ route('admin.repairs.index', array_merge(request()->except('page'), ['tab' => 'buyer_approval'])) }}">Buyer Approval</a></li>
        <li class="nav-item"><a class="nav-link {{ ($tab ?? '') === 'completed' ? 'active fw-bold' : '' }}" href="{{ route('admin.repairs.index', array_merge(request()->except('page'), ['tab' => 'completed'])) }}">Completed</a></li>
        <li class="nav-item"><a class="nav-link {{ ($tab ?? '') === 'rejected' ? 'active fw-bold' : '' }}" href="{{ route('admin.repairs.index', array_merge(request()->except('page'), ['tab' => 'rejected'])) }}">Rejected</a></li>
    </ul>

    <!-- Filters Section -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.repairs.index') }}" id="repairsFilterForm">
                <input type="hidden" name="tab" value="{{ $tab }}">
                
                <div class="row g-3">
                    <!-- General Search -->
                    <div class="col-lg-3 col-md-6">
                        <label class="filter-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search order #, product...">
                        </div>
                    </div>

                    <!-- Craftsman Searchable Dropdown -->
                    <div class="col-lg-3 col-md-6">
                        <label class="filter-label">Craftsman</label>
                        <div class="custom-dropdown-container" id="craftsman_filter_container">
                            <div class="custom-dropdown-display" id="craftsman_filter_display">
                                <span class="display-text">All Craftsmen</span>
                                <i class="bi bi-chevron-down text-muted small"></i>
                            </div>
                            <div class="custom-dropdown-menu" id="craftsman_filter_menu" style="display: none;">
                                <input type="text" class="custom-dropdown-search" id="craftsman_filter_search" placeholder="Search for an item...">
                                <ul class="custom-dropdown-list" id="craftsman_filter_list">
                                    <li data-value="">All Craftsmen</li>
                                    @foreach($craftsmen as $craftsman)
                                        <li data-value="{{ $craftsman->craftman_code }}" class="{{ request('craftsman_filter') == $craftsman->craftman_code ? 'selected' : '' }}">
                                            {{ $craftsman->craftman_code }} - {{ $craftsman->business_name ?? $craftsman->name }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <select name="craftsman_filter" id="craftsman_filter_select" style="display: none;">
                                <option value="">All Craftsmen</option>
                                @foreach($craftsmen as $craftsman)
                                    <option value="{{ $craftsman->craftman_code }}" {{ request('craftsman_filter') == $craftsman->craftman_code ? 'selected' : '' }}>
                                        {{ $craftsman->craftman_code }} - {{ $craftsman->business_name ?? $craftsman->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Buyer Searchable Dropdown -->
                    <div class="col-lg-3 col-md-6">
                        <label class="filter-label">Buyer</label>
                        <div class="custom-dropdown-container" id="buyer_filter_container">
                            <div class="custom-dropdown-display" id="buyer_filter_display">
                                <span class="display-text">All Buyers</span>
                                <i class="bi bi-chevron-down text-muted small"></i>
                            </div>
                            <div class="custom-dropdown-menu" id="buyer_filter_menu" style="display: none;">
                                <input type="text" class="custom-dropdown-search" id="buyer_filter_search" placeholder="Search for an item...">
                                <ul class="custom-dropdown-list" id="buyer_filter_list">
                                    <li data-value="">All Buyers</li>
                                    @foreach($buyers as $buyer)
                                        <li data-value="{{ $buyer->id }}" class="{{ request('buyer_filter') == $buyer->id ? 'selected' : '' }}">
                                            {{ $buyer->bp_code }} - {{ $buyer->business_name ?? $buyer->name }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <select name="buyer_filter" id="buyer_filter_select" style="display: none;">
                                <option value="">All Buyers</option>
                                @foreach($buyers as $buyer)
                                    <option value="{{ $buyer->id }}" {{ request('buyer_filter') == $buyer->id ? 'selected' : '' }}>
                                        {{ $buyer->bp_code }} - {{ $buyer->business_name ?? $buyer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Type Searchable Dropdown -->
                    <div class="col-lg-3 col-md-6">
                        <label class="filter-label">Repair or Sample</label>
                        <div class="custom-dropdown-container" id="type_filter_container">
                            <div class="custom-dropdown-display" id="type_filter_display">
                                <span class="display-text">All Types</span>
                                <i class="bi bi-chevron-down text-muted small"></i>
                            </div>
                            <div class="custom-dropdown-menu" id="type_filter_menu" style="display: none;">
                                <input type="text" class="custom-dropdown-search" id="type_filter_search" placeholder="Search for an item...">
                                <ul class="custom-dropdown-list" id="type_filter_list">
                                    <li data-value="">All Types</li>
                                    <li data-value="repair" class="{{ request('repair_or_sample') == 'repair' ? 'selected' : '' }}">Repair</li>
                                    <li data-value="sample" class="{{ request('repair_or_sample') == 'sample' ? 'selected' : '' }}">Sample</li>
                                </ul>
                            </div>
                            <select name="repair_or_sample" id="type_filter_select" style="display: none;">
                                <option value="">All Types</option>
                                <option value="repair" {{ request('repair_or_sample') == 'repair' ? 'selected' : '' }}>Repair</option>
                                <option value="sample" {{ request('repair_or_sample') == 'sample' ? 'selected' : '' }}>Sample</option>
                            </select>
                        </div>
                    </div>

                    <!-- From Date -->
                    <div class="col-lg-3 col-md-6">
                        <label class="filter-label">From Date</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                    </div>

                    <!-- To Date -->
                    <div class="col-lg-3 col-md-6">
                        <label class="filter-label">To Date</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                    </div>

                    <!-- Filter Actions -->
                    <div class="col-lg-6 col-md-12 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-funnel"></i> Apply Filter
                        </button>
                        <a href="{{ route('admin.repairs.index', ['tab' => $tab]) }}" class="btn btn-outline-secondary px-3">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Complete Form Wrapper -->
    <form action="{{ route('admin.repairs.bulk-complete') }}" method="POST" id="bulkCompleteForm">
        @csrf
        
        @if(($tab ?? '') === 'allocated')
            <div class="mb-3">
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#bulkCompleteModal">
                    Bulk Complete Selected
                </button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                @if(($tab ?? '') === 'allocated')
                                    <th width="30" class="text-center">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                @endif
                                <th>ID</th>
                                <th>Date</th>
                                <th>Image</th>
                                <th>Buyer</th>
                                <th>Product Name</th>
                                <th>Order No</th>
                                <th>Type</th>
                                <th>Status / Details</th>
                                <th>Assigned Craftman</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($repairs as $repair)
                                <tr>
                                    @if(($tab ?? '') === 'allocated')
                                        <td class="text-center">
                                            <input type="checkbox" name="repair_ids[]" value="{{ $repair->id }}" class="form-check-input repair-checkbox">
                                        </td>
                                    @endif
                                    <td>{{ $repair->id }}</td>
                                    <td>{{ $repair->date }}</td>
                                    <td class="text-center">
                                        @if($repair->repair_image)
                                            <a href="{{ asset('storage/' . $repair->repair_image) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $repair->repair_image) }}" alt="Repair Image" width="50" class="img-thumbnail">
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $repair->buyer->bp_code ? $repair->buyer->bp_code . ' - ' : '' }}{{ $repair->buyer->business_name ?? ($repair->buyer->name ?? 'N/A') }}</td>
                                    <td>{{ $repair->product_name }}</td>
                                    <td>{{ $repair->order_number }}</td>
                                    <td>
                                        <span class="badge bg-{{ $repair->repair_or_sample === 'repair' ? 'warning text-dark' : 'info text-dark' }}">
                                            {{ ucfirst($repair->repair_or_sample) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $repair->status === 'completed' ? 'success' : 'primary' }}">
                                            {{ ucfirst($repair->status) }}
                                        </span>
                                        @if($repair->craftsman_status)
                                            <br><small class="text-muted">({{ str_replace('_', ' ', $repair->craftsman_status) }})</small>
                                        @endif

                                        @if(($tab ?? '') === 'craftsman_approval' || $repair->craftsman_status === 'pending_approval')
                                            <div class="mt-2 p-2 bg-light border rounded">
                                                <small class="d-block text-secondary"><strong>Returned By:</strong> {{ $repair->item_returned_by ?? '-' }}</small>
                                                <small class="d-block text-secondary mt-1"><strong>Collected By:</strong> {{ $repair->item_collected_by ?? '-' }}</small>
                                            </div>
                                        @endif

                                        @if(($tab ?? '') === 'approval')
                                            <div class="mt-2 p-2 bg-light border rounded">
                                                <small class="d-block text-secondary"><strong>Returned By:</strong> {{ $repair->item_returned_by ?? '-' }}</small>
                                                <small class="d-block text-secondary mt-1"><strong>Collected By:</strong> {{ $repair->item_collected_by ?? '-' }}</small>
                                            </div>
                                        @endif

                                        @if(($tab ?? '') === 'buyer_approval')
                                            <div class="mt-2 p-2 bg-light border rounded">
                                                <small class="d-block text-secondary"><strong>Item Sent By:</strong> {{ $repair->item_sent_by ?? '-' }}</small>
                                            </div>
                                        @endif

                                        @if(($tab ?? '') === 'completed')
                                            <div class="mt-2 p-2 bg-light border rounded">
                                                <small class="d-block text-secondary"><strong>Sent By:</strong> {{ $repair->item_sent_by ?? '-' }}</small>
                                                <small class="d-block text-secondary mt-1"><strong>Accepted By:</strong> {{ $repair->item_accepted_by ?? '-' }}</small>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($repair->craftman)
                                            <strong>{{ $repair->craftman->craftman_code }}</strong> - {{ $repair->craftman->business_name ?? $repair->craftman->name }}
                                        @else
                                            <span class="text-muted">Not Allocated</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.repairs.show', array_merge(['repair' => $repair->id], request()->query())) }}" class="btn btn-sm btn-info text-white">View</a>
                                        
                                        @if($repair->status === 'pending')
                                            <button type="submit" form="acceptForm{{ $repair->id }}" class="btn btn-sm btn-success">Accept</button>
                                            <button type="submit" form="rejectForm{{ $repair->id }}" class="btn btn-sm btn-danger" onclick="return confirm('Reject this order?');">Reject</button>
                                        @endif

                                        @if($repair->status === 'accepted')
                                            <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#allocateModal{{ $repair->id }}">Allocate</button>
                                        @endif

                                        @if($repair->status === 'allocated' && $repair->craftsman_status !== 'pending_approval')
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#completeModal{{ $repair->id }}">Complete</button>
                                        @endif

                                        @if($repair->craftsman_status === 'pending_approval')
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $repair->id }}">Approve</button>
                                        @endif

                                        @if($repair->craftsman_status === 'approved_by_superadmin' && empty($repair->item_sent_by))
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#sentByModal{{ $repair->id }}">Fill Item Sent By</button>
                                        @endif

                                        @if(!empty($repair->item_sent_by) && $repair->status !== 'completed')
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#buyerCompleteModal{{ $repair->id }}">Complete</button>
                                        @endif

                                        <a href="{{ route('admin.repairs.edit', array_merge(['repair' => $repair->id], request()->query())) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <button type="submit" form="deleteForm{{ $repair->id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this record?');">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">No records found for this tab.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $repairs->withQueryString()->links() }}
                </div>
            </div>
        </div>

        <!-- Bulk Complete Modal -->
        @if(($tab ?? '') === 'allocated')
        <div class="modal fade" id="bulkCompleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bulk Complete Selected Orders</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-start">
                        <div class="mb-3">
                            <label class="form-label">Item Returned By <span class="text-danger">*</span></label>
                            <input type="text" name="item_returned_by" class="form-control" list="returnedList" placeholder="Select or type name..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Item Collected By <span class="text-danger">*</span></label>
                            <input type="text" name="item_collected_by" class="form-control" list="collectedList" placeholder="Select or type name..." required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Confirm Bulk Complete</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </form>

    <!-- Isolated Action Forms & Modals -->
    @foreach($repairs as $repair)
        @if($repair->status === 'pending')
            <form id="acceptForm{{ $repair->id }}" action="{{ route('admin.repairs.accept', $repair->id) }}" method="POST" style="display: none;">
                @csrf @method('PATCH')
            </form>
            <form id="rejectForm{{ $repair->id }}" action="{{ route('admin.repairs.reject', $repair->id) }}" method="POST" style="display: none;">
                @csrf @method('PATCH')
            </form>
        @endif

        <form id="deleteForm{{ $repair->id }}" action="{{ route('admin.repairs.destroy', array_merge(['repair' => $repair->id], request()->query())) }}" method="POST" style="display: none;">
            @csrf @method('DELETE')
            @foreach(request()->query() as $key => $val)
                @if(!in_array($key, ['_token', '_method']))
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endif
            @endforeach
        </form>

        <!-- Allocation Modal -->
        @if($repair->status === 'accepted')
        <div class="modal fade" id="allocateModal{{ $repair->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.repairs.allocate', $repair->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Allocate to Craftman (Order #{{ $repair->order_number }})</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-start">
                            <div class="mb-3">
                                <label class="form-label">Select Craftman <span class="text-danger">*</span></label>
                                <select name="craftman_code" class="form-select" required>
                                    <option value="">Choose Craftman...</option>
                                    @foreach($craftsmen as $craftman)
                                        <option value="{{ $craftman->craftman_code }}">
                                            {{ $craftman->craftman_code }} - {{ $craftman->business_name ?? $craftman->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Confirm Allocation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Single Complete Modal (Allocated Tab -> Craftsman Approval) -->
        @if($repair->status === 'allocated' && $repair->craftsman_status !== 'pending_approval')
        <div class="modal fade" id="completeModal{{ $repair->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.repairs.complete', $repair->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Complete Order #{{ $repair->order_number }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-start">
                            <div class="mb-3">
                                <label class="form-label">Item Returned By <span class="text-danger">*</span></label>
                                <input type="text" name="item_returned_by" class="form-control" list="returnedList" placeholder="Select or type name..." required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Item Collected By <span class="text-danger">*</span></label>
                                <input type="text" name="item_collected_by" class="form-control" list="collectedList" placeholder="Select or type name..." required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Weight</label>
                                <input type="number" step="0.01" name="weight" class="form-control" value="{{ $repair->weight }}" placeholder="Enter weight...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Craftsman Voucher Image</label>
                                <input type="file" name="craftsman_voucher_image" class="form-control" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Craftsman Completion Proof Image</label>
                                <input type="file" name="craftsman_completion_proof_image" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Mark as Completed</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Craftsman Approval Modal -->
        @if($repair->craftsman_status === 'pending_approval')
        <div class="modal fade" id="approveModal{{ $repair->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.repairs.approve-completion', $repair->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Approve Craftsman Completion (#{{ $repair->order_number }})</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-start">
                            <div class="mb-3">
                                <label class="form-label">Item Returned By (Editable) <span class="text-danger">*</span></label>
                                <input type="text" name="item_returned_by" class="form-control" value="{{ old('item_returned_by', $repair->item_returned_by) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Item Collected By (Editable) <span class="text-danger">*</span></label>
                                <input type="text" name="item_collected_by" class="form-control" value="{{ old('item_collected_by', $repair->item_collected_by) }}" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Approve & Move to Approval Tab</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Approval Tab Modal (Fill Item Sent By) -->
        @if($repair->craftsman_status === 'approved_by_superadmin' && empty($repair->item_sent_by))
        <div class="modal fade" id="sentByModal{{ $repair->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.repairs.store-item-sent-by', $repair->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Fill Item Sent By (#{{ $repair->order_number }})</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-start">
                            <div class="mb-3">
                                <label class="form-label">Item Sent By <span class="text-danger">*</span></label>
                                <input type="text" name="item_sent_by" class="form-control" list="sentList" placeholder="Select or type name..." required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save & Move to Buyer Approval</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Buyer Approval Completion Modal (Includes Buyer Voucher & Proof Images) -->
        @if(!empty($repair->item_sent_by) && $repair->status !== 'completed')
        <div class="modal fade" id="buyerCompleteModal{{ $repair->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.repairs.buyer-complete', $repair->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Buyer Approval Completion (#{{ $repair->order_number }})</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-start">
                            <div class="mb-3">
                                <label class="form-label">Item Sent By (Editable) <span class="text-danger">*</span></label>
                                <input type="text" name="item_sent_by" class="form-control" list="sentList" value="{{ old('item_sent_by', $repair->item_sent_by) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Item Accepted By <span class="text-danger">*</span></label>
                                <input type="text" name="item_accepted_by" class="form-control" list="acceptedList" placeholder="Select or type name..." required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Buyer Voucher Image</label>
                                <input type="file" name="buyer_voucher_image" class="form-control" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Buyer Completion Proof Image</label>
                                <input type="file" name="buyer_completion_proof_image" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Mark as Completed</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @endforeach
</div>

<!-- Shared Datalists -->
<datalist id="returnedList">
    @foreach(($returnedNames ?? []) as $name)
        <option value="{{ $name }}">
    @endforeach
</datalist>

<datalist id="collectedList">
    @foreach(($collectedNames ?? []) as $name)
        <option value="{{ $name }}">
    @endforeach
</datalist>

<datalist id="sentList">
    @foreach(($sentNames ?? []) as $name)
        <option value="{{ $name }}">
    @endforeach
</datalist>

<datalist id="acceptedList">
    @foreach(($acceptedNames ?? []) as $name)
        <option value="{{ $name }}">
    @endforeach
</datalist>

<style>
.filter-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    margin-bottom: 5px;
}
.custom-dropdown-container {
    position: relative;
    width: 100%;
}
.custom-dropdown-display {
    width: 100%;
    min-height: 38px;
    padding: 7px 12px;
    background-color: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    user-select: none;
    transition: all 0.15s ease-in-out;
}
.custom-dropdown-display:hover {
    border-color: #94a3b8;
    background-color: #f1f5f9;
}
.custom-dropdown-menu {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    width: 100%;
    min-width: 220px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    z-index: 1050;
    padding: 8px;
}
.custom-dropdown-search {
    width: 100%;
    padding: 7px 10px;
    border: 1.5px solid #ec4899;
    border-radius: 6px;
    margin-bottom: 8px;
    font-size: 13px;
    outline: none;
    box-shadow: 0 0 0 2px rgba(236, 72, 153, 0.15);
}
.custom-dropdown-search:focus {
    border-color: #db2777;
    box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.25);
}
.custom-dropdown-list {
    max-height: 200px;
    overflow-y: auto;
    list-style: none;
    padding: 0;
    margin: 0;
}
.custom-dropdown-list li {
    padding: 6px 10px;
    font-size: 13px;
    border-radius: 4px;
    cursor: pointer;
    color: #334155;
    transition: background 0.1s;
}
.custom-dropdown-list li:hover {
    background-color: #f1f5f9;
}
.custom-dropdown-list li.selected {
    background-color: #fdf2f8;
    color: #be185d;
    font-weight: 600;
}
</style>

<script>
    document.getElementById('selectAll')?.addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.repair-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    function initSearchableDropdown(containerId, displayId, menuId, searchInputId, listId, hiddenSelectId, placeholder, autoSubmit = false) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const display = document.getElementById(displayId);
        const menu = document.getElementById(menuId);
        const searchInput = document.getElementById(searchInputId);
        const listContainer = document.getElementById(listId);
        const hiddenSelect = document.getElementById(hiddenSelectId);

        function getListItems() {
            return listContainer.querySelectorAll('li');
        }

        display.addEventListener('click', function(e) {
            e.stopPropagation();
            const isVisible = menu.style.display !== 'none';

            document.querySelectorAll('.custom-dropdown-menu').forEach(m => {
                if (m !== menu) m.style.display = 'none';
            });

            if (isVisible) {
                menu.style.display = 'none';
            } else {
                menu.style.display = 'block';
                searchInput.value = '';
                filterItems('');
                setTimeout(() => searchInput.focus(), 50);
            }
        });

        searchInput.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        searchInput.addEventListener('input', function() {
            filterItems(this.value.toLowerCase());
        });

        function filterItems(query) {
            getListItems().forEach(item => {
                if (!item.hasAttribute('data-original-text')) {
                    item.setAttribute('data-original-text', item.textContent.trim());
                }
                const originalText = item.getAttribute('data-original-text');
                const text = originalText.toLowerCase();

                if (query && text.includes(query)) {
                    item.style.display = 'block';
                    const regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                    item.innerHTML = originalText.replace(regex, '<span style="background-color: #fef08a; color: #1f2937; padding: 1px 4px; border-radius: 2px;">$1</span>');
                } else if (!query) {
                    item.style.display = 'block';
                    item.textContent = originalText;
                } else {
                    item.style.display = 'none';
                    item.textContent = originalText;
                }
            });
        }

        listContainer.addEventListener('click', function(e) {
            const item = e.target.closest('li');
            if (!item) return;

            const val = item.dataset.value;
            const text = item.getAttribute('data-original-text') || item.textContent.trim();

            display.querySelector('.display-text').textContent = val ? text : placeholder;
            hiddenSelect.value = val;

            getListItems().forEach(i => {
                i.classList.remove('selected');
                i.style.fontWeight = 'normal';
            });
            item.classList.add('selected');
            item.style.fontWeight = 'bold';

            menu.style.display = 'none';

            if (autoSubmit && hiddenSelect.form) {
                hiddenSelect.form.submit();
            }
        });

        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                menu.style.display = 'none';
            }
        });

        if (hiddenSelect && hiddenSelect.value) {
            const selectedItem = Array.from(getListItems()).find(i => String(i.dataset.value) === String(hiddenSelect.value));
            if (selectedItem) {
                const txt = selectedItem.getAttribute('data-original-text') || selectedItem.textContent.trim();
                display.querySelector('.display-text').textContent = txt;
                selectedItem.classList.add('selected');
                selectedItem.style.fontWeight = 'bold';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        initSearchableDropdown('craftsman_filter_container', 'craftsman_filter_display', 'craftsman_filter_menu', 'craftsman_filter_search', 'craftsman_filter_list', 'craftsman_filter_select', 'All Craftsmen');
        initSearchableDropdown('buyer_filter_container', 'buyer_filter_display', 'buyer_filter_menu', 'buyer_filter_search', 'buyer_filter_list', 'buyer_filter_select', 'All Buyers');
        initSearchableDropdown('type_filter_container', 'type_filter_display', 'type_filter_menu', 'type_filter_search', 'type_filter_list', 'type_filter_select', 'All Types');
    });
</script>
@endsection