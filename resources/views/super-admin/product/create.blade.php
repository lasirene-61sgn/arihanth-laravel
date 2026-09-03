@extends('super-admin.layouts.app')

@section('title', 'Add New Product')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/superadminproduct.css') }}">
@endsection

@section('scripts')
<script>
    window.ProductAppConfig = {
        getCategoryOptionsUrl: "{{ url('/super-admin/product/get-category-options') }}",
        getSubcategoriesUrl: "{{ url('/super-admin/product/get-subcategories') }}",
        storeCategoryUrl: "{{ route('super-admin.product-category.store') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('js/superadminproductcreate.js') }}"></script>
@endsection

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add New Product</h1>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h4 class="mb-0">Product Information</h4>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('super-admin.product.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="product_code" class="form-label">Product Code </label>
                    <input type="text" class="form-control" id="product_code" name="product_code" value="{{ old('product_code') }}">
                </div>
                <!-- <div class="col-md-6 mb-3">
                    <label for="relabel_code" class="form-label">Relabel Code</label>
                    <input type="text" class="form-control" id="relabel_code" name="relabel_code" value="{{ old('relabel_code') }}">
                </div> -->
                <div class="col-md-6 mb-3">
                    <label for="product_name" class="form-label">Product Name </label>
                    <input type="text" class="form-control" id="product_name" name="product_name" value="{{ old('product_name') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="craftsman_code" class="form-label">Craftsman</label>
                    <div class="custom-dropdown-container" id="dropdown_container_craftsman_code">
                        <div class="custom-dropdown-display" id="dropdown_display_craftsman_code">Select Craftsman</div>
                        <div class="custom-dropdown-menu" id="dropdown_menu_craftsman_code" style="display: none;">
                            <input type="text" class="custom-dropdown-search" placeholder="Search Craftsman...">
                            <div class="custom-dropdown-list">
                                @foreach(\App\Models\Craftman::orderBy('name')->get() as $craftsman)
                                <div class="custom-dropdown-item" data-value="{{ $craftsman->craftman_code }}">
                                    {{ $craftsman->craftman_code }} - {{ $craftsman->name }}
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <select class="form-select" id="craftsman_code" name="craftsman_code" style="display: none;">
                            <option value="">Select Craftsman</option>
                            @foreach(\App\Models\Craftman::orderBy('name')->get() as $craftsman)
                            <option value="{{ $craftsman->craftman_code }}" {{ old('craftsman_code') == $craftsman->craftman_code ? 'selected' : '' }}>
                                {{ $craftsman->craftman_code }} - {{ $craftsman->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="bp_code" class="form-label">BP Code</label>
                    <div class="custom-dropdown-container" id="dropdown_container_bp_code">
                        <div class="custom-dropdown-display" id="dropdown_display_bp_code">Select BP Code</div>
                        <div class="custom-dropdown-menu" id="dropdown_menu_bp_code" style="display: none;">
                            <input type="text" class="custom-dropdown-search" placeholder="Search BP Code...">
                            <div class="custom-dropdown-list">
                                @foreach(\App\Models\Buyer::all() as $buyer)
                                <div class="custom-dropdown-item" data-value="{{ $buyer->bp_code }}">{{ $buyer->bp_code }} - {{ $buyer->business_name }}</div>
                                @endforeach
                            </div>
                        </div>
                        <select class="form-select" id="bp_code" name="bp_code" style="display: none;">
                            <option value="">Select BP Code</option>
                            @foreach(\App\Models\Buyer::all() as $buyer)
                            <option value="{{ $buyer->bp_code }}" {{ old('bp_code') == $buyer->bp_code ? 'selected' : '' }}>
                                {{ $buyer->bp_code }} - {{ $buyer->business_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="product_category_id" class="form-label">Product Category *</label>
                    <div class="input-group">
                        <div class="custom-dropdown-container flex-grow-1" id="dropdown_container_product_category_id">
                            <div class="custom-dropdown-display" id="dropdown_display_product_category_id">Select Category</div>
                            <div class="custom-dropdown-menu" id="dropdown_menu_product_category_id" style="display: none;">
                                <input type="text" class="custom-dropdown-search" placeholder="Search Category...">
                                <div class="custom-dropdown-list">
                                    @foreach($categories as $category)
                                    <div class="custom-dropdown-item" data-value="{{ $category->id }}">{{ $category->name }}</div>
                                    @endforeach
                                </div>
                            </div>
                            <select class="form-select" id="product_category_id" name="product_category_id" style="display: none;" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('product_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-outline-secondary" type="button" id="addCategoryBtn">New</button>
                    </div>
                </div>

                <div class="col-md-6 mb-3" id="subcategory-container" style="display: none;">
                    <label for="subcategory_id" class="form-label">Sub Category</label>
                    <div class="input-group">
                        <div class="custom-dropdown-container flex-grow-1" id="dropdown_container_subcategory_id">
                            <div class="custom-dropdown-display" id="dropdown_display_subcategory_id">Select Sub Category</div>
                            <div class="custom-dropdown-menu" id="dropdown_menu_subcategory_id" style="display: none;">
                                <input type="text" class="custom-dropdown-search" placeholder="Search Sub Category...">
                                <div class="custom-dropdown-list">
                                    <!-- Dynamic items -->
                                </div>
                            </div>
                            <select class="form-select" id="subcategory_id" name="subcategory_id" style="display: none;">
                                <option value="">Select Sub Category</option>
                            </select>
                        </div>
                        <button class="btn btn-outline-secondary" type="button" id="addSubcategoryBtn">New</button>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="type" class="form-label">Type *</label>
                    <select class="form-select" id="type" name="type" required>
                        <option value="Piece" {{ old('type') == 'Piece' ? 'selected' : '' }}>Piece</option>
                        <option value="Pair" {{ old('type') == 'Pair' ? 'selected' : '' }}>Pair</option>
                    </select>
                </div>

                <!-- <div class="col-md-6 mb-3">
                    <label for="order_type" class="form-label">Order Type *</label>
                    <select class="form-select" id="order_type" name="order_type" required>
                        <option value="Regular" {{ old('order_type') == 'Regular' ? 'selected' : '' }}>Regular</option>
                        <option value="Urgent" {{ old('order_type') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="Super Urgent" {{ old('order_type') == 'Super Urgent' ? 'selected' : '' }}>Super Urgent</option>
                    </select>
                </div> -->

                <div class="col-md-6 mb-3 category-option" data-opt="has_open_close" style="display:none;">
                    <label for="open_close" class="form-label">Open/Close</label>
                    <select class="form-select" id="open_close" name="open_close">
                        <option value="">Select</option>
                        <option value="Open" {{ old('open_close') == 'Open' ? 'selected' : '' }}>Open</option>
                        <option value="Close" {{ old('open_close') == 'Close' ? 'selected' : '' }}>Close</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3 category-option" data-opt="has_hook" style="display:none;">
                    <label for="hook" class="form-label">Hook</label>
                    <input type="text" class="form-control" id="hook" name="hook" value="{{ old('hook') }}">
                </div>

                <div class="col-md-6 mb-3 category-option" data-opt="has_enamel" style="display:none;">
                    <label for="enamel" class="form-label">Enamel</label>
                    <input type="text" class="form-control" id="enamel" name="enamel" value="{{ old('enamel') }}">
                </div>

                <div class="col-md-6 mb-3 category-option" data-opt="has_rodium" style="display:none;">
                    <label for="rodium" class="form-label">Rodium</label>
                    <input type="text" class="form-control" id="rodium" name="rodium" value="{{ old('rodium') }}">
                </div>

                <div class="col-md-6 mb-3 category-option" data-opt="has_stone" style="display:none;">
                    <label for="stone" class="form-label">Stone</label>
                    <input type="text" class="form-control" id="stone" name="stone" value="{{ old('stone') }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="size" class="form-label">Size</label>
                    <input type="text" class="form-control" id="size" name="size" value="{{ old('size') }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="length" class="form-label">Length</label>
                    <input type="text" class="form-control" id="length" name="length" value="{{ old('length') }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="weight_from" class="form-label">Weight From</label>
                    <input type="text" step="0.001" class="form-control" id="weight_from" name="weight_from" value="{{ old('weight_from') }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="weight_to" class="form-label">Weight To</label>
                    <input type="text" step="0.001" class="form-control" id="weight_to" name="weight_to" value="{{ old('weight_to') }}">
                </div>

                <div class="col-md-12 mb-3">
                    <label for="product_images" class="form-label fw-bold text-primary">Product Images (Only white background, *No Logo, No Title .)</label>
                    <input type="file" class="form-control" id="product_images" name="product_images[]" accept="image/*" multiple>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('super-admin.product.index') }}" class="btn btn-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">Create Product</button>
            </div>
        </form>
    </div>
</div>

@endsection