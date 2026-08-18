@extends('admin.layouts.app')

@section('content')
<style>
    /* Custom Floating Searchable Dropdown */
    .custom-dropdown-container {
        position: relative;
        width: 100%;
    }
    .custom-dropdown-btn {
        width: 100%;
        text-align: left;
        background: #ffffff;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 0.45rem 0.85rem;
        font-size: 0.95rem;
        color: #333333;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        user-select: none;
        height: 38px;
    }
    .custom-dropdown-btn:focus {
        outline: none;
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    .custom-dropdown-btn::after {
        content: "";
        display: inline-block;
        margin-left: 0.5em;
        vertical-align: 0.255em;
        border-top: 0.3em solid #6c757d;
        border-right: 0.3em solid transparent;
        border-bottom: 0;
        border-left: 0.3em solid transparent;
    }
    .custom-dropdown-menu {
        display: none !important;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 0.5rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        margin-top: 5px;
        padding: 8px;
    }
    .custom-dropdown-menu.show {
        display: block !important;
    }
    .custom-dropdown-search {
        width: 100%;
        border: 1px solid #ced4da;
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 0.9rem;
        outline: none;
        margin-bottom: 6px;
        color: #495057;
    }
    .custom-dropdown-search:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.25);
    }
    .custom-dropdown-list {
        max-height: 200px;
        overflow-y: auto;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .custom-dropdown-item {
        padding: 8px 12px;
        cursor: pointer;
        border-radius: 4px;
        font-size: 0.9rem;
        color: #2c3e50;
        transition: background-color 0.15s ease-in-out;
    }
    .custom-dropdown-item:hover {
        background-color: #f1f5f9;
        color: #0d6efd;
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Craftsman Staff</h2>
        @if(auth('admin')->user() && auth('admin')->user()->hasPermission('can_create_staff'))
            <a href="{{ route('admin.business-partner.craftsman-staff.create') }}" class="btn btn-primary">Add Staff</a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <strong>Filters</strong>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.business-partner.craftsman-staff') }}" class="row g-3">
                
                <!-- Craftsman Searchable Dropdown -->
                <div class="col-md-4">
                    <label class="form-label">Craftsman</label>
                    <div class="custom-dropdown-container">
                        @php
                            $selectedCraftsman = $craftsmen->firstWhere('id', request('craftsman_id'));
                            $craftsmanLabel = $selectedCraftsman ? ($selectedCraftsman->business_name ? $selectedCraftsman->business_name . ' - ' : '') . $selectedCraftsman->name . ' (' . $selectedCraftsman->craftman_code . ')' : 'All Craftsmen';
                        @endphp
                        <button type="button" class="custom-dropdown-btn">
                            <span class="btn-text">{{ $craftsmanLabel }}</span>
                        </button>
                        <input type="hidden" name="craftsman_id" value="{{ request('craftsman_id') }}">
                        
                        <div class="custom-dropdown-menu">
                            <input type="text" class="custom-dropdown-search" placeholder="Search for an item...">
                            <ul class="custom-dropdown-list">
                                <li class="custom-dropdown-item" data-value="">All Craftsmen</li>
                                @foreach($craftsmen as $craftsman)
                                    <li class="custom-dropdown-item" data-value="{{ $craftsman->id }}" data-search="{{ strtolower(($craftsman->business_name ?? '') . ' ' . $craftsman->name . ' ' . $craftsman->craftman_code) }}">
                                        {{ $craftsman->business_name ? $craftsman->business_name . ' - ' : '' }}{{ $craftsman->name }} ({{ $craftsman->craftman_code }})
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Staff Searchable Dropdown -->
                <div class="col-md-4">
                    <label class="form-label">Staff (Code / Name)</label>
                    <div class="custom-dropdown-container">
                        @php
                            $selectedStaff = $staffs->firstWhere('staff_code', request('staff_code'));
                            $staffLabel = $selectedStaff ? '[' . $selectedStaff->staff_code . '] ' . $selectedStaff->name : 'All Staffs';
                        @endphp
                        <button type="button" class="custom-dropdown-btn">
                            <span class="btn-text">{{ $staffLabel }}</span>
                        </button>
                        <input type="hidden" name="staff_code" value="{{ request('staff_code') }}">
                        
                        <div class="custom-dropdown-menu">
                            <input type="text" class="custom-dropdown-search" placeholder="Search for an item...">
                            <ul class="custom-dropdown-list">
                                <li class="custom-dropdown-item" data-value="">All Staffs</li>
                                @foreach($staffs as $item)
                                    <li class="custom-dropdown-item" data-value="{{ $item->staff_code }}" data-search="{{ strtolower($item->staff_code . ' ' . $item->name) }}">
                                        [{{ $item->staff_code }}] {{ $item->name }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Mobile</label>
                    <input type="text" name="mobile" class="form-control" value="{{ request('mobile') }}" placeholder="Search Mobile">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ request('email') }}" placeholder="Search Email">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="frozen" {{ request('status') == 'frozen' ? 'selected' : '' }}>Frozen</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('admin.business-partner.craftsman-staff') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Staff Table -->
    <div class="table-responsive bg-white p-3 rounded shadow-sm">
        <table class="table table-bordered table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Staff Code</th>
                    <th>Staff Name</th>
                    <th>Craftsman Code</th>
                    <th>Business Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staffs as $staff)
                <tr>
                    <td><strong>{{ $staff->staff_code }}</strong></td>
                    <td>{{ $staff->name }}</td>
                    <td>{{ $staff->craftsman->craftman_code ?? 'N/A' }}</td>
                    <td>{{ $staff->craftsman->business_name ?? ($staff->craftsman->name ?? 'N/A') }}</td>
                    <td>{{ $staff->mobile }}</td>
                    <td>{{ $staff->email }}</td>
                    <td>
                        @if($staff->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Frozen</span>
                        @endif
                    </td>
                    <td>
                        @if(auth('admin')->user() && auth('admin')->user()->hasPermission('can_create_staff'))
                            <a href="{{ route('admin.business-partner.craftsman-staff.edit', $staff->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.business-partner.craftsman-staff.destroy', $staff->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">No craftsman staff records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $staffs->links() }}
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.custom-dropdown-container').forEach(container => {
            const btn = container.querySelector('.custom-dropdown-btn');
            const menu = container.querySelector('.custom-dropdown-menu');
            const searchInput = container.querySelector('.custom-dropdown-search');
            const listItems = container.querySelectorAll('.custom-dropdown-item');
            const hiddenInput = container.querySelector('input[type="hidden"]');
            const btnText = container.querySelector('.btn-text');

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                document.querySelectorAll('.custom-dropdown-menu').forEach(m => {
                    if (m !== menu) m.classList.remove('show');
                });
                
                menu.classList.toggle('show');
                if (menu.classList.contains('show')) {
                    searchInput.value = '';
                    filterItems('');
                    setTimeout(() => searchInput.focus(), 50);
                }
            });

            menu.addEventListener('click', function (e) {
                e.stopPropagation();
            });

            searchInput.addEventListener('input', function (e) {
                filterItems(e.target.value.toLowerCase());
            });

            listItems.forEach(item => {
                item.addEventListener('click', function () {
                    hiddenInput.value = item.getAttribute('data-value');
                    btnText.textContent = item.textContent.trim();
                    menu.classList.remove('show');
                });
            });

            function filterItems(keyword) {
                listItems.forEach(item => {
                    const text = (item.getAttribute('data-search') || item.textContent).toLowerCase();
                    item.style.display = text.includes(keyword) ? '' : 'none';
                });
            }
        });

        document.addEventListener('click', function () {
            document.querySelectorAll('.custom-dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        });
    });
</script>
@endsection