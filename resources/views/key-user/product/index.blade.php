@extends('key-user.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Product Management</h1>
            <p class="text-sm text-gray-500">Manage and monitor your product inventory</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('key-user.product.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors shadow-sm">
                <i class="bi bi-file-earmark-excel mr-2"></i> Export
            </a>
            <button id="print_selected_btn" onclick="printSelected()" class="hidden inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                <i class="bi bi-printer mr-2"></i> Print Selected
            </button>
            <a href="{{ route('key-user.product.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                <i class="bi bi-plus-circle mr-2"></i> Add Product
            </a>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center gap-4">

            {{-- Quick Search --}}
            <form action="{{ route('key-user.product.index') }}" method="GET" class="relative flex-1 max-w-md">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all text-sm"
                    placeholder="Search products...">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="bi bi-search text-gray-400"></i>
                </div>
                <button type="submit" class="hidden">Search</button>
            </form>

            <div class="flex flex-wrap items-center gap-3 lg:ml-auto">
                {{-- Advanced Filter Dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm">
                        <i class="bi bi-funnel mr-2"></i> Advanced Filter
                    </button>

                    <div x-show="open" @click.away="open = false" x-cloak
                        class="absolute right-0 mt-2 w-80 md:w-96 bg-white rounded-xl shadow-xl border border-gray-200 z-50 p-6">
                        <form action="{{ route('key-user.product.index') }}" method="GET" class="space-y-4">
                            
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Product Code</label>
                                    <input type="text" name="filter_code" class="w-full p-2 border border-gray-300 rounded-md text-sm outline-none focus:ring-1 focus:ring-indigo-500" value="{{ request('filter_code') }}" placeholder="Ex: P001">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Product Name</label>
                                    <input type="text" name="filter_name" class="w-full p-2 border border-gray-300 rounded-md text-sm outline-none focus:ring-1 focus:ring-indigo-500" value="{{ request('filter_name') }}" placeholder="Ex: Ring">
                                </div>
                            </div>

                            {{-- Category & Conditional Subcategory Grid --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Category</label>
                                    <select name="product_category_id" id="filter_category_select" class="w-full p-2 border border-gray-300 rounded-md text-sm outline-none focus:ring-1 focus:ring-indigo-500">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ request('product_category_id') == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Hidden by default; visible only when chosen category contains subcategories --}}
                                <div id="filter_subcategory_wrapper" style="display: none;">
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Subcategory</label>
                                    <select name="subcategory_id" id="filter_subcategory_select" class="w-full p-2 border border-gray-300 rounded-md text-sm outline-none focus:ring-1 focus:ring-indigo-500">
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

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Type</label>
                                <select name="type" class="w-full p-2 border border-gray-300 rounded-md text-sm outline-none focus:ring-1 focus:ring-indigo-500">
                                    <option value="">All Types</option>
                                    <option value="Piece" {{ request('type') == 'Piece' ? 'selected' : '' }}>Piece</option>
                                    <option value="Pair" {{ request('type') == 'Pair' ? 'selected' : '' }}>Pair</option>
                                </select>
                            </div>

                            <div class="flex gap-2 pt-2 border-t">
                                <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">Apply</button>
                                <a href="{{ route('key-user.product.index') }}" class="flex-1 bg-gray-100 text-gray-700 text-center py-2 rounded-lg text-sm font-semibold hover:bg-gray-200 transition border">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Sort Dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm">
                        <i class="bi bi-sort-down mr-2"></i> Sort
                    </button>

                    <div x-show="open" @click.away="open = false" x-cloak
                        class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-200 z-50 p-4">
                        <form action="{{ route('key-user.product.index') }}" method="GET" class="space-y-3">
                            @foreach(request()->except(['sort', 'direction']) as $key => $val)
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endforeach

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Sort By</label>
                                <select name="sort" class="w-full p-2 border border-gray-300 rounded-md text-sm">
                                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Created Date</option>
                                    <option value="product_name" {{ request('sort') == 'product_name' ? 'selected' : '' }}>Product Name</option>
                                    <option value="product_code" {{ request('sort') == 'product_code' ? 'selected' : '' }}>Product Code</option>
                                </select>
                            </div>
                            <div>
                                <select name="direction" class="w-full p-2 border border-gray-300 rounded-md text-sm">
                                    <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>Newest First</option>
                                    <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Oldest First</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-gray-800 text-white py-2 rounded-lg text-sm font-semibold hover:bg-black transition">Apply Sort</button>
                        </form>
                    </div>
                </div>

                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                    {{ $products->total() }} Products Found
                </span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="select_all" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        </th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Image</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            <input type="checkbox" name="selected_products[]" value="{{ $product->id }}" class="product-checkbox w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        </td>
                        <td class="px-6 py-4">
                            <div class="relative h-12 w-12 flex-shrink-0">
                                @if($product->images->count() > 0)
                                <img src="{{ asset('storage/' . $product->images->first()->path) }}"
                                    alt="Product"
                                    class="h-full w-full rounded-lg object-cover border border-gray-200 cursor-pointer hover:opacity-75 transition"
                                    onclick="window.location.href='{{ route('key-user.product.show', $product) }}'">
                                @if($product->images->count() > 1)
                                <span class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-gray-800 text-[10px] font-bold text-white ring-2 ring-white">
                                    +{{ $product->images->count() - 1 }}
                                </span>
                                @endif
                                @else
                                <div class="h-full w-full rounded-lg bg-gray-100 flex items-center justify-center border border-gray-200">
                                    <i class="bi bi-image text-gray-400"></i>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 font-mono text-sm font-bold text-red-600">
                            {{ $product->product_code }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-gray-800">{{ $product->product_name }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $product->type }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $product->created_at->format('d M, Y') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="inline-flex rounded-md shadow-sm" role="group">
                                <a href="{{ route('key-user.product.show', $product) }}" class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-white border border-gray-200 rounded-l-lg hover:bg-blue-50 transition-colors">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('key-user.product.edit', $product) }}" class="px-3 py-1.5 text-xs font-medium text-indigo-600 bg-white border-t border-b border-r border-gray-200 rounded-r-lg hover:bg-indigo-50 transition-colors">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500 bg-gray-50">
                            <i class="bi bi-inbox text-4xl block mb-2 opacity-20"></i>
                            No products found matching your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600">
                    Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} results
                </div>
                <div class="tailwind-pagination">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<form id="print_selected_form" action="{{ route('key-user.product.print-selected') }}" method="POST" target="_blank" class="hidden">
    @csrf
    <div id="selected_ids_container"></div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('filter_category_select');
        const subcategorySelect = document.getElementById('filter_subcategory_select');
        const subcategoryWrapper = document.getElementById('filter_subcategory_wrapper');
        const subcategoryOptions = Array.from(subcategorySelect.querySelectorAll('option[data-parent]'));

        function syncSubcategories() {
            const selectedCatId = categorySelect.value;
            const currentSubcatVal = subcategorySelect.value;

            // If no category is selected, hide the subcategory container completely
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

            // Only display the subcategory block if matching subcategories exist
            subcategoryWrapper.style.display = matchingCount > 0 ? 'block' : 'none';
        }

        if (categorySelect && subcategorySelect && subcategoryWrapper) {
            categorySelect.addEventListener('change', syncSubcategories);
            syncSubcategories(); // Run on initial render
        }

        // Checkbox & Print Logic
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

<style>
    @media print {
        .no-print {
            display: none !important;
        }
        header, aside, footer {
            display: none !important;
        }
        main {
            padding: 0 !important;
            background: white !important;
        }
        .bg-white {
            box-shadow: none !important;
            border: none !important;
        }
    }

    .tailwind-pagination nav svg {
        height: 20px;
        width: 20px;
    }

    .tailwind-pagination nav div p {
        margin-bottom: 0;
    }
</style>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection