@extends('buyer.layouts.app')

@section('styles')
<style>
    /* Premium Pagination Styling for Buyer (Purple theme) */
    nav[role="navigation"] {
        margin-top: 2.5rem;
    }

    .flex.shadow-sm.rounded-md {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        gap: 0.4rem;
    }

    /* Page Numbers */
    nav[role="navigation"] a,
    nav[role="navigation"] span[aria-current="page"] span,
    nav[role="navigation"] span[aria-disabled="true"] span {
        border-radius: 8px !important;
        border: 1px solid #e2e8f0 !important;
        margin-left: 0 !important;
        padding: 0.6rem 1.1rem !important;
        font-weight: 600 !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    /* Active Page */
    nav[role="navigation"] span[aria-current="page"] span {
        background-color: #7c3aed !important;
        border-color: #7c3aed !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3) !important;
    }

    /* Hover state */
    nav[role="navigation"] a:hover {
        background-color: #f5f3ff !important;
        color: #7c3aed !important;
        border-color: #c4b5fd !important;
        transform: translateY(-2px);
    }

    /* Navigation Arrows (Big > <) */
    nav[role="navigation"] a[rel="prev"],
    nav[role="navigation"] a[rel="next"],
    nav[role="navigation"] span[aria-disabled="true"] {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    nav[role="navigation"] svg {
        width: 1.8rem !important;
        height: 1.8rem !important;
        stroke-width: 3 !important;
    }

    /* Mobile adjustments */
    @media (max-width: 640px) {
        nav[role="navigation"] .flex.justify-between.sm\:hidden {
            gap: 1rem;
        }

        nav[role="navigation"] .flex.justify-between.sm\:hidden a,
        nav[role="navigation"] .flex.justify-between.sm\:hidden span {
            flex: 1;
            text-align: center;
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Work Orders</h1>
            <nav class="flex text-sm text-slate-500 mt-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li><a href="{{ route('buyer.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a></li>
                    <li class="flex items-center">
                        <i class="bi bi-chevron-right text-[10px] mx-2"></i>
                        <span class="font-medium text-slate-700">Work Orders</span>
                    </li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('buyer.work-order.create') }}" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all duration-200">
                <i class="bi bi-plus-lg mr-2"></i> Create Work Order
            </a>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h2 class="text-lg font-bold text-slate-800">Work Order Management</h2>
        </div>

        <div class="p-6">
            @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg flex items-center justify-between animate-in fade-in slide-in-from-top-2">
                <div class="flex items-center">
                    <i class="bi bi-check-circle-fill mr-3 text-emerald-500"></i>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-600">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>
            @endif

            <!-- Filters and Search Section -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-1">
                    <form method="GET" class="flex">
                        <input type="hidden" name="tab" value="{{ request('tab', 'new-orders') }}">
                        <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                        <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                        <input type="hidden" name="category_filter" value="{{ request('category_filter') }}">
                        <input type="hidden" name="subcategory_filter" value="{{ request('subcategory_filter') }}">
                        <div class="relative w-full">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search WO#, Product..." class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 pl-3 pr-10 py-2">
                            <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        <a href="{{ route('buyer.work-order.index', ['tab' => request('tab', 'new-orders')]) }}" class="ml-2 p-2 text-slate-400 hover:text-slate-600 border border-slate-200 rounded-lg" title="Clear Filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </form>
                </div>
                <div>
                    <form method="GET">
                        <input type="hidden" name="tab" value="{{ request('tab', 'new-orders') }}">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                        <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                        <input type="hidden" name="subcategory_filter" value="{{ request('subcategory_filter') }}">
                        <select name="category_filter" onchange="this.form.submit()" class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-2 pl-3 pr-8">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_filter') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div>
                    <form method="GET">
                        <input type="hidden" name="tab" value="{{ request('tab', 'new-orders') }}">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                        <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                        <input type="hidden" name="category_filter" value="{{ request('category_filter') }}">
                        <select name="subcategory_filter" onchange="this.form.submit()" class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-2 pl-3 pr-8">
                            <option value="">All Subcategories</option>
                            @foreach($subcategories as $subcategory)
                            <option value="{{ $subcategory->id }}" {{ request('subcategory_filter') == $subcategory->id ? 'selected' : '' }}>{{ $subcategory->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            <!-- Custom Tailwind Tabs -->
            <div class="border-b border-slate-200">
                <nav class="flex -mb-px space-x-8 overflow-x-auto pb-px" id="workOrderTabs">
                    @php
                    $tabList = [
                    'new-orders' => 'New Orders',
                    'allocated-orders' => 'Allocated',
                    'in-process-orders' => 'In Process',
                    'overdue-orders' => 'Overdue',
                    'for-approval-orders' => 'Pending Approval',
                    'completed-orders' => 'Completed',
                    'rejected-orders' => 'Rejected',
                    'all-orders' => 'All Orders'
                    ];
                    @endphp
                    @foreach($tabList as $id => $label)
                    <button
                        data-tab-target="{{ $id }}"
                        class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 {{ $id === 'new-orders' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </nav>
            </div>

            <!-- Tab Panels -->
            <div id="workOrderTabsContent" class="mt-6">
                <!-- Data Table Helper Component (PHP logic stays same, classes change) -->
                @php
                $panes = [
                'new-orders' => [$newOrders, 'No new work orders found.'],
                'allocated-orders' => [$allocatedOrders, 'No allocated work orders found.'],
                'in-process-orders' => [$inProcessOrders, 'No work orders in process found.'],
                'overdue-orders' => [$overdueOrders, 'No overdue work orders found.'],
                'for-approval-orders' => [$forApprovalOrders, 'No work orders pending approval found.'],
                'completed-orders' => [$completedOrders, 'No completed work orders found.'],
                'rejected-orders' => [$rejectedOrders, 'No rejected work orders found.'],
                'all-orders' => [$allOrders, 'No work orders found.']
                ];
                @endphp

                @foreach($panes as $id => $data)
                <div id="{{ $id }}" class="tab-pane {{ $id === 'new-orders' ? '' : 'hidden animate-in fade-in duration-300' }}">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 px-2 gap-4">
                        <div class="flex items-center gap-3">
                            <h3 class="text-md font-semibold text-slate-700 capitalize group flex items-center">
                                {{ str_replace('-', ' ', $id) }}
                                <span class="ml-2 px-2 py-0.5 text-xs font-bold bg-slate-100 text-slate-500 rounded-full border border-slate-200">
                                    {{ method_exists($data[0], 'total') ? $data[0]->total() : $data[0]->count() }}
                                </span>
                            </h3>
                            <button type="button" onclick="submitBulkPrint('{{ $id }}')" class="inline-flex items-center px-3 py-1.5 text-[11px] font-bold text-white bg-slate-900 rounded-lg hover:bg-slate-800 transition-all shadow-sm">
                                <i class="bi bi-printer mr-2"></i> Print Selected
                            </button>
                        </div>
                        <form method="GET" class="flex items-center space-x-2">
                            <input type="hidden" name="tab" value="{{ $id }}">
                            <label for="per_page_{{ $id }}" class="text-xs font-medium text-slate-500 uppercase tracking-wider">Page size:</label>
                            <select name="per_page" id="per_page_{{ $id }}" class="text-xs border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-1 pl-2 pr-8" onchange="this.form.submit()">
                                @foreach([5, 10, 15, 20, 25, 30, 40, 50] as $size)
                                <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <form action="{{ route('buyer.work-order.bulk-print') }}" method="POST" id="form-{{ $id }}" target="_blank">
                        @csrf
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-y border-slate-100">
                                    <tr>
                                        <th class="px-6 py-4 font-semibold text-center">
                                            <input type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 select-all-btn" data-target-tab="{{ $id }}">
                                        </th>
                                        <th class="px-6 py-4 font-semibold">Image</th>
                                        <th class="px-6 py-4 font-semibold">WO Number</th>
                                        <th class="px-6 py-4 font-semibold">BP Code</th>
                                        <th class="px-6 py-4 font-semibold">Category</th>
                                        <th class="px-6 py-4 font-semibold">Type</th>
                                        <th class="px-6 py-4 font-semibold">Order Type</th>
                                        <th class="px-6 py-4 font-semibold">Qty</th>
                                        <th class="px-6 py-4 font-semibold">Status</th>
                                        <th class="px-6 py-4 font-semibold">Date</th>
                                        <th class="px-6 py-4 font-semibold text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($data[0] as $workOrder)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4 text-center">
                                            <input type="checkbox" name="selected_orders[]" value="{{ $workOrder->id }}" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 order-checkbox-{{ $id }}">
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                            $imagePath = $workOrder->product_image;
                                            $displayUrl = null;
                                            $isPdf = false;

                                            if ($imagePath) {
                                            $isPdf = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)) === 'pdf';
                                            if (str_starts_with($imagePath, 'images/') || str_starts_with($imagePath, 'storage/') || str_starts_with($imagePath, 'uploads/') || filter_var($imagePath, FILTER_VALIDATE_URL)) {
                                            $displayUrl = asset($imagePath);
                                            } else {
                                            $displayUrl = asset('storage/' . $imagePath);
                                            }
                                            } elseif ($workOrder->product && $workOrder->product->images->count() > 0) {
                                            $firstImg = $workOrder->product->images->first();
                                            $displayUrl = asset('storage/' . $firstImg->path);
                                            }

                                            $previewUrl = $displayUrl ?: asset('images/no-image.png');
                                            $previewType = $isPdf ? 'pdf' : 'image';
                                            @endphp

                                            <div class="relative group cursor-zoom-in w-12 h-12 bg-slate-50 rounded-lg border border-slate-100 overflow-hidden flex items-center justify-center transition-all hover:border-blue-200 hover:shadow-sm"
                                                onclick="openUniversalPreview('{{ $previewUrl }}', '{{ $previewType }}')">

                                                @if($displayUrl)
                                                @if($isPdf)
                                                <canvas class="pdf-canvas" data-url="{{ $displayUrl }}" data-desired-width="48"></canvas>
                                                @else
                                                <img src="{{ $displayUrl }}" class="w-full h-full object-cover">
                                                @endif
                                                @else
                                                <i class="bi bi-image text-slate-300"></i>
                                                @endif

                                                <div class="absolute inset-0 bg-blue-600/0 group-hover:bg-blue-600/5 flex items-center justify-center transition-colors">
                                                    <i class="bi bi-search text-white opacity-0 group-hover:opacity-100 text-xs translate-y-2 group-hover:translate-y-0 transition-all"></i>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 font-medium text-slate-900">{{ $workOrder->work_order_number }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ $workOrder->bp_code }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ $workOrder->product_category }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ $workOrder->type }}</td>
                                        <td class="px-6 py-4 font-medium">
                                            @php
                                            $typeClasses = [
                                            'Regular' => 'bg-blue-50 text-blue-700 border-blue-100',
                                            'Urgent' => 'bg-amber-50 text-amber-700 border-amber-100',
                                            'Danger' => 'bg-red-50 text-red-700 border-red-100'
                                            ];
                                            $cls = $typeClasses[$workOrder->order_type] ?? 'bg-slate-50 text-slate-700 border-slate-100';
                                            @endphp
                                            <span class="px-2.5 py-1 text-[11px] rounded-full border {{ $cls }}">
                                                {{ $workOrder->order_type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-600">{{ $workOrder->quantity }}</td>
                                        <td class="px-6 py-4 font-medium">
                                            <span class="inline-flex items-center {{ $workOrder->open_close == 'Open' ? 'text-emerald-600' : 'text-slate-500' }}">
                                                <span class="w-1.5 h-1.5 rounded-full mr-2 {{ $workOrder->open_close == 'Open' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                                {{ $workOrder->open_close }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500">{{ $workOrder->created_at->format('d M, Y') }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end space-x-2">
                                                <!-- <a href="{{ route('buyer.work-order.show', $workOrder) }}" 
                                                       class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md transition-colors" title="View">
                                                        <i class="bi bi-eye text-lg"></i>
                                                    </a> -->

                                                @if($id === 'new-orders' || $id === 'rejected-orders')
                                                <a href="{{ route('buyer.work-order.edit', $workOrder) }}"
                                                    class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-md transition-colors" title="Edit">
                                                    <i class="bi bi-pencil-square text-lg"></i>
                                                </a>
                                                @endif

                                                <a href="{{ route('buyer.work-order.print', $workOrder) }}"
                                                    class="p-1.5 text-slate-600 hover:bg-slate-100 rounded-md transition-colors" target="_blank" title="Print">
                                                    <i class="bi bi-printer text-lg"></i>
                                                </a>

                                                @if($id === 'new-orders')
                                                <form action="{{ route('buyer.work-order.destroy', $workOrder) }}"
                                                    method="POST"
                                                    class="inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this work order?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-md transition-colors" title="Delete">
                                                        <i class="bi bi-trash text-lg"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="px-6 py-12 text-center text-slate-400 italic bg-slate-50/30">
                                            <i class="bi bi-inbox text-3xl mb-2 d-block opacity-20"></i>
                                            <p>{{ $data[1] }}</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>

                    @if(method_exists($data[0], 'links'))
                    <div class="mt-8 px-2">
                        {{ $data[0]->links('vendor.pagination.tailwind') }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    function refreshSubcategories(categoryId) {
        const subcategorySelect = document.querySelector('select[name="subcategory_filter"]');
        if (!subcategorySelect) return;

        subcategorySelect.innerHTML = '<option value="">All Subcategories</option>';
        if (!categoryId) {
            return;
        }

        fetch(`/buyer/product/get-subcategories?category_id=${categoryId}`)
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

    // Bulk Print selected handling
    function submitBulkPrint(tabId) {
        const selected = document.querySelectorAll(`.order-checkbox-${tabId}:checked`);
        if (selected.length === 0) {
            alert('Please select at least one work order to print.');
            return;
        }
        document.getElementById(`form-${tabId}`).submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.querySelector('select[name="category_filter"]');
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                refreshSubcategories(this.value);
            });
        }

        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabPanes = document.querySelectorAll('.tab-pane');

        function showTab(tabId) {
            // Update URL
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);

            // Update Buttons
            tabButtons.forEach(btn => {
                if (btn.getAttribute('data-tab-target') === tabId) {
                    btn.classList.add('border-blue-600', 'text-blue-600');
                    btn.classList.remove('border-transparent', 'text-slate-500');
                } else {
                    btn.classList.remove('border-blue-600', 'text-blue-600');
                    btn.classList.add('border-transparent', 'text-slate-500');
                }
            });

            // Update Panes
            tabPanes.forEach(pane => {
                if (pane.id === tabId) {
                    pane.classList.remove('hidden');
                } else {
                    pane.classList.add('hidden');
                }
            });
        }

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                showTab(button.getAttribute('data-tab-target'));
            });
        });

        document.querySelectorAll('.select-all-btn').forEach(btn => {
            btn.addEventListener('change', function() {
                const target = this.getAttribute('data-target-tab');
                const checkboxes = document.querySelectorAll(`.order-checkbox-${target}`);
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        });

        // Initial load from URL
        const urlParams = new URLSearchParams(window.location.search);
        const initialTab = urlParams.get('tab');
        if (initialTab && document.getElementById(initialTab)) {
            showTab(initialTab);
        }
    });
</script>
@endsection
@endsection