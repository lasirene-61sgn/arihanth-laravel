@extends('super-admin.layouts.app')

@section('title', 'Edit Live Stock Order')

@section('content')
<div class="tw-max-w-7xl tw-mx-auto">
    <div class="tw-mb-8">
        <a href="{{ route('super-admin.stock-order.index') }}" class="tw-text-slate-400 hover:tw-text-indigo-600 tw-transition-colors tw-flex tw-items-center tw-gap-2 tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest tw-mb-4">
            <svg class="tw-w-3 tw-h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path></svg>
            Back to Orders
        </a>
        <h1 class="tw-text-2xl tw-font-black tw-text-slate-900 tw-tracking-tight tw-uppercase">Edit Stock Order: {{ $stockOrder->order_number }}</h1>
        <p class="tw-text-slate-500 tw-text-sm tw-font-medium">Modify order details and items</p>
    </div>

    <form action="{{ route('super-admin.stock-order.update', $stockOrder->id) }}" method="POST" id="stockOrderForm">
        @csrf
        @method('PUT')
        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-8">
            <!-- Sidebar: Order Info -->
            <div class="lg:tw-col-span-1">
                <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-100 tw-p-6 tw-sticky tw-top-8">
                    <div class="tw-mb-6">
                        <label class="tw-block tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-2">Buyer Selection</label>
                        <select name="buyer_id" required class="tw-w-full tw-bg-slate-50 tw-border-none tw-rounded-xl tw-px-4 tw-py-3 tw-text-sm tw-font-bold tw-text-slate-700 focus:tw-ring-2 focus:tw-ring-indigo-500/10 tw-transition-all">
                            <option value="">Select a Buyer</option>
                            @foreach($buyers as $buyer)
                                <option value="{{ $buyer->id }}" {{ $stockOrder->buyer_id == $buyer->id ? 'selected' : '' }}>{{ $buyer->business_name }} ({{ $buyer->bp_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="tw-mb-6">
                        <label class="tw-block tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-2">Order Notes</label>
                        <textarea name="notes" rows="4" placeholder="General order instructions..." 
                                  class="tw-w-full tw-bg-slate-50 tw-border-none tw-rounded-xl tw-px-4 tw-py-3 tw-text-sm tw-font-bold tw-text-slate-700 placeholder:tw-text-slate-400 focus:tw-ring-2 focus:tw-ring-indigo-500/10 tw-transition-all">{{ $stockOrder->notes }}</textarea>
                    </div>

                    <div class="tw-pt-6 tw-border-t tw-border-slate-50">
                        <button type="submit" class="tw-w-full tw-bg-indigo-600 tw-text-white tw-px-6 tw-py-4 tw-rounded-xl tw-font-black tw-text-xs tw-uppercase tw-tracking-widest hover:tw-bg-indigo-700 tw-transition-all tw-shadow-lg tw-shadow-indigo-100 tw-flex tw-items-center tw-justify-center tw-gap-2">
                            Update Stock Order
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content: Items -->
            <div class="lg:tw-col-span-2 tw-space-y-8">
                <!-- Scanner Section -->
                <div class="tw-bg-slate-900 tw-rounded-[2rem] tw-overflow-hidden tw-shadow-2xl tw-relative tw-border-4 tw-border-white">
                    <div id="reader" style="width: 100%; min-height: 300px;"></div>
                    <div id="scanner-overlay" class="tw-absolute tw-inset-0 tw-flex tw-flex-col tw-items-center tw-justify-center tw-bg-slate-900/80 tw-z-10 tw-pointer-events-none tw-transition-opacity tw-duration-300">
                        <i class="bi bi-camera tw-text-4xl tw-text-white tw-mb-3 tw-animate-pulse"></i>
                        <p class="tw-text-white tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest">Awaiting Camera Access</p>
                    </div>
                    <!-- Status Bar -->
                    <div id="scan-status" class="tw-absolute tw-bottom-0 tw-left-0 tw-right-0 tw-bg-indigo-600 tw-text-white tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest tw-py-3 tw-px-6 tw-translate-y-full tw-transition-transform tw-duration-300 tw-z-20">
                        Scanner Ready
                    </div>
                </div>

                <div class="tw-flex tw-items-center tw-justify-between tw-bg-white tw-p-6 tw-rounded-2xl tw-border tw-border-slate-100 tw-shadow-sm">
                    <div class="tw-flex tw-items-center tw-gap-4">
                        <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-indigo-50 tw-text-indigo-600 tw-flex tw-items-center tw-justify-center">
                            <i class="bi bi-qr-code-scan tw-text-xl"></i>
                        </div>
                        <div>
                            <h3 class="tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-0.5">Scanner Mode</h3>
                            <p class="tw-text-xs tw-font-bold tw-text-slate-900">Batch scan multiple designs</p>
                        </div>
                    </div>
                    <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                        <input type="checkbox" id="batch-mode" class="tw-sr-only tw-peer" checked>
                        <div class="tw-w-11 tw-h-6 tw-bg-slate-200 peer-focus:tw-outline-none tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full rtl:peer-checked:after:tw-translate-x-[-100%] peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-start-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-indigo-600"></div>
                    </label>
                </div>

                <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-slate-100 tw-overflow-hidden">
                    <div class="tw-p-6 tw-border-b tw-border-slate-50 tw-flex tw-justify-between tw-items-center">
                        <h2 class="tw-text-xs tw-font-black tw-text-slate-900 tw-uppercase tw-tracking-widest">Order Items</h2>
                        <button type="button" id="addItemBtn" class="tw-text-indigo-600 hover:tw-text-indigo-700 tw-font-black tw-text-[10px] tw-uppercase tw-tracking-widest tw-flex tw-items-center tw-gap-1">
                            <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                            Add Row
                        </button>
                    </div>

                    <div class="tw-overflow-x-auto">
                        <table class="tw-w-full tw-text-left tw-border-collapse">
                            <thead>
                                <tr class="tw-bg-slate-50/50 tw-border-b tw-border-slate-100">
                                    <th class="tw-px-6 tw-py-4 tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest">Design Details</th>
                                    <th class="tw-px-6 tw-py-4 tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-w-32">Weight / Size</th>
                                    <th class="tw-px-6 tw-py-4 tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-w-32">Qty / Grams</th>
                                    <th class="tw-px-6 tw-py-4 tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-text-right"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsContainer" class="tw-divide-y tw-divide-slate-50">
                                @foreach($stockOrder->items as $index => $item)
                                <tr class="item-row">
                                    <td class="tw-px-6 tw-py-4">
                                        <div class="tw-flex tw-gap-4">
                                            <div class="tw-w-16 tw-h-16 tw-bg-slate-50 tw-rounded-xl tw-border tw-border-slate-100 tw-overflow-hidden tw-shrink-0 tw-p-1 item-image-container">
                                                @if($item->product && $item->product->image)
                                                    <img src="{{ asset('storage/' . $item->product->image) }}" class="tw-w-full tw-h-full tw-object-contain tw-rounded-lg">
                                                @else
                                                    <div class="tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center tw-text-slate-200">
                                                        <i class="bi bi-image"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="tw-flex-1 tw-space-y-3">
                                                <div class="tw-flex tw-gap-3">
                                                    <div class="tw-flex-1">
                                                        <input type="text" name="items[{{ $index }}][design_code]" required value="{{ $item->design_code }}" placeholder="Design Code" 
                                                               class="design-code-input tw-w-full tw-bg-slate-50 tw-border-none tw-rounded-xl tw-px-4 tw-py-2.5 tw-text-sm tw-font-bold tw-text-slate-700 placeholder:tw-text-slate-400 focus:tw-ring-2 focus:tw-ring-indigo-500/10 tw-transition-all">
                                                    </div>
                                                    <div class="tw-flex-1">
                                                        <select name="items[{{ $index }}][category_name]" class="category-select tw-w-full tw-bg-slate-50 tw-border-none tw-rounded-xl tw-px-4 tw-py-2.5 tw-text-sm tw-font-bold tw-text-slate-700 focus:tw-ring-2 focus:tw-ring-indigo-500/10 tw-transition-all">
                                                            <option value="">Category</option>
                                                            @foreach($categories as $cat)
                                                                <option value="{{ $cat->name }}" {{ $item->category_name == $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <input type="text" name="items[{{ $index }}][item_notes]" value="{{ $item->item_notes }}" placeholder="Item specific notes..." 
                                                       class="tw-w-full tw-bg-slate-50 tw-border-none tw-rounded-xl tw-px-4 tw-py-2.5 tw-text-xs tw-font-medium tw-text-slate-600 placeholder:tw-text-slate-400 focus:tw-ring-2 focus:tw-ring-indigo-500/10 tw-transition-all">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        <div class="tw-space-y-2">
                                            <div class="tw-flex tw-gap-2">
                                                <input type="text" name="items[{{ $index }}][weight_from]" value="{{ $item->weight_from }}" placeholder="From" class="weight-from-input tw-w-full tw-bg-slate-50 tw-border-none tw-rounded-xl tw-px-3 tw-py-2.5 tw-text-xs tw-font-bold">
                                                <input type="text" name="items[{{ $index }}][weight_to]" value="{{ $item->weight_to }}" placeholder="To" class="weight-to-input tw-w-full tw-bg-slate-50 tw-border-none tw-rounded-xl tw-px-3 tw-py-2.5 tw-text-xs tw-font-bold">
                                            </div>
                                            <input type="text" name="items[{{ $index }}][size]" value="{{ $item->size }}" placeholder="Size" class="size-input tw-w-full tw-bg-slate-50 tw-border-none tw-rounded-xl tw-px-3 tw-py-2.5 tw-text-xs tw-font-bold">
                                        </div>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        <div class="tw-space-y-2">
                                            <input type="number" name="items[{{ $index }}][quantity]" required min="1" value="{{ $item->quantity }}" placeholder="Qty" class="tw-w-full tw-bg-slate-50 tw-border-none tw-rounded-xl tw-px-3 tw-py-2.5 tw-text-xs tw-font-bold">
                                            <input type="number" name="items[{{ $index }}][grams]" step="0.001" value="{{ $item->grams }}" placeholder="Grams" class="grams-input tw-w-full tw-bg-slate-50 tw-border-none tw-rounded-xl tw-px-3 tw-py-2.5 tw-text-xs tw-font-bold">
                                        </div>
                                    </td>
                                    <td class="tw-px-6 tw-py-4 tw-text-right">
                                        <button type="button" class="removeItemBtn tw-text-slate-300 hover:tw-text-red-500 tw-transition-colors">
                                            <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('itemsContainer');
        const addBtn = document.getElementById('addItemBtn');
        const html5QrCode = new Html5Qrcode("reader");
        const batchMode = document.getElementById('batch-mode');
        const scanStatus = document.getElementById('scan-status');
        let rowIdx = {{ count($stockOrder->items) }};

        function playBeep() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();
                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(880, audioCtx.currentTime);
                gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);
                oscillator.start();
                oscillator.stop(audioCtx.currentTime + 0.1);
            } catch (e) {}
        }

        function showStatus(message, isError = false) {
            if (!isError && message.includes('Added')) playBeep();
            scanStatus.innerText = message;
            scanStatus.classList.remove('tw-bg-indigo-600', 'tw-bg-red-600');
            scanStatus.classList.add(isError ? 'tw-bg-red-600' : 'tw-bg-indigo-600');
            scanStatus.classList.remove('tw-translate-y-full');
            setTimeout(() => scanStatus.classList.add('tw-translate-y-full'), 2500);
        }

        async function lookupAndAdd(code, isScan = false) {
            if (!code) return;
            
            if (isScan && !batchMode.checked) {
                Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            } else {
                showStatus('Searching Design...');
            }

            try {
                const response = await fetch(`{{ url('super-admin/stock-order/lookup') }}/${encodeURIComponent(code)}`);
                const data = await response.json();

                if (data.success) {
                    addRow(data.product);
                    if (isScan) {
                        showStatus(`Added: ${data.product.design_code}`);
                        if (!batchMode.checked) Swal.close();
                    }
                } else {
                    if (isScan) {
                        showStatus(data.message, true);
                        Swal.fire({ icon: 'error', title: 'Not Found', text: data.message, timer: 2000 });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Not Found', text: data.message });
                    }
                }
            } catch (error) {
                showStatus('Error searching design', true);
            } finally {
                if (isScan) {
                    setTimeout(() => {
                        try { html5QrCode.resume(); showStatus('Scanner Ready'); } catch (e) {}
                    }, 1500);
                }
            }
        }

        function addRow(data = null) {
            const row = document.querySelector('.item-row').cloneNode(true);
            
            // Update names and clear values
            row.querySelectorAll('input, select').forEach(el => {
                el.name = el.name.replace(/\[\d+\]/, `[${rowIdx}]`);
                if (!data) el.value = '';
                // Ensure required attribute is set for design_code
                if (el.classList.contains('design-code-input')) el.required = true;
            });

            if (data) {
                row.querySelector('.design-code-input').value = data.design_code;
                row.querySelector('.category-select').value = data.category;
                row.querySelector('.weight-from-input').value = data.weight_from || '';
                row.querySelector('.weight-to-input').value = data.weight_to || '';
                row.querySelector('.size-input').value = data.size || '';
                row.querySelector('.grams-input').value = data.weight_from || ''; // Default grams to weight_from
                
                if (data.image) {
                    row.querySelector('.item-image-container').innerHTML = `<img src="${data.image}" class="tw-w-full tw-h-full tw-object-contain tw-rounded-lg">`;
                }
            } else {
                // Clear image container for manual new row
                row.querySelector('.item-image-container').innerHTML = '<div class="tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center tw-text-slate-200"><i class="bi bi-image"></i></div>';
            }

            // If we are scanning and the only row is empty, replace it
            const rows = container.querySelectorAll('.item-row');
            if (data && rows.length === 1 && !rows[0].querySelector('.design-code-input').value) {
                rows[0].remove();
            }

            container.appendChild(row);
            rowIdx++;
        }

        addBtn.addEventListener('click', () => addRow());

        container.addEventListener('click', function(e) {
            if (e.target.closest('.removeItemBtn')) {
                const rows = container.querySelectorAll('.item-row');
                if (rows.length > 1) {
                    e.target.closest('.item-row').remove();
                } else {
                    // Just clear the first row if it's the last one
                    const row = rows[0];
                    row.querySelectorAll('input').forEach(i => i.value = '');
                    row.querySelector('select').selectedIndex = 0;
                    row.querySelector('.item-image-container').innerHTML = '<div class="tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center tw-text-slate-200"><i class="bi bi-image"></i></div>';
                }
            }
        });

        // Scanner Initialization
        function onScanSuccess(decodedText) {
            html5QrCode.pause(true);
            let designCode = decodedText;
            if (decodedText.includes('/')) {
                const parts = decodedText.split('/');
                const lastPart = parts[parts.length - 1];
                if (!isNaN(lastPart)) designCode = lastPart;
            }
            lookupAndAdd(designCode, true);
        }

        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        
        function startScanner() {
            Html5Qrcode.getCameras().then(cameras => {
                if (cameras && cameras.length > 0) {
                    document.getElementById('scanner-overlay').classList.add('tw-opacity-0');
                    showStatus('Starting Camera...');
                    
                    html5QrCode.start(
                        { facingMode: "environment" }, 
                        config, 
                        onScanSuccess
                    ).then(() => {
                        showStatus('Scanner Ready');
                    }).catch(err => {
                        console.warn("Environment camera failed, trying default...", err);
                        html5QrCode.start(cameras[0].id, config, onScanSuccess).then(() => {
                            showStatus('Scanner Ready');
                        });
                    });
                } else {
                    throw new Error("No cameras found");
                }
            }).catch(err => {
                console.error("Camera detection failed", err);
                document.getElementById('scanner-overlay').innerHTML = `
                    <i class="bi bi-camera-video-off tw-text-4xl tw-text-red-400 tw-mb-3"></i>
                    <p class="tw-text-white tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest tw-text-center tw-px-4">Camera Error: ${err.message || 'Permissions required'}</p>
                    <button onclick="location.reload()" class="tw-mt-4 tw-px-4 tw-py-2 tw-bg-indigo-600 tw-text-white tw-text-[10px] tw-font-black tw-uppercase tw-rounded-lg">Retry</button>
                `;
            });
        }

        setTimeout(startScanner, 500);
    });
</script>
@endpush
@endsection
