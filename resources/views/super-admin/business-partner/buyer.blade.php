@extends('super-admin.layouts.app')

@section('title', 'Buyer Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Toolbar & Title -->
            <div class="tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-items-center tw-mb-6 tw-gap-4">
                <h1 class="tw-text-2xl tw-font-bold tw-text-gray-800">Buyer Management</h1>

                <div class="tw-flex tw-flex-wrap tw-gap-2">
                    <a href="{{ route('super-admin.business-partner.buyer', array_merge(request()->all(), ['export' => 'excel'])) }}"
                        class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-green-600 hover:tw-bg-green-700 tw-text-white tw-text-sm tw-font-medium tw-rounded-lg tw-transition-colors">
                        <i class="bi bi-file-earmark-excel tw-mr-2"></i> Export
                    </a>

                    <!-- <button type="button" onclick="printCurrentTab();" 
                            class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-cyan-500 hover:tw-bg-cyan-600 tw-text-white tw-text-sm tw-font-medium tw-rounded-lg tw-transition-colors">
                        <i class="bi bi-printer tw-mr-2"></i> Print All
                    </button> -->

                    <button type="button" onclick="printSelectedBuyers();"
                        class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-purple-600 hover:tw-bg-purple-700 tw-text-white tw-text-sm tw-font-medium tw-rounded-lg tw-transition-colors">
                        <i class="bi bi-check-all tw-mr-2"></i> Print
                    </button>

                    <button type="button" onclick="toggleFilters();"
                        class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-gray-300 hover:tw-bg-gray-50 tw-text-gray-700 tw-text-sm tw-font-medium tw-rounded-lg tw-transition-colors">
                        <i class="bi bi-funnel tw-mr-2"></i> Filter
                    </button>

                    <a href="{{ route('super-admin.business-partner.buyer.create') }}"
                        class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-blue-600 hover:tw-bg-blue-700 tw-text-white tw-text-sm tw-font-medium tw-rounded-lg tw-transition-colors">
                        <i class="bi bi-plus-lg tw-mr-2"></i> Add Buyer
                    </a>
                </div>
            </div>

            <!-- Filter Section (Collapsible Funnel) -->
            <div id="filterSection" class="tw-hidden tw-mb-6 tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-100 tw-overflow-hidden tw-transition-all duration-300">
                <div class="tw-p-5 tw-bg-gray-50/50 tw-border-b tw-border-gray-100">
                    <div class="tw-flex tw-items-center tw-gap-2 tw-text-gray-800 tw-font-semibold">
                        <i class="bi bi-funnel-fill tw-text-blue-500"></i>
                        <span>Advanced Filters</span>
                    </div>
                </div>

                <div class="tw-p-5" data-print-selected-route="{{ route('super-admin.business-partner.buyer.print-selected') }}" data-print-all-route="{{ route('super-admin.business-partner.buyer.print-all') }}">
                    <form method="GET" action="{{ route('super-admin.business-partner.buyer') }}" id="filterForm">
                        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-12 tw-gap-4">
                            <!-- Search -->
                            <div class="md:tw-col-span-3">
                                <label class="tw-block tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-mb-1">Search</label>
                                <div class="tw-relative">
                                    <span class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-text-gray-400">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" name="search" value="{{ request('search') }}"
                                        class="tw-w-full tw-pl-10 tw-pr-3 tw-py-2 tw-bg-white tw-border tw-border-gray-200 tw-rounded-lg tw-text-sm focus:tw-outline-none focus:tw-border-blue-500 focus:tw-ring-1 focus:tw-ring-blue-500"
                                        placeholder="Search all fields...">
                                </div>
                            </div>

                            <!-- BP Code -->
                            <div class="md:tw-col-span-2">
                                <label class="tw-block tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-mb-1">BP Code</label>
                                <input type="text" name="bp_code" value="{{ request('bp_code') }}"
                                    class="tw-w-full tw-px-3 tw-py-2 tw-bg-white tw-border tw-border-gray-200 tw-rounded-lg tw-text-sm focus:tw-outline-none focus:tw-border-blue-500 focus:tw-ring-1 focus:tw-ring-blue-500"
                                    placeholder="e.g. BP001">
                            </div>

                            <!-- Business Name -->
                            <div class="md:tw-col-span-3">
                                <label class="tw-block tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-mb-1">Business Name</label>
                                <input type="text" name="business_name" value="{{ request('business_name') }}"
                                    class="tw-w-full tw-px-3 tw-py-2 tw-bg-white tw-border tw-border-gray-200 tw-rounded-lg tw-text-sm focus:tw-outline-none focus:tw-border-blue-500 focus:tw-ring-1 focus:tw-ring-blue-500"
                                    placeholder="Company Name">
                            </div>

                            <!-- City -->
                            <div class="md:tw-col-span-2">
                                <label class="tw-block tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-mb-1">City</label>
                                <select name="city" class="tw-w-full tw-px-3 tw-py-2 tw-bg-white tw-border tw-border-gray-200 tw-rounded-lg tw-text-sm focus:tw-outline-none focus:tw-border-blue-500 focus:tw-ring-1 focus:tw-ring-blue-500">
                                    <option value="">All Cities</option>
                                    @php
                                    $cities = \App\Models\Buyer::select('city')->distinct()->whereNotNull('city')->pluck('city');
                                    @endphp
                                    @foreach($cities as $city)
                                    <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- State -->
                            <div class="md:tw-col-span-2">
                                <label class="tw-block tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-mb-1">State</label>
                                <select name="state" class="tw-w-full tw-px-3 tw-py-2 tw-bg-white tw-border tw-border-gray-200 tw-rounded-lg tw-text-sm focus:tw-outline-none focus:tw-border-blue-500 focus:tw-ring-1 focus:tw-ring-blue-500">
                                    <option value="">All States</option>
                                    @php
                                    $states = \App\Models\Buyer::select('state')->distinct()->whereNotNull('state')->pluck('state');
                                    @endphp
                                    @foreach($states as $state)
                                    <option value="{{ $state }}" {{ request('state') == $state ? 'selected' : '' }}>{{ $state }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter Actions -->
                            <div class="md:tw-col-span-12 tw-flex tw-justify-end tw-gap-2 tw-mt-2">
                                <a href="{{ route('super-admin.business-partner.buyer') }}"
                                    class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-text-gray-600 hover:tw-text-gray-800 tw-text-sm tw-font-medium tw-transition-colors">
                                    Reset
                                </a>
                                <button type="submit"
                                    class="tw-inline-flex tw-items-center tw-px-6 tw-py-2 tw-bg-blue-600 hover:tw-bg-blue-700 tw-text-white tw-text-sm tw-font-bold tw-rounded-lg tw-shadow-sm tw-transition-all transform hover:tw-translate-y-[-1px]">
                                    <i class="bi bi-funnel-fill tw-mr-2"></i> Apply Filters
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Buyer List</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this.checked)">
                                    </th>
                                    <th>
                                        <a href="{{ route('super-admin.business-partner.buyer', array_merge(request()->query(), ['sort_by' => 'bp_code', 'sort_order' => request('sort_by') == 'bp_code' && request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                            BP Code
                                            @if(request('sort_by') == 'bp_code')
                                            <i class="bi bi-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('super-admin.business-partner.buyer', array_merge(request()->query(), ['sort_by' => 'business_name', 'sort_order' => request('sort_by') == 'business_name' && request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                            Business Name
                                            @if(request('sort_by') == 'business_name')
                                            <i class="bi bi-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('super-admin.business-partner.buyer', array_merge(request()->query(), ['sort_by' => 'name', 'sort_order' => request('sort_by') == 'name' && request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                            Contact Person
                                            @if(request('sort_by') == 'name')
                                            <i class="bi bi-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('super-admin.business-partner.buyer', array_merge(request()->query(), ['sort_by' => 'mobile', 'sort_order' => request('sort_by') == 'mobile' && request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                            Mobile
                                            @if(request('sort_by') == 'mobile')
                                            <i class="bi bi-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('super-admin.business-partner.buyer', array_merge(request()->query(), ['sort_by' => 'email', 'sort_order' => request('sort_by') == 'email' && request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                            Email
                                            @if(request('sort_by') == 'email')
                                            <i class="bi bi-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('super-admin.business-partner.buyer', array_merge(request()->query(), ['sort_by' => 'city', 'sort_order' => request('sort_by') == 'city' && request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                            City
                                            @if(request('sort_by') == 'city')
                                            <i class="bi bi-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($buyers) && $buyers->count() > 0)
                                @foreach($buyers as $buyer)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="row-checkbox" value="{{ $buyer->id }}" name="selected_buyers[]">
                                    </td>
                                    <td>{{ $buyer->bp_code }}</td>
                                    <td>{{ $buyer->business_name }}</td>
                                    <td>{{ $buyer->name }}</td>
                                    <td>{{ $buyer->mobile }}</td>
                                    <td>{{ $buyer->email }}</td>
                                    <td>{{ $buyer->city ?? 'N/A' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('super-admin.chat.start', ['receiver_id' => $buyer->id, 'type' => 'buyer']) }}"
                                                class="btn btn-sm btn-outline-success" title="Chat">
                                                <i class="bi bi-chat-dots"></i>
                                            </a>
                                            <a href="{{ route('super-admin.business-partner.buyer.show', $buyer) }}"
                                                class="btn btn-sm btn-outline-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('super-admin.business-partner.buyer.edit', $buyer) }}"
                                                class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <!-- <button type="button" class="btn btn-sm btn-outline-success share-btn" title="Share" 
                                                        data-bp-code="{{ $buyer->bp_code }}"
                                                        data-business-name="{{ addslashes(str_replace('"', '&quot;', $buyer->business_name ?? '')) }}"
                                                        data-name="{{ addslashes(str_replace('"', '&quot;', $buyer->name ?? '')) }}"
                                                        data-mobile="{{ $buyer->mobile }}"
                                                        data-email="{{ $buyer->email }}"
                                                        data-city="{{ $buyer->city ?? 'N/A' }}"
                                                        data-state="{{ $buyer->state ?? 'N/A' }}"
                                                        data-created-at="{{ $buyer->created_at ?? 'N/A' }}">
                                                    <i class="bi bi-share"></i>
                                                </button> -->
                                            <form action="{{ route('super-admin.business-partner.buyer.destroy', $buyer) }}"
                                                method="POST"
                                                style="display: inline-block;"
                                                onsubmit="return confirm('Are you sure you want to delete this buyer?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="7" class="text-center">No buyers found.</td>
                                </tr>
                                @endif
                            </tbody>
                    </div>
                    <div class="mt-4 tw-px-4">
                        {{ $buyers->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shareModalLabel">Share Buyer Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Buyer Details:</label>
                    <textarea id="shareText" class="form-control" rows="5" readonly></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Share via:</label>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" onclick="copyToClipboard()">
                            <i class="bi bi-copy"></i> Copy to Clipboard
                        </button>
                        <button class="btn btn-primary" onclick="shareViaEmail()">
                            <i class="bi bi-envelope"></i> Share via Email
                        </button>
                        <button class="btn btn-info" onclick="shareViaWhatsApp()">
                            <i class="bi bi-whatsapp"></i> Share via WhatsApp
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle filter section visibility
    function toggleFilters() {
        const filterSection = document.getElementById('filterSection');
        filterSection.classList.toggle('tw-hidden');
    }

    // Print current tab data
    function printCurrentTab() {
        const printWindow = window.open('', '_blank');
        const table = document.querySelector('.table');
        const filterSection = document.querySelector('#filterSection').cloneNode(true);

        // Ensure filters are visible in print
        filterSection.classList.remove('tw-hidden');

        // Hide the filter toggle button in print if it exists inside (it doesn't in new layout, but kept for safety)
        const filterToggleBtn = filterSection.querySelector('button[onclick="toggleFilters()"]');
        if (filterToggleBtn) {
            filterToggleBtn.style.display = 'none';
        }

        printWindow.document.write(
            '<html>' +
            '<head>' +
            '<title>Buyer Management - Print</title>' +
            '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">' +
            '<script src="https://cdn.tailwindcss.com"><\/script>' +
            '<script>' +
            'tailwind.config = {' +
            'prefix: "tw-",' +
            'important: true,' +
            '}' +
            '<\/script>' +
            '<style>' +
            'body { padding: 20px; }' +
            '.no-print { display: none; }' +
            'table { width: 100%; border-collapse: collapse; }' +
            'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }' +
            'th { background-color: #f2f2f2; }' +
            '</style>' +
            '</head>' +
            '<body>' +
            '<h2>Buyer Management Report</h2>' +
            filterSection.outerHTML +
            table.outerHTML +
            '<script>' +
            'window.onload = function() {' +
            'window.print();' +
            'window.close();' +
            '}' +
            '<' + '/script>' +
            '</body>' +
            '</html>'
        );
        printWindow.document.close();
    }

    // Attach event listeners to share buttons
    document.addEventListener('DOMContentLoaded', function() {
        const shareButtons = document.querySelectorAll('.share-btn');
        shareButtons.forEach(button => {
            button.addEventListener('click', function() {
                const bpCode = this.getAttribute('data-bp-code');
                const businessName = this.getAttribute('data-business-name');
                const name = this.getAttribute('data-name');
                const mobile = this.getAttribute('data-mobile');
                const email = this.getAttribute('data-email');
                const city = this.getAttribute('data-city');
                const state = this.getAttribute('data-state');
                const createdAt = this.getAttribute('data-created-at');

                // Format buyer details for sharing
                const shareText = `Buyer Information:\n\n` +
                    `BP Code: ${bpCode}\n` +
                    `Business Name: ${businessName}\n` +
                    `Contact Person: ${name}\n` +
                    `Mobile: ${mobile}\n` +
                    `Email: ${email}\n` +
                    `City: ${city}\n` +
                    `State: ${state}\n` +
                    `Created: ${createdAt}\n`;

                document.getElementById('shareText').value = shareText;

                // Show modal
                var modal = new bootstrap.Modal(document.getElementById('shareModal'));
                modal.show();
            });
        });
    });

    function copyToClipboard() {
        const textarea = document.getElementById('shareText');
        textarea.select();
        document.execCommand('copy');

        // Show success feedback
        const originalText = document.querySelector('#shareModal .btn-success').innerHTML;
        document.querySelector('#shareModal .btn-success').innerHTML = '<i class="bi bi-check"></i> Copied!';
        setTimeout(() => {
            document.querySelector('#shareModal .btn-success').innerHTML = originalText;
        }, 2000);
    }

    function shareViaEmail() {
        const shareText = document.getElementById('shareText').value;
        const subject = encodeURIComponent('Buyer Information');
        const body = encodeURIComponent(shareText);

        window.open(`mailto:?subject=${subject}&body=${body}`);
    }

    function shareViaWhatsApp() {
        const shareText = document.getElementById('shareText').value;
        const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(shareText)}`;
        window.open(whatsappUrl);
    }

    function toggleSelectAll(checked) {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = checked;
        });
    }

    function printSelectedBuyers() {
        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);

        if (selectedIds.length === 0) {
            alert('Please select at least one buyer to print.');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('super-admin.business-partner.buyer.print-selected') }}";
        form.target = '_blank';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = "{{ csrf_token() }}";
        form.appendChild(csrfInput);

        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_buyers[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
</script>
@endsection