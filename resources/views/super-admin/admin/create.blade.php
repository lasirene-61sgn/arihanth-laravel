@extends('super-admin.layouts.app')

@section('title', 'Create Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Create New Admin</h1>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Admin Details</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('super-admin.admin.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                                    id="full_name" name="full_name" value="{{ old('full_name') }}" required>
                                @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email_id" class="form-label">Email *</label>
                                <input type="email" class="form-control @error('email_id') is-invalid @enderror"
                                    id="email_id" name="email_id" value="{{ old('email_id') }}" required>
                                @error('email_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="mobile_no" class="form-label">Mobile Number *</label>
                                <input type="text"
                                    class="form-control @error('mobile_no') is-invalid @enderror"
                                    id="mobile_no" name="mobile_no" value="{{ old('mobile_no') }}"
                                    pattern="[0-9]{10}"
                                    maxlength="10"
                                    minlength="10"
                                    title="Please enter exactly 10 digits"
                                    required>
                                @error('mobile_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="user_code" class="form-label">User Code *</label>
                                <input type="text" class="form-control @error('user_code') is-invalid @enderror"
                                    id="user_code" name="user_code" value="{{ old('user_code') }}" required>
                                @error('user_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- <div class="col-md-6 mb-3">
                                <label for="dear" class="form-label">Dear Code</label>
                                <input type="text" class="form-control @error('dear') is-invalid @enderror" 
                                       id="dear" name="dear" value="{{ old('dear') }}">
                                @error('dear')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="bp_code" class="form-label">BP Code</label>
                                <input type="text" class="form-control @error('bp_code') is-invalid @enderror" 
                                       id="bp_code" name="bp_code" value="{{ old('bp_code') }}">
                                @error('bp_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> -->

                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" required>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control"
                                    id="password_confirmation" name="password_confirmation" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status">
                                    <option value="">Select Status</option>
                                    <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label d-flex justify-content-between">
                                    Category
                                    <a href="#" class="text-primary text-decoration-none small" data-bs-toggle="modal" data-bs-target="#addCategoryModal">+ Add New</a>
                                </label>
                                <select class="form-select @error('category') is-invalid @enderror"
                                    id="category" name="category">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->name }}" {{ old('category') == $cat->name ? 'selected' : '' }}>{{ ucfirst($cat->name) }}</option>
                                    @endforeach
                                </select>
                                @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="dob" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control @error('dob') is-invalid @enderror"
                                    id="dob" name="dob" value="{{ old('dob') }}">
                                @error('dob')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror"
                                    id="city" name="city" value="{{ old('city') }}">
                                @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control @error('state') is-invalid @enderror"
                                    id="state" name="state" value="{{ old('state') }}">
                                @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror"
                                    id="country" name="country" value="{{ old('country') }}">
                                @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="pincode" class="form-label">Pincode</label>
                                <input type="text" class="form-control @error('pincode') is-invalid @enderror"
                                    id="pincode" name="pincode" value="{{ old('pincode') }}">
                                @error('pincode')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="aadhar_number" class="form-label">Aadhar Number</label>
                                <input type="text" class="form-control @error('aadhar_number') is-invalid @enderror"
                                    id="aadhar_number" name="aadhar_number" value="{{ old('aadhar_number') }}"

                                    maxlength="12"
                                    minlength="12">
                                @error('aadhar_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Permissions Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Permissions</h5>
                                        <small class="text-muted">Select which sidebar items this admin can access</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach(App\Models\ProcessOwner::getAllPermissions() as $permission)
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="permissions[]" value="{{ $permission }}"
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

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('super-admin.admin.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Admin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label for="new_category_name" class="form-label">Category Name</label>
            <input type="text" class="form-control" id="new_category_name" placeholder="e.g. Marketing">
            <div class="invalid-feedback" id="new_category_error"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="saveCategoryBtn">Save Category</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('saveCategoryBtn').addEventListener('click', function() {
        const name = document.getElementById('new_category_name').value;
        const errorDiv = document.getElementById('new_category_error');
        const input = document.getElementById('new_category_name');
        
        if(!name) {
            input.classList.add('is-invalid');
            errorDiv.textContent = 'Category name is required';
            return;
        }
        
        input.classList.remove('is-invalid');
        
        fetch('{{ route("super-admin.admin.storeCategory") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name: name })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if(data.success) {
                // Add to dropdown
                const select = document.getElementById('category');
                const option = document.createElement('option');
                option.value = data.category.name;
                option.text = data.category.name.charAt(0).toUpperCase() + data.category.name.slice(1);
                option.selected = true;
                select.appendChild(option);
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addCategoryModal'));
                modal.hide();
                
                // Clear input
                input.value = '';
            } else {
                input.classList.add('is-invalid');
                errorDiv.textContent = data.message || 'Error saving category';
            }
        })
        .catch(error => {
            input.classList.add('is-invalid');
            errorDiv.textContent = error.message || 'Server error. Please try again.';
        });
    });
});
</script>
@endsection