@extends('super-admin.layouts.app')

@section('title', 'Repairs')

@section('styles')
<style>
.custom-dropdown-container {
    position: relative;
    width: 100%;
}
.custom-dropdown-display {
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 4px;
    padding: 6px 12px;
    cursor: pointer;
    font-size: 14px;
    color: #495057;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    position: relative;
    min-height: 38px;
    display: flex;
    align-items: center;
}
.custom-dropdown-display::after {
    content: "";
    width: 0; 
    height: 0; 
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid #adb5bd;
    position: absolute;
    right: 10px;
}
.custom-dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    background-color: #ffffff;
    border: 1px solid #ced4da;
    border-radius: 4px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    z-index: 1050;
    margin-top: 2px;
}
.custom-dropdown-menu.active {
    display: block;
}
.custom-dropdown-search {
    width: 100%;
    padding: 8px 10px;
    border: none;
    border-bottom: 1px solid #eee;
    outline: none;
    font-size: 13px;
}
.custom-dropdown-list {
    list-style: none;
    margin: 0;
    padding: 0;
    max-height: 250px;
    overflow-y: auto;
}
.custom-dropdown-item {
    padding: 10px 12px;
    font-size: 13px;
    color: #333;
    cursor: pointer;
    transition: background 0.2s;
}
.custom-dropdown-item:hover {
    background-color: #f8f9fa;
    color: #007bff;
}
.form-label {
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 5px;
    display: block;
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
                    <a href="{{ route('super-admin.repairs.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Add New Repair
                    </a>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger mt-3">{{ session('error') }}</div>
            @endif

            <div class="card mt-3">
                <div class="card-body">
                    <form action="{{ route('super-admin.repairs.index') }}" method="GET" class="row g-3 mb-4">
                        <input type="hidden" name="tab" value="{{ $activeTab }}">
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
                                            <li class="custom-dropdown-item" data-value="{{ $buyer->bp_code }}">
                                                {{ $buyer->bp_code }} - {{ $buyer->customer_name ?? $buyer->business_name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <select name="bp_code" id="bp_code_hidden" style="display: none;">
                                    <option value="">All Buyers</option>
                                    @foreach($buyers as $buyer)
                                        <option value="{{ $buyer->bp_code }}" {{ request('bp_code') == $buyer->bp_code ? 'selected' : '' }}>
                                            {{ $buyer->bp_code }} - {{ $buyer->customer_name ?? $buyer->business_name }}
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
                            <a href="{{ route('super-admin.repairs.index', ['tab' => $activeTab]) }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i> Reset</a>
                        </div>
                    </form>

                    <ul class="nav nav-tabs mb-3" id="repairTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'new' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'new', 'page' => null]) }}">
                                New <span class="badge bg-primary ms-1">{{ $counts['new'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'allocated' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'allocated', 'page' => null]) }}">
                                Allocated <span class="badge bg-info ms-1">{{ $counts['allocated'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'in_process' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'in_process', 'page' => null]) }}">
                                In Process <span class="badge bg-warning text-dark ms-1">{{ $counts['in_process'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'for_approval' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'for_approval', 'page' => null]) }}">
                                For Approval <span class="badge bg-secondary ms-1">{{ $counts['for_approval'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'completed' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'completed', 'page' => null]) }}">
                                Completed <span class="badge bg-success ms-1">{{ $counts['completed'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'rejected' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'rejected', 'page' => null]) }}">
                                Rejected <span class="badge bg-danger ms-1">{{ $counts['rejected'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'all' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'all', 'page' => null]) }}">
                                All <span class="badge bg-dark ms-1">{{ $counts['all'] }}</span>
                            </a>
                        </li>
                    </ul>
                    
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#bulkCompleteModal">
                            <i class="bi bi-check-circle"></i> Bulk Complete Selected
                        </button>
                    </div>

                    {{-- Main Form Wrapping Table Only --}}
                    <form id="bulkCompleteForm" action="{{ route('super-admin.repairs.bulk-complete') }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-striped table-sm align-middle">
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
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($repairs as $repair)
                                    @php
                                        $isEditable = $repair->created_at->gt(now()->subDays(60));
                                    @endphp
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="repair_ids[]" value="{{ $repair->id }}" class="repair-checkbox">
                                        </td>
                                        <td>{{ $repair->id }}</td>
                                        <td>{{ optional($repair->repair_date)->format('d M Y') ?? $repair->created_at->format('d M Y') }}</td>
                                        <td>{{ optional($repair->buyer)->bp_code ?? 'N/A' }} - {{ optional($repair->buyer)->business_name ?? optional($repair->buyer)->customer_name }}</td>
                                        <td>{{ $repair->product_name }}</td>
                                        <td>{{ $repair->weight }}</td>
                                        <td>{{ $repair->item_given_to }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ str_replace('_', ' ', $repair->status) }}</span>
                                        </td>
                                        <td>{{ optional($repair->craftsman)->craftman_code ?? '-' }}</td>
                                        <td>
                                            @if($repair->image_proof)
                                            <a href="{{ asset($repair->image_proof) }}" target="_blank">View</a>
                                            @else
                                            N/A
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('super-admin.repairs.show', $repair->id) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            @if($isEditable)
                                            <a href="{{ route('super-admin.repairs.edit', $repair->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @endif

                                            @if($repair->status == 'Pending')
                                            <button type="submit" formaction="{{ route('super-admin.repairs.accept', $repair->id) }}" class="btn btn-sm btn-success" title="Accept"><i class="bi bi-check-lg"></i> Accept</button>
                                            <button type="button" class="btn btn-sm btn-danger" title="Reject" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $repair->id }}">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                            @endif

                                            @if(in_array($repair->status, ['Accepted', 'Craftsman_Rejected']))
                                            <button type="button" class="btn btn-sm btn-primary" title="Allocate to Craftsman" data-bs-toggle="modal" data-bs-target="#allocateModal{{ $repair->id }}">
                                                <i class="bi bi-person-plus"></i> Allocate
                                            </button>
                                            @endif

                                            @if(!in_array($repair->status, ['Buyer_Accepted', 'Completed', 'Rejected_by_Admin', 'Buyer_Rejected', 'Pending', 'Accepted', 'Allocated']))
                                            <button type="button" class="btn btn-sm btn-success" title="Mark Complete" data-bs-toggle="modal" data-bs-target="#completeModal{{ $repair->id }}">
                                                <i class="bi bi-check-circle"></i> Complete
                                            </button>
                                            @endif

                                            <button type="submit" form="deleteForm{{$repair->id}}" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this repair order?');">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-4">No repairs found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Bulk Complete Modal Placed Safely Inside the Main Form --}}
                        <div class="modal fade" id="bulkCompleteModal" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Bulk Complete Selected Repairs</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body space-y-3">
                                        <div class="mb-3">
                                            <label class="form-label">Item Received Through</label>
                                            <select name="item_received_through" id="bulkReceivedThroughSelect" class="form-select" onchange="checkCustomInput('bulkReceivedThroughSelect', 'bulkReceivedThroughCustom')">
                                                <option value="">-- Select Source --</option>
                                                @php
                                                    $receivedThroughOptions = \App\Models\Repair::whereNotNull('item_received_through')->distinct()->pluck('item_received_through');
                                                @endphp
                                                @foreach($receivedThroughOptions as $opt)
                                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                                @endforeach
                                                <option value="__custom__">+ Add New...</option>
                                            </select>
                                            <input type="text" name="item_received_through_custom" id="bulkReceivedThroughCustom" placeholder="Enter new source..." class="form-control mt-2 d-none">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Delivered By Type</label>
                                            <select name="item_delivered_by_type" class="form-select">
                                                <option value="Self">Self</option>
                                                <option value="AJPL">AJPL</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Item Delivered By Name</label>
                                            <select name="item_delivered_by" id="bulkDeliveredBySelect" class="form-select" onchange="checkCustomInput('bulkDeliveredBySelect', 'bulkDeliveredByCustom')">
                                                <option value="">-- Select Person --</option>
                                                @php
                                                    $deliveredByOptions = \App\Models\Repair::whereNotNull('item_delivered_by')->distinct()->pluck('item_delivered_by');
                                                @endphp
                                                @foreach($deliveredByOptions as $opt)
                                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                                @endforeach
                                                <option value="__custom__">+ Add New...</option>
                                            </select>
                                            <input type="text" name="item_delivered_by_custom" id="bulkDeliveredByCustom" placeholder="Enter new deliverer..." class="form-control mt-2 d-none">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Item Delivered To</label>
                                            <input type="text" name="item_delivered_to" class="form-control" placeholder="Receiver/Buyer name...">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Mark selected repairs as completed?')">Confirm Bulk Complete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Individual Row Modals Rendered Separately Outside Main Form --}}
                    @foreach($repairs as $repair)
                        <form id="deleteForm{{$repair->id}}" action="{{ route('super-admin.repairs.destroy', $repair->id) }}" method="POST" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>

                        @if($repair->status == 'Pending')
                        <div class="modal fade" id="rejectModal{{ $repair->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('super-admin.repairs.reject', $repair->id) }}" method="POST">
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
                                    <form action="{{ route('super-admin.repairs.allocate', $repair->id) }}" method="POST">
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
                                                    @foreach($craftsmen as $c)
                                                    <option value="{{ $c->craftman_code }}">{{ $c->craftman_code }} - {{ $c->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Allocation Notes</label>
                                                <textarea name="allocation_notes" class="form-control" rows="3" placeholder="Add instructions..."></textarea>
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
                                    <form action="{{ route('super-admin.repairs.complete', $repair->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Complete Repair #{{ $repair->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body space-y-3">
                                            <div class="mb-3">
                                                <label class="form-label">Item Received Through</label>
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
                                                <label class="form-label">Delivered By Type</label>
                                                <select name="item_delivered_by_type" class="form-select">
                                                    <option value="Self" {{ $repair->item_delivered_by_type == 'Self' ? 'selected' : '' }}>Self</option>
                                                    <option value="AJPL" {{ $repair->item_delivered_by_type == 'AJPL' ? 'selected' : '' }}>AJPL</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Item Delivered By Name</label>
                                                <select name="item_delivered_by" id="deliveredBySelect{{ $repair->id }}" class="form-select" onchange="checkCustomInput('deliveredBySelect{{ $repair->id }}', 'deliveredByCustom{{ $repair->id }}')">
                                                    <option value="">-- Select Person --</option>
                                                    @php
                                                        $deliveredByOptions = \App\Models\Repair::whereNotNull('item_delivered_by')->distinct()->pluck('item_delivered_by');
                                                    @endphp
                                                    @foreach($deliveredByOptions as $opt)
                                                        <option value="{{ $opt }}" {{ $repair->item_delivered_by == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                    <option value="__custom__">+ Add New...</option>
                                                </select>
                                                <input type="text" name="item_delivered_by_custom" id="deliveredByCustom{{ $repair->id }}" placeholder="Enter new deliverer..." class="form-control mt-2 d-none">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Item Delivered To</label>
                                                <input type="text" name="item_delivered_to" value="{{ $repair->item_delivered_to }}" class="form-control" placeholder="Receiver/Buyer name...">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success">Mark as Completed</button>
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
        const selectAll = document.getElementById('selectAllRepairs');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.repair-checkbox');
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
            });
        }

        function initSearchableDropdown(containerId, displayId, menuId, searchInputId, listId, hiddenSelectId, placeholder) {
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
                        item.classList.remove('d-none');
                    } else {
                        item.classList.add('d-none');
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
            });

            document.addEventListener('click', function(e) {
                if (!container.contains(e.target)) {
                    menu.style.display = 'none';
                }
            });

            if (hiddenSelect.value) {
                const selectedItem = Array.from(getListItems()).find(i => i.dataset.value === hiddenSelect.value);
                if (selectedItem) {
                    display.textContent = selectedItem.textContent.trim();
                    selectedItem.classList.add('selected');
                }
            }
        }

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
</script>
@endsection