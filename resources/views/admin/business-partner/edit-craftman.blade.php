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

            <form action="{{ route('admin.business-partner.craftman.update', $craftman) }}" method="POST" enctype="multipart/form-data">
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
                        <!-- Permissions Section -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Permissions</label>
                                    <div class="row">
                                        @foreach(\App\Models\Craftman::getAllPermissions() as $permission)
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission }}" id="permission_{{ $permission }}" 
                                                           {{ in_array($permission, $craftman->getPermissionsArray()) ? 'checked' : '' }}>
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

                    <!-- Address Information Tab -->
                    <div class="tab-pane fade" id="address" role="tabpanel">
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="door_no" class="form-label">Door Number</label>
                                    <input type="text" class="form-control" id="door_no" name="door_no" value="{{ old('door_no', $craftman->door_no) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shop_no" class="form-label">Shop Number</label>
                                    <input type="text" class="form-control" id="shop_no" name="shop_no" value="{{ old('shop_no', $craftman->shop_no) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="complex_name" class="form-label">Complex Name</label>
                                    <input type="text" class="form-control" id="complex_name" name="complex_name" value="{{ old('complex_name', $craftman->complex_name) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="building_name" class="form-label">Building Name</label>
                                    <input type="text" class="form-control" id="building_name" name="building_name" value="{{ old('building_name', $craftman->building_name) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="street_name" class="form-label">Street Name</label>
                                    <input type="text" class="form-control" id="street_name" name="street_name" value="{{ old('street_name', $craftman->street_name) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="area" class="form-label">Area</label>
                                    <input type="text" class="form-control" id="area" name="area" value="{{ old('area', $craftman->area) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pincode" class="form-label">Pincode</label>
                                    <input type="text" class="form-control" id="pincode" name="pincode" value="{{ old('pincode', $craftman->pincode) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city" value="{{ old('city', $craftman->city) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" class="form-control" id="state" name="state" value="{{ old('state', $craftman->state) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="map_location" class="form-label">Map Location</label>
                                    <input type="text" class="form-control" id="map_location" name="map_location" value="{{ old('map_location', $craftman->map_location) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="location_guide" class="form-label">Location Guide</label>
                                    <textarea class="form-control" id="location_guide" name="location_guide" rows="3">{{ old('location_guide', $craftman->location_guide) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KYC Details Tab -->
                    <div class="tab-pane fade" id="kyc" role="tabpanel">
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>Aadhar Details <button type="button" class="btn btn-sm btn-success" onclick="addAadharField()">Add New</button></h5>
                                <div id="aadhar-fields">
                                    @forelse($craftman->aadharDetails as $index => $aadhar)
                                        <div class="aadhar-entry mb-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Aadhar Name </label>
                                                        <input type="text" class="form-control" name="aadhar_name[]" value="{{ $aadhar->aadhar_name }}" >
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Aadhar Number</label>
                                                        <input type="text" class="form-control" name="aadhar_number[]" value="{{ $aadhar->aadhar_number }}" minlength="12" maxlength="12">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Aadhar Image</label>
                                                        <input type="file" class="form-control" name="aadhar_image[]">
                                                        @if($aadhar->aadhar_image)
                                                            <small class="text-muted d-block mt-1">
                                                                Current: <a href="{{ asset('storage/' . $aadhar->aadhar_image) }}" target="_blank">{{ basename($aadhar->aadhar_image) }}</a>
                                                                <br>
                                                                <a href="{{ asset('storage/' . $aadhar->aadhar_image) }}" download class="btn btn-xs btn-outline-success py-0 px-1" style="font-size: 0.75rem;">Download</a>
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)">Remove</button>
                                                </div>
                                            </div>
                                            <hr>
                                        </div>
                                    @empty
                                        <div class="aadhar-entry mb-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Aadhar Name </label>
                                                        <input type="text" class="form-control" name="aadhar_name[]" >
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Aadhar Number</label>
                                                        <input type="text" class="form-control" name="aadhar_number[]" minlength="12" maxlength="12">
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
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <h5>PAN Details <button type="button" class="btn btn-sm btn-success" onclick="addPanField()">Add New</button></h5>
                                <div id="pan-fields">
                                    @forelse($craftman->panDetails as $index => $pan)
                                        <div class="pan-entry mb-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">PAN Number</label>
                                                        <input type="text" class="form-control" name="pan_number[]" value="{{ $pan->pan_number }}" minlength="10" maxlength="10">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">PAN Image</label>
                                                        <input type="file" class="form-control" name="pan_image[]">
                                                        @if($pan->pan_image)
                                                            <small class="text-muted d-block mt-1">
                                                                Current: <a href="{{ asset('storage/' . $pan->pan_image) }}" target="_blank">{{ basename($pan->pan_image) }}</a>
                                                                <br>
                                                                <a href="{{ asset('storage/' . $pan->pan_image) }}" download class="btn btn-xs btn-outline-success py-0 px-1" style="font-size: 0.75rem;">Download</a>
                                                            </small>
                                                        @endif
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
                                        </div>
                                    @empty
                                        <div class="pan-entry mb-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">PAN Number </label>
                                                        <input type="text" class="form-control" name="pan_number[]" minlength="10" maxlength="10">
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
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gst_no" class="form-label">GST Number</label>
                                    <input type="text" class="form-control" id="gst_no" name="gst_no" value="{{ old('gst_no', $craftman->gst_no) }}" minlength="15" maxlength="15">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gst_attachment" class="form-label">GST Attachment</label>
                                    <input type="file" class="form-control" id="gst_attachment" name="gst_attachment">
                                    @if($craftman->gst_attachment)
                                        <small class="text-muted d-block mt-1">
                                            Current: <a href="{{ asset('storage/' . $craftman->gst_attachment) }}" target="_blank">{{ basename($craftman->gst_attachment) }}</a>
                                            <br>
                                            <a href="{{ asset('storage/' . $craftman->gst_attachment) }}" download class="btn btn-xs btn-outline-success py-0 px-1" style="font-size: 0.75rem;">Download</a>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bis_no" class="form-label">BIS Number</label>
                                    <input type="text" class="form-control" id="bis_no" name="bis_no" value="{{ old('bis_no', $craftman->bis_no) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bis_attachment" class="form-label">BIS Attachment</label>
                                    <input type="file" class="form-control" id="bis_attachment" name="bis_attachment">
                                    @if($craftman->bis_attachment)
                                        <small class="text-muted d-block mt-1">
                                            Current: <a href="{{ asset('storage/' . $craftman->bis_attachment) }}" target="_blank">{{ basename($craftman->bis_attachment) }}</a>
                                            <br>
                                            <a href="{{ asset('storage/' . $craftman->bis_attachment) }}" download class="btn btn-xs btn-outline-success py-0 px-1" style="font-size: 0.75rem;">Download</a>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="msme_no" class="form-label">MSME Number</label>
                                    <input type="text" class="form-control" id="msme_no" name="msme_no" value="{{ old('msme_no', $craftman->msme_no) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="msme_attachment" class="form-label">MSME Attachment</label>
                                    <input type="file" class="form-control" id="msme_attachment" name="msme_attachment">
                                    @if($craftman->msme_attachment)
                                        <small class="text-muted d-block mt-1">
                                            Current: <a href="{{ asset('storage/' . $craftman->msme_attachment) }}" target="_blank">{{ basename($craftman->msme_attachment) }}</a>
                                            <br>
                                            <a href="{{ asset('storage/' . $craftman->msme_attachment) }}" download class="btn btn-xs btn-outline-success py-0 px-1" style="font-size: 0.75rem;">Download</a>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tan_no" class="form-label">TAN Number</label>
                                    <input type="text" class="form-control" id="tan_no" name="tan_no" value="{{ old('tan_no', $craftman->tan_no) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tan_attachment" class="form-label">TAN Attachment</label>
                                    <input type="file" class="form-control" id="tan_attachment" name="tan_attachment">
                                    @if($craftman->tan_attachment)
                                        <small class="text-muted d-block mt-1">
                                            Current: <a href="{{ asset('storage/' . $craftman->tan_attachment) }}" target="_blank">{{ basename($craftman->tan_attachment) }}</a>
                                            <br>
                                            <a href="{{ asset('storage/' . $craftman->tan_attachment) }}" download class="btn btn-xs btn-outline-success py-0 px-1" style="font-size: 0.75rem;">Download</a>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cin_no" class="form-label">CIN Number</label>
                                    <input type="text" class="form-control" id="cin_no" name="cin_no" value="{{ old('cin_no', $craftman->cin_no ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cin_attachment" class="form-label">CIN Attachment</label>
                                    <input type="file" class="form-control" id="cin_attachment" name="cin_attachment">
                                    @if($craftman->cin_attachment)
                                        <small class="text-muted d-block mt-1">
                                            Current: <a href="{{ asset('storage/' . $craftman->cin_attachment) }}" target="_blank">{{ basename($craftman->cin_attachment) }}</a>
                                            <br>
                                            <a href="{{ asset('storage/' . $craftman->cin_attachment) }}" download class="btn btn-xs btn-outline-success py-0 px-1" style="font-size: 0.75rem;">Download</a>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bank Details Tab -->
                    <div class="tab-pane fade" id="bank" role="tabpanel">
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>Bank Details <button type="button" class="btn btn-sm btn-success" onclick="addBankField()">Add New</button></h5>
                                <div id="bank-fields">
                                    @forelse($craftman->bankDetails as $index => $bank)
                                        <div class="bank-entry mb-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Bank Name</label>
                                                        <input type="text" class="form-control" name="bank_name[]" value="{{ $bank->bank_name }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Account Holder Name</label>
                                                        <input type="text" class="form-control" name="account_holder_name[]" value="{{ $bank->account_holder_name }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Account Number</label>
                                                        <input type="text" class="form-control" name="account_number[]" value="{{ $bank->account_number }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">IFSC Code</label>
                                                        <input type="text" class="form-control" name="ifsc_code[]" value="{{ $bank->ifsc_code }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Branch</label>
                                                        <input type="text" class="form-control" name="branch[]" value="{{ $bank->branch }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Bank City</label>
                                                        <input type="text" class="form-control" name="bank_city[]" value="{{ $bank->bank_city }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Bank State</label>
                                                        <input type="text" class="form-control" name="bank_state[]" value="{{ $bank->bank_state }}">
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
                                                        <label class="form-label">Passbook/Cheque Leaf Image</label>
                                                        <input type="file" class="form-control" name="passbook_image[]">
                                                        @if($bank->passbook_image)
                                                            <small class="text-muted d-block mt-1">
                                                                Current: <a href="{{ asset('storage/' . $bank->passbook_image) }}" target="_blank">{{ basename($bank->passbook_image) }}</a>
                                                                <br>
                                                                <a href="{{ asset('storage/' . $bank->passbook_image) }}" download class="btn btn-xs btn-outline-success py-0 px-1" style="font-size: 0.75rem;">Download</a>
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                        </div>
                                    @empty
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
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Bank State</label>
                                                        <input type="text" class="form-control" name="bank_state[]">
                                                    </div>
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
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="note" class="form-label">Notes</label>
                                    <textarea class="form-control" id="note" name="note" rows="3">{{ old('note', $craftman->note) }}</textarea>
                                </div>
                            </div>
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

                <div class="d-flex justify-content-between">
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