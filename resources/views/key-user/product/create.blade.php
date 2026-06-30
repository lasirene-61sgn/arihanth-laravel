@extends('key-user.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Create Product</h1>
            <nav class="flex mt-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-sm font-medium">
                    <li><a href="{{ route('key-user.dashboard') }}" class="text-slate-500 hover:text-blue-600 transition-colors">Dashboard</a></li>
                    <li class="flex items-center text-slate-400">
                        <i class="bi bi-chevron-right mx-2 text-[10px]"></i>
                        <a href="{{ route('key-user.product.index') }}" class="text-slate-500 hover:text-blue-600 transition-colors">Products</a>
                    </li>
                    <li class="flex items-center text-slate-400">
                        <i class="bi bi-chevron-right mx-2 text-[10px]"></i>
                        <span class="text-blue-600">Create</span>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden text-slate-900">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center">
            <i class="bi bi-box-seam text-blue-600 mr-2"></i>
            <h4 class="text-lg font-bold text-slate-800 uppercase tracking-tight">Product Details</h4>
        </div>

        <div class="p-6 md:p-8">
            @if ($errors->any())
                <div class="mb-6 flex items-start p-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-r-lg">
                    <i class="bi bi-exclamation-triangle-fill text-xl mr-3 opacity-80"></i>
                    <div>
                        <span class="font-bold text-sm uppercase tracking-wide">Please Fix These Errors</span>
                        <ul class="mt-1 list-disc list-inside text-sm opacity-90">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('key-user.product.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    
                    <div class="space-y-2">
                        <label for="bp_code" class="text-sm font-bold text-slate-700">BP Code <span class="text-red-500">*</span></label>
                        @if(isset($buyer))
                            <input type="text" 
                                   class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono text-slate-500 outline-none cursor-not-allowed" 
                                   id="bp_code" name="bp_code" value="{{ $buyer->bp_code }}" readonly>
                            <p class="text-[11px] font-bold text-blue-500 flex items-center">
                                <i class="bi bi-building mr-1"></i> Business: {{ $buyer->business_name }}
                            </p>
                        @else
                            <input type="text" 
                                   class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono text-slate-500 outline-none cursor-not-allowed" 
                                   id="bp_code" name="bp_code" value="{{ old('bp_code') }}" readonly>
                        @endif
                    </div>

                    <div class="space-y-2">
                        <label for="product_name" class="text-sm font-bold text-slate-700">Product Name <span class="text-red-500"></span></label>
                        <input type="text" 
                               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none @error('product_name') border-red-500 @enderror" 
                               id="product_name" name="product_name" value="{{ old('product_name') }}" placeholder="e.g., Diamond Stud Earrings">
                    </div>

                    <div class="space-y-2">
                        <label for="product_code" class="text-sm font-bold text-slate-700">Product Code <span class="text-red-500"></span></label>
                        <input type="text" 
                               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 transition-all outline-none @error('product_code') border-red-500 @enderror" 
                               id="product_code" name="product_code" value="{{ old('product_code') }}" placeholder="Unique SKU or Code">
                    </div>

                    <div class="space-y-2">
                        <label for="product_category_id" class="text-sm font-bold text-slate-700">Product Category <span class="text-red-500">*</span></label>
                        <div class="flex gap-1">
                            <select class="flex-1 px-4 py-2.5 bg-white border border-slate-200 rounded-l-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none appearance-none @error('product_category_id') border-red-500 @enderror" 
                                    id="product_category_id" name="product_category_id" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('product_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="px-4 bg-slate-100 text-slate-600 font-bold text-xs rounded-r-xl border border-slate-200 hover:bg-slate-200 transition-colors" type="button" id="addCategoryBtn">New</button>
                        </div>
                    </div>

                    <div class="space-y-2" id="subcategory-container" style="display: none;">
                        <label for="subcategory_id" class="text-sm font-bold text-slate-700">Sub Category</label>
                        <div class="flex gap-1">
                            <select class="flex-1 px-4 py-2.5 bg-white border border-slate-200 rounded-l-xl text-sm outline-none appearance-none" id="subcategory_id" name="subcategory_id">
                                <option value="">Select Sub Category</option>
                            </select>
                            <button class="px-4 bg-slate-100 text-slate-600 font-bold text-xs rounded-r-xl border border-slate-200" type="button" id="addSubcategoryBtn">New</button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="type" class="text-sm font-bold text-slate-700">Type <span class="text-red-500">*</span></label>
                        <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none appearance-none @error('type') border-red-500 @enderror" 
                                id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="Piece" {{ old('type') == 'Piece' ? 'selected' : '' }}>Piece</option>
                            <option value="Pair" {{ old('type') == 'Pair' ? 'selected' : '' }}>Pair</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="space-y-2 category-option" data-opt="has_open_close" style="display:none;">
                        <label for="open_close" class="text-sm font-bold text-slate-700">Open/Close</label>
                        <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm outline-none" id="open_close" name="open_close">
                            <option value="">Select</option>
                            <option value="Open" {{ old('open_close') == 'Open' ? 'selected' : '' }}>Open</option>
                            <option value="Close" {{ old('open_close') == 'Close' ? 'selected' : '' }}>Close</option>
                        </select>
                    </div>
                    <div class="space-y-2 category-option" data-opt="has_hook" style="display:none;">
                        <label for="hook" class="text-sm font-bold text-slate-700">Hook</label>
                        <input type="text" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm outline-none" id="hook" name="hook" value="{{ old('hook') }}">
                    </div>
                    <div class="space-y-2 category-option" data-opt="has_enamel" style="display:none;">
                        <label for="enamel" class="text-sm font-bold text-slate-700">Enamel</label>
                        <input type="text" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm outline-none" id="enamel" name="enamel" value="{{ old('enamel') }}">
                    </div>
                    <div class="space-y-2 category-option" data-opt="has_rodium" style="display:none;">
                        <label for="rodium" class="text-sm font-bold text-slate-700">Rodium</label>
                        <input type="text" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm outline-none" id="rodium" name="rodium" value="{{ old('rodium') }}">
                    </div>
                    <div class="space-y-2 category-option" data-opt="has_stone" style="display:none;">
                        <label for="stone" class="text-sm font-bold text-slate-700">Stone</label>
                        <input type="text" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm outline-none" id="stone" name="stone" value="{{ old('stone') }}">
                    </div>
                </div>

                <div class="bg-slate-50 rounded-3xl p-6 border border-slate-100">
                    <h5 class="text-xs font-bold text-blue-600 uppercase tracking-[0.2em] mb-6">Technical Specifications</h5>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="space-y-2">
                            <label for="size" class="text-sm font-bold text-slate-700">Size</label>
                            <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" id="size" name="size" value="{{ old('size') }}">
                        </div>
                        <div class="space-y-2">
                            <label for="length" class="text-sm font-bold text-slate-700">Length</label>
                            <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" id="length" name="length" value="{{ old('length') }}">
                        </div>
                        <div class="space-y-2">
                            <label for="weight_from" class="text-sm font-bold text-slate-700">Weight From (gm)</label>
                            <input type="number" step="0.001" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" id="weight_from" name="weight_from" value="{{ old('weight_from') }}">
                        </div>
                        <div class="space-y-2">
                            <label for="weight_to" class="text-sm font-bold text-slate-700">Weight To (gm)</label>
                            <input type="number" step="0.001" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" id="weight_to" name="weight_to" value="{{ old('weight_to') }}">
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="images" class="text-sm font-bold text-slate-700 flex items-center">
                        <i class="bi bi-images mr-2 text-blue-500"></i> Product Images(Only white background, *No Logo, No Title .)
                    </label>
                    <div class="relative group">
                        <input type="file" 
                               class="w-full px-4 py-1.5 bg-white border border-slate-200 rounded-xl text-xs file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all" 
                               id="images" name="images[]" multiple accept="image/*">
                    </div>
                    <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest italic pt-1">Max 2MB per image • Multiple selection allowed</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-8 border-t border-slate-100">
                    <a href="{{ route('key-user.product.index') }}" 
                       class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all shadow-md shadow-blue-200">
                        <i class="bi bi-save mr-2"></i> Create Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('product_category_id');
        const subcategoryContainer = document.getElementById('subcategory-container');
        const subcategorySelect = document.getElementById('subcategory_id');
        const optionBlocks = document.querySelectorAll('.category-option');

        function refreshCategoryOptions(categoryId) {
            // Hide all dynamic option blocks first
            optionBlocks.forEach(b => b.style.display = 'none');
            if (!categoryId) return;
            
            fetch(`{{ url('/key-user/product/get-category-options') }}?category_id=${categoryId}`)
                .then(r => r.json())
                .then(data => {
                    Object.keys(data).forEach(key => {
                        if (data[key]) {
                            const el = document.querySelector(`.category-option[data-opt="${key}"]`);
                            if (el) el.style.display = '';
                        }
                    });
                });
        }

        function refreshSubcategories(categoryId) {
            subcategorySelect.innerHTML = '<option value="">Select Sub Category</option>';
            if (!categoryId) { subcategoryContainer.style.display = 'none'; return; }
            
            fetch(`{{ url('/key-user/product/get-subcategories') }}?category_id=${categoryId}`)
                .then(r => r.json())
                .then(list => {
                    if (list.length > 0) {
                        subcategoryContainer.style.display = 'block';
                        list.forEach(s => {
                            const opt = document.createElement('option');
                            opt.value = s.id; opt.textContent = s.name; subcategorySelect.appendChild(opt);
                        });
                    } else {
                        subcategoryContainer.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error fetching subcategories:', error);
                    subcategoryContainer.style.display = 'none';
                });
        }

        categorySelect.addEventListener('change', function() {
            const id = this.value;
            refreshSubcategories(id);
            refreshCategoryOptions(id);
        });

        // Initialize on load if old value exists
        if (categorySelect.value) {
            refreshSubcategories(categorySelect.value);
            refreshCategoryOptions(categorySelect.value);
        }

        // Add Category
        document.getElementById('addCategoryBtn').addEventListener('click', function() {
            const name = prompt('Enter new category name');
            if (!name) return;
            
            const opts = {
                has_hook: confirm('Enable Hook option for this category?') ? 1 : 0,
                has_enamel: confirm('Enable Enamel option?') ? 1 : 0,
                has_rodium: confirm('Enable Rodium option?') ? 1 : 0,
                has_open_close: confirm('Enable Open/Close option?') ? 1 : 0,
                has_stone: confirm('Enable Stone option?') ? 1 : 0,
            };
            
            fetch(`{{ route('key-user.product-category.store') }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ name, ...opts })
            }).then(r => r.json()).then(res => {
                if (res.category) {
                    const opt = document.createElement('option');
                    opt.value = res.category.id; opt.textContent = res.category.name; opt.selected = true;
                    categorySelect.appendChild(opt);
                    refreshCategoryOptions(res.category.id);
                    // After creating a new category, ensure subcategory container is ready for adding subcategories
                    subcategoryContainer.style.display = 'block';
                    subcategorySelect.innerHTML = '<option value="">Select Sub Category</option>';
                } else {
                    alert('Failed to create category');
                }
            });
        });

        // Add Subcategory
        document.getElementById('addSubcategoryBtn').addEventListener('click', function() {
            const parentId = categorySelect.value;
            if (!parentId) { alert('Select a category first'); return; }
            const name = prompt('Enter new subcategory name');
            if (!name) return;
            
            fetch(`{{ route('key-user.product-category.store') }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ parent_category_id: parentId, name })
            }).then(r => r.json()).then(res => {
                if (res.subcategory) {
                    const opt = document.createElement('option');
                    opt.value = res.subcategory.id; opt.textContent = res.subcategory.name; opt.selected = true;
                    subcategorySelect.appendChild(opt);
                    // Show the subcategory container if it was hidden
                    subcategoryContainer.style.display = 'block';
                } else {
                    alert('Failed to create subcategory');
                }
            });
        });
    });
</script>
@endsection