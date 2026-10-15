@extends('buyer.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h2 class="mb-0">My Repairs & Samples</h2>
            @if(isset($currentBuyer))
                <small class="text-muted"><strong>Buyer:</strong> {{ $currentBuyer->name }} | <strong>BP Code:</strong> {{ $currentBuyer->bp_code }}</small>
            @endif
        </div>
        <a href="{{ route('buyer.repairs.create') }}" class="btn btn-primary">Add New Record</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
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

    <!-- Buyer Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? 'active') === 'active' ? 'active fw-bold' : '' }}" href="{{ route('buyer.repairs.index', array_merge(request()->except('page'), ['tab' => 'active'])) }}">In Progress / Active</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? '') === 'approval' ? 'active fw-bold' : '' }}" href="{{ route('buyer.repairs.index', array_merge(request()->except('page'), ['tab' => 'approval'])) }}">Pending Acceptance</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? '') === 'completed' ? 'active fw-bold' : '' }}" href="{{ route('buyer.repairs.index', array_merge(request()->except('page'), ['tab' => 'completed'])) }}">Completed</a>
        </li>
    </ul>

    <!-- Filters Section -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('buyer.repairs.index') }}" id="buyerFilterForm">
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
                    <div class="col-lg-2 col-md-4">
                        <label class="filter-label">From Date</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                    </div>

                    <!-- To Date -->
                    <div class="col-lg-2 col-md-4">
                        <label class="filter-label">To Date</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                    </div>

                    <!-- Filter Actions -->
                    <div class="col-lg-2 col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('buyer.repairs.index', ['tab' => $tab]) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Order No</th>
                            <th>Type</th>
                            <th>Status / Tracking</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($repairs as $repair)
                            <tr>
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

                                    @if($repair->item_sent_by)
                                        <div class="mt-1"><small class="text-muted"><strong>Item Sent By:</strong> {{ $repair->item_sent_by }}</small></div>
                                    @endif

                                    @if($repair->item_accepted_by)
                                        <div class="mt-1"><small class="text-muted"><strong>Item Accepted By:</strong> {{ $repair->item_accepted_by }}</small></div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <!-- Final Step Action: Buyer Acceptance -->
                                    @if(!empty($repair->item_sent_by) && $repair->status !== 'completed')
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#acceptModal{{ $repair->id }}">
                                            Accept & Complete
                                        </button>
                                    @endif

                                    <!-- Editable & Deletable Only When Pending -->
                                    @if($repair->status === 'pending')
                                        <a href="{{ route('buyer.repairs.edit', array_merge(['repair' => $repair->id], request()->query())) }}" class="btn btn-sm btn-warning">Edit</a>
                                        
                                        <form action="{{ route('buyer.repairs.destroy', array_merge(['repair' => $repair->id], request()->query())) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            @foreach(request()->query() as $key => $val)
                                                @if(!in_array($key, ['_token', '_method']))
                                                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                                                @endif
                                            @endforeach
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this record?');">Delete</button>
                                        </form>
                                    @endif

                                    @if($repair->status !== 'pending' && (empty($repair->item_sent_by) || $repair->status === 'completed'))
                                        <span class="text-muted small">No Action Required</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $repairs->withQueryString()->links() }}
        </div>
    </div>

    <!-- Buyer Acceptance Modals -->
    @foreach($repairs as $repair)
        @if(!empty($repair->item_sent_by) && $repair->status !== 'completed')
        <div class="modal fade" id="acceptModal{{ $repair->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('buyer.repairs.accept-completion', $repair->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Accept Order #{{ $repair->order_number }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-start">
                            <p class="text-muted small mb-3">Item Sent By: <strong>{{ $repair->item_sent_by }}</strong></p>
                            
                            <div class="mb-3">
                                <label class="form-label">Item Accepted By <span class="text-danger">*</span></label>
                                <input type="text" name="item_accepted_by" class="form-control" placeholder="Enter your name..." required>
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
                            <button type="submit" class="btn btn-success">Confirm & Complete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @endforeach
</div>

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
    min-width: 200px;
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
        initSearchableDropdown('type_filter_container', 'type_filter_display', 'type_filter_menu', 'type_filter_search', 'type_filter_list', 'type_filter_select', 'All Types');
    });
</script>
@endsection