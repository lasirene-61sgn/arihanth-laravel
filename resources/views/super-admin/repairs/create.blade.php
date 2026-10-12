@extends('super-admin.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="mb-0">Create Repair / Sample Record</h2>
        <a href="{{ route('super-admin.repairs.index') }}" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('super-admin.repairs.store') }}" method="POST">
                @csrf
                @include('super-admin.repairs._form', ['buttonText' => 'Save Record'])
            </form>
        </div>
    </div>
</div>
@endsection