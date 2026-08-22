@extends('buyer.layouts.app')

@section('title', 'Global Search | Buyer')

@section('content')
<div class="max-w-5xl mx-auto py-8">
    <div class="mb-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Global Search</h2>
        <p class="text-gray-600 dark:text-gray-400">Search across all modules: Work Orders, Products, Catalogues, Designs, and more.</p>
    </div>

    <!-- Search Form -->
    <div class="mb-10">
        <form action="{{ route('buyer.global-search') }}" method="GET" class="relative" id="searchForm" onsubmit="return false;" enctype="multipart/form-data">
            <div class="relative flex items-center w-full h-16 rounded-2xl bg-white dark:bg-slate-900 shadow-xl overflow-hidden border border-gray-200 dark:border-slate-700 focus-within:ring-2 focus-within:ring-purple-600 transition-all">
                <div class="grid place-items-center h-full w-16 text-gray-400" id="searchIconContainer">
                    <i class="bi bi-search text-xl"></i>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    id="searchInput"
                    value="{{ request('search') }}" 
                    class="peer h-full w-full outline-none text-lg text-gray-700 dark:text-gray-200 pr-6 bg-transparent placeholder-gray-400" 
                    placeholder="Type 'WA', order number, name, or anything..." 
                    autocomplete="off"
                    autofocus 
                />

                <!-- Image Upload Trigger -->
                <label for="imageUploadInput" class="h-full grid place-items-center w-14 text-gray-400 hover:text-purple-600 transition-colors cursor-pointer" title="Search by image">
                    <i class="bi bi-camera text-xl"></i>
                </label>
                <input type="file" id="imageUploadInput" class="hidden" accept="image/*" name="image_search" />

                <!-- Clear Button -->
                <a href="{{ route('buyer.global-search') }}" id="clearBtn" class="h-full grid place-items-center w-14 text-gray-400 hover:text-purple-600 transition-colors mr-2 {{ request('search') ? '' : 'hidden' }}">
                    <i class="bi bi-x-circle text-lg"></i>
                </a>
            </div>

            <!-- Image preview indicator -->
            <div id="imagePreviewContainer" class="hidden mt-4 flex items-center justify-between bg-white dark:bg-slate-800 p-3 px-4 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm max-w-md mx-auto animate-fade-in-up">
                <div class="flex items-center gap-3">
                    <img id="imagePreview" src="" alt="Search Preview" class="w-10 h-10 object-cover rounded-lg border border-gray-200 dark:border-slate-600" />
                    <div>
                        <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">Image Search Active</p>
                        <p class="text-xs text-purple-600">Visual similarity matching</p>
                    </div>
                </div>
                <button type="button" id="removeImageBtn" class="text-xs text-red-500 hover:text-red-700 font-medium px-2.5 py-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    Remove
                </button>
            </div>
        </form>
    </div>

    <!-- Results Section -->
    <div id="resultsContainer">
        @if(request()->has('search') && !empty($query))
            @if(empty($results))
                <div class="text-center py-16 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800 animate-fade-in-up">
                    <div class="text-gray-300 dark:text-slate-700 mb-4">
                        <i class="bi bi-search" style="font-size: 3.75rem;"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">No results found</h3>
                    <p class="text-gray-500 dark:text-gray-400">We couldn't find anything matching "<span class="font-medium text-purple-600">{{ $query }}</span>".</p>
                </div>
            @else
                <div class="mb-4 flex items-center justify-between animate-fade-in-up">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                        Search Results for "<span class="text-purple-600">{{ $query }}</span>"
                    </h3>
                    <span class="text-sm text-gray-500 bg-gray-200 dark:bg-slate-800 px-3 py-1 rounded-full">
                        Found in {{ count($results) }} categories
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($results as $category => $data)
                        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-md border border-gray-100 dark:border-slate-800 overflow-hidden animate-fade-in-up">
                            <div class="bg-gray-50 dark:bg-slate-800/50 px-5 py-4 border-b border-gray-100 dark:border-slate-700 flex justify-between items-center">
                                <h4 class="font-bold text-gray-800 dark:text-gray-100">{{ $category }}</h4>
                                <span class="bg-purple-600 text-white text-xs font-bold px-2 py-1 rounded-full">{{ $data['count'] }}</span>
                            </div>
                            <ul class="divide-y divide-gray-50 dark:divide-slate-800/50 max-h-72 overflow-y-auto custom-scrollbar">
                                @foreach($data['items'] as $item)
                                    <li>
                                        <button type="button" 
                                            class="search-result-btn w-full text-left flex items-start justify-between p-4 hover:bg-purple-600/5 dark:hover:bg-slate-800 transition-colors group"
                                            data-url="{{ $item['url'] }}"
                                            data-display="{{ $item['display'] }}"
                                            data-details="{{ $item['details'] ?? 'No additional details available.' }}"
                                            data-image="{{ $item['image'] ?? '' }}">
                                            <div class="flex items-center gap-3.5 min-w-0">
                                                @if(!empty($item['image']))
                                                    <img src="{{ $item['image'] }}" alt="Preview" class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-slate-700 shrink-0" />
                                                @else
                                                    <div class="w-12 h-12 rounded-xl bg-purple-600/10 flex items-center justify-center text-purple-600 shrink-0">
                                                        <i class="bi bi-link-45deg text-lg"></i>
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <span class="text-gray-800 dark:text-gray-200 font-semibold group-hover:text-purple-600 transition-colors block truncate">{{ $item['display'] }}</span>
                                                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate mt-0.5">{{ $item['details'] ?? 'No additional details available.' }}</p>
                                                </div>
                                            </div>
                                            <i class="bi bi-chevron-right text-gray-300 group-hover:text-purple-600 transition-colors text-sm mt-3 ml-2 shrink-0"></i>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    <!-- Details Preview Modal -->
    <div id="detailsModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-gray-900/50 backdrop-blur-sm opacity-0 transition-opacity duration-300">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden transform scale-95 transition-transform duration-300" id="detailsModalContent">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 id="modalTitle" class="text-xl font-bold text-gray-900 dark:text-white pr-8"></h3>
                    <button type="button" onclick="closeDetailsModal()" class="absolute top-6 right-6 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>
                </div>
                
                <div id="modalImageContainer" class="mb-4 hidden">
                    <img id="modalImage" src="" alt="Preview" class="w-full h-48 object-cover rounded-xl border border-gray-100 dark:border-slate-700" />
                </div>
                
                <div class="mb-6">
                    <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wider">Preview Details</h4>
                    <p id="modalDetails" class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed"></p>
                </div>
                
                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-slate-700">
                    <button type="button" onclick="closeDetailsModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <a id="modalLink" href="#" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-xl transition-colors shadow-md flex items-center">
                        View Full Page <i class="bi bi-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: rgba(156, 163, 175, 0.3);
        border-radius: 20px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: rgba(71, 85, 105, 0.5);
    }
    .custom-scrollbar:hover::-webkit-scrollbar-thumb {
        background-color: rgba(156, 163, 175, 0.6);
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.4s ease-out forwards;
    }
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const resultsContainer = document.getElementById('resultsContainer');
        const clearBtn = document.getElementById('clearBtn');
        const searchIconContainer = document.getElementById('searchIconContainer');
        const imageUploadInput = document.getElementById('imageUploadInput');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        const imagePreview = document.getElementById('imagePreview');
        const removeImageBtn = document.getElementById('removeImageBtn');
        
        let debounceTimer;

        function renderResults(query, results) {
            if (!query) {
                resultsContainer.innerHTML = '';
                return;
            }

            const categories = Object.keys(results);
            
            if (categories.length === 0) {
                resultsContainer.innerHTML = `
                    <div class="text-center py-16 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800 animate-fade-in-up">
                        <div class="text-gray-300 dark:text-slate-700 mb-4">
                            <i class="bi bi-search" style="font-size: 3.75rem;"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">No results found</h3>
                        <p class="text-gray-500 dark:text-gray-400">We couldn't find anything matching "<span class="font-medium text-purple-600">${query}</span>".</p>
                    </div>
                `;
                return;
            }

            let html = `
                <div class="mb-4 flex items-center justify-between animate-fade-in-up">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                        Search Results for "<span class="text-purple-600">${query}</span>"
                    </h3>
                    <span class="text-sm text-gray-500 bg-gray-200 dark:bg-slate-800 px-3 py-1 rounded-full">
                        Found in ${categories.length} categories
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            `;

            categories.forEach((category, index) => {
                const data = results[category];
                const animationDelay = index * 0.1;
                html += `
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-md border border-gray-100 dark:border-slate-800 overflow-hidden animate-fade-in-up" style="animation-delay: ${animationDelay}s; opacity: 0; animation-fill-mode: forwards;">
                        <div class="bg-gray-50 dark:bg-slate-800/50 px-5 py-4 border-b border-gray-100 dark:border-slate-700 flex justify-between items-center">
                            <h4 class="font-bold text-gray-800 dark:text-gray-100">${category}</h4>
                            <span class="bg-purple-600 text-white text-xs font-bold px-2 py-1 rounded-full">${data.count}</span>
                        </div>
                        <ul class="divide-y divide-gray-50 dark:divide-slate-800/50 max-h-72 overflow-y-auto custom-scrollbar">
                `;

                data.items.forEach(item => {
                    const displaySafe = (item.display || '').replace(/"/g, '&quot;');
                    const detailsSafe = (item.details || 'No additional details available.').replace(/"/g, '&quot;');
                    const imgHtml = item.image 
                        ? `<img src="${item.image}" alt="Preview" class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-slate-700 shrink-0" />`
                        : `<div class="w-12 h-12 rounded-xl bg-purple-600/10 flex items-center justify-center text-purple-600 shrink-0"><i class="bi bi-link-45deg text-lg"></i></div>`;

                    html += `
                        <li>
                            <button type="button" class="search-result-btn w-full text-left flex items-start justify-between p-4 hover:bg-purple-600/5 dark:hover:bg-slate-800 transition-colors group"
                                data-url="${item.url}"
                                data-display="${displaySafe}"
                                data-details="${detailsSafe}"
                                data-image="${item.image || ''}">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    ${imgHtml}
                                    <div class="min-w-0">
                                        <span class="text-gray-800 dark:text-gray-200 font-semibold group-hover:text-purple-600 transition-colors block truncate">${item.display}</span>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 truncate mt-0.5">${item.details || 'No additional details available.'}</p>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-right text-gray-300 group-hover:text-purple-600 transition-colors text-sm mt-3 ml-2 shrink-0"></i>
                            </button>
                        </li>
                    `;
                });

                html += `
                        </ul>
                    </div>
                `;
            });

            html += `</div>`;
            resultsContainer.innerHTML = html;
        }

        const performSearch = () => {
            const query = searchInput.value.trim();
            const hasImage = imageUploadInput.files.length > 0;

            if (query.length > 0 || hasImage) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
                resultsContainer.innerHTML = '';
                return;
            }

            searchIconContainer.innerHTML = `<div class="spinner-border text-purple-600 w-5 h-5" role="status"></div>`;

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const formData = new FormData();
                let url = `{{ route('buyer.global-search') }}`;
                let options = {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                };

                if (hasImage) {
                    formData.append('image_search', imageUploadInput.files[0]);
                    options.method = 'POST';
                    options.body = formData;
                    options.headers['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
                } else {
                    url += `?search=${encodeURIComponent(query)}`;
                    options.method = 'GET';
                }

                fetch(url, options)
                .then(response => response.json())
                .then(data => {
                    renderResults(data.query, data.results);
                })
                .catch(error => console.error('Search error:', error))
                .finally(() => {
                    searchIconContainer.innerHTML = `<i class="bi bi-search text-xl"></i>`;
                });
            }, 300);
        };

        searchInput.addEventListener('input', performSearch);

        imageUploadInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreviewContainer.classList.remove('hidden');
                    imagePreviewContainer.classList.add('flex');
                    searchInput.value = '';
                    performSearch();
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        removeImageBtn.addEventListener('click', function() {
            imageUploadInput.value = '';
            imagePreviewContainer.classList.add('hidden');
            imagePreviewContainer.classList.remove('flex');
            imagePreview.src = '';
            performSearch();
        });

        // Details Modal Opens
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.search-result-btn');
            if (!btn) return;
            
            e.preventDefault();
            
            const url = btn.dataset.url;
            const title = btn.dataset.display;
            const details = btn.dataset.details;
            const image = btn.dataset.image;
            
            const modal = document.getElementById('detailsModal');
            const modalContent = document.getElementById('detailsModalContent');
            
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalDetails').textContent = details;
            document.getElementById('modalLink').href = url;
            
            const imageContainer = document.getElementById('modalImageContainer');
            const modalImage = document.getElementById('modalImage');
            
            if (image && image !== 'null' && image !== '') {
                modalImage.src = image;
                imageContainer.classList.remove('hidden');
            } else {
                imageContainer.classList.add('hidden');
            }
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('scale-95');
            }, 10);
        });

        document.getElementById('detailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                window.closeDetailsModal();
            }
        });
        
        window.closeDetailsModal = function() {
            const modal = document.getElementById('detailsModal');
            const modalContent = document.getElementById('detailsModalContent');
            
            modal.classList.add('opacity-0');
            modalContent.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        };
    });
</script>
@endsection