@extends('super-admin.layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Manage System Updates</h2>
    <hr>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>{{ $editUpdate ? 'Edit Update' : 'Create New Update' }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ $editUpdate ? route('super-admin.updates.update', $editUpdate->id) : route('super-admin.updates.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if($editUpdate)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $editUpdate ? $editUpdate->title : '') }}" placeholder="Enter update title">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Enter full description">{{ old('description', $editUpdate ? $editUpdate->description : '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="duration" class="form-label">Display Duration (Seconds)</label>
                            <input type="number" name="duration" id="duration" class="form-control @error('duration') is-invalid @enderror" value="{{ old('duration', $editUpdate ? $editUpdate->duration : '') }}" placeholder="e.g. 5000">
                            @error('duration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="target_audience" class="form-label">Target Audience</label>
                            <select name="target_audience" id="target_audience" class="form-control @error('target_audience') is-invalid @enderror" onchange="toggleAudienceLists()">
                                <option value="all" {{ old('target_audience', $editUpdate ? $editUpdate->target_audience : 'all') == 'all' ? 'selected' : '' }}>All (Buyers & Craftsmen)</option>
                                <option value="buyer" {{ old('target_audience', $editUpdate ? $editUpdate->target_audience : '') == 'buyer' ? 'selected' : '' }}>Buyers Only</option>
                                <option value="craftsman" {{ old('target_audience', $editUpdate ? $editUpdate->target_audience : '') == 'craftsman' ? 'selected' : '' }}>Craftsmen Only</option>
                            </select>
                            @error('target_audience')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="buyers_list_container" style="display: {{ old('target_audience', $editUpdate ? $editUpdate->target_audience : 'all') == 'buyer' ? 'block' : 'none' }};">
                            <label for="target_buyers" class="form-label">Select Buyers</label>
                            <select name="target_buyers[]" id="target_buyers" class="form-control select2" multiple>
                                @php
                                    $selectedBuyers = old('target_buyers', $editUpdate && $editUpdate->target_buyers ? $editUpdate->target_buyers : ['all']);
                                @endphp
                                <option value="all" {{ in_array('all', $selectedBuyers) ? 'selected' : '' }}>-- Select All Buyers --</option>
                                @foreach($buyers as $buyer)
                                    <option value="{{ $buyer->id }}" {{ in_array((string)$buyer->id, $selectedBuyers) ? 'selected' : '' }}>{{ $buyer->bp_code }} - {{ $buyer->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Leave as "Select All Buyers" to send to all buyers, or select individuals.</small>
                            @error('target_buyers')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="craftsmen_list_container" style="display: {{ old('target_audience', $editUpdate ? $editUpdate->target_audience : 'all') == 'craftsman' ? 'block' : 'none' }};">
                            <label for="target_craftsmen" class="form-label">Select Craftsmen</label>
                            <select name="target_craftsmen[]" id="target_craftsmen" class="form-control select2" multiple>
                                @php
                                    $selectedCraftsmen = old('target_craftsmen', $editUpdate && $editUpdate->target_craftsmen ? $editUpdate->target_craftsmen : ['all']);
                                @endphp
                                <option value="all" {{ in_array('all', $selectedCraftsmen) ? 'selected' : '' }}>-- Select All Craftsmen --</option>
                                @foreach($craftsmen as $craftsman)
                                    <option value="{{ $craftsman->id }}" {{ in_array((string)$craftsman->id, $selectedCraftsmen) ? 'selected' : '' }}>{{ $craftsman->craftman_code }} - {{ $craftsman->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Leave as "Select All Craftsmen" to send to all craftsmen, or select individuals.</small>
                            @error('target_craftsmen')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="media" class="form-label">Media (Image or Video)</label>
                            <input type="file" name="media" id="media" class="form-control @error('media') is-invalid @enderror" accept="image/*,video/*">
                            @if($editUpdate && $editUpdate->media_path)
                                <div class="mt-2">
                                    <small>Current Media: <a href="{{ asset('storage/'.$editUpdate->media_path) }}" target="_blank">View</a></small>
                                </div>
                            @endif
                            @error('media')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="newupdates" class="form-label">Old Update Content (Optional/Legacy)</label>
                            <textarea name="newupdates" id="newupdates" rows="2" class="form-control @error('newupdates') is-invalid @enderror" placeholder="Write update details here...">{{ old('newupdates', $editUpdate ? $editUpdate->newupdates : '') }}</textarea>
                            @error('newupdates')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn {{ $editUpdate ? 'btn-warning' : 'btn-primary' }} w-100">
                            {{ $editUpdate ? 'Update Entry' : 'Publish Update' }}
                        </button>

                        @if($editUpdate)
                            <a href="{{ route('super-admin.updates.index') }}" class="btn btn-secondary w-100 mt-2">Cancel Edit</a>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Existing Updates</h5>
                </div>
                <div class="card-body">
                    @if($updates->isEmpty())
                        <p class="text-muted text-centermy-3">No updates found.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 5%">ID</th>
                                        <th>Title / Content</th>
                                        <th>Audience</th>
                                        <th>Media</th>
                                        <th>Duration</th>
                                        <th style="width: 25%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($updates as $update)
                                        <tr>
                                            <td>{{ $update->id }}</td>
                                            <td>
                                                <strong>{{ $update->title ?? 'N/A' }}</strong><br>
                                                <small>{{ $update->description ?? $update->newupdates }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ ucfirst($update->target_audience ?? 'all') }}</span>
                                            </td>
                                            <td>
                                                @if($update->media_path)
                                                    @if($update->media_type == 'image')
                                                        <img src="{{ asset('storage/'.$update->media_path) }}" alt="media" width="60" class="img-thumbnail">
                                                    @elseif($update->media_type == 'video')
                                                        <video width="60" class="img-thumbnail" controls>
                                                            <source src="{{ asset('storage/'.$update->media_path) }}" type="video/{{ pathinfo($update->media_path, PATHINFO_EXTENSION) }}">
                                                        </video>
                                                    @endif
                                                @else
                                                    No Media
                                                @endif
                                            </td>
                                            <td>{{ $update->duration ? $update->duration . 's' : 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('super-admin.updates.index', ['edit' => $update->id]) }}" class="btn btn-sm btn-info text-white">Edit</a>
                                                
                                                <form action="{{ route('super-admin.updates.destroy', $update->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this update?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    function toggleAudienceLists() {
        const audience = document.getElementById('target_audience').value;
        const buyersList = document.getElementById('buyers_list_container');
        const craftsmenList = document.getElementById('craftsmen_list_container');

        if (audience === 'buyer') {
            buyersList.style.display = 'block';
            craftsmenList.style.display = 'none';
        } else if (audience === 'craftsman') {
            buyersList.style.display = 'none';
            craftsmenList.style.display = 'block';
        } else {
            buyersList.style.display = 'none';
            craftsmenList.style.display = 'none';
        }
    }
    
    // Initialize select2 if available
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
            $('.select2').select2({
                placeholder: "Select targets",
                allowClear: true
            });
        }
        toggleAudienceLists(); // Initial state
    });
</script>
@endsection
@endsection