@extends('buyer.layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Product Details</h1>
            <p class="text-sm text-slate-500">View complete information for product: <span class="font-bold text-slate-700">{{ $product->product_code }}</span></p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('buyer.product.index') }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-50 transition-all shadow-sm">
                <i class="bi bi-arrow-left mr-2"></i> Back to List
            </a>
            <a href="{{ route('buyer.product.edit', $product) }}" class="inline-flex items-center px-4 py-2.5 bg-amber-50 border border-amber-200 text-amber-600 text-sm font-bold rounded-xl hover:bg-amber-100 transition-all shadow-sm">
                <i class="bi bi-pencil mr-2"></i> Edit
            </a>
            <form action="{{ route('buyer.product.destroy', $product) }}" method="POST" class="m-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-red-50 border border-red-200 text-red-600 text-sm font-bold rounded-xl hover:bg-red-100 transition-all shadow-sm"
                        onclick="return confirm('Are you sure you want to delete this product?')">
                    <i class="bi bi-trash mr-2"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center">
                    <i class="bi bi-info-circle text-blue-600 mr-2 text-lg"></i>
                    <h4 class="text-lg font-bold text-slate-800">General Information</h4>
                </div>
                <div class="p-6 md:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Product Name</label>
                                <p class="text-lg font-bold text-slate-800">{{ $product->product_name }}</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Product Code</label>
                                <p class="text-sm font-mono text-blue-600 bg-blue-50 px-2 py-1 rounded inline-block mt-1">{{ $product->product_code }}</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Category</label>
                                <p class="text-sm font-medium text-slate-700 mt-1">{{ $product->category->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Subcategory</label>
                                <p class="text-sm font-medium text-slate-700 mt-1">{{ $product->subcategory->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="space-y-4 border-l border-slate-100 pl-0 md:pl-8">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Product Type</label>
                                <p class="text-sm font-medium text-slate-700 mt-1">
                                    <span class="bg-slate-100 px-3 py-1 rounded-full">{{ $product->type }}</span>
                                </p>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Created By</label>
                                <p class="text-sm font-bold text-slate-800 mt-1">{{ $product->creator->business_name ?? $product->creator->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">BP Code</label>
                                <p class="text-sm font-medium text-slate-600 mt-1">{{ $product->bp_code }}</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Date Created</label>
                                <p class="text-sm font-medium text-slate-600 mt-1"><i class="bi bi-calendar3 mr-2 text-slate-400"></i>{{ $product->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    @if($product->description)
                    <div class="mt-8 pt-6 border-t border-slate-100">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Description</label>
                        <p class="text-sm text-slate-600 mt-2 leading-relaxed bg-slate-50 p-4 rounded-xl">
                            {{ $product->description }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                    <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Product Gallery</h4>
                    <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $product->images->count() }} Files</span>
                </div>
                <div class="p-4">
                    @if($product->images->count() > 0)
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($product->images as $image)
                                <div class="relative group aspect-square rounded-xl overflow-hidden border border-slate-100">
                                    <img src="{{ asset('storage/' . $image->path) }}" 
                                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" 
                                         alt="Product Image">
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ asset('storage/' . $image->path) }}" target="_blank" class="text-white text-lg">
                                            <i class="bi bi-zoom-in"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-12 flex flex-col items-center justify-center text-slate-400 italic">
                            <i class="bi bi-image text-4xl mb-2 opacity-20"></i>
                            <p class="text-xs">No images uploaded</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="bg-blue-600 rounded-2xl p-6 text-white shadow-lg shadow-blue-200">
                <h5 class="font-bold mb-1">System Status</h5>
                <p class="text-blue-100 text-xs">This product is visible in the active catalog.</p>
                <div class="mt-4 pt-4 border-t border-blue-500/50">
                    <p class="text-[10px] uppercase font-bold text-blue-200 tracking-widest">Last Update</p>
                    <p class="text-xs font-medium">{{ $product->updated_at->diffForHumans() }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection