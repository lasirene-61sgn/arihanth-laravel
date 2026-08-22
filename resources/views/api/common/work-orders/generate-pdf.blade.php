<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Work Order(s)</title>
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
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
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
            color: #1a202c;
            letter-spacing: 1px;
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

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 9pt;
            text-transform: uppercase;
        }

        .info-label {
            font-weight: bold;
            color: #4a5568;
            width: 120px;
            display: inline-block;
        }

        .image-container {
            width: 100%;
            text-align: center;
            margin-top: 20px;
        }

        .product-image {
            max-width: 250px;
            max-height: 250px;
            border: 1px solid #ddd;
            padding: 5px;
        }

        .footer {
            margin-top: 50px;
            width: 100%;
        }

        .signature-box {
            width: 40%;
            float: left;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin: 0 5%;
        }

        .text-right {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .mt-2 {
            margin-top: 10px;
        }

        .mb-2 {
            margin-bottom: 10px;
        }

        .section-title {
            background: #edf2f7;
            padding: 5px 10px;
            font-weight: bold;
            margin-bottom: 10px;
            border-left: 4px solid #333;
        }
    </style>
</head>

<body>
    @foreach($workOrders as $workOrder)
    <div class="{{ !$loop->last ? 'page-break' : '' }}">
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
                <h2 style="margin:0;">LOGO</h2>
                @endif
            </div>
            <div class="order-info">
                <h3>WORK ORDER</h3>
                <div style="font-size: 16pt; font-weight: bold; color: #1a202c;">{{ $workOrder['work_order_number'] }}</div>
                <div style="font-size: 9pt; color: #718096;">Date: {{ \Carbon\Carbon::parse($workOrder['created_at'])->format('d M, Y') }}</div>
            </div>
            <div class="clear"></div>
        </div>

        <div class="details-section">
            <div class="details-box">
                @if(!$isCraftsman)
                <div class="mb-2"><span class="info-label">Customer:</span> {{ $workOrder['customer_name'] ?? 'N/A' }}</div>
                @endif

                @if(!$isBuyer && !$isCraftsman)
                <div class="mb-2"><span class="info-label">Order Date:</span> {{ isset($workOrder['due_date']) ? \Carbon\Carbon::parse($workOrder['due_date'])->format('d M, Y') : 'N/A' }}</div>
                @endif

                @if(!$isCraftsman)
                <div class="mb-2"><span class="info-label">Reference No:</span> {{ $workOrder['reference_no'] ?? 'N/A' }}</div>
                @endif

                @if($isAdmin)
                <div class="mb-2"><span class="info-label">Buyer Code:</span> {{ $workOrder['bp_code'] ?? 'N/A' }}</div>
                @endif
            </div>
            <div class="details-box">
                @if(!$isBuyer && !$isCraftsman)
                <div class="mb-2 text-right"><span class="info-label">Status:</span> <strong>{{ ucfirst($workOrder['status']) }}</strong></div>
                @endif

                @if(!$isBuyer)
                <div class="mb-2 text-right"><span class="info-label">Craftsman:</span> {{ $workOrder['allocated_craftsman_bp_code'] ?? 'Not Allocated' }}</div>
                @endif

                @if(!$isBuyer && isset($workOrder['craftsman_due_date']))
                <div class="mb-2 text-right"><span class="info-label">Craftsman Due:</span> {{ \Carbon\Carbon::parse($workOrder['craftsman_due_date'])->format('d M, Y') }}</div>
                @endif
            </div>
            <div class="clear"></div>
        </div>

        <div class="section-title">PRODUCT SPECIFICATIONS</div>
        <table class="table">
            {{-- Row 1: Product Name & Code --}}
            @if(!empty($workOrder['product_name']) || !empty($workOrder['product_code']))
            <tr>
                <th width="25%">Product Name</th>
                <td width="25%">{{ $workOrder['product_name'] ?? 'N/A' }}</td>
                <th width="25%">Product Code</th>
                <td width="25%">{{ $workOrder['product_code'] ?? 'N/A' }}</td>
            </tr>
            @endif

            {{-- Row 2: Category & Subcategory --}}
            @if(!empty($workOrder['product_category']) || !empty($workOrder['subcategory']))
            <tr>
                <th>Category</th>
                <td>{{ $workOrder['product_category'] ?? 'N/A' }}</td>
                <th>Subcategory</th>
                <td>{{ $workOrder['subcategory'] ?? 'N/A' }}</td>
            </tr>
            @endif

            {{-- Row 3: Quantity & Type --}}
            @if(!empty($workOrder['quantity']) || !empty($workOrder['type']))
            <tr>
                <th>Quantity</th>
                <td>{{ $workOrder['quantity'] }}</td>
                <th>Type</th>
                <td>{{ $workOrder['type'] ?? 'N/A' }}</td>
            </tr>
            @endif

            {{-- Row 4: Size & Length --}}
            @if(!empty($workOrder['size']) || !empty($workOrder['length']))
            <tr>
                <th>Size</th>
                <td>{{ $workOrder['size'] ?? 'N/A' }}</td>
                <th>Length</th>
                <td>{{ $workOrder['length'] ?? 'N/A' }}</td>
            </tr>
            @endif

            {{-- Row 5: Weight & Hallmark --}}
            @if(!empty($workOrder['weight_from']) || !empty($workOrder['hallmark']))
            <tr>
                <th>Weight Range</th>
                <td>{{ $workOrder['weight_from'] ?? '0' }}g - {{ $workOrder['weight_to'] ?? '0' }}g</td>
                <th>HUID / Hallmark</th>
                <td>{{ $workOrder['hallmark'] ?? 'N/A' }}</td>
            </tr>
            @endif

            {{-- Row 6: Rodium & Hook --}}
            @if(!empty($workOrder['rodium']) || !empty($workOrder['hook']))
            <tr>
                <th>Rodium</th>
                <td>{{ $workOrder['rodium'] ?? 'N/A' }}</td>
                <th>Hook</th>
                <td>{{ $workOrder['hook'] ?? 'N/A' }}</td>
            </tr>
            @endif

            {{-- Row 7: Stone & Enamel --}}
            @if(!empty($workOrder['stone']) || !empty($workOrder['enamel']))
            <tr>
                <th>Stone</th>
                <td>{{ $workOrder['stone'] ?? 'N/A' }}</td>
                <th>Enamel</th>
                <td>{{ $workOrder['enamel'] ?? 'N/A' }}</td>
            </tr>
            @endif
        </table>

        @if(isset($workOrder['narration_craftsman']) && $workOrder['narration_craftsman'])
        <div class="section-title">NOTE FOR CRAFTSMAN</div>
        <div style="padding: 10px; background: #f7fafc; border: 1px solid #e2e8f0; margin-bottom: 20px;">
            {{ $workOrder['narration_craftsman'] }}
        </div>
        @endif

        <div class="section-title">PRODUCT IMAGES</div>
        <div class="image-container">
            @php
            $images = [];
            // Collect images from gallery_images (already strings)
            if (isset($workOrder['gallery_images']) && is_array($workOrder['gallery_images']) && !empty($workOrder['gallery_images'])) {
                $images = array_merge($images, $workOrder['gallery_images']);
            }

            // Collect images from completion_proof_images (already strings)
            if (isset($workOrder['completion_proof_images']) && is_array($workOrder['completion_proof_images']) && !empty($workOrder['completion_proof_images'])) {
                $images = array_merge($images, $workOrder['completion_proof_images']);
            }

            // Fallback to legacy images array if the above are empty
            if (empty($images) && isset($workOrder['images']) && is_array($workOrder['images'])) {
                foreach ($workOrder['images'] as $img) {
                    if (is_string($img)) {
                        $images[] = $img;
                    } elseif (is_array($img) && isset($img['image_url'])) {
                        $images[] = $img['image_url'];
                    }
                }
            }

            // Final fallback to single image fields
            if (empty($images)) {
                if (!empty($workOrder['preview_image_url']) && is_string($workOrder['preview_image_url'])) {
                    $images[] = $workOrder['preview_image_url'];
                } elseif (!empty($workOrder['product_image_url']) && is_string($workOrder['product_image_url'])) {
                    $images[] = $workOrder['product_image_url'];
                } elseif (!empty($workOrder['product_image']) && is_string($workOrder['product_image'])) {
                    $images[] = $workOrder['product_image'];
                }
            }

            $images = array_unique($images);

            // Convert to Base64 for Dompdf
            $base64Images = [];
            $pdfLinks = [];
            
            \Illuminate\Support\Facades\Log::info("Generating PDF for WorkOrder " . ($workOrder['work_order_number'] ?? 'Unknown'));
            \Illuminate\Support\Facades\Log::info("Collected images: " . json_encode($images));

            foreach ($images as $imgUrl) {
                if (!$imgUrl || !is_string($imgUrl)) continue;

                // Handle PDFs by adding them as links instead of rendering as images
                if (str_ends_with(strtolower($imgUrl), '.pdf')) {
                    $pdfLinks[] = filter_var($imgUrl, FILTER_VALIDATE_URL) ? $imgUrl : asset($imgUrl);
                    continue;
                }

                $fullPath = '';
                
                $parsedUrl = parse_url($imgUrl);
                $path = ltrim($parsedUrl['path'] ?? $imgUrl, '/');
                $storageTrimmed = str_replace('storage/', '', $path);
                
                $pathsToTry = [
                    $imgUrl,
                    public_path($path),
                    storage_path('app/public/' . $storageTrimmed),
                    public_path('storage/' . $path)
                ];
                
                foreach ($pathsToTry as $p) {
                    if (is_string($p) && file_exists($p) && is_file($p)) {
                        $fullPath = $p;
                        break;
                    }
                }

                \Illuminate\Support\Facades\Log::info("Resolving image: $imgUrl -> FullPath: $fullPath");

                if ($fullPath && @getimagesize($fullPath)) {
                    $type = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                    if ($type === 'jpg') $type = 'jpeg';
                    $data = file_get_contents($fullPath);
                    $base64Images[] = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    \Illuminate\Support\Facades\Log::info("Added local image: data:image/$type");
                } elseif (filter_var($imgUrl, FILTER_VALIDATE_URL)) {
                    \Illuminate\Support\Facades\Log::info("Trying remote download: $imgUrl");
                    try {
                        $context = stream_context_create(['http' => ['ignore_errors' => true]]);
                        $data = @file_get_contents($imgUrl, false, $context);
                        if ($data && @imagecreatefromstring($data)) {
                            $type = strtolower(pathinfo(parse_url($imgUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
                            if ($type === 'jpg' || !$type) $type = 'jpeg';
                            $base64Images[] = 'data:image/' . $type . ';base64,' . base64_encode($data);
                            \Illuminate\Support\Facades\Log::info("Added remote image: data:image/$type");
                        } else {
                            \Illuminate\Support\Facades\Log::warning("Remote download failed or invalid image: $imgUrl");
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Remote download exception: " . $e->getMessage());
                    }
                } else {
                    \Illuminate\Support\Facades\Log::warning("Could not resolve image: $imgUrl");
                }
            }
            @endphp

            @forelse($base64Images as $b64)
            <img src="{{ $b64 }}" class="product-image" width="250" style="margin: 5px;">
            @empty
                @if(empty($pdfLinks))
                <div style="color: #a0aec0; padding: 20px;">No images available for this work order.</div>
                @endif
            @endforelse

            @if(!empty($pdfLinks))
                <div style="margin-top: 15px; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px;">
                    <strong style="color: #4a5568; font-size: 14px;">Attached Documents:</strong><br>
                    @foreach($pdfLinks as $pdfUrl)
                        <a href="{{ $pdfUrl }}" target="_blank" style="color: #3182ce; text-decoration: none; font-size: 13px; display: block; margin-top: 5px;">
                            📄 View Attached PDF ({{ basename(parse_url($pdfUrl, PHP_URL_PATH)) }})
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="footer">
            <div class="signature-box">ISSUED BY</div>
            <div class="signature-box">RECEIVED BY</div>
            <div class="clear"></div>
        </div>
    </div>
    @endforeach
</body>

</html>