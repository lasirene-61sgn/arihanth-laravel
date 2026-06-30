<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>{{ ucfirst($status) }} Products</h4>
        <span class="badge bg-primary">{{ $products->count() }}</span>
    </div>
    <div class="card-body">
        @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-sm align-middle">
                    <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product Code</th>
                        <th>Relabel Code</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Subcategory</th>
                        <th>Type</th>
                        <th>Order Type</th>
                        <th>Design Status</th>
                        <th>Design Code</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>
                                @php
                                    $imagesCount = $product->images->count();
                                    $firstImage = $imagesCount > 0 ? $product->images->first()->path : null;
                                    
                                    // Fallback for legacy data
                                    if (!$firstImage && $product->product_image) {
                                        $imgs = explode(',', $product->product_image);
                                        $firstImage = trim($imgs[0]);
                                    }

                                    $imgUrl = null;
                                    if ($firstImage) {
                                        if (str_starts_with($firstImage, 'http')) {
                                            $imgUrl = $firstImage;
                                        } elseif (str_starts_with($firstImage, 'products/')) {
                                            $imgUrl = asset('storage/' . $firstImage);
                                        } elseif (str_starts_with($firstImage, 'images/') || str_starts_with($firstImage, 'storage/')) {
                                            $imgUrl = asset($firstImage);
                                        } else {
                                            $imgUrl = asset('storage/products/' . $firstImage);
                                        }
                                    }
                                @endphp

                                <div class="position-relative" style="width: 40px; height: 40px;">
                                    @if($imgUrl)
                                        <img src="{{ $imgUrl }}" 
                                             alt="Product" 
                                             class="rounded border" 
                                             style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;"
                                             onclick="window.location.href='{{ route('super-admin.product.show', $product) }}'">
                                        @if($imagesCount > 1)
                                            <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-dark bg-opacity-75" style="font-size: 0.5rem; padding: 1px 3px;">
                                                +{{ $imagesCount - 1 }}
                                            </span>
                                        @endif
                                    @else
                                        <div class="bg-light rounded border d-flex align-items-center justify-content-center" style="width: 100%; height: 100%;">
                                            <i class="bi bi-image text-muted" style="font-size: 0.8rem;"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $product->product_code }}</td>
                            <td>{{ $product->relabel_code }}</td>
                            <td>{{ $product->product_name }}</td>
                            <td>{{ optional($product->category)->name }}</td>
                            <td>{{ optional($product->subcategory)->name }}</td>
                            <td>{{ $product->type }}</td>
                            <td>{{ $product->order_type }}</td>
                            <td>
                                @if($product->design_status == 'accepted')
                                    <span class="badge bg-success">Accepted</span>
                                @elseif($product->design_status == 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                             <td>{{ (strtolower($product->design_status) === 'accepted') ? ($product->design_code ?? '-') : '-' }}</td>
                            <td>{{ $product->created_at->format('d M, Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('super-admin.product.show', $product) }}" class="btn btn-outline-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('super-admin.product.edit', $product) }}" class="btn btn-outline-primary" title="Edit Design">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    @if($status == 'pending' || $status == 'all')
                                        @if($product->design_status != 'accepted')
                                            <form action="{{ route('super-admin.design.accept', $product) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Accept Design" onclick="return confirm('Are you sure you want to accept this design?')">
                                                    <i class="bi bi-check-circle"></i> Accept
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if($product->design_status != 'rejected')
                                            <form action="{{ route('super-admin.design.reject', $product) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger" title="Reject Design" onclick="return confirm('Are you sure you want to reject this design?')">
                                                    <i class="bi bi-x-circle"></i> Reject
                                                </button>
                                            </form>
                                        @endif
                                    @elseif($status == 'accepted')
                                        <a href="{{ route('super-admin.catalogue.index') }}" class="btn btn-outline-secondary" title="View in Catalogue">
                                            <i class="bi bi-folder"></i> View Catalogue
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-box" style="font-size: 2rem;"></i>
                <p class="mt-2">No {{ $status }} products found.</p>
            </div>
        @endif
    </div>
</div>