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
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #e5e7eb;
    }
    .dynamic-names-container {
        margin-top: 16px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .dynamic-name-row {
        display: flex;
        align-items: center;
        gap: 16px;
        background: #f9fafb;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }
    .dynamic-name-row img {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 6px;
    }
    .dynamic-name-input {
        flex-grow: 1;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        outline: none;
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
        <form action="{{ route('super-admin.favorites.store') }}" method="POST">
            @csrf
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                <!-- Buyers -->
                <div>
                    <label class="form-label">Select Buyers</label>
                    <select name="buyer_ids[]" id="buyers" class="form-select select2-multiple" multiple="multiple">
                        @foreach($buyers as $buyer)
                            <option value="{{ $buyer->id }}">{{ $buyer->name ?? $buyer->full_name }} ({{ $buyer->bp_code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Craftsmen -->
                <div>
                    <label class="form-label">Select Craftsmen</label>
                    <select name="craftsman_ids[]" id="craftsmen" class="form-select select2-multiple" multiple="multiple">
                        @foreach($craftsmen as $craftsman)
                            <option value="{{ $craftsman->id }}">{{ $craftsman->name }} ({{ $craftsman->craftman_code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <label class="form-label">Select Designs</label>
                <select name="product_ids[]" id="products" class="form-select select2-products" multiple="multiple" required>
                    @foreach($products as $product)
                        @php
                            $imagesCount = $product->images->count();
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

            <!-- Dynamic Design Names -->
            <div id="dynamic-names-wrapper" style="display: none; margin-bottom: 24px;">
                <label class="form-label">Provide Names for Selected Designs</label>
                <div id="dynamic-names-container" class="dynamic-names-container">
                    <!-- JS will populate this -->
                </div>
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

        function formatProduct (product) {
            if (!product.id) { return product.text; }
            var imageUrl = $(product.element).data('image');
            var $product = $(
                '<div class="product-list-item">' +
                '<img src="' + imageUrl + '" />' +
                '<span>' + product.text + '</span>' +
                '</div>'
            );
            return $product;
        };

        $('#products').select2({
            width: '100%',
            placeholder: "Select designs...",
            templateResult: formatProduct,
            templateSelection: formatProduct
        });

        // Dynamic Name Fields
        $('#products').on('change', function() {
            let selectedData = $(this).select2('data');
            let container = $('#dynamic-names-container');
            let wrapper = $('#dynamic-names-wrapper');
            
            // Store current values
            let currentValues = {};
            container.find('.dynamic-name-input').each(function() {
                currentValues[$(this).data('id')] = $(this).val();
            });

            container.empty();

            if (selectedData.length > 0) {
                wrapper.show();
                selectedData.forEach(function(item) {
                    let id = item.id;
                    let text = item.text;
                    let img = $(item.element).data('image');
                    let code = $(item.element).data('code');
                    
                    let existingVal = currentValues[id] || '';

                    let row = `
                        <div class="dynamic-name-row">
                            <img src="${img}" alt="${code}" />
                            <div style="width: 150px; font-weight: 600; font-size: 14px;">${code}</div>
                            <input type="text" name="design_names[${id}]" class="dynamic-name-input" data-id="${id}" value="${existingVal}" placeholder="Enter name for this design (Optional)" />
                        </div>
                    `;
                    container.append(row);
                });
            } else {
                wrapper.hide();
            }
        });
    });
</script>
@endsection
