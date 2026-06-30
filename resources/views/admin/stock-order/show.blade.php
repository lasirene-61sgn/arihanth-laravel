@extends('admin.layouts.app')

@section('title', 'Order Details - ' . $stockOrder->order_number)

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.stock-order.index') }}" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
            <i class="bi bi-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight uppercase">{{ $stockOrder->order_number }}</h1>
            <p class="text-slate-500 text-sm font-medium">Order details and craftsman allocation</p>
        </div>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('admin.stock-order.edit', $stockOrder->id) }}" class="bg-slate-100 text-slate-600 px-6 py-2.5 rounded-xl font-bold hover:bg-slate-200 transition-all flex items-center gap-2">
            <i class="bi bi-pencil-square text-xl"></i>
            <span>Edit Order</span>
        </a>
        <button id="reallocate-selected-btn" data-bs-toggle="modal" data-bs-target="#reallocateItemsModal" disabled class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all flex items-center gap-2">
            <i class="bi bi-arrow-repeat text-xl"></i>
            <span>Re-allocate Selected</span>
        </button>
        <button data-bs-toggle="modal" data-bs-target="#allocateModal" class="bg-white text-indigo-600 border border-indigo-200 px-6 py-2.5 rounded-xl font-bold hover:bg-indigo-50 transition-all flex items-center gap-2">
            <i class="bi bi-person-plus text-xl"></i>
            <span>{{ $stockOrder->craftsman_id ? 'Bulk Re-allocate' : 'Allocate Order' }}</span>
        </button>
    </div>
</div>

@if(session('success'))
<div class="mb-6 p-4 bg-emerald-100 border border-emerald-200 text-emerald-800 rounded-xl font-bold text-sm">
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Items List -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Order Items ({{ $stockOrder->items->count() }})</h3>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="select-all-items" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="select-all-items" class="text-[10px] font-black text-slate-400 uppercase tracking-widest cursor-pointer">Select All</label>
                </div>
            </div>
            <div class="divide-y divide-slate-50">
                @foreach($stockOrder->items->groupBy('design_code') as $designCode => $items)
                    @php $firstItem = $items->first(); @endphp
                    <div class="p-6 hover:bg-slate-50/50 transition-all group">
                        <div class="flex gap-6">
                            <div class="w-24 h-24 rounded-2xl overflow-hidden bg-white shrink-0 border border-slate-100 p-1 group-hover:shadow-md transition-all">
                                @if($firstItem->image_path)
                                    <img src="{{ asset('storage/' . $firstItem->image_path) }}" class="w-full h-full object-contain">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-200"><i class="bi bi-image text-2xl"></i></div>
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
                                    @if($firstItem->craftsman)
                                        <div class="text-right">
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Craftsman</p>
                                            <p class="text-xs font-bold text-indigo-600">{{ $firstItem->craftsman->name }}</p>
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-4 space-y-2">
                                    <div class="grid grid-cols-12 gap-2 px-3 py-1.5 bg-slate-50 rounded-lg text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                        <div class="col-span-1"></div>
                                        <div class="col-span-2">Quantity</div>
                                        <div class="col-span-2">Grams</div>
                                        <div class="col-span-3">Total</div>
                                        <div class="col-span-2">Status</div>
                                        <div class="col-span-2 text-right">Action</div>
                                    </div>
                                    @foreach($items as $item)
                                    <div class="grid grid-cols-12 gap-2 px-3 py-2 bg-white border border-slate-100 rounded-lg items-center">
                                        <div class="col-span-1">
                                            <input type="checkbox" name="selected_items[]" value="{{ $item->id }}" class="item-checkbox w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        </div>
                                        <div class="col-span-2 text-xs font-bold text-slate-900">{{ $item->quantity }} pcs</div>
                                        <div class="col-span-2 text-xs font-bold text-slate-900">{{ number_format($item->grams, 3) }}g</div>
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
                                                {{ $item->status === 'Finished' ? 'Ready' : $item->status }}
                                            </span>
                                        </div>
                                        <div class="col-span-2 text-right">
                                            @if($item->status !== 'Completed')
                                            <form action="{{ route('admin.stock-order.item-status', [$stockOrder->id, $item->id]) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="Completed">
                                                <button type="submit" class="bg-emerald-600 text-white px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest hover:bg-emerald-700 transition-all">
                                                    Complete
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                @if($firstItem->item_notes)
                                <div class="mt-3 p-3 bg-slate-50 rounded-xl border border-slate-100 text-[11px] text-slate-600 italic">
                                    "{{ $firstItem->item_notes }}"
                                </div>
                                @endif

                                @php $rejectedItem = $items->firstWhere('status', 'Rejected'); @endphp
                                @if($rejectedItem)
                                    <div class="mt-3 p-3 bg-red-50 border border-red-100 rounded-xl text-[10px] text-red-600 italic flex items-start gap-2">
                                        <i class="bi bi-exclamation-circle-fill mt-0.5"></i>
                                        <span><strong>Rejection Reason:</strong> {{ $rejectedItem->rejection_reason }}</span>
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
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Buyer Information</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-sm uppercase">
                            {{ substr($stockOrder->buyer->business_name, 0, 2) }}
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-900">{{ $stockOrder->buyer->business_name }}</p>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ $stockOrder->buyer->bp_code }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Global Order Status</p>
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
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Order Notes</p>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 text-xs text-slate-600 leading-relaxed italic">
                        "{{ $stockOrder->notes }}"
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Bulk Allocate Modal -->
<div class="modal fade" id="allocateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3xl border-none shadow-2xl overflow-hidden">
            <form action="{{ route('admin.stock-order.allocate', $stockOrder->id) }}" method="POST">
                @csrf
                <div class="bg-indigo-600 p-8 text-white">
                    <h3 class="text-2xl font-black uppercase tracking-tight">Bulk Allocation</h3>
                    <p class="text-indigo-100 text-sm opacity-80">This will assign ALL items to one craftsman.</p>
                </div>
                <div class="p-8 space-y-6">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block">Select Craftsman</label>
                        <select name="craftsman_id" class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-sm font-bold focus:ring-2 focus:ring-indigo-500 appearance-none" required>
                            <option value="">-- Choose Craftsman --</option>
                            @foreach($craftsmen as $c)
                            <option value="{{ $c->id }}" {{ $stockOrder->craftsman_id == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->craftsman_code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="p-8 bg-slate-50 flex gap-3">
                    <button type="button" class="flex-1 bg-white border border-slate-200 text-slate-600 py-4 rounded-2xl font-black uppercase tracking-widest text-xs" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="flex-1 bg-indigo-600 text-white py-4 rounded-2xl font-black uppercase tracking-widest text-xs shadow-lg shadow-indigo-100">Confirm Bulk Allocation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Re-allocate Items Modal -->
<div class="modal fade" id="reallocateItemsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3xl border-none shadow-2xl overflow-hidden">
            <form id="reallocate-items-form" action="{{ route('admin.stock-order.allocate-items', $stockOrder->id) }}" method="POST">
                @csrf
                <div id="selected-items-hidden-inputs"></div>
                <div class="bg-indigo-900 p-8 text-white">
                    <h3 class="text-2xl font-black uppercase tracking-tight">Re-allocate Items</h3>
                    <p class="text-indigo-200 text-sm opacity-80">Assign selected items to a new craftsman.</p>
                </div>
                <div class="p-8 space-y-6">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block">New Craftsman</label>
                        <select name="craftsman_id" class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-sm font-bold focus:ring-2 focus:ring-indigo-500 appearance-none" required>
                            <option value="">-- Choose Craftsman --</option>
                            @foreach($craftsmen as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->craftsman_code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="p-8 bg-slate-50 flex gap-3">
                    <button type="button" class="flex-1 bg-white border border-slate-200 text-slate-600 py-4 rounded-2xl font-black uppercase tracking-widest text-xs" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="flex-1 bg-indigo-900 text-white py-4 rounded-2xl font-black uppercase tracking-widest text-xs shadow-lg shadow-indigo-800/20">Re-allocate Items</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const selectAll = document.getElementById('select-all-items');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const reallocateBtn = document.getElementById('reallocate-selected-btn');
    const hiddenInputsContainer = document.getElementById('selected-items-hidden-inputs');

    function updateReallocateBtn() {
        const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
        reallocateBtn.disabled = checkedCount === 0;
        
        // Update hidden inputs
        hiddenInputsContainer.innerHTML = '';
        document.querySelectorAll('.item-checkbox:checked').forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'item_ids[]';
            input.value = cb.value;
            hiddenInputsContainer.appendChild(input);
        });
    }

    selectAll.addEventListener('change', (e) => {
        itemCheckboxes.forEach(cb => cb.checked = e.target.checked);
        updateReallocateBtn();
    });

    itemCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateReallocateBtn);
    });
</script>
@endsection
