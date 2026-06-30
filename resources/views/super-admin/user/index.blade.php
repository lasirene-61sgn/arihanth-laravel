@extends('super-admin.layouts.app')

@section('title', 'User Management')

@section('content')
<div class="tw-bg-white tw-min-h-screen tw-p-4 md:tw-p-6">
    <!-- Page Header -->
    <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-items-start sm:tw-items-center tw-gap-4 tw-pb-4 tw-mb-6 tw-border-b tw-border-gray-200">
        <h1 class="tw-text-2xl tw-font-bold tw-text-gray-800">User Management</h1>
        <div class="tw-flex tw-flex-wrap tw-gap-2">
            <a href="{{ route('super-admin.user.index', array_merge(request()->all(), ['export' => 'excel'])) }}" class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-4 tw-py-2 tw-bg-emerald-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-emerald-700 tw-transition-colors tw-no-underline">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export
            </a>
            <button onclick="printSelectedUsers()" class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-4 tw-py-2 tw-border tw-border-[#8B3B16] tw-text-[#8B3B16] tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-[#8B3B16] hover:tw-text-white tw-transition-colors tw-bg-white">
                <i class="bi bi-check-all"></i> Print
            </button>
            <a href="{{ route('super-admin.user.create') }}" class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-4 tw-py-2 tw-bg-[#8B3B16] tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-[#722F11] tw-transition-colors tw-no-underline">
                <i class="bi bi-plus-lg"></i> Add New User
            </a>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="tw-flex tw-flex-wrap tw-gap-2 tw-mb-4">
        <button class="tw-px-3 tw-py-2 tw-border tw-border-[#8B3B16] tw-text-[#8B3B16] tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-[#8B3B16] hover:tw-text-white tw-transition-colors tw-bg-white" onclick="toggleSection('searchSection')"><i class="bi bi-search tw-mr-1"></i> Search</button>
        <button class="tw-px-3 tw-py-2 tw-border tw-border-gray-400 tw-text-gray-600 tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-gray-100 tw-transition-colors tw-bg-white" onclick="toggleSection('filterSection')"><i class="bi bi-funnel tw-mr-1"></i> Filter</button>
        <button class="tw-px-3 tw-py-2 tw-border tw-border-cyan-500 tw-text-cyan-600 tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-cyan-50 tw-transition-colors tw-bg-white" onclick="toggleSection('sortSection')"><i class="bi bi-layout-three-columns tw-mr-1"></i> Sort & Columns</button>
        <button class="tw-text-gray-400 tw-text-sm hover:tw-text-gray-600 tw-transition-colors tw-bg-transparent tw-border-none" onclick="resetAll()">Reset View</button>
    </div>

    <!-- Search Section -->
    <div id="searchSection" class="tw-bg-white tw-border tw-border-[#8B3B16] tw-rounded-xl tw-shadow-sm tw-mb-4 tw-hidden">
        <div class="tw-p-4">
            <form method="GET" action="{{ route('super-admin.user.index') }}" class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..." class="tw-flex-1 tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-ring-2 focus:tw-ring-[#8B3B16] focus:tw-border-[#8B3B16] tw-outline-none">
                <button type="submit" class="tw-px-6 tw-py-2 tw-bg-[#8B3B16] tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-[#722F11] tw-transition-colors">Search</button>
            </form>
        </div>
    </div>

    <!-- Filter Section -->
    <div id="filterSection" class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-xl tw-shadow-sm tw-mb-4 tw-hidden">
        <div class="tw-p-4">
            <form method="GET" action="{{ route('super-admin.user.index') }}" class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-2">
                <select name="status_filter" class="tw-flex-1 tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-ring-2 focus:tw-ring-[#8B3B16] tw-outline-none">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status_filter') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status_filter') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="tw-px-6 tw-py-2 tw-bg-gray-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-gray-700 tw-transition-colors">Apply Filter</button>
            </form>
        </div>
    </div>

    <!-- Sort Section -->
    <div id="sortSection" class="tw-bg-white tw-border tw-border-cyan-400 tw-rounded-xl tw-shadow-sm tw-mb-4 tw-hidden">
        <div class="tw-p-4">
            <form method="GET" action="{{ route('super-admin.user.index') }}">
                <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-3 tw-gap-3 tw-pb-4 tw-mb-4 tw-border-b tw-border-gray-200">
                    <div>
                        <label class="tw-text-xs tw-font-bold tw-text-gray-600 tw-mb-1 tw-block">Order By</label>
                        <select name="sort_by" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-ring-2 focus:tw-ring-[#8B3B16] tw-outline-none">
                            <option value="user_code" {{ request('sort_by') == 'user_code' ? 'selected' : '' }}>User Code</option>
                            <option value="full_name" {{ request('sort_by') == 'full_name' ? 'selected' : '' }}>Full Name</option>
                            <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Registration Date</option>
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
                        @foreach(['col-code'=>'Code', 'col-name'=>'Name', 'col-email'=>'Email', 'col-mobile'=>'Mobile', 'col-city'=>'City', 'col-status'=>'Status', 'col-creator'=>'Creator', 'col-bp'=>'Partner'] as $val => $label)
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
        <div class="tw-flex tw-items-center tw-justify-between tw-px-4 tw-py-3 tw-border-b tw-border-gray-100">
            <h4 class="tw-text-base tw-font-semibold tw-text-gray-800 tw-m-0">All Users</h4>
            <span class="tw-px-3 tw-py-1 tw-bg-[#8B3B16] tw-text-white tw-rounded-full tw-text-xs tw-font-semibold">{{ $users->total() }} Records</span>
        </div>
        @if($users->count() > 0)
            <div class="tw-overflow-x-auto">
                <table class="tw-w-full tw-text-sm" id="userTable">
                    <thead>
                        <tr class="tw-bg-gray-50 tw-border-b tw-border-gray-200">
                            <th class="tw-px-4 tw-py-3 tw-w-10">
                                <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll(this.checked)">
                            </th>
                            <th class="col-code tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-600 tw-uppercase tw-tracking-wider">User Code</th>
                            <th class="col-bp tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-600 tw-uppercase tw-tracking-wider">BP Code</th>
                            <th class="col-name tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-600 tw-uppercase tw-tracking-wider">Full Name</th>
                            <th class="col-mobile tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-600 tw-uppercase tw-tracking-wider">Mobile</th>
                            <th class="col-city tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-600 tw-uppercase tw-tracking-wider">City</th>
                            <th class="col-status tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-600 tw-uppercase tw-tracking-wider">Status</th>
                            <!-- <th class="col-creator tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-600 tw-uppercase tw-tracking-wider">Created By</th> -->
                            <th class="col-actions tw-px-4 tw-py-3 tw-text-right tw-text-xs tw-font-semibold tw-text-gray-600 tw-uppercase tw-tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="tw-divide-y tw-divide-gray-100">
                        @foreach($users as $user)
                        <tr class="hover:tw-bg-gray-50 tw-transition-colors">
                            <td class="tw-px-4 tw-py-3">
                                <input type="checkbox" class="form-check-input user-checkbox" value="{{ $user->id }}">
                            </td>
                            <td class="col-code tw-px-4 tw-py-3 tw-font-medium tw-text-gray-800">{{ $user->user_code }}</td>
                            <td class="col-bp tw-px-4 tw-py-3 tw-text-gray-600">{{ $user->buyer->bp_code ?? 'N/A' }}</td>
                            <td class="col-name tw-px-4 tw-py-3 tw-text-gray-700">{{ $user->full_name }}</td>
                            <td class="col-mobile tw-px-4 tw-py-3 tw-text-gray-600">{{ $user->mobile_no }}</td>
                            <td class="col-city tw-px-4 tw-py-3 tw-text-gray-600">{{ $user->city }}</td>
                            <td class="col-status tw-px-4 tw-py-3">
                                <span class="tw-px-2.5 tw-py-1 tw-rounded-full tw-text-xs tw-font-semibold {{ $user->status == 'active' ? 'tw-bg-green-100 tw-text-green-700' : 'tw-bg-gray-100 tw-text-gray-600' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <!-- <td class="col-creator tw-px-4 tw-py-3 tw-text-gray-500 tw-text-xs">{{ $user->createdBy->full_name ?? 'System' }}</td> -->
                            <td class="col-actions tw-px-4 tw-py-3 tw-text-right">
                                <div class="tw-flex tw-justify-end tw-gap-1">
                                    <a href="{{ route('super-admin.user.show', $user) }}" class="tw-p-1.5 tw-text-cyan-600 tw-border tw-border-cyan-200 tw-rounded-md hover:tw-bg-cyan-50 tw-transition-colors tw-no-underline"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('super-admin.user.edit', $user) }}" class="tw-p-1.5 tw-text-[#8B3B16] tw-border tw-border-[#8B3B16]/30 tw-rounded-md hover:tw-bg-[#8B3B16]/10 tw-transition-colors tw-no-underline"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('super-admin.user.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete?');" class="tw-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="tw-p-1.5 tw-text-red-500 tw-border tw-border-red-200 tw-rounded-md hover:tw-bg-red-50 tw-transition-colors tw-bg-white"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="tw-px-4 tw-py-3 tw-border-t tw-border-gray-100 tw-flex tw-justify-end">
                {{ $users->links() }}
            </div>
        @else
            <div class="tw-text-center tw-py-12 tw-text-gray-400">
                <i class="bi bi-people tw-text-4xl tw-block tw-mb-2 tw-opacity-50"></i>
                <p>No users found.</p>
            </div>
        @endif
    </div>
</div>

<script>
    function toggleSection(sectionId) {
        const sections = ['searchSection', 'filterSection', 'sortSection'];
        sections.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                if (id === sectionId) { el.classList.toggle('tw-hidden'); } else { el.classList.add('tw-hidden'); }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const savedSettings = localStorage.getItem('userTableColumns');
        if (savedSettings) {
            const hiddenColumns = JSON.parse(savedSettings);
            document.querySelectorAll('.column-checkbox').forEach(checkbox => {
                if (hiddenColumns.includes(checkbox.value)) { checkbox.checked = false; applyVisibility(checkbox.value, false); }
            });
        }
    });

    document.querySelectorAll('.column-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            applyVisibility(this.value, this.checked);
            const hidden = Array.from(document.querySelectorAll('.column-checkbox:not(:checked)')).map(c => c.value);
            localStorage.setItem('userTableColumns', JSON.stringify(hidden));
        });
    });

    function applyVisibility(colClass, isVisible) {
        document.querySelectorAll('.' + colClass).forEach(el => el.style.display = isVisible ? '' : 'none');
    }

    function resetAll() { localStorage.removeItem('userTableColumns'); window.location.href = "{{ route('super-admin.user.index') }}"; }
    function toggleSelectAll(checked) { document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = checked); }

    function printSelectedUsers() {
        const selected = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) { alert('Please select at least one user to print.'); return; }
        const form = document.createElement('form');
        form.method = 'POST'; form.action = "{{ route('super-admin.user.print-selected') }}"; form.target = '_blank';
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden'; csrfInput.name = '_token'; csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrfInput);
        selected.forEach(id => { const input = document.createElement('input'); input.type = 'hidden'; input.name = 'selected_users[]'; input.value = id; form.appendChild(input); });
        document.body.appendChild(form); form.submit(); document.body.removeChild(form);
    }
</script>
@endsection