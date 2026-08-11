@extends('buyer.layouts.app')

@section('title', 'Work Order Details')

@section('content')
<div class="space-y-6 py-4">
    <!-- Header Section -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                    <i class="bi bi-file-earmark-text text-2xl"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">Work Order Summary</span>
                    <h1 class="text-xl font-bold text-slate-800">
                        @if(!empty($workOrder->id))
                            #{{ $workOrder->id }}
                        @else
                            Work Order Details
                        @endif
                    </h1>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('buyer.work-order.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all shadow-sm">
                    <i class="bi bi-arrow-left mr-2"></i> Back to List
                </a>
                
                @if(!empty($workOrder->id))
                <!-- <a href="{{ route('buyer.work-order.print', $workOrder) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-700 text-xs font-bold rounded-xl transition-all shadow-sm">
                    <i class="bi bi-eye mr-2"></i> View
                </a> -->
                @endif
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i class="bi bi-sliders text-blue-500"></i>
                Technical Specification Sheet
            </h4>
            
            @if(!empty($workOrder->status))
            <div>
                @php
                    $statusColors = [
                        'completed'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'pending'    => 'bg-amber-50 text-amber-700 border-amber-200',
                        'in_process' => 'bg-blue-50 text-blue-700 border-blue-200',
                    ];
                    $badgeClass = $statusColors[$workOrder->status] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $badgeClass }}">
                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-current opacity-75"></span>
                    {{ ucfirst($workOrder->status) }}
                </span>
            </div>
            @endif
        </div>

        <div class="p-6 md:p-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                
                <!-- Left Details Grid -->
                <div class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @if(!empty($workOrder->id))
                        <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Work Order Number</label>
                            <p class="text-sm font-mono font-bold text-slate-800">#{{ $workOrder->id }}</p>
                        </div>
                        @endif

                        @if(!empty($workOrder->product_code))
                        <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Product Code</label>
                            <p class="text-sm font-bold text-blue-600">{{ $workOrder->product_code }}</p>
                        </div>
                        @endif

                        @php $productName = $workOrder->product->product_name ?? null; @endphp
                        @if(!empty($productName) && $productName !== 'N/A')
                        <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Product Name</label>
                            <p class="text-sm font-bold text-slate-800">{{ $productName }}</p>
                        </div>
                        @endif

                        @if(!empty($workOrder->qty))
                        <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Quantity / Type</label>
                            <p class="text-sm font-bold text-slate-800">
                                {{ $workOrder->qty }} 
                                @if(!empty($workOrder->type))
                                    <span class="text-slate-500 font-medium lowercase me-1">({{ $workOrder->type }})</span>
                                @endif
                            </p>
                        </div>
                        @endif

                        @if(!empty($workOrder->wo_date))
                        <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100 sm:col-span-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Order Date</label>
                            <p class="text-sm font-bold text-slate-800 flex items-center">
                                <i class="bi bi-calendar-check mr-2 text-slate-400"></i>{{ $workOrder->wo_date->format('d M Y') }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Right Details Grid -->
                <div class="space-y-6 lg:border-l lg:border-slate-100 lg:pl-10">
                    <!-- Order Tracking -->
                    <div class="mb-6">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3">Order Tracking</label>
                        <div class="p-4 bg-slate-50/80 rounded-xl border border-slate-100 space-y-4">
                            <!-- Created -->
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                                    <i class="bi bi-clock-history text-blue-600 text-[10px]"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800 mb-0.5">Order Created</p>
                                    <p class="text-[11px] font-medium text-slate-500"><i class="bi bi-calendar-event me-1"></i>{{ $workOrder->created_at ? $workOrder->created_at->timezone('Asia/Kolkata')->format('d M Y h:i A') : 'N/A' }}</p>
                                </div>
                            </div>
                            
                            <!-- Completed -->
                            @if(strtolower($workOrder->status) === 'completed' || strtolower($workOrder->craftsman_status) === 'completed')
                            <div class="flex items-start gap-3 relative">
                                <!-- Connecting line -->
                                <div class="absolute left-3 top-[-16px] bottom-6 w-px bg-slate-200"></div>
                                
                                <div class="mt-0.5 w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center shrink-0 relative z-10">
                                    <i class="bi bi-check-circle-fill text-emerald-600 text-[10px]"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800 mb-0.5">Order Completed</p>
                                    <p class="text-[11px] font-medium text-slate-500"><i class="bi bi-calendar-event me-1"></i>{{ $workOrder->craftsman_completed_at ? \Carbon\Carbon::parse($workOrder->craftsman_completed_at)->timezone('Asia/Kolkata')->format('d M Y h:i A') : ($workOrder->updated_at ? $workOrder->updated_at->timezone('Asia/Kolkata')->format('d M Y h:i A') : 'N/A') }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Order Gallery -->
                    @php $gallery = array_filter($workOrder->gallery_images ?? []); @endphp
                    @if(!empty($gallery) && count($gallery) > 0)
                    <div class="mb-6">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3">Order Gallery</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($gallery as $imageUrl)
                                @php $isPdf = str_ends_with(strtolower($imageUrl), '.pdf'); @endphp
                                <div class="relative group cursor-pointer aspect-square bg-slate-50 rounded-xl border border-slate-200/80 overflow-hidden flex items-center justify-center transition-all hover:border-blue-300 hover:shadow-md"
                                     onclick="openUniversalPreview('{{ $imageUrl }}', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                    @if($isPdf)
                                        <canvas class="pdf-canvas" data-url="{{ $imageUrl }}" data-desired-width="200"></canvas>
                                    @else
                                        <img src="{{ $imageUrl }}" class="w-full h-full object-cover">
                                    @endif
                                    <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/10 flex items-center justify-center transition-colors">
                                        <div class="bg-white/90 backdrop-blur-sm p-2 rounded-full shadow-md opacity-0 group-hover:opacity-100 scale-90 group-hover:scale-100 transition-all duration-200">
                                            <i class="bi bi-search text-blue-600 text-xs"></i>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if(!empty($workOrder->expected_delivery_date))
                        <div class="p-3.5 bg-amber-50/50 rounded-xl border border-amber-100">
                            <label class="text-[10px] font-bold text-amber-600/80 uppercase tracking-widest block mb-1">Expected Delivery</label>
                            <p class="text-sm font-bold text-amber-700 flex items-center">
                                <i class="bi bi-truck mr-2"></i>{{ $workOrder->expected_delivery_date->format('d M Y') }}
                            </p>
                        </div>
                        @endif

                        @if(!empty($workOrder->work_order_for))
                        <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Work Order For</label>
                            <p class="text-sm font-bold text-slate-800">{{ $workOrder->work_order_for }}</p>
                        </div>
                        @endif

                        @if(!empty($workOrder->bp_code))
                        <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">BP Code</label>
                            <p class="text-sm font-bold text-slate-800">{{ $workOrder->bp_code }}</p>
                        </div>
                        @endif

                        @php $buyerName = $workOrder->buyer->business_name ?? $workOrder->buyer->name ?? null; @endphp
                        @if(!empty($buyerName) && $buyerName !== 'N/A')
                        <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Created By</label>
                            <p class="text-sm font-bold text-slate-800 italic">{{ $buyerName }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Production Instructions -->
            @if(!empty($workOrder->instructions))
            <div class="mt-8 pt-6 border-t border-slate-100">
                <div class="flex items-center gap-2 mb-3">
                    <i class="bi bi-chat-left-text text-blue-500"></i>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Production Instructions</label>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 text-sm text-slate-700 leading-relaxed italic">
                    {{ $workOrder->instructions }}
                </div>
            </div>
            @endif

            <!-- Footer Action Buttons -->
            @if(!empty($workOrder->id))
            <div class="flex flex-wrap items-center justify-between gap-3 mt-8 pt-6 border-t border-slate-100">
                <!-- <a href="{{ route('buyer.work-order.edit', $workOrder) }}" class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                    <i class="bi bi-pencil mr-2"></i> Edit Specifications
                </a> -->
                
                <form action="{{ route('buyer.work-order.destroy', $workOrder) }}" method="POST" class="m-0">
                    @csrf
                    @method('DELETE')
                    <!-- <button type="submit" class="inline-flex items-center px-4 py-2.5 text-red-600 hover:text-red-700 hover:bg-red-50 text-xs font-bold rounded-xl transition-all"
                            onclick="return confirm('Are you sure you want to permanently delete this work order?')">
                        <i class="bi bi-trash mr-2"></i> Delete Order
                    </button> -->
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection