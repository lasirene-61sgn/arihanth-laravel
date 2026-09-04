@extends('super-admin.layouts.app')

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
    <div class="card">
        <div class="card-header bg-light">
            <h4 class="mb-0">Edit Craftsman Staff: {{ $staff->name }}</h4>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('super-admin.business-partner.craftsman-staff.update', $staff->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Craftsman Searchable Dropdown -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Craftsman <span class="text-danger">*</span></label>
                        <div class="custom-dropdown-container">
                            @php
                                $selectedCraftsman = $craftsmen->firstWhere('id', old('craftsman_id', $staff->craftsman_id));
                                $btnLabel = $selectedCraftsman ? ($selectedCraftsman->business_name ? $selectedCraftsman->business_name . ' - ' : '') . $selectedCraftsman->name . ' (' . $selectedCraftsman->craftman_code . ')' : '-- Select Craftsman --';
                            @endphp
                            <button type="button" class="custom-dropdown-btn">
                                <span class="btn-text">{{ $btnLabel }}</span>
                            </button>
                            <input type="hidden" name="craftsman_id" value="{{ old('craftsman_id', $staff->craftsman_id) }}" required>
                            
                            <div class="custom-dropdown-menu">
                                <input type="text" class="custom-dropdown-search" placeholder="Search for an item...">
                                <ul class="custom-dropdown-list">
                                    @foreach($craftsmen as $craftsman)
                                        <li class="custom-dropdown-item" data-value="{{ $craftsman->id }}" data-search="{{ strtolower(($craftsman->business_name ?? '') . ' ' . $craftsman->name . ' ' . $craftsman->craftman_code) }}">
                                            {{ $craftsman->business_name ? $craftsman->business_name . ' - ' : '' }}{{ $craftsman->name }} ({{ $craftsman->craftman_code }})
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Staff Code <span class="text-danger">*</span></label>
                        <input type="text" name="staff_code" class="form-control" value="{{ old('staff_code', $staff->staff_code) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Staff Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $staff->name) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mobile <span class="text-danger">*</span></label>
                        <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $staff->mobile) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $staff->email) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Aadhar Number</label>
                        <input type="text" name="aadhar_number" class="form-control" value="{{ old('aadhar_number', $staff->aadhar_number) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password <small class="text-muted">(Leave blank to keep existing password)</small></label>
                        <input type="password" name="password" class="form-control" placeholder="New password">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Profile Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        @if($staff->image)
                            <div class="mt-2">
                                <small class="text-muted d-block">Current Profile Image:</small>
                                <img src="{{ asset('storage/' . $staff->image) }}" alt="Profile Image" width="70" class="rounded border">
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Aadhar Image</label>
                        <input type="file" name="aadhar_image" class="form-control" accept="image/*">
                        @if($staff->aadhar_image)
                            <div class="mt-2">
                                <small class="text-muted d-block">Current Aadhar Image:</small>
                                <img src="{{ asset('storage/' . $staff->aadhar_image) }}" alt="Aadhar Image" width="70" class="rounded border">
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Permissions Section -->
                @php
                    $currentPermissions = old('permissions', is_array($staff->permissions) ? $staff->permissions : json_decode($staff->permissions ?? '[]', true) ?? []);
                @endphp
                <div class="mt-4 mb-4">
                    <h5 class="border-bottom pb-2 mb-3 text-dark">Permissions</h5>
                    <div class="row g-3">
                        <!-- Work Orders -->
                        <div class="col-md-6">
                            <div class="card bg-light border-0 shadow-none h-100">
                                <div class="card-body py-2 px-3">
                                    <h6 class="fw-bold text-secondary mb-2">Work Orders</h6>
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="wo_view" id="perm_wo_view" {{ in_array('wo_view', $currentPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_wo_view">View</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="wo_accept" id="perm_wo_accept" {{ in_array('wo_accept', $currentPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_wo_accept">Accept</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="wo_reject" id="perm_wo_reject" {{ in_array('wo_reject', $currentPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_wo_reject">Reject</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Purchase Orders -->
                        <div class="col-md-6">
                            <div class="card bg-light border-0 shadow-none h-100">
                                <div class="card-body py-2 px-3">
                                    <h6 class="fw-bold text-secondary mb-2">Purchase Orders</h6>
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="po_view" id="perm_po_view" {{ in_array('po_view', $currentPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_po_view">View</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="po_accept" id="perm_po_accept" {{ in_array('po_accept', $currentPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_po_accept">Accept</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="po_reject" id="perm_po_reject" {{ in_array('po_reject', $currentPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_po_reject">Reject</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Repairs -->
                        <div class="col-md-6">
                            <div class="card bg-light border-0 shadow-none h-100">
                                <div class="card-body py-2 px-3">
                                    <h6 class="fw-bold text-secondary mb-2">Repairs</h6>
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="repair_view" id="perm_repair_view" {{ in_array('repair_view', $currentPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_repair_view">View</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="repair_accept" id="perm_repair_accept" {{ in_array('repair_accept', $currentPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_repair_accept">Accept</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="repair_reject" id="perm_repair_reject" {{ in_array('repair_reject', $currentPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_repair_reject">Reject</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Products -->
                        <div class="col-md-6">
                            <div class="card bg-light border-0 shadow-none h-100">
                                <div class="card-body py-2 px-3">
                                    <h6 class="fw-bold text-secondary mb-2">Products</h6>
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="product_view" id="perm_product_view" {{ in_array('product_view', $currentPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_product_view">View</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="product_create" id="perm_product_create" {{ in_array('product_create', $currentPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_product_create">Create</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="product_edit" id="perm_product_edit" {{ in_array('product_edit', $currentPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_product_edit">Edit</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Design & Catalogue -->
                        <div class="col-md-6">
                            <div class="card bg-light border-0 shadow-none h-100">
                                <div class="card-body py-2 px-3">
                                    <h6 class="fw-bold text-secondary mb-2">Design & Catalogue</h6>
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="design_view" id="perm_design_view" {{ in_array('design_view', $currentPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_design_view">Design (View)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="catalogue_view" id="perm_catalogue_view" {{ in_array('catalogue_view', $currentPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_catalogue_view">Catalogue (View)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Update Staff</button>
                    <a href="{{ route('super-admin.business-partner.craftsman-staff') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
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