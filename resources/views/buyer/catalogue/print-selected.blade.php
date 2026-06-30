<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selected Catalogue Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .page-break { page-break-after: always; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; background-color: white !important; }
            .no-print { display: none !important; }
        }
        body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
    </style>
</head>
<body class="bg-gray-50 p-6">

    <div class="max-w-5xl mx-auto mb-10 flex justify-between items-end border-b-2 border-slate-900 pb-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tight">Personal Catalogue</h1>
            <p class="text-slate-500 font-medium">Generated: <span class="text-slate-800">{{ date('d M Y | H:i') }}</span></p>
        </div>
        <div class="text-right">
            <div class="inline-block bg-slate-900 text-white px-4 py-2 rounded-lg">
                <p class="text-[10px] uppercase tracking-widest leading-none mb-1">Total Items</p>
                <p class="text-xl font-bold leading-none">{{ count($products) }}</p>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto space-y-12">
        @foreach($products as $index => $product)
            <div class="{{ !$loop->last ? 'page-break' : '' }} bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                
                <div class="bg-slate-50 border-b border-slate-200 px-8 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800">
                        <span class="text-indigo-600 mr-2">#{{ $index + 1 }}</span> 
                        {{ $product->product_name }}
                    </h3>
                    <span class="inline-flex items-center px-3 py-1 bg-green-50 text-green-700 text-[10px] font-bold rounded-full border border-green-100 uppercase tracking-wider">
                        Approved Item
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12">
                    <div class="md:col-span-12 lg:col-span-5 bg-white p-6 flex flex-col items-center justify-center border-r border-slate-100">
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
                                    $data = file_get_contents($fullPath);
                                    $mainImage = 'data:image/' . $ext . ';base64,' . base64_encode($data);
                                } else {
                                    $mainImage = asset('storage/' . $imagePath);
                                }
                            }
                        @endphp

                        @if($mainImage)
                            <img src="{{ $mainImage }}" 
                                 class="w-full h-auto max-h-[400px] object-contain rounded-lg shadow-sm" 
                                 style="image-rendering: -webkit-optimize-contrast; image-rendering: crisp-edges;"
                                 alt="Product Preview">
@else
                            <div class="text-slate-300 text-center py-20">
                                <i class="bi bi-image text-5xl opacity-20"></i>
                                <p class="text-[10px] uppercase font-bold mt-2">No Image Available</p>
                            </div>
                        @endif
                    </div>

                    <div class="md:col-span-12 lg:col-span-7">
                        <table class="w-full text-sm">
                            <tr class="border-b border-slate-100">
                                <td class="px-6 py-4 font-bold text-slate-400 uppercase text-[10px] tracking-widest bg-slate-50/50 w-1/3">Design Code</td>
                                <td class="px-6 py-4 text-slate-900 font-mono font-bold text-indigo-600 text-base uppercase pt-6 pb-6">{{ $product->design_code }}</td>
                            </tr>
                            <tr class="border-b border-slate-100">
                                <td class="px-6 py-4 font-bold text-slate-400 uppercase text-[10px] tracking-widest bg-slate-50/50">Product Code</td>
                                <td class="px-6 py-4 text-slate-800 font-bold uppercase tracking-wider">{{ $product->product_code }}</td>
                            </tr>
                            <tr class="border-b border-slate-100">
                                <td class="px-6 py-4 font-bold text-slate-400 uppercase text-[10px] tracking-widest bg-slate-50/50">Weight Range</td>
                                <td class="px-6 py-4 text-slate-900 font-bold text-lg">{{ $product->weight_from }} - {{ $product->weight_to }} <span class="text-xs font-normal text-slate-500">gm</span></td>
                            </tr>
                            <tr class="border-b border-slate-100">
                                <td class="px-6 py-4 font-bold text-slate-400 uppercase text-[10px] tracking-widest bg-slate-50/50">Category</td>
                                <td class="px-6 py-4 text-slate-700 font-medium">
                                    {{ optional($product->category)->name }}
                                </td>
                            </tr>
                            <tr class="border-b border-slate-100">
                                <td class="px-6 py-4 font-bold text-slate-400 uppercase text-[10px] tracking-widest bg-slate-50/50">Sub Category</td>
                                <td class="px-6 py-4 text-slate-700">{{ optional($product->subcategory)->name ?? '---' }}</td>
                            </tr>
                            <tr class="border-b border-slate-100">
                                <td class="px-6 py-4 font-bold text-slate-400 uppercase text-[10px] tracking-widest bg-slate-50/50">Specifications</td>
                                <td class="px-6 py-4">
                                    <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-[11px]">
                                        <div><span class="text-slate-400">Type:</span> <span class="font-bold text-slate-700">{{ $product->type ?: '---' }}</span></div>
                                        <div><span class="text-slate-400">Size:</span> <span class="font-bold text-slate-700">{{ $product->size ?: '---' }}</span></div>
                                        <div><span class="text-slate-400">Length:</span> <span class="font-bold text-slate-700">{{ $product->length ?: '---' }}</span></div>
                                        <div><span class="text-slate-400">Hallmark:</span> <span class="font-bold text-slate-700">{{ $product->hallmark ?: '---' }}</span></div>
                                    </div>
                                </td>
                            </tr>
                            @if($product->details)
                            <tr>
                                <td class="px-6 py-4 font-bold text-slate-400 uppercase text-[10px] tracking-widest bg-slate-50/50 rounded-bl-2xl">Description</td>
                                <td class="px-6 py-4 text-slate-500 italic text-[11px] leading-relaxed">
                                    {{ $product->details }}
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="max-w-5xl mx-auto mt-12 pt-6 border-t border-slate-200 flex justify-between items-center text-[10px] text-slate-400 uppercase tracking-[0.4em]">
        <p>Arihanth Jewellers &copy; {{ date('Y') }}</p>
        <p>Catalogue Specification Document</p>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        };
    </script>
</body>
</html>
