@extends('admin.layouts.app')

@section('title', 'Edit Purchase Order')

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
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Purchase Order</h1>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Edit Purchase Order Information</h4>
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

                    <form method="POST" action="{{ route('admin.purchase-order.update', $purchaseOrder) }}" enctype="multipart/form-data" id="purchaseOrderForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="return_url" value="{{ old('return_url', request('return_url')) }}">
                        @php
                            $currentTab = request('tab', 'created');
                            if (request('return_url')) {
                                parse_str(parse_url(request('return_url'), PHP_URL_QUERY), $query);
                                if (isset($query['tab'])) {
                                    $currentTab = $query['tab'];
                                }
                            }
                        @endphp
                        <input type="hidden" name="tab" value="{{ old('tab', $currentTab) }}">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="due_date" class="form-label fw-bold">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" value="{{ old('due_date', $purchaseOrder->due_date ? (\Carbon\Carbon::parse($purchaseOrder->due_date)->format('Y-m-d')) : '') }}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="notes" class="form-label fw-bold">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $purchaseOrder->notes) }}</textarea>
                            </div>
                        </div>
                        
                        <div class="card mb-4 border-0">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Items List</h5>
                            </div>
                            <div class="card-body px-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle" id="itemsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="50">SNo</th>
                                                <th>Category</th>
                                                <th>Sub Category (Product)</th>
                                                <th>Design Code</th>
                                                <th width="250">Grams & Quantity</th>
                                                <th>Total</th>
                                                <th>Size</th>
                                                <th>Item Notes</th>
                                                <th width="180">Image</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemsContainer">
                                            @foreach($itemsWithDetails as $index => $item)
                                                <tr class="item-row">
                                                    <td class="sno">{{ $index + 1 }}</td>
                                                    <td>
                                                        <select class="form-select category-select" name="items[{{ $index }}][category]">
                                                            <option value="">Select Category</option>
                                                            @foreach($categories as $category)
                                                                <option value="{{ $category->id }}" {{ (isset($item['product']->product_category_id) && $item['product']->product_category_id == $category->id) ? 'selected' : (isset($item['category']) && $item['category'] == $category->id ? 'selected' : '') }}>
                                                                    {{ $category->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $designCode = $item['product']->design_code ?? '';
                                                            $imageUrl = '';
                                                            
                                                            // Check for Design Image First
                                                            if(isset($item['design']) && $item['design']) {
                                                                $designCode = $item['design']->design_code;
                                                                if($item['design']->image) {
                                                                    $path = $item['design']->image;
                                                                    if (!str_starts_with($path, 'storage/')) {
                                                                        $path = 'storage/' . $path;
                                                                    }
                                                                    $imageUrl = asset($path);
                                                                }
                                                            }
                                                            
                                                            // Fallback to Product Image
                                                            if(empty($imageUrl) && $item['product'] && $item['product']->images && count($item['product']->images) > 0) {
                                                                $path = $item['product']->images[0]->path;
                                                                if (!str_starts_with($path, 'storage/') && !str_starts_with($path, 'images/')) {
                                                                    $path = 'storage/' . $path;
                                                                }
                                                                $imageUrl = asset($path);
                                                            }

                                                            // Handle Manual/API Upload pathing (images/purchase-orders/...)
                                                            if(!empty($item['image']) && str_contains($item['image'], 'images/purchase-orders')) {
                                                                $manualImageUrl = asset($item['image']);
                                                            } else {
                                                                $manualImageUrl = !empty($item['image']) ? asset('storage/' . $item['image']) : '';
                                                            }
                                                        @endphp
                                                        <select class="form-select product-select" name="items[{{ $index }}][product_id]">
                                                            <option value="{{ $item['product_id'] ?? '' }}" selected 
                                                                data-design-code="{{ $designCode }}"
                                                                data-image="{{ $imageUrl }}"
                                                            >
                                                                @php
                                                                    $initialSubName = ($item['subcategory_name'] && $item['subcategory_name'] !== 'N/A') ? $item['subcategory_name'] : (($item['product'] && $item['product']->subcategory) ? $item['product']->subcategory->name : '');
                                                                    $initialProductName = $item['product']->product_name ?? 'Manual';
                                                                    $initialDesignText = $designCode ? " [Design: $designCode]" : "";
                                                                @endphp
                                                                {{ $initialSubName ? "$initialSubName - $initialProductName$initialDesignText" : "$initialProductName$initialDesignText" }}
                                                            </option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="form-select design-code-select select2" name="items[{{ $index }}][design_code]">
                                                            <option value="">Select Design Code</option>
                                                            @foreach($designs as $design)
                                                                <option value="{{ $design->design_code }}" {{ ($item['product'] && $item['product']->design_code == $design->design_code) ? 'selected' : (isset($item['design_code']) && $item['design_code'] == $design->design_code ? 'selected' : '') }}>
                                                                    {{ $design->design_code }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <div class="grams-quantity-group">
                                                            @php
                                                                $gramsArr = is_array($item['grams']) ? $item['grams'] : [$item['grams']];
                                                                $qtyArr = is_array($item['quantity']) ? $item['quantity'] : [$item['quantity']];
                                                            @endphp
                                                            @foreach($gramsArr as $k => $grams)
                                                                <div class="grams-quantity-row mb-2">
                                                                    <div class="row g-1">
                                                                        <div class="col-4">
                                                                            <input type="number" class="form-control grams-input" name="items[{{ $index }}][grams][]" step="0.01" min="0" value="{{ $grams }}" required>
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="number" class="form-control quantity-input" name="items[{{ $index }}][quantity][]" min="1" value="{{ $qtyArr[$k] ?? 1 }}" required>
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="number" class="form-control individual-total" name="items[{{ $index }}][individual_totals][]" step="0.01" value="{{ $item['individual_totals'][$k] ?? 0 }}" readonly placeholder="Total">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-outline-primary add-grams-quantity mt-1">Add More</button>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control total-input text-end fw-bold" name="items[{{ $index }}][total]" step="0.01" value="{{ number_format($item['total'] ?? 0, 2, '.', '') }}" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="items[{{ $index }}][item_size]" value="{{ $item['item_size'] ?? '' }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="items[{{ $index }}][item_notes]" value="{{ $item['item_notes'] ?? '' }}">
                                                    </td>
                                                    <td>
                                                        {{-- PERSIST IMAGE LOGIC --}}
                                                        <input type="hidden" name="items[{{ $index }}][old_image]" value="{{ $item['image'] ?? '' }}">
                                                        
                                                        {{-- DELETION MARKER --}}
                                                        <input type="hidden" name="items[{{ $index }}][_deleted]" value="0" class="deleted-marker">
                                                        
                                                        {{-- 1. SHOW SAVED IMAGE --}}
                                                        @if(!empty($item['image']))
                                                            <div class="saved-preview mb-2">
                                                                <small class="text-primary d-block fw-bold">Current Order Image:</small>
                                                                <img src="{{ (str_starts_with($item['image'], 'images/')) ? asset($item['image']) : asset('storage/' . $item['image']) }}" class="img-thumbnail" style="max-height: 60px;">
                                                            </div>
                                                        @endif

                                                        {{-- 2. SHOW CATALOGUE IMAGE --}}
                                                        <div class="design-image-preview-container mb-2" style="display:none;">
                                                            <small class="text-success d-block fw-bold">Catalogue Ref:</small>
                                                            <img src="" class="img-thumbnail fetched-image" style="max-height: 60px;">
                                                        </div>
                                                        
                                                        <input type="file" class="form-control image-input" name="items[{{ $index }}][image]" accept="image/*">
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm remove-item-row"><i class="bi bi-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light fw-bold text-end">
                                                <td colspan="5">Grand Total weight (g):</td>
                                                <td class="text-primary" id="grandTotal">0.00</td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                
                                <button type="button" class="btn btn-success mt-2" id="addItemBtn">
                                    <i class="bi bi-plus-lg"></i> Add New Item Row
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between border-top pt-3">
                            <a href="{{ request('return_url', route('admin.purchase-order.index')) }}" class="btn btn-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-5">Update Purchase Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = document.querySelectorAll('.item-row').length;

    // Initialize all existing rows
    document.querySelectorAll('.item-row').forEach(row => {
        initializeRow(row);
        
        // For existing items, check if they have a saved image and show it
        const savedPreview = row.querySelector('.saved-preview');
        // Check if the item has an image stored in the old_image field
        const oldImageField = row.querySelector('input[name*="[old_image]"]');
        if (oldImageField && oldImageField.value) {
            savedPreview.style.display = 'block';
        }
        
        // Check if this item has a catalogue image and show it
        const designImageContainer = row.querySelector('.design-image-preview-container');
        const designImageElement = designImageContainer.querySelector('img');
        
        const productSelect = row.querySelector('.product-select');
        if (productSelect && productSelect.options.length > 0) {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            if (selectedOption && selectedOption.dataset.image) {
                designImageElement.src = selectedOption.dataset.image;
                if (!oldImageField || !oldImageField.value) {
                    designImageContainer.style.display = 'block';
                }
            }
        }
    });

    // Add New Item Row logic
    document.getElementById('addItemBtn').addEventListener('click', function() {
        const container = document.getElementById('itemsContainer');
        const firstRow = document.querySelector('.item-row');
        
        // Temporarily destroy Select2 before cloning
        $(firstRow).find('.select2-hidden-accessible').select2('destroy');
        
        const newRow = firstRow.cloneNode(true);
        
        // Re-initialize Select2 for the first row
        initializeRow(firstRow);

        newRow.querySelectorAll('input, select').forEach(element => {
            if (element.name) {
                element.name = element.name.replace(/items\[\d+\]/, `items[${itemIndex}]`);
            }
            if (element.classList.contains('total-input') || element.classList.contains('individual-total')) {
                element.value = '0.00';
            } else if (element.classList.contains('deleted-marker')) {
                element.value = '0';
            } else if (element.type !== 'file') {
                element.value = '';
            }
        });

        // Reset Select2 specific clones
        newRow.querySelectorAll('.select2-container').forEach(el => el.remove());
        $(newRow).find('.select2-hidden-accessible').removeClass('select2-hidden-accessible');

        // Reset Display UI for new row
        newRow.querySelector('.product-select').innerHTML = '<option value="">Select Category First</option>';
        newRow.querySelector('.design-image-preview-container').style.display = 'none';
        newRow.querySelector('.saved-preview').style.display = 'none';
        const newFetchedImage = newRow.querySelector('.fetched-image');
        if (newFetchedImage) {
            newFetchedImage.src = '';
        }

        const group = newRow.querySelector('.grams-quantity-group');
        group.innerHTML = `
            <div class="grams-quantity-row mb-2">
                <div class="row g-1">
                    <div class="col-4"><input type="number" step="0.01" class="form-control grams-input" name="items[${itemIndex}][grams][]" placeholder="Grams" required></div>
                    <div class="col-4"><input type="number" class="form-control quantity-input" name="items[${itemIndex}][quantity][]" placeholder="Qty" required></div>
                    <div class="col-4"><input type="number" class="form-control individual-total" name="items[${itemIndex}][individual_totals][]" value="0.00" readonly></div>
                </div>
            </div>`;

        container.appendChild(newRow);
        initializeRow(newRow);
        updateSerialNumbers();
        calculateGrandTotal();
        itemIndex++;
    });

    function initializeRow(row) {
        const categorySelect = row.querySelector('.category-select');
        const productSelect = row.querySelector('.product-select');
        const designCodeSelect = row.querySelector('.design-code-select');
        const addGramsBtn = row.querySelector('.add-grams-quantity');
        const catalogueCont = row.querySelector('.design-image-preview-container');
        const savedCont = row.querySelector('.saved-preview');
        const imgTag = row.querySelector('.fetched-image');

        // Initialize Select2
        $(row).find('.select2').select2({
            width: '100%'
        });

        // FLOW 1: Category -> Product
        $(categorySelect).on('change', function(e, skipReset) {
            const categoryId = this.value;
            productSelect.innerHTML = '<option value="">Loading...</option>';
            
            if (!skipReset) {
                if (designCodeSelect) {
                    $(designCodeSelect).val('').trigger('change.select2', [true]);
                }
                catalogueCont.style.display = 'none';
            }

            $(productSelect).trigger('change.select2');
            
            if (!categoryId) {
                productSelect.innerHTML = '<option value="">Select Category First</option>';
                $(productSelect).trigger('change.select2');
                return;
            }

            fetch("{{ route('admin.purchase-order.get-products-by-category') }}?category_id=" + categoryId)
                .then(r => r.json())
                .then(data => {
                    productSelect.innerHTML = '<option value="">Select Sub Category</option>';
                    data.forEach(product => {
                        const opt = document.createElement('option');
                        opt.value = product.id;
                        const subName = product.subcategory ? product.subcategory.name : 'N/A';
                        const designCode = product.design_code_display || '';
                        const designText = designCode ? ` [Design: ${designCode}]` : '';
                        opt.textContent = `${subName} - ${product.product_name}${designText}`;
                        
                        // Metadata for Catalogue Reference
                        opt.dataset.designCode = product.design_code_display || '';
                        opt.dataset.image = product.image_url_display || '';
                        
                        productSelect.appendChild(opt);
                    });
                    $(productSelect).trigger('change.select2');
                });
        });

        // FLOW 2: Product -> Design & Image
        $(productSelect).on('change', function() {
            const selected = this.options[this.selectedIndex];
            if (!selected || selected.value === "") return;

            const designCode = selected.dataset.designCode || '';
            const imageUrl = selected.dataset.image || '';
            
            if (designCode && designCode !== "" && designCode !== "N/A") {
                $(row).find('.design-code-select').val(designCode).trigger('change.select2', [true]);
                
                if (imageUrl) {
                    imgTag.src = imageUrl;
                    if (!row.querySelector('input[name*="[old_image]"]').value && !row.querySelector('.image-input').files[0]) {
                        catalogueCont.style.display = 'block';
                        catalogueCont.style.opacity = '1';
                    }
                }
            } else {
                catalogueCont.style.display = 'none';
            }
        });

        // FLOW 3: Fetch BY DESIGN CODE
        $(designCodeSelect).on('change', function(e, skipAjax) {
            if (skipAjax) return;
            
            const designCode = this.value;
            if (!designCode) return;

            fetch("{{ route('admin.purchase-order.get-product-by-design-code') }}?design_code=" + designCode)
                .then(response => response.json())
                .then(data => {
                    if (data.error) return;

                    // 1. Set Category - pass true to skip resetting Design Code
                    $(categorySelect).val(data.category_id).trigger('change', [true]);
                    
                    // 2. Wait for products to load then set Product
                    const checkInterval = setInterval(() => {
                        const productOption = Array.from(productSelect.options).find(opt => opt.value == data.product_id);
                        if (productOption) {
                            clearInterval(checkInterval);
                            $(productSelect).val(data.product_id).trigger('change');
                        }
                    }, 100);

                    // 3. Set image preview
                    if (data.image_url) {
                        imgTag.src = data.image_url;
                        if (!row.querySelector('input[name*="[old_image]"]').value && !row.querySelector('.image-input').files[0]) {
                            catalogueCont.style.display = 'block';
                            catalogueCont.style.opacity = '1';
                        }
                    }
                });
        });

        // FLOW 4: If manual image is selected, dim the catalogue image
        row.querySelector('.image-input').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                catalogueCont.style.display = 'none'; 
                if (savedCont) savedCont.style.display = 'none';
            } else {
                if (row.querySelector('input[name*="[old_image]"]').value) {
                    if (savedCont) savedCont.style.display = 'block';
                } else if (imgTag.src && imgTag.src !== window.location.href) {
                    catalogueCont.style.display = 'block';
                    catalogueCont.style.opacity = '1';
                }
            }
        });

        // Add More logic
        addGramsBtn.addEventListener('click', function() {
            const group = row.querySelector('.grams-quantity-group');
            const match = categorySelect.name.match(/items\[(\d+)\]/);
            const currentIdx = match ? match[1] : 0;
            
            const div = document.createElement('div');
            div.className = 'grams-quantity-row mb-2';
            div.innerHTML = `
                <div class="row g-1">
                    <div class="col-4"><input type="number" step="0.01" class="form-control grams-input" name="items[${currentIdx}][grams][]" value="0" required></div>
                    <div class="col-4"><input type="number" class="form-control quantity-input" name="items[${currentIdx}][quantity][]" value="1" required></div>
                    <div class="col-4"><input type="number" class="form-control individual-total" name="items[${currentIdx}][individual_totals][]" value="0.00" readonly></div>
                </div>
`;
            group.appendChild(div);
            
            div.querySelectorAll('input').forEach(i => {
                if (i.classList.contains('grams-input') || i.classList.contains('quantity-input')) {
                    i.addEventListener('input', () => { 
                        const gramVal = parseFloat(i.closest('.row').querySelector('.grams-input').value) || 0;
                        const qtyVal = parseFloat(i.closest('.row').querySelector('.quantity-input').value) || 0;
                        const totalField = i.closest('.row').querySelector('.individual-total');
                        if (totalField) {
                            totalField.value = (gramVal * qtyVal).toFixed(2);
                        }
                        calculateItemTotal(row); 
                        calculateGrandTotal(); 
                    });
                }
            });
        });

        // Global row listeners
        row.addEventListener('input', (e) => {
            if (e.target.classList.contains('grams-input') || e.target.classList.contains('quantity-input')) {
                const gramVal = parseFloat(e.target.closest('.row').querySelector('.grams-input').value) || 0;
                const qtyVal = parseFloat(e.target.closest('.row').querySelector('.quantity-input').value) || 0;
                const totalField = e.target.closest('.row').querySelector('.individual-total');
                if (totalField) {
                    totalField.value = (gramVal * qtyVal).toFixed(2);
                }
                calculateItemTotal(row);
                calculateGrandTotal();
            }
        });

        // Delete Row Logic - Mark as deleted instead of removing from DOM
        row.querySelector('.remove-item-row').addEventListener('click', () => {
            if (document.querySelectorAll('.item-row').length > 1) {
                const deletedMarker = row.querySelector('.deleted-marker');
                if (deletedMarker) {
                    deletedMarker.value = '1';
                }
                row.style.display = 'none';
                updateSerialNumbers();
                calculateGrandTotal();
            } else {
                alert("At least one item is required.");
            }
        });
    }

    function calculateItemTotal(row) {
        let total = 0;
        const individualTotals = row.querySelectorAll('.individual-total');
        individualTotals.forEach(totalInput => {
            total += (parseFloat(totalInput.value) || 0);
        });
        row.querySelector('.total-input').value = total.toFixed(2);
    }

    function calculateGrandTotal() {
        let grand = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const marker = row.querySelector('.deleted-marker');
            if (!marker || marker.value !== '1') {
                const input = row.querySelector('.total-input');
                grand += parseFloat(input.value) || 0;
            }
        });
        document.getElementById('grandTotal').textContent = grand.toFixed(2);
    }

    function updateSerialNumbers() {
        let sno = 1;
        document.querySelectorAll('.item-row').forEach(row => {
            const marker = row.querySelector('.deleted-marker');
            if (!marker || marker.value !== '1') {
                row.querySelector('.sno').textContent = sno++;
            }
        });
    }

    calculateGrandTotal();
});
</script>
@endsection
