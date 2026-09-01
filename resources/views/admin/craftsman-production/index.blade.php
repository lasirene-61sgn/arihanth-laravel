@extends('admin.layouts.app')

@section('title', 'Craftsman Production Dashboard')

@section('content')
<style>
    .highlight-term {
        background-color: #ffeb3b !important;
        color: #000000 !important;
        font-weight: 700;
        padding: 1px 4px;
        border-radius: 3px;
        display: inline-block;
    }
</style>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Craftsman Production Dashboard</h1>
    </div>
    
    <!-- Filter and Live Search Controls -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <!-- Live Search Input -->
            <div class="md:col-span-7">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           id="liveSearchInput" 
                           class="w-full pl-10 pr-10 py-2 rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-magenta-800 focus:outline-none" 
                           placeholder="Type to live search and highlight by name, code, business..." 
                           value="{{ request('search', $search ?? '') }}" 
                           autocomplete="off">
                    <button type="button" 
                            id="clearSearchBtn" 
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hidden" 
                            title="Clear Search">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
            </div>

            <!-- Per Page Dropdown -->
            <div class="md:col-span-3">
                <form action="{{ route('admin.craftsman-production.index') }}" method="GET" id="perPageForm">
                    <input type="hidden" name="search" id="perPageSearchField" value="{{ request('search', $search ?? '') }}">
                    <div class="flex items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400 mr-2 whitespace-nowrap">Per Page:</span>
                        <select name="per_page" 
                                class="w-full py-2 px-3 rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-magenta-800 focus:outline-none" 
                                onchange="document.getElementById('perPageForm').submit();">
                            <option value="10" {{ request('per_page', $perPage ?? 20) == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ request('per_page', $perPage ?? 20) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page', $perPage ?? 20) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', $perPage ?? 20) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Reset Button -->
            <div class="md:col-span-2">
                <button type="button" 
                        id="resetBtn" 
                        class="w-full bg-gray-200 dark:bg-gray-750 hover:bg-gray-300 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 py-2 px-4 rounded-lg font-medium transition-colors">
                    <i class="bi bi-arrow-clockwise mr-1"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Craftsmen Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
        <div class="p-4 bg-gray-50 dark:bg-gray-750 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Craftsmen List</h2>
            <span class="text-xs text-gray-500 dark:text-gray-400">
                Showing {{ $craftsmen->firstItem() ?? 0 }} to {{ $craftsmen->lastItem() ?? 0 }} of {{ $craftsmen->total() }} entries
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="craftsmenTable">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200">Code</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200">Name</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200">Business Name</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200">City</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($craftsmen as $craftsman)
                    <tr class="craftsman-row hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <td class="px-6 py-4">
                            <span class="search-item bg-gray-100 dark:bg-gray-900 px-2 py-1 rounded text-xs font-bold text-gray-600 dark:text-gray-400" data-text="{{ $craftsman->craftman_code }}">
                                {{ $craftsman->craftman_code }}
                            </span>
                        </td>
                        <td class="search-item px-6 py-4 text-gray-800 dark:text-gray-200 font-medium" data-text="{{ $craftsman->name }}">
                            {{ $craftsman->name }}
                        </td>
                        <td class="search-item px-6 py-4 text-gray-600 dark:text-gray-400" data-text="{{ $craftsman->business_name }}">
                            {{ $craftsman->business_name }}
                        </td>
                        <td class="search-item px-6 py-4 text-gray-600 dark:text-gray-400" data-text="{{ $craftsman->city ?? 'N/A' }}">
                            {{ $craftsman->city ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.craftsman-production.show', $craftsman->craftman_code) }}" class="text-magenta-800 hover:text-magenta-900 font-semibold text-sm">
                                <i class="bi bi-graph-up mr-1"></i> View Production
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">No craftsmen found.</td>
                    </tr>
                    @endforelse
                    
                    <tr id="noMatchMessage" class="hidden">
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                            <i class="bi bi-info-circle mr-1"></i> No matching craftsmen found for this live search.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($craftsmen->hasPages())
        <div class="p-6 border-t border-gray-200 dark:border-gray-700" id="paginationArea">
            {{ $craftsmen->links() }}
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('liveSearchInput');
    const clearBtn = document.getElementById('clearSearchBtn');
    const resetBtn = document.getElementById('resetBtn');
    const rows = document.querySelectorAll('#craftsmenTable tbody tr.craftsman-row');
    const noMatchMessage = document.getElementById('noMatchMessage');
    const paginationArea = document.getElementById('paginationArea');
    const perPageSearchField = document.getElementById('perPageSearchField');

    function filterAndHighlight(query) {
        const rawTerm = query.trim();
        const term = rawTerm.toLowerCase();

        if (perPageSearchField) perPageSearchField.value = rawTerm;

        // Toggle clear icon
        if (clearBtn) {
            clearBtn.classList.toggle('hidden', rawTerm === '');
        }

        // Reset if empty query
        if (term === '') {
            rows.forEach(row => {
                row.style.display = '';
                row.querySelectorAll('.search-item').forEach(cell => {
                    cell.innerHTML = cell.getAttribute('data-text');
                });
            });
            if (noMatchMessage) noMatchMessage.classList.add('hidden');
            if (paginationArea) paginationArea.style.display = '';
            return;
        }

        // Hide pagination when active filtering
        if (paginationArea) paginationArea.style.display = 'none';

        const regex = new RegExp(`(${rawTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        let matchedCount = 0;

        rows.forEach(row => {
            const cells = row.querySelectorAll('.search-item');
            let isRowMatch = false;

            cells.forEach(cell => {
                const originalText = cell.getAttribute('data-text') || '';
                if (originalText.toLowerCase().includes(term)) {
                    isRowMatch = true;
                    cell.innerHTML = originalText.replace(regex, '<span class="highlight-term">$1</span>');
                } else {
                    cell.innerHTML = originalText;
                }
            });

            if (isRowMatch) {
                row.style.display = '';
                matchedCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (noMatchMessage) {
            noMatchMessage.classList.toggle('hidden', matchedCount > 0);
        }
    }

    // Live search listener
    searchInput.addEventListener('input', function () {
        filterAndHighlight(this.value);
    });

    // Clear search button
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            filterAndHighlight('');
            searchInput.focus();
        });
    }

    // Reset button
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            searchInput.value = '';
            filterAndHighlight('');
            searchInput.focus();
        });
    }

    // Initial run if pre-filled
    if (searchInput.value.trim() !== '') {
        filterAndHighlight(searchInput.value);
    }
});
</script>
@endsection