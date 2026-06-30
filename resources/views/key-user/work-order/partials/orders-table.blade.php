@if(isset($workOrders) && $workOrders->count() > 0)
<table class="table table-striped">
    <thead>
        <tr>
            <th>WO Number</th>
            <th>Image</th>
            <th>Customer Name</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($workOrders as $workOrder)
        <tr>
            <td>{{ $workOrder->work_order_number }}</td>
            <td style="width: 80px;">
                @php
                    $displayImage = null;
                    $isPdf = false;
                    
                    if ($workOrder->product_image) {
                        $isPdf = strtolower(pathinfo($workOrder->product_image, PATHINFO_EXTENSION)) === 'pdf';
                        if (strpos($workOrder->product_image, 'http') === 0 || strpos($workOrder->product_image, 'images/') === 0 || strpos($workOrder->product_image, 'storage/') === 0 || strpos($workOrder->product_image, 'uploads/') === 0) {
                            $displayImage = asset($workOrder->product_image);
                        } else {
                            $displayImage = asset('storage/' . $workOrder->product_image);
                        }
                    } elseif ($workOrder->product && $workOrder->product->images->count() > 0) {
                        $firstImg = $workOrder->product->images->first()->path;
                        $isPdf = strtolower(pathinfo($firstImg, PATHINFO_EXTENSION)) === 'pdf';
                        if (strpos($firstImg, 'http') === 0 || strpos($firstImg, 'storage/') === 0 || strpos($firstImg, 'images/') === 0 || strpos($firstImg, 'uploads/') === 0) {
                            $displayImage = asset($firstImg);
                        } else {
                            $displayImage = asset('storage/' . $firstImg);
                        }
                    }
                @endphp

                @if($displayImage)
                    <div class="position-relative" style="width: 60px; height: 60px; cursor: pointer;" 
                         onclick="openUniversalPreview('{{ $displayImage }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                        @if($isPdf)
                            <canvas class="pdf-canvas border rounded shadow-sm" 
                                    data-url="{{ $displayImage }}" 
                                    data-desired-width="60" 
                                    style="width: 60px; height: 60px; object-fit: cover;"></canvas>
                            <div class="position-absolute top-50 start-50 translate-middle">
                                <i class="bi bi-file-pdf text-danger fs-4"></i>
                            </div>
                        @else
                            <img src="{{ $displayImage }}" alt="Product" 
                                 class="rounded border shadow-sm" 
                                 style="width: 60px; height: 60px; object-fit: cover;">
                        @endif
                    </div>
                @else
                    <div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 60px;">
                        <i class="bi bi-image fs-4"></i>
                    </div>
                @endif
            </td>
            <td>{{ $workOrder->customer_name }}</td>
            <td>{{ $workOrder->product_name }}</td>
            <td>{{ $workOrder->quantity }}</td>
            <td>{{ $workOrder->due_date }}</td>
            <td>
                <span class="badge bg-{{ $workOrder->status == 'new' ? 'primary' : ($workOrder->status == 'allocated' ? 'warning' : 'success') }}">
                    {{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}
                </span>
            </td>
            <td>
                <a href="{{ route('key-user.work-order.show', $workOrder) }}" class="btn btn-sm btn-info">View</a>
                <a href="{{ route('key-user.work-order.edit', $workOrder) }}" class="btn btn-sm btn-primary">Edit</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="alert alert-info">
    No work orders found.
</div>
@endif