@extends('admin.layouts.app')

@section('title', 'Catalogue')

@section('content')
<div class="p-4 sm:p-6 space-y-6">
    <!-- Header/Toolbar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Catalogue</h1>
            <p class="text-sm text-gray-500 mt-1">Browse and manage accepted product designs</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Global Search -->
            <form action="{{ route('admin.catalogue.index') }}" method="GET" class="relative group min-w-[250px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Search anything..." 
                    class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 transition-all text-sm shadow-sm">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-magenta-500 transition-colors"></i>
            </form>

            <!-- Sort Dropdown -->
            <div class="relative" x-data="{ sortOpen: false }">
                <button @click="sortOpen = !sortOpen" class="flex items-center gap-2 px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg border border-gray-200 transition-colors shadow-sm">
                    <i class="bi bi-sort-down"></i>
                    <span>Sort</span>
                </button>
                <div x-show="sortOpen" @click.away="sortOpen = false" 
                    class="absolute left-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden"
                    x-transition:enter="transition ease-out duration-200"
                    style="display: none;">
                    <div class="flex flex-col">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-magenta-50 hover:text-magenta-800 {{ request('sort') == 'latest' ? 'bg-magenta-50 text-magenta-800 font-bold' : '' }}">Latest First</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name_asc']) }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-magenta-50 hover:text-magenta-800 {{ request('sort') == 'name_asc' ? 'bg-magenta-50 text-magenta-800 font-bold' : '' }}">Product Name (A-Z)</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'design_asc']) }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-magenta-50 hover:text-magenta-800 {{ request('sort') == 'design_asc' ? 'bg-magenta-50 text-magenta-800 font-bold' : '' }}">Design Code</a>
                        <hr class="border-gray-100">
                        <a href="{{ route('admin.catalogue.index') }}" class="px-4 py-2 text-xs text-rose-600 font-bold hover:bg-rose-50">Reset Sort</a>
                    </div>
                </div>
            </div>

            <!-- Advanced Filter -->
            <div class="relative" x-data="{ filterOpen: false }">
                <button @click="filterOpen = !filterOpen" class="flex items-center gap-2 px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg border border-gray-200 transition-colors shadow-sm">
                    <i class="bi bi-funnel"></i>
                    <span>Filters</span>
                </button>

                <div x-show="filterOpen" @click.away="filterOpen = false" 
                    class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-xl border border-gray-100 z-50 p-5"
                    x-transition:enter="transition ease-out duration-200"
                    style="display: none;">
                    
                    <form action="{{ route('admin.catalogue.index') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Product Name</label>
                                <input type="text" name="product_name" value="{{ request('product_name') }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-magenta-500">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Product Code</label>
                                <input type="text" name="product_code" value="{{ request('product_code') }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-magenta-500">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Category</label>
                                <input type="text" name="category" value="{{ request('category') }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-magenta-500">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">BP Code</label>
                                <input type="text" name="bp_code" value="{{ request('bp_code') }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-magenta-500">
                            </div>
                        </div>
                        <div class="flex gap-3 pt-4 border-t border-gray-50">
                            <button type="submit" class="flex-1 px-4 py-2 bg-magenta-800 hover:bg-magenta-900 text-white text-sm font-bold rounded-lg transition-colors shadow-lg shadow-magenta-100">Apply</button>
                            <a href="{{ route('admin.catalogue.index') }}" class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-lg text-center transition-colors">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button onclick="printSelectedCatalogue()" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                    <i class="bi bi-printer"></i>
                    <span>Print</span>
                </button>
                <button onclick="exportSelectedCatalogue()" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                    <i class="bi bi-file-earmark-excel"></i>
                    <span>Excel</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
            <h5 class="text-sm font-bold text-gray-700 uppercase tracking-widest">Accepted Products Catalogue</h5>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                {{ $products->count() }} active items
            </span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider w-10 text-center">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this.checked)" 
                                class="rounded border-gray-300 text-magenta-600 focus:ring-magenta-500 h-4 w-4 transition-all">
                        </th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Preview</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Design Code</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Product Info</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Weight Range</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Created By</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-end">Task</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 bg-white">
                    @foreach($products as $product)
                    <tr class="hover:bg-magenta-50/30 transition-colors group">
                        <td class="px-6 py-4 text-center">
                            <input type="checkbox" class="product-checkbox rounded border-gray-300 text-magenta-600 focus:ring-magenta-500 h-4 w-4" value="{{ $product->id }}">
                        </td>
                        <td class="px-6 py-4">
                            <div class="w-14 h-14 shrink-0 rounded-lg border-2 border-white shadow-sm overflow-hidden group-hover:shadow-md transition-shadow">
                                @if($product->images->first())
                                    <img src="{{ asset('storage/'.$product->images->first()->path) }}" 
                                        class="w-full h-full object-cover cursor-zoom-in"
                                        onclick="window.openUniversalPreview('{{ asset('storage/'.$product->images->first()->path) }}')">
                                @else
                                    <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-300">
                                        <i class="bi bi-image text-lg"></i>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex px-2 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded-md border border-emerald-100 uppercase tracking-widest">
                                {{ $product->design_code }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-gray-400">{{ $product->product_code }}</span>
                                <span class="text-sm font-extrabold text-gray-900 line-clamp-1 uppercase tracking-tight">{{ $product->product_name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-semibold text-gray-600">{{ $product->category->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold text-gray-700 bg-gray-50 px-2 py-1 rounded border border-gray-100">
                                {{ $product->weight_from }} - {{ $product->weight_to }} <span class="text-[8px] uppercase tracking-tighter ml-0.5">gm</span>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-gray-800">{{ $product->creator_name }}</span>
                                @if($product->bp_code)
                                    <span class="text-[10px] text-indigo-500 font-extrabold tracking-tighter uppercase whitespace-nowrap">BP: {{ $product->bp_code }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-end">
                            <button type="button" 
                                onclick="loadProductDetails({{ $product->id }})"
                                class="inline-flex items-center gap-2 px-3 py-1.5 bg-magenta-50 text-magenta-800 hover:bg-magenta-800 hover:text-white rounded-lg text-xs font-bold transition-all border border-magenta-100 shadow-sm">
                                <i class="bi bi-eye text-sm"></i>
                                <span>Full Specs</span>
                            </button>
                        </td>
                    @endforeach
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

<!-- Product Details Modal -->
<div id="productDetailsModal" class="fixed inset-0 bg-black/60 z-[70] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl overflow-hidden transform transition-all flex flex-col max-h-[90vh]">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-900 text-white">
            <h5 class="text-lg font-bold flex items-center gap-3">
                <i class="bi bi-grid-3x3-gap-fill text-magenta-400"></i>
                <span>Product Master Information</span>
            </h5>
            <button type="button" class="text-gray-400 hover:text-white transition-colors" onclick="closeCatalogueModal()"><i class="bi bi-x-lg text-xl"></i></button>
        </div>
        
        <div class="p-6 overflow-y-auto custom-scrollbar flex-1" id="productDetailsContent">
            <!-- Content injected via AJAX --> fly-in from bottom
        </div>
        
        <div class="p-5 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button type="button" class="px-6 py-2.5 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-black transition-colors shadow-lg" onclick="closeCatalogueModal()">Close Viewer</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function toggleSelectAll(checked) {
        document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = checked);
    }

    function closeCatalogueModal() {
        document.getElementById('productDetailsModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function printSelectedCatalogue() {
        const selected = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one product to print.');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('admin.catalogue.print-selected') }}";
        form.target = '_blank';

        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_products[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    function exportSelectedCatalogue() {
        const selected = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(cb => cb.value);
        let url = "{{ route('admin.catalogue.export', request()->query()) }}";
        
        if (selected.length > 0) {
            url += (url.includes('?') ? '&' : '?') + 'selected_ids=' + selected.join(',');
        }
        
        window.location.href = url;
    }

    function loadProductDetails(productId) {
        const modal = document.getElementById('productDetailsModal');
        const contentDiv = document.getElementById('productDetailsContent');
        
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        
        contentDiv.innerHTML = `
            <div class="flex flex-col items-center justify-center py-20 space-y-4">
                <div class="w-12 h-12 border-4 border-magenta-200 border-t-magenta-800 rounded-full animate-spin"></div>
                <p class="text-sm font-bold text-gray-500 uppercase tracking-widest">Gathering full specifications...</p>
            </div>
        `;

        fetch(`/admin/product/${productId}/json`)
            .then(response => response.json())
            .then(product => {
                let html = '<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">';

                // Images Section
                html += '<div class="lg:col-span-12 xl:col-span-5">';
                if (product.images && product.images.length > 0) {
                    html += `
                        <div class="relative rounded-3xl overflow-hidden border border-gray-100 shadow-xl bg-gray-50 aspect-square group">
                            <div class="flex h-full" id="modalCarouselTrack">
                                ${product.images.map((img, idx) => `
                                    <div class="w-full h-full flex-shrink-0 flex items-center justify-center bg-white p-4">
                                        <img src="/storage/${img.path}" class="max-w-full max-h-full object-contain">
                                    </div>
                                `).join('')}
                            </div>
                            
                            ${product.images.length > 1 ? `
                                <div class="absolute inset-0 flex items-center justify-between p-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button onclick="scrollCarousel(-1)" class="w-10 h-10 bg-white/90 backdrop-blur rounded-full shadow-lg flex items-center justify-center hover:bg-magenta-800 hover:text-white transition-all">
                                        <i class="bi bi-chevron-left text-lg"></i>
                                    </button>
                                    <button onclick="scrollCarousel(1)" class="w-10 h-10 bg-white/90 backdrop-blur rounded-full shadow-lg flex items-center justify-center hover:bg-magenta-800 hover:text-white transition-all">
                                        <i class="bi bi-chevron-right text-lg"></i>
                                    </button>
                                </div>
                                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                                    ${product.images.map((_, idx) => `
                                        <div class="w-1.5 h-1.5 rounded-full bg-gray-300 transition-all carousel-dot" data-idx="${idx}"></div>
                                    `).join('')}
                                </div>
                            ` : ''}
                        </div>
                    `;
                } else {
                    html += '<div class="h-80 bg-gray-100 rounded-3xl flex flex-col items-center justify-center text-gray-300"><i class="bi bi-image text-4xl mb-2"></i><p class="text-xs font-bold uppercase">No images available</p></div>';
                }
                html += '</div>';

                // Data Section
                html += '<div class="lg:col-span-12 xl:col-span-7 space-y-6">';
                
                // Identity
                html += `
                    <div class="p-6 bg-emerald-50/50 rounded-2xl border border-emerald-100/50">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="p-2 bg-emerald-600 text-white rounded-lg shadow-md shadow-emerald-200"><i class="bi bi-fingerprint"></i></span>
                            <h6 class="text-sm font-extrabold text-emerald-900 uppercase tracking-widest">Identification</h6>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6">
                            <div class="flex flex-col"><span class="text-[10px] font-bold text-emerald-600 uppercase tracking-tighter">Design Code</span><span class="text-lg font-black text-emerald-900">${product.design_code}</span></div>
                            <div class="flex flex-col"><span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Product Code</span><span class="text-sm font-bold text-gray-700">${product.product_code}</span></div>
                            <div class="flex flex-col col-span-2"><span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Full Product Name</span><span class="text-sm font-extrabold text-gray-900 uppercase">${product.product_name}</span></div>
                        </div>
                    </div>
                `;

                // Technical
                html += `
                    <div class="p-6 bg-indigo-50/50 rounded-2xl border border-indigo-100/50">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="p-2 bg-indigo-600 text-white rounded-lg shadow-md shadow-indigo-200"><i class="bi bi-gear-fill"></i></span>
                            <h6 class="text-sm font-extrabold text-indigo-900 uppercase tracking-widest">Technical Specifications</h6>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-4 gap-x-6">
                            <div class="flex flex-col"><span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Weight Range</span><span class="text-sm font-bold text-indigo-900 bg-white px-2 py-1 rounded w-fit border border-indigo-50">${product.weight_from}g - ${product.weight_to}g</span></div>
                            <div class="flex flex-col"><span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Size / Length</span><span class="text-sm font-bold text-gray-900">${product.size || '-'} / ${product.length || '-'}</span></div>
                            <div class="flex flex-col"><span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Category / Sub</span><span class="text-xs font-bold text-gray-700">${product.category?.name || 'N/A'}<br><span class="text-[9px] text-gray-400 font-medium">${product.subcategory?.name || ''}</span></span></div>
                        </div>
                    </div>
                `;

                // Attributes
                html += `
                    <div class="p-6 bg-amber-50/50 rounded-2xl border border-amber-100/50">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="p-2 bg-amber-600 text-white rounded-lg shadow-md shadow-amber-200"><i class="bi bi-stars"></i></span>
                            <h6 class="text-sm font-extrabold text-amber-900 uppercase tracking-widest">Additional Attributes</h6>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div class="flex flex-col"><span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Hallmark</span><span class="inline-flex px-2 py-1 bg-white border border-amber-200 rounded text-[10px] font-black text-amber-800 uppercase tracking-widest">${product.hallmark || 'NONE'}</span></div>
                            <div class="flex flex-col"><span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Rodium</span><span class="text-xs font-bold text-gray-800">${product.rodium || '-'}</span></div>
                            <div class="flex flex-col"><span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Hook / Stone</span><span class="text-xs font-bold text-gray-800">${product.hook || '-'}<br>${product.stone || '-'}</span></div>
                            <div class="flex flex-col"><span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Type</span><span class="text-xs font-black text-magenta-800 uppercase tracking-widest">${product.type}</span></div>
                        </div>
                    </div>
                `;

                html += '</div></div>';
                contentDiv.innerHTML = html;
                
                // Init mini carousel logic
                let currentIdx = 0;
                window.scrollCarousel = (dir) => {
                    const track = document.getElementById('modalCarouselTrack');
                    const total = product.images.length;
                    currentIdx = (currentIdx + dir + total) % total;
                    track.style.transform = `translateX(-${currentIdx * 100}%)`;
                    track.style.transition = 'transform 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                    document.querySelectorAll('.carousel-dot').forEach((dot, i) => {
                        dot.className = i === currentIdx ? 'w-4 h-1.5 rounded-full bg-magenta-800 transition-all' : 'w-1.5 h-1.5 rounded-full bg-gray-300 transition-all';
                    });
                };
                if(product.images.length > 1) scrollCarousel(0); // Trigger first dot
            })
            .catch(err => {
                contentDiv.innerHTML = '<div class="p-10 text-center"><p class="text-rose-600 font-bold">Sorry dear, could not load all specifications at this time.</p></div>';
            });
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    
    @media print {
        .no-print { display: none !important; }
        .print-only { display: block !important; }
        body { background: white !important; }
    }
</style>
@endsection