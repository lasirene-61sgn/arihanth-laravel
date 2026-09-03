@extends('craftsman_staff.layouts.app')

@section('title', 'My Design Catalogue')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-indigo-200 pb-4">
        <div>
            <h4 class="text-2xl font-bold text-indigo-900">My Approved Catalogue</h4>
            <p class="text-sm text-indigo-600">Overview of your personalized approved designs</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('craftsman_staff.catalogue.export', request()->query()) }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                <i class="bi bi-file-earmark-excel me-2"></i> Export Excel
            </a>
            <button id="print_selected_btn" onclick="printSelected()"
                class="hidden inline-flex items-center px-4 py-2 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 transition shadow-sm">
                <i class="bi bi-printer me-2"></i> Print Selected
            </button>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-indigo-100 flex flex-wrap items-center gap-4">
        <form action="{{ route('craftsman_staff.catalogue.index') }}" method="GET" class="relative flex-grow max-w-sm">
            <input type="text" name="search" placeholder="Search my catalogue..."
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
                <form action="{{ route('craftsman_staff.catalogue.index') }}" method="GET" class="space-y-4">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-indigo-700 uppercase mb-1">Design Code</label>
                            <input type="text" name="filter_design_code" class="w-full px-3 py-2 border border-indigo-100 rounded-md text-sm outline-none focus:ring-2 focus:ring-indigo-500" value="{{ request('filter_design_code') }}" placeholder="Ex: DES001">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-indigo-700 uppercase mb-1">Product Code</label>
                            <input type="text" name="filter_product_code" class="w-full px-3 py-2 border border-indigo-100 rounded-md text-sm outline-none focus:ring-2 focus:ring-indigo-500" value="{{ request('filter_product_code') }}" placeholder="Ex: PRD001">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-indigo-700 uppercase mb-1">Product Name</label>
                            <input type="text" name="filter_product_name" class="w-full px-3 py-2 border border-indigo-100 rounded-md text-sm outline-none focus:ring-2 focus:ring-indigo-500" value="{{ request('filter_product_name') }}" placeholder="Enter name...">
                        </div>

                        {{-- Cascading Category & Subcategory Dropdowns --}}
                        <div>
                            <label class="block text-xs font-bold text-indigo-700 uppercase mb-1">Category</label>
                            <select name="product_category_id" id="catalogue_category_select" class="w-full px-3 py-2 border border-indigo-100 rounded-md text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('product_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Subcategory: Shown only if selected category has subcategories --}}
                        <div id="catalogue_subcategory_wrapper" style="display: none;">
                            <label class="block text-xs font-bold text-indigo-700 uppercase mb-1">Subcategory</label>
                            <select name="subcategory_id" id="catalogue_subcategory_select" class="w-full px-3 py-2 border border-indigo-100 rounded-md text-sm outline-none focus:ring-2 focus:ring-indigo-500">
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
                        <a href="{{ route('craftsman_staff.catalogue.index') }}" class="flex-1 text-center border border-indigo-200 py-2 rounded-lg text-sm font-bold text-indigo-700 hover:bg-indigo-50 transition">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="dropdown">
            <button class="inline-flex items-center px-4 py-2 bg-white border border-indigo-200 text-indigo-700 text-sm font-medium rounded-lg hover:bg-indigo-50 transition dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-sort-down me-2"></i> Sort
            </button>
            <div class="dropdown-menu shadow-xl border-0 rounded-lg overflow-hidden">
                <a class="dropdown-item py-2 hover:bg-indigo-50" href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}">Newest First</a>
                <a class="dropdown-item py-2 hover:bg-indigo-50" href="{{ request()->fullUrlWithQuery(['sort' => 'name_asc']) }}">Name (A-Z)</a>
                <a class="dropdown-item py-2 hover:bg-indigo-50" href="{{ request()->fullUrlWithQuery(['sort' => 'name_desc']) }}">Name (Z-A)</a>
            </div>
        </div>
    </div>

    <div>
        @if($designs->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($designs as $item)
            <div class="group bg-white rounded-2xl shadow-sm border border-indigo-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col">

                <div class="relative h-56 bg-white overflow-hidden flex items-center justify-center p-4">
                    <div class="absolute top-3 left-3 z-10">
                        <input type="checkbox" name="selected_products[]" value="{{ $item->id }}" class="product-checkbox w-5 h-5 text-indigo-600 border-indigo-300 rounded focus:ring-indigo-500 shadow-sm transition-transform hover:scale-110 cursor-pointer">
                    </div>
                    @php
                    $imagesCount = $item->images ? $item->images->count() : 0;
                    $firstImage = $imagesCount > 0 ? $item->images->first()->path : null;

                    if (!$firstImage && $item->product_image) {
                        $imgs = explode(',', $item->product_image);
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
                        alt="{{ $item->product_name }}">
                    @if($imagesCount > 1)
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
                        <h6 class="font-bold text-indigo-900 truncate pr-2 w-2/3" title="{{ $item->product_name }}">
                            {{ $item->product_name }}
                        </h6>
                        <span class="text-[10px] font-black text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100 uppercase">
                            {{ $item->design_code }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center text-sm mb-4">
                        <div class="text-xs">
                            <span class="text-indigo-400 font-bold uppercase tracking-tighter">Code:</span>
                            <span class="text-indigo-900 font-semibold ml-1">{{ $item->product_code }}</span>
                        </div>
                        <div class="px-2 py-0.5 bg-indigo-900 text-white text-[10px] rounded font-bold">
                            {{ $item->category->name ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="mt-auto">
                        <a href="{{ route('craftsman_staff.catalogue.show', $item->id) }}"
                            class="block w-full text-center bg-indigo-900 text-white text-sm font-bold py-2.5 rounded-xl hover:bg-indigo-800 transition-all active:scale-95 shadow-sm">
                            View Design
                        </a>
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
                <i class="bi bi-patch-question text-indigo-200 text-4xl"></i>
            </div>
            <h5 class="text-indigo-900 font-bold">No personal designs found.</h5>
            <p class="text-indigo-500 text-sm mt-1">Your approved catalogue is currently empty.</p>
        </div>
        @endif
    </div>
</div>

<form id="print_selected_form" action="{{ route('craftsman_staff.catalogue.print-selected') }}" method="POST" target="_blank" class="hidden">
    @csrf
    <div id="selected_ids_container"></div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cascading Category & Subcategory Logic
        const categorySelect = document.getElementById('catalogue_category_select');
        const subcategorySelect = document.getElementById('catalogue_subcategory_select');
        const subcategoryWrapper = document.getElementById('catalogue_subcategory_wrapper');
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

            // Display subcategory wrapper only if matching subcategories exist for the selected category
            subcategoryWrapper.style.display = matchingCount > 0 ? 'block' : 'none';
        }

        if (categorySelect && subcategorySelect && subcategoryWrapper) {
            categorySelect.addEventListener('change', syncSubcategories);
            syncSubcategories(); // Evaluate on page load
        }

        // Checkbox & Print Handler
        const checkboxes = document.querySelectorAll('.product-checkbox');
        const printBtn = document.getElementById('print_selected_btn');

        function updatePrintButton() {
            const checkedCount = document.querySelectorAll('.product-checkbox:checked').length;
            if (checkedCount > 0) {
                printBtn.classList.remove('hidden');
            } else {
                printBtn.classList.add('hidden');
            }
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updatePrintButton);
        });
    });

    function printSelected() {
        const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select at least one design to print.');
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
@endsection