@extends('super-admin.layouts.app')

@section('title', 'Product Management')

@section('content')
<div class="tw-max-w-full tw-mx-auto">
    <!-- Top Action Bar -->
    <div class="tw-flex tw-flex-col xl:tw-flex-row tw-justify-between tw-items-center tw-mb-6 tw-gap-4 tw-bg-white dark:tw-bg-gray-800 tw-p-4 tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-100 dark:tw-border-gray-700">
        
        <div class="tw-flex tw-items-center tw-gap-6">
            <div>
                <h1 class="tw-text-base tw-font-black tw-text-gray-800 dark:tw-text-white tw-uppercase tw-tracking-tight tw-mb-0 tw-whitespace-nowrap">
                    Product Management
                </h1>
            </div>
            
            <div class="tw-h-6 tw-w-[1px] tw-bg-gray-200 dark:tw-bg-gray-700 tw-hidden md:tw-block"></div>

            <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2">
                <button class="tw-px-3 tw-py-1.5 tw-rounded-xl tw-bg-gray-50 dark:tw-bg-gray-700 tw-text-gray-600 dark:tw-text-gray-200 tw-text-[10px] tw-font-bold hover:tw-bg-gray-100 dark:hover:tw-bg-gray-600 tw-transition-all" onclick="toggleSection('searchSection')">
                    <i class="bi bi-search tw-mr-1"></i> SEARCH
                </button>
                <button class="tw-px-3 tw-py-1.5 tw-rounded-xl tw-bg-gray-50 dark:tw-bg-gray-700 tw-text-gray-600 dark:tw-text-gray-200 tw-text-[10px] tw-font-bold hover:tw-bg-gray-100 dark:hover:tw-bg-gray-600 tw-transition-all" onclick="toggleSection('filterSection')">
                    <i class="bi bi-funnel tw-mr-1"></i> FILTER
                </button>
                <button class="tw-px-3 tw-py-1.5 tw-text-[9px] tw-text-gray-400 hover:tw-text-blue-600 tw-font-black tw-uppercase tw-tracking-widest" onclick="resetAll()">
                    RESET VIEW
                </button>
            </div>
        </div>

        <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2">
            <a href="{{ route('super-admin.product.index', array_merge(request()->all(), ['export' => 'excel'])) }}" 
               class="tw-inline-flex tw-items-center tw-gap-2 tw-bg-emerald-600 hover:tw-bg-emerald-700 tw-text-white tw-px-3 tw-py-1.5 tw-rounded-xl tw-text-[10px] tw-font-bold tw-transition-all tw-no-underline">
                <i class="bi bi-file-earmark-spreadsheet"></i> EXPORT
            </a>
            <button onclick="printSelectedProducts()" 
                    class="tw-inline-flex tw-items-center tw-gap-2 tw-bg-sky-500 hover:tw-bg-sky-600 tw-text-white tw-px-3 tw-py-1.5 tw-rounded-xl tw-text-[10px] tw-font-bold tw-transition-all">
                <i class="bi bi-printer"></i> PRINT
            </button>
            <a href="{{ route('super-admin.product.create') }}" 
               class="tw-inline-flex tw-items-center tw-gap-2 tw-bg-sky-500 hover:tw-bg-sky-600 tw-text-white tw-px-3 tw-py-1.5 tw-rounded-xl tw-text-[10px] tw-font-bold tw-transition-all tw-no-underline">
                <i class="bi bi-plus-lg"></i> ADD PRODUCT
            </a>
            <button onclick="document.getElementById('categoriesModal').classList.remove('tw-hidden')" 
                    class="tw-inline-flex tw-items-center tw-gap-2 tw-bg-purple-600 hover:tw-bg-purple-700 tw-text-white tw-px-3 tw-py-1.5 tw-rounded-xl tw-text-[10px] tw-font-bold tw-transition-all">
                <i class="bi bi-diagram-3"></i> VIEW CATEGORIES
            </button>
        </div>
    </div>

    <!-- Bulk Upload Section -->
    <div class="tw-bg-white dark:tw-bg-gray-800 tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-100 dark:tw-border-gray-700 tw-mb-6 tw-overflow-hidden">
        <div class="tw-p-4">
            <form action="{{ route('super-admin.product.bulk-upload') }}" method="POST" enctype="multipart/form-data" class="tw-flex tw-flex-col lg:tw-flex-row tw-items-center tw-gap-6">
                @csrf
                <div class="tw-flex-1 tw-w-full">
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <div class="tw-flex-1">
                            <input type="file" name="zip_file" class="form-control tw-rounded-xl tw-border-gray-200 dark:tw-border-gray-700 dark:tw-bg-gray-700 dark:tw-text-white tw-text-sm" accept=".zip" required>
                        </div>
                        <button type="submit" class="tw-bg-gray-900 dark:tw-bg-white dark:tw-text-gray-900 tw-text-white tw-px-6 tw-py-2 tw-rounded-xl tw-text-xs tw-font-bold hover:tw-opacity-90 tw-transition-all tw-flex tw-items-center tw-gap-2">
                            <i class="bi bi-cloud-arrow-up"></i> IMPORT
                        </button>
                    </div>
                </div>

                <div class="tw-flex-1 tw-w-full tw-py-2 tw-px-4 tw-bg-blue-50/50 dark:tw-bg-blue-900/10 tw-rounded-xl tw-border tw-border-blue-100 dark:tw-border-blue-900/30">
                    <div class="tw-flex tw-items-center tw-gap-4">
                        <span class="tw-text-[10px] tw-font-black tw-text-blue-700 dark:tw-text-blue-400 tw-uppercase tw-whitespace-nowrap"><i class="bi bi-info-circle-fill tw-mr-1"></i> GUIDELINES:</span>
                        <div class="tw-text-[10px] tw-text-blue-600 dark:tw-text-blue-400 tw-flex tw-flex-wrap tw-gap-x-4 tw-font-medium">
                            <span>1. Folder contains Excel + Images</span>
                            <span>2. Image name = Product Code</span>
                            <span>3. ZIP the folder & upload</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Search Section -->
    <div id="searchSection" class="tw-hidden tw-mb-4 tw-p-4 tw-bg-white dark:tw-bg-gray-800 tw-rounded-xl tw-border tw-border-gray-100 dark:tw-border-gray-700 tw-shadow-sm">
        <form method="GET" action="{{ route('super-admin.product.index') }}" class="tw-flex tw-gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or code..." class="form-control tw-rounded-xl tw-text-sm dark:tw-bg-gray-700 dark:tw-text-white dark:tw-border-gray-600">
            <button type="submit" class="tw-bg-blue-600 hover:tw-bg-blue-700 tw-text-white tw-px-6 tw-rounded-xl tw-text-xs tw-font-bold tw-transition-all">APPLY</button>
        </form>
    </div>

    <!-- Advanced Filter Section -->
    <div id="filterSection" class="tw-hidden tw-mb-6 tw-p-6 tw-bg-white dark:tw-bg-gray-800 tw-rounded-2xl tw-border tw-border-gray-100 dark:tw-border-gray-700 tw-shadow-sm">
        <div class="tw-mb-4 tw-flex tw-justify-between tw-items-center">
            <h3 class="tw-text-sm tw-font-black tw-text-gray-800 dark:tw-text-white tw-uppercase tw-tracking-wider">Advanced Filters</h3>
            <button type="button" onclick="toggleSection('filterSection')" class="tw-text-gray-400 hover:tw-text-gray-600 dark:hover:tw-text-gray-200"><i class="bi bi-x-lg"></i></button>
        </div>

        <form method="GET" action="{{ route('super-admin.product.index') }}" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-4">
            
            <div class="tw-space-y-1">
                <label class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase">Product Name</label>
                <input type="text" name="filter_name" value="{{ request('filter_name') }}" class="form-control tw-rounded-xl tw-text-sm tw-bg-gray-50 dark:tw-bg-gray-700 dark:tw-text-white dark:tw-border-gray-600">
            </div>
            
            <div class="tw-space-y-1">
                <label class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase">Product Code</label>
                <input type="text" name="filter_code" value="{{ request('filter_code') }}" class="form-control tw-rounded-xl tw-text-sm tw-bg-gray-50 dark:tw-bg-gray-700 dark:tw-text-white dark:tw-border-gray-600">
            </div>
            
            <div class="tw-space-y-1">
                <label class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase">Design Code</label>
                <input type="text" name="filter_design_code" value="{{ request('filter_design_code') }}" class="form-control tw-rounded-xl tw-text-sm tw-bg-gray-50 dark:tw-bg-gray-700 dark:tw-text-white dark:tw-border-gray-600">
            </div>

            <div class="tw-space-y-1">
                <label class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase">Category</label>
                <select name="category_filter" id="category_filter" onchange="filterSubcategories()" class="form-select tw-rounded-xl tw-text-sm tw-bg-gray-50 dark:tw-bg-gray-700 dark:tw-text-white dark:tw-border-gray-600">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_filter') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="tw-space-y-1">
                <label class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase">Sub Category</label>
                <select name="filter_subcategory" id="filter_subcategory" class="form-select tw-rounded-xl tw-text-sm tw-bg-gray-50 dark:tw-bg-gray-700 dark:tw-text-white dark:tw-border-gray-600">
                    <option value="">All Sub Categories</option>
                    @foreach($subCategories as $sub)
                        <option value="{{ $sub->id }}" data-category="{{ $sub->product_category_id }}" {{ request('filter_subcategory') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="tw-space-y-1 lg:tw-col-span-2">
                <label class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase">BP Code (Buyer)</label>
                <select name="filter_bp_code" id="filter_bp_code" onchange="handleCreatorChange('buyer')" class="form-select tw-rounded-xl tw-text-sm tw-bg-gray-50 dark:tw-bg-gray-700 dark:tw-text-white dark:tw-border-gray-600">
                    <option value="">All Buyers</option>
                    @foreach($buyers as $buyer)
                        <option value="{{ $buyer->bp_code }}" {{ request('filter_bp_code') == $buyer->bp_code ? 'selected' : '' }}>{{ $buyer->bp_code }} - {{ $buyer->business_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="tw-space-y-1 lg:tw-col-span-2">
                <label class="tw-text-[10px] tw-font-bold tw-text-gray-500 dark:tw-text-gray-400 tw-uppercase">Craftsman Code</label>
                <select name="filter_craftsman" id="filter_craftsman" onchange="handleCreatorChange('craftsman')" class="form-select tw-rounded-xl tw-text-sm tw-bg-gray-50 dark:tw-bg-gray-700 dark:tw-text-white dark:tw-border-gray-600">
                    <option value="">All Craftsmen</option>
                    @foreach($craftsmen as $craftsman)
                        <option value="{{ $craftsman->craftman_code }}" {{ request('filter_craftsman') == $craftsman->craftman_code ? 'selected' : '' }}>{{ $craftsman->craftman_code }} - {{ $craftsman->business_name }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- High visibility action buttons -->
            <div class="tw-col-span-1 md:tw-col-span-2 lg:tw-col-span-4 tw-flex tw-justify-end tw-items-center tw-gap-3 tw-mt-4 tw-pt-4 tw-border-t tw-border-gray-100 dark:tw-border-gray-700">
                <a href="{{ route('super-admin.product.index') }}" class="tw-px-6 tw-py-2.5 tw-rounded-xl tw-bg-gray-200 hover:tw-bg-gray-300 dark:tw-bg-gray-700 dark:hover:tw-bg-gray-600 tw-text-gray-700 dark:tw-text-gray-200 tw-text-xs tw-font-bold tw-no-underline tw-transition-all">
                    RESET
                </a>
                <button type="submit" class="tw-px-6 tw-py-2.5 tw-rounded-xl tw-bg-blue-600 hover:tw-bg-blue-700 tw-text-white tw-text-xs tw-font-bold tw-shadow-md hover:tw-shadow-lg tw-transition-all tw-flex tw-items-center tw-gap-2">
                    <i class="bi bi-funnel-fill"></i> APPLY FILTERS
                </button>
            </div>
        </form>
    </div>

    <!-- Product Table Card -->
    <div class="tw-bg-white dark:tw-bg-gray-800 tw-rounded-2xl tw-shadow-xl tw-border tw-border-gray-100 dark:tw-border-gray-700 tw-overflow-hidden">
        <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-left tw-border-collapse">
                <thead>
                    <tr class="tw-bg-gray-50/50 dark:tw-bg-gray-900/50">
                        <th class="tw-p-4 tw-w-10"><input type="checkbox" class="tw-rounded" id="selectAll" onchange="toggleSelectAll(this.checked)"></th>
                        <th class="tw-p-4 tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase">Image</th>
                        <th class="tw-p-4 tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase">Code</th>
                        <th class="tw-p-4 tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase">Product Name</th>
                        <th class="tw-p-4 tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase">Category</th>
                        <th class="tw-p-4 tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase">Type</th>
                        <th class="tw-p-4 tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase">Weight</th>
                        <th class="tw-p-4 tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase">Created</th>
                        <th class="tw-p-4 tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="tw-divide-y tw-divide-gray-50 dark:tw-divide-gray-700">
                    @forelse($products as $product)
                    <tr class="hover:tw-bg-gray-50/80 dark:hover:tw-bg-gray-900/40 tw-transition-colors">
                        <td class="tw-p-4"><input type="checkbox" class="tw-rounded product-checkbox" value="{{ $product->id }}"></td>
                        <td class="tw-p-4">
                            @if($product->images && $product->images->count())
                                <img src="{{ asset('storage/' . $product->images->first()->path) }}" class="tw-w-10 tw-h-10 tw-object-cover tw-rounded-lg tw-border">
                            @else
                                <div class="tw-w-10 tw-h-10 tw-bg-gray-50 dark:tw-bg-gray-700 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-text-[10px] tw-text-gray-300">NA</div>
                            @endif
                        </td>
                        <td class="tw-p-4 tw-text-xs tw-font-bold tw-text-blue-600 dark:tw-text-blue-400">{{ $product->product_code }}</td>
                        <td class="tw-p-4 tw-text-xs tw-font-medium tw-text-gray-700 dark:tw-text-gray-300">{{ $product->product_name }}</td>
                        <td class="tw-p-4"><span class="tw-px-2 tw-py-0.5 tw-bg-gray-100 dark:tw-bg-gray-700 tw-rounded tw-text-[9px] tw-font-bold">{{ optional($product->category)->name }}</span></td>
                        <td class="tw-p-4 tw-text-[11px] tw-text-gray-500">{{ $product->type }}</td>
                        <td class="tw-p-4 tw-text-[11px] tw-font-mono">{{ $product->weight_from }}g</td>
                        <td class="tw-p-4 tw-text-[10px] tw-text-gray-400">{{ $product->created_at ? $product->created_at->format('d M, y') : '-' }}</td>
                        <td class="tw-p-4">
                            <div class="tw-flex tw-gap-1">
                                <a href="{{ route('super-admin.product.show', $product) }}" class="tw-w-7 tw-h-7 tw-flex tw-items-center tw-justify-center tw-rounded-lg tw-bg-blue-50 tw-text-blue-600 hover:tw-bg-blue-600 hover:tw-text-white tw-transition-all"><i class="bi bi-eye tw-text-xs"></i></a>
                                <a href="{{ route('super-admin.product.edit', ['product' => $product->id, 'return_url' => url()->full()]) }}" class="tw-w-7 tw-h-7 tw-flex tw-items-center tw-justify-center tw-rounded-lg tw-bg-amber-50 tw-text-amber-600 hover:tw-bg-amber-600 hover:tw-text-white tw-transition-all"><i class="bi bi-pencil tw-text-xs"></i></a>
                                <form action="{{ route('super-admin.product.destroy', $product) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');" class="tw-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="tw-w-7 tw-h-7 tw-flex tw-items-center tw-justify-center tw-rounded-lg tw-bg-red-50 tw-text-red-600 hover:tw-bg-red-600 hover:tw-text-white tw-transition-all"><i class="bi bi-trash tw-text-xs"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="tw-p-10 tw-text-center tw-text-gray-400 tw-text-xs">No products found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="tw-p-4 tw-border-t tw-border-gray-50 dark:tw-border-gray-700">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Categories Modal -->
<div id="categoriesModal" class="tw-hidden tw-fixed tw-inset-0 tw-bg-black/50 tw-z-50 tw-flex tw-items-center tw-justify-center tw-p-4" onclick="if(event.target === this) this.classList.add('tw-hidden')">
    <div class="tw-bg-white dark:tw-bg-gray-800 tw-w-full tw-max-w-2xl tw-rounded-2xl tw-shadow-2xl tw-overflow-hidden tw-flex tw-flex-col tw-max-h-[85vh]">
        <div class="tw-p-4 tw-border-b tw-border-gray-100 dark:tw-border-gray-700 tw-flex tw-justify-between tw-items-center tw-bg-gray-50/50 dark:tw-bg-gray-900/50">
            <h3 class="tw-font-black tw-text-gray-800 dark:tw-text-white tw-uppercase tw-tracking-tight tw-mb-0">Product Categories Structure</h3>
            <button onclick="document.getElementById('categoriesModal').classList.add('tw-hidden')" class="tw-text-gray-400 hover:tw-text-red-500 tw-transition-colors">
                <i class="bi bi-x-lg tw-text-lg"></i>
            </button>
        </div>
        <div class="tw-p-6 tw-overflow-y-auto tw-flex-1">
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                @foreach($all_categories ?? collect() as $category)
                    <div class="tw-border tw-border-gray-100 dark:tw-border-gray-700 tw-rounded-xl tw-p-4 tw-bg-white dark:tw-bg-gray-800 hover:tw-border-purple-200 dark:hover:tw-border-purple-800 tw-transition-all">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                            <div class="tw-flex tw-items-center tw-gap-2">
                                <input type="checkbox" class="category-checkbox tw-rounded" value="{{ $category->id }}">
                                <div class="tw-w-8 tw-h-8 tw-rounded-lg tw-bg-purple-50 dark:tw-bg-purple-900/30 tw-text-purple-600 dark:tw-text-purple-400 tw-flex tw-items-center tw-justify-center">
                                    <i class="bi bi-folder-fill"></i>
                                </div>
                                <h4 class="tw-font-bold tw-text-sm tw-text-gray-800 dark:tw-text-gray-200 tw-mb-0">
                                    {{ $category->name }}
                                    <span class="tw-text-xs tw-font-normal tw-text-gray-500">({{ $category->products_count }})</span>
                                </h4>
                            </div>
                            <div class="tw-flex tw-gap-1">
                                <button onclick="editCategory({{ $category->id }}, '{{ $category->name }}')" class="tw-w-6 tw-h-6 tw-flex tw-items-center tw-justify-center tw-rounded tw-bg-gray-100 hover:tw-bg-gray-200 dark:tw-bg-gray-700 dark:hover:tw-bg-gray-600 tw-text-gray-600 dark:tw-text-gray-300"><i class="bi bi-pencil tw-text-xs"></i></button>
                                <button onclick="deleteCategory({{ $category->id }})" class="tw-w-6 tw-h-6 tw-flex tw-items-center tw-justify-center tw-rounded tw-bg-red-50 hover:tw-bg-red-100 tw-text-red-600"><i class="bi bi-trash tw-text-xs"></i></button>
                            </div>
                        </div>
                        @if($category->subcategories->count() > 0)
                            <div class="tw-ml-4 tw-pl-4 tw-border-l-2 tw-border-gray-100 dark:tw-border-gray-700 tw-space-y-2">
                                @foreach($category->subcategories as $sub)
                                    <div class="tw-flex tw-items-center tw-justify-between tw-text-xs tw-text-gray-600 dark:tw-text-gray-400">
                                        <div class="tw-flex tw-items-center tw-gap-2">
                                            <input type="checkbox" class="subcategory-checkbox tw-rounded" value="{{ $sub->id }}">
                                            <i class="bi bi-arrow-return-right tw-text-gray-300 dark:tw-text-gray-600"></i>
                                            <span class="tw-font-medium">{{ $sub->name }}</span>
                                            <span class="tw-text-xs tw-text-gray-400">({{ $sub->products_count }})</span>
                                        </div>
                                        <div class="tw-flex tw-gap-1">
                                            <button onclick="editSubcategory({{ $sub->id }}, '{{ $sub->name }}')" class="tw-w-5 tw-h-5 tw-flex tw-items-center tw-justify-center tw-rounded tw-bg-gray-100 hover:tw-bg-gray-200 dark:tw-bg-gray-700 dark:hover:tw-bg-gray-600 tw-text-gray-600 dark:tw-text-gray-300"><i class="bi bi-pencil tw-text-[10px]"></i></button>
                                            <button onclick="deleteSubcategory({{ $sub->id }})" class="tw-w-5 tw-h-5 tw-flex tw-items-center tw-justify-center tw-rounded tw-bg-red-50 hover:tw-bg-red-100 tw-text-red-600"><i class="bi bi-trash tw-text-[10px]"></i></button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="tw-ml-4 tw-pl-4 tw-border-l-2 tw-border-gray-100 dark:tw-border-gray-700">
                                <span class="tw-text-[10px] tw-text-gray-400 tw-italic">No subcategories</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <div class="tw-p-4 tw-border-t tw-border-gray-100 dark:tw-border-gray-700 tw-bg-gray-50/50 dark:tw-bg-gray-900/50 tw-flex tw-justify-end">
            <button onclick="bulkDeleteCategories()" class="tw-px-4 tw-py-2 tw-bg-red-600 hover:tw-bg-red-700 tw-text-white tw-text-xs tw-font-bold tw-rounded-xl tw-transition-colors tw-mr-2">
                DELETE SELECTED CATEGORIES
            </button>
            <button onclick="bulkDeleteSubcategories()" class="tw-px-4 tw-py-2 tw-bg-red-600 hover:tw-bg-red-700 tw-text-white tw-text-xs tw-font-bold tw-rounded-xl tw-transition-colors tw-mr-2">
                DELETE SELECTED SUBCATEGORIES
            </button>
            <button onclick="document.getElementById('categoriesModal').classList.add('tw-hidden')" class="tw-px-4 tw-py-2 tw-bg-gray-200 hover:tw-bg-gray-300 dark:tw-bg-gray-700 dark:hover:tw-bg-gray-600 tw-text-gray-800 dark:tw-text-gray-200 tw-text-xs tw-font-bold tw-rounded-xl tw-transition-colors">
                CLOSE
            </button>
        </div>
    </div>
</div>

@if(request()->anyFilled(['filter_name', 'filter_code', 'filter_design_code', 'category_filter', 'filter_subcategory', 'filter_bp_code', 'filter_craftsman']))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterSection = document.getElementById('filterSection');
        if (filterSection) {
            filterSection.classList.remove('tw-hidden');
        }
    });
</script>
@endif

<script>
    function toggleSection(id) {
        const sections = ['searchSection', 'filterSection'];
        sections.forEach(s => {
            const el = document.getElementById(s);
            if (el) {
                if (s === id) {
                    el.classList.toggle('tw-hidden');
                } else {
                    el.classList.add('tw-hidden');
                }
            }
        });
    }

    function resetAll() {
        window.location.href = "{{ route('super-admin.product.index') }}";
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

        // Ensure Buyer and Craftsman cannot be selected together
        if (type === 'buyer' && buyerSelect.value) {
            craftsmanSelect.value = '';
        } else if (type === 'craftsman' && craftsmanSelect.value) {
            buyerSelect.value = '';
        }
    }

    function toggleSelectAll(checked) {
        document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = checked);
    }

    function editCategory(id, name) {
        const newName = prompt('Enter new name for category:', name);
        if (newName && newName !== name) {
            fetch(`/super-admin/product-category/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name: newName })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert(data.message || 'Error updating category');
                }
            });
        }
    }

    function deleteCategory(id) {
        if (confirm('Are you sure you want to delete this category?')) {
            fetch(`/super-admin/product-category/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert(data.message || 'Error deleting category');
                }
            });
        }
    }

    function editSubcategory(id, name) {
        const newName = prompt('Enter new name for subcategory:', name);
        if (newName && newName !== name) {
            fetch(`/super-admin/product-subcategory/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name: newName })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert(data.message || 'Error updating subcategory');
                }
            });
        }
    }

    function deleteSubcategory(id) {
        if (confirm('Are you sure you want to delete this subcategory?')) {
            fetch(`/super-admin/product-subcategory/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert(data.message || 'Error deleting subcategory');
                }
            });
        }
    }

    function bulkDeleteCategories() {
        const checkboxes = document.querySelectorAll('.category-checkbox:checked');
        const ids = Array.from(checkboxes).map(cb => cb.value);
        if (ids.length === 0) {
            alert('Please select at least one category');
            return;
        }
        if (confirm('Are you sure you want to delete selected categories?')) {
            fetch('/super-admin/product-category/bulk-delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids: ids })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert(data.message || 'Error deleting categories');
                }
            });
        }
    }

    function bulkDeleteSubcategories() {
        const checkboxes = document.querySelectorAll('.subcategory-checkbox:checked');
        const ids = Array.from(checkboxes).map(cb => cb.value);
        if (ids.length === 0) {
            alert('Please select at least one subcategory');
            return;
        }
        if (confirm('Are you sure you want to delete selected subcategories?')) {
            fetch('/super-admin/product-subcategory/bulk-delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids: ids })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert(data.message || 'Error deleting subcategories');
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        filterSubcategories();
    });
</script>
@endsection