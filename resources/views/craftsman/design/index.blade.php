@extends('craftsman.layouts.app')

@section('title', 'Approved Design Catalogue')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-emerald-200 pb-4">
        <div>
            <h4 class="text-2xl font-bold text-emerald-900">Approved Designs Catalogue</h4>
            <p class="text-sm text-emerald-600">Browse and manage approved product designs</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('craftsman.design.export', request()->query()) }}"
                class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition shadow-sm">
                <i class="bi bi-file-earmark-excel me-2"></i> Export Excel
            </a>
            <button onclick="window.print()"
                class="inline-flex items-center px-4 py-2 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 transition shadow-sm">
                <i class="bi bi-printer me-2"></i> Print
            </button>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100 flex flex-wrap items-center gap-4">
        <form action="{{ route('craftsman.design.index') }}" method="GET" class="relative flex-grow max-w-sm">
            <input type="text" name="search" placeholder="Quick search..."
                class="w-full pl-10 pr-4 py-2 border border-emerald-100 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none"
                value="{{ request('search') }}">
            <div class="absolute left-3 top-2.5 text-emerald-400">
                <i class="bi bi-search"></i>
            </div>
        </form>

        <div class="dropdown">
            <button class="inline-flex items-center px-4 py-2 bg-white border border-emerald-200 text-emerald-700 text-sm font-semibold rounded-lg hover:bg-emerald-50 transition dropdown-toggle"
                type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                <i class="bi bi-funnel me-2"></i> Filters
            </button>
            <div class="dropdown-menu p-6 shadow-2xl border-0 rounded-xl" style="min-width: 420px;">
                <form action="{{ route('craftsman.design.index') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-1">
                            <label class="block text-xs font-bold text-emerald-700 uppercase mb-1">Design Code</label>
                            <input type="text" name="filter_design_code" class="w-full px-3 py-2 border border-emerald-100 rounded-md text-sm outline-none focus:ring-2 focus:ring-emerald-500" value="{{ request('filter_design_code') }}" placeholder="Ex: DS-101">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-xs font-bold text-emerald-700 uppercase mb-1">Product Code</label>
                            <input type="text" name="filter_product_code" class="w-full px-3 py-2 border border-emerald-100 rounded-md text-sm outline-none focus:ring-2 focus:ring-emerald-500" value="{{ request('filter_product_code') }}" placeholder="Ex: PRD-001">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-emerald-700 uppercase mb-1">Product Name</label>
                            <input type="text" name="filter_product_name" class="w-full px-3 py-2 border border-emerald-100 rounded-md text-sm outline-none focus:ring-2 focus:ring-emerald-500" value="{{ request('filter_product_name') }}" placeholder="Ex: Gold Ring">
                        </div>

                        <!-- Category Dropdown -->
                        <div class="col-span-1">
                            <label class="block text-xs font-bold text-emerald-700 uppercase mb-1">Category</label>
                            <select name="filter_category" id="craftsman_design_category_filter" onchange="filterCraftsmanDesignSubcategories()" class="w-full px-3 py-2 border border-emerald-100 rounded-md text-sm outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('filter_category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Subcategory Dropdown (Dynamically Loaded) -->
                        <div class="col-span-1">
                            <label class="block text-xs font-bold text-emerald-700 uppercase mb-1">Subcategory</label>
                            <select name="filter_subcategory" id="craftsman_design_subcategory_filter" class="w-full px-3 py-2 border border-emerald-100 rounded-md text-sm outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="" id="craftsman_design_subcat_default_option">Select Category First</option>
                                @foreach($subcategories as $sub)
                                <option value="{{ $sub->id }}" data-category="{{ $sub->product_category_id }}" {{ request('filter_subcategory') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-2 pt-4 border-t border-emerald-50">
                        <button type="submit" class="flex-1 bg-emerald-900 text-white py-2 rounded-lg text-sm font-bold hover:bg-emerald-800 transition">Apply</button>
                        <a href="{{ route('craftsman.design.index') }}" class="flex-1 text-center border border-emerald-200 py-2 rounded-lg text-sm font-bold text-emerald-700 hover:bg-emerald-50 transition">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @php
    $loggedCraftsman = Auth::guard('craftsman')->user();
    // Handles Craftsman direct login or CraftsmanStaff portal login
    $craftsmanId = $loggedCraftsman->craftman_id ?? $loggedCraftsman->craftsman_id ?? $loggedCraftsman->id ?? null;

    $craftsmanFavoritesMap = $craftsmanId ? \App\Models\Favorite::where('user_id', $craftsmanId)
    ->where('user_type', 'craftsman')
    ->pluck('design_name', 'product_id')
    ->toArray() : [];
    @endphp

    <div>
        @if($designs->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($designs as $design)
            @php
            $isLocked = $design->isDesignLocked(Auth::guard('craftsman')->user());
            $isFavorited = array_key_exists($design->id, $craftsmanFavoritesMap);
            $currentDesignName = $isFavorited ? ($craftsmanFavoritesMap[$design->id] ?? '') : '';

            $imagesCount = $design->images->count();
            $firstImage = $imagesCount > 0 ? $design->images->first()->path : null;

            if (!$firstImage && $design->product_image) {
            $imgs = explode(',', $design->product_image);
            $firstImage = trim($imgs[0]);
            }

            $imgSrc = null;
            if ($firstImage) {
            if (str_starts_with($firstImage, 'http')) { $imgSrc = $firstImage; }
            elseif (str_starts_with($firstImage, 'products/')) { $imgSrc = asset('storage/' . $firstImage); }
            elseif (str_starts_with($firstImage, 'images/') || str_starts_with($firstImage, 'storage/')) { $imgSrc = asset($firstImage); }
            else { $imgSrc = asset('storage/products/' . $firstImage); }
            }
            @endphp

            <div class="group bg-white rounded-2xl shadow-sm border border-emerald-100 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col">

                <div class="relative h-56 bg-white overflow-hidden flex items-center justify-center p-4">
                    @if($imgSrc)
                    <img src="{{ $imgSrc }}"
                        class="w-full h-full object-contain transition-all duration-500 {{ $isLocked ? 'blur-[30px] opacity-40 grayscale' : 'group-hover:scale-110' }}"
                        alt="{{ $design->product_name }}">

                    @if($isLocked)
                    <div class="absolute inset-0 flex items-center justify-center bg-white/10 backdrop-blur-[2px]">
                        <div class="bg-white/90 p-3 rounded-full shadow-lg border border-emerald-50">
                            <img src="{{ asset('images/ajlogo.png') }}" class="w-12 h-12 object-contain" alt="Locked">
                        </div>
                    </div>
                    @elseif($imagesCount > 1)
                    <div class="absolute bottom-3 right-3">
                        <span class="bg-emerald-900/80 text-white text-[10px] font-bold px-2 py-1 rounded-full backdrop-blur-sm">
                            +{{ $imagesCount - 1 }} Images
                        </span>
                    </div>
                    @endif
                    @else
                    <div class="flex flex-col items-center justify-center text-emerald-100">
                        <i class="bi bi-image text-5xl"></i>
                    </div>
                    @endif
                </div>

                <div class="p-4 flex flex-col flex-grow bg-white border-t border-emerald-50">
                    <div class="flex justify-between items-start mb-2">
                        <div class="max-w-[85%] mx-auto flex flex-col items-center justify-center gap-1">
                            <h6 class="font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-4 py-1 rounded-lg shadow-sm text-center truncate w-full"
                                title="{{ $design->design_code }}">
                                {{ $design->design_code }}
                            </h6>
                            <span id="fav-badge-{{ $design->id }}" class="{{ $isFavorited && !empty($currentDesignName) ? '' : 'hidden' }} text-xs font-semibold text-pink-700 bg-pink-50 border border-pink-200 px-2 py-0.5 rounded-md truncate max-w-full">
                                {{ $currentDesignName }}
                            </span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-sm mb-4">
                        <span class="text-emerald-900 font-bold">{{ $design->category->name ?? 'N/A' }}</span>
                        <span class="text-emerald-900 font-black tracking-tight">{{ $design->weight_from }}-{{ $design->weight_to }}g</span>
                    </div>

                    <div class="mt-auto flex gap-2">
                        @if(!$isLocked)
                        <a href="{{ route('craftsman.design.show', $design->id) }}"
                            class="flex-1 text-center bg-emerald-900 text-white text-[10px] font-bold py-2.5 rounded-xl hover:bg-emerald-800 transition-all active:scale-95 shadow-sm">
                            VIEW
                        </a>
                        @else
                        <button disabled
                            class="flex-1 flex items-center justify-center gap-2 bg-slate-100 text-slate-400 text-[10px] font-bold py-2.5 rounded-xl cursor-not-allowed border border-slate-200">
                            <i class="bi bi-lock-fill"></i> LOCKED
                        </button>
                        @endif

                        <button type="button"
                            id="fav-btn-{{ $design->id }}"
                            onclick="openFavoriteModal({{ $design->id }}, '{{ addslashes($design->design_code) }}', '{{ addslashes($currentDesignName) }}', {{ $isFavorited ? 'true' : 'false' }})"
                            class="p-2 {{ $isFavorited ? 'bg-pink-600 text-white border-pink-700' : 'bg-pink-50 text-pink-600 border-pink-100 hover:bg-pink-100' }} border rounded-xl transition-colors shadow-sm"
                            title="{{ $isFavorited ? 'Edit Favorite Design Name' : 'Add to Favorites' }}">
                            <i class="bi {{ $isFavorited ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-8 flex justify-center">
            {{ $designs->appends(request()->query())->links() }}
        </div>
        @else
        <div class="bg-white rounded-2xl border border-emerald-100 p-20 text-center shadow-sm">
            <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-patch-check text-emerald-200 text-4xl"></i>
            </div>
            <h5 class="text-emerald-900 font-bold">No approved designs found.</h5>
        </div>
        @endif
    </div>
</div>

<!-- Add/Edit Favorite Modal -->
<div id="favoriteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center font-bold">
                    <i class="bi bi-heart-fill"></i>
                </div>
                <h3 id="favModalTitle" class="font-bold text-slate-800 text-base">Add to Favorites</h3>
            </div>
            <button type="button" onclick="closeFavoriteModal()" class="text-slate-400 hover:text-slate-600 text-lg">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="favoriteForm" onsubmit="submitFavoriteForm(event)">
            <div class="p-5 space-y-4">
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Design Code</span>
                    <p id="favModalDesignCode" class="text-sm font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 rounded-lg px-3 py-1.5 inline-block"></p>
                </div>
                <div>
                    <label for="fav_custom_design_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Custom Design Name <span class="text-slate-400 font-normal">(Optional)</span>
                    </label>
                    <input type="text" id="fav_custom_design_name" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-pink-500 focus:bg-white outline-none transition-all" placeholder="E.g., Special Casting, Master Mold...">
                    <p class="text-[11px] text-slate-500 mt-1">Assign your own reference name to easily search and identify this design.</p>
                </div>
            </div>
            <div class="p-5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeFavoriteModal()" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-200/70 rounded-xl transition-all">Cancel</button>
                <button type="submit" id="favSubmitBtn" class="px-5 py-2 text-sm font-bold text-white bg-pink-600 hover:bg-pink-700 rounded-xl transition-all shadow-sm">Save to Favorites</button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentTargetProductId = null;

    function openFavoriteModal(productId, designCode, currentName, isFavorited) {
        currentTargetProductId = productId;
        document.getElementById('favModalDesignCode').textContent = designCode;
        document.getElementById('fav_custom_design_name').value = currentName || '';

        const title = document.getElementById('favModalTitle');
        const submitBtn = document.getElementById('favSubmitBtn');

        if (isFavorited) {
            title.textContent = 'Update Favorite Design Name';
            submitBtn.textContent = 'Update Name';
        } else {
            title.textContent = 'Add to Favorites';
            submitBtn.textContent = 'Save to Favorites';
        }

        const modal = document.getElementById('favoriteModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            document.getElementById('fav_custom_design_name').focus();
        }, 50);
    }

    function closeFavoriteModal() {
        const modal = document.getElementById('favoriteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        currentTargetProductId = null;
    }

    function submitFavoriteForm(event) {
        event.preventDefault();
        if (!currentTargetProductId) return;

        const designName = document.getElementById('fav_custom_design_name').value.trim();
        const submitBtn = document.getElementById('favSubmitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        fetch("{{ route('craftsman.favorites.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    product_id: currentTargetProductId,
                    design_name: designName
                })
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                closeFavoriteModal();

                if (data.success) {
                    const btn = document.getElementById(`fav-btn-${currentTargetProductId}`);
                    if (btn) {
                        btn.className = "p-2 bg-pink-600 text-white border-pink-700 border rounded-xl transition-colors shadow-sm";
                        btn.innerHTML = `<i class="bi bi-heart-fill"></i>`;
                        btn.title = "Edit Favorite Design Name";
                    }

                    const badge = document.getElementById(`fav-badge-${currentTargetProductId}`);
                    if (badge) {
                        if (designName) {
                            badge.textContent = designName;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }
                }
                alert(data.message || 'Updated successfully!');
            })
            .catch(error => {
                submitBtn.disabled = false;
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
    }

    function filterCraftsmanDesignSubcategories() {
        const categorySelect = document.getElementById('craftsman_design_category_filter');
        const subcategorySelect = document.getElementById('craftsman_design_subcategory_filter');
        const defaultOption = document.getElementById('craftsman_design_subcat_default_option');
        if (!categorySelect || !subcategorySelect) return;

        const categoryId = categorySelect.value;
        const options = subcategorySelect.querySelectorAll('option[data-category]');

        if (!categoryId) {
            subcategorySelect.disabled = true;
            if (defaultOption) {
                defaultOption.textContent = 'Select Category First';
            }
            subcategorySelect.value = '';
            options.forEach(opt => {
                opt.hidden = true;
                opt.disabled = true;
            });
            return;
        }

        subcategorySelect.disabled = false;
        if (defaultOption) {
            defaultOption.textContent = 'All Subcategories';
        }

        let selectedOptionHidden = false;
        options.forEach(option => {
            if (option.getAttribute('data-category') === categoryId) {
                option.hidden = false;
                option.disabled = false;
            } else {
                option.hidden = true;
                option.disabled = true;
                if (option.selected) {
                    selectedOptionHidden = true;
                }
            }
        });

        if (selectedOptionHidden) {
            subcategorySelect.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        filterCraftsmanDesignSubcategories();
    });
</script>
@endsection