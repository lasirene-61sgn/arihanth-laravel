@extends('super-admin.layouts.app')

@section('title', 'View Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">View Admin</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="{{ route('super-admin.admin.edit', $admin) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit Admin
                        </a>
                        <a href="{{ route('super-admin.admin.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>Admin Details</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">User Code</label>
                                    <p>{{ $admin->user_code }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">BP Code</label>
                                    <p>{{ $admin->bp_code ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Full Name</label>
                                    <p>{{ $admin->full_name }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Email</label>
                                    <p>{{ $admin->email_id }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Mobile Number</label>
                                    <p>{{ $admin->mobile_no }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Status</label>
                                    <p>
                                        @if($admin->status == 1)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Date of Birth</label>
                                    <p>{{ $admin->dob ? $admin->dob->format('d M, Y') : 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">City</label>
                                    <p>{{ $admin->city ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">State</label>
                                    <p>{{ $admin->state ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Country</label>
                                    <p>{{ $admin->country ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Pincode</label>
                                    <p>{{ $admin->pincode ?? 'N/A' }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted">Aadhar Number</label>
                                    <p>{{ $admin->aadhar_number ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h4>Profile Picture</h4>
                        </div>
                        <div class="card-body text-center">
                            @if($admin->profile_picture)
                                <img src="{{ asset('storage/' . $admin->profile_picture) }}" 
                                     alt="Profile Picture" class="img-fluid rounded" style="max-height: 200px;">
                            @else
                                <div class="bg-light p-5 rounded">
                                    <i class="bi bi-person-circle" style="font-size: 4rem;"></i>
                                    <p class="mt-2">No profile picture</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h4>Aadhar Document</h4>
                        </div>
                        <div class="card-body text-center">
                            @if($admin->aadhar_photo)
                                <img src="{{ asset('storage/' . $admin->aadhar_photo) }}" 
                                     alt="Aadhar Photo" class="img-fluid rounded" style="max-height: 200px;">
                            @else
                                <div class="bg-light p-5 rounded">
                                    <i class="bi bi-file-earmark-text" style="font-size: 4rem;"></i>
                                    <p class="mt-2">No Aadhar document</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection