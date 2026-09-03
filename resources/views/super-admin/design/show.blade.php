@extends('super-admin.layouts.app')

@section('title', 'Design Details | ' . ($product->design_code ?? $product->product_code))

@section('content')
<script src="https://cdn.tailwindcss.com"></script>

<div class="bg-slate-50 min-h-screen p-4 font-sans text-slate-900">
    <!-- Header -->

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('super-admin.design.index') }}" class="text-slate-400 hover:text-slate-600 text-sm mb-2 inline-block transition">
                    <i class="bi bi-arrow-left me-1"></i> Back to Designs
                </a>
                <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                    {{ $product->product_name }}
                    <span class="ms-3 text-lg font-mono text-indigo-600 bg-indigo-50 px-3 py-1 rounded-md border border-indigo-100">
                        {{ $product->design_status === 'Accepted' ? ($product->design_code ?? $product->product_code) : $product->product_code }}
                    </span>
                </h1>
            </div>
            <div class="flex gap-2">
                @if($product->design_status != 'Accepted')
                <form action="{{ route('super-admin.design.accept', $product) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Accept this design?')" class="px-4 py-2 bg-emerald-600 text-white rounded-lg font-bold hover:bg-emerald-700 shadow-sm transition flex items-center">
                        <i class="bi bi-check-lg me-2"></i> Accept Design
                    </button>
                </form>
                @endif

                @if($product->design_status == 'Pending' || empty($product->design_status))
                <form action="{{ route('super-admin.design.reject', $product) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Reject this design?')" class="px-4 py-2 bg-white text-red-600 border border-red-200 rounded-lg font-bold hover:bg-red-50 hover:border-red-300 shadow-sm transition flex items-center">
                        <i class="bi bi-x-lg me-2"></i> Reject
                    </button>
                </form>
                @endif
                
                <button onclick="showUnlockModal('{{ $product->id }}')" class="px-4 py-2 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg font-bold hover:bg-indigo-100 shadow-sm transition flex items-center">
                    <i class="bi bi-unlock me-2"></i> Unlock Design
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Image Column -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sticky top-6">
                @php
                    $images = $product->images;
                    $hasImages = $images->count() > 0;
                @endphp

                @if($hasImages)
                    <div class="relative aspect-square bg-slate-100 rounded-lg overflow-hidden mb-4 group">
                        @php
                            $firstImage = $images->first()->path;
                            $mainImgUrl = str_starts_with($firstImage, 'http') ? $firstImage : asset('storage/' . $firstImage);
                        @endphp
                        <img src="{{ $mainImgUrl }}" 
                             id="mainImage" 
                             class="w-full h-full object-contain cursor-zoom-in transition-transform duration-500 group-hover:scale-105" 
                             onclick="window.openUniversalPreview('{{ $mainImgUrl }}', 'image')">
                        
                        <div class="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded backdrop-blur-sm">
                            <i class="bi bi-arrows-fullscreen"></i>
                        </div>
                    </div>

                    @if($images->count() > 1)
                    <div class="grid grid-cols-4 gap-2">
                        @foreach($images as $img)
                            @php
                                $thumbUrl = str_starts_with($img->path, 'http') ? $img->path : asset('storage/' . $img->path);
                            @endphp
                            <div class="aspect-square rounded-md overflow-hidden border border-slate-200 cursor-pointer hover:border-indigo-500 hover:ring-2 hover:ring-indigo-100 transition"
                                 onclick="document.getElementById('mainImage').src = '{{ $thumbUrl }}'">
                                <img src="{{ $thumbUrl }}" class="w-full h-full object-cover">
                            </div>
                        @endforeach
                    </div>
                    @endif
                @else
                    <div class="aspect-square bg-slate-50 rounded-lg border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-slate-400">
                        <i class="bi bi-image text-4xl mb-2"></i>
                        <span class="text-sm">No Image Available</span>
                    </div>
                @endif

                {{-- QR Code Section --}}
                @if($product->design_status === 'Accepted' && $product->qr_code)
                <div class="mt-4 bg-white rounded-lg border border-slate-200 p-4">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">QR Code</h4>
                    <div class="flex justify-center">
                        @if(str_ends_with($product->qr_code, '.svg'))
                            <img src="{{ asset('storage/' . $product->qr_code) }}" alt="QR Code" class="w-40 h-40">
                        @else
                            <img src="{{ asset('storage/' . $product->qr_code) }}" alt="QR Code" class="w-40 h-40 object-contain">
                        @endif
                    </div>
                    <p class="text-center text-xs text-slate-500 mt-2 font-mono">{{ $product->design_code }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Details Column -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                    <h3 class="font-bold text-slate-800">Design Information</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                        <!-- Product Code -->
                        <div>
                            <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Product Code</dt>
                            <dd class="text-sm font-semibold text-slate-800">{{ $product->product_code }}</dd>
                        </div>
                        
                        <!-- Design Code -->
                        <div>
                            <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Design Code</dt>
                            <dd class="text-sm font-mono font-bold text-indigo-600 bg-indigo-50 inline-block px-2 py-0.5 rounded border border-indigo-100">
                                {{ $product->design_code ?? 'Not Generated' }}
                            </dd>
                        </div>

                        <!-- Category -->
                        <div>
                            <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Category</dt>
                            <dd class="text-sm text-slate-700">{{ $product->category->name ?? 'N/A' }}</dd>
                        </div>

                        <!-- Sub Category -->
                        <div>
                            <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Sub Category</dt>
                            <dd class="text-sm text-slate-700">{{ $product->subcategory->name ?? 'N/A' }}</dd>
                        </div>

                        <!-- Status -->
                        <div>
                            <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Approval Status</dt>
                            <dd>
                                @php
                                    $statusColor = match($product->design_status) {
                                        'Accepted' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                        'Rejected' => 'bg-red-100 text-red-700 border-red-200',
                                        default => 'bg-amber-100 text-amber-700 border-amber-200',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $statusColor }}">
                                    {{ $product->design_status ?? 'Pending' }}
                                </span>
                            </dd>
                        </div>

                        <!-- Created By -->
                        <div>
                            <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Created By</dt>
                            <dd class="text-sm text-slate-700 flex items-center gap-2">
                                @php $creator = $product->creator_details; @endphp
                                <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">
                                    {{ substr($creator['name'] ?? 'U', 0, 1) }}
                                </div>
                                {{ $creator['code'] }} - {{ $creator['name'] }}
                                <span class="text-[10px] text-slate-400 font-bold ml-1 bg-slate-100 px-1.5 py-0.5 rounded">({{ $creator['type'] }})</span>
                                <span class="text-xs text-slate-400 ml-1">({{ $product->created_at->format('d M Y') }})</span>
                            </dd>
                        </div>

                        <!-- Accepted By -->
                        @if($product->design_status === 'Accepted' && $product->acceptor_details)
                        <div>
                            <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Accepted By</dt>
                            <dd class="text-sm text-slate-700 flex items-center gap-2">
                                @php $acceptor = $product->acceptor_details; @endphp
                                <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center text-xs font-bold text-emerald-700">
                                    {{ substr($acceptor['name'] ?? 'A', 0, 1) }}
                                </div>
                                {{ $acceptor['code'] }} - {{ $acceptor['name'] }}
                                <span class="text-[10px] text-slate-400 font-bold ml-1 bg-slate-100 px-1.5 py-0.5 rounded">({{ $acceptor['type'] }})</span>
                            </dd>
                        </div>
                        @endif

                        <!-- View Lock Status -->
                        <div class="col-span-1 md:col-span-2 border-t border-slate-100 pt-4 mt-2">
                            <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">View Access</dt>
                            <dd class="flex items-center gap-3">
                                @if($product->design_view_unlocked_until && now()->isBefore($product->design_view_unlocked_until))
                                    <div class="flex items-center text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-100">
                                        <i class="bi bi-unlock-fill me-2"></i>
                                        <div>
                                            <span class="text-sm font-bold block">Temporarily Unlocked</span>
                                            <span class="text-xs opacity-80">Until {{ \Carbon\Carbon::parse($product->design_view_unlocked_until)->format('d M, h:i A') }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center text-slate-500 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200">
                                        <i class="bi bi-lock-fill me-2"></i>
                                        <span class="text-sm font-bold">Locked by Default</span>
                                    </div>
                                @endif
                                
                            </dd>
                        </div>

                        <!-- Technical Specs -->
                        <div class="col-span-1 md:col-span-2 border-t border-slate-100 pt-4 mt-2">
                            <h4 class="text-sm font-bold text-slate-800 mb-3">Specifications</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="bg-slate-50 p-3 rounded border border-slate-100">
                                    <span class="block text-[10px] uppercase text-slate-400 font-bold">Weight Range</span>
                                    <span class="block text-sm font-mono font-semibold">{{ $product->weight_from }} - {{ $product->weight_to }} g</span>
                                </div>
                                <div class="bg-slate-50 p-3 rounded border border-slate-100">
                                    <span class="block text-[10px] uppercase text-slate-400 font-bold">Size / Length</span>
                                    <span class="block text-sm font-mono font-semibold">{{ $product->size ?? '-' }} / {{ $product->length ?? '-' }}</span>
                                </div>
                                <div class="bg-slate-50 p-3 rounded border border-slate-100">
                                    <span class="block text-[10px] uppercase text-slate-400 font-bold">Purity / HUID</span>
                                    <span class="block text-sm font-mono font-semibold">{{ $product->rodium ?? '-' }} / {{ $product->hallmark ?? '-' }}</span>
                                </div>
                                <div class="bg-slate-50 p-3 rounded border border-slate-100">
                                    <span class="block text-[10px] uppercase text-slate-400 font-bold">Touch / Hook</span>
                                    <span class="block text-sm font-mono font-semibold">{{ $product->touch ?? '-' }} / {{ $product->hook ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Unlock Modal (Reused from Index) -->
    @include('super-admin.design.partials.unlock-modal')
    
</div>
@endsection
