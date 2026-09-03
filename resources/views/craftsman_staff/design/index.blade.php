@extends('craftsman_staff.layouts.app')

@section('title', 'Approved Design Catalogue')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-indigo-200 pb-4">
        <div>
            <h4 class="text-2xl font-bold text-indigo-900">Approved Designs Catalogue</h4>
            <p class="text-sm text-indigo-600">Browse and manage approved product designs</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('craftsman_staff.design.export', request()->query()) }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                <i class="bi bi-file-earmark-excel me-2"></i> Export Excel
            </a>
            <button onclick="window.print()"
                class="inline-flex items-center px-4 py-2 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 transition shadow-sm">
                <i class="bi bi-printer me-2"></i> Print
            </button>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-indigo-100 flex flex-wrap items-center gap-4">
        <form action="{{ route('craftsman_staff.design.index') }}" method="GET" class="relative flex-grow max-w-sm">
            <input type="text" name="search" placeholder="Quick search..."
                class="w-full pl-10 pr-4 py-2 border border-indigo-100 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                value="{{ request('search') }}">
            <div class="absolute left-3 top-2.5 text-indigo-400">
                <i class="bi bi-search"></i>
            </div>
        </form>

        <div class="dropdown">
            <button class="inline-flex items-center px-4 py-2 bg-white border border-indigo-200 text-indigo-700 text-sm font-semibold rounded-lg hover:bg-indigo-50 transition dropdown-toggle"
                type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                <i class="bi bi-funnel me-2"></i> Filters
            </button>
            <div class="dropdown-menu p-6 shadow-2xl border-0 rounded-xl" style="min-width: 400px;">
                <form action="{{ route('craftsman_staff.design.index') }}" method="GET" class="space-y-4">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-1">
                            <label class="block text-xs font-bold text-indigo-700 uppercase mb-1">Design Code</label>
                            <input type="text" name="filter_design_code" class="w-full px-3 py-2 border border-indigo-100 rounded-md text-sm outline-none focus:ring-2 focus:ring-indigo-500" value="{{ request('filter_design_code') }}" placeholder="Ex: DES001">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-xs font-bold text-indigo-700 uppercase mb-1">Product Code</label>
                            <input type="text" name="filter_product_code" class="w-full px-3 py-2 border border-indigo-100 rounded-md text-sm outline-none focus:ring-2 focus:ring-indigo-500" value="{{ request('filter_product_code') }}" placeholder="Ex: PRD001">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-indigo-700 uppercase mb-1">Product Name</label>
                        <input type="text" name="filter_product_name" class="w-full px-3 py-2 border border-indigo-100 rounded-md text-sm outline-none focus:ring-2 focus:ring-indigo-500" value="{{ request('filter_product_name') }}" placeholder="Enter name...">
                    </div>

                    {{-- Cascading Category & Subcategory Dropdowns --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-indigo-700 uppercase mb-1">Category</label>
                            <select name="product_category_id" id="design_category_select" class="w-full px-3 py-2 border border-indigo-100 rounded-md text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('product_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Subcategory container: only visible when selected category has subcategories --}}
                        <div id="design_subcategory_wrapper" style="display: none;">
                            <label class="block text-xs font-bold text-indigo-700 uppercase mb-1">Subcategory</label>
                            <select name="subcategory_id" id="design_subcategory_select" class="w-full px-3 py-2 border border-indigo-100 rounded-md text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">All Subcategories</option>
                                @foreach($subcategories as $subcat)
                                    <option value="{{ $subcat->id }}" 
                                            data-parent="{{ $subcat->product_category_id }}" 
                                            {{ request('subcategory_id') == $subcat->id ? 'selected' : '' }}>
                                        {{ $subcat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-4 border-t border-indigo-50">
                        <button type="submit" class="flex-1 bg-indigo-900 text-white py-2 rounded-lg text-sm font-bold hover:bg-indigo-800 transition">Apply</button>
                        <a href="{{ route('craftsman_staff.design.index') }}" class="flex-1 text-center border border-indigo-200 py-2 rounded-lg text-sm font-bold text-indigo-700 hover:bg-indigo-50 transition">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div>
        @if($designs->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($designs as $design)
            @php
            $activeCraftsman = Auth::guard('craftsman')->user() ?? auth()->user()->craftsman ?? null;
            $isLocked = method_exists($design, 'isDesignLocked') ? $design->isDesignLocked($activeCraftsman) : false;

            $imagesCount = $design->images ? $design->images->count() : 0;
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

            <div class="group bg-white rounded-2xl shadow-sm border border-indigo-100 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col">

                <div class="relative h-56 bg-white overflow-hidden flex items-center justify-center p-4">
                    @if($imgSrc)
                    <img src="{{ $imgSrc }}"
                        class="w-full h-full object-contain transition-all duration-500 {{ $isLocked ? 'blur-[30px] opacity-40 grayscale' : 'group-hover:scale-110' }}"
                        alt="{{ $design->product_name }}">

                    @if($isLocked)
                    <div class="absolute inset-0 flex items-center justify-center bg-white/10 backdrop-blur-[2px]">
                        <div class="bg-white/90 p-3 rounded-full shadow-lg border border-indigo-50">
                            <img src="{{ asset('images/ajlogo.png') }}" class="w-12 h-12 object-contain" alt="Locked">
                        </div>
                    </div>
                    @elseif($imagesCount > 1)
                    <div class="absolute bottom-3 right-3">
                        <span class="bg-indigo-900/80 text-white text-[10px] font-bold px-2 py-1 rounded-full backdrop-blur-sm">
                            +{{ $imagesCount - 1 }} Images
                        </span>
                    </div>
                    @endif
                    @else
                    <div class="flex flex-col items-center justify-center text-indigo-100">
                        <i class="bi bi-image text-5xl"></i>
                    </div>
                    @endif
                </div>

                <div class="p-4 flex flex-col flex-grow bg-white border-t border-indigo-50">
                    <div class="flex justify-between items-start mb-2">
                        <div class="max-w-[70%] mx-auto flex justify-center">
                            <h6 class="font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-4 py-1 rounded-lg shadow-sm text-center truncate font-mono text-sm"
                                title="{{ $design->design_code }}">
                                {{ $design->design_code }}
                            </h6>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-sm mb-4">
                        <span class="text-indigo-900 font-bold">{{ $design->category->name ?? 'N/A' }}</span>
                        <span class="text-indigo-900 font-black tracking-tight">{{ $design->weight_from }}-{{ $design->weight_to }}g</span>
                    </div>

                    <div class="mt-auto flex gap-2">
                        @if(!$isLocked)
                        <a href="{{ route('craftsman_staff.design.show', $design->id) }}"
                            class="flex-1 text-center bg-indigo-900 text-white text-[10px] font-bold py-2.5 rounded-xl hover:bg-indigo-800 transition-all active:scale-95 shadow-sm">
                            VIEW
                        </a>
                        @else
                        <button disabled
                            class="flex-1 flex items-center justify-center gap-2 bg-slate-100 text-slate-400 text-[10px] font-bold py-2.5 rounded-xl cursor-not-allowed border border-slate-200">
                            <i class="bi bi-lock-fill"></i> LOCKED
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-8 flex justify-center">
            {{ $designs->links() }}
        </div>
        @else
        <div class="bg-white rounded-2xl border border-indigo-100 p-20 text-center shadow-sm">
            <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-patch-check text-indigo-200 text-4xl"></i>
            </div>
            <h5 class="text-indigo-900 font-bold">No approved designs found.</h5>
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('design_category_select');
        const subcategorySelect = document.getElementById('design_subcategory_select');
        const subcategoryWrapper = document.getElementById('design_subcategory_wrapper');
        const subcategoryOptions = Array.from(subcategorySelect.querySelectorAll('option[data-parent]'));

        function syncSubcategories() {
            const selectedCatId = categorySelect.value;
            const currentSubcatVal = subcategorySelect.value;

            if (!selectedCatId) {
                subcategoryWrapper.style.display = 'none';
                subcategorySelect.value = '';
                return;
            }

            let matchingCount = 0;
            subcategoryOptions.forEach(opt => {
                const parentId = opt.getAttribute('data-parent');
                if (parentId === selectedCatId) {
                    opt.style.display = '';
                    matchingCount++;
                } else {
                    opt.style.display = 'none';
                    if (opt.value === currentSubcatVal) {
                        subcategorySelect.value = '';
                    }
                }
            });

            subcategoryWrapper.style.display = matchingCount > 0 ? 'block' : 'none';
        }

        if (categorySelect && subcategorySelect && subcategoryWrapper) {
            categorySelect.addEventListener('change', syncSubcategories);
            syncSubcategories(); // Run on initial render
        }
    });
</script>
@endsection