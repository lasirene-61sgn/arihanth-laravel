@extends('user.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Edit Product</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('user.product.index') }}">Products</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Product Details</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('user.product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- BP Code Field - Only show the User's BP code -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bp_code" class="form-label">BP Code <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('bp_code') is-invalid @enderror" 
                                           id="bp_code" 
                                           name="bp_code" 
                                           value="{{ old('bp_code', $product->bp_code) }}" 
                                           readonly>
                                    @error('bp_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('product_name') is-invalid @enderror" 
                                           id="product_name" 
                                           name="product_name" 
                                           value="{{ old('product_name', $product->product_name) }}" 
                                           required>
                                    @error('product_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_code" class="form-label">Product Code <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('product_code') is-invalid @enderror" 
                                           id="product_code" 
                                           name="product_code" 
                                           value="{{ old('product_code', $product->product_code) }}" 
                                           required>
                                    @error('product_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_category_id" class="form-label">Product Category <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select class="form-control @error('product_category_id') is-invalid @enderror" 
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
                                        <button class="btn btn-outline-secondary" type="button" id="addCategoryBtn">New</button>
                                    </div>
                                    @error('product_category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3" id="subcategory-container" style="{{ $product->product_subcategory_id ? 'display: block;' : 'display: none;' }}">
                                <label for="subcategory_id" class="form-label">Sub Category</label>
                                <div class="input-group">
                                    <select class="form-control" id="subcategory_id" name="subcategory_id">
                                        <option value="">Select Sub Category</option>
                                        @foreach($subcategories as $subcategory)
                                            <option value="{{ $subcategory->id }}" {{ old('subcategory_id', $product->product_subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                                {{ $subcategory->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-outline-secondary" type="button" id="addSubcategoryBtn">New</button>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('type') is-invalid @enderror" 
                                            id="type" 
                                            name="type" 
                                            required>
                                        <option value="">Select Type</option>
                                        <option value="Piece" {{ old('type', $product->type) == 'Piece' ? 'selected' : '' }}>Piece</option>
                                        <option value="Pair" {{ old('type', $product->type) == 'Pair' ? 'selected' : '' }}>Pair</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="order_type" class="form-label">Order Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('order_type') is-invalid @enderror" 
                                            id="order_type" 
                                            name="order_type" 
                                            required>
                                        <option value="">Select Order Type</option>
                                        <option value="Regular" {{ old('order_type', $product->order_type) == 'Regular' ? 'selected' : '' }}>Regular</option>
                                        <option value="Urgent" {{ old('order_type', $product->order_type) == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                                        <option value="Super Urgent" {{ old('order_type', $product->order_type) == 'Super Urgent' ? 'selected' : '' }}>Super Urgent</option>
                                    </select>
                                    @error('order_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3 category-option" data-opt="has_open_close" style="{{ $product->category && $product->category->has_open_close ? '' : 'display:none;' }}">
                                <label for="open_close" class="form-label">Open/Close</label>
                                <select class="form-control" id="open_close" name="open_close">
                                    <option value="">Select</option>
                                    <option value="Open" {{ old('open_close', $product->open_close) == 'Open' ? 'selected' : '' }}>Open</option>
                                    <option value="Close" {{ old('open_close', $product->open_close) == 'Close' ? 'selected' : '' }}>Close</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3 category-option" data-opt="has_hook" style="{{ $product->category && $product->category->has_hook ? '' : 'display:none;' }}">
                                <label for="hook" class="form-label">Hook</label>
                                <input type="text" class="form-control" id="hook" name="hook" value="{{ old('hook', $product->hook) }}">
                            </div>
                            
                            <div class="col-md-6 mb-3 category-option" data-opt="has_enamel" style="{{ $product->category && $product->category->has_enamel ? '' : 'display:none;' }}">
                                <label for="enamel" class="form-label">Enamel</label>
                                <input type="text" class="form-control" id="enamel" name="enamel" value="{{ old('enamel', $product->enamel) }}">
                            </div>
                            
                            <div class="col-md-6 mb-3 category-option" data-opt="has_rodium" style="{{ $product->category && $product->category->has_rodium ? '' : 'display:none;' }}">
                                <label for="rodium" class="form-label">Rodium</label>
                                <input type="text" class="form-control" id="rodium" name="rodium" value="{{ old('rodium', $product->rodium) }}">
                            </div>
                            
                            <div class="col-md-6 mb-3 category-option" data-opt="has_stone" style="{{ $product->category && $product->category->has_stone ? '' : 'display:none;' }}">
                                <label for="stone" class="form-label">Stone</label>
                                <input type="text" class="form-control" id="stone" name="stone" value="{{ old('stone', $product->stone) }}">
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="size" class="form-label">Size</label>
                                    <input type="text" 
                                           class="form-control @error('size') is-invalid @enderror" 
                                           id="size" 
                                           name="size" 
                                           value="{{ old('size', $product->size) }}">
                                    @error('size')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="length" class="form-label">Length</label>
                                    <input type="text" 
                                           class="form-control @error('length') is-invalid @enderror" 
                                           id="length" 
                                           name="length" 
                                           value="{{ old('length', $product->length) }}">
                                    @error('length')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="weight_from" class="form-label">Weight From</label>
                                    <input type="number" 
                                           step="0.001"
                                           class="form-control @error('weight_from') is-invalid @enderror" 
                                           id="weight_from" 
                                           name="weight_from" 
                                           value="{{ old('weight_from', $product->weight_from) }}">
                                    @error('weight_from')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="weight_to" class="form-label">Weight To</label>
                                    <input type="number" 
                                           step="0.001"
                                           class="form-control @error('weight_to') is-invalid @enderror" 
                                           id="weight_to" 
                                           name="weight_to" 
                                           value="{{ old('weight_to', $product->weight_to) }}">
                                    @error('weight_to')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="images" class="form-label">Add Product Images</label>
                                    <input type="file" 
                                           class="form-control @error('images') is-invalid @enderror" 
                                           id="images" 
                                           name="images[]" 
                                           multiple 
                                           accept="image/*">
                                    <small class="text-muted">You can select multiple images to add</small>
                                    @error('images')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                    <div class="mt-3">
                                        <label class="form-label d-block">Current Images:</label>
                                        <div class="row g-2">
                                            @foreach($product->images as $image)
                                                <div class="col-4">
                                                    <div class="position-relative">
                                                        <img src="{{ asset('storage/' . $image->path) }}" 
                                                             alt="Current Image" 
                                                             class="img-fluid rounded border shadow-sm"
                                                             style="height: 100px; width: 100%; object-fit: cover;">
                                                        <div class="position-absolute bottom-0 start-50 translate-middle-x bg-white rounded px-1 shadow-sm" style="z-index:2;">
                                                            <input type="checkbox" name="delete_images[]" value="{{ $image->id }}" id="del_img_{{ $image->id }}" class="form-check-input">
                                                            <label for="del_img_{{ $image->id }}" class="text-danger small fw-bold cursor-pointer">Del</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            
                                            @if($product->images->count() == 0)
                                                <div class="col-12">
                                                    <p class="text-muted small">No images uploaded yet.</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('user.product.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Product</button>
                        </div>
                    </form>
                </div>
            </div>
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
            
            fetch(`{{ url('/user/product/get-category-options') }}?category_id=${categoryId}`)
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
            
            fetch(`{{ url('/user/product/get-subcategories') }}?category_id=${categoryId}`)
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
            
            fetch(`{{ route('user.product-category.store') }}`, {
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
            
            fetch(`{{ route('user.product-category.store') }}`, {
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
