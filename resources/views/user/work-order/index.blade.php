@extends('user.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <h4 class="text-2xl font-black text-slate-800 tracking-tight">Work Orders</h4>
            <nav class="flex mt-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-2 text-xs font-bold uppercase tracking-wider">
                    <li><a href="{{ route('user.dashboard') }}" class="text-slate-400 hover:text-indigo-600 transition-colors">Dashboard</a></li>
                    <li class="flex items-center text-indigo-600">
                        <i class="bi bi-chevron-right mx-2 text-[10px]"></i>
                        Work Orders
                    </li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('user.work-order.create') }}" 
           class="inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition shadow-md group">
            <i class="bi bi-plus-circle me-2 transition-transform group-hover:scale-110"></i>
            Create Work Order
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 bg-slate-50/50 border-b border-slate-200">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div class="space-y-1.5">
                    <label for="category_filter" class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Category</label>
                    <select id="category_filter" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all shadow-sm" 
                            onchange="refreshSubcategories(this.value); applyFilters();">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_filter') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label for="subcategory_filter" class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Subcategory</label>
                    <select id="subcategory_filter" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all shadow-sm" 
                            onchange="applyFilters()">
                        <option value="">All Subcategories</option>
                        @foreach($subcategories as $subcategory)
                            <option value="{{ $subcategory->id }}" {{ request('subcategory_filter') == $subcategory->id ? 'selected' : '' }}>{{ $subcategory->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-xl flex justify-between items-center shadow-sm">
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                    <button type="button" class="text-emerald-500 hover:text-emerald-800" onclick="this.parentElement.remove()"><i class="bi bi-x-lg"></i></button>
                </div>
            @endif

            <div class="border-b border-slate-200 mb-6">
                <ul class="flex flex-wrap -mb-px text-xs font-black uppercase tracking-widest text-center" id="workOrderTabs" role="tablist">
                    <li class="me-2" role="presentation">
                        <button class="inline-block p-4 border-b-2 rounded-t-lg transition-all {{ request('tab') ? '' : 'border-indigo-600 text-indigo-600 active' }}" id="new-orders-tab" data-bs-toggle="tab" data-bs-target="#new-orders" type="button" role="tab">New Orders</button>
                    </li>
                    <li class="me-2" role="presentation">
                        <button class="inline-block p-4 border-b-2 border-transparent hover:text-slate-600 hover:border-slate-300 transition-all" id="allocated-orders-tab" data-bs-toggle="tab" data-bs-target="#allocated-orders" type="button" role="tab">Allocated</button>
                    </li>
                    <li class="me-2" role="presentation">
                        <button class="inline-block p-4 border-b-2 border-transparent hover:text-slate-600 hover:border-slate-300 transition-all" id="in-process-orders-tab" data-bs-toggle="tab" data-bs-target="#in-process-orders" type="button" role="tab">In Process</button>
                    </li>
                    <li class="me-2" role="presentation">
                        <button class="inline-block p-4 border-b-2 border-transparent hover:text-slate-600 hover:border-slate-300 transition-all" id="completed-orders-tab" data-bs-toggle="tab" data-bs-target="#completed-orders" type="button" role="tab">Completed</button>
                    </li>
                    <li class="me-2" role="presentation">
                        <button class="inline-block p-4 border-b-2 border-transparent hover:text-red-600 hover:border-red-300 transition-all" id="rejected-orders-tab" data-bs-toggle="tab" data-bs-target="#rejected-orders" type="button" role="tab">Rejected</button>
                    </li>
                </ul>
            </div>

            <div class="tab-content" id="workOrderTabsContent">
                @php
                    $tabs = [
                        'new-orders' => ['data' => $newOrders, 'active' => true],
                        'allocated-orders' => ['data' => $allocatedOrders, 'active' => false],
                        'in-process-orders' => ['data' => $inProcessOrders, 'active' => false],
                        'completed-orders' => ['data' => $completedOrders, 'active' => false],
                        'rejected-orders' => ['data' => $rejectedOrders, 'active' => false],
                    ];
                @endphp

                @foreach($tabs as $id => $tab)
                <div class="tab-pane fade {{ $tab['active'] ? 'show active' : '' }}" id="{{ $id }}" role="tabpanel">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[11px] font-black text-slate-400 uppercase tracking-[0.15em] border-b border-slate-100">
                                    <th class="px-4 py-4">WO Number</th>
                                    <th class="px-4 py-4">BP Code</th>
                                    <th class="px-4 py-4">Category</th>
                                    <th class="px-4 py-4">Type</th>
                                    <!-- <th class="px-4 py-4">Order Type</th> -->
                                    <th class="px-4 py-4 text-center">Qty</th>
                                    <!-- <th class="px-4 py-4">Status</th> -->
                                    <th class="px-4 py-4">Created At</th>
                                    <th class="px-4 py-4">Due Date</th>
                                    <!-- @if($id !== 'new-orders') <th class="px-4 py-4">Created By</th> @endif -->
                                    <th class="px-4 py-4 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm">
                                @forelse($tab['data'] as $workOrder)
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    <td class="px-4 py-4 font-black text-slate-800">{{ $workOrder->work_order_number }}</td>
                                    <td class="px-4 py-4 text-slate-600 font-medium">{{ $workOrder->bp_code }}</td>
                                    <td class="px-4 py-4 text-slate-600">{{ $workOrder->product_category }}</td>
                                    <td class="px-4 py-4 text-slate-500 italic">{{ $workOrder->type }}</td>
                                    <!-- <td class="px-4 py-4">
                                        @if($workOrder->order_type == 'Regular')
                                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 text-[10px] font-black rounded border border-indigo-100 uppercase">{{ $workOrder->order_type }}</span>
                                        @elseif($workOrder->order_type == 'Urgent')
                                            <span class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[10px] font-black rounded border border-amber-100 uppercase">{{ $workOrder->order_type }}</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-red-50 text-red-600 text-[10px] font-black rounded border border-red-100 uppercase">{{ $workOrder->order_type }}</span>
                                        @endif
                                    </td> -->
                                    <td class="px-4 py-4 text-center font-bold text-slate-800">{{ $workOrder->quantity }}</td>
                                    <!-- <td class="px-4 py-4">
                                        <span class="px-2 py-0.5 {{ $workOrder->open_close == 'Open' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200' }} text-[10px] font-black rounded border uppercase">
                                            {{ $workOrder->open_close }}
                                        </span>
                                    </td> -->
                                    <td class="px-4 py-4 text-slate-400 font-medium text-xs">{{ $workOrder->created_at->format('d M, Y') }}</td>
                                    <td class="px-4 py-4 text-slate-400 font-medium text-xs">{{ $workOrder->due_date->format('d M, Y') }}</td>
                                    
                                    <!-- @if($id !== 'new-orders')
                                    <td class="px-4 py-4">
                                        @if($workOrder->createdBy)
                                            <div class="flex flex-col">
                                                <span class="text-[10px] font-black text-indigo-500 leading-none mb-1">
                                                    {{ $workOrder->createdBy instanceof \App\Models\User ? ($workOrder->createdBy->bp_code ?? 'N/A') : class_basename($workOrder->createdBy) }}
                                                </span>
                                                <span class="text-[11px] text-slate-500">{{ $workOrder->createdBy->name ?? 'N/A' }}</span>
                                            </div>
                                        @else
                                            <span class="text-[10px] font-black text-slate-300 uppercase">System</span>
                                        @endif
                                    </td>
                                    @endif -->

                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-1.5 opacity-80 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('user.work-order.show', $workOrder) }}" 
                                               class="p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-indigo-600 hover:text-white transition shadow-sm border border-slate-200" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($id === 'new-orders' || $id === 'rejected-orders')
                                            <a href="{{ route('user.work-order.edit', $workOrder) }}" 
                                               class="p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-amber-500 hover:text-white transition shadow-sm border border-slate-200" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @endif
                                            <a href="{{ route('user.work-order.print', $workOrder) }}" 
                                               class="p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-teal-600 hover:text-white transition shadow-sm border border-slate-200" target="_blank" title="Print">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            @if($id === 'new-orders')
                                            <form action="{{ route('user.work-order.destroy', $workOrder) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this work order?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-red-600 hover:text-white transition shadow-sm border border-slate-200" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-20 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mb-3">
                                                <i class="bi bi-clipboard text-2xl"></i>
                                            </div>
                                            <p class="text-slate-400 font-bold text-sm italic">No work orders found in this section.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6 flex justify-end">
                        @if(method_exists($tab['data'], 'links'))
                            {{ $tab['data']->links() }}
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>


<script>
function refreshSubcategories(categoryId) {
    const subcategorySelect = document.getElementById('subcategory_filter');
    if (!subcategorySelect) return;

    subcategorySelect.innerHTML = '<option value="">All Subcategories</option>';
    if (!categoryId) {
        return;
    }
    
    fetch(`/user/product/get-subcategories?category_id=${categoryId}`)
        .then(response => response.json())
        .then(list => {
            list.forEach(sub => {
                const opt = document.createElement('option');
                opt.value = sub.id;
                opt.textContent = sub.name;
                subcategorySelect.appendChild(opt);
            });
        })
        .catch(error => {
            console.error('Error fetching subcategories:', error);
        });
}

function applyFilters() {
    const activeTab = document.querySelector('#workOrderTabs button.active').getAttribute('data-bs-target').substring(1);
    loadTabContent(activeTab);
}

document.addEventListener('DOMContentLoaded', function() {
    // Activate tab based on URL hash
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    
    if (tab) {
        const tabButton = document.querySelector(`[data-bs-target="#${tab}"]`);
        if (tabButton) {
            const tab = new bootstrap.Tab(tabButton);
            tab.show();
        }
    }
    
    // Add click event to tabs to update URL
    const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-bs-target').substring(1);
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
        });
    });
    
    // Enable AJAX loading for tabs
    const tabElements = document.querySelectorAll('#workOrderTabs button[data-bs-toggle="tab"]');
    tabElements.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (event) {
            const tabId = event.target.getAttribute('data-bs-target').substring(1);
            loadTabContent(tabId);
        });
    });
});

function loadTabContent(tabId) {
    const search = document.querySelector('input[name="search"]').value;
    const sortBy = document.getElementById('sort_by').value;
    const sortOrder = document.getElementById('sort_order').value;
    const perPage = document.getElementById('per_page').value;
    const categoryFilter = document.getElementById('category_filter').value;
    const subcategoryFilter = document.getElementById('subcategory_filter').value;
    
    fetch(`{{ route('user.work-order.load-orders') }}?tab=${tabId}&search=${encodeURIComponent(search)}&sort_by=${sortBy}&sort_order=${sortOrder}&per_page=${perPage}&category_filter=${categoryFilter}&subcategory_filter=${subcategoryFilter}`)
        .then(response => response.json())
        .then(data => {
            document.querySelector(`#${tabId} .table-responsive`).innerHTML = data.html;
            document.querySelector(`#${tabId} .d-flex.justify-content-end.mt-3`).innerHTML = data.pagination;
        })
        .catch(error => {
            console.error('Error loading tab content:', error);
        });
}
</script>
@endsection