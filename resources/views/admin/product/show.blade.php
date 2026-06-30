@extends('admin.layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 border-b pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Product Details</h1>
            <p class="text-sm text-gray-500">Full information for {{ $product->product_name }}</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-2">
            <a href="{{ route('admin.product.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">Back</a>
            <a href="{{ route('admin.product.edit', $product) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Edit Product</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <div class="lg:col-span-5">
                    @if($product->images->count() > 0)
                        <div id="productImageCarousel" class="relative overflow-hidden rounded-lg bg-gray-100 group">
                            <div class="carousel-inner">
                                @foreach($product->images as $index => $image)
                                    <div class="carousel-item {{ $index == 0 ? 'block' : 'hidden' }} transition-opacity duration-500">
                                        <img src="{{ asset('storage/' . $image->path) }}" alt="Product Image" class="w-full h-96 object-contain">
                                    </div>
                                @endforeach
                            </div>
                            
                            @if($product->images->count() > 1)
                                <button onclick="prevSlide()" class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 p-2 rounded-full shadow hover:bg-white transition opacity-0 group-hover:opacity-100">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                </button>
                                <button onclick="nextSlide()" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 p-2 rounded-full shadow hover:bg-white transition opacity-0 group-hover:opacity-100">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="w-full h-64 bg-gray-50 flex flex-col items-center justify-center border-2 border-dashed border-gray-200 rounded-lg">
                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <p class="text-gray-400 mt-2">No Images Available</p>
                        </div>
                    @endif
                </div>

                <div class="lg:col-span-7">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">{{ $product->product_name }}</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6 border-t pt-4">
                        
                        {{-- Product Code --}}
                        @if($product->product_code)
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Product Code</label>
                            <p class="mt-1 text-gray-900 font-medium">{{ $product->product_code }}</p>
                        </div>
                        @endif

                        {{-- Category --}}
                        @if($product->category)
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Category</label>
                            <p class="mt-1 text-gray-900">{{ $product->category->name }}</p>
                        </div>
                        @endif

                        {{-- Sub Category --}}
                        @if($product->subcategory)
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Sub Category</label>
                            <p class="mt-1 text-gray-900">{{ $product->subcategory->name }}</p>
                        </div>
                        @endif

                        {{-- Type --}}
                        @if($product->type)
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Type</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $product->type }}
                            </span>
                        </div>
                        @endif

                        {{-- BP Code --}}
                        @if($product->bp_code)
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">BP Code</label>
                            <p class="mt-1 text-gray-900">{{ $product->bp_code }}</p>
                        </div>
                        @endif

                        {{-- Size --}}
                        @if($product->size)
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Size</label>
                            <p class="mt-1 text-gray-900">{{ $product->size }}</p>
                        </div>
                        @endif

                        {{-- Length --}}
                        @if($product->length)
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Length</label>
                            <p class="mt-1 text-gray-900">{{ $product->length }}</p>
                        </div>
                        @endif

                        {{-- Weight Range --}}
                        @if($product->weight_from || $product->weight_to)
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Weight Range</label>
                            <p class="mt-1 text-gray-900">{{ $product->weight_from ?? '0' }} - {{ $product->weight_to ?? 'N/A' }}</p>
                        </div>
                        @endif

                        {{-- Hallmark --}}
                        @if($product->hallmark)
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Hallmark</label>
                            <p class="mt-1 text-gray-900">{{ $product->hallmark }}</p>
                        </div>
                        @endif

                        {{-- Stone --}}
                        @if($product->stone)
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Stone Details</label>
                            <p class="mt-1 text-gray-900">{{ $product->stone }}</p>
                        </div>
                        @endif

                        {{-- Dates --}}
                        <div class="md:col-span-2 border-t pt-4 mt-2 grid grid-cols-2">
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Added On</label>
                                <p class="mt-1 text-xs text-gray-600">{{ $product->created_at->format('d M, Y') }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Last Update</label>
                                <p class="mt-1 text-xs text-gray-600">{{ $product->updated_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex justify-between items-center border-t pt-6">
                <form action="{{ route('admin.product.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this product permanently?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm transition">
                        Delete Product
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.carousel-item');

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.toggle('hidden', i !== index);
            slide.classList.toggle('block', i === index);
        });
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }

    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(currentSlide);
    }
</script>
@endsection