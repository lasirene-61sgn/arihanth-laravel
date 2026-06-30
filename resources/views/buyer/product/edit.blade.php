@extends('buyer.layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Edit Product</h1>
            <p class="text-sm text-slate-500">Updating: <span class="font-bold text-blue-600">{{ $product->product_name }}</span></p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('buyer.product.index') }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-50 transition-all shadow-sm">
                <i class="bi bi-arrow-left mr-2"></i> Back to List
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <h4 class="text-lg font-bold text-slate-800">Product Information</h4>
        </div>

        <div class="p-6 md:p-8">
            @if($errors->any())
                <div class="mb-6 flex items-start p-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-r-lg">
                    <i class="bi bi-exclamation-triangle-fill text-xl mr-3"></i>
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

            <form method="POST" action="{{ route('buyer.product.update', $product) }}" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <div class="space-y-2">
                        <label for="product_code" class="text-sm font-bold text-slate-700">Product Code <span class="text-red-500"></span></label>
                        <input type="text" 
                               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none font-mono text-blue-600" 
                               id="product_code" name="product_code" 
                               value="{{ old('product_code', $product->product_code) }}" >
                    </div>

                    <div class="space-y-2">
                        <label for="product_name" class="text-sm font-bold text-slate-700">Product Name <span class="text-red-500"></span></label>
                        <input type="text" 
                               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none" 
                               id="product_name" name="product_name" 
                               value="{{ old('product_name', $product->product_name) }}" >
                    </div>

                    <div class="space-y-2">
                        <label for="product_category_id" class="text-sm font-bold text-slate-700">Product Category <span class="text-red-500"></span></label>
                        <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none appearance-none" 
                                id="product_category_id" name="product_category_id" >
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('product_category_id', $product->product_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="subcategory_id" class="text-sm font-bold text-slate-700">Subcategory</label>
                        <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none appearance-none" 
                                id="subcategory_id" name="subcategory_id">
                            <option value="">Select Subcategory</option>
                            @foreach($subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}" {{ old('subcategory_id', $product->product_subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                    {{ $subcategory->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="type" class="text-sm font-bold text-slate-700">Type <span class="text-red-500">*</span></label>
                        <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none appearance-none" 
                                id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="Piece" {{ old('type', $product->type) == 'Piece' ? 'selected' : '' }}>Piece</option>
                            <option value="Pair" {{ old('type', $product->type) == 'Pair' ? 'selected' : '' }}>Pair</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="images" class="text-sm font-bold text-slate-700">Add More Images</label>
                        <input type="file" 
                               class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all" 
                               id="images" name="images[]" multiple accept="image/*">
                        <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-wide italic">Max 2MB per file. Multi-select allowed.</p>
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label for="description" class="text-sm font-bold text-slate-700">Description</label>
                        <textarea class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none min-h-[120px]" 
                                  id="description" name="description" placeholder="Enter product details...">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>

                @if($product->images->count() > 0)
                    <div class="pt-6 border-t border-slate-100">
                        <h5 class="text-sm font-bold text-slate-700 uppercase tracking-widest mb-4">Current Media ({{ $product->images->count() }})</h5>
                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
                            @foreach($product->images as $image)
                                <div class="relative aspect-square rounded-xl overflow-hidden border border-slate-200 group shadow-sm">
                                    <img src="{{ asset('storage/' . $image->path) }}" 
                                         class="w-full h-full object-cover" alt="Product Image">
                                    <div class="absolute bottom-1 left-1/2 -translate-x-1/2 bg-white rounded px-1 shadow-sm" style="z-index:2;">
                                        <input type="checkbox" name="delete_images[]" value="{{ $image->id }}" id="del_img_{{ $image->id }}" class="form-check-input">
                                        <label for="del_img_{{ $image->id }}" class="text-red-500 text-[10px] font-bold cursor-pointer">Del</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-3 pt-8 border-t border-slate-100">
                    <a href="{{ route('buyer.product.index') }}" 
                       class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all shadow-md shadow-blue-200">
                        <i class="bi bi-save mr-2"></i> Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('product_category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    
    categorySelect.addEventListener('change', function() {
        const categoryId = this.value;
        
        if (categoryId) {
            fetch(`/buyer/product/get-subcategories?category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    const currentSubcategoryId = `{!! old('subcategory_id', $product->product_subcategory_id) !!}`;
                    subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
                    data.forEach(subcategory => {
                        const option = document.createElement('option');
                        option.value = subcategory.id;
                        option.textContent = subcategory.name;
                        if (subcategory.id == currentSubcategoryId) {
                            option.selected = true;
                        }
                        subcategorySelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error:', error));
        } else {
            subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
        }
    });
});
</script>
@endpush
@endsection