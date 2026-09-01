@extends('key-user.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Work Order</h1>
            <nav class="flex text-sm text-gray-500 mt-1">
                <a href="{{ route('key-user.dashboard') }}" class="hover:text-indigo-600 transition">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('key-user.work-order.index') }}" class="hover:text-indigo-600 transition">Work Orders</a>
                <span class="mx-2">/</span>
                <span class="text-gray-800 font-medium">Edit Order</span>
            </nav>
        </div>
        <a href="{{ route('key-user.work-order.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
            <i class="bi bi-arrow-left mr-2"></i> Back to List
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h4 class="text-lg font-bold text-gray-800">Edit Work Order Details</h4>
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

            <form action="{{ route('key-user.work-order.update', $workOrder) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end bg-indigo-50/30 p-6 rounded-2xl border border-indigo-100 mb-8">
                    <div class="md:col-span-4 space-y-1">
                        <label for="product_code" class="block text-sm font-bold text-indigo-900">Product / Design Code Lookup</label>
                        <div class="flex shadow-sm">
                            <input type="text" id="product_code" name="product_code" value="{{ old('product_code', $workOrder->product_code) }}" 
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" 
                                   placeholder="Enter code to lookup">
                            <button type="button" id="test_lookup_btn" 
                                    class="px-4 py-2 bg-indigo-600 text-white font-bold text-sm rounded-r-lg hover:bg-indigo-700 transition">
                                Test
                            </button>
                        </div>
                        <p class="text-[11px] text-indigo-500 italic mt-1" id="lookup_status">Enter code to refresh product details.</p>
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
                        <label for="design_code" class="block text-sm font-semibold text-gray-700">Design Code</label>
                        <input type="text" id="design_code" name="design_code" value="{{ $workOrder->design_code }}" readonly 
                               class="w-full px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg text-gray-500 font-mono text-sm cursor-not-allowed shadow-inner">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    
                    <div class="space-y-1">
                        <label for="work_order_number" class="block text-sm font-semibold text-gray-700">WO Number</label>
                        <input type="text" id="work_order_number" value="{{ $workOrder->work_order_number }}" disabled
                               class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-indigo-600 font-mono font-bold text-sm cursor-not-allowed shadow-inner">
                    </div>

                    <div class="space-y-1">
                        <label for="customer_name" class="block text-sm font-semibold text-gray-700">Customer Name <span class="text-red-500">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name', $workOrder->customer_name) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition @error('customer_name') border-red-500 @enderror">
                    </div>

                    <div class="space-y-1">
                        <label for="bp_code" class="block text-sm font-semibold text-gray-700">BP Code <span class="text-red-500">*</span></label>
                        <select id="bp_code" name="bp_code" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition @error('bp_code') border-red-500 @enderror">
                            <option value="">Select BP Code</option>
                            @foreach($buyers as $buyer)
                                <option value="{{ $buyer->bp_code }}" {{ old('bp_code', $workOrder->bp_code) == $buyer->bp_code ? 'selected' : '' }}>
                                    {{ $buyer->bp_code }} - {{ $buyer->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="product_name" class="block text-sm font-semibold text-gray-700">Product Name</label>
                        <input type="text" id="product_name" name="product_name" value="{{ old('product_name', $workOrder->product_name) }}" 
                               class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-500 text-sm cursor-allowed">
                    </div>

                    <div class="space-y-1">
                        <label for="product_category_id" class="block text-sm font-semibold text-gray-700">Product Category <span class="text-red-500">*</span></label>
                        <select id="product_category_id" name="product_category_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('product_category_id', $workOrder->product_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1" id="subcategory-container" style="{{ $workOrder->subcategory_id ? '' : 'display: none;' }}">
                        <label for="subcategory_id" class="block text-sm font-semibold text-gray-700">Sub Category</label>
                        <select id="subcategory_id" name="subcategory_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                            <option value="">Select Sub Category</option>
                            @if($workOrder->product_category_id)
                                @foreach(\App\Models\ProductSubcategory::where('product_category_id', $workOrder->product_category_id)->get() as $sub)
                                    <option value="{{ $sub->id }}" {{ old('subcategory_id', $workOrder->subcategory_id) == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="type" class="block text-sm font-semibold text-gray-700">Type <span class="text-red-500">*</span></label>
                        <select id="type" name="type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                            <option value="">Select Type</option>
                            <option value="Piece" {{ old('type', $workOrder->type) == 'Piece' ? 'selected' : '' }}>Piece</option>
                            <option value="Pair" {{ old('type', $workOrder->type) == 'Pair' ? 'selected' : '' }}>Pair</option>
                            <option value="Set" {{ old('type', $workOrder->type) == 'Set' ? 'selected' : '' }}>Set</option>

                        </select>
                    </div>

                    <!-- <div class="space-y-1">
                        <label for="order_type" class="block text-sm font-semibold text-gray-700">Order Type <span class="text-red-500">*</span></label>
                        <select id="order_type" name="order_type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                            <option value="">Select Order Type</option>
                            <option value="Regular" {{ old('order_type', $workOrder->order_type) == 'Regular' ? 'selected' : '' }}>Regular</option>
                            <option value="Urgent" {{ old('order_type', $workOrder->order_type) == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="Super Urgent" {{ old('order_type', $workOrder->order_type) == 'Super Urgent' ? 'selected' : '' }}>Super Urgent</option>
                        </select>
                    </div> -->

                    <div class="space-y-1">
                        <label for="quantity" class="block text-sm font-semibold text-gray-700">Quantity <span class="text-red-500">*</span></label>
                        <input type="number" id="quantity" name="quantity" value="{{ old('quantity', $workOrder->quantity) }}" min="1" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1">
                        <label for="size" class="block text-sm font-semibold text-gray-700">Size <span class="text-red-500">*</span></label>
                        <input type="text" id="size" name="size" value="{{ old('size', $workOrder->size) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="space-y-1">
                        <label for="open_close" class="block text-sm font-semibold text-gray-700">Open/Close <span class="text-red-500"></span></label>
                        <select id="open_close" name="open_close" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                            <option value="">Select Status</option>
                            <option value="Open" {{ old('open_close', $workOrder->open_close) == 'Open' ? 'selected' : '' }}>Open</option>
                            <option value="Close" {{ old('open_close', $workOrder->open_close) == 'Close' ? 'selected' : '' }}>Close</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-100">
                    <div class="space-y-1">
                        <label for="weight_from" class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Weight From</label>
                        <input type="text" id="weight_from" name="weight_from" value="{{ old('weight_from', $workOrder->weight_from) }}"
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-0 transition">
                    </div>
                    <div class="space-y-1">
                        <label for="weight_to" class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Weight To</label>
                        <input type="text" id="weight_to" name="weight_to" value="{{ old('weight_to', $workOrder->weight_to) }}"
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-0 transition">
                    </div>
                    <div class="space-y-1">
                        <label for="length" class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Length</label>
                        <input type="text" id="length" name="length" value="{{ old('length', $workOrder->length) }}"
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-0 transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="space-y-1">
                        <label for="enamel" class="block text-sm font-semibold text-gray-700">Enamel <span class="text-red-500">*</span></label>
                        <select id="enamel" name="enamel" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="Yes" {{ old('enamel', $workOrder->enamel) == 'Yes' ? 'selected' : '' }}>Yes</option>
                            <option value="No" {{ old('enamel', $workOrder->enamel) == 'No' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label for="hallmark" class="block text-sm font-semibold text-gray-700">Hallmark</label>
                        <input type="text" id="hallmark" name="hallmark" value="{{ old('hallmark', $workOrder->hallmark) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div class="space-y-1">
                        <label for="rodium" class="block text-sm font-semibold text-gray-700">Rodium</label>
                        <input type="text" id="rodium" name="rodium" value="{{ old('rodium', $workOrder->rodium) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div class="space-y-1">
                        <label for="hook" class="block text-sm font-semibold text-gray-700">Hook</label>
                        <input type="text" id="hook" name="hook" value="{{ old('hook', $workOrder->hook) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div class="space-y-1">
                        <label for="hook" class="block text-sm font-semibold text-gray-700">Due Date</label>
                        <input type="date" id="due_date" name="due_date" value="{{ old('due_date', $workOrder->due_date) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 pt-6 border-t border-gray-100">
                    <div class="space-y-4">
                        <!-- <div class="space-y-1">
                            <label for="stone" class="block text-sm font-semibold text-gray-700">Stone Details</label>
                            <input type="text" id="stone" name="stone" value="{{ old('stone', $workOrder->stone) }}" placeholder="Describe stone types..."
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                        </div> -->

                        <div class="space-y-1">
                            <label for="product_images" class="block text-sm font-semibold text-gray-700">Add More Images</label>
                            <input type="file" id="product_images" name="product_images[]" accept="image/*" multiple
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition">
                            <div id="multi_image_preview_container" class="flex flex-wrap gap-2 mt-2"></div>
                        </div>
                    </div>

                    <div class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-2xl border border-gray-200">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3">Current Gallery</span>
                        <div class="flex flex-wrap gap-3 justify-center">
                            {{-- Primary Image --}}
                            @if($workOrder->product_image)
                                <div class="group relative w-24 border border-gray-200 rounded-xl p-1 bg-white shadow-sm transition-all hover:border-red-200">
                                    <img src="{{ asset($workOrder->product_image) }}" class="w-full h-20 object-cover rounded-lg">
                                    <div class="mt-1 flex items-center gap-1.5 px-1 pb-1">
                                        <input type="checkbox" name="remove_product_image" id="rem_primary" value="1" class="w-3 h-3 text-red-600 border-slate-300 rounded focus:ring-red-500">
                                        <label for="rem_primary" class="text-[9px] font-bold text-slate-500 group-hover:text-red-600 truncate">Remove Primary</label>
                                    </div>
                                </div>
                            @endif

                            {{-- Additional Images --}}
                            @foreach($workOrder->images as $image)
                                <div class="group relative w-24 border border-gray-200 rounded-xl p-1 bg-white shadow-sm transition-all hover:border-red-200">
                                    <img src="{{ asset($image->image_path) }}" class="w-full h-20 object-cover rounded-lg">
                                    <div class="mt-1 flex items-center gap-1.5 px-1 pb-1">
                                        <input type="checkbox" name="remove_images[]" id="rem_img_{{ $image->id }}" value="{{ $image->id }}" class="w-3 h-3 text-red-600 border-slate-300 rounded focus:ring-red-500">
                                        <label for="rem_img_{{ $image->id }}" class="text-[9px] font-bold text-slate-500 group-hover:text-red-600">Remove</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="space-y-1 pt-6 border-t border-gray-100">
                    <label for="narration_admin" class="block text-sm font-semibold text-gray-700">Admin Instructions / Narration</label>
                    <textarea id="narration_admin" name="narration_admin" rows="4" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">{{ old('narration_admin', $workOrder->narration_admin) }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-8 border-t border-gray-100">
                    <a href="{{ route('key-user.work-order.index') }}" class="px-6 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-10 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition shadow-md">
                        Update Work Order
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
        const productCodeInput = document.getElementById('product_code');
        const designCodeInput = document.getElementById('design_code');
        const lookupStatus = document.getElementById('lookup_status');

        function toggleCategoryOptions(categoryId) {
            // In edit view we might not have all the option blocks if the UI is different,
            // but for consistency we'll keep the logic if they are present.
            const optionBlocks = document.querySelectorAll('.category-option');
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

        function refreshSubcategories(categoryId) {
            subcategorySelect.innerHTML = '<option value="">Select Sub Category</option>';
            if (!categoryId) { 
                subcategoryContainer.style.display = 'none'; 
                return Promise.resolve(); 
            }
            
            subcategoryContainer.style.display = 'block'; 

            return fetch(`{{ url('/key-user/product/get-subcategories') }}?category_id=${categoryId}`)
                .then(r => r.json())
                .then(list => {
                    list.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.id; 
                        opt.textContent = s.name;
                        subcategorySelect.appendChild(opt);
                    });
                });
        }

        categorySelect.addEventListener('change', function() {
            refreshSubcategories(this.value);
            toggleCategoryOptions(this.value);
        });

        let currentLookupFile = { url: '', type: 'image' };

        window.openPreviewFromLookup = function() {
            if (currentLookupFile.url) {
                openUniversalPreview(currentLookupFile.url, currentLookupFile.type);
            }
        };

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

                        if (data.product.product_category_id) {
                            categorySelect.value = data.product.product_category_id;
                            const togglePromise = toggleCategoryOptions(data.product.product_category_id);
                            const refreshPromise = refreshSubcategories(data.product.product_category_id);
                            
                            Promise.all([togglePromise, refreshPromise]).then(() => {
                                if (data.product.subcategory_id) {
                                    subcategorySelect.value = data.product.subcategory_id;
                                }
                            });
                        }

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

        document.getElementById('test_lookup_btn').addEventListener('click', function() {
            const code = productCodeInput.value.trim();
            performLookup(code);
        });

        if (categorySelect.value) {
            toggleCategoryOptions(categorySelect.value);
        }

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
    });
</script>
@endsection