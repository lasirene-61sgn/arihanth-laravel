@extends('craftsman_staff.layouts.app')

@section('title', 'Create Product')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Create Product</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('craftsman_staff.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('craftsman_staff.product.index') }}">Products</a></li>
                        <li class="breadcrumb-item active">Create</li>
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

                    @php
                        // Resolve craftsman safely from controller passed variable, staff relation, or guard
                        $activeCraftsman = $craftsman 
                            ?? auth()->user()->craftsman 
                            ?? Auth::guard('craftsman_staff')->user()->craftsman 
                            ?? Auth::guard('craftsman')->user() 
                            ?? null;

                        $resolvedCode = $activeCraftsman->craftman_code ?? $activeCraftsman->craftsman_code ?? '';
                        $resolvedBusiness = $activeCraftsman->business_name ?? $activeCraftsman->name ?? '';
                    @endphp

                    <form action="{{ route('craftsman_staff.product.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <!-- Craftsman Code Field -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="craftsman_code" class="form-label">Craftsman Code <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('craftsman_code') is-invalid @enderror" 
                                           id="craftsman_code" 
                                           name="craftsman_code" 
                                           value="{{ old('craftsman_code', $resolvedCode) }}" 
                                           readonly>
                                    @if($resolvedBusiness)
                                        <small class="form-text text-muted">Business: {{ $resolvedBusiness }}</small>
                                    @endif
                                    @error('craftsman_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_code" class="form-label">Product Code</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="product_code" 
                                           name="product_code" 
                                           value="{{ old('product_code') }}"
                                           placeholder="Auto-generated if left blank">
                                    <small class="form-text text-muted">Leave blank to auto-generate</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_name" class="form-label">Product Name</label>
                                    <input type="text" 
                                           class="form-control @error('product_name') is-invalid @enderror" 
                                           id="product_name" 
                                           name="product_name" 
                                           value="{{ old('product_name') }}">
                                    @error('product_name')
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
                                                <option value="{{ $category->id }}" {{ old('product_category_id') == $category->id ? 'selected' : '' }}>
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
                            
                            <div class="col-md-6 mb-3" id="subcategory-container" style="display: none;">
                                <label for="product_subcategory_id" class="form-label">Sub Category</label>
                                <div class="input-group">
                                    <select class="form-control" id="product_subcategory_id" name="product_subcategory_id">
                                        <option value="">Select Sub Category</option>
                                    </select>
                                    <button class="btn btn-outline-secondary" type="button" id="addSubcategoryBtn">New</button>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3 category-option" data-opt="has_open_close" style="display:none;">
                                <label for="open_close" class="form-label">Open/Close</label>
                                <select class="form-control" id="open_close" name="open_close">
                                    <option value="">Select</option>
                                    <option value="Open" {{ old('open_close') == 'Open' ? 'selected' : '' }}>Open</option>
                                    <option value="Close" {{ old('open_close') == 'Close' ? 'selected' : '' }}>Close</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('type') is-invalid @enderror" 
                                            id="type" 
                                            name="type" 
                                            required>
                                        <option value="">Select Type</option>
                                        <option value="Piece" {{ old('type') == 'Piece' ? 'selected' : '' }}>Piece</option>
                                        <option value="Pair" {{ old('type') == 'Pair' ? 'selected' : '' }}>Pair</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="size" class="form-label">Size</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="size" 
                                           name="size" 
                                           value="{{ old('size') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="length" class="form-label">Length</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="length" 
                                           name="length" 
                                           value="{{ old('length') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="weight_from" class="form-label">Weight From</label>
                                    <input type="text" 
                                           step="0.001"
                                           class="form-control" 
                                           id="weight_from" 
                                           name="weight_from" 
                                           value="{{ old('weight_from') }}"
                                           placeholder="0.000">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="weight_to" class="form-label">Weight To</label>
                                    <input type="text" 
                                           step="0.001"
                                           class="form-control" 
                                           id="weight_to" 
                                           name="weight_to" 
                                           value="{{ old('weight_to') }}"
                                           placeholder="0.000">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hallmark" class="form-label">Hallmark</label>
                                    <input type="text" 
                                           class="form-control @error('hallmark') is-invalid @enderror" 
                                           id="hallmark" 
                                           name="hallmark" 
                                           value="{{ old('hallmark') }}">
                                    @error('hallmark')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3 category-option" data-opt="has_rodium" style="display:none;">
                                <label for="rodium" class="form-label">Rodium</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="rodium" 
                                       name="rodium" 
                                       value="{{ old('rodium') }}">
                            </div>
                            
                            <div class="col-md-6 mb-3 category-option" data-opt="has_hook" style="display:none;">
                                <label for="hook" class="form-label">Hook</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="hook" 
                                       name="hook" 
                                       value="{{ old('hook') }}">
                            </div>
                            
                            <div class="col-md-6 mb-3 category-option" data-opt="has_stone" style="display:none;">
                                <label for="stone" class="form-label">Stone</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="stone" 
                                       name="stone" 
                                       value="{{ old('stone') }}">
                            </div>
                            
                            <div class="col-md-6 mb-3 category-option" data-opt="has_enamel" style="display:none;">
                                <label for="enamel" class="form-label">Enamel</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="enamel" 
                                       name="enamel" 
                                       value="{{ old('enamel') }}">
                            </div>
                            
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="images" class="form-label">Product Images (Only white background, *No Logo, No Title.)</label>
                                    <input type="file" 
                                           class="form-control @error('images') is-invalid @enderror" 
                                           id="images" 
                                           name="product_images[]" 
                                           multiple 
                                           accept="image/*">
                                    <small class="text-muted">You can select multiple images (max 2MB each)</small>
                                    @error('images')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('craftsman_staff.product.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Product</button>
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
        const subcategorySelect = document.getElementById('product_subcategory_id');
        const optionBlocks = document.querySelectorAll('.category-option');

        function refreshCategoryOptions(categoryId) {
            optionBlocks.forEach(b => b.style.display = 'none');
            if (!categoryId) return;
            
            fetch(`/craftsman/product-category/get-category-options?category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    Object.keys(data).forEach(key => {
                        if (data[key]) {
                            const el = document.querySelector(`.category-option[data-opt="${key}"]`);
                            if (el) el.style.display = '';
                        }
                    });
                })
                .catch(error => {
                    console.error('Error fetching category options:', error);
                });
        }

        function refreshSubcategories(categoryId) {
            subcategorySelect.innerHTML = '<option value="">Select Sub Category</option>';
            if (!categoryId) { 
                subcategoryContainer.style.display = 'none'; 
                return; 
            }
            
            fetch(`/craftsman/product/category/${categoryId}/subcategories`)
                .then(response => response.json())
                .then(subcategories => {
                    if (subcategories.length > 0) {
                        subcategoryContainer.style.display = 'block';
                        subcategories.forEach(subcategory => {
                            const option = document.createElement('option');
                            option.value = subcategory.id;
                            option.textContent = subcategory.name;
                            subcategorySelect.appendChild(option);
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

        // Initialize dynamic options and subcategories on load if category is preselected
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
                has_stone: confirm('Enable Stone option?') ? 1 : 0,
            };
            
            fetch('{{ route('craftsman_staff.product-category.store') }}', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                },
                body: JSON.stringify({ name, ...opts })
            })
            .then(response => response.json())
            .then(res => {
                if (res.status === 'success' && res.category) {
                    const opt = document.createElement('option');
                    opt.value = res.category.id; 
                    opt.textContent = res.category.name; 
                    opt.selected = true;
                    categorySelect.appendChild(opt);
                    refreshCategoryOptions(res.category.id);
                    document.getElementById('subcategory-container').style.display = 'block';
                    document.getElementById('product_subcategory_id').innerHTML = '<option value="">Select Sub Category</option>';
                } else {
                    alert('Failed to create category');
                }
            })
            .catch(error => {
                console.error('Error creating category:', error);
                alert('Failed to create category');
            });
        });

        // Add Subcategory
        document.getElementById('addSubcategoryBtn').addEventListener('click', function() {
            const parentId = categorySelect.value;
            if (!parentId) { 
                alert('Select a category first'); 
                return; 
            }
            const name = prompt('Enter new subcategory name');
            if (!name) return;
            
            fetch('{{ route('craftsman_staff.product-category.store') }}', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                },
                body: JSON.stringify({ parent_category_id: parentId, name })
            })
            .then(response => response.json())
            .then(res => {
                if (res.status === 'success' && res.subcategory) {
                    const opt = document.createElement('option');
                    opt.value = res.subcategory.id; 
                    opt.textContent = res.subcategory.name; 
                    opt.selected = true;
                    subcategorySelect.appendChild(opt);
                } else {
                    alert('Failed to create subcategory');
                }
            })
            .catch(error => {
                console.error('Error creating subcategory:', error);
                alert('Failed to create subcategory');
            });
        });
    });
</script>
@endsection