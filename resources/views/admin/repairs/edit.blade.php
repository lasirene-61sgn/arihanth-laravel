@extends('admin.layouts.app')

@section('title', 'Edit Repair')

@section('styles')
<style>
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
                <h1 class="h2">Edit Repair #{{ $repair->id }}</h1>
                <a href="{{ route('admin.repairs.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>

            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="card mt-3">
                <div class="card-body">
                    <form action="{{ route('admin.repairs.update', $repair->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Repair Date</label>
                                <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($repair->repair_date)->format('d M Y') }}" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">BP Code (Buyer) *</label>
                                <div class="custom-dropdown-container" id="buyer_id_container">
                                    <div class="custom-dropdown-display" id="buyer_id_display">--Select BP Code--</div>
                                    <div class="custom-dropdown-menu" id="buyer_id_menu">
                                        <input type="text" class="custom-dropdown-search" id="buyer_id_search" placeholder="Search for an item...">
                                        <ul class="custom-dropdown-list" id="buyer_id_list">
                                            <li class="custom-dropdown-item" data-value="">--Select BP Code--</li>
                                            @foreach($buyers as $buyer)
                                                <li class="custom-dropdown-item" data-value="{{ $buyer->id }}">
                                                    {{ $buyer->bp_code }} - {{ $buyer->business_name ?? $buyer->name }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    {{-- Hidden select for form submission --}}
                                    <select name="buyer_id" id="buyer_id" style="display: none;" required>
                                        <option value="">Select BP Code</option>
                                        @foreach($buyers as $buyer)
                                            <option value="{{ $buyer->id }}" {{ $repair->buyer_id == $buyer->id ? 'selected' : '' }}>
                                                {{ $buyer->bp_code }} - {{ $buyer->business_name ?? $buyer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Name</label>
                                <input type="text" name="product_name" class="form-control" value="{{ old('product_name', $repair->product_name) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Weight</label>
                                <input type="number" name="weight" class="form-control" step="0.01" value="{{ old('weight', $repair->weight) }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Order Number</label>
                                <input type="text" name="order_no" class="form-control" value="{{ old('order_no', $repair->order_no) }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="repair" class="form-label">Repairs/Samples Type</label>
                                <select class="form-select" id="repair" name="repair">
                                    <option value="" disabled>Please Select</option>

                                    <option value="Repair" {{ old('repair', $repair->repair) == 'Repair' ? 'selected' : '' }}>
                                        Repair
                                    </option>

                                    <option value="Samples" {{ old('repair', $repair->repair) == 'Samples' ? 'selected' : '' }}>
                                        Samples
                                    </option>
                                </select>

                                @error('repair')
                                <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reference Number:</label>
                            <textarea name="ref" class="form-control" rows="3">{{ old('ref', $repair->ref) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $repair->notes) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Repair Details</label>
                            <textarea name="repair_details" class="form-control" rows="3">{{ old('repair_details', $repair->repair_details) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sample Details</label>
                            <textarea name="sample_details" class="form-control" rows="3">{{ old('sample_details', $repair->sample_details) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Item Given To</label>
                                <input type="text" name="item_given_to" class="form-control" value="{{ old('item_given_to', $repair->item_given_to) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Image Proof</label>
                                <input type="file" name="image_proof" class="form-control" accept="image/*">
                                @if($repair->image_proof)
                                <small class="text-muted">Current: <a href="{{ asset($repair->image_proof) }}" target="_blank">View</a></small>
                                @endif
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Repair</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
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

        // Initialize BP Code Dropdown
        initSearchableDropdown('buyer_id_container', 'buyer_id_display', 'buyer_id_menu', 'buyer_id_search', 'buyer_id_list', 'buyer_id', '--Select BP Code--');
    });
</script>
@endsection