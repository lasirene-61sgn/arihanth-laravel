@extends('key-user.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Product Details</h1>
            <nav class="flex text-sm text-gray-500 mt-1">
                <a href="{{ route('key-user.dashboard') }}" class="hover:text-indigo-600 transition">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('key-user.product.index') }}" class="hover:text-indigo-600 transition">Products</a>
                <span class="mx-2">/</span>
                <span class="text-gray-800 font-medium">View Details</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('key-user.product.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="bi bi-arrow-left mr-2"></i> Back
            </a>
            <a href="{{ route('key-user.product.edit', $product) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition shadow-sm">
                <i class="bi bi-pencil mr-2"></i> Edit Product
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-6 lg:p-8">
            <div class="flex flex-col lg:flex-row gap-12">
                
                <div class="w-full lg:w-1/3 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        @forelse($product->images as $image)
                            <div class="group relative aspect-square overflow-hidden rounded-xl border border-gray-100 shadow-sm">
                                <img src="{{ asset('storage/' . $image->path) }}" 
                                     alt="Product Image" 
                                     class="h-full w-full object-cover transition duration-300 group-hover:scale-110 cursor-zoom-in"
                                     onclick="window.open(this.src)">
                            </div>
                        @empty
                            @if($product->product_image)
                                <div class="col-span-2 aspect-square rounded-xl border border-gray-100 overflow-hidden shadow-sm">
                                    <img src="{{ asset($product->product_image) }}" alt="Product Image" class="h-full w-full object-cover">
                                </div>
                            @else
                                <div class="col-span-2 aspect-square rounded-xl bg-gray-50 border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400">
                                    <i class="bi bi-image text-5xl mb-2"></i>
                                    <p class="text-sm">No images available</p>
                                </div>
                            @endif
                        @endforelse
                    </div>
                    
                    <div class="pt-4 border-t border-gray-50">
                        <h2 class="text-xl font-bold text-gray-900">{{ $product->product_name }}</h2>
                        <p class="text-sm font-mono text-red-600 font-bold tracking-wider mt-1">{{ $product->product_code }}</p>
                    </div>
                </div>

                <div class="flex-1">
                    <div class="bg-gray-50 rounded-2xl p-6 lg:p-8">
                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-200 pb-2">Technical Specifications</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                            <div class="space-y-1">
                                <span class="text-xs font-medium text-gray-500">Category</span>
                                <p class="text-sm font-semibold text-gray-800">{{ $product->product_category ?? 'General' }}</p>
                            </div>

                            <div class="space-y-1">
                                <span class="text-xs font-medium text-gray-500">Unit Type</span>
                                <p class="text-sm font-semibold text-gray-800">{{ $product->type }}</p>
                            </div>

                            <div class="space-y-1">
                                <span class="text-xs font-medium text-gray-500">Priority Level</span>
                                <div>
                                    @php
                                        $orderColors = [
                                            'Regular' => 'bg-blue-100 text-blue-700 border-blue-200',
                                            'Urgent' => 'bg-amber-100 text-amber-700 border-amber-200',
                                            'Super Urgent' => 'bg-red-100 text-red-700 border-red-200'
                                        ];
                                        $colorClass = $orderColors[$product->order_type] ?? 'bg-gray-100 text-gray-700';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $colorClass }}">
                                        {{ $product->order_type }}
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <span class="text-xs font-medium text-gray-500">Current Status</span>
                                <div class="flex items-center">
                                    <span class="h-2 w-2 rounded-full mr-2 {{ $product->open_close == 'Open' ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                    <p class="text-sm font-semibold text-gray-800">{{ $product->open_close }}</p>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <span class="text-xs font-medium text-gray-500">Size / Length</span>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $product->size ?? 'N/A' }} / {{ $product->length ?? 'N/A' }}
                                </p>
                            </div>

                            <div class="space-y-1">
                                <span class="text-xs font-medium text-gray-500">Weight Range</span>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $product->weight_from ?? '0' }}g - {{ $product->weight_to ?? '0' }}g
                                </p>
                            </div>

                            <div class="space-y-1">
                                <span class="text-xs font-medium text-gray-500">Hallmark / Rodium</span>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $product->hallmark ?? 'N/A' }} / {{ $product->rodium ?? 'N/A' }}
                                </p>
                            </div>

                            <div class="space-y-1">
                                <span class="text-xs font-medium text-gray-500">Hook / Enamel</span>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $product->hook ?? 'N/A' }} / {{ $product->enamel ?? 'N/A' }}
                                </p>
                            </div>

                            <div class="space-y-1">
                                <span class="text-xs font-medium text-gray-500">Stone Details</span>
                                <p class="text-sm font-semibold text-gray-800">{{ $product->stone ?? 'No stones' }}</p>
                            </div>

                            <div class="space-y-1">
                                <span class="text-xs font-medium text-gray-500">System Dates</span>
                                <div class="text-[11px] text-gray-500 leading-tight">
                                    Created: {{ $product->created_at->format('d M, Y h:i A') }}<br>
                                    Updated: {{ $product->updated_at->format('d M, Y h:i A') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection