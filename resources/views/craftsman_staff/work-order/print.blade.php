<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .page-container {
                height: 100vh;
                width: 100vw;
                page-break-after: always !important;
                break-after: page !important;
                display: flex;
                flex-direction: column;
                padding: 2rem !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 antialiased text-gray-900">

    <div class="no-print bg-white p-4 flex justify-center gap-4 border-b border-gray-200 sticky top-0 z-50">
        <button onclick="window.print()" class="px-5 py-2 bg-slate-800 text-white text-sm font-medium rounded shadow">
            Print / Save PDF
        </button>
        <button onclick="saveAsImage()" class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded shadow">
            Download PNG
        </button>
    </div>

    <div id="print-content">
        @foreach($workOrders as $workOrder)
        <div class="page-container max-w-3xl mx-auto bg-white border-x border-gray-200 md:my-4 shadow-sm overflow-hidden flex flex-col justify-between h-screen">
            
            <div class="space-y-4">
                <div class="flex gap-6 items-start">
                    
                    <div class="flex-1">
                        <h2 class="text-[10px] font-black uppercase tracking-widest text-blue-600 border-b pb-1 mb-4">Specifications</h2>
                        
                        <div class="grid grid-cols-2 gap-x-4 gap-y-3">
                            <div class="col-span-2">
                                <p class="text-[9px] font-bold text-gray-400 uppercase">WorkOrder</p>
                                <p class="text-lg font-black leading-none">{{ $workOrder->work_order_number }}</p>
                            </div>

                            <div>
                                <p class="text-[9px] font-bold text-gray-400 uppercase">Status</p>
                                <p class="text-[11px] font-bold text-blue-600">{{ $workOrder->craftsman_status }}</p>
                            </div>

                            <div>
                                <p class="text-[9px] font-bold text-gray-400 uppercase">Due Date</p>
                                <p class="text-[11px] font-bold text-red-500">{{ $workOrder->craftsman_due_date ?? 'N/A' }}</p>
                            </div>

                            <div>
                                <p class="text-[9px] font-bold text-gray-400 uppercase">Weight</p>
                                <p class="text-[11px] font-bold">{{ $workOrder->weight_from ?? '---' }}</p>
                            </div>

                            <div>
                                <p class="text-[9px] font-bold text-gray-400 uppercase">Qty</p>
                                <p class="text-[11px] font-bold">{{ $workOrder->quantity ?? '1' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-1 mt-4">
                            @if($workOrder->size) <div class="text-[10px] bg-gray-50 p-1 px-2 rounded"><b>Size:</b> {{ is_array($workOrder->size) ? implode(', ', $workOrder->size) : $workOrder->size }}</div> @endif
                            @if($workOrder->hook) <div class="text-[10px] bg-gray-50 p-1 px-2 rounded"><b>Hook:</b> {{ is_array($workOrder->hook) ? implode(', ', $workOrder->hook) : $workOrder->hook }}</div> @endif
                            @if($workOrder->stone) <div class="text-[10px] bg-gray-50 p-1 px-2 rounded"><b>Stone:</b> {{ is_array($workOrder->stone) ? implode(', ', $workOrder->stone) : $workOrder->stone }}</div> @endif
                        </div>
                    </div>

                    <div class="w-48 shrink-0 space-y-3">
                        <div class="aspect-square w-full border border-gray-200 rounded-lg bg-gray-50 flex items-center justify-center overflow-hidden">
                            @if(!empty($workOrder->product_gallery_images))
                                <img src="{{ $workOrder->product_gallery_images[0] }}" crossorigin="anonymous" class="w-full h-full object-contain p-1">
                            @else
                                <span class="text-[9px] text-gray-300">NO IMAGE</span>
                            @endif
                        </div>

                        @if(!empty($workOrder->completion_proof_images))
                        <div class="grid grid-cols-2 gap-1">
                            @foreach(array_slice($workOrder->completion_proof_images, 0, 2) as $proof)
                                <div class="aspect-square border border-gray-100 rounded overflow-hidden">
                                    <img src="{{ $proof }}" crossorigin="anonymous" class="w-full h-full object-contain">
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                <div class="border-t pt-3">
                    <p class="text-[9px] font-bold text-gray-400 uppercase mb-1">Notes</p>
                    <p class="text-[11px] text-gray-600 italic leading-snug line-clamp-4">"{{ is_array($workOrder->narration_craftsman) ? implode(', ', $workOrder->narration_craftsman) : $workOrder->narration_craftsman }}"</p>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-3 flex justify-between items-center text-[9px] text-gray-300 font-bold uppercase tracking-widest">
                <span>Ref: {{ $workOrder->work_order_number }}</span>
                <span>Generated: {{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <script>
        function saveAsImage() {
            const content = document.getElementById('print-content');
            html2canvas(content, { scale: 2, useCORS: true, backgroundColor: "#ffffff" }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'Work-Order.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        }
    </script>
</body>
</html>
