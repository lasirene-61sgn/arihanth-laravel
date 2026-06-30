@extends('user.layouts.app')

@section('title', 'Approved Design Catalogue')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <h4 class="text-2xl font-bold text-slate-800">Approved Design Catalogue</h4>
            <p class="text-sm text-slate-500">Browse through the finalized and approved design collection.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('user.design.export', request()->query()) }}"
                class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition shadow-sm">
                <i class="bi bi-file-earmark-excel me-2"></i> Export Excel
            </a>

            <div class="dropdown">
                <button class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-50 transition shadow-sm dropdown-toggle"
                    type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="bi bi-funnel me-2"></i> Advanced Filter
                </button>
                <div class="dropdown-menu p-6 shadow-2xl border-0 rounded-xl mt-2" style="min-width: 450px;">
                    <form action="{{ route('user.design.index') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Design Code</label>
                                <input type="text" name="filter_design_code" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" value="{{ request('filter_design_code') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Product Name</label>
                                <input type="text" name="filter_product_name" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" value="{{ request('filter_product_name') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Category</label>
                                <input type="text" name="filter_category" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" value="{{ request('filter_category') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Subcategory</label>
                                <input type="text" name="filter_subcategory" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" value="{{ request('filter_subcategory') }}">
                            </div>
                        </div>
                        <div class="flex gap-2 pt-4 border-t border-slate-50">
                            <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg text-sm font-bold hover:bg-indigo-700 transition shadow-sm">Apply</button>
                            <a href="{{ route('user.design.index') }}" class="flex-1 text-center border border-slate-200 py-2 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-200 transition shadow-sm border border-slate-200">
                <i class="bi bi-printer me-2"></i> Print
            </button>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
        <form action="{{ route('user.design.index') }}" method="GET" class="relative w-full md:w-80">
            <input type="text" name="search" placeholder="Quick search designs..."
                class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"
                value="{{ request('search') }}">
            <div class="absolute left-3 top-2.5 text-slate-400">
                <i class="bi bi-search"></i>
            </div>
        </form>

        <div class="dropdown">
            <button class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-sort-down me-2"></i> Sort
            </button>
            <div class="dropdown-menu shadow-2xl border-0 rounded-xl overflow-hidden mt-2">
                <a class="dropdown-item py-2 hover:bg-indigo-50" href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}">Newest First</a>
                <a class="dropdown-item py-2 hover:bg-indigo-50" href="{{ request()->fullUrlWithQuery(['sort' => 'name_asc']) }}">Name (A-Z)</a>
                <a class="dropdown-item py-2 hover:bg-indigo-50" href="{{ request()->fullUrlWithQuery(['sort' => 'name_desc']) }}">Name (Z-A)</a>
            </div>
        </div>
    </div>

    <div>
        @if($designs->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($designs as $design)
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

            $isLocked = $design->isDesignLocked(Auth::user());
            @endphp

            <div class="group bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col h-full">

                <div class="relative aspect-square bg-slate-50 overflow-hidden flex items-center justify-center p-6">
                    @if($imgSrc)
                    <img src="{{ $imgSrc }}"
                        class="w-full h-full object-contain transition-all duration-500 {{ $isLocked ? 'blur-[30px] scale-110 opacity-60 grayscale' : 'group-hover:scale-110' }}"
                        alt="{{ $design->product_name }}">

                    @if($imagesCount > 1 && !$isLocked)
                    <div class="absolute bottom-3 right-3">
                        <span class="bg-slate-900/80 text-white text-[10px] font-black px-2 py-1 rounded-full backdrop-blur-sm">
                            +{{ $imagesCount - 1 }} Photos
                        </span>
                    </div>
                    @endif

                    @if($isLocked)
                    <div class="absolute inset-0 bg-white/20 backdrop-blur-[2px] flex items-center justify-center transition-opacity duration-300">
                        <div class="bg-white/90 p-4 rounded-full shadow-2xl border border-slate-100 transform scale-100 group-hover:scale-110 transition-transform">
                            <img src="{{ asset('images/ajlogo.png') }}" class="w-12 h-12 object-contain" alt="Locked Logo">
                            <div class="absolute -bottom-1 -right-1 bg-amber-500 text-white rounded-full w-6 h-6 flex items-center justify-center border-2 border-white">
                                <i class="bi bi-lock-fill text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                    @endif
                    @else
                    <div class="flex flex-col items-center justify-center text-slate-300">
                        <i class="bi bi-image text-5xl"></i>
                        <span class="text-xs font-bold mt-2 uppercase tracking-tighter">No Image Available</span>
                    </div>
                    @endif
                </div>

                <div class="p-5 flex flex-col flex-grow bg-white border-t border-slate-50">
                    <div class="flex justify-between items-start mb-3">
                        <div class="max-w-[80%] mx-auto flex justify-center">
                            <h6 class="font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-4 py-1 rounded-lg shadow-sm text-center truncate"
                                title="{{ $design->design_code }}">
                                {{ $design->design_code }}
                            </h6>
                        </div>
                        <!-- <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2 py-1 rounded border border-indigo-100 uppercase tracking-widest">
                            {{ $design->design_code }}
                        </span> -->
                    </div>

                    <div class="space-y-2 mb-4">
                        @if ($design->category->name)
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-400 font-bold uppercase tracking-tighter"></span>
                            <span class="text-slate-700 font-black italic">{{ $design->category->name }}</span>
                        </div>
                        @endif
                        
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-400 font-bold uppercase tracking-tighter">Weight:</span>
                            <span class="text-slate-900 font-black">{{ $design->weight_from }}-{{ $design->weight_to }}g</span>
                        </div>
                    </div>

                    <div class="mt-auto">
                        @if(!$isLocked)
                        <a href="{{ route('user.design.show', $design->id) }}"
                            class="block w-full text-center bg-slate-900 text-white text-xs font-black py-3 rounded-xl hover:bg-indigo-600 shadow-sm transition-all active:scale-95 uppercase tracking-widest">
                            View Details
                        </a>
                        @else
                        <button disabled
                            class="w-full flex items-center justify-center gap-2 bg-slate-100 text-slate-400 text-xs font-black py-3 rounded-xl cursor-not-allowed border border-slate-200 uppercase tracking-widest">
                            <i class="bi bi-lock-fill"></i> Locked Design
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-white rounded-3xl border border-slate-200 p-20 text-center shadow-sm">
            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="bi bi-patch-check text-slate-200 text-4xl"></i>
            </div>
            <h5 class="text-slate-800 font-black text-xl">No designs found.</h5>
            <p class="text-slate-500 text-sm max-w-xs mx-auto mt-2">Adjust your filters or search keywords to find what you're looking for.</p>
        </div>
        @endif
    </div>
</div>
@endsection