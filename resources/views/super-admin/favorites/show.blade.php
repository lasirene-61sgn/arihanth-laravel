@extends('super-admin.layouts.app')

@section('title', 'User Favorites Details')

@section('content')
<style>
    /* Layout & Typography */
    .favorites-show-container {
        display: flex;
        flex-direction: column;
        gap: 24px;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .favorites-show-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
    }

    .favorites-show-header h1 {
        font-size: 24px;
        font-weight: 800;
        color: #111827;
        margin: 0;
    }

    .favorites-show-header p {
        font-size: 14px;
        color: #6b7280;
        margin: 4px 0 0 0;
    }

    .back-link {
        color: #6b7280;
        font-size: 24px;
        text-decoration: none;
        transition: color 0.2s;
        display: flex;
        align-items: center;
    }

    .back-link:hover {
        color: #2563eb;
    }

    .highlight-name {
        font-weight: 700;
        color: #111827;
    }

    /* Table Card */
    .favorites-container {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .favorites-header {
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .table th {
        padding: 12px 24px;
        font-size: 12px;
        font-weight: 600;
        color: #4b5563;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .table td {
        padding: 12px 24px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    .favorites-row:hover {
        background-color: #f9fafb;
    }

    /* Image Styles */
    .design-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: #f3f4f6;
    }

    .design-image-placeholder {
        width: 60px;
        height: 60px;
        background: #f3f4f6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        border: 1px dashed #d1d5db;
    }

    /* Badge & Info */
    .design-code-badge {
        display: inline-block;
        padding: 4px 12px;
        background: #eff6ff;
        color: #1e40af;
        border: 1px solid #dbeafe;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
    }

    /* Delete Button */
    .delete-btn {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fee2e2;
        padding: 8px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .delete-btn:hover {
        background: #dc2626;
        color: white;
        border-color: #dc2626;
    }

    .empty-state {
        padding: 40px;
        text-align: center;
        color: #9ca3af;
        font-size: 14px;
    }
</style>

<div class="favorites-show-container">
    <div class="favorites-show-header">
        <div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <a href="{{ route('super-admin.favorites.index') }}" class="back-link">
                    <i class="bi bi-arrow-left-circle"></i>
                </a>
                <h1>Favorites Details</h1>
            </div>
            <p>
                Viewing favorited designs for
                <span class="highlight-name">{{ $user->full_name ?? $user->name }}</span>
                ({{ ucfirst($user_type) }})
            </p>
        </div>
    </div>

    <div class="favorites-container">
        <div class="table-responsive">
            <table class="table">
                <thead class="favorites-header">
                    <tr>
                        <th>Design Image</th>
                        <th>Design Code</th>
                        <th>Design Name</th>
                        <th>Added At</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($favorites as $favorite)
                    <tr class="favorites-row">
                        <td>
                            @php
                            $design = $favorite->product;
                            $imagesCount = $design->images->count();
                            $firstImage = $imagesCount > 0 ? $design->images->first()->path : null;
                            if (!$firstImage && $design->product_image) {
                                $imgs = explode(',', $design->product_image);
                                $firstImage = trim($imgs[0]);
                            }

                            $imgSrc = null;
                            if ($firstImage) {
                                if (str_starts_with($firstImage, 'http')) { $imgSrc = $firstImage; }
                                elseif (str_starts_with($firstImage, 'products/')) { $imgSrc = asset('storage/' . $firstImage); }
                                elseif (str_starts_with($firstImage, 'images/') || str_starts_with($firstImage, 'storage/')) { $imgSrc = asset($firstImage); }
                                else { $imgSrc = asset('storage/products/' . $firstImage); }
                            }
                            @endphp
                            
                            @if($imgSrc)
                                <img src="{{ $imgSrc }}" class="design-image" alt="Design">
                            @else
                                <div class="design-image-placeholder">
                                    <i class="bi bi-image"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="design-code-badge">
                                {{ $favorite->product->design_code ?? 'N/A' }}
                            </span>
                        </td>
                        <td style="font-size: 14px; font-weight: 500; color: #374151;">
                            {{ $favorite->design_name ?? 'N/A' }}
                        </td>
                        <td style="white-space: nowrap; font-size: 14px; color: #6b7280;">
                            {{ $favorite->created_at->format('M d, Y H:i') }}
                        </td>
                        <td style="text-align: center;">
                            <form action="{{ route('super-admin.favorites.destroy', $favorite->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this favorite?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-btn" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <i class="bi bi-heartbreak" style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                                <p>No favorites found for this user.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection