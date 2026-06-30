@extends('key-user.layouts.app')

@section('title', 'Users')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
            <p class="text-sm text-gray-500">Manage system users, contact details, and account status</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('key-user.user.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors shadow-sm">
                <i class="bi bi-file-earmark-excel mr-2"></i> Export Excel
            </a>
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-cyan-600 rounded-lg hover:bg-cyan-700 transition-colors shadow-sm">
                <i class="bi bi-printer mr-2"></i> Print
            </button>
            <a href="{{ route('key-user.user.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                <i class="bi bi-plus-circle mr-2"></i> Create User
            </a>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
            
            <form action="{{ route('key-user.user.index') }}" method="GET" class="relative flex-1 max-w-md">
                <input type="text" name="search" value="{{ request('search') }}" 
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all text-sm" 
                       placeholder="Search name or email...">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="bi bi-search text-gray-400"></i>
                </div>
                <button type="submit" class="hidden">Search</button>
            </form>

            <div class="flex flex-wrap items-center gap-3 lg:ml-auto">
                
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm">
                        <i class="bi bi-funnel mr-2"></i> Filters
                    </button>
                    
                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute right-0 mt-2 w-80 md:w-96 bg-white rounded-xl shadow-xl border border-gray-200 z-50 p-6">
                        <form action="{{ route('key-user.user.index') }}" method="GET" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">User Code</label>
                                <input type="text" name="filter_user_code" class="w-full p-2 border border-gray-300 rounded-md text-sm" value="{{ request('filter_user_code') }}" placeholder="Ex: USR001">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Mobile</label>
                                    <input type="text" name="filter_mobile" class="w-full p-2 border border-gray-300 rounded-md text-sm" value="{{ request('filter_mobile') }}" placeholder="Mobile No">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Status</label>
                                    <select name="filter_status" class="w-full p-2 border border-gray-300 rounded-md text-sm">
                                        <option value="">All Status</option>
                                        <option value="1" {{ request('filter_status') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ request('filter_status') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex gap-2 pt-4 border-t">
                                <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">Apply Filters</button>
                                <a href="{{ route('key-user.user.index') }}" class="flex-1 bg-gray-100 text-gray-700 text-center py-2 rounded-lg text-sm font-semibold hover:bg-gray-200 transition border">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm">
                        <i class="bi bi-sort-down mr-2"></i> Sort
                    </button>
                    
                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-200 z-50 p-4">
                        <form action="{{ route('key-user.user.index') }}" method="GET" class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Sort By</label>
                                <select name="sort" class="w-full p-2 border border-gray-300 rounded-md text-sm">
                                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Newest Member</option>
                                    <option value="full_name" {{ request('sort') == 'full_name' ? 'selected' : '' }}>Name A-Z</option>
                                    <option value="user_code" {{ request('sort') == 'user_code' ? 'selected' : '' }}>User Code</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-gray-800 text-white py-2 rounded-lg text-sm font-semibold hover:bg-black transition">Sort</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">User Code</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Name & Contact</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Created At</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-bold text-indigo-600 font-mono">{{ $user->user_code }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-gray-800">{{ $user->full_name }}</div>
                            <div class="flex flex-col mt-1 space-y-1">
                                <span class="text-xs text-gray-500 flex items-center"><i class="bi bi-envelope mr-1"></i> {{ $user->email }}</span>
                                <span class="text-xs text-gray-500 flex items-center"><i class="bi bi-phone mr-1"></i> {{ $user->mobile_no }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->status)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 border border-red-200">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->created_at->format('d M, Y') }}
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <div class="inline-flex rounded-md shadow-sm" role="group">
                                <a href="{{ route('key-user.user.show', $user) }}" class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-white border border-gray-200 rounded-l-lg hover:bg-blue-50 transition-colors" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('key-user.user.edit', $user) }}" class="px-3 py-1.5 text-xs font-medium text-indigo-600 bg-white border-t border-b border-gray-200 hover:bg-indigo-50 transition-colors" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('key-user.user.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Delete this user?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-red-600 bg-white border border-l-0 border-gray-200 rounded-r-lg hover:bg-red-50 transition-colors" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 bg-gray-50">
                            <i class="bi bi-people text-4xl block mb-2 opacity-20"></i>
                            No users found in the system.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

<style>
    @media print {
        .no-print, aside, header, form, .btn, .pagination { display: none !important; }
        body { background: white !important; }
        .bg-white { border: none !important; box-shadow: none !important; }
        table { border: 1px solid #eee !important; width: 100% !important; }
    }
</style>
@endsection