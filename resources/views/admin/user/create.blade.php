@extends('admin.layouts.app')

@section('styles')
<style>
    .custom-dropdown-container {
        position: relative;
        width: 100%;
    }
    .custom-dropdown-display {
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background-color: #fff;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        min-height: 38px;
    }
    .dark .custom-dropdown-display {
        background-color: #374151;
        border-color: #4b5563;
        color: #f3f4f6;
    }
    .custom-dropdown-display::after {
        content: "";
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 5px solid #666;
    }
    .custom-dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        z-index: 1000;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-top: 2px;
    }
    .dark .custom-dropdown-menu {
        background-color: #1f2937;
        border-color: #374151;
    }
    .custom-dropdown-search {
        width: 100%;
        padding: 8px 12px;
        border: none;
        border-bottom: 1px solid #dee2e6;
        outline: none;
    }
    .dark .custom-dropdown-search {
        background-color: #374151;
        color: #f3f4f6;
        border-bottom-color: #4b5563;
    }
    .custom-dropdown-list {
        max-height: 200px;
        overflow-y: auto;
    }
    .custom-dropdown-item {
        padding: 8px 12px;
        cursor: pointer;
    }
    .custom-dropdown-item:hover {
        background-color: #f8f9fa;
    }
    .dark .custom-dropdown-item:hover {
        background-color: #374151;
    }
</style>
@endsection

@section('scripts')
<script>
    function initSearchableDropdown(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const display = container.querySelector('.custom-dropdown-display');
        const menu = container.querySelector('.custom-dropdown-menu');
        const searchInput = container.querySelector('.custom-dropdown-search');
        const list = container.querySelector('.custom-dropdown-list');
        const hiddenSelect = container.querySelector('select');

        function updateDisplay() {
            const selectedOption = hiddenSelect.options[hiddenSelect.selectedIndex];
            display.textContent = selectedOption && selectedOption.value !== "" ? selectedOption.textContent : 'Select Business Partner';
        }

        function filterOptions() {
            const filter = searchInput.value.toLowerCase();
            const items = list.querySelectorAll('.custom-dropdown-item');
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(filter) ? '' : 'none';
            });
        }

        function selectOption(value, text) {
            hiddenSelect.value = value;
            hiddenSelect.dispatchEvent(new Event('change'));
            updateDisplay();
            menu.style.display = 'none';
            searchInput.value = '';
            filterOptions();
        }

        function refreshList() {
            list.innerHTML = '';
            Array.from(hiddenSelect.options).forEach(option => {
                if (option.value === "") return;
                const item = document.createElement('div');
                item.className = 'custom-dropdown-item';
                item.textContent = option.textContent;
                item.dataset.value = option.value;
                item.addEventListener('click', () => selectOption(option.value, option.textContent));
                list.appendChild(item);
            });
            updateDisplay();
        }

        display.addEventListener('click', (e) => {
            e.stopPropagation();
            const isVisible = menu.style.display === 'block';
            document.querySelectorAll('.custom-dropdown-menu').forEach(m => m.style.display = 'none');
            menu.style.display = isVisible ? 'none' : 'block';
            if (!isVisible) searchInput.focus();
        });

        searchInput.addEventListener('input', filterOptions);

        document.addEventListener('click', () => {
            menu.style.display = 'none';
        });

        menu.addEventListener('click', (e) => e.stopPropagation());

        refreshList();

        return {
            refresh: refreshList,
            select: (value, text) => {
                if (!Array.from(hiddenSelect.options).some(o => o.value == value)) {
                    const opt = document.createElement('option');
                    opt.value = value; opt.textContent = text;
                    hiddenSelect.appendChild(opt);
                    refreshList();
                }
                selectOption(value, text);
            }
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        initSearchableDropdown('dropdown_container_bp_code');
    });
</script>
@endsection

@section('title', 'Add New User')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Add New User</h1>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>User Information</h4>
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

                    <form method="POST" action="{{ route('admin.user.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" value="{{ old('full_name') }}" required>
                            </div>
                            
                             <div class="col-md-6 mb-3">
                                 <label for="bp_code" class="form-label">Business Partner *</label>
                                 <div class="custom-dropdown-container" id="dropdown_container_bp_code">
                                     <div class="custom-dropdown-display" id="dropdown_display_bp_code">Select Business Partner</div>
                                     <div class="custom-dropdown-menu" id="dropdown_menu_bp_code" style="display: none;">
                                         <input type="text" class="custom-dropdown-search" placeholder="Search Business Partner...">
                                         <div class="custom-dropdown-list">
                                             @foreach($buyers as $buyer)
                                                 <div class="custom-dropdown-item" data-value="{{ $buyer->bp_code }}">{{ $buyer->bp_code }} - {{ $buyer->business_name }}</div>
                                             @endforeach
                                         </div>
                                     </div>
                                     <select class="form-control" id="bp_code" name="bp_code" required style="display: none;">
                                         <option value="">Select Business Partner</option>
                                         @foreach($buyers as $buyer)
                                             <option value="{{ $buyer->bp_code }}" {{ old('bp_code') == $buyer->bp_code ? 'selected' : '' }}>
                                                 {{ $buyer->bp_code }} - {{ $buyer->business_name }}
                                             </option>
                                         @endforeach
                                     </select>
                                 </div>
                             </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email_id" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email_id" name="email_id" value="{{ old('email_id') }}" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="mobile_no" class="form-label">Mobile Number *</label>
                                <input type="text" class="form-control" id="mobile_no" name="mobile_no" value="{{ old('mobile_no') }}" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="{{ old('city') }}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" value="{{ old('state') }}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country" value="{{ old('country') }}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="pincode" class="form-label">Pincode</label>
                                <input type="text" class="form-control" id="pincode" name="pincode" value="{{ old('pincode') }}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="aadhar_number" class="form-label">Aadhar Number</label>
                                <input type="text" class="form-control" id="aadhar_number" name="aadhar_number" value="{{ old('aadhar_number') }}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.user.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection