@extends('super-admin.layouts.app')

@section('title', 'Craftsman Production Dashboard')

@section('content')
<style>
    .highlight-term {
        background-color: #ffeb3b !important;
        color: #000 !important;
        font-weight: 700;
        padding: 1px 4px;
        border-radius: 3px;
        display: inline-block;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Craftsman Production Dashboard</h1>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Select Craftsman</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" id="liveSearchInput" class="form-control" placeholder="Type here to instantly search and highlight..." value="{{ request('search', $search ?? '') }}" autocomplete="off">
                                <button type="button" id="clearSearchBtn" class="btn btn-outline-secondary d-none" title="Clear">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <form action="{{ route('super-admin.craftsman-production.index') }}" method="GET" id="perPageForm">
                                <input type="hidden" name="search" id="perPageSearchField" value="{{ request('search', $search ?? '') }}">
                                <div class="input-group">
                                    <span class="input-group-text">Per Page</span>
                                    <select name="per_page" class="form-select" onchange="document.getElementById('perPageForm').submit();">
                                        <option value="10" {{ request('per_page', $perPage ?? 20) == 10 ? 'selected' : '' }}>10</option>
                                        <option value="20" {{ request('per_page', $perPage ?? 20) == 20 ? 'selected' : '' }}>20</option>
                                        <option value="50" {{ request('per_page', $perPage ?? 20) == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page', $perPage ?? 20) == 100 ? 'selected' : '' }}>100</option>
                                    </select>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-2">
                            <button type="button" class="btn btn-secondary w-100" id="resetBtn">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Craftsmen List</h5>
                    <small class="text-muted" id="resultsCount">
                        Showing {{ $craftsmen->firstItem() ?? 0 }} to {{ $craftsmen->lastItem() ?? 0 }} of {{ $craftsmen->total() }} entries
                    </small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="craftsmenTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Business Name</th>
                                    <th>City</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($craftsmen as $craftsman)
                                <tr class="craftsman-row">
                                    <td><span class="badge bg-secondary search-item" data-text="{{ $craftsman->craftman_code }}">{{ $craftsman->craftman_code }}</span></td>
                                    <td class="search-item" data-text="{{ $craftsman->name }}">{{ $craftsman->name }}</td>
                                    <td class="search-item" data-text="{{ $craftsman->business_name }}">{{ $craftsman->business_name }}</td>
                                    <td class="search-item" data-text="{{ $craftsman->city ?? 'N/A' }}">{{ $craftsman->city ?? 'N/A' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('super-admin.craftsman-production.show', $craftsman->craftman_code) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-graph-up"></i> View Production
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">No craftsmen found.</td>
                                </tr>
                                @endforelse
                                <tr id="noMatchMessage" class="d-none">
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-info-circle me-1"></i> No matching records found for this live search.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($craftsmen->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center" id="paginationArea">
                    <div>
                        {{ $craftsmen->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
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

        // Toggle clear button
        if (clearBtn) {
            clearBtn.classList.toggle('d-none', rawTerm === '');
        }

        // Reset if blank
        if (term === '') {
            rows.forEach(row => {
                row.style.display = '';
                row.querySelectorAll('.search-item').forEach(cell => {
                    cell.innerHTML = cell.getAttribute('data-text');
                });
            });
            if (noMatchMessage) noMatchMessage.classList.add('d-none');
            if (paginationArea) paginationArea.style.display = '';
            return;
        }

        // Hide pagination during live filtering
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
            noMatchMessage.classList.toggle('d-none', matchedCount > 0);
        }
    }

    // Trigger on every keystroke, backspace, and paste
    searchInput.addEventListener('input', function () {
        filterAndHighlight(this.value);
    });

    // Clear search
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

    // Initial check
    if (searchInput.value.trim() !== '') {
        filterAndHighlight(searchInput.value);
    }
});
</script>
@endsection