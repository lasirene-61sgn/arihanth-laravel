@extends('admin.layouts.app')

@section('title', 'Product Management')

@section('content')
<div class="p-4 sm:p-6 space-y-6">
    <!-- Header/Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Product Management</h1>
            <p class="text-sm text-gray-500 mt-1">Manage and monitor your product inventory</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <button onclick="exportSelectedProducts()" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="bi bi-file-earmark-excel"></i>
                <span>Export Excel</span>
            </button>
            <button onclick="printSelectedProducts()" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-lg border border-gray-300 transition-colors shadow-sm">
                <i class="bi bi-printer"></i>
                <span>Print</span>
            </button>
            <button onclick="document.getElementById('categoriesModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="bi bi-diagram-3"></i>
                <span>View Categories</span>
            </button>
            <a href="{{ route('admin.product.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-magenta-800 hover:bg-magenta-900 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="bi bi-plus-lg"></i>
                <span>Add New Product</span>
            </a>
        </div>
    </div>
    <div class="card shadow-sm mb-4 border-info">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Admin Bulk Upload (ZIP)</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.product.bulk-upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row align-items-center">
                <div class="col-md-9">
                    <input type="file" name="zip_file" class="form-control" accept=".zip" required>
                    <small class="text-muted">Upload a ZIP containing your CSV/Excel and Jewelry images.</small>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-info w-100 text-white">Upload Batch</button>
                </div>
            </div>
        </form>
    </div>
