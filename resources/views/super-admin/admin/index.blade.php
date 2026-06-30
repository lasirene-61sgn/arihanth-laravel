@extends('super-admin.layouts.app')

@section('title', __('messages.admin_management'))

@section('content')
<div class="tw-bg-white tw-min-h-screen tw-p-4 md:tw-p-6">
    <!-- Page Header -->
    <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-items-start sm:tw-items-center tw-gap-4 tw-pb-4 tw-mb-6 tw-border-b tw-border-gray-200">
        <h1 class="tw-text-2xl tw-font-bold tw-text-gray-800">{{ __('messages.admin_management') }}</h1>
        <div class="tw-flex tw-flex-wrap tw-gap-2">
            <a href="{{ route('super-admin.admin.index', array_merge(request()->all(), ['export' => 'excel'])) }}" class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-4 tw-py-2 tw-bg-emerald-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-emerald-700 tw-transition-colors tw-no-underline">
                <i class="bi bi-file-earmark-spreadsheet"></i> {{ __('messages.export') }}
            </a>
            <button onclick="printSelectedAdmins()" class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-4 tw-py-2 tw-border tw-border-[#8B3B16] tw-text-[#8B3B16] tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-[#8B3B16] hover:tw-text-white tw-transition-colors tw-bg-white">
                <i class="bi bi-check-all"></i> {{ __('messages.print') }}
            </button>
            <a href="{{ route('super-admin.admin.create') }}" class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-4 tw-py-2 tw-bg-[#8B3B16] tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-[#722F11] tw-transition-colors tw-no-underline">
                <i class="bi bi-plus-lg"></i> {{ __('messages.add_admin') }}
            </a>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="tw-flex tw-flex-wrap tw-gap-2 tw-mb-4">
        <button type="button" class="tw-px-3 tw-py-2 tw-border tw-border-[#8B3B16] tw-text-[#8B3B16] tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-[#8B3B16] hover:tw-text-white tw-transition-colors tw-bg-white" onclick="toggleSection('searchSection')"><i class="bi bi-search tw-mr-1"></i> Search</button>
        <button type="button" class="tw-px-3 tw-py-2 tw-border tw-border-gray-400 tw-text-gray-600 tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-gray-100 tw-transition-colors tw-bg-white" onclick="toggleSection('filterSection')"><i class="bi bi-funnel tw-mr-1"></i> Filter</button>
        <button type="button" class="tw-px-3 tw-py-2 tw-border tw-border-cyan-500 tw-text-cyan-600 tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-cyan-50 tw-transition-colors tw-bg-white" onclick="toggleSection('sortSection')"><i class="bi bi-layout-three-columns tw-mr-1"></i> Sort & Columns</button>
        <button type="button" class="tw-text-gray-400 tw-text-sm hover:tw-text-gray-600 tw-transition-colors tw-bg-transparent tw-border-none" onclick="resetAll()">Reset View</button>
    </div>

    <!-- Search Section -->
    <div id="searchSection" class="tw-bg-white tw-border tw-border-[#8B3B16] tw-rounded-xl tw-shadow-sm tw-mb-4 tw-hidden">
        <div class="tw-p-4">
            <form method="GET" action="{{ route('super-admin.admin.index') }}" class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, or code..." class="tw-flex-1 tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-ring-2 focus:tw-ring-[#8B3B16] focus:tw-border-[#8B3B16] tw-outline-none">
                <button type="submit" class="tw-px-6 tw-py-2 tw-bg-[#8B3B16] tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-[#722F11] tw-transition-colors">{{ __('messages.search') }}</button>
            </form>
        </div>
    </div>

    <!-- Filter Section -->
    <div id="filterSection" class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-xl tw-shadow-sm tw-mb-4 tw-hidden">
        <div class="tw-p-4">
            <form method="GET" action="{{ route('super-admin.admin.index') }}" class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-2">
                <select name="status_filter" class="tw-flex-1 tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-ring-2 focus:tw-ring-[#8B3B16] tw-outline-none">
                    <option value="">{{ __('messages.status') }}</option>
                    <option value="1" {{ request('status_filter') == '1' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                    <option value="0" {{ request('status_filter') == '0' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                </select>
                <button type="submit" class="tw-px-6 tw-py-2 tw-bg-gray-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-gray-700 tw-transition-colors">{{ __('messages.apply_filters') }}</button>
            </form>
        </div>
    </div>

    <!-- Sort Section -->
    <div id="sortSection" class="tw-bg-white tw-border tw-border-cyan-400 tw-rounded-xl tw-shadow-sm tw-mb-4 tw-hidden">
        <div class="tw-p-4">
            <form method="GET" action="{{ route('super-admin.admin.index') }}">
                <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-3 tw-gap-3 tw-pb-4 tw-mb-4 tw-border-b tw-border-gray-200">
                    <div>
                        <label class="tw-text-xs tw-font-bold tw-text-gray-600 tw-mb-1 tw-block">Order By</label>
                        <select name="sort_by" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-ring-2 focus:tw-ring-[#8B3B16] tw-outline-none">
                            <option value="user_code" {{ request('sort_by') == 'user_code' ? 'selected' : '' }}>User Code</option>
                            <option value="full_name" {{ request('sort_by') == 'full_name' ? 'selected' : '' }}>Full Name</option>
                            <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Creation Date</option>
                        </select>
                    </div>
                    <div>
                        <label class="tw-text-xs tw-font-bold tw-text-gray-600 tw-mb-1 tw-block">Direction</label>
                        <select name="sort_order" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-ring-2 focus:tw-ring-[#8B3B16] tw-outline-none">
                            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                            <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Descending</option>
                        </select>
                    </div>
                    <div class="tw-flex tw-items-end">
                        <button type="submit" class="tw-w-full tw-px-4 tw-py-2 tw-bg-cyan-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-cyan-700 tw-transition-colors">Rearrange</button>
                    </div>
                </div>
                <div>
                    <label class="tw-text-xs tw-font-bold tw-text-gray-600 tw-mb-2 tw-block">Show/Hide Headings:</label>
                    <div class="tw-flex tw-flex-wrap tw-gap-4">
                        @foreach(['col-code'=>'Code', 'col-name'=>'Name', 'col-email'=>'Email', 'col-mobile'=>'Mobile', 'col-status'=>'Status', 'col-perms'=>'Perms'] as $val => $label)
                            <label class="tw-flex tw-items-center tw-gap-2 tw-text-sm tw-text-gray-700 tw-cursor-pointer">
                                <input class="form-check-input column-checkbox" type="checkbox" value="{{ $val }}" id="check{{ $val }}" checked>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="tw-bg-white tw-border tw-border-gray-200 tw-rounded-xl tw-shadow-sm tw-overflow-hidden">
        <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-sm" id="adminTable">
                <thead>
                    <tr class="tw-bg-gray-50 tw-border-b tw-border-gray-200">
                        <th class="tw-px-4 tw-py-3 tw-w-10">
                            <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll(this.checked)">
                        </th>
                        <th class="col-code tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-600 tw-uppercase tw-tracking-wider">{{ __('messages.name') }}</th>
                        <th class="col-name tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-600 tw-uppercase tw-tracking-wider">{{ __('messages.full_name') }}</th>
                        <th class="col-mobile tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-600 tw-uppercase tw-tracking-wider">{{ __('messages.mobile') }}</th>
                        <th class="col-status tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-600 tw-uppercase tw-tracking-wider">{{ __('messages.status') }}</th>
                        <th class="col-actions tw-px-4 tw-py-3 tw-text-right tw-text-xs tw-font-semibold tw-text-gray-600 tw-uppercase tw-tracking-wider">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="tw-divide-y tw-divide-gray-100">
                    @foreach($admins as $admin)
                    <tr class="hover:tw-bg-gray-50 tw-transition-colors">
                        <td class="tw-px-4 tw-py-3">
                            <input type="checkbox" class="form-check-input admin-checkbox" value="{{ $admin->id }}">
                        </td>
                        <td class="col-code tw-px-4 tw-py-3 tw-font-medium tw-text-gray-800">{{ $admin->user_code }}</td>
                        <td class="col-name tw-px-4 tw-py-3 tw-text-gray-700">{{ $admin->full_name }}</td>
                        <td class="col-mobile tw-px-4 tw-py-3 tw-text-gray-600">{{ $admin->mobile_no }}</td>
                        <td class="col-status tw-px-4 tw-py-3">
                            <span class="tw-px-2.5 tw-py-1 tw-rounded-full tw-text-xs tw-font-semibold {{ $admin->status == 1 ? 'tw-bg-green-100 tw-text-green-700' : 'tw-bg-gray-100 tw-text-gray-600' }}">
                                {{ $admin->status == 1 ? __('messages.active') : __('messages.inactive') }}
                            </span>
                        </td>
                        <td class="col-actions tw-px-4 tw-py-3 tw-text-right">
                            <div class="tw-flex tw-justify-end tw-gap-1">
                                <a href="{{ route('super-admin.admin.show', $admin) }}" class="tw-p-1.5 tw-text-cyan-600 tw-border tw-border-cyan-200 tw-rounded-md hover:tw-bg-cyan-50 tw-transition-colors tw-no-underline"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('super-admin.admin.edit', $admin) }}" class="tw-p-1.5 tw-text-[#8B3B16] tw-border tw-border-[#8B3B16]/30 tw-rounded-md hover:tw-bg-[#8B3B16]/10 tw-transition-colors tw-no-underline"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('super-admin.admin.destroy', $admin) }}" method="POST" class="tw-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="tw-p-1.5 tw-text-red-500 tw-border tw-border-red-200 tw-rounded-md hover:tw-bg-red-50 tw-transition-colors tw-bg-white" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function toggleSection(sectionId) {
        const sections = ['searchSection', 'filterSection', 'sortSection'];
        sections.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                if (id === sectionId) {
                    el.classList.toggle('tw-hidden');
                } else {
                    el.classList.add('tw-hidden');
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const savedSettings = localStorage.getItem('adminTableColumns');
        if (savedSettings) {
            const hiddenColumns = JSON.parse(savedSettings);
            document.querySelectorAll('.column-checkbox').forEach(checkbox => {
                if (hiddenColumns.includes(checkbox.value)) {
                    checkbox.checked = false;
                    applyColumnVisibility(checkbox.value, false);
                }
            });
        }
    });

    document.querySelectorAll('.column-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            applyColumnVisibility(this.value, this.checked);
            saveToStorage();
        });
    });

    function applyColumnVisibility(colClass, isVisible) {
        document.querySelectorAll('.' + colClass).forEach(el => {
            el.style.display = isVisible ? '' : 'none';
        });
    }

    function saveToStorage() {
        const hiddenColumns = Array.from(document.querySelectorAll('.column-checkbox:not(:checked)')).map(cb => cb.value);
        localStorage.setItem('adminTableColumns', JSON.stringify(hiddenColumns));
    }

    function resetAll() {
        localStorage.removeItem('adminTableColumns');
        window.location.href = "{{ route('super-admin.admin.index') }}";
    }

    function toggleSelectAll(checked) {
        document.querySelectorAll('.admin-checkbox').forEach(cb => cb.checked = checked);
    }

    function printSelectedAdmins() {
        const selected = Array.from(document.querySelectorAll('.admin-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) { alert('Please select at least one admin to print.'); return; }
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('super-admin.admin.print-selected') }}";
        form.target = '_blank';
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden'; csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrfInput);
        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'selected_admins[]'; input.value = id;
            form.appendChild(input);
        });
        document.body.appendChild(form); form.submit(); document.body.removeChild(form);
    }
</script>
@endsection
