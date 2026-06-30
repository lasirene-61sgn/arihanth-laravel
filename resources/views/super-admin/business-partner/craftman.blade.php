@extends('super-admin.layouts.app')

@section('title', __('messages.craftsman_management'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">{{ __('messages.craftsman_management') }}</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2" role="group">
                        <a href="{{ route('super-admin.business-partner.craftman', array_merge(request()->all(), ['export' => 'excel'])) }}" class="btn btn-success">
                            <i class="bi bi-file-earmark-excel"></i> {{ __('messages.export') }}
                        </a>
                    </div>
                    <!-- <div class="btn-group me-2" role="group">
                        <button type="button" class="btn btn-info" onclick="printCurrentTab();">
                            <i class="bi bi-printer"></i> {{ __('messages.print_all') }}
                        </button>
                    </div> -->
                    <div class="btn-group me-2" role="group">
                        <button type="button" class="btn btn-purple" style="background-color: #6f42c1; color: white;" onclick="printSelectedCraftsmen();">
                            <i class="bi bi-check-all"></i> {{ __('messages.print') }}
                        </button>
                    </div>
                    <div class="btn-group me-2" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="toggleFilters();">
                            <i class="bi bi-funnel"></i> {{ __('messages.filter') }}
                        </button>
                    </div>
                    <a href="{{ route('super-admin.business-partner.craftman.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> {{ __('messages.add_craftsman') }}
                    </a>
                </div>
            </div>
            
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Search and Filter Section -->
            <div class="card mb-3" style="display: none;">
                <div class="card-body">
                    <form method="GET" action="{{ route('super-admin.business-partner.craftman') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search all fields..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="craftman_code" class="form-control" placeholder="Craftman Code" value="{{ request('craftman_code') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="business_name" class="form-control" placeholder="Business Name" value="{{ request('business_name') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="city" class="form-control">
                                    <option value="">{{ __('messages.all_cities') }}</option>
                                    @php
                                        $cities = \App\Models\Craftman::select('city')->distinct()->whereNotNull('city')->pluck('city');
                                    @endphp
                                    @foreach($cities as $city)
                                        <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="state" class="form-control">
                                    <option value="">{{ __('messages.all_states') }}</option>
                                    @php
                                        $states = \App\Models\Craftman::select('state')->distinct()->whereNotNull('state')->pluck('state');
                                    @endphp
                                    @foreach($states as $state)
                                        <option value="{{ $state }}" {{ request('state') == $state ? 'selected' : '' }}>{{ $state }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">{{ __('messages.filter') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>{{ __('messages.craftsman_list') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this.checked)">
                                    </th>
                                    <th>{{ __('messages.craftman_code') }}</th>
                                    <th>{{ __('messages.business_name') }}</th>
                                    <th>{{ __('messages.contact_person') }}</th>
                                    <th>{{ __('messages.mobile') }}</th>
                                    <th>{{ __('messages.email') }}</th>
                                    <th>{{ __('messages.city') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($craftmen) && $craftmen->count() > 0)
                                    @foreach($craftmen as $craftman)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="row-checkbox" value="{{ $craftman->id }}" name="selected_craftsmen[]">
                                        </td>
                                        <td>{{ $craftman->craftman_code }}</td>
                                        <td>{{ $craftman->business_name }}</td>
                                        <td>{{ $craftman->name }}</td>
                                        <td>{{ $craftman->mobile }}</td>
                                        <td>{{ $craftman->email }}</td>
                                        <td>{{ $craftman->city ?? 'N/A' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('super-admin.chat.start', ['receiver_id' => $craftman->id, 'type' => 'craftsman']) }}"
                                                   class="btn btn-sm btn-outline-success" title="Chat">
                                                    <i class="bi bi-chat-dots"></i>
                                                </a>
                                                <a href="{{ route('super-admin.business-partner.craftman.show', $craftman) }}" 
                                                   class="btn btn-sm btn-outline-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('super-admin.business-partner.craftman.edit', $craftman) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('super-admin.business-partner.craftman.destroy', $craftman) }}" 
                                                      method="POST" 
                                                      style="display: inline-block;"
                                                      onsubmit="return confirm('Are you sure you want to delete this craftman?')">
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
                                        <td colspan="7" class="text-center">{{ __('messages.no_craftsmen_found') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                    </div>
                    <div class="mt-4">
                        {{ $craftmen->links('pagination::bootstrap-5') }}
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle filter section visibility
    function toggleFilters() {
        const filterCard = document.querySelector('.card.mb-3');
        filterCard.style.display = filterCard.style.display === 'none' ? 'block' : 'none';
    }
    
    // Print current tab data
    function printCurrentTab() {
        const printWindow = window.open('', '_blank');
        const table = document.querySelector('.table');
        const filterSection = document.querySelector('.card.mb-3').cloneNode(true);
        
        // Hide the filter toggle button in print
        const filterToggleBtn = filterSection.querySelector('button[onclick="toggleFilters()"]');
        if (filterToggleBtn) {
            filterToggleBtn.style.display = 'none';
        }
        
        printWindow.document.write(
            '<html>' +
                '<head>' +
                    '<title>Craftman Management - Print</title>' +
                    '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">' +
                    '<style>' +
                        'body { padding: 20px; }' +
                        '.no-print { display: none; }' +
                        'table { width: 100%; border-collapse: collapse; }' +
                        'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }' +
                        'th { background-color: #f2f2f2; }' +
                    '</style>' +
                '</head>' +
                '<body>' +
                    '<h2>Craftman Management Report</h2>' +
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

    function toggleSelectAll(checked) {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = checked;
        });
    }

    function printSelectedCraftsmen() {
        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        
        if (selectedIds.length === 0) {
            alert('Please select at least one craftsman to print.');
            return;
        }
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('super-admin.business-partner.craftman.print-selected') }}";
        form.target = '_blank';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = "{{ csrf_token() }}";
        form.appendChild(csrfInput);
        
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_craftsmen[]';
            input.value = id;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
</script>
@endsection