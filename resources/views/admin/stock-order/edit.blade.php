@extends('admin.layouts.app')

@section('title', 'Edit Live Stock Order')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('admin.stock-order.index') }}" class="text-slate-400 hover:text-indigo-600 transition-colors flex items-center gap-2 text-[10px] font-black uppercase tracking-widest mb-4">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path></svg>
            Back to Orders
        </a>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Edit Stock Order: {{ $stockOrder->order_number }}</h1>
        <p class="text-slate-500 text-sm font-medium">Modify order details and items</p>
    </div>

    <form action="{{ route('admin.stock-order.update', $stockOrder->id) }}" method="POST" id="stockOrderForm">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sidebar: Order Info -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sticky top-8">
                    <div class="mb-6">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Buyer Selection</label>
                        <select name="buyer_id" required class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/10 transition-all">
                            <option value="">Select a Buyer</option>
                            @foreach($buyers as $buyer)
                                <option value="{{ $buyer->id }}" {{ $stockOrder->buyer_id == $buyer->id ? 'selected' : '' }}>{{ $buyer->business_name }} ({{ $buyer->bp_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-6">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Order Notes</label>
                        <textarea name="notes" rows="4" placeholder="General order instructions..." 
                                  class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/10 transition-all">{{ $stockOrder->notes }}</textarea>
                    </div>

                    <div class="pt-6 border-t border-slate-50">
                        <button type="submit" class="w-full bg-indigo-600 text-white px-6 py-4 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center justify-center gap-2">
                            Update Stock Order
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content: Items -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Scanner Section -->
                <div class="bg-slate-900 rounded-[2rem] overflow-hidden shadow-2xl relative border-4 border-white">
                    <div id="reader" style="width: 100%; min-height: 300px;"></div>
                    <div id="scanner-overlay" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900/80 z-10 pointer-events-none transition-opacity duration-300">
                        <i class="bi bi-camera text-4xl text-white mb-3 animate-pulse"></i>
                        <p class="text-white text-[10px] font-black uppercase tracking-widest">Awaiting Camera Access</p>
                    </div>
                    <!-- Status Bar -->
                    <div id="scan-status" class="absolute bottom-0 left-0 right-0 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest py-3 px-6 translate-y-full transition-transform duration-300 z-20">
                        Scanner Ready
                    </div>
                </div>

                <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <i class="bi bi-qr-code-scan text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Scanner Mode</h3>
                            <p class="text-xs font-bold text-slate-900">Batch scan multiple designs</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="batch-mode" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:translate-x-[-100%] peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                        <h2 class="text-xs font-black text-slate-900 uppercase tracking-widest">Order Items</h2>
                        <button type="button" id="addItemBtn" class="text-indigo-600 hover:text-indigo-700 font-black text-[10px] uppercase tracking-widest flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                            Add Row
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Design Details</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest w-32">Weight / Size</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest w-32">Qty / Grams</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsContainer" class="divide-y divide-slate-50">
                                @foreach($stockOrder->items as $index => $item)
                                <tr class="item-row">
                                    <td class="px-6 py-4">
                                        <div class="flex gap-4">
                                            <div class="w-16 h-16 bg-slate-50 rounded-xl border border-slate-100 overflow-hidden shrink-0 p-1 item-image-container">
                                                @if($item->product && $item->product->image)
                                                    <img src="{{ asset('storage/' . $item->product->image) }}" class="w-full h-full object-contain rounded-lg">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-slate-200">
                                                        <i class="bi bi-image"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-1 space-y-3">
                                                <div class="flex gap-3">
                                                    <div class="flex-1">
                                                        <input type="text" name="items[{{ $index }}][design_code]" required value="{{ $item->design_code }}" placeholder="Design Code" 
                                                               class="design-code-input w-full bg-slate-50 border-none rounded-xl px-4 py-2.5 text-sm font-bold text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/10 transition-all">
                                                    </div>
                                                    <div class="flex-1">
                                                        <select name="items[{{ $index }}][category_name]" class="category-select w-full bg-slate-50 border-none rounded-xl px-4 py-2.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/10 transition-all">
                                                            <option value="">Category</option>
                                                            @foreach($categories as $cat)
                                                                <option value="{{ $cat->name }}" {{ $item->category_name == $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <input type="text" name="items[{{ $index }}][item_notes]" value="{{ $item->item_notes }}" placeholder="Item specific notes..." 
                                                       class="w-full bg-slate-50 border-none rounded-xl px-4 py-2.5 text-xs font-medium text-slate-600 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/10 transition-all">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-2">
                                            <div class="flex gap-2">
                                                <input type="text" name="items[{{ $index }}][weight_from]" value="{{ $item->weight_from }}" placeholder="From" class="weight-from-input w-full bg-slate-50 border-none rounded-xl px-3 py-2.5 text-xs font-bold">
                                                <input type="text" name="items[{{ $index }}][weight_to]" value="{{ $item->weight_to }}" placeholder="To" class="weight-to-input w-full bg-slate-50 border-none rounded-xl px-3 py-2.5 text-xs font-bold">
                                            </div>
                                            <input type="text" name="items[{{ $index }}][size]" value="{{ $item->size }}" placeholder="Size" class="size-input w-full bg-slate-50 border-none rounded-xl px-3 py-2.5 text-xs font-bold">
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-2">
                                            <input type="number" name="items[{{ $index }}][quantity]" required min="1" value="{{ $item->quantity }}" placeholder="Qty" class="w-full bg-slate-50 border-none rounded-xl px-3 py-2.5 text-xs font-bold">
                                            <input type="number" name="items[{{ $index }}][grams]" step="0.001" value="{{ $item->grams }}" placeholder="Grams" class="grams-input w-full bg-slate-50 border-none rounded-xl px-3 py-2.5 text-xs font-bold">
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button type="button" class="removeItemBtn text-slate-300 hover:text-red-500 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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
            scanStatus.classList.remove('bg-indigo-600', 'bg-red-600');
            scanStatus.classList.add(isError ? 'bg-red-600' : 'bg-indigo-600');
            scanStatus.classList.remove('translate-y-full');
            setTimeout(() => scanStatus.classList.add('translate-y-full'), 2500);
        }

        async function lookupAndAdd(code, isScan = false) {
            if (!code) return;
            
            if (isScan && !batchMode.checked) {
                Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            } else {
                showStatus('Searching Design...');
            }

            try {
                const response = await fetch(`{{ url('admin/stock-order/lookup') }}/${encodeURIComponent(code)}`);
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
                    row.querySelector('.item-image-container').innerHTML = `<img src="${data.image}" class="w-full h-full object-contain rounded-lg">`;
                }
            } else {
                // Clear image container for manual new row
                row.querySelector('.item-image-container').innerHTML = '<div class="w-full h-full flex items-center justify-center text-slate-200"><i class="bi bi-image"></i></div>';
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
                    row.querySelector('.item-image-container').innerHTML = '<div class="w-full h-full flex items-center justify-center text-slate-200"><i class="bi bi-image"></i></div>';
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
                    document.getElementById('scanner-overlay').classList.add('opacity-0');
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
                    <i class="bi bi-camera-video-off text-4xl text-red-400 mb-3"></i>
                    <p class="text-white text-[10px] font-black uppercase tracking-widest text-center px-4">Camera Error: ${err.message || 'Permissions required'}</p>
                    <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-indigo-600 text-white text-[10px] font-black uppercase rounded-lg">Retry</button>
                `;
            });
        }

        setTimeout(startScanner, 500);
    });
</script>
@endpush
@endsection
