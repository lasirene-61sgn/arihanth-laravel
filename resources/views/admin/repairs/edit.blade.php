@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="mb-0">Edit Repair / Sample Record (#{{ $repair->order_number }})</h2>
        <a href="{{ route('admin.repairs.index') }}" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.repairs.update', $repair->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.repairs._form', ['buttonText' => 'Update Record'])
            </form>
        </div>
    </div>
</div>
@endsection