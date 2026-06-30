@extends('user.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Product Details</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('user.product.index') }}">Products</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Product Information</h4>
                    <div>
                        <a href="{{ route('user.product.edit', $product) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="{{ route('user.product.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
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
                                        <div class="col-12 mb-3">
                                            <img src="{{ asset($product->product_image) }}" 
                                                 alt="Product Image" 
                                                 class="img-fluid rounded border shadow-sm">
                                        </div>
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
                            <h5>{{ $product->product_name }}</h5>
                            <p class="text-muted">{{ $product->product_code }}</p>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Product Code</label>
                                        <p class="fw-bold">{{ $product->product_code }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Product Name</label>
                                        <p class="fw-bold">{{ $product->product_name }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Product Category</label>
                                        <p class="fw-bold">{{ $product->product_category }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Type</label>
                                        <p class="fw-bold">{{ $product->type }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Order Type</label>
                                        <p class="fw-bold">
                                            @if($product->order_type == 'Regular')
                                                <span class="badge bg-primary">{{ $product->order_type }}</span>
                                            @elseif($product->order_type == 'Urgent')
                                                <span class="badge bg-warning">{{ $product->order_type }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ $product->order_type }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Open/Close</label>
                                        <p class="fw-bold">
                                            @if($product->open_close == 'Open')
                                                <span class="badge bg-success">{{ $product->open_close }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $product->open_close }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Hallmark</label>
                                        <p class="fw-bold">{{ $product->hallmark ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Rodium</label>
                                        <p class="fw-bold">{{ $product->rodium ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Hook</label>
                                        <p class="fw-bold">{{ $product->hook ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Size</label>
                                        <p class="fw-bold">{{ $product->size ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Stone</label>
                                        <p class="fw-bold">{{ $product->stone ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Enamel</label>
                                        <p class="fw-bold">{{ $product->enamel ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Length</label>
                                        <p class="fw-bold">{{ $product->length ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Created At</label>
                                        <p class="fw-bold">{{ $product->created_at->format('d M, Y h:i A') }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted">Updated At</label>
                                        <p class="fw-bold">{{ $product->updated_at->format('d M, Y h:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
