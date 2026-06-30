@extends('admin.layouts.app')

@section('title', 'View Craftman')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">View Craftman</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="{{ route('admin.business-partner.craftman.edit', $craftman) }}" class="btn btn-primary me-2">
                            <i class="bi bi-pencil"></i> Edit
                        </a>

                        @if($craftman->kyc_status === 'approved')
                            <form action="{{ route('admin.business-partner.craftman.unlock', $craftman) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to unlock this profile? The craftsman will be able to edit their details again.')">
                                @csrf
                                <button type="submit" class="btn btn-warning me-2">
                                    <i class="bi bi-unlock"></i> Unlock Profile
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.business-partner.craftman.approve', $craftman) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to approve this KYC? The profile will become read-only for the craftsman.')">
                                @csrf
                                <button type="submit" class="btn btn-success me-2">
                                    <i class="bi bi-check-circle"></i> Approve KYC
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('admin.business-partner.craftman') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>Craftman Details</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Craftman Code</label>
                                    <p>{{ $craftman->craftman_code }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Business Name</label>
                                    <p>{{ $craftman->business_name }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Contact Person</label>
                                    <p>{{ $craftman->name }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Mobile</label>
                                    <p>{{ $craftman->mobile }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Email</label>
                                    <p>{{ $craftman->email }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Landline</label>
                                    <p>{{ $craftman->landline ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Business Email</label>
                                    <p>{{ $craftman->business_email ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Referred By</label>
                                    <p>{{ $craftman->refered_by ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">More Information</label>
                                    <p>{{ $craftman->more ?? 'N/A' }}</p>
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
                                    <p>{{ $craftman->door_no ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Shop No</label>
                                    <p>{{ $craftman->shop_no ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Complex Name</label>
                                    <p>{{ $craftman->complex_name ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Building Name</label>
                                    <p>{{ $craftman->building_name ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Street Name</label>
                                    <p>{{ $craftman->street_name ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Area</label>
                                    <p>{{ $craftman->area ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Pincode</label>
                                    <p>{{ $craftman->pincode ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">City</label>
                                    <p>{{ $craftman->city ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">State</label>
                                    <p>{{ $craftman->state ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Map Location</label>
                                    <p>{{ $craftman->map_location ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Location Guide</label>
                                    <p>{{ $craftman->location_guide ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h4>Worker Details</h4>
                        </div>
                        <div class="card-body">
                            @if($craftman->workers->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Worker Name</th>
                                                <th>Worker Number</th>
                                                <th>Worker Image</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($craftman->workers as $worker)
                                                <tr>
                                                    <td>{{ $worker->worker_name }}</td>
                                                    <td>{{ $worker->worker_number ?? 'N/A' }}</td>
                                                     <td>
                                                        @if($worker->worker_image)
                                                            <div class="mb-2">
                                                                <img src="{{ asset('storage/' . $worker->worker_image) }}" alt="Worker Image" style="max-height: 50px;" class="img-thumbnail">
                                                            </div>
                                                            <a href="{{ asset('storage/' . $worker->worker_image) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                                            <a href="{{ asset('storage/' . $worker->worker_image) }}" download class="btn btn-sm btn-outline-success">Download</a>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p>No worker details available.</p>
                            @endif
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
                                    {{ $craftman->bis_no ?? 'N/A' }}
                                    @if($craftman->bis_attachment)
                                        <br>
                                        <a href="{{ asset('storage/' . $craftman->bis_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                        <a href="{{ asset('storage/' . $craftman->bis_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">Download</a>
                                    @endif
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-muted">GST No</label>
                                <p>
                                    {{ $craftman->gst_no ?? 'N/A' }}
                                    @if($craftman->gst_attachment)
                                        <br>
                                        <a href="{{ asset('storage/' . $craftman->gst_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                        <a href="{{ asset('storage/' . $craftman->gst_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">Download</a>
                                    @endif
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-muted">MSME No</label>
                                <p>
                                    {{ $craftman->msme_no ?? 'N/A' }}
                                    @if($craftman->msme_attachment)
                                        <br>
                                        <a href="{{ asset('storage/' . $craftman->msme_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                        <a href="{{ asset('storage/' . $craftman->msme_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">Download</a>
                                    @endif
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-muted">PAN No</label>
                                <p>
                                    {{ $craftman->pan_no ?? 'N/A' }}
                                    @if($craftman->pan_attachment)
                                        <br>
                                        <a href="{{ asset('storage/' . $craftman->pan_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                        <a href="{{ asset('storage/' . $craftman->pan_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">Download</a>
                                    @endif
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-muted">TAN No</label>
                                <p>
                                    {{ $craftman->tan_no ?? 'N/A' }}
                                    @if($craftman->tan_attachment)
                                        <br>
                                        <a href="{{ asset('storage/' . $craftman->tan_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                        <a href="{{ asset('storage/' . $craftman->tan_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">Download</a>
                                    @endif
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-muted">Aadhar No</label>
                                <p>{{ $craftman->aadhar_no ?? 'N/A' }}</p>
                            </div>

                            @if($craftman->aadharDetails->count() > 0)
                                <div class="mt-4">
                                    <h6>Aadhar Documents</h6>
                                    @foreach($craftman->aadharDetails as $aadhar)
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

                            @if($craftman->panDetails->count() > 0)
                                <div class="mt-4">
                                    <h6>PAN Documents</h6>
                                    @foreach($craftman->panDetails as $pan)
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
                             @if($craftman->bankDetails->count() > 0)
                                @foreach($craftman->bankDetails as $bank)
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