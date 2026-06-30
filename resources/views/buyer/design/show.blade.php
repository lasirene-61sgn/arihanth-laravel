@extends('buyer.layouts.app')

@section('title', 'Design Technical Details')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Design Info: <span class="text-blue-600">{{ $product->design_code }}</span></h1>
            <p class="text-sm text-slate-500">Full technical specifications and media gallery.</p>
        </div>
        <a href="{{ route('buyer.design.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-50 transition-all shadow-sm">
            <i class="bi bi-arrow-left mr-2"></i> Back to Gallery
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-3">
                @php
                    $allImages = [];
                    if($product->images && $product->images->count() > 0) {
                        foreach($product->images as $img) $allImages[] = $img->path;
                    }
                    if($product->product_image) {
                        $legacy = explode(',', $product->product_image);
                        foreach($legacy as $img) {
                            $t = trim($img);
                            if($t && !in_array($t, $allImages)) $allImages[] = $t;
                        }
                    }
                @endphp

                @if(count($allImages) > 0)
                    @php 
                        $first = $allImages[0];
                        $mainSrc = str_starts_with($first, 'http') ? $first : (
                            str_starts_with($first, 'products/') ? asset('storage/'.$first) : (
                            str_starts_with($first, 'images/') || str_starts_with($first, 'storage/') ? asset($first) : asset('storage/products/'.$first)
                        ));
                    @endphp
                    <div class="bg-slate-50 rounded-xl overflow-hidden mb-4 aspect-square flex items-center justify-center border border-slate-100">
                        <img src="{{ $mainSrc }}" 
                             id="galleryMain"
                             class="max-w-full max-h-full object-contain mix-blend-multiply transition-all duration-300" 
                             alt="Main Preview">
                    </div>
                    
                    @if(count($allImages) > 1)
                        <div class="flex flex-wrap gap-3 justify-center">
                            @foreach($allImages as $img)
                                @php
                                    $src = str_starts_with($img, 'http') ? $img : (
                                        str_starts_with($img, 'products/') ? asset('storage/'.$img) : (
                                        str_starts_with($img, 'images/') || str_starts_with($img, 'storage/') ? asset($img) : asset('storage/products/'.$img)
                                    ));
                                @endphp
                                <button onclick="document.getElementById('galleryMain').src = '{{ $src }}'" 
                                        class="w-16 h-16 rounded-lg border-2 border-slate-100 overflow-hidden hover:border-blue-500 transition-all focus:border-blue-500 focus:outline-none bg-white">
                                    <img src="{{ $src }}" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="bg-slate-50 rounded-xl aspect-square flex flex-col items-center justify-center text-slate-300">
                        <i class="bi bi-image text-6xl mb-2"></i>
                        <p class="text-sm">No Images Available</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                    <h4 class="text-lg font-bold text-slate-800">Technical Specifications</h4>
                    <span class="inline-flex items-center px-3 py-1 bg-green-50 text-green-700 text-xs font-bold rounded-full border border-green-100">
                        <i class="bi bi-patch-check-fill mr-1"></i> Accepted Item
                    </span>
                </div>
                
                <div class="p-6 md:p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Design Name</label>
                            <h2 class="text-2xl font-extrabold text-slate-900 leading-tight">{{ $product->product_name }}</h2>
                        </div>
                        <div class="md:text-right">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Weight Range</label>
                            <p class="text-2xl font-bold text-blue-600">{{ $product->weight_from }} - {{ $product->weight_to }} <span class="text-sm font-medium text-slate-500">gm</span></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Category</label>
                            <p class="text-sm font-bold text-slate-700">{{ $product->category->name ?? 'N/A' }}</p>
                        </div>
                        <div class="border-l border-slate-200 pl-4">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Sub Category</label>
                            <p class="text-sm font-bold text-slate-700">{{ $product->subcategory->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div>
                        <h6 class="text-xs font-bold text-blue-600 uppercase tracking-widest border-b border-blue-100 pb-2 mb-4">Product Details</h6>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-6 gap-x-4">
                            @php
                                $details = [
                                    'Type' => $product->type,
                                    'Size' => $product->size,
                                    'Length' => $product->length,
                                    'Hallmark' => $product->hallmark,
                                    'Rodium' => $product->rodium,
                                    'Hook' => $product->hook,
                                    'Stone' => $product->stone,
                                    'Enamel' => $product->enamel,
                                    'Opening' => $product->open_close
                                ];
                            @endphp
                            @foreach($details as $label => $value)
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">{{ $label }}</label>
                                    <span class="text-sm font-semibold {{ $value ? 'text-slate-800' : 'text-slate-300' }}">
                                        {{ $value ?: 'Not Specified' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if($product->details)
                        <div class="pt-6 border-t border-slate-100">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Additional Description</label>
                            <div class="text-sm text-slate-600 leading-relaxed bg-slate-50 p-4 rounded-xl italic">
                                {!! nl2br(e($product->details)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection