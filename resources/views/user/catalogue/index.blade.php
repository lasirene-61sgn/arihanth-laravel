@extends('user.layouts.app')

@section('title', 'My Catalogue')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <h4 class="text-2xl font-bold text-slate-800">My Accepted Catalogue</h4>
            <p class="text-sm text-slate-500 font-medium">Manage and view your personalized collection of accepted designs.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('user.catalogue.export', request()->query()) }}" 
               class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition shadow-sm">
                <i class="bi bi-file-earmark-excel me-2"></i> Export Excel
            </a>

            <div class="dropdown">
                <button class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-50 transition shadow-sm dropdown-toggle" 
                        type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="bi bi-funnel me-2"></i> Filters
                </button>
                <div class="dropdown-menu p-6 shadow-2xl border-0 rounded-xl mt-2" style="min-width: 450px;">
                    <form action="{{ route('user.catalogue.index') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1 tracking-wider">Design Code</label>
                                <input type="text" name="filter_design_code" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" value="{{ request('filter_design_code') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1 tracking-wider">Product Code</label>
                                <input type="text" name="filter_product_code" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" value="{{ request('filter_product_code') }}">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1 tracking-wider">Product Name</label>
                                <input type="text" name="filter_product_name" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" value="{{ request('filter_product_name') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1 tracking-wider">Category</label>
                                <input type="text" name="filter_category" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" value="{{ request('filter_category') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1 tracking-wider">Subcategory</label>
                                <input type="text" name="filter_subcategory" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" value="{{ request('filter_subcategory') }}">
                            </div>
                        </div>
                        <div class="flex gap-2 pt-4 border-t border-slate-50">
                            <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg text-sm font-bold hover:bg-indigo-700 transition shadow-sm">Apply Filters</button>
                            <a href="{{ route('user.catalogue.index') }}" class="flex-1 text-center border border-slate-200 py-2 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition">Reset</a>
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
        <form action="{{ route('user.catalogue.index') }}" method="GET" class="relative w-full md:w-80">
            <input type="text" name="search" placeholder="Search my items..." 
                   class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"
                   value="{{ request('search') }}">
            <div class="absolute left-3 top-2.5 text-slate-400">
                <i class="bi bi-search"></i>
            </div>
        </form>

        <div class="dropdown">
            <button class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-sort-down me-2"></i> Sort By
            </button>
            <div class="dropdown-menu shadow-2xl border-0 rounded-xl overflow-hidden mt-2">
                <a class="dropdown-item py-2 hover:bg-indigo-50 transition-colors" href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}">Newest First</a>
                <a class="dropdown-item py-2 hover:bg-indigo-50 transition-colors" href="{{ request()->fullUrlWithQuery(['sort' => 'name_asc']) }}">Name (A-Z)</a>
                <a class="dropdown-item py-2 hover:bg-indigo-50 transition-colors" href="{{ request()->fullUrlWithQuery(['sort' => 'name_desc']) }}">Name (Z-A)</a>
            </div>
        </div>
    </div>

    <div>
        @if($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($products as $product)
                    <div class="group bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col h-full">
                        
                        <div class="relative aspect-[4/3] bg-white overflow-hidden flex items-center justify-center p-4">
                            @php 
                                $imagesCount = $product->images->count();
                                $firstImage = $imagesCount > 0 ? $product->images->first()->path : null;
                                
                                if (!$firstImage && $product->product_image) {
                                    $imgs = explode(',', $product->product_image);
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
                                     class="w-full h-full object-contain transition-all duration-500 group-hover:scale-105" 
                                     alt="{{ $product->product_name }}">
                                
                                @if($imagesCount > 1)
                                    <div class="absolute bottom-3 right-3">
                                        <span class="bg-slate-900/80 text-white text-[10px] font-black px-2 py-1 rounded-full backdrop-blur-sm shadow-sm ring-1 ring-white/20">
                                            +{{ $imagesCount - 1 }} Photos
                                        </span>
                                    </div>
                                @endif
                            @else
                                <div class="flex flex-col items-center justify-center text-slate-200">
                                    <i class="bi bi-image text-5xl"></i>
                                    <span class="text-[10px] font-bold mt-2 uppercase">No Image</span>
                                </div>
                            @endif
                        </div>

                        <div class="p-5 flex flex-col flex-grow bg-white border-t border-slate-50">
                            <div class="flex justify-between items-start mb-3">
                                <div class="max-w-[70%]">
                                    <h6 class="font-black text-slate-800 truncate leading-tight" title="{{ $product->product_name }}">
                                        {{ $product->product_name }}
                                    </h6>
                                </div>
                                <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2 py-1 rounded border border-indigo-100 uppercase tracking-widest">
                                    {{ $product->design_code }}
                                </span>
                            </div>

                            <div class="space-y-2 mb-4 flex-grow">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-400 font-bold uppercase tracking-tighter">Product Code:</span>
                                    <span class="text-slate-700 font-black italic">{{ $product->product_code }}</span>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-400 font-bold uppercase tracking-tighter">Category:</span>
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 font-bold rounded shadow-sm text-[10px]">
                                        {{ $product->category->name }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-auto">
                                <button class="block w-full text-center bg-slate-900 text-white text-xs font-black py-3 rounded-xl hover:bg-indigo-600 shadow-sm transition-all active:scale-95 uppercase tracking-widest">
                                    View Design
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-3xl border border-slate-200 p-20 text-center shadow-sm">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="bi bi-archive text-slate-200 text-4xl"></i>
                </div>
                <h5 class="text-slate-800 font-black text-xl">No items in your personal catalogue.</h5>
                <p class="text-slate-500 text-sm max-w-xs mx-auto mt-2">Accepted designs from the master catalogue will appear here for quick access.</p>
            </div>
        @endif
    </div>
</div>
@endsection