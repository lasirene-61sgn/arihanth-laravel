@extends('admin.layouts.app')

@section('title', 'Business Partner Management')

@section('content')
<div class="p-4 md:p-6 lg:p-8">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Business Partner Management</h1>
            <p class="text-gray-500 text-sm mt-1">Overview and management of buyers and craftsmen.</p>
        </div>
        
        <form action="{{ route('admin.business-partner.index') }}" method="GET" class="flex items-center gap-2">
            <div class="relative min-w-[300px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search by name or code..." 
                    class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 text-sm transition-all duration-200">
            </div>
            <button type="submit" class="p-2 bg-magenta-600 text-white rounded-lg hover:bg-magenta-700 transition-colors shadow-sm shadow-magenta-100">
                <i class="bi bi-search"></i>
            </button>
            @if(request('search'))
                <a href="{{ route('admin.business-partner.index') }}" class="p-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors" title="Clear Search">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 flex items-center justify-between rounded-r-lg shadow-sm">
            <div class="flex items-center gap-3">
                <i class="bi bi-check-circle-fill text-lg"></i>
                <p class="font-medium text-sm">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-green-500 hover:text-green-700 transition-colors">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    <!-- Quick Stats Grid -->
    <!-- <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
         Total Buyers 
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-magenta-50 rounded-xl flex items-center justify-center text-magenta-600 group-hover:bg-magenta-600 group-hover:text-white transition-all duration-300">
                    <i class="bi bi-people text-2xl"></i>
                </div>
                <span class="text-[0.65rem] font-bold uppercase tracking-widest text-gray-400">Total Buyers</span>
            </div>
            <h3 class="text-3xl font-extrabold text-gray-900">{{ $buyers->count() }}</h3>
            <div class="mt-2 flex items-center gap-2">
                <span class="text-xs text-magenta-600 font-semibold italic">Registered Buyers</span>
            </div>
        </div>

         Total Craftmen 
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                    <i class="bi bi-person-workspace text-2xl"></i>
                </div>
                <span class="text-[0.65rem] font-bold uppercase tracking-widest text-gray-400">Total Craftmen</span>
            </div>
            <h3 class="text-3xl font-extrabold text-gray-900">{{ $craftmen->count() }}</h3>
            <div class="mt-2 flex items-center gap-2">
                <span class="text-xs text-blue-600 font-semibold italic">Registered Artisans</span>
            </div>
        </div>

        Combined Total 
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                    <i class="bi bi-people-fill text-2xl"></i>
                </div>
                <span class="text-[0.65rem] font-bold uppercase tracking-widest text-gray-400">Total Partners</span>
            </div>
            <h3 class="text-3xl font-extrabold text-gray-900">{{ $buyers->count() + $craftmen->count() }}</h3>
            <div class="mt-2 flex items-center gap-2">
                <span class="text-xs text-indigo-600 font-semibold italic">Network Strength</span>
            </div>
        </div>
    </div> -->

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Buyers Section -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden min-h-[400px]">
            <div class="p-6 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-magenta-100 rounded-lg flex items-center justify-center text-magenta-600">
                        <i class="bi bi-people"></i>
                    </div>
                    <h4 class="font-bold text-gray-900">Recent Buyers</h4>
                </div>
                <span class="px-2.5 py-1 bg-magenta-600 text-white text-[0.65rem] font-bold rounded-full shadow-sm shadow-magenta-200">{{ $buyers->count() }}</span>
            </div>
            
            <div class="p-0">
                @if($buyers->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[0.65rem] uppercase tracking-widest text-gray-400 border-b border-gray-50">
                                    <th class="px-6 py-4 font-bold">BP Code</th>
                                    <th class="px-6 py-4 font-bold">Business Name</th>
                                    <th class="px-6 py-4 font-bold">Contact</th>
                                    <th class="px-6 py-4 font-bold text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-sm">
                                @foreach($buyers->take(5) as $buyer)
                                <tr class="hover:bg-gray-50 transition-colors duration-150 group">
                                    <td class="px-6 py-4 font-bold text-magenta-600">{{ $buyer->bp_code }}</td>
                                    <td class="px-6 py-4 text-gray-700 font-medium">{{ $buyer->business_name }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ $buyer->name }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.business-partner.buyer.show', $buyer) }}" 
                                               class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-600 hover:text-white transition-all duration-200" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.business-partner.buyer.edit', $buyer) }}" 
                                               class="w-8 h-8 flex items-center justify-center rounded-lg bg-magenta-100 text-magenta-700 hover:bg-magenta-600 hover:text-white transition-all duration-200" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($buyers->count() > 5)
                        <div class="p-6 border-t border-gray-50 bg-gray-50/20">
                            <a href="{{ route('admin.business-partner.buyer') }}" class="flex items-center justify-center gap-2 w-full py-2.5 bg-white border border-gray-200 rounded-xl text-magenta-600 text-sm font-bold hover:bg-magenta-50 hover:border-magenta-200 transition-all duration-200">
                                <span>View All Buyers</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    @endif
                @else
                    <div class="flex flex-col items-center justify-center py-16 px-6">
                        <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-300 mb-4">
                            <i class="bi bi-person-x text-3xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium mb-4">No buyers found in the system.</p>
                        <a href="{{ route('admin.business-partner.buyer.create') }}" class="px-6 py-2.5 bg-magenta-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-magenta-100 hover:bg-magenta-700 transition-all">
                            <i class="bi bi-plus-lg me-1"></i> Add New Buyer
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Craftmen Section -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden min-h-[400px]">
            <div class="p-6 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                        <i class="bi bi-person-workspace"></i>
                    </div>
                    <h4 class="font-bold text-gray-900">Recent Craftmen</h4>
                </div>
                <span class="px-2.5 py-1 bg-blue-600 text-white text-[0.65rem] font-bold rounded-full shadow-sm shadow-blue-200">{{ $craftmen->count() }}</span>
            </div>
            
            <div class="p-0">
                @if($craftmen->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[0.65rem] uppercase tracking-widest text-gray-400 border-b border-gray-50">
                                    <th class="px-6 py-4 font-bold">BP Code</th>
                                    <th class="px-6 py-4 font-bold">Business Name</th>
                                    <th class="px-6 py-4 font-bold">Contact</th>
                                    <th class="px-6 py-4 font-bold text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-sm">
                                @foreach($craftmen->take(5) as $craftman)
                                <tr class="hover:bg-gray-50 transition-colors duration-150 group">
                                    <td class="px-6 py-4 font-bold text-blue-600">{{ $craftman->craftman_code }}</td>
                                    <td class="px-6 py-4 text-gray-700 font-medium">{{ $craftman->business_name }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ $craftman->name }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.business-partner.craftman.show', $craftman) }}" 
                                               class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-600 hover:text-white transition-all duration-200" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.business-partner.craftman.edit', $craftman) }}" 
                                               class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-600 hover:text-white transition-all duration-200" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($craftmen->count() > 5)
                        <div class="p-6 border-t border-gray-50 bg-gray-50/20">
                            <a href="{{ route('admin.business-partner.craftman') }}" class="flex items-center justify-center gap-2 w-full py-2.5 bg-white border border-gray-200 rounded-xl text-blue-600 text-sm font-bold hover:bg-blue-50 hover:border-blue-200 transition-all duration-200">
                                <span>View All Craftmen</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    @endif
                @else
                    <div class="flex flex-col items-center justify-center py-16 px-6">
                        <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-300 mb-4">
                            <i class="bi bi-person-x text-3xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium mb-4">No craftmen found in the system.</p>
                        <a href="{{ route('admin.business-partner.craftman.create') }}" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all">
                            <i class="bi bi-plus-lg me-1"></i> Add New Craftsman
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
