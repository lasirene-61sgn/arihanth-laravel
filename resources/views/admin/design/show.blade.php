@extends('admin.layouts.app')

@section('title', 'Design Details | ' . ($product->design_code ?? $product->product_code))

@section('content')
<div class="bg-gray-50 min-h-screen p-4 sm:p-6 font-sans">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <a href="{{ route('admin.design.index') }}" class="text-gray-400 hover:text-magenta-800 text-sm mb-2 inline-flex items-center transition-colors">
                    <i class="bi bi-arrow-left me-1"></i> Back to Designs
                </a>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                    {{ $product->product_name }}
                    <span class="ms-3 text-lg font-mono text-magenta-800 bg-magenta-50 px-3 py-1 rounded-md border border-magenta-100">
                        {{ $product->design_status === 'Accepted' ? ($product->design_code ?? $product->product_code) : $product->product_code }}
                    </span>
                </h1>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($product->design_status != 'Accepted')
                <button type="button" onclick="showAcceptModal('{{ $product->id }}', '{{ $product->product_code }}')" class="px-4 py-2 bg-emerald-600 text-white rounded-lg font-bold hover:bg-emerald-700 shadow-sm transition flex items-center">
                    <i class="bi bi-check-lg me-2"></i> Accept Design
                </button>
                @endif

                @if($product->design_status == 'Pending' || empty($product->design_status))
                <form action="{{ route('admin.design.reject', $product) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Reject this design?')" class="px-4 py-2 bg-white text-red-600 border border-red-200 rounded-lg font-bold hover:bg-red-50 hover:border-red-300 shadow-sm transition flex items-center">
                        <i class="bi bi-x-lg me-2"></i> Reject
                    </button>
                </form>
                @endif
                
                <button onclick="showUnlockModalSingle('{{ $product->id }}', '{{ $product->design_code ?? $product->product_code }}', '{{ $product->images->first() ? (str_starts_with($product->images->first()->path, 'http') ? $product->images->first()->path : asset('storage/' . $product->images->first()->path)) : '' }}')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 shadow-sm transition flex items-center">
                    <i class="bi bi-unlock me-2"></i> Unlock Access
                </button>

                <form action="{{ route('admin.design.toggle-lock', $product) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 {{ $product->is_locked ? 'bg-rose-50 text-rose-600 border-rose-200' : 'bg-emerald-50 text-emerald-600 border-emerald-200' }} border rounded-lg font-bold hover:opacity-80 transition flex items-center">
                        <i class="bi {{ $product->is_locked ? 'bi-lock-fill' : 'bi-unlock-fill' }} me-2"></i>
                        {{ $product->is_locked ? 'Locked' : 'Unlocked' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Image Column -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sticky top-6">
                @php
                    $images = $product->images;
                    $hasImages = $images->count() > 0;
                @endphp

                @if($hasImages)
                    <div class="relative aspect-square bg-gray-50 rounded-lg overflow-hidden mb-4 group border border-gray-100">
                        @php
                            $firstImage = $images->first()->path;
                            $mainImgUrl = str_starts_with($firstImage, 'http') ? $firstImage : asset('storage/' . $firstImage);
                        @endphp
                        <img src="{{ $mainImgUrl }}" 
                             id="mainImage" 
                             class="w-full h-full object-contain cursor-zoom-in transition-transform duration-500 group-hover:scale-105" 
                             onclick="window.openUniversalPreview('{{ $mainImgUrl }}', 'image')">
                        
                        <div class="absolute bottom-2 right-2 bg-black/60 text-white text-[10px] px-2 py-1 rounded backdrop-blur-sm">
                            <i class="bi bi-arrows-fullscreen"></i>
                        </div>
                    </div>

                    @if($images->count() > 1)
                    <div class="grid grid-cols-4 gap-2">
                        @foreach($images as $img)
                            @php
                                $thumbUrl = str_starts_with($img->path, 'http') ? $img->path : asset('storage/' . $img->path);
                            @endphp
                            <div class="aspect-square rounded-md overflow-hidden border border-gray-100 cursor-pointer hover:border-magenta-500 hover:ring-2 hover:ring-magenta-50 transition"
                                 onclick="document.getElementById('mainImage').src = '{{ $thumbUrl }}'">
                                <img src="{{ $thumbUrl }}" class="w-full h-full object-cover">
                            </div>
                        @endforeach
                    </div>
                    @endif
                @else
                    <div class="aspect-square bg-gray-50 rounded-lg border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400">
                        <i class="bi bi-image text-4xl mb-2"></i>
                        <span class="text-sm">No Image Available</span>
                    </div>
                @endif

                {{-- QR Code Section --}}
                @if($product->design_status === 'Accepted' && $product->qr_code)
                <div class="mt-4 bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                    <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">QR Code</h4>
                    <div class="flex justify-center">
                        @if(str_ends_with($product->qr_code, '.svg'))
                            <img src="{{ asset('storage/' . $product->qr_code) }}" alt="QR Code" class="w-40 h-40">
                        @else
                            <img src="{{ asset('storage/' . $product->qr_code) }}" alt="QR Code" class="w-40 h-40 object-contain">
                        @endif
                    </div>
                    <p class="text-center text-[10px] text-gray-400 mt-2 font-mono font-bold">{{ $product->design_code }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Details Column -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-50 bg-gray-50/30 px-6 py-4">
                    <h3 class="font-bold text-gray-800">Design Specifications</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                        <!-- Product Code -->
                        <div>
                            <dt class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Product Code</dt>
                            <dd class="text-sm font-semibold text-gray-800">{{ $product->product_code }}</dd>
                        </div>
                        
                        <!-- Design Code -->
                        <div>
                            <dt class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Design Code</dt>
                            <dd class="text-sm font-mono font-bold text-magenta-800 bg-magenta-50 inline-block px-2 py-0.5 rounded border border-magenta-100">
                                {{ $product->design_code ?? 'Not Generated' }}
                            </dd>
                        </div>

                        <!-- Category -->
                        <div>
                            <dt class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Category</dt>
                            <dd class="text-sm text-gray-700 font-medium">{{ $product->category->name ?? 'N/A' }}</dd>
                        </div>

                        <!-- Sub Category -->
                        <div>
                            <dt class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Sub Category</dt>
                            <dd class="text-sm text-gray-700 font-medium">{{ $product->subcategory->name ?? 'N/A' }}</dd>
                        </div>

                        <!-- Status -->
                        <div>
                            <dt class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Approval Status</dt>
                            <dd>
                                @php
                                    $statusColor = match($product->design_status) {
                                        'Accepted' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                        'Rejected' => 'bg-rose-100 text-rose-700 border-rose-200',
                                        default => 'bg-sky-100 text-sky-700 border-sky-200',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $statusColor }}">
                                    {{ $product->design_status ?? 'Pending' }}
                                </span>
                            </dd>
                        </div>

                        <!-- Created By -->
                        <div>
                            <dt class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Created By</dt>
                            <dd class="text-sm text-gray-700 flex items-center gap-2 font-medium">
                                @php $creator = $product->creator_details; @endphp
                                <div class="w-6 h-6 rounded-full bg-magenta-100 flex items-center justify-center text-[10px] font-bold text-magenta-800">
                                    {{ substr($creator['name'] ?? 'U', 0, 1) }}
                                </div>
                                {{ $creator['code'] }} - {{ $creator['name'] }}
                                <span class="text-[10px] text-gray-400 font-bold ml-1 bg-gray-100 px-1.5 py-0.5 rounded">({{ $creator['type'] }})</span>
                                <span class="text-[10px] text-gray-400 font-bold ml-1">({{ $product->created_at->format('d M Y') }})</span>
                            </dd>
                        </div>

                        <!-- Accepted By -->
                        @if($product->design_status === 'Accepted' && $product->acceptor_details)
                        <div>
                            <dt class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Accepted By</dt>
                            <dd class="text-sm text-gray-700 flex items-center gap-2 font-medium">
                                @php $acceptor = $product->acceptor_details; @endphp
                                <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center text-[10px] font-bold text-emerald-800">
                                    {{ substr($acceptor['name'] ?? 'A', 0, 1) }}
                                </div>
                                {{ $acceptor['code'] }} - {{ $acceptor['name'] }}
                                <span class="text-[10px] text-gray-400 font-bold ml-1 bg-gray-100 px-1.5 py-0.5 rounded">({{ $acceptor['type'] }})</span>
                            </dd>
                        </div>
                        @endif

                        <!-- Access Control Section -->
                        <div class="col-span-1 md:col-span-2 border-t border-gray-50 pt-6 mt-2">
                             <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Access Control</h4>
                             
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Image Lock -->
                                <div class="p-4 rounded-xl {{ $product->is_locked ? 'bg-rose-50 border border-rose-100' : 'bg-emerald-50 border border-emerald-100' }}">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 rounded-lg {{ $product->is_locked ? 'bg-rose-200 text-rose-700' : 'bg-emerald-200 text-emerald-700' }}">
                                            <i class="bi {{ $product->is_locked ? 'bi-lock-fill' : 'bi-unlock-fill' }} text-xl"></i>
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold block uppercase tracking-tight {{ $product->is_locked ? 'text-rose-700' : 'text-emerald-700' }}">General Lock</span>
                                            <span class="text-sm font-bold text-gray-800">{{ $product->is_locked ? 'Images are restricted' : 'Images are public' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Temporary Access -->
                                <div class="p-4 rounded-xl {{ $product->design_view_unlocked_until && now()->isBefore($product->design_view_unlocked_until) ? 'bg-indigo-50 border border-indigo-100' : 'bg-gray-50 border border-gray-100' }}">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 rounded-lg {{ $product->design_view_unlocked_until && now()->isBefore($product->design_view_unlocked_until) ? 'bg-indigo-200 text-indigo-700' : 'bg-gray-200 text-gray-500' }}">
                                            <i class="bi bi-clock-history text-xl"></i>
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold block uppercase tracking-tight {{ $product->design_view_unlocked_until && now()->isBefore($product->design_view_unlocked_until) ? 'text-indigo-700' : 'text-gray-500' }}">Global Temp access</span>
                                            @if($product->design_view_unlocked_until && now()->isBefore($product->design_view_unlocked_until))
                                                <span class="text-sm font-bold text-indigo-800">Until {{ \Carbon\Carbon::parse($product->design_view_unlocked_until)->format('d M, h:i A') }}</span>
                                            @else
                                                <span class="text-sm font-bold text-gray-600">No active window</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                             </div>
                        </div>

                        <!-- Technical Specs -->
                        <div class="col-span-1 md:col-span-2 border-t border-gray-50 pt-6 mt-2">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Technical Specifications</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="bg-gray-50/50 p-3 rounded-xl border border-gray-100">
                                    <span class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Weight Range</span>
                                    <span class="block text-sm font-mono font-bold text-gray-800">{{ $product->weight_from }} - {{ $product->weight_to }} g</span>
                                </div>
                                <div class="bg-gray-50/50 p-3 rounded-xl border border-gray-100">
                                    <span class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Size / Length</span>
                                    <span class="block text-sm font-mono font-bold text-gray-800">{{ $product->size ?? '-' }} / {{ $product->length ?? '-' }}</span>
                                </div>
                                <div class="bg-gray-50/50 p-3 rounded-xl border border-gray-100">
                                    <span class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Purity / HUID</span>
                                    <span class="block text-sm font-mono font-bold text-gray-800">{{ $product->rodium ?? '-' }} / {{ $product->hallmark ?? '-' }}</span>
                                </div>
                                <div class="bg-gray-50/50 p-3 rounded-xl border border-gray-100">
                                    <span class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Hook / Enamel</span>
                                    <span class="block text-sm font-mono font-bold text-gray-800">{{ $product->hook ?? '-' }} / {{ $product->enamel ?? '-' }}</span>
                                </div>
                                <div class="bg-gray-50/50 p-3 rounded-xl border border-gray-100">
                                    <span class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Stone</span>
                                    <span class="block text-sm font-mono font-bold text-gray-800">{{ $product->stone ?? '-' }}</span>
                                </div>
                                <div class="bg-gray-50/50 p-3 rounded-xl border border-gray-100">
                                    <span class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Type</span>
                                    <span class="block text-sm font-mono font-bold text-gray-800">{{ $product->type }}</span>
                                </div>
                                <div class="bg-gray-50/50 p-3 rounded-xl border border-gray-100">
                                    <span class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Open/Close</span>
                                    <span class="block text-sm font-mono font-bold text-gray-800">{{ $product->open_close ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals (Reused from Index) -->
    @include('admin.design.partials.modals', ['product' => $product])
    
</div>
@endsection

@section('scripts')
<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        if(modal) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if(modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    function showAcceptModal(productId, productCode) {
        const form = document.getElementById('acceptDesignForm');
        const urlTemplate = "{{ route('admin.design.accept', '000', false) }}";
        form.action = urlTemplate.replace('000', productId);
        const codeSpan = document.getElementById('acceptProductCode');
        if(codeSpan) codeSpan.textContent = productCode;
        const input = document.getElementById('designCodeInput');
        if(input) input.value = '';
        openModal('acceptDesignModal');
    }

    function showUnlockModalSingle(id, code, imageUrl) {
        // Reset and show modal
        const previewContainer = document.getElementById('previewImagesContainer');
        const previewSection = document.getElementById('selectedDesignsPreview');
        
        previewContainer.innerHTML = '';
        if (imageUrl) {
            previewSection.style.display = 'block';
            const thumb = document.createElement('div');
            thumb.className = "w-10 h-10 border-2 border-white rounded-lg shadow-sm overflow-hidden";
            thumb.innerHTML = `<img src="${imageUrl}" class="w-full h-full object-cover" title="${code}">`;
            previewContainer.appendChild(thumb);
        } else {
            previewSection.style.display = 'none';
        }

        // Set current ID for confirmUnlock
        window.singleUnlockId = id;
        
        updateDurationOptions('minutes');
        loadAvailableUsers();
        openModal('unlockModal');
    }

    const durationOptions = {
        minutes: [{v: 1, l: '1 Minute'}, {v: 5, l: '5 Minutes'}, {v: 15, l: '15 Minutes'}, {v: 30, l: '30 Minutes'}, {v: 45, l: '45 Minutes'}],
        hours: [{v: 1, l: '1 Hour'}, {v: 2, l: '2 Hours'}, {v: 4, l: '4 Hours'}, {v: 8, l: '8 Hours'}, {v: 24, l: '1 Day'}, {v: 168, l: '1 Week'}],
        months: [{v: 1, l: '1 Month'}, {v: 3, l: '3 Months'}, {v: 6, l: '6 Months'}],
        years: [{v: 1, l: '1 Year'}],
        permanent: [{v: -1, l: 'Permanent'}]
    };

    function updateDurationOptions(unit) {
        const select = document.getElementById('unlockDuration');
        const wrapper = document.getElementById('durationAmountWrapper');
        if(!select || !wrapper) return;

        select.innerHTML = '';
        if (unit === 'permanent') {
            wrapper.style.display = 'none';
            select.innerHTML = '<option value="-1" selected>Permanent</option>';
        } else {
            wrapper.style.display = 'block';
            durationOptions[unit].forEach(opt => {
                const o = document.createElement('option');
                o.value = opt.v;
                o.textContent = opt.l;
                select.appendChild(o);
            });
        }
    }

    document.querySelectorAll('input[name="durationUnit"]').forEach(radio => {
        radio.addEventListener('change', (e) => updateDurationOptions(e.target.value));
    });

    document.querySelectorAll('input[name="userScope"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            const container = document.getElementById('userSelectionContainer');
            if(container) container.style.display = e.target.value === 'specific' ? 'block' : 'none';
        });
    });

    async function loadAvailableUsers() {
        const select = document.getElementById('selectedUsers');
        if(!select) return;

        select.innerHTML = '<option disabled class="p-4 text-center">Loading users...</option>';
        try {
            const response = await fetch("{{ route('admin.design.get-available-users') }}");
            const users = await response.json();
            select.innerHTML = '';
            
            const groupMap = {
                buyers: { label: 'Buyers', prefix: 'buyer', id: 'bp_code', name: (u) => u.business_name || u.name },
                key_users: { label: 'Key Users', prefix: 'key_user', id: 'user_code', name: (u) => u.full_name },
                users: { label: 'Users', prefix: 'user', id: 'user_code', name: (u) => u.full_name },
                craftsmen: { label: 'Craftsmen', prefix: 'craftsman', id: 'craftman_code', name: (u) => u.full_name || u.name }
            };

            Object.entries(groupMap).forEach(([key, cfg]) => {
                if (users[key]?.length) {
                    const group = document.createElement('optgroup');
                    group.label = cfg.label;
                    group.className = "font-extrabold text-gray-900 border-t border-gray-100 mt-2";
                    users[key].forEach(u => {
                        const opt = document.createElement('option');
                        opt.value = `${cfg.prefix}:${u[cfg.id]}`;
                        opt.textContent = `${u[cfg.id]} - ${cfg.name(u)}`;
                        opt.className = "p-2 font-medium text-gray-600 hover:bg-indigo-50";
                        group.appendChild(opt);
                    });
                    select.appendChild(group);
                }
            });
        } catch (error) {
            select.innerHTML = '<option disabled class="text-rose-500">Error loading users</option>';
        }
    }

    async function confirmUnlock() {
        const btn = document.getElementById('confirmUnlockBtn');
        const ids = window.singleUnlockId ? [window.singleUnlockId] : [];
        
        let duration = parseInt(document.getElementById('unlockDuration').value);
        const durationUnitCheck = document.querySelector('input[name="durationUnit"]:checked');
        const durationUnit = durationUnitCheck ? durationUnitCheck.value : 'minutes';
        
        const userScopeCheck = document.querySelector('input[name="userScope"]:checked');
        const userScope = userScopeCheck ? userScopeCheck.value : 'all';
        
        const selectedUsersSelect = document.getElementById('selectedUsers');
        const selectedUsers = userScope === 'specific' ? Array.from(selectedUsersSelect.selectedOptions).map(o => o.value) : null;

        if (userScope === 'specific' && (!selectedUsers || !selectedUsers.length)) {
            alert('Please select at least one user for private access.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="inline-block animate-spin mr-2">⟳</span>Processing...';

        try {
            const resp = await fetch("{{ route('admin.design.unlock-designs') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ selected_designs: ids, duration, duration_unit: durationUnit, user_scope: userScope, selected_users: selectedUsers })
            });
            const result = await resp.json();
            if (result.success) { alert(result.message); location.reload(); }
            else { alert('Failed: ' + result.message); }
        } catch (error) {
            console.error(error); alert('A server error occurred.');
        } finally {
            btn.disabled = false; btn.innerHTML = 'Confirm Permissions';
        }
    }

    // Modal search logic
    const searchInput = document.getElementById('userSearchInput');
    if(searchInput) {
        searchInput.addEventListener('input', function(e) {
            const filter = e.target.value.toLowerCase();
            const select = document.getElementById('selectedUsers');
            const options = select.getElementsByTagName('option');
            const groups = select.getElementsByTagName('optgroup');
            
            Array.from(options).forEach(opt => opt.style.display = opt.textContent.toLowerCase().includes(filter) ? '' : 'none');
            Array.from(groups).forEach(grp => {
                const hasVisible = Array.from(grp.children).some(opt => opt.style.display !== 'none');
                grp.style.display = hasVisible ? '' : 'none';
            });
        });
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    .select-multiple-custom option:checked { background-color: #fce7f3 !important; color: #9d174d !important; }
    .animate-fadeIn { animation: fadeIn 0.3s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
