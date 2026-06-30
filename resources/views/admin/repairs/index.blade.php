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
                            <a class="nav-link {{ $activeTab == 'all' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'all']) }}">
                                All <span class="badge bg-secondary ms-1">{{ $counts['all'] }}</span>
                            </a>
                        </li>
                    </ul>
                    
                    @if(($activeTab == 'new' || $activeTab == 'in_process' || $activeTab == 'all') && $repairs->count() > 0)
                        <div class="mb-3">
                            <form id="bulkCompleteForm" action="{{ route('admin.repairs.bulk-complete') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mark selected repairs as completed?')">
                                    <i class="bi bi-check-circle"></i> Bulk Complete Selected
                                </button>
                            </form>
                        </div>
                    @endif

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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($repairs as $repair)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="repair_ids[]" value="{{ $repair->id }}" class="repair-checkbox" form="bulkCompleteForm">
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
                                        <td>{{ $repair->craftsman ? $repair->craftsman->craftman_code : '-' }}</td>
                                        <td>
                                            @if($repair->image_proof)
                                                <a href="{{ asset($repair->image_proof) }}" target="_blank">View</a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            {{-- View --}}
                                            <a href="{{ route('admin.repairs.show', $repair->id) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            {{-- Edit --}}
                                            @if(in_array($repair->status, ['Pending', 'Accepted']))
                                                <a href="{{ route('admin.repairs.edit', $repair->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif

                                            {{-- Accept --}}
                                            @if($repair->status == 'Pending')
                                                <form action="{{ route('admin.repairs.accept', $repair->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Accept"><i class="bi bi-check-lg"></i></button>
                                                </form>
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
                                            @if($repair->status == 'Craftsman_Completed')
                                                <form action="{{ route('admin.repairs.complete', $repair->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Mark Complete"><i class="bi bi-check-circle"></i></button>
                                                </form>
                                            @endif

                                            {{-- Delete --}}
                                            <form action="{{ route('admin.repairs.destroy', $repair->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this repair order?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    {{-- Reject Modal --}}
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

                                    {{-- Allocate Modal --}}
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
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No repairs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
</script>
@endsection
