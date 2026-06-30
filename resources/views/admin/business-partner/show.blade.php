@extends('admin.layouts.app')

@section('title', 'View Buyer')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">View Buyer</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="{{ route('admin.business-partner.buyer.edit', $buyer->id) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        
                        @if($buyer->kyc_status !== 'approved')
                            <form action="{{ route('admin.business-partner.buyer.approve', $buyer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to approve this buyer? Profile will become read-only for them.')">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Approve KYC
                                </button>
                            </form>
                        @else
                            <button class="btn btn-success" disabled>
                                <i class="bi bi-check-circle-fill"></i> KYC Approved
                            </button>
                            
                            <form action="{{ route('admin.business-partner.buyer.unlock', $buyer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to unlock this profile? Buyer will be able to edit their details.')">
                                @csrf
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-unlock"></i> Unlock Profile
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('admin.business-partner.buyer') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <strong>KYC Status: </strong> 
                        @if($buyer->kyc_status === 'approved')
                            <span class="badge bg-success">Approved</span>
                        @elseif($buyer->kyc_status === 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                        @else
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>Buyer Details</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">BP Code</label>
                                    <p>{{ $buyer->bp_code }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Business Name</label>
                                    <p>{{ $buyer->business_name }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Contact Person</label>
                                    <p>{{ $buyer->name }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Mobile</label>
                                    <p>{{ $buyer->mobile }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Email</label>
                                    <p>{{ $buyer->email }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Landline</label>
                                    <p>{{ $buyer->landline ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Business Email</label>
                                    <p>{{ $buyer->business_email ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Referred By</label>
                                    <p>{{ $buyer->refered_by ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">More Information</label>
                                    <p>{{ $buyer->more ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h4>Address Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Door No</label>
                                    <p>{{ $buyer->door_no ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Shop No</label>
                                    <p>{{ $buyer->shop_no ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Complex Name</label>
                                    <p>{{ $buyer->complex_name ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Building Name</label>
                                    <p>{{ $buyer->building_name ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Street Name</label>
                                    <p>{{ $buyer->street_name ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Area</label>
                                    <p>{{ $buyer->area ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Pincode</label>
                                    <p>{{ $buyer->pincode ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">City</label>
                                    <p>{{ $buyer->city ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">State</label>
                                    <p>{{ $buyer->state ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Map Location</label>
                                    <p>{{ $buyer->map_location ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Location Guide</label>
                                    <p>{{ $buyer->location_guide ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h4>KYC Documents</h4>
                        </div>
                        <div class="card-body">
                             <div class="mb-3">
                                <label class="text-muted">BIS No</label>
                                <p>
                                    {{ $buyer->bis_no ?? 'N/A' }}
                                    @if($buyer->bis_attachment)
                                        <br>
                                        <a href="{{ asset('storage/' . $buyer->bis_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                        <a href="{{ asset('storage/' . $buyer->bis_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">Download</a>
                                    @endif
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-muted">GST No</label>
                                <p>
                                    {{ $buyer->gst_no ?? 'N/A' }}
                                    @if($buyer->gst_attachment)
                                        <br>
                                        <a href="{{ asset('storage/' . $buyer->gst_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                        <a href="{{ asset('storage/' . $buyer->gst_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">Download</a>
                                    @endif
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-muted">MSME No</label>
                                <p>
                                    {{ $buyer->msme_no ?? 'N/A' }}
                                    @if($buyer->msme_attachment)
                                        <br>
                                        <a href="{{ asset('storage/' . $buyer->msme_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                        <a href="{{ asset('storage/' . $buyer->msme_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">Download</a>
                                    @endif
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-muted">PAN No</label>
                                <p>
                                    {{ $buyer->pan_no ?? 'N/A' }}
                                    @if($buyer->pan_attachment)
                                        <br>
                                        <a href="{{ asset('storage/' . $buyer->pan_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                        <a href="{{ asset('storage/' . $buyer->pan_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">Download</a>
                                    @endif
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-muted">TAN No</label>
                                <p>
                                    {{ $buyer->tan_no ?? 'N/A' }}
                                    @if($buyer->tan_attachment)
                                        <br>
                                        <a href="{{ asset('storage/' . $buyer->tan_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                        <a href="{{ asset('storage/' . $buyer->tan_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">Download</a>
                                    @endif
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-muted">CIN No</label>
                                <p>
                                    {{ $buyer->cin_no ?? 'N/A' }}
                                    @if($buyer->cin_attachment)
                                        <br>
                                        <a href="{{ asset('storage/' . $buyer->cin_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                        <a href="{{ asset('storage/' . $buyer->cin_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">Download</a>
                                    @endif
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-muted">Aadhar No</label>
                                <p>{{ $buyer->aadhar_no ?? 'N/A' }}</p>
                            </div>
                            
                            @if($buyer->aadharDetails->count() > 0)
                                <div class="mt-4">
                                    <h6>Aadhar Documents</h6>
                                    @foreach($buyer->aadharDetails as $aadhar)
                                        <div class="mb-3 p-2 border rounded">
                                            <strong>{{ $aadhar->aadhar_name }}</strong> ({{ $aadhar->aadhar_number }})
                                            @if($aadhar->aadhar_image)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/' . $aadhar->aadhar_image) }}" alt="Aadhar Image" style="max-width: 100%; max-height: 100px;" class="img-thumbnail mb-1">
                                                    <br>
                                                    <a href="{{ asset('storage/' . $aadhar->aadhar_image) }}" target="_blank" class="btn btn-xs btn-outline-primary">View</a>
                                                    <a href="{{ asset('storage/' . $aadhar->aadhar_image) }}" download class="btn btn-xs btn-outline-success">Download</a>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            
                            @if($buyer->panDetails->count() > 0)
                                <div class="mt-4">
                                    <h6>PAN Documents</h6>
                                    @foreach($buyer->panDetails as $pan)
                                        <div class="mb-3 p-2 border rounded">
                                            <strong>{{ $pan->pan_number }}</strong>
                                            @if($pan->pan_image)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/' . $pan->pan_image) }}" alt="PAN Image" style="max-width: 100%; max-height: 100px;" class="img-thumbnail mb-1">
                                                    <br>
                                                    <a href="{{ asset('storage/' . $pan->pan_image) }}" target="_blank" class="btn btn-xs btn-outline-primary">View</a>
                                                    <a href="{{ asset('storage/' . $pan->pan_image) }}" download class="btn btn-xs btn-outline-success">Download</a>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h4>Bank Details</h4>
                        </div>
                        <div class="card-body">
                             @if($buyer->bankDetails->count() > 0)
                                @foreach($buyer->bankDetails as $bank)
                                    <div class="mb-4 p-2 border rounded">
                                        <div class="mb-2">
                                            <label class="text-muted">Bank Name</label>
                                            <p>{{ $bank->bank_name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="mb-2">
                                            <label class="text-muted">Account Holder</label>
                                            <p>{{ $bank->account_holder_name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="mb-2">
                                            <label class="text-muted">Acc No / IFSC</label>
                                            <p>{{ $bank->account_number ?? 'N/A' }} / {{ $bank->ifsc_code ?? 'N/A' }}</p>
                                        </div>
                                        @if($bank->passbook_image)
                                            <div class="mt-2">
                                                <label class="text-muted">Passbook Image</label><br>
                                                <img src="{{ asset('storage/' . $bank->passbook_image) }}" alt="Passbook Image" style="max-width: 100%; max-height: 100px;" class="img-thumbnail mb-1">
                                                <br>
                                                <a href="{{ asset('storage/' . $bank->passbook_image) }}" target="_blank" class="btn btn-xs btn-outline-primary">View</a>
                                                <a href="{{ asset('storage/' . $bank->passbook_image) }}" download class="btn btn-xs btn-outline-success">Download</a>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <p>No bank details available.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection