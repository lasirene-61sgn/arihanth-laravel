@extends('super-admin.layouts.app')

@section('title', __('messages.design_approval_management') . ' | ERP')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>

<div class="bg-slate-50 min-h-screen p-4 font-sans text-slate-900">

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ __('messages.design_approval_management') }}</h1>
                <p class="text-slate-500 text-sm">{{ __('messages.review_accept_reject') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="exportCurrentTab()" class="flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition text-sm font-semibold">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> {{ __('excel') }}
                </button>
                    <button onclick="printSelectedDesigns()" class="flex items-center px-4 py-2 bg-sky-50 text-sky-700 border border-sky-200 rounded-lg hover:bg-sky-100 transition text-sm font-semibold">
                        <i class="bi bi-printer me-2"></i> {{ __('messages.print') }}
                    </button>
                    <!-- <button onclick="bulkPrintPRN()" class="flex items-center px-4 py-2 bg-slate-100 text-slate-700 border border-slate-200 rounded-lg hover:bg-slate-200 transition text-sm font-semibold">
                        <i class="bi bi-file-earmark-code me-2"></i> {{ __('PRN') }}
                    </button> -->
                    @if(request('tab') === 'accepted')
                    <button onclick="bulkPrint80x40()" class="flex items-center px-4 py-2 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg hover:bg-blue-100 transition text-sm font-semibold">
                        <i class="bi bi-printer me-2"></i> {{ __('Print 80x40') }}
                    </button>
                    @endif
                    <button onclick="bulkAccept()" class="flex items-center px-4 py-2 bg-emerald-600 text-white border border-emerald-700 rounded-lg hover:bg-emerald-700 transition text-sm font-semibold">
                        <i class="bi bi-check-circle me-2"></i> {{ __('') }}
                    </button>
                    <button onclick="bulkReject()" class="flex items-center px-4 py-2 bg-rose-600 text-white border border-rose-700 rounded-lg hover:bg-rose-700 transition text-sm font-semibold">
                        <i class="bi bi-x-circle me-2"></i> {{ __('') }}
                    </button>
                    <!-- <button onclick="showUnlockModal('selected')" class="flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition text-sm font-semibold">
                    <i class="bi bi-unlock me-2"></i> {{ __('Unlock') }}
                </button> -->
                    <a href="{{ route('super-admin.design.generate-missing-qrcodes') }}" class="flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition text-sm font-semibold">
                        <i class="bi bi-qr-code me-2"></i> {{ __('QRs') }}
                    </a>
                    <button onclick="showUnlockModal('category')" class="flex items-center px-4 py-2 bg-violet-50 text-violet-700 border border-violet-200 rounded-lg hover:bg-violet-100 transition text-sm font-semibold">
                        <i class="bi bi-tags me-2"></i> {{ __('Unlock') }}
                    </button>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3 border-t border-slate-100 pt-5">
            <button onclick="toggleSection('searchSection')" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm hover:border-indigo-500 flex items-center shadow-sm">
                <i class="bi bi-search me-2 text-indigo-600"></i> {{ __('messages.search') }}
            </button>
            <button onclick="toggleSection('filterSection')" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm hover:border-indigo-500 flex items-center shadow-sm">
                <i class="bi bi-funnel me-2 text-indigo-600"></i> {{ __('messages.advanced_filter') }}
            </button>
            <a href="{{ route('super-admin.design.index') }}" class="px-4 py-2 text-sm text-slate-400 hover:text-red-500 transition">{{ __('messages.reset_all') }}</a>
        </div>
    </div>

    <div id="searchSection" class="hidden mb-4 animate-in slide-in-from-top duration-200">
        <div class="bg-white p-4 rounded-lg border border-indigo-200 shadow-sm">
            <form method="GET" class="flex gap-3">
                <input type="hidden" name="category_filter" value="{{ request('category_filter') }}">
                <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Code, Name or Design Code..." class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold">{{ __('messages.find') }}</button>
            </form>
        </div>
    </div>

    <div id="filterSection" class="hidden mb-4">
        <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">{{ __('messages.category') }}</label>
                    <select name="category_filter" class="w-full mt-1 border border-slate-200 rounded-md p-2 text-sm">
                        <option value="">All Categories ({{ $categories->count() }})</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">
                            {{ $cat->name }}
                        </option>
                        @endforeach
                    </select>

                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">{{ __('messages.sort_by') }}</label>
                    <select name="sort_by" class="w-full mt-1 border border-slate-200 rounded-md p-2 text-sm">
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>{{ __('messages.date_created') }}</option>
                        <option value="product_code" {{ request('sort_by') == 'product_code' ? 'selected' : '' }}>Product Code</option>
                        <option value="product_name" {{ request('sort_by') == 'product_name' ? 'selected' : '' }}>{{ __('messages.name') }}</option>
                        <option value="design_code" {{ request('sort_by') == 'design_code' ? 'selected' : '' }}>{{ __('messages.design_code') }}</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-slate-800 text-white py-2 rounded-md font-bold text-sm hover:bg-slate-900 transition">{{ __('messages.apply_filters') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="flex border-b border-slate-200 mb-6 gap-8 overflow-x-auto no-scrollbar">
        @php
        $statusTabs = [
        ['id' => 'all', 'label' => __('messages.all_items'), 'count' => $statusCounts['all'], 'color' => 'indigo'],
        ['id' => 'accepted', 'label' => __('messages.accepted'), 'count' => $statusCounts['accepted'], 'color' => 'emerald'],
        ['id' => 'rejected', 'label' => __('messages.rejected'), 'count' => $statusCounts['rejected'], 'color' => 'red'],
        ['id' => 'pending', 'label' => __('messages.pending_approval'), 'count' => $statusCounts['pending'], 'color' => 'amber'],
        ];
        @endphp

        @foreach($statusTabs as $tab)
        <a href="{{ request()->fullUrlWithQuery(['tab' => $tab['id'], 'page' => 1]) }}"
            class="pb-4 px-2 text-sm font-bold transition-all border-b-2 {{ $activeTab == $tab['id'] ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
            {{ $tab['label'] }}
            <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] bg-{{ $tab['color'] }}-100 text-{{ $tab['color'] }}-700 border border-{{ $tab['color'] }}-200">
                {{ $tab['count'] }}
            </span>
        </a>
        @endforeach
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
        <div id="pane-{{ $activeTab }}" class="tab-pane">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                            <th class="px-4 py-4 w-10">
                                <input type="checkbox" class="select-all-designs rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th class="px-4 py-4 w-16">{{ __('messages.image') }}</th>
                            <th class="px-4 py-4">{{ __('messages.product_info') }}</th>
                            <th class="px-4 py-4">{{ __('messages.design_code') }}</th>
                            <th class="px-4 py-4">{{ __('messages.category') }}</th>
                            <th class="px-4 py-4 text-center">{{ __('messages.priority') }}</th>
                            <th class="px-4 py-4">{{ __('messages.creator') }}</th>
                            <th class="px-4 py-4 text-center">{{ __('messages.status') }}</th>
                            <th class="px-4 py-4">{{ __('Unlock Info') }}</th>
                            <th class="px-4 py-4 text-right">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($products as $product)
                        <tr class="hover:bg-indigo-50/40 transition group">
                            <td class="px-4 py-4">
                                @if($activeTab !== 'all' || $product->design_status !== 'Accepted')
                                <input type="checkbox" class="design-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" value="{{ $product->id }}">
                                @else
                                <div class="flex justify-center">
                                    <i class="bi bi-check-circle-fill text-emerald-500 shadow-sm rounded-full" title="Accepted"></i>
                                </div>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @php
                                $imagesCount = $product->images->count();
                                $firstImage = $imagesCount > 0 ? $product->images->first()->path : null;

                                // Fallback for legacy data
                                if (!$firstImage && $product->product_image) {
                                $imgs = explode(',', $product->product_image);
                                $firstImage = trim($imgs[0]);
                                }

                                $imgUrl = null;
                                if ($firstImage) {
                                if (str_starts_with($firstImage, 'http')) {
                                $imgUrl = $firstImage;
                                } elseif (str_starts_with($firstImage, 'products/')) {
                                $imgUrl = asset('storage/' . $firstImage);
                                } elseif (str_starts_with($firstImage, 'images/') || str_starts_with($firstImage, 'storage/')) {
                                $imgUrl = asset($firstImage);
                                } else {
                                $imgUrl = asset('storage/products/' . $firstImage);
                                }
                                }
                                @endphp

                                <div class="relative w-12 h-12">
                                    @if($imgUrl)
                                    <img src="{{ $imgUrl }}"
                                        alt="Product"
                                        class="w-full h-full object-cover rounded border border-slate-200 cursor-pointer hover:scale-110 transition-transform"
                                        onclick="window.openUniversalPreview('{{ $imgUrl }}', 'image')">
                                    @if($imagesCount > 1)
                                    <span class="absolute -bottom-1 -right-1 bg-slate-800 text-white text-[8px] px-1 rounded-full font-bold">
                                        +{{ $imagesCount - 1 }}
                                    </span>
                                    @endif
                                    @else
                                    <div class="w-full h-full bg-slate-50 rounded border border-slate-200 flex items-center justify-center">
                                        <i class="bi bi-image text-slate-300"></i>
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-mono text-indigo-600 font-bold text-sm">{{ $product->product_code }}</div>
                                <div class="text-sm font-semibold text-slate-800">{{ $product->product_name }}</div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-mono text-emerald-600 font-bold">
                                    {{ $product->design_status === 'Accepted' ? ($product->design_code ?? '-') : '-' }}
                                </div>
                                @if($product->design_status === 'Accepted' && $product->qr_code)
                                <div class="mt-1">
                                    <img src="{{ asset('storage/' . $product->qr_code) }}" alt="QR" class="w-8 h-8 border border-slate-100 rounded cursor-pointer hover:scale-150 transition-transform" onclick="window.openUniversalPreview('{{ asset('storage/' . $product->qr_code) }}', 'image')">
                                </div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-600">
                                <div>{{ $product->category->name ?? 'N/A' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $product->subcategory->name ?? '' }}</div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($product->order_type == 'Urgent' || $product->order_type == 'Super Urgent')
                                <span class="px-2 py-1 rounded bg-red-50 text-red-600 text-[10px] font-black border border-red-100 uppercase italic">
                                    {{ $product->order_type }}
                                </span>
                                @else
                                <span class="text-slate-400 text-[10px] uppercase font-bold tracking-tighter">Regular</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <!--<div class="text-xs font-medium">{{ $product->creator_name }}</div>-->
                                <div class="text-xs font-medium">{{ $product->bp_code }}</div>
                                <!--<div class="text-[10px] text-slate-400">{{ $product->created_at->format('d/m/Y') }}</div>-->
                            </td>
                            <td class="px-4 py-4 text-center">
                                @php
                                $badgeClass = match($product->design_status) {
                                'Accepted' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                                'Rejected' => 'bg-red-50 text-red-600 border-red-200',
                                default => 'bg-amber-50 text-amber-600 border-amber-200',
                                };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full border text-[10px] font-bold {{ $badgeClass }}">
                                    {{ strtoupper($product->design_status ?? 'Pending') }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                @if($product->userAccess->count() > 0)
                                @foreach($product->userAccess as $access)
                                {{-- Wrap $access->unlocked_until in Carbon::parse --}}
                                @if($access->unlocked_until && \Carbon\Carbon::parse($access->unlocked_until)->isFuture())
                                <div class="mb-1 flex flex-col">
                                    <span class="font-mono text-indigo-600 font-bold text-[10px]">{{ $access->user_code }}</span>
                                    <span class="text-[9px] text-slate-500 countdown-timer" data-until="{{ \Carbon\Carbon::parse($access->unlocked_until)->timestamp }}">
                                        {{ \Carbon\Carbon::parse($access->unlocked_until)->diffForHumans() }}
                                    </span>
                                </div>
                                @endif
                                @endforeach
                                @endif

                                {{-- Wrap $product->design_view_unlocked_until in Carbon::parse --}}
                                @if($product->design_view_unlocked_until && \Carbon\Carbon::parse($product->design_view_unlocked_until)->isFuture())
                                <div class="text-[10px] text-emerald-600 font-bold italic flex flex-col border-t border-slate-50 mt-1 pt-1">
                                    <span>GLOBAL UNLOCK</span>
                                    <span class="countdown-timer text-[9px]" data-until="{{ \Carbon\Carbon::parse($product->design_view_unlocked_until)->timestamp }}">
                                        {{ \Carbon\Carbon::parse($product->design_view_unlocked_until)->diffForHumans() }}
                                    </span>
                                </div>
                                @endif
                            </td>

                            <td class="px-4 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    @if($product->design_status != 'Accepted')
                                    <button type="button" onclick="showAcceptModal('{{ $product->id }}', '{{ $product->product_code }}')" class="p-2 bg-emerald-500 text-white rounded hover:bg-emerald-600 shadow-sm" title="Accept">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    @endif

                                    @if($product->design_status == 'Pending' || empty($product->design_status))
                                    <form action="{{ route('super-admin.design.reject', $product) }}" method="POST">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Reject design?')" class="p-2 bg-red-500 text-white rounded hover:bg-red-600 shadow-sm" title="Reject">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                    @endif

                                    <a href="{{ route('super-admin.design.show', $product) }}" class="p-2 bg-slate-100 text-slate-600 rounded hover:bg-slate-200" title="View Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($product->design_status != 'Accepted')
                                    <form action="{{ route('super-admin.design.toggle-lock', $product) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="p-2 bg-slate-100 text-slate-600 rounded hover:bg-slate-200" title="{{ $product->is_locked ? 'Unlock Image' : 'Lock Image' }}">
                                            <i class="bi {{ $product->is_locked ? 'bi-unlock' : 'bi-lock' }}"></i>
                                        </button>
                                    </form>
                                    @endif

                                    <form action="{{ route('super-admin.product.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus desain ini secara permanen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 bg-red-50 text-red-600 rounded hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Delete Design">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="py-20 text-center">
                                <p class="text-slate-400 text-sm">{{ __('messages.no_items_found') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $products->links() }}
        </div>

        <!-- Unlock Duration Modal is included via partial at the bottom -->

        <script>
            function toggleSection(id) {
                const el = document.getElementById(id);
                el.classList.toggle('hidden');
            }


            document.querySelectorAll('.select-all-designs').forEach(selectAll => {
                selectAll.addEventListener('change', function() {
                    const table = this.closest('table');
                    table.querySelectorAll('.design-checkbox').forEach(cb => cb.checked = this.checked);
                });
            });

            function printSelectedDesigns() {
                const selected = Array.from(document.querySelectorAll('.design-checkbox:checked')).map(cb => cb.value);
                if (selected.length === 0) {
                    alert('Please select at least one design to print.');
                    return;
                }

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('super-admin.design.print-selected') }}";
                form.target = '_blank';

                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);

                selected.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_designs[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }

            function bulkPrintPRN() {
                const selected = Array.from(document.querySelectorAll('.design-checkbox:checked')).map(cb => cb.value);
                if (selected.length === 0) {
                    alert('Please select at least one design to print PRN.');
                    return;
                }

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('super-admin.design.bulk-print-prn') }}";

                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);

                selected.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_designs[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }

            function bulkPrint80x40() {
                const selected = Array.from(document.querySelectorAll('.design-checkbox:checked')).map(cb => cb.value);
                if (selected.length === 0) {
                    alert('Please select at least one design to print.');
                    return;
                }

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('super-admin.design.bulk-print-80x40') }}";
                form.target = '_blank';

                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);

                selected.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_designs[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }

            function exportCurrentTab() {
                const currentTab = getCurrentTabId();
                const selected = Array.from(document.querySelectorAll('.design-checkbox:checked')).map(cb => cb.value);

                // Add the current tab to the export parameters
                let exportUrl = "{{ route('super-admin.design.index') }}?export=excel&tab=" + currentTab + "&" + new URLSearchParams(window.location.search).toString();

                if (selected.length > 0) {
                    exportUrl += "&selected_ids=" + selected.join(',');
                }

                window.location.href = exportUrl;
            }

            function bulkAccept() {
                const selected = Array.from(document.querySelectorAll('.design-checkbox:checked')).map(cb => cb.value);
                if (selected.length === 0) {
                    alert('Please select at least one design to accept.');
                    return;
                }

                if (!confirm(`Are you sure you want to accept ${selected.length} designs?`)) return;

                fetch("{{ route('super-admin.design.bulk-accept') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            selected_designs: selected
                        })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Server error: ' + res.status);
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert(data.message || 'Something went wrong');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('An error occurred: ' + err.message);
                    });
            }

            function bulkReject() {
                const selected = Array.from(document.querySelectorAll('.design-checkbox:checked')).map(cb => cb.value);
                if (selected.length === 0) {
                    alert('Please select at least one design to reject.');
                    return;
                }

                if (!confirm(`Are you sure you want to reject ${selected.length} designs?`)) return;

                fetch("{{ route('super-admin.design.bulk-reject') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            selected_designs: selected
                        })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Server error: ' + res.status);
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert(data.message || 'Something went wrong');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('An error occurred. Please try again.');
                    });
            }

            function getCurrentTabId() {
                return new URLSearchParams(window.location.search).get('tab') || 'all';
            }

            // Countdown Timer logic
            function updateTimers() {
                const timers = document.querySelectorAll('.countdown-timer');
                const now = Math.floor(Date.now() / 1000);

                timers.forEach(timer => {
                    const until = parseInt(timer.getAttribute('data-until'));
                    const diff = until - now;

                    if (diff <= 0) {
                        timer.textContent = 'Expired';
                        timer.classList.add('text-red-500');
                        return;
                    }

                    const days = Math.floor(diff / 86400);
                    const hours = Math.floor((diff % 86400) / 3600);
                    const mins = Math.floor((diff % 3600) / 60);
                    const secs = diff % 60;

                    let timeStr = '';
                    if (days > 0) timeStr += days + 'd ';
                    if (hours > 0 || days > 0) timeStr += hours + 'h ';
                    timeStr += mins + 'm ' + secs + 's left';

                    timer.textContent = timeStr;
                });
            }

            setInterval(updateTimers, 1000);
            window.addEventListener('load', updateTimers);
        </script>

        <!-- Accept Design Modal -->
        <div id="acceptDesignModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden animate-in zoom-in-95 duration-200">
                <form id="acceptDesignForm" method="POST">
                    @csrf
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                        <h3 class="font-bold text-slate-800">{{ __('messages.accept_design') }}</h3>
                        <button type="button" onclick="hideAcceptModal()" class="text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="p-6 text-center">
                        <i class="bi bi-question-circle text-5xl text-emerald-500 mb-4 block"></i>
                        <p class="text-sm text-slate-600 mb-4">Are you sure you want to accept this design? <br>A unique Design Code will be generated automatically for Product: <strong id="acceptProductCode" class="text-slate-900"></strong></p>
                    </div>
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                        <button type="button" onclick="hideAcceptModal()" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200 rounded-lg transition">{{ __('messages.cancel') }}</button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">{{ __('messages.accept_design') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function showAcceptModal(productId, productCode) {
                const modal = document.getElementById('acceptDesignModal');
                const form = document.getElementById('acceptDesignForm');

                // Use relative route generation (3rd param false) to handle subdirectories/hosts correctly
                const urlTemplate = "{{ route('super-admin.design.accept', '000', false) }}";
                form.action = urlTemplate.replace('000', productId);

                document.getElementById('acceptProductCode').textContent = productCode;
                modal.classList.remove('hidden');
            }

            function showUnlockModal(type = 'selected', specificIds = null) {
                window.currentUnlockType = type;
                const previewSection = document.getElementById('selectedDesignsPreview');
                const categorySection = document.getElementById('categorySelectionSection');

                if (type === 'selected') {
                    if (specificIds) {
                        designsToUnlock = Array.isArray(specificIds) ? specificIds : [specificIds];
                    } else {
                        const checkboxes = document.querySelectorAll('.design-checkbox:checked');
                        if (checkboxes.length > 0) {
                            designsToUnlock = Array.from(checkboxes).map(cb => cb.value);
                        } else {
                            designsToUnlock = [];
                        }
                    }

                    if (designsToUnlock.length === 0) {
                        alert('Please select at least one design to unlock.');
                        return;
                    }
                    if (previewSection) previewSection.style.display = 'block';
                    if (categorySection) categorySection.style.display = 'none';
                } else {
                    designsToUnlock = [];
                    if (previewSection) previewSection.style.display = 'none';
                    if (categorySection) categorySection.style.display = 'block';
                }

                // Initialize duration options for minutes (default) - force reset
                try {
                    const defaultUnit = document.querySelector('input[name="durationUnit"][value="minutes"]');
                    if (defaultUnit) defaultUnit.checked = true;
                    updateDurationOptions('minutes');
                } catch (e) {
                    console.error('Error resetting duration unit:', e);
                }

                // Load available users if not already loaded
                if (!usersLoaded && !isLoadingUsers) {
                    loadAvailableUsers();
                }

                document.getElementById('unlockModal').classList.remove('hidden');
            }

            function hideAcceptModal() {
                document.getElementById('acceptDesignModal').classList.add('hidden');
            }
        </script>

        @include('super-admin.design.partials.unlock-modal')

        <style>
            @media print {
                .bg-slate-50 {
                    background-color: white !important;
                }

                .shadow-sm,
                .shadow-lg,
                button,
                form,
                .toolbar,
                .tabs {
                    display: none !important;
                }

                .hidden {
                    display: table-row !important;
                }

                .no-scrollbar::-webkit-scrollbar {
                    display: none;
                }
            }
        </style>
        @endsection