</div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Search -->
                <form action="{{ route('admin.product.index') }}" method="GET" class="relative group min-w-[300px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Search products..." 
                        class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 transition-all text-sm">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-magenta-500 transition-colors"></i>
                </form>

                <!-- Advanced Filter Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm font-medium rounded-lg border border-gray-200 transition-colors">
                        <i class="bi bi-funnel"></i>
                        <span>Advanced Filters</span>
                        <i class="bi bi-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="open" @click.away="open = false" 
                        class="absolute left-0 mt-2 w-[400px] bg-white rounded-xl shadow-xl border border-gray-100 z-50 p-5"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        style="display: none;">
                        
                        <form action="{{ route('admin.product.index') }}" method="GET" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Product Name</label>
                                    <input type="text" name="filter_name" value="{{ request('filter_name') }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-magenta-500">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Product Code</label>
                                    <input type="text" name="filter_code" value="{{ request('filter_code') }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-magenta-500">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Category</label>
                                    <select name="filter_category" id="category_filter" onchange="filterSubcategories()" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-magenta-500">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ request('filter_category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Subcategory</label>
                                    <select name="filter_subcategory" id="filter_subcategory" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-magenta-500">
                                        <option value="">All Sub Categories</option>
                                        @foreach($subCategories as $sub)
                                            <option value="{{ $sub->id }}" data-category="{{ $sub->product_category_id }}" {{ request('filter_subcategory') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-2 space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">BP Code (Buyer)</label>
                                    <select name="filter_bp_code" id="filter_bp_code" onchange="handleCreatorChange('buyer')" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-magenta-500">
                                        <option value="">All Buyers</option>
                                        @foreach($buyers as $buyer)
                                            <option value="{{ $buyer->bp_code }}" {{ request('filter_bp_code') == $buyer->bp_code ? 'selected' : '' }}>{{ $buyer->bp_code }} - {{ $buyer->business_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-2 space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Craftsman Code</label>
                                    <select name="filter_craftsman" id="filter_craftsman" onchange="handleCreatorChange('craftsman')" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-magenta-500">
                                        <option value="">All Craftsmen</option>
                                        @foreach($craftsmen as $craftsman)
                                            <option value="{{ $craftsman->craftman_code }}" {{ request('filter_craftsman') == $craftsman->craftman_code ? 'selected' : '' }}>{{ $craftsman->craftman_code }} - {{ $craftsman->business_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                                <button type="submit" class="flex-1 px-4 py-2 bg-magenta-800 hover:bg-magenta-900 text-white text-sm font-bold rounded-lg transition-colors">
                                    Apply Filters
                                </button>
                                <a href="{{ route('admin.product.index') }}" class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-lg text-center transition-colors">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sort Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm font-medium rounded-lg border border-gray-200 transition-colors">
                        <i class="bi bi-sort-down"></i>
                        <span>Sort</span>
                    </button>

                    <div x-show="open" @click.away="open = false" 
                        class="absolute right-0 lg:left-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden"
                        x-transition:enter="transition ease-out duration-200"
                        style="display: none;">
                        <div class="flex flex-col">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-magenta-50 hover:text-magenta-800 {{ request('sort') == 'latest' ? 'bg-magenta-50 text-magenta-800 font-bold' : '' }}">Latest First</a>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'name_asc']) }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-magenta-50 hover:text-magenta-800 {{ request('sort') == 'name_asc' ? 'bg-magenta-50 text-magenta-800 font-bold' : '' }}">Name (A-Z)</a>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'name_desc']) }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-magenta-50 hover:text-magenta-800 {{ request('sort') == 'name_desc' ? 'bg-magenta-50 text-magenta-800 font-bold' : '' }}">Name (Z-A)</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-magenta-100 text-magenta-800">
                    {{ $products->total() }} total products
                </span>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider w-10 text-center">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this.checked)" 
                                class="rounded border-gray-300 text-magenta-600 focus:ring-magenta-500 h-4 w-4">
                        </th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Image</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Product Info</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Type</th>
                        <!-- <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Created By</th> -->
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50 transition-colors group">
                        <td class="px-6 py-4 text-center">
                            <input type="checkbox" class="product-checkbox rounded border-gray-300 text-magenta-600 focus:ring-magenta-500 h-4 w-4" value="{{ $product->id }}">
                        </td>
                        <td class="px-6 py-4">
                            <div class="relative w-16 h-16 shrink-0 group/img">
                                @if($product->images->count() > 0)
                                    <img src="{{ asset('storage/' . $product->images->first()->path) }}" 
                                         alt="Product" 
                                         class="w-full h-full object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                                         onclick="window.openUniversalPreview('{{ asset('storage/' . $product->images->first()->path) }}')">
                                    @if($product->images->count() > 1)
                                        <span class="absolute -top-2 -right-2 bg-gray-900 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white">
                                            +{{ $product->images->count() - 1 }}
                                        </span>
                                    @endif
                                @else
                                    <div class="w-full h-full bg-gray-100 rounded-lg flex items-center justify-content-center text-gray-400">
                                        <i class="bi bi-image text-xl"></i>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-magenta-800 mb-0.5">{{ $product->product_code }}</span>
                                <span class="text-sm font-semibold text-gray-900 line-clamp-1">{{ $product->product_name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 space-y-1">
                            <div class="text-sm font-medium text-gray-900">{{ $product->category->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $product->subcategory->name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex px-2 py-1 text-[10px] font-bold bg-gray-100 text-gray-700 rounded border border-gray-200 uppercase tracking-wider">
                                {{ $product->type }}
                            </span>
                        </td>
                        <!-- <td class="px-6 py-4">
                            <div class="flex flex-col">
                                @if($product->creator instanceof \App\Models\KeyUser)
                                    <span class="text-xs font-bold text-indigo-600">Key User: {{ $product->creator->full_name }}</span>
                                    <span class="text-[10px] text-gray-500 uppercase tracking-tighter">BP: {{ $product->bp_code }}</span>
                                @elseif($product->creator instanceof \App\Models\Admin)
                                    <span class="text-xs font-bold text-emerald-600">Admin: {{ $product->creator->name }}</span>
                                @else
                                    <span class="text-xs text-gray-400 italic font-medium">System</span>
                                @endif
                            </div>
                        </td> -->
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.product.show', $product) }}" 
                                    class="p-2 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg transition-all shadow-sm group/btn" 
                                    title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.product.edit', ['product' => $product->id, 'return_url' => url()->full()]) }}" 
                                    class="p-2 bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white rounded-lg transition-all shadow-sm group/btn" 
                                    title="Edit Product">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.product.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Silahkan konfirmasi untuk menghapus data ini?');">
                                    @csrf @method('DELETE')
                                    <button class="p-2 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-lg transition-all shadow-sm group/btn" 
                                        title="Delete Product">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center space-y-3">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-300">
                                    <i class="bi bi-box text-3xl"></i>
                                </div>
                                <div class="text-gray-500 font-medium">No products found matching your search.</div>
                                <a href="{{ route('admin.product.index') }}" class="text-sm text-magenta-700 hover:underline font-bold">Clear all filters</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Categories Modal -->
<div id="categoriesModal" class="hidden fixed inset-0 bg-gray-900/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm transition-all" onclick="if(event.target === this) this.classList.add('hidden')">
    <div class="bg-white w-full max-w-3xl rounded-2xl shadow-xl overflow-hidden flex flex-col max-h-[85vh]">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2 m-0">
                <i class="bi bi-diagram-3 text-purple-600"></i> Product Categories Structure
            </h3>
            <button type="button" onclick="document.getElementById('categoriesModal').classList.add('hidden')" class="text-gray-400 hover:text-red-500 transition-colors rounded-lg p-1 hover:bg-red-50 border-0 bg-transparent">
                <i class="bi bi-x-lg text-lg"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto flex-1 bg-gray-50/30">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($all_categories ?? collect() as $category)
                    <div class="border border-gray-200 rounded-xl p-4 bg-white hover:border-purple-300 hover:shadow-md transition-all group">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-colors">
                                <i class="bi bi-folder-fill text-lg"></i>
                            </div>
                            <h4 class="font-bold text-gray-900 m-0">
                                {{ $category->name }}
                                <span class="text-xs font-normal text-gray-500">({{ $category->products_count }})</span>
                            </h4>
                        </div>
                        @if($category->subcategories->count() > 0)
                            <div class="ml-5 pl-5 border-l-2 border-gray-100 space-y-2">
                                @foreach($category->subcategories as $sub)
                                    <div class="flex items-center gap-2 text-sm text-gray-600 hover:text-purple-700 transition-colors">
                                        <i class="bi bi-arrow-return-right text-gray-300"></i>
                                        <span class="font-medium">{{ $sub->name }}</span>
                                        <span class="text-xs text-gray-400">({{ $sub->products_count }})</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="ml-5 pl-5 border-l-2 border-gray-100">
                                <span class="text-xs text-gray-400 italic bg-gray-50 px-2 py-1 rounded">No subcategories</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <div class="p-4 border-t border-gray-100 bg-gray-50/50 flex justify-end">
            <button type="button" onclick="document.getElementById('categoriesModal').classList.add('hidden')" class="px-5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-lg transition-colors shadow-sm">
                Close
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleSelectAll(checked) {
        document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = checked);
    }

    function printSelectedProducts() {
        const selected = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one product to print.');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('admin.product.print-selected') }}";
        form.target = '_blank';

        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_products[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    function exportSelectedProducts() {
        const selected = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(cb => cb.value);
        let url = "{{ route('admin.product.export', request()->query()) }}";
        
        if (selected.length > 0) {
            url += (url.includes('?') ? '&' : '?') + 'selected_ids=' + selected.join(',');
        }
        
        window.location.href = url;
    }

    function filterSubcategories() {
        const categoryId = document.getElementById('category_filter').value;
        const subcategorySelect = document.getElementById('filter_subcategory');
        if (!subcategorySelect) return;
        
        const options = subcategorySelect.querySelectorAll('option[data-category]');
        let selectedOptionHidden = false;

        options.forEach(option => {
            if (!categoryId || option.getAttribute('data-category') === categoryId) {
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

    function handleCreatorChange(type) {
        const buyerSelect = document.getElementById('filter_bp_code');
        const craftsmanSelect = document.getElementById('filter_craftsman');
        
        if (!buyerSelect || !craftsmanSelect) return;

        if (type === 'buyer' && buyerSelect.value) {
            craftsmanSelect.value = '';
        } else if (type === 'craftsman' && craftsmanSelect.value) {
            buyerSelect.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        filterSubcategories();
    });
</script>
@endsection
