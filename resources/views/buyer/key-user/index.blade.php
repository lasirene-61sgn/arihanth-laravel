@extends('buyer.layouts.app')

@section('title', 'Key Users')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Key User Management</h1>
            <p class="text-sm text-slate-500">Manage administrative access and user roles for your BP code.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('buyer.key-user-management.export', request()->query()) }}" 
               class="inline-flex items-center px-4 py-2 bg-green-50 text-green-700 border border-green-100 rounded-xl text-sm font-bold hover:bg-green-100 transition-all">
                <i class="bi bi-file-earmark-excel mr-2"></i> Export
            </a>
            <button onclick="window.print()" 
                    class="inline-flex items-center px-4 py-2 bg-cyan-50 text-cyan-700 border border-cyan-100 rounded-xl text-sm font-bold hover:bg-cyan-100 transition-all">
                <i class="bi bi-printer mr-2"></i> Print
            </button>
            <a href="{{ route('buyer.key-user-management.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-all shadow-md shadow-blue-100">
                <i class="bi bi-plus-circle mr-2"></i> Create Key User
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-visible">
        <div class="p-4 flex flex-wrap items-center gap-4">
            <form action="{{ route('buyer.key-user-management.index') }}" method="GET" class="flex-grow max-w-md relative group">
                <input type="text" name="search" 
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all" 
                       placeholder="Quick Search..." value="{{ request('search') }}">
                <div class="absolute left-3 top-3 text-slate-400 group-focus-within:text-blue-600 transition-colors">
                    <i class="bi bi-search"></i>
                </div>
            </form>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-all shadow-sm">
                    <i class="bi bi-funnel mr-2"></i> Advanced Filter
                </button>
                <div x-show="open" @click.away="open = false" 
                     class="absolute right-0 z-50 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 p-6" 
                     style="display: none;"
                     x-transition:enter="transition ease-out duration-100">
                    <form action="{{ route('buyer.key-user-management.index') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Full Name</label>
                                <input type="text" name="filter_name" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm" value="{{ request('filter_name') }}" placeholder="Name">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">User Code</label>
                                <input type="text" name="filter_code" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm" value="{{ request('filter_code') }}" placeholder="Code">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Email ID</label>
                            <input type="email" name="filter_email" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm" value="{{ request('filter_email') }}" placeholder="email@example.com">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Mobile No</label>
                            <input type="text" name="filter_mobile" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm" value="{{ request('filter_mobile') }}" placeholder="Mobile number">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="flex gap-2 pt-4 border-t border-slate-50">
                            <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors">Apply Filters</button>
                            <a href="{{ route('buyer.key-user-management.index') }}" class="flex-1 bg-slate-100 text-slate-600 text-center py-2 rounded-lg text-sm font-bold hover:bg-slate-200 transition-colors">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="relative" x-data="{ sortOpen: false }">
                <button @click="sortOpen = !sortOpen" class="inline-flex items-center px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-all shadow-sm">
                    <i class="bi bi-sort-down mr-2"></i> Sort
                </button>
                <div x-show="sortOpen" @click.away="sortOpen = false" class="absolute right-0 z-50 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 p-4" style="display: none;">
                    <form action="{{ route('buyer.key-user-management.index') }}" method="GET" class="space-y-3">
                        <select name="sort" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                            <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Join Date</option>
                            <option value="full_name" {{ request('sort') == 'full_name' ? 'selected' : '' }}>Name</option>
                            <option value="user_code" {{ request('sort') == 'user_code' ? 'selected' : '' }}>User Code</option>
                        </select>
                        <select name="direction" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                            <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>Newest First</option>
                            <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Oldest First</option>
                        </select>
                        <button type="submit" class="w-full bg-slate-800 text-white py-2 rounded-lg text-sm font-bold hover:bg-slate-900 transition-colors">Rearrange</button>
                    </form>
                </div>
            </div>

            <span class="ml-auto inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-full border border-blue-100">
                {{ $keyUsers->total() }} Records Found
            </span>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">User Code</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Full Name</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">BP Code</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Mobile</th>
                        <!-- <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Status</th> -->
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center no-print">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($keyUsers as $keyUser)
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="bg-slate-100 text-slate-700 font-mono text-xs px-2 py-1 rounded-md">
                                    {{ $keyUser->user_code }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-slate-800">{{ $keyUser->full_name }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $keyUser->bp_code }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $keyUser->email_id }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $keyUser->mobile_no }}
                            </td>
                            <!-- <td class="px-6 py-4 text-center">
                                @if($keyUser->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                        <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-green-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                        <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-red-500"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td> -->
                            <td class="px-6 py-4 no-print">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('buyer.key-user-management.show', $keyUser) }}" class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('buyer.key-user-management.edit', $keyUser) }}" class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('buyer.key-user-management.destroy', $keyUser) }}" method="POST" class="m-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" onclick="return confirm('Delete this user?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="bi bi-people text-5xl text-slate-200 mb-4"></i>
                                    <p class="text-slate-500 font-medium">No users found matching your search.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($keyUsers->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                {{ $keyUsers->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection