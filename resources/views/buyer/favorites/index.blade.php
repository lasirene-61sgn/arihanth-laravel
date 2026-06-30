@extends('buyer.layouts.app')

@section('title', 'My Favorites')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">My Favorites</h1>
            <p class="text-sm text-slate-500">Your curated collection of favorite designs</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('buyer.design.index') }}"
                class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 border border-slate-200 rounded-xl text-sm font-bold hover:bg-slate-200 transition-all">
                <i class="bi bi-arrow-left mr-2"></i> Back to Catalogue
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 bg-slate-50/50 border-b border-slate-100">
            <h5 class="text-sm font-bold text-slate-700 uppercase tracking-widest">Favorite Designs</h5>
        </div>

        <div class="p-6 bg-white">
            @if($favorites->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($favorites as $favorite)
                @php $design = $favorite->product; @endphp
                <div class="group flex flex-col bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-300 overflow-hidden">
                    <div class="relative aspect-[4/3] bg-white p-4 overflow-hidden">
                        @php
                        $imagesCount = $design->images->count();
                        $firstImage = $imagesCount > 0 ? $design->images->first()->path : null;
                        if (!$firstImage && $design->product_image) {
                            $imgs = explode(',', $design->product_image);
                            $firstImage = trim($imgs[0]);
                        }

                        $imgSrc = null;
                        if ($firstImage) {
                            if (str_starts_with($firstImage, 'http')) { $imgSrc = $firstImage; }
                            elseif (str_starts_with($firstImage, 'products/')) { $imgSrc = asset('storage/' . $firstImage); }
                            elseif (str_starts_with($firstImage, 'images/') || str_starts_with($firstImage, 'storage/')) { $imgSrc = asset($firstImage); }
                            else { $imgSrc = asset('storage/products/' . $firstImage); }
                        }
                        @endphp

                        @if($imgSrc)
                        <img src="{{ $imgSrc }}"
                            class="w-full h-full object-contain transition-all duration-500 group-hover:scale-110"
                            alt="{{ $design->product_name }}">
                        @else
                        <div class="w-full h-full bg-slate-50 rounded-lg flex items-center justify-center text-slate-300">
                            <i class="bi bi-image text-4xl"></i>
                        </div>
                        @endif

                        <form action="{{ route('buyer.favorites.destroy', $favorite->id) }}" method="POST" class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 bg-red-500 text-white rounded-full shadow-lg hover:bg-red-600 transition-colors" title="Remove from Favorites">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </form>
                    </div>

                    <div class="p-4 flex flex-col flex-1 border-t border-slate-50">
                        <div class="flex justify-between items-start mb-2">
                             <div class="max-w-[70%] mx-auto flex justify-center">
                                <h6 class="font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-4 py-1 rounded-lg shadow-sm text-center truncate"
                                    title="{{ $design->design_code }}">
                                    {{ $design->design_code }}
                                </h6>
                            </div>
                        </div>

                        <div class="flex justify-between items-center text-xs text-slate-500 mb-4">
                            <span class="text-[11px] font-mono font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded"> {{ $design->category->name ?? 'N/A' }}</span>
                            <span class="font-bold text-slate-700">{{ $design->weight_from }}-{{ $design->weight_to }} gm</span>
                        </div>

                        <div class="mt-auto pt-4 border-t border-slate-50 flex gap-2">
                            @if(!$design->isDesignLocked(Auth::guard('buyer')->user()))
                            <a href="{{ route('buyer.design.show', $design->id) }}"
                                class="flex-1 inline-flex items-center justify-center py-2.5 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition-colors">
                                Details
                            </a>
                            @else
                            <button class="flex-1 inline-flex items-center justify-center py-2.5 bg-slate-100 text-slate-400 text-xs font-bold rounded-xl cursor-not-allowed border border-slate-200" disabled>
                                <i class="bi bi-lock-fill mr-2"></i> Locked
                            </button>
                            @endif
                            
                            <form action="{{ route('buyer.favorites.destroy', $favorite->id) }}" method="POST" class="flex-none">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2.5 bg-red-50 text-red-600 border border-red-100 rounded-xl hover:bg-red-100 transition-colors">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-200">
                    <i class="bi bi-heart text-4xl"></i>
                </div>
                <h5 class="text-slate-500 font-medium">You haven't added any designs to your favorites yet.</h5>
                <a href="{{ route('buyer.design.index') }}" class="mt-4 inline-flex items-center px-6 py-2.5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition-all">
                    Browse Catalogue
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
