@extends('key-user.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Create New Work Order</h1>
            <nav class="flex text-sm text-gray-500 mt-1">
                <a href="{{ route('key-user.dashboard') }}" class="hover:text-indigo-600 transition">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('key-user.work-order.index') }}" class="hover:text-indigo-600 transition">Work Orders</a>
                <span class="mx-2">/</span>
                <span class="text-gray-800 font-medium">Create</span>
            </nav>
        </div>
        <a href="{{ route('key-user.work-order.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
            <i class="bi bi-arrow-left mr-2"></i> Back to List
        </a>
    </div>

    <style>
        /* Custom Searchable Dropdown Styles */
        .custom-dropdown-container {
            position: relative;
            width: 100%;
        }
        .custom-dropdown-display {
            width: 100%;
            padding: 0.5rem 1rem;
            background-color: #fff;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            outline: none;
            transition: all 0.2s;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .custom-dropdown-display:focus {
            box-shadow: 0 0 0 2px #6366f1;
            border-color: #6366f1;
        }
        .custom-dropdown-display:after {
            content: "\F282";
            font-family: "bootstrap-icons";
            color: #9ca3af;
            font-size: 0.75rem;
        }
        .custom-dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background-color: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 0 0 0.5rem 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 50;
            display: none;
            padding: 0.75rem;
        }
        .custom-dropdown-search {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
            outline: none;
            transition: all 0.2s;
        }
        .custom-dropdown-search:focus {
            box-shadow: 0 0 0 2px #6366f1;
            border-color: #6366f1;
        }
        .custom-dropdown-list {
            max-height: 15rem;
            overflow-y: auto;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .custom-dropdown-item {
            padding: 0.5rem 1rem;
            cursor: pointer;
            font-size: 0.875rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }
        .custom-dropdown-item:hover {
            background-color: #f9fafb;
        }
        .custom-dropdown-item.selected {
            background-color: #eef2ff;
            color: #4338ca;
            font-weight: 700;
        }
        .custom-dropdown-item.hidden {
            display: none;
        }
    </style>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h4 class="text-lg font-bold text-gray-800">Order Details</h4>
        </div>

        <div class="p-6 lg:p-8">
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                    <div class="flex">
                        <i class="bi bi-exclamation-triangle-fill text-red-500 mr-3"></i>
                        <ul class="text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('key-user.work-order.store') }}" method="POST" enctype="multipart/form-data" id="workOrderForm" class="space-y-8">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end bg-indigo-50/30 p-6 rounded-2xl border border-indigo-100">
                    <div class="md:col-span-4 space-y-1">
                        <label for="product_code" class="block text-sm font-bold text-indigo-900">Product / Design Code Lookup</label>
                        <div class="flex shadow-sm">
                            <input type="text" id="product_code" name="product_code" value="{{ old('product_code') }}" 
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" 
                                   placeholder="Enter code to lookup">
                            <button type="button" id="test_lookup_btn" 
                                    class="px-4 py-2 bg-indigo-600 text-white font-bold text-sm rounded-r-lg hover:bg-indigo-700 transition">
                                Test
                            </button>
                        </div>
                        <p class="text-[11px] text-indigo-500 italic mt-1" id="lookup_status">Enter code for existing product, or leave blank for new.</p>
                    </div>

                    <div class="md:col-span-2 flex flex-col items-center">
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Preview</label>
                        <div id="design_image_container" class="h-20 w-20 rounded-xl border-2 border-dashed border-gray-300 bg-white flex items-center justify-center overflow-hidden shadow-inner cursor-pointer" onclick="openPreviewFromLookup()">
                            <span id="no_image_text" class="text-[10px] text-gray-400 font-bold uppercase">None</span>
                            <img id="design_image_preview" src="" class="hidden max-h-full max-w-full object-contain">
                            <canvas id="design_pdf_preview" class="hidden max-h-full max-w-full object-contain"></canvas>
                        </div>
                    </div>

                    <div class="md:col-span-6 space-y-1">
                        <label for="design_code" class="block text-sm font-semibold text-gray-700">Auto-Generated Design Code</label>
                        <input type="text" id="design_code" name="design_code" readonly 
                               class="w-full px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg text-gray-500 font-mono text-sm cursor-not-allowed shadow-inner">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    
                    <div class="space-y-1">
                        <label for="bp_code" class="block text-sm font-semibold text-gray-700">Buyer Business Partner <span class="text-red-500">*</span></label>
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
                            <select name="bp_code" id="bp_code" style="display: none;" required>
                                <option value="">Select Buyer BP Code</option>
                                @foreach($buyers as $buyer)
                                    <option value="{{ $buyer->bp_code }}" data-name="{{ $buyer->business_name }}" {{ old('bp_code') == $buyer->bp_code ? 'selected' : '' }}>
                                        {{ $buyer->bp_code }} - {{ $buyer->business_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label for="customer_name" class="block text-sm font-semibold text-gray-700">Customer Name <span class="text-red-500">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1">
                        <label for="product_category_id" class="block text-sm font-semibold text-gray-700">Product Category <span class="text-red-500"></span></label>
                        <div class="custom-dropdown-container" id="category_container">
                            <div class="custom-dropdown-display" id="category_display">--Select Category--</div>
                            <div class="custom-dropdown-menu" id="category_menu">
                                <div class="p-2 border-b border-gray-100 flex gap-2">
                                    <input type="text" class="custom-dropdown-search flex-1" id="category_search" placeholder="Search categories...">
                                    <button type="button" id="addCategoryBtn" class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-bold hover:bg-indigo-100 transition uppercase">New</button>
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
                            <select id="product_category_id" name="product_category_id" style="display: none;" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('product_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1" id="subcategory-container" style="display: none;">
                        <label for="subcategory_id" class="block text-sm font-semibold text-gray-700">Sub Category</label>
                        <div class="custom-dropdown-container" id="subcategory_container">
                            <div class="custom-dropdown-display" id="subcategory_display">--Select Sub Category--</div>
                            <div class="custom-dropdown-menu" id="subcategory_menu">
                                <div class="p-2 border-b border-gray-100 flex gap-2">
                                    <input type="text" class="custom-dropdown-search flex-1" id="subcategory_search" placeholder="Search subcategories...">
                                    <button type="button" id="addSubcategoryBtn" class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-bold hover:bg-indigo-100 transition uppercase">New</button>
                                </div>
                                <ul class="custom-dropdown-list" id="subcategory_list">
                                    <li class="custom-dropdown-item" data-value="">--Select Sub Category--</li>
                                </ul>
                            </div>
                            <select id="subcategory_id" name="subcategory_id" style="display: none;">
                                <option value="">Select Sub Category</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1 category-option" data-opt="has_open_close" style="display:none;">
                        <label for="open_close" class="block text-sm font-semibold text-gray-700">Open/Close</label>
                        <select id="open_close" name="open_close" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                            <option value="">Select Status</option>
                            <option value="Open" {{ old('open_close') == 'Open' ? 'selected' : '' }}>Open</option>
                            <option value="Close" {{ old('open_close') == 'Close' ? 'selected' : '' }}>Close</option>
                        </select>
                    </div>

                    <div class="space-y-1 category-option" data-opt="has_hook" style="display:none;">
                        <label for="hook" class="block text-sm font-semibold text-gray-700">Hook</label>
                        <input type="text" id="hook" name="hook" value="{{ old('hook') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1 category-option" data-opt="has_enamel" style="display:none;">
                        <label for="enamel" class="block text-sm font-semibold text-gray-700">Enamel</label>
                        <input type="text" id="enamel" name="enamel" value="{{ old('enamel') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1 category-option" data-opt="has_rodium" style="display:none;">
                        <label for="rodium" class="block text-sm font-semibold text-gray-700">Rodium</label>
                        <input type="text" id="rodium" name="rodium" value="{{ old('rodium') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1 category-option" data-opt="has_stone" style="display:none;">
                        <label for="stone" class="block text-sm font-semibold text-gray-700">Stone</label>
                        <input type="text" id="stone" name="stone" value="{{ old('stone') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1">
                        <label for="product_name" class="block text-sm font-semibold text-gray-700">Product Name <span class="text-red-500"></span></label>
                        <input type="text" id="product_name" name="product_name" value="{{ old('product_name') }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1">
                        <label for="quantity" class="block text-sm font-semibold text-gray-700">Quantity <span class="text-red-500">*</span></label>
                        <input type="number" id="quantity" name="quantity" value="{{ old('quantity') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1">
                        <label for="type" class="block text-sm font-semibold text-gray-700">Type</label>
                        <select id="type" name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                            <option value="">Select Type</option>
                            <option value="Piece" {{ old('type') == 'Piece' ? 'selected' : '' }}>Piece</option>
                            <option value="Pair" {{ old('type') == 'Pair' ? 'selected' : '' }}>Pair</option>
                            <option value="Set" {{ old('type') == 'Set' ? 'selected' : '' }}>Set</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="due_date" class="block text-sm font-semibold text-gray-700">Due Date <span class="text-red-500">*</span></label>
                        <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1">
                        <label for="reference_no" class="block text-sm font-semibold text-gray-700">Reference No</label>
                        <input type="text" id="reference_no" name="reference_no" value="{{ old('reference_no') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 pt-4 border-t border-gray-100">
                    <div class="space-y-1">
                        <label for="weight_from" class="block text-[11px] font-bold text-gray-400 uppercase">Weight From</label>
                        <input type="text" id="weight_from" name="weight_from" value="{{ old('weight_from') }}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:border-indigo-500 outline-none">
                    </div>
                    <div class="space-y-1">
                        <label for="weight_to" class="block text-[11px] font-bold text-gray-400 uppercase">Weight To</label>
                        <input type="text" id="weight_to" name="weight_to" value="{{ old('weight_to') }}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:border-indigo-500 outline-none">
                    </div>
                    <div class="space-y-1">
                        <label for="size" class="block text-[11px] font-bold text-gray-400 uppercase">Size</label>
                        <input type="text" id="size" name="size" value="{{ old('size') }}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:border-indigo-500 outline-none">
                    </div>
                    <div class="space-y-1">
                        <label for="length" class="block text-[11px] font-bold text-gray-400 uppercase">Length</label>
                        <input type="text" id="length" name="length" value="{{ old('length') }}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:border-indigo-500 outline-none">
                    </div>
                    <div class="space-y-1">
                        <label for="hallmark" class="block text-[11px] font-bold text-gray-400 uppercase">Hallmark</label>
                        <input type="text" id="hallmark" name="hallmark" value="{{ old('hallmark') }}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:border-indigo-500 outline-none">
                    </div>
                </div>

                <div class="md:col-span-4 space-y-2 pt-4">
                    <label for="product_images" class="block text-sm font-semibold text-gray-700">Product Images (Supports Multiple)</label>
                    <input type="file" id="product_images" name="product_images[]" accept="image/*" multiple
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition">
                    <div id="multi_image_preview_container" class="flex flex-wrap gap-2 mt-2"></div>
                    <p class="text-[10px] text-gray-400">You can select and upload multiple images at once.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 pt-4 border-t border-gray-100">
                    <div class="space-y-1">
                        <label for="narration_craftsman" class="block text-sm font-semibold text-gray-700">Narration (Craftsman)</label>
                        <textarea id="narration_craftsman" name="narration_craftsman" rows="2" 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">{{ old('narration_craftsman') }}</textarea>
                    </div>
                    <div class="space-y-1">
                        <label for="narration_admin" class="block text-sm font-semibold text-gray-700">Narration (Admin)</label>
                        <textarea id="narration_admin" name="narration_admin" rows="2" 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">{{ old('narration_admin') }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-8 border-t border-gray-100">
                    <a href="{{ route('key-user.work-order.index') }}" class="px-6 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-10 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition shadow-md">
                        Create Work Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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

        // 1. Dynamic Technical Fields Logic (Show/Hide Hook, Stone, etc.)
        function toggleCategoryOptions(categoryId) {
            optionBlocks.forEach(b => b.style.display = 'none');
            if (!categoryId) return Promise.resolve();
            
            return fetch(`{{ url('/key-user/product/get-category-options') }}?category_id=${categoryId}`)
                .then(r => r.json())
                .then(data => {
                    Object.keys(data).forEach(key => {
                        if (data[key]) {
                            const el = document.querySelector(`.category-option[data-opt="${key}"]`);
                            if (el) el.style.display = 'block';
                        }
                    });
                    return data;
                });
        }

        // 2. Subcategory fetch logic
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
            
            subcategoryContainer.style.display = 'block'; 

            return fetch(`{{ url('/key-user/product/get-subcategories') }}?category_id=${categoryId}`)
                .then(r => r.json())
                .then(list => {
                    list.forEach(s => {
                        // Update hidden select
                        const opt = document.createElement('option');
                        opt.value = s.id; 
                        opt.textContent = s.name;
                        subcategorySelect.appendChild(opt);

                        // Update custom list
                        const li = document.createElement('li');
                        li.className = 'custom-dropdown-item';
                        li.dataset.value = s.id;
                        li.textContent = s.name;
                        listContainer.appendChild(li);
                    });
                    return list;
                });
        }

        let currentLookupFile = { url: '', type: 'image' };

        window.openPreviewFromLookup = function() {
            if (currentLookupFile.url) {
                openUniversalPreview(currentLookupFile.url, currentLookupFile.type);
            }
        };

        // 3. MAIN LOOKUP LOGIC
        function performLookup(code) {
            if (!code) return;

            lookupStatus.textContent = 'Looking up...';
            lookupStatus.className = 'text-info';

            fetch(`{{ url('/key-user/work-order/get-product-details') }}?product_code=${encodeURIComponent(code)}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const setFieldValue = (elementId, value) => {
                            const element = document.getElementById(elementId);
                            if (element && value !== undefined && value !== null) {
                                element.value = value;
                            }
                        };

                        setFieldValue('product_name', data.product.product_name);
                        setFieldValue('design_code', data.product.design_code);
                        setFieldValue('hallmark', data.product.hallmark);
                        setFieldValue('rodium', data.product.rodium);
                        setFieldValue('hook', data.product.hook);
                        setFieldValue('size', data.product.size);
                        setFieldValue('stone', data.product.stone);
                        setFieldValue('enamel', data.product.enamel);
                        setFieldValue('length', data.product.length);
                        setFieldValue('weight_from', data.product.weight_from);
                        setFieldValue('weight_to', data.product.weight_to);
                        setFieldValue('relabel_code', data.product.relabel_code);

                        if (data.product.product_category_id) {
                            categorySelect.value = data.product.product_category_id;
                            
                            // Update Category Display
                            const categoryItem = document.querySelector(`#category_list .custom-dropdown-item[data-value="${data.product.product_category_id}"]`);
                            if (categoryItem) {
                                document.getElementById('category_display').textContent = categoryItem.textContent.trim();
                                document.querySelectorAll('#category_list .custom-dropdown-item').forEach(i => i.classList.remove('selected'));
                                categoryItem.classList.add('selected');
                            }

                            const togglePromise = toggleCategoryOptions(data.product.product_category_id);
                            const refreshPromise = refreshSubcategories(data.product.product_category_id);
                            
                            Promise.all([togglePromise, refreshPromise]).then(() => {
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

                        // Image/PDF Preview
                        const imgElem = document.getElementById('design_image_preview');
                        const canvasElem = document.getElementById('design_pdf_preview');
                        const noText = document.getElementById('no_image_text');
                        
                        imgElem.classList.add('hidden');
                        canvasElem.classList.add('hidden');
                        noText.classList.remove('hidden');
                        currentLookupFile = { url: '', type: 'image' };

                        if (data.product.product_image_url) {
                            currentLookupFile = { 
                                url: data.product.product_image_url, 
                                type: data.product.file_type || 'image' 
                            };
                            noText.classList.add('hidden');
                            
                            if (currentLookupFile.type === 'pdf') {
                                canvasElem.classList.remove('hidden');
                                renderPdfToCanvas(canvasElem, currentLookupFile.url, 80);
                            } else {
                                imgElem.src = currentLookupFile.url;
                                imgElem.classList.remove('hidden');
                            }
                        }

                        lookupStatus.textContent = 'Product found!';
                        lookupStatus.className = 'text-success';
                    } else {
                        lookupStatus.textContent = data.message || 'Product not found';
                        lookupStatus.className = 'text-danger';
                        document.getElementById('design_image_preview').classList.add('hidden');
                        document.getElementById('design_pdf_preview').classList.add('hidden');
                        document.getElementById('no_image_text').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Lookup error:', error);
                    lookupStatus.textContent = 'Lookup failed: ' + error.message;
                    lookupStatus.className = 'text-danger';
                });
        }

        productCodeInput.addEventListener('blur', function() {
            const code = this.value.trim();
            performLookup(code);
        });

        // Test button
        document.getElementById('test_lookup_btn').addEventListener('click', function() {
            const code = productCodeInput.value.trim();
            performLookup(code);
        });

        // Add Category
        document.getElementById('addCategoryBtn').addEventListener('click', function(e) {
            e.stopPropagation();
            const name = prompt('Enter new category name');
            if (!name) return;
            
            const opts = {
                has_hook: confirm('Enable Hook option for this category?') ? 1 : 0,
                has_enamel: confirm('Enable Enamel option?') ? 1 : 0,
                has_rodium: confirm('Enable Rodium option?') ? 1 : 0,
                has_open_close: confirm('Enable Open/Close option?') ? 1 : 0,
                has_stone: confirm('Enable Stone option?') ? 1 : 0,
            };
            
            fetch('{{ route("key-user.product-category.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name, ...opts })
            })
            .then(response => response.json())
            .then(data => {
                if (data.category) {
                    // Update hidden select
                    const opt = document.createElement('option');
                    opt.value = data.category.id;
                    opt.textContent = data.category.name;
                    opt.selected = true;
                    categorySelect.appendChild(opt);

                    // Update custom list
                    const li = document.createElement('li');
                    li.className = 'custom-dropdown-item selected';
                    li.dataset.value = data.category.id;
                    li.textContent = data.category.name;
                    document.getElementById('category_list').appendChild(li);
                    
                    // Update display
                    document.getElementById('category_display').textContent = data.category.name;

                    toggleCategoryOptions(data.category.id);
                    refreshSubcategories(data.category.id);
                } else {
                    alert('Failed to create category');
                }
            })
            .catch(error => {
                console.error('Error creating category:', error);
                alert('Failed to create category');
            });
        });

        // Add Subcategory
        document.getElementById('addSubcategoryBtn').addEventListener('click', function(e) {
            e.stopPropagation();
            const parentId = categorySelect.value;
            if (!parentId) {
                alert('Select a category first');
                return;
            }
            
            const name = prompt('Enter new subcategory name');
            if (!name) return;
            
            fetch('{{ route("key-user.product-category.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ parent_category_id: parentId, name })
            })
            .then(response => response.json())
            .then(data => {
                if (data.subcategory) {
                    // Update hidden select
                    const opt = document.createElement('option');
                    opt.value = data.subcategory.id;
                    opt.textContent = data.subcategory.name;
                    opt.selected = true;
                    subcategorySelect.appendChild(opt);

                    // Update custom list
                    const li = document.createElement('li');
                    li.className = 'custom-dropdown-item selected';
                    li.dataset.value = data.subcategory.id;
                    li.textContent = data.subcategory.name;
                    document.getElementById('subcategory_list').appendChild(li);

                    // Update display
                    document.getElementById('subcategory_display').textContent = data.subcategory.name;
                } else {
                    alert('Failed to create subcategory');
                }
            })
            .catch(error => {
                console.error('Error creating subcategory:', error);
                alert('Failed to create subcategory');
            });
        });

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
                            div.className = 'relative w-24 h-24 border border-gray-200 rounded-lg overflow-hidden bg-gray-50 shadow-sm';
                            div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                            multiPreviewContainer.appendChild(div);
                        }
                        reader.readAsDataURL(file);
                    });
                }
            });
        }

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

        // Initialize BP Code dropdown
        initSearchableDropdown(
            'bp_code_container', 'bp_code_display', 'bp_code_menu', 'bp_code_search', 'bp_code_list', 'bp_code', '--Select BP Code--',
            (val, item) => {
                const name = item.dataset.name;
                if (name) {
                    customerNameInput.value = name;
                } else {
                    customerNameInput.value = '';
                }
            }
        );

        // Initialize Category dropdown
        initSearchableDropdown(
            'category_container', 'category_display', 'category_menu', 'category_search', 'category_list', 'product_category_id', '--Select Category--',
            (val) => {
                toggleCategoryOptions(val);
                refreshSubcategories(val);
            }
        );

        // Initialize Subcategory dropdown
        initSearchableDropdown(
            'subcategory_container', 'subcategory_display', 'subcategory_menu', 'subcategory_search', 'subcategory_list', 'subcategory_id', '--Select Sub Category--'
        );
    });
</script>
@endsection
