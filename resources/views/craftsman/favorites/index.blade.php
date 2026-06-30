@extends('craftsman.layouts.app')

@section('title', 'My Favorites')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-emerald-200 pb-4">
        <div>
            <h4 class="text-2xl font-bold text-emerald-900">My Favorites</h4>
            <p class="text-sm text-emerald-600">Your curated collection of favorite designs</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('craftsman.design.index') }}"
                class="inline-flex items-center px-4 py-2 bg-white text-emerald-700 border border-emerald-200 rounded-lg text-sm font-semibold hover:bg-emerald-50 transition shadow-sm">
                <i class="bi bi-arrow-left me-2"></i> Back to Catalogue
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 overflow-hidden">
        <div class="p-6 bg-emerald-50/50 border-b border-emerald-100">
            <h5 class="text-sm font-bold text-emerald-700 uppercase tracking-widest">Favorite Designs</h5>
        </div>

        <div class="p-6 bg-white">
            @if($favorites->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($favorites as $favorite)
                @php $design = $favorite->product; @endphp
                @php
                $isLocked = $design->isDesignLocked(Auth::guard('craftsman')->user());

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

                <div class="group bg-white rounded-2xl shadow-sm border border-emerald-100 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col">

                    <div class="relative h-56 bg-white overflow-hidden flex items-center justify-center p-4">
                        @if($imgSrc)
                        <img src="{{ $imgSrc }}"
                            class="w-full h-full object-contain transition-all duration-500 group-hover:scale-110"
                            alt="{{ $design->product_name }}">
                        @else
                        <div class="flex flex-col items-center justify-center text-emerald-100">
                            <i class="bi bi-image text-5xl"></i>
                        </div>
                        @endif

                        <form action="{{ route('craftsman.favorites.destroy', $favorite->id) }}" method="POST" class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 bg-red-500 text-white rounded-full shadow-lg hover:bg-red-600 transition-colors" title="Remove from Favorites">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </form>
                    </div>

                    <div class="p-4 flex flex-col flex-grow bg-white border-t border-emerald-50">
                        <div class="flex justify-between items-start mb-2">
                            <div class="max-w-[70%] mx-auto flex justify-center">
                                <h6 class="font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-4 py-1 rounded-lg shadow-sm text-center truncate"
                                    title="{{ $design->design_code }}">
                                    {{ $design->design_code }}
                                </h6>
                            </div>
                        </div>

                        <div class="flex justify-between items-center text-sm mb-4">
                            <span class="text-emerald-900 font-bold">{{ $design->category->name ?? 'N/A' }}</span>
                            <span class="text-emerald-900 font-black tracking-tight">{{ $design->weight_from }}-{{ $design->weight_to }}g</span>
                        </div>

                        <div class="mt-auto flex gap-2">
                            @if(!$isLocked)
                            <a href="{{ route('craftsman.design.show', $design->id) }}"
                                class="flex-1 text-center bg-emerald-900 text-white text-sm font-bold py-2.5 rounded-xl hover:bg-emerald-800 transition-all active:scale-95 shadow-sm">
                                Details
                            </a>
                            @else
                            <button disabled
                                class="flex-1 flex items-center justify-center gap-2 bg-slate-100 text-slate-400 text-sm font-bold py-2.5 rounded-xl cursor-not-allowed border border-slate-200">
                                <i class="bi bi-lock-fill"></i> Locked
                            </button>
                            @endif

                            <form action="{{ route('craftsman.favorites.destroy', $favorite->id) }}" method="POST" class="flex-none">
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
                <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4 text-emerald-200">
                    <i class="bi bi-heart text-4xl"></i>
                </div>
                <h5 class="text-emerald-900 font-bold">You haven't added any designs to your favorites yet.</h5>
                <a href="{{ route('craftsman.design.index') }}" class="mt-4 inline-flex items-center px-6 py-2.5 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 transition-all shadow-sm">
                    Browse Catalogue
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
