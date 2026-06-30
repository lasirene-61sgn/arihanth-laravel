@extends('craftsman.layouts.app')

@section('title', 'My Profile')
@push('styles')
<style>
    .nav-tabs .nav-link {
        color: #000000 ;
    }

    .nav-tabs .nav-link.active {
        background-color: #000000 ;
        color: #000000  !important;
    }
</style>
@endpush
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">My Profile</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    @if($craftman->kyc_status === 'approved')
                        <span class="badge bg-success fs-6">Verified & Approved <i class="bi bi-check-circle-fill"></i></span>
                    @elseif($craftman->kyc_status === 'rejected')
                        <span class="badge bg-danger fs-6">Rejected - Please Update <i class="bi bi-exclamation-circle-fill"></i></span>
                    @else
                        <span class="badge bg-warning text-dark fs-6">Verification Pending <i class="bi bi-clock-history"></i></span>
                    @endif
                </div>
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

            @if($isReadOnly)
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle"></i> Your profile has been approved by the administrator and is now read-only. To make changes, please contact support.
                </div>
            @endif
            
            <div class="card">
                <div class="card-header">
                    <h4>Profile Information</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('craftsman.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <ul class="nav nav-tabs" id="craftmanTabs" role="tablist" >
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab" >Basic Info</button>
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
                                            <label for="business_name" class="form-label">Business Name *</label>
                                            <input type="text" class="form-control" id="business_name" name="business_name" value="{{ old('business_name', $craftman->business_name) }}" required {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Contact Person Name *</label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $craftman->name) }}" required {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mobile" class="form-label">Mobile Number *</label>
                                            <input type="text" class="form-control" id="mobile" name="mobile" value="{{ old('mobile', $craftman->mobile) }}" required {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="landline" class="form-label">Landline Number</label>
                                            <input type="text" class="form-control" id="landline" name="landline" value="{{ old('landline', $craftman->landline) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address *</label>
                                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $craftman->email) }}" required {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="business_email" class="form-label">Business Email</label>
                                            <input type="email" class="form-control" id="business_email" name="business_email" value="{{ old('business_email', $craftman->business_email) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="password" name="password" {{ $isReadOnly ? 'disabled' : '' }}>
                                            <small class="text-muted">Leave blank to keep current password</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="more" class="form-label">Additional Information</label>
                                            <textarea class="form-control" id="more" name="more" rows="3" {{ $isReadOnly ? 'disabled' : '' }}>{{ old('more', $craftman->more) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Address Information Tab -->
                            <div class="tab-pane fade" id="address" role="tabpanel">
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="door_no" class="form-label">Door Number</label>
                                            <input type="text" class="form-control" id="door_no" name="door_no" value="{{ old('door_no', $craftman->door_no) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="shop_no" class="form-label">Shop Number</label>
                                            <input type="text" class="form-control" id="shop_no" name="shop_no" value="{{ old('shop_no', $craftman->shop_no) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="complex_name" class="form-label">Complex Name</label>
                                            <input type="text" class="form-control" id="complex_name" name="complex_name" value="{{ old('complex_name', $craftman->complex_name) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="building_name" class="form-label">Building Name</label>
                                            <input type="text" class="form-control" id="building_name" name="building_name" value="{{ old('building_name', $craftman->building_name) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="street_name" class="form-label">Street Name</label>
                                            <input type="text" class="form-control" id="street_name" name="street_name" value="{{ old('street_name', $craftman->street_name) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="area" class="form-label">Area</label>
                                            <input type="text" class="form-control" id="area" name="area" value="{{ old('area', $craftman->area) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="pincode" class="form-label">Pincode</label>
                                            <input type="text" class="form-control" id="pincode" name="pincode" value="{{ old('pincode', $craftman->pincode) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" class="form-control" id="city" name="city" value="{{ old('city', $craftman->city) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="state" class="form-label">State</label>
                                            <input type="text" class="form-control" id="state" name="state" value="{{ old('state', $craftman->state) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="map_location" class="form-label">Map Location</label>
                                            <input type="text" class="form-control" id="map_location" name="map_location" value="{{ old('map_location', $craftman->map_location) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- KYC Details Tab -->
                            <div class="tab-pane fade" id="kyc" role="tabpanel">
                                
                                <!-- GST Section - ALWAYS Disabled/Readonly if exists, or disabled generally as per requirements -->
                                <div class="alert alert-warning mt-3">
                                    <i class="bi bi-lock-fill"></i> GST Details are locked and cannot be edited. Please contact admin for changes.
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="gst_no" class="form-label">GST Number</label>
                                            <input type="text" class="form-control" id="gst_no" value="{{ $craftman->gst_no }}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">GST Attachment</label>
                                            @if($craftman->gst_attachment)
                                                <div class="mt-2">
                                                    <a href="{{ asset('storage/' . $craftman->gst_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">View GST Certificate</a>
                                                </div>
                                            @else
                                                <p class="text-muted">No attachment uploaded.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <hr>

                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h5>Aadhar Details 
                                            @if(!$isReadOnly)
                                            <button type="button" class="btn btn-sm btn-success" onclick="addAadharField()">Add New</button>
                                            @endif
                                        </h5>
                                        <div id="aadhar-fields">
                                            @forelse($craftman->aadharDetails as $index => $aadhar)
                                                <div class="aadhar-entry mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">Aadhar Name *</label>
                                                                <input type="text" class="form-control" name="aadhar_name[]" value="{{ $aadhar->aadhar_name }}" required {{ $isReadOnly ? 'disabled' : '' }}>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">Aadhar Number *</label>
                                                                <input type="text" class="form-control" name="aadhar_number[]" value="{{ $aadhar->aadhar_number }}" required {{ $isReadOnly ? 'disabled' : '' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">Aadhar Image</label>
                                                                <input type="file" class="form-control" name="aadhar_image[]" {{ $isReadOnly ? 'disabled' : '' }}>
                                                                @if($aadhar->aadhar_image)
                                                                    <small class="text-muted">Current: <a href="{{ asset('storage/' . $aadhar->aadhar_image) }}" target="_blank">View Image</a></small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            @if(!$isReadOnly)
                                                            <label class="form-label">&nbsp;</label>
                                                            <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)">Remove</button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <hr>
                                                </div>
                                            @empty
                                                <!-- If no Aadhar exists, show one empty field set if not readonly -->
                                                @if(!$isReadOnly)
                                                <div class="aadhar-entry mb-3">
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
                                                    </div>
                                                    <hr>
                                                </div>
                                                @endif
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <h5>PAN Details 
                                            @if(!$isReadOnly)
                                            <button type="button" class="btn btn-sm btn-success" onclick="addPanField()">Add New</button>
                                            @endif
                                        </h5>
                                        <div id="pan-fields">
                                            @forelse($craftman->panDetails as $index => $pan)
                                                <div class="pan-entry mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">Company PAN Number *</label>
                                                                <input type="text" class="form-control" name="pan_number[]" value="{{ $pan->pan_number }}" required {{ $isReadOnly ? 'disabled' : '' }}>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">PAN Image</label>
                                                                <input type="file" class="form-control" name="pan_image[]" {{ $isReadOnly ? 'disabled' : '' }}>
                                                                @if($pan->pan_image)
                                                                    <small class="text-muted">Current: <a href="{{ asset('storage/' . $pan->pan_image) }}" target="_blank">View Image</a></small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            @if(!$isReadOnly)
                                                            <label class="form-label">&nbsp;</label>
                                                            <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)">Remove</button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <hr>
                                                </div>
                                            @empty
                                                @if(!$isReadOnly)
                                                <div class="pan-entry mb-3">
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
                                                    <hr>
                                                </div>
                                                @endif
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="bis_no" class="form-label">BIS Number</label>
                                            <input type="text" class="form-control" id="bis_no" name="bis_no" value="{{ old('bis_no', $craftman->bis_no) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="bis_attachment" class="form-label">BIS Attachment</label>
                                            <input type="file" class="form-control" id="bis_attachment" name="bis_attachment" {{ $isReadOnly ? 'disabled' : '' }}>
                                            @if($craftman->bis_attachment)
                                                <small class="text-muted">Current file: {{ basename($craftman->bis_attachment) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="msme_no" class="form-label">MSME Number</label>
                                            <input type="text" class="form-control" id="msme_no" name="msme_no" value="{{ old('msme_no', $craftman->msme_no) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="msme_attachment" class="form-label">MSME Attachment</label>
                                            <input type="file" class="form-control" id="msme_attachment" name="msme_attachment" {{ $isReadOnly ? 'disabled' : '' }}>
                                            @if($craftman->msme_attachment)
                                                <small class="text-muted">Current file: {{ basename($craftman->msme_attachment) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bank Details Tab -->
                            <div class="tab-pane fade" id="bank" role="tabpanel">
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h5>Bank Details 
                                            @if(!$isReadOnly)
                                            <button type="button" class="btn btn-sm btn-success" onclick="addBankField()">Add New</button>
                                            @endif
                                        </h5>
                                        <div id="bank-fields">
                                            @forelse($craftman->bankDetails as $index => $bank)
                                                <div class="bank-entry mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">Bank Name</label>
                                                                <input type="text" class="form-control" name="bank_name[]" value="{{ $bank->bank_name }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">Account Holder Name</label>
                                                                <input type="text" class="form-control" name="account_holder_name[]" value="{{ $bank->account_holder_name }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">Account Number</label>
                                                                <input type="text" class="form-control" name="account_number[]" value="{{ $bank->account_number }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">IFSC Code</label>
                                                                <input type="text" class="form-control" name="ifsc_code[]" value="{{ $bank->ifsc_code }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label class="form-label">Branch</label>
                                                                <input type="text" class="form-control" name="branch[]" value="{{ $bank->branch }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label class="form-label">Bank City</label>
                                                                <input type="text" class="form-control" name="bank_city[]" value="{{ $bank->bank_city }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label">Bank State</label>
                                                                <input type="text" class="form-control" name="bank_state[]" value="{{ $bank->bank_state }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-1">
                                                            @if(!$isReadOnly)
                                                            <label class="form-label">&nbsp;</label>
                                                            <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)">Remove</button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">Passbook Image</label>
                                                                <input type="file" class="form-control" name="passbook_image[]" {{ $isReadOnly ? 'disabled' : '' }}>
                                                                @if($bank->passbook_image)
                                                                    <small class="text-muted">Current: <a href="{{ asset('storage/' . $bank->passbook_image) }}" target="_blank">View Image</a></small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                </div>
                                            @empty
                                                @if(!$isReadOnly)
                                                <div class="bank-entry mb-3">
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
                                                    <hr>
                                                </div>
                                                @endif
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Worker Details Tab -->
                            <div class="tab-pane fade" id="worker" role="tabpanel">
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h5>Worker Details 
                                            @if(!$isReadOnly)
                                            <button type="button" class="btn btn-sm btn-success" onclick="addWorkerField()">Add New</button>
                                            @endif
                                        </h5>
                                        <div id="worker-fields">
                                            @forelse($craftman->workers as $index => $worker)
                                                <div class="worker-entry mb-3">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label class="form-label">Worker Name</label>
                                                                <input type="text" class="form-control" name="worker_name[]" value="{{ $worker->worker_name }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label class="form-label">Worker Number</label>
                                                                <input type="text" class="form-control" name="worker_number[]" value="{{ $worker->worker_number }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label">Worker Image</label>
                                                                <input type="file" class="form-control" name="worker_image[]" {{ $isReadOnly ? 'disabled' : '' }}>
                                                                @if($worker->worker_image)
                                                                    <small class="text-muted">Current: <a href="{{ asset('storage/' . $worker->worker_image) }}" target="_blank">View Image</a></small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-md-1">
                                                            @if(!$isReadOnly)
                                                            <label class="form-label">&nbsp;</label>
                                                            <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)">Remove</button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <hr>
                                                </div>
                                            @empty
                                                @if(!$isReadOnly)
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
                                                    </div>
                                                    <hr>
                                                </div>
                                                @endif
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if(!$isReadOnly)
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                        @else
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-lock-fill"></i> Profile Updates Disabled
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Keep track of the last active tab
    document.addEventListener('DOMContentLoaded', function() {
        const lastTab = localStorage.getItem('craftmanProfileActiveTab');
        if (lastTab) {
            const tabTrigger = new bootstrap.Tab(document.querySelector(lastTab));
            tabTrigger.show();
        }
        
        const tabs = document.querySelectorAll('#craftmanTabs button[data-bs-toggle="tab"]');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (event) {
                localStorage.setItem('craftmanProfileActiveTab', event.target.getAttribute('data-bs-target'));
            });
        });
    });
    
    // Add dynamic fields only if not readonly
    const isReadOnly = {{ $isReadOnly ? 'true' : 'false' }};

    function addAadharField() {
        if(isReadOnly) return;
        const container = document.getElementById('aadhar-fields');
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
    
    // ... Similar functions for PAN, Bank, Worker (basically copies of SuperAdmin view but with checks)
     function addPanField() {
        if(isReadOnly) return;
        const container = document.getElementById('pan-fields');
        const newField = document.createElement('div');
        newField.className = 'pan-entry mb-3';
        newField.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Company PAN Number *</label>
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

    function addBankField() {
        if(isReadOnly) return;
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

    function addWorkerField() {
        if(isReadOnly) return;
        const container = document.getElementById('worker-fields');
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

    function removeField(button) {
        if(isReadOnly) return;
        const entry = button.closest('.aadhar-entry, .pan-entry, .bank-entry, .worker-entry');
        if (entry) {
            entry.remove();
        }
    }
</script>
@endsection
