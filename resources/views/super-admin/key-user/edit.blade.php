@extends('super-admin.layouts.app')

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

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Edit Key User</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('super-admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('super-admin.key-user.index') }}">Key Users</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit Key User Details</h4>
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

                    <form action="{{ route('super-admin.key-user.update', $keyUser) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="user_code" class="form-label">User Code</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="user_code" 
                                           value="{{ $keyUser->user_code }}" 
                                           disabled>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bp_code" class="form-label">Business Partner <span class="text-danger">*</span></label>
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
                                        <select class="form-control @error('bp_code') is-invalid @enderror" 
                                                id="bp_code" 
                                                name="bp_code" 
                                                required style="display: none;">
                                            <option value="">Select Business Partner</option>
                                            @foreach($buyers as $buyer)
                                                <option value="{{ $buyer->bp_code }}" {{ old('bp_code', $keyUser->bp_code) == $buyer->bp_code ? 'selected' : '' }}>
                                                    {{ $buyer->bp_code }} - {{ $buyer->business_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('bp_code')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('full_name') is-invalid @enderror" 
                                           id="full_name" 
                                           name="full_name" 
                                           value="{{ old('full_name', $keyUser->full_name) }}" 
                                           required>
                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_id" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" 
                                           class="form-control @error('email_id') is-invalid @enderror" 
                                           id="email_id" 
                                           name="email_id" 
                                           value="{{ old('email_id', $keyUser->email_id) }}" 
                                           required>
                                    @error('email_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mobile_no" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('mobile_no') is-invalid @enderror" 
                                           id="mobile_no" 
                                           name="mobile_no" 
                                           value="{{ old('mobile_no', $keyUser->mobile_no) }}" 
                                           required>
                                    @error('mobile_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" 
                                            name="status" 
                                            required>
                                        <option value="1" {{ old('status', $keyUser->status) == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('status', $keyUser->status) == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dob" class="form-label">Date of Birth</label>
                                    <input type="date" 
                                           class="form-control @error('dob') is-invalid @enderror" 
                                           id="dob" 
                                           name="dob" 
                                           value="{{ old('dob', $keyUser->dob ? $keyUser->dob->format('Y-m-d') : '') }}">
                                    @error('dob')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="profile_picture" class="form-label">Profile Picture</label>
                                    <input type="file" 
                                           class="form-control @error('profile_picture') is-invalid @enderror" 
                                           id="profile_picture" 
                                           name="profile_picture">
                                    @error('profile_picture')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                    @if($keyUser->profile_picture)
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/' . $keyUser->profile_picture) }}" 
                                                 alt="Profile Picture" 
                                                 class="img-thumbnail" 
                                                 width="100">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" 
                                           class="form-control @error('city') is-invalid @enderror" 
                                           id="city" 
                                           name="city" 
                                           value="{{ old('city', $keyUser->city) }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" 
                                           class="form-control @error('state') is-invalid @enderror" 
                                           id="state" 
                                           name="state" 
                                           value="{{ old('state', $keyUser->state) }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" 
                                           class="form-control @error('country') is-invalid @enderror" 
                                           id="country" 
                                           name="country" 
                                           value="{{ old('country', $keyUser->country) }}">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pincode" class="form-label">Pincode</label>
                                    <input type="text" 
                                           class="form-control @error('pincode') is-invalid @enderror" 
                                           id="pincode" 
                                           name="pincode" 
                                           value="{{ old('pincode', $keyUser->pincode) }}">
                                    @error('pincode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="aadhar_number" class="form-label">Aadhar Number</label>
                                    <input type="text" 
                                           class="form-control @error('aadhar_number') is-invalid @enderror" 
                                           id="aadhar_number" 
                                           name="aadhar_number" 
                                           value="{{ old('aadhar_number', $keyUser->aadhar_number) }}">
                                    @error('aadhar_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="aadhar_photo" class="form-label">Aadhar Photo</label>
                                    <input type="file" 
                                           class="form-control @error('aadhar_photo') is-invalid @enderror" 
                                           id="aadhar_photo" 
                                           name="aadhar_photo">
                                    @error('aadhar_photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                    @if($keyUser->aadhar_photo)
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/' . $keyUser->aadhar_photo) }}" 
                                                 alt="Aadhar Photo" 
                                                 class="img-thumbnail" 
                                                 width="100">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Permissions Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border border-info">
                                    <div class="card-header bg-light">
                                        <h4 class="mb-0">Key User Permissions</h4>
                                        <small class="text-muted">Select which modules this key user can access</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach(App\Models\KeyUser::getAllPermissions() as $permission)
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" 
                                                               type="checkbox" 
                                                               name="permissions[]" 
                                                               value="{{ $permission }}" 
                                                               id="permission_{{ $permission }}"
                                                               {{ in_array($permission, old('permissions', $keyUser->getPermissionsArray())) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="permission_{{ $permission }}">
                                                            {{ ucfirst(str_replace('_', ' ', $permission)) }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('super-admin.key-user.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Key User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection