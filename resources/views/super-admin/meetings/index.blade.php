@extends('super-admin.layouts.app')

@section('content')
<div class="container">
    <h2>Jewelry Consultation Master Schedule</h2>

    <!-- Schedule New Meeting Form -->
    <div class="card mb-4">
        <div class="card-header">Schedule New Video Call</div>
        <div class="card-body">
            <form action="{{ route('super-admin.meetings.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <label>Select Participant Type</label>
                        <select name="participant_type" id="participant_type" class="form-control" required>
                            <option value="App\Models\Buyer">Buyer</option>
                            <option value="App\Models\Craftman">Craftsman</option>
                            <option value="App\Models\ProcessOwner">Admin/Superadmin</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Participant ID</label>
                        <select name="participant_id" id="participant_id" class="form-control" required>
                            <option value="">Select Participant</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Schedule Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label>Duration (Mins)</label>
                        <input type="number" name="duration_minutes" class="form-control" value="30" required>
                    </div>
                    <div class="col-md-1">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- All Meetings Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th><input type="checkbox" id="select-all"></th>
                <th>S.No</th>
                <th>Host (Admin)</th>
                <th>Participant</th>
                <th>Scheduled At</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($meetings as $meeting)
            <tr>
                <td><input type="checkbox" class="meeting-checkbox" value="{{ $meeting->id }}"></td>
                <td>{{ ($meetings->currentPage() - 1) * $meetings->perPage() + $loop->iteration }}</td>
                <td>
                    {{ $meeting->host->business_name ?? $meeting->host->name ?? 'Admin' }}
                    @if(isset($meeting->host->bp_code))
                        ({{ $meeting->host->bp_code }})
                    @elseif(isset($meeting->host->user_code))
                        ({{ $meeting->host->user_code }})
                    @endif
                </td>
                <td>
                    {{ $meeting->participant->business_name ?? $meeting->participant->name ?? 'User '.$meeting->participant_id }}
                    @if(isset($meeting->participant->bp_code))
                        ({{ $meeting->participant->bp_code }})
                    @elseif(isset($meeting->participant->craftman_code))
                        ({{ $meeting->participant->craftman_code }})
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($meeting->scheduled_at)->format('h:i A d-m-Y') }}</td>
                <td>
                    @php $displayStatus = $meeting->display_status; @endphp
                    @if($displayStatus === 'pending')
                        <span class="badge bg-warning text-dark">Pending Approval</span>
                    @elseif($displayStatus === 'approved')
                        <span class="badge bg-info text-white">Confirmed</span>
                    @elseif($displayStatus === 'completed')
                        <span class="badge bg-success">Completed</span>
                    @elseif($displayStatus === 'cancelled')
                        <span class="badge bg-danger">Cancelled</span>
                    @elseif($displayStatus === 'expired')
                        <span class="badge bg-secondary">Expired</span>
                    @else
                        <span class="badge bg-secondary">{{ $displayStatus }}</span>
                    @endif
                </td>
                <td>
                    @if($meeting->status === 'pending')
                        <form action="{{ route('super-admin.meetings.approve', $meeting) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                        </form>
                        <form action="{{ route('super-admin.meetings.cancel', $meeting) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger">Decline</button>
                        </form>
                    @elseif($meeting->status === 'approved')
                        <a href="{{ route('video.join', $meeting->room_id) }}" class="btn btn-sm btn-success">Join Room</a>
                        <form action="{{ route('super-admin.meetings.cancel', $meeting) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                        </form>
                    @endif

                    <form action="{{ route('super-admin.meetings.destroy', $meeting) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this meeting?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination Links -->
    <div class="d-flex justify-content-center">
        {{ $meetings->links() }}
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const participantType = document.getElementById('participant_type');
        const participantId = document.getElementById('participant_id');

        const buyers = @json($buyers);
        const craftsmen = @json($craftsmen);
        const processOwners = @json($processOwners);

        function updateParticipants() {
            const type = participantType.value;
            participantId.innerHTML = '<option value="">Select Participant</option>';

            let participants = [];
            if (type === 'App\\Models\\Buyer') {
                participants = Object.values(buyers || {});
            } else if (type === 'App\\Models\\Craftman') {
                participants = Object.values(craftsmen || {});
            } else if (type === 'App\\Models\\ProcessOwner') {
                participants = Object.values(processOwners || {});
            }

            participants.forEach(function(participant) {
                const option = document.createElement('option');
                option.value = participant.id;
                // Try full_name first, then name, then company_name, then ID
                const name = participant.full_name || participant.name || participant.company_name || participant.business_name || ('ID: ' + participant.id);
                const code = participant.bp_code || participant.craftman_code || participant.user_code || '';
                option.textContent = code ? `${name} (${code})` : name;
                participantId.appendChild(option);
            });
        }

        participantType.addEventListener('change', updateParticipants);
        
        // Initial populate
        updateParticipants();
    });
</script>
@endsection