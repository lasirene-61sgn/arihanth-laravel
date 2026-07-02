@extends('admin.layouts.app')

@section('title', 'Edit Buyer')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Buyer</h1>
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

            <form action="{{ route('admin.business-partner.buyer.update', $buyer->id) }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="page" value="{{ request()->page }}">

                <ul class="nav nav-tabs" id="buyerTabs" role="tablist">
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
                </ul>

                <div class="tab-content" id="buyerTabsContent">
                    <!-- Basic Information Tab -->
                    <div class="tab-pane fade show active" id="basic" role="tabpanel">
                        <div class="row mt-3">
                            <!-- <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="dear" class="form-label">Dear Code *</label>
                                    <input type="text" class="form-control" id="dear" name="dear" value="{{ old('dear', $buyer->dear) }}" required>
                                </div>
                            </div> -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="business_name" class="form-label">Business Name *</label>
                                    <input type="text" class="form-control" id="business_name" name="business_name" value="{{ old('business_name', $buyer->business_name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Contact Person Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $buyer->name) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mobile" class="form-label">Mobile Number *</label>
                                    <input type="text" class="form-control" id="mobile" name="mobile" value="{{ old('mobile', $buyer->mobile) }}" required minlength="10" maxlength="10">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="landline" class="form-label">Landline/Centrex Number</label>
                                    <input type="text" class="form-control" id="landline" name="landline" value="{{ old('landline', $buyer->landline) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $buyer->email) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="business_email" class="form-label">Business Email</label>
                                    <input type="email" class="form-control" id="business_email" name="business_email" value="{{ old('business_email', $buyer->business_email) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="refered_by" class="form-label">Referred By</label>
                                    <input type="text" class="form-control" id="refered_by" name="refered_by" value="{{ old('refered_by', $buyer->refered_by) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Leave empty to keep current">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="more" class="form-label">Additional Information</label>
                                    <textarea class="form-control" id="more" name="more" rows="3">{{ old('more', $buyer->more) }}</textarea>
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
                                    <input type="text" class="form-control" id="door_no" name="door_no" value="{{ old('door_no', $buyer->door_no) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shop_no" class="form-label">Shop Number</label>
                                    <input type="text" class="form-control" id="shop_no" name="shop_no" value="{{ old('shop_no', $buyer->shop_no) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="complex_name" class="form-label">Complex Name</label>
                                    <input type="text" class="form-control" id="complex_name" name="complex_name" value="{{ old('complex_name', $buyer->complex_name) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="building_name" class="form-label">Building Name</label>
                                    <input type="text" class="form-control" id="building_name" name="building_name" value="{{ old('building_name', $buyer->building_name) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="street_name" class="form-label">Street Name</label>
                                    <input type="text" class="form-control" id="street_name" name="street_name" value="{{ old('street_name', $buyer->street_name) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="area" class="form-label">Area</label>
                                    <input type="text" class="form-control" id="area" name="area" value="{{ old('area', $buyer->area) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pincode" class="form-label">Pincode</label>
                                    <input type="text" class="form-control" id="pincode" name="pincode" value="{{ old('pincode', $buyer->pincode) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city" value="{{ old('city', $buyer->city) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" class="form-control" id="state" name="state" value="{{ old('state', $buyer->state) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="map_location" class="form-label">Map Location</label>
                                    <input type="text" class="form-control" id="map_location" name="map_location" value="{{ old('map_location', $buyer->map_location) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="location_guide" class="form-label">Location Guide</label>
                                    <textarea class="form-control" id="location_guide" name="location_guide" rows="3">{{ old('location_guide', $buyer->location_guide) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KYC Details Tab -->
                    <div class="tab-pane fade" id="kyc" role="tabpanel">
                        <!-- Aadhar Details Section -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>Aadhar Details <button type="button" class="btn btn-sm btn-success" onclick="addAadharField()">Add New</button></h5>
                                <div id="aadhar-fields">
                                    @forelse($buyer->aadharDetails as $index => $aadhar)
                                        <div class="row aadhar-entry mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Aadhar Name </label>
                                                <input type="text" class="form-control" name="aadhar_name[]" value="{{ $aadhar->aadhar_name }}" >
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Aadhar Number </label>
                                                <input type="text" class="form-control" name="aadhar_number[]" value="{{ $aadhar->aadhar_number }}" minlength="12" maxlength="12">
                                            </div>
                                            <div class="col-md-3">
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
                                            <div class="col-md-1">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)">Remove</button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="row aadhar-entry mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Aadhar Name </label>
                                                <input type="text" class="form-control" name="aadhar_name[]" >
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Aadhar Number </label>
                                                <input type="text" class="form-control" name="aadhar_number[]" minlength="12" maxlength="12">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Aadhar Image</label>
                                                <input type="file" class="form-control" name="aadhar_image[]">
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        
                        <!-- PAN Details Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>PAN Details <button type="button" class="btn btn-sm btn-success" onclick="addPanField()">Add New</button></h5>
                                <div id="pan-fields">
                                    @forelse($buyer->panDetails as $index => $pan)
                                        <div class="row pan-entry mb-3">
                                            <div class="col-md-5">
                                                <label class="form-label">PAN Number </label>
                                                <input type="text" class="form-control" name="pan_number[]" value="{{ $pan->pan_number }}" minlength="10" maxlength="10">
                                            </div>
                                            <div class="col-md-5">
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
                                            <div class="col-md-2">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeField(this)">Remove</button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="row pan-entry mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">PAN Number </label>
                                                <input type="text" class="form-control" name="pan_number[]" minlength="10" maxlength="10">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">PAN Image</label>
                                                <input type="file" class="form-control" name="pan_image[]">
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        
                        <!-- Other KYC Fields -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gst_no" class="form-label">GST Number</label>
                                    <input type="text" class="form-control" id="gst_no" name="gst_no" value="{{ old('gst_no', $buyer->gst_no) }}" minlength="15" maxlength="15">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gst_attachment" class="form-label">GST Attachment</label>
                                    <input type="file" class="form-control" id="gst_attachment" name="gst_attachment">
                                     @if($buyer->gst_attachment)
                                        <small class="text-muted d-block mt-1">
                                            Current: <a href="{{ asset('storage/' . $buyer->gst_attachment) }}" target="_blank">{{ basename($buyer->gst_attachment) }}</a>
                                            <br>
                                            <a href="{{ asset('storage/' . $buyer->gst_attachment) }}" download class="btn btn-xs btn-outline-success py-0 px-1" style="font-size: 0.75rem;">Download</a>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bis_no" class="form-label">BIS Number</label>
                                    <input type="text" class="form-control" id="bis_no" name="bis_no" value="{{ old('bis_no', $buyer->bis_no) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bis_attachment" class="form-label">BIS Attachment</label>
                                    <input type="file" class="form-control" id="bis_attachment" name="bis_attachment">
                                     @if($buyer->bis_attachment)
                                        <small class="text-muted d-block mt-1">
                                            Current: <a href="{{ asset('storage/' . $buyer->bis_attachment) }}" target="_blank">{{ basename($buyer->bis_attachment) }}</a>
                                            <br>
                                            <a href="{{ asset('storage/' . $buyer->bis_attachment) }}" download class="btn btn-xs btn-outline-success py-0 px-1" style="font-size: 0.75rem;">Download</a>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="msme_no" class="form-label">MSME Number</label>
                                    <input type="text" class="form-control" id="msme_no" name="msme_no" value="{{ old('msme_no', $buyer->msme_no) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="msme_attachment" class="form-label">MSME Attachment</label>
                                    <input type="file" class="form-control" id="msme_attachment" name="msme_attachment">
                                     @if($buyer->msme_attachment)
                                        <small class="text-muted d-block mt-1">
                                            Current: <a href="{{ asset('storage/' . $buyer->msme_attachment) }}" target="_blank">{{ basename($buyer->msme_attachment) }}</a>
                                            <br>
                                            <a href="{{ asset('storage/' . $buyer->msme_attachment) }}" download class="btn btn-xs btn-outline-success py-0 px-1" style="font-size: 0.75rem;">Download</a>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tan_no" class="form-label">TAN Number</label>
                                    <input type="text" class="form-control" id="tan_no" name="tan_no" value="{{ old('tan_no', $buyer->tan_no) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tan_attachment" class="form-label">TAN Attachment</label>
                                    <input type="file" class="form-control" id="tan_attachment" name="tan_attachment">
                                     @if($buyer->tan_attachment)
                                        <small class="text-muted d-block mt-1">
                                            Current: <a href="{{ asset('storage/' . $buyer->tan_attachment) }}" target="_blank">{{ basename($buyer->tan_attachment) }}</a>
                                            <br>
                                            <a href="{{ asset('storage/' . $buyer->tan_attachment) }}" download class="btn btn-xs btn-outline-success py-0 px-1" style="font-size: 0.75rem;">Download</a>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cin_no" class="form-label">CIN Number</label>
                                    <input type="text" class="form-control" id="cin_no" name="cin_no" value="{{ old('cin_no', $buyer->cin_no ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cin_attachment" class="form-label">CIN Attachment</label>
                                    <input type="file" class="form-control" id="cin_attachment" name="cin_attachment">
                                     @if($buyer->cin_attachment)
                                        <small class="text-muted d-block mt-1">
                                            Current: <a href="{{ asset('storage/' . $buyer->cin_attachment) }}" target="_blank">{{ basename($buyer->cin_attachment) }}</a>
                                            <br>
                                            <a href="{{ asset('storage/' . $buyer->cin_attachment) }}" download class="btn btn-xs btn-outline-success py-0 px-1" style="font-size: 0.75rem;">Download</a>
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
                                    @forelse($buyer->bankDetails as $index => $bank)
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
                                                        <label class="form-label">Passbook Image</label>
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
                                                        <label class="form-label">Passbook/Cheque Leaf Image</label>
                                                        <input type="file" class="form-control" name="passbook_image[]">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="note" class="form-label">Notes</label>
                                    <textarea class="form-control" id="note" name="note" rows="3">{{ old('note', $buyer->note) }}</textarea>
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
                                                       {{ in_array($permission, old('permissions', $buyer->getPermissionsArray())) ? 'checked' : '' }}>
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

                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('admin.business-partner.buyer', ['page' => request()->page]) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Buyer</button>
                </div>
            </form>
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
                <label class="form-label">PAN Number *</label>
                <input type="text" class="form-control" name="pan_number[]" required>
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
