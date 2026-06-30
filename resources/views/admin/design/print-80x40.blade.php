<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tag Print</title>
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
                display: block;
                width: 80mm;
                height: 45mm;
                background-color: white;
            }
        }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
            width: 80mm;
            background-color: white;
        }

        .label-container {
            width: 40mm;
            height: 40mm;
            float: left;
        }

        .left-label {
            padding: 2mm 0 0 2mm;
        }

        .right-label {
            padding: 2mm 2mm 0 0;
        }
    </style>
</head>
<body onload="window.print()">
    @if(isset($designs) && count($designs) > 0)
        @foreach($designs->chunk(2) as $chunk)
        @php $chunk = $chunk->values(); @endphp
        <div class="page-break">
            
            <!-- LEFT LABEL -->
            @if(isset($chunk[0]))
            <div class="label-container left-label">
                <div class="w-[34mm]">
                    <!-- QR Code -->
                    <div class="mb-[1mm]">
                        @if($chunk[0]->qr_code)
                            <img src="{{ asset('storage/' . $chunk[0]->qr_code) }}"
                                 alt="QR Code"
                                 class="max-h-[19mm] w-auto object-contain">
                        @else
                            <div class="border border-dashed border-gray-400 w-[15mm] h-[15mm] flex items-center justify-center text-[7pt]">
                                No QR
                            </div>
                        @endif
                    </div>
                   
                    
                    <!-- Text Details -->
                    <div class="leading-tight">
                        <div class="text-[12.5pt] font-black uppercase">
                            {{ $chunk[0]->design_code ?? 'CODE' }}
                        </div>
                        <div class="text-[10.2pt] font-bold">
                            {{ $chunk[0]->weight_to ? number_format($chunk[0]->weight_to, 3) . 'g' : '-' }}
                        </div>
                        <div class="text-[9pt] font-semibold uppercase">
                            {{ $chunk[0]->category->name ?? '' }}
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- RIGHT LABEL -->
            @if(isset($chunk[1]))
            <div class="label-container right-label">
                <div class="w-[34mm] ml-auto">
                    <!-- QR Code -->
                    <div class="mb-[1mm] flex justify-end">
                        @if($chunk[1]->qr_code)
                            <img src="{{ asset('storage/' . $chunk[1]->qr_code) }}"
                                 alt="QR Code"
                                 class="max-h-[19mm] w-auto object-contain">
                        @else
                            <div class="border border-dashed border-gray-400 w-[15mm] h-[15mm] flex items-center justify-center text-[7pt]">
                                No QR
                            </div>
                        @endif
                    </div>
                   
                    
                    <!-- Text Details -->
                    <div class="leading-tight text-right">
                        <div class="text-[12.5pt] font-black uppercase">
                            {{ $chunk[1]->design_code ?? 'CODE' }}
                        </div>
                        <div class="text-[10.2pt] font-bold">
                            {{ $chunk[1]->weight_to ? number_format($chunk[1]->weight_to, 3) . 'g' : '-' }}
                        </div>
                        <div class="text-[9pt] font-semibold uppercase">
                            {{ $chunk[1]->category->name ?? '' }}
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
        @endforeach
    @else
        <div class="p-10 text-center">No designs found to print.</div>
    @endif
</body>
</html>