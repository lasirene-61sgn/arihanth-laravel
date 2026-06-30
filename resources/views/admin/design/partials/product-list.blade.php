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
                                    <a href="{{ route('admin.product.show', $product) }}" class="btn btn-outline-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.product.edit', $product) }}" class="btn btn-outline-primary" title="Edit Design">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    @if($status == 'pending' || $status == 'all')
                                        @if($product->design_status != 'accepted')
                                            <form action="{{ route('admin.design.accept', $product) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Accept Design" onclick="return confirm('Are you sure you want to accept this design?')">
                                                    <i class="bi bi-check-circle"></i> Accept
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if($product->design_status != 'rejected')
                                            <form action="{{ route('admin.design.reject', $product) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger" title="Reject Design" onclick="return confirm('Are you sure you want to reject this design?')">
                                                    <i class="bi bi-x-circle"></i> Reject
                                                </button>
                                            </form>
                                        @endif
                                    @elseif($status == 'accepted')
                                        <a href="{{ route('admin.catalogue.index') }}" class="btn btn-outline-secondary" title="View in Catalogue">
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