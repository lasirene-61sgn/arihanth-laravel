@extends('buyer.layouts.app')

@section('title', 'My Design Catalogue')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">My Approved Catalogue</h1>
            <p class="text-sm text-slate-500">View and manage your personal approved designs.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" form="bulk_print_form" onclick="submitBulkPrint()"
                class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-xl text-sm font-bold hover:bg-indigo-100 transition-all shadow-sm">
                <i class="bi bi-printer mr-2"></i> Print Selected
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
        <div class="p-4 flex flex-wrap items-center gap-4">
            <form action="{{ route('buyer.catalogue.index') }}" method="GET" class="flex-grow max-w-md relative group">
                <input type="text" name="search"
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all"
                    placeholder="Search my catalogue..." value="{{ request('search') }}">
                <div class="absolute left-3 top-3 text-slate-400 group-focus-within:text-blue-600">
                    <i class="bi bi-search"></i>
                </div>
            </form>

            <div class="relative" x-data="{ filterOpen: false }">
                <button @click="filterOpen = !filterOpen"
                    class="inline-flex items-center px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-all shadow-sm">
                    <i class="bi bi-funnel mr-2"></i> Filters
                </button>
                <div x-show="filterOpen" @click.away="filterOpen = false"
                    class="absolute left-0 lg:right-0 lg:left-auto z-50 mt-2 w-screen max-w-md bg-white rounded-2xl shadow-xl border border-slate-100 p-6"
                    style="display: none;"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95">
                    <form action="{{ route('buyer.catalogue.index') }}" method="GET">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="col-span-1">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Design Code</label>
                                <input type="text" name="filter_design_code" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm" value="{{ request('filter_design_code') }}" placeholder="Ex: DS-101">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Product Code</label>
                                <input type="text" name="filter_product_code" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm" value="{{ request('filter_product_code') }}" placeholder="Ex: PRD-001">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Product Name</label>
                                <input type="text" name="filter_product_name" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm" value="{{ request('filter_product_name') }}" placeholder="Ex: Gold Ring">
                            </div>
                            
                            <!-- Dynamic Category Dropdown -->
                            <div class="col-span-1">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Category</label>
                                <select name="filter_category" id="catalogue_category_filter" onchange="filterCatalogueSubcategories()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('filter_category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Dynamic Subcategory Dropdown -->
                            <div class="col-span-1">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Subcategory</label>
                                <select name="filter_subcategory" id="catalogue_subcategory_filter" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                                    <option value="" id="catalogue_subcat_default_option">Select Category First</option>
                                    @foreach($subcategories as $sub)
                                    <option value="{{ $sub->id }}" data-category="{{ $sub->product_category_id }}" {{ request('filter_subcategory') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-2 pt-4 border-t border-slate-100">
                            <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors">Apply Filter</button>
                            <a href="{{ route('buyer.catalogue.index') }}" class="flex-1 bg-slate-100 text-slate-600 text-center py-2 rounded-lg text-sm font-bold hover:bg-slate-200 transition-colors">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="relative" x-data="{ sortOpen: false }">
                <button @click="sortOpen = !sortOpen"
                    class="inline-flex items-center px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-all shadow-sm">
                    <i class="bi bi-sort-down mr-2"></i> Sort
                </button>
                <div x-show="sortOpen" @click.away="sortOpen = false"
                    class="absolute right-0 z-50 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden"
                    style="display: none;">
                    <div class="py-1">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Newest First</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name_asc']) }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Name (A-Z)</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name_desc']) }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Name (Z-A)</a>
                    </div>
                </div>
            </div>

            <div class="ml-auto">
                <label class="inline-flex items-center cursor-pointer group">
                    <input type="checkbox" id="select_all" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 transition-all">
                    <span class="ml-2 text-xs font-bold text-slate-500 group-hover:text-slate-800 transition-colors uppercase tracking-tighter">Select All Page</span>
                </label>
            </div>
        </div>
    </div>

    <form id="bulk_print_form" action="{{ route('buyer.catalogue.print-selected') }}" method="POST" target="_blank">
        @csrf
        <div class="bg-transparent overflow-hidden">
            @if($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($products as $item)
                <div class="group flex flex-col bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-blue-300 transition-all duration-300 overflow-hidden relative">
                    <div class="absolute top-3 left-3 z-10">
                        <input type="checkbox" name="selected_products[]" value="{{ $item->id }}" class="product-checkbox w-5 h-5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 shadow-sm transition-all pointer-events-auto cursor-pointer">
                    </div>

                    <div class="relative aspect-[4/3] bg-white p-4 overflow-hidden border-b border-slate-50 flex items-center justify-center">
                        @php $img = $item->images->first(); @endphp
                        @if($img)
                        <img src="{{ asset('storage/' . $img->path) }}"
                            class="max-w-full max-h-full object-contain transition-transform duration-500 group-hover:scale-110"
                            alt="{{ $item->product_name }}">
                        @else
                        <div class="flex flex-col items-center justify-center text-slate-300">
                            <i class="bi bi-image text-5xl opacity-20"></i>
                            <span class="text-[10px] uppercase font-bold mt-2 tracking-widest">No Image</span>
                        </div>
                        @endif
                    </div>

                    <div class="p-4 flex flex-col flex-1">
                        <div class="flex justify-between items-start mb-2">
                            <h6 class="text-sm font-bold text-slate-800 line-clamp-1 flex-1 mr-2" title="{{ $item->product_name }}">
                                {{ $item->product_name }}
                            </h6>
                            <span class="text-[10px] font-mono font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded">
                                {{ $item->design_code ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="flex flex-col space-y-1 mb-4">
                            <div class="flex justify-between text-[11px]">
                                <span class="text-slate-500">Product Code</span>
                                <span class="font-semibold text-slate-700">{{ $item->product_code }}</span>
                            </div>
                            <div class="flex justify-between text-[11px]">
                                <span class="text-slate-500">Category</span>
                                <span class="font-semibold text-slate-700">{{ $item->category->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between text-[11px]">
                                <span class="text-slate-500">Subcategory</span>
                                <span class="font-semibold text-slate-700">{{ $item->subcategory->name ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="mt-auto">
                            <a href="{{ route('buyer.catalogue.show', $item->id) }}"
                                class="inline-flex items-center justify-center w-full py-2 bg-slate-50 text-slate-700 text-[11px] font-bold rounded-xl hover:bg-slate-200 transition-colors shadow-sm">
                                Details
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
            <div class="flex flex-col items-center justify-center py-20 bg-white rounded-3xl border border-dashed border-slate-300">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                    <i class="bi bi-folder-x text-4xl text-slate-300"></i>
                </div>
                <h5 class="text-slate-600 font-bold">No personal designs found.</h5>
                <p class="text-slate-400 text-sm mt-1">Your approved catalogue is currently empty.</p>
            </div>
            @endif
        </div>
    </form>
</div>

<script>
    function filterCatalogueSubcategories() {
        const categorySelect = document.getElementById('catalogue_category_filter');
        const subcategorySelect = document.getElementById('catalogue_subcategory_filter');
        const defaultOption = document.getElementById('catalogue_subcat_default_option');
        if (!categorySelect || !subcategorySelect) return;

        const categoryId = categorySelect.value;
        const options = subcategorySelect.querySelectorAll('option[data-category]');

        if (!categoryId) {
            // Disable subcategory if no category is picked
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

        // Enable subcategory and only show child options
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
        // Initialize dropdown filter state on page load
        filterCatalogueSubcategories();

        const selectAll = document.getElementById('select_all');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.product-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        }
    });

    function submitBulkPrint() {
        const checked = document.querySelectorAll('.product-checkbox:checked');
        if (checked.length === 0) {
            alert('Please select at least one product to print.');
            return;
        }
        document.getElementById('bulk_print_form').submit();
    }
</script>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection