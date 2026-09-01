@extends('super-admin.layouts.app')

@section('title', 'Global Search | Super Admin')

@section('content')
<div class="tw-max-w-6xl tw-mx-auto tw-py-10 tw-px-4 sm:tw-px-6 lg:tw-px-8">
    <div class="tw-mb-12 tw-text-center">
        <h2 class="tw-text-4xl tw-font-extrabold tw-text-transparent tw-bg-clip-text tw-bg-gradient-to-r tw-from-gray-900 tw-to-gray-600 dark:tw-from-white dark:tw-to-gray-400 tw-mb-4 tw-tracking-tight">Search</h2>
        <p class="tw-text-lg tw-text-gray-500 dark:tw-text-gray-400 tw-max-w-2xl tw-mx-auto">Seamlessly search across all modules: Work Orders, Products, Craftsmen, Buyers, and more.</p>
    </div>

    <!-- Search Form -->
    <div class="tw-mb-12 tw-max-w-4xl tw-mx-auto">
        <form action="{{ route('super-admin.global-search') }}" method="GET" class="tw-relative tw-group" id="searchForm" onsubmit="return false;" enctype="multipart/form-data">
            <div class="tw-relative tw-flex tw-items-center tw-w-full tw-h-16 tw-rounded-full tw-bg-white dark:tw-bg-slate-800 tw-shadow-[0_8px_30px_rgb(0,0,0,0.06)] dark:tw-shadow-[0_8px_30px_rgb(0,0,0,0.2)] tw-overflow-hidden tw-border tw-border-gray-100 dark:tw-border-slate-700 focus-within:tw-ring-4 focus-within:tw-ring-maroon/20 focus-within:tw-border-maroon/50 tw-transition-all tw-duration-300">
                <div class="tw-grid tw-place-items-center tw-h-full tw-w-16 tw-text-gray-400 group-focus-within:tw-text-maroon tw-transition-colors" id="searchIconContainer">
                    <i class="bi bi-search tw-text-xl"></i>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    id="searchInput"
                    value="{{ request('search') }}" 
                    class="tw-peer tw-h-full tw-w-full tw-outline-none tw-text-lg tw-text-gray-700 dark:tw-text-gray-200 tw-pr-6 tw-bg-transparent placeholder:tw-text-gray-400 tw-font-medium" 
                    placeholder="Type 'WA', order number, name, or anything..." 
                    autocomplete="off"
                    autofocus 
                />
                
                <!-- Image upload trigger -->
                <label for="imageUploadInput" class="tw-h-full tw-grid tw-place-items-center tw-w-16 tw-text-gray-400 hover:tw-text-maroon dark:hover:tw-text-maroon tw-transition-colors tw-cursor-pointer group-focus-within:tw-text-gray-500" title="Search by image">
                    <i class="bi bi-image tw-text-xl"></i>
                </label>
                <input type="file" id="imageUploadInput" class="tw-hidden" accept="image/*" name="image_search" />

                <a href="{{ route('super-admin.global-search') }}" id="clearBtn" class="tw-h-full tw-grid tw-place-items-center tw-w-16 tw-text-gray-400 hover:tw-text-maroon tw-transition-colors tw-mr-2 {{ request('search') ? '' : 'tw-hidden' }}">
                    <i class="bi bi-x-circle-fill tw-text-lg"></i>
                </a>
            </div>

            <!-- Image preview container -->
            <div id="imagePreviewContainer" class="tw-hidden tw-mt-6 tw-mx-auto tw-flex-col sm:tw-flex-row tw-items-center tw-gap-4 tw-bg-white dark:tw-bg-slate-800 tw-p-4 tw-rounded-2xl tw-border tw-border-gray-100 dark:tw-border-slate-700 tw-shadow-md tw-w-max tw-animate-fade-in-up">
                <div class="tw-relative">
                    <img id="imagePreview" src="" alt="Search Image" class="tw-h-20 tw-w-20 tw-object-cover tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-100 dark:tw-border-slate-600" />
                    <div class="tw-absolute -tw-top-2 -tw-right-2 tw-bg-maroon tw-text-white tw-rounded-full tw-w-6 tw-h-6 tw-flex tw-items-center tw-justify-center tw-shadow-md">
                        <i class="bi bi-image tw-text-xs"></i>
                    </div>
                </div>
                <div class="tw-text-center sm:tw-text-left">
                    <p class="tw-text-sm tw-font-semibold tw-text-gray-800 dark:tw-text-gray-200 tw-mb-1">Searching visually similar images...</p>
                    <button type="button" id="removeImageBtn" class="tw-text-xs tw-font-medium tw-text-red-500 hover:tw-text-red-700 dark:tw-text-red-400 dark:hover:tw-text-red-300 tw-transition-colors tw-px-3 tw-py-1 tw-bg-red-50 dark:tw-bg-red-900/20 tw-rounded-full hover:tw-bg-red-100 dark:hover:tw-bg-red-900/40">Remove image</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Section -->
    <div id="resultsContainer">
        @if(request()->has('search') && !empty($query))
            @if(empty($results))
                <div class="tw-text-center tw-py-20 tw-bg-white dark:tw-bg-slate-800 tw-rounded-3xl tw-shadow-sm tw-border tw-border-gray-100 dark:tw-border-slate-700 tw-max-w-4xl tw-mx-auto">
                    <div class="tw-text-gray-200 dark:tw-text-slate-700 tw-mb-6 tw-inline-block tw-p-6 tw-rounded-full tw-bg-gray-50 dark:tw-bg-slate-900">
                        <i class="bi bi-search tw-text-6xl"></i>
                    </div>
                    <h3 class="tw-text-2xl tw-font-bold tw-text-gray-800 dark:tw-text-gray-100 tw-mb-3">No results found</h3>
                    <p class="tw-text-gray-500 dark:tw-text-gray-400 tw-text-lg">We couldn't find anything matching "<span class="tw-font-semibold tw-text-maroon">{{ $query }}</span>".</p>
                </div>
            @else
                <div class="tw-mb-6 tw-flex tw-flex-col sm:tw-flex-row tw-items-center tw-justify-between tw-gap-4 tw-max-w-5xl tw-mx-auto">
                    <h3 class="tw-text-xl tw-font-bold tw-text-gray-800 dark:tw-text-gray-100">
                        Search Results for "<span class="tw-text-transparent tw-bg-clip-text tw-bg-gradient-to-r tw-from-maroon tw-to-red-500">{{ $query }}</span>"
                    </h3>
                    <span class="tw-text-sm tw-font-medium tw-text-maroon tw-bg-maroon/10 dark:tw-bg-maroon/20 tw-px-4 tw-py-1.5 tw-rounded-full tw-border tw-border-maroon/20 tw-shadow-sm">
                        Found in {{ count($results) }} categories
                    </span>
                </div>

                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-2 tw-gap-8 tw-max-w-5xl tw-mx-auto">
                    @foreach($results as $category => $data)
                        <div class="tw-bg-white dark:tw-bg-slate-800 tw-rounded-2xl tw-shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:tw-shadow-[0_8px_30px_rgb(0,0,0,0.08)] dark:hover:tw-shadow-[0_8px_30px_rgb(0,0,0,0.3)] tw-transition-all tw-duration-300 tw-border tw-border-gray-100 dark:tw-border-slate-700 tw-overflow-hidden tw-transform hover:-tw-translate-y-1">
                            <div class="tw-bg-gray-50/80 dark:tw-bg-slate-800/80 tw-px-6 tw-py-5 tw-border-b tw-border-gray-100 dark:tw-border-slate-700 tw-flex tw-justify-between tw-items-center tw-backdrop-blur-sm">
                                <h4 class="tw-text-lg tw-font-bold tw-text-gray-800 dark:tw-text-gray-100 tw-flex tw-items-center tw-gap-2">
                                    <div class="tw-w-2 tw-h-2 tw-rounded-full tw-bg-maroon"></div>
                                    {{ $category }}
                                </h4>
                                <span class="tw-bg-maroon tw-text-white tw-text-xs tw-font-bold tw-px-3 tw-py-1 tw-rounded-full tw-shadow-sm">{{ $data['count'] }}</span>
                            </div>
                            <ul class="tw-divide-y tw-divide-gray-50 dark:tw-divide-slate-700/50 tw-max-h-72 tw-overflow-y-auto custom-scrollbar">
                                @foreach($data['items'] as $item)
                                    @php
                                        $display = htmlspecialchars($item['display'] ?? '');
                                        if(!empty($query)) {
                                            $pattern = '/(' . preg_quote($query, '/') . ')/i';
                                            $replacement = '<span class="tw-bg-yellow-200 dark:tw-bg-yellow-900/50 tw-text-gray-900 dark:tw-text-yellow-100 tw-rounded tw-px-1">$1</span>';
                                            $displayHtml = preg_replace($pattern, $replacement, $display);
                                        } else {
                                            $displayHtml = $display;
                                        }
                                    @endphp
                                    <li>
                                        <button type="button" 
                                            class="search-result-btn tw-w-full tw-text-left tw-flex tw-items-center tw-justify-between tw-px-6 tw-py-4 hover:tw-bg-maroon/5 dark:hover:tw-bg-slate-700/50 tw-transition-all tw-duration-200 tw-group tw-border-l-2 tw-border-transparent hover:tw-border-maroon"
                                            data-url="{{ $item['url'] }}"
                                            data-display="{{ $item['display'] }}"
                                            data-details="{{ $item['details'] ?? 'No additional details available.' }}"
                                            data-image="{{ $item['image'] ?? '' }}">
                                            <div class="tw-flex tw-items-center tw-gap-4">
                                                <div class="tw-w-10 tw-h-10 tw-rounded-full tw-bg-gray-100 dark:tw-bg-slate-700 group-hover:tw-bg-maroon/10 tw-flex tw-items-center tw-justify-center tw-text-gray-500 group-hover:tw-text-maroon tw-transition-colors tw-shadow-sm">
                                                    <i class="bi bi-link-45deg tw-text-lg"></i>
                                                </div>
                                                <span class="tw-text-gray-700 dark:tw-text-gray-300 tw-font-semibold group-hover:tw-text-maroon tw-transition-colors">{!! $displayHtml !!}</span>
                                            </div>
                                            <div class="tw-flex tw-items-center">
                                                @if(!empty($item['image']))
                                                    <img src="{{ $item['image'] }}" alt="Preview" class="tw-w-12 tw-h-12 tw-object-cover tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-100 dark:tw-border-slate-600 tw-mr-4" />
                                                @endif
                                                <i class="bi bi-chevron-right tw-text-gray-300 group-hover:tw-text-maroon group-hover:tw-translate-x-1 tw-transition-all"></i>
                                            </div>
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
    <div id="detailsModal" class="tw-fixed tw-inset-0 tw-z-[100] tw-hidden tw-items-center tw-justify-center tw-bg-gray-900/50 tw-backdrop-blur-sm tw-opacity-0 tw-transition-opacity tw-duration-300">
        <div class="tw-bg-white dark:tw-bg-slate-800 tw-rounded-2xl tw-shadow-2xl tw-w-full tw-max-w-md tw-mx-4 tw-overflow-hidden tw-transform tw-scale-95 tw-transition-transform tw-duration-300" id="detailsModalContent">
            <div class="tw-p-6">
                <div class="tw-flex tw-justify-between tw-items-start tw-mb-4">
                    <h3 id="modalTitle" class="tw-text-xl tw-font-bold tw-text-gray-900 dark:tw-text-white tw-pr-8"></h3>
                    <button type="button" onclick="closeDetailsModal()" class="tw-absolute tw-top-6 tw-right-6 tw-text-gray-400 hover:tw-text-gray-600 dark:hover:tw-text-gray-300 tw-transition-colors">
                        <i class="bi bi-x-lg tw-text-xl"></i>
                    </button>
                </div>
                
                <div id="modalImageContainer" class="tw-mb-4 tw-hidden">
                    <img id="modalImage" src="" alt="Preview" class="tw-w-full tw-h-48 tw-object-cover tw-rounded-xl tw-border tw-border-gray-100 dark:tw-border-slate-700" />
                </div>
                
                <div class="tw-mb-6">
                    <h4 class="tw-text-xs tw-font-semibold tw-text-gray-500 dark:tw-text-gray-400 tw-mb-2 tw-uppercase tw-tracking-wider">Preview Details</h4>
                    <p id="modalDetails" class="tw-text-gray-700 dark:tw-text-gray-300 tw-text-sm tw-leading-relaxed"></p>
                </div>
                
                <div class="tw-flex tw-justify-end tw-gap-3 tw-mt-6 tw-pt-4 tw-border-t tw-border-gray-100 dark:tw-border-slate-700">
                    <button type="button" onclick="closeDetailsModal()" class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 dark:tw-text-gray-200 tw-bg-gray-100 dark:tw-bg-slate-700 hover:tw-bg-gray-200 dark:hover:tw-bg-slate-600 tw-rounded-xl tw-transition-colors">
                        Cancel
                    </button>
                    <a id="modalLink" href="#" class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-white tw-bg-maroon hover:tw-bg-maroon/90 tw-rounded-xl tw-transition-colors tw-shadow-md tw-flex tw-items-center">
                        View Full Page <i class="bi bi-arrow-right tw-ml-2"></i>
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
    .tw-dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: rgba(71, 85, 105, 0.5);
    }
    .custom-scrollbar:hover::-webkit-scrollbar-thumb {
        background-color: rgba(156, 163, 175, 0.6);
    }
    .tw-animate-fade-in-up {
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

        // Function to render results
        function renderResults(query, results) {
            if (!query) {
                resultsContainer.innerHTML = '';
                return;
            }

            const categories = Object.keys(results);
            
            if (categories.length === 0) {
                resultsContainer.innerHTML = `
                    <div class="tw-text-center tw-py-20 tw-bg-white dark:tw-bg-slate-800 tw-rounded-3xl tw-shadow-sm tw-border tw-border-gray-100 dark:tw-border-slate-700 tw-max-w-4xl tw-mx-auto tw-animate-fade-in-up">
                        <div class="tw-text-gray-200 dark:tw-text-slate-700 tw-mb-6 tw-inline-block tw-p-6 tw-rounded-full tw-bg-gray-50 dark:tw-bg-slate-900">
                            <i class="bi bi-search tw-text-6xl"></i>
                        </div>
                        <h3 class="tw-text-2xl tw-font-bold tw-text-gray-800 dark:tw-text-gray-100 tw-mb-3">No results found</h3>
                        <p class="tw-text-gray-500 dark:tw-text-gray-400 tw-text-lg">We couldn't find anything matching "<span class="tw-font-semibold tw-text-maroon">${query}</span>".</p>
                    </div>
                `;
                return;
            }

            let html = `
                <div class="tw-mb-6 tw-flex tw-flex-col sm:tw-flex-row tw-items-center tw-justify-between tw-gap-4 tw-max-w-5xl tw-mx-auto tw-animate-fade-in-up">
                    <h3 class="tw-text-xl tw-font-bold tw-text-gray-800 dark:tw-text-gray-100">
                        Search Results for "<span class="tw-text-transparent tw-bg-clip-text tw-bg-gradient-to-r tw-from-maroon tw-to-red-500">${query}</span>"
                    </h3>
                    <span class="tw-text-sm tw-font-medium tw-text-maroon tw-bg-maroon/10 dark:tw-bg-maroon/20 tw-px-4 tw-py-1.5 tw-rounded-full tw-border tw-border-maroon/20 tw-shadow-sm">
                        Found in ${categories.length} categories
                    </span>
                </div>
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-2 tw-gap-8 tw-max-w-5xl tw-mx-auto">
            `;

            categories.forEach((category, index) => {
                const data = results[category];
                const animationDelay = index * 0.1;
                html += `
                    <div class="tw-bg-white dark:tw-bg-slate-800 tw-rounded-2xl tw-shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:tw-shadow-[0_8px_30px_rgb(0,0,0,0.08)] dark:hover:tw-shadow-[0_8px_30px_rgb(0,0,0,0.3)] tw-transition-all tw-duration-300 tw-border tw-border-gray-100 dark:tw-border-slate-700 tw-overflow-hidden tw-transform hover:-tw-translate-y-1 tw-animate-fade-in-up" style="animation-delay: ${animationDelay}s; opacity: 0; animation-fill-mode: forwards;">
                        <div class="tw-bg-gray-50/80 dark:tw-bg-slate-800/80 tw-px-6 tw-py-5 tw-border-b tw-border-gray-100 dark:tw-border-slate-700 tw-flex tw-justify-between tw-items-center tw-backdrop-blur-sm">
                            <h4 class="tw-text-lg tw-font-bold tw-text-gray-800 dark:tw-text-gray-100 tw-flex tw-items-center tw-gap-2">
                                <div class="tw-w-2 tw-h-2 tw-rounded-full tw-bg-maroon"></div>
                                ${category}
                            </h4>
                            <span class="tw-bg-maroon tw-text-white tw-text-xs tw-font-bold tw-px-3 tw-py-1 tw-rounded-full tw-shadow-sm">${data.count}</span>
                        </div>
                        <ul class="tw-divide-y tw-divide-gray-50 dark:tw-divide-slate-700/50 tw-max-h-72 tw-overflow-y-auto custom-scrollbar">
                `;

                data.items.forEach(item => {
                    const displaySafe = (item.display || '').replace(/"/g, '&quot;');
                    const detailsSafe = (item.details || 'No additional details available.').replace(/"/g, '&quot;');
                    
                    const escapeHtml = (text) => text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                    let displayHtml = escapeHtml(item.display || '');
                    
                    if (query) {
                        const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                        const regex = new RegExp('(' + escapedQuery + ')', 'gi');
                        const highlightTag = '<span class="tw-bg-yellow-200 dark:tw-bg-yellow-900/50 tw-text-gray-900 dark:tw-text-yellow-100 tw-rounded tw-px-1">$1</span>';
                        displayHtml = displayHtml.replace(regex, highlightTag);
                    }

                    html += `
                        <li>
                            <button type="button" class="search-result-btn tw-w-full tw-text-left tw-flex tw-items-center tw-justify-between tw-px-6 tw-py-4 hover:tw-bg-maroon/5 dark:hover:tw-bg-slate-700/50 tw-transition-all tw-duration-200 tw-group tw-border-l-2 tw-border-transparent hover:tw-border-maroon"
                                data-url="${item.url}"
                                data-display="${displaySafe}"
                                data-details="${detailsSafe}"
                                data-image="${item.image || ''}">
                                <div class="tw-flex tw-items-center tw-gap-4">
                                    <div class="tw-w-10 tw-h-10 tw-rounded-full tw-bg-gray-100 dark:tw-bg-slate-700 group-hover:tw-bg-maroon/10 tw-flex tw-items-center tw-justify-center tw-text-gray-500 group-hover:tw-text-maroon tw-transition-colors tw-shadow-sm">
                                        <i class="bi bi-link-45deg tw-text-lg"></i>
                                    </div>
                                    <span class="tw-text-gray-700 dark:tw-text-gray-300 tw-font-semibold group-hover:tw-text-maroon tw-transition-colors">${displayHtml}</span>
                                </div>
                                <div class="tw-flex tw-items-center">
                                    ${item.image ? `<img src="${item.image}" alt="Preview" class="tw-w-12 tw-h-12 tw-object-cover tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-100 dark:tw-border-slate-600 tw-mr-4" />` : ''}
                                    <i class="bi bi-chevron-right tw-text-gray-300 group-hover:tw-text-maroon group-hover:tw-translate-x-1 tw-transition-all"></i>
                                </div>
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
                clearBtn.classList.remove('tw-hidden');
            } else {
                clearBtn.classList.add('tw-hidden');
                resultsContainer.innerHTML = '';
                return;
            }

            // Show loading spinner
            searchIconContainer.innerHTML = `<div class="spinner-border tw-text-maroon/50 tw-w-5 tw-h-5 tw-border-2 tw-border-t-maroon tw-rounded-full tw-animate-spin" style="border-top-color: transparent;"></div>`;

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const formData = new FormData();
                let url = `{{ route('super-admin.global-search') }}`;
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
                .catch(error => console.error('Error fetching search results:', error))
                .finally(() => {
                    // Restore search icon
                    searchIconContainer.innerHTML = `<i class="bi bi-search tw-text-xl"></i>`;
                });
            }, 300); // 300ms debounce
        };

        // Handle text input changes
        searchInput.addEventListener('input', performSearch);
        
        // Handle image upload changes
        imageUploadInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreviewContainer.classList.remove('tw-hidden');
                    imagePreviewContainer.classList.add('tw-flex');
                    searchInput.value = ''; // Clear text query when image is uploaded
                    performSearch();
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Handle remove image
        removeImageBtn.addEventListener('click', function() {
            imageUploadInput.value = '';
            imagePreviewContainer.classList.add('tw-hidden');
            imagePreviewContainer.classList.remove('tw-flex');
            imagePreview.src = '';
            performSearch();
        });

        // Handle modal opens
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
                imageContainer.classList.remove('tw-hidden');
            } else {
                imageContainer.classList.add('tw-hidden');
            }
            
            // Show modal with animation
            modal.classList.remove('tw-hidden');
            modal.classList.add('tw-flex');
            
            // Small delay to allow display block to apply before animating opacity
            setTimeout(() => {
                modal.classList.remove('tw-opacity-0');
                modalContent.classList.remove('tw-scale-95');
            }, 10);
        });

        // Close when clicking outside
        document.getElementById('detailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                window.closeDetailsModal();
            }
        });
        
        window.closeDetailsModal = function() {
            const modal = document.getElementById('detailsModal');
            const modalContent = document.getElementById('detailsModalContent');
            
            modal.classList.add('tw-opacity-0');
            modalContent.classList.add('tw-scale-95');
            
            setTimeout(() => {
                modal.classList.add('tw-hidden');
                modal.classList.remove('tw-flex');
            }, 300);
        };
    });
</script>
@endsection
