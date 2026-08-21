@extends('admin.layouts.app')

@section('title', 'Craftsman Management')

@section('content')
<div class="p-4 md:p-6 lg:p-8">
    <!-- Toolbar & Title -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Craftsman Management</h1>
            <p class="text-gray-500 text-sm mt-1">Manage, filter and export your artisan network.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.business-partner.craftman.export', request()->all()) }}" 
               class="flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white text-sm font-bold rounded-xl hover:bg-green-700 transition-all shadow-sm shadow-green-100">
                <i class="bi bi-file-earmark-excel"></i>
                <span>Export</span>
            </a>
            
            <button type="button" onclick="printSelectedCraftsmen();" 
                    class="flex items-center gap-2 px-4 py-2.5 bg-magenta-600 text-white text-sm font-bold rounded-xl hover:bg-magenta-700 transition-all shadow-sm shadow-magenta-100">
                <i class="bi bi-check-all"></i>
                <span>Print Selected</span>
            </button>
            
            <button type="button" onclick="toggleFilters();" 
                    class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-600 text-sm font-bold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
                <i class="bi bi-funnel"></i>
                <span>Filter</span>
            </button>
            
            <a href="{{ route('admin.business-partner.craftman.create') }}" 
               class="flex items-center gap-2 px-4 py-2.5 bg-magenta-600 text-white text-sm font-bold rounded-xl hover:bg-magenta-700 transition-all shadow-lg shadow-magenta-100">
                <i class="bi bi-plus-lg"></i>
                <span>Add Craftsman</span>
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div id="filterSection" class="hidden mb-8 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden animate-in fade-in slide-in-from-top-4 duration-300">
        <div class="p-4 border-b border-gray-50 bg-gray-50/50 flex items-center gap-3">
            <div class="w-8 h-8 bg-magenta-100 rounded-lg flex items-center justify-center text-magenta-600">
                <i class="bi bi-funnel-fill"></i>
            </div>
            <span class="font-bold text-gray-900">Advanced Filters</span>
        </div>
        
        <div class="p-6">
            <form method="GET" action="{{ route('admin.business-partner.craftman') }}" id="filterForm">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Search -->
                    <div class="lg:col-span-1">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Search</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 text-sm transition-all shadow-sm"
                                   placeholder="Search all fields...">
                        </div>
                    </div>
                    
                    <!-- Craftman Code -->
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Code</label>
                        <input type="text" name="craftman_code" value="{{ request('craftman_code') }}" 
                               class="block w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 text-sm transition-all shadow-sm"
                               placeholder="e.g. CR001">
                    </div>
                    
                    <!-- Business Name -->
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Business Name</label>
                        <input type="text" name="business_name" value="{{ request('business_name') }}" 
                               class="block w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 text-sm transition-all shadow-sm"
                               placeholder="Company Name">
                    </div>
                    
                    <!-- Contact Person -->
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Contact Person</label>
                        <input type="text" name="name" value="{{ request('name') }}" 
                               class="block w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 text-sm transition-all shadow-sm"
                               placeholder="Name">
                    </div>
                    
                    <!-- City -->
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">City</label>
                        <select name="city" class="block w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 text-sm transition-all shadow-sm">
                            <option value="">All Cities</option>
                            @php
                                $cities = \App\Models\Craftman::select('city')->distinct()->whereNotNull('city')->pluck('city');
                            @endphp
                            @foreach($cities as $city)
                                <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <a href="{{ route('admin.business-partner.craftman') }}" 
                       class="px-5 py-2 text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">
                        Reset
                    </a>
                    <button type="submit" 
                            class="px-5 py-2 bg-magenta-600 text-white text-sm font-bold rounded-lg hover:bg-magenta-700 transition-all shadow-sm shadow-magenta-100">
                        <i class="bi bi-funnel-fill me-2"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
            <h4 class="font-bold text-gray-900">Craftsman List</h4>
            <div class="flex items-center gap-2 text-xs font-bold text-gray-400">
                <i class="bi bi-info-circle"></i>
                <span>Showing {{ $craftmen->count() }} results</span>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[0.65rem] uppercase tracking-widest text-gray-400 border-b border-gray-50 bg-white">
                        <th class="px-6 py-4 font-bold w-12 text-center">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this.checked)" 
                                class="w-4 h-4 text-magenta-600 border-gray-300 rounded focus:ring-magenta-500">
                        </th>
                        <th class="px-6 py-4 font-bold">
                            <a href="{{ route('admin.business-partner.craftman', array_merge(request()->query(), ['sort' => 'craftman_code', 'direction' => request('sort') == 'craftman_code' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                               class="flex items-center gap-2 hover:text-magenta-600 transition-colors">
                                Code
                                @if(request('sort') == 'craftman_code')
                                    <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-magenta-600"></i>
                                @else
                                    <i class="bi bi-arrow-up-down opacity-30 text-[0.6rem]"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4 font-bold">
                            <a href="{{ route('admin.business-partner.craftman', array_merge(request()->query(), ['sort' => 'business_name', 'direction' => request('sort') == 'business_name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                               class="flex items-center gap-2 hover:text-magenta-600 transition-colors">
                                Business Name
                                @if(request('sort') == 'business_name')
                                    <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-magenta-600"></i>
                                @else
                                    <i class="bi bi-arrow-up-down opacity-30 text-[0.6rem]"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4 font-bold">
                            <a href="{{ route('admin.business-partner.craftman', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                               class="flex items-center gap-2 hover:text-magenta-600 transition-colors">
                                Contact Person
                                @if(request('sort') == 'name')
                                    <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-magenta-600"></i>
                                @else
                                    <i class="bi bi-arrow-up-down opacity-30 text-[0.6rem]"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4 font-bold">Mobile</th>
                        <th class="px-6 py-4 font-bold">Email</th>
                        <th class="px-6 py-4 font-bold">City</th>
                        <th class="px-6 py-4 font-bold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm">
                    @if(isset($craftmen) && $craftmen->count() > 0)
                        @foreach($craftmen as $craftman)
                        <tr class="hover:bg-magenta-50/30 transition-colors duration-150 group">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" class="row-checkbox w-4 h-4 text-magenta-600 border-gray-300 rounded focus:ring-magenta-500" value="{{ $craftman->id }}" name="selected_craftsmen[]">
                            </td>
                            <td class="px-6 py-4 font-bold text-magenta-600">{{ $craftman->craftman_code }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $craftman->business_name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $craftman->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $craftman->mobile }}</td>
                            <td class="px-6 py-4 text-gray-500">
                                <a href="mailto:{{ $craftman->email }}" class="hover:text-magenta-600 transition-colors">{{ $craftman->email }}</a>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-[0.7rem] font-bold">{{ $craftman->city ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5 transition-opacity duration-200">
                                    <a href="{{ route('admin.chat.start', ['receiverId' => $craftman->id, 'type' => 'craftsman']) }}"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-green-100 text-green-700 hover:bg-green-600 hover:text-white transition-all duration-200" title="Chat">
                                        <i class="bi bi-chat-dots"></i>
                                    </a>
                                    <a href="{{ route('admin.business-partner.craftman.show', $craftman) }}"
                                       class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-600 hover:text-white transition-all duration-200" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.business-partner.craftman.edit', $craftman) }}" 
                                       class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-600 hover:text-white transition-all duration-200" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.business-partner.craftman.destroy', $craftman) }}" 
                                          method="POST" 
                                          class="inline-block"
                                          onsubmit="return confirm('Are you sure you want to delete this craftsman?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-100 text-red-700 hover:bg-red-600 hover:text-white transition-all duration-200" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="bi bi-inbox text-4xl text-gray-200 mb-2"></i>
                                    <p class="text-gray-400 font-medium font-bold">No craftmen found.</p>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="tw-mt-4">
    {{ $craftmen->links() }}
</div>
    </div>
</div>

<script>
    // Toggle filter section visibility
    function toggleFilters() {
        const filterSection = document.getElementById('filterSection');
        filterSection.classList.toggle('hidden');
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
        form.action = "{{ route('admin.business-partner.craftman.print-selected') }}";
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