@extends('super-admin.layouts.app')

@section('title', 'Add Favorites Collection')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 24px;
        margin-bottom: 24px;
    }
    .form-label {
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
        color: #374151;
    }
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 4px;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #3b82f6;
    }
    .product-list-item {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .product-list-item img {
        width: 36px;
        height: 36px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #e5e7eb;
    }
    .user-assignment-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 16px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .user-badge-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f3f4f6;
    }
    .user-badge {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 3px 8px;
        border-radius: 6px;
        letter-spacing: 0.5px;
    }
    .badge-buyer {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }
    .badge-craftsman {
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .dynamic-name-row {
        display: flex;
        align-items: center;
        gap: 14px;
        background: #f9fafb;
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        margin-bottom: 8px;
    }
    .dynamic-name-row img {
        width: 44px;
        height: 44px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
    }
    .dynamic-name-input {
        flex-grow: 1;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        outline: none;
        font-size: 14px;
        background: white;
    }
    .dynamic-name-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
    }
</style>

<div>
    <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
        <a href="{{ route('super-admin.favorites.index') }}" class="btn btn-outline-secondary" style="padding: 6px 12px; text-decoration: none; border: 1px solid #d1d5db; border-radius: 6px; color: #374151;">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <h2 style="margin: 0; font-size: 20px; font-weight: 700;">Assign Favorites</h2>
    </div>

    @if(session('error'))
        <div class="alert alert-danger" style="color: #b91c1c; background: #fef2f2; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f87171;">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <form action="{{ route('super-admin.favorites.store') }}" method="POST" id="favoritesForm">
            @csrf
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                <!-- Buyers -->
                <div>
                    <label class="form-label">Select Buyers</label>
                    <select name="buyer_ids[]" id="buyers" class="form-select select2-multiple" multiple="multiple">
                        @foreach($buyers as $buyer)
                            <option value="{{ $buyer->id }}" data-type="buyer" data-name="{{ $buyer->name ?? $buyer->full_name }} ({{ $buyer->bp_code }})">
                                {{ $buyer->name ?? $buyer->full_name }} ({{ $buyer->bp_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Craftsmen -->
                <div>
                    <label class="form-label">Select Craftsmen</label>
                    <select name="craftsman_ids[]" id="craftsmen" class="form-select select2-multiple" multiple="multiple">
                        @foreach($craftsmen as $craftsman)
                            <option value="{{ $craftsman->id }}" data-type="craftsman" data-name="{{ $craftsman->name }} ({{ $craftsman->craftman_code }})">
                                {{ $craftsman->name }} ({{ $craftsman->craftman_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Product Designs Selection -->
            <div style="margin-bottom: 24px;">
                <label class="form-label">Select Designs</label>
                <select name="product_ids[]" id="products" class="form-select select2-products" multiple="multiple" required>
                    @foreach($products as $product)
                        @php
                            $imagesCount = $product->images ? $product->images->count() : 0;
                            $firstImage = $imagesCount > 0 ? $product->images->first()->path : null;
                            if (!$firstImage && $product->product_image) {
                                $imgs = explode(',', $product->product_image);
                                $firstImage = trim($imgs[0]);
                            }

                            $imageUrl = 'https://via.placeholder.com/40';
                            if ($firstImage) {
                                if (str_starts_with($firstImage, 'http')) { $imageUrl = $firstImage; }
                                elseif (str_starts_with($firstImage, 'products/')) { $imageUrl = asset('storage/' . $firstImage); }
                                elseif (str_starts_with($firstImage, 'images/') || str_starts_with($firstImage, 'storage/')) { $imageUrl = asset($firstImage); }
                                else { $imageUrl = asset('storage/products/' . $firstImage); }
                            }
                        @endphp
                        <option value="{{ $product->id }}" data-image="{{ $imageUrl }}" data-code="{{ $product->design_code }}">
                            {{ $product->design_code }} {{ $product->product_name ? '- ' . $product->product_name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Global / Default Name Fallback (Optional) -->
            <div id="global-names-wrapper" style="display: none; margin-bottom: 24px;">
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                    <span style="font-size: 13px; color: #166534; font-weight: 600;">
                        <i class="bi bi-info-circle"></i> Default Design Names: If you leave any user-specific design field empty below, it will use these names as a fallback.
                    </span>
                </div>
                <div id="global-names-container"></div>
            </div>

            <!-- User-Specific Design Names Container -->
            <div id="user-names-wrapper" style="display: none; margin-bottom: 24px;">
                <label class="form-label" style="font-size: 16px; margin-bottom: 12px;">Customize Design Names for Selected Users</label>
                <div id="user-names-container"></div>
            </div>

            <div style="text-align: right;">
                <button type="submit" class="btn btn-primary" style="background: #2563eb; color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 500; cursor: pointer;">
                    Save Favorites
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-multiple').select2({
            width: '100%',
            placeholder: "Select users..."
        });

        function formatProduct(product) {
            if (!product.id) { return product.text; }
            var imageUrl = $(product.element).data('image');
            return $(
                '<div class="product-list-item">' +
                '<img src="' + imageUrl + '" />' +
                '<span>' + product.text + '</span>' +
                '</div>'
            );
        }

        $('#products').select2({
            width: '100%',
            placeholder: "Select designs...",
            templateResult: formatProduct,
            templateSelection: formatProduct
        });

        // Store active inputs to prevent wiping values when adding users/products
        let activeValues = {
            global: {},
            buyers: {},
            craftsmen: {}
        };

        function syncInputs() {
            // Save current global inputs
            $('#global-names-container .global-design-input').each(function() {
                activeValues.global[$(this).data('pid')] = $(this).val();
            });

            // Save user specific inputs
            $('#user-names-container .user-design-input').each(function() {
                let uType = $(this).data('type');
                let uid = $(this).data('uid');
                let pid = $(this).data('pid');
                if (!activeValues[uType]) activeValues[uType] = {};
                if (!activeValues[uType][uid]) activeValues[uType][uid] = {};
                activeValues[uType][uid][pid] = $(this).val();
            });

            let selectedProducts = $('#products').select2('data');
            let selectedBuyers = $('#buyers').select2('data');
            let selectedCraftsmen = $('#craftsmen').select2('data');

            let globalContainer = $('#global-names-container');
            let userContainer = $('#user-names-container');

            globalContainer.empty();
            userContainer.empty();

            if (selectedProducts.length === 0) {
                $('#global-names-wrapper').hide();
                $('#user-names-wrapper').hide();
                return;
            }

            // 1. Render Global Fallbacks
            $('#global-names-wrapper').show();
            selectedProducts.forEach(function(prod) {
                let pid = prod.id;
                let img = $(prod.element).data('image');
                let code = $(prod.element).data('code');
                let prevVal = activeValues.global[pid] || '';

                let row = `
                    <div class="dynamic-name-row">
                        <img src="${img}" alt="${code}" />
                        <div style="width: 140px; font-weight: 600; font-size: 13px;">${code}</div>
                        <input type="text" name="design_names[${pid}]" class="dynamic-name-input global-design-input" data-pid="${pid}" value="${prevVal}" placeholder="Default Name for ${code} (Optional fallback)" />
                    </div>
                `;
                globalContainer.append(row);
            });

            // 2. Render User-Specific Section if any users selected
            let totalUsers = selectedBuyers.length + selectedCraftsmen.length;
            if (totalUsers > 0) {
                $('#user-names-wrapper').show();

                // Buyers
                selectedBuyers.forEach(function(b) {
                    let bId = b.id;
                    let bName = $(b.element).data('name') || b.text;
                    let block = `
                        <div class="user-assignment-card">
                            <div class="user-badge-header">
                                <span style="font-weight: 700; color: #1f2937; font-size: 14px;">${bName}</span>
                                <span class="user-badge badge-buyer">Buyer</span>
                            </div>
                    `;

                    selectedProducts.forEach(function(prod) {
                        let pid = prod.id;
                        let img = $(prod.element).data('image');
                        let code = $(prod.element).data('code');
                        let savedVal = (activeValues.buyers && activeValues.buyers[bId] && activeValues.buyers[bId][pid]) ? activeValues.buyers[bId][pid] : '';

                        block += `
                            <div class="dynamic-name-row">
                                <img src="${img}" alt="${code}" />
                                <div style="width: 140px; font-weight: 600; font-size: 13px;">${code}</div>
                                <input type="text" 
                                    name="buyer_design_names[${bId}][${pid}]" 
                                    class="dynamic-name-input user-design-input" 
                                    data-type="buyers" 
                                    data-uid="${bId}" 
                                    data-pid="${pid}" 
                                    value="${savedVal}" 
                                    placeholder="Design name for this buyer (e.g. Bridal Set)" />
                            </div>
                        `;
                    });

                    block += `</div>`;
                    userContainer.append(block);
                });

                // Craftsmen
                selectedCraftsmen.forEach(function(c) {
                    let cId = c.id;
                    let cName = $(c.element).data('name') || c.text;
                    let block = `
                        <div class="user-assignment-card">
                            <div class="user-badge-header">
                                <span style="font-weight: 700; color: #1f2937; font-size: 14px;">${cName}</span>
                                <span class="user-badge badge-craftsman">Craftsman</span>
                            </div>
                    `;

                    selectedProducts.forEach(function(prod) {
                        let pid = prod.id;
                        let img = $(prod.element).data('image');
                        let code = $(prod.element).data('code');
                        let savedVal = (activeValues.craftsmen && activeValues.craftsmen[cId] && activeValues.craftsmen[cId][pid]) ? activeValues.craftsmen[cId][pid] : '';

                        block += `
                            <div class="dynamic-name-row">
                                <img src="${img}" alt="${code}" />
                                <div style="width: 140px; font-weight: 600; font-size: 13px;">${code}</div>
                                <input type="text" 
                                    name="craftsman_design_names[${cId}][${pid}]" 
                                    class="dynamic-name-input user-design-input" 
                                    data-type="craftsmen" 
                                    data-uid="${cId}" 
                                    data-pid="${pid}" 
                                    value="${savedVal}" 
                                    placeholder="Design name for this craftsman (e.g. Model Gold Casting)" />
                            </div>
                        `;
                    });

                    block += `</div>`;
                    userContainer.append(block);
                });
            } else {
                $('#user-names-wrapper').hide();
            }
        }

        $('#products, #buyers, #craftsmen').on('change', syncInputs);
    });
</script>
@endsection