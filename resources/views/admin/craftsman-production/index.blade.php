@extends('admin.layouts.app')

@section('title', 'Craftsman Production Dashboard')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Craftsman Production Dashboard</h1>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-6">
        <form action="{{ route('admin.craftsman-production.index') }}" method="GET" class="flex gap-4">
            <input type="text" name="search" class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="Search by name, code or business name..." value="{{ request('search') }}">
            <button type="submit" class="bg-magenta-800 text-white px-6 py-2 rounded-lg font-medium hover:bg-magenta-900 transition-colors">
                <i class="bi bi-search mr-2"></i> Search
            </button>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200">Code</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200">Name</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200">Business Name</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200">City</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($craftsmen as $craftsman)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <td class="px-6 py-4">
                            <span class="bg-gray-100 dark:bg-gray-900 px-2 py-1 rounded text-xs font-bold text-gray-600 dark:text-gray-400">
                                {{ $craftsman->craftman_code }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-800 dark:text-gray-200 font-medium">{{ $craftsman->name }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $craftsman->business_name }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $craftsman->city ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.craftsman-production.show', $craftsman->craftman_code) }}" class="text-magenta-800 hover:text-magenta-900 font-semibold text-sm">
                                <i class="bi bi-graph-up mr-1"></i> View Production
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">No craftsmen found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($craftsmen->hasPages())
        <div class="p-6 border-t border-gray-200 dark:border-gray-700">
            {{ $craftsmen->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
