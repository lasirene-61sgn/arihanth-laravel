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
            <h4 class="mb-0">Add Craftsman Staff</h4>
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

            <form action="{{ route('super-admin.business-partner.craftsman-staff.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row">
                    <!-- Craftsman Searchable Dropdown -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Craftsman <span class="text-danger">*</span></label>
                        <div class="custom-dropdown-container">
                            @php
                                $selectedCraftsman = $craftsmen->firstWhere('id', old('craftsman_id'));
                                $btnLabel = $selectedCraftsman ? ($selectedCraftsman->business_name ? $selectedCraftsman->business_name . ' - ' : '') . $selectedCraftsman->name . ' (' . $selectedCraftsman->craftman_code . ')' : '-- Select Craftsman --';
                            @endphp
                            <button type="button" class="custom-dropdown-btn">
                                <span class="btn-text">{{ $btnLabel }}</span>
                            </button>
                            <input type="hidden" name="craftsman_id" value="{{ old('craftsman_id') }}" required>
                            
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
                        <input type="text" name="staff_code" class="form-control" value="{{ old('staff_code') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Staff Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mobile <span class="text-danger">*</span></label>
                        <input type="text" name="mobile" class="form-control" value="{{ old('mobile') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Aadhar Number</label>
                        <input type="text" name="aadhar_number" class="form-control" value="{{ old('aadhar_number') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Profile Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Aadhar Image</label>
                        <input type="file" name="aadhar_image" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Save Staff</button>
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