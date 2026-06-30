<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order {{ $purchaseOrder->purchase_order_code }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #000;
            line-height: 1.4;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header img { height: 60px; }
        .section h2 {
            background: #f0f0f0;
            padding: 8px 12px;
            font-size: 16px;
            border-left: 4px solid #007bff;
            margin-bottom: 15px;
        }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .detail-label { font-weight: bold; font-size: 12px; color: #555; }
        .detail-value { font-size: 14px; margin-top: 2px; }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ccc;
            padding: 10px 8px;
            text-align: left;
            font-size: 13px;
        }
        .items-table th { background: #f8f9fa; }
        .status-badge {
            background: #007bff;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        @media print {
            .no-print { display: none; }
            .container { border: none; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 20px; max-width: 900px; margin: 0 auto 20px auto;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px;">Print Details</button>
    </div>

    <div class="container">
        <div class="header">
            <img src="{{ asset('images/ajlogo.png') }}" alt="Logo">
            <div style="text-align: right;">
                <h1 style="margin:0; font-size: 22px;">PURCHASE ORDER DETAILS</h1>
                <p style="margin:0; color: #666;">Order Code: {{ $purchaseOrder->purchase_order_code }}</p>
            </div>
        </div>

        <div class="section">
            <h2>Basic Information</h2>
            <div class="details-grid">
                <div>
                    <div class="detail-label">Purchase Order Code</div>
                    <div class="detail-value">{{ $purchaseOrder->purchase_order_code }}</div>
                </div>
                <div>
                    <div class="detail-label">Due Date</div>
                    <div class="detail-value">{{ $purchaseOrder->due_date ? $purchaseOrder->due_date->format('d M, Y') : 'N/A' }}</div>
                </div>
                <div>
                    <div class="detail-label">Status</div>
                    <div class="detail-value"><span class="status-badge">{{ strtoupper($purchaseOrder->status) }}</span></div>
                </div>
                <div>
                    <div class="detail-label">Craftsman Status</div>
                    <div class="detail-value"><strong>{{ strtoupper($purchaseOrder->craftsman_status ?? 'UNKNOWN') }}</strong></div>
                </div>
            </div>
        </div>

        @if($purchaseOrder->notes)
        <div class="section">
            <h2>Notes</h2>
            <p style="font-size: 14px;">{{ $purchaseOrder->notes }}</p>
        </div>
        @endif

        <div class="section">
            <h2>Items</h2>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Category</th>
                        <th>Sub Category</th>
                        <th>Design Code</th>
                        <th>Grams & Quantity</th>
                        <th>Total</th>
                        <th>Item Image</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($itemsWithDetails as $index => $item)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $item['category_name'] ?? 'N/A' }}</td>
                        <td>
                            {{-- This logic ensures the subcategory name shows up --}}
                            {{ $item['subcategory_name'] ?? ($item['product']->subcategory->name ?? 'N/A') }}
                        </td>
                        <td>
                            {{ $item['design'] ? $item['design']->design_code : ($item['product']->design_code ?? 'N/A') }}
                        </td>
                        <td>
                            @if(isset($item['grams']) && is_array($item['grams']))
                                @foreach($item['grams'] as $k => $gram)
                                    <div>{{ $gram }}g × {{ $item['quantity'][$k] }} = <strong>{{ number_format($item['individual_totals'][$k] ?? ($gram * $item['quantity'][$k]), 2) }}g</strong></div>
                                @endforeach
                            @else
                                {{ $item['grams'] ?? 0 }}g × {{ $item['quantity'] ?? 0 }}
                            @endif
                        </td>
                        <td class="fw-bold">{{ number_format($item['total'], 2) }}</td>
                        <td style="text-align: center;">
                            @php
                                $imagePath = !empty($item['image']) ? $item['image'] : null;
                                $imgPath = null;

                                if ($imagePath) {
                                    $imgPath = str_contains($imagePath, 'images/') ? $imagePath : 'storage/' . $imagePath;
                                } else {
                                    if(isset($item['design']) && !empty($item['design']->image)) {
                                        $path = $item['design']->image;
                                        $imgPath = str_starts_with($path, 'storage/') || str_starts_with($path, 'images/') ? $path : 'storage/' . $path;
                                    } elseif(isset($item['product']) && $item['product']->images && count($item['product']->images) > 0) {
                                        $path = $item['product']->images[0]->path;
                                        $imgPath = str_starts_with($path, 'storage/') || str_starts_with($path, 'images/') ? $path : 'storage/' . $path;
                                    }
                                }
                            @endphp
                            @if($imgPath)
                                <img src="{{ asset($imgPath) }}" style="max-height: 70px; max-width: 70px; object-fit: contain;">
                            @else
                                <span style="font-size: 10px; color: #999;">No Image</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Grand Total:</td>
                        <td colspan="2" class="fw-bold">{{ number_format(collect($itemsWithDetails)->sum('total'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div style="text-align: center; margin-top: 30px; font-size: 11px; color: #888; border-top: 1px solid #eee; padding-top: 10px;">
            Generated on {{ now()->format('d M, Y H:i') }} | Purchase Order: {{ $purchaseOrder->purchase_order_code }}
        </div>
    </div>
</body>
</html>