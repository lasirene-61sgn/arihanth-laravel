@extends('super-admin.layouts.app') {{-- Apne layout ka naam yahan likhein --}}


@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0 fw-bold text-primary">Create New Update</h4>
                </div>
                <div class="card-body p-4">

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('super-admin.update.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Enter update title...">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="5" placeholder="Enter details here...">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="images" class="form-label fw-semibold">Upload Images (Multiple)</label>
                            <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                            <div class="form-text">Aap ek sath multiple images select kar sakte hain (JPG, PNG, WEBP).</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('super-admin.update.index') }}" class="btn btn-light px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Save Update</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection