@extends('admin.layouts.app')

@section('title', 'Design Management')

@section('content')
<div class="p-4 sm:p-6 space-y-6" x-data="{ activeTab: '{{ request('tab', 'all') }}' }">
    <!-- Header/Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Design Management</h1>
            <p class="text-sm text-gray-500 mt-1">Review and approve product designs</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button onclick="exportSelectedDesigns()" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="bi bi-file-earmark-excel"></i>
                <span>Export Excel</span>
            </button>
            <button onclick="printSelectedDesigns()" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-lg border border-gray-300 transition-colors shadow-sm">
                <i class="bi bi-printer"></i>
                <span>Print</span>
            </button>
            <button onclick="bulkPrintPRN()" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg border border-slate-300 transition-colors shadow-sm">
                <i class="bi bi-file-earmark-code"></i>
                <span>PRN</span>
            </button>
            @if(request('tab') === 'accepted')
            <button onclick="bulkPrint80x40()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm font-semibold rounded-lg border border-blue-300 transition-colors shadow-sm">
                <i class="bi bi-printer"></i>
                <span>Print 80x40</span>
            </button>
            @endif
            <button onclick="bulkAccept()" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="bi bi-check-circle"></i>
                <span>Bulk Accept</span>
            </button>
            <button onclick="bulkReject()" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="bi bi-x-circle"></i>
                <span>Bulk Reject</span>
            </button>
            <!-- <button onclick="showUnlockModal('selected')" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="bi bi-unlock"></i>
                <span>Unlock Selected</span>
            </button> -->
            <a href="{{ route('admin.design.generate-missing-qrcodes') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="bi bi-qr-code"></i>
                <span>Generate Missing QRs</span>
            </a>
            <button onclick="showUnlockModal('category')" class="inline-flex items-center gap-2 px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="bi bi-tags"></i>
                <span>Unlock Category</span>
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border-l-4 border-emerald-400 p-4 rounded-lg flex items-center justify-between shadow-sm">
        <div class="flex items-center">
            <i class="bi bi-check-circle-fill text-emerald-400 text-xl mr-3"></i>
            <p class="text-sm text-emerald-800 font-medium">{{ session('success') }}</p>
        </div>
        <button type="button" class="text-emerald-400 hover:text-emerald-600 transition-colors" onclick="this.parentElement.remove()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    @endif

    <!-- Filters & Search -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Search -->
                <form action="{{ route('admin.design.index') }}" method="GET" class="relative group min-w-[300px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Global Search..."
                        class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 transition-all text-sm">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-magenta-500 transition-colors"></i>
                </form>

                <!-- Advanced Filter Dropdown -->
                <div class="relative" x-data="{ filterOpen: false }">
                    <button @click="filterOpen = !filterOpen" class="flex items-center gap-2 px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm font-medium rounded-lg border border-gray-200 transition-colors">
                        <i class="bi bi-funnel"></i>
                        <span>Advanced Filters</span>
                        <i class="bi bi-chevron-down text-xs transition-transform" :class="filterOpen ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="filterOpen" @click.away="filterOpen = false"
                        class="absolute left-0 mt-2 w-96 bg-white rounded-xl shadow-xl border border-gray-100 z-50 p-5"
                        x-transition:enter="transition ease-out duration-200"
                        style="display: none;">

                        <form action="{{ route('admin.design.index') }}" method="GET" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Product Name</label>
                                    <input type="text" name="filter_name" value="{{ request('filter_name') }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Product Code</label>
                                    <input type="text" name="filter_code" value="{{ request('filter_code') }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Design Code</label>
                                    <input type="text" name="filter_design_code" value="{{ request('filter_design_code') }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">BP Code</label>
                                    <input type="text" name="filter_bp_code" value="{{ request('filter_bp_code') }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Category</label>
                                    <select name="filter_category" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ request('filter_category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Subcategory</label>
                                    <input type="text" name="filter_subcategory" value="{{ request('filter_subcategory') }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                                </div>
                            </div>
                            <div class="flex gap-3 pt-4 border-t border-gray-100">
                                <button type="submit" class="flex-1 px-4 py-2 bg-magenta-800 hover:bg-magenta-900 text-white text-sm font-bold rounded-lg transition-colors">Apply</button>
                                <a href="{{ route('admin.design.index') }}" class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-lg text-center transition-colors">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sort Dropdown -->
                <div class="relative" x-data="{ sortOpen: false }">
                    <button @click="sortOpen = !sortOpen" class="flex items-center gap-2 px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm font-medium rounded-lg border border-gray-200 transition-colors">
                        <i class="bi bi-sort-down"></i>
                        <span>Sort</span>
                    </button>

                    <div x-show="sortOpen" @click.away="sortOpen = false"
                        class="absolute left-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden"
                        x-transition:enter="transition ease-out duration-200"
                        style="display: none;">
                        <div class="flex flex-col">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-magenta-50 hover:text-magenta-800 {{ request('sort') == 'latest' ? 'bg-magenta-50 text-magenta-800 font-bold' : '' }}">Latest</a>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'name_asc']) }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-magenta-50 hover:text-magenta-800 {{ request('sort') == 'name_asc' ? 'bg-magenta-50 text-magenta-800 font-bold' : '' }}">Name (A-Z)</a>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'name_desc']) }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-magenta-50 hover:text-magenta-800 {{ request('sort') == 'name_desc' ? 'bg-magenta-50 text-magenta-800 font-bold' : '' }}">Name (Z-A)</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="flex space-x-8">
            @php
            $tabs = [
            'all' => ['label' => 'All Products', 'count' => $statusCounts['all'], 'color' => 'gray'],
            'accepted' => ['label' => 'Accepted', 'count' => $statusCounts['accepted'], 'color' => 'emerald'],
            'rejected' => ['label' => 'Rejected', 'count' => $statusCounts['rejected'], 'color' => 'rose'],
            'pending' => ['label' => 'Pending Approval', 'count' => $statusCounts['pending'], 'color' => 'amber'],
            ];
            @endphp
            @foreach($tabs as $val => $cfg)
            <a href="{{ request()->fullUrlWithQuery(['tab' => $val, 'page' => 1]) }}"
                class="py-4 px-1 border-b-2 font-bold text-sm transition-all whitespace-nowrap flex items-center gap-2 {{ (request('tab', 'all') == $val) ? 'border-magenta-800 text-magenta-800' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                {{ $cfg['label'] }}
                <span class="px-2 py-0.5 rounded-full text-[10px] {{ (request('tab', 'all') == $val) ? 'bg-magenta-50 text-magenta-700 border border-magenta-100' : 'bg-gray-50 text-gray-600 border border-gray-100' }}">
                    {{ $cfg['count'] }}
                </span>
            </a>
            @endforeach
        </nav>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider w-10 text-center">
                            <input type="checkbox" class="select-all-designs rounded border-gray-300 text-magenta-600 focus:ring-magenta-500 h-4 w-4">
                        </th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Image</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Codes</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Design Info</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Type / Order</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Created by</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Unlock Info</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50 transition-colors group">
                        <td class="px-6 py-4 text-center">
                            @php
                            $imagesCount = $product->images->count();
                            $firstImage = $imagesCount > 0 ? $product->images->first()->path : null;
                            if (!$firstImage && $product->product_image) {
                            $imgs = explode(',', $product->product_image);
                            $firstImage = trim($imgs[0]);
                            }
                            $imgUrl = null;
                            if ($firstImage) {
                            if (str_starts_with($firstImage, 'http')) { $imgUrl = $firstImage; }
                            elseif (str_starts_with($firstImage, 'products/')) { $imgUrl = asset('storage/' . $firstImage); }
                            elseif (str_starts_with($firstImage, 'images/') || str_starts_with($firstImage, 'storage/')) { $imgUrl = asset($firstImage); }
                            else { $imgUrl = asset('storage/products/' . $firstImage); }
                            }
                            $activeTab = request('tab', 'all');
                            @endphp
                            @if($activeTab !== 'all' || $product->design_status !== 'Accepted')
                            <input type="checkbox" class="design-checkbox rounded border-gray-300 text-magenta-600 focus:ring-magenta-500 h-4 w-4"
                                value="{{ $product->id }}"
                                data-code="{{ $product->design_code ?? $product->product_code }}"
                                data-name="{{ $product->product_name }}"
                                data-image="{{ $imgUrl ?? '' }}">
                            @else
                            <div class="flex justify-center">
                                <i class="bi bi-check-circle-fill text-emerald-500" title="Accepted"></i>
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="relative w-16 h-16 shrink-0 group/img">
                                @if($imgUrl)
                                <img src="{{ $imgUrl }}"
                                    alt="Product"
                                    class="w-full h-full object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                                    onclick="window.openUniversalPreview('{{ $imgUrl }}', 'image')">
                                @if($imagesCount > 1)
                                <span class="absolute -top-2 -right-2 bg-gray-900 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white">
                                    +{{ $imagesCount - 1 }}
                                </span>
                                @endif
                                @else
                                <div class="w-full h-full bg-gray-100 rounded-lg flex items-center justify-content-center text-gray-400">
                                    <i class="bi bi-image text-xl"></i>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-gray-400">PC: {{ $product->product_code }}</span>
                                <span class="text-sm font-extrabold text-emerald-600 uppercase tracking-wider">DC: {{ $product->design_status === 'Accepted' ? ($product->design_code ?? '-') : '-' }}</span>
                                @if($product->design_status === 'Accepted' && $product->qr_code)
                                <div class="mt-1">
                                    <img src="{{ asset('storage/' . $product->qr_code) }}" alt="QR" class="w-8 h-8 border border-gray-100 rounded cursor-pointer hover:scale-150 transition-transform" onclick="window.openUniversalPreview('{{ asset('storage/' . $product->qr_code) }}', 'image')">
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-gray-900 line-clamp-1">{{ $product->product_name }}</span>
                                <span class="text-xs text-gray-500">{{ $product->category->name ?? 'N/A' }} / {{ $product->subcategory->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                <span class="text-[10px] font-bold text-gray-700 uppercase tracking-widest">{{ $product->type }}</span>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold w-fit {{ $product->order_type == 'Super Urgent' ? 'bg-rose-100 text-rose-700' : ($product->order_type == 'Urgent' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700') }}">
                                    {{ $product->order_type }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <!--<span class="text-xs font-bold text-gray-800">{{ $product->creator_name }}</span>-->
                                <span class="text-[10px] text-indigo-600 font-bold uppercase tracking-tighter">{{ $product->bp_code }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                            $statusClass = [
                            'Accepted' => 'bg-emerald-100 text-emerald-700',
                            'Rejected' => 'bg-rose-100 text-rose-700',
                            'Pending' => 'bg-sky-100 text-sky-700'
                            ][$product->design_status] ?? 'bg-sky-100 text-sky-700';
                            @endphp
                            <span class="inline-flex px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $statusClass }}">
                                {{ $product->design_status ?: 'Pending' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1.5">
                                @if($product->is_locked)
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-rose-50 text-rose-600 text-[10px] font-bold rounded border border-rose-100 w-fit"><i class="bi bi-lock-fill"></i> Image Locked</span>
                                @else
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] font-bold rounded border border-emerald-100 w-fit"><i class="bi bi-unlock-fill"></i> Image Free</span>
                                @endif

                                @if($product->userAccess->count() > 0)
                                @foreach($product->userAccess as $access)
                                {{-- Manually parse to Carbon --}}
                                @php
                                $unlockedUntil = $access->unlocked_until ? \Carbon\Carbon::parse($access->unlocked_until) : null;
                                @endphp

                                @if($unlockedUntil && $unlockedUntil->isFuture())
                                <div class="flex flex-col border-l-2 border-indigo-200 pl-2 py-0.5">
                                    <span class="font-mono text-indigo-600 font-bold text-[10px]">{{ $access->user_code }}</span>
                                    <span class="text-[9px] text-gray-500 countdown-timer" data-until="{{ $unlockedUntil->timestamp }}">
                                        {{ $unlockedUntil->diffForHumans() }}
                                    </span>
                                </div>
                                @endif
                                @endforeach
                                @endif

                                {{-- Manually parse Global Access date --}}
                                @php
                                $globalUnlockedUntil = $product->design_view_unlocked_until ? \Carbon\Carbon::parse($product->design_view_unlocked_until) : null;
                                @endphp

                                @if($globalUnlockedUntil && $globalUnlockedUntil->isFuture())
                                <div class="text-[10px] text-emerald-600 font-bold italic flex flex-col border-t border-gray-50 mt-1 pt-1">
                                    <span>GLOBAL ACCESS</span>
                                    <span class="countdown-timer text-[9px]" data-until="{{ $globalUnlockedUntil->timestamp }}">
                                        {{ $globalUnlockedUntil->diffForHumans() }}
                                    </span>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                @if($product->design_status != 'Accepted')
                                <button type="button" class="p-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-lg transition-all shadow-sm"
                                    onclick="showAcceptModal('{{ $product->id }}', '{{ $product->product_code }}')" title="Accept Design">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                @endif

                                @if($product->design_status == 'Pending' || !$product->design_status)
                                <form action="{{ route('admin.design.reject', $product) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-2 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-lg transition-all shadow-sm"
                                        onclick="return confirm('Silahkan konfirmasi untuk mereject desain ini?');" title="Reject Design">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('admin.design.show', $product) }}"
                                    class="p-2 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg transition-all shadow-sm"
                                    title="View Full Details">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <form action="{{ route('admin.product.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus desain ini secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-lg transition-all shadow-sm" title="Delete Design">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500 italic">No records found matching your criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
            {{ $products->links() }}
        </div>
        @endif

    </div>
</div>

<!-- Modals -->
@include('admin.design.partials.modals')


@endsection

@section('scripts')
<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');

        // Reset subcategory info if closing unlock modal
        if (id === 'unlockModal') {
            const subContainer = document.getElementById('subcategoryInfoContainer');
            if (subContainer) subContainer.classList.add('hidden');
            const catSelect = document.getElementById('unlockCategorySelect');
            if (catSelect) catSelect.value = '';
        }
    }

    // Design checkbox logic
    document.querySelectorAll('.select-all-designs').forEach(selectAll => {
        selectAll.addEventListener('change', function() {
            const table = this.closest('table');
            table.querySelectorAll('.design-checkbox').forEach(cb => {
                if (cb.offsetParent !== null) cb.checked = this.checked;
            });
        });
    });

    function bulkAccept() {
        const selected = Array.from(document.querySelectorAll('.design-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one design to accept.');
            return;
        }

        if (!confirm(`Are you sure you want to accept ${selected.length} designs?`)) return;

        fetch("{{ route('admin.design.bulk-accept') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    selected_designs: selected
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Something went wrong');
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred. Please try again.');
            });
    }

    function bulkReject() {
        const selected = Array.from(document.querySelectorAll('.design-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one design to reject.');
            return;
        }

        if (!confirm(`Are you sure you want to reject ${selected.length} designs?`)) return;

        fetch("{{ route('admin.design.bulk-reject') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    selected_designs: selected
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Something went wrong');
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred. Please try again.');
            });
    }

    // Countdown Timer logic
    function updateTimers() {
        const timers = document.querySelectorAll('.countdown-timer');
        const now = Math.floor(Date.now() / 1000);

        timers.forEach(timer => {
            const until = parseInt(timer.getAttribute('data-until'));
            const diff = until - now;

            if (diff <= 0) {
                timer.textContent = 'Expired';
                timer.classList.add('text-red-500');
                return;
            }

            const days = Math.floor(diff / 86400);
            const hours = Math.floor((diff % 86400) / 3600);
            const mins = Math.floor((diff % 3600) / 60);
            const secs = diff % 60;

            let timeStr = '';
            if (days > 0) timeStr += days + 'd ';
            if (hours > 0 || days > 0) timeStr += hours + 'h ';
            timeStr += mins + 'm ' + secs + 's left';

            timer.textContent = timeStr;
        });
    }

    setInterval(updateTimers, 1000);
    window.addEventListener('load', updateTimers);

    function printSelectedDesigns() {
        const selected = Array.from(document.querySelectorAll('.design-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Silahkan pilih minimal 1 desain untuk diprint.');
            return;
        }
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('admin.design.print-selected') }}";
        form.target = '_blank';
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrfInput);
        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_designs[]';
            input.value = id;
            form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    function bulkPrintPRN() {
        const selected = Array.from(document.querySelectorAll('.design-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Silahkan pilih minimal 1 desain untuk diprint PRN.');
            return;
        }
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('admin.design.bulk-print-prn') }}";
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrfInput);
        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_designs[]';
            input.value = id;
            form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    function bulkPrint80x40() {
        const selected = Array.from(document.querySelectorAll('.design-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Silahkan pilih minimal 1 desain untuk diprint.');
            return;
        }
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('admin.design.bulk-print-80x40') }}";
        form.target = '_blank';
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrfInput);
        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_designs[]';
            input.value = id;
            form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    function showAcceptModal(productId, productCode) {
        const form = document.getElementById('acceptDesignForm');
        const urlTemplate = "{{ route('admin.design.accept', '000', false) }}";
        form.action = urlTemplate.replace('000', productId);
        document.getElementById('acceptProductCode').textContent = productCode;
        openModal('acceptDesignModal');
    }

    function showUnlockModal(type = 'selected') {
        const previewSection = document.getElementById('selectedDesignsPreview');
        const categorySection = document.getElementById('categorySelectionSection');
        const previewContainer = document.getElementById('previewImagesContainer');

        window.currentUnlockType = type;

        if (type === 'selected') {
            const checkboxes = document.querySelectorAll('.design-checkbox:checked');
            const selected = Array.from(checkboxes).map(cb => cb.value);
            if (selected.length === 0) {
                alert('Silahkan pilih minimal 1 desain untuk diunlock.');
                return;
            }

            previewContainer.innerHTML = '';
            let hasImages = false;

            checkboxes.forEach(cb => {
                const imgUrl = cb.getAttribute('data-image');
                const code = cb.getAttribute('data-code');
                if (imgUrl) {
                    hasImages = true;
                    const thumb = document.createElement('div');
                    thumb.className = "w-10 h-10 border-2 border-white rounded-lg shadow-sm overflow-hidden";
                    thumb.innerHTML = `<img src="${imgUrl}" class="w-full h-full object-cover" title="${code}">`;
                    previewContainer.appendChild(thumb);
                }
            });
            previewSection.style.display = hasImages ? 'block' : 'none';
            categorySection.style.display = 'none';
        } else {
            previewSection.style.display = 'none';
            categorySection.style.display = 'block';
        }

        updateDurationOptions('minutes');
        loadAvailableUsers();
        openModal('unlockModal');
    }

    const durationOptions = {
        minutes: [{
            v: 1,
            l: '1 Minute'
        }, {
            v: 5,
            l: '5 Minutes'
        }, {
            v: 15,
            l: '15 Minutes'
        }, {
            v: 30,
            l: '30 Minutes'
        }, {
            v: 45,
            l: '45 Minutes'
        }],
        hours: [{
            v: 1,
            l: '1 Hour'
        }, {
            v: 2,
            l: '2 Hours'
        }, {
            v: 4,
            l: '4 Hours'
        }, {
            v: 8,
            l: '8 Hours'
        }, {
            v: 24,
            l: '1 Day'
        }, {
            v: 168,
            l: '1 Week'
        }],
        months: [{
            v: 1,
            l: '1 Month'
        }, {
            v: 3,
            l: '3 Months'
        }, {
            v: 6,
            l: '6 Months'
        }],
        years: [{
            v: 1,
            l: '1 Year'
        }],
        permanent: [{
            v: -1,
            l: 'Permanent'
        }]
    };

    function updateDurationOptions(unit) {
        const select = document.getElementById('unlockDuration');
        const wrapper = document.getElementById('durationAmountWrapper');
        select.innerHTML = '';
        if (unit === 'permanent') {
            wrapper.style.display = 'none';
            select.innerHTML = '<option value="-1" selected>Permanent</option>';
        } else {
            wrapper.style.display = 'block';
            durationOptions[unit].forEach(opt => {
                const o = document.createElement('option');
                o.value = opt.v;
                o.textContent = opt.l;
                select.appendChild(o);
            });
        }
    }

    document.querySelectorAll('input[name="durationUnit"]').forEach(radio => {
        radio.addEventListener('change', (e) => updateDurationOptions(e.target.value));
    });

    document.querySelectorAll('input[name="userScope"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            document.getElementById('userSelectionContainer').style.display = e.target.value === 'specific' ? 'block' : 'none';
        });
    });

    async function loadAvailableUsers() {
        const select = document.getElementById('selectedUsers');
        select.innerHTML = '<option disabled class="p-4 text-center">Loading users...</option>';
        try {
            const response = await fetch("{{ route('admin.design.get-available-users') }}");
            const users = await response.json();
            select.innerHTML = '';

            const groupMap = {
                buyers: {
                    label: 'Buyers',
                    prefix: 'buyer',
                    id: 'bp_code',
                    name: (u) => u.business_name || u.name
                },
                key_users: {
                    label: 'Key Users',
                    prefix: 'key_user',
                    id: 'user_code',
                    name: (u) => u.full_name
                },
                users: {
                    label: 'Users',
                    prefix: 'user',
                    id: 'user_code',
                    name: (u) => u.full_name
                },
                craftsmen: {
                    label: 'Craftsmen',
                    prefix: 'craftsman',
                    id: 'craftman_code',
                    name: (u) => u.full_name || u.name
                }
            };

            Object.entries(groupMap).forEach(([key, cfg]) => {
                if (users[key]?.length) {
                    const group = document.createElement('optgroup');
                    group.label = cfg.label;
                    group.className = "font-extrabold text-gray-900 border-t border-gray-100 mt-2";
                    users[key].forEach(u => {
                        const opt = document.createElement('option');
                        opt.value = `${cfg.prefix}:${u[cfg.id]}`;
                        opt.textContent = `${u[cfg.id]} - ${cfg.name(u)}`;
                        opt.className = "p-2 font-medium text-gray-600 hover:bg-indigo-50";
                        group.appendChild(opt);
                    });
                    select.appendChild(group);
                }
            });
        } catch (error) {
            select.innerHTML = '<option disabled class="text-rose-500">Error loading users</option>';
        }
    }

    document.getElementById('userSearchInput').addEventListener('input', function(e) {
        const filter = e.target.value.toLowerCase();
        const options = document.getElementById('selectedUsers').getElementsByTagName('option');
        const groups = document.getElementById('selectedUsers').getElementsByTagName('optgroup');

        Array.from(options).forEach(opt => opt.style.display = opt.textContent.toLowerCase().includes(filter) ? '' : 'none');
        Array.from(groups).forEach(grp => {
            const hasVisible = Array.from(grp.children).some(opt => opt.style.display !== 'none');
            grp.style.display = hasVisible ? '' : 'none';
        });
    });

    function updateUnlockSelectionLabel() {
        const selectedCategories = document.querySelectorAll('.unlock-category-checkbox:checked').length;
        const selectedSubcategories = document.querySelectorAll('.unlock-subcategory-checkbox:checked').length;
        const label = document.getElementById('selectedUnlockNodeLabel');
        if (!label) return;
        
        if (selectedCategories > 0 || selectedSubcategories > 0) {
            label.textContent = `Selected: ${selectedCategories} Categories, ${selectedSubcategories} Subcategories`;
        } else {
            label.textContent = '';
        }
    }

    function toggleSubcategories(categoryId, checked) {
        document.querySelectorAll(`.unlock-subcategory-checkbox[data-category="${categoryId}"]`).forEach(cb => {
            cb.checked = checked;
        });
    }

    async function confirmUnlock() {
        const btn = document.getElementById('confirmUnlockBtn');
        const unlockType = window.currentUnlockType || 'selected';
        const selected = unlockType === 'selected' ? Array.from(document.querySelectorAll('.design-checkbox:checked')).map(cb => cb.value) : null;
        
        let categoryIds = [];
        let subcategoryIds = [];
        
        if (unlockType === 'category') {
            categoryIds = Array.from(document.querySelectorAll('.unlock-category-checkbox:checked')).map(cb => cb.getAttribute('data-category'));
            subcategoryIds = Array.from(document.querySelectorAll('.unlock-subcategory-checkbox:checked')).map(cb => cb.getAttribute('data-subcategory'));
            
            if (categoryIds.length === 0 && subcategoryIds.length === 0) {
                alert('Please select at least one category or subcategory.');
                return;
            }
        }

        let duration = parseInt(document.getElementById('unlockDuration').value);
        const durationUnit = document.querySelector('input[name="durationUnit"]:checked').value;
        const userScope = document.querySelector('input[name="userScope"]:checked').value;
        const selectedUsers = userScope === 'specific' ? Array.from(document.getElementById('selectedUsers').selectedOptions).map(o => o.value) : null;

        if (userScope === 'specific' && (!selectedUsers || !selectedUsers.length)) {
            alert('Pilih minimal 1 user untuk akses private.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="inline-block animate-spin mr-2">⟳</span>Processing...';

        const payload = {
            unlock_type: unlockType,
            duration,
            duration_unit: durationUnit,
            user_scope: userScope,
            selected_users: selectedUsers
        };

        if (unlockType === 'selected') {
            payload.ids = selected;
        } else {
            payload.category_ids = categoryIds;
            payload.subcategory_ids = subcategoryIds;
        }

        try {
            const resp = await fetch("{{ route('admin.design.unlock-designs') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            });

            const result = await resp.json();

            if (resp.ok && result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert('Gagal: ' + (result.message || 'Unknown error occurred'));
            }
        } catch (error) {
            console.error(error);
            alert('Terjadi kesalahan pada server. Silahkan periksa koneksi atau hubungi administrator.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Confirm Permissions';
        }
    }

    function exportSelectedDesigns() {
        const selected = Array.from(document.querySelectorAll('.design-checkbox:checked')).map(cb => cb.value);
        let url = "{{ route('admin.design.export', request()->query()) }}";
        if (selected.length > 0) url += (url.includes('?') ? '&' : '?') + 'selected_ids=' + selected.join(',');
        window.location.href = url;
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #c7d2fe;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #818cf8;
    }

    .select-multiple-custom option:checked {
        background-color: #e0e7ff !important;
        color: #4338ca !important;
    }

    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection