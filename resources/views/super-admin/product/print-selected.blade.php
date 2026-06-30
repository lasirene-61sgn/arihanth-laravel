<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selected Products Report - Super Admin</title>
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
        .product-card { 
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
        @foreach($products as $index => $product)
            <div class="product-card bg-white {{ !$loop->last ? 'page-break' : '' }}">
                
                <!-- Header -->
                <div class="flex justify-between items-start border-b-2 border-slate-900 pb-6 mb-8">
                    <div>
                        <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">Product Data</h1>
                        <p class="text-sm text-slate-500 font-medium">Internal Inventory Record - Super Admin</p>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Master SKU</div>
                        <div class="text-xl font-black text-slate-800">{{ $product->product_code }}</div>
                    </div>
                </div>

                <div class="flex gap-10 items-start flex-grow">
                    <!-- Left: Details -->
                    <div class="flex-1 space-y-8">
                        <div>
                            <h2 class="text-2xl font-black text-slate-900 leading-tight mb-2">{{ $product->product_name }}</h2>
                            <div class="flex gap-3">
                                <span class="bg-indigo-600 text-white px-3 py-1 rounded text-[10px] font-black uppercase tracking-widest font-mono shadow-sm">
                                    DESIGN: {{ $product->design_code ?? '---' }}
                                </span>
                                <span class="bg-slate-100 text-slate-500 px-3 py-1 rounded text-[10px] font-black uppercase tracking-widest border border-slate-200 shadow-inner">
                                    {{ $product->type ?: 'STANDARD' }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-sm">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Classification Hierarchy</p>
                                <p class="font-bold text-slate-800 uppercase tracking-wide">{{ optional($product->category)->name ?? '---' }} <span class="text-slate-300 px-1">/</span> {{ optional($product->subcategory)->name ?? '---' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Weight Constraint</p>
                                <p class="font-black text-slate-900 underline underline-offset-8 decoration-indigo-600">{{ $product->weight_from }}g - {{ $product->weight_to }}g</p>
                            </div>
                            <div class="col-span-2 mt-4">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-4">Metadata Analysis</p>
                                <div class="grid grid-cols-4 gap-4">
                                    <div class="bg-slate-50 p-2 rounded-lg border border-slate-100">
                                        <p class="text-[8px] font-black text-slate-400 uppercase">Hook</p>
                                        <p class="text-xs font-black text-slate-700">{{ $product->hook ?: '---' }}</p>
                                    </div>
                                    <div class="bg-slate-50 p-2 rounded-lg border border-slate-100">
                                        <p class="text-[8px] font-black text-slate-400 uppercase">Size</p>
                                        <p class="text-xs font-black text-slate-700">{{ $product->size ?: '---' }}</p>
                                    </div>
                                    <div class="bg-slate-50 p-2 rounded-lg border border-slate-100">
                                        <p class="text-[8px] font-black text-slate-400 uppercase">Length</p>
                                        <p class="text-xs font-black text-slate-700">{{ $product->length ?: '---' }}</p>
                                    </div>
                                    <div class="bg-slate-50 p-2 rounded-lg border border-slate-100">
                                        <p class="text-[8px] font-black text-slate-400 uppercase">HUID</p>
                                        <p class="text-xs font-black text-slate-700">{{ $product->hallmark ?: '---' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($product->details)
                        <div class="bg-indigo-50/20 p-6 rounded-2xl border-l-4 border-slate-900 mt-8 relative">
                            <span class="absolute -top-3 left-6 bg-slate-900 text-white text-[8px] font-black px-2 py-0.5 rounded tracking-widest">DESCRIPTION</span>
                            <p class="text-sm text-slate-700 leading-relaxed font-medium italic">
                                "{{ $product->details }}"
                            </p>
                        </div>
                        @else
                        <div class="flex items-center gap-3 text-slate-300 mt-10">
                            <div class="h-px bg-slate-100 flex-grow"></div>
                            <span class="text-[10px] font-black uppercase tracking-[0.3em]">No Detailed Description</span>
                            <div class="h-px bg-slate-100 flex-grow"></div>
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

                        <div class="aspect-[1/1] w-full bg-slate-50 rounded-2xl border-2 border-slate-100 flex items-center justify-center p-4 shadow-inner overflow-hidden relative group">
                            @if($mainImage)
                                <img src="{{ $mainImage }}" class="w-full h-full object-contain" style="image-rendering: -webkit-optimize-contrast;">
                                <div class="absolute inset-0 border-8 border-white rounded-xl pointer-events-none opacity-50"></div>
                                <div class="absolute bottom-2 right-2 bg-slate-900 text-white text-[8px] px-2 py-0.5 rounded font-black tracking-widest">SUPER-ADMIN RECAP</div>
                            @else
                                <div class="text-center opacity-40">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-slate-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Metadata-Only Record</p>
                                </div>
                            @endif
                        </div>
                        
                        <div class="mt-6 space-y-2">
                             <div class="flex justify-between text-[10px] items-baseline">
                                <span class="text-slate-400 font-bold uppercase tracking-tight">Rhodium Check</span>
                                <span class="text-slate-800 font-black">{{ $product->rodium ?: '---' }}</span>
                             </div>
                             <div class="flex justify-between text-[10px] items-baseline">
                                <span class="text-slate-400 font-bold uppercase tracking-tight">Stone Check</span>
                                <span class="text-slate-800 font-black">{{ $product->stone ?: '---' }}</span>
                             </div>
                             <div class="flex justify-between text-[10px] items-baseline">
                                <span class="text-slate-400 font-bold uppercase tracking-tight">Enamel Check</span>
                                <span class="text-slate-800 font-black">{{ $product->enamel ?: '---' }}</span>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t-2 border-slate-100 pt-6 flex justify-between items-center text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-12">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 bg-slate-900 rounded-full animate-pulse"></span>
                        <span>Arihanth Jewellers • Master Operations</span>
                    </div>
                    <span>System Record Entry: {{ $product->created_at->format('d/m/Y') }}</span>
                    <span>Print Log: {{ now()->format('d/m/Y | H:i') }}</span>
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