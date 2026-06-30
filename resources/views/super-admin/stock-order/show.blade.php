@extends('super-admin.layouts.app')

@section('title', 'Order ' . $stockOrder->order_number)

@section('content')
<div class="tw-max-w-7xl tw-mx-auto tw-pb-20">
    <!-- Breadcrumbs & Header -->
    <div class="tw-mb-8 tw-flex tw-flex-col md:tw-flex-row md:tw-items-center tw-justify-between tw-gap-6">
        <div class="tw-flex tw-items-center tw-gap-5">
            <a href="{{ route('super-admin.stock-order.index') }}" class="tw-group tw-flex tw-items-center tw-justify-center tw-w-12 tw-h-12 tw-rounded-2xl tw-bg-white tw-border tw-border-slate-100 tw-text-slate-400 hover:tw-bg-slate-900 hover:tw-text-white tw-transition-all tw-duration-300 tw-shadow-sm">
                <i class="bi bi-arrow-left tw-text-xl group-hover:-tw-translate-x-1 tw-transition-transform"></i>
            </a>
            <div>
                <div class="tw-flex tw-items-center tw-gap-3 tw-mb-1">
                    <h1 class="tw-text-3xl tw-font-black tw-text-slate-900 tw-tracking-tight tw-uppercase tw-leading-none">{{ $stockOrder->order_number }}</h1>
                    @php
                        $statusColors = [
                            'Pending' => 'tw-bg-amber-100 tw-text-amber-700',
                            'Allocated' => 'tw-bg-blue-100 tw-text-blue-700',
                            'Completed' => 'tw-bg-emerald-100 tw-text-emerald-700',
                            'Cancelled' => 'tw-bg-red-100 tw-text-red-700',
                        ];
                    @endphp
                    <span class="tw-px-3 tw-py-1 tw-rounded-full tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest {{ $statusColors[$stockOrder->status] ?? 'tw-bg-slate-100 tw-text-slate-700' }}">
                        {{ $stockOrder->status }}
                    </span>
                </div>
                <p class="tw-text-slate-500 tw-text-sm tw-font-medium">Production workflow and item allocation</p>
            </div>
        </div>

        <div class="tw-flex tw-items-center tw-gap-3">
            <a href="{{ route('super-admin.stock-order.edit', $stockOrder->id) }}" 
                class="tw-px-6 tw-py-3 tw-bg-slate-100 tw-text-slate-600 tw-rounded-2xl tw-font-black tw-uppercase tw-tracking-widest tw-text-xs hover:tw-bg-slate-200 tw-transition-all tw-flex tw-items-center tw-gap-2">
                <i class="bi bi-pencil-square tw-text-lg"></i>
                Edit Order
            </a>
            <button id="reallocate-selected-btn" data-bs-toggle="modal" data-bs-target="#reallocateItemsModal" disabled 
                class="tw-px-6 tw-py-3 tw-bg-white tw-border-2 tw-border-indigo-600 tw-text-indigo-600 tw-rounded-2xl tw-font-black tw-uppercase tw-tracking-widest tw-text-xs hover:tw-bg-indigo-50 tw-transition-all disabled:tw-opacity-30 disabled:tw-pointer-events-none tw-flex tw-items-center tw-gap-2">
                <i class="bi bi-arrow-repeat tw-text-lg"></i>
                Re-allocate Selected
            </button>
            <button data-bs-toggle="modal" data-bs-target="#allocateModal" 
                class="tw-px-6 tw-py-3 tw-bg-indigo-600 tw-text-white tw-rounded-2xl tw-font-black tw-uppercase tw-tracking-widest tw-text-xs hover:tw-bg-indigo-700 tw-shadow-lg tw-shadow-indigo-100 tw-transition-all tw-flex tw-items-center tw-gap-2">
                <i class="bi bi-person-plus-fill tw-text-lg"></i>
                {{ $stockOrder->craftsman_id ? 'Global Re-allocate' : 'Full Allocation' }}
            </button>
        </div>
    </div>

    <!-- Production Progress -->
    <div class="tw-mb-10 tw-bg-white tw-p-8 tw-rounded-[2rem] tw-border tw-border-slate-100 tw-shadow-sm tw-flex tw-flex-col md:tw-flex-row tw-items-center tw-gap-8 tw-relative tw-overflow-hidden">
        <div class="tw-absolute tw-right-0 tw-top-0 tw-w-32 tw-h-32 tw-bg-indigo-50/50 tw-rounded-full -tw-translate-y-1/2 tw-translate-x-1/2"></div>
        
        <div class="tw-w-full md:tw-w-auto tw-text-center md:tw-text-left">
            <p class="tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-1">Production Progress</p>
            @php
                $total = $stockOrder->items->count();
                $completed = $stockOrder->items->where('status', 'Completed')->count();
                $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
            @endphp
            <h2 class="tw-text-4xl tw-font-black tw-text-slate-900 tw-leading-none">{{ $percent }}%</h2>
        </div>

        <div class="tw-flex-1 tw-w-full tw-space-y-4">
            <div class="tw-h-3 tw-w-full tw-bg-slate-50 tw-rounded-full tw-overflow-hidden tw-border tw-border-slate-100">
                <div class="tw-h-full tw-bg-gradient-to-r tw-from-indigo-500 tw-to-indigo-600 tw-rounded-full tw-transition-all tw-duration-1000 tw-shadow-[0_0_15px_rgba(79,70,229,0.3)]" style="width: {{ $percent }}%"></div>
            </div>
            <div class="tw-flex tw-justify-between tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest">
                <span>Production Started</span>
                <span>{{ $completed }} of {{ $total }} Units Finished</span>
            </div>
        </div>
    </div>

    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-10">
        <!-- Main Content: Items -->
        <div class="lg:tw-col-span-2 tw-space-y-8">
            <div class="tw-bg-white tw-rounded-[2.5rem] tw-shadow-xl tw-shadow-slate-200/40 tw-border tw-border-slate-100 tw-overflow-hidden">
                <div class="tw-px-8 tw-py-6 tw-bg-slate-50/50 tw-border-b tw-border-slate-100 tw-flex tw-justify-between tw-items-center">
                    <div class="tw-flex tw-items-center tw-gap-4">
                        <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-indigo-600 tw-text-white tw-flex tw-items-center tw-justify-center tw-shadow-lg tw-shadow-indigo-100">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div>
                            <h3 class="tw-text-sm tw-font-black tw-text-slate-900 tw-uppercase tw-tracking-wider">Production Batch</h3>
                            <p class="tw-text-[10px] tw-text-slate-400 tw-font-bold tw-uppercase tw-tracking-widest">{{ $stockOrder->items->count() }} Designs Loaded</p>
                        </div>
                    </div>
                    <label class="tw-flex tw-items-center tw-gap-3 tw-cursor-pointer tw-group">
                        <input type="checkbox" id="select-all-items" class="tw-w-5 tw-h-5 tw-rounded-lg tw-border-slate-200 tw-text-indigo-600 focus:tw-ring-indigo-500 tw-transition-all">
                        <span class="tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest group-hover:tw-text-slate-900 tw-transition-colors">Select All</span>
                    </label>
                </div>

                <div class="tw-divide-y tw-divide-slate-50">
                    @foreach($stockOrder->items->groupBy('design_code') as $designCode => $items)
                    @php $firstItem = $items->first(); @endphp
                    <div class="tw-p-8 tw-group hover:tw-bg-slate-50/50 tw-transition-all tw-duration-300">
                        <div class="tw-flex tw-gap-8">
                            <!-- Image -->
                            <div class="tw-w-28 tw-h-28 tw-rounded-3xl tw-bg-white tw-border tw-border-slate-100 tw-overflow-hidden tw-shrink-0 tw-shadow-sm tw-p-2 group-hover:tw-shadow-md tw-transition-all">
                                @if($firstItem->image_path)
                                    <img src="{{ asset('storage/' . $firstItem->image_path) }}" class="tw-w-full tw-h-full tw-object-contain tw-cursor-pointer" onclick="window.openUniversalPreview('{{ asset('storage/' . $firstItem->image_path) }}', 'image')">
                                @else
                                    <div class="tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center tw-text-slate-200 tw-bg-slate-50">
                                        <i class="bi bi-image tw-text-3xl"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="tw-flex-1 tw-min-w-0">
                                <!-- Header -->
                                <div class="tw-flex tw-justify-between tw-items-start tw-mb-4">
                                    <div>
                                        <h4 class="tw-text-xl tw-font-black tw-text-slate-900 tw-uppercase tw-tracking-tight tw-mb-1">{{ $designCode }}</h4>
                                        <div class="tw-flex tw-items-center tw-gap-2">
                                            <span class="tw-text-[10px] tw-font-black tw-text-slate-400 tw-bg-slate-100 tw-px-2 tw-py-0.5 tw-rounded tw-uppercase tw-tracking-widest">{{ $firstItem->category_name }}</span>
                                            <span class="tw-text-[10px] tw-font-black tw-text-indigo-400 tw-bg-indigo-50 tw-px-2 tw-py-0.5 tw-rounded tw-uppercase tw-tracking-widest">{{ $items->count() }} Variants</span>
                                        </div>
                                    </div>
                                    @if($firstItem->craftsman)
                                        <div class="tw-text-right">
                                            <p class="tw-text-[9px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-1">Production Lead</p>
                                            <div class="tw-flex tw-items-center tw-gap-2 tw-justify-end">
                                                <span class="tw-text-xs tw-font-black tw-text-indigo-600 tw-underline tw-underline-offset-4 tw-decoration-indigo-200">{{ $firstItem->craftsman->name }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Variants Table -->
                                <div class="tw-space-y-2">
                                    <div class="tw-grid tw-grid-cols-12 tw-gap-4 tw-px-4 tw-py-2 tw-bg-slate-100/50 tw-rounded-xl tw-text-[9px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest">
                                        <div class="tw-col-span-1">#</div>
                                        <div class="tw-col-span-2">Quantity</div>
                                        <div class="tw-col-span-2">Grams</div>
                                        <div class="tw-col-span-2">Total</div>
                                        <div class="tw-col-span-2">Status</div>
                                        <div class="tw-col-span-3 tw-text-right">Action</div>
                                    </div>
                                    @foreach($items as $idx => $item)
                                    <div class="tw-grid tw-grid-cols-12 tw-gap-4 tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-100 tw-rounded-xl tw-items-center hover:tw-border-indigo-200 tw-transition-colors">
                                        <div class="tw-col-span-1">
                                            <input type="checkbox" name="selected_items[]" value="{{ $item->id }}" 
                                                class="item-checkbox tw-w-4 tw-h-4 tw-rounded tw-border-slate-200 tw-text-indigo-600 focus:tw-ring-indigo-500 tw-transition-all">
                                        </div>
                                        <div class="tw-col-span-2 tw-text-sm tw-font-black tw-text-slate-900">{{ $item->quantity }} <span class="tw-text-[9px] tw-text-slate-400">pcs</span></div>
                                        <div class="tw-col-span-2 tw-text-sm tw-font-black tw-text-slate-900">{{ number_format($item->grams, 3) }}g</div>
                                        <div class="tw-col-span-2 tw-text-sm tw-font-black tw-text-indigo-600">{{ number_format($item->quantity * $item->grams, 3) }}g</div>
                                        <div class="tw-col-span-2">
                                            @php
                                                $itemColors = [
                                                    'Pending' => 'tw-text-amber-500 tw-bg-amber-50',
                                                    'Accepted' => 'tw-text-blue-500 tw-bg-blue-50',
                                                    'Finished' => 'tw-text-indigo-500 tw-bg-indigo-50',
                                                    'Completed' => 'tw-text-emerald-500 tw-bg-emerald-50',
                                                    'Rejected' => 'tw-text-red-500 tw-bg-red-50',
                                                ];
                                            @endphp
                                            <span class="tw-text-[8px] tw-font-black tw-px-2 tw-py-0.5 tw-rounded tw-uppercase tw-tracking-widest {{ $itemColors[$item->status] ?? 'tw-text-slate-500 tw-bg-slate-50' }}">
                                                {{ $item->status === 'Finished' ? 'Ready' : $item->status }}
                                            </span>
                                        </div>
                                        <div class="tw-col-span-3 tw-text-right">
                                            @if($item->status !== 'Completed')
                                            <form action="{{ route('super-admin.stock-order.item-status', [$stockOrder->id, $item->id]) }}" method="POST" class="tw-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="Completed">
                                                <button type="submit" class="tw-bg-emerald-600 tw-text-white tw-px-2 tw-py-1 tw-rounded tw-text-[8px] tw-font-black tw-uppercase tw-tracking-widest hover:tw-bg-emerald-700 tw-transition-all">
                                                    Complete
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @if($firstItem->item_notes)
                                <div class="tw-mt-4 tw-p-3 tw-bg-slate-50 tw-rounded-xl tw-border tw-border-slate-100">
                                    <p class="tw-text-[9px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-1">Design Notes</p>
                                    <p class="tw-text-[11px] tw-text-slate-600 tw-italic">"{{ $firstItem->item_notes }}"</p>
                                </div>
                                @endif

                                @php
                                    $rejectedItem = $items->firstWhere('status', 'Rejected');
                                @endphp
                                @if($rejectedItem)
                                    <div class="tw-mt-4 tw-p-4 tw-bg-red-50 tw-border tw-border-red-100 tw-rounded-2xl tw-flex tw-items-start tw-gap-3">
                                        <div class="tw-w-8 tw-h-8 tw-rounded-full tw-bg-red-100 tw-text-red-600 tw-flex tw-items-center tw-justify-center tw-shrink-0">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </div>
                                        <div>
                                            <p class="tw-text-[10px] tw-font-black tw-text-red-400 tw-uppercase tw-tracking-widest tw-leading-none tw-mb-1">Rejection Reason</p>
                                            <p class="tw-text-xs tw-font-bold tw-text-red-700">{{ $rejectedItem->rejection_reason }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar Section -->
        <div class="tw-space-y-8">
            <!-- Order Overview -->
            <div class="tw-bg-white tw-rounded-[2.5rem] tw-shadow-xl tw-shadow-slate-200/40 tw-border tw-border-slate-100 tw-p-8 tw-sticky tw-top-8">
                <h3 class="tw-text-sm tw-font-black tw-text-slate-900 tw-uppercase tw-tracking-wider tw-mb-8 tw-pb-4 tw-border-b tw-border-slate-50 tw-flex tw-items-center tw-gap-2">
                    <i class="bi bi-info-circle tw-text-indigo-500"></i>
                    Fulfillment Insight
                </h3>
                
                <div class="tw-space-y-8">
                    <div>
                        <p class="tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-3">Origin Buyer</p>
                        <div class="tw-flex tw-items-center tw-gap-4 tw-bg-slate-50 tw-p-4 tw-rounded-3xl tw-border tw-border-slate-100">
                            <div class="tw-w-12 tw-h-12 tw-rounded-2xl tw-bg-slate-900 tw-text-white tw-flex tw-items-center tw-justify-center tw-font-black tw-text-sm tw-uppercase tw-shadow-lg tw-shadow-slate-200">
                                {{ substr($stockOrder->buyer->business_name, 0, 2) }}
                            </div>
                            <div>
                                <p class="tw-text-sm tw-font-black tw-text-slate-900 tw-leading-tight">{{ $stockOrder->buyer->business_name }}</p>
                                <p class="tw-text-[10px] tw-text-slate-400 tw-font-bold tw-uppercase tw-tracking-widest tw-mt-1">{{ $stockOrder->buyer->bp_code }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="tw-grid tw-grid-cols-2 tw-gap-4">
                        <div class="tw-bg-indigo-50/50 tw-p-4 tw-rounded-3xl tw-border tw-border-indigo-100">
                            <p class="tw-text-[9px] tw-font-black tw-text-indigo-400 tw-uppercase tw-tracking-widest tw-mb-1">Total Weight</p>
                            <p class="tw-text-xl tw-font-black tw-text-indigo-600 tw-tracking-tight tw-leading-none">{{ number_format($stockOrder->items->sum(fn($i) => $i->quantity * $i->grams), 3) }}g</p>
                        </div>
                        <div class="tw-bg-slate-50 tw-p-4 tw-rounded-3xl tw-border tw-border-slate-100">
                            <p class="tw-text-[9px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-1">Total Pieces</p>
                            <p class="tw-text-xl tw-font-black tw-text-slate-900 tw-tracking-tight tw-leading-none">{{ $stockOrder->items->sum('quantity') }} pcs</p>
                        </div>
                    </div>

                    @if($stockOrder->notes)
                    <div>
                        <p class="tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-3">Global Order Instructions</p>
                        <div class="tw-bg-amber-50/50 tw-p-5 tw-rounded-[2rem] tw-border tw-border-amber-100 tw-text-xs tw-text-amber-800 tw-leading-relaxed tw-italic">
                            <i class="bi bi-chat-left-text-fill tw-text-amber-300 tw-mr-2"></i>
                            "{{ $stockOrder->notes }}"
                        </div>
                    </div>
                    @endif

                    <div class="tw-pt-6">
                        <button data-bs-toggle="modal" data-bs-target="#allocateModal" class="tw-w-full tw-bg-slate-900 tw-text-white tw-py-4 tw-rounded-2xl tw-font-black tw-uppercase tw-tracking-widest tw-text-xs hover:tw-bg-indigo-600 tw-transition-all tw-shadow-xl tw-shadow-slate-200 tw-group">
                            Assign Production Lead
                            <i class="bi bi-chevron-right tw-ml-2 group-hover:tw-translate-x-1 tw-transition-transform"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals with Premium Styling -->
<!-- Bulk Allocate Modal -->
<div class="modal fade" id="allocateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content tw-rounded-[2.5rem] tw-border-none tw-shadow-2xl tw-overflow-hidden">
            <form action="{{ route('super-admin.stock-order.allocate', $stockOrder->id) }}" method="POST">
                @csrf
                <div class="tw-bg-indigo-600 tw-p-10 tw-text-white tw-text-center tw-relative tw-overflow-hidden">
                    <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-br tw-from-indigo-500 tw-to-indigo-700"></div>
                    <div class="tw-relative tw-z-10">
                        <div class="tw-w-16 tw-h-16 tw-bg-white/20 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4 tw-backdrop-blur-md">
                            <i class="bi bi-people-fill tw-text-3xl tw-text-white"></i>
                        </div>
                        <h3 class="tw-text-2xl tw-font-black tw-uppercase tw-tracking-tight tw-mb-2">Global Allocation</h3>
                        <p class="tw-text-indigo-100 tw-text-sm tw-opacity-80 tw-max-w-[200px] tw-mx-auto">This will assign ALL batch items to a single craftsman.</p>
                    </div>
                </div>
                <div class="tw-p-10 tw-space-y-8">
                    <div>
                        <label class="tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-4 tw-block">Choose Production Craftsman</label>
                        <div class="tw-relative tw-group">
                            <select name="craftsman_id" class="tw-w-full tw-bg-slate-50 tw-border-2 tw-border-slate-100 tw-rounded-2xl tw-px-6 tw-py-4 tw-text-sm tw-font-black focus:tw-ring-4 focus:tw-ring-indigo-500/10 focus:tw-border-indigo-500 tw-appearance-none tw-transition-all tw-outline-none" required>
                                <option value="">Select Craftsman...</option>
                                @foreach($craftsmen as $c)
                                <option value="{{ $c->id }}" {{ $stockOrder->craftsman_id == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->craftsman_code }})</option>
                                @endforeach
                            </select>
                            <div class="tw-absolute tw-right-6 tw-top-1/2 -tw-translate-y-1/2 tw-pointer-events-none tw-text-slate-400">
                                <i class="bi bi-chevron-down"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tw-p-8 tw-bg-slate-50 tw-flex tw-gap-4">
                    <button type="button" class="tw-flex-1 tw-bg-white tw-border-2 tw-border-slate-200 tw-text-slate-500 tw-py-4 tw-rounded-2xl tw-font-black tw-uppercase tw-tracking-widest tw-text-[10px] hover:tw-bg-slate-100 tw-transition-all" data-bs-dismiss="modal">Dismiss</button>
                    <button type="submit" class="tw-flex-1 tw-bg-indigo-600 tw-text-white tw-py-4 tw-rounded-2xl tw-font-black tw-uppercase tw-tracking-widest tw-text-[10px] tw-shadow-lg tw-shadow-indigo-100 hover:tw-bg-indigo-700 tw-transition-all">Apply Allocation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Re-allocate Items Modal -->
<div class="modal fade" id="reallocateItemsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content tw-rounded-[2.5rem] tw-border-none tw-shadow-2xl tw-overflow-hidden">
            <form id="reallocate-items-form" action="{{ route('super-admin.stock-order.allocate-items', $stockOrder->id) }}" method="POST">
                @csrf
                <div id="selected-items-hidden-inputs"></div>
                <div class="tw-bg-slate-900 tw-p-10 tw-text-white tw-text-center tw-relative tw-overflow-hidden">
                    <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-br tw-from-slate-800 tw-to-slate-950"></div>
                    <div class="tw-relative tw-z-10">
                        <div class="tw-w-16 tw-h-16 tw-bg-white/10 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4 tw-backdrop-blur-md">
                            <i class="bi bi-shuffle tw-text-3xl tw-text-white"></i>
                        </div>
                        <h3 class="tw-text-2xl tw-font-black tw-uppercase tw-tracking-tight tw-mb-2">Item Re-allocation</h3>
                        <p class="tw-text-slate-400 tw-text-sm tw-opacity-80 tw-max-w-[200px] tw-mx-auto">Redirect selected production units to a different craftsman.</p>
                    </div>
                </div>
                <div class="tw-p-10 tw-space-y-8">
                    <div>
                        <label class="tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-4 tw-block">New Production Lead</label>
                        <div class="tw-relative tw-group">
                            <select name="craftsman_id" class="tw-w-full tw-bg-slate-50 tw-border-2 tw-border-slate-100 tw-rounded-2xl tw-px-6 tw-py-4 tw-text-sm tw-font-black focus:tw-ring-4 focus:tw-ring-indigo-500/10 focus:tw-border-indigo-500 tw-appearance-none tw-transition-all tw-outline-none" required>
                                <option value="">Select New Lead...</option>
                                @foreach($craftsmen as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->craftsman_code }})</option>
                                @endforeach
                            </select>
                            <div class="tw-absolute tw-right-6 tw-top-1/2 -tw-translate-y-1/2 tw-pointer-events-none tw-text-slate-400">
                                <i class="bi bi-chevron-down"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tw-p-8 tw-bg-slate-50 tw-flex tw-gap-4">
                    <button type="button" class="tw-flex-1 tw-bg-white tw-border-2 tw-border-slate-200 tw-text-slate-500 tw-py-4 tw-rounded-2xl tw-font-black tw-uppercase tw-tracking-widest tw-text-[10px] hover:tw-bg-slate-100 tw-transition-all" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="tw-flex-1 tw-bg-slate-900 tw-text-white tw-py-4 tw-rounded-2xl tw-font-black tw-uppercase tw-tracking-widest tw-text-[10px] tw-shadow-lg tw-shadow-slate-200 hover:tw-bg-slate-800 tw-transition-all">Update Allocation</button>
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

