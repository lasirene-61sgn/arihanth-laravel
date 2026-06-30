@extends('craftsman.layouts.app')

@section('title', 'Purchase Order Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Purchase Order Details</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <!-- <button type="button" class="btn btn-info dropdown-toggle text-white" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-share"></i> Share
                        </button> -->
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('craftsman.purchase-order.print', $purchaseOrder) }}" target="_blank">
                                    <i class="bi bi-file-earmark-pdf text-danger"></i> Generate PDF/Image
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="https://wa.me/?text=Purchase Order: {{ $purchaseOrder->purchase_order_code }}%0ADue Date: {{ $purchaseOrder->due_date ? $purchaseOrder->due_date->format('d M, Y') : 'N/A' }}%0AStatus: {{ ucfirst($purchaseOrder->craftsman_status) }}" target="_blank">
                                    <i class="bi bi-whatsapp text-success"></i> WhatsApp
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="mailto:?subject=Purchase Order: {{ $purchaseOrder->purchase_order_code }}&body=Code: {{ $purchaseOrder->purchase_order_code }}%0AStatus: {{ ucfirst($purchaseOrder->craftsman_status) }}">
                                    <i class="bi bi-envelope text-primary"></i> Email
                                </a>
                            </li>
                        </ul>
                        <a href="{{ route('craftsman.purchase-order.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Order: {{ $purchaseOrder->purchase_order_code }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-muted">PO Code:</label>
                            <p class="fs-5">{{ $purchaseOrder->purchase_order_code }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-muted">Due Date:</label>
                            <p class="fs-5">{{ $purchaseOrder->due_date ? $purchaseOrder->due_date->format('d M, Y') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-muted">Status:</label>
                            <p>
                                @php
                                    $statusColors = [
                                        'allocated' => 'primary',
                                        'in_process' => 'warning',
                                        'completed' => 'success',
                                        'rejected' => 'danger'
                                    ];
                                    $color = $statusColors[$purchaseOrder->craftsman_status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }} fs-6">{{ ucfirst(str_replace('_', ' ', $purchaseOrder->craftsman_status)) }}</span>
                            </p>
                        </div>
                        <div class="col-md-3 mb-3 text-md-end">
                            <label class="form-label fw-bold text-muted">Order Total:</label>
                            <h4 class="text-primary">{{ number_format(collect($itemsWithDetails)->sum('total'), 2) }}g</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    @if($itemsWithDetails && count($itemsWithDetails) > 0)
                        <form method="POST" action="{{ route('craftsman.purchase-order.process-items', $purchaseOrder) }}">
                            @csrf
                            @method('PUT')
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            @if($purchaseOrder->craftsman_status == 'in_process')
                                                <th width="30" class="text-center"><input type="checkbox" id="selectAllItems"></th>
                                            @endif
                                            <th>S.No</th>
                                            <th>Image</th>
                                            <th>Category / Product</th>
                                            <!-- <th>Design</th> -->
                                            <th>Weights (Grams & Qty)</th>
                                            <th>Row Total</th>
                                            <th>Size</th>
                                            @if($purchaseOrder->craftsman_status == 'allocated' || $purchaseOrder->craftsman_status == 'in_process')
                                                <th class="text-center">Action</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($itemsWithDetails as $index => $item)
                                            <tr>
                                                @if($purchaseOrder->craftsman_status == 'in_process')
                                                    <td class="text-center">
                                                        <input type="checkbox" name="selected_items[]" value="{{ $index }}" class="item-checkbox w-4 h-4 text-emerald-600 rounded" form="bulkCompleteItemsForm">
                                                    </td>
                                                @endif
                                                <td>{{ $index + 1 }}</td>
                                                <td class="text-center">
                                                    @php
                                                        $imagePath = !empty($item['image']) ? $item['image'] : null;
                                                        $imageSrc = null;

                                                        if ($imagePath) {
                                                            $imageSrc = str_contains($imagePath, 'images/') ? asset($imagePath) : asset('storage/' . $imagePath);
                                                        } else {
                                                            if(isset($item['design']) && !empty($item['design']->image)) {
                                                                $path = $item['design']->image;
                                                                $imageSrc = str_starts_with($path, 'storage/') || str_starts_with($path, 'images/') ? asset($path) : asset('storage/' . $path);
                                                            } elseif(isset($item['product']) && $item['product']->images && count($item['product']->images) > 0) {
                                                                $path = $item['product']->images[0]->path;
                                                                $imageSrc = str_starts_with($path, 'storage/') || str_starts_with($path, 'images/') ? asset($path) : asset('storage/' . $path);
                                                            }
                                                        }
                                                    @endphp
                                                    @if($imageSrc)
                                                        <img src="{{ $imageSrc }}" alt="Item" class="img-thumbnail" style="max-height: 80px; cursor: pointer;" onclick="showImagePreview(this.src)">
                                                    @else
                                                        <span class="text-muted small">No Image</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="fw-bold">{{ $item['category_name'] ?? 'N/A' }}</span><br>
                                                    <small class="text-muted">{{ $item['subcategory_name'] ?? '' }}</small>
                                                </td>
                                                <!-- <td>
                                                    <span class="badge bg-secondary">
                                                        {{ $item['product']->design_code ?? 'N/A' }}
                                                    </span>
                                                </td> -->
                                                <td>
                                                    @php
                                                        $grams = is_string($item['grams']) ? json_decode($item['grams'], true) : $item['grams'];
                                                        $qtys = is_string($item['quantity']) ? json_decode($item['quantity'], true) : $item['quantity'];
                                                    @endphp
                                                    @if(is_array($grams))
                                                        @foreach($grams as $i => $g)
                                                            <div class="border-bottom mb-1 pb-1">
                                                                {{ $g }}g × {{ $qtys[$i] ?? 1 }} nos
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        {{ $grams }}g × {{ $qtys }} nos
                                                    @endif
                                                </td>
                                                <td class="fw-bold">{{ number_format($item['total'], 2) }}g</td>
                                                <td class="fw-bold">{{ $item['item_size'] ?? 'N/A' }}</td>
                                                
                                                @if($purchaseOrder->craftsman_status == 'allocated')
                                                    <td>
                                                        <div class="d-flex flex-column gap-2 align-items-center">
                                                            <div class="form-check">
                                                                <input class="form-check-input item-checkbox accept-checkbox" type="checkbox" name="accepted_items[]" value="{{ $index }}" id="accept_{{ $index }}" data-index="{{ $index }}">
                                                                <label class="form-check-label text-success fw-bold" for="accept_{{ $index }}">Accept</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input item-checkbox reject-checkbox" type="checkbox" name="rejected_items[]" value="{{ $index }}" id="reject_{{ $index }}" data-index="{{ $index }}" >
                                                                <label class="form-check-label text-danger fw-bold" for="reject_{{ $index }}" >Reject</label>
                                                            </div>
                                                        </div>
                                                    </td>
                                                @endif

                                                @if($purchaseOrder->craftsman_status == 'in_process')
                                                    <td class="text-center">
                                                        <form method="POST" action="{{ route('craftsman.purchase-order.complete-items', $purchaseOrder) }}">
                                                            @csrf
                                                            <input type="hidden" name="selected_items[]" value="{{ $index }}">
                                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mark this item as completed?')">
                                                                <i class="bi bi-check-circle"></i> Complete Item
                                                            </button>
                                                        </form>
                                                    </td>
                                                @endif
                                            </tr>
                                            @if(!empty($item['item_notes']))
                                            <tr>
                                                    <td colspan="{{ ($purchaseOrder->craftsman_status == 'allocated' || $purchaseOrder->craftsman_status == 'in_process') ? 8 : 7 }}" class="bg-light">
                                                    <small><strong>Notes:</strong> {{ is_array($item['item_notes'] ?? null) ? implode(', ', $item['item_notes']) : ($item['item_notes'] ?? '') }}</small>
                                                </td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            @if($purchaseOrder->craftsman_status == 'in_process' && count($itemsWithDetails) > 0)
                                <div class="mt-3 d-flex justify-content-end">
                                    <form id="bulkCompleteItemsForm" method="POST" action="{{ route('craftsman.purchase-order.complete-items', $purchaseOrder) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Mark selected items as completed?')">
                                            <i class="bi bi-check-circle"></i> Complete Selected Items
                                        </button>
                                    </form>
                                </div>
                            @endif

                            @if($purchaseOrder->craftsman_status == 'allocated')
                                <div class="mt-3 d-flex gap-2 justify-content-end">
                                    <button type="submit" name="action" value="reject_all" class="btn btn-outline-danger" onclick="return confirm('Reject all items?')">Reject All</button>
                                    <button type="submit" name="action" value="accept_all" class="btn btn-success">Accept All Items</button>
                                    <button type="submit" name="action" value="process" class="btn btn-primary">Process Selected</button>
                                </div>
                            @endif
                        </form>
                    @else
                        <p class="text-muted">No items found.</p>
                    @endif
                </div>
            </div>

            @if(isset($rejectedItemsWithDetails) && count($rejectedItemsWithDetails) > 0)
            <div class="card mt-4 shadow-sm border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Rejected Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>S.No</th>
                                    <th>Image</th>
                                    <th>Category / Product</th>
                                    <th>Design</th>
                                    <th>Weights (Grams & Qty)</th>
                                    <th>Size</th>
                                    <th>Row Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rejectedItemsWithDetails as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="text-center">
                                            @php
                                                $rejImagePath = !empty($item['image']) ? $item['image'] : null;
                                                $rejImageSrc = null;

                                                if ($rejImagePath) {
                                                    $rejImageSrc = str_contains($rejImagePath, 'images/') ? asset($rejImagePath) : asset('storage/' . $rejImagePath);
                                                } else {
                                                    if(isset($item['design']) && !empty($item['design']->image)) {
                                                        $path = $item['design']->image;
                                                        $rejImageSrc = str_starts_with($path, 'storage/') || str_starts_with($path, 'images/') ? asset($path) : asset('storage/' . $path);
                                                    } elseif(isset($item['product']) && $item['product']->images && count($item['product']->images) > 0) {
                                                        $path = $item['product']->images[0]->path;
                                                        $rejImageSrc = str_starts_with($path, 'storage/') || str_starts_with($path, 'images/') ? asset($path) : asset('storage/' . $path);
                                                    }
                                                }
                                            @endphp
                                            @if($rejImageSrc)
                                                <img src="{{ $rejImageSrc }}" alt="Item" class="img-thumbnail" style="max-height: 80px; cursor: pointer;" onclick="showImagePreview(this.src)">
                                            @else
                                                <span class="text-muted small">No Image</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $item['category_name'] ?? 'N/A' }}</span><br>
                                            <small class="text-muted">{{ $item['subcategory_name'] ?? '' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $item['product']->design_code ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $grams = is_string($item['grams']) ? json_decode($item['grams'], true) : $item['grams'];
                                                $qtys = is_string($item['quantity']) ? json_decode($item['quantity'], true) : $item['quantity'];
                                            @endphp
                                            @if(is_array($grams))
                                                @foreach($grams as $i => $g)
                                                    <div class="border-bottom mb-1 pb-1">
                                                        {{ $g }}g × {{ $qtys[$i] ?? 1 }} nos
                                                    </div>
                                                @endforeach
                                            @else
                                                {{ $grams }}g × {{ $qtys }} nos
                                            @endif
                                        </td>
                                        <td class="fw-bold">{{ $item['item_size'] ?? 'N/A' }}</td>
                                        <td class="fw-bold text-danger">{{ number_format($item['total'], 2) }}g</td>
                                    </tr>
                                    @if(!empty($item['item_notes']))
                                    <tr>
                                        <td colspan="7" class="bg-light">
                                            <small class="text-danger"><strong>Unique Rejection Note:</strong> {{ is_array($item['item_notes'] ?? null) ? implode(', ', $item['item_notes']) : ($item['item_notes'] ?? '') }}</small>
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <div class="d-flex justify-content-between mt-4 mb-5">
                <a href="{{ route('craftsman.purchase-order.index') }}" class="btn btn-secondary">Back</a>
                
                @if($purchaseOrder->craftsman_status == 'in_process')
                    <form method="POST" action="{{ route('craftsman.purchase-order.complete', $purchaseOrder) }}">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg px-5" onclick="return confirm('Mark this entire order as completed?')">
                            <i class="bi bi-check2-all"></i> Mark as Completed
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div id="imagePreviewModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.8);" onclick="this.style.display='none'">
  <span style="position:absolute; top:20px; right:35px; color:#fff; font-size:40px; font-weight:bold; cursor:pointer;">&times;</span>
  <img id="previewImage" style="margin:auto; display:block; max-width:90%; max-height:90%; margin-top:5vh;">
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select All Items Logic
    const selectAll = document.getElementById('selectAllItems');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    // Prevent selecting both accept and reject for the same item
    const acceptCheckboxes = document.querySelectorAll('.accept-checkbox');
    const rejectCheckboxes = document.querySelectorAll('.reject-checkbox');
    
    // When an accept checkbox is checked, uncheck the corresponding reject checkbox
    acceptCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const index = this.dataset.index;
            const rejectCheckbox = document.querySelector(`.reject-checkbox[data-index="${index}"]`);
            if (this.checked && rejectCheckbox) {
                rejectCheckbox.checked = false;
            }
        });
    });
    
    // When a reject checkbox is checked, uncheck the corresponding accept checkbox
    rejectCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const index = this.dataset.index;
            const acceptCheckbox = document.querySelector(`.accept-checkbox[data-index="${index}"]`);
            if (this.checked && acceptCheckbox) {
                acceptCheckbox.checked = false;
            }
        });
    });
    
    // Form validation before submission
    const form = document.querySelector('form[method="POST"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            const acceptedItems = document.querySelectorAll('input[name="accepted_items[]"]:checked');
            const rejectedItems = document.querySelectorAll('input[name="rejected_items[]"]:checked');
            
            // Check for conflicts
            const conflicts = [];
            acceptedItems.forEach(acceptBox => {
                const index = acceptBox.value;
                const isRejected = Array.from(rejectedItems).some(rejectBox => rejectBox.value === index);
                if (isRejected) {
                    conflicts.push(index);
                }
            });
            
            if (conflicts.length > 0) {
                e.preventDefault();
                alert('Error: Some items are marked as both accepted and rejected. Please review your selections.');
                return false;
            }
            
            // Check if at least one item is selected
            if (acceptedItems.length === 0 && rejectedItems.length === 0) {
                e.preventDefault();
                alert('Please select at least one item to accept or reject.');
                return false;
            }
        });
    }
});

function showImagePreview(src) {
    document.getElementById('previewImage').src = src;
    document.getElementById('imagePreviewModal').style.display = 'block';
}
</script>
@endsection