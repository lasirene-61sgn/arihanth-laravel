<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Product List</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #d9534f;
            padding-bottom: 10px;
        }

        .logo {
            display: inline-block;
            width: 40%;
        }

        .title-info {
            display: inline-block;
            width: 58%;
            text-align: right;
            vertical-align: top;
        }

        h3 {
            margin: 0;
            color: #d9534f;
            font-size: 18pt;
        }

        .product-card {
            width: 100%;
            margin-bottom: 25px;
            border: 1px solid #eee;
            padding: 15px;
            page-break-inside: avoid;
        }

        .product-image-box {
            display: inline-block;
            width: 30%;
            text-align: center;
            vertical-align: top;
        }

        .product-details {
            display: inline-block;
            width: 68%;
            vertical-align: top;
        }

        .item-image {
            max-width: 150px;
            max-height: 150px;
            border: 1px solid #ddd;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            padding: 6px;
            border-bottom: 1px solid #f0f0f0;
            text-align: left;
        }

        th {
            color: #666;
            width: 25%;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="header">
        <div class="logo">
            @php
                $logoPath = public_path('images/ajlogo.png');
                $base64Logo = null;

                if (file_exists($logoPath)) {
                    $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                    $data = file_get_contents($logoPath);
                    $base64Logo = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            @endphp

            @if($base64Logo)
                <img src="{{ $base64Logo }}" style="height: 50px;">
            @else
                <h2 style="color:#d9534f;">LASIRENE</h2>
            @endif
        </div>

        <div class="title-info">
            <h3>PRODUCT CATALOG</h3>
            <div style="font-size: 10pt; color: #666;">
                Generated on {{ now()->format('d M, Y H:i') }}
            </div>
        </div>
    </div>

    {{-- PRODUCTS --}}
    @foreach($products as $product)
        <div class="product-card">

            {{-- IMAGE --}}
            <div class="product-image-box">
                @php
                    $imagePath = null;

                    if ($product->images && $product->images->isNotEmpty()) {
                        $relPath = $product->images->first()->path;

                        if (file_exists(public_path('storage/' . $relPath))) {
                            $imagePath = public_path('storage/' . $relPath);
                        } elseif (file_exists(public_path($relPath))) {
                            $imagePath = public_path($relPath);
                        }
                    }

                    $base64Image = null;

                    if ($imagePath) {
                        try {
                            $type = pathinfo($imagePath, PATHINFO_EXTENSION);
                            $data = file_get_contents($imagePath);
                            $base64Image = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        } catch (\Exception $e) {}
                    }
                @endphp

                @if($base64Image)
                    <img src="{{ $base64Image }}" class="item-image">
                @else
                    <div style="width:150px;height:150px;background:#f5f5f5;line-height:150px;color:#999;">
                        No Image
                    </div>
                @endif
            </div>

            {{-- DETAILS --}}
            <div class="product-details">
                <table>
                    <tbody>

                        <tr>
                            <th>Product Name</th>
                            <td>{{ $product->product_name ?? 'N/A' }}</td>

                            <th>Product Code</th>
                            <td>{{ $product->product_code ?? 'N/A' }}</td>
                        </tr>

                        <tr>
                            <th>Category</th>
                            <td>{{ $product->product_category ?? 'N/A' }}</td>

                            <th>Subcategory</th>
                            <td>{{ $product->subcategory ?? 'N/A' }}</td>
                        </tr>

                        <tr>
                            <th>Quantity</th>
                            <td>{{ $product->quantity ?? '0' }}</td>

                            <th>Type</th>
                            <td>{{ $product->type ?? 'N/A' }}</td>
                        </tr>

                        <tr>
                            <th>Size</th>
                            <td>{{ $product->size ?? 'N/A' }}</td>

                            <th>Length</th>
                            <td>{{ $product->length ?? 'N/A' }}</td>
                        </tr>

                        <tr>
                            <th>Weight</th>
                            <td>
                                {{ $product->weight_from ?? '0' }}g -
                                {{ $product->weight_to ?? '0' }}g
                            </td>

                            <th>Hallmark</th>
                            <td>{{ $product->hallmark ?? 'N/A' }}</td>
                        </tr>

                        <tr>
                            <th>Rodium</th>
                            <td>{{ $product->rodium ?? 'N/A' }}</td>

                            <th>Hook</th>
                            <td>{{ $product->hook ?? 'N/A' }}</td>
                        </tr>

                        <tr>
                            <th>Stone</th>
                            <td>{{ $product->stone ?? 'N/A' }}</td>

                            <th>Enamel</th>
                            <td>{{ $product->enamel ?? 'N/A' }}</td>
                        </tr>

                    </tbody>
                </table>
            </div>

        </div>
    @endforeach

    {{-- FOOTER --}}
    <div class="footer">
        &copy; {{ date('Y') }} AJPL. All rights reserved.
    </div>

</body>
</html>