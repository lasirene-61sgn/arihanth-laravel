@extends('buyer.layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>My Jewelry Consultations</h3>
        <span class="badge badge-primary">Total: {{ $meetings->count() }}</span>
    </div>

    <!-- Request a Meeting Form -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-white">
            <h6 class="mb-0">Request a Video Consultation</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('buyer.meetings.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <label>Meet With</label>
                        <select name="host_id" class="form-control" required>
                            <optgroup label="Super Admins">
                                @foreach($superAdmins as $sa)
                                    <option value="{{ $sa->id }}">{{ $sa->full_name ?? $sa->name }} (Super Admin)</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Admins">
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}">{{ $admin->full_name ?? $admin->name }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Schedule Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label>Duration (Mins)</label>
                        <input type="number" name="duration_minutes" class="form-control" value="30" min="1" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Request Meeting</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($meetings->isEmpty())
        <div class="alert alert-light text-center border">
            <p>No jewelry viewings scheduled at the moment.</p>
        </div>
    @else
        <div class="row">
            @foreach($meetings as $meeting)
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">Virtual Viewing</h5>
                                <p class="text-muted mb-0">
                                    <i class="far fa-user"></i> Host: {{ $meeting->host->full_name ?? $meeting->host->name ?? 'Admin' }} <br>
                                    <i class="far fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($meeting->scheduled_at)->format('d M, Y | h:i A') }}
                                    <br>
                                    @php $displayStatus = $meeting->display_status; @endphp
                                    @if($displayStatus === 'pending')
                                        <span class="badge badge-warning">Pending Approval</span>
                                    @elseif($displayStatus === 'approved')
                                        <span class="badge badge-info">Confirmed</span>
                                    @elseif($displayStatus === 'completed')
                                        <span class="badge badge-success">Completed</span>
                                    @elseif($displayStatus === 'cancelled')
                                        <span class="badge badge-danger">Cancelled</span>
                                    @elseif($displayStatus === 'expired')
                                        <span class="badge badge-secondary">Expired</span>
                                    @endif
                                </p>
                            </div>

                            <div>
                                @php $displayStatus = $meeting->display_status; @endphp
                                @if($displayStatus === 'approved')
                                    <a href="{{ route('video.join', $meeting->room_id) }}" class="btn btn-success px-4 pulse-animation">
                                        JOIN NOW
                                    </a>
                                @elseif($displayStatus === 'pending')
                                    <button class="btn btn-outline-warning btn-sm" disabled>Awaiting Approval</button>
                                @elseif($displayStatus === 'completed')
                                    <button class="btn btn-outline-success" disabled>Completed</button>
                                @else
                                    <button class="btn btn-outline-secondary" disabled>Link Inactive</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<style>
    .pulse-animation {
        animation: pulse-red 2s infinite;
    }
    @keyframes pulse-red {
        0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
    }
</style>
@endsection