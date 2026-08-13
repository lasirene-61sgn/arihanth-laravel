@extends('super-admin.layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Product Details</h1>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-primary">{{ $product->product_name }}</h4>
        <a href="{{ route('super-admin.product.edit', $product) }}" class="btn btn-sm btn-primary">Edit Product</a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-5 mb-4 text-center">
                @if($product->images->count() > 0)
                    <div id="productCarousel" class="carousel slide border rounded bg-light" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @foreach($product->images as $index => $image)
                                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                    <img src="{{ asset('storage/' . $image->path) }}" class="img-fluid rounded" style="max-height: 400px; width: 100%; object-fit: contain;" alt="Product">
                                </div>
                            @endforeach
                        </div>
                        @if($product->images->count() > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon bg-secondary rounded-circle" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon bg-secondary rounded-circle" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        @endif
                    </div>
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center rounded border" style="height: 350px;">
                        <div class="text-muted">
                            <i class="bi bi-image" style="font-size: 4rem;"></i>
                            <p>No Image Uploaded</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-md-7">
                <table class="table table-sm table-striped border">
                    <tbody>
                        <tr><th width="150" class="bg-light">Product Code</th><td class="fw-bold">{{ $product->product_code }}</td></tr>
                        <tr><th class="bg-light">Category</th><td>{{ optional($product->category)->name }}</td></tr>
                        <tr><th class="bg-light">Sub Category</th><td>{{ optional($product->subcategory)->name ?? 'N/A' }}</td></tr>
                        <!-- <tr><th class="bg-light">Order Type</th><td>{{ $product->order_type }}</td></tr> -->
                        <tr><th class="bg-light">Weight Range</th><td>{{ $product->weight_from }} - {{ $product->weight_to }} g</td></tr>
                        <tr><th class="bg-light">Hook Info</th><td>{{ $product->hook ?? $product->open_close ?? 'N/A' }}</td></tr>
                        <tr><th class="bg-light">Size / Length</th><td>{{ $product->size ?? 'N/A' }} / {{ $product->length ?? 'N/A' }}</td></tr>
                        <tr><th class="bg-light">Stone / Enamel</th><td>{{ $product->stone ?? 'N/A' }} / {{ $product->enamel ?? 'N/A' }}</td></tr>
                        <tr><th class="bg-light">Rodium / HUID</th><td>{{ $product->rodium ?? 'N/A' }} / {{ $product->hallmark ?? 'N/A' }}</td></tr>
                        <tr>
                            <th class="bg-light">Craftsman</th>
                            <td>
                                {{ $product->craftsman->craftman_code ?? 'N/A' }} 
                                @if($product->craftsmanStaff)
                                    <br><span class="text-[10px] text-indigo-600">(Created By Staff: {{ $product->craftsmanStaff->staff_code ?? '' }} - {{ $product->craftsmanStaff->name ?? '' }})</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white border-top-0">
        <a href="{{ route('super-admin.product.index') }}" class="btn btn-outline-secondary btn-sm">Back to Products</a>
    </div>
</div>
@endsection