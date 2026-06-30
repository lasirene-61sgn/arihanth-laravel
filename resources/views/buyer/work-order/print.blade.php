<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Order - {{ $workOrder->work_order_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white; padding: 0; margin: 0; }
            .page-container {
                height: 297mm; /* Full A4 height */
                width: 210mm;  /* Full A4 width */
                padding: 1.5rem !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 antialiased text-gray-900 font-sans">

    <div class="no-print bg-white p-4 flex justify-center gap-4 border-b border-gray-200 sticky top-0 z-50 shadow-sm">
        <button onclick="window.print()" class="px-6 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold rounded-md transition-all shadow">
            Print / Save PDF
        </button>
        <button onclick="saveAsImage()" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-md transition-all shadow">
            Download PNG
        </button>
    </div>

    <div id="print-content">
        <div class="page-container max-w-4xl mx-auto bg-white border-x border-gray-200 md:my-8 shadow-2xl overflow-hidden flex flex-col justify-between min-h-[1056px] p-10">
            
            <div class="space-y-8">
                <div class="flex justify-between items-start border-b-2 border-indigo-600 pb-6">
                    <div>
                        <h1 class="text-3xl font-black text-indigo-900 tracking-tighter uppercase">Work Order</h1>
                        <p class="text-sm text-gray-500 font-medium">Production Specification Sheet</p>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">Document Ref</div>
                        <div class="text-xl font-black text-gray-800">#{{ $workOrder->work_order_number }}</div>
                    </div>
                </div>

                <div class="flex gap-10 items-start">
                    <div class="flex-1">
                        <h2 class="text-[11px] font-black uppercase tracking-[0.2em] text-indigo-600 mb-4">Core Details</h2>
                        
                        <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Status</p>
                                <p class="text-sm font-bold text-indigo-800 bg-indigo-50 px-2 py-0.5 rounded inline-block">{{ ucfirst($workOrder->status) }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Product Category</p>
                                <p class="text-sm font-bold text-gray-800">{{ $workOrder->product_category ?? '---' }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Order Date</p>
                                <p class="text-sm font-bold text-gray-700">{{ $workOrder->created_at ? $workOrder->created_at->format('d M, Y') : 'N/A' }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase italic">Due Date</p>
                                <p class="text-sm font-bold text-red-600 underline underline-offset-2">{{ $workOrder->due_date ? $workOrder->due_date->format('d M, Y') : 'N/A' }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Quantity</p>
                                <p class="text-sm font-bold text-gray-800">{{ $workOrder->quantity }} <span class="text-xs text-gray-500 font-normal uppercase">{{ $workOrder->type }}</span></p>
                            </div>
                            
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">BP / weight </p>
                                <p class="text-sm font-bold text-gray-800">{{ $workOrder->bp_code }} / {{ $workOrder->weight_from ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="mt-8">
                            <h2 class="text-[11px] font-black uppercase tracking-[0.2em] text-indigo-600 mb-3 border-b pb-1">Technical Attributes</h2>
                            <div class="grid grid-cols-2 gap-2">
                                @php
                                    $attributes = [
                                        'Size' => $workOrder->size,
                                        'Hook' => $workOrder->hook,
                                        'Stone' => $workOrder->stone,
                                        'Hallmark' => $workOrder->hallmark,
                                        'Rodium' => $workOrder->rodium,
                                        'Enamel' => $workOrder->enamel
                                    ];
                                @endphp

                                @foreach($attributes as $label => $value)
                                    @if($value)
                                        <div class="flex justify-between text-[11px] bg-gray-50 border border-gray-100 p-2 rounded">
                                            <span class="font-bold text-gray-500">{{ $label }}:</span>
                                            <span class="font-bold text-gray-900">{{ is_array($value) ? implode(', ', $value) : $value }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="w-64 shrink-0 space-y-4">
                        <div class="aspect-square w-full border-2 border-gray-100 rounded-xl bg-gray-50 flex items-center justify-center overflow-hidden shadow-inner">
                            @if($workOrder->product_image_url)
                                <img src="{{ $workOrder->product_image_url }}" crossorigin="anonymous" class="w-full h-full object-contain p-2 hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="text-center">
                                    <div class="text-gray-200 text-4xl mb-1">🖼️</div>
                                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">No Image</span>
                                </div>
                            @endif
                        </div>

                        @if(!empty($workOrder->gallery_images))
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(array_slice($workOrder->gallery_images, 0, 4) as $galleryImg)
                                <div class="aspect-square border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm">
                                    <img src="{{ $galleryImg }}" crossorigin="anonymous" class="w-full h-full object-contain">
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                <div class="bg-slate-50 border-l-4 border-indigo-500 p-6 rounded-r-lg">
                    <p class="text-[10px] font-black text-indigo-900 uppercase mb-2 tracking-widest">Special Instructions / Production Notes</p>
                    <p class="text-sm text-gray-700 italic leading-relaxed">
                        {{ $workOrder->narration_admin ?? 'No specific instructions provided for this production run.' }}
                    </p>
                </div>
            </div>

            <div class="border-t-2 border-gray-100 pt-6 flex justify-between items-center text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 bg-indigo-600 rounded-full"></span>
                    <span>System ID: {{ $workOrder->id }}</span>
                </div>
                <span>Ref: {{ $workOrder->work_order_number }}</span>
                <span>Generated: {{ now()->format('d/m/Y | H:i') }}</span>
            </div>
        </div>
    </div>

    <script>
        function saveAsImage() {
            const content = document.getElementById('print-content');
            html2canvas(content, { 
                scale: 3, // High quality
                useCORS: true, 
                backgroundColor: "#ffffff",
                logging: false
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'Work-Order-{{ $workOrder->work_order_number }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        }
    </script>
</body>
</html>