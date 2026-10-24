<div class="row g-3">
    <!-- Date -->
    <div class="col-md-4">
        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', $repair->date ?? date('Y-m-d')) }}" required>
        @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Buyer / BP Code Dropdown with Search Bar -->
    <div class="col-md-4">
        <label for="buyer_dropdown_btn" class="form-label">BP Code <span class="text-danger">*</span></label>
        @php
            $currentBuyerId = old('buyer_id', $repair->buyer_id ?? '');
            $selectedBuyer = $buyers->firstWhere('id', $currentBuyerId);
            $selectedLabel = $selectedBuyer 
                ? trim(($selectedBuyer->bp_code ? $selectedBuyer->bp_code . ' - ' : '') . ($selectedBuyer->business_name ?: $selectedBuyer->name)) 
                : '--Select BP Code--';
        @endphp
        <div class="custom-dropdown-container position-relative" id="buyer_dropdown_container">
            <button type="button" class="custom-dropdown-btn form-control text-start d-flex justify-content-between align-items-center @error('buyer_id') is-invalid @enderror" id="buyer_dropdown_btn">
                <span id="buyer_dropdown_text" class="{{ $selectedBuyer ? 'text-dark' : 'text-secondary' }}">{{ $selectedLabel }}</span>
                <i class="bi bi-chevron-down text-muted small"></i>
            </button>
            <input type="hidden" name="buyer_id" id="buyer_id" value="{{ $currentBuyerId }}">

            <div class="custom-dropdown-menu" id="buyer_dropdown_menu">
                <input type="text" class="custom-dropdown-search" id="buyer_dropdown_search" placeholder="Search for an item..." autocomplete="off">
                <ul class="custom-dropdown-list mb-0" id="buyer_dropdown_list">
                    <li class="custom-dropdown-item {{ empty($currentBuyerId) ? 'selected' : '' }}" data-value="" data-search="--select bp code-- select buyer">
                        --Select BP Code--
                    </li>
                    @foreach($buyers as $buyer)
                        @php
                            $bText = trim(($buyer->bp_code ? $buyer->bp_code . ' - ' : '') . ($buyer->business_name ?: $buyer->name));
                            $searchStr = strtolower(($buyer->bp_code ?? '') . ' ' . ($buyer->business_name ?? '') . ' ' . ($buyer->name ?? ''));
                        @endphp
                        <li class="custom-dropdown-item {{ (string)$currentBuyerId === (string)$buyer->id ? 'selected' : '' }}" 
                            data-value="{{ $buyer->id }}" 
                            data-search="{{ $searchStr }}">
                            {{ $bText }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @error('buyer_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
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
        <label for="order_number" class="form-label">Order Number</label>
        <input type="text" class="form-control @error('order_number') is-invalid @enderror" id="order_number" name="order_number" value="{{ old('order_number', $repair->order_number ?? '') }}">
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
        <label for="item_delivered_by" class="form-label">Item Delivered By (Buyer)<span class="text-danger">*</span></label>
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
        <label for="item_received_by" class="form-label">Item Received By (AJPL)<span class="text-danger">*</span></label>
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

    <!-- Submit Button -->
    <div class="col-12 mt-4">
        <button type="submit" class="btn btn-success">{{ $buttonText }}</button>
        <a href="{{ route('super-admin.repairs.index', request()->query()) }}" class="btn btn-secondary">Cancel</a>
    </div>
</div>

<style>
    /* Custom Searchable BP Code Dropdown Styles */
    .custom-dropdown-container {
        position: relative;
        width: 100%;
    }
    .custom-dropdown-btn {
        width: 100%;
        background-color: #ffffff;
        border: 1px solid #ced4da;
        border-radius: 0.5rem;
        padding: 0.45rem 0.85rem;
        font-size: 0.95rem;
        color: #333333;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        user-select: none;
        height: 38px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .custom-dropdown-btn:focus,
    .custom-dropdown-container.open .custom-dropdown-btn {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
    }
    .custom-dropdown-btn.is-invalid {
        border-color: #dc3545 !important;
    }
    .custom-dropdown-menu {
        display: none;
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 1050;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        padding: 8px;
    }
    .custom-dropdown-container.open .custom-dropdown-menu {
        display: block;
    }
    .custom-dropdown-search {
        width: 100%;
        border: 1.5px solid #cbd5e1;
        border-radius: 0.5rem;
        padding: 0.45rem 0.85rem;
        font-size: 0.9rem;
        outline: none;
        margin-bottom: 6px;
        color: #1e293b;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .custom-dropdown-search:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
    }
    .custom-dropdown-list {
        max-height: 220px;
        overflow-y: auto;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .custom-dropdown-item {
        padding: 8px 12px;
        cursor: pointer;
        border-radius: 0.375rem;
        font-size: 0.9rem;
        color: #1e293b;
        transition: background-color 0.15s ease-in-out, color 0.15s ease-in-out;
    }
    .custom-dropdown-item:hover {
        background-color: #f1f5f9;
        color: #2563eb;
    }
    .custom-dropdown-item.selected {
        background-color: #eff6ff;
        font-weight: 600;
        color: #2563eb;
    }
    .custom-dropdown-item.hidden {
        display: none !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('buyer_dropdown_container');
        if (!container) return;

        const btn = document.getElementById('buyer_dropdown_btn');
        const menu = document.getElementById('buyer_dropdown_menu');
        const searchInput = document.getElementById('buyer_dropdown_search');
        const textDisplay = document.getElementById('buyer_dropdown_text');
        const hiddenInput = document.getElementById('buyer_id');
        const listItems = container.querySelectorAll('.custom-dropdown-item');

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = container.classList.contains('open');
            container.classList.toggle('open');
            if (!isOpen) {
                searchInput.value = '';
                filterItems('');
                setTimeout(() => searchInput.focus(), 50);
            }
        });

        menu.addEventListener('click', function (e) {
            e.stopPropagation();
        });

        searchInput.addEventListener('input', function (e) {
            filterItems(e.target.value.toLowerCase().trim());
        });

        listItems.forEach(item => {
            item.addEventListener('click', function () {
                const val = item.getAttribute('data-value');
                const text = item.textContent.trim();

                hiddenInput.value = val;
                textDisplay.textContent = text;
                if (val) {
                    textDisplay.classList.remove('text-secondary');
                    textDisplay.classList.add('text-dark');
                    btn.classList.remove('is-invalid');
                } else {
                    textDisplay.classList.remove('text-dark');
                    textDisplay.classList.add('text-secondary');
                }

                listItems.forEach(i => i.classList.remove('selected'));
                item.classList.add('selected');

                container.classList.remove('open');
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        function filterItems(keyword) {
            listItems.forEach(item => {
                const searchData = (item.getAttribute('data-search') || item.textContent).toLowerCase();
                if (!keyword || searchData.includes(keyword)) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        }

        document.addEventListener('click', function (e) {
            if (!container.contains(e.target)) {
                container.classList.remove('open');
            }
        });

        const form = container.closest('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!hiddenInput.value) {
                    e.preventDefault();
                    btn.classList.add('is-invalid');
                    container.classList.add('open');
                    setTimeout(() => searchInput.focus(), 50);
                } else {
                    btn.classList.remove('is-invalid');
                }
            });
        }
    });
</script>

