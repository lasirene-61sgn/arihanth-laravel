@extends('admin.layouts.app')

@section('title', 'User Favorites Details')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('admin.favorites.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="bi bi-arrow-left-circle text-2xl"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Favorites Details</h1>
            </div>
            <p class="text-sm text-gray-500">
                Viewing favorited designs for 
                <span class="font-bold text-indigo-600">{{ $user->full_name ?? $user->name }}</span> 
                ({{ ucfirst($user_type) }})
            </p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Design Image</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Design Code</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Design Name</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Added At</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($favorites as $favorite)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
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
                            <img src="{{ $imgSrc }}" class="h-16 w-16 object-contain rounded-lg border border-gray-100 shadow-sm" alt="Design">
                            @else
                            <div class="h-16 w-16 bg-gray-50 rounded-lg flex items-center justify-center text-gray-300 border border-gray-100">
                                <i class="bi bi-image text-xl"></i>
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-mono font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded">
                                {{ $favorite->product->design_code ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                            {{ $favorite->design_name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $favorite->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <form action="{{ route('admin.favorites.destroy', $favorite->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this favorite?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 p-2 rounded-lg transition-colors" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                            <p>No favorites found for this user.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
