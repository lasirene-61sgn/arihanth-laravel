@extends('admin.layouts.print')

@section('title', 'Bulk Print Work Orders')

@section('styles')
<style>
    @media print {
        .page-break {
            page-break-after: always;
        }

        .page-break:last-child {
            page-break-after: auto;
        }

        body {
            -webkit-print-color-adjust: exact;
            background-color: white !important;
        }

        .no-print {
            display: none !important;
        }

        .wo-container {
            box-shadow: none !important;
            border: 1px solid #e2e8f0 !important;
            margin: 0 auto 30px auto !important;
        }
    }

    body {
        background-color: #f8fafc;
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }

    .wo-container {
        max-width: 850px;
        margin: 30px auto;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    /* Header Styling */
    .header-section {
        padding: 25px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #f8fafc;
    }

    .brand-title {
        margin: 10px 0 0 0;
        font-size: 18px;
        color: #0f172a;
        font-weight: 700;
    }

    .report-label {
        font-size: 11px;
        color: #94a3b8;
        text-transform: uppercase;
        font-weight: bold;
        letter-spacing: 1px;
    }

    .wo-number {
        font-size: 20px;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }

    /* Content Layout */
    .wo-body {
        padding: 30px;
    }

    .wo-banner {
        margin-bottom: 25px;
        background: #1e293b;
        padding: 15px 20px;
        border-radius: 6px;
        color: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .info-table {
        width: 100%;
        border-collapse: collapse;
    }

    .info-label {
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
        color: #64748b;
        font-size: 13px;
        width: 35%;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
        color: #0f172a;
        font-size: 14px;
        font-weight: 600;
    }

    /* Images */
    .product-image-box {
        border: 1px solid #edf2f7;
        background-color: #f8fafc;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 15px;
        text-align: center;
    }

    .product-image {
        max-width: 100%;
        max-height: 600px;
        object-fit: contain;
    }

    /* Footer / Signatures */
    .wo-footer-stats {
        background: #f8fafc;
        padding: 15px 25px;
        border-top: 1px solid #f1f5f9;
        text-align: center;
        font-size: 10px;
        color: #cbd5e1;
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .signature-area {
        display: flex;
        justify-content: space-between;
        padding: 40px 30px 20px 30px;
    }

    .sig-box {
        width: 200px;
        border-top: 1.5px solid #1e293b;
        text-align: center;
        padding-top: 8px;
        font-size: 12px;
        font-weight: 700;
        color: #1e293b;
        text-transform: uppercase;
    }
</style>
@endsection

@section('content')
<div class="row no-print mb-4 mt-3">
    <div class="col-12 text-center">
        <button onclick="window.print()" class="btn btn-dark px-4"><i class="bi bi-printer"></i> Print All Work Orders</button>
        <button onclick="window.close()" class="btn btn-outline-secondary px-4">Close</button>
    </div>
</div>

@foreach($workOrders as $workOrder)
<div class="wo-container {{ !$loop->last ? 'page-break' : '' }}">

    <div class="header-section">
        <div>
            <img src="{{ asset('images/ajlogo.png') }}" alt="Logo" style="height: 55px;">
            <h2 class="brand-title">Arihanth Jewellers Pvt Ltd</h2>
        </div>
        <div style="text-align: right;">
            <span class="report-label">Official Work Order</span><br>
            <h3 class="wo-number">{{ $workOrder->work_order_number }}</h3>
            <span style="font-size: 12px; color: #64748b;">Date: {{ $workOrder->created_at->format('d M, Y') }}</span>
        </div>
    </div>

    <div class="wo-body">
        <div class="wo-banner">
            <div>
                <span style="font-size: 11px; opacity: 0.8; text-transform: uppercase;">Product Name</span>
                <div style="font-size: 18px; font-weight: 600;">{{ $workOrder->product_name }}</div>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 11px; opacity: 0.8; text-transform: uppercase;">Quantity</span>
                <div style="font-size: 18px; font-weight: 600;">{{ $workOrder->quantity }} {{ $workOrder->type ?: 'PCS' }}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-7">
                <table class="info-table">
                    @if($workOrder->due_date)
                    <tr>
                        <td class="info-label">Due Date</td>
                        <td class="info-value" style="color: #e11d48;">{{ $workOrder->due_date->format('d M, Y') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="info-label">Category</td>
                        <td class="info-value">{{ $workOrder->productCategory->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Subcategory</td>
                        <td class="info-value">{{ $workOrder->subcategoryRelation->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Weight Range</td>
                        <td class="info-value">{{ $workOrder->weight_from }} To {{ $workOrder->weight_to }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Size / Length</td>
                        <td class="info-value">{{ $workOrder->size ?? 'N/A' }} / {{ $workOrder->length ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">HUID / Rodium</td>
                        <td class="info-value">{{ $workOrder->hallmark }} / {{ $workOrder->rodium }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Stone / Enamel</td>
                        <td class="info-value">{{ $workOrder->stone }} / {{ $workOrder->enamel }}</td>
                    </tr>
                </table>

                @if($workOrder->narration_craftsman)
                <div style="margin-top: 20px; padding: 15px; background: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 4px;">
                    <strong style="font-size: 11px; text-transform: uppercase; color: #92400e;">Craftsman Instructions:</strong>
                    <p style="margin: 5px 0 0 0; font-size: 13px; color: #78350f; line-height: 1.5;">{{ $workOrder->narration_craftsman }}</p>
                </div>
                @endif
            </div>

            <div class="col-5">
                <div class="product-image-box">
                    <span class="report-label" style="display:block; margin-bottom:10px;">Reference Gallery</span>

                    @php
                    $productGallery = $workOrder->product_gallery_images;
                    $processedImages = [];
                    foreach ($productGallery ?? [] as $imagePath) {
                    if (!$imagePath || !is_string($imagePath)) continue;

                    if (Str::endsWith(strtolower($imagePath), '.pdf')) {
                    $processedImages[] = ['type' => 'pdf', 'content' => $imagePath];
                    continue;
                    }

                    // Try to get local path for Base64 conversion
                    $relativePath = str_replace(asset(''), '', $imagePath);
                    $fullPath = public_path(ltrim($relativePath, '/'));

                    if (!file_exists($fullPath)) {
                    $storagePath = str_replace('storage/', '', $relativePath);
                    $fullPath = storage_path('app/public/' . ltrim($storagePath, '/'));
                    }

                    if (file_exists($fullPath) && !empty(getimagesize($fullPath))) {
                    $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
                    $data = file_get_contents($fullPath);
                    $base64 = 'data:image/' . $ext . ';base64,' . base64_encode($data);
                    $processedImages[] = ['type' => 'image', 'content' => $base64];
                    } else {
                    $processedImages[] = ['type' => 'image', 'content' => $imagePath];
                    }
                    }
                    @endphp

                    @if(!empty($processedImages))
                    @foreach($processedImages as $img)
                    @if($img['type'] === 'pdf')
                    <div class="pdf-wrapper mb-2" style="border: 1px solid #ddd;">
                        <canvas class="pdf-canvas" data-url="{{ $img['content'] }}" data-desired-width="350" style="width: 100%; height: auto; object-fit: contain;"></canvas>
                    </div>
                    @else
                    <img src="{{ $img['content'] }}" class="product-image mb-2" style="max-width:100%; height: auto; border: 1px solid #ddd;">
                    @endif
                    @endforeach
                    @else
                    <div style="padding: 40px 0; color: #cbd5e1; font-size: 12px;">NO IMAGE AVAILABLE</div>
                    @endif
                </div>

                <div style="padding: 10px; border-radius: 6px; border: 1px dashed #cbd5e0;">
                    <span class="report-label">Allocated Craftsman</span>
                    <div style="font-weight: 700; color: #1e293b; font-size: 14px; margin-top: 5px;">
                        {{ $workOrder->allocated_craftsman_bp_code ?? 'PENDING ALLOCATION' }}
                    </div>
                    @if($workOrder->craftsman)
                    <div style="font-size: 12px; color: #64748b;">{{ $workOrder->craftsman->business_name }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="signature-area">
            <div class="sig-box">Issued By</div>
            <div class="sig-box">Received By</div>
        </div>
    </div>

    <div class="wo-footer-stats">
        Arihanth Jewellers Pvt Ltd - Internal Production Document
    </div>
</div>
@endforeach

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

    async function redactSensitiveText(page, context, viewport) {
        const textContent = await page.getTextContent();
        const items = textContent.items;
        const fieldsToHide = ["order id", "design", "qty", "weight", "size", "product", "tolerance from", "tolerance to"];

        for (let i = 0; i < items.length; i++) {
            let current = items[i].str.toLowerCase();
            let next = items[i + 1] ? items[i + 1].str.toLowerCase() : "";
            let combined = current + " " + next;

            fieldsToHide.forEach(field => {
                if (current.includes(field) || combined.includes(field)) {
                    const labelTx = pdfjsLib.Util.transform(viewport.transform, items[i].transform);
                    const labelY = labelTx[5];
                    for (let k = 0; k < items.length; k++) {
                        const valueTx = pdfjsLib.Util.transform(viewport.transform, items[k].transform);
                        if (Math.abs(labelY - valueTx[5]) < 3) {
                            context.fillStyle = "#FFFFFF";
                            context.fillRect(valueTx[4] - 2, valueTx[5] - (items[k].height * viewport.scale), (items[k].width * viewport.scale) + 20, (items[k].height * viewport.scale) + 4);
                        }
                    }
                }
            });
        }
        if (items.length == 0) {
            context.fillStyle = "#FFFFFF";
            context.fillRect(viewport.width * 0.04, viewport.height * 0.03, viewport.width * 0.50, viewport.height * 0.20);
        }
    }

    async function renderPdfs() {
        const canvases = document.querySelectorAll('.pdf-canvas');
        for (const canvas of canvases) {
            const url = canvas.dataset.url;
            try {
                const pdf = await pdfjsLib.getDocument(url).promise;
                for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                    let currentCanvas = canvas;
                    if (pageNum > 1) {
                        currentCanvas = document.createElement('canvas');
                        currentCanvas.className = 'pdf-canvas mb-3';
                        currentCanvas.style.width = '100%';
                        currentCanvas.style.border = '1px solid #ddd';
                        canvas.parentElement.appendChild(currentCanvas);
                    }
                    const page = await pdf.getPage(pageNum);

                    const scale = 4.0;
                    const viewport = page.getViewport({
                        scale: scale
                    });

                    const context = currentCanvas.getContext('2d');
                    currentCanvas.width = viewport.width;
                    currentCanvas.height = viewport.height;
                    currentCanvas.style.width = '100%';

                    await page.render({
                        canvasContext: context,
                        viewport: viewport
                    }).promise;
                    await redactSensitiveText(page, context, viewport);
                }
            } catch (error) {
                console.error("Error rendering PDF:", error);
            }
        }
    }

    window.onload = function() {
        renderPdfs().then(() => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('auto_print')) {
                setTimeout(() => {
                    window.print();
                }, 1000);
            }
        });
    }
</script>
@endsection