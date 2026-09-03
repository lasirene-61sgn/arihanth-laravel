@extends('craftsman_staff.layouts.app')
@section('title', 'My Products')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-indigo-200 pb-4">
        <h4 class="text-2xl font-bold text-indigo-900">My Products</h4>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('craftsman_staff.product.export', request()->query()) }}"
                class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                <i class="bi bi-file-earmark-excel me-2"></i> Export
            </a>

            <div class="dropdown">
                <button class="inline-flex items-center px-3 py-2 bg-white border border-indigo-200 text-indigo-700 text-sm font-semibold rounded-lg hover:bg-indigo-50 transition shadow-sm dropdown-toggle"
                    type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="bi bi-funnel me-2"></i> Advanced Filters
                </button>
                <div class="dropdown-menu p-6 shadow-2xl border-0 rounded-xl" style="min-width: 380px;">
                    <form action="{{ route('craftsman_staff.product.index') }}" method="GET" class="space-y-4">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-indigo-700 uppercase mb-1">Product Code</label>
                                <input type="text" name="filter_product_code" class="w-full px-3 py-2 border border-indigo-100 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 outline-none" value="{{ request('filter_product_code') }}" placeholder="Ex: PRD001">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-indigo-700 uppercase mb-1">Product Name</label>
                                <input type="text" name="filter_product_name" class="w-full px-3 py-2 border border-indigo-100 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 outline-none" value="{{ request('filter_product_name') }}" placeholder="Enter name...">
                            </div>

                            {{-- Cascading Category & Subcategory --}}
                            <div>
                                <label class="block text-xs font-bold text-indigo-700 uppercase mb-1">Category</label>
                                <select name="product_category_id" id="craftsman_category_select" class="w-full px-3 py-2 border border-indigo-100 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('product_category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Subcategory: Visible only when selected category contains subcategories --}}
                            <div id="craftsman_subcategory_wrapper" style="display: none;">
                                <label class="block text-xs font-bold text-indigo-700 uppercase mb-1">Subcategory</label>
                                <select name="subcategory_id" id="craftsman_subcategory_select" class="w-full px-3 py-2 border border-indigo-100 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
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
                            <a href="{{ route('craftsman_staff.product.index') }}" class="flex-1 text-center border border-indigo-200 py-2 rounded-lg text-sm font-bold text-indigo-700 hover:bg-indigo-50 transition">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <button id="print_selected_btn" onclick="printSelected()" class="hidden inline-flex items-center px-3 py-2 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 transition shadow-sm">
                <i class="bi bi-printer me-2"></i> Print Selected
            </button>
            
            @php $staffUser = auth()->guard('craftsman_staff')->user(); @endphp
            @if(!$staffUser || $staffUser->hasPermission('product_create'))
            <a href="{{ route('craftsman_staff.product.create') }}"
                class="inline-flex items-center px-3 py-2 bg-indigo-900 text-white text-sm font-semibold rounded-lg hover:bg-black transition shadow-sm">
                <i class="bi bi-plus-lg me-2"></i> Create New
            </a>
            @endif
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-indigo-100 flex flex-wrap items-center justify-between gap-4">
        <form action="{{ route('craftsman_staff.product.index') }}" method="GET" class="relative w-full md:w-80">
            <input type="text" name="search" placeholder="Search products..."
                class="w-full pl-10 pr-4 py-2 border border-indigo-100 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                value="{{ request('search') }}">
            <div class="absolute left-3 top-2.5 text-indigo-400">
                <i class="bi bi-search"></i>
            </div>
        </form>

        <div class="dropdown">
            <button class="inline-flex items-center px-4 py-2 bg-white border border-indigo-200 text-indigo-700 text-sm font-medium rounded-lg hover:bg-indigo-50 transition dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-sort-down me-2"></i> Sort By
            </button>
            <div class="dropdown-menu shadow-xl border-0 rounded-lg overflow-hidden">
                <a class="dropdown-item py-2 hover:bg-indigo-50" href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}">Newest First</a>
                <a class="dropdown-item py-2 hover:bg-indigo-50" href="{{ request()->fullUrlWithQuery(['sort' => 'name_asc']) }}">Name (A-Z)</a>
                <a class="dropdown-item py-2 hover:bg-indigo-50" href="{{ request()->fullUrlWithQuery(['sort' => 'name_desc']) }}">Name (Z-A)</a>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-hammer"></i> Craftsman Bulk Upload (ZIP)</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('craftsman_staff.product.bulk-upload') }}" method="POST" enctype="multipart/form-data">
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

    <div class="bg-white rounded-xl shadow-sm border border-indigo-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-indigo-50 text-indigo-800 uppercase text-xs font-bold">
                    <tr>
                        <th class="px-4 py-4 text-center">
                            <input type="checkbox" id="select_all" class="w-4 h-4 text-indigo-600 border-indigo-300 rounded focus:ring-indigo-500">
                        </th>
                        <th class="px-6 py-4">Image</th>
                        <th class="px-6 py-4">Code</th>
                        <th class="px-6 py-4">Product Name</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Weight Range</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-indigo-50">
                    @forelse($products as $product)
                    <tr class="hover:bg-indigo-50/50 transition">
                        <td class="px-4 py-4 text-center">
                            <input type="checkbox" name="selected_products[]" value="{{ $product->id }}" class="product-checkbox w-4 h-4 text-indigo-600 border-indigo-300 rounded focus:ring-indigo-500">
                        </td>
                        <td class="px-6 py-4">
                            <div class="relative w-12 h-12">
                                @if($product->images->count() > 0)
                                <img src="{{ asset('storage/' . $product->images->first()->path) }}"
                                    alt="Product"
                                    class="w-full h-full object-cover rounded-lg border border-indigo-100 cursor-pointer hover:opacity-80 transition"
                                    onclick="window.location.href='{{ route('craftsman_staff.product.show', $product) }}'">
                                @if($product->images->count() > 1)
                                <span class="absolute -bottom-1 -right-1 bg-indigo-900 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ring-2 ring-white">
                                    +{{ $product->images->count() - 1 }}
                                </span>
                                @endif
                                @else
                                <div class="w-full h-full bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-300 border border-indigo-100">
                                    <i class="bi bi-image"></i>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold text-indigo-900 bg-indigo-50 px-2 py-1 rounded">
                                {{ $product->product_code }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-indigo-900">{{ $product->product_name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-indigo-900">{{ $product->category->name ?? 'N/A' }}</div>
                            <div class="text-xs text-indigo-500">{{ $product->subcategory->name ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-indigo-700">
                                {{ $product->weight_from }}g - {{ $product->weight_to }}g
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                @php $staffUser = auth()->guard('craftsman_staff')->user(); @endphp
                                @if(!$staffUser || $staffUser->hasPermission('product_view') || $staffUser->hasPermission('product_create') || $staffUser->hasPermission('product_edit'))
                                <a href="{{ route('craftsman_staff.product.show', $product) }}"
                                    class="p-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @endif
                                @if(!$staffUser || $staffUser->hasPermission('product_edit'))
                                <a href="{{ route('craftsman_staff.product.edit', $product) }}"
                                    class="p-2 rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-600 hover:text-white transition" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('craftsman_staff.product.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Delete this product?')">
                                    @csrf @method('DELETE')
                                    <button class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-indigo-400 italic">
                            No products found matching your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-indigo-50 border-t border-indigo-100">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-sm text-indigo-700">
                    Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} results
                </div>
                <div class="tailwind-pagination">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<form id="print_selected_form" action="{{ route('craftsman_staff.product.print-selected') }}" method="POST" target="_blank" class="hidden">
    @csrf
    <div id="selected_ids_container"></div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cascading Category & Subcategory Logic
        const categorySelect = document.getElementById('craftsman_category_select');
        const subcategorySelect = document.getElementById('craftsman_subcategory_select');
        const subcategoryWrapper = document.getElementById('craftsman_subcategory_wrapper');
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
            syncSubcategories(); // Evaluate on page load
        }

        // Checkbox & Print Handling
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