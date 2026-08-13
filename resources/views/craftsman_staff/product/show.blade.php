@extends('craftsman_staff.layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Product Details</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('craftsman_staff.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('craftsman_staff.product.index') }}">Products</a></li>
                        <li class="breadcrumb-item active">View</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Product Information</h4>
                    <div>
                        <a href="{{ route('craftsman_staff.product.edit', $product) }}" class="btn btn-primary btn-sm me-2">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="{{ route('craftsman_staff.product.destroy', $product) }}" method="POST" class="d-inline" 
                              onsubmit="return confirm('Are you sure you want to delete this product?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="row">
                                @forelse($product->images as $image)
                                    <div class="col-6 mb-3">
                                        <img src="{{ asset('storage/' . $image->path) }}" 
                                             alt="Product Image" 
                                             class="img-fluid rounded border shadow-sm"
                                             style="cursor: pointer;"
                                             onclick="window.open(this.src)">
                                    </div>
                                @empty
                                    @if($product->product_image)
                                        @php $legacyImages = explode(',', $product->product_image); @endphp
                                        @foreach($legacyImages as $legImg)
                                            @if(trim($legImg))
                                                <div class="col-6 mb-3">
                                                    @php
                                                        $legacyPath = trim($legImg);
                                                        if (!str_starts_with($legacyPath, 'images/') && !str_starts_with($legacyPath, 'uploads/') && !str_starts_with($legacyPath, 'storage/')) {
                                                            $legacyPath = 'storage/products/' . $legacyPath;
                                                        }
                                                        if (!str_starts_with($legacyPath, 'storage/') && !str_starts_with($legacyPath, 'http')) {
                                                            $legacyUrl = asset($legacyPath);
                                                        } else {
                                                            $legacyUrl = asset($legacyPath);
                                                        }
                                                    @endphp
                                                    <img src="{{ $legacyUrl }}" 
                                                         alt="Product Image" 
                                                         class="img-fluid rounded border shadow-sm">
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <div class="col-12">
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" 
                                                 style="height: 200px;">
                                                <i class="bi bi-image" style="font-size: 3rem; color: #6c757d;"></i>
                                            </div>
                                        </div>
                                    @endif
                                @endforelse
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Product Code</label>
                                    <p class="fw-bold fs-5">{{ $product->product_code }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Product Name</label>
                                    <p class="fs-5">{{ $product->product_name }}</p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Category / Subcategory</label>
                                    <p>{{ $product->category->name ?? 'N/A' }} / {{ $product->subcategory->name ?? 'N/A' }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Type</label>
                                    <p><span class="badge bg-info">{{ $product->type }}</span></p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Weight Range</label>
                                    <p>{{ $product->weight_from ?? '0' }} - {{ $product->weight_to ?? '0' }} gm</p>
                                </div>

                                @if($product->size)
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Size / Length</label>
                                    <p>{{ $product->size }} / {{ $product->length ?? 'N/A' }}</p>
                                </div>
                                @endif

                                <div class="col-md-12"><hr></div>

                                @foreach(['hallmark', 'rodium', 'hook', 'stone', 'enamel'] as $field)
                                    @if($product->$field)
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label text-muted text-capitalize">{{ $field }}</label>
                                            <p>{{ $product->$field }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
