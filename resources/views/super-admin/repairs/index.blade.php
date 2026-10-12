@extends('super-admin.layouts.app') {{-- Adjust to your layout layout name --}}

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="mb-0">Repair & Sample Records</h2>
        <a href="{{ route('super-admin.repairs.create') }}" class="btn btn-primary">Add New Record</a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Buyer</th>
                            <th>Product Name</th>
                            <th>Order No</th>
                            <th>Type</th>
                            <th>Delivered By</th>
                            <th>Received By</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($repairs as $repair)
                        <tr>
                            <td>{{ $repair->id }}</td>
                            <td>{{ $repair->date }}</td>
                            <td>{{ $repair->buyer->name ?? 'N/A' }}</td>
                            <td>{{ $repair->product_name }}</td>
                            <td>{{ $repair->order_number }}</td>
                            <td>
                                <span class="badge bg-{{ $repair->repair_or_sample === 'repair' ? 'warning' : 'info' }}">
                                    {{ ucfirst($repair->repair_or_sample) }}
                                </span>
                            </td>
                            <td>{{ $repair->item_delivered_by }}</td>
                            <td>{{ $repair->item_received_by }}</td>
                            <td class="text-center">
                                <!-- Updated with super-admin prefix -->
                                <a href="{{ route('super-admin.repairs.show', $repair->id) }}" class="btn btn-sm btn-info text-white">View</a>
                                <a href="{{ route('super-admin.repairs.edit', $repair->id) }}" class="btn btn-sm btn-warning">Edit</a>

                                <form action="{{ route('super-admin.repairs.destroy', $repair->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No repair or sample records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $repairs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection