<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Work Orders</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 11px;
            color: #1a202c;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        .page-break {
            page-break-after: always;
        }

        .wo-container {
            padding: 20px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
            background: #fff;
        }

        .wo-header {
            border-bottom: 2px solid #1a202c;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-title {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 2px;
            margin-top: 5px;
        }

        .wo-number {
            font-size: 22px;
            font-weight: bold;
            text-align: right;
        }

        .main-layout-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-column {
            width: 65%;
            vertical-align: top;
            padding-right: 20px;
        }

        .images-column {
            width: 35%;
            vertical-align: top;
            text-align: center;
            border-left: 1px solid #edf2f7;
            padding-left: 15px;
        }

        .info-row {
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: 700;
            font-size: 10px;
            color: #4a5568;
            text-transform: uppercase;
            width: 120px;
            display: inline-block;
        }

        .info-value {
            font-size: 11px;
            font-weight: normal;
        }

        .section-separator {
            border-top: 1px solid #edf2f7;
            margin: 15px 0;
        }

        .technical-grid {
            width: 100%;
        }

        .technical-grid td {
            padding: 4px 0;
            width: 50%;
        }

        .image-box {
            width: 100%;
            margin-bottom: 15px;
            text-align: center;
        }

        .product-image {
            max-width: 200px;
            max-height: 250px;
            border: 1px solid #edf2f7;
            border-radius: 4px;
        }

        .proof-image {
            max-width: 150px;
            max-height: 180px;
            border: 1px solid #0dcaf0;
            border-radius: 4px;
            margin-bottom: 5px;
        }

        .pdf-placeholder {
            width: 120px;
            height: 150px;
            background: #f8fafc;
            border: 1px dashed #cbd5e0;
            color: #ed1c24;
            display: inline-block;
            text-align: center;
            padding-top: 40px;
        }

        .allocation-box {
            text-align: left;
            margin-top: 15px;
            background: #f7fafc;
            padding: 10px;
            border-radius: 4px;
        }

        .wo-footer {
            margin-top: 30px;
            width: 100%;
        }

        .footer-table {
            width: 100%;
            margin-top: 40px;
        }

        .footer-sign-box {
            border-top: 1px solid #000;
            width: 40%;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            padding-top: 5px;
        }

        .note-box {
            background-color: #f7fafc;
            border-left: 4px solid #cbd5e0;
            padding: 10px;
            margin-top: 15px;
            font-style: italic;
        }
    </style>

<body>
    @php
    $logoPath = public_path('images/ajlogo.png');
    $logoBase64 = null;
    if (file_exists($logoPath)) {
    $logoData = file_get_contents($logoPath);
    $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    }

    // Format dates for display
    foreach($workOrders as &$order) {
    if (isset($order['created_at'])) {
    $order['created_at_formatted'] = date('d M, Y', strtotime($order['created_at']));
    }
    if (isset($order['due_date'])) {
    $order['due_date_formatted'] = date('d M, Y', strtotime($order['due_date']));
    }
    }
    @endphp

    @foreach($workOrders as $index => $order)
    <div class="wo-container {{ !$loop->last ? 'page-break' : '' }}">
        <div class="wo-header">
            <table class="header-table">
                <tr>
                    <td>
                        @if($logoBase64)
                        <img src="{{ $logoBase64 }}" style="height: 40px;">
                        @else
                        <div style="font-size: 20px; font-weight: bold; color: #ed1c24;">AJ</div>
                        @endif
                        <div class="header-title">WORK ORDER</div>
                    </td>
                    <td class="wo-number">
                        <div>{{ $order['work_order_number'] }}</div>
                        <div style="font-size: 10px; font-weight: normal; color: #666;">
                            Date: {{ $order['created_at_formatted'] ?? date('d M, Y') }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <table class="main-layout-table">
            <tr>
                <td class="details-column">
                    <div class="info-row">
                        <span class="info-label">Customer Name:</span>
                        <span class="info-value">{{ $order['customer_name'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Order Date:</span>
                        <span class="info-value">{{ $order['due_date_formatted'] ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Product Name:</span>
                        <span class="info-value">{{ $order['product_name'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Quantity:</span>
                        <span class="info-value">{{ $order['quantity'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Category:</span>
                        <span class="info-value">{{ $order['product_category'] ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Subcategory:</span>
                        <span class="info-value">{{ $order['subcategory'] ?? 'N/A' }}</span>
                    </div>

                    <div class="section-separator"></div>

                    <table class="technical-grid">
                        <tr>
                            <td><span class="info-label">Type:</span> <span class="info-value">{{ $order['type'] ?? 'N/A' }}</span></td>
                            <td><span class="info-label">Size:</span> <span class="info-value">{{ $order['size'] ?? 'N/A' }}</span></td>
                        </tr>
                        <tr>
                            <td><span class="info-label">Length:</span> <span class="info-value">{{ $order['length'] ?? 'N/A' }}</span></td>
                            <td><span class="info-label">Weight:</span> <span class="info-value">{{ $order['weight_from'] }}g - {{ $order['weight_to'] }}g</span></td>
                        </tr>
                        <tr>
                            <td><span class="info-label">HUID:</span> <span class="info-value">{{ $order['hallmark'] ?? 'N/A' }}</span></td>
                            <td><span class="info-label">Rodium:</span> <span class="info-value">{{ $order['rodium'] ?? 'N/A' }}</span></td>
                        </tr>
                        <tr>
                            <td><span class="info-label">Hook:</span> <span class="info-value">{{ $order['hook'] ?? 'N/A' }}</span></td>
                            <td><span class="info-label">Stone:</span> <span class="info-value">{{ $order['stone'] ?? 'N/A' }}</span></td>
                        </tr>
                        <tr>
                            <td colspan="2"><span class="info-label">Enamel:</span> <span class="info-value">{{ $order['enamel'] ?? 'N/A' }}</span></td>
                        </tr>
                    </table>

                    @if(!empty($order['narration_craftsman']) && $order['narration_craftsman'] !== '***')
                    <div class="note-box">
                        <strong>Note for Craftsman:</strong>
                        <p style="margin: 5px 0 0 0;">{{ $order['narration_craftsman'] }}</p>
                    </div>
                    @endif
                </td>
                <td class="images-column">
                    @php
                    // Use array keys since $order is transformed data
                    $productGallery = $order['gallery_images'] ?? [];
                    $completionProofs = $order['completion_proof_images'] ?? [];

                    // Robust image path resolver function (inline)
                    $resolveLocalPath = function($url) {
                    if (empty($url)) return null;

                    // 1. If it's already an absolute local path (e.g. C:\... or /var/...)
                    if (file_exists($url)) return $url;

                    // 2. Try to handle URL or relative path
                    $path = parse_url($url, PHP_URL_PATH);
                    if (!$path) $path = $url;

                    // Clean up the path (remove leading slash if it exists)
                    $cleanPath = ltrim($path, '/');

                    // 3. Try common path patterns
                    // Handle /storage/
                    if (str_contains($path, '/storage/')) {
                    $parts = explode('/storage/', $path);
                    $rel = ltrim(end($parts), '/');
                    $full = storage_path('app/public/' . $rel);
                    if (file_exists($full)) return $full;
                    }

                    // Handle /images/ or /uploads/
                    if (str_contains($path, '/images/') || str_contains($path, '/uploads/')) {
                    $marker = str_contains($path, '/images/') ? '/images/' : '/uploads/';
                    $parts = explode($marker, $path);
                    $rel = ltrim(end($parts), '/');
                    $full = public_path(ltrim($marker, '/') . $rel);
                    if (file_exists($full)) return $full;
                    }

                    // 4. Fallback search in public and storage
                    if (file_exists(public_path($cleanPath))) return public_path($cleanPath);
                    if (str_starts_with($cleanPath, 'storage/')) {
                    $sp = storage_path('app/public/' . substr($cleanPath, 8));
                    if (file_exists($sp)) return $sp;
                    }

                    return null;
                    };

                    $toBase64 = function($url) use ($resolveLocalPath) {
                    $local = $resolveLocalPath($url);
                    if ($local && file_exists($local)) {
                    $ext = pathinfo($local, PATHINFO_EXTENSION);
                    if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    try {
                    return 'data:image/'.$ext.';base64,'.base64_encode(file_get_contents($local));
                    } catch (\Exception $e) { return null; }
                    }
                    }

                    // Final desperate attempt: Fetch via URL if remote is enabled
                    // (Only if server allows outbound it self-call)
                    /*
                    try {
                    $data = file_get_contents($url, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
                    if ($data) {
                    $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
                    return 'data:image/'.$ext.';base64,'.base64_encode($data);
                    }
                    } catch (\Exception $e) {}
                    */

                    return null;
                    };
                    @endphp

                    @if(!empty($productGallery))
                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 10px; color: #3182ce; font-weight: bold; margin-bottom: 10px; text-transform: uppercase; border-bottom: 1px solid #ebf8ff; padding-bottom: 2px;">Product Images</div>
                        @foreach($productGallery as $imagePath)
                        @php
                        $isPdf = str_ends_with(strtolower($imagePath), '.pdf');
                        $imgBase64 = $toBase64($imagePath);
                        @endphp

                        @if($isPdf)
                        <div class="pdf-wrapper" style="width: 180px; height: 180px; border: 1px solid #edf2f7; background: #f7fafc; margin: 0 auto 15px; position: relative;">
                            <a href="{{ $imagePath }}" target="_blank" style="text-decoration: none; display: block; height: 100%;">
                                <div style="padding-top: 65px; text-align: center;">
                                    <div style="font-size: 24px; color: #e53e3e; font-weight: bold;">PDF</div>
                                    <div style="font-size: 9px; color: #718096; margin-top: 5px;">Click to View Attachment</div>
                                </div>
                            </a>
                        </div>
                        @elseif($imgBase64)
                        <div style="margin-bottom: 15px; text-align: center;">
                            <img src="{{ $imgBase64 }}" class="product-image">
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @else
                    <div style="padding: 20px; color: #a0aec0; border: 1px dashed #edf2f7; margin-bottom: 20px; font-style: italic;">No Images Available</div>
                    @endif

                    @if(!empty($completionProofs))
                    <div style="border-top: 1px solid #edf2f7; padding-top: 15px; margin-top: 10px;">
                        <div style="font-size: 10px; color: #00b5ad; font-weight: bold; margin-bottom: 10px; text-transform: uppercase;">Completion Proofs</div>
                        @foreach($completionProofs as $imagePath)
                        @php
                        $isPdfProof = str_ends_with(strtolower($imagePath), '.pdf');
                        $proofBase64 = $toBase64($imagePath);
                        @endphp

                        @if($isPdfProof)
                        <div class="pdf-wrapper" style="width: 140px; height: 140px; border: 1px solid #edf2f7; background: #f7fafc; margin: 0 auto 10px;">
                            <a href="{{ $imagePath }}" target="_blank" style="text-decoration: none;">
                                <div style="padding-top: 45px; text-align: center;">
                                    <div style="font-size: 20px; color: #e53e3e; font-weight: bold;">PDF</div>
                                    <div style="font-size: 9px; color: #718096;">View Proof</div>
                                </div>
                            </a>
                        </div>
                        @elseif($proofBase64)
                        <div style="margin-bottom: 10px;">
                            <img src="{{ $proofBase64 }}" class="proof-image" style="width: 150px; height: auto;">
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @endif

                    <div class="allocation-box" style="text-align: left; margin-top: 20px; background: #f8fafc; padding: 12px; border: 1px solid #edf2f7; border-radius: 6px;">
                        <div style="font-size: 9px; color: #718096; font-weight: bold; text-transform: uppercase;">Allocated To:</div>
                        <div style="font-size: 12px; font-weight: bold; color: #2d3748; margin-top: 2px;">{{ $order['allocated_craftsman_bp_code'] ?? 'Not Allocated' }}</div>
                        @if(isset($order['craftsman']['business_name']))
                        <div style="font-size: 10px; color: #4a5568; margin-top: 2px;">{{ $order['craftsman']['business_name'] }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <table class="footer-table">
            <tr>
                <td class="footer-sign-box" style="width: 45%; color: #718096;">ISSUED BY</td>
                <td style="width: 10%;"></td>
                <td class="footer-sign-box" style="width: 45%; color: #718096;">RECEIVED BY</td>
            </tr>
        </table>
    </div>
    @endforeach

    <div style="position: fixed; bottom: 10px; width: 100%; text-align: center; font-size: 9px; color: #a0aec0; border-top: 1px solid #edf2f7; padding-top: 5px;">
        ARIHANTH JEWELLERS PRIVATE LIMITED | Generated on {{ date('d M, Y H:i') }}
    </div>
</body>

</html>