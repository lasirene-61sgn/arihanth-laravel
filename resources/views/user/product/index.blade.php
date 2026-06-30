@extends('user.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <h4 class="text-2xl font-bold text-slate-800">Product Management</h4>
            <p class="text-sm text-slate-500">Manage and track your product inventory and specifications.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('user.product.export', request()->query()) }}" 
               class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition shadow-sm">
                <i class="bi bi-file-earmark-excel me-2"></i> Export
            </a>

            <div class="dropdown">
                <button class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-50 transition shadow-sm dropdown-toggle" 
                        type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="bi bi-funnel me-2"></i> Advanced Filter
                </button>
                <div class="dropdown-menu p-6 shadow-2xl border-0 rounded-xl" style="min-width: 450px;">
                    <form action="{{ route('user.product.index') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1 tracking-wider">Product Name</label>
                                <input type="text" name="filter_product_name" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" 
                                       placeholder="e.g. Gold Ring" value="{{ request('filter_product_name') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1 tracking-wider">Product Code</label>
                                <input type="text" name="filter_product_code" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" 
                                       placeholder="e.g. PRD-001" value="{{ request('filter_product_code') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1 tracking-wider">Category</label>
                                <input type="text" name="filter_category" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" value="{{ request('filter_category') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1 tracking-wider">Subcategory</label>
                                <input type="text" name="filter_subcategory" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" value="{{ request('filter_subcategory') }}">
                            </div>
                        </div>
                        <div class="flex gap-2 pt-4 border-t border-slate-50">
                            <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg text-sm font-bold hover:bg-indigo-700 transition shadow-sm">
                                <i class="bi bi-filter me-1"></i> Apply Filters
                            </button>
                            <a href="{{ route('user.product.index') }}" class="flex-1 text-center border border-slate-200 py-2 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition">
                                <i class="bi bi-arrow-clockwise me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-200 transition shadow-sm border border-slate-200">
                <i class="bi bi-printer me-2"></i> Print
            </button>
            <a href="{{ route('user.product.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 transition shadow-md">
                <i class="bi bi-plus-circle me-2"></i> Add New Product
            </a>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
        <form action="{{ route('user.product.index') }}" method="GET" class="relative w-full md:w-96">
            <input type="text" name="search" placeholder="Search product name or code..." 
                   class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none shadow-sm transition-all"
                   value="{{ request('search') }}">
            <div class="absolute left-3 top-2.5 text-slate-400">
                <i class="bi bi-search"></i>
            </div>
        </form>

        <div class="dropdown">
            <button class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-sort-down me-2"></i> Sort Options
            </button>
            <ul class="dropdown-menu shadow-2xl border-0 rounded-xl overflow-hidden mt-2">
                <li><a class="dropdown-item py-2 hover:bg-indigo-50 transition-colors" href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}">Newest First</a></li>
                <li><a class="dropdown-item py-2 hover:bg-indigo-50 transition-colors" href="{{ request()->fullUrlWithQuery(['sort' => 'name_asc']) }}">Name (A-Z)</a></li>
                <li><a class="dropdown-item py-2 hover:bg-indigo-50 transition-colors" href="{{ request()->fullUrlWithQuery(['sort' => 'name_desc']) }}">Name (Z-A)</a></li>
            </ul>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] font-black tracking-widest border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Image</th>
                        <th class="px-6 py-4">Code</th>
                        <th class="px-6 py-4">Product Name</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4 text-center">Order Type</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4">Created By</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4">
                            <div class="relative w-12 h-12">
                                @if($product->images->count() > 0)
                                    <img src="{{ asset('storage/' . $product->images->first()->path) }}" 
                                         alt="Product" 
                                         class="w-full h-full object-cover rounded-lg border border-slate-200 cursor-pointer shadow-sm hover:ring-2 hover:ring-indigo-300 transition"
                                         onclick="window.location.href='{{ route('user.product.show', $product) }}'">
                                    @if($product->images->count() > 1)
                                        <span class="absolute -bottom-1 -right-1 bg-slate-900 text-white text-[9px] font-black px-1.5 py-0.5 rounded-full ring-2 ring-white">
                                            +{{ $product->images->count() - 1 }}
                                        </span>
                                    @endif
                                @else
                                    <div class="w-full h-full bg-slate-50 rounded-lg flex items-center justify-center text-slate-300 border border-slate-200">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 font-black text-indigo-600 tracking-tight">{{ $product->product_code }}</td>
                        <td class="px-6 py-4 font-semibold text-slate-800">{{ $product->product_name }}</td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-700 text-xs">{{ $product->category->name ?? 'N/A' }}</div>
                            <div class="text-[10px] text-slate-400 font-medium tracking-tight">{{ $product->subcategory->name ?? '---' }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($product->order_type == 'Urgent')
                                <span class="px-2 py-1 bg-amber-100 text-amber-700 text-[10px] font-black rounded uppercase border border-amber-200">Urgent</span>
                            @else
                                <span class="px-2 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black rounded uppercase border border-indigo-100">{{ $product->order_type }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 {{ $product->open_close == 'Open' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200' }} text-[10px] font-black rounded uppercase border">
                                {{ $product->open_close }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-[11px] font-black text-slate-800">{{ $product->creator->full_name ?? 'N/A' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $product->bp_code }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('user.product.show', $product) }}" 
                                   class="p-2 rounded-lg bg-slate-50 text-slate-600 hover:bg-indigo-600 hover:text-white transition shadow-sm border border-slate-100" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('user.product.edit', $product) }}" 
                                   class="p-2 rounded-lg bg-slate-50 text-slate-600 hover:bg-amber-500 hover:text-white transition shadow-sm border border-slate-100" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('user.product.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Delete this product?');">
                                    @csrf @method('DELETE')
                                    <button class="p-2 rounded-lg bg-slate-50 text-slate-600 hover:bg-red-600 hover:text-white transition shadow-sm border border-slate-100" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-20">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-200 mb-4">
                                    <i class="bi bi-search text-3xl"></i>
                                </div>
                                <h5 class="text-slate-800 font-bold">No products found</h5>
                                <p class="text-slate-500 text-sm">Try adjusting your filters or search keywords.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection