@extends('super-admin.layouts.app')

@section('title', 'Edit Product')

@section('styles')
<style>
    .custom-dropdown-container {
        position: relative;
        width: 100%;
    }
    .custom-dropdown-display {
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background-color: #fff;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        min-height: 38px;
    }
    .dark .custom-dropdown-display {
        background-color: #374151;
        border-color: #4b5563;
        color: #f3f4f6;
    }
    .custom-dropdown-display::after {
        content: "";
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 5px solid #666;
    }
    .custom-dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        z-index: 1000;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-top: 2px;
    }
    .dark .custom-dropdown-menu {
        background-color: #1f2937;
        border-color: #374151;
    }
    .custom-dropdown-search {
        width: 100%;
        padding: 8px 12px;
        border: none;
        border-bottom: 1px solid #dee2e6;
        outline: none;
    }
    .dark .custom-dropdown-search {
        background-color: #374151;
        color: #f3f4f6;
        border-bottom-color: #4b5563;
    }
    .custom-dropdown-list {
        max-height: 200px;
        overflow-y: auto;
    }
    .custom-dropdown-item {
        padding: 8px 12px;
        cursor: pointer;
    }
    .custom-dropdown-item:hover {
        background-color: #f8f9fa;
    }
    .dark .custom-dropdown-item:hover {
        background-color: #374151;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Product</h1>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h4 class="mb-0">Edit Product Information</h4>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('super-admin.product.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="return_url" value="{{ request('return_url') }}">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="product_code" class="form-label">Product Code </label>
                    <input type="text" class="form-control" id="product_code" name="product_code" value="{{ old('product_code', $product->product_code) }}" >
                </div>
                <!-- <div class="col-md-6 mb-3">
                    <label for="relabel_code" class="form-label">Relabel Code</label>
                    <input type="text" class="form-control" id="relabel_code" name="relabel_code" value="{{ old('relabel_code', $product->relabel_code) }}">
                </div> -->
                <div class="col-md-6 mb-3">
                    <label for="product_name" class="form-label">Product Name </label>
                    <input type="text" class="form-control" id="product_name" name="product_name" value="{{ old('product_name', $product->product_name) }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="craftsman_code" class="form-label">Craftsman</label>
                    <div class="custom-dropdown-container" id="dropdown_container_craftsman_code">
                        <div class="custom-dropdown-display" id="dropdown_display_craftsman_code">Select Craftsman</div>
                        <div class="custom-dropdown-menu" id="dropdown_menu_craftsman_code" style="display: none;">
                            <input type="text" class="custom-dropdown-search" placeholder="Search Craftsman...">
                            <div class="custom-dropdown-list">
                                @foreach(\App\Models\Craftman::orderBy('name')->get() as $craftsman)
                                    <div class="custom-dropdown-item" data-value="{{ $craftsman->craftman_code }}">{{ $craftsman->craftman_code }} - {{ $craftsman->name }}</div>
                                @endforeach
                            </div>
                        </div>
                        <select class="form-select" id="craftsman_code" name="craftsman_code" style="display: none;">
                            <option value="">Select Craftsman</option>
                            @foreach(\App\Models\Craftman::orderBy('name')->get() as $craftsman)
                                <option value="{{ $craftsman->craftman_code }}" {{ old('craftsman_code', $product->bp_code) == $craftsman->craftman_code ? 'selected' : '' }}>
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
                                <option value="{{ $buyer->bp_code }}" {{ old('bp_code', $product->bp_code) == $buyer->bp_code ? 'selected' : '' }}>
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
                                    <option value="{{ $category->id }}" {{ old('product_category_id', $product->product_category_id) == $category->id ? 'selected' : '' }}>
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
                                    @foreach($subcategories as $sub)
                                        <div class="custom-dropdown-item" data-value="{{ $sub->id }}">{{ $sub->name }}</div>
                                    @endforeach
                                </div>
                            </div>
                            <select class="form-select" id="subcategory_id" name="subcategory_id" style="display: none;">
                                <option value="">Select Sub Category</option>
                                @foreach($subcategories as $sub)
                                    <option value="{{ $sub->id }}" {{ old('subcategory_id', $product->subcategory_id) == $sub->id ? 'selected' : '' }}>
                                        {{ $sub->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-outline-secondary" type="button" id="addSubcategoryBtn">New</button>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="type" class="form-label">Type *</label>
                    <select class="form-select" id="type" name="type" required>
                        <option value="Piece" {{ old('type', $product->type) == 'Piece' ? 'selected' : '' }}>Piece</option>
                        <option value="Pair" {{ old('type', $product->type) == 'Pair' ? 'selected' : '' }}>Pair</option>
                    </select>
                </div>

                <!-- <div class="col-md-6 mb-3">
                    <label for="order_type" class="form-label">Order Type *</label>
                    <select class="form-select" id="order_type" name="order_type" required>
                        <option value="Regular" {{ old('order_type', $product->order_type) == 'Regular' ? 'selected' : '' }}>Regular</option>
                        <option value="Urgent" {{ old('order_type', $product->order_type) == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="Super Urgent" {{ old('order_type', $product->order_type) == 'Super Urgent' ? 'selected' : '' }}>Super Urgent</option>
                    </select>
                </div> -->

                <div class="col-md-6 mb-3 category-option" data-opt="has_open_close" style="display:none;">
                    <label for="open_close" class="form-label">Open/Close</label>
                    <select class="form-select" id="open_close" name="open_close">
                        <option value="" {{ old('open_close', $product->open_close) == '' ? 'selected' : '' }}>Select</option>
                        <option value="Open" {{ old('open_close', $product->open_close) == 'Open' ? 'selected' : '' }}>Open</option>
                        <option value="Close" {{ old('open_close', $product->open_close) == 'Close' ? 'selected' : '' }}>Close</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3 category-option" data-opt="has_hook" style="display:none;">
                    <label for="hook" class="form-label">Hook</label>
                    <input type="text" class="form-control" id="hook" name="hook" value="{{ old('hook', $product->hook) }}">
                </div>

                <div class="col-md-6 mb-3 category-option" data-opt="has_enamel" style="display:none;">
                    <label for="enamel" class="form-label">Enamel</label>
                    <input type="text" class="form-control" id="enamel" name="enamel" value="{{ old('enamel', $product->enamel) }}">
                </div>

                <div class="col-md-6 mb-3 category-option" data-opt="has_rodium" style="display:none;">
                    <label for="rodium" class="form-label">Rodium</label>
                    <input type="text" class="form-control" id="rodium" name="rodium" value="{{ old('rodium', $product->rodium) }}">
                </div>

                <div class="col-md-6 mb-3 category-option" data-opt="has_stone" style="display:none;">
                    <label for="stone" class="form-label">Stone</label>
                    <input type="text" class="form-control" id="stone" name="stone" value="{{ old('stone', $product->stone) }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="size" class="form-label">Size</label>
                    <input type="text" class="form-control" id="size" name="size" value="{{ old('size', $product->size) }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="length" class="form-label">Length</label>
                    <input type="text" class="form-control" id="length" name="length" value="{{ old('length', $product->length) }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="weight_from" class="form-label">Weight From</label>
                    <input type="text" step="0.001" class="form-control" id="weight_from" name="weight_from" value="{{ old('weight_from', $product->weight_from) }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="weight_to" class="form-label">Weight To</label>
                    <input type="text" step="0.001" class="form-control" id="weight_to" name="weight_to" value="{{ old('weight_to', $product->weight_to) }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="hallmark" class="form-label">HUID</label>
                    <input type="text" class="form-control" id="hallmark" name="hallmark" value="{{ old('hallmark', $product->hallmark) }}">
                </div>

                <div class="col-md-12 mb-3 mt-4 border-top pt-3">
                    <label class="form-label fw-bold">Current Product Images (Watermarked)</label>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @forelse($product->images as $img)
                            <div class="border rounded p-1 text-center bg-light shadow-sm position-relative">
                                <img src="{{ asset('storage/' . $img->path) }}" alt="Product" class="rounded" style="height:100px; width:100px; object-fit:cover;">
                                <div class="form-check position-absolute bottom-0 start-50 translate-middle-x bg-white rounded px-1" style="bottom: -10px !important; border: 1px solid #ddd;">
                                    <input class="form-check-input" type="checkbox" name="delete_images[]" value="{{ $img->id }}" id="del_img_{{ $img->id }}">
                                    <label class="form-check-label text-danger small" for="del_img_{{ $img->id }}">Del</label>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">No images available for this product.</p>
                        @endforelse
                    </div>
                    
                    <label for="product_images" class="form-label">Add More Images</label>
                    <input type="file" class="form-control" id="product_images" name="product_images[]" accept="image/*" multiple>
                    <small class="text-muted">New images will have the ajlogo.png watermark applied automatically.</small>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ request('return_url', route('super-admin.product.index')) }}" class="btn btn-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">Update Product</button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
    function initSearchableDropdown(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const display = container.querySelector('.custom-dropdown-display');
        const menu = container.querySelector('.custom-dropdown-menu');
        const searchInput = container.querySelector('.custom-dropdown-search');
        const list = container.querySelector('.custom-dropdown-list');
        const hiddenSelect = container.querySelector('select');

        function updateDisplay() {
            const selectedOption = hiddenSelect.options[hiddenSelect.selectedIndex];
            if (selectedOption && selectedOption.value !== "") {
                display.textContent = selectedOption.textContent;
            } else {
                const id = hiddenSelect.getAttribute('id');
                if (id === 'bp_code') display.textContent = 'Select BP Code';
                else if (id === 'craftsman_code') display.textContent = 'Select Craftsman';
                else if (id === 'product_category_id') display.textContent = 'Select Category';
                else display.textContent = 'Select Sub Category';
            }
        }

        function filterOptions() {
            const filter = searchInput.value.toLowerCase();
            const items = list.querySelectorAll('.custom-dropdown-item');
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(filter) ? '' : 'none';
            });
        }

        function selectOption(value, text) {
            hiddenSelect.value = value;
            hiddenSelect.dispatchEvent(new Event('change'));
            updateDisplay();
            menu.style.display = 'none';
            searchInput.value = '';
            filterOptions();
        }

        function refreshList() {
            list.innerHTML = '';
            Array.from(hiddenSelect.options).forEach(option => {
                if (option.value === "") return;
                const item = document.createElement('div');
                item.className = 'custom-dropdown-item';
                item.textContent = option.textContent;
                item.dataset.value = option.value;
                item.addEventListener('click', () => selectOption(option.value, option.textContent));
                list.appendChild(item);
            });
            updateDisplay();
        }

        display.addEventListener('click', (e) => {
            e.stopPropagation();
            const isVisible = menu.style.display === 'block';
            document.querySelectorAll('.custom-dropdown-menu').forEach(m => m.style.display = 'none');
            menu.style.display = isVisible ? 'none' : 'block';
            if (!isVisible) searchInput.focus();
        });

        searchInput.addEventListener('input', filterOptions);

        document.addEventListener('click', () => {
            menu.style.display = 'none';
        });

        menu.addEventListener('click', (e) => e.stopPropagation());

        refreshList();

        return {
            refresh: refreshList,
            select: (value, text) => {
                if (!Array.from(hiddenSelect.options).some(o => o.value == value)) {
                    const opt = document.createElement('option');
                    opt.value = value; opt.textContent = text;
                    hiddenSelect.appendChild(opt);
                    refreshList();
                }
                selectOption(value, text);
            }
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        const bpCodeDropdown = initSearchableDropdown('dropdown_container_bp_code');
        const craftsmanDropdown = initSearchableDropdown('dropdown_container_craftsman_code');
        const categoryDropdown = initSearchableDropdown('dropdown_container_product_category_id');
        const subcategoryDropdown = initSearchableDropdown('dropdown_container_subcategory_id');

        const categorySelect = document.getElementById('product_category_id');
        const subcategoryContainer = document.getElementById('subcategory-container');
        const subcategorySelect = document.getElementById('subcategory_id');
        const optionBlocks = document.querySelectorAll('.category-option');

        function refreshCategoryOptions(categoryId) {
            optionBlocks.forEach(b => b.style.display = 'none');
            if (!categoryId) return;
            fetch(`{{ url('/super-admin/product/get-category-options') }}?category_id=${categoryId}`)
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
            if (!categoryId) { 
                subcategoryContainer.style.display = 'none'; 
                subcategoryDropdown.refresh();
                return; 
            }
            fetch(`{{ url('/super-admin/product/get-subcategories') }}?category_id=${categoryId}`)
                .then(r => r.json())
                .then(list => {
                    subcategoryContainer.style.display = '';
                    list.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.id; opt.textContent = s.name;
                        if(s.id == "{{ old('subcategory_id', $product->product_subcategory_id) }}") opt.selected = true;
                        subcategorySelect.appendChild(opt);
                    });
                    subcategoryDropdown.refresh();
                });
        }

        categorySelect.addEventListener('change', function() {
            refreshSubcategories(this.value);
            refreshCategoryOptions(this.value);
        });

        if (categorySelect.value) {
            refreshSubcategories(categorySelect.value);
            refreshCategoryOptions(categorySelect.value);
        }

        document.getElementById('addCategoryBtn').addEventListener('click', function() {
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
            }).then(r => r.json()).then(res => {
                if (res.category) {
                    categoryDropdown.select(res.category.id, res.category.name);
                    refreshCategoryOptions(res.category.id);
                    subcategoryContainer.style.display = '';
                }
            });
        });

        document.getElementById('addSubcategoryBtn').addEventListener('click', function() {
            const parentId = categorySelect.value;
            if (!parentId) return alert('Select category first');
            const name = prompt('Enter new subcategory name');
            if (!name) return;
            fetch(`{{ route('super-admin.product-category.store') }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ parent_category_id: parentId, name })
            }).then(r => r.json()).then(res => {
                if (res.subcategory) {
                    subcategoryDropdown.select(res.subcategory.id, res.subcategory.name);
                }
            });
        });
    });
</script>
@endsection
@endsection