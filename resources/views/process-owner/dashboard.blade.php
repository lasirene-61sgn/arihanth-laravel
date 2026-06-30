<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Owner Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">ERP System</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Welcome, {{ Auth::guard('process_owner')->user()->full_name }}
                </span>
                <form method="POST" action="{{ route('process-owner.logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h2>Process Owner Dashboard</h2>
                
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Your Profile</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th>User Code</th>
                                <td>{{ Auth::guard('process_owner')->user()->user_code }}</td>
                            </tr>
                            <tr>
                                <th>Full Name</th>
                                <td>{{ Auth::guard('process_owner')->user()->full_name }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ Auth::guard('process_owner')->user()->email_id }}</td>
                            </tr>
                            <tr>
                                <th>Mobile</th>
                                <td>{{ Auth::guard('process_owner')->user()->mobile_no }}</td>
                            </tr>
                            <tr>
                                <th>Role</th>
                                <td>{{ ucfirst(str_replace('_', ' ', Auth::guard('process_owner')->user()->role)) }}</td>
                            </tr>
                            <tr>
                                <th>City</th>
                                <td>{{ Auth::guard('process_owner')->user()->city ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>State</th>
                                <td>{{ Auth::guard('process_owner')->user()->state ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Country</th>
                                <td>{{ Auth::guard('process_owner')->user()->country ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Pincode</th>
                                <td>{{ Auth::guard('process_owner')->user()->pincode ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Aadhar Number</th>
                                <td>{{ Auth::guard('process_owner')->user()->aadhar_number ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if (Auth::guard('process_owner')->user()->role === 'process_owner')
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Create Super Admin</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('process-owner.create-super-admin') }}">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="sa_full_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="sa_full_name" name="full_name" value="{{ old('full_name') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="sa_email_id" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="sa_email_id" name="email_id" value="{{ old('email_id') }}" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="sa_mobile_no" class="form-label">Mobile Number *</label>
                                    <input type="text" class="form-control" id="sa_mobile_no" name="mobile_no" value="{{ old('mobile_no') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="sa_password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="sa_password" name="password" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="sa_password_confirmation" class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control" id="sa_password_confirmation" name="password_confirmation" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="sa_city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="sa_city" name="city" value="{{ old('city') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="sa_state" class="form-label">State</label>
                                    <input type="text" class="form-control" id="sa_state" name="state" value="{{ old('state') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="sa_country" class="form-label">Country</label>
                                    <input type="text" class="form-control" id="sa_country" name="country" value="{{ old('country') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="sa_pincode" class="form-label">Pincode</label>
                                    <input type="text" class="form-control" id="sa_pincode" name="pincode" value="{{ old('pincode') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="sa_aadhar_number" class="form-label">Aadhar Number</label>
                                    <input type="text" class="form-control" id="sa_aadhar_number" name="aadhar_number" value="{{ old('aadhar_number') }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-success">Create Super Admin</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                <div class="card shadow-sm mb-5">
                    <div class="card-header bg-dark text-white">
                        <h4 class="mb-0">Created Super Admins</h4>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-secondary">
                                <tr>
                                    <th>User Code</th>
                                    <th>Full Name</th>
                                    <th>Email ID</th>
                                    <th>Mobile No</th>
                                    <th>City / State</th>
                                    <th>Aadhar Number</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($superAdmins as $admin)
                                    <tr>
                                        <td><strong>{{ $admin->user_code }}</strong></td>
                                        <td>{{ $admin->full_name }}</td>
                                        <td>{{ $admin->email_id }}</td>
                                        <td>{{ $admin->mobile_no }}</td>
                                        <td>
                                            {{ $admin->city ?? '-' }} 
                                            {{ $admin->state ? ', ' . $admin->state : '' }}
                                        </td>
                                        <td>{{ $admin->aadhar_number ?? '-' }}</td>
                                        <td>
                                            @if($admin->status == 1)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No Super Admins created yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>