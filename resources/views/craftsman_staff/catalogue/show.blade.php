@extends('craftsman_staff.layouts.app')

@section('title', 'Catalogue Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between">
            <h4 class="mb-0">Design Details: {{ $design->design_code }}</h4>
            <a href="{{ route('craftsman_staff.catalogue.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Catalogue
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card mb-4 bg-light">
                <div class="card-body text-center">
                    @php
                        $allImages = [];
                        if($design->images && $design->images->count() > 0) {
                            foreach($design->images as $img) $allImages[] = $img->path;
                        }
                        if($design->product_image) {
                            $legacy = explode(',', $design->product_image);
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
                        <img src="{{ $mainSrc }}" 
                             id="main" 
                             class="img-fluid rounded mb-2 shadow-sm" 
                             style="max-height: 400px; width: 100%; object-fit: contain; background: white;">
                        
                        @if(count($allImages) > 1)
                            <div class="d-flex flex-wrap gap-2 justify-content-center mt-3">
                                @foreach($allImages as $img)
                                    @php
                                        $src = str_starts_with($img, 'http') ? $img : (
                                            str_starts_with($img, 'products/') ? asset('storage/'.$img) : (
                                            str_starts_with($img, 'images/') || str_starts_with($img, 'storage/') ? asset($img) : asset('storage/products/'.$img)
                                        ));
                                    @endphp
                                    <img src="{{ $src }}" 
                                         class="img-thumbnail" 
                                         style="width: 60px; height: 60px; cursor: pointer; object-fit: cover;" 
                                         onclick="document.getElementById('main').src=this.src">
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="d-flex align-items-center justify-content-center bg-light rounded" style="height: 300px;">
                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0 text-primary">Technical Specifications</h5></div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6"><label class="text-muted small d-block">Design Name</label><span class="fw-bold fs-5">{{ $design->product_name }}</span></div>
                        <div class="col-md-6"><label class="text-muted small d-block">Category</label><span class="fw-medium">{{ $design->category->name ?? 'N/A' }}</span></div>
                        <div class="col-md-4"><label class="text-muted small d-block">Type</label><span class="badge bg-info px-3">{{ $design->type }}</span></div>
                        <div class="col-md-8"><label class="text-muted small d-block">Approx Weight</label><span class="fw-bold">{{ $design->weight_from }} - {{ $design->weight_to }} gm</span></div>
                        
                        <div class="col-12 mt-4"><h6 class="border-bottom pb-2 text-uppercase small fw-bold">Attributes</h6></div>
                        @php $fields = ['Size','Length','Hallmark','Rodium','Hook','Stone','Enamel']; @endphp
                        @foreach($fields as $f)
                            <div class="col-md-4 col-6 mb-3">
                                <label class="text-muted small d-block">{{ $f }}</label>
                                <span class="fw-medium">{{ $design->{strtolower($f)} ?: '—' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
