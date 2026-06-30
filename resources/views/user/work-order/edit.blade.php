@extends('user.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Edit Work Order</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('user.work-order.index') }}">Work Orders</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Update Work Order</h4>
                </div>
                <div class="card-body">
                    <form id="workOrderForm" action="{{ route('user.work-order.update', $workOrder) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bp_code" class="form-label">BP Code</label>
                                    <input type="text" class="form-control" id="bp_code" name="bp_code" value="{{ $workOrder->bp_code }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_name" class="form-label">Customer Name</label>
                                    <input type="text" class="form-control" id="customer_name" name="customer_name" value="{{ $workOrder->customer_name }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_code" class="form-label">Product Code / Design Code</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="product_code" name="product_code" placeholder="Enter product code or design code" value="{{ $workOrder->product_code }}">
                                        <button class="btn btn-outline-secondary" type="button" id="lookupBtn">Lookup</button>
                                    </div>
                                    <div id="lookupFeedback" class="mt-2" style="display: none;">
                                        <div class="alert alert-info" id="lookupResult"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_name" class="form-label">Product Name</label>
                                    <input type="text" class="form-control" id="product_name" name="product_name" placeholder="Product name" value="{{ $workOrder->product_name }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_category_id" class="form-label">Product Category</label>
                                    <select class="form-select" id="product_category_id" name="product_category_id">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ $workOrder->product_category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subcategory_id" class="form-label">Subcategory</label>
                                    <select class="form-select" id="subcategory_id" name="subcategory_id">
                                        <option value="">Select Subcategory</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Quantity" value="{{ $workOrder->quantity }}" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="due_date" class="form-label">Due Date</label>
                                    <input type="date" class="form-control" id="due_date" name="due_date" value="{{ $workOrder->due_date }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type</label>
                                    <select class="form-select" id="type" name="type">
                                        <option value="">Select Type</option>
                                        <option value="Earring" {{ $workOrder->type == 'Earring' ? 'selected' : '' }}>Earring</option>
                                        <option value="Necklace" {{ $workOrder->type == 'Necklace' ? 'selected' : '' }}>Necklace</option>
                                        <option value="Ring" {{ $workOrder->type == 'Ring' ? 'selected' : '' }}>Ring</option>
                                        <option value="Bracelet" {{ $workOrder->type == 'Bracelet' ? 'selected' : '' }}>Bracelet</option>
                                        <option value="Chain" {{ $workOrder->type == 'Chain' ? 'selected' : '' }}>Chain</option>
                                        <option value="Pendant" {{ $workOrder->type == 'Pendant' ? 'selected' : '' }}>Pendant</option>
                                        <option value="Bangles" {{ $workOrder->type == 'Bangles' ? 'selected' : '' }}>Bangles</option>
                                        <option value="Nose Pin" {{ $workOrder->type == 'Nose Pin' ? 'selected' : '' }}>Nose Pin</option>
                                        <option value="Anklet" {{ $workOrder->type == 'Anklet' ? 'selected' : '' }}>Anklet</option>
                                        <option value="Brooch" {{ $workOrder->type == 'Brooch' ? 'selected' : '' }}>Brooch</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="open_close" class="form-label">Open/Close</label>
                                    <select class="form-select" id="open_close" name="open_close">
                                        <option value="">Select Open/Close</option>
                                        <option value="Open" {{ $workOrder->open_close == 'Open' ? 'selected' : '' }}>Open</option>
                                        <option value="Close" {{ $workOrder->open_close == 'Close' ? 'selected' : '' }}>Close</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="weight_from" class="form-label">Weight From</label>
                                    <input type="number" class="form-control" id="weight_from" name="weight_from" placeholder="Weight From" step="0.01" value="{{ $workOrder->weight_from }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="weight_to" class="form-label">Weight To</label>
                                    <input type="number" class="form-control" id="weight_to" name="weight_to" placeholder="Weight To" step="0.01" value="{{ $workOrder->weight_to }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reference_no" class="form-label">Reference No</label>
                                    <input type="text" class="form-control" id="reference_no" name="reference_no" placeholder="Reference No" value="{{ $workOrder->reference_no }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="relabel_code" class="form-label">Relabel Code</label>
                                    <input type="text" class="form-control" id="relabel_code" name="relabel_code" placeholder="Relabel Code" value="{{ $workOrder->relabel_code }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hallmark" class="form-label">Hallmark</label>
                                    <select class="form-select" id="hallmark" name="hallmark">
                                        <option value="">Select Hallmark</option>
                                        <option value="Yes" {{ $workOrder->hallmark == 'Yes' ? 'selected' : '' }}>Yes</option>
                                        <option value="No" {{ $workOrder->hallmark == 'No' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="rodium" class="form-label">Rodium</label>
                                    <select class="form-select" id="rodium" name="rodium">
                                        <option value="">Select Rodium</option>
                                        <option value="Yes" {{ $workOrder->rodium == 'Yes' ? 'selected' : '' }}>Yes</option>
                                        <option value="No" {{ $workOrder->rodium == 'No' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hook" class="form-label">Hook</label>
                                    <select class="form-select" id="hook" name="hook">
                                        <option value="">Select Hook</option>
                                        <option value="Yes" {{ $workOrder->hook == 'Yes' ? 'selected' : '' }}>Yes</option>
                                        <option value="No" {{ $workOrder->hook == 'No' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="size" class="form-label">Size</label>
                                    <input type="text" class="form-control" id="size" name="size" placeholder="Size" value="{{ $workOrder->size }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="stone" class="form-label">Stone</label>
                                    <select class="form-select" id="stone" name="stone">
                                        <option value="">Select Stone</option>
                                        <option value="Yes" {{ $workOrder->stone == 'Yes' ? 'selected' : '' }}>Yes</option>
                                        <option value="No" {{ $workOrder->stone == 'No' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="enamel" class="form-label">Enamel</label>
                                    <select class="form-select" id="enamel" name="enamel">
                                        <option value="">Select Enamel</option>
                                        <option value="Yes" {{ $workOrder->enamel == 'Yes' ? 'selected' : '' }}>Yes</option>
                                        <option value="No" {{ $workOrder->enamel == 'No' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="length" class="form-label">Length</label>
                                    <input type="text" class="form-control" id="length" name="length" placeholder="Length" value="{{ $workOrder->length }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="narration_admin" class="form-label">Narration</label>
                                    <textarea class="form-control" id="narration_admin" name="narration_admin" rows="3" placeholder="Additional notes">{{ $workOrder->narration_admin }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Created By:</strong></label>
                                    <p class="form-control-plaintext">{{ $workOrder->creator_name }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="product_images" class="form-label">Add More Images</label>
                            <input type="file" class="form-control" id="product_images" name="product_images[]" accept="image/*" multiple>
                            <div id="multi_image_preview_container" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label d-block"><strong>Current Gallery:</strong></label>
                            <div class="d-flex flex-wrap gap-3">
                                {{-- Primary Image --}}
                                @if($workOrder->product_image)
                                    <div class="border rounded p-2 text-center bg-light" style="width: 120px;">
                                        <img src="{{ asset($workOrder->product_image) }}" class="rounded mb-2" style="width: 100px; height: 100px; object-fit: cover;">
                                        <div class="form-check justify-content-center">
                                            <input class="form-check-input" type="checkbox" name="remove_product_image" id="remove_primary" value="1">
                                            <label class="form-check-label small text-danger" for="remove_primary">Remove</label>
                                        </div>
                                    </div>
                                @endif

                                {{-- Additional Images --}}
                                @foreach($workOrder->images as $image)
                                    <div class="border rounded p-2 text-center bg-light" style="width: 120px;">
                                        <img src="{{ asset($image->image_path) }}" class="rounded mb-2" style="width: 100px; height: 100px; object-fit: cover;">
                                        <div class="form-check justify-content-center">
                                            <input class="form-check-input" type="checkbox" name="remove_images[]" id="remove_image_{{ $image->id }}" value="{{ $image->id }}">
                                            <label class="form-check-label small text-danger" for="remove_image_{{ $image->id }}">Remove</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('user.work-order.index') }}" class="btn btn-secondary">Cancel</a>
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
    // Load subcategories based on the initial category
    const initialCategoryId = "{{ $workOrder->product_category_id }}";
    if(initialCategoryId) {
        fetch(`/user/product/get-subcategories?category_id=${initialCategoryId}`)
            .then(response => response.json())
            .then(data => {
                const subcategorySelect = document.getElementById('subcategory_id');
                subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
                data.forEach(subcategory => {
                    const option = document.createElement('option');
                    option.value = subcategory.id;
                    option.textContent = subcategory.name;
                    if(subcategory.id == "{{ $workOrder->subcategory_id }}") {
                        option.selected = true;
                    }
                    subcategorySelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error:', error));
    }

    // Fetch subcategories when category changes
    document.getElementById('product_category_id').addEventListener('change', function() {
        const categoryId = this.value;
        if(categoryId) {
            fetch(`/user/product/get-subcategories?category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    const subcategorySelect = document.getElementById('subcategory_id');
                    subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
                    data.forEach(subcategory => {
                        const option = document.createElement('option');
                        option.value = subcategory.id;
                        option.textContent = subcategory.name;
                        subcategorySelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error:', error));
        } else {
            document.getElementById('subcategory_id').innerHTML = '<option value="">Select Subcategory</option>';
        }
    });

    // Lookup product details when button is clicked
    document.getElementById('lookupBtn').addEventListener('click', function() {
        const productCode = document.getElementById('product_code').value.trim();
        if(!productCode) {
            alert('Please enter a product code or design code');
            return;
        }

        fetch(`/user/work-order/get-product-details?product_code=${encodeURIComponent(productCode)}`)
            .then(response => response.json())
            .then(data => {
                const lookupResult = document.getElementById('lookupResult');
                const lookupFeedback = document.getElementById('lookupFeedback');
                
                if(data.success) {
                    // Populate form fields with product data
                    document.getElementById('product_name').value = data.product.product_name || '';
                    document.getElementById('type').value = data.product.type || '';
                    document.getElementById('open_close').value = data.product.open_close || '';
                    document.getElementById('weight_from').value = data.product.weight_from || '';
                    document.getElementById('weight_to').value = data.product.weight_to || '';
                    document.getElementById('hallmark').value = data.product.hallmark || '';
                    document.getElementById('rodium').value = data.product.rodium || '';
                    document.getElementById('hook').value = data.product.hook || '';
                    document.getElementById('size').value = data.product.size || '';
                    document.getElementById('stone').value = data.product.stone || '';
                    document.getElementById('enamel').value = data.product.enamel || '';
                    document.getElementById('length').value = data.product.length || '';
                    document.getElementById('relabel_code').value = data.product.relabel_code || '';
                    
                    // Set category and subcategory
                    if(data.product.product_category_id) {
                        document.getElementById('product_category_id').value = data.product.product_category_id;
                        
                        // Trigger change event to load subcategories
                        const categoryEvent = new Event('change');
                        document.getElementById('product_category_id').dispatchEvent(categoryEvent);
                        
                        // Set subcategory if available
                        if(data.product.subcategory_id) {
                            setTimeout(() => {
                                document.getElementById('subcategory_id').value = data.product.subcategory_id;
                            }, 300); // Delay to allow subcategories to load
                        }
                    }
                    
                    lookupResult.className = 'alert alert-success';
                    lookupResult.textContent = 'Product details loaded successfully!';
                } else {
                    lookupResult.className = 'alert alert-danger';
                    lookupResult.textContent = data.message || 'Product not found';
                }
                
                lookupFeedback.style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                const lookupResult = document.getElementById('lookupResult');
                const lookupFeedback = document.getElementById('lookupFeedback');
                lookupResult.className = 'alert alert-danger';
                lookupResult.textContent = 'Error occurred while fetching product details';
                lookupFeedback.style.display = 'block';
            });
    });

    // Handle multi-image preview
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
                        div.className = 'border rounded p-1 bg-light shadow-sm';
                        div.style.width = '100px';
                        div.style.height = '100px';
                        div.innerHTML = `<img src="${e.target.result}" class="w-100 h-100 object-fit-cover rounded">`;
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