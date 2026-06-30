@extends('super-admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Key User Details</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('super-admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('super-admin.key-user.index') }}">Key Users</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Key User Information</h4>
                    <div>
                        <a href="{{ route('super-admin.key-user.edit', $keyUser) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="{{ route('super-admin.key-user.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            @if($keyUser->profile_picture)
                                <img src="{{ asset('storage/' . $keyUser->profile_picture) }}" 
                                     alt="Profile Picture" 
                                     class="img-fluid rounded-circle mb-3" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3" 
                                     style="width: 150px; height: 150px; margin: 0 auto;">
                                    <i class="bi bi-person" style="font-size: 4rem; color: #6c757d;"></i>
                                </div>
                            @endif
                            <h5>{{ $keyUser->full_name }}</h5>
                            <p class="text-muted">{{ $keyUser->user_code }}</p>
                            @if($keyUser->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </div>
                        
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">User Code</label>
                                        <p class="fw-bold">{{ $keyUser->user_code }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">BP Code</label>
                                        <p class="fw-bold">{{ $keyUser->bp_code }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Full Name</label>
                                        <p class="fw-bold">{{ $keyUser->full_name }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Email</label>
                                        <p class="fw-bold">{{ $keyUser->email_id }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Mobile Number</label>
                                        <p class="fw-bold">{{ $keyUser->mobile_no }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Date of Birth</label>
                                        <p class="fw-bold">{{ $keyUser->dob ? $keyUser->dob->format('d M, Y') : 'N/A' }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">City</label>
                                        <p class="fw-bold">{{ $keyUser->city ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">State</label>
                                        <p class="fw-bold">{{ $keyUser->state ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Country</label>
                                        <p class="fw-bold">{{ $keyUser->country ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Pincode</label>
                                        <p class="fw-bold">{{ $keyUser->pincode ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Aadhar Number</label>
                                        <p class="fw-bold">{{ $keyUser->aadhar_number ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Aadhar Photo</label>
                                        @if($keyUser->aadhar_photo)
                                            <div>
                                                <img src="{{ asset('storage/' . $keyUser->aadhar_photo) }}" 
                                                     alt="Aadhar Photo" 
                                                     class="img-thumbnail" 
                                                     width="100">
                                            </div>
                                        @else
                                            <p class="fw-bold">N/A</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Created At</label>
                                        <p class="fw-bold">{{ $keyUser->created_at->format('d M, Y h:i A') }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Updated At</label>
                                        <p class="fw-bold">{{ $keyUser->updated_at->format('d M, Y h:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection