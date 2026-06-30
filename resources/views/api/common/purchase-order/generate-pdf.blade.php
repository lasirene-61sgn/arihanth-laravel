<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order(s)</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-after: always;
        }
        .header {
            width: 100%;
            margin-bottom: 20px;
        }
        .logo {
            float: left;
            width: 150px;
        }
        .order-info {
            float: right;
            text-align: right;
        }
        .clear {
            clear: both;
        }
        h3 {
            margin: 0;
            color: #d9534f;
        }
        .details-section {
            width: 100%;
            margin-bottom: 20px;
        }
        .details-box {
            width: 48%;
            float: left;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .item-image {
            max-width: 80px;
            max-height: 80px;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .text-right {
            text-align: right;
        }
        .fw-bold {
            font-weight: bold;
        }
        .badge {
            padding: 3px 6px;
            border-radius: 4px;
            color: #fff;
            background-color: #777;
            font-size: 8pt;
        }
    </style>
</head>

<body>
    @foreach($ordersWithDetails as $data)
    @php
        $purchaseOrder = $data['purchaseOrder'];
        $items         = $data['items'];
        $craftsman     = $data['craftsman'] ?? null;

        // Build craftsman full address string logic
        $craftsmanAddress = '';
        if ($craftsman) {
            $addrParts = array_filter([
                $craftsman->door_no,
                $craftsman->shop_no,
                $craftsman->complex_name,
                $craftsman->building_name,
                $craftsman->street_name,
                $craftsman->area,
                $craftsman->city,
                $craftsman->state,
                $craftsman->pincode,
            ]);
            $craftsmanAddress = implode(', ', $addrParts);
        }
    @endphp

    <div class="{{ !$loop->last ? 'page-break' : '' }}">
        <div class="header-section" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <div class="company-block">
                @php
                    $logoPath = public_path('images/ajlogo.png');
                    if (file_exists($logoPath)) {
                        $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                        $dataLogo = file_get_contents($logoPath);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($dataLogo);
                    } else {
                        $base64 = null;
                    }
                @endphp
                
                @if($base64)
                    <img src="{{ $base64 }}" alt="Logo" style="height: 55px; margin-bottom: 6px;">
                @else
                    <h2>LOGO</h2>
                @endif

                <div class="brand-name" style="font-weight: bold; font-size: 16pt;">ARIHANTH JEWELLERS PVT LTD</div>
                <div class="company-meta" style="font-size: 9pt; color: #475569;">
                    @if(!empty($company['address']))
                        <div>{{ $company['address'] }}</div>
                    @endif
                    <div style="margin-top: 3px;">
                        @if(!empty($company['mobile']))
                            <span><strong>Mob:</strong> {{ $company['mobile'] }}</span>
                        @endif
                        @if(!empty($company['email']))
                            <span><strong>Email:</strong> {{ $company['email'] }}</span>
                        @endif
                    </div>
                    <div style="margin-top: 3px;">
                        @if(!empty($company['gst']))
                            <span><strong>GST:</strong> {{ $company['gst'] }}</span>
                        @endif
                        @if(!empty($company['cin']))
                            <span><strong>CIN:</strong> {{ $company['cin'] }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div style="text-align: right;">
                <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Purchase Order #</div>
                <div style="font-size: 28px; font-weight: 900; color: #0f172a; line-height: 1;">{{ $purchaseOrder->purchase_order_code }}</div>
                <div style="font-size: 12px; margin-top: 5px; color: #475569;">Issued: <strong>{{ $purchaseOrder->created_at->format('d M Y') }}</strong></div>
            </div>
        </div>

        @if($craftsman)
        <div class="craftsman-box" style="border: 1px solid #e2e8f0; padding: 10px; margin-bottom: 20px; background-color: #f8fafc;">
            <div class="craftsman-box-title" style="font-weight: bold; margin-bottom: 8px; border-bottom: 1px solid #cbd5e1;">🔨 Craftsman Details</div>
            <div class="craftsman-grid" style="display: grid; grid-template-columns: 1fr 1fr; font-size: 10pt;">
                <div><strong>Name:</strong> {{ $craftsman->business_name ?: 'N/A' }}</div>
                <div><strong>Mobile:</strong> {{ $craftsman->mobile ?: 'N/A' }}</div>
                <div><strong>GST No:</strong> {{ $craftsman->gst_no ?: 'N/A' }}</div>
                <div><strong>CIN No:</strong> {{ $craftsman->cin_no ?: 'N/A' }}</div>
            </div>
            @if(!empty($craftsmanAddress))
            <div class="craftsman-address" style="margin-top: 5px; font-size: 10pt;">
                <span style="font-weight: 600; color: #15803d;">Address:</span> {{ $craftsmanAddress }}
            </div>
            @endif
        </div>
        @endif

        <div class="details-section" style="margin-bottom: 15px;">
            <div class="details-box" style="float: left; width: 50%;">
                <strong>To:</strong><br>
                {{ $purchaseOrder->allocated_craftsman_code ? 'Craftsman Code: ' . $purchaseOrder->allocated_craftsman_code : 'Not Allocated' }}<br>
                <strong>Date:</strong> {{ $purchaseOrder->created_at->format('d M, Y') }}<br>
                <strong>Due Date:</strong> {{ $purchaseOrder->due_date ? \Carbon\Carbon::parse($purchaseOrder->due_date)->format('d M, Y') : 'N/A' }}
            </div>
            <div class="details-box text-right" style="float: right; width: 50%; text-align: right;">
                <strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $purchaseOrder->status)) }}
            </div>
            <div style="clear: both;"></div>
        </div>

        <table class="table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <thead>
                <tr style="background-color: #f1f5f9;">
                    <th width="5%" style="border: 1px solid #cbd5e1; padding: 8px;">#</th>
                    <th width="15%" style="border: 1px solid #cbd5e1; padding: 8px;">Image</th>
                    <th width="30%" style="border: 1px solid #cbd5e1; padding: 8px;">Product / Category</th>
                    <th width="15%" style="border: 1px solid #cbd5e1; padding: 8px;">Design</th>
                    <th width="15%" style="border: 1px solid #cbd5e1; padding: 8px;">Grams (Qty)</th>
                    <th width="20%" style="border: 1px solid #cbd5e1; padding: 8px;">Weight</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                <tr>
                    <td style="border: 1px solid #cbd5e1; padding: 8px; text-align: center;">{{ $index + 1 }}</td>
                    <td style="border: 1px solid #cbd5e1; padding: 8px; text-align: center;">
                        @php
                            $imagePath = $item['image_path'] ?? null;
                            $base64Image = null;
                            if ($imagePath && file_exists($imagePath)) {
                                $type = pathinfo($imagePath, PATHINFO_EXTENSION);
                                $dataImage = file_get_contents($imagePath);
                                $base64Image = 'data:image/' . $type . ';base64,' . base64_encode($dataImage);
                            }
                        @endphp
                        @if($base64Image)
                            <img src="{{ $base64Image }}" style="max-height: 60px; max-width: 60px;">
                        @else
                            <span style="font-size: 8pt; color: #999;">No Image</span>
                        @endif
                    </td>
                    <td style="border: 1px solid #cbd5e1; padding: 8px;">
                        <div style="font-weight: bold;">{{ $item['product'] ? $item['product']->product_name : ($item['product_name'] ?? 'Unknown') }}</div>
                        <div style="font-size: 8pt; color: #666;">
                            Cat: {{ $item['category_name'] ?? 'N/A' }}<br>
                            Sub: {{ $item['subcategory_name'] ?? 'N/A' }}
                        </div>
                    </td>
                    <td style="border: 1px solid #cbd5e1; padding: 8px; text-align: center;">{{ $item['design_code'] ?? 'N/A' }}</td>
                    <td style="border: 1px solid #cbd5e1; padding: 8px;">
                        @if(isset($item['grams']) && is_array($item['grams']))
                            @foreach($item['grams'] as $k => $gram)
                                {{ $gram }}g (x{{ $item['quantity'][$k] ?? 0 }})<br>
                            @endforeach
                        @else
                            N/A
                        @endif
                    </td>
                    <td style="border: 1px solid #cbd5e1; padding: 8px; text-align: right; font-weight: bold;">
                        {{ isset($item['total']) ? number_format($item['total'], 2) : '0.00' }}g
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="border: 1px solid #cbd5e1; padding: 8px; text-align: right; font-weight: bold;">Total Order Weight:</td>
                    <td style="border: 1px solid #cbd5e1; padding: 8px; text-align: right; font-weight: bold;">{{ number_format(collect($items)->sum('total'), 2) }}g</td>
                </tr>
            </tfoot>
        </table>

        @if($purchaseOrder->notes)
        <div class="footer" style="margin-top: 20px; padding: 10px; border-top: 1px solid #eee;">
            <strong>Notes:</strong>
            <p style="margin-top: 5px; font-size: 10pt;">{{ $purchaseOrder->notes }}</p>
        </div>
        @endif
    </div>
    @endforeach
</body>
</html>
