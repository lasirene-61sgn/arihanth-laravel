<?php
$base_dirs = [
    "d:/pulic_html/resources/views/super-admin/business-partner/craftsman-staff",
    "d:/pulic_html/resources/views/admin/business-partner/craftsman-staff"
];

foreach ($base_dirs as $base_dir) {
    if (!is_dir($base_dir)) {
        mkdir($base_dir, 0777, true);
    }
    
    $is_super = strpos($base_dir, 'super-admin') !== false;
    $layout = $is_super ? "super-admin.layouts.app" : "admin.layouts.app";
    $route_prefix = $is_super ? "super-admin" : "admin";
    
    $index = <<<EOT
@extends('$layout')
@section('content')
<div class="container-fluid">
    <h2>Craftsman Staff</h2>
    <a href="{{ route('$route_prefix.business-partner.craftsman-staff.create') }}" class="btn btn-primary mb-3">Add Staff</a>
    
    <table class="table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Craftsman</th>
                <th>Mobile</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach(\$staffs as \$staff)
            <tr>
                <td>{{ \$staff->staff_code }}</td>
                <td>{{ \$staff->name }}</td>
                <td>{{ \$staff->craftsman->name ?? 'N/A' }}</td>
                <td>{{ \$staff->mobile }}</td>
                <td>
                    @if(\$staff->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Frozen</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('$route_prefix.business-partner.craftsman-staff.edit', \$staff->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('$route_prefix.business-partner.craftsman-staff.destroy', \$staff->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ \$staffs->links() }}
</div>
@endsection
EOT;

    $create = <<<EOT
@extends('$layout')
@section('content')
<div class="container-fluid">
    <h2>Add Craftsman Staff</h2>
    <form action="{{ route('$route_prefix.business-partner.craftsman-staff.store') }}" method="POST">
        @csrf
        <div class="form-group mb-3">
            <label>Craftsman</label>
            <select name="craftsman_id" class="form-control" required>
                @foreach(\$craftsmen as \$craftsman)
                    <option value="{{ \$craftsman->id }}">{{ \$craftsman->name }} ({{ \$craftsman->craftman_code }})</option>
                @endforeach
            </select>
        </div>
        <div class="form-group mb-3">
            <label>Staff Code</label>
            <input type="text" name="staff_code" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Mobile</label>
            <input type="text" name="mobile" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
EOT;

    $edit = <<<EOT
@extends('$layout')
@section('content')
<div class="container-fluid">
    <h2>Edit Craftsman Staff</h2>
    <form action="{{ route('$route_prefix.business-partner.craftsman-staff.update', \$staff->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group mb-3">
            <label>Craftsman</label>
            <select name="craftsman_id" class="form-control" required>
                @foreach(\$craftsmen as \$craftsman)
                    <option value="{{ \$craftsman->id }}" {{ \$staff->craftsman_id == \$craftsman->id ? 'selected' : '' }}>{{ \$craftsman->name }} ({{ \$craftsman->craftman_code }})</option>
                @endforeach
            </select>
        </div>
        <div class="form-group mb-3">
            <label>Staff Code</label>
            <input type="text" name="staff_code" class="form-control" value="{{ \$staff->staff_code }}" required>
        </div>
        <div class="form-group mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ \$staff->name }}" required>
        </div>
        <div class="form-group mb-3">
            <label>Mobile</label>
            <input type="text" name="mobile" class="form-control" value="{{ \$staff->mobile }}" required>
        </div>
        <div class="form-group mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ \$staff->email }}" required>
        </div>
        <div class="form-group mb-3">
            <label>Password (leave blank to keep current)</label>
            <input type="password" name="password" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
EOT;

    file_put_contents("$base_dir/index.blade.php", $index);
    file_put_contents("$base_dir/create.blade.php", $create);
    file_put_contents("$base_dir/edit.blade.php", $edit);
    file_put_contents("$base_dir/show.blade.php", "@extends('$layout')\n@section('content')\n<div class='container'><h2>Staff Details</h2></div>\n@endsection");
}
