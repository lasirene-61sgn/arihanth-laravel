@extends('craftsman_staff.layouts.app')

@section('title', 'Design Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Design Info: {{ $product->design_code }}</h4>
            <a href="{{ route('craftsman_staff.design.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Gallery
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body bg-light rounded text-center">
                    @php
                        $allImages = [];
                        if($product->images && $product->images->count() > 0) {
                            foreach($product->images as $img) $allImages[] = $img->path;
                        }
                        if($product->product_image) {
                            $legacy = explode(',', $product->product_image);
                            foreach($legacy as $img) {
                                $t = trim($img);
                                if($t && !in_array($t, $allImages)) $allImages[] = $t;
                            }
                        }
                    @endphp

                    @if(count($allImages) > 0)
                        @php 
                            $first = $allImages[0];
                            $mainSrc = str_starts_with($first, 'http') ? $first : (
                                str_starts_with($first, 'products/') ? asset('storage/'.$first) : (
                                str_starts_with($first, 'images/') || str_starts_with($first, 'storage/') ? asset($first) : asset('storage/products/'.$first)
                            ));
                        @endphp
                        <img src="{{ $mainSrc }}" id="mainImg" class="img-fluid rounded shadow-sm mb-3" 
                             style="max-height: 400px; width: 100%; object-fit: contain; background: white;">
                        
                        @if(count($allImages) > 1)
                            <div class="d-flex flex-wrap gap-2 justify-content-center">
                                @foreach($allImages as $img)
                                    @php
                                        $src = str_starts_with($img, 'http') ? $img : (
                                            str_starts_with($img, 'products/') ? asset('storage/'.$img) : (
                                            str_starts_with($img, 'images/') || str_starts_with($img, 'storage/') ? asset($img) : asset('storage/products/'.$img)
                                        ));
                                    @endphp
                                    <img src="{{ $src }}" class="img-thumbnail" 
                                         style="width: 65px; height: 65px; cursor: pointer; object-fit: cover;"
                                         onclick="document.getElementById('mainImg').src = this.src">
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="bg-white rounded d-flex align-items-center justify-content-center" style="height: 350px;">
                            <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 text-primary">Technical Specifications</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Design Name</label>
                            <span class="fw-bold fs-5">{{ $product->product_name }}</span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Category / Sub</label>
                            <span class="fw-medium">{{ $product->category->name ?? 'N/A' }} / {{ $product->subcategory->name ?? 'N/A' }}</span>
                        </div>

                        <div class="col-md-4">
                            <label class="text-muted small d-block">Material Type</label>
                            <span class="badge bg-info-subtle text-info border border-info px-3">{{ $product->type }}</span>
                        </div>
                        <div class="col-md-8">
                            <label class="text-muted small d-block">Weight Reference</label>
                            <span class="fw-bold text-dark">{{ $product->weight_from }} gm - {{ $product->weight_to }} gm</span>
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="border-bottom pb-2 text-muted text-uppercase small fw-bold">Detailed Attributes</h6>
                            <div class="row mt-2">
                                @php
                                    $specs = [
                                        'Size' => $product->size,
                                        'Length' => $product->length,
                                        'Hallmark' => $product->hallmark,
                                        'Rodium' => $product->rodium,
                                        'Hook' => $product->hook,
                                        'Stone' => $product->stone,
                                        'Enamel' => $product->enamel,
                                        'Opening' => $product->open_close
                                    ];
                                @endphp
                                @foreach($specs as $label => $value)
                                    <div class="col-md-3 col-6 mb-3">
                                        <label class="text-muted small d-block">{{ $label }}</label>
                                        <p class="fw-medium mb-0">{{ $value ?: '—' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if($product->details)
                        <div class="col-12 mt-2">
                            <label class="text-muted small d-block mb-1">Additional Remarks</label>
                            <div class="p-3 bg-light rounded border small">
                                {!! nl2br(e($product->details)) !!}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-white border-0 py-3">
                    <small class="text-muted">Catalogue Publication Date: {{ $product->updated_at->format('d M, Y') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
