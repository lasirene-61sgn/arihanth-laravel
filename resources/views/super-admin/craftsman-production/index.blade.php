@extends('super-admin.layouts.app')

@section('title', 'Craftsman Production Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Craftsman Production Dashboard</h1>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Select Craftsman</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('super-admin.craftsman-production.index') }}" method="GET" class="row g-3">
                        <div class="col-md-10">
                            <input type="text" name="search" class="form-control" placeholder="Search by name, code or business name..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Craftsmen List</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Business Name</th>
                                    <th>City</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($craftsmen as $craftsman)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ $craftsman->craftman_code }}</span></td>
                                    <td>{{ $craftsman->name }}</td>
                                    <td>{{ $craftsman->business_name }}</td>
                                    <td>{{ $craftsman->city ?? 'N/A' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('super-admin.craftsman-production.show', $craftsman->craftman_code) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-graph-up"></i> View Production
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">No craftsmen found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($craftsmen->hasPages())
                <div class="card-footer">
                    {{ $craftsmen->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
