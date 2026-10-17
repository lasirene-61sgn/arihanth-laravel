@extends('super-admin.layouts.app')


@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Updates Management</h3>
            <p class="text-muted mb-0">Manage all your updates and announcements here.</p>
        </div>
        <a href="{{ route('super-admin.update.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Add New Update
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 ps-3">ID</th>
                            <th class="py-3">Title</th>
                            <th class="py-3">Description</th>
                            <th class="py-3">Images</th>
                            <th class="py-3 text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($updates as $update)
                            <tr>
                                <td class="ps-3 fw-semibold">#{{ $update->id }}</td>
                                <td>{{ $update->title ?? 'N/A' }}</td>
                                <td>{{ Str::limit($update->description, 40) }}</td>
                                <td>
                                    @php
                                        $images = json_decode($update->images, true) ?? [];
                                    @endphp
                                    <div class="d-flex gap-1 flex-wrap">
                                        @forelse($images as $img)
                                            <img src="{{ asset('storage/' . $img) }}" width="45" height="45" class="object-fit-cover rounded border">
                                        @empty
                                            <span class="text-muted small">No Image</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('super-admin.update.edit', $update->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    
                                    <form action="{{ route('super-admin.update.destroy', $update->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this update?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No updates found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination Links with proper spacing --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $updates->links() }}
    </div>
</div>
@endsection