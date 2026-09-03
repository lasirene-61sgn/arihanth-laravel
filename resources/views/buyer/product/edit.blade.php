@extends('buyer.layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Edit Product</h1>
            <p class="text-sm text-slate-500">Updating: <span class="font-bold text-blue-600">{{ $product->product_name ?? $product->product_code }}</span></p>
        </div>
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm font-medium">
                <li><a href="{{ route('buyer.dashboard') }}" class="text-slate-500 hover:text-blue-600 transition-colors">Dashboard</a></li>
                <li class="flex items-center text-slate-400">
                    <i class="bi bi-chevron-right mx-2 text-[10px]"></i>
                    <a href="{{ route('buyer.product.index') }}" class="text-slate-500 hover:text-blue-600 transition-colors">Products</a>
                </li>
                <li class="flex items-center text-slate-400">
                    <i class="bi bi-chevron-right mx-2 text-[10px]"></i>
                    <span class="text-blue-600">Edit</span>
                </li>
            </ol>
        </nav>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h4 class="text-lg font-bold text-slate-800">Product Details</h4>
            <a href="{{ route('buyer.product.index') }}" class="text-xs font-bold text-slate-500 hover:text-blue-600 transition-colors flex items-center gap-1">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="p-6 md:p-8">
            @if ($errors->any())
                <div class="mb-6 flex items-start p-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-r-lg">
                    <i class="bi bi-exclamation-triangle-fill text-xl mr-3"></i>
                    <div>
                        <span class="font-bold">Please correct the following errors:</span>
                        <ul class="mt-1 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('buyer.product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    
                    {{-- BP Code --}}
                    <div class="space-y-2">
                        <label for="bp_code" class="text-sm font-bold text-slate-700">BP Code <span class="text-red-500">*</span></label>
                        <input type="text" 
                               class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 cursor-not-allowed outline-none @error('bp_code') border-red-500 @enderror" 
                               id="bp_code" 
                               name="bp_code" 
                               value="{{ old('bp_code', $product->bp_code ?? (isset($buyer) ? $buyer->bp_code : '')) }}" 
                               readonly>
                        @if(isset($buyer) || isset($product->buyer))
                            <p class="text-[11px] font-medium text-slate-500 italic">
                                <i class="bi bi-building mr-1"></i> Business: {{ $buyer->business_name ?? $product->buyer->business_name }}
                            </p>
                        @endif
                        @error('bp_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Product Name --}}
                    <div class="space-y-2">
                        <label for="product_name" class="text-sm font-bold text-slate-700">Product Name</label>
                        <input type="text" 
                               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none @error('product_name') border-red-500 @enderror" 
                               id="product_name" 
                               name="product_name" 
                               value="{{ old('product_name', $product->product_name) }}" 
                               placeholder="Enter name">
                        @error('product_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Product Code --}}
                    <div class="space-y-2">
                        <label for="product_code" class="text-sm font-bold text-slate-700">Product Code</label>
                        <input type="text" 
                               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none @error('product_code') border-red-500 @enderror" 
                               id="product_code" 
                               name="product_code" 
                               value="{{ old('product_code', $product->product_code) }}" 
                               placeholder="e.g. PRD-001">
                        @error('product_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Category --}}
                    <div class="space-y-2">
                        <label for="product_category_id" class="text-sm font-bold text-slate-700">Product Category <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <select class="flex-1 px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none appearance-none @error('product_category_id') border-red-500 @enderror" 
                                    id="product_category_id" 
                                    name="product_category_id" 
                                    required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('product_category_id', $product->product_category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition-colors border border-slate-200" type="button" id="addCategoryBtn">New</button>
                        </div>
                        @error('product_category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Sub Category --}}
                    <div class="space-y-2" id="subcategory-container" style="{{ old('subcategory_id', $product->subcategory_id ?? $product->product_subcategory_id) ? '' : 'display: none;' }}">
                        <label for="subcategory_id" class="text-sm font-bold text-slate-700">Sub Category</label>
                        <div class="flex gap-2">
                            <select class="flex-1 px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" id="subcategory_id" name="subcategory_id">
                                <option value="">Select Sub Category</option>
                                @if(isset($subcategories))
                                    @foreach($subcategories as $subcategory)
                                        <option value="{{ $subcategory->id }}" {{ old('subcategory_id', $product->subcategory_id ?? $product->product_subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                            {{ $subcategory->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <button class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition-colors border border-slate-200" type="button" id="addSubcategoryBtn">New</button>
                        </div>
                    </div>

                    {{-- Type --}}
                    <div class="space-y-2">
                        <label for="type" class="text-sm font-bold text-slate-700">Type <span class="text-red-500">*</span></label>
                        <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none appearance-none @error('type') border-red-500 @enderror" 
                                id="type" 
                                name="type" 
                                required>
                            <option value="">Select Type</option>
                            <option value="Piece" {{ old('type', $product->type) == 'Piece' ? 'selected' : '' }}>Piece</option>
                            <option value="Pair" {{ old('type', $product->type) == 'Pair' ? 'selected' : '' }}>Pair</option>
                        </select>
                        @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Dynamic Option: Open/Close --}}
                    <div class="space-y-2 category-option" data-opt="has_open_close" style="display:none;">
                        <label for="open_close" class="text-sm font-bold text-slate-700">Open/Close</label>
                        <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" id="open_close" name="open_close">
                            <option value="">Select</option>
                            <option value="Open" {{ old('open_close', $product->open_close) == 'Open' ? 'selected' : '' }}>Open</option>
                            <option value="Close" {{ old('open_close', $product->open_close) == 'Close' ? 'selected' : '' }}>Close</option>
                        </select>
                    </div>

                    {{-- Dynamic Option: Hook --}}
                    <div class="space-y-2 category-option" data-opt="has_hook" style="display:none;">
                        <label for="hook" class="text-sm font-bold text-slate-700">Hook</label>
                        <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" id="hook" name="hook" value="{{ old('hook', $product->hook) }}">
                    </div>

                    {{-- Dynamic Option: Enamel --}}
                    <div class="space-y-2 category-option" data-opt="has_enamel" style="display:none;">
                        <label for="enamel" class="text-sm font-bold text-slate-700">Enamel</label>
                        <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" id="enamel" name="enamel" value="{{ old('enamel', $product->enamel) }}">
                    </div>

                    {{-- Dynamic Option: Rodium --}}
                    <div class="space-y-2 category-option" data-opt="has_rodium" style="display:none;">
                        <label for="rodium" class="text-sm font-bold text-slate-700">Rodium</label>
                        <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" id="rodium" name="rodium" value="{{ old('rodium', $product->rodium) }}">
                    </div>

                    {{-- Dynamic Option: Stone --}}
                    <div class="space-y-2 category-option" data-opt="has_stone" style="display:none;">
                        <label for="stone" class="text-sm font-bold text-slate-700">Stone</label>
                        <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" id="stone" name="stone" value="{{ old('stone', $product->stone) }}">
                    </div>

                    {{-- Size --}}
                    <div class="space-y-2">
                        <label for="size" class="text-sm font-bold text-slate-700">Size</label>
                        <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" id="size" name="size" value="{{ old('size', $product->size) }}">
                    </div>

                    {{-- Length --}}
                    <div class="space-y-2">
                        <label for="length" class="text-sm font-bold text-slate-700">Length</label>
                        <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" id="length" name="length" value="{{ old('length', $product->length) }}">
                    </div>

                    {{-- Weight From --}}
                    <div class="space-y-2">
                        <label for="weight_from" class="text-sm font-bold text-slate-700">Weight From</label>
                        <input type="text" step="0.001" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" id="weight_from" name="weight_from" value="{{ old('weight_from', $product->weight_from) }}" placeholder="0.000">
                    </div>

                    {{-- Weight To --}}
                    <div class="space-y-2">
                        <label for="weight_to" class="text-sm font-bold text-slate-700">Weight To</label>
                        <input type="text" step="0.001" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" id="weight_to" name="weight_to" value="{{ old('weight_to', $product->weight_to) }}" placeholder="0.000">
                    </div>

                    {{-- Images Upload Area --}}
                    <div class="md:col-span-2 space-y-2">
                        <label for="images" class="text-sm font-bold text-slate-700">Upload Additional Images (Only white background, *No Logo, No Title .)</label>
                        <div class="flex items-center justify-center w-full">
                            <label for="images" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-200 border-dashed rounded-2xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-colors">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <i class="bi bi-cloud-arrow-up text-3xl text-slate-400 mb-2"></i>
                                    <p class="text-sm text-slate-500"><span class="font-bold text-blue-600">Click to upload</span> or drag and drop</p>
                                    <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-1">Images only (Max 2MB per file)</p>
                                </div>
                                <input id="images" name="images[]" type="file" class="hidden" multiple accept="image/*" />
                            </label>
                        </div>
                        @error('images') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Existing Media Section --}}
                @if(isset($product->images) && $product->images->count() > 0)
                    <div class="pt-6 mt-6 border-t border-slate-100">
                        <h5 class="text-sm font-bold text-slate-700 uppercase tracking-widest mb-4">Current Media ({{ $product->images->count() }})</h5>
                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
                            @foreach($product->images as $image)
                                <div class="relative aspect-square rounded-xl overflow-hidden border border-slate-200 group shadow-sm bg-slate-50">
                                    <img src="{{ asset('storage/' . $image->path) }}" 
                                         class="w-full h-full object-cover" alt="Product Image">
                                    <div class="absolute bottom-1.5 left-1/2 -translate-x-1/2 bg-white/95 backdrop-blur-xs px-2 py-0.5 rounded-md shadow-xs border border-slate-200 flex items-center gap-1.5" style="z-index:2;">
                                        <input type="checkbox" name="delete_images[]" value="{{ $image->id }}" id="del_img_{{ $image->id }}" class="rounded text-red-600 focus:ring-red-500 cursor-pointer">
                                        <label for="del_img_{{ $image->id }}" class="text-red-600 text-[11px] font-bold cursor-pointer select-none">Delete</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Form Actions --}}
                <div class="flex items-center justify-end gap-3 mt-10 pt-6 border-t border-slate-100">
                    <a href="{{ route('buyer.product.index') }}" class="px-6 py-2.5 text-sm font-bold text-slate-600 hover:text-slate-800 transition-colors">Cancel</a>
                    <button type="submit" class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all shadow-md shadow-blue-200">
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
        const currentSubcategoryId = "{{ old('subcategory_id', $product->subcategory_id ?? $product->product_subcategory_id ?? '') }}";

        function refreshCategoryOptions(categoryId) {
            optionBlocks.forEach(b => b.style.display = 'none');
            if (!categoryId) return;
            
            fetch(`{{ url('/buyer/product/get-category-options') }}?category_id=${categoryId}`)
                .then(r => r.json())
                .then(data => {
                    Object.keys(data).forEach(key => {
                        if (data[key]) {
                            const el = document.querySelector(`.category-option[data-opt="${key}"]`);
                            if (el) el.style.display = '';
                        }
                    });
                })
                .catch(err => console.error('Error fetching category options:', err));
        }

        function refreshSubcategories(categoryId, selectedId = null) {
            subcategorySelect.innerHTML = '<option value="">Select Sub Category</option>';
            if (!categoryId) { subcategoryContainer.style.display = 'none'; return; }
            
            fetch(`{{ url('/buyer/product/get-subcategories') }}?category_id=${categoryId}`)
                .then(r => r.json())
                .then(list => {
                    if (list.length > 0) {
                        subcategoryContainer.style.display = 'block';
                        list.forEach(s => {
                            const opt = document.createElement('option');
                            opt.value = s.id; 
                            opt.textContent = s.name;
                            if (selectedId && s.id == selectedId) {
                                opt.selected = true;
                            }
                            subcategorySelect.appendChild(opt);
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

        // Initialize state on page load using saved product values
        if (categorySelect.value) {
            refreshCategoryOptions(categorySelect.value);
            refreshSubcategories(categorySelect.value, currentSubcategoryId);
        }

        // Add Category Modal / Prompt
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
            
            fetch(`{{ route('buyer.product-category.store') }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ name, ...opts })
            }).then(r => r.json()).then(res => {
                if (res.category) {
                    const opt = document.createElement('option');
                    opt.value = res.category.id; opt.textContent = res.category.name; opt.selected = true;
                    categorySelect.appendChild(opt);
                    refreshCategoryOptions(res.category.id);
                    subcategoryContainer.style.display = 'block';
                    subcategorySelect.innerHTML = '<option value="">Select Sub Category</option>';
                } else {
                    alert('Failed to create category');
                }
            });
        });

        // Add Subcategory Modal / Prompt
        document.getElementById('addSubcategoryBtn').addEventListener('click', function() {
            const parentId = categorySelect.value;
            if (!parentId) { alert('Select a category first'); return; }
            const name = prompt('Enter new subcategory name');
            if (!name) return;
            
            fetch(`{{ route('buyer.product-category.store') }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ parent_category_id: parentId, name })
            }).then(r => r.json()).then(res => {
                if (res.subcategory) {
                    const opt = document.createElement('option');
                    opt.value = res.subcategory.id; opt.textContent = res.subcategory.name; opt.selected = true;
                    subcategorySelect.appendChild(opt);
                    subcategoryContainer.style.display = 'block';
                } else {
                    alert('Failed to create subcategory');
                }
            });
        });
    });
</script>
@endsection