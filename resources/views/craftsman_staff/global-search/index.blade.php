@extends('craftsman_staff.layouts.app')

@section('title', 'Global Search | Craftsman Staff')

@section('content')
<div class="max-w-5xl mx-auto py-8">
    <div class="mb-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Global Search</h2>
        <p class="text-gray-600 dark:text-gray-400">Search across all modules: Work Orders, Products, Craftsmen, Buyers, and more.</p>
    </div>

    <!-- Search Form -->
    <div class="mb-10">
        <form action="{{ route('craftsman_staff.global-search') }}" method="GET" class="relative" id="searchForm" onsubmit="return false;">
            <div class="relative flex items-center w-full h-16 rounded-2xl bg-white dark:bg-slate-900 shadow-xl overflow-hidden border border-gray-200 dark:border-slate-700 focus-within:ring-2 focus-within:ring-indigo-600 transition-all">
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
                <a href="{{ route('craftsman_staff.global-search') }}" id="clearBtn" class="h-full grid place-items-center w-16 text-gray-400 hover:text-indigo-600 transition-colors mr-2 {{ request('search') ? '' : 'hidden' }}">
                    <i class="bi bi-x-circle text-lg"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Results Section -->
    <div id="resultsContainer">
        @if(request()->has('search') && !empty($query))
            @if(empty($results))
                <div class="text-center py-16 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800">
                    <div class="text-gray-300 dark:text-slate-700 mb-4">
                        <i class="bi bi-search text-6xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">No results found</h3>
                    <p class="text-gray-500 dark:text-gray-400">We couldn't find anything matching "<span class="font-medium text-indigo-600">{{ $query }}</span>".</p>
                </div>
            @else
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                        Search Results for "<span class="text-indigo-600">{{ $query }}</span>"
                    </h3>
                    <span class="text-sm text-gray-500 bg-gray-200 dark:bg-slate-800 px-3 py-1 rounded-full">
                        Found in {{ count($results) }} categories
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($results as $category => $data)
                        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-md border border-gray-100 dark:border-slate-800 overflow-hidden">
                            <div class="bg-gray-50 dark:bg-slate-800/50 px-5 py-4 border-b border-gray-100 dark:border-slate-700 flex justify-between items-center">
                                <h4 class="font-bold text-gray-800 dark:text-gray-100">{{ $category }}</h4>
                                <span class="bg-indigo-600 text-white text-xs font-bold px-2 py-1 rounded-full">{{ $data['count'] }}</span>
                            </div>
                            <ul class="divide-y divide-gray-50 dark:divide-slate-800/50 max-h-64 overflow-y-auto">
                                @foreach($data['items'] as $item)
                                    <li>
                                        <a href="{{ $item['url'] }}" class="flex items-center justify-between px-5 py-3 hover:bg-indigo-600/5 dark:hover:bg-slate-800 transition-colors group">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-indigo-600/10 flex items-center justify-center text-indigo-600">
                                                    <i class="bi bi-link-45deg"></i>
                                                </div>
                                                <span class="text-gray-700 dark:text-gray-300 font-medium group-hover:text-indigo-600 transition-colors">{{ $item['display'] }}</span>
                                            </div>
                                            <i class="bi bi-chevron-right text-gray-300 group-hover:text-indigo-600 transition-colors text-sm"></i>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const resultsContainer = document.getElementById('resultsContainer');
        const clearBtn = document.getElementById('clearBtn');
        const searchIconContainer = document.getElementById('searchIconContainer');
        
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
                    <div class="text-center py-16 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800">
                        <div class="text-gray-300 dark:text-slate-700 mb-4">
                            <i class="bi bi-search text-6xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">No results found</h3>
                        <p class="text-gray-500 dark:text-gray-400">We couldn't find anything matching "<span class="font-medium text-indigo-600">${query}</span>".</p>
                    </div>
                `;
                return;
            }

            let html = `
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                        Search Results for "<span class="text-indigo-600">${query}</span>"
                    </h3>
                    <span class="text-sm text-gray-500 bg-gray-200 dark:bg-slate-800 px-3 py-1 rounded-full">
                        Found in ${categories.length} categories
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            `;

            categories.forEach(category => {
                const data = results[category];
                html += `
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-md border border-gray-100 dark:border-slate-800 overflow-hidden">
                        <div class="bg-gray-50 dark:bg-slate-800/50 px-5 py-4 border-b border-gray-100 dark:border-slate-700 flex justify-between items-center">
                            <h4 class="font-bold text-gray-800 dark:text-gray-100">${category}</h4>
                            <span class="bg-indigo-600 text-white text-xs font-bold px-2 py-1 rounded-full">${data.count}</span>
                        </div>
                        <ul class="divide-y divide-gray-50 dark:divide-slate-800/50 max-h-64 overflow-y-auto">
                `;

                data.items.forEach(item => {
                    html += `
                        <li>
                            <a href="${item.url}" class="flex items-center justify-between px-5 py-3 hover:bg-indigo-600/5 dark:hover:bg-slate-800 transition-colors group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-600/10 flex items-center justify-center text-indigo-600">
                                        <i class="bi bi-link-45deg"></i>
                                    </div>
                                    <span class="text-gray-700 dark:text-gray-300 font-medium group-hover:text-indigo-600 transition-colors">${item.display}</span>
                                </div>
                                <i class="bi bi-chevron-right text-gray-300 group-hover:text-indigo-600 transition-colors text-sm"></i>
                            </a>
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

        // Handle input changes
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();

            if (query.length > 0) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
                resultsContainer.innerHTML = '';
                return;
            }

            // Show loading spinner
            searchIconContainer.innerHTML = `<div class="spinner-border text-secondary w-5 h-5" role="status"></div>`;

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetch(`{{ route('craftsman_staff.global-search') }}?search=${encodeURIComponent(query)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    renderResults(data.query, data.results);
                })
                .catch(error => console.error('Error fetching search results:', error))
                .finally(() => {
                    // Restore search icon
                    searchIconContainer.innerHTML = `<i class="bi bi-search text-xl"></i>`;
                });
            }, 300); // 300ms debounce
        });
    });
</script>
@endsection


