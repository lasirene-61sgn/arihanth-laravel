@extends('super-admin.layouts.app')

@section('title', 'Create Work Order')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.375rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 12px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
    
    /* Custom Searchable Dropdown Styles */
    .custom-dropdown-container {
        position: relative;
        width: 100%;
    }
    .custom-dropdown-display {
        width: 100%;
        height: 38px;
        padding: 6px 12px;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background-color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        font-size: 1rem;
    }
    .custom-dropdown-display:after {
        content: "\F282";
        font-family: "bootstrap-icons";
        font-size: 0.8rem;
    }
    .custom-dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: #fff;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 0.375rem 0.375rem;
        z-index: 1050;
        display: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        padding: 10px;
    }
    .custom-dropdown-search {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }
    .custom-dropdown-search:focus {
        outline: none;
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .custom-dropdown-list {
        max-height: 200px;
        overflow-y: auto;
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .custom-dropdown-item {
        padding: 8px 12px;
        cursor: pointer;
        font-size: 0.95rem;
        border-radius: 0.25rem;
    }
    .custom-dropdown-item:hover {
        background-color: #f8f9fa;
    }
    .custom-dropdown-item.selected {
        background-color: #e9ecef;
        font-weight: bold;
    }
    .custom-dropdown-item.hidden {
        display: none;
    }

    /* Multiple Image Preview Style */
    #multi_image_preview_container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }
    .preview-item {
        position: relative;
        width: 100px;
        height: 100px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        overflow: hidden;
    }
    .preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Create New Work Order</h1>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Work Order Details</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('super-admin.work-order.store') }}" method="POST" enctype="multipart/form-data" id="workOrderForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="product_code" class="form-label">Product / Design Code Lookup</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="product_code" name="product_code" value="{{ old('product_code') }}" placeholder="Enter code to lookup">
                                    <button class="btn btn-outline-secondary" type="button" id="test_lookup_btn">Test</button>
                                </div>
                                <small class="text-info" id="lookup_status">Enter code for existing product, or leave blank for new.</small>
                            </div>

                            <div class="col-md-2 mb-3 text-center">
                                <label class="form-label">Image Preview</label>
                                <div id="design_image_container" class="border rounded bg-light d-flex align-items-center justify-content-center" style="height: 100px; overflow: hidden;">
                                    <span id="no_image_text" class="text-muted small">None</span>
                                    <img id="design_image_preview" src="" style="display: none; max-height: 100%; max-width: 100%; object-fit: contain;">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="design_code" class="form-label">Design Code</label>
                                <input type="text" class="form-control" id="design_code" name="design_code" readonly style="background-color: #e9ecef;">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="bp_code" class="form-label">BP Code</label>
                                <div class="custom-dropdown-container" id="bp_code_container">
                                    <div class="custom-dropdown-display" id="bp_code_display">--Select BP Code--</div>
                                    <div class="custom-dropdown-menu" id="bp_code_menu">
                                        <input type="text" class="custom-dropdown-search" id="bp_code_search" placeholder="Search for an item...">
                                        <ul class="custom-dropdown-list" id="bp_code_list">
                                            <li class="custom-dropdown-item" data-value="">--Select BP Code--</li>
                                            @foreach($buyers as $buyer)
                                                <li class="custom-dropdown-item" data-value="{{ $buyer->bp_code }}" data-name="{{ $buyer->business_name }}">
                                                    {{ $buyer->bp_code }} - {{ $buyer->business_name }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    {{-- Hidden select for form submission --}}
                                    <select name="bp_code" id="bp_code" style="display: none;">
                                        <option value="">Select Buyer BP Code</option>
                                        @foreach($buyers as $buyer)
                                            <option value="{{ $buyer->bp_code }}" data-name="{{ $buyer->business_name }}">{{ $buyer->bp_code }} - {{ $buyer->business_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="customer_name" class="form-label">Customer Name *</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                            </div>
                            
                             <div class="col-md-6 mb-3">
                                <label for="product_category_id" class="form-label">Product Category *</label>
                                <div class="custom-dropdown-container" id="category_container">
                                    <div class="custom-dropdown-display" id="category_display">--Select Category--</div>
                                    <div class="custom-dropdown-menu" id="category_menu">
                                        <div class="p-2 border-bottom d-flex gap-2">
                                            <input type="text" class="custom-dropdown-search flex-grow-1" id="category_search" placeholder="Search categories...">
                                            <button class="btn btn-sm btn-primary" type="button" id="addCategoryBtn">New</button>
                                        </div>
                                        <ul class="custom-dropdown-list" id="category_list">
                                            <li class="custom-dropdown-item" data-value="">--Select Category--</li>
                                            @foreach($categories as $category)
                                                <li class="custom-dropdown-item" data-value="{{ $category->id }}">
                                                    {{ $category->name }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    {{-- Hidden select for form submission --}}
                                    <select name="product_category_id" id="product_category_id" style="display: none;" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3" id="subcategory-container" style="display: none;">
                                <label for="subcategory_id" class="form-label">Sub Category</label>
                                <div class="custom-dropdown-container" id="subcategory_container">
                                    <div class="custom-dropdown-display" id="subcategory_display">--Select Sub Category--</div>
                                    <div class="custom-dropdown-menu" id="subcategory_menu">
                                        <div class="p-2 border-bottom d-flex gap-2">
                                            <input type="text" class="custom-dropdown-search flex-grow-1" id="subcategory_search" placeholder="Search subcategories...">
                                            <button class="btn btn-sm btn-primary" type="button" id="addSubcategoryBtn">New</button>
                                        </div>
                                        <ul class="custom-dropdown-list" id="subcategory_list">
                                            <li class="custom-dropdown-item" data-value="">--Select Sub Category--</li>
                                        </ul>
                                    </div>
                                    {{-- Hidden select for form submission --}}
                                    <select name="subcategory_id" id="subcategory_id" style="display: none;">
                                        <option value="">Select Sub Category</option>
                                    </select>
                                </div>
                            </div>

                            
                            
                            <div class="col-md-6 mb-3 category-option" data-opt="has_open_close" style="display:none;">
                                <label for="open_close" class="form-label">Open/Close</label>
                                <select class="form-select" id="open_close" name="open_close">
                                    <option value="">Select Status</option>
                                    <option value="Open">Open</option>
                                    <option value="Close">Close</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3 category-option" data-opt="hook" style="display:none;">
                                <label for="hook" class="form-label">Hook</label>
                                <select class="form-select" id="hook" name="hook">
                                    <option value="">Select Status</option>
                                    <option value="Single">Single</option>
                                    <option value="Double">Double</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3 category-option" data-opt="enamel" style="display:none;">
                                <label for="enamel" class="form-label">Enamel</label>
                                <select class="form-select" id="enamel" name="enamel">
                                    <option value="">Select Status</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3 category-option" data-opt="rodium" style="display:none;">
                                <label for="rodium" class="form-label">Rodium</label>
                                <select class="form-select" id="rodium" name="rodium">
                                    <option value="">Select Status</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3 category-option" data-opt="has_stone" style="display:none;">
                                <label for="has_stone" class="form-label">Stone</label>
                                <select class="form-select" id="has_stone" name="has_stone">
                                    <option value="">Select Status</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <!-- <div class="col-md-6 mb-3 category-option" data-opt="has_hook" style="display:none;">
                                <label for="hook" class="form-label">Hook</label>
                                <input type="text" class="form-control" id="hook" name="hook">
                            </div>

                            <div class="col-md-6 mb-3 category-option" data-opt="has_enamel" style="display:none;">
                                <label for="enamel" class="form-label">Enamel</label>
                                <input type="text" class="form-control" id="enamel" name="enamel">
                            </div> -->

                            <!-- <div class="col-md-6 mb-3 category-option" data-opt="has_rodium" style="display:none;">
                                <label for="rodium" class="form-label">Rodium</label>
                                <input type="text" class="form-control" id="rodium" name="rodium">
                            </div> -->

                            <div class="col-md-6 mb-3 category-option" data-opt="has_stone" style="display:none;">
                                <label for="stone" class="form-label">Stone</label>
                                <input type="text" class="form-control" id="stone" name="stone">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="product_name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="product_name" name="product_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">Quantity *</label>
                                <input type="text" class="form-control" id="quantity" name="quantity" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="">Select Type</option>
                                    <option value="Piece">Piece</option>
                                    <option value="Pair">Pair</option>
                                    <option value="Set">Set</option>

                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="screw_name" class="form-label">Screw</label>
                                <select class="form-select" id="screw_name" name="screw_name">
                                    <option value="">Select Screw</option>
                                    <option value="North Screw">North Screw</option>
                                    <option value="South Screw">South Screw</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="due_date" class="form-label">Customer Due Date *</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="reference_no" class="form-label">Reference No</label>
                                <input type="text" class="form-control" id="reference_no" name="reference_no">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="weight_from" class="form-label">Weight From *</label>
                                <input type="text" class="form-control" id="weight_from" name="weight_from" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="weight_to" class="form-label">Weight To *</label>
                                <input type="text" class="form-control" id="weight_to" name="weight_to" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="size" class="form-label">Size</label>
                                <input type="text" class="form-control" id="size" name="size">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="length" class="form-label">Length</label>
                                <input type="text" class="form-control" id="length" name="length">
                            </div>

                            <div class="col-md-6 mb-3 category-option" data-opt="hallmark" style="display:none;">
                                <label for="hallmark" class="form-label">Hallmark</label>
                                <select class="form-select" id="hallmark" name="hallmark">
                                    <option value="">Select Status</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <!-- <div class="col-md-4 mb-3">
                                <label for="hallmark" class="form-label">HUID</label>
                                <input type="text" class="form-control" id="hallmark" name="hallmark">
                            </div> -->
                            <div class="col-md-4 mb-3">
                                <label for="craftsman_due_date" class="form-label">Craftsman Due Date</label>
                                <input type="date" class="form-control" id="craftsman_due_date" name="craftsman_due_date">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="product_images" class="form-label">Product Images (Supports Multiple)</label>
                                <input type="file" class="form-control" id="product_images" name="product_images[]" accept="image/*" multiple>
                                <div id="multi_image_preview_container"></div>
                                <small class="text-muted">You can select and upload multiple images at once.</small>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="narration_craftsman" class="form-label">Narration (Craftsman)</label>
                                <textarea class="form-control" id="narration_craftsman" name="narration_craftsman" rows="2"></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="narration_admin" class="form-label">Narration (Admin)</label>
                                <textarea class="form-control" id="narration_admin" name="narration_admin" rows="2"></textarea>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('super-admin.work-order.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Work Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        const categorySelect = document.getElementById('product_category_id');
        const subcategoryContainer = document.getElementById('subcategory-container');
        const subcategorySelect = document.getElementById('subcategory_id');
        const bpCodeSelect = document.getElementById('bp_code');
        const customerNameInput = document.getElementById('customer_name');
        const optionBlocks = document.querySelectorAll('.category-option');
        const productCodeInput = document.getElementById('product_code');
        const designCodeInput = document.getElementById('design_code');
        const designImagePreview = document.getElementById('design_image_preview');
        const noImageText = document.getElementById('no_image_text');
        const lookupStatus = document.getElementById('lookup_status');

        // GENERIC SEARCHABLE DROPDOWN
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

            display.addEventListener('click', function(e) {
                e.stopPropagation();
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

            // Set initial state
            if (hiddenSelect.value) {
                const selectedItem = Array.from(getListItems()).find(i => i.dataset.value === hiddenSelect.value);
                if (selectedItem) {
                    display.textContent = selectedItem.textContent.trim();
                    selectedItem.classList.add('selected');
                }
            }
        }

        // CATEGORY & SUBCATEGORY LOGIC
        function toggleCategoryOptions(categoryId) {
            optionBlocks.forEach(block => block.style.display = 'none');
            if (!categoryId) return Promise.resolve();

            return fetch(`{{ url('/super-admin/product/get-category-options') }}?category_id=${categoryId}`)
                .then(response => response.ok ? response.json() : {})
                .then(data => {
                    Object.keys(data).forEach(option => {
                        if (data[option]) {
                            const block = document.querySelector(`.category-option[data-opt="${option}"]`);
                            if (block) block.style.display = 'block';
                        }
                    });
                    return data;
                })
                .catch(error => {
                    console.error('Error fetching category options:', error);
                    return {};
                });
        }

        function refreshSubcategories(categoryId) {
            subcategorySelect.innerHTML = '<option value="">Select Sub Category</option>';
            const listContainer = document.getElementById('subcategory_list');
            const display = document.getElementById('subcategory_display');
            listContainer.innerHTML = '<li class="custom-dropdown-item" data-value="">--Select Sub Category--</li>';
            display.textContent = '--Select Sub Category--';

            if (!categoryId) { 
                subcategoryContainer.style.display = 'none'; 
                return Promise.resolve(); 
            }
            
            return fetch(`{{ url('/super-admin/product/get-subcategories') }}?category_id=${categoryId}`)
                .then(response => response.ok ? response.json() : [])
                .then(list => {
                    if (list.length > 0) {
                        subcategoryContainer.style.display = 'block';
                        list.forEach(sub => {
                            const opt = document.createElement('option');
                            opt.value = sub.id; opt.textContent = sub.name;
                            subcategorySelect.appendChild(opt);

                            const li = document.createElement('li');
                            li.className = 'custom-dropdown-item';
                            li.dataset.value = sub.id; li.textContent = sub.name;
                            listContainer.appendChild(li);
                        });
                    } else {
                        subcategoryContainer.style.display = 'none';
                    }
                    return list;
                })
                .catch(error => {
                    console.error('Error fetching subcategories:', error);
                    return [];
                });
        }

        // LOOKUP LOGIC
        function performLookup(code) {
            if (!code) {
                lookupStatus.textContent = "New Product Flow: Code will be auto-generated.";
                lookupStatus.className = "text-warning";
                return;
            }
            lookupStatus.textContent = 'Looking up...';
            lookupStatus.className = 'text-info';

            fetch(`{{ url('/super-admin/work-order/get-product-details') }}?product_code=${encodeURIComponent(code)}`)
                .then(response => response.ok ? response.json() : { success: false })
                .then(data => {
                    if (data.success) {
                        const fields = ['product_name', 'design_code', 'hallmark', 'rodium', 'hook', 'size', 'stone', 'enamel', 'length', 'weight_from', 'weight_to'];
                        fields.forEach(f => {
                            const el = document.getElementById(f);
                            if (el) el.value = data.product[f] || '';
                        });

                        if (data.product.product_category_id) {
                            categorySelect.value = data.product.product_category_id;
                            
                            // Update Category Dropdown Display
                            const catItem = document.querySelector(`#category_list .custom-dropdown-item[data-value="${data.product.product_category_id}"]`);
                            if (catItem) {
                                document.getElementById('category_display').textContent = catItem.textContent.trim();
                                document.querySelectorAll('#category_list .custom-dropdown-item').forEach(i => i.classList.remove('selected'));
                                catItem.classList.add('selected');
                            }

                            toggleCategoryOptions(data.product.product_category_id);
                            refreshSubcategories(data.product.product_category_id).then(() => {
                                if (data.product.subcategory_id) {
                                    subcategorySelect.value = data.product.subcategory_id;
                                    const subItem = document.querySelector(`#subcategory_list .custom-dropdown-item[data-value="${data.product.subcategory_id}"]`);
                                    if (subItem) {
                                        document.getElementById('subcategory_display').textContent = subItem.textContent.trim();
                                        document.querySelectorAll('#subcategory_list .custom-dropdown-item').forEach(i => i.classList.remove('selected'));
                                        subItem.classList.add('selected');
                                    }
                                }
                            });
                        }

                        if (data.product.product_image_url && designImagePreview && noImageText) {
                            designImagePreview.src = data.product.product_image_url;
                            designImagePreview.style.display = 'block';
                            noImageText.style.display = 'none';
                        }
                        lookupStatus.textContent = 'Product found!';
                        lookupStatus.className = 'text-success';
                    } else {
                        lookupStatus.textContent = data.message || 'Product not found';
                        lookupStatus.className = 'text-danger';
                    }
                });
        }

        // EVENT LISTENERS
        productCodeInput.addEventListener('blur', () => performLookup(productCodeInput.value.trim()));
        document.getElementById('test_lookup_btn').addEventListener('click', () => performLookup(productCodeInput.value.trim()));

        // Add Category Button
        document.getElementById('addCategoryBtn').addEventListener('click', function(e) {
            e.stopPropagation();
            const name = prompt('Enter new category name');
            if (!name) return;
            const opts = {
                has_hook: confirm('Enable Hook?') ? 1 : 0,
                has_enamel: confirm('Enable Enamel?') ? 1 : 0,
                has_rodium: confirm('Enable Rodium?') ? 1 : 0,
                has_open_close: confirm('Enable Open/Close?') ? 1 : 0,
                has_stone: confirm('Enable Stone?') ? 1 : 0,
            };
            fetch(`{{ route('super-admin.product-category.store') }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ name, ...opts })
            })
            .then(res => res.json())
            .then(data => {
                if (data.category) {
                    const opt = new Option(data.category.name, data.category.id, true, true);
                    categorySelect.add(opt);
                    const li = document.createElement('li');
                    li.className = 'custom-dropdown-item selected';
                    li.dataset.value = data.category.id;
                    li.textContent = data.category.name;
                    document.getElementById('category_list').appendChild(li);
                    document.getElementById('category_display').textContent = data.category.name;
                    toggleCategoryOptions(data.category.id);
                    refreshSubcategories(data.category.id);
                }
            });
        });

        // Add Subcategory Button
        document.getElementById('addSubcategoryBtn').addEventListener('click', function(e) {
            e.stopPropagation();
            const parentId = categorySelect.value;
            if (!parentId) { alert('Select category first'); return; }
            const name = prompt('Enter new subcategory name');
            if (!name) return;
            fetch(`{{ route('super-admin.product-category.store') }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ parent_category_id: parentId, name })
            })
            .then(res => res.json())
            .then(data => {
                if (data.subcategory) {
                    const opt = new Option(data.subcategory.name, data.subcategory.id, true, true);
                    subcategorySelect.add(opt);
                    const li = document.createElement('li');
                    li.className = 'custom-dropdown-item selected';
                    li.dataset.value = data.subcategory.id;
                    li.textContent = data.subcategory.name;
                    document.getElementById('subcategory_list').appendChild(li);
                    document.getElementById('subcategory_display').textContent = data.subcategory.name;
                }
            });
        });

        // Multi-image preview
        const multiImageInput = document.getElementById('product_images');
        if (multiImageInput) {
            multiImageInput.addEventListener('change', function() {
                const container = document.getElementById('multi_image_preview_container');
                if (!container) return;
                container.innerHTML = '';
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = e => {
                        const div = document.createElement('div');
                        div.className = 'preview-item';
                        div.innerHTML = `<img src="${e.target.result}">`;
                        container.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            });
        }

        // Initialize Dropdowns
        initSearchableDropdown('bp_code_container', 'bp_code_display', 'bp_code_menu', 'bp_code_search', 'bp_code_list', 'bp_code', '--Select BP Code--', (val, item) => {
            const name = item.dataset.name;
            if (name) customerNameInput.value = name;
        });
        initSearchableDropdown('category_container', 'category_display', 'category_menu', 'category_search', 'category_list', 'product_category_id', '--Select Category--', (val) => {
            toggleCategoryOptions(val);
            refreshSubcategories(val);
        });
        initSearchableDropdown('subcategory_container', 'subcategory_display', 'subcategory_menu', 'subcategory_search', 'subcategory_list', 'subcategory_id', '--Select Sub Category--');

        // Initial load
        if (categorySelect.value) {
            toggleCategoryOptions(categorySelect.value);
            refreshSubcategories(categorySelect.value);
        }
    });
</script>
@endsection