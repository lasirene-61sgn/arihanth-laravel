@extends('admin.layouts.app')

@section('title', 'Edit Work Order')

@section('styles')
<style>
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
    .existing-image-item {
        position: relative;
        width: 120px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 5px;
    }
    .existing-image-item img {
        width: 100%;
        height: 100px;
        object-fit: cover;
        border-radius: 2px;
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
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Work Order</h1>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Work Order Details</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.work-order.update', $workOrder) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="return_url" value="{{ old('return_url', request('return_url')) }}">
                        @php
                            $currentTab = request('tab', 'new-orders');
                            if (request('return_url')) {
                                parse_str(parse_url(request('return_url'), PHP_URL_QUERY), $query);
                                if (isset($query['tab'])) {
                                    $currentTab = $query['tab'];
                                }
                            }
                        @endphp
                        <input type="hidden" name="tab" value="{{ old('tab', $currentTab) }}">
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="work_order_number" class="form-label">Work Order Number</label>
                                <input type="text" class="form-control" id="work_order_number" value="{{ $workOrder->work_order_number }}" readonly>
                            </div>

                            <div class="col-md-2 mb-3">
                                <label class="form-label">Order Image</label>
                                <div class="border rounded text-center bg-light d-flex align-items-center justify-content-center overflow-hidden" style="height: 100px;">
                                    @if($workOrder->preview_image_url)
                                        <div style="height: 100%; display: flex; align-items: center; justify-content: center; cursor: pointer; position: relative; width: 100%;" 
                                             onclick="openUniversalPreview('{{ $workOrder->preview_image_url }}', '{{ $workOrder->file_type }}')">
                                            @if($workOrder->file_type === 'pdf')
                                                <div class="pdf-thumbnail-container" style="height: 100%; overflow: hidden;">
                                                     <canvas class="pdf-canvas" data-url="{{ $workOrder->preview_image_url }}" data-desired-width="300"></canvas>
                                                </div>
                                            @else
                                                <img src="{{ $workOrder->preview_image_url }}" style="height: 100%; width: auto;" title="Preview Image">
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted small">No Image</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="customer_name" class="form-label">Customer Name *</label>
                                <input type="text" class="form-control @error('customer_name') is-invalid @enderror" 
                                       id="customer_name" name="customer_name" value="{{ old('customer_name', $workOrder->customer_name) }}" required>
                                @error('customer_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="product_name" class="form-label">Product Name </label>
                                <input type="text" class="form-control @error('product_name') is-invalid @enderror" 
                                       id="product_name" name="product_name" value="{{ old('product_name', $workOrder->product_name) }}">
                               
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">Quantity *</label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                       id="quantity" name="quantity" value="{{ old('quantity', $workOrder->quantity) }}" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="due_date" class="form-label">Customer Due Date  *</label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                       id="due_date" name="due_date" value="{{ old('due_date', $workOrder->due_date ? $workOrder->due_date->format('Y-m-d') : '') }}" required>
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="bp_code" class="form-label">BP Code</label>
                                <select class="form-control @error('bp_code') is-invalid @enderror" 
                                       id="bp_code" name="bp_code">
                                    <option value="">Select Buyer BP Code</option>
                                    @foreach($buyers as $buyer)
                                        <option value="{{ $buyer->bp_code }}" data-name="{{ $buyer->business_name }}" 
                                            {{ (old('bp_code', $workOrder->bp_code) == $buyer->bp_code) ? 'selected' : '' }}>
                                            {{ $buyer->bp_code }} - {{ $buyer->business_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bp_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="reference_no" class="form-label">Reference No</label>
                                <input type="text" class="form-control @error('reference_no') is-invalid @enderror" 
                                       id="reference_no" name="reference_no" value="{{ old('reference_no', $workOrder->reference_no) }}">
                                @error('reference_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="product_category_id" class="form-label">Product Category</label>
                                <div class="custom-dropdown-container" id="category_container">
                                    <div class="custom-dropdown-display" id="category_display">--Select Category--</div>
                                    <div class="custom-dropdown-menu" id="category_menu">
                                        <div class="p-2 border-bottom d-flex gap-2">
                                            <input type="text" class="custom-dropdown-search flex-grow-1" id="category_search" placeholder="Search categories...">
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
                                    <select class="form-control @error('product_category_id') is-invalid @enderror" 
                                           id="product_category_id" name="product_category_id" style="display: none;">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ (old('product_category_id') == $category->id || $workOrder->product_category_id == $category->id || $workOrder->product_category == $category->name) ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('product_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3" id="subcategory-container" style="display: none;">
                                <label for="subcategory_id" class="form-label">Sub Category</label>
                                <div class="custom-dropdown-container" id="subcategory_container">
                                    <div class="custom-dropdown-display" id="subcategory_display">--Select Sub Category--</div>
                                    <div class="custom-dropdown-menu" id="subcategory_menu">
                                        <div class="p-2 border-bottom d-flex gap-2">
                                            <input type="text" class="custom-dropdown-search flex-grow-1" id="subcategory_search" placeholder="Search subcategories...">
                                        </div>
                                        <ul class="custom-dropdown-list" id="subcategory_list">
                                            <li class="custom-dropdown-item" data-value="">--Select Sub Category--</li>
                                        </ul>
                                    </div>
                                    <select class="form-control @error('subcategory_id') is-invalid @enderror" 
                                           id="subcategory_id" name="subcategory_id" style="display: none;">
                                        <option value="">Select Sub Category</option>
                                    </select>
                                </div>
                                @error('subcategory_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-control @error('type') is-invalid @enderror" 
                                       id="type" name="type">
                                    <option value="Piece" {{ (old('type', $workOrder->type) == 'Piece') ? 'selected' : '' }}>Piece</option>
                                    <option value="Pair" {{ (old('type', $workOrder->type) == 'Pair') ? 'selected' : '' }}>Pair</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="screw_name" class="form-label">Screw</label>
                                <select class="form-control @error('screw_name') is-invalid @enderror" id="screw_name" name="screw_name">
                                    <option value="">Select Screw</option>
                                    <option value="North Screw" {{ (old('screw_name', $workOrder->screw_name) == 'North Screw') ? 'selected' : '' }}>North Screw</option>
                                    <option value="South Screw" {{ (old('screw_name', $workOrder->screw_name) == 'South Screw') ? 'selected' : '' }}>South Screw</option>
                                </select>
                                @error('screw_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="weight_from" class="form-label">Weight From</label>
                                <input type="text" class="form-control @error('weight_from') is-invalid @enderror" 
                                       id="weight_from" name="weight_from" value="{{ old('weight_from', $workOrder->weight_from) }}">
                                @error('weight_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="weight_to" class="form-label">Weight To</label>
                                <input type="text" class="form-control @error('weight_to') is-invalid @enderror" 
                                       id="weight_to" name="weight_to" value="{{ old('weight_to', $workOrder->weight_to) }}">
                                @error('weight_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">HUID</label>
                                <select class="form-control @error('hallmark') is-invalid @enderror" 
                                       id="hallmark" name="hallmark">
                                    <option value="">Select Option</option>
                                    <option value="Yes" {{ (old('hallmark', $workOrder->hallmark) == 'Yes') ? 'selected' : '' }}>Yes</option>
                                    <option value="No" {{ (old('hallmark', $workOrder->hallmark) == 'No') ? 'selected' : '' }}>No</option>
                                </select>
                                @error('hallmark')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- <div class="col-md-6 mb-3">
                                <label for="hallmark" class="form-label">HUID</label>
                                <input type="text" class="form-control @error('hallmark') is-invalid @enderror" 
                                       id="hallmark" name="hallmark" value="{{ old('hallmark', $workOrder->hallmark) }}">
                                @error('hallmark')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> -->

                            <div class="col-md-6 mb-3">
                                <label for="rodium" class="form-label">Rodium</label>
                                <select class="form-control @error('rodium') is-invalid @enderror" 
                                       id="rodium" name="rodium">
                                    <option>Select Option</option>
                                    <option value="Yes" {{ (old('rodium', $workOrder->rodium) == 'Yes') ? 'selected' : '' }}>Yes</option>
                                    <option value="No" {{ (old('rodium', $workOrder->rodium) == 'No') ? 'selected' : '' }}>No</option>
                                </select>
                                @error('rodium')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Hook</label>
                                <select class="form-control @error('hook') is-invalid @enderror" 
                                       id="hook" name="hook">
                                    <option value="">Select Option</option>
                                    <option value="Single" {{ (old('hook', $workOrder->hook) == 'Single') ? 'selected' : '' }}>Single</option>
                                    <option value="Double" {{ (old('hook', $workOrder->hook) == 'Double') ? 'selected' : '' }}>Double</option>
                                </select>
                                @error('hook')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- <div class="col-md-6 mb-3">
                                <label for="rodium" class="form-label">Rodium</label>
                                <input type="text" class="form-control @error('rodium') is-invalid @enderror" 
                                       id="rodium" name="rodium" value="{{ old('rodium', $workOrder->rodium) }}">
                                @error('rodium')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> -->
                            
                            <!-- <div class="col-md-6 mb-3">
                                <label for="hook" class="form-label">Hook</label>
                                <input type="text" class="form-control @error('hook') is-invalid @enderror" 
                                       id="hook" name="hook" value="{{ old('hook', $workOrder->hook) }}">
                                @error('hook')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> -->
                            
                            <div class="col-md-6 mb-3">
                                <label for="size" class="form-label">Size</label>
                                <input type="text" class="form-control @error('size') is-invalid @enderror" 
                                       id="size" name="size" value="{{ old('size', $workOrder->size) }}">
                                @error('size')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="stone" class="form-label">Stone</label>
                                <select class="form-control @error('stone') is-invalid @enderror" 
                                       id="stone" name="stone">
                                    <option value="">Select Option</option>
                                    <option value="Yes" {{ (old('stone', $workOrder->stone) == 'Yes') ? 'selected' : '' }}>Yes</option>
                                    <option value="No" {{ (old('stone', $workOrder->stone) == 'No') ? 'selected' : '' }}>No</option>
                                </select>
                                @error('stone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Enamel</label>
                                <select class="form-control @error('enamel') is-invalid @enderror" 
                                       id="enamel" name="enamel">
                                    <option value="">Select Option</option>
                                    <option value="Yes" {{ (old('enamel', $workOrder->enamel) == 'Yes') ? 'selected' : '' }}>Yes</option>
                                    <option value="No" {{ (old('enamel', $workOrder->enamel) == 'No') ? 'selected' : '' }}>No</option>
                                </select>
                                @error('enamel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- <div class="col-md-6 mb-3">
                                <label for="stone" class="form-label">Stone</label>
                                <input type="text" class="form-control @error('stone') is-invalid @enderror" 
                                       id="stone" name="stone" value="{{ old('stone', $workOrder->stone) }}">
                                @error('stone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> -->
                            
                            <!-- <div class="col-md-6 mb-3">
                                <label for="enamel" class="form-label">Enamel</label>
                                <input type="text" class="form-control @error('enamel') is-invalid @enderror" 
                                       id="enamel" name="enamel" value="{{ old('enamel', $workOrder->enamel) }}">
                                @error('enamel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> -->
                            
                            <div class="col-md-6 mb-3">
                                <label for="length" class="form-label">Length</label>
                                <input type="text" class="form-control @error('length') is-invalid @enderror" 
                                       id="length" name="length" value="{{ old('length', $workOrder->length) }}">
                                @error('length')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="product_code" class="form-label">Product Code</label>
                                <input type="text" class="form-control @error('product_code') is-invalid @enderror" 
                                       id="product_code" name="product_code" value="{{ old('product_code', $workOrder->product_code) }}">
                                @error('product_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="craftsman_due_date" class="form-label">Craftsman Due Date</label>
                                <input type="date" class="form-control @error('craftsman_due_date') is-invalid @enderror" 
                                       id="craftsman_due_date" name="craftsman_due_date" value="{{ old('craftsman_due_date', $workOrder->craftsman_due_date ? $workOrder->craftsman_due_date->format('Y-m-d') : '') }}">
                                @error('craftsman_due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="product_images" class="form-label">Add More Images (Supports Multiple)</label>
                                <input type="file" class="form-control @error('product_images') is-invalid @enderror" 
                                       id="product_images" name="product_images[]" accept="image/*" multiple>
                                <div id="multi_image_preview_container"></div>
                                
                                @if($workOrder->images->count() > 0 || $workOrder->product_image)
                                    <div class="mt-3">
                                        <label class="form-label">Current Gallery / Images</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            {{-- Primary Image --}}
                                            @if($workOrder->product_image)
                                                <div class="existing-image-item">
                                                    <img src="{{ asset($workOrder->product_image) }}" alt="Primary Image">
                                                    <div class="form-check mt-1">
                                                        <input class="form-check-input" type="checkbox" name="remove_product_image" id="remove_primary" value="1">
                                                        <label class="form-check-label text-danger small" for="remove_primary">Remove (Primary)</label>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Additional Images --}}
                                            @foreach($workOrder->images as $image)
                                                <div class="existing-image-item">
                                                    <img src="{{ asset($image->image_path) }}" alt="Work Order Image">
                                                    <div class="form-check mt-1">
                                                        <input class="form-check-input" type="checkbox" name="remove_images[]" id="remove_img_{{ $image->id }}" value="{{ $image->id }}">
                                                        <label class="form-check-label text-danger small" for="remove_img_{{ $image->id }}">Remove</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                @error('product_images')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="narration_craftsman" class="form-label">Narration for Craftsman</label>
                                <textarea class="form-control @error('narration_craftsman') is-invalid @enderror" 
                                          id="narration_craftsman" name="narration_craftsman" rows="3">{{ old('narration_craftsman', $workOrder->narration_craftsman) }}</textarea>
                                @error('narration_craftsman')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="narration_admin" class="form-label">Narration for Admin</label>
                                <textarea class="form-control @error('narration_admin') is-invalid @enderror" 
                                          id="narration_admin" name="narration_admin" rows="3">{{ old('narration_admin', $workOrder->narration_admin) }}</textarea>
                                @error('narration_admin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.work-order.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Work Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        
        initSearchableDropdown('category_container', 'category_display', 'category_menu', 'category_search', 'category_list', 'product_category_id', '--Select Category--');
        initSearchableDropdown('subcategory_container', 'subcategory_display', 'subcategory_menu', 'subcategory_search', 'subcategory_list', 'subcategory_id', '--Select Sub Category--');
        const categorySelect = document.getElementById('product_category_id');
        const subcategoryContainer = document.getElementById('subcategory-container');
        const subcategorySelect = document.getElementById('subcategory_id');
        const bpCodeSelect = document.getElementById('bp_code');
        const customerNameInput = document.getElementById('customer_name');
        
        // Buyer Sync
        if(bpCodeSelect) {
            bpCodeSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if(selectedOption.value) {
                    customerNameInput.value = selectedOption.getAttribute('data-name') || '';
                }
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
            
            return fetch(`{{ url('/admin/product/get-subcategories') }}?category_id=${categoryId}`)
                .then(response => response.ok ? response.json() : [])
                .then(data => {
                    const list = Array.isArray(data) ? data : (data.subcategories || []);
                    if (list.length > 0) {
                        subcategoryContainer.style.display = '';
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
                        subcategoryContainer.style.display = '';
                    }
                    return list;
                })
                .catch(error => {
                    console.error('Error fetching subcategories:', error);
                    return [];
                });
        }

        categorySelect.addEventListener('change', function() {
            const id = this.value;
            refreshSubcategories(id);
        });

        // Initialize on load
        function initializeSubcategory() {
            if (categorySelect.value) {
                refreshSubcategories(categorySelect.value).then(() => {
                    const savedSubcategoryName = @json($workOrder->subcategory);
                    const savedSubcategoryId = @json($workOrder->subcategory_id);
                    
                    if (savedSubcategoryName || savedSubcategoryId) {
                        const subcategoryOptions = subcategorySelect.options;
                        const listItems = document.querySelectorAll('#subcategory_list .custom-dropdown-item');
                        
                        for (let i = 0; i < subcategoryOptions.length; i++) {
                            if (subcategoryOptions[i].value == savedSubcategoryId || 
                               (savedSubcategoryName && subcategoryOptions[i].text.trim().toLowerCase() === savedSubcategoryName.trim().toLowerCase())) {
                                subcategorySelect.selectedIndex = i;
                                document.getElementById('subcategory_display').textContent = subcategoryOptions[i].text;
                                
                                listItems.forEach(item => {
                                    if(item.dataset.value == subcategoryOptions[i].value) {
                                        item.classList.add('selected');
                                    }
                                });
                                break;
                            }
                        }
                        subcategoryContainer.style.display = '';
                    } else {
                        subcategoryContainer.style.display = '';
                    }
                });
            }
        }
        
        initializeSubcategory();

        // Multiple Image Preview Logic
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
                            div.className = 'preview-item';
                            div.innerHTML = `<img src="${e.target.result}">`;
                            multiPreviewContainer.appendChild(div);
                        }
                        reader.readAsDataURL(file);
                    });
                }
            });
        }
    });
</script>
@endsection