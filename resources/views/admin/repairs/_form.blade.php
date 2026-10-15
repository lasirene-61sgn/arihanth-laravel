<div class="row g-3">
    <!-- Date -->
    <div class="col-md-4">
        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', $repair->date ?? date('Y-m-d')) }}" required>
        @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Buyer / BP Code Dropdown -->
    <div class="col-md-4">
        <label for="buyer_id" class="form-label">Buyer (BP Code) <span class="text-danger">*</span></label>
        <select class="form-select @error('buyer_id') is-invalid @enderror" id="buyer_id" name="buyer_id" required>
            <option value="">Select Buyer</option>
            @foreach($buyers as $buyer)
                <option value="{{ $buyer->id }}" {{ old('buyer_id', $repair->buyer_id ?? '') == $buyer->id ? 'selected' : '' }}>
                    {{ $buyer->bp_code ?? $buyer->name }} ({{ $buyer->name }})
                </option>
            @endforeach
        </select>
        @error('buyer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Repair or Sample Dropdown -->
    <div class="col-md-4">
        <label for="repair_or_sample" class="form-label">Type <span class="text-danger">*</span></label>
        <select class="form-select @error('repair_or_sample') is-invalid @enderror" id="repair_or_sample" name="repair_or_sample" required>
            <option value="">Select Type</option>
            <option value="repair" {{ old('repair_or_sample', $repair->repair_or_sample ?? '') === 'repair' ? 'selected' : '' }}>Repair</option>
            <option value="sample" {{ old('repair_or_sample', $repair->repair_or_sample ?? '') === 'sample' ? 'selected' : '' }}>Sample</option>
        </select>
        @error('repair_or_sample')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Product Name -->
    <div class="col-md-6">
        <label for="product_name" class="form-label">Product Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('product_name') is-invalid @enderror" id="product_name" name="product_name" value="{{ old('product_name', $repair->product_name ?? '') }}" required>
        @error('product_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Order Number -->
    <div class="col-md-6">
        <label for="order_number" class="form-label">Order Number <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('order_number') is-invalid @enderror" id="order_number" name="order_number" value="{{ old('order_number', $repair->order_number ?? '') }}" required>
        @error('order_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Reference Number -->
    <div class="col-md-6">
        <label for="reference_number" class="form-label">Reference Number</label>
        <input type="text" class="form-control @error('reference_number') is-invalid @enderror" id="reference_number" name="reference_number" value="{{ old('reference_number', $repair->reference_number ?? '') }}">
        @error('reference_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Weight -->
    <div class="col-md-6">
        <label for="weight" class="form-label">Weight</label>
        <input type="number" step="0.01" class="form-control @error('weight') is-invalid @enderror" id="weight" name="weight" value="{{ old('weight', $repair->weight ?? '') }}">
        @error('weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Item Delivered By -->
    <div class="col-md-6">
        <label for="item_delivered_by" class="form-label">Item Delivered By <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('item_delivered_by') is-invalid @enderror" id="item_delivered_by" name="item_delivered_by" list="deliveredList" value="{{ old('item_delivered_by', $repair->item_delivered_by ?? '') }}" placeholder="Select or type new name..." required>
        <datalist id="deliveredList">
            @foreach($deliveredNames as $name)
                <option value="{{ $name }}">
            @endforeach
        </datalist>
        @error('item_delivered_by')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Item Received By -->
    <div class="col-md-6">
        <label for="item_received_by" class="form-label">Item Received By <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('item_received_by') is-invalid @enderror" id="item_received_by" name="item_received_by" list="receivedList" value="{{ old('item_received_by', $repair->item_received_by ?? '') }}" placeholder="Select or type new name..." required>
        <datalist id="receivedList">
            @foreach($receivedNames as $name)
                <option value="{{ $name }}">
            @endforeach
        </datalist>
        @error('item_received_by')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Repair Details -->
    <div class="col-md-12">
        <label for="repair_details" class="form-label">Repair Details</label>
        <textarea class="form-control @error('repair_details') is-invalid @enderror" id="repair_details" name="repair_details" rows="3">{{ old('repair_details', $repair->repair_details ?? '') }}</textarea>
        @error('repair_details')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Notes -->
    <div class="col-md-12">
        <label for="notes" class="form-label">Notes</label>
        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes', $repair->notes ?? '') }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Repair Image -->
    <div class="col-md-12">
        <label class="form-label">Repair Image</label>
        <input type="file" name="repair_image" class="form-control @error('repair_image') is-invalid @enderror" accept="image/*">
        @error('repair_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        
        @if(isset($repair) && $repair->repair_image)
            <div class="mt-2">
                <p class="text-muted small mb-1">Current Image:</p>
                <a href="{{ asset('storage/' . $repair->repair_image) }}" target="_blank">
                    <img src="{{ asset('storage/' . $repair->repair_image) }}" alt="Repair Image" width="100" class="img-thumbnail">
                </a>
            </div>
        @endif
    </div>

    <!-- Submit Button -->
    <div class="col-12 mt-4">
        <button type="submit" class="btn btn-success">{{ $buttonText }}</button>
        <a href="{{ route('admin.repairs.index', request()->query()) }}" class="btn btn-secondary">Cancel</a>
    </div>
</div>