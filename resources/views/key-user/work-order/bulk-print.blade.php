<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Work Orders Report - Key User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        @media print {
            .page-break { page-break-after: always; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; background-color: white !important; }
            .no-print { display: none !important; }
        }
        body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        .work-order-card { 
            min-height: 297mm; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between;
            padding: 40px;
        }
    </style>
</head>
<body class="bg-gray-50">

    <div class="max-w-5xl mx-auto space-y-0">
        @foreach($workOrders as $index => $workOrder)
            <div class="work-order-card bg-white {{ !$loop->last ? 'page-break' : '' }}">
                
                <div class="space-y-8">
                    <!-- Header -->
                    <div class="flex justify-between items-start border-b-2 border-slate-900 pb-6">
                        <div>
                            <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">Work Order</h1>
                            <p class="text-sm text-slate-500 font-medium">Production Specification Sheet</p>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Document Ref</div>
                            <div class="text-xl font-black text-slate-800">#{{ $workOrder->work_order_number }}</div>
                        </div>
                    </div>

                    <div class="flex gap-10 items-start">
                        <div class="flex-1">
                            <h2 class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-900 mb-4">Core Details</h2>
                            
                            <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Status</p>
                                    <p class="text-sm font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded inline-block">{{ ucfirst($workOrder->status) }}</p>
                                </div>

                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Product Category</p>
                                    <p class="text-sm font-bold text-slate-800">{{ $workOrder->product_category ?? '---' }}</p>
                                </div>

                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Order Date</p>
                                    <p class="text-sm font-bold text-slate-700">{{ $workOrder->created_at ? $workOrder->created_at->format('d M, Y') : 'N/A' }}</p>
                                </div>

                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase italic">Due Date</p>
                                    <p class="text-sm font-bold text-red-600 underline underline-offset-2">{{ $workOrder->due_date ? $workOrder->due_date->format('d M, Y') : 'N/A' }}</p>
                                </div>

                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Quantity</p>
                                    <p class="text-sm font-bold text-slate-800">{{ $workOrder->quantity }} <span class="text-xs text-slate-500 font-normal uppercase">{{ $workOrder->type }}</span></p>
                                </div>
                                
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">BP Code</p>
                                    <p class="text-sm font-bold text-slate-800">{{ $workOrder->bp_code }}</p>
                                </div>
                            </div>

                            <div class="mt-8">
                                <h2 class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-900 mb-3 border-b pb-1">Technical Attributes</h2>
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
                                            <div class="flex justify-between text-[11px] bg-slate-50 border border-slate-100 p-2 rounded">
                                                <span class="font-bold text-slate-500">{{ $label }}:</span>
                                                <span class="font-bold text-slate-900">{{ is_array($value) ? implode(', ', $value) : $value }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="w-72 shrink-0 space-y-4">
                            @php
                                $mainImage = null;
                                $imagePath = $workOrder->product_image;
                                
                                if ($imagePath) {
                                    $fullPath = storage_path('app/public/' . $imagePath);
                                    if (!file_exists($fullPath)) {
                                        $fullPath = public_path($imagePath);
                                    }
                                    
                                    if (file_exists($fullPath)) {
                                        $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
                                        $data = file_get_contents($fullPath);
                                        $mainImage = 'data:image/' . $ext . ';base64,' . base64_encode($data);
                                    }
                                }
                            @endphp

                            <div class="aspect-square w-full border-2 border-slate-100 rounded-xl bg-slate-50 flex items-center justify-center overflow-hidden shadow-inner">
                                @if($mainImage)
                                    <img src="{{ $mainImage }}" 
                                         class="w-full h-full object-contain p-2"
                                         style="image-rendering: -webkit-optimize-contrast; image-rendering: crisp-edges;">
                                @else
                                    <div class="text-center">
                                        <div class="text-slate-200 text-4xl mb-1">🖼️</div>
                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">No Image</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 border-l-4 border-slate-900 p-6 rounded-r-lg">
                        <p class="text-[10px] font-black text-slate-900 uppercase mb-2 tracking-widest">Special Instructions / Production Notes</p>
                        <p class="text-sm text-slate-700 italic leading-relaxed">
                            {{ $workOrder->narration_admin ?? 'No specific instructions provided for this production run.' }}
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t-2 border-slate-100 pt-6 flex justify-between items-center text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-slate-900 rounded-full"></span>
                        <span>System ID: {{ $workOrder->id }}</span>
                    </div>
                    <span>Ref: {{ $workOrder->work_order_number }}</span>
                    <span>Generated: {{ now()->format('d/m/Y | H:i') }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
