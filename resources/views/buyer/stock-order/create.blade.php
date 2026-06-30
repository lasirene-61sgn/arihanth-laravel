@extends('buyer.layouts.app')

@section('title', 'New Live Stock Order')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight uppercase">New Live Stock Order</h1>
            <p class="text-slate-500 text-sm font-medium">Scan design QR codes to build your order</p>
        </div>
        <a href="{{ route('buyer.stock-order.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
            <i class="bi bi-x-lg text-2xl"></i>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Scanner Section -->
        <div class="space-y-6">
            <div class="bg-slate-900 rounded-3xl overflow-hidden shadow-2xl relative border-4 border-white">
                <div id="reader" style="width: 100%; min-height: 300px;"></div>
                <div id="scanner-overlay" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900/80 z-10 pointer-events-none transition-opacity duration-300">
                    <i class="bi bi-camera text-4xl text-white mb-3 animate-pulse"></i>
                    <p class="text-white text-xs font-black uppercase tracking-widest">Awaiting Camera Access</p>
                </div>
                <!-- Status Bar -->
                <div id="scan-status" class="absolute bottom-0 left-0 right-0 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest py-2 px-4 translate-y-full transition-transform duration-300 z-20">
                    Item Added Successfully
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Scanner Mode</h3>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-slate-400">Single</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="batch-mode" class="sr-only peer" checked>
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                            <span class="text-[10px] font-bold text-indigo-600">Batch Scan</span>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-400 leading-relaxed italic">Batch Scan mode allows you to scan designs one after another without stopping. The scanner will automatically resume after each item is added.</p>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Manual Entry</h3>
                        <button onclick="toggleBulkModal()" class="text-[10px] font-bold text-indigo-600 hover:underline">Bulk Add Codes</button>
                    </div>
                    <div class="flex gap-2">
                        <input type="text" id="manual-code" class="flex-1 bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500" placeholder="Type design code...">
                        <button onclick="lookupProduct(document.getElementById('manual-code').value)" class="bg-slate-900 text-white px-6 rounded-xl font-bold hover:bg-slate-800 transition-all">Add</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Add Modal -->
        <div id="bulk-modal" class="fixed inset-0 z-[110] hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-3xl p-8 max-w-lg w-full shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Bulk Design Entry</h3>
                    <button onclick="toggleBulkModal()" class="text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg"></i></button>
                </div>
                <p class="text-slate-500 text-xs mb-4">Enter design codes separated by commas, spaces, or new lines.</p>
                <textarea id="bulk-codes" rows="6" class="w-full bg-slate-50 border border-slate-100 rounded-2xl p-4 text-sm font-mono focus:ring-2 focus:ring-indigo-500 outline-none mb-6" placeholder="DS-101, DS-102&#10;DS-205"></textarea>
                <div class="flex gap-3">
                    <button onclick="toggleBulkModal()" class="flex-1 px-6 py-3 border border-slate-200 text-slate-600 rounded-2xl font-bold hover:bg-slate-50 transition-all">Cancel</button>
                    <button onclick="processBulkCodes()" class="flex-1 px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">Add All Designs</button>
                </div>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm flex flex-col min-h-[500px]">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50 rounded-t-3xl">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Live Cart</h3>
                <span id="cart-count" class="bg-indigo-600 text-white text-[10px] font-black px-2 py-1 rounded-full">0/20</span>
            </div>

            <div id="cart-items" class="flex-1 overflow-y-auto p-4 space-y-4 max-h-[400px]">
                <div id="empty-cart" class="h-full flex flex-col items-center justify-center py-20 text-center">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                        <i class="bi bi-qr-code-scan text-2xl text-slate-300"></i>
                    </div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">No items scanned yet</p>
                </div>
            </div>

            <div class="p-6 border-t border-slate-50 space-y-4">
                <div class="flex justify-between items-center px-2">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Weight</span>
                    <span id="grand-total-weight" class="text-lg font-black text-slate-900 tracking-tight">0.000g</span>
                </div>
                <textarea id="order-notes" rows="2" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500" placeholder="Add any specific instructions..."></textarea>
                <button id="submit-order" onclick="submitOrder()" disabled class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-indigo-100 disabled:opacity-50 disabled:bg-slate-300 disabled:shadow-none hover:bg-indigo-700 transition-all active:scale-[0.98]">
                    Confirm Stock Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="success-modal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl p-8 max-w-sm w-full text-center shadow-2xl scale-95 transition-transform duration-300" id="modal-content">
        <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="bi bi-check-lg text-4xl"></i>
        </div>
        <h2 class="text-2xl font-black text-slate-900 mb-2 uppercase">Order Placed!</h2>
        <p class="text-slate-500 text-sm mb-8">Your live stock order has been submitted successfully.</p>
        <a href="{{ route('buyer.stock-order.index') }}" class="block w-full bg-slate-900 text-white py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-slate-800 transition-all">Go to Orders</a>
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

    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
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
            oscillator.frequency.setValueAtTime(880, audioCtx.currentTime); // A5 note
            gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);

            oscillator.start();
            oscillator.stop(audioCtx.currentTime + 0.1);
        } catch (e) {
            console.log("Audio not supported or blocked:", e);
        }
    }

    function showStatus(message, isError = false) {
        if (!isError && message.includes('Added')) {
            playBeep();
        }
        scanStatus.innerText = message;
        scanStatus.classList.remove('bg-indigo-600', 'bg-red-600');
        scanStatus.classList.add(isError ? 'bg-red-600' : 'bg-indigo-600');
        scanStatus.classList.remove('translate-y-full');
        setTimeout(() => {
            scanStatus.classList.add('translate-y-full');
        }, 2500);
    }

    function onScanSuccess(decodedText, decodedResult) {
        if (html5QrCode) html5QrCode.pause(true);
        console.log("Scanned text:", decodedText);
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
            Swal.fire({
                title: 'Processing...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        } else {
            showStatus('Searching Design...');
        }

        try {
            const encodedCode = encodeURIComponent(code);
            const response = await fetch(`{{ url('buyer/stock-order/lookup') }}/${encodedCode}`);
            const data = await response.json();

            if (data.success) {
                const added = addToCart(data.product, isBatch && isScan);
                if (isBatch && isScan) {
                    if (added) {
                        showStatus(`Added: ${data.product.design_code}`);
                    }
                } else {
                    Swal.close();
                }
            } else {
                if (isBatch && isScan) {
                    showStatus(data.message, true);
                    // For batch mode errors, we still pause the scanner and show a proper alert to get user's attention
                    Swal.fire({
                        icon: 'error',
                        title: 'Not Found',
                        text: data.message,
                        timer: 2000
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Not Found',
                        text: data.message,
                        timer: 2000
                    });
                }
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Something went wrong. Please try again.'
            });
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
        // Prevent adding the same design multiple times at the top level
        // instead, we'll allow adding rows inside it.
        if (cart.find(item => item.id === product.id)) {
            if (!isSilent) {
                Swal.fire({ icon: 'info', title: 'Already Added', text: 'This design is already in your cart. You can add more rows inside it.' });
            }
            return false;
        }

        if (cart.length >= 20) {
            Swal.fire({ icon: 'warning', title: 'Limit Reached', text: 'Maximum 20 designs allowed.' });
            return false;
        }

        // Initialize design with one default variant row
        const newItem = {
            ...product,
            cart_id: ++cart_counter,
            variants: [
                {
                    row_id: Date.now(),
                    quantity: 1,
                    grams: parseFloat(product.weight_from) || 0
                }
            ],
            item_notes: ''
        };

        cart.push(newItem);
        renderCart();
        return true;
    }

    window.toggleBulkModal = function() {
        const modal = document.getElementById('bulk-modal');
        modal.classList.toggle('hidden');
        if (!modal.classList.contains('hidden')) document.getElementById('bulk-codes').focus();
    }

    window.processBulkCodes = async function() {
        const input = document.getElementById('bulk-codes').value;
        const codes = input.split(/[\s,\n]+/).filter(c => c.trim().length > 0);
        
        if (codes.length === 0) {
            toggleBulkModal();
            return;
        }

        toggleBulkModal();
        Swal.fire({
            title: 'Processing Bulk Entry',
            text: `Checking ${codes.length} designs...`,
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        let successCount = 0;
        let failCount = 0;

        for (const code of codes) {
            try {
                const encodedCode = encodeURIComponent(code);
                const response = await fetch(`{{ url('buyer/stock-order/lookup') }}/${encodedCode}`);
                const data = await response.json();

                if (data.success) {
                    if (addToCart(data.product, true)) {
                        successCount++;
                    }
                } else {
                    failCount++;
                }
            } catch (e) {
                failCount++;
            }
        }

        Swal.fire({
            icon: successCount > 0 ? 'success' : 'info',
            title: 'Bulk Entry Complete',
            text: `Successfully added ${successCount} items. ${failCount} items failed or were duplicates.`,
            timer: 3000
        });
        
        document.getElementById('bulk-codes').value = '';
    }

    window.addVariantRow = function(cart_id) {
        const item = cart.find(i => i.cart_id === cart_id);
        if (item) {
            item.variants.push({
                row_id: Date.now() + Math.random(),
                quantity: 1,
                grams: parseFloat(item.weight_from) || 0
            });
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

    function adjustVariantQty(cart_id, row_id, delta) {
        const item = cart.find(i => i.cart_id === cart_id);
        if (item) {
            const variant = item.variants.find(v => v.row_id === row_id);
            if (variant) {
                variant.quantity = Math.max(1, (parseInt(variant.quantity) || 0) + delta);
                renderCart();
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
                // Update row total display without full re-render
                const rowTotalEl = document.getElementById(`total-grams-${row_id}`);
                if (rowTotalEl) {
                    const q = parseFloat(variant.quantity) || 0;
                    const g = parseFloat(variant.grams) || 0;
                    rowTotalEl.innerText = (q * g).toFixed(3) + 'g';
                }
            }
        }
    }

    window.updateItemNote = function(cart_id, value) {
        const item = cart.find(i => i.cart_id === cart_id);
        if (item) item.item_notes = value;
    }

    function updateGrandTotal() {
        let totalWeight = 0;
        let totalPieces = 0;

        cart.forEach(item => {
            item.variants.forEach(v => {
                const q = parseFloat(v.quantity) || 0;
                const g = parseFloat(v.grams) || 0;
                totalWeight += (q * g);
                totalPieces += q;
            });
        });
        
        const weightEl = document.getElementById('grand-total-weight');
        if (weightEl) weightEl.innerText = totalWeight.toFixed(3) + 'g';

        const countEl = document.getElementById('cart-count');
        if (countEl) countEl.innerText = `${cart.length} Designs / ${totalPieces} Pcs`;
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
            if (document.getElementById('empty-cart')) {
                document.getElementById('empty-cart').remove();
            }
            cartItemsContainer.innerHTML = cart.map(item => `
                <div class="p-5 bg-white rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/40 group animate-in slide-in-from-right duration-300 space-y-5">
                    <div class="flex gap-5">
                        <div class="w-20 h-20 rounded-2xl overflow-hidden bg-slate-50 shrink-0 border border-slate-100 p-1">
                            ${item.image ? `<img src="${item.image}" class="w-full h-full object-contain rounded-xl">` : `<div class="w-full h-full flex items-center justify-center text-slate-200"><i class="bi bi-image"></i></div>`}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight truncate">${item.product_name}</h4>
                                    <div class="text-[10px] font-bold text-indigo-600 mt-0.5 tracking-wider uppercase">${item.design_code}</div>
                                </div>
                                <button onclick="window.removeFromCart(${item.cart_id})" class="text-slate-300 hover:text-red-500 transition-colors">
                                    <i class="bi bi-x-circle-fill text-xl"></i>
                                </button>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span class="text-[8px] bg-slate-100 px-2 py-0.5 rounded-full text-slate-500 font-black uppercase tracking-widest">${item.category}</span>
                                <span class="text-[8px] bg-indigo-50 px-2 py-0.5 rounded-full text-indigo-600 font-black uppercase tracking-widest">${item.weight_from}-${item.weight_to}g Range</span>
                                <span class="text-[8px] bg-amber-50 px-2 py-0.5 rounded-full text-amber-600 font-black uppercase tracking-widest">Size: ${item.size || 'N/A'}</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h5 class="text-[10px] font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                <i class="bi bi-list-check text-indigo-500"></i>
                                Grams & Quantity
                            </h5>
                        </div>

                        <div class="space-y-3" id="variants-${item.cart_id}">
                            ${item.variants.map((v, idx) => `
                                <div class="grid grid-cols-12 gap-3 items-center animate-in slide-in-from-top-2 duration-200">
                                    <div class="col-span-4">
                                        <div class="relative group">
                                            <input type="number" step="0.001" value="${v.grams}" placeholder="Grams"
                                                onchange="window.updateVariantField(${item.cart_id}, ${v.row_id}, 'grams', this.value)"
                                                class="w-full bg-slate-50 border border-slate-100 rounded-xl px-3 py-2.5 text-xs font-bold focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder:text-slate-300">
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[8px] font-black text-slate-300 uppercase">Grams</span>
                                        </div>
                                    </div>
                                    <div class="col-span-3">
                                        <div class="relative group">
                                            <input type="number" value="${v.quantity}" placeholder="Qty"
                                                onchange="window.updateVariantField(${item.cart_id}, ${v.row_id}, 'quantity', this.value)"
                                                class="w-full bg-slate-50 border border-slate-100 rounded-xl px-3 py-2.5 text-xs font-bold focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder:text-slate-300 text-center">
                                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[8px] font-black text-slate-300 uppercase">Qty</span>
                                        </div>
                                    </div>
                                    <div class="col-span-4">
                                        <div class="bg-indigo-50/50 rounded-xl px-3 py-2 border border-indigo-100/50 flex flex-col justify-center h-[42px]">
                                            <p class="text-[7px] font-black text-indigo-400 uppercase tracking-widest leading-none mb-1">Row Total</p>
                                            <p id="total-grams-${v.row_id}" class="text-xs font-black text-indigo-600 leading-none">
                                                ${(v.quantity * v.grams).toFixed(3)}g
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-span-1">
                                        @if($item->variants ?? true)
                                            ${item.variants.length > 1 ? `
                                                <button onclick="window.removeVariantRow(${item.cart_id}, ${v.row_id})" class="text-slate-300 hover:text-red-500 transition-colors p-1">
                                                    <i class="bi bi-trash3 text-sm"></i>
                                                </button>
                                            ` : ''}
                                        @endif
                                    </div>
                                </div>
                            `).join('')}
                        </div>

                        <button onclick="window.addVariantRow(${item.cart_id})" class="w-full py-2.5 bg-slate-50 border border-dashed border-slate-200 rounded-xl text-[9px] font-black text-slate-400 uppercase tracking-widest hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-600 transition-all flex items-center justify-center gap-2 group">
                            <i class="bi bi-plus-circle text-lg group-hover:scale-110 transition-transform"></i>
                            Add More Grams/Qty
                        </button>
                    </div>

                    <div class="pt-2">
                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest block mb-2 px-1">Special Instructions for this Design</label>
                        <input type="text" value="${item.item_notes || ''}" onchange="window.updateItemNote(${item.cart_id}, this.value)" 
                            class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none transition-all italic placeholder:text-slate-300" placeholder="e.g. Extra polish, custom hook...">
                    </div>
                </div>
            `).join('');
            submitBtn.disabled = false;
        }

        cartCountEl.innerText = `${cart.length}/20`;
        updateGrandTotal();
    }

    window.submitOrder = async function() {
        const notes = document.getElementById('order-notes').value;
        
        // Flatten variants for submission
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
            title: 'Confirm Order?',
            text: `You are about to place an order for ${flattenedItems.length} line items across ${cart.length} designs.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Yes, Submit'
        }).then(async (result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                try {
                    const response = await fetch(`{{ route('buyer.stock-order.store') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ items: flattenedItems, notes: notes })
                    });
                    const data = await response.json();
  
                    if (data.success) {
                        document.getElementById('success-modal').classList.remove('hidden');
                        setTimeout(() => {
                            document.getElementById('modal-content').classList.remove('scale-95');
                        }, 10);
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'Submission failed.', 'error');
                }
            }
        });
    }

    function initScanner() {
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        
        // Check for "add" query param
        const urlParams = new URLSearchParams(window.location.search);
        const addCode = urlParams.get('add');
        if (addCode) {
            if (addCode.includes(',')) {
                document.getElementById('bulk-codes').value = addCode;
                window.processBulkCodes();
            } else {
                window.lookupProduct(addCode);
            }
            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Start camera
        Html5Qrcode.getCameras().then(cameras => {
            if (cameras && cameras.length > 0) {
                document.getElementById('scanner-overlay').classList.add('opacity-0');
                html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess);
            }
        }).catch(err => {
            console.error(err);
            document.getElementById('scanner-overlay').innerHTML = `
                <i class="bi bi-camera-video-off text-4xl text-red-400 mb-3"></i>
                <p class="text-white text-xs font-black uppercase tracking-widest text-center px-4">Camera Error: Please ensure permissions are granted</p>
            `;
        });
    }

</script>
@endsection
