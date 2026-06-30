@extends('key-user.layouts.app')

@section('title', 'Work Order Details')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Work Order Details</h1>
            <nav class="flex text-sm text-gray-500 mt-1">
                <a href="{{ route('key-user.dashboard') }}" class="hover:text-indigo-600 transition">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('key-user.work-order.index') }}" class="hover:text-indigo-600 transition">Work Orders</a>
                <span class="mx-2">/</span>
                <span class="text-gray-800 font-medium">#{{ $workOrder->work_order_number }}</span>
            </nav>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('key-user.work-order.edit', $workOrder) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition shadow-sm">
                <i class="bi bi-pencil mr-2"></i> Edit
            </a>
            <a href="{{ route('key-user.work-order.print', $workOrder) }}" target="_blank" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="bi bi-printer mr-2"></i> Print
            </a>
            <a href="{{ route('key-user.work-order.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="bi bi-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h4 class="text-lg font-bold text-gray-800">Work Order Information</h4>
            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-xs font-black uppercase tracking-widest rounded-full border border-indigo-200">
                Official Document
            </span>
        </div>

        <div class="p-6 lg:p-8">
            <div class="flex flex-col lg:flex-row gap-10">
                
                <div class="flex-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                        
                        <div class="space-y-1">
                            <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider">WO Number</span>
                            <p class="text-lg font-bold text-indigo-600 font-mono tracking-tight">{{ $workOrder->work_order_number }}</p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider">Business Partner</span>
                            <p class="text-sm font-bold text-gray-800">{{ $workOrder->bp_code }}</p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider">Product Category</span>
                            <p class="text-sm font-bold text-gray-800">{{ $workOrder->product_category }}</p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider">Unit Type</span>
                            <p class="text-sm font-bold text-gray-800">{{ $workOrder->type }}</p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider">Priority Level</span>
                            <div>
                                @if($workOrder->order_type == 'Regular')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">Regular</span>
                                @elseif($workOrder->order_type == 'Urgent')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">Urgent</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">Super Urgent</span>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-1">
                            <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider">Order Quantity</span>
                            <p class="text-sm font-bold text-gray-800">{{ $workOrder->quantity }} Units</p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider">Size Parameter</span>
                            <p class="text-sm font-bold text-gray-800">{{ $workOrder->size ?? 'N/A' }}</p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider">Closing Status</span>
                            <div>
                                @if($workOrder->open_close == 'Open')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">Open</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200">Closed</span>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-1">
                            <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider">Enamel Details</span>
                            <p class="text-sm font-bold text-gray-800">{{ $workOrder->enamel ?? 'None' }}</p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider">Entry Date</span>
                            <p class="text-xs font-semibold text-gray-600">{{ $workOrder->created_at->format('d M, Y | h:i A') }}</p>
                        </div>

                        <div class="col-span-1 md:col-span-2 pt-4 border-t border-gray-50">
                            <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider block mb-2">Order Narration / Instructions</span>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 text-sm text-gray-700 leading-relaxed italic">
                                {{ $workOrder->narration_admin ?? 'No additional instructions provided for this work order.' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-80">
                    <div class="sticky top-6">
                        <span class="text-[11px] font-black text-gray-400 uppercase tracking-widest block mb-3 text-center lg:text-left">Order Gallery</span>
                        
                        @php
                            $gallery = $workOrder->gallery_images;
                        @endphp

                        @if(count($gallery) > 0)
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($gallery as $imageUrl)
                                    @php $isPdf = str_ends_with(strtolower($imageUrl), '.pdf'); @endphp
                                    <div class="relative group aspect-square overflow-hidden rounded-xl border-2 border-white shadow-md ring-1 ring-gray-200 cursor-pointer transition-all hover:ring-indigo-500"
                                         onclick="openUniversalPreview('{{ $imageUrl }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                        @if($isPdf)
                                            <div class="h-full w-full bg-gray-50 flex items-center justify-center relative">
                                                <canvas class="pdf-canvas w-full h-full object-cover" 
                                                        data-url="{{ $imageUrl }}" 
                                                        data-desired-width="200"></canvas>
                                                <i class="absolute bi bi-file-earmark-pdf text-xl text-red-500 opacity-40"></i>
                                            </div>
                                        @else
                                            <img src="{{ $imageUrl }}" 
                                                 class="h-full w-full object-cover transition duration-300 group-hover:scale-110">
                                        @endif
                                        <div class="absolute inset-0 bg-indigo-600/0 group-hover:bg-indigo-600/5 transition-colors"></div>
                                    </div>
                                @endforeach
                            </div>
                            <p class="text-[10px] text-gray-400 mt-3 text-center italic">Click to view full preview</p>
                        @else
                            <div class="aspect-[3/4] rounded-2xl bg-gray-50 border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400">
                                <i class="bi bi-image text-5xl mb-2 opacity-20"></i>
                                <p class="text-xs font-bold uppercase tracking-tighter">No images provided</p>
                            </div>
                        @endif

                        
                        <div class="mt-6 p-4 rounded-xl bg-indigo-600 text-white shadow-lg">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-clock-history text-2xl opacity-80"></i>
                                <div>
                                    <p class="text-[10px] font-bold uppercase opacity-80 tracking-widest leading-none">Last Updated</p>
                                    <p class="text-xs font-bold mt-1">{{ $workOrder->updated_at->format('d M, Y') }}</p>
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