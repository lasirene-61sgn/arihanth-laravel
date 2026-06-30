@extends('buyer.layouts.app')

@section('title', 'My Repairs')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">My Repairs</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="{{ route('buyer.repairs.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Add New Repair
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success mt-3">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Product Name</th>
                                    <th>Weight</th>
                                    <th>Item Given To</th>
                                    <th>Status</th>
                                    <th>Rejection Reason</th>
                                    <th>Proof</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($repairs as $repair)
                                    <tr>
                                        <td>{{ $repair->id }}</td>
                                        <td>{{ \Carbon\Carbon::parse($repair->repair_date)->format('d M Y') }}</td>
                                        <td>{{ $repair->product_name }}</td>
                                        <td>{{ $repair->weight }}</td>
                                        <td>{{ $repair->item_given_to }}</td>
                                        <td>
                                            @if($repair->status == 'Pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif(in_array($repair->status, ['Accepted', 'Allocated']))
                                                <span class="badge bg-info">In Process</span>
                                            @elseif($repair->status == 'Rejected_by_Admin')
                                                <span class="badge bg-danger">Rejected</span>
                                            @elseif($repair->status == 'Craftsman_Completed')
                                                <span class="badge bg-info">In Process</span>
                                            @elseif($repair->status == 'Completed')
                                                <span class="badge bg-success">Completed - Awaiting Your Approval</span>
                                            @elseif($repair->status == 'Buyer_Accepted')
                                                <span class="badge bg-success">Accepted</span>
                                            @elseif($repair->status == 'Buyer_Rejected')
                                                <span class="badge bg-danger">Rejected by You</span>
                                            @elseif($repair->status == 'Craftsman_Rejected')
                                                <span class="badge bg-info">In Process</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $repair->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($repair->status == 'Rejected_by_Admin' && $repair->reject_reason)
                                                <span class="text-danger">{{ $repair->reject_reason }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($repair->image_proof)
                                                <a href="{{ asset($repair->image_proof) }}" target="_blank">View</a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            {{-- Accept / Reject buttons for completed repairs --}}
                                            @if($repair->status == 'Completed')
                                                <form action="{{ route('buyer.repairs.accept-completed', $repair->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Accept"><i class="bi bi-check-lg"></i> Accept</button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-danger" title="Reject" data-bs-toggle="modal" data-bs-target="#buyerRejectModal{{ $repair->id }}">
                                                    <i class="bi bi-x-lg"></i> Reject
                                                </button>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>

                                    {{-- Buyer Reject Modal --}}
                                    @if($repair->status == 'Completed')
                                    <div class="modal fade" id="buyerRejectModal{{ $repair->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('buyer.repairs.reject-completed', $repair->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Reject Repair #{{ $repair->id }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Rejection Reason</label>
                                                            <textarea name="reject_reason" class="form-control" rows="3" required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Reject</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No repairs found.</td>
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
    </div>
</div>
@endsection
