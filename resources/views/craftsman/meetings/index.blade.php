@extends('craftsman.layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Production Design Calls</h3>

    <!-- Request a Meeting Form -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <h6 class="mb-0">Request a Video Consultation</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('craftsman.meetings.store') }}" method="POST">
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

    <div class="table-responsive">
        <table class="table table-hover bg-white shadow-sm">
            <thead class="thead-dark">
                <tr>
                    <th>Date & Time</th>
                    <th>Admin Contact</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Meeting Link</th>
                </tr>
            </thead>
            <tbody>
                @forelse($meetings as $meeting)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($meeting->scheduled_at)->format('Y-m-d H:i') }}</td>
                    <td>{{ $meeting->host->full_name ?? $meeting->host->name ?? 'Admin' }}</td>
                    <td>{{ $meeting->duration_minutes }} Mins</td>
                    <td>
                        @php $displayStatus = $meeting->display_status; @endphp
                        @if($displayStatus === 'pending')
                            <span class="badge badge-warning">Pending Approval</span>
                        @elseif($displayStatus === 'approved')
                            <span class="badge badge-info text-white">Confirmed</span>
                        @elseif($displayStatus === 'completed')
                            <span class="badge badge-success">Completed</span>
                        @elseif($displayStatus === 'cancelled')
                            <span class="badge badge-danger">Cancelled</span>
                        @elseif($displayStatus === 'expired')
                            <span class="badge badge-secondary">Expired</span>
                        @else
                            <span class="badge badge-secondary">{{ ucfirst($displayStatus) }}</span>
                        @endif
                    </td>
                    <td>
                        @php $displayStatus = $meeting->display_status; @endphp
                        @if($displayStatus === 'approved')
                            <a href="{{ route('video.join', $meeting->room_id) }}" class="btn btn-primary btn-sm">Enter Production Room</a>
                        @elseif($displayStatus === 'pending')
                            <span class="badge badge-light">Awaiting Approval</span>
                        @elseif($displayStatus === 'completed')
                            <span class="text-success">Meeting Done</span>
                        @else
                            <span class="badge badge-light">Link Inactive</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No production calls scheduled.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection