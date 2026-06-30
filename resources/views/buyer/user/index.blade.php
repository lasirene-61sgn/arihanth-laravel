@extends('buyer.layouts.app')

@section('title', 'User Management')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">User Management</h1>
            <p class="text-sm text-slate-500">Manage regular staff accounts and their system permissions.</p>
        </div>
        <div class="flex items-center">
            <a href="{{ route('buyer.user-management.create') }}" 
               class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-all shadow-md shadow-blue-100">
                <i class="bi bi-person-plus mr-2"></i> Add New User
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 flex flex-wrap items-center gap-4">
            <form action="{{ route('buyer.user-management.index') }}" method="GET" class="flex-grow max-w-md relative group">
                <input type="text" name="search" 
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all" 
                       placeholder="Search by Name, Email, Code..." value="{{ request('search') }}">
                <div class="absolute left-3 top-3 text-slate-400 group-focus-within:text-blue-600 transition-colors">
                    <i class="bi bi-search"></i>
                </div>
            </form>
            
            <div class="ml-auto">
                <span class="inline-flex items-center px-3 py-1 bg-slate-100 text-slate-600 text-xs font-bold rounded-full border border-slate-200">
                    {{ $users->total() }} Total Users
                </span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">User Code</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Full Name</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Mobile</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Permissions</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="bg-slate-100 text-slate-700 font-mono text-[11px] font-bold px-2 py-1 rounded border border-slate-200">
                                    {{ $user->user_code }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-800">
                                {{ $user->full_name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 font-medium">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 font-medium">
                                {{ $user->mobile_no }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->getPermissionsArray() as $perm)
                                        <span class="inline-flex px-2 py-0.5 rounded bg-blue-50 text-blue-600 text-[10px] font-bold border border-blue-100 capitalize">
                                            {{ str_replace('_', ' ', $perm) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('buyer.user-management.show', $user->id) }}" 
                                       class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('buyer.user-management.edit', $user->id) }}" 
                                       class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('buyer.user-management.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">
                                <div class="flex flex-col items-center">
                                    <i class="bi bi-person-x text-4xl mb-2 opacity-20"></i>
                                    <p>No users found matching your selection.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection