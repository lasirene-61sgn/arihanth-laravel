@extends('buyer.layouts.app')

@section('title', 'Approved Design Catalogue')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Approved Design Catalogue</h1>
            <p class="text-sm text-slate-500">Browse and manage accepted product designs</p>
        </div>
        <div class="flex items-center gap-2">
            @if(Auth::guard('buyer')->user()->hasPermission('stock_order'))
            <button id="bulk-cart-btn" onclick="bulkAddToCart()" class="hidden items-center px-4 py-2 bg-indigo-600 text-white border border-indigo-700 rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all shadow-sm">
                <i class="bi bi-cart-plus mr-2"></i> Bulk Add to Order (<span id="bulk-count">0</span>)
            </button>
            @endif
            <a href="{{ route('buyer.design.export', request()->query()) }}"
                class="inline-flex items-center px-4 py-2 bg-green-50 text-green-700 border border-green-100 rounded-xl text-sm font-bold hover:bg-green-100 transition-all">
                <i class="bi bi-file-earmark-excel mr-2"></i> Export
            </a>
            <button onclick="window.print()"
                class="inline-flex items-center px-4 py-2 bg-cyan-50 text-cyan-700 border border-cyan-100 rounded-xl text-sm font-bold hover:bg-cyan-100 transition-all">
                <i class="bi bi-printer mr-2"></i> Print
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-visible">
        <div class="p-4 flex flex-wrap items-center gap-4">
            <form action="{{ route('buyer.design.index') }}" method="GET" class="flex-grow max-w-md relative group">
                <input type="text" name="search"
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all"
                    placeholder="Search Design or Product Name..." value="{{ request('search') }}">
                <div class="absolute left-3 top-3 text-slate-400 group-focus-within:text-blue-600 transition-colors">
                    <i class="bi bi-search"></i>
                </div>
            </form>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-all">
                    <i class="bi bi-funnel mr-2"></i> Filters
                </button>
                <div x-show="open" @click.away="open = false" class="absolute left-0 lg:right-0 lg:left-auto z-50 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 p-6" style="display: none;">
                    <form action="{{ route('buyer.design.index') }}" method="GET" class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Design Code</label>
                            <input type="text" name="filter_code" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm" value="{{ request('filter_code') }}" placeholder="Ex: DS-101">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Product Name</label>
                            <input type="text" name="filter_name" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm" value="{{ request('filter_name') }}" placeholder="Ex: Gold Ring">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Category</label>
                            <select name="category" id="design_category_filter" onchange="filterDesignSubcategories()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Subcategory</label>
                            <select name="subcategory" id="design_subcategory_filter" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                                <option value="" id="design_subcat_default_option">Select Category First</option>
                                @foreach($subcategories as $sub)
                                <option value="{{ $sub->id }}" data-category="{{ $sub->product_category_id }}" {{ request('subcategory') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors">Apply</button>
                            <a href="{{ route('buyer.design.index') }}" class="flex-1 bg-slate-100 text-slate-600 text-center py-2 rounded-lg text-sm font-bold hover:bg-slate-200 transition-colors">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="relative" x-data="{ sortOpen: false }">
                <button @click="sortOpen = !sortOpen" class="inline-flex items-center px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-all">
                    <i class="bi bi-sort-down mr-2"></i> Sort
                </button>
                <div x-show="sortOpen" @click.away="sortOpen = false" class="absolute right-0 z-50 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 p-4" style="display: none;">
                    <form action="{{ route('buyer.design.index') }}" method="GET" class="space-y-3">
                        <select name="sort" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                            <option value="created_at" {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }}>Newest Items</option>
                            <option value="design_code" {{ request('sort') == 'design_code' ? 'selected' : '' }}>Design Code</option>
                            <option value="product_name" {{ request('sort') == 'product_name' ? 'selected' : '' }}>Product Name</option>
                        </select>
                        <select name="direction" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                            <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Ascending</option>
                            <option value="desc" {{ request('direction', 'desc') == 'desc' ? 'selected' : '' }}>Descending</option>
                        </select>
                        <button type="submit" class="w-full bg-slate-800 text-white py-2 rounded-lg text-sm font-bold hover:bg-slate-900 transition-colors">Sort Now</button>
                    </form>
                </div>
            </div>

            <span class="ml-auto inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-full border border-blue-100">
                {{ $designs->total() }} Designs Found
            </span>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
            <h5 class="text-sm font-bold text-slate-700 uppercase tracking-widest">Accepted Products Gallery</h5>
            @if(Auth::guard('buyer')->user()->hasPermission('stock_order'))
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" id="select-all" onchange="toggleSelectAll(this)" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Select All on Page</span>
            </label>
            @endif
        </div>

        <div class="p-6 bg-white">
            @if($designs->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($designs as $design)
                <div class="group flex flex-col bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-300 overflow-hidden relative">
                    
                    @if(Auth::guard('buyer')->user()->hasPermission('stock_order') && !$design->isDesignLocked(Auth::guard('buyer')->user()))
                    <div class="absolute top-3 left-3 z-10">
                        <input type="checkbox" class="design-checkbox w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 shadow-sm cursor-pointer" value="{{ $design->design_code }}" onchange="updateBulkCart()">
                    </div>
                    @endif

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
                        @php
                        $isLocked = $design->isDesignLocked(Auth::guard('buyer')->user());
                        @endphp
                        <img src="{{ $imgSrc }}"
                            class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105 {{ $isLocked ? 'blur-3xl' : '' }}"
                            alt="{{ $design->product_name }}">
                        @if($isLocked)
                        <div class="absolute inset-0 flex items-center justify-center bg-white/30 backdrop-blur-[2px]">
                            <div class="bg-yellow-100/80 p-3 rounded-full shadow-sm">
                                <img src="{{ asset('images/ajlogo.png') }}" style="height: 60px; width: 60px;" alt="AJ Logo">
                            </div>
                        </div>
                        @endif
                        @if($imagesCount > 1 && !$isLocked)
                        <div class="absolute bottom-3 right-3">
                            <span class="bg-slate-900/80 text-white text-[10px] font-bold px-2 py-0.5 rounded-full ring-2 ring-white">+{{ $imagesCount - 1 }}</span>
                        </div>
                        @endif
                        @else
                        <div class="w-full h-full bg-slate-50 rounded-lg flex items-center justify-center text-slate-300">
                            <i class="bi bi-image text-4xl"></i>
                        </div>
                        @endif
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
                            @if ($design->category && $design->category->name)
                            <span class="text-[11px] font-mono font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded"> {{ $design->category->name }}</span>
                            @endif
                            <span class="font-bold text-slate-700">{{ $design->weight_from }}-{{ $design->weight_to }} gm</span>
                        </div>

                        <div class="mt-auto pt-4 border-t border-slate-50 flex gap-2">
                            @if(!$design->isDesignLocked(Auth::guard('buyer')->user()))
                            <a href="{{ route('buyer.design.show', $design->id) }}"
                                class="flex-1 inline-flex items-center justify-center py-2.5 bg-slate-900 text-white text-[10px] font-bold rounded-xl hover:bg-blue-600 transition-colors">
                                VIEW
                            </a>
                            @else
                            <button class="flex-1 inline-flex items-center justify-center py-2.5 bg-slate-100 text-slate-400 text-xs font-bold rounded-xl cursor-not-allowed border border-slate-200" disabled title="Design Locked - Request Access to View">
                                <i class="bi bi-lock-fill mr-1"></i> LOCKED
                            </button>
                            @endif

                            <button onclick="addToFavorite({{ $design->id }})" 
                                class="p-2 bg-pink-50 text-pink-600 border border-pink-100 rounded-xl hover:bg-pink-100 transition-colors" title="Add to Favorites">
                                <i class="bi bi-heart"></i>
                            </button>

                            @if(Auth::guard('buyer')->user()->hasPermission('stock_order'))
                            <a href="{{ route('buyer.stock-order.create', ['add' => $design->design_code]) }}"
                                class="p-2 bg-blue-50 text-blue-600 border border-blue-100 rounded-xl hover:bg-blue-100 transition-colors" title="Add to Live Stock Order">
                                <i class="bi bi-cart-plus"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-8 flex justify-center">
                {{ $designs->appends(request()->query())->links() }}
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                    <i class="bi bi-search text-3xl text-slate-200"></i>
                </div>
                <h5 class="text-slate-500 font-medium">No designs found matching your search.</h5>
                <a href="{{ route('buyer.design.index') }}" class="mt-2 text-blue-600 font-bold text-sm">Reset all filters</a>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function filterDesignSubcategories() {
    const categorySelect = document.getElementById('design_category_filter');
    const subcategorySelect = document.getElementById('design_subcategory_filter');
    const defaultOption = document.getElementById('design_subcat_default_option');
    if (!categorySelect || !subcategorySelect) return;

    const categoryId = categorySelect.value;
    const options = subcategorySelect.querySelectorAll('option[data-category]');

    if (!categoryId) {
        subcategorySelect.disabled = true;
        if (defaultOption) {
            defaultOption.textContent = 'Select Category First';
        }
        subcategorySelect.value = '';
        options.forEach(opt => {
            opt.hidden = true;
            opt.disabled = true;
        });
        return;
    }

    subcategorySelect.disabled = false;
    if (defaultOption) {
        defaultOption.textContent = 'All Subcategories';
    }

    let selectedOptionHidden = false;
    options.forEach(option => {
        if (option.getAttribute('data-category') === categoryId) {
            option.hidden = false;
            option.disabled = false;
        } else {
            option.hidden = true;
            option.disabled = true;
            if (option.selected) {
                selectedOptionHidden = true;
            }
        }
    });

    if (selectedOptionHidden) {
        subcategorySelect.value = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    filterDesignSubcategories();
});

function addToFavorite(productId) {
    fetch("{{ route('buyer.favorites.store') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

function addToCart(productId) {
    alert("Added to cart (Dummy)!");
}

function updateBulkCart() {
    const checkboxes = document.querySelectorAll('.design-checkbox:checked');
    const bulkBtn = document.getElementById('bulk-cart-btn');
    const bulkCount = document.getElementById('bulk-count');
    
    if (checkboxes.length > 0) {
        bulkBtn.classList.remove('hidden');
        bulkBtn.classList.add('inline-flex');
        bulkCount.innerText = checkboxes.length;
    } else {
        bulkBtn.classList.add('hidden');
        bulkBtn.classList.remove('inline-flex');
    }
}

function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('.design-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = source.checked;
    });
    updateBulkCart();
}

function bulkAddToCart() {
    const checkboxes = document.querySelectorAll('.design-checkbox:checked');
    if (checkboxes.length === 0) return;
    
    const codes = Array.from(checkboxes).map(cb => cb.value).join(',');
    window.location.href = `{{ route('buyer.stock-order.create') }}?add=${encodeURIComponent(codes)}`;
}
</script>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection