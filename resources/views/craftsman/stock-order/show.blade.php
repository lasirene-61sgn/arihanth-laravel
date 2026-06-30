@extends('craftsman.layouts.app')

@section('title', 'Order Details - ' . $stockOrder->order_number)

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div class="flex items-center gap-4">
        <a href="{{ route('craftsman.stock-order.index') }}" class="w-10 h-10 rounded-xl bg-white border border-emerald-200 flex items-center justify-center text-emerald-500 hover:bg-emerald-50 transition-all shadow-sm">
            <i class="bi bi-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-emerald-900 tracking-tight uppercase">{{ $stockOrder->order_number }}</h1>
            <p class="text-emerald-600 text-sm font-medium">Full details of the stock order</p>
        </div>
    </div>
    <div class="flex gap-3">
        @if($stockOrder->status === 'Allocated')
        <form action="{{ route('craftsman.stock-order.status', $stockOrder->id) }}" method="POST">
            @csrf
            <input type="hidden" name="status" value="Completed">
            <button type="submit" class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-emerald-200 hover:bg-emerald-700 transition-all flex items-center gap-2">
                <i class="bi bi-check2-circle text-xl"></i>
                <span>Mark as Completed</span>
            </button>
        </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Items List -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-emerald-100 overflow-hidden">
            <div class="px-6 py-4 bg-emerald-50/50 border-b border-emerald-100 flex justify-between items-center">
                <h3 class="text-sm font-black text-emerald-900 uppercase tracking-wider">Order Items ({{ $stockOrder->items->count() }})</h3>
                <span class="text-[10px] font-black text-emerald-400 uppercase tracking-widest">Fulfillment List</span>
            </div>
            <div class="divide-y divide-emerald-50">
                @foreach($stockOrder->items->groupBy('design_code') as $designCode => $items)
                @php $firstItem = $items->first(); @endphp
                <div class="p-6 hover:bg-emerald-50/30 transition-all group">
                    <div class="flex gap-6">
                        <div class="w-24 h-24 rounded-2xl overflow-hidden bg-white shrink-0 border border-emerald-100 p-1 group-hover:shadow-md transition-all">
                            @if($firstItem->image_path)
                                <img src="{{ asset('storage/' . $firstItem->image_path) }}" class="w-full h-full object-contain cursor-pointer" onclick="window.openUniversalPreview('{{ asset('storage/' . $firstItem->image_path) }}', 'image')">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-emerald-200 bg-emerald-50/50"><i class="bi bi-image text-3xl"></i></div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-black text-emerald-900 uppercase tracking-tight">{{ $designCode }}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] font-black text-emerald-400 bg-emerald-50 px-2 py-0.5 rounded uppercase tracking-widest">{{ $firstItem->category_name }}</span>
                                        <span class="text-[10px] font-black text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded uppercase tracking-widest">{{ $items->count() }} Variants</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 space-y-2">
                                <div class="grid grid-cols-12 gap-2 px-3 py-1.5 bg-emerald-50/50 rounded-lg text-[9px] font-black text-emerald-400 uppercase tracking-widest">
                                    <div class="col-span-1">#</div>
                                    <div class="col-span-2">Quantity</div>
                                    <div class="col-span-2">Grams</div>
                                    <div class="col-span-3">Total</div>
                                    <div class="col-span-2">Status</div>
                                    <div class="col-span-2 text-right">Action</div>
                                </div>
                                @foreach($items as $idx => $item)
                                <div class="grid grid-cols-12 gap-2 px-3 py-2 bg-white border border-emerald-100/50 rounded-lg items-center">
                                    <div class="col-span-1 text-[10px] font-bold text-emerald-400">{{ $idx + 1 }}</div>
                                    <div class="col-span-2 text-xs font-bold text-emerald-900">{{ $item->quantity }} pcs</div>
                                    <div class="col-span-2 text-xs font-bold text-emerald-900">{{ number_format($item->grams, 3) }}g</div>
                                    <div class="col-span-3 text-xs font-bold text-emerald-600">{{ number_format($item->quantity * $item->grams, 3) }}g</div>
                                    <div class="col-span-2">
                                        @php
                                            $statusColors = [
                                                'Pending' => 'bg-amber-100 text-amber-700',
                                                'Accepted' => 'bg-blue-100 text-blue-700',
                                                'Finished' => 'bg-emerald-100 text-emerald-700',
                                                'Completed' => 'bg-slate-100 text-slate-700',
                                                'Rejected' => 'bg-red-100 text-red-700',
                                            ];
                                        @endphp
                                        <span class="text-[8px] font-black px-2 py-0.5 rounded uppercase tracking-widest {{ $statusColors[$item->status] ?? 'bg-emerald-50 text-emerald-700' }}">
                                            {{ $item->status === 'Finished' ? 'Ready' : $item->status }}
                                        </span>
                                    </div>
                                    <div class="col-span-2 text-right">
                                        @if($item->status === 'Pending')
                                            <form action="{{ route('craftsman.stock-order.item-status', [$stockOrder->id, $item->id]) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="Accepted">
                                                <button type="submit" class="bg-emerald-500 text-white px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all">Accept</button>
                                            </form>
                                        @elseif($item->status === 'Accepted')
                                            <form action="{{ route('craftsman.stock-order.item-status', [$stockOrder->id, $item->id]) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="Finished">
                                                <button type="submit" class="bg-indigo-600 text-white px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all">Finish</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            @if($firstItem->item_notes)
                            <div class="mt-3 p-3 bg-emerald-50 border border-emerald-100 rounded-xl text-[11px] text-emerald-700 leading-relaxed italic">
                                "{{ $firstItem->item_notes }}"
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
        <div class="bg-white rounded-3xl shadow-sm border border-emerald-100 p-8">
            <h3 class="text-sm font-black text-emerald-900 uppercase tracking-wider mb-6 pb-4 border-b border-emerald-50">Order Info</h3>
            
            <div class="space-y-6">
                <div>
                    <p class="text-[9px] font-black text-emerald-400 uppercase tracking-widest mb-2">Buyer</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-900 text-white flex items-center justify-center font-bold text-sm uppercase">
                            {{ substr($stockOrder->buyer->business_name, 0, 2) }}
                        </div>
                        <div>
                            <p class="text-sm font-black text-emerald-900">{{ $stockOrder->buyer->business_name }}</p>
                            <p class="text-[10px] text-emerald-500 font-bold uppercase tracking-widest">{{ $stockOrder->buyer->bp_code }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="text-[9px] font-black text-emerald-400 uppercase tracking-widest mb-2">Current Status</p>
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
                    <div class="bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100">
                        <p class="text-[9px] font-black text-emerald-400 uppercase tracking-widest mb-1">Total Weight</p>
                        <p class="text-xl font-black text-emerald-900 tracking-tight">{{ number_format($stockOrder->items->sum(fn($i) => $i->quantity * $i->grams), 3) }}g</p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Pieces</p>
                        <p class="text-xl font-black text-slate-900 tracking-tight">{{ $stockOrder->items->sum('quantity') }} pcs</p>
                    </div>
                </div>

                @if($stockOrder->notes)
                <div>
                    <p class="text-[9px] font-black text-emerald-400 uppercase tracking-widest mb-2">Order Notes</p>
                    <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-100 text-xs text-emerald-700 leading-relaxed italic">
                        "{{ $stockOrder->notes }}"
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejection-modal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl">
        <h2 class="text-xl font-black text-emerald-900 mb-4 uppercase">Reject Item</h2>
        <form id="rejection-form" method="POST">
            @csrf
            <input type="hidden" name="status" value="Rejected">
            <div class="mb-6">
                <label class="text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-2 block">Reason for Rejection</label>
                <textarea name="rejection_reason" required rows="3" class="w-full bg-emerald-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-500" placeholder="Why are you rejecting this item?"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="hideRejectionModal()" class="flex-1 bg-emerald-50 text-emerald-700 py-3 rounded-2xl font-bold uppercase tracking-widest hover:bg-emerald-100 transition-all">Cancel</button>
                <button type="submit" class="flex-1 bg-red-600 text-white py-3 rounded-2xl font-bold uppercase tracking-widest hover:bg-red-700 transition-all shadow-lg shadow-red-100">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function showRejectionModal(itemId) {
        const modal = document.getElementById('rejection-modal');
        const form = document.getElementById('rejection-form');
        let actionUrl = "{{ route('craftsman.stock-order.item-status', [$stockOrder->id, ':itemId']) }}";
        form.action = actionUrl.replace(':itemId', itemId);
        modal.classList.remove('hidden');
    }

    function hideRejectionModal() {
        document.getElementById('rejection-modal').classList.add('hidden');
    }
</script>
@endsection
