@extends('super-admin.layouts.app')

@section('title', __('messages.add_new_buyer'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">{{ __('messages.add_new_buyer') }}</h1>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('messages.buyer_information') }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('super-admin.business-partner.buyer.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <ul class="nav nav-tabs" id="buyerTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">{{ __('messages.basic_info') }}</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="address-tab" data-bs-toggle="tab" data-bs-target="#address" type="button" role="tab">{{ __('messages.address_tab') }}</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="kyc-tab" data-bs-toggle="tab" data-bs-target="#kyc" type="button" role="tab">{{ __('messages.kyc_details') }}</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank" type="button" role="tab">{{ __('messages.bank_details') }}</button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="buyerTabsContent">
                            <!-- Basic Information Tab -->
                            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                <div class="row mt-3">
                                    <!-- <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="dear" class="form-label">Dear Code *</label>
                                            <input type="text" class="form-control" id="dear" name="dear" value="{{ old('dear') }}" required>
                                        </div>
                                    </div> -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="business_name" class="form-label">{{ __('messages.business_name_required') }}</label>
                                            <input type="text" class="form-control" id="business_name" name="business_name" value="{{ old('business_name') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{ __('messages.contact_person_name_req') }}</label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mobile" class="form-label">{{ __('messages.mobile_number_required') }}</label>
                                            <input type="text" class="form-control" id="mobile" name="mobile" value="{{ old('mobile') }}"
                                            maxlength="10" 
           minlength="10" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="landline" class="form-label">{{ __('messages.landline_centrex') }}</label>
                                            <input type="text" class="form-control" id="landline" name="landline" value="{{ old('landline') }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">{{ __('messages.email_address_required') }}</label>
                                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="business_email" class="form-label">{{ __('messages.business_email') }}</label>
                                            <input type="email" class="form-control" id="business_email" name="business_email" value="{{ old('business_email') }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="refered_by" class="form-label">{{ __('messages.referred_by') }}</label>
                                            <input type="text" class="form-control" id="refered_by" name="refered_by" value="{{ old('refered_by') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password" class="form-label">{{ __('messages.password') }}</label>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="{{ __('messages.leave_empty_autogenerate') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password_confirmation" class="form-label">{{ __('messages.confirm_password') }}</label>
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="{{ __('messages.confirm_password_hint') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="more" class="form-label">{{ __('messages.additional_information') }}</label>
                                            <textarea class="form-control" id="more" name="more" rows="3">{{ old('more') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Address Information Tab -->
                            <div class="tab-pane fade" id="address" role="tabpanel">
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="door_no" class="form-label">{{ __('messages.door_number') }}</label>
                                            <input type="text" class="form-control" id="door_no" name="door_no" value="{{ old('door_no') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="shop_no" class="form-label">{{ __('messages.shop_number') }}</label>
                                            <input type="text" class="form-control" id="shop_no" name="shop_no" value="{{ old('shop_no') }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="complex_name" class="form-label">{{ __('messages.complex_name') }}</label>
                                            <input type="text" class="form-control" id="complex_name" name="complex_name" value="{{ old('complex_name') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="building_name" class="form-label">{{ __('messages.building_name') }}</label>
                                            <input type="text" class="form-control" id="building_name" name="building_name" value="{{ old('building_name') }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="street_name" class="form-label">{{ __('messages.street_name') }}</label>
                                            <input type="text" class="form-control" id="street_name" name="street_name" value="{{ old('street_name') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="area" class="form-label">{{ __('messages.area') }}</label>
                                            <input type="text" class="form-control" id="area" name="area" value="{{ old('area') }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="pincode" class="form-label">{{ __('messages.pincode') }}</label>
                                            <input type="text" class="form-control" id="pincode" name="pincode" value="{{ old('pincode') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="city" class="form-label">{{ __('messages.city') }}</label>
                                            <input type="text" class="form-control" id="city" name="city" value="{{ old('city') }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="state" class="form-label">{{ __('messages.state') }}</label>
                                            <input type="text" class="form-control" id="state" name="state" value="{{ old('state') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="map_location" class="form-label">{{ __('messages.map_location') }}</label>
                                            <input type="text" class="form-control" id="map_location" name="map_location" value="{{ old('map_location') }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="location_guide" class="form-label">{{ __('messages.location_guide') }}</label>
                                            <textarea class="form-control" id="location_guide" name="location_guide" rows="3">{{ old('location_guide') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- KYC Details Tab -->
                            <div class="tab-pane fade" id="kyc" role="tabpanel">
                                <!-- Aadhar Details Section -->
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h5>{{ __('messages.aadhar_details') }} <button type="button" class="btn btn-sm btn-success" onclick="addAadharField()">{{ __('messages.add_new') }}</button></h5>
                                        <div id="aadhar-fields">
                                            <div class="row aadhar-entry mb-3">
                                                <div class="col-md-4">
                                                    <label for="aadhar_name[]" class="form-label">{{ __('messages.aadhar_name') }}</label>
                                                    <input type="text" class="form-control" name="aadhar_name[]" >
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="aadhar_number[]" class="form-label">{{ __('messages.aadhar_number') }}</label>
                                                    <input type="text" class="form-control" name="aadhar_number[]" maxlength="12" 
           minlength="12" >
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="aadhar_image[]" class="form-label">{{ __('messages.aadhar_image') }}</label>
                                                    <input type="file" class="form-control" name="aadhar_image[]">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- PAN Details Section -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h5>{{ __('messages.pan_details') }} <button type="button" class="btn btn-sm btn-success" onclick="addPanField()">{{ __('messages.add_new') }}</button></h5>
                                        <div id="pan-fields">
                                            <div class="row pan-entry mb-3">
                                                <div class="col-md-6">
                                                    <label for="pan_number[]" class="form-label">{{ __('messages.pan_number') }}</label>
                                                    <input type="text" class="form-control" name="pan_number[]" maxlength="10" 
           minlength="10">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="pan_image[]" class="form-label">{{ __('messages.pan_image') }}</label>
                                                    <input type="file" class="form-control" name="pan_image[]">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Other KYC Fields -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="gst_no" class="form-label">{{ __('messages.gst_number') }}</label>
                                            <input type="text" class="form-control" id="gst_no" name="gst_no" value="{{ old('gst_no') }}"
                                            maxlength="15" 
           minlength="15">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="gst_attachment" class="form-label">{{ __('messages.gst_attachment') }}</label>
                                            <input type="file" class="form-control" id="gst_attachment" name="gst_attachment">
                                        </div>
                                    </div>
                                </div>
                                                        
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="bis_no" class="form-label">{{ __('messages.bis_number') }}</label>
                                            <input type="text" class="form-control" id="bis_no" name="bis_no" value="{{ old('bis_no') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="bis_attachment" class="form-label">{{ __('messages.bis_attachment') }}</label>
                                            <input type="file" class="form-control" id="bis_attachment" name="bis_attachment">
                                        </div>
                                    </div>
                                </div>
                                                        
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="msme_no" class="form-label">{{ __('messages.msme_number') }}</label>
                                            <input type="text" class="form-control" id="msme_no" name="msme_no" value="{{ old('msme_no') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="msme_attachment" class="form-label">{{ __('messages.msme_attachment') }}</label>
                                            <input type="file" class="form-control" id="msme_attachment" name="msme_attachment">
                                        </div>
                                    </div>
                                </div>
                                                        
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tan_no" class="form-label">{{ __('messages.tan_number') }}</label>
                                            <input type="text" class="form-control" id="tan_no" name="tan_no" value="{{ old('tan_no') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tan_attachment" class="form-label">{{ __('messages.tan_attachment') }}</label>
                                            <input type="file" class="form-control" id="tan_attachment" name="tan_attachment">
                                        </div>
                                    </div>
                                </div>
                                                        
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="cin_no" class="form-label">{{ __('messages.cin_number') }}</label>
                                            <input type="text" class="form-control" id="cin_no" name="cin_no" value="{{ old('cin_no') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="cin_attachment" class="form-label">{{ __('messages.cin_attachment') }}</label>
                                            <input type="file" class="form-control" id="cin_attachment" name="cin_attachment">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bank Details Tab -->
                            <div class="tab-pane fade" id="bank" role="tabpanel">
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h5>{{ __('messages.bank_details') }} <button type="button" class="btn btn-sm btn-success" onclick="addBankField()">{{ __('messages.add_new') }}</button></h5>
                                        <div id="bank-fields">
                                            <div class="row bank-entry mb-3">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="bank_name[]" class="form-label">{{ __('messages.bank_name') }}</label>
                                                        <input type="text" class="form-control" name="bank_name[]">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="account_holder_name[]" class="form-label">{{ __('messages.account_holder_name') }}</label>
                                                        <input type="text" class="form-control" name="account_holder_name[]">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row bank-entry mb-3">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="account_number[]" class="form-label">{{ __('messages.account_number') }}</label>
                                                        <input type="text" class="form-control" name="account_number[]">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="ifsc_code[]" class="form-label">{{ __('messages.ifsc_code') }}</label>
                                                        <input type="text" class="form-control" name="ifsc_code[]">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row bank-entry mb-3">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="branch[]" class="form-label">{{ __('messages.branch') }}</label>
                                                        <input type="text" class="form-control" name="branch[]">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="bank_city[]" class="form-label">{{ __('messages.bank_city') }}</label>
                                                        <input type="text" class="form-control" name="bank_city[]">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="bank_state[]" class="form-label">{{ __('messages.bank_state') }}</label>
                                                        <input type="text" class="form-control" name="bank_state[]">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row bank-entry mb-3">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="passbook_image[]" class="form-label">{{ __('messages.passbook_cheque_image') }}</label>
                                                        <input type="file" class="form-control" name="passbook_image[]">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="note" class="form-label">{{ __('messages.notes') }}</label>
                                            <textarea class="form-control" id="note" name="note" rows="3">{{ old('note') }}</textarea>
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
                                        <h4 class="mb-0">{{ __('messages.buyer_permissions') }}</h4>
                                        <small class="text-muted">{{ __('messages.select_modules_access') }}</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach(App\Models\Buyer::getAllPermissions() as $permission)
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" 
                                                               type="checkbox" 
                                                               name="permissions[]" 
                                                               value="{{ $permission }}" 
                                                               id="permission_{{ $permission }}"
                                                               {{ in_array($permission, old('permissions', [])) ? 'checked' : '' }}>
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
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">{{ __('messages.save_buyer') }}</button>
                            <a href="{{ route('super-admin.business-partner.buyer') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
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
        const lastTab = localStorage.getItem('buyerFormActiveTab');
        if (lastTab) {
            const tabTrigger = new bootstrap.Tab(document.querySelector(lastTab));
            tabTrigger.show();
        }
        
        // Save the active tab to localStorage when it changes
        const tabs = document.querySelectorAll('#buyerTabs button[data-bs-toggle="tab"]');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (event) {
                localStorage.setItem('buyerFormActiveTab', event.target.getAttribute('data-bs-target'));
            });
        });
    });
    
    // Functions to add new fields dynamically
    function addAadharField() {
        const container = document.getElementById('aadhar-fields');
        const newField = document.createElement('div');
        newField.className = 'row aadhar-entry mb-3';
        newField.innerHTML = `
            <div class="col-md-4">
                <label class="form-label">Aadhar Name *</label>
                <input type="text" class="form-control" name="aadhar_name[]" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Aadhar Number *</label>
                <input type="text" class="form-control" name="aadhar_number[]" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Aadhar Image</label>
                <input type="file" class="form-control" name="aadhar_image[]">
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)">Remove</button>
            </div>
        `;
        container.appendChild(newField);
    }
    
    function addPanField() {
        const container = document.getElementById('pan-fields');
        const newField = document.createElement('div');
        newField.className = 'row pan-entry mb-3';
        newField.innerHTML = `
            <div class="col-md-5">
                <label class="form-label">PAN Number</label>
                <input type="text" class="form-control" name="pan_number[]">
            </div>
            <div class="col-md-5">
                <label class="form-label">PAN Image</label>
                <input type="file" class="form-control" name="pan_image[]">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)">Remove</button>
            </div>
        `;
        container.appendChild(newField);
    }
    
    function addBankField() {
        const container = document.getElementById('bank-fields');
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
    
    function removeField(button) {
        button.closest('.aadhar-entry, .pan-entry, .bank-entry').remove();
    }
</script>
<script src="{{ asset('js/fetch-address.js') }}"></script>
@endsection