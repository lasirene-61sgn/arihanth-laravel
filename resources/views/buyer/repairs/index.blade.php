@extends('buyer.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="mb-0">My Repairs & Samples</h2>
        <a href="{{ route('buyer.repairs.create') }}" class="btn btn-primary">Add New Record</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Buyer Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? 'active') === 'active' ? 'active fw-bold' : '' }}" href="{{ route('buyer.repairs.index', ['tab' => 'active']) }}">In Progress / Active</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? '') === 'approval' ? 'active fw-bold' : '' }}" href="{{ route('buyer.repairs.index', ['tab' => 'approval']) }}">Pending Acceptance</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? '') === 'completed' ? 'active fw-bold' : '' }}" href="{{ route('buyer.repairs.index', ['tab' => 'completed']) }}">Completed</a>
        </li>
    </ul>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Product Name</th>
                            <th>Order No</th>
                            <th>Type</th>
                            <th>Status / Tracking</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($repairs as $repair)
                            <tr>
                                <td>{{ $repair->id }}</td>
                                <td>{{ $repair->date }}</td>
                                <td>{{ $repair->product_name }}</td>
                                <td>{{ $repair->order_number }}</td>
                                <td>
                                    <span class="badge bg-{{ $repair->repair_or_sample === 'repair' ? 'warning text-dark' : 'info text-dark' }}">
                                        {{ ucfirst($repair->repair_or_sample) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $repair->status === 'completed' ? 'success' : 'primary' }}">
                                        {{ ucfirst($repair->status) }}
                                    </span>

                                    @if($repair->item_sent_by)
                                        <div class="mt-1"><small class="text-muted"><strong>Item Sent By:</strong> {{ $repair->item_sent_by }}</small></div>
                                    @endif

                                    @if($repair->item_accepted_by)
                                        <div class="mt-1"><small class="text-muted"><strong>Item Accepted By:</strong> {{ $repair->item_accepted_by }}</small></div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <!-- Final Step Action: Buyer Acceptance -->
                                    @if(!empty($repair->item_sent_by) && $repair->status !== 'completed')
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#acceptModal{{ $repair->id }}">
                                            Accept & Complete
                                        </button>
                                    @endif

                                    <!-- Editable & Deletable Only When Pending -->
                                    @if($repair->status === 'pending')
                                        <a href="{{ route('buyer.repairs.edit', $repair->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                        
                                        <form action="{{ route('buyer.repairs.destroy', $repair->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this record?');">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>

                            <!-- Buyer Acceptance Modal -->
                            @if(!empty($repair->item_sent_by) && $repair->status !== 'completed')
                            <div class="modal fade" id="acceptModal{{ $repair->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('buyer.repairs.accept-completion', $repair->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Accept Order #{{ $repair->order_number }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <p class="text-muted small">Item Sent By: <strong>{{ $repair->item_sent_by }}</strong></p>
                                                <div class="mb-3">
                                                    <label class="form-label">Item Accepted By <span class="text-danger">*</span></label>
                                                    <input type="text" name="item_accepted_by" class="form-control" placeholder="Enter your name..." required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-success">Confirm & Complete</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $repairs->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection