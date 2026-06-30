<div class="table-responsive mt-3">
    <table class="table table-centered table-nowrap mb-0">
        <thead class="table-light">
            <tr>
                <th>Image</th>
                <th>WO Number</th>
                <th>BP Code</th>
                <th>Product Category</th>
                <th>Type</th>
                <th>Order Type</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Created By</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($workOrders as $workOrder)
            <tr>
                <td>
                    @php
                        $imagePath = $workOrder->product_image;
                        $displayUrl = null;
                        $isPdf = false;

                        if ($imagePath) {
                            $isPdf = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)) === 'pdf';
                            if (str_starts_with($imagePath, 'images/') || str_starts_with($imagePath, 'storage/') || str_starts_with($imagePath, 'uploads/') || filter_var($imagePath, FILTER_VALIDATE_URL)) {
                                $displayUrl = asset($imagePath);
                            } else {
                                $displayUrl = asset('storage/' . $imagePath);
                            }
                        } elseif ($workOrder->product && $workOrder->product->images->count() > 0) {
                            $firstImg = $workOrder->product->images->first();
                            $displayUrl = asset('storage/' . $firstImg->path);
                        }
                        
                        $previewUrl = $displayUrl ?: asset('images/no-image.png');
                        $previewType = $isPdf ? 'pdf' : 'image';
                    @endphp

                    <div class="position-relative group cursor-zoom-in" 
                         style="width: 45px; height: 45px; background: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6; overflow: hidden; display: flex; align-items: center; justify-content: center; transition: all 0.2s; cursor: pointer;"
                         onclick="openUniversalPreview('{{ $previewUrl }}', '{{ $previewType }}')">
                        
                        @if($displayUrl)
                            @if($isPdf)
                                <canvas class="pdf-canvas" data-url="{{ $displayUrl }}" data-desired-width="45"></canvas>
                            @else
                                <img src="{{ $displayUrl }}" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                            @endif
                        @else
                            <i class="bi bi-image text-muted"></i>
                        @endif
                    </div>
                </td>
                <td>{{ $workOrder->work_order_number }}</td>
                <td>{{ $workOrder->bp_code }}</td>
                <td>{{ $workOrder->product_category }}</td>
                <td>{{ $workOrder->type }}</td>
                <td>
                    @if($workOrder->order_type == 'Regular')
                        <span class="badge bg-primary">{{ $workOrder->order_type }}</span>
                    @elseif($workOrder->order_type == 'Urgent')
                        <span class="badge bg-warning">{{ $workOrder->order_type }}</span>
                    @else
                        <span class="badge bg-danger">{{ $workOrder->order_type }}</span>
                    @endif
                </td>
                <td>{{ $workOrder->quantity }}</td>
                <td>
                    @if($workOrder->open_close == 'Open')
                        <span class="badge bg-success">{{ $workOrder->open_close }}</span>
                    @else
                        <span class="badge bg-secondary">{{ $workOrder->open_close }}</span>
                    @endif
                </td>
                <td>{{ $workOrder->created_at->format('d M, Y') }}</td>
                <td>
                    @if($workOrder->created_by)
                        <small class="text-muted">By: {{ $workOrder->creator_name }}</small><br>
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <a href="{{ route('user.work-order.show', $workOrder) }}" 
                           class="btn btn-info btn-sm">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('user.work-order.edit', $workOrder) }}" 
                           class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="{{ route('user.work-order.print', $workOrder) }}" 
                           class="btn btn-secondary btn-sm" target="_blank">
                            <i class="bi bi-printer"></i>
                        </a>
                        @if(in_array($workOrder->status, ['new', 'rejected']))
                        <form action="{{ route('user.work-order.destroy', $workOrder) }}" 
                              method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this work order?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center">No work orders found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>