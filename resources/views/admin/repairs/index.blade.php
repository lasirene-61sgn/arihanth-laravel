@extends('admin.layouts.app')

@section('title', 'Repairs')

@section('styles')
<style>
    /* Custom Searchable Dropdown Styles */
    .custom-dropdown-container {
        position: relative;
        width: 100%;
    }
    .custom-dropdown-display {
        width: 100%;
        height: 38px;
        padding: 6px 12px;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background-color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        font-size: 1rem;
    }
    .custom-dropdown-display:after {
        content: "\F282";
        font-family: "bootstrap-icons";
        font-size: 0.8rem;
    }
    .custom-dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: #fff;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 0.375rem 0.375rem;
        z-index: 1050;
        display: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        padding: 10px;
    }
    .custom-dropdown-search {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }
    .custom-dropdown-search:focus {
        outline: none;
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .custom-dropdown-list {
        max-height: 200px;
        overflow-y: auto;
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .custom-dropdown-item {
        padding: 8px 12px;
        cursor: pointer;
        font-size: 0.95rem;
        border-radius: 0.25rem;
    }
    .custom-dropdown-item:hover {
        background-color: #f8f9fa;
    }
    .custom-dropdown-item.selected {
        background-color: #e9ecef;
        font-weight: bold;
    }
    .custom-dropdown-item.hidden {
        display: none;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Repairs</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="{{ route('admin.repairs.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Add New Repair
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success mt-3">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card mt-3">
                <div class="card-body">
                    <form action="{{ route('admin.repairs.index') }}" method="GET" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="ID, Product, Buyer, or Craftsman" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Buyer (BP Code)</label>
                            <div class="custom-dropdown-container" id="bp_code_container">
                                <div class="custom-dropdown-display" id="bp_code_display">--Select BP Code--</div>
                                <div class="custom-dropdown-menu" id="bp_code_menu">
                                    <input type="text" class="custom-dropdown-search" id="bp_code_search" placeholder="Search for an item...">
                                    <ul class="custom-dropdown-list" id="bp_code_list">
                                        <li class="custom-dropdown-item" data-value="">All Buyers</li>
                                        @foreach($buyers as $buyer)
                                            <li class="custom-dropdown-item" data-value="{{ $buyer->bp_code }} - {{$buyer->customer_name}}">
                                                {{ $buyer->bp_code }} - {{ $buyer->customer_name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <select name="bp_code" id="bp_code_hidden" style="display: none;">
                                    <option value="">All Buyers</option>
                                    @foreach($buyers as $buyer)
                                        <option value="{{ $buyer->bp_code }}" {{ request('bp_code') == $buyer->bp_code ? 'selected' : '' }}>
                                            {{ $buyer->bp_code }} - {{ $buyer->customer_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Craftsman</label>
                            <div class="custom-dropdown-container" id="craftsman_container">
                                <div class="custom-dropdown-display" id="craftsman_display">--Select Craftsman--</div>
                                <div class="custom-dropdown-menu" id="craftsman_menu">
                                    <input type="text" class="custom-dropdown-search" id="craftsman_search" placeholder="Search for an item...">
                                    <ul class="custom-dropdown-list" id="craftsman_list">
                                        <li class="custom-dropdown-item" data-value="">All Craftsmen</li>
                                        @foreach($craftsmen as $craftsman)
                                            <li class="custom-dropdown-item" data-value="{{ $craftsman->craftman_code }}">
                                                {{ $craftsman->craftman_code }} - {{ $craftsman->name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <select name="craftsman_code" id="craftsman_hidden" style="display: none;">
                                    <option value="">All Craftsmen</option>
                                    @foreach($craftsmen as $craftsman)
                                        <option value="{{ $craftsman->craftman_code }}" {{ request('craftsman_code') == $craftsman->craftman_code ? 'selected' : '' }}>
                                            {{ $craftsman->craftman_code }} - {{ $craftsman->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <div class="input-group">
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                <span class="input-group-text">to</span>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-filter"></i> Filter</button>
                            <a href="{{ route('admin.repairs.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i> Reset</a>
                        </div>
                    </form>

                    <ul class="nav nav-tabs mb-3" id="repairTabs" role="tablist">
                        
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'new' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'new']) }}">
                                New <span class="badge bg-primary ms-1">{{ $counts['new'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'allocated' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'allocated']) }}">
                                Allocated <span class="badge bg-info ms-1">{{ $counts['allocated'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'in_process' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'in_process']) }}">
                                In Process <span class="badge bg-warning text-dark ms-1">{{ $counts['in_process'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'completed' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'completed']) }}">
                                Completed <span class="badge bg-success ms-1">{{ $counts['completed'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'rejected' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'rejected']) }}">
                                Rejected <span class="badge bg-danger ms-1">{{ $counts['rejected'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'for_approval' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'for_approval', 'page' => null]) }}">
                                Craftsman Approval <span class="badge bg-secondary ms-1">{{ $counts['for_approval'] ?? 0 }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'buyer_approval' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'buyer_approval', 'page' => null]) }}">
                                Buyer Approval <span class="badge bg-secondary ms-1">{{ $counts['buyer_approval'] ?? 0 }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'all' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'all']) }}">
                                All <span class="badge bg-secondary ms-1">{{ $counts['all'] ?? 0 }}</span>
                            </a>
                        </li>
                    </ul>
                    
                    <div class="mb-3">
                        @if($activeTab == 'for_approval' || $activeTab == 'all')
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#bulkCompleteModal">
                            <i class="bi bi-check-circle"></i> Bulk Craftsman Approve
                        </button>
                        @endif
                        @if($activeTab == 'buyer_approval' || $activeTab == 'all')
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#bulkBuyerCompleteModal">
                            <i class="bi bi-check-all"></i> Bulk Buyer Approve
                        </button>
                        @endif
                    </div>

                    <form id="bulkCompleteForm" method="POST" enctype="multipart/form-data">
                        @csrf

                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAllRepairs"></th>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>BP Code</th>
                                    <th>Product Name</th>
                                    <th>Weight</th>
                                    <th>Item Given To</th>
                                    <th>Status</th>
                                    <th>Craftsman</th>
                                    <th>Proof</th>
                                    <th>Completion Proof</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($repairs as $repair)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="repair_ids[]" value="{{ $repair->id }}" class="repair-checkbox">
                                        </td>
                                        <td>{{ $repair->id }}</td>
                                        <td>{{ \Carbon\Carbon::parse($repair->repair_date)->format('d M Y') }}</td>
                                        <td>{{ $repair->buyer ? $repair->buyer->bp_code : 'N/A' }}- {{$repair->buyer->business_name}} </td>
                                        <td>{{ $repair->product_name }}</td>
                                        <td>{{ $repair->weight }}</td>
                                        <td>{{ $repair->item_given_to }}</td>
                                        <td>
                                            @if($repair->status == 'Pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif($repair->status == 'Accepted')
                                                <span class="badge bg-info">Accepted</span>
                                            @elseif($repair->status == 'In_Process')
                                                <span class="badge bg-info">In Process</span>
                                            @elseif($repair->status == 'Allocated')
                                                <span class="badge bg-primary">Allocated</span>
                                            @elseif($repair->status == 'Craftsman_Completed')
                                                <span class="badge bg-success">Craftsman Completed</span>
                                            @elseif($repair->status == 'Craftsman_Rejected')
                                                <span class="badge bg-danger">Craftsman Rejected</span>
                                            @elseif($repair->status == 'Completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($repair->status == 'Rejected_by_Admin')
                                                <span class="badge bg-danger">Rejected</span>
                                            @elseif($repair->status == 'Buyer_Accepted')
                                                <span class="badge bg-success">Buyer Accepted</span>
                                            @elseif($repair->status == 'Buyer_Rejected')
                                                <span class="badge bg-danger">Buyer Rejected</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $repair->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($repair->craftsman)
                                                {{ $repair->craftsman->craftman_code }}<br>
                                                <small class="text-muted">{{ $repair->craftsman->name }}</small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($repair->image_proof)
                                                <a href="{{ asset($repair->image_proof) }}" target="_blank">View</a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if($repair->completion_proof)
                                                <a href="{{ asset($repair->completion_proof) }}" target="_blank" class="text-success fw-semibold"><i class="bi bi-camera me-1"></i>View</a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{-- View --}}
                                            <a href="{{ route('admin.repairs.show', $repair->id) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            {{-- Edit (available for all statuses within 60-day window) --}}
                                            @php
                                                $isEditable = $repair->created_at->gt(now()->subDays(60));
                                            @endphp
                                            @if($isEditable)
                                                <a href="{{ route('admin.repairs.edit', $repair->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif

                                            {{-- Accept --}}
                                            @if($repair->status == 'Pending')
                                                <button type="submit" formaction="{{ route('admin.repairs.accept', $repair->id) }}" class="btn btn-sm btn-success" title="Accept"><i class="bi bi-check-lg"></i></button>
                                            @endif

                                            {{-- Reject --}}
                                            @if(in_array($repair->status, ['Pending', 'Craftsman_Completed']))
                                                <button type="button" class="btn btn-sm btn-danger" title="Reject" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $repair->id }}">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            @endif

                                            {{-- Allocate --}}
                                            @if(in_array($repair->status, ['Accepted', 'Craftsman_Rejected']))
                                                <button type="button" class="btn btn-sm btn-primary" title="Allocate to Craftsman" data-bs-toggle="modal" data-bs-target="#allocateModal{{ $repair->id }}">
                                                    <i class="bi bi-person-plus"></i>
                                                </button>
                                            @endif

                                            {{-- Complete (after craftsman completed) --}}
                                            @if(!in_array($repair->status, ['Buyer_Accepted', 'Completed', 'Rejected_by_Admin', 'Buyer_Rejected', 'Pending', 'Accepted', 'Allocated']))
                                                <button type="button" class="btn btn-sm btn-success" title="Craftsman Approval" data-bs-toggle="modal" data-bs-target="#completeModal{{ $repair->id }}">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            @endif

                                            {{-- Buyer Complete --}}
                                            @if($repair->status == 'Completed')
                                                <button type="button" class="btn btn-sm btn-success" title="Buyer Approval" data-bs-toggle="modal" data-bs-target="#buyerCompleteModal{{ $repair->id }}">
                                                    <i class="bi bi-check-all"></i>
                                                </button>
                                            @endif

                                            {{-- Delete --}}
                                            <button type="submit" form="deleteForm{{$repair->id}}" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this repair order?');">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center">No repairs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Bulk Complete Modal --}}
                    <div class="modal fade" id="bulkCompleteModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Bulk Craftsman Approve Selected Repairs</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-3 text-muted">Are you sure you want to approve and mark the selected repairs as Craftsman Completed?</p>
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-muted small mb-1">Update Item Received Through (Optional)</label>
                                        <select name="item_received_through" id="bulkReceivedThroughSelect" class="form-select" onchange="checkCustomInput('bulkReceivedThroughSelect', 'bulkReceivedThroughCustom')">
                                            <option value="">-- Keep Existing --</option>
                                            @php
                                                $receivedThroughOptions = \App\Models\Repair::whereNotNull('item_received_through')->distinct()->pluck('item_received_through');
                                            @endphp
                                            @foreach($receivedThroughOptions as $opt)
                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                            <option value="__custom__">+ Add New...</option>
                                        </select>
                                        <input type="text" name="item_received_through_custom" id="bulkReceivedThroughCustom" placeholder="Enter new source..." class="form-control mt-2 d-none">
                                        <div class="form-text text-muted">Leave empty to keep the craftsman's original value for each repair.</div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" formaction="{{ route('admin.repairs.bulk-complete') }}" class="btn btn-success" onclick="return confirm('Approve selected repairs?')">Confirm Craftsman Approval</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bulk Buyer Complete Modal --}}
                    <div class="modal fade" id="bulkBuyerCompleteModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Bulk Buyer Approve Selected Repairs</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-3 text-muted">Are you sure you want to approve and mark the selected repairs as fully Completed?</p>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Delivered By Type <span class="text-danger">*</span></label>
                                        <select name="item_delivered_by_type" class="form-select">
                                            <option value="AJPL">AJPL</option>
                                            <option value="Self">Self</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Item Delivered By <span class="text-danger">*</span></label>
                                        <select name="item_delivered_by" id="bulkDeliveredBySelect" class="form-select" onchange="checkCustomInput('bulkDeliveredBySelect', 'bulkDeliveredByCustom')">
                                            <option value="">-- Select Person --</option>
                                            @php
                                                $deliveredByOptions = \App\Models\Repair::where('item_delivered_by_type', 'AJPL')->whereNotNull('item_delivered_by')->distinct()->pluck('item_delivered_by');
                                            @endphp
                                            @foreach($deliveredByOptions as $opt)
                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                            <option value="__custom__">+ Add New...</option>
                                        </select>
                                        <input type="text" name="item_delivered_by_custom" id="bulkDeliveredByCustom" placeholder="Enter delivery person..." class="form-control mt-2 d-none">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Item Delivered To <span class="text-danger">*</span></label>
                                        <select name="item_delivered_to" id="bulkDeliveredToSelect" class="form-select" onchange="checkCustomInput('bulkDeliveredToSelect', 'bulkDeliveredToCustom')">
                                            <option value="">-- Select Person --</option>
                                            @php
                                                $deliveredToOptions = \App\Models\Repair::whereNotNull('item_delivered_to')->distinct()->pluck('item_delivered_to');
                                            @endphp
                                            @foreach($deliveredToOptions as $opt)
                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                            <option value="__custom__">+ Add New...</option>
                                        </select>
                                        <input type="text" name="item_delivered_to_custom" id="bulkDeliveredToCustom" placeholder="Enter recipient..." class="form-control mt-2 d-none">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" formaction="{{ route('admin.repairs.bulk-buyer-complete') }}" class="btn btn-success" onclick="return confirm('Mark selected repairs as fully completed?')">Confirm Buyer Approval</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    </form>

                    {{-- Individual Complete Modals and Actions --}}
                    @foreach($repairs as $repair)
                        <form id="deleteForm{{$repair->id}}" action="{{ route('admin.repairs.destroy', $repair->id) }}" method="POST" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>

                        @if(in_array($repair->status, ['Pending', 'Craftsman_Completed']))
                        <div class="modal fade" id="rejectModal{{ $repair->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.repairs.reject', $repair->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reject Repair #{{ $repair->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Rejection Reason</label>
                                                <textarea name="reject_reason" class="form-control" rows="3" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Reject</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(in_array($repair->status, ['Accepted', 'Craftsman_Rejected']))
                        <div class="modal fade" id="allocateModal{{ $repair->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.repairs.allocate', $repair->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Allocate Repair #{{ $repair->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Select Craftsman</label>
                                                <select name="craftsman_code" class="form-select" required>
                                                    <option value="">-- Select Craftsman --</option>
                                                    @foreach(\App\Models\Craftman::all() as $c)
                                                        <option value="{{ $c->craftman_code }}">{{ $c->craftman_code }} - {{ $c->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Allocation Notes</label>
                                                <textarea name="allocation_notes" class="form-control" rows="3" placeholder="Add any specific instructions for the craftsman..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Allocate</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif


                        @if(!in_array($repair->status, ['Buyer_Accepted', 'Completed', 'Rejected_by_Admin', 'Buyer_Rejected', 'Pending', 'Accepted', 'Allocated']))
                        <div class="modal fade" id="completeModal{{ $repair->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.repairs.complete', $repair->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Craftsman Approval for Repair #{{ $repair->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            @if($repair->craftsman)
                                            <div class="alert alert-info py-2 mb-3 small">
                                                <strong>Allocated Craftsman:</strong> {{ $repair->craftsman->craftman_code }} - {{ $repair->craftsman->name }}
                                            </div>
                                            @endif
                                            
                                            <div class="mb-3">
                                                <label class="form-label text-muted small mb-1">Item Received Through</label>
                                                <select name="item_received_through" id="receivedThroughSelect{{ $repair->id }}" class="form-select" onchange="checkCustomInput('receivedThroughSelect{{ $repair->id }}', 'receivedThroughCustom{{ $repair->id }}')">
                                                    <option value="">-- Select Source --</option>
                                                    @php
                                                        $receivedThroughOptions = \App\Models\Repair::whereNotNull('item_received_through')->distinct()->pluck('item_received_through');
                                                    @endphp
                                                    @foreach($receivedThroughOptions as $opt)
                                                        <option value="{{ $opt }}" {{ $repair->item_received_through == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                    <option value="__custom__">+ Add New...</option>
                                                </select>
                                                <input type="text" name="item_received_through_custom" id="receivedThroughCustom{{ $repair->id }}" placeholder="Enter new source..." class="form-control mt-2 d-none">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label text-muted small mb-1">Completion Proof</label>
                                                <div>
                                                    @if($repair->completion_proof)
                                                        <img src="{{ asset($repair->completion_proof) }}" alt="Completion Proof" class="img-fluid rounded border" style="max-height: 250px;">
                                                    @else
                                                        <span class="text-muted fst-italic">No proof uploaded by craftsman.</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success">Approve</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($repair->status == 'Completed')
                        <div class="modal fade" id="buyerCompleteModal{{ $repair->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.repairs.buyer-complete', $repair->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Buyer Approval for Repair #{{ $repair->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Delivered By Type <span class="text-danger">*</span></label>
                                                <select name="item_delivered_by_type" class="form-select" required>
                                                    <option value="AJPL" {{ $repair->item_delivered_by_type == 'AJPL' ? 'selected' : '' }}>AJPL</option>
                                                    <option value="Self" {{ $repair->item_delivered_by_type == 'Self' ? 'selected' : '' }}>Self</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Item Delivered By <span class="text-danger">*</span></label>
                                                <select name="item_delivered_by" id="deliveredBySelect{{ $repair->id }}" class="form-select" required onchange="checkCustomInput('deliveredBySelect{{ $repair->id }}', 'deliveredByCustom{{ $repair->id }}')">
                                                    <option value="">-- Select Person --</option>
                                                    @php
                                                        $deliveredByOptions = \App\Models\Repair::where('item_delivered_by_type', 'AJPL')->whereNotNull('item_delivered_by')->distinct()->pluck('item_delivered_by');
                                                    @endphp
                                                    @foreach($deliveredByOptions as $opt)
                                                        <option value="{{ $opt }}" {{ $repair->item_delivered_by == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                    <option value="__custom__">+ Add New...</option>
                                                </select>
                                                <input type="text" name="item_delivered_by_custom" id="deliveredByCustom{{ $repair->id }}" placeholder="Enter delivery person..." class="form-control mt-2 d-none">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Item Delivered To <span class="text-danger">*</span></label>
                                                <select name="item_delivered_to" id="deliveredToSelect{{ $repair->id }}" class="form-select" required onchange="checkCustomInput('deliveredToSelect{{ $repair->id }}', 'deliveredToCustom{{ $repair->id }}')">
                                                    <option value="">-- Select Person --</option>
                                                    @php
                                                        $deliveredToOptions = \App\Models\Repair::whereNotNull('item_delivered_to')->distinct()->pluck('item_delivered_to');
                                                    @endphp
                                                    @foreach($deliveredToOptions as $opt)
                                                        <option value="{{ $opt }}" {{ $repair->item_delivered_to == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                    <option value="__custom__">+ Add New...</option>
                                                </select>
                                                <input type="text" name="item_delivered_to_custom" id="deliveredToCustom{{ $repair->id }}" placeholder="Enter recipient..." class="form-control mt-2 d-none">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success">Complete Delivery</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach

                    <div class="mt-3">
                        {{ $repairs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Bulk Select Logic
        const selectAll = document.getElementById('selectAllRepairs');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.repair-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        }

        // GENERIC SEARCHABLE DROPDOWN
        function initSearchableDropdown(containerId, displayId, menuId, searchInputId, listId, hiddenSelectId, placeholder, onSelect = null) {
            const container = document.getElementById(containerId);
            if (!container) return;

            const display = document.getElementById(displayId);
            const menu = document.getElementById(menuId);
            const searchInput = document.getElementById(searchInputId);
            const listContainer = document.getElementById(listId);
            const hiddenSelect = document.getElementById(hiddenSelectId);

            function getListItems() {
                return listContainer.querySelectorAll('.custom-dropdown-item');
            }

            display.addEventListener('click', function(e) {
                e.stopPropagation();
                const isVisible = menu.style.display === 'block';
                // Close all other menus first
                document.querySelectorAll('.custom-dropdown-menu').forEach(m => {
                    if (m !== menu) m.style.display = 'none';
                });
                menu.style.display = isVisible ? 'none' : 'block';
                if (!isVisible) {
                    searchInput.focus();
                    searchInput.value = '';
                    filterItems('');
                }
            });

            searchInput.addEventListener('input', function() {
                filterItems(this.value.toLowerCase());
            });

            function filterItems(query) {
                getListItems().forEach(item => {
                    const text = item.textContent.toLowerCase();
                    if (text.includes(query)) {
                        item.classList.remove('hidden');
                    } else {
                        item.classList.add('hidden');
                    }
                });
            }

            listContainer.addEventListener('click', function(e) {
                const item = e.target.closest('.custom-dropdown-item');
                if (!item) return;

                const val = item.dataset.value;
                const text = item.textContent.trim();
                
                display.textContent = val ? text : placeholder;
                hiddenSelect.value = val;
                
                hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
                
                getListItems().forEach(i => i.classList.remove('selected'));
                item.classList.add('selected');
                
                menu.style.display = 'none';

                if (onSelect) {
                    onSelect(val, item);
                }
            });

            document.addEventListener('click', function(e) {
                if (!container.contains(e.target)) {
                    menu.style.display = 'none';
                }
            });

            // Set initial state from existing value
            if (hiddenSelect.value) {
                const selectedItem = Array.from(getListItems()).find(i => i.dataset.value === hiddenSelect.value);
                if (selectedItem) {
                    display.textContent = selectedItem.textContent.trim();
                    selectedItem.classList.add('selected');
                }
            }
        }

        // Initialize Dropdowns
        initSearchableDropdown('bp_code_container', 'bp_code_display', 'bp_code_menu', 'bp_code_search', 'bp_code_list', 'bp_code_hidden', '--Select BP Code--');
        initSearchableDropdown('craftsman_container', 'craftsman_display', 'craftsman_menu', 'craftsman_search', 'craftsman_list', 'craftsman_hidden', '--Select Craftsman--');
    });

    function checkCustomInput(selectId, inputId) {
        const select = document.getElementById(selectId);
        const input = document.getElementById(inputId);
        if (select.value === '__custom__') {
            input.classList.remove('d-none');
            input.required = true;
            input.focus();
        } else {
            input.classList.add('d-none');
            input.required = false;
            input.value = '';
        }
    }

    function checkCustomCraftsman(selectId, containerId) {
        const select = document.getElementById(selectId);
        const container = document.getElementById(containerId);
        if (select.value === '__custom__') {
            container.classList.remove('d-none');
        } else {
            container.classList.add('d-none');
        }
    }
</script>
@endsection
