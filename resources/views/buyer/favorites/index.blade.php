@extends('buyer.layouts.app')

@section('title', 'My Favorites')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">My Favorites</h1>
            <p class="text-sm text-slate-500">Your curated collection of favorite designs (All unlocked)</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('buyer.design.index') }}"
                class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 border border-slate-200 rounded-xl text-sm font-bold hover:bg-slate-200 transition-all">
                <i class="bi bi-arrow-left mr-2"></i> Back to Catalogue
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
            <h5 class="text-sm font-bold text-slate-700 uppercase tracking-widest">Favorite Designs</h5>
            <span class="text-xs font-bold text-slate-500 bg-slate-200/70 px-3 py-1 rounded-full">
                {{ $favorites->count() }} Saved
            </span>
        </div>

        <div class="p-6 bg-white">
            @if($favorites->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($favorites as $favorite)
                @php $design = $favorite->product; @endphp
                <div class="group flex flex-col bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:border-pink-200 transition-all duration-300 overflow-hidden relative">
                    <div class="relative aspect-[4/3] bg-white p-4 overflow-hidden">
                        @php
                        $imagesCount = $design->images ? $design->images->count() : 0;
                        $firstImage = $imagesCount > 0 ? $design->images->first()->path : null;
                        if (!$firstImage && $design->product_image) {
                            $imgs = explode(',', $design->product_image);
                            $firstImage = trim($imgs[0]);
                        }

                        $imgSrc = null;
                        if ($firstImage) {
                            if (str_starts_with($firstImage, 'http')) { $imgSrc = $firstImage; }
                            elseif (str_starts_with($firstImage, 'products/')) { $imgSrc = asset('storage/' . $firstImage); }
                            elseif (str_starts_with($firstImage, 'images/') || str_starts_with($firstImage, 'storage/')) { $imgSrc = asset($firstImage); }
                            else { $imgSrc = asset('storage/products/' . $firstImage); }
                        }
                        @endphp

                        @if($imgSrc)
                        {{-- Unlocked: No blur, no lock logo --}}
                        <img src="{{ $imgSrc }}"
                            class="w-full h-full object-contain transition-all duration-500 group-hover:scale-110"
                            alt="{{ $design->product_name }}">
                        @else
                        <div class="w-full h-full bg-slate-50 rounded-lg flex items-center justify-center text-slate-300">
                            <i class="bi bi-image text-4xl"></i>
                        </div>
                        @endif

                        <form action="{{ route('buyer.favorites.destroy', $favorite->id) }}" method="POST" class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 bg-red-500 text-white rounded-full shadow-lg hover:bg-red-600 transition-colors" title="Remove from Favorites">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </form>
                    </div>

                    <div class="p-4 flex flex-col flex-1 border-t border-slate-50">
                        <div class="flex flex-col items-center justify-center mb-3 gap-1.5">
                            <h6 class="font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-4 py-1 rounded-lg shadow-sm text-center truncate max-w-[90%]"
                                title="{{ $design->design_code }}">
                                {{ $design->design_code }}
                            </h6>

                            <div class="flex items-center gap-1.5 max-w-[95%]">
                                
                                <!-- <button type="button" 
                                    onclick="editFavoriteName({{ $favorite->id }}, '{{ addslashes($favorite->design_name ?? '') }}', '{{ addslashes($design->design_code) }}')" 
                                    class="text-slate-400 hover:text-slate-700 text-xs p-1" 
                                    title="Edit Custom Design Name">
                                    <i class="bi bi-pencil-square"></i>
                                </button> -->
                            </div>
                        </div>

                        <div class="flex justify-between items-center text-xs text-slate-500 mb-4">
                            <span class="text-[11px] font-mono font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded"> {{ $design->category->name ?? 'N/A' }}</span>
                            <span class="font-bold text-slate-700">{{ $design->weight_from }}-{{ $design->weight_to }} gm</span>
                            
                        </div>

                        <span id="fav-text-{{ $favorite->id }}" class="text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-0.5 rounded-md truncate" title="{{ $favorite->design_name ?: 'No custom name' }}">
                                    {{ $favorite->design_name ?: 'Add Custom Name' }}
                                </span>
                        <div class="mt-auto pt-4 border-t border-slate-50 flex gap-2">
                            {{-- Always unlocked in Favorites view --}}
                            <a href="{{ route('buyer.design.show', $design->id) }}"
                                class="flex-1 inline-flex items-center justify-center py-2.5 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition-colors">
                                <i class="bi bi-eye mr-1.5"></i> Details
                            </a>
                            
                            <form action="{{ route('buyer.favorites.destroy', $favorite->id) }}" method="POST" class="flex-none">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2.5 bg-red-50 text-red-600 border border-red-100 rounded-xl hover:bg-red-100 transition-colors" title="Remove from favorites">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-200">
                    <i class="bi bi-heart text-4xl"></i>
                </div>
                <h5 class="text-slate-500 font-medium">You haven't added any designs to your favorites yet.</h5>
                <a href="{{ route('buyer.design.index') }}" class="mt-4 inline-flex items-center px-6 py-2.5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition-all">
                    Browse Catalogue
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Edit Favorite Name Modal -->
<div id="editFavModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-base">Edit Custom Design Name</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 text-lg">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="editFavForm" onsubmit="saveEditedName(event)">
            <div class="p-5 space-y-4">
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Design Code</span>
                    <p id="editDesignCode" class="text-sm font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 rounded-lg px-3 py-1.5 inline-block"></p>
                </div>
                <div>
                    <label for="edit_design_name_input" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Custom Design Name
                    </label>
                    <input type="text" id="edit_design_name_input" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-pink-500 focus:bg-white outline-none transition-all" placeholder="Enter design name...">
                </div>
            </div>
            <div class="p-5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-200/70 rounded-xl transition-all">Cancel</button>
                <button type="submit" id="saveEditBtn" class="px-5 py-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-all shadow-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
let editTargetFavId = null;

function editFavoriteName(favId, currentName, designCode) {
    editTargetFavId = favId;
    document.getElementById('editDesignCode').textContent = designCode;
    document.getElementById('edit_design_name_input').value = currentName || '';

    const modal = document.getElementById('editFavModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        document.getElementById('edit_design_name_input').focus();
    }, 50);
}

function closeEditModal() {
    const modal = document.getElementById('editFavModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    editTargetFavId = null;
}

function saveEditedName(event) {
    event.preventDefault();
    if (!editTargetFavId) return;

    const newName = document.getElementById('edit_design_name_input').value.trim();
    const saveBtn = document.getElementById('saveEditBtn');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    fetch(`{{ url('buyer/favorites') }}/${editTargetFavId}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        },
        body: JSON.stringify({
            design_name: newName
        })
    })
    .then(response => response.json())
    .then(data => {
        saveBtn.disabled = false;
        closeEditModal();

        if (data.success) {
            const badge = document.getElementById(`fav-text-${editTargetFavId}`);
            if (badge) {
                badge.textContent = newName || 'Add Custom Name';
            }
        }
        alert(data.message || 'Updated successfully!');
    })
    .catch(error => {
        saveBtn.disabled = false;
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>
@endsection