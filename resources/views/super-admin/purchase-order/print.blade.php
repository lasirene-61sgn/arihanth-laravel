<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order {{ $purchaseOrder->purchase_order_code }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #fff;
            color: #000;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 20px;
            background: #fff;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section h2 {
            background: #f0f0f0;
            padding: 10px;
            margin: 0 0 15px;
            border-left: 4px solid #007bff;
            font-size: 18px;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .detail-item {
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #555;
            font-size: 14px;
        }
        
        .detail-value {
            font-size: 15px;
            margin-top: 3px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-created { background: #007bff; color: white; }
        .status-for_approval { background: #ffc107; color: black; }
        .status-approved { background: #28a745; color: white; }
        .status-allocated { background: #28a745; color: white; }
        .status-rejected { background: #dc3545; color: white; }
        .status-in_process { background: #17a2b8; color: white; }
        .status-completed { background: #28a745; color: white; }
        .status-accepted { background: #28a745; color: white; }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        
        .items-table th {
            background: #f0f0f0;
            font-weight: bold;
        }
        
        .items-table img {
            max-width: 100px;
            max-height: 100px;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            color: #666;
            font-size: 12px;
        }
        
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            
            .container {
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PURCHASE ORDER DETAILS</h1>
            <p>Order Code: {{ $purchaseOrder->purchase_order_code }}</p>
        </div>
        
        <div class="section">
            <h2>Basic Information</h2>
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Purchase Order Code</div>
                    <div class="detail-value">{{ $purchaseOrder->purchase_order_code }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Due Date</div>
                    <div class="detail-value">{{ $purchaseOrder->due_date ? $purchaseOrder->due_date->format('d M, Y') : 'N/A' }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">
                        @if($purchaseOrder->status == 'created')
                            <span class="status-badge status-created">Created</span>
                        @elseif($purchaseOrder->status == 'for_approval')
                            <span class="status-badge status-for_approval">For Approval</span>
                        @elseif($purchaseOrder->status == 'approved')
                            <span class="status-badge status-approved">Approved</span>
                        @else
                            <span class="status-badge status-{{ str_replace('_', '-', $purchaseOrder->status ?? 'unknown') }}">{{ ucfirst($purchaseOrder->status ?? 'Unknown') }}</span>
                        @endif
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Craftsman Status</div>
                    <div class="detail-value">
                        @if($purchaseOrder->craftsman_status == 'allocated')
                            <span class="status-badge status-allocated">Allocated</span>
                        @elseif($purchaseOrder->craftsman_status == 'accepted')
                            <span class="status-badge status-accepted">Accepted</span>
                        @elseif($purchaseOrder->craftsman_status == 'in_process')
                            <span class="status-badge status-in_process">In Process</span>
                        @elseif($purchaseOrder->craftsman_status == 'completed')
                            <span class="status-badge status-completed">Completed</span>
                        @elseif($purchaseOrder->craftsman_status == 'rejected')
                            <span class="status-badge status-rejected">Rejected</span>
                        @else
                            <span class="status-badge status-{{ str_replace('_', '-', $purchaseOrder->craftsman_status ?? 'unknown') }}">{{ ucfirst($purchaseOrder->craftsman_status ?? 'Unknown') }}</span>
                        @endif
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Created At</div>
                    <div class="detail-value">{{ $purchaseOrder->created_at->format('d M, Y H:i') }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Updated At</div>
                    <div class="detail-value">{{ $purchaseOrder->updated_at->format('d M, Y H:i') }}</div>
                </div>
                
                @if($purchaseOrder->allocated_craftsman_code)
                <div class="detail-item">
                    <div class="detail-label">Allocated Craftsman</div>
                    <div class="detail-value">{{ $purchaseOrder->craftsman ? $purchaseOrder->craftsman->business_name : $purchaseOrder->allocated_craftsman_code }}</div>
                </div>
                @endif
            </div>
        </div>
        
        @if($purchaseOrder->notes)
        <div class="section">
            <h2>Notes</h2>
            <div class="detail-item">
                <div class="narration detail-value">{{ $purchaseOrder->notes }}</div>
            </div>
        </div>
        @endif
        
        <div class="section">
            <h2>Items</h2>
            @if($itemsWithDetails && count($itemsWithDetails) > 0)
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Category</th>
                            <th>Product</th>
                            <th>Design Code</th>
                            <th>Grams & Quantity</th>
                            <th>Total</th>
                            <th>Item Notes</th>
                            <th>Item Image</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($itemsWithDetails as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item['category'] ?? 'N/A' }}</td>
                                <td>
                                    @if($item['product'])
                                        <strong>{{ $item['product']->product_name }}</strong><br>
                                        <small>Sub: {{ $item['product']->subcategory->name ?? 'N/A' }}</small>
                                    @else
                                        <strong>{{ $item['product_name'] ?? 'N/A' }}</strong><br>
                                        <small>Sub: {{ $item['subcategory_name'] ?? 'N/A' }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($item['product'] && $item['product']->design_code)
                                        {{ $item['product']->design_code }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if(isset($item['grams']) && isset($item['quantity']) && 
                                         is_array($item['grams']) && is_array($item['quantity']))
                                        @for($i = 0; $i < count($item['grams']); $i++)
                                            @if($i > 0)<br>@endif
                                            {{ $item['grams'][$i] }}g × {{ $item['quantity'][$i] }} = <strong>{{ number_format($item['individual_totals'][$i], 2) }}g</strong>
                                        @endfor
                                    @else
                                        {{ $item['grams'] ?? 'N/A' }}g × {{ $item['quantity'] ?? 'N/A' }} = <strong>{{ number_format(($item['grams'] ?? 0) * ($item['quantity'] ?? 0), 2) }}g</strong>
                                    @endif
                                </td>
                                <td>{{ $item['total'] ?? 0 }}</td>
                                <td>{{ $item['item_notes'] }}</td>
                                <td>
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
                                        <img src="{{ $imageSrc }}" class="img-thumbnail" style="max-height: 100px;" alt="Item Image">
                                    @else
                                        <span class="text-muted small"><em>No Image</em></span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7" style="text-align: right;"><strong>Grand Total:</strong></td>
                            <td>
                                <strong>
                                    {{ array_sum(array_column($itemsWithDetails, 'total')) }}
                                </strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            @else
                <p class="text-muted">No items found for this purchase order.</p>
            @endif
        </div>
        
        @if($purchaseOrder->hasRejectedItems())
        <div class="section">
            <h2>Rejected Items</h2>
            @if($purchaseOrder->rejected_items && count($purchaseOrder->rejected_items) > 0)
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Category</th>
                            <th>Product</th>
                            <th>Design</th>
                            <th>Grams</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Item Image</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseOrder->rejected_items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item['category'] ?? 'N/A' }}</td>
                                <td>
                                    @if(isset($item['product_id']))
                                        @php
                                            $product = \App\Models\Product::find($item['product_id']);
                                        @endphp
                                        @if($product)
                                            {{ $product->product_name }}
                                        @else
                                            N/A
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if(isset($item['design_id']))
                                        @php
                                            $design = \App\Models\Design::find($item['design_id']);
                                        @endphp
                                        @if($design)
                                            {{ $design->design_code }} - {{ $design->design_name }}
                                        @else
                                            N/A
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $item['grams'] ?? 'N/A' }}</td>
                                <td>{{ $item['quantity'] ?? 'N/A' }}</td>
                                <td>{{ $item['total'] ?? 'N/A' }}</td>
                                <td>
                                    @if(isset($item['image']) && $item['image'])
                                        <img src="{{ asset($item['image']) }}" class="img-thumbnail" style="max-height: 100px;" alt="Rejected Item Image">
                                    @elseif(isset($item['design_id']))
                                        @php
                                            $design = \App\Models\Design::find($item['design_id']);
                                            $imageSrc = null;
                                            if($design && $design->image) {
                                                $path = $design->image;
                                                $imageSrc = str_starts_with($path, 'storage/') || str_starts_with($path, 'images/') ? asset($path) : asset('storage/' . $path);
                                            }
                                        @endphp
                                        @if($imageSrc)
                                            <img src="{{ $imageSrc }}" class="img-thumbnail" style="max-height: 100px;" alt="Rejected Design Image">
                                        @else
                                            <span class="text-muted small"><em>No Image</em></span>
                                        @endif
                                    @elseif(isset($item['product_id']))
                                        @php
                                            $product = \App\Models\Product::with('images')->find($item['product_id']);
                                            $imageSrc = null;
                                            if($product && $product->images && count($product->images) > 0) {
                                                $path = $product->images[0]->path;
                                                $imageSrc = str_starts_with($path, 'storage/') || str_starts_with($path, 'images/') ? asset($path) : asset('storage/' . $path);
                                            }
                                        @endphp
                                        @if($imageSrc)
                                            <img src="{{ $imageSrc }}" class="img-thumbnail" style="max-height: 100px;" alt="Rejected Product Image">
                                        @else
                                            <span class="text-muted small"><em>No Image</em></span>
                                        @endif
                                    @else
                                        <span class="text-muted small"><em>No Image</em></span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">No rejected items found for this purchase order.</p>
            @endif
        </div>
        @endif
        
        <div class="footer">
            <p>Generated on {{ now()->format('d M, Y H:i') }} | Purchase Order: {{ $purchaseOrder->purchase_order_code }}</p>
        </div>
    </div>
</body>
</html>