@extends('super-admin.layouts.app')

@section('title', 'Purchase Order Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Purchase Order Details</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-share"></i> Share
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('super-admin.purchase-order.print', $purchaseOrder) }}" target="_blank">
                                    <i class="bi bi-file-earmark-pdf text-danger"></i> Generate PDF/Image
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="https://wa.me/?text=Purchase Order: {{ $purchaseOrder->purchase_order_code }}" target="_blank">
                                    <i class="bi bi-whatsapp text-success"></i> WhatsApp
                                </a>
                            </li>
                        </ul>
                        <a href="{{ route('super-admin.purchase-order.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-white">
                    <h4 class="mb-0">{{ $purchaseOrder->purchase_order_code }}</h4>
                    @if(!in_array($purchaseOrder->status, ['in_process', 'overdue', 'completed', 'for_approval']))
                    <div class="btn-group me-2">
                        <a href="{{ route('super-admin.purchase-order.edit', $purchaseOrder) }}" class="btn btn-primary btn-sm">Edit Purchase Order</a>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small">Order Code</label>
                            <p class="mb-0">{{ $purchaseOrder->purchase_order_code }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small">Due Date</label>
                            <p class="mb-0">{{ $purchaseOrder->due_date ? $purchaseOrder->due_date->format('d M, Y') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small">Status</label>
                            <p class="mb-0">
                                <span class="badge bg-primary">{{ ucfirst($purchaseOrder->status) }}</span>
                            </p>
                        </div>
                        <div class="col-md-3 mt-3">
                            <label class="form-label fw-bold text-muted small">Created At</label>
                            <p class="mb-0">{{ $purchaseOrder->created_at->format('d M, Y H:i') }}</p>
                        </div>
                        @if($purchaseOrder->creator_details['name'] !== 'System' && $purchaseOrder->creator_details['name'] !== 'N/A')
                        <div class="col-md-3 mt-3">
                            <label class="form-label fw-bold text-muted small">Created By</label>
                            @php $creator = $purchaseOrder->creator_details; @endphp
                            <p class="mb-0">{{ $creator['name'] }} <span class="badge bg-secondary">{{ $creator['type'] }}</span></p>
                        </div>
                        @endif
                        @if($purchaseOrder->allocator_details['name'] !== 'Unknown User' && $purchaseOrder->allocator_details['name'] !== 'N/A')
                        <div class="col-md-3 mt-3">
                            <label class="form-label fw-bold text-muted small">Allocated By</label>
                            @php $allocator = $purchaseOrder->allocator_details; @endphp
                            <p class="mb-0">{{ $allocator['name'] }} <span class="badge bg-secondary">{{ $allocator['type'] }}</span></p>
                        </div>
                        @endif
                        @if($purchaseOrder->approver_details['name'] !== 'Unknown User' && $purchaseOrder->approver_details['name'] !== 'N/A')
                        <div class="col-md-3 mt-3">
                            <label class="form-label fw-bold text-muted small">Approved By</label>
                            @php $approver = $purchaseOrder->approver_details; @endphp
                            <p class="mb-0">{{ $approver['name'] }} <span class="badge bg-secondary">{{ $approver['type'] }}</span></p>
                        </div>
                        @endif
                        <div class="col-md-3 mt-3">
                            <label class="form-label fw-bold text-muted small">Completed Date</label>
                            <p class="mb-0">
                                @if(strtolower($purchaseOrder->status) === 'completed')
                                    <span class="text-success fw-bold">{{ $purchaseOrder->updated_at ? $purchaseOrder->updated_at->format('d M, Y H:i') : 'N/A' }}</span>
                                @else
                                    <span class="text-muted">Not Completed</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">S.No</th>
                                    <th>Category</th>
                                    <th>Product</th>
                                    <th>Design Code</th>
                                    <th>Grams & Quantity</th>
                                    <th class="text-end">Total Weight</th>
                                    <th class="text-center">Item Image</th>
                                    <th>Notes</th>
                                    @if($purchaseOrder->status == 'in_process' || $purchaseOrder->craftsman_status == 'allocated')
                                        <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @php $grandTotalWeight = 0; @endphp
                                @foreach($itemsWithDetails as $index => $item)
                                @php $grandTotalWeight += (float)($item['total'] ?? 0); @endphp
                                <tr>
                                    <td>
                                        @if($purchaseOrder->status == 'in_process' || $purchaseOrder->craftsman_status == 'allocated')
                                            <input type="checkbox" form="bulkCompleteItemsForm" name="selected_items[]" value="{{ $index }}" class="me-1">
                                        @endif
                                        {{ $index + 1 }}
                                    </td>
                                    <td>
                                        <strong>{{ $item['category_name'] ?? ($item['category'] ?? 'N/A') }}</strong>
                                    </td>
                                    <td>
                                        @if($item['product'])
                                            <span class="fw-bold d-block">{{ $item['product']->product_name ?? 'Unknown' }}</span>
                                            <small class="text-blue-600">Sub: {{ $item['product']->subcategory->name ?? 'N/A' }}</small>
                                        @else
                                            <span class="fw-bold d-block">{{ $item['manual_product'] ?? 'Unknown' }}</span>
                                            <small class="text-blue-600">Sub: {{ $item['subcategory_name'] ?? 'N/A' }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item['product']->design_code ?? ($item['design_code'] ?? 'N/A') }}</td>
                                    <td>
                                        @if(isset($item['grams']) && is_array($item['grams']))
                                        @foreach($item['grams'] as $i => $gram)
                                        <div>{{ $gram }}g × {{ $item['quantity'][$i] ?? 1 }} = <strong>{{ number_format($item['individual_totals'][$i], 2) }}g</strong></div>
                                        @endforeach
                                        @else
                                        {{ $item['grams'] ?? 0 }}g × {{ $item['quantity'] ?? 0 }} = <strong>{{ number_format(($item['grams'] ?? 0) * ($item['quantity'] ?? 0), 2) }}g</strong>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($item['total'] ?? 0, 2) }}g</td>                                    <td class="text-center">
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
                                            <img src="{{ $imageSrc }}" class="img-thumbnail" style="max-height: 80px; cursor: pointer;" onclick="showImagePreview(this.src)" alt="Item Image">
                                        @else
                                            <span class="text-muted small">No Image</span>
                                        @endif
                                    </td>

                                    <td><small>{{ $item['item_notes'] ?? '-' }}</small></td>
                                    @if($purchaseOrder->status == 'in_process' || $purchaseOrder->craftsman_status == 'allocated')
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('super-admin.purchase-order.complete-items', $purchaseOrder) }}">
                                                @csrf
                                                <input type="hidden" name="selected_items[]" value="{{ $index }}">
                                                <button type="submit" class="btn btn-sm btn-success fw-bold text-white d-inline-flex align-items-center" onclick="return confirm('Mark this item as completed?')">
                                                    <i class="bi bi-check-circle me-1"></i> Complete
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-end fw-bold">Grand Total Weight:</td>
                                    <td class="text-end fw-bold fs-5 text-primary">{{ number_format($grandTotalWeight, 2) }}g</td>
                                    <td colspan="{{ ($purchaseOrder->status == 'in_process' || $purchaseOrder->craftsman_status == 'allocated') ? 3 : 2 }}"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if(($purchaseOrder->status == 'in_process' || $purchaseOrder->craftsman_status == 'allocated') && count($itemsWithDetails) > 0)
                        <div class="mt-3 d-flex justify-content-end">
                            <form id="bulkCompleteItemsForm" method="POST" action="{{ route('super-admin.purchase-order.complete-items', $purchaseOrder) }}">
                                @csrf
                                <button type="submit" class="btn btn-success fw-bold text-white d-inline-flex align-items-center" onclick="return confirm('Mark selected items as completed?')">
                                    <i class="bi bi-check-circle me-2"></i> Complete Selected Items
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($purchaseOrder->notes)
                    <div class="mt-3 p-3 bg-light rounded">
                        <label class="fw-bold small text-muted d-block">General Notes:</label>
                        {{ $purchaseOrder->notes }}
                    </div>
                    @endif

                    @if(isset($rejectedItemsWithDetails) && count($rejectedItemsWithDetails) > 0)
                    <div class="mt-4 p-3 bg-warning bg-opacity-10 rounded border border-warning">
                        <h5 class="text-warning mb-3"><i class="bi bi-exclamation-triangle-fill"></i> Rejected Items</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-warning align-middle">
                                <thead class="table-warning">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Image</th>
                                        <th>Category / Product</th>
                                        <th>Design</th>
                                        <th>Weights (Grams & Qty)</th>
                                        <th>Row Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rejectedItemsWithDetails as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="text-center">
                                            @if(isset($item['image']) && $item['image'])
                                                <img src="{{ asset($item['image']) }}" class="img-thumbnail" style="max-height: 80px; cursor: pointer;" onclick="showImagePreview(this.src)" alt="Item Image">
                                            @elseif(isset($item['design']) && $item['design'] && $item['design']->image)
                                                @php
                                                    $designImagePath = 'storage/' . $item['design']->image;
                                                    $fullDesignImagePath = public_path($designImagePath);
                                                @endphp
                                                @if(file_exists($fullDesignImagePath))
                                                    <img src="{{ asset($designImagePath) }}" class="img-thumbnail" style="max-height: 80px; cursor: pointer;" onclick="showImagePreview(this.src)" alt="Design Image">
                                                @else
                                                    <span class="text-warning small"><em>Design image missing</em></span>
                                                @endif
                                            @else
                                                <span class="text-muted small">No Image</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $item['category'] }}</span><br>
                                            <small>{{ $item['product']->product_name ?? 'N/A' }}</small>
                                        </td>
                                        <td>{{ $item['product']->design_code ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $grams = is_string($item['grams']) ? json_decode($item['grams'], true) : $item['grams'];
                                                $qtys = is_string($item['quantity']) ? json_decode($item['quantity'], true) : $item['quantity'];
                                            @endphp
                                            @if(is_array($grams))
                                                @foreach($grams as $i => $g)
                                                    <div>
                                                        {{ $g }}g × {{ $qtys[$i] ?? 1 }} nos
                                                    </div>
                                                @endforeach
                                            @else
                                                {{ $grams }}g × {{ $qtys }} nos
                                            @endif
                                        </td>
                                        <td class="fw-bold">{{ number_format($item['total'], 2) }}g</td>
                                    </tr>
                                    @if(!empty($item['item_notes']))
                                    <tr>
                                        <td colspan="6" class="bg-light text-danger">
                                            <small><strong>Rejection Note:</strong> {{ $item['item_notes'] }}</small>
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between">
                        <a href="{{ route('super-admin.purchase-order.index') }}" class="btn btn-outline-secondary">Back to List</a>
                        <form action="{{ route('super-admin.purchase-order.destroy', $purchaseOrder) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this purchase order?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">Delete Purchase Order</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" alt="Preview" class="img-fluid" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function showImagePreview(src) {
        document.getElementById('previewImage').src = src;
        var myModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        myModal.show();
    }
</script>
@endsection