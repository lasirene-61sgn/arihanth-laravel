<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jewelry Tag Print</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: 80mm 45mm;
            margin: 0;
        }
        
        @media print {
            body {
                margin: 0 !important;
                padding: 0 !important;
                width: 80mm;
                height: 45mm;
                -webkit-print-color-adjust: exact;
            }
            .page-break {
                page-break-after: always;
                page-break-inside: avoid;
                display: flex;
                justify-content: space-between; /* Pushes the tags to the absolute left and right ends */
                align-items: center;
                width: 80mm;
                height: 45mm;
                background-color: white;
                box-sizing: border-box;
                padding: 0 4mm; /* Adjust this to match your physical label edge margins */
            }
        }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
            width: 80mm;
            height: 45mm;
            background-color: white;
            font-family: sans-serif;
            overflow: hidden;
        }

        /* Container for a single tag side */
        .label-container {
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            box-sizing: border-box;
        }

        /* Left column inside the tag: QR code + design code underneath */
        .left-column {
            display: flex;
            flex-direction: column;
            align-items: center; 
            text-align: center;
        }

        /* Right column inside the tag: Weight, Size, Product Name */
        .right-column {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            text-align: right;
            margin-left: 2.5mm; /* Exact gap between QR and the side text */
            margin-top: 0.5mm;  /* Visual alignment with the top of the QR code */
        }

        /* Clean small QR block size matching image_0059f6.png */
        .qr-box {
            width: 7.5mm; 
            height: 7.5mm;
            flex-shrink: 0;
            margin-bottom: 1.5mm;
        }

        .qr-box img {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* Bold, crisp text style */
        .tag-text {
            font-size: 6.5pt; 
            font-weight: 700;
            color: #000;
            text-transform: uppercase;
            white-space: nowrap;
            line-height: 1.2;
        }
        
        .design-code-text {
            font-size: 7.5pt; /* Sightly larger to match your image reference */
            font-weight: 800;
            color: #000;
            text-transform: uppercase;
            white-space: nowrap;
        }
    </style>
</head>
<body onload="window.print()">
    @if(isset($designs) && count($designs) > 0)
        @foreach($designs->chunk(2) as $chunk)
        @php $chunk = $chunk->values(); @endphp
        <div class="page-break">
            
            @foreach($chunk as $item)
            <div class="label-container">
                
                <!-- LEFT PIECE: QR & Code -->
                <div class="left-column">
                    <div class="qr-box">
                        @if($item->qr_code)
                            <img src="{{ asset('storage/' . $item->qr_code) }}" alt="QR">
                        @else
                            <div class="border border-black w-full h-full"></div>
                        @endif
                    </div>
                    <div class="design-code-text">{{ $item->design_code ?? 'CODE' }}</div>
                </div>

                <!-- RIGHT PIECE: Weight, Size, and Name stacked -->
                <div class="right-column">
                    <div class="tag-text">
                        {{ $item->weight_to ? number_format($item->weight_to, 3) : '0.000' }}G
                    </div>
                    
                    <div class="tag-text">
                        {{ (isset($item->size) && $item->size) ? $item->size : '-' }}
                    </div>
                    
                    <div class="tag-text">
                        {{ $item->category->name ?? 'PRD' }}
                    </div>
                </div>

            </div>
            @endforeach

        </div>
        @endforeach
    @else
        <div class="p-10 text-center">No designs found to print.</div>
    @endif
</body>
</html>