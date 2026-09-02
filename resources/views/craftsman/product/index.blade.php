@extends('craftsman.layouts.app')
@section('title', 'My Products')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-emerald-200 pb-4">
        <h4 class="text-2xl font-bold text-emerald-900">My Products</h4>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('craftsman.product.export', request()->query()) }}"
                class="inline-flex items-center px-3 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition shadow-sm">
                <i class="bi bi-file-earmark-excel me-2"></i> Export
            </a>

            <div class="dropdown">
                <button class="inline-flex items-center px-3 py-2 bg-white border border-emerald-200 text-emerald-700 text-sm font-semibold rounded-lg hover:bg-emerald-50 transition shadow-sm dropdown-toggle"
                    type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="bi bi-funnel me-2"></i> Advanced Filters
                </button>
                <div class="dropdown-menu p-6 shadow-2xl border-0 rounded-xl" style="min-width: 350px;">
                    <form action="{{ route('craftsman.product.index') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-emerald-700 uppercase mb-1">Product Code</label>
                                <input type="text" name="filter_product_code" class="w-full px-3 py-2 border border-emerald-100 rounded-md text-sm focus:ring-2 focus:ring-emerald-500 outline-none" value="{{ request('filter_product_code') }}" placeholder="Ex: PRD-001">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-emerald-700 uppercase mb-1">Product Name</label>
                                <input type="text" name="filter_product_name" class="w-full px-3 py-2 border border-emerald-100 rounded-md text-sm focus:ring-2 focus:ring-emerald-500 outline-none" value="{{ request('filter_product_name') }}" placeholder="Ex: Gold Ring">
                            </div>
                            
                            <!-- Dynamic Category Dropdown -->
                            <div>
                                <label class="block text-xs font-bold text-emerald-700 uppercase mb-1">Category</label>
                                <select name="filter_category" id="craftsman_category_filter" onchange="filterCraftsmanSubcategories()" class="w-full px-3 py-2 border border-emerald-100 rounded-md text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('filter_category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Dynamic Subcategory Dropdown -->
                            <div>
                                <label class="block text-xs font-bold text-emerald-700 uppercase mb-1">Subcategory</label>
                                <select name="filter_subcategory" id="craftsman_subcategory_filter" class="w-full px-3 py-2 border border-emerald-100 rounded-md text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                                    <option value="" id="craftsman_subcat_default_option">Select Category First</option>
                                    @foreach($subcategories as $sub)
                                    <option value="{{ $sub->id }}" data-category="{{ $sub->product_category_id }}" {{ request('filter_subcategory') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-2 pt-4 border-t border-emerald-50">
                            <button type="submit" class="flex-1 bg-emerald-900 text-white py-2 rounded-lg text-sm font-bold hover:bg-emerald-800 transition">Apply</button>
                            <a href="{{ route('craftsman.product.index') }}" class="flex-1 text-center border border-emerald-200 py-2 rounded-lg text-sm font-bold text-emerald-700 hover:bg-emerald-50 transition">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <button id="print_selected_btn" onclick="printSelected()" class="hidden inline-flex items-center px-3 py-2 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 transition shadow-sm">
                <i class="bi bi-printer me-2"></i> Print Selected
            </button>
            <a href="{{ route('craftsman.product.create') }}"
                class="inline-flex items-center px-3 py-2 bg-emerald-900 text-white text-sm font-semibold rounded-lg hover:bg-black transition shadow-sm">
                <i class="bi bi-plus-lg me-2"></i> Create New
            </a>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100 flex flex-wrap items-center justify-between gap-4">
        <form action="{{ route('craftsman.product.index') }}" method="GET" class="relative w-full md:w-80">
            <input type="text" name="search" placeholder="Search products..."
                class="w-full pl-10 pr-4 py-2 border border-emerald-100 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none"
                value="{{ request('search') }}">
            <div class="absolute left-3 top-2.5 text-emerald-400">
                <i class="bi bi-search"></i>
            </div>
        </form>

        <div class="dropdown">
            <button class="inline-flex items-center px-4 py-2 bg-white border border-emerald-200 text-emerald-700 text-sm font-medium rounded-lg hover:bg-emerald-50 transition dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-sort-down me-2"></i> Sort By
            </button>
            <div class="dropdown-menu shadow-xl border-0 rounded-lg overflow-hidden">
                <a class="dropdown-item py-2 hover:bg-emerald-50" href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}">Newest First</a>
                <a class="dropdown-item py-2 hover:bg-emerald-50" href="{{ request()->fullUrlWithQuery(['sort' => 'name_asc']) }}">Name (A-Z)</a>
                <a class="dropdown-item py-2 hover:bg-emerald-50" href="{{ request()->fullUrlWithQuery(['sort' => 'name_desc']) }}">Name (Z-A)</a>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-hammer"></i> Craftsman Bulk Upload (ZIP)</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('craftsman.product.bulk-upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <label class="form-label font-weight-bold">Select ZIP File</label>
                        <input type="file" name="zip_file" class="form-control" accept=".zip" required>
                        <div class="form-text mt-2">
                            Upload a ZIP containing your <b>products.xlsx</b> and all related <b>images</b>.
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-dark w-100 mt-3">
                            <i class="fas fa-upload"></i> Upload Batch
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-emerald-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-emerald-50 text-emerald-800 uppercase text-xs font-bold">
                    <tr>
                        <th class="px-4 py-4 text-center">
                            <input type="checkbox" id="select_all" class="w-4 h-4 text-emerald-600 border-emerald-300 rounded focus:ring-emerald-500">
                        </th>
                        <th class="px-6 py-4">Image</th>
                        <th class="px-6 py-4">Code</th>
                        <th class="px-6 py-4">Product Name</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Weight Range</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-emerald-50">
                    @forelse($products as $product)
                    <tr class="hover:bg-emerald-50/50 transition">
                        <td class="px-4 py-4 text-center">
                            <input type="checkbox" name="selected_products[]" value="{{ $product->id }}" class="product-checkbox w-4 h-4 text-emerald-600 border-emerald-300 rounded focus:ring-emerald-500">
                        </td>
                        <td class="px-6 py-4">
                            <div class="relative w-12 h-12">
                                @if($product->images->count() > 0)
                                <img src="{{ asset('storage/' . $product->images->first()->path) }}"
                                    alt="Product"
                                    class="w-full h-full object-cover rounded-lg border border-emerald-100 cursor-pointer hover:opacity-80 transition"
                                    onclick="window.location.href='{{ route('craftsman.product.show', $product) }}'">
                                @if($product->images->count() > 1)
                                <span class="absolute -bottom-1 -right-1 bg-emerald-900 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ring-2 ring-white">
                                    +{{ $product->images->count() - 1 }}
                                </span>
                                @endif
                                @else
                                <div class="w-full h-full bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-300 border border-emerald-100">
                                    <i class="bi bi-image"></i>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold text-emerald-900 bg-emerald-50 px-2 py-1 rounded">
                                {{ $product->product_code }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-emerald-900">{{ $product->product_name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-emerald-900">{{ $product->category->name ?? 'N/A' }}</div>
                            <div class="text-xs text-emerald-500">{{ $product->subcategory->name ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-emerald-700">
                                {{ $product->weight_from }}g - {{ $product->weight_to }}g
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('craftsman.product.show', $product) }}"
                                    class="p-2 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('craftsman.product.edit', $product) }}"
                                    class="p-2 rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-600 hover:text-white transition" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('craftsman.product.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Delete this product?')">
                                    @csrf @method('DELETE')
                                    <button class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-emerald-400 italic">
                            No products found matching your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-emerald-50 border-t border-emerald-100">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-sm text-emerald-700">
                    Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} results
                </div>
                <div class="tailwind-pagination">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<form id="print_selected_form" action="{{ route('craftsman.product.print-selected') }}" method="POST" target="_blank" class="hidden">
    @csrf
    <div id="selected_ids_container"></div>
</form>

<script>
    function filterCraftsmanSubcategories() {
        const categorySelect = document.getElementById('craftsman_category_filter');
        const subcategorySelect = document.getElementById('craftsman_subcategory_filter');
        const defaultOption = document.getElementById('craftsman_subcat_default_option');
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

        // Enable subcategory and display only matching options
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
        filterCraftsmanSubcategories();

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
@endsection