@extends('key-user.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Product</h1>
            <nav class="flex text-sm text-gray-500 mt-1">
                <a href="{{ route('key-user.dashboard') }}" class="hover:text-indigo-600 transition">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('key-user.product.index') }}" class="hover:text-indigo-600 transition">Products</a>
                <span class="mx-2">/</span>
                <span class="text-gray-800 font-medium">Edit</span>
            </nav>
        </div>
        <a href="{{ route('key-user.product.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
            <i class="bi bi-arrow-left mr-2"></i> Back to List
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h4 class="text-lg font-bold text-gray-800">Product Details</h4>
        </div>

        <div class="p-6">
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                    <div class="flex">
                        <i class="bi bi-exclamation-triangle-fill text-red-500 mr-3"></i>
                        <ul class="text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('key-user.product.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label for="bp_code" class="block text-sm font-semibold text-gray-700">BP Code <span class="text-red-500">*</span></label>
                        <input type="text" id="bp_code" name="bp_code" value="{{ old('bp_code', $product->bp_code) }}" readonly
                               class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-500 cursor-not-allowed text-sm focus:ring-0">
                    </div>

                    <div class="space-y-1">
                        <label for="product_name" class="block text-sm font-semibold text-gray-700">Product Name <span class="text-red-500"></span></label>
                        <input type="text" id="product_name" name="product_name" value="{{ old('product_name', $product->product_name) }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition @error('product_name') border-red-500 @enderror">
                    </div>

                    <div class="space-y-1">
                        <label for="product_code" class="block text-sm font-semibold text-gray-700">Product Code <span class="text-red-500"></span></label>
                        <input type="text" id="product_code" name="product_code" value="{{ old('product_code', $product->product_code) }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition @error('product_code') border-red-500 @enderror">
                    </div>

                    <div class="space-y-1">
                        <label for="product_category_id" class="block text-sm font-semibold text-gray-700">Product Category <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <select id="product_category_id" name="product_category_id" required
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition @error('product_category_id') border-red-500 @enderror">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('product_category_id', $product->product_category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" id="addCategoryBtn" class="px-4 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 transition">New</button>
                        </div>
                    </div>

                    <div class="space-y-1" id="subcategory-container" style="{{ $product->product_subcategory_id ? 'display: block;' : 'display: none;' }}">
                        <label for="subcategory_id" class="block text-sm font-semibold text-gray-700">Sub Category</label>
                        <div class="flex gap-2">
                            <select id="subcategory_id" name="subcategory_id"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                                <option value="">Select Sub Category</option>
                                @foreach($subcategories as $subcategory)
                                    <option value="{{ $subcategory->id }}" {{ old('subcategory_id', $product->product_subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                        {{ $subcategory->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" id="addSubcategoryBtn" class="px-4 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 transition">New</button>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label for="type" class="block text-sm font-semibold text-gray-700">Type <span class="text-red-500">*</span></label>
                        <select id="type" name="type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition @error('type') border-red-500 @enderror">
                            <option value="">Select Type</option>
                            <option value="Piece" {{ old('type', $product->type) == 'Piece' ? 'selected' : '' }}>Piece</option>
                            <option value="Pair" {{ old('type', $product->type) == 'Pair' ? 'selected' : '' }}>Pair</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="order_type" class="block text-sm font-semibold text-gray-700">Order Type <span class="text-red-500">*</span></label>
                        <select id="order_type" name="order_type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition @error('order_type') border-red-500 @enderror">
                            <option value="">Select Order Type</option>
                            <option value="Regular" {{ old('order_type', $product->order_type) == 'Regular' ? 'selected' : '' }}>Regular</option>
                            <option value="Urgent" {{ old('order_type', $product->order_type) == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="Super Urgent" {{ old('order_type', $product->order_type) == 'Super Urgent' ? 'selected' : '' }}>Super Urgent</option>
                        </select>
                    </div>

                    <div class="space-y-1 category-option" data-opt="has_open_close" style="{{ $product->category && $product->category->has_open_close ? '' : 'display:none;' }}">
                        <label for="open_close" class="block text-sm font-semibold text-gray-700">Open/Close</label>
                        <select id="open_close" name="open_close" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                            <option value="">Select</option>
                            <option value="Open" {{ old('open_close', $product->open_close) == 'Open' ? 'selected' : '' }}>Open</option>
                            <option value="Close" {{ old('open_close', $product->open_close) == 'Close' ? 'selected' : '' }}>Close</option>
                        </select>
                    </div>

                    <div class="space-y-1 category-option" data-opt="has_hook" style="{{ $product->category && $product->category->has_hook ? '' : 'display:none;' }}">
                        <label for="hook" class="block text-sm font-semibold text-gray-700">Hook</label>
                        <input type="text" id="hook" name="hook" value="{{ old('hook', $product->hook) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1 category-option" data-opt="has_enamel" style="{{ $product->category && $product->category->has_enamel ? '' : 'display:none;' }}">
                        <label for="enamel" class="block text-sm font-semibold text-gray-700">Enamel</label>
                        <input type="text" id="enamel" name="enamel" value="{{ old('enamel', $product->enamel) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1 category-option" data-opt="has_rodium" style="{{ $product->category && $product->category->has_rodium ? '' : 'display:none;' }}">
                        <label for="rodium" class="block text-sm font-semibold text-gray-700">Rodium</label>
                        <input type="text" id="rodium" name="rodium" value="{{ old('rodium', $product->rodium) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1 category-option" data-opt="has_stone" style="{{ $product->category && $product->category->has_stone ? '' : 'display:none;' }}">
                        <label for="stone" class="block text-sm font-semibold text-gray-700">Stone</label>
                        <input type="text" id="stone" name="stone" value="{{ old('stone', $product->stone) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1">
                        <label for="size" class="block text-sm font-semibold text-gray-700">Size</label>
                        <input type="text" id="size" name="size" value="{{ old('size', $product->size) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1">
                        <label for="length" class="block text-sm font-semibold text-gray-700">Length</label>
                        <input type="text" id="length" name="length" value="{{ old('length', $product->length) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1">
                        <label for="weight_from" class="block text-sm font-semibold text-gray-700">Weight From</label>
                        <input type="number" step="0.001" id="weight_from" name="weight_from" value="{{ old('weight_from', $product->weight_from) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1">
                        <label for="weight_to" class="block text-sm font-semibold text-gray-700">Weight To</label>
                        <input type="number" step="0.001" id="weight_to" name="weight_to" value="{{ old('weight_to', $product->weight_to) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100">
                    <label for="images" class="block text-sm font-semibold text-gray-700 mb-2">Manage Images</label>
                    <div class="flex flex-col md:flex-row gap-6 items-start">
                        <div class="w-full md:w-1/2">
                            <input type="file" id="images" name="images[]" multiple accept="image/*"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition">
                            <p class="mt-2 text-xs text-gray-400">Select multiple images if needed (max 2MB per file).</p>
                        </div>

                        @if($product->images->count() > 0 || $product->product_image)
                        <div class="w-full md:w-1/2">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Current Images</p>
                            <div class="flex flex-wrap gap-3">
                                @foreach($product->images as $image)
                                    <div class="group relative">
                                        <img src="{{ asset('storage/' . $image->path) }}" class="w-20 h-20 object-cover rounded-lg border border-gray-200 shadow-sm transition group-hover:opacity-75">
                                    </div>
                                @endforeach
                                
                                @if($product->product_image)
                                    <div class="relative group">
                                        <img src="{{ asset($product->product_image) }}" class="w-20 h-20 object-cover rounded-lg border-2 border-yellow-400 shadow-sm transition group-hover:opacity-75">
                                        <span class="absolute -top-2 -left-2 bg-yellow-400 text-white text-[8px] font-black px-1.5 py-0.5 rounded shadow-sm">LEGACY</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                    <a href="{{ route('key-user.product.index') }}" class="px-6 py-2 text-sm font-semibold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition shadow-md">
                        Update Product
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
                            opt.value = s.id; opt.textContent = s.name; 
                            
                            // Check if this should be selected
                            if (s.id == '{{ old('subcategory_id', $product->product_subcategory_id) }}') {
                                opt.selected = true;
                            }
                            
                            subcategorySelect.appendChild(opt);
                        });
                    } else {
                        subcategoryContainer.style.display = 'none';
                    }
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
                    refreshSubcategories(res.category.id);
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
                } else {
                    alert('Failed to create subcategory');
                }
            });
        });
    });
</script>
@endsection