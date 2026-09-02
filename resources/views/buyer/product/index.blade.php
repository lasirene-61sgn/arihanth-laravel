@extends('buyer.layouts.app')

@section('title', 'Products')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-visible">
        <div class="p-4 md:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Product Management</h1>
                <p class="text-xs text-slate-500 mt-1">Manage and filter your product inventory</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <form action="{{ route('buyer.product.index') }}" method="GET" class="relative group">
                    <input type="text" name="search"
                        class="w-full sm:w-64 pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all outline-none"
                        placeholder="Quick search..." value="{{ request('search') }}">
                    <div class="absolute left-3 top-2.5 text-slate-400 group-focus-within:text-blue-500">
                        <i class="bi bi-search"></i>
                    </div>
                </form>

                <div class="relative inline-block text-left" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="inline-flex items-center px-4 py-2 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-all">
                        <i class="bi bi-funnel mr-2"></i> Advanced Filter
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 z-50 mt-2 w-80 origin-top-right rounded-2xl bg-white p-6 shadow-xl border border-slate-100 ring-1 ring-black ring-opacity-5 focus:outline-none" style="display: none;">
                        <form action="{{ route('buyer.product.index') }}" method="GET" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Product Name</label>
                                <input type="text" name="filter_name" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm" value="{{ request('filter_name') }}" placeholder="Filter by name...">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Product Code</label>
                                <input type="text" name="filter_code" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm" value="{{ request('filter_code') }}" placeholder="Filter by code...">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Category</label>
                                <select name="category" id="buyer_category_filter" onchange="filterBuyerSubcategories()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Subcategory</label>
                                <select name="subcategory" id="buyer_subcategory_filter" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                                    <option value="" id="subcat_default_option">Select Category First</option>
                                    @foreach($subcategories as $sub)
                                    <option value="{{ $sub->id }}" data-category="{{ $sub->product_category_id }}" {{ request('subcategory') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex gap-2 pt-2">
                                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors">Apply</button>
                                <a href="{{ route('buyer.product.index') }}" class="flex-1 bg-slate-100 text-slate-600 text-center py-2 rounded-lg text-sm font-bold hover:bg-slate-200 transition-colors">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="relative inline-block text-left" x-data="{ sortOpen: false }">
                    <button @click="sortOpen = !sortOpen" type="button" class="inline-flex items-center px-4 py-2 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-all">
                        <i class="bi bi-sort-down mr-2"></i> Sort
                    </button>
                    <div x-show="sortOpen" @click.away="sortOpen = false" class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-2xl bg-white p-4 shadow-xl border border-slate-100 ring-1 ring-black ring-opacity-5 focus:outline-none" style="display: none;">
                        <form action="{{ route('buyer.product.index') }}" method="GET" class="space-y-3">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Sort By</label>
                                <select name="sort" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                                    <option value="created_at" {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }}>Date Added</option>
                                    <option value="product_name" {{ request('sort') == 'product_name' ? 'selected' : '' }}>Product Name</option>
                                    <option value="product_code" {{ request('sort') == 'product_code' ? 'selected' : '' }}>Product Code</option>
                                    <option value="product_category_id" {{ request('sort') == 'product_category_id' ? 'selected' : '' }}>Category</option>
                                </select>
                            </div>
                            <select name="direction" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                                <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Ascending (A-Z)</option>
                                <option value="desc" {{ request('direction', 'desc') == 'desc' ? 'selected' : '' }}>Descending (Z-A)</option>
                            </select>
                            <button type="submit" class="w-full bg-slate-800 text-white py-2 rounded-lg text-sm font-bold hover:bg-slate-900 transition-colors">Apply Sort</button>
                        </form>
                    </div>
                </div>

                <div class="h-8 w-px bg-slate-200 mx-1 hidden sm:block"></div>

                <button id="print_selected_btn" onclick="printSelected()" class="hidden inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-sm">
                    <i class="bi bi-printer mr-2"></i> Print Selected
                </button>
                <button onclick="window.print()" class="p-2 bg-white text-slate-600 rounded-xl hover:bg-slate-50 border border-slate-200 transition-all" title="Print Page">
                    <i class="bi bi-printer text-lg"></i>
                </button>
                <a href="{{ route('buyer.product.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition-all shadow-sm">
                    <i class="bi bi-plus-lg mr-2"></i> Create
                </a>
            </div>
        </div>
    </div>

    <!-- Bulk Upload Card -->
    <div class="card shadow-sm mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-file-import"></i> Bulk Upload Your Products</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('buyer.product.bulk-upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <input type="file" name="zip_file" class="form-control" accept=".zip" required>
                        <p class="text-muted small mt-2 mb-0">
                            Upload a ZIP containing your <b>products.csv</b> and product photos.
                        </p>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-success w-100">Upload Now</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Product Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">
                            <input type="checkbox" id="select_all" class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Image</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Product Code</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Product Name</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Subcategory</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Files</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="px-6 py-4">
                            <input type="checkbox" name="selected_products[]" value="{{ $product->id }}" class="product-checkbox w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                        </td>
                        <td class="px-6 py-4">
                            <div class="relative w-12 h-12">
                                @if($product->images && $product->images->count() > 0)
                                <img src="{{ asset('storage/' . $product->images->first()->path) }}"
                                    class="w-full h-full object-cover rounded-lg border border-slate-200 cursor-pointer hover:ring-2 hover:ring-blue-400 transition-all"
                                    onclick="window.location.href='{{ route('buyer.product.show', $product) }}'">
                                @if($product->images->count() > 1)
                                <span class="absolute -bottom-1 -right-1 bg-slate-800 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full ring-2 ring-white">
                                    +{{ $product->images->count() - 1 }}
                                </span>
                                @endif
                                @else
                                <div class="w-full h-full bg-slate-100 rounded-lg flex items-center justify-center text-slate-400 border border-slate-200 border-dashed">
                                    <i class="bi bi-image"></i>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-slate-100 text-slate-700 font-mono text-xs px-2 py-1 rounded-md">
                                {{ $product->product_code }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-slate-800">{{ $product->product_name }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ $product->category->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ $product->subcategory->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-[10px] font-bold rounded-md bg-blue-50 text-blue-600 uppercase">
                                {{ $product->type }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($product->images && $product->images->count() > 0)
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-cyan-50 text-cyan-700">
                                <i class="bi bi-files mr-1"></i> {{ $product->images->count() }}
                            </span>
                            @else
                            <span class="text-xs text-slate-400 italic">No files</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('buyer.product.show', $product) }}" class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('buyer.product.edit', $product) }}" class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('buyer.product.destroy', $product) }}" method="POST" class="m-0">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" onclick="return confirm('Are you sure you want to delete this product?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <i class="bi bi-inbox text-5xl text-slate-200 mb-4"></i>
                                <p class="text-slate-500 font-medium">No products found matching your search.</p>
                                <a href="{{ route('buyer.product.index') }}" class="text-blue-600 text-sm font-bold mt-2">Clear all filters</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
            {{ $products->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<form id="print_selected_form" action="{{ route('buyer.product.print-selected') }}" method="POST" target="_blank" class="hidden">
    @csrf
    <div id="selected_ids_container"></div>
</form>

<script>
    function filterBuyerSubcategories() {
        const categorySelect = document.getElementById('buyer_category_filter');
        const subcategorySelect = document.getElementById('buyer_subcategory_filter');
        const defaultOption = document.getElementById('subcat_default_option');
        if (!categorySelect || !subcategorySelect) return;

        const categoryId = categorySelect.value;
        const options = subcategorySelect.querySelectorAll('option[data-category]');

        if (!categoryId) {
            // No category selected -> Disable subcategory dropdown and hide all subcategories
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

        // Category is selected -> Enable dropdown and only display matching subcategories
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
        // Run category/subcategory dependency on initial load
        filterBuyerSubcategories();

        const selectAll = document.getElementById('select_all');
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
                    if (selectAll) selectAll.checked = false;
                } else if (document.querySelectorAll('.product-checkbox:checked').length === checkboxes.length) {
                    if (selectAll) selectAll.checked = true;
                }
            });
        });
    });

    function printSelected() {
        const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select at least one product.');
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

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection