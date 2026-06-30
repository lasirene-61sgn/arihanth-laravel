@extends('user.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Create Work Order</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('user.work-order.index') }}">Work Orders</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Custom Searchable Dropdown Styles (Bootstrap Version) */
        .custom-dropdown-container {
            position: relative;
            width: 100%;
        }
        .custom-dropdown-display {
            width: 100%;
            padding: 0.47rem 0.75rem;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .custom-dropdown-display:after {
            content: "";
            border-top: .3em solid;
            border-right: .3em solid transparent;
            border-bottom: 0;
            border-left: .3em solid transparent;
            margin-left: .255em;
            vertical-align: .255em;
        }
        .custom-dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            z-index: 1000;
            display: none;
            padding: 0.5rem;
        }
        .custom-dropdown-search {
            width: 100%;
            padding: 0.375rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            outline: none;
        }
        .custom-dropdown-list {
            max-height: 200px;
            overflow-y: auto;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .custom-dropdown-item {
            padding: 0.375rem 0.75rem;
            cursor: pointer;
            font-size: 0.875rem;
            border-radius: 0.2rem;
        }
        .custom-dropdown-item:hover {
            background-color: #f8f9fa;
        }
        .custom-dropdown-item.selected {
            background-color: #0d6efd;
            color: #fff;
        }
        .custom-dropdown-item.hidden {
            display: none;
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">New Work Order</h4>
                </div>
                <div class="card-body">
                    <form id="workOrderForm" action="{{ route('user.work-order.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bp_code" class="form-label">BP Code</label>
                                    <input type="text" class="form-control" id="bp_code" name="bp_code" value="{{ auth()->user()->bp_code ?? '' }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_name" class="form-label">Customer Name</label>
                                    <input type="text" class="form-control" id="customer_name" name="customer_name" value="{{ $buyer->business_name ?? '' }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_code" class="form-label">Product Code / Design Code</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="product_code" name="product_code" placeholder="Enter product code or design code" value="{{ old('product_code') }}" autocomplete="off">
                                        <button class="btn btn-outline-secondary" type="button" id="test_lookup_btn">Test</button>
                                    </div>
                                    <div id="lookupFeedback" class="mt-2" style="display: none;">
                                        <div class="alert alert-info" id="lookupResult"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_name" class="form-label">Product Name</label>
                                    <input type="text" class="form-control" id="product_name" name="product_name" placeholder="Product name" value="{{ old('product_name') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_category_id" class="form-label">Product Category</label>
                                    <div class="custom-dropdown-container" id="category_container">
                                        <div class="custom-dropdown-display" id="category_display">--Select Category--</div>
                                        <div class="custom-dropdown-menu" id="category_menu">
                                            <input type="text" class="custom-dropdown-search" id="category_search" placeholder="Search categories...">
                                            <ul class="custom-dropdown-list" id="category_list">
                                                <li class="custom-dropdown-item" data-value="">--Select Category--</li>
                                                @foreach($categories as $category)
                                                    <li class="custom-dropdown-item" data-value="{{ $category->id }}">
                                                        {{ $category->name }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <select name="product_category_id" id="product_category_id" style="display: none;" required>
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('product_category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subcategory_id" class="form-label">Subcategory</label>
                                    <div class="custom-dropdown-container" id="subcategory_container">
                                        <div class="custom-dropdown-display" id="subcategory_display">--Select Subcategory--</div>
                                        <div class="custom-dropdown-menu" id="subcategory_menu">
                                            <input type="text" class="custom-dropdown-search" id="subcategory_search" placeholder="Search subcategories...">
                                            <ul class="custom-dropdown-list" id="subcategory_list">
                                                <li class="custom-dropdown-item" data-value="">--Select Subcategory--</li>
                                            </ul>
                                        </div>
                                        <select name="subcategory_id" id="subcategory_id" style="display: none;">
                                            <option value="">Select Subcategory</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Quantity" value="{{ old('quantity') }}" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="due_date" class="form-label">Due Date</label>
                                    <input type="date" class="form-control" id="due_date" name="due_date" value="{{ old('due_date') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type</label>
                                    <select class="form-select" id="type" name="type">
                                        <option value="">Select Type</option>
                                        <option value="Piece" {{ old('type') == 'Piece' ? 'selected' : '' }}>Piece</option>
                                        <option value="Pair" {{ old('type') == 'Pair' ? 'selected' : '' }}>Pair</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="open_close" class="form-label">Open/Close</label>
                                    <select class="form-select" id="open_close" name="open_close">
                                        <option value="">Select Open/Close</option>
                                        <option value="Open" {{ old('open_close') == 'Open' ? 'selected' : '' }}>Open</option>
                                        <option value="Close" {{ old('open_close') == 'Close' ? 'selected' : '' }}>Close</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="weight_from" class="form-label">Weight From</label>
                                    <input type="number" class="form-control" id="weight_from" name="weight_from" placeholder="Weight From" step="0.01" value="{{ old('weight_from') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="weight_to" class="form-label">Weight To</label>
                                    <input type="number" class="form-control" id="weight_to" name="weight_to" placeholder="Weight To" step="0.01" value="{{ old('weight_to') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reference_no" class="form-label">Reference No</label>
                                    <input type="text" class="form-control" id="reference_no" name="reference_no" placeholder="Reference No" value="{{ old('reference_no') }}">
                                </div>
                            </div>
                            <!-- <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="relabel_code" class="form-label">Relabel Code</label>
                                    <input type="text" class="form-control" id="relabel_code" name="relabel_code" placeholder="Relabel Code" value="{{ old('relabel_code') }}">
                                </div>
                            </div> -->
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hallmark" class="form-label">Hallmark</label>
                                    <select class="form-select" id="hallmark" name="hallmark">
                                        <option value="">Select Hallmark</option>
                                        <option value="Yes" {{ old('hallmark') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                        <option value="No" {{ old('hallmark') == 'No' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="rodium" class="form-label">Rodium</label>
                                    <select class="form-select" id="rodium" name="rodium">
                                        <option value="">Select Rodium</option>
                                        <option value="Yes" {{ old('rodium') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                        <option value="No" {{ old('rodium') == 'No' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hook" class="form-label">Hook</label>
                                    <select class="form-select" id="hook" name="hook">
                                        <option value="">Select Hook</option>
                                        <option value="Yes" {{ old('hook') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                        <option value="No" {{ old('hook') == 'No' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="size" class="form-label">Size</label>
                                    <input type="text" class="form-control" id="size" name="size" placeholder="Size" value="{{ old('size') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="stone" class="form-label">Stone</label>
                                    <select class="form-select" id="stone" name="stone">
                                        <option value="">Select Stone</option>
                                        <option value="Yes" {{ old('stone') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                        <option value="No" {{ old('stone') == 'No' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="enamel" class="form-label">Enamel</label>
                                    <select class="form-select" id="enamel" name="enamel">
                                        <option value="">Select Enamel</option>
                                        <option value="Yes" {{ old('enamel') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                        <option value="No" {{ old('enamel') == 'No' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="length" class="form-label">Length</label>
                                    <input type="text" class="form-control" id="length" name="length" placeholder="Length" value="{{ old('length') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="narration_admin" class="form-label">Narration</label>
                                    <textarea class="form-control" id="narration_admin" name="narration_admin" rows="3" placeholder="Additional notes">{{ old('narration_admin') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="product_images" class="form-label">Product Images (Supports Multiple)</label>
                            <input type="file" class="form-control" id="product_images" name="product_images[]" accept="image/*" multiple>
                            <div class="form-text">You can select and upload multiple images at once.</div>
                            <div id="multi_image_preview_container" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>

                        <div class="mb-3">
                            <div id="imagePreviewContainer" style="display: none;">
                                <label class="form-label">Current Image</label>
                                <div id="imagePreview"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('user.work-order.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Work Order</button>
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
    const subcategorySelect = document.getElementById('subcategory_id');
    const productCodeInput = document.getElementById('product_code');
    const productNameInput = document.getElementById('product_name');
    const lookupResult = document.getElementById('lookupResult');
    const lookupFeedback = document.getElementById('lookupFeedback');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const imagePreview = document.getElementById('imagePreview');

    // Generic Searchable Dropdown Initialization
    function initSearchableDropdown(containerId, displayId, menuId, searchInputId, listId, hiddenSelectId, placeholder, onSelect = null) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const display = document.getElementById(displayId);
        const menu = document.getElementById(menuId);
        const searchInput = document.getElementById(searchInputId);
        const listContainer = document.getElementById(listId);
        const hiddenSelect = document.getElementById(hiddenSelectId);

        function getListItems() {
            return listContainer.querySelectorAll('.custom-dropdown-item');
        }

        display.addEventListener('click', function() {
            const isVisible = menu.style.display === 'block';
            menu.style.display = isVisible ? 'none' : 'block';
            if (!isVisible) {
                searchInput.focus();
                searchInput.value = '';
                filterItems('');
            }
        });

        searchInput.addEventListener('input', function() {
            filterItems(this.value.toLowerCase());
        });

        function filterItems(query) {
            getListItems().forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(query)) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        }

        listContainer.addEventListener('click', function(e) {
            const item = e.target.closest('.custom-dropdown-item');
            if (!item) return;

            const val = item.dataset.value;
            const text = item.textContent.trim();
            
            display.textContent = val ? text : placeholder;
            hiddenSelect.value = val;
            
            hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
            
            getListItems().forEach(i => i.classList.remove('selected'));
            item.classList.add('selected');
            
            menu.style.display = 'none';

            if (onSelect) {
                onSelect(val, item);
            }
        });

        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                menu.style.display = 'none';
            }
        });

        // Initialize display if value already set
        if (hiddenSelect.value) {
            const selectedItem = Array.from(getListItems()).find(i => i.dataset.value === hiddenSelect.value);
            if (selectedItem) {
                display.textContent = selectedItem.textContent.trim();
                selectedItem.classList.add('selected');
            }
        }
    }

    // Subcategory fetch logic
    function refreshSubcategories(categoryId) {
        subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
        const listContainer = document.getElementById('subcategory_list');
        const display = document.getElementById('subcategory_display');
        listContainer.innerHTML = '<li class="custom-dropdown-item" data-value="">--Select Subcategory--</li>';
        display.textContent = '--Select Subcategory--';

        if (!categoryId) return Promise.resolve();

        return fetch(`/user/product/get-subcategories?category_id=${categoryId}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(s => {
                    // Update hidden select
                    const option = document.createElement('option');
                    option.value = s.id;
                    option.textContent = s.name;
                    subcategorySelect.appendChild(option);

                    // Update custom list
                    const li = document.createElement('li');
                    li.className = 'custom-dropdown-item';
                    li.dataset.value = s.id;
                    li.textContent = s.name;
                    listContainer.appendChild(li);
                });
                return data;
            })
            .catch(error => console.error('Error fetching subcategories:', error));
    }

    // MAIN LOOKUP LOGIC
    function performLookup(productCode) {
        if (!productCode) {
            lookupFeedback.style.display = 'none';
            return;
        }

        lookupResult.className = 'alert alert-info';
        lookupResult.textContent = 'Looking up...';
        lookupFeedback.style.display = 'block';

        fetch(`/user/work-order/get-product-details?product_code=${encodeURIComponent(productCode)}`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const fields = [
                        'product_name', 'type', 'open_close', 'weight_from', 'weight_to', 
                        'hallmark', 'rodium', 'hook', 'size', 'stone', 'enamel', 'length'
                    ];
                    fields.forEach(field => {
                        const el = document.getElementById(field);
                        if (el) el.value = data.product[field] || '';
                    });
                    
                    if (data.product.product_category_id) {
                        categorySelect.value = data.product.product_category_id;
                        
                        // Update Category Display
                        const categoryItem = document.querySelector(`#category_list .custom-dropdown-item[data-value="${data.product.product_category_id}"]`);
                        if (categoryItem) {
                            document.getElementById('category_display').textContent = categoryItem.textContent.trim();
                            document.querySelectorAll('#category_list .custom-dropdown-item').forEach(i => i.classList.remove('selected'));
                            categoryItem.classList.add('selected');
                        }

                        refreshSubcategories(data.product.product_category_id).then(() => {
                            if (data.product.subcategory_id) {
                                subcategorySelect.value = data.product.subcategory_id;
                                
                                // Update Subcategory Display
                                const subItem = document.querySelector(`#subcategory_list .custom-dropdown-item[data-value="${data.product.subcategory_id}"]`);
                                if (subItem) {
                                    document.getElementById('subcategory_display').textContent = subItem.textContent.trim();
                                    document.querySelectorAll('#subcategory_list .custom-dropdown-item').forEach(i => i.classList.remove('selected'));
                                    subItem.classList.add('selected');
                                }
                            }
                        });
                    }
                    
                    if (data.product.product_image_url) {
                        imagePreview.innerHTML = `<img src="${data.product.product_image_url}" alt="Product Preview" style="max-width: 200px; max-height: 200px;">`;
                        imagePreviewContainer.style.display = 'block';
                    } else {
                        imagePreviewContainer.style.display = 'none';
                    }
                    
                    lookupResult.className = 'alert alert-success';
                    lookupResult.textContent = 'Product details loaded successfully!';
                } else {
                    lookupResult.className = 'alert alert-danger';
                    lookupResult.textContent = data.message || 'Product not found';
                    imagePreviewContainer.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Lookup error:', error);
                lookupResult.className = 'alert alert-danger';
                lookupResult.textContent = 'Error occurred while fetching product details';
            });
    }

    productCodeInput.addEventListener('blur', function() {
        performLookup(this.value.trim());
    });

    document.getElementById('test_lookup_btn').addEventListener('click', function() {
        performLookup(productCodeInput.value.trim());
    });

    productCodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performLookup(this.value.trim());
        }
    });

    // Multi-image preview
    const multiImageInput = document.getElementById('product_images');
    const multiPreviewContainer = document.getElementById('multi_image_preview_container');

    if (multiImageInput) {
        multiImageInput.addEventListener('change', function() {
            multiPreviewContainer.innerHTML = '';
            if (this.files) {
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'border rounded p-1 bg-light shadow-sm';
                        div.style.width = '100px';
                        div.style.height = '100px';
                        div.innerHTML = `<img src="${e.target.result}" class="w-100 h-100 object-fit-cover rounded">`;
                        multiPreviewContainer.appendChild(div);
                    }
                    reader.readAsDataURL(file);
                });
            }
        });
    }

    // Initialize Category dropdown
    initSearchableDropdown(
        'category_container', 'category_display', 'category_menu', 'category_search', 'category_list', 'product_category_id', '--Select Category--',
        (val) => refreshSubcategories(val)
    );

    // Initialize Subcategory dropdown
    initSearchableDropdown(
        'subcategory_container', 'subcategory_display', 'subcategory_menu', 'subcategory_search', 'subcategory_list', 'subcategory_id', '--Select Subcategory--'
    );
});
</script>
@endsection