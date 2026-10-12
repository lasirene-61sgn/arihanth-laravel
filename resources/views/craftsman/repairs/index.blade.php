@extends('craftsman.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <h2 class="my-4">My Assigned Repairs & Samples</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Craftsman Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? 'assigned') === 'assigned' ? 'active fw-bold' : '' }}" href="{{ route('craftsman.repairs.index', ['tab' => 'assigned']) }}">New Assigned</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? '') === 'in_process' ? 'active fw-bold' : '' }}" href="{{ route('craftsman.repairs.index', ['tab' => 'in_process']) }}">In Process</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? '') === 'pending_approval' ? 'active fw-bold' : '' }}" href="{{ route('craftsman.repairs.index', ['tab' => 'pending_approval']) }}">Pending Superadmin Approval</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? '') === 'completed' ? 'active fw-bold' : '' }}" href="{{ route('craftsman.repairs.index', ['tab' => 'completed']) }}">Completed</a>
        </li>
    </ul>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Buyer</th>
                        <th>Product Name</th>
                        <th>Order No</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
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
                            <td><span class="badge bg-info text-dark">{{ ucfirst($repair->repair_or_sample) }}</span></td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $repair->craftsman_status)) }}</span>
                            </td>
                            <td class="text-center">
                                @if($repair->craftsman_status === 'pending_acceptance')
                                    <form action="{{ route('craftsman.repairs.accept', $repair->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success">Accept Order</button>
                                    </form>
                                @elseif($repair->craftsman_status === 'in_process')
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#completeModal{{ $repair->id }}">
                                        Mark as Completed
                                    </button>
                                @else
                                    <span class="text-muted">No Action Needed</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Craftsman Complete Modal -->
                        @if($repair->craftsman_status === 'in_process')
                        <div class="modal fade" id="completeModal{{ $repair->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('craftsman.repairs.complete', $repair->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Complete Order #{{ $repair->order_number }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <div class="mb-3">
                                                <label class="form-label">Item Returned By <span class="text-danger">*</span></label>
                                                <input type="text" name="item_returned_by" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Item Collected By <span class="text-danger">*</span></label>
                                                <input type="text" name="item_collected_by" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-success">Submit for Approval</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $repairs->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection