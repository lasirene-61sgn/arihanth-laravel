@extends('super-admin.layouts.app')

@section('title', __('messages.edit_craftman'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">{{ __('messages.edit_craftman') }}</h1>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('messages.craftman_information') }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('super-admin.business-partner.craftman.update', $craftman) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <ul class="nav nav-tabs" id="craftmanTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">{{ __('messages.basic_info') }}</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="address-tab" data-bs-toggle="tab" data-bs-target="#address" type="button" role="tab">{{ __('messages.address') }}</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="kyc-tab" data-bs-toggle="tab" data-bs-target="#kyc" type="button" role="tab">{{ __('messages.kyc_details') }}</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank" type="button" role="tab">{{ __('messages.bank_details') }}</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="worker-tab" data-bs-toggle="tab" data-bs-target="#worker" type="button" role="tab">{{ __('messages.worker_details') }}</button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="craftmanTabsContent">
                            <!-- Basic Information Tab -->
                            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="craftman_code" class="form-label">{{ __('messages.craftman_code') }} *</label>
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
                                            <label for="business_name" class="form-label">{{ __('messages.business_name') }} *</label>
                                            <input type="text" class="form-control" id="business_name" name="business_name" value="{{ old('business_name', $craftman->business_name) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{ __('messages.contact_person_name') }} *</label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $craftman->name) }}" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mobile" class="form-label">{{ __('messages.mobile_number') }} *</label>
                                            <input type="text" class="form-control" id="mobile" name="mobile" value="{{ old('mobile', $craftman->mobile) }}" required minlength="10" maxlength="10">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="landline" class="form-label">{{ __('messages.landline_centrex_number') }}</label>
                                            <input type="text" class="form-control" id="landline" name="landline" value="{{ old('landline', $craftman->landline) }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">{{ __('messages.email_address') }} *</label>
                                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $craftman->email) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="business_email" class="form-label">{{ __('messages.business_email') }}</label>
                                            <input type="email" class="form-control" id="business_email" name="business_email" value="{{ old('business_email', $craftman->business_email) }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password" class="form-label">{{ __('messages.password') }}</label>
                                            <input type="password" class="form-control" id="password" name="password">
                                            <small class="text-muted">{{ __('messages.leave_blank_password') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password_confirmation" class="form-label">{{ __('messages.confirm_password') }}</label>
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                        </div>
                                    </div>
                                </div>

                                

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="refered_by" class="form-label">{{ __('messages.referred_by') }}</label>
                                            <input type="text" class="form-control" id="refered_by" name="refered_by" value="{{ old('refered_by', $craftman->refered_by) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="more" class="form-label">{{ __('messages.additional_information') }}</label>
                                            <textarea class="form-control" id="more" name="more" rows="3">{{ old('more', $craftman->more) }}</textarea>
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
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">{{ __('messages.update_craftman') }}</button>
                            <a href="{{ route('super-admin.business-partner.craftman') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
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
    
    function addAadharField() {
        const container = document.getElementById('aadhar-fields');
        const fieldIndex = container.children.length;
        
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
                        <label class="form-label"><?= __('messages.aadhar_image') ?></label>
                        <input type="file" class="form-control" name="aadhar_image[]">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)"><?= __('messages.remove') ?></button>
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
                        <label class="form-label"><?= __('messages.worker_name') ?></label>
                        <input type="text" class="form-control" name="worker_name[]">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label"><?= __('messages.worker_number') ?></label>
                        <input type="text" class="form-control" name="worker_number[]">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label"><?= __('messages.worker_image') ?></label>
                        <input type="file" class="form-control" name="worker_image[]">
                    </div>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)"><?= __('messages.remove') ?></button>
                </div>
            </div>
            <hr>
        `;
        
        container.appendChild(newField);
    }

    function addPanField() {
        const container = document.getElementById('pan-fields');
        const fieldIndex = container.children.length;
        
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
                        <label class="form-label"><?= __('messages.pan_image') ?></label>
                        <input type="file" class="form-control" name="pan_image[]">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)"><?= __('messages.remove') ?></button>
                </div>
            </div>
            <hr>
        `;
        
        container.appendChild(newField);
    }
    
    function addBankField() {
        const container = document.getElementById('bank-fields');
        const fieldIndex = container.children.length;
        
        const newField = document.createElement('div');
        newField.className = 'bank-entry mb-3';
        newField.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label"><?= __('messages.bank_name') ?></label>
                        <input type="text" class="form-control" name="bank_name[]">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label"><?= __('messages.account_holder_name') ?></label>
                        <input type="text" class="form-control" name="account_holder_name[]">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label"><?= __('messages.account_number') ?></label>
                        <input type="text" class="form-control" name="account_number[]">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label"><?= __('messages.ifsc_code') ?></label>
                        <input type="text" class="form-control" name="ifsc_code[]">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label"><?= __('messages.branch') ?></label>
                        <input type="text" class="form-control" name="branch[]">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label"><?= __('messages.bank_city') ?></label>
                        <input type="text" class="form-control" name="bank_city[]">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label"><?= __('messages.bank_state') ?></label>
                        <input type="text" class="form-control" name="bank_state[]">
                    </div>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)"><?= __('messages.remove') ?></button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label"><?= __('messages.passbook_cheque_image') ?></label>
                        <input type="file" class="form-control" name="passbook_image[]">
                    </div>
                </div>
            </div>
            <hr>
        `;
        
        container.appendChild(newField);
    }
    
    function removeField(button) {
        const fieldContainer = button.closest('.aadhar-entry, .pan-entry, .bank-entry, .worker-entry');
        if (fieldContainer) {
            fieldContainer.remove();
        }
    }
</script>
<script src="{{ asset('js/fetch-address.js') }}"></script>
@endsection