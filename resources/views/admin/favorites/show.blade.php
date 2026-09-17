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

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-100 bg-gray-50">
            <form action="{{ route('admin.favorites.show', [$user->id, $user_type]) }}" method="GET" class="flex flex-col md:flex-row items-center gap-3">
                
                <div class="w-full md:w-1/3">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search design code or name..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                </div>

                <div class="w-full md:w-1/3">
                    <select name="category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all bg-white">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (isset($categoryId) && $categoryId == $category->id) ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full md:w-auto flex items-center gap-2">
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm w-full md:w-auto text-center">
                        <i class="bi bi-search mr-1"></i> Filter
                    </button>
                    @if(!empty($search) || !empty($categoryId))
                        <a href="{{ route('admin.favorites.show', [$user->id, $user_type]) }}" class="px-4 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors shadow-sm w-full md:w-auto text-center whitespace-nowrap">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
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

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Select2 custom styling for Tailwind-like UI */
    .select2-container--default .select2-selection--single {
        height: 42px;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        background-color: white;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 1rem;
        color: #374151;
        line-height: normal;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
        right: 10px;
    }
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #6366f1;
        outline: none;
    }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('select[name="category_id"]').select2({
            placeholder: "All Categories",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection
