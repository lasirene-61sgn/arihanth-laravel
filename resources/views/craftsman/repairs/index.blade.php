@extends('craftsman.layouts.app')

@section('title', 'Allocated Repairs')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Allocated Repairs</h1>
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
                                    <!--<th>BP Code</th>-->
                                    <th>Product Name</th>
                                    <th>Weight</th>
                                    <th>Repair Details</th>
                                    <th>Order Number</th>
                                    <th>Repairs</th>
                                    <th>Notes</th>
                                    <th>Status</th>
                                    <th>Proof</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($repairs as $repair)
                                    <tr>
                                        <td>{{ $repair->id }}</td>
                                        <td>{{ \Carbon\Carbon::parse($repair->repair_date)->format('d M Y') }}</td>
                                        <!--<td>{{ $repair->buyer ? $repair->buyer->bp_code : 'N/A' }}</td>-->
                                        <td>{{ $repair->product_name }}</td>
                                        <td>{{ $repair->weight }}</td>
                                        <td>
                                            @if($repair->allocation_notes)
                                                <div class="mb-2"><strong>Notes:</strong> {{ $repair->allocation_notes }}</div>
                                            @endif
                                            {{ Str::limit($repair->repair_details, 50) }}
                                        </td>
                                        <td>{{ $repair->order_no }}</td>
                                        <td>{{ $repair->repair }}</td>
                                        <td>{{$repair->notes}}</td>
                                        <td>
                                            @if($repair->craftsman_status == 'Pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif($repair->craftsman_status == 'Accepted')
                                                <span class="badge bg-info">In Process</span>
                                            @elseif($repair->craftsman_status == 'Completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($repair->craftsman_status == 'Rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $repair->craftsman_status }}</span>
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
                                            @if($repair->craftsman_status == 'Pending')
                                                {{-- Accept --}}
                                                <form action="{{ route('craftsman.repairs.accept', $repair->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Accept"><i class="bi bi-check-lg"></i> Accept</button>
                                                </form>
                                                {{-- Reject --}}
                                                <form action="{{ route('craftsman.repairs.reject', $repair->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Reject"><i class="bi bi-x-lg"></i> Reject</button>
                                                </form>
                                            @elseif($repair->craftsman_status == 'Accepted')
                                                {{-- Complete --}}
                                                <form action="{{ route('craftsman.repairs.complete', $repair->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Mark Complete"><i class="bi bi-check-circle"></i> Complete</button>
                                                </form>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No allocated repairs found.</td>
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
