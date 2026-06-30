@extends('admin.layouts.app')

@section('title', 'Favorites Management')

<style>
    /* Table Responsiveness & Polish */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }
    .table-responsive::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .design-badge {
        font-family: ui-monospace, monospace;
        font-size: 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        padding: 2px 6px;
        border-radius: 4px;
    }
</style>

@section('content')
<div class="space-y-6">
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Favorites Management</h1>
            <p class="text-sm text-gray-500">Monitor and manage favorited designs across all users</p>
        </div>

        <div class="w-full xl:w-96">
            <form action="{{ route('admin.favorites.index') }}" method="GET" class="relative">
                <input type="text" 
                       name="search" 
                       value="{{ $search ?? '' }}"
                       placeholder="Search name, code, or design..." 
                       class="w-full pl-10 pr-10 py-2.5 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all shadow-sm">
                <div class="absolute left-3 top-3 text-gray-400">
                    <i class="bi bi-search"></i>
                </div>
                @if($search)
                    <a href="{{ route('admin.favorites.index') }}" class="absolute right-3 top-3 text-gray-400 hover:text-red-500">
                        <i class="bi bi-x-circle-fill"></i>
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">User Type</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Total Favorites</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Design Codes</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Last Activity</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($favorites as $favGroup)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center min-w-[180px]">
                                <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold">
                                    {{ substr($favGroup->user->full_name ?? $favGroup->user->name ?? 'U', 0, 1) }}
                                </div>
                                <div class="ms-4">
                                    <div class="text-sm font-bold text-gray-900">
                                        {{ $favGroup->user->full_name ?? $favGroup->user->name ?? 'Unknown' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $favGroup->user_type == 'buyer' ? ($favGroup->user->bp_code ?? 'N/A') : ($favGroup->user->craftman_code ?? 'N/A') }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $favGroup->user_type == 'buyer' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ ucfirst($favGroup->user_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center justify-center px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-sm font-bold border border-indigo-100">
                                {{ $favGroup->total_favorites }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1 max-w-[200px]">
                                @php $codes = array_filter(explode(', ', $favGroup->design_codes)); @endphp
                                @foreach(array_slice($codes, 0, 4) as $code)
                                    <span class="design-badge">{{ $code }}</span>
                                @endforeach
                                @if(count($codes) > 4)
                                    <span class="text-[10px] text-gray-400 font-bold self-center">+{{ count($codes) - 4 }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">
                            {{ \Carbon\Carbon::parse($favGroup->last_added_at)->format('M d, Y') }}
                            <div class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($favGroup->last_added_at)->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <a href="{{ route('admin.favorites.show', ['user_id' => $favGroup->user_id, 'user_type' => $favGroup->user_type]) }}" 
                               class="text-indigo-600 hover:bg-indigo-600 hover:text-white p-2 rounded-lg border border-indigo-100 transition-all inline-block shadow-sm" 
                               title="View Details">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <i class="bi bi-search text-5xl text-gray-200 mb-4"></i>
                                <p class="text-gray-500 font-medium">No results found for your search.</p>
                                @if($search)
                                    <a href="{{ route('admin.favorites.index') }}" class="mt-2 text-indigo-600 hover:underline">Clear Search</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($favorites->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $favorites->links() }}
        </div>
        @endif
    </div>
</div>
@endsection