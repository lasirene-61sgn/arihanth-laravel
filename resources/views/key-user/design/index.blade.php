@extends('key-user.layouts.app')

@section('title', 'Approved Design Catalogue')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Approved Design Catalogue</h1>
            <p class="text-sm text-gray-500">Browse and manage accepted product designs</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('key-user.design.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors shadow-sm">
                <i class="bi bi-file-earmark-excel mr-2"></i> Export Excel
            </a>
            <button id="print_selected_btn" onclick="printSelected()" class="hidden inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                <i class="bi bi-printer mr-2"></i> Print Selected
            </button>
            <!-- <button onclick="window.print()" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-cyan-600 rounded-lg hover:bg-cyan-700 transition-colors shadow-sm">
                <i class="bi bi-printer mr-2"></i> Print Page
            </button> -->
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center gap-4">

            <form action="{{ route('key-user.design.index') }}" method="GET" class="relative flex-1 max-w-md">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all text-sm"
                    placeholder="Search Design Code or Name...">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="bi bi-search text-gray-400"></i>
                </div>
                <button type="submit" class="hidden">Search</button>
            </form>

            <div class="flex flex-wrap items-center gap-3 lg:ml-auto">
                <label class="flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-100 transition">
                    <input type="checkbox" id="select_all" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="text-xs font-bold text-gray-600 uppercase">Select All</span>
                </label>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm transition">
                        <i class="bi bi-funnel mr-2"></i> Advanced Filter
                    </button>

                    <div x-show="open" @click.away="open = false" x-cloak
                        class="absolute right-0 mt-2 w-80 md:w-[450px] bg-white rounded-xl shadow-xl border border-gray-200 z-50 p-6">
                        <form action="{{ route('key-user.design.index') }}" method="GET" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Design Code</label>
                                    <input type="text" name="filter_design_code" class="w-full p-2 border border-gray-300 rounded-md text-sm" value="{{ request('filter_design_code') }}" placeholder="Ex: DES001">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Product Code</label>
                                    <input type="text" name="filter_product_code" class="w-full p-2 border border-gray-300 rounded-md text-sm" value="{{ request('filter_product_code') }}" placeholder="Ex: PRD001">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Product Name</label>
                                <input type="text" name="filter_name" class="w-full p-2 border border-gray-300 rounded-md text-sm" value="{{ request('filter_name') }}" placeholder="Enter name...">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Category</label>
                                    <input type="text" name="filter_category" class="w-full p-2 border border-gray-300 rounded-md text-sm" value="{{ request('filter_category') }}" placeholder="Category">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Subcategory</label>
                                    <input type="text" name="filter_subcategory" class="w-full p-2 border border-gray-300 rounded-md text-sm" value="{{ request('filter_subcategory') }}" placeholder="Subcategory">
                                </div>
                            </div>

                            <div class="flex gap-2 pt-4 border-t">
                                <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition shadow-sm">
                                    <i class="bi bi-search mr-1"></i> Search Catalogue
                                </button>
                                <a href="{{ route('key-user.design.index') }}" class="flex-1 bg-gray-100 text-gray-700 text-center py-2 rounded-lg text-sm font-semibold hover:bg-gray-200 transition border">
                                    <i class="bi bi-arrow-counterclockwise mr-1"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm transition">
                        <i class="bi bi-sort-down mr-2"></i> Sort
                    </button>

                    <div x-show="open" @click.away="open = false" x-cloak
                        class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-200 z-50 p-4">
                        <form action="{{ route('key-user.design.index') }}" method="GET" class="space-y-3">
                            <select name="sort" class="w-full p-2 border border-gray-300 rounded-md text-sm">
                                <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Latest Accepted</option>
                                <option value="design_code" {{ request('sort') == 'design_code' ? 'selected' : '' }}>Design Code</option>
                                <option value="weight_from" {{ request('sort') == 'weight_from' ? 'selected' : '' }}>Weight (Low-High)</option>
                            </select>
                            <button type="submit" class="w-full bg-gray-800 text-white py-2 rounded-lg text-sm font-semibold hover:bg-black transition">Sort Now</button>
                        </form>
                    </div>
                </div>

                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                    {{ $designs->total() }} Designs in Catalogue
                </span>
            </div>
        </div>
    </div>

    <div class="pb-10">
        @if($designs->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($designs as $design)
            <div class="group bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col overflow-hidden relative">

                <!-- Checkbox Overlay -->
                <div class="absolute top-3 left-3 z-10">
                    <input type="checkbox" name="selected_products[]" value="{{ $design->id }}"
                        class="product-checkbox w-5 h-5 text-indigo-600 border-gray-300 rounded-md focus:ring-indigo-500 shadow-sm cursor-pointer transition-transform transform scale-0 group-hover:scale-110">
                </div>

                <div class="relative h-56 bg-white overflow-hidden flex items-center justify-center border-b border-gray-50">
                    @php
                    $imagesCount = $design->images->count();
                    $firstImage = $imagesCount > 0 ? $design->images->first()->path : null;

                    if (!$firstImage && $design->product_image) {
                    $imgs = explode(',', $design->product_image);
                    $firstImage = trim($imgs[0]);
                    }

                    $imgSrc = null;
                    if ($firstImage) {
                    if (str_starts_with($firstImage, 'http')) {
                    $imgSrc = $firstImage;
                    } elseif (str_starts_with($firstImage, 'products/')) {
                    $imgSrc = asset('storage/' . $firstImage);
                    } elseif (str_starts_with($firstImage, 'images/') || str_starts_with($firstImage, 'storage/')) {
                    $imgSrc = asset($firstImage);
                    } else {
                    $imgSrc = asset('storage/products/' . $firstImage);
                    }
                    }
                    @endphp

                    @if($imgSrc)
                    @php
                    $isLocked = $design->isDesignLocked(Auth::guard('key_user')->user());
                    @endphp
                    <img src="{{ $imgSrc }}"
                        class="w-full h-full object-contain p-4 transition-transform duration-500 group-hover:scale-105 {{ $isLocked ? 'blur-2xl' : '' }}"
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
                        <span class="bg-gray-900/80 text-white text-[10px] font-bold px-2 py-1 rounded-full backdrop-blur-sm">
                            +{{ $imagesCount - 1 }} Photos
                        </span>
                    </div>
                    @endif
                    @else
                    <div class="w-full h-full flex flex-col items-center justify-center bg-gray-50 text-gray-300">
                        <i class="bi bi-image text-4xl mb-2"></i>
                        <span class="text-xs">No Preview</span>
                    </div>
                    @endif
                </div>

                <div class="p-4 flex flex-col flex-1">
                    <div class="flex justify-between items-start gap-2 mb-2">
                        <div class="max-w-[70%] mx-auto flex justify-center">
                            <h6 class="font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-4 py-1 rounded-lg shadow-sm text-center truncate"
                                title="{{ $design->design_code }}">
                                {{ $design->design_code }}
                            </h6>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-[13px] text-gray-500 mb-4">
                        <div class="flex items-center">
                            @if ($design->category->name)
                            <i class="bi bi-rulers mr-1.5"></i>
                            <span class="font-semibold text-gray-700 ml-1">{{ $design->category->name ?? 'N/A' }}</span>
                            @endif
                        </div>
                        <div class="font-bold text-gray-700">
                            {{ $design->weight_from }}-{{ $design->weight_to }}g
                        </div>
                    </div>

                    <div class="mt-auto">
                        @if(!$design->isDesignLocked(Auth::guard('key_user')->user()))
                        <a href="{{ route('key-user.design.show', $design->id) }}"
                            class="block w-full text-center py-2.5 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-indigo-600 transition-colors duration-300 shadow-sm">
                            View Details
                        </a>
                        @else
                        <button class="w-full py-2.5 bg-gray-100 text-gray-400 text-sm font-bold rounded-xl cursor-not-allowed flex items-center justify-center" disabled>
                            <i class="bi bi-lock-fill mr-2"></i> Locked
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $designs->appends(request()->query())->links() }}
        </div>
        @else
        <div class="bg-white rounded-2xl border border-gray-200 border-dashed py-20 flex flex-col items-center justify-center">
            <div class="h-20 w-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                <i class="bi bi-search text-3xl text-gray-300"></i>
            </div>
            <h5 class="text-lg font-bold text-gray-800">No designs found</h5>
            <p class="text-sm text-gray-500 mt-1">Try adjusting your filters or search keywords.</p>
            <a href="{{ route('key-user.design.index') }}" class="mt-4 text-indigo-600 font-semibold text-sm hover:underline">Clear all filters</a>
        </div>
        @endif
    </div>
</div>

<form id="print_selected_form" action="{{ route('key-user.design.print-selected') }}" method="POST" target="_blank" class="hidden">
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
    /* Printing optimization */
    @media print {
        .bg-white {
            border: 1px solid #eee !important;
        }

        aside,
        header,
        form,
        .pagination,
        .btn {
            display: none !important;
        }

        .grid {
            display: block !important;
        }

        .group {
            break-inside: avoid;
            margin-bottom: 20px;
            page-break-inside: avoid;
            border: 1px solid #ddd !important;
        }
    }

    /* Hover shadow for cards */
    .hover-shadow:hover {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }
</style>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection