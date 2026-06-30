<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selected Designs Report - Super Admin</title>
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
        .design-card { 
            min-height: 297mm; 
            padding: 40px;
            display: flex;
            flex-direction: column;
        }
    </style>
</head>
<body class="bg-gray-50">

    <div class="row no-print mb-4 mt-3 flex justify-center gap-4">
        <button onclick="window.print()" class="bg-slate-900 text-white px-6 py-2 rounded-lg font-bold shadow-lg hover:bg-slate-800 transition-all">Print All</button>
        <button onclick="window.close()" class="bg-white text-slate-600 border border-slate-200 px-6 py-2 rounded-lg font-bold shadow-sm hover:bg-slate-50 transition-all">Close</button>
    </div>

    <div class="max-w-5xl mx-auto space-y-0">
        @foreach($designs as $index => $design)
            <div class="design-card bg-white {{ !$loop->last ? 'page-break' : '' }}">
                
                <!-- Header -->
                <div class="flex justify-between items-start border-b-2 border-slate-900 pb-6 mb-8">
                    <div>
                        <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">Design Master</h1>
                        <p class="text-sm text-slate-500 font-medium">Production Specification Archive - Super Admin</p>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Entry Ref</div>
                        <div class="text-xl font-black text-slate-800 italic">#{{ $index + 1 }}</div>
                        
                        @if($design->design_status === 'Accepted' && $design->qr_code)
                            @php
                                $qrBase64 = null;
                                $qrFullPath = storage_path('app/public/' . $design->qr_code);
                                if (file_exists($qrFullPath)) {
                                    $qrExt = pathinfo($qrFullPath, PATHINFO_EXTENSION);
                                    $qrData = @file_get_contents($qrFullPath);
                                    if($qrData) {
                                        $qrBase64 = 'data:image/' . ($qrExt == 'svg' ? 'svg+xml' : $qrExt) . ';base64,' . base64_encode($qrData);
                                    }
                                }
                            @endphp
                            @if($qrBase64)
                                <div class="mt-4 flex justify-end">
                                    <img src="{{ $qrBase64 }}" class="w-20 h-20 border border-slate-100 p-1">
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="flex gap-10 items-start flex-grow">
                    <!-- Left: Details -->
                    <div class="flex-1 space-y-8">
                        <div>
                            <h2 class="text-2xl font-black text-slate-900 leading-tight mb-2">{{ $design->product_name }}</h2>
                            <div class="flex gap-3">
                                <span class="bg-indigo-600 text-white px-3 py-1 rounded text-[10px] font-black uppercase tracking-widest font-mono">
                                    {{ $design->design_code ?? 'NO-CODE' }}
                                </span>
                                <span class="bg-emerald-500 text-white px-3 py-1 rounded text-[10px] font-black uppercase tracking-widest">
                                    {{ $design->design_status ?? 'Approved' }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-sm">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Product Category</p>
                                <p class="font-bold text-slate-800">{{ optional($design->category)->name ?? '---' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Sub-Category</p>
                                <p class="font-bold text-slate-800">{{ optional($design->subcategory)->name ?? '---' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Weight Constraint</p>
                                <p class="font-bold text-slate-900 underline underline-offset-4 decoration-slate-200">{{ $design->weight_from }}g - {{ $design->weight_to }}g</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Architect/Creator</p>
                                <p class="font-bold text-slate-700 italic underline decoration-slate-100 italic font-serif leading-none">{{ $design->creator_name }}</p>
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-xl p-6 border border-slate-100 shadow-inner">
                            <h3 class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-4 italic underline decoration-slate-200">Execution DNA</h3>
                            <div class="grid grid-cols-3 gap-y-6">
                                @php
                                    $specs = [
                                        'Type' => $design->order_type,
                                        'Size' => $design->size,
                                        'Length' => $design->length,
                                        'Hallmark' => $design->hallmark,
                                        'Rodium' => $design->rodium,
                                        'Hook' => $design->hook
                                    ];
                                @endphp

                                @foreach($specs as $label => $val)
                                    <div class="flex flex-col">
                                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-tighter">{{ $label }}</span>
                                        <span class="text-[11px] font-black text-slate-900 mt-0.5">{{ $val ?: '---' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if($design->details)
                        <div class="border-l-4 border-slate-900 pl-6 py-2">
                            <p class="text-[9px] font-black text-slate-400 uppercase mb-2">Internal Specification Narrative</p>
                            <p class="text-sm text-slate-700 leading-relaxed font-medium italic">
                                "{{ $design->details }}"
                            </p>
                        </div>
                        @endif
                    </div>

                    <!-- Right: Image -->
                    <div class="w-80 shrink-0">
                        @php
                            $mainImage = null;
                            if ($design->images->count()) {
                                $imagePath = $design->images->first()->path;
                                $fullPath = storage_path('app/public/' . $imagePath);
                                if (!file_exists($fullPath)) {
                                    $fullPath = public_path($imagePath);
                                }
                                
                                if (file_exists($fullPath)) {
                                    $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
                                    $data = @file_get_contents($fullPath);
                                    if($data) {
                                        $mainImage = 'data:image/' . $ext . ';base64,' . base64_encode($data);
                                    }
                                }
                            }
                        @endphp

                        <div class="aspect-[3/4] w-full bg-slate-50 rounded-2xl border-2 border-slate-100 flex items-center justify-center p-3 shadow-inner relative overflow-hidden">
                             @if($mainImage)
                                <img src="{{ $mainImage }}" class="w-full h-full object-contain" style="image-rendering: -webkit-optimize-contrast;">
                                <div class="absolute top-2 right-2 bg-slate-900/50 backdrop-blur-sm text-white text-[8px] px-2 py-0.5 rounded font-black tracking-widest uppercase">HD Render</div>
                            @else
                                <div class="text-center">
                                    <div class="text-slate-200 text-6xl mb-2">📐</div>
                                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">Asset Pending</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t-2 border-slate-100 pt-6 flex justify-between items-center text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-12">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 bg-slate-900 rounded-full"></span>
                        <span>Arihanth Jewellers • Design Assets Division</span>
                    </div>
                    <span>Record Entry: {{ $design->created_at->format('d/M/Y') }}</span>
                    <span>System Print ID: {{ now()->format('Ymd-Hi') }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        window.onload = function() {
            setTimeout(() => { window.print(); }, 1000);
        };
    </script>
</body>
</html>