@extends('craftsman_staff.layouts.app')
@section('title', 'Edit Product')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header"><h4>Edit Product: {{ $product->product_code }}</h4></div>
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
            <form action="{{ route('craftsman_staff.product.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="product_name" class="form-control" value="{{ old('product_name', $product->product_name) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category</label>
                        <select name="product_category_id" id="product_category_id" class="form-control" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $product->product_category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3" id="subcategory-container" style="display: {{ $subcategories->count() > 0 ? 'block' : 'none' }};">
                        <label class="form-label">Sub Category</label>
                        <select name="product_subcategory_id" id="product_subcategory_id" class="form-control">
                            <option value="">Select Sub Category</option>
                            @foreach($subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}" {{ $product->product_subcategory_id == $subcategory->id ? 'selected' : '' }}>{{ $subcategory->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-control">
                            <option value="Piece" {{ $product->type == 'Piece' ? 'selected' : '' }}>Piece</option>
                            <option value="Pair" {{ $product->type == 'Pair' ? 'selected' : '' }}>Pair</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Weight From (g)</label>
                        <input type="text" step="0.01" name="weight_from" class="form-control" value="{{ $product->weight_from }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Weight To (g)</label>
                        <input type="text" step="0.01" name="weight_to" class="form-control" value="{{ $product->weight_to }}">
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Add Product Images</label>
                        <input type="file" name="product_images[]" class="form-control" multiple accept="image/*">
                        <small class="text-muted">You can select multiple images to add</small>
                        
                        <div class="mt-3">
                            <label class="form-label d-block">Current Images:</label>
                            <div class="row g-2">
                                @foreach($product->images as $image)
                                    <div class="col-auto">
                                        <div class="position-relative">
                                            <img src="{{ asset('storage/' . $image->path) }}" 
                                                 alt="Current Image" 
                                                 class="img-thumbnail"
                                                 style="height: 100px; width: 100px; object-fit: cover;">
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
                <div class="text-end">
                    <a href="{{ route('craftsman_staff.product.index') }}" class="btn btn-light">Back</a>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('product_category_id');
        const subcategoryContainer = document.getElementById('subcategory-container');
        const subcategorySelect = document.getElementById('product_subcategory_id');

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
                        
                        // Set the originally selected value if it exists
                        const originalValue = '{{ $product->product_subcategory_id }}';
                        if (originalValue) {
                            subcategorySelect.value = originalValue;
                        }
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
            refreshSubcategories(this.value);
        });
    });
</script>

@endsection
