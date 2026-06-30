@extends('super-admin.layouts.app')

@section('title', __('messages.master_catalogue'))

@section('content')
<script src="https://cdn.tailwindcss.com"></script>

<div class="bg-slate-50 min-h-screen p-6 font-sans">
    
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ __('messages.master_catalogue') }}</h1>
                <p class="text-slate-500 text-sm">{{ __('messages.approved_designs') }}</p>
            </div>
            
            <div class="flex items-center gap-2">
                <button onclick="exportSelectedCatalogue()" 
                   class="flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition font-bold text-sm">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> {{ __('messages.export_catalogue') }}
                </button>
                <button onclick="printSelectedCatalogue()" class="flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition font-bold text-sm shadow-sm">
                    <i class="bi bi-check-all me-2"></i> {{ __('messages.print') }}
                </button>
            </div>
        </div>

        <div class="mt-6 border-t border-slate-100 pt-5">
            <form action="{{ route('super-admin.catalogue.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
                <!-- Search -->
                <div class="relative">
                    <i class="bi bi-search absolute left-3 top-2.5 text-slate-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="{{ __('Search...') }}" 
                           class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                </div>

                <!-- Product Name -->
                <div>
                    <input type="text" name="product_name" value="{{ request('product_name') }}" 
                           placeholder="Product Name..." 
                           class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                </div>

                <!-- Product Code -->
                <div>
                    <input type="text" name="product_code" value="{{ request('product_code') }}" 
                           placeholder="Product Code..." 
                           class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                </div>

                <!-- BP Code -->
                <select name="bp_code" onchange="this.form.submit()" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">{{ __('BP Code: All') }}</option>
                    @foreach($bpCodes as $code)
                        <option value="{{ $code }}" {{ request('bp_code') == $code ? 'selected' : '' }}>{{ $code }}</option>
                    @endforeach
                </select>
                
                <!-- Category -->
                <select name="category_id" onchange="this.form.submit()" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">{{ __('Category: All') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm font-bold hover:bg-indigo-700 transition">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'category_id', 'bp_code', 'product_name', 'product_code']))
                        <a href="{{ route('super-admin.catalogue.index') }}" class="px-3 py-2 text-sm text-rose-500 hover:bg-rose-50 border border-rose-100 rounded-lg flex items-center transition" title="Reset All">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="catalogueTable">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" class="form-check-input rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" id="selectAll" onchange="toggleSelectAll(this.checked)">
                        </th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">{{ __('messages.image') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">{{ __('messages.design_bp_code') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">{{ __('messages.product_name') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">{{ __('messages.category') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">{{ __('messages.weight_range') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-indigo-50/30 transition group">
                        <td class="px-6 py-4">
                            <input type="checkbox" class="form-check-input product-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" value="{{ $product->id }}">
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $imagesCount = $product->images->count();
                                $firstImage = $imagesCount > 0 ? $product->images->first()->path : null;
                                if (!$firstImage && $product->product_image) {
                                    $imgs = explode(',', $product->product_image);
                                    $firstImage = trim($imgs[0]);
                                }
                                $imgUrl = $firstImage ? (str_starts_with($firstImage, 'http') ? $firstImage : asset('storage/' . $firstImage)) : null;
                            @endphp

                            <div class="relative w-12 h-12">
                                @if($imgUrl)
                                    <img src="{{ $imgUrl }}" class="w-full h-full object-cover rounded border border-slate-200 cursor-pointer hover:scale-110 transition-transform" onclick="window.openUniversalPreview('{{ $imgUrl }}', 'image')">
                                    @if($imagesCount > 1)
                                        <span class="absolute -bottom-1 -right-1 bg-slate-800 text-white text-[8px] px-1 rounded-full font-bold">+{{ $imagesCount - 1 }}</span>
                                    @endif
                                @else
                                    <div class="w-full h-full bg-slate-50 rounded border border-slate-200 flex items-center justify-center">
                                        <i class="bi bi-image text-slate-300"></i>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-emerald-600 font-black tracking-wider text-sm">{{ $product->design_code }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">BP: {{ $product->bp_code ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-slate-800 font-bold text-sm">{{ $product->product_name }}</div>
                            <div class="text-[11px] text-indigo-500">{{ $product->product_code }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded text-[10px] font-bold uppercase">{{ $product->category->name ?? 'N/A' }}</span>
                            <div class="text-[10px] text-slate-400 mt-1 ml-1">{{ $product->subcategory->name ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($product->weight_from)
                                <span class="font-mono text-sm text-slate-700">{{ number_format($product->weight_from, 2) }} - {{ number_format($product->weight_to, 2) }}g</span>
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button type="button" 
                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-md hover:bg-indigo-600 hover:text-white transition font-bold text-xs"
                                    data-bs-toggle="modal" data-bs-target="#productDetailsModal" 
                                    onclick="loadProductDetails({{ $product->id }})">
                                <i class="bi bi-eye me-1"></i> VIEW
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-20 text-center text-slate-400">
                            <i class="bi bi-folder2-open text-5xl block mb-3 opacity-20"></i>
                            <p>{{ __('messages.no_products_in_catalogue') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="bg-slate-50 p-4 border-t border-slate-200">
            {{ $products->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="productDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header bg-slate-900 text-white border-0 py-4 px-6">
                <div>
                    <h5 class="modal-title font-extrabold tracking-tight text-xl uppercase" id="productDetailsModalLabel">
                        {{ __('messages.product_master_file') }}
                    </h5>
                    <p class="text-slate-400 text-xs mt-1">Detailed Product Specification & Assets</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-white" id="productDetailsContent">
                </div>
            <div class="modal-footer bg-slate-50 border-t border-slate-100 py-3">
                <button type="button" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg font-bold text-sm" data-bs-dismiss="modal">Close Window</button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSelectAll(checked) {
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = checked);
}

function printSelectedCatalogue() {
    const selected = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) {
        alert('Please select at least one product to print.');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = "{{ route('super-admin.catalogue.print-selected') }}";
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
    let url = "{{ route('super-admin.catalogue.index', array_merge(request()->all(), ['export' => 'excel'])) }}";
    if (selected.length > 0) {
        url += (url.includes('?') ? '&' : '?') + 'selected_ids=' + selected.join(',');
    }
    window.location.href = url;
}

function loadProductDetails(productId) {
    // Get products from PHP to JS
    const products = {!! json_encode($products->items()) !!};
    const product = products.find(p => p.id == productId);
    
    if (product) {
        // Build Images Gallery
        let imageHtml = '';
        if (product.images && product.images.length > 0) {
            product.images.forEach(img => {
                const url = img.path.startsWith('http') ? img.path : "{{ asset('storage') }}/" + img.path;
                imageHtml += `
                    <div class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white p-1">
                        <img src="${url}" class="w-full h-48 object-cover rounded-lg">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                             <button onclick="window.open('${url}', '_blank')" class="bg-white text-black p-2 rounded-full shadow-lg text-xs font-bold">Zoom</button>
                        </div>
                    </div>`;
            });
        } else {
            imageHtml = `<div class="col-span-2 py-12 text-center bg-slate-50 rounded-xl text-slate-400 border-2 border-dashed border-slate-200">No images found for this product</div>`;
        }

        // Build Full Details HTML
        let html = `
            <div class="grid grid-cols-1 lg:grid-cols-12">
                <div class="lg:col-span-5 bg-slate-50 p-8 border-r border-slate-100 max-h-[70vh] overflow-y-auto">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Product Assets</h3>
                    <div class="grid grid-cols-2 gap-4">
                        ${imageHtml}
                    </div>
                </div>

                <div class="lg:col-span-7 p-8">
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-2">
                             <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-black rounded-full uppercase tracking-tighter">${product.category ? product.category.name : 'Uncategorized'}</span>
                             <span class="text-slate-300">|</span>
                             <span class="text-slate-500 text-xs font-medium">${product.subcategory ? product.subcategory.name : ''}</span>
                        </div>
                        <h2 class="text-3xl font-black text-slate-900 leading-tight">${product.product_name}</h2>
                        <p class="text-slate-400 font-mono text-sm mt-1">Internal Code: ${product.product_code}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-100">
                            <p class="text-emerald-500 text-[10px] font-bold uppercase mb-1">Design Code</p>
                            <p class="text-emerald-900 font-black text-lg tracking-wider">${product.design_code}</p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                            <p class="text-slate-500 text-[10px] font-bold uppercase mb-1">BP Code</p>
                            <p class="text-slate-900 font-bold text-lg">${product.bp_code || 'N/A'} - ${product.customer_name || 'N/A'}</p>
                        </div>

                        <div class="col-span-2 grid grid-cols-2 gap-4 bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                             <div>
                                <p class="text-slate-400 text-xs font-bold mb-1">MIN WEIGHT</p>
                                <p class="text-2xl font-black text-slate-800">${parseFloat(product.weight_from).toFixed(2)} <span class="text-sm font-normal text-slate-400">g</span></p>
                             </div>
                             <div class="border-l border-slate-100 pl-4">
                                <p class="text-slate-400 text-xs font-bold mb-1">MAX WEIGHT</p>
                                <p class="text-2xl font-black text-slate-800">${parseFloat(product.weight_to).toFixed(2)} <span class="text-sm font-normal text-slate-400">g</span></p>
                             </div>
                        </div>

                        <div class="col-span-2 space-y-3 pt-4 border-t border-slate-100">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Unit Type:</span>
                                <span class="text-slate-800 font-bold">${product.unit_type || 'PCS'}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Created At:</span>
                                <span class="text-slate-800 font-medium">${new Date(product.created_at).toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'})}</span>
                            </div>
                             <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Last Updated:</span>
                                <span class="text-slate-800 font-medium">${new Date(product.updated_at).toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'})}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('productDetailsContent').innerHTML = html;
    }
}
</script>

<style>
    @media print {
        header, .shadow-sm, button, .pagination, form { display: none !important; }
        .bg-slate-50 { background: white !important; }
        table { border: 1px solid #000 !important; width: 100%; }
        th, td { border: 1px solid #ddd !important; padding: 10px !important; }
    }
    /* Simple Scrollbar for Modal Sidebar */
    #productDetailsContent div::-webkit-scrollbar { width: 5px; }
    #productDetailsContent div::-webkit-scrollbar-track { background: transparent; }
    #productDetailsContent div::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>
@endsection