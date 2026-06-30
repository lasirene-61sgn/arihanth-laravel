@extends('buyer.layouts.app')

@section('title', 'Catalogue Details')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Design Details</h1>
            <p class="text-sm text-slate-500 flex items-center">
                <span class="bg-blue-50 text-blue-600 font-mono px-2 py-0.5 rounded mr-2 border border-blue-100">{{ $product->design_code }}</span>
                Full technical overview of this catalogue item.
            </p>
        </div>
        <a href="{{ route('buyer.catalogue.index') }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-50 transition-all shadow-sm">
            <i class="bi bi-arrow-left mr-2"></i> Back to Catalogue
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-3">
                @if($product->images->count() > 0)
                    @php 
                        $firstImg = $product->images->first(); 
                        $mainPath = asset('storage/' . $firstImg->path);
                    @endphp
                    
                    <div class="bg-slate-50 rounded-xl overflow-hidden aspect-square flex items-center justify-center border border-slate-100 mb-4">
                        <img src="{{ $mainPath }}" 
                             id="main" 
                             class="max-w-full max-h-full object-contain mix-blend-multiply transition-all duration-300" 
                             alt="{{ $product->product_name }}">
                    </div>
                    
                    <div class="flex flex-wrap gap-2 justify-center pb-2">
                        @foreach($product->images as $img)
                            @php $thumbPath = asset('storage/' . $img->path); @endphp
                            <button onclick="document.getElementById('main').src=this.src" 
                                    class="w-16 h-16 rounded-lg border-2 border-slate-100 overflow-hidden hover:border-blue-500 transition-all focus:border-blue-500 focus:outline-none bg-white">
                                <img src="{{ $thumbPath }}" class="w-full h-full object-cover" alt="Thumbnail">
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="bg-slate-50 rounded-xl aspect-square flex flex-col items-center justify-center text-slate-300">
                        <i class="bi bi-image text-6xl mb-2 opacity-20"></i>
                        <p class="text-xs font-bold tracking-widest uppercase">No Media Found</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden h-full">
                <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center">
                    <i class="bi bi-gear-wide-connected text-blue-600 mr-2 text-lg"></i>
                    <h4 class="text-lg font-bold text-slate-800 uppercase tracking-tight">Technical Specifications</h4>
                </div>
                
                <div class="p-6 md:p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Design Name</label>
                            <h2 class="text-2xl font-extrabold text-slate-900 leading-tight">{{ $product->product_name }}</h2>
                        </div>
                        <div class="space-y-1 md:text-right">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Category</label>
                            <p class="text-lg font-bold text-slate-700">{{ $product->category->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="text-center md:text-left md:pl-4">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Item Type</label>
                            <span class="inline-flex items-center px-3 py-1 bg-cyan-100 text-cyan-700 text-xs font-bold rounded-lg uppercase">
                                {{ $product->type }}
                            </span>
                        </div>
                        <div class="text-center md:text-right md:pr-4 border-l border-slate-200">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Approx Weight</label>
                            <span class="text-lg font-bold text-slate-800">{{ $product->weight_from }} - {{ $product->weight_to }} <small class="text-slate-400">gm</small></span>
                        </div>
                    </div>

                    <div>
                        <h6 class="text-[10px] font-bold text-blue-600 uppercase tracking-[0.2em] border-b border-blue-50 pb-3 mb-6">Physical Attributes</h6>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-8 gap-x-4">
                            @php 
                                $fields = [
                                    'Size' => 'size',
                                    'Length' => 'length',
                                    'Hallmark' => 'hallmark',
                                    'Rodium' => 'rodium',
                                    'Hook' => 'hook',
                                    'Stone' => 'stone',
                                    'Enamel' => 'enamel'
                                ]; 
                            @endphp
                            @foreach($fields as $label => $key)
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">{{ $label }}</label>
                                    <p class="text-sm font-semibold {{ $product->$key ? 'text-slate-800' : 'text-slate-300' }}">
                                        {{ $product->$key ?: '—' }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection