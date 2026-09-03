@extends('super-admin.layouts.app')

@section('title', 'Favorites Management')

@section('content')
<style>
    /* Layout & Typography */
    .fav-wrapper {
        display: flex;
        flex-direction: column;
        gap: 24px;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .header-section {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .header-title h1 {
        font-size: 24px;
        font-weight: 800;
        color: #111827;
        margin: 0 0 4px 0;
    }

    .header-title p {
        font-size: 14px;
        color: #6b7280;
        margin: 0;
    }

    /* Search Bar */
    .search-container {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .search-input-group {
        position: relative;
    }

    .search-input {
        padding: 8px 36px 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        width: 280px;
        outline: none;
        transition: border-color 0.2s;
    }

    .search-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .search-submit-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
    }

    .clear-link {
        display: flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        color: #4b5563;
        font-size: 14px;
        font-weight: 500;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: white;
    }

    /* Table Styles */
    .table-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .main-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .main-table thead {
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .main-table th {
        padding: 12px 24px;
        font-size: 12px;
        font-weight: 600;
        color: #4b5563;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .main-table td {
        padding: 16px 24px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    .main-table tr:hover {
        background-color: #f9fafb;
    }

    /* User Info Column */
    .user-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar {
        width: 40px;
        height: 40px;
        background: #eff6ff;
        color: #2563eb;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        border: 1px solid #dbeafe;
    }

    .user-name-text {
        font-weight: 600;
        color: #111827;
        display: block;
        font-size: 14px;
    }

    .user-id-text {
        color: #6b7280;
        font-size: 12px;
    }

    /* Badges */
    .badge {
        padding: 2px 10px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 500;
    }

    .badge-buyer { background: #dcfce7; color: #166534; }
    .badge-craftman { background: #f3e8ff; color: #6b21a8; }

    .count-box {
        padding: 4px 12px;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        font-weight: 700;
        font-size: 13px;
    }

    .code-tag {
        background: #eff6ff;
        color: #1d4ed8;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        border: 1px solid #dbeafe;
    }

    /* Actions */
    .view-btn {
        color: #2563eb;
        background: #eff6ff;
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s;
    }

    .view-btn:hover {
        background: #2563eb;
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 48px;
        color: #9ca3af;
    }

    .pagination-footer {
        padding: 16px 24px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
    }
</style>

<div class="fav-wrapper">
    <!-- Header -->
    <div class="header-section">
        <div class="header-title" style="display: flex; align-items: center; gap: 16px;">
            <div>
                <h1>Favorites Management</h1>
                <p>Monitor and manage favorited designs across all users</p>
            </div>
            <a href="{{ route('super-admin.favorites.create') }}" class="btn btn-primary" style="background-color: #2563eb; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                <i class="bi bi-plus-lg"></i> Add Favorite Collection
            </a>
        </div>
        
        <form method="GET" action="{{ route('super-admin.favorites.index') }}" class="search-container">
            <div class="search-input-group">
                <input type="text" name="search" class="search-input" placeholder="Search by name, code or design..." value="{{ $search ?? '' }}">
                <button type="submit" class="search-submit-icon">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            @if(!empty($search))
            <a href="{{ route('super-admin.favorites.index') }}" class="clear-link">
                <i class="bi bi-x-lg"></i>
                <span>Clear</span>
            </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="table-card">
        <table class="main-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>User Type</th>
                    <th style="text-align: center;">Total Favorites</th>
                    <th>Design Names / Codes</th>
                    <th>Last Activity</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($favorites as $favGroup)
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="avatar">
                                {{ substr($favGroup->user->full_name ?? $favGroup->user->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <span class="user-name-text">{{ $favGroup->user->full_name ?? $favGroup->user->name ?? 'Unknown' }}</span>
                                <span class="user-id-text">{{ $favGroup->user_type == 'buyer' ? ($favGroup->user->bp_code ?? 'N/A') : ($favGroup->user->craftman_code ?? 'N/A') }}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge {{ $favGroup->user_type == 'buyer' ? 'badge-buyer' : 'badge-craftman' }}">
                            {{ ucfirst($favGroup->user_type) }}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <span class="count-box">{{ $favGroup->total_favorites }}</span>
                    </td>
                    <td>
                        <div style="display: flex; flex-wrap: wrap; gap: 4px; max-width: 250px;">
                            @php 
                                $names = explode(', ', $favGroup->design_names ?? $favGroup->design_codes);
                                // unique names
                                $names = array_unique(array_filter($names));
                            @endphp
                            @foreach(array_slice($names, 0, 5) as $name)
                                <span class="code-tag">{{ $name }}</span>
                            @endforeach
                            @if(count($names) > 5)
                                <span style="font-size: 11px; color: #9ca3af; margin-left: 4px;">+{{ count($names) - 5 }} more</span>
                            @endif
                        </div>
                    </td>
                    <td style="font-size: 13px; color: #6b7280;">
                        {{ \Carbon\Carbon::parse($favGroup->last_added_at)->format('M d, Y H:i') }}
                    </td>
                    <td style="text-align: center;">
                        <a href="{{ route('super-admin.favorites.edit', ['user_id' => $favGroup->user_id, 'user_type' => $favGroup->user_type]) }}" class="view-btn" title="Edit Details" style="background-color: #fef3c7; color: #d97706; margin-right: 4px;">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="{{ route('super-admin.favorites.show', ['user_id' => $favGroup->user_id, 'user_type' => $favGroup->user_type]) }}" class="view-btn" title="View Details">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="bi bi-heart" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                            <p>No favorite entries found in the system.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($favorites->hasPages())
        <div class="pagination-footer">
            {{ $favorites->links() }}
        </div>
        @endif
    </div>
</div>
@endsection