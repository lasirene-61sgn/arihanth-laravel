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
            <!-- <button onclick="window.print()" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-cyan-600 rounded-lg hover:bg-cyan-700 transition-colors shadow-sm">
                <i class="bi bi-printer mr-2"></i> Print Page
            </button> -->
            <a href="{{ route('key-user.product.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                <i class="bi bi-plus-circle mr-2"></i> Add Product
            </a>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center gap-4">

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
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm">
                        <i class="bi bi-funnel mr-2"></i> Advanced Filter
                    </button>

                    <div x-show="open" @click.away="open = false" x-cloak
                        class="absolute right-0 mt-2 w-80 md:w-96 bg-white rounded-xl shadow-xl border border-gray-200 z-50 p-6">
                        <form action="{{ route('key-user.product.index') }}" method="GET" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Product Code</label>
                                    <input type="text" name="filter_code" class="w-full p-2 border border-gray-300 rounded-md text-sm" value="{{ request('filter_code') }}" placeholder="Ex: P001">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Product Name</label>
                                    <input type="text" name="filter_name" class="w-full p-2 border border-gray-300 rounded-md text-sm" value="{{ request('filter_name') }}" placeholder="Ex: Cotton Fabric">
                                </div>
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

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Status</label>
                                <select name="status" class="w-full p-2 border border-gray-300 rounded-md text-sm">
                                    <option value="">All Status</option>
                                    <option value="Open" {{ request('status') == 'Open' ? 'selected' : '' }}>Open</option>
                                    <option value="Closed" {{ request('status') == 'Closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>

                            <div class="flex gap-2 pt-2 border-t">
                                <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">Apply</button>
                                <a href="{{ route('key-user.product.index') }}" class="flex-1 bg-gray-100 text-gray-700 text-center py-2 rounded-lg text-sm font-semibold hover:bg-gray-200 transition border">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm">
                        <i class="bi bi-sort-down mr-2"></i> Sort
                    </button>

                    <div x-show="open" @click.away="open = false" x-cloak
                        class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-200 z-50 p-4">
                        <form action="{{ route('key-user.product.index') }}" method="GET" class="space-y-3">
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
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
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
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="relative inline-block w-10 h-5 transition duration-200 ease-in-out bg-gray-200 rounded-full">
                                    <div class="absolute left-0 w-5 h-5 transition duration-200 ease-in-out transform bg-white border border-gray-300 rounded-full {{ $product->open_close == 'Open' ? 'translate-x-full border-indigo-600 bg-indigo-600' : '' }}"></div>
                                    <input type="checkbox" class="absolute inset-0 w-full h-full opacity-0 cursor-not-allowed" {{ $product->open_close == 'Open' ? 'checked' : '' }} disabled>
                                </div>
                            </div>
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
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500 bg-gray-50">
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
                    {{ $products->appends(request()->query())->links() }}
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
    /* Printing styles */
    @media print {
        .no-print {
            display: none !important;
        }

        header,
        aside,
        footer {
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

        .shadow-sm {
            box-shadow: none !important;
        }
    }

    /* Ensure Laravel pagination matches Tailwind if using default views */
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