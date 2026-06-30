@extends('key-user.layouts.app')

@section('title', 'Design Technical Details')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Design Info: <span class="text-indigo-600">{{ $product->design_code }}</span></h1>
            <p class="text-sm text-gray-500">Full technical specifications and gallery</p>
        </div>
        <a href="{{ route('key-user.design.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
            <i class="bi bi-arrow-left mr-2"></i> Back to Gallery
        </a>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        
        <div class="w-full lg:w-5/12">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden p-4">
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
                    
                    <div class="bg-gray-50 rounded-xl overflow-hidden mb-4 flex items-center justify-center p-4 border border-gray-100">
                        <img src="{{ $mainSrc }}" 
                             id="galleryMain"
                             class="max-h-[400px] w-full object-contain mix-blend-multiply transition-all duration-300"
                             alt="Design Main View">
                    </div>
                    
                    @if(count($allImages) > 1)
                        <div class="flex flex-wrap gap-2 justify-center">
                            @foreach($allImages as $img)
                                @php
                                    $src = str_starts_with($img, 'http') ? $img : (
                                        str_starts_with($img, 'products/') ? asset('storage/'.$img) : (
                                        str_starts_with($img, 'images/') || str_starts_with($img, 'storage/') ? asset($img) : asset('storage/products/'.$img)
                                    ));
                                @endphp
                                <img src="{{ $src }}" 
                                     class="w-16 h-16 rounded-lg border-2 border-gray-100 cursor-pointer object-cover hover:border-indigo-500 transition-all shadow-sm"
                                     onclick="document.getElementById('galleryMain').src = this.src">
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="h-[400px] rounded-xl bg-gray-50 border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400">
                        <i class="bi bi-image text-5xl mb-2"></i>
                        <p class="text-sm">No design images available</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex-1">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h5 class="font-bold text-gray-800">Technical Specifications</h5>
                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full border border-green-200">
                        Accepted Catalogue Item
                    </span>
                </div>
                
                <div class="p-6 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Design Name</label>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ $product->product_name }}</p>
                        </div>
                        <div class="md:text-right">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Weight Range</label>
                            <p class="text-xl font-bold text-indigo-600 mt-1">{{ $product->weight_from }} - {{ $product->weight_to }} gm</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Category</label>
                            <p class="text-sm font-semibold text-gray-800 mt-1">{{ $product->category->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Sub Category</label>
                            <p class="text-sm font-semibold text-gray-800 mt-1">{{ $product->subcategory->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Product Code</label>
                            <p class="text-sm font-mono font-bold text-red-600 mt-1">{{ $product->product_code }}</p>
                        </div>
                    </div>

                    <div>
                        <h6 class="text-xs font-bold text-indigo-600 uppercase tracking-widest mb-4 border-b border-indigo-50 pb-2">Product Detail Matrix</h6>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-y-6 gap-x-4">
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
                                <div class="group">
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">{{ $label }}</label>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors">
                                        {{ $value ?: '—' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if($product->details)
                    <div class="pt-4 border-t border-gray-100">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-2">Additional Description</label>
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 text-sm text-gray-600 leading-relaxed italic">
                            {!! nl2br(e($product->details)) !!}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Prevent image stretching in the gallery */
    #galleryMain {
        object-fit: contain;
    }
    
    /* Animation for thumbnail click */
    #galleryMain.fade-in {
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>

<script>
    // Smooth transition effect when clicking thumbnails
    document.querySelectorAll('.img-thumbnail-trigger').forEach(thumb => {
        thumb.addEventListener('click', function() {
            const main = document.getElementById('galleryMain');
            main.classList.remove('fade-in');
            void main.offsetWidth; // trigger reflow
            main.classList.add('fade-in');
        });
    });
</script>
@endsection