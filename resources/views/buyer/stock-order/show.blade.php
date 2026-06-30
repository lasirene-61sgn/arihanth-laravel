@extends('buyer.layouts.app')

@section('title', 'Order Details - ' . $stockOrder->order_number)

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div class="flex items-center gap-4">
        <a href="{{ route('buyer.stock-order.index') }}" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
            <i class="bi bi-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight uppercase">{{ $stockOrder->order_number }}</h1>
            <p class="text-slate-500 text-sm font-medium">Track your stock order fulfillment</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Items List -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Order Items ({{ $stockOrder->items->count() }})</h3>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Fulfillment Status</span>
            </div>
            <div class="divide-y divide-slate-50">
                @foreach($stockOrder->items->groupBy('design_code') as $designCode => $items)
                @php $firstItem = $items->first(); @endphp
                <div class="p-6 hover:bg-slate-50/50 transition-all group">
                    <div class="flex gap-6">
                        <div class="w-24 h-24 rounded-2xl overflow-hidden bg-white shrink-0 border border-slate-100 p-1 group-hover:shadow-md transition-all">
                            @if($firstItem->image_path)
                                <img src="{{ asset('storage/' . $firstItem->image_path) }}" class="w-full h-full object-contain cursor-pointer" onclick="window.openUniversalPreview('{{ asset('storage/' . $firstItem->image_path) }}', 'image')">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-200 bg-slate-50">
                                    <i class="bi bi-image text-3xl"></i>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-black text-slate-900 uppercase tracking-tight">{{ $designCode }}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] font-black text-slate-400 bg-slate-100 px-2 py-0.5 rounded uppercase tracking-widest">{{ $firstItem->category_name }}</span>
                                        <span class="text-[10px] font-black text-indigo-400 bg-indigo-50 px-2 py-0.5 rounded uppercase tracking-widest">{{ $items->count() }} Variants</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 space-y-2">
                                <div class="grid grid-cols-12 gap-2 px-3 py-1.5 bg-slate-50 rounded-lg text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                    <div class="col-span-1">#</div>
                                    <div class="col-span-3">Quantity</div>
                                    <div class="col-span-3">Grams</div>
                                    <div class="col-span-3">Total</div>
                                    <div class="col-span-2">Status</div>
                                </div>
                                @foreach($items as $idx => $item)
                                <div class="grid grid-cols-12 gap-2 px-3 py-2 bg-white border border-slate-100 rounded-lg items-center">
                                    <div class="col-span-1 text-[10px] font-bold text-slate-400">{{ $idx + 1 }}</div>
                                    <div class="col-span-3 text-xs font-bold text-slate-900">{{ $item->quantity }} pcs</div>
                                    <div class="col-span-3 text-xs font-bold text-slate-900">{{ number_format($item->grams, 3) }}g</div>
                                    <div class="col-span-3 text-xs font-bold text-indigo-600">{{ number_format($item->quantity * $item->grams, 3) }}g</div>
                                    <div class="col-span-2">
                                        @php
                                            $itemStatusColors = [
                                                'Pending' => 'bg-amber-100 text-amber-700',
                                                'Accepted' => 'bg-blue-100 text-blue-700',
                                                'Finished' => 'bg-indigo-100 text-indigo-700',
                                                'Completed' => 'bg-emerald-100 text-emerald-700',
                                                'Rejected' => 'bg-red-100 text-red-700',
                                            ];
                                        @endphp
                                        <span class="text-[8px] font-black px-2 py-0.5 rounded uppercase tracking-widest {{ $itemStatusColors[$item->status] ?? 'bg-slate-100 text-slate-700' }}">
                                            {{ $item->status === 'Finished' ? 'Production Finished' : $item->status }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            @if($firstItem->item_notes)
                            <div class="mt-3 p-3 bg-slate-50 rounded-xl border border-slate-100 text-[11px] text-slate-600 italic">
                                <i class="bi bi-info-circle-fill me-1"></i>
                                <strong>My Note:</strong> {{ $firstItem->item_notes }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-6 pb-4 border-b border-slate-50">Order Overview</h3>
            
            <div class="space-y-6">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Order Status</p>
                    <div class="inline-block">
                        @php
                            $statusColors = [
                                'Pending' => 'bg-amber-50 text-amber-600 border-amber-100',
                                'Allocated' => 'bg-blue-50 text-blue-600 border-blue-100',
                                'Completed' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                'Cancelled' => 'bg-red-50 text-red-600 border-red-100',
                            ];
                            $color = $statusColors[$stockOrder->status] ?? 'bg-slate-50 text-slate-600 border-slate-100';
                        @endphp
                        <span class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest border {{ $color }}">
                            {{ $stockOrder->status }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-indigo-50/50 p-4 rounded-2xl border border-indigo-100">
                        <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1">Total Weight</p>
                        <p class="text-xl font-black text-indigo-600 tracking-tight">{{ number_format($stockOrder->items->sum(fn($i) => $i->quantity * $i->grams), 3) }}g</p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Pieces</p>
                        <p class="text-xl font-black text-slate-900 tracking-tight">{{ $stockOrder->items->sum('quantity') }} pcs</p>
                    </div>
                </div>

                @if($stockOrder->notes)
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">My Notes</p>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 text-xs text-slate-600 leading-relaxed italic">
                        "{{ $stockOrder->notes }}"
                    </div>
                </div>
                @endif

                <div class="pt-6 border-t border-slate-50">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-4 italic leading-relaxed">
                        If an item is rejected, our team will automatically re-allocate it to another craftsman to ensure fulfillment.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
