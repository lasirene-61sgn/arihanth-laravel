@extends('admin.layouts.app')

@section('title', 'Purchase Order Details')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 space-y-4 md:space-y-0 border-b pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Purchase Order Details</h1>
            <p class="text-sm text-gray-500">Manage and view specifics for order #{{ $purchaseOrder->purchase_order_code }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <div class="relative inline-block text-left" x-data="{ open: false }">
                <button @click="open = !open" type="button" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                    Share
                </button>
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 border ring-1 ring-black ring-opacity-5">
                    <div class="py-1">
                        <a href="{{ route('admin.purchase-order.print', $purchaseOrder) }}" target="_blank" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            Generate PDF
                        </a>
                        <hr class="my-1">
                        <a href="https://wa.me/?text=Purchase Order: {{ $purchaseOrder->purchase_order_code }}" target="_blank" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            WhatsApp
                        </a>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.purchase-order.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to List
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
            <h4 class="text-lg font-bold text-gray-800">{{ $purchaseOrder->purchase_order_code }}</h4>
            @if(!in_array($purchaseOrder->status, ['in_process', 'overdue', 'completed', 'for_approval']))
                <div>
                    <a href="{{ route('admin.purchase-order.edit', $purchaseOrder) }}" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">Edit Order</a>
                </div>
            @endif
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 gap-y-8 mb-8 bg-blue-50/50 p-4 rounded-lg">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Order Code</label>
                    <p class="text-gray-900 font-medium">{{ $purchaseOrder->purchase_order_code }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Due Date</label>
                    <p class="text-gray-900 font-medium">{{ $purchaseOrder->due_date ? $purchaseOrder->due_date->format('d M, Y') : 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Status</label>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 uppercase">
                        {{ $purchaseOrder->status }}
                    </span>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Created At</label>
                    <p class="text-gray-900 font-medium">{{ $purchaseOrder->created_at->format('d M, Y H:i') }}</p>
                </div>
                @if($purchaseOrder->creator_details['name'] !== 'System' && $purchaseOrder->creator_details['name'] !== 'N/A')
                  <div>
                      <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Created By</label>
                      @php 
                          $creator = $purchaseOrder->creator_details; 
                          $creatorCode = !empty($creator['user_code']) && $creator['user_code'] !== 'N/A' ? $creator['user_code'] : (!empty($creator['bp_code']) && $creator['bp_code'] !== 'N/A' ? $creator['bp_code'] : $creator['type']);
                      @endphp
                      <p class="text-gray-900 font-medium">{{ $creator['name'] }} <span class="text-[10px] bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded">{{ $creatorCode }}</span></p>
                  </div>
                  @endif
                @if($purchaseOrder->allocator_details['name'] !== 'Unknown User' && $purchaseOrder->allocator_details['name'] !== 'N/A')
                  <div>
                      <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Allocated By</label>
                      @php 
                          $allocator = $purchaseOrder->allocator_details; 
                          $allocatorCode = !empty($allocator['user_code']) && $allocator['user_code'] !== 'N/A' ? $allocator['user_code'] : (!empty($allocator['bp_code']) && $allocator['bp_code'] !== 'N/A' ? $allocator['bp_code'] : $allocator['type']);
                      @endphp
                      <p class="text-gray-900 font-medium">{{ $allocator['name'] }} <span class="text-[10px] bg-yellow-100 text-yellow-800 px-1.5 py-0.5 rounded">{{ $allocatorCode }}</span></p>
                  </div>
                  @endif
                @if($purchaseOrder->approver_details['name'] !== 'Unknown User' && $purchaseOrder->approver_details['name'] !== 'N/A')
                  <div>
                      <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Approved By</label>
                      @php 
                          $approver = $purchaseOrder->approver_details; 
                          $approverCode = !empty($approver['user_code']) && $approver['user_code'] !== 'N/A' ? $approver['user_code'] : (!empty($approver['bp_code']) && $approver['bp_code'] !== 'N/A' ? $approver['bp_code'] : $approver['type']);
                      @endphp
                      <p class="text-gray-900 font-medium">{{ $approver['name'] }} <span class="text-[10px] bg-green-100 text-green-800 px-1.5 py-0.5 rounded">{{ $approverCode }}</span></p>
                  </div>
                  @endif
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Completed Date</label>
                    <p class="text-gray-900 font-medium">
                        @if(strtolower($purchaseOrder->status) === 'completed')
                            <span class="text-green-600">{{ $purchaseOrder->updated_at ? $purchaseOrder->updated_at->format('d M, Y H:i') : 'N/A' }}</span>
                        @else
                            <span class="text-gray-400">Not Completed</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Order Tracking Timeline -->
            <div class="mt-8 mb-6 border border-gray-200 rounded-lg bg-white overflow-hidden shadow-sm">
                <div class="px-6 py-4 bg-gray-50 border-b flex items-center">
                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <h4 class="text-lg font-bold text-gray-800">Order Tracking</h4>
                </div>
                <div class="p-6">
                    <div class="relative border-l-2 border-gray-200 ml-3">
                        
                        <!-- Created -->
                        <div class="mb-8 ml-6 relative">
                            <span class="absolute -left-[33px] top-1 w-4 h-4 rounded-full bg-blue-500 ring-4 ring-white"></span>
                            <h3 class="font-semibold text-gray-900 text-sm">Order Created</h3>
                            @php 
                                $creator = $purchaseOrder->creator_details;
                                $creatorCode = !empty($creator['user_code']) && $creator['user_code'] !== 'N/A' ? $creator['user_code'] : (!empty($creator['bp_code']) && $creator['bp_code'] !== 'N/A' ? $creator['bp_code'] : $creator['type']);
                            @endphp
                            <p class="text-xs text-gray-500 mt-1">Created By: <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 text-[10px] font-medium">{{ $creatorCode }}</span> <span class="font-semibold text-gray-800">{{ $creator['name'] ?? 'N/A' }}</span></p>
                            <p class="text-xs text-gray-400 mt-1 flex items-center"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>{{ $purchaseOrder->created_at ? $purchaseOrder->created_at->timezone('Asia/Kolkata')->format('d M Y h:i A') : 'N/A' }}</p>
                        </div>

                        <!-- Target Due Date -->
                        <div class="mb-8 ml-6 relative">
                            <span class="absolute -left-[33px] top-1 w-4 h-4 rounded-full bg-yellow-400 ring-4 ring-white"></span>
                            <h3 class="font-semibold text-gray-900 text-sm">Target Due Date</h3>
                            <p class="text-xs text-gray-400 mt-1 flex items-center"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>{{ $purchaseOrder->due_date ? $purchaseOrder->due_date->format('d M Y') : 'Not Set' }}</p>
                        </div>

                        <!-- Allocated -->
                        @if($purchaseOrder->allocated_craftsman_code)
                        <div class="mb-8 ml-6 relative">
                            <span class="absolute -left-[33px] top-1 w-4 h-4 rounded-full bg-indigo-500 ring-4 ring-white"></span>
                            <h3 class="font-semibold text-gray-900 text-sm">Allocated to Craftsman</h3>
                            @php 
                                $allocator = $purchaseOrder->allocator_details;
                                $allocatorCode = !empty($allocator['user_code']) && $allocator['user_code'] !== 'N/A' ? $allocator['user_code'] : (!empty($allocator['bp_code']) && $allocator['bp_code'] !== 'N/A' ? $allocator['bp_code'] : $allocator['type']);
                            @endphp
                            <p class="text-xs text-gray-500 mt-1">Allocated By: <span class="px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-800 text-[10px] font-medium">{{ $allocatorCode }}</span> <span class="font-semibold text-gray-800">{{ $allocator['name'] ?? 'Admin' }}</span></p>
                            <p class="text-xs text-gray-500 mt-1">Allocated To: <span class="px-1.5 py-0.5 rounded bg-gray-200 text-gray-800 text-[10px] font-medium">{{ $purchaseOrder->craftsman->craftman_code ?? 'N/A' }}</span> <span class="font-semibold text-gray-800">{{ $purchaseOrder->craftsman->full_name ?? $purchaseOrder->craftsman->name ?? 'N/A' }}</span></p>
                            <p class="text-xs text-gray-400 mt-1 flex items-center"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>{{ $purchaseOrder->allocated_at ? \Carbon\Carbon::parse($purchaseOrder->allocated_at)->timezone('Asia/Kolkata')->format('d M Y h:i A') : 'N/A' }}</p>
                        </div>
                        @endif

                        <!-- Craftsman Due Date -->
                        @if($purchaseOrder->craftsman_due_date)
                        <div class="mb-8 ml-6 relative">
                            <span class="absolute -left-[33px] top-1 w-4 h-4 rounded-full bg-red-500 ring-4 ring-white"></span>
                            <h3 class="font-semibold text-gray-900 text-sm">Craftsman Due Date</h3>
                            <p class="text-xs text-gray-400 mt-1 flex items-center"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>{{ $purchaseOrder->craftsman_due_date->format('d M Y') }}</p>
                        </div>
                        @endif

                        <!-- Accepted by Craftsman -->
                        @if(in_array($purchaseOrder->craftsman_status, ['in_process', 'completed', 'accepted']) && $purchaseOrder->allocated_craftsman_code)
                        <div class="mb-8 ml-6 relative">
                            <span class="absolute -left-[33px] top-1 w-4 h-4 rounded-full bg-indigo-500 ring-4 ring-white"></span>
                            <h3 class="font-semibold text-gray-900 text-sm">Accepted by Craftsman</h3>
                            <p class="text-xs text-gray-500 mt-1">Accepted By: <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-800 text-[10px] font-medium">{{ $purchaseOrder->craftsman->craftman_code ?? 'N/A' }}</span> <span class="font-semibold text-gray-800">{{ $purchaseOrder->craftsman->full_name ?? $purchaseOrder->craftsman->name ?? 'N/A' }}</span></p>
                            <p class="text-xs text-gray-400 mt-1 flex items-center"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>{{ $purchaseOrder->craftsman_accepted_at ? \Carbon\Carbon::parse($purchaseOrder->craftsman_accepted_at)->timezone('Asia/Kolkata')->format('d M Y h:i A') : ($purchaseOrder->updated_at ? $purchaseOrder->updated_at->timezone('Asia/Kolkata')->format('d M Y h:i A') : 'N/A') }}</p>
                        </div>
                        @endif

                        <!-- Completed by Craftsman -->
                        @if(strtolower($purchaseOrder->craftsman_status) === 'completed' && $purchaseOrder->allocated_craftsman_code)
                        <div class="mb-8 ml-6 relative">
                            <span class="absolute -left-[33px] top-1 w-4 h-4 rounded-full bg-purple-500 ring-4 ring-white"></span>
                            <h3 class="font-semibold text-gray-900 text-sm">Completed by Craftsman</h3>
                            <p class="text-xs text-gray-500 mt-1">Completed By: <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-800 text-[10px] font-medium">{{ $purchaseOrder->craftsman->craftman_code ?? 'N/A' }}</span> <span class="font-semibold text-gray-800">{{ $purchaseOrder->craftsman->full_name ?? $purchaseOrder->craftsman->name ?? 'Craftsman' }}</span></p>
                            <p class="text-xs text-gray-400 mt-1 flex items-center"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>{{ $purchaseOrder->craftsman_completed_at ? \Carbon\Carbon::parse($purchaseOrder->craftsman_completed_at)->timezone('Asia/Kolkata')->format('d M Y h:i A') : ($purchaseOrder->updated_at ? $purchaseOrder->updated_at->timezone('Asia/Kolkata')->format('d M Y h:i A') : 'N/A') }}</p>
                        </div>
                        @endif

                        <!-- Final Approval by Admin/Superadmin -->
                        @if(strtolower($purchaseOrder->status) === 'completed' || strtolower($purchaseOrder->status) === 'approved')
                        <div class="ml-6 relative">
                            <span class="absolute -left-[33px] top-1 w-4 h-4 rounded-full bg-green-500 ring-4 ring-white"></span>
                            <h3 class="font-semibold text-green-600 text-sm">Order Approved & Completed</h3>
                            @php 
                                $approver = $purchaseOrder->approver_details;
                                $approverCode = !empty($approver['user_code']) && $approver['user_code'] !== 'N/A' ? $approver['user_code'] : (!empty($approver['bp_code']) && $approver['bp_code'] !== 'N/A' ? $approver['bp_code'] : $approver['type']);
                            @endphp
                            <p class="text-xs text-gray-500 mt-1">Approved By: <span class="px-1.5 py-0.5 rounded bg-green-100 text-green-800 text-[10px] font-medium">{{ $approverCode }}</span> <span class="font-semibold text-gray-800">{{ $approver['name'] ?? 'Admin' }}</span></p>
                            <p class="text-xs text-gray-400 mt-1 flex items-center"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>{{ $purchaseOrder->updated_at ? $purchaseOrder->updated_at->timezone('Asia/Kolkata')->format('d M Y h:i A') : 'N/A' }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto mb-8">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 border-b border-gray-200">
                            <th class="px-4 py-3 text-xs font-bold text-gray-600 uppercase">S.No</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-600 uppercase">Category</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-600 uppercase">Product Details</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-600 uppercase text-center">Design Code</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-600 uppercase">Grams & Qty</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-600 uppercase text-right">Total Weight</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-600 uppercase text-right">Size</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-600 uppercase text-center">Image</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-600 uppercase">Notes</th>
                            @if($purchaseOrder->status == 'in_process' || $purchaseOrder->craftsman_status == 'allocated')
                                <th class="px-4 py-3 text-xs font-bold text-gray-600 uppercase text-center">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php $grandTotalWeight = 0; @endphp
                        @foreach($itemsWithDetails as $index => $item)
                        @php $grandTotalWeight += (float)($item['total'] ?? 0); @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-4 text-sm text-gray-600">
                                @if($purchaseOrder->status == 'in_process' || $purchaseOrder->craftsman_status == 'allocated')
                                    <input type="checkbox" form="bulkCompleteItemsForm" name="selected_items[]" value="{{ $index }}" class="mr-2">
                                @endif
                                {{ $index + 1 }}
                            </td>
                            <td class="px-4 py-4">
                                {{-- Fixed: Explicitly looking for Category Name --}}
                                <span class="font-bold text-gray-800 block">{{ $item['category_name'] ?? ($item['category']->name ?? ($item['category'] ?? 'N/A')) }}</span>
                                <span class="text-[10px] text-gray-400 font-semibold uppercase tracking-tighter">Category</span>
                            </td>
                            <td class="px-4 py-4">
                                @if(isset($item['product']))
                                    <span class="font-medium text-gray-900 block">{{ $item['product']->product_name ?? 'Unknown' }}</span>
                                    {{-- Fixed: Explicitly looking for Subcategory Name --}}
                                    <span class="text-xs text-blue-600 font-medium">Sub: {{ $item['product']->subcategory->name ?? 'N/A' }}</span>
                                @else
                                    <span class="font-medium text-gray-900 block">{{ $item['product_name'] ?? 'Unknown' }}</span>
                                    <span class="text-xs text-blue-600 font-medium">Sub: {{ $item['subcategory_name'] ?? 'N/A' }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center text-sm font-mono bg-gray-50">{{ $item['product']->design_code ?? ($item['design_code'] ?? 'N/A') }}</td>
                            <td class="px-4 py-4 text-sm">
                                @if(isset($item['grams']) && is_array($item['grams']))
                                    @foreach($item['grams'] as $i => $gram)
                                        <div class="whitespace-nowrap">{{ $gram }}g × {{ $item['quantity'][$i] ?? 1 }} = <span class="font-bold">{{ number_format($item['individual_totals'][$i], 2) }}g</span></div>
                                    @endforeach
                                @else
                                    <div class="whitespace-nowrap">{{ $item['grams'] ?? 0 }}g × {{ $item['quantity'] ?? 0 }} = <span class="font-bold">{{ number_format(($item['grams'] ?? 0) * ($item['quantity'] ?? 0), 2) }}g</span></div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right font-bold text-gray-900">{{ number_format($item['total'] ?? 0, 2) }}g</td>
                            <td class="px-4 py-4 text-right text-sm text-gray-600">{{ $item['item_size'] ?? 'N/A' }}</td>
                            <td class="px-4 py-4 text-center">
                                @php
                                    $imagePath = !empty($item['image']) ? $item['image'] : null;
                                    $imageSrc = null;

                                    if ($imagePath) {
                                        $imageSrc = str_contains($imagePath, 'images/') ? asset($imagePath) : asset('storage/' . $imagePath);
                                    } else {
                                        if(isset($item['design']) && !empty($item['design']->image)) {
                                            $path = $item['design']->image;
                                            $imageSrc = str_starts_with($path, 'storage/') || str_starts_with($path, 'images/') ? asset($path) : asset('storage/' . $path);
                                        } elseif(isset($item['product']) && $item['product']->images && count($item['product']->images) > 0) {
                                            $path = $item['product']->images[0]->path;
                                            $imageSrc = str_starts_with($path, 'storage/') || str_starts_with($path, 'images/') ? asset($path) : asset('storage/' . $path);
                                        }
                                    }
                                @endphp
                                @if($imageSrc)
                                    <img src="{{ $imageSrc }}" class="w-16 h-16 object-cover rounded border mx-auto hover:scale-110 transition cursor-pointer" onclick="showImagePreview(this.src)" alt="Item Image">
                                @else
                                    <span class="text-gray-300 italic text-xs">No Image</span>
                                @endif
                            </td>
                            
                            <td class="px-4 py-4 text-xs text-gray-500 max-w-[150px] truncate" title="{{ $item['item_notes'] ?? '' }}">{{ $item['item_notes'] ?? '-' }}</td>
                            @if($purchaseOrder->status == 'in_process' || $purchaseOrder->craftsman_status == 'allocated')
                                <td class="px-4 py-4 text-center">
                                    <form method="POST" action="{{ route('admin.purchase-order.complete-items', $purchaseOrder) }}">
                                        @csrf
                                        <input type="hidden" name="selected_items[]" value="{{ $index }}">
                                        <button type="submit" class="inline-flex items-center px-3 py-1 bg-green-500 text-white rounded text-xs font-bold hover:bg-green-600 transition" onclick="return confirm('Mark this item as completed?')">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Complete
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-800 text-white">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-right font-bold uppercase tracking-widest text-xs">Grand Total Weight:</td>
                            <td class="px-4 py-3 text-right font-bold text-lg">{{ number_format($grandTotalWeight, 2) }}g</td>
                            <td colspan="{{ ($purchaseOrder->status == 'in_process' || $purchaseOrder->craftsman_status == 'allocated') ? 4 : 3 }}"></td>
                        </tr>
                    </tfoot>
                    
                </table>
            </div>

            @if(($purchaseOrder->status == 'in_process' || $purchaseOrder->craftsman_status == 'allocated') && count($itemsWithDetails) > 0)
                <div class="mt-3 flex justify-end">
                    <form id="bulkCompleteItemsForm" method="POST" action="{{ route('admin.purchase-order.complete-items', $purchaseOrder) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg font-bold hover:bg-green-700 transition" onclick="return confirm('Mark selected items as completed?')">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Complete Selected Items
                        </button>
                    </form>
                </div>
            @endif

            @if($purchaseOrder->notes)
            <div class="mt-6 p-4 bg-gray-50 border-l-4 border-gray-400 rounded">
                <label class="text-xs font-bold text-gray-500 uppercase block mb-1">General Order Notes:</label>
                <p class="text-gray-700 leading-relaxed">{{ $purchaseOrder->notes }}</p>
            </div>
            @endif

            @if(isset($rejectedItemsWithDetails) && count($rejectedItemsWithDetails) > 0)
            <div class="mt-10 border-2 border-amber-100 rounded-xl overflow-hidden">
                <div class="bg-amber-50 px-6 py-4 flex items-center border-b border-amber-100">
                    <svg class="w-5 h-5 text-amber-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    <h5 class="font-bold text-amber-800">Rejected Items</h5>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-amber-50/50">
                            <tr>
                                <th class="px-4 py-2 text-xs font-bold text-amber-700 uppercase">S.No</th>
                                <th class="px-4 py-2 text-xs font-bold text-amber-700 uppercase">Product Details</th>
                                <th class="px-4 py-2 text-xs font-bold text-amber-700 uppercase">Weights</th>
                                <th class="px-4 py-2 text-xs font-bold text-amber-700 uppercase">Size</th>
                                <th class="px-4 py-2 text-xs font-bold text-amber-700 uppercase text-right">Row Total</th>
                                <th class="px-4 py-2 text-xs font-bold text-amber-700 uppercase text-center">Image</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-100">
                            @foreach($rejectedItemsWithDetails as $index => $item)
                            <tr>
                                <td class="px-4 py-4 text-sm text-amber-900">{{ $index + 1 }}</td>
                                <td class="px-4 py-4">
                                    <div class="font-bold text-amber-900">{{ $item['category_name'] ?? ($item['category']->name ?? ($item['category'] ?? 'N/A')) }}</div>
                                    @if(isset($item['product']))
                                        <div class="text-xs text-amber-700">{{ $item['product']->product_name ?? 'N/A' }}</div>
                                    @else
                                        <div class="text-xs text-amber-700">{{ $item['product_name'] ?? 'N/A' }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-xs text-amber-800">
                                    @php
                                        $grams = is_string($item['grams']) ? json_decode($item['grams'], true) : $item['grams'];
                                        $qtys = is_string($item['quantity']) ? json_decode($item['quantity'], true) : $item['quantity'];
                                    @endphp
                                    @if(is_array($grams))
                                        @foreach($grams as $i => $g)
                                            <div>{{ $g }}g × {{ $qtys[$i] ?? 1 }} nos</div>
                                        @endforeach
                                    @else
                                        {{ $grams }}g × {{ $qtys }} nos
                                    @endif
                                </td>
                                <td>{{ $item['item_size'] ?? 'N/A' }}</td>
                                <td class="px-4 py-4 text-right font-bold text-amber-900">{{ number_format($item['total'], 2) }}g</td>
                                <td class="px-4 py-4 text-center">
                                    @if(isset($item['image']) && $item['image'])
                                        <img src="{{ asset($item['image']) }}" class="w-12 h-12 rounded opacity-75 grayscale hover:grayscale-0 transition cursor-pointer mx-auto" onclick="showImagePreview(this.src)" alt="Rejected Image">
                                    @elseif(isset($item['design']) && $item['design'] && $item['design']->image)
                                        @php
                                            $path = $item['design']->image;
                                            $imageSrc = str_starts_with($path, 'storage/') || str_starts_with($path, 'images/') ? asset($path) : asset('storage/' . $path);
                                        @endphp
                                        <img src="{{ $imageSrc }}" class="w-12 h-12 rounded opacity-75 grayscale hover:grayscale-0 transition cursor-pointer mx-auto" onclick="showImagePreview(this.src)" alt="Rejected Design Image">
                                    @elseif(isset($item['product']) && $item['product']->images && count($item['product']->images) > 0)
                                        @php
                                            $path = $item['product']->images[0]->path;
                                            $imageSrc = str_starts_with($path, 'storage/') || str_starts_with($path, 'images/') ? asset($path) : asset('storage/' . $path);
                                        @endphp
                                        <img src="{{ $imageSrc }}" class="w-12 h-12 rounded opacity-75 grayscale hover:grayscale-0 transition cursor-pointer mx-auto" onclick="showImagePreview(this.src)" alt="Rejected Product Image">
                                    @else
                                        <span class="text-amber-300 text-[10px]">No Image</span>
                                    @endif
                                </td>
                            </tr>
                            @if(!empty($item['item_notes']))
                            <tr class="bg-amber-50/20">
                                <td colspan="5" class="px-4 py-2 text-xs italic text-red-600 border-t border-amber-50">
                                    <strong>Rejection Note:</strong> {{ $item['item_notes'] }}
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <div class="mt-10 pt-6 border-t flex flex-col md:flex-row justify-between items-center gap-4">
                <a href="{{ route('admin.purchase-order.index') }}" class="px-6 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-100 transition w-full md:w-auto text-center">
                    Back to List
                </a>
                <form action="{{ route('admin.purchase-order.destroy', $purchaseOrder) }}" method="POST" onsubmit="return confirm('Permanently delete this order?');" class="w-full md:w-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-6 py-2 bg-white text-red-500 border border-red-500 rounded-lg hover:bg-red-50 transition w-full">
                        Delete Order
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div id="imagePreviewModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.8);" onclick="this.style.display='none'">
  <span style="position:absolute; top:20px; right:35px; color:#fff; font-size:40px; font-weight:bold; cursor:pointer;">&times;</span>
  <img id="previewImage" style="margin:auto; display:block; max-width:90%; max-height:90%; margin-top:5vh;">
</div>

@endsection

@section('scripts')
<script>
function showImagePreview(src) {
    document.getElementById('previewImage').src = src;
    document.getElementById('imagePreviewModal').style.display = 'block';
}
</script>
@endsection