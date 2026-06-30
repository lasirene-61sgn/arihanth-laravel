<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selected Catalogue Items - Super Admin</title>
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
        .catalogue-card { 
            min-height: 297mm; 
            padding: 40px;
            display: flex;
            flex-direction: column;
        }
    </style>
</head>
<body class="bg-gray-50">

    <div class="row no-print mb-4 mt-3 flex justify-center gap-4">
        <button onclick="window.print()" class="bg-indigo-900 text-white px-6 py-2 rounded-lg font-bold shadow-lg hover:bg-indigo-800 transition-all">Print All</button>
        <button onclick="window.close()" class="bg-white text-slate-600 border border-slate-200 px-6 py-2 rounded-lg font-bold shadow-sm hover:bg-slate-50 transition-all">Close</button>
    </div>

    <div class="max-w-5xl mx-auto space-y-0">
        @foreach($products as $index => $product)
            <div class="catalogue-card bg-white {{ !$loop->last ? 'page-break' : '' }}">
                
                <!-- Header -->
                <div class="flex justify-between items-start border-b-2 border-indigo-900 pb-6 mb-8">
                    <div>
                        <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">Catalogue Item</h1>
                        <p class="text-sm text-slate-500 font-medium">Master Inventory Specification - Super Admin</p>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-indigo-900">SKU Ref</div>
                        <div class="text-xl font-black text-slate-800">{{ $product->product_code }}</div>
                    </div>
                </div>

                <div class="flex gap-10 items-start flex-grow">
                    <!-- Left: Details -->
                    <div class="flex-1 space-y-8">
                        <div>
                            <h2 class="text-2xl font-black text-slate-900 leading-tight mb-2">{{ $product->product_name }}</h2>
                            <div class="flex gap-3">
                                <span class="bg-indigo-600 text-white px-3 py-1 rounded text-[10px] font-black uppercase tracking-widest font-mono">
                                    DSN: {{ $product->design_code ?? '---' }}
                                </span>
                                <span class="bg-white text-indigo-700 px-3 py-1 rounded text-[10px] font-black uppercase tracking-widest border border-indigo-200 shadow-sm">
                                    BP: {{ $product->bp_code ?? 'Not Set' }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-sm">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Taxonomy</p>
                                <p class="font-bold text-slate-800 uppercase tracking-tight">{{ optional($product->category)->name ?? '---' }} <span class="text-indigo-300 mx-1">/</span> {{ optional($product->subcategory)->name ?? '---' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Production Type</p>
                                <p class="font-bold text-slate-800 uppercase tracking-widest text-xs">{{ $product->type ?: 'STANDARD' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Weight Constraint</p>
                                <p class="text-lg font-black text-slate-900 underline underline-offset-8 decoration-emerald-200">{{ number_format($product->weight_from, 2) }}g - {{ number_format($product->weight_to, 2) }}g</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Record Owner</p>
                                <p class="font-bold text-indigo-900 italic font-serif">{{ $product->creator_name }}</p>
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 shadow-inner">
                            <h3 class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Technical Breakdown</h3>
                            <div class="grid grid-cols-3 gap-y-6">
                                @php
                                    $techSpecs = [
                                        'Hook' => $product->hook,
                                        'Size' => $product->size,
                                        'Length' => $product->length,
                                        'Hallmark' => $product->hallmark,
                                        'Rodium' => $product->rodium
                                    ];
                                @endphp

                                @foreach($techSpecs as $label => $val)
                                    <div class="flex flex-col">
                                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-tighter">{{ $label }}</span>
                                        <span class="text-[11px] font-black text-slate-900 mt-0.5">{{ $val ?: '---' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if($product->details)
                        <div class="bg-indigo-50/20 p-5 rounded-xl border-l-4 border-indigo-900 shadow-sm">
                            <p class="text-[8px] font-black text-indigo-400 uppercase mb-2 tracking-widest">Architectural Notes</p>
                            <p class="text-sm text-slate-700 leading-relaxed font-medium italic">
                                "{{ $product->details }}"
                            </p>
                        </div>
                        @endif
                    </div>

                    <!-- Right: Image -->
                    <div class="w-80 shrink-0">
                        @php
                            $mainImage = null;
                            if ($product->images->count()) {
                                $imagePath = $product->images->first()->path;
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

                        <div class="aspect-[1/1] w-full bg-slate-50 rounded-2xl border-2 border-slate-100 flex items-center justify-center p-5 shadow-inner overflow-hidden relative">
                             @if($mainImage)
                                <img src="{{ $mainImage }}" class="w-full h-full object-contain" style="image-rendering: -webkit-optimize-contrast;">
                                <div class="absolute bottom-2 left-2 bg-white/80 backdrop-blur px-2 py-0.5 rounded text-[8px] font-black text-indigo-900 uppercase">High Definition</div>
                            @else
                                <div class="text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-slate-200 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em]">Visual Data Pending</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t-2 border-slate-100 pt-6 flex justify-between items-center text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-12">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-indigo-900 rounded-full"></span>
                        <span>Arihanth Jewellers • Master Operations Catalogue</span>
                    </div>
                    <span>{{ now()->format('d/m/Y | H:i') }}</span>
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