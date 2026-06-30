<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Official Catalogue</title>
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
            border-bottom: 2px solid #a94442;
            padding-bottom: 10px;
        }

        .logo {
            float: left;
            width: 150px;
        }

        .title-info {
            float: right;
            text-align: right;
        }

        .clear {
            clear: both;
        }

        h3 {
            margin: 0;
            color: #a94442;
            font-size: 18pt;
        }

        .product-card {
            width: 100%;
            margin-bottom: 30px;
            border: 1px solid #eee;
            padding: 15px;
            page-break-inside: avoid;
        }

        .product-image-box {
            width: 200px;
            float: left;
            text-align: center;
        }

        .product-details {
            margin-left: 220px;
        }

        .item-image {
            max-width: 180px;
            max-height: 180px;
            border: 1px solid #ddd;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table td {
            padding: 5px;
            vertical-align: top;
            border-bottom: 1px solid #f9f9f9;
        }

        .label {
            font-weight: bold;
            width: 120px;
            color: #666;
        }

        .value {
            color: #000;
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
    <div class="header">
        <div class="logo">
            @php
            $logoPath = public_path('images/ajlogo.png');
            if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $dataLogo = file_get_contents($logoPath);
            $base64Logo = 'data:image/' . $type . ';base64,' . base64_encode($dataLogo);
            } else {
            $base64Logo = null;
            }
            @endphp
            @if($base64Logo)
            <img src="{{ $base64Logo }}" style="height: 50px;">
            @else
            <h2 style="margin:0; color:#a94442;">LASIRENE</h2>
            @endif
        </div>
        <div class="title-info">
            <h3>OFFICIAL CATALOGUE</h3>
            <div style="font-size: 10pt; color: #666;">Generated on {{ now()->format('d M, Y H:i') }}</div>
        </div>
        <div class="clear"></div>
    </div>

    @foreach($products as $product)
    <div class="product-card">
        <div class="product-image-box">
            @php
            $imagePath = null;
            if ($product->images->isNotEmpty()) {
            $relPath = $product->images->first()->path;
            $path = public_path('storage/' . $relPath);
            if (file_exists($path)) {
            $imagePath = $path;
            } else {
            $path = public_path($relPath);
            if (file_exists($path)) {
            $imagePath = $path;
            }
            }
            }

            $base64Image = null;
            if ($imagePath && file_exists($imagePath)) {
            try {
            $type = pathinfo($imagePath, PATHINFO_EXTENSION);
            $dataImage = file_get_contents($imagePath);
            $base64Image = 'data:image/' . $type . ';base64,' . base64_encode($dataImage);
            } catch (\Exception $e) {}
            }
            @endphp
            @if($base64Image)
            <img src="{{ $base64Image }}" class="item-image">
            @else
            <div style="width: 180px; height: 180px; background: #f5f5f5; line-height: 180px; color: #999; border: 1px solid #ddd;">No Image</div>
            @endif
        </div>
        <div class="product-details">
            {{-- Product Name Header --}}
            @if(!empty($product->product_name))
            <div style="font-size: 14pt; font-weight: bold; color: #a94442; margin-bottom: 10px;">
                {{ $product->product_name }}
            </div>
            @endif

            <table class="table">
                {{-- Design Code --}}
                @if(!empty($product->design_code))
                <tr>
                    <td class="label">Design Code</td>
                    <td class="value">{{ $product->design_code }}</td>
                </tr>
                @endif

                {{-- Product Code --}}
                @if(!empty($product->product_code))
                <tr>
                    <td class="label">Product Code</td>
                    <td class="value">{{ $product->product_code }}</td>
                </tr>
                @endif

                {{-- Category --}}
                @if(!empty($product->category->name))
                <tr>
                    <td class="label">Category</td>
                    <td class="value">{{ $product->category->name }}</td>
                </tr>
                @endif

                {{-- Subcategory --}}
                @if(!empty($product->subcategory->name))
                <tr>
                    <td class="label">Subcategory</td>
                    <td class="value">{{ $product->subcategory->name }}</td>
                </tr>
                @endif

                {{-- Type --}}
                @if(!empty($product->type))
                <tr>
                    <td class="label">Type</td>
                    <td class="value">{{ $product->type }}</td>
                </tr>
                @endif

                {{-- Weight Range --}}
                @if(!empty($product->weight_from) || !empty($product->weight_to))
                <tr>
                    <td class="label">Weight Range</td>
                    <td class="value">{{ $product->weight_from ?? '0' }}g - {{ $product->weight_to ?? '0' }}g</td>
                </tr>
                @endif

                {{-- Size / Length --}}
                @if(!empty($product->size) || !empty($product->length))
                <tr>
                    <td class="label">Size / Length</td>
                    <td class="value">
                        {{ $product->size ?? 'N/A' }}
                        @if(!empty($product->length))
                        / {{ $product->length }}
                        @endif
                    </td>
                </tr>
                @endif

                {{-- Repairs/Samples --}}
                @if(!empty($product->repair))
                <tr>
                    <td class="label">Repairs/Samples</td>
                    <td class="value">{{ $product->repair }}</td>
                </tr>
                @endif
            </table>
        </div>
        <div class="clear"></div>
    </div>
    @endforeach

    <div class="footer">
        &copy; {{ date('Y') }} AJPL. All rights reserved.
    </div>
</body>

</html>