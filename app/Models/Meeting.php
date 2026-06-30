<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Meeting extends Model
{
    protected $fillable = [
        'room_id',
        'host_id',
        'host_type',
        'participant_id',
        'participant_type',
        'scheduled_at',
        'duration_minutes',
        'started_at',
        'ended_at',
        'status',
    ];

    protected static function boot(){
        parent::boot();
        static::creating(function ($meeting){
            $meeting->room_id = (string) Str::uuid();
        });
    }

    public function host() : MorphTo{
        return $this->morphTo();
    }

    public function participant() : MorphTo{
        return $this->morphTo();
    }

    /**
     * Get the dynamic status based on current time and duration.
     */
    public function getDisplayStatusAttribute()
    {
        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        $scheduledAt = \Carbon\Carbon::parse($this->scheduled_at);
        $expiresAt = $scheduledAt->copy()->addMinutes($this->duration_minutes);

        if (now()->gt($expiresAt)) {
            if ($this->status === 'approved' || $this->started_at) {
                return 'completed';
            }
            if ($this->status === 'pending') {
                return 'expired';
            }
        }

        return $this->status;
    }
}
