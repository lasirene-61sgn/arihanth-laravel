@extends('buyer.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Edit Work Order</h1>
            <p class="text-sm text-slate-500">Modify details for request: <span class="font-mono font-bold text-blue-600">{{ $workOrder->work_order_number }}</span></p>
        </div>
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm font-medium">
                <li><a href="{{ route('buyer.dashboard') }}" class="text-slate-500 hover:text-blue-600 transition-colors">Dashboard</a></li>
                <li class="flex items-center text-slate-400">
                    <i class="bi bi-chevron-right mx-2 text-[10px]"></i>
                    <a href="{{ route('buyer.work-order.index') }}" class="text-slate-500 hover:text-blue-600 transition-colors">Work Orders</a>
                </li>
                <li class="flex items-center text-slate-400">
                    <i class="bi bi-chevron-right mx-2 text-[10px]"></i>
                    <span class="text-blue-600">Edit</span>
                </li>
            </ol>
        </nav>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden text-slate-900">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <h4 class="text-lg font-bold text-slate-800">Edit Work Order Details</h4>
        </div>

        <div class="p-6 md:p-8">
            @if ($errors->any())
                <div class="mb-6 flex items-start p-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-r-lg">
                    <i class="bi bi-exclamation-triangle-fill text-xl mr-3 opacity-80"></i>
                    <div>
                        <span class="font-bold text-sm uppercase tracking-wide">Validation Errors</span>
                        <ul class="mt-1 list-disc list-inside text-sm opacity-90">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('buyer.work-order.update', $workOrder) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <div class="space-y-2">
                        <label for="work_order_number" class="text-sm font-bold text-slate-700">WO Number</label>
                        <input type="text" 
                               class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono text-slate-500 outline-none cursor-not-allowed" 
                               id="work_order_number" 
                               value="{{ $workOrder->work_order_number }}" 
                               disabled>
                    </div>

                    <div class="space-y-2">
                        <label for="customer_name" class="text-sm font-bold text-slate-700">Customer Name <span class="text-red-500">*</span></label>
                        <input type="text" 
                               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none @error('customer_name') border-red-500 @enderror" 
                               id="customer_name" 
                               name="customer_name" 
                               value="{{ old('customer_name', $workOrder->customer_name) }}" 
                               required>
                    </div>

                    <div class="space-y-2">
                        <label for="bp_code" class="text-sm font-bold text-slate-700">BP Code <span class="text-red-500">*</span></label>
                        <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none appearance-none @error('bp_code') border-red-500 @enderror" 
                                id="bp_code" 
                                name="bp_code" 
                                required>
                            <option value="">Select BP Code</option>
                            @foreach($buyers as $buyer)
                                <option value="{{ $buyer->bp_code }}" {{ old('bp_code', $workOrder->bp_code) == $buyer->bp_code ? 'selected' : '' }}>
                                    {{ $buyer->bp_code }} - {{ $buyer->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    
                     <div class="space-y-2">
                        <label for="product_name" class="text-sm font-bold text-slate-700">Product Code</label>
                        <input type="text" 
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-600 outline-none" 
                               id="product_name" 
                               name="product_name" 
                               value="{{ old('product_name', $workOrder->product_code) }}" 
                               readonly>
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
                    <div class="space-y-2">
                        <label for="type" class="text-sm font-bold text-slate-700">Type <span class="text-red-500">*</span></label>
                        <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none appearance-none" 
                                id="type" 
                                name="type" 
                                required>
                            <option value="">Select Type</option>
                            <option value="Piece" {{ old('type', $workOrder->type) == 'Piece' ? 'selected' : '' }}>Piece</option>
                            <option value="Pair" {{ old('type', $workOrder->type) == 'Pair' ? 'selected' : '' }}>Pair</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="screw_name" class="text-sm font-bold text-slate-700">Screw</label>
                        <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none appearance-none" id="screw_name" name="screw_name">
                            <option value="">Select Screw</option>
                            <option value="North Screw" {{ old('screw_name', $workOrder->screw_name) == 'North Screw' ? 'selected' : '' }}>North Screw</option>
                            <option value="South Screw" {{ old('screw_name', $workOrder->screw_name) == 'South Screw' ? 'selected' : '' }}>South Screw</option>
                        </select>
                    </div>

                    <!-- <div class="space-y-2">
                        <label for="order_type" class="text-sm font-bold text-slate-700">Order Type <span class="text-red-500">*</span></label>
                        <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none appearance-none" 
                                id="order_type" 
                                name="order_type" 
                                required>
                            <option value="">Select Order Type</option>
                            <option value="Regular" {{ old('order_type', $workOrder->order_type) == 'Regular' ? 'selected' : '' }}>Regular</option>
                            <option value="Urgent" {{ old('order_type', $workOrder->order_type) == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="Super Urgent" {{ old('order_type', $workOrder->order_type) == 'Super Urgent' ? 'selected' : '' }}>Super Urgent</option>
                        </select>
                    </div> -->

                    <div class="space-y-2">
                        <label for="quantity" class="text-sm font-bold text-slate-700">Quantity <span class="text-red-500">*</span></label>
                        <input type="number" 
                               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" 
                               id="quantity" 
                               name="quantity" 
                               value="{{ old('quantity', $workOrder->quantity) }}" 
                               min="1" 
                               required>
                    </div>

                    <div class="space-y-2">
                        <label for="size" class="text-sm font-bold text-slate-700">Size <span class="text-red-500"></span></label>
                        <input type="text" 
                               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" 
                               id="size" 
                               name="size" 
                               value="{{ old('size', $workOrder->size) }}" 
                               >
                    </div>

                    <div class="space-y-2">
                        <label for="open_close" class="text-sm font-bold text-slate-700">Open/Close <span class="text-red-500"></span></label>
                        <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none appearance-none" 
                                id="open_close" 
                                name="open_close" 
                                >
                            <option value="">Select Status</option>
                            <option value="Open" {{ old('open_close', $workOrder->open_close) == 'Open' ? 'selected' : '' }}>Open</option>
                            <option value="Close" {{ old('open_close', $workOrder->open_close) == 'Close' ? 'selected' : '' }}>Close</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="enamel" class="text-sm font-bold text-slate-700">Enamel <span class="text-red-500"></span></label>
                        <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none appearance-none" 
                                id="enamel" 
                                name="enamel" 
                                >
                            <option value="">Select Enamel</option>
                            <option value="Yes" {{ old('enamel', $workOrder->enamel) == 'Yes' ? 'selected' : '' }}>Yes</option>
                            <option value="No" {{ old('enamel', $workOrder->enamel) == 'No' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="hallmark" class="text-sm font-bold text-slate-700">Hallmark</label>
                        <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" 
                               id="hallmark" name="hallmark" value="{{ old('hallmark', $workOrder->hallmark) }}">
                    </div>

                    <div class="space-y-2">
                        <label for="rodium" class="text-sm font-bold text-slate-700">Rodium</label>
                        <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" 
                               id="rodium" name="rodium" value="{{ old('rodium', $workOrder->rodium) }}">
                    </div>

                    <div class="space-y-2">
                        <label for="hook" class="text-sm font-bold text-slate-700">Hook</label>
                        <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" 
                               id="hook" name="hook" value="{{ old('hook', $workOrder->hook) }}">
                    </div>

                    <div class="space-y-2">
                        <label for="stone" class="text-sm font-bold text-slate-700">Stone</label>
                        <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" 
                               id="stone" name="stone" value="{{ old('stone', $workOrder->stone) }}">
                    </div>

                    <div class="space-y-2">
                        <label for="weight_from" class="text-sm font-bold text-slate-700">Weight From</label>
                        <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" 
                               id="weight_from" name="weight_from" value="{{ old('weight_from', $workOrder->weight_from) }}">
                    </div>

                    <div class="space-y-2">
                        <label for="weight_to" class="text-sm font-bold text-slate-700">Weight To</label>
                        <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" 
                               id="weight_to" name="weight_to" value="{{ old('weight_to', $workOrder->weight_to) }}">
                    </div>

                    <div class="space-y-2">
                        <label for="length" class="text-sm font-bold text-slate-700">Length</label>
                        <input type="text" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" 
                               id="length" name="length" value="{{ old('length', $workOrder->length) }}">
                    </div>

                    <div class="space-y-2">
                        <label for="due_date" class="text-sm font-bold text-slate-700">Due Date</label>
                        <input type="date" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" 
                               id="due_date" name="due_date" value="{{ old('due_date', $workOrder->due_date) }}">
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label for="product_images" class="text-sm font-bold text-slate-700">Add More Images</label>
                        <input type="file" 
                               class="w-full px-4 py-1.5 bg-white border border-slate-200 rounded-xl text-xs file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all" 
                               id="product_images" name="product_images[]" accept="image/*" multiple>
                        <div id="multi_image_preview_container" class="flex flex-wrap gap-2 mt-2"></div>

                        @if($workOrder->images->count() > 0 || $workOrder->product_image)
                            <div class="mt-4 space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Current Gallery</label>
                                <div class="flex flex-wrap gap-3">
                                    {{-- Primary Image --}}
                                    @if($workOrder->product_image)
                                        <div class="group relative w-24 border border-slate-200 rounded-xl p-1 bg-white shadow-sm transition-all hover:border-red-200">
                                            <img src="{{ asset($workOrder->product_image) }}" class="w-full h-20 object-cover rounded-lg">
                                            <div class="mt-1 flex items-center gap-1.5 px-1 pb-1">
                                                <input type="checkbox" name="remove_product_image" id="rem_primary" value="1" class="w-3 h-3 text-red-600 border-slate-300 rounded focus:ring-red-500">
                                                <label for="rem_primary" class="text-[9px] font-bold text-slate-500 group-hover:text-red-600 truncate">Remove Primary</label>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Additional Images --}}
                                    @foreach($workOrder->images as $image)
                                        <div class="group relative w-24 border border-slate-200 rounded-xl p-1 bg-white shadow-sm transition-all hover:border-red-200">
                                            <img src="{{ asset($image->image_path) }}" class="w-full h-20 object-cover rounded-lg">
                                            <div class="mt-1 flex items-center gap-1.5 px-1 pb-1">
                                                <input type="checkbox" name="remove_images[]" id="rem_img_{{ $image->id }}" value="{{ $image->id }}" class="w-3 h-3 text-red-600 border-slate-300 rounded focus:ring-red-500">
                                                <label for="rem_img_{{ $image->id }}" class="text-[9px] font-bold text-slate-500 group-hover:text-red-600">Remove</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label for="narration_admin" class="text-sm font-bold text-slate-700">Narration (Admin Description)</label>
                        <textarea class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-blue-500 outline-none min-h-[120px]" 
                                  id="narration_admin" 
                                  name="narration_admin" 
                                  rows="4">{{ old('narration_admin', $workOrder->narration_admin) }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-8 border-t border-slate-100">
                    <a href="{{ route('buyer.work-order.index') }}" 
                       class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all shadow-md shadow-blue-200">
                        <i class="bi bi-check-lg mr-2"></i> Update Work Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_code');
    const productNameInput = document.getElementById('product_name');
    const categorySelect = document.getElementById('product_category');
    const subcategoryContainer = document.getElementById('subcategory-container');
    const subcategorySelect = document.getElementById('subcategory');
    const typeSelect = document.getElementById('type');
    const orderTypeSelect = document.getElementById('order_type');
    const openCloseSelect = document.getElementById('open_close');
    const enamelSelect = document.getElementById('enamel');
    const hallmarkInput = document.getElementById('hallmark');
    const rodiumInput = document.getElementById('rodium');
    const hookInput = document.getElementById('hook');
    const sizeInput = document.getElementById('size');
    const stoneInput = document.getElementById('stone');
    const weightFromInput = document.getElementById('weight_from');
    const weightToInput = document.getElementById('weight_to');
    const lengthInput = document.getElementById('length');
    
    productSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption.value) {
            // Auto-fill the related fields based on selected product
            productNameInput.value = selectedOption.text;
            // Use category name if available, otherwise fallback to product_category
            const categoryName = selectedOption.dataset.categoryName || selectedOption.dataset.category || '';
            categorySelect.value = categoryName;
            
            // Handle subcategory
            const subcategoryName = selectedOption.dataset.subcategoryName || '';
            if (subcategoryName) {
                // Create option if it doesn't exist
                let option = subcategorySelect.querySelector(`option[value="${subcategoryName}"]`);
                if (!option) {
                    option = document.createElement('option');
                    option.value = subcategoryName;
                    option.textContent = subcategoryName;
                    subcategorySelect.appendChild(option);
                }
                subcategorySelect.value = subcategoryName;
                subcategoryContainer.style.display = 'block';
            } else {
                subcategoryContainer.style.display = 'none';
            }
            
            typeSelect.value = selectedOption.dataset.type;
            orderTypeSelect.value = selectedOption.dataset.orderType;
            openCloseSelect.value = selectedOption.dataset.openClose;
            enamelSelect.value = selectedOption.dataset.enamel;
            hallmarkInput.value = selectedOption.dataset.hallmark || '';
            rodiumInput.value = selectedOption.dataset.rodium || '';
            hookInput.value = selectedOption.dataset.hook || '';
            sizeInput.value = selectedOption.dataset.size || '';
            stoneInput.value = selectedOption.dataset.stone || '';
            // Fill in the new fields
            weightFromInput.value = selectedOption.dataset.weightFrom || '';
            weightToInput.value = selectedOption.dataset.weightTo || '';
            lengthInput.value = selectedOption.dataset.length || '';
        } else {
            // Clear all fields if no product is selected
            productNameInput.value = '';
            categorySelect.value = '';
            subcategoryContainer.style.display = 'none';
            subcategorySelect.innerHTML = '<option value="">Select Sub Category</option>';
            typeSelect.value = '';
            orderTypeSelect.value = '';
            openCloseSelect.value = '';
            enamelSelect.value = '';
            hallmarkInput.value = '';
            rodiumInput.value = '';
            hookInput.value = '';
            sizeInput.value = '';
            stoneInput.value = '';
            weightFromInput.value = '';
            weightToInput.value = '';
            lengthInput.value = '';
        }
    });
    
    if (productSelect.value) {
        productSelect.dispatchEvent(new Event('change'));
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
                        div.className = 'relative w-24 h-24 border border-slate-200 rounded-lg overflow-hidden bg-slate-50';
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