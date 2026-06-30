@extends('super-admin.layouts.app')

@section('title', 'Add New Purchase Order')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
                <h1 class="h2">Add New Purchase Order</h1>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Purchase Order Information</h4>
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

                    <form method="POST" action="{{ route('super-admin.purchase-order.store') }}" enctype="multipart/form-data" id="purchaseOrderForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" value="{{ old('due_date') }}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="General order notes...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                        
                        <div class="card mb-4 border-0">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Items</h5>
                            </div>
                            <div class="card-body px-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle" id="itemsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>S.No</th>
                                                <th style="min-width: 180px;">Category</th>
                                                <th style="min-width: 200px;">Sub Category (Product)</th>
                                                <th style="width: 150px;">Design Code</th>
                                                <th style="min-width: 220px;">Grams & Quantity</th>
                                                <th style="min-width: 220px;">Size</th>
                                                <th style="width: 120px;">Total</th>
                                                <th>Item Notes</th>
                                                <th style="min-width: 200px;">Image</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemsContainer">
                                            <tr class="item-row">
                                                <td class="sno">1</td>
                                                <td>
                                                    <select class="form-select category-select select2" name="items[0][category]">
                                                        <option value="">Select Category</option>
                                                        @foreach($categories as $category)
                                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-select product-select select2" name="items[0][product_id]">
                                                        <option value="">Select Category First</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-select design-code-select select2" name="items[0][design_code]">
                                                        <option value="">Select Design Code</option>
                                                        @foreach($designs as $design)
                                                            <option value="{{ $design->design_code }}">{{ $design->design_code }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="grams-quantity-group">
                                                        <div class="grams-quantity-row mb-2">
                                                            <div class="row g-1">
                                                                <div class="col-4">
                                                                    <input type="number" class="form-control grams-input" name="items[0][grams][]" step="0.01" min="0" placeholder="Grams" required>
                                                                </div>
                                                                <div class="col-4">
                                                                    <input type="number" class="form-control quantity-input" name="items[0][quantity][]" min="1" placeholder="Qty" required>
                                                                </div>
                                                                <div class="col-4">
                                                                    <input type="number" class="form-control individual-total" name="items[0][individual_totals][]" step="0.01" value="0.00" readonly placeholder="Total">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-grams-quantity mt-1">
                                                        <i class="bi bi-plus-circle"></i> Add More
                                                    </button>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" name="items[0][item_size]" placeholder="size">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control total-input text-end fw-bold" name="items[0][total]" step="0.01" value="0.00" readonly>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" name="items[0][item_notes]" placeholder="Notes">
                                                </td>
                                                <td>
                                                    <div class="design-image-preview-container mb-1" style="display:none;">
                                                        <img src="" class="img-thumbnail fetched-image" style="max-height: 60px;">
                                                        <small class="d-block text-muted">Current Design</small>
                                                    </div>
                                                    <input type="file" class="form-control image-input" name="items[0][image]" accept="image/*">
                                                    <input type="hidden" name="items[0][_deleted]" value="0" class="deleted-marker">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm remove-item">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light fw-bold">
                                                <td colspan="5" class="text-end">Grand Total weight (g):</td>
                                                <td class="text-end text-primary" id="grandTotal">0.00</td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                
                                <button type="button" class="btn btn-success mt-2" id="addItemBtn">
                                    <i class="bi bi-plus-lg"></i> Add Another Item Row
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between border-top pt-3">
                            <a href="{{ route('super-admin.purchase-order.index') }}" class="btn btn-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-5">Create Purchase Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let itemIndex = 1;

    // 1. Initial Row setup
    initializeRow(document.querySelector('.item-row'));

    // 2. Add New Main Item Row logic
    document.getElementById('addItemBtn').addEventListener('click', function() {
        const container = document.getElementById('itemsContainer');
        const firstRow = document.querySelector('.item-row');
        
        // Temporarily destroy Select2 before cloning to avoid issues
        $(firstRow).find('.select2-hidden-accessible').select2('destroy');
        
        const newRow = firstRow.cloneNode(true);
        
        // Re-initialize Select2 for the first row
        initializeRow(firstRow);

        // Reset inputs and update names for the new index
        newRow.querySelectorAll('input, select').forEach(element => {
            if (element.name) {
                element.name = element.name.replace(/items\[\d+\]/, `items[${itemIndex}]`);
            }
            if (element.classList.contains('total-input') || element.classList.contains('individual-total')) {
                element.value = '0.00';
            } else if (element.type !== 'file' && element.tagName !== 'SELECT') {
                element.value = '';
            }
        });

        // Reset Select2 specific clones
        newRow.querySelectorAll('.select2-container').forEach(el => el.remove());
        $(newRow).find('.select2-hidden-accessible').removeClass('select2-hidden-accessible');

        // Reset Grams & Quantity Group: Keep only the first row
        const gramsGroup = newRow.querySelector('.grams-quantity-group');
        const gramsRows = gramsGroup.querySelectorAll('.grams-quantity-row');
        gramsRows.forEach((row, index) => {
            if (index > 0) row.remove();
        });

        // Reset specific UI elements for the new row
        const productSelect = newRow.querySelector('.product-select');
        productSelect.innerHTML = '<option value="">Select Category First</option>';
        const designSelect = newRow.querySelector('.design-code-select');
        if (designSelect) designSelect.value = '';
        newRow.querySelector('.design-image-preview-container').style.display = 'none';
                
                // Make sure the new row has a fresh deletion marker
                const deletedMarker = newRow.querySelector('.deleted-marker');
                if (deletedMarker) {
                    deletedMarker.name = `items[${itemIndex}][_deleted]`;
                    deletedMarker.value = '0';
                }

        container.appendChild(newRow);
        initializeRow(newRow);
        updateSerialNumbers();
        itemIndex++;
    });

    function initializeRow(row) {
        const categorySelect = row.querySelector('.category-select');
        const productSelect = row.querySelector('.product-select');
        const designCodeSelect = row.querySelector('.design-code-select');
        const addGramsBtn = row.querySelector('.add-grams-quantity');
        const imgCont = row.querySelector('.design-image-preview-container');
        const imgTag = row.querySelector('.fetched-image');

        // Initialize Select2
        $(row).find('.select2').select2({
            width: '100%',
            minimumResultsForSearch: 0 // Always show search box
        });

        // FLOW 1: Filter Products by Category
        $(categorySelect).on('change', function(e, skipReset) {
            const categoryId = this.value;
            productSelect.innerHTML = '<option value="">Loading...</option>';
            
            if (!skipReset) {
                if (designCodeSelect) {
                    $(designCodeSelect).val('').trigger('change.select2', [true]);
                }
                imgCont.style.display = 'none';
            }
            
            // Re-initialize product Select2 to show loading
            $(productSelect).trigger('change.select2');
            
            if (!categoryId) {
                productSelect.innerHTML = '<option value="">Select Category First</option>';
                $(productSelect).trigger('change.select2');
                return;
            }

            fetch("{{ route('super-admin.purchase-order.get-products-by-category') }}?category_id=" + categoryId)
                .then(response => response.json())
                .then(data => {
                    productSelect.innerHTML = '<option value="">Select Sub Category</option>';
                    data.forEach(product => {
                        const opt = document.createElement('option');
                        opt.value = product.id;
                        const subName = product.subcategory ? product.subcategory.name : 'N/A';
                        const designCode = product.design_code_display || '';
                        const designText = designCode ? ` [Design: ${designCode}]` : '';
                        opt.textContent = ` ${subName} - ${product.product_name}${designText}`;
                        
                        // STORE DATA FOR AUTO-FILL (Like your Catalogue)
                        opt.dataset.designCode = product.design_code_display || '';
                        opt.dataset.image = product.image_url_display || '';
                        
                        productSelect.appendChild(opt);
                    });
                    // Refresh Select2
                    $(productSelect).trigger('change.select2');
                });
        });

        // FLOW 2: Auto-fill Design Code Text and Show Image Preview
        $(productSelect).on('change', function() {
            const selected = this.options[this.selectedIndex];
            
            // Reset defaults
            if (!selected || selected.value === "") {
                return;
            }

            const designCode = selected.dataset.designCode || '';
            const imageUrl = selected.dataset.image || '';
            
            // NEW LOGIC: Only show image if Design Code exists and is not empty
            if (designCode && designCode !== "" && designCode !== "N/A") {
                // If we're changing product manually, update the design select to match
                $(row).find('.design-code-select').val(designCode).trigger('change.select2', [true]); // true to skip loop
                
                if (imageUrl) {
                    imgTag.src = imageUrl;
                    imgCont.style.display = 'block';
                    imgCont.style.opacity = '1';
                }
            } else {
                imgCont.style.display = 'none';
            }
        });

        // FLOW 3: FETCh BY DESIGN CODE (Main Feature)
        $(row).find('.design-code-select').on('change', function(e, skipAjax) {
            if (skipAjax) return;
            
            const designCode = this.value;
            if (!designCode) return;

            fetch("{{ route('super-admin.purchase-order.get-product-by-design-code') }}?design_code=" + designCode)
                .then(response => response.json())
                .then(data => {
                    if (data.error) return;

                    // 1. Set Category - pass true to skip resetting Design Code
                    $(categorySelect).val(data.category_id).trigger('change', [true]);
                    
                    // 2. Wait for products to load then set Product
                    // We need to wait for the AJAX in the category change to finish
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
                        imgCont.style.display = 'block';
                        imgCont.style.opacity = '1';
                    }
                });
        });

        // FLOW 4: If manual image is selected, dim the catalogue image
        row.querySelector('.image-input').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                imgCont.style.opacity = '0.3'; 
            } else {
                imgCont.style.opacity = '1';
            }
        });

        // CALCULATION: Multi-row Grams/Quantity logic
        addGramsBtn.addEventListener('click', function() {
            const group = row.querySelector('.grams-quantity-group');
            const newGramsRow = document.createElement('div');
            newGramsRow.className = 'grams-quantity-row mb-2';
            
            const currentIdxMatch = categorySelect.name.match(/items\[(\d+)\]/);
            const currentIdx = currentIdxMatch ? currentIdxMatch[1] : 0;

            newGramsRow.innerHTML = `
                <div class="row g-1">
                    <div class="col-3">
                        <input type="number" class="form-control grams-input" name="items[${currentIdx}][grams][]" step="0.01" min="0" required placeholder="Grams">
                    </div>
                    <div class="col-3">
                        <input type="number" class="form-control quantity-input" name="items[${currentIdx}][quantity][]" min="1" required placeholder="Qty">
                    </div>
                    <div class="col-3">
                        <input type="number" class="form-control individual-total" name="items[${currentIdx}][individual_totals][]" step="0.01" value="0.00" readonly placeholder="Total">
                    </div>
                    <div class="col-3 text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-grams-row">
                            <i class="bi bi-dash-circle"></i>
                        </button>
                    </div>
                </div>`;
            group.appendChild(newGramsRow);
            
            newGramsRow.querySelectorAll('input').forEach(input => {
                if (input.classList.contains('grams-input') || input.classList.contains('quantity-input')) {
                    input.addEventListener('input', () => { 
                        // Calculate individual total for this row
                        const gramVal = parseFloat(input.closest('.row').querySelector('.grams-input').value) || 0;
                        const qtyVal = parseFloat(input.closest('.row').querySelector('.quantity-input').value) || 0;
                        const totalField = input.closest('.row').querySelector('.individual-total');
                        if (totalField) {
                            totalField.value = (gramVal * qtyVal).toFixed(2);
                        }
                        // Then recalculate the item total and grand total
                        calculateItemTotal(row); 
                        calculateGrandTotal(); 
                    });
                }
            });

            newGramsRow.querySelector('.remove-grams-row').addEventListener('click', function() {
                newGramsRow.remove();
                calculateItemTotal(row);
                calculateGrandTotal();
            });
        });

        // Calculate totals on row input
        row.addEventListener('input', function(e) {
            if (e.target.classList.contains('grams-input') || e.target.classList.contains('quantity-input')) {
                // Calculate individual total for this specific row
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

        // Remove the whole item row - mark as deleted instead of removing
        row.querySelector('.remove-item').addEventListener('click', function() {
            if (document.querySelectorAll('.item-row').length > 1) {
                // Mark the item as deleted by setting the hidden field value to 1
                const deletedMarker = row.querySelector('.deleted-marker');
                if (deletedMarker) {
                    deletedMarker.value = '1';
                }
                
                // Visually hide the row to indicate it's deleted
                row.style.display = 'none';
                
                updateSerialNumbers();
                calculateGrandTotal();
            } else {
                alert('At least one item row is required.');
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
        document.querySelectorAll('.total-input').forEach(input => {
            grand += parseFloat(input.value) || 0;
        });
        document.getElementById('grandTotal').textContent = grand.toFixed(2);
    }

    function updateSerialNumbers() {
        document.querySelectorAll('.sno').forEach((element, index) => {
            element.textContent = index + 1;
        });
    }
    
    // Initial total update
    calculateGrandTotal();
});
</script>
@endsection
