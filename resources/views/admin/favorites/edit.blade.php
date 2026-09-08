@extends('admin.layouts.app')

@section('title', 'Edit Favorites')

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
        border-color: #e11d48;
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
    .dynamic-name-row {
        display: flex;
        align-items: center;
        gap: 14px;
        background: #f9fafb;
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        margin-bottom: 10px;
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
        border-color: #e11d48;
        box-shadow: 0 0 0 2px rgba(225, 29, 72, 0.15);
    }
    .user-info-banner {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 14px 18px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }
</style>

<div>
    <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
        <a href="{{ route('admin.favorites.index') }}" class="btn btn-outline-secondary" style="padding: 6px 12px; text-decoration: none; border: 1px solid #d1d5db; border-radius: 6px; color: #374151;">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <h2 style="margin: 0; font-size: 20px; font-weight: 700;">Edit Favorites</h2>
    </div>

    @if(session('error'))
        <div class="alert alert-danger" style="color: #b91c1c; background: #fef2f2; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f87171;">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <!-- Assigned User Banner -->
        <div class="user-info-banner">
            <div>
                <span style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 700;">Assigned User</span>
                <h3 style="margin: 2px 0 0; font-size: 18px; font-weight: 700; color: #1e293b;">
                    {{ $user->name ?? $user->full_name ?? 'User #' . $user->id }}
                    <span style="font-size: 13px; font-weight: 500; color: #64748b;">
                        ({{ $user_type === 'buyer' ? ($user->bp_code ?? 'Buyer') : ($user->craftman_code ?? 'Craftsman') }})
                    </span>
                </h3>
            </div>
            <span style="padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700; text-transform: uppercase; background: {{ $user_type === 'buyer' ? '#eff6ff' : '#fef3c7' }}; color: {{ $user_type === 'buyer' ? '#1d4ed8' : '#b45309' }}; border: 1px solid {{ $user_type === 'buyer' ? '#bfdbfe' : '#fde68a' }};">
                {{ ucfirst($user_type) }}
            </span>
        </div>

        <form action="{{ route('admin.favorites.update', [$user->id, $user_type]) }}" method="POST">
            @csrf
            @method('PUT')

            @php
                $selectedProductIds = $favorites->pluck('product_id')->toArray();
                $existingNamesMap = $favorites->pluck('design_name', 'product_id')->toArray();
            @endphp

            <div style="margin-bottom: 24px;">
                <label class="form-label">Selected Designs</label>
                <select name="product_ids[]" id="products" class="form-select select2-products" multiple="multiple">
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
                            $isSelected = in_array($product->id, $selectedProductIds);
                        @endphp
                        <option value="{{ $product->id }}" 
                            data-image="{{ $imageUrl }}" 
                            data-code="{{ $product->design_code }}"
                            {{ $isSelected ? 'selected' : '' }}>
                            {{ $product->design_code }} {{ $product->product_name ? '- ' . $product->product_name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Custom Design Names -->
            <div id="dynamic-names-wrapper" style="margin-bottom: 24px;">
                <label class="form-label">Custom Design Names for this {{ ucfirst($user_type) }}</label>
                <div id="dynamic-names-container"></div>
            </div>

            <div style="text-align: right;">
                <button type="submit" class="btn btn-primary" style="background: #e11d48; color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 500; cursor: pointer;">
                    Update Favorites
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        let initialNames = @json($existingNamesMap);

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

        function renderRows() {
            let selectedData = $('#products').select2('data');
            let container = $('#dynamic-names-container');
            let wrapper = $('#dynamic-names-wrapper');

            let currentValues = {};
            container.find('.dynamic-name-input').each(function() {
                currentValues[$(this).data('pid')] = $(this).val();
            });

            container.empty();

            if (selectedData.length > 0) {
                wrapper.show();
                selectedData.forEach(function(item) {
                    let pid = item.id;
                    let img = $(item.element).data('image');
                    let code = $(item.element).data('code');

                    let val = currentValues.hasOwnProperty(pid) 
                        ? currentValues[pid] 
                        : (initialNames[pid] || '');

                    let row = `
                        <div class="dynamic-name-row">
                            <img src="${img}" alt="${code}" />
                            <div style="width: 140px; font-weight: 600; font-size: 13px;">${code}</div>
                            <input type="text" 
                                name="design_names[${pid}]" 
                                class="dynamic-name-input" 
                                data-pid="${pid}" 
                                value="${val}" 
                                placeholder="Custom name for this design (Optional)" />
                        </div>
                    `;
                    container.append(row);
                });
            } else {
                wrapper.hide();
            }
        }

        renderRows();
        $('#products').on('change', renderRows);
    });
</script>
@endsection