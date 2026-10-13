@extends('super-admin.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="mb-0">Edit Repair / Sample Record</h2>
        <a href="{{ route('super-admin.repairs.index') }}" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('super-admin.repairs.update', $repair->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Repair Image</label>
                    <input type="file" name="repair_image" class="form-control" accept="image/*">
                    @if(isset($repair) && $repair->repair_image)
                    <div class="mt-2">
                        <p class="text-muted small mb-1">Current Image:</p>
                        <a href="{{ asset('storage/' . $repair->repair_image) }}" target="_blank">
                            <img src="{{ asset('storage/' . $repair->repair_image) }}" alt="Repair Image" width="100" class="img-thumbnail">
                        </a>
                    </div>
                    @endif
                </div>
                @include('super-admin.repairs._form', ['buttonText' => 'Update Record'])
            </form>
        </div>
    </div>
</div>
@endsection