@extends('super-admin.layouts.app')

@section('title', 'New Live Stock Order')

@section('content')
<div class="tw-max-w-6xl tw-mx-auto">
    <div class="tw-mb-8 tw-flex tw-justify-between tw-items-center">
        <div>
            <h1 class="tw-text-2xl tw-font-black tw-text-slate-900 tw-tracking-tight tw-uppercase">New Live Stock Order</h1>
            <p class="tw-text-slate-500 tw-text-sm tw-font-medium">Create a stock order for a buyer</p>
        </div>
        <a href="{{ route('super-admin.stock-order.index') }}" class="tw-text-slate-400 hover:tw-text-slate-600 tw-transition-colors">
            <i class="bi bi-x-lg tw-text-2xl"></i>
        </a>
    </div>

    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8">
        <!-- Left Side: Selection & Scanner -->
        <div class="tw-space-y-6">
            <!-- Buyer Selection -->
            <div class="tw-bg-white tw-rounded-3xl tw-p-6 tw-border tw-border-slate-100 tw-shadow-sm">
                <label class="tw-block tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest tw-mb-3">Select Buyer <span class="tw-text-rose-500">*</span></label>
                <select id="buyer_id" class="tw-w-full tw-bg-slate-50 tw-border-none tw-rounded-2xl tw-px-5 tw-py-4 tw-text-sm tw-font-bold tw-text-slate-900 focus:tw-ring-2 focus:tw-ring-indigo-500 transition-all">
                    <option value="">-- Choose Buyer --</option>
                    @foreach($buyers as $buyer)
                        <option value="{{ $buyer->id }}">{{ $buyer->business_name }} ({{ $buyer->bp_code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Scanner Section -->
            <div class="tw-bg-slate-900 tw-rounded-3xl tw-overflow-hidden tw-shadow-2xl tw-relative tw-border-4 tw-border-white">
                <div id="reader" style="width: 100%; min-height: 300px;"></div>
                <div id="scanner-overlay" class="tw-absolute tw-inset-0 tw-flex tw-flex-col tw-items-center tw-justify-center tw-bg-slate-900/80 tw-z-10 tw-pointer-events-none tw-transition-opacity tw-duration-300">
                    <i class="bi bi-camera tw-text-4xl tw-text-white tw-mb-3 tw-animate-pulse"></i>
                    <p class="tw-text-white tw-text-xs tw-font-black tw-uppercase tw-tracking-widest">Awaiting Camera Access</p>
                </div>
                <!-- Status Bar -->
                <div id="scan-status" class="tw-absolute tw-bottom-0 tw-left-0 tw-right-0 tw-bg-indigo-600 tw-text-white tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest tw-py-2 tw-px-4 tw-translate-y-full tw-transition-transform tw-duration-300 tw-z-20">
                    Item Added Successfully
                </div>
            </div>

            <div class="tw-flex tw-flex-col tw-gap-4">
                <div class="tw-bg-white tw-rounded-2xl tw-p-6 tw-border tw-border-slate-100 tw-shadow-sm">
                    <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                        <h3 class="tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest">Scanner Mode</h3>
                        <div class="tw-flex tw-items-center tw-gap-2">
                            <span class="tw-text-[10px] tw-font-bold tw-text-slate-400">Single</span>
                            <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                                <input type="checkbox" id="batch-mode" class="tw-sr-only tw-peer" checked>
                                <div class="tw-w-9 tw-h-5 tw-bg-slate-200 peer-focus:tw-outline-none tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full rtl:peer-checked:after:tw-translate-x-[-100%] peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-start-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-4 after:tw-w-4 after:tw-transition-all peer-checked:tw-bg-indigo-600"></div>
                            </label>
                            <span class="tw-text-[10px] tw-font-bold tw-text-indigo-600">Batch Scan</span>
                        </div>
                    </div>
                    <p class="tw-text-[10px] tw-text-slate-400 tw-leading-relaxed tw-italic">Batch Scan mode allows you to scan designs one after another without stopping.</p>
                </div>

                <div class="tw-bg-white tw-rounded-2xl tw-p-6 tw-border tw-border-slate-100 tw-shadow-sm">
                    <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                        <h3 class="tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest">Manual Entry</h3>
                        <button onclick="toggleBulkModal()" class="tw-text-[10px] tw-font-bold tw-text-indigo-600 hover:tw-underline">Bulk Add Codes</button>
                    </div>
                    <div class="tw-flex tw-gap-2">
                        <input type="text" id="manual-code" class="tw-flex-1 tw-bg-slate-50 tw-border-none tw-rounded-xl tw-px-4 tw-py-3 tw-text-sm tw-font-bold focus:tw-ring-2 focus:tw-ring-indigo-500" placeholder="Type design code...">
                        <button onclick="lookupProduct(document.getElementById('manual-code').value)" class="tw-bg-slate-900 tw-text-white tw-px-6 tw-rounded-xl tw-font-bold hover:tw-bg-slate-800 tw-transition-all">Add</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Add Modal -->
        <div id="bulk-modal" class="tw-fixed tw-inset-0 tw-z-[110] tw-hidden tw-flex tw-items-center tw-justify-center tw-p-4 tw-bg-slate-900/60 tw-backdrop-blur-sm">
            <div class="tw-bg-white tw-rounded-3xl tw-p-8 tw-max-w-lg tw-w-full tw-shadow-2xl">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
                    <h3 class="tw-text-lg tw-font-black tw-text-slate-900 tw-uppercase tw-tracking-tight">Bulk Design Entry</h3>
                    <button onclick="toggleBulkModal()" class="tw-text-slate-400 hover:tw-text-slate-600"><i class="bi bi-x-lg"></i></button>
                </div>
                <p class="tw-text-slate-500 tw-text-xs tw-mb-4">Enter design codes separated by commas, spaces, or new lines.</p>
                <textarea id="bulk-codes" rows="6" class="tw-w-full tw-bg-slate-50 tw-border tw-border-slate-100 tw-rounded-2xl tw-p-4 tw-text-sm tw-font-mono focus:tw-ring-2 focus:tw-ring-indigo-500 tw-outline-none tw-mb-6" placeholder="DS-101, DS-102&#10;DS-205"></textarea>
                <div class="tw-flex tw-gap-3">
                    <button onclick="toggleBulkModal()" class="tw-flex-1 tw-px-6 tw-py-3 tw-border tw-border-slate-200 tw-text-slate-600 tw-rounded-2xl tw-font-bold hover:tw-bg-slate-50 tw-transition-all">Cancel</button>
                    <button onclick="processBulkCodes()" class="tw-flex-1 tw-px-6 tw-py-3 tw-bg-indigo-600 tw-text-white tw-rounded-2xl tw-font-bold hover:tw-bg-indigo-700 tw-transition-all tw-shadow-lg tw-shadow-indigo-100">Add All Designs</button>
                </div>
            </div>
        </div>

        <!-- Right Side: Live Cart -->
        <div class="tw-bg-white tw-rounded-3xl tw-border tw-border-slate-100 tw-shadow-sm tw-flex tw-flex-col tw-min-h-[600px]">
            <div class="tw-p-6 tw-border-b tw-border-slate-50 tw-flex tw-justify-between tw-items-center tw-bg-slate-50/50 tw-rounded-t-3xl">
                <h3 class="tw-text-sm tw-font-black tw-text-slate-900 tw-uppercase tw-tracking-wider">Order Cart</h3>
                <span id="cart-count" class="tw-bg-indigo-600 tw-text-white tw-text-[10px] tw-font-black tw-px-2 tw-py-1 tw-rounded-full">0/20</span>
            </div>

            <div id="cart-items" class="tw-flex-1 tw-overflow-y-auto tw-p-4 tw-space-y-4 tw-max-h-[500px]">
                <div id="empty-cart" class="tw-h-full tw-flex tw-flex-col tw-items-center tw-justify-center tw-py-20 tw-text-center">
                    <div class="tw-w-16 tw-h-16 tw-bg-slate-50 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mb-4">
                        <i class="bi bi-qr-code-scan tw-text-2xl tw-text-slate-300"></i>
                    </div>
                    <p class="tw-text-slate-400 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest">No items scanned yet</p>
                </div>
            </div>

            <div class="tw-p-6 tw-border-t tw-border-slate-50 tw-space-y-4">
                <div class="tw-flex tw-justify-between tw-items-center tw-px-2">
                    <span class="tw-text-[10px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest">Total Weight</span>
                    <span id="grand-total-weight" class="tw-text-lg tw-font-black tw-text-slate-900 tw-tracking-tight">0.000g</span>
                </div>
                <textarea id="order-notes" rows="2" class="tw-w-full tw-bg-slate-50 tw-border-none tw-rounded-2xl tw-px-4 tw-py-3 tw-text-sm placeholder:tw-text-slate-400 focus:tw-ring-2 focus:tw-ring-indigo-500" placeholder="Add any specific instructions..."></textarea>
                <button id="submit-order" onclick="submitOrder()" disabled class="tw-w-full tw-bg-indigo-600 tw-text-white tw-py-4 tw-rounded-2xl tw-font-black tw-uppercase tw-tracking-widest tw-shadow-lg tw-shadow-indigo-100 disabled:tw-opacity-50 disabled:tw-bg-slate-300 disabled:tw-shadow-none hover:tw-bg-indigo-700 tw-transition-all active:tw-scale-[0.98]">
                    Create Stock Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="success-modal" class="tw-fixed tw-inset-0 tw-z-[100] tw-hidden tw-flex tw-items-center tw-justify-center tw-p-4 tw-bg-slate-900/60 tw-backdrop-blur-sm">
    <div class="tw-bg-white tw-rounded-3xl tw-p-8 tw-max-w-sm tw-w-full tw-text-center tw-shadow-2xl tw-scale-95 tw-transition-transform tw-duration-300" id="modal-content">
        <div class="tw-w-20 tw-h-20 tw-bg-emerald-100 tw-text-emerald-600 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6">
            <i class="bi bi-check-lg tw-text-4xl"></i>
        </div>
        <h2 class="tw-text-2xl tw-font-black tw-text-slate-900 tw-mb-2 tw-uppercase">Order Created!</h2>
        <p class="tw-text-slate-500 tw-text-sm tw-mb-8">The stock order has been created successfully for the selected buyer.</p>
        <a href="{{ route('super-admin.stock-order.index') }}" class="tw-block tw-w-full tw-bg-slate-900 tw-text-white tw-py-4 tw-rounded-2xl tw-font-black tw-uppercase tw-tracking-widest hover:tw-bg-slate-800 tw-transition-all">Go to Orders</a>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let cart = [];
    let html5QrCode = null;
    let cartItemsContainer, cartCountEl, submitBtn, emptyCartEl, scanStatus, batchMode;

    document.addEventListener('DOMContentLoaded', () => {
        cartItemsContainer = document.getElementById('cart-items');
        cartCountEl = document.getElementById('cart-count');
        submitBtn = document.getElementById('submit-order');
        emptyCartEl = document.getElementById('empty-cart');
        scanStatus = document.getElementById('scan-status');
        batchMode = document.getElementById('batch-mode');

        if (typeof Html5Qrcode !== 'undefined') {
            html5QrCode = new Html5Qrcode("reader");
            initScanner();
        }
    });

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

    function onScanSuccess(decodedText) {
        if (html5QrCode) html5QrCode.pause(true);
        let designCode = decodedText;
        if (decodedText.includes('/')) {
            const parts = decodedText.split('/');
            const lastPart = parts[parts.length - 1];
            if (!isNaN(lastPart)) designCode = lastPart;
        }
        window.lookupProduct(designCode, true);
    }

    window.lookupProduct = async function(code, isScan = false) {
        if (!code) return;
        const isBatch = batchMode.checked;
        if (!isBatch || !isScan) {
            Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        } else {
            showStatus('Searching Design...');
        }

        try {
            const response = await fetch(`{{ url('super-admin/stock-order/lookup') }}/${encodeURIComponent(code)}`);
            const data = await response.json();

            if (data.success) {
                const added = addToCart(data.product, isBatch && isScan);
                if (isBatch && isScan) {
                    if (added) showStatus(`Added: ${data.product.design_code}`);
                } else {
                    Swal.close();
                }
            } else {
                Swal.fire({ icon: 'error', title: 'Not Found', text: data.message, timer: 2000 });
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to lookup design.' });
        } finally {
            if (isScan && html5QrCode) {
                setTimeout(() => {
                    try { html5QrCode.resume(); showStatus('Scanner Ready'); } catch (e) {}
                }, 1500);
            }
        }
    }

    let cart_counter = 0;
    window.addToCart = function(product, isSilent = false) {
        if (cart.find(item => item.id === product.id)) {
            if (!isSilent) Swal.fire({ icon: 'info', title: 'Already Added', text: 'This design is already in your cart.' });
            return false;
        }

        if (cart.length >= 20) {
            Swal.fire({ icon: 'warning', title: 'Limit Reached', text: 'Max 20 designs allowed.' });
            return false;
        }

        const newItem = {
            ...product,
            cart_id: ++cart_counter,
            variants: [{ row_id: Date.now(), quantity: 1, grams: parseFloat(product.weight_from) || 0 }],
            item_notes: ''
        };

        cart.push(newItem);
        renderCart();
        return true;
    }

    window.toggleBulkModal = function() {
        const modal = document.getElementById('bulk-modal');
        modal.classList.toggle('tw-hidden');
        if (!modal.classList.contains('tw-hidden')) document.getElementById('bulk-codes').focus();
    }

    window.processBulkCodes = async function() {
        const input = document.getElementById('bulk-codes').value;
        const codes = input.split(/[\s,\n]+/).filter(c => c.trim().length > 0);
        if (codes.length === 0) { toggleBulkModal(); return; }

        toggleBulkModal();
        Swal.fire({ title: 'Processing Bulk Entry', text: `Checking ${codes.length} designs...`, allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        let successCount = 0;
        for (const code of codes) {
            try {
                const response = await fetch(`{{ url('super-admin/stock-order/lookup') }}/${encodeURIComponent(code)}`);
                const data = await response.json();
                if (data.success && addToCart(data.product, true)) successCount++;
            } catch (e) {}
        }

        Swal.fire({ icon: 'success', title: 'Bulk Entry Complete', text: `Added ${successCount} designs.`, timer: 2000 });
        document.getElementById('bulk-codes').value = '';
    }

    window.addVariantRow = function(cart_id) {
        const item = cart.find(i => i.cart_id === cart_id);
        if (item) {
            item.variants.push({ row_id: Date.now() + Math.random(), quantity: 1, grams: parseFloat(item.weight_from) || 0 });
            renderCart();
        }
    }

    window.removeVariantRow = function(cart_id, row_id) {
        const item = cart.find(i => i.cart_id === cart_id);
        if (item) {
            if (item.variants.length > 1) {
                item.variants = item.variants.filter(v => v.row_id !== row_id);
                renderCart();
            } else {
                removeFromCart(cart_id);
            }
        }
    }

    window.updateVariantField = function(cart_id, row_id, field, value) {
        const item = cart.find(i => i.cart_id === cart_id);
        if (item) {
            const variant = item.variants.find(v => v.row_id === row_id);
            if (variant) {
                variant[field] = value;
                updateGrandTotal();
                const totalEl = document.getElementById(`total-grams-${row_id}`);
                if (totalEl) totalEl.innerText = (parseFloat(variant.quantity || 0) * parseFloat(variant.grams || 0)).toFixed(3) + 'g';
            }
        }
    }

    window.updateItemNote = function(cart_id, value) {
        const item = cart.find(i => i.cart_id === cart_id);
        if (item) item.item_notes = value;
    }

    function updateGrandTotal() {
        let totalWeight = 0;
        let totalPcs = 0;
        cart.forEach(item => {
            item.variants.forEach(v => {
                totalWeight += (parseFloat(v.quantity || 0) * parseFloat(v.grams || 0));
                totalPcs += parseFloat(v.quantity || 0);
            });
        });
        const wtEl = document.getElementById('grand-total-weight');
        if (wtEl) wtEl.innerText = totalWeight.toFixed(3) + 'g';
        const ctEl = document.getElementById('cart-count');
        if (ctEl) ctEl.innerText = `${cart.length} Designs / ${totalPcs} Pcs`;
    }

    window.removeFromCart = function(cart_id) {
        cart = cart.filter(item => item.cart_id !== cart_id);
        renderCart();
    }

    function renderCart() {
        if (cart.length === 0) {
            cartItemsContainer.innerHTML = '';
            cartItemsContainer.appendChild(emptyCartEl);
            submitBtn.disabled = true;
        } else {
            if (document.getElementById('empty-cart')) document.getElementById('empty-cart').remove();
            cartItemsContainer.innerHTML = cart.map(item => `
                <div class="tw-p-5 tw-bg-white tw-rounded-[2rem] tw-border tw-border-slate-100 tw-shadow-xl tw-shadow-slate-200/40 tw-space-y-5 tw-animate-in tw-slide-in-from-right tw-duration-300">
                    <div class="tw-flex tw-gap-5">
                        <div class="tw-w-20 tw-h-20 tw-rounded-2xl tw-overflow-hidden tw-bg-slate-50 tw-shrink-0 tw-border tw-border-slate-100 tw-p-1">
                            ${item.image ? `<img src="${item.image}" class="tw-w-full tw-h-full tw-object-contain tw-rounded-xl">` : `<div class="tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center tw-text-slate-200"><i class="bi bi-image"></i></div>`}
                        </div>
                        <div class="tw-flex-1 tw-min-w-0">
                            <div class="tw-flex tw-justify-between tw-items-start">
                                <div>
                                    <h4 class="tw-text-xs tw-font-black tw-text-slate-900 tw-uppercase tw-tracking-tight tw-truncate">${item.product_name}</h4>
                                    <div class="tw-text-[10px] tw-font-bold tw-text-indigo-600 tw-mt-0.5 tw-tracking-wider tw-uppercase">${item.design_code}</div>
                                </div>
                                <button onclick="removeFromCart(${item.cart_id})" class="tw-text-slate-300 hover:tw-text-red-500 tw-transition-colors">
                                    <i class="bi bi-x-circle-fill tw-text-xl"></i>
                                </button>
                            </div>
                            <div class="tw-mt-2 tw-flex tw-flex-wrap tw-gap-2">
                                <span class="tw-text-[8px] tw-bg-slate-100 tw-px-2 tw-py-0.5 tw-rounded-full tw-text-slate-500 tw-font-black tw-uppercase tw-tracking-widest">${item.category}</span>
                                <span class="tw-text-[8px] tw-bg-indigo-50 tw-px-2 tw-py-0.5 tw-rounded-full tw-text-indigo-600 tw-font-black tw-uppercase tw-tracking-widest">${item.weight_from}-${item.weight_to}g Range</span>
                            </div>
                        </div>
                    </div>

                    <div class="tw-space-y-4">
                        <div class="tw-space-y-3">
                            ${item.variants.map((v, idx) => `
                                <div class="tw-grid tw-grid-cols-12 tw-gap-3 tw-items-center">
                                    <div class="tw-col-span-4">
                                        <div class="tw-relative">
                                            <input type="number" step="0.001" value="${v.grams}" 
                                                onchange="window.updateVariantField(${item.cart_id}, ${v.row_id}, 'grams', this.value)"
                                                class="tw-w-full tw-bg-slate-50 tw-border tw-border-slate-100 tw-rounded-xl tw-px-3 tw-py-2.5 tw-text-xs tw-font-bold focus:tw-ring-2 focus:tw-ring-indigo-500 tw-outline-none">
                                            <span class="tw-absolute tw-right-2 tw-top-1/2 -tw-translate-y-1/2 tw-text-[7px] tw-font-black tw-text-slate-300 tw-uppercase">Grams</span>
                                        </div>
                                    </div>
                                    <div class="tw-col-span-3">
                                        <div class="tw-relative">
                                            <input type="number" value="${v.quantity}" 
                                                onchange="window.updateVariantField(${item.cart_id}, ${v.row_id}, 'quantity', this.value)"
                                                class="tw-w-full tw-bg-slate-50 tw-border tw-border-slate-100 tw-rounded-xl tw-px-3 tw-py-2.5 tw-text-xs tw-font-bold focus:tw-ring-2 focus:tw-ring-indigo-500 tw-outline-none tw-text-center">
                                            <span class="tw-absolute tw-right-1 tw-top-1/2 -tw-translate-y-1/2 tw-text-[7px] tw-font-black tw-text-slate-300 tw-uppercase">Qty</span>
                                        </div>
                                    </div>
                                    <div class="tw-col-span-4">
                                        <div class="tw-bg-indigo-50/50 tw-rounded-xl tw-px-3 tw-py-2 tw-border tw-border-indigo-100/50">
                                            <p id="total-grams-${v.row_id}" class="tw-text-[10px] tw-font-black tw-text-indigo-600">
                                                ${(v.quantity * v.grams).toFixed(3)}g
                                            </p>
                                        </div>
                                    </div>
                                    <div class="tw-col-span-1">
                                        ${item.variants.length > 1 ? `
                                            <button onclick="removeVariantRow(${item.cart_id}, ${v.row_id})" class="tw-text-slate-300 hover:tw-text-red-500"><i class="bi bi-trash3"></i></button>
                                        ` : ''}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        <button onclick="addVariantRow(${item.cart_id})" class="tw-w-full tw-py-2 tw-bg-slate-50 tw-border tw-border-dashed tw-rounded-xl tw-text-[9px] tw-font-black tw-text-slate-400 tw-uppercase tw-tracking-widest hover:tw-bg-indigo-50 hover:tw-text-indigo-600 transition-all">
                            + Add Grams/Qty Row
                        </button>
                    </div>

                    <input type="text" value="${item.item_notes || ''}" onchange="updateItemNote(${item.cart_id}, this.value)" 
                        class="tw-w-full tw-bg-slate-50 tw-border tw-border-slate-100 tw-rounded-xl tw-px-4 tw-py-2.5 tw-text-[10px] focus:tw-ring-2 focus:tw-ring-indigo-500 tw-outline-none" placeholder="Item notes...">
                </div>
            `).join('');
            submitBtn.disabled = false;
        }
        updateGrandTotal();
    }

    window.submitOrder = async function() {
        const buyer_id = document.getElementById('buyer_id').value;
        if (!buyer_id) { Swal.fire('Error', 'Please select a buyer first.', 'error'); return; }

        const notes = document.getElementById('order-notes').value;
        let flattenedItems = [];
        cart.forEach(item => {
            item.variants.forEach(v => {
                flattenedItems.push({
                    id: item.id,
                    design_code: item.design_code,
                    category: item.category,
                    subcategory: item.subcategory,
                    weight_from: item.weight_from,
                    weight_to: item.weight_to,
                    size: item.size,
                    quantity: v.quantity,
                    grams: v.grams,
                    item_notes: item.item_notes,
                    image_raw: item.image_raw
                });
            });
        });

        if (flattenedItems.length === 0) return;

        Swal.fire({
            title: 'Create Order?',
            text: `Place ${flattenedItems.length} items for the selected buyer?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Create'
        }).then(async (result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                try {
                    const response = await fetch(`{{ route('super-admin.stock-order.store') }}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ buyer_id: buyer_id, items: flattenedItems, notes: notes })
                    });
                    const data = await response.json();
                    if (data.success) {
                        document.getElementById('success-modal').classList.remove('tw-hidden');
                    } else {
                        Swal.fire('Error', data.message || 'Failed to create order', 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Submission failed.', 'error');
                }
            }
        });
    }

    function initScanner() {
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        Html5Qrcode.getCameras().then(cameras => {
            if (cameras && cameras.length > 0) {
                document.getElementById('scanner-overlay').classList.add('tw-opacity-0');
                html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess);
            }
        }).catch(() => {
            document.getElementById('scanner-overlay').innerHTML = `<p class="tw-text-white tw-text-[10px]">Camera Error</p>`;
        });
    }

</script>
@endsection
