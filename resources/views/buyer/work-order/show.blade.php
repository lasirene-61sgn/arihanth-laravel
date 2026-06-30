@extends('buyer.layouts.app')

@section('title', 'Work Order Details')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Work Order Details</h1>
            <p class="text-sm text-slate-500">Full technical summary for Order ID: <span class="font-mono font-bold text-blue-600">#{{ $workOrder->id }}</span></p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('buyer.work-order.index') }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-50 transition-all shadow-sm">
                <i class="bi bi-arrow-left mr-2"></i> Back to List
            </a>
            <a href="{{ route('buyer.work-order.print', $workOrder) }}" target="_blank" class="inline-flex items-center px-4 py-2.5 bg-slate-100 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-200 transition-all shadow-sm">
                <i class="bi bi-printer mr-2"></i> Print
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h4 class="text-lg font-bold text-slate-800 uppercase tracking-tight">Technical Specification Sheet</h4>
            <div>
                @php
                    $statusColors = [
                        'completed' => 'bg-green-100 text-green-700 border-green-200',
                        'pending'   => 'bg-amber-100 text-amber-700 border-amber-200',
                        'in_process'=> 'bg-blue-100 text-blue-700 border-blue-200',
                    ];
                    $badgeClass = $statusColors[$workOrder->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $badgeClass }}">
                    <span class="w-2 h-2 mr-2 rounded-full bg-current opacity-70"></span>
                    {{ ucfirst($workOrder->status) }}
                </span>
            </div>
        </div>

        <div class="p-6 md:p-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                
                <div class="space-y-8">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-8 gap-x-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Work Order Number</label>
                            <p class="text-sm font-mono font-bold text-slate-800">{{ $workOrder->id }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Product Code</label>
                            <p class="text-sm font-bold text-blue-600">{{ $workOrder->product_code }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Product Name</label>
                            <p class="text-sm font-bold text-slate-800">{{ $workOrder->product->product_name ?? 'N/A' }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Quantity / Type</label>
                            <p class="text-sm font-bold text-slate-800">{{ $workOrder->qty }} <span class="text-slate-500 font-medium lowercase">{{ $workOrder->type }}</span></p>
                        </div>
                        <div class="space-y-1 col-span-full">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Order Date</label>
                            <p class="text-sm font-bold text-slate-800"><i class="bi bi-calendar-check mr-2 text-slate-300"></i>{{ $workOrder->wo_date->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-8 border-l border-slate-100 pl-0 lg:pl-12">
                    <div class="mb-8">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-4">Order Gallery</label>
                        @php
                            $gallery = $workOrder->gallery_images;
                        @endphp

                        @if(count($gallery) > 0)
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($gallery as $imageUrl)
                                    @php $isPdf = str_ends_with(strtolower($imageUrl), '.pdf'); @endphp
                                    <div class="relative group cursor-zoom-in aspect-square bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden flex items-center justify-center transition-all hover:border-blue-200 hover:shadow-lg shadow-blue-50"
                                         onclick="openUniversalPreview('{{ $imageUrl }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                        @if($isPdf)
                                            <canvas class="pdf-canvas" data-url="{{ $imageUrl }}" data-desired-width="200"></canvas>
                                        @else
                                            <img src="{{ $imageUrl }}" class="w-full h-full object-cover">
                                        @endif
                                        <div class="absolute inset-0 bg-blue-600/0 group-hover:bg-blue-600/5 flex items-center justify-center transition-colors">
                                            <div class="bg-white/90 backdrop-blur-sm p-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 scale-90 group-hover:scale-100 transition-all duration-300">
                                                <i class="bi bi-search text-blue-600 text-sm"></i>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center p-8 bg-slate-50 rounded-2xl border border-slate-100">
                                <i class="bi bi-image text-slate-200 text-4xl mb-2"></i>
                                <p class="text-xs text-slate-400 font-medium italic">No images provided</p>
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-8 gap-x-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Expected Delivery</label>
                            <p class="text-sm font-bold text-amber-600"><i class="bi bi-truck mr-2"></i>{{ $workOrder->expected_delivery_date->format('d M Y') }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Work Order For</label>
                            <p class="text-sm font-bold text-slate-800">{{ $workOrder->work_order_for }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">BP Code</label>
                            <p class="text-sm font-bold text-slate-800">{{ $workOrder->bp_code }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Created By</label>
                            <p class="text-sm font-bold text-slate-800 italic">{{ $workOrder->buyer->business_name ?? $workOrder->buyer->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($workOrder->instructions)
            <div class="mt-12 pt-8 border-t border-slate-100">
                <div class="flex items-center gap-2 mb-4">
                    <i class="bi bi-chat-left-text text-blue-500"></i>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Production Instructions</label>
                </div>
                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 text-sm text-slate-600 leading-relaxed italic">
                    {{ $workOrder->instructions }}
                </div>
            </div>
            @endif

            <div class="flex flex-wrap items-center gap-3 mt-12 pt-8 border-t border-slate-100">
                <a href="{{ route('buyer.work-order.edit', $workOrder) }}" class="inline-flex items-center px-6 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition-all shadow-md shadow-blue-100">
                    <i class="bi bi-pencil mr-2"></i> Edit Specifications
                </a>
                
                <form action="{{ route('buyer.work-order.destroy', $workOrder) }}" method="POST" class="m-0 sm:ml-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-6 py-2.5 text-red-600 hover:text-red-700 hover:bg-red-50 text-sm font-bold rounded-xl transition-all"
                            onclick="return confirm('Are you sure you want to permanently delete this work order?')">
                        <i class="bi bi-trash mr-2"></i> Delete Order
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection