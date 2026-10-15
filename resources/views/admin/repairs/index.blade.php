@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <h2 class="mb-0">Repair & Sample Management</h2>
        <a href="{{ route('admin.repairs.create') }}" class="btn btn-primary">Add New Record</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item"><a class="nav-link {{ ($tab ?? 'new') === 'new' ? 'active fw-bold' : '' }}" href="{{ route('admin.repairs.index', ['tab' => 'new']) }}">New Orders (Pending)</a></li>
        <li class="nav-item"><a class="nav-link {{ ($tab ?? '') === 'accepted' ? 'active fw-bold' : '' }}" href="{{ route('admin.repairs.index', ['tab' => 'accepted']) }}">Accepted</a></li>
        <li class="nav-item"><a class="nav-link {{ ($tab ?? '') === 'allocated' ? 'active fw-bold' : '' }}" href="{{ route('admin.repairs.index', ['tab' => 'allocated']) }}">Allocated to Craftman</a></li>
        <li class="nav-item"><a class="nav-link {{ ($tab ?? '') === 'craftsman_approval' ? 'active fw-bold' : '' }}" href="{{ route('admin.repairs.index', ['tab' => 'craftsman_approval']) }}">Craftsman Approval</a></li>
        <li class="nav-item"><a class="nav-link {{ ($tab ?? '') === 'approval' ? 'active fw-bold' : '' }}" href="{{ route('admin.repairs.index', ['tab' => 'approval']) }}">Approval</a></li>
        <li class="nav-item"><a class="nav-link {{ ($tab ?? '') === 'buyer_approval' ? 'active fw-bold' : '' }}" href="{{ route('admin.repairs.index', ['tab' => 'buyer_approval']) }}">Buyer Approval</a></li>
        <li class="nav-item"><a class="nav-link {{ ($tab ?? '') === 'completed' ? 'active fw-bold' : '' }}" href="{{ route('admin.repairs.index', ['tab' => 'completed']) }}">Completed</a></li>
        <li class="nav-item"><a class="nav-link {{ ($tab ?? '') === 'rejected' ? 'active fw-bold' : '' }}" href="{{ route('admin.repairs.index', ['tab' => 'rejected']) }}">Rejected</a></li>
    </ul>

    <!-- Bulk Complete Form Wrapper -->
    <form action="{{ route('admin.repairs.bulk-complete') }}" method="POST" id="bulkCompleteForm">
        @csrf
        
        @if(($tab ?? '') === 'allocated')
            <div class="mb-3">
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#bulkCompleteModal">
                    Bulk Complete Selected
                </button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                @if(($tab ?? '') === 'allocated')
                                    <th width="30" class="text-center">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                @endif
                                <th>ID</th>
                                <th>Date</th>
                                <th>Image</th>
                                <th>Buyer</th>
                                <th>Product Name</th>
                                <th>Order No</th>
                                <th>Type</th>
                                <th>Status / Details</th>
                                <th>Assigned Craftman</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($repairs as $repair)
                                <tr>
                                    @if(($tab ?? '') === 'allocated')
                                        <td class="text-center">
                                            <input type="checkbox" name="repair_ids[]" value="{{ $repair->id }}" class="form-check-input repair-checkbox">
                                        </td>
                                    @endif
                                    <td>{{ $repair->id }}</td>
                                    <td>{{ $repair->date }}</td>
                                    <td class="text-center">
                                        @if($repair->repair_image)
                                            <a href="{{ asset('storage/' . $repair->repair_image) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $repair->repair_image) }}" alt="Repair Image" width="50" class="img-thumbnail">
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $repair->buyer->name ?? 'N/A' }}</td>
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
                                        @if($repair->craftsman_status)
                                            <br><small class="text-muted">({{ str_replace('_', ' ', $repair->craftsman_status) }})</small>
                                        @endif

                                        @if(($tab ?? '') === 'craftsman_approval' || $repair->craftsman_status === 'pending_approval')
                                            <div class="mt-2 p-2 bg-light border rounded">
                                                <small class="d-block text-secondary"><strong>Returned By:</strong> {{ $repair->item_returned_by ?? '-' }}</small>
                                                <small class="d-block text-secondary mt-1"><strong>Collected By:</strong> {{ $repair->item_collected_by ?? '-' }}</small>
                                            </div>
                                        @endif

                                        @if(($tab ?? '') === 'approval')
                                            <div class="mt-2 p-2 bg-light border rounded">
                                                <small class="d-block text-secondary"><strong>Returned By:</strong> {{ $repair->item_returned_by ?? '-' }}</small>
                                                <small class="d-block text-secondary mt-1"><strong>Collected By:</strong> {{ $repair->item_collected_by ?? '-' }}</small>
                                            </div>
                                        @endif

                                        @if(($tab ?? '') === 'buyer_approval')
                                            <div class="mt-2 p-2 bg-light border rounded">
                                                <small class="d-block text-secondary"><strong>Item Sent By:</strong> {{ $repair->item_sent_by ?? '-' }}</small>
                                            </div>
                                        @endif

                                        @if(($tab ?? '') === 'completed')
                                            <div class="mt-2 p-2 bg-light border rounded">
                                                <small class="d-block text-secondary"><strong>Sent By:</strong> {{ $repair->item_sent_by ?? '-' }}</small>
                                                <small class="d-block text-secondary mt-1"><strong>Accepted By:</strong> {{ $repair->item_accepted_by ?? '-' }}</small>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($repair->craftman)
                                            <strong>{{ $repair->craftman->craftman_code }}</strong> - {{ $repair->craftman->business_name }}
                                        @else
                                            <span class="text-muted">Not Allocated</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.repairs.show', $repair->id) }}" class="btn btn-sm btn-info text-white">View</a>
                                        
                                        @if($repair->status === 'pending')
                                            <button type="submit" form="acceptForm{{ $repair->id }}" class="btn btn-sm btn-success">Accept</button>
                                            <button type="submit" form="rejectForm{{ $repair->id }}" class="btn btn-sm btn-danger" onclick="return confirm('Reject this order?');">Reject</button>
                                        @endif

                                        @if($repair->status === 'accepted')
                                            <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#allocateModal{{ $repair->id }}">Allocate</button>
                                        @endif

                                        @if($repair->status === 'allocated' && $repair->craftsman_status !== 'pending_approval')
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#completeModal{{ $repair->id }}">Complete</button>
                                        @endif

                                        @if($repair->craftsman_status === 'pending_approval')
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $repair->id }}">Approve</button>
                                        @endif

                                        @if($repair->craftsman_status === 'approved_by_superadmin' && empty($repair->item_sent_by))
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#sentByModal{{ $repair->id }}">Fill Item Sent By</button>
                                        @endif

                                        @if(!empty($repair->item_sent_by) && $repair->status !== 'completed')
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#buyerCompleteModal{{ $repair->id }}">Complete</button>
                                        @endif

                                        <a href="{{ route('admin.repairs.edit', $repair->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <button type="submit" form="deleteForm{{ $repair->id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this record?');">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">No records found for this tab.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $repairs->withQueryString()->links() }}
                </div>
            </div>
        </div>

        <!-- Bulk Complete Modal -->
        @if(($tab ?? '') === 'allocated')
        <div class="modal fade" id="bulkCompleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bulk Complete Selected Orders</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-start">
                        <div class="mb-3">
                            <label class="form-label">Item Returned By <span class="text-danger">*</span></label>
                            <input type="text" name="item_returned_by" class="form-control" list="returnedList" placeholder="Select or type name..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Item Collected By <span class="text-danger">*</span></label>
                            <input type="text" name="item_collected_by" class="form-control" list="collectedList" placeholder="Select or type name..." required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Confirm Bulk Complete</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </form>

    <!-- Isolated Action Forms & Modals -->
    @foreach($repairs as $repair)
        @if($repair->status === 'pending')
            <form id="acceptForm{{ $repair->id }}" action="{{ route('admin.repairs.accept', $repair->id) }}" method="POST" style="display: none;">
                @csrf @method('PATCH')
            </form>
            <form id="rejectForm{{ $repair->id }}" action="{{ route('admin.repairs.reject', $repair->id) }}" method="POST" style="display: none;">
                @csrf @method('PATCH')
            </form>
        @endif

        <form id="deleteForm{{ $repair->id }}" action="{{ route('admin.repairs.destroy', $repair->id) }}" method="POST" style="display: none;">
            @csrf @method('DELETE')
        </form>

        <!-- Allocation Modal -->
        @if($repair->status === 'accepted')
        <div class="modal fade" id="allocateModal{{ $repair->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.repairs.allocate', $repair->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Allocate to Craftman (Order #{{ $repair->order_number }})</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-start">
                            <div class="mb-3">
                                <label class="form-label">Select Craftman <span class="text-danger">*</span></label>
                                <select name="craftman_code" class="form-select" required>
                                    <option value="">Choose Craftman...</option>
                                    @foreach($craftsmen as $craftman)
                                        <option value="{{ $craftman->craftman_code }}">
                                            {{ $craftman->craftman_code }} - {{ $craftman->business_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Confirm Allocation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Single Complete Modal (Allocated Tab -> Craftsman Approval) -->
        @if($repair->status === 'allocated' && $repair->craftsman_status !== 'pending_approval')
        <div class="modal fade" id="completeModal{{ $repair->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.repairs.complete', $repair->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Complete Order #{{ $repair->order_number }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-start">
                            <div class="mb-3">
                                <label class="form-label">Item Returned By <span class="text-danger">*</span></label>
                                <input type="text" name="item_returned_by" class="form-control" list="returnedList" placeholder="Select or type name..." required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Item Collected By <span class="text-danger">*</span></label>
                                <input type="text" name="item_collected_by" class="form-control" list="collectedList" placeholder="Select or type name..." required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Weight</label>
                                <input type="number" step="0.01" name="weight" class="form-control" value="{{ $repair->weight }}" placeholder="Enter weight...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Craftsman Voucher Image</label>
                                <input type="file" name="craftsman_voucher_image" class="form-control" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Craftsman Completion Proof Image</label>
                                <input type="file" name="craftsman_completion_proof_image" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Mark as Completed</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Craftsman Approval Modal -->
        @if($repair->craftsman_status === 'pending_approval')
        <div class="modal fade" id="approveModal{{ $repair->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.repairs.approve-completion', $repair->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Approve Craftsman Completion (#{{ $repair->order_number }})</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-start">
                            <div class="mb-3">
                                <label class="form-label">Item Returned By (Editable) <span class="text-danger">*</span></label>
                                <input type="text" name="item_returned_by" class="form-control" value="{{ old('item_returned_by', $repair->item_returned_by) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Item Collected By (Editable) <span class="text-danger">*</span></label>
                                <input type="text" name="item_collected_by" class="form-control" value="{{ old('item_collected_by', $repair->item_collected_by) }}" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Approve & Move to Approval Tab</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Approval Tab Modal (Fill Item Sent By) -->
        @if($repair->craftsman_status === 'approved_by_superadmin' && empty($repair->item_sent_by))
        <div class="modal fade" id="sentByModal{{ $repair->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.repairs.store-item-sent-by', $repair->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Fill Item Sent By (#{{ $repair->order_number }})</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-start">
                            <div class="mb-3">
                                <label class="form-label">Item Sent By <span class="text-danger">*</span></label>
                                <input type="text" name="item_sent_by" class="form-control" list="sentList" placeholder="Select or type name..." required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save & Move to Buyer Approval</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Buyer Approval Completion Modal (Includes Buyer Voucher & Proof Images) -->
        @if(!empty($repair->item_sent_by) && $repair->status !== 'completed')
        <div class="modal fade" id="buyerCompleteModal{{ $repair->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.repairs.buyer-complete', $repair->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Buyer Approval Completion (#{{ $repair->order_number }})</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-start">
                            <div class="mb-3">
                                <label class="form-label">Item Sent By (Editable) <span class="text-danger">*</span></label>
                                <input type="text" name="item_sent_by" class="form-control" list="sentList" value="{{ old('item_sent_by', $repair->item_sent_by) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Item Accepted By <span class="text-danger">*</span></label>
                                <input type="text" name="item_accepted_by" class="form-control" list="acceptedList" placeholder="Select or type name..." required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Buyer Voucher Image</label>
                                <input type="file" name="buyer_voucher_image" class="form-control" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Buyer Completion Proof Image</label>
                                <input type="file" name="buyer_completion_proof_image" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Mark as Completed</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @endforeach
</div>

<!-- Shared Datalists -->
<datalist id="returnedList">
    @foreach(($returnedNames ?? []) as $name)
        <option value="{{ $name }}">
    @endforeach
</datalist>

<datalist id="collectedList">
    @foreach(($collectedNames ?? []) as $name)
        <option value="{{ $name }}">
    @endforeach
</datalist>

<datalist id="sentList">
    @foreach(($sentNames ?? []) as $name)
        <option value="{{ $name }}">
    @endforeach
</datalist>

<datalist id="acceptedList">
    @foreach(($acceptedNames ?? []) as $name)
        <option value="{{ $name }}">
    @endforeach
</datalist>

<script>
    document.getElementById('selectAll')?.addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.repair-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>
@endsection