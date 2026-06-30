@extends('key-user.layouts.app')

@section('title', 'My Catalogue')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">My Catalogue</h1>
            <p class="text-sm text-gray-500">View and manage your personal product collection</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('key-user.catalogue.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors shadow-sm">
                <i class="bi bi-file-earmark-excel mr-2"></i> Export Excel
            </a>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm transition">
                    <i class="bi bi-funnel mr-2"></i> Advanced Filter
                </button>

                <div x-show="open" @click.away="open = false" x-cloak
                    class="absolute right-0 mt-2 w-80 md:w-[450px] bg-white rounded-xl shadow-xl border border-gray-200 z-50 p-6">
                    <form action="{{ route('key-user.catalogue.index') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Design Code</label>
                                <input type="text" name="filter_design_code" class="w-full p-2 border border-gray-300 rounded-md text-sm" value="{{ request('filter_design_code') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Product Code</label>
                                <input type="text" name="filter_product_code" class="w-full p-2 border border-gray-300 rounded-md text-sm" value="{{ request('filter_product_code') }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Category</label>
                                <input type="text" name="filter_category" class="w-full p-2 border border-gray-300 rounded-md text-sm" value="{{ request('filter_category') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Subcategory</label>
                                <input type="text" name="filter_subcategory" class="w-full p-2 border border-gray-300 rounded-md text-sm" value="{{ request('filter_subcategory') }}">
                            </div>
                        </div>
                        <div class="flex gap-2 pt-4 border-t">
                            <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">Apply</button>
                            <a href="{{ route('key-user.catalogue.index') }}" class="flex-1 bg-gray-100 text-gray-700 text-center py-2 rounded-lg text-sm font-semibold hover:bg-gray-200 transition border">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <button id="print_selected_btn" onclick="printSelected()" class="hidden inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition shadow-sm">
                <i class="bi bi-printer mr-2"></i> Print Selected
            </button>
            <!-- <button onclick="window.print()" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-cyan-600 rounded-lg hover:bg-cyan-700 transition shadow-sm">
                <i class="bi bi-printer mr-2"></i> Print Page
            </button> -->
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
            <form action="{{ route('key-user.catalogue.index') }}" method="GET" class="relative flex-1 max-w-md">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all text-sm"
                    placeholder="Search product...">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="bi bi-search text-gray-400"></i>
                </div>
            </form>

            <div class="flex items-center gap-3 lg:ml-auto">
                <label class="flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-100 transition">
                    <input type="checkbox" id="select_all" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="text-xs font-bold text-gray-600 uppercase">Select All</span>
                </label>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm transition">
                        <i class="bi bi-sort-down mr-2"></i> Sort
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-200 z-50 overflow-hidden">
                        <a class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition" href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}">Newest First</a>
                        <a class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition" href="{{ request()->fullUrlWithQuery(['sort' => 'name_asc']) }}">Name (A-Z)</a>
                        <a class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition" href="{{ request()->fullUrlWithQuery(['sort' => 'name_desc']) }}">Name (Z-A)</a>
                    </div>
                </div>
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-100">
                    {{ $products->total() }} Items Found
                </span>
            </div>
        </div>
    </div>

    <div class="pb-10">
        @if($products->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
            <div class="group bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col overflow-hidden relative">

                <!-- Checkbox Overlay -->
                <div class="absolute top-3 left-3 z-10">
                    <input type="checkbox" name="selected_products[]" value="{{ $product->id }}"
                        class="product-checkbox w-5 h-5 text-indigo-600 border-gray-300 rounded-md focus:ring-indigo-500 shadow-sm cursor-pointer transition-transform transform scale-0 group-hover:scale-110">
                </div>

                <div class="relative h-52 bg-white flex items-center justify-center border-b border-gray-50 overflow-hidden">
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
                    <img src="{{ $imgSrc }}" class="w-full h-full object-contain p-4 transition-transform duration-500 group-hover:scale-105" alt="{{ $product->product_name }}">
                    @if($imagesCount > 1)
                    <div class="absolute bottom-3 right-3">
                        <span class="bg-gray-900/80 text-white text-[10px] font-bold px-2 py-1 rounded-full backdrop-blur-sm">+{{ $imagesCount - 1 }}</span>
                    </div>
                    @endif
                    @else
                    <div class="w-full h-full flex flex-col items-center justify-center bg-gray-50 text-gray-300">
                        <i class="bi bi-image text-4xl"></i>
                    </div>
                    @endif
                </div>

                <div class="p-4 flex flex-col flex-1">
                    <div class="mb-3">
                        <h3 class="text-sm font-bold text-gray-800 line-clamp-1">{{ $product->product_name }}</h3>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-[11px] font-mono font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ $product->design_code }}</span>
                            <span class="text-[11px] font-mono text-red-500 font-bold">{{ $product->product_code }}</span>
                        </div>
                    </div>

                    <div class="mt-auto pt-3 border-t border-gray-50 flex items-center justify-between">
                        <span class="text-[11px] font-medium text-gray-500 uppercase tracking-wider">{{ $product->category->name ?? 'N/A' }}</span>
                        <a href="{{ route('key-user.product.show', $product->id) }}"
                            class="text-[11px] font-bold text-gray-900 hover:text-indigo-600 transition flex items-center">
                            View Details <i class="bi bi-chevron-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-8 flex justify-center">
            {{ $products->appends(request()->query())->links() }}
        </div>
        @else
        <div class="bg-white rounded-2xl border border-gray-200 border-dashed py-20 flex flex-col items-center justify-center">
            <i class="bi bi-inbox text-5xl text-gray-200 mb-4"></i>
            <h5 class="text-lg font-bold text-gray-800">No products found</h5>
            <p class="text-sm text-gray-500 mt-1">Your catalogue is currently empty.</p>
        </div>
        @endif
    </div>
</div>

<form id="print_selected_form" action="{{ route('key-user.catalogue.print-selected') }}" method="POST" target="_blank" class="hidden">
    @csrf
    <div id="selected_ids_container"></div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select_all');
        const checkboxes = document.querySelectorAll('.product-checkbox');
        const printBtn = document.getElementById('print_selected_btn');

        function updatePrintButton() {
            const checkedCount = document.querySelectorAll('.product-checkbox:checked').length;
            if (checkedCount > 0) {
                printBtn.classList.remove('hidden');
                // Persist checkbox visibility when items are selected
                checkboxes.forEach(cb => {
                    cb.classList.remove('scale-0');
                    cb.classList.add('scale-110');
                });
            } else {
                printBtn.classList.add('hidden');
                checkboxes.forEach(cb => {
                    if (!cb.checked) {
                        cb.classList.add('scale-0');
                        cb.classList.remove('scale-110');
                    }
                });
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
                updatePrintButton();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updatePrintButton();
                if (!this.checked) {
                    selectAll.checked = false;
                } else if (document.querySelectorAll('.product-checkbox:checked').length === checkboxes.length) {
                    selectAll.checked = true;
                }
            });
        });
    });

    function printSelected() {
        const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select at least one design.');
            return;
        }

        const container = document.getElementById('selected_ids_container');
        container.innerHTML = '';
        checkedBoxes.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_products[]';
            input.value = cb.value;
            container.appendChild(input);
        });

        document.getElementById('print_selected_form').submit();
    }
</script>

<style>
    @media print {

        aside,
        header,
        .no-print,
        form,
        .btn,
        .shadow-sm {
            display: none !important;
        }

        .grid {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 10px !important;
        }

        .group {
            border: 1px solid #eee !important;
            break-inside: avoid;
        }
    }
</style>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection