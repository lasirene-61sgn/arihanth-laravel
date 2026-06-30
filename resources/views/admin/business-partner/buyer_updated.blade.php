@extends('admin.layouts.app')

@section('title', 'Buyer Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Toolbar & Title -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2 text-dark">Buyer Management</h1>
                
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.business-partner.buyer', array_merge(request()->all(), ['export' => 'excel'])) }}" 
                       class="btn btn-success">
                        <i class="bi bi-file-earmark-excel me-2"></i> Export
                    </a>
                    
                    <button type="button" onclick="printCurrentTab();" 
                            class="btn btn-info text-white">
                        <i class="bi bi-printer me-2"></i> Print All
                    </button>

                    <button type="button" onclick="printSelectedBuyers();" 
                            class="btn btn-purple" style="background-color: #6f42c1; color: white;">
                        <i class="bi bi-check-all me-2"></i> Print Selected
                    </button>
                    
                    <button type="button" onclick="toggleFilters();" 
                            class="btn btn-outline-secondary">
                        <i class="bi bi-funnel me-2"></i> Filter
                    </button>
                    
                    <a href="{{ route('admin.business-partner.buyer.create') }}" 
                       class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i> Add Buyer
                    </a>
                </div>
            </div>

            <!-- Filter Section (Collapsible Funnel) -->
            <div id="filterSection" class="d-none mb-4 bg-white rounded shadow-sm border">
                <div class="p-3 bg-light border-bottom">
                    <div class="d-flex align-items-center gap-2 text-dark fw-semibold">
                        <i class="bi bi-funnel-fill text-primary"></i>
                        <span>Advanced Filters</span>
                    </div>
                </div>
                
                <div class="p-4">
                    <form method="GET" action="{{ route('admin.business-partner.buyer') }}" id="filterForm">
                        <div class="row g-3">
                            <!-- Search -->
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Search</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" value="{{ request('search') }}" 
                                           class="form-control" 
                                           placeholder="Search all fields...">
                                </div>
                            </div>
                            
                            <!-- BP Code -->
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">BP Code</label>
                                <input type="text" name="bp_code" value="{{ request('bp_code') }}" 
                                       class="form-control" 
                                       placeholder="e.g. BP001">
                            </div>
                            
                            <!-- Business Name -->
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Business Name</label>
                                <input type="text" name="business_name" value="{{ request('business_name') }}" 
                                       class="form-control" 
                                       placeholder="Company Name">
                            </div>
                            
                            <!-- City -->
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">City</label>
                                <select name="city" class="form-select">
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
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">State</label>
                                <select name="state" class="form-select">
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
                            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                                <a href="{{ route('admin.business-partner.buyer') }}" 
                                   class="btn btn-outline-secondary">
                                    Reset
                                </a>
                                <button type="submit" 
                                        class="btn btn-primary">
                                    <i class="bi bi-funnel-fill me-2"></i> Apply Filters
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
                                        <a href="{{ route('admin.business-partner.buyer', array_merge(request()->query(), ['sort_by' => 'bp_code', 'sort_order' => request('sort_by') == 'bp_code' && request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                            BP Code
                                            @if(request('sort_by') == 'bp_code')
                                                <i class="bi bi-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('admin.business-partner.buyer', array_merge(request()->query(), ['sort_by' => 'business_name', 'sort_order' => request('sort_by') == 'business_name' && request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                            Business Name
                                            @if(request('sort_by') == 'business_name')
                                                <i class="bi bi-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('admin.business-partner.buyer', array_merge(request()->query(), ['sort_by' => 'name', 'sort_order' => request('sort_by') == 'name' && request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                            Contact Person
                                            @if(request('sort_by') == 'name')
                                                <i class="bi bi-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('admin.business-partner.buyer', array_merge(request()->query(), ['sort_by' => 'mobile', 'sort_order' => request('sort_by') == 'mobile' && request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                            Mobile
                                            @if(request('sort_by') == 'mobile')
                                                <i class="bi bi-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('admin.business-partner.buyer', array_merge(request()->query(), ['sort_by' => 'email', 'sort_order' => request('sort_by') == 'email' && request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                            Email
                                            @if(request('sort_by') == 'email')
                                                <i class="bi bi-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('admin.business-partner.buyer', array_merge(request()->query(), ['sort_by' => 'city', 'sort_order' => request('sort_by') == 'city' && request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
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
                                                <a href="{{ route('admin.business-partner.buyer.show', $buyer) }}" 
                                                   class="btn btn-sm btn-outline-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.business-partner.buyer.edit', $buyer) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('admin.business-partner.buyer.destroy', $buyer) }}" 
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
                                        <td colspan="8" class="text-center">No buyers found.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle filter section visibility
    function toggleFilters() {
        const filterSection = document.getElementById('filterSection');
        filterSection.classList.toggle('d-none');
    }
    
    // Print current tab data
    function printCurrentTab() {
        const printWindow = window.open('', '_blank');
        const table = document.querySelector('.table');
        const filterSection = document.querySelector('#filterSection').cloneNode(true);
        
        // Ensure filters are visible in print
        filterSection.classList.remove('d-none');
        
        printWindow.document.write(
            '<html>' +
                '<head>' +
                    '<title>Buyer Management - Print</title>' +
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
        form.action = "{{ route('admin.business-partner.buyer.print-selected') }}";
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