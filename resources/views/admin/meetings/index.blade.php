@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h3>My Jewelry Consultations</h3>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.meetings.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <label>Meeting With</label>
                        <select name="participant_type" id="participant_type" class="form-control">
                            <option value="App\Models\Buyer">Buyer</option>
                            <option value="App\Models\Craftman">Craftsman</option>
                            <option value="App\Models\ProcessOwner">Admin/Superadmin</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Participant ID</label>
                        <select name="participant_id" id="participant_id" class="form-control" required>
                            <option value="">Select Participant</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control" required>
                        <input type="hidden" name="duration_minutes" value="45">
                    </div>
                </div>
                <button type="submit" class="btn btn-gold mt-3" style="background-color: #D4AF37; color: white;">
                    Schedule Viewing
                </button>
            </form>
        </div>
    </div>

    <div class="list-group">
        @foreach($meetings as $meeting)
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                @php
                    $isHost = ($meeting->host_id == Auth::id() && $meeting->host_type == get_class(Auth::user()));
                    $partner = $isHost ? $meeting->participant : $meeting->host;
                    $partnerType = $isHost ? $meeting->participant_type : $meeting->host_type;
                @endphp
                <strong>Meeting with {{ $partner->full_name ?? $partner->name ?? class_basename($partnerType) }}</strong><br>
                <small class="text-muted">Time: {{ $meeting->scheduled_at }} ({{ $meeting->duration_minutes }} mins)</small>
                @if($meeting->status === 'pending')
                    <br><span class="badge badge-warning">Pending Approval</span>
                @elseif($meeting->status === 'approved')
                    <br><span class="badge badge-info">Confirmed</span>
                @elseif($meeting->status === 'cancelled')
                    <br><span class="badge badge-danger">Cancelled</span>
                @endif
            </div>
            
            <div>
                @if($meeting->status === 'pending')
                    <form action="{{ route('admin.meetings.approve', $meeting) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                    </form>
                    <form action="{{ route('admin.meetings.cancel', $meeting) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">Decline</button>
                    </form>
                @else
                    @if($meeting->status === 'approved')
                        <a href="{{ route('video.join', $meeting->room_id) }}" class="btn btn-success">JOIN LIVE</a>
                    @endif
                @endif
            </div>
        </div>
        @endforeach
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