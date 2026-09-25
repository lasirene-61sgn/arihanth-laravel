@extends('admin.layouts.app')

@section('title', 'Edit Craftman')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Craftman</h1>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.business-partner.craftman.update', $craftman) }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')

                <ul class="nav nav-tabs" id="craftmanTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">Basic Info</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="address-tab" data-bs-toggle="tab" data-bs-target="#address" type="button" role="tab">Address</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="kyc-tab" data-bs-toggle="tab" data-bs-target="#kyc" type="button" role="tab">KYC Details</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank" type="button" role="tab">Bank Details</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="worker-tab" data-bs-toggle="tab" data-bs-target="#worker" type="button" role="tab">Worker Details</button>
                    </li>
                </ul>

                <div class="tab-content" id="craftmanTabsContent">
                    <!-- Basic Information Tab -->
                    <div class="tab-pane fade show active" id="basic" role="tabpanel">
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="craftman_code" class="form-label">Craftman Code *</label>
                                    <input type="text" class="form-control" id="craftman_code" name="craftman_code" value="{{ old('craftman_code', $craftman->craftman_code) }}" required>
                                </div>
                            </div>
                            <!-- <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dear" class="form-label">Dear Code *</label>
                                    <input type="text" class="form-control" id="dear" name="dear" value="{{ old('dear', $craftman->dear) }}" required>
                                </div>
                            </div> -->
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="business_name" class="form-label">Business Name *</label>
                                    <input type="text" class="form-control" id="business_name" name="business_name" value="{{ old('business_name', $craftman->business_name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Contact Person Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $craftman->name) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mobile" class="form-label">Mobile Number *</label>
                                    <input type="text" class="form-control" id="mobile" name="mobile" value="{{ old('mobile', $craftman->mobile) }}" required minlength="10" maxlength="10">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="landline" class="form-label">Landline/Centrex Number</label>
                                    <input type="text" class="form-control" id="landline" name="landline" value="{{ old('landline', $craftman->landline) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $craftman->email) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="business_email" class="form-label">Business Email</label>
                                    <input type="email" class="form-control" id="business_email" name="business_email" value="{{ old('business_email', $craftman->business_email) }}">
                                </div>
                            </div>
                        </div>

                        

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="refered_by" class="form-label">Referred By</label>
                                    <input type="text" class="form-control" id="refered_by" name="refered_by" value="{{ old('refered_by', $craftman->refered_by) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="more" class="form-label">Additional Information</label>
                                    <textarea class="form-control" id="more" name="more" rows="3">{{ old('more', $craftman->more) }}</textarea>
                                </div>
                            </div>
                        </div>
                       
                    
                    <!-- Worker Details Tab -->
                    <div class="tab-pane fade" id="worker" role="tabpanel">
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>Worker Details <button type="button" class="btn btn-sm btn-success" onclick="addWorkerField()">Add New</button></h5>
                                <div id="worker-fields">
                                    @forelse($craftman->workers as $index => $worker)
                                        <div class="worker-entry mb-3">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Worker Name</label>
                                                        <input type="text" class="form-control" name="worker_name[]" value="{{ $worker->worker_name }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Worker Number</label>
                                                        <input type="text" class="form-control" name="worker_number[]" value="{{ $worker->worker_number }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Worker Image</label>
                                                        <input type="file" class="form-control" name="worker_image[]">
                                                        @if($worker->worker_image)
                                                            <small class="text-muted d-block mt-1">
                                                                Current: <a href="{{ asset('storage/' . $worker->worker_image) }}" target="_blank">View Image</a>
                                                                <br>
                                                                <a href="{{ asset('storage/' . $worker->worker_image) }}" download class="btn btn-xs btn-outline-success py-0 px-1" style="font-size: 0.75rem;">Download</a>
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)">Remove</button>
                                                </div>
                                            </div>
                                            <hr>
                                        </div>
                                    @empty
                                        <div class="worker-entry mb-3">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Worker Name</label>
                                                        <input type="text" class="form-control" name="worker_name[]">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Worker Number</label>
                                                        <input type="text" class="form-control" name="worker_number[]">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Worker Image</label>
                                                        <input type="file" class="form-control" name="worker_image[]">
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)">Remove</button>
                                                </div>
                                            </div>
                                            <hr>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                                                                <!-- Permissions Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border border-info">
                                    <div class="card-header bg-light">
                                        <h4 class="mb-0">{{ __('messages.permissions') ?? 'Permissions' }}</h4>
                                        <small class="text-muted">Configure access for Web Panel and Mobile App</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12"><h6 class="mb-2 fw-bold text-secondary">Web Panel Permissions</h6></div>
                                            @foreach(\App\Models\Craftman::getWebPermissions() as $permission)
                                                <div class="col-md-4 mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission }}" id="permission_{{ $permission }}" {{ in_array($permission, old('permissions', $craftman->getPermissionsArray())) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="permission_{{ $permission }}">
                                                            {{ ucfirst(str_replace('_', ' ', $permission)) }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach

                                            <div class="col-12 mt-3"><h6 class="mb-2 fw-bold text-secondary">API Permissions (Mobile App)</h6></div>
                                            @foreach(\App\Models\Craftman::getGroupedApiPermissions() as $tabName => $permissions)
                                                <div class="col-12 mt-2"><strong class="text-primary">{{ $tabName }}</strong></div>
                                                @foreach($permissions as $permission)
                                                    <div class="col-md-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission }}" id="permission_{{ $permission }}" {{ in_array($permission, old('permissions', $craftman->getPermissionsArray())) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="permission_{{ $permission }}">
                                                                {{ ucfirst(str_replace('_', ' ', $permission)) }} <small class="text-muted">(API)</small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('admin.business-partner.craftman') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success">Update Craftman</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Keep track of the last active tab
    document.addEventListener('DOMContentLoaded', function() {
        // Restore the last active tab from localStorage
        const lastTab = localStorage.getItem('craftmanFormActiveTab');
        if (lastTab) {
            const tabTrigger = new bootstrap.Tab(document.querySelector(lastTab));
            tabTrigger.show();
        }

        // Save the active tab to localStorage when it changes
        const tabs = document.querySelectorAll('#craftmanTabs button[data-bs-toggle="tab"]');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (event) {
                localStorage.setItem('craftmanFormActiveTab', event.target.getAttribute('data-bs-target'));
            });
        });
    });
    
    // Add Aadhar field function
    function addAadharField() {
        const container = document.getElementById('aadhar-fields');
        const newIndex = container.children.length;
        
        const newField = document.createElement('div');
        newField.className = 'aadhar-entry mb-3';
        newField.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Aadhar Name *</label>
                        <input type="text" class="form-control" name="aadhar_name[]" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Aadhar Number *</label>
                        <input type="text" class="form-control" name="aadhar_number[]" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Aadhar Image</label>
                        <input type="file" class="form-control" name="aadhar_image[]">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)">Remove</button>
                </div>
            </div>
            <hr>
        `;
        container.appendChild(newField);
    }
    
    // Add PAN field function
    function addPanField() {
        const container = document.getElementById('pan-fields');
        const newIndex = container.children.length;
        
        const newField = document.createElement('div');
        newField.className = 'pan-entry mb-3';
        newField.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">PAN Number *</label>
                        <input type="text" class="form-control" name="pan_number[]" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">PAN Image</label>
                        <input type="file" class="form-control" name="pan_image[]">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)">Remove</button>
                </div>
            </div>
            <hr>
        `;
        container.appendChild(newField);
    }
    
    // Add Bank field function
    function addBankField() {
        const container = document.getElementById('bank-fields');
        const newIndex = container.children.length;
        
        const newField = document.createElement('div');
        newField.className = 'bank-entry mb-3';
        newField.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Bank Name</label>
                        <input type="text" class="form-control" name="bank_name[]">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Account Holder Name</label>
                        <input type="text" class="form-control" name="account_holder_name[]">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Account Number</label>
                        <input type="text" class="form-control" name="account_number[]">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">IFSC Code</label>
                        <input type="text" class="form-control" name="ifsc_code[]">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Branch</label>
                        <input type="text" class="form-control" name="branch[]">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Bank City</label>
                        <input type="text" class="form-control" name="bank_city[]">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Bank State</label>
                        <input type="text" class="form-control" name="bank_state[]">
                    </div>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)">Remove</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Passbook Image</label>
                        <input type="file" class="form-control" name="passbook_image[]">
                    </div>
                </div>
            </div>
            <hr>
        `;
        container.appendChild(newField);

    }
    
    // Add Worker field function
    function addWorkerField() {
        const container = document.getElementById('worker-fields');
        const fieldIndex = container.children.length;
        
        const newField = document.createElement('div');
        newField.className = 'worker-entry mb-3';
        newField.innerHTML = `
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Worker Name</label>
                        <input type="text" class="form-control" name="worker_name[]">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Worker Number</label>
                        <input type="text" class="form-control" name="worker_number[]">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Worker Image</label>
                        <input type="file" class="form-control" name="worker_image[]">
                    </div>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)">Remove</button>
                </div>
            </div>
            <hr>
        `;
        
        container.appendChild(newField);
    }
    
    // Remove field function
    function removeField(button) {
        const entry = button.closest('.aadhar-entry, .pan-entry, .bank-entry, .worker-entry');
        if (entry) {
            entry.remove();
        }
    }
</script>
<script src="{{ asset('js/fetch-address.js') }}"></script>
@endsection