<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repair extends Model
{
    protected $fillable = [
        'buyer_id',
        'repair_date',
        'product_name',
        'weight',
        'repair_details',
        'sample_details',
        'item_given_to',
        'image_proof',
        'order_no',
        'repair',
        'ref',
        'notes',
        'status',
        'reject_reason',
        'allocated_craftsman_code',
        'allocation_notes',
        'craftsman_status',
        'created_by',
        'creator_type',
        'creator_user_code',
        'allocated_by',
        'allocated_at',
        'craftsman_accepted_at',
        'craftsman_completed_at',
        'approved_by',
        'approved_at',
        'buyer_accepted_at',
        'due_date',
        'craftsman_staff_id',
        'accepted_by_staff_id',
        'staff_accepted_at',
        'staff_completed_at',
        'item_received_by',
        'item_received_through',
        'item_delivered_by_type',
        'item_delivered_by'
    ];

    protected $appends = ['creator_details', 'approver_details', 'allocator_details'];

    protected $casts = [
        'due_date' => 'date',
        'allocated_at' => 'datetime',
        'craftsman_accepted_at' => 'datetime',
        'craftsman_completed_at' => 'datetime',
        'approved_at' => 'datetime',
        'buyer_accepted_at' => 'datetime',
    ];

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function craftsman()
    {
        return $this->belongsTo(Craftman::class, 'allocated_craftsman_code', 'craftman_code');
    }

    public function craftsmanStaff()
    {
        return $this->belongsTo(CraftsmanStaff::class, 'craftsman_staff_id');
    }

    public function acceptedByStaff()
    {
        return $this->belongsTo(CraftsmanStaff::class, 'accepted_by_staff_id');
    }

    public function getCreatorDetailsAttribute()
    {
        if ($this->creator_type) {
            if ($this->creator_type === 'key_user') {
                $keyUser = \App\Models\KeyUser::where('user_code', $this->creator_user_code)->first() ?? \App\Models\KeyUser::find($this->created_by);
                if ($keyUser) return [
                    'name' => $keyUser->full_name ?? $keyUser->name ?? 'Key User',
                    'bp_code' => $keyUser->bp_code,
                    'user_code' => $keyUser->user_code ?? 'N/A',
                    'type' => 'Key User'
                ];
            } elseif ($this->creator_type === 'user') {
                $user = \App\Models\User::where('user_code', $this->creator_user_code)->first() ?? \App\Models\User::find($this->created_by);
                if ($user) return [
                    'name' => $user->full_name ?? $user->name ?? 'User',
                    'bp_code' => $user->bp_code ?? 'N/A',
                    'user_code' => $user->user_code ?? 'N/A',
                    'type' => 'User'
                ];
            } elseif ($this->creator_type === 'buyer') {
                $buyer = \App\Models\Buyer::find($this->created_by);
                if ($buyer) return [
                    'name' => $buyer->business_name ?? $buyer->name ?? 'Buyer',
                    'bp_code' => $buyer->bp_code,
                    'user_code' => 'N/A',
                    'type' => 'Buyer'
                ];
            } elseif (in_array($this->creator_type, ['admin', 'super_admin', 'superadmin'])) {
                $processOwner = \App\Models\ProcessOwner::find($this->created_by);
                if ($processOwner) return [
                    'name' => $processOwner->full_name ?? $processOwner->name ?? 'Admin',
                    'bp_code' => 'N/A',
                    'user_code' => $processOwner->user_code ?? 'N/A',
                    'type' => $this->creator_type === 'super_admin' ? 'Super Admin' : 'Admin'
                ];
            }
        }

        // Fallback for old records
        if (is_null($this->created_by)) {
            return [
                'name' => 'Unknown User',
                'bp_code' => 'N/A',
                'user_code' => 'N/A',
                'type' => 'Unknown'
            ];
        }

        $buyer = \App\Models\Buyer::find($this->created_by);
        if ($buyer) {
            return [
                'name' => $buyer->business_name ?? $buyer->name ?? 'Buyer',
                'bp_code' => $buyer->bp_code,
                'user_code' => 'N/A',
                'type' => 'Buyer'
            ];
        }

        return [
            'name' => 'Unknown User',
            'bp_code' => 'N/A',
            'user_code' => 'N/A',
            'type' => 'Unknown'
        ];
    }

    public function getApproverDetailsAttribute()
    {
        if (is_null($this->approved_by)) {
            return [
                'name' => 'N/A',
                'user_code' => 'N/A',
                'type' => 'N/A'
            ];
        }

        $processOwner = \App\Models\ProcessOwner::find($this->approved_by);
        if ($processOwner) {
            return [
                'name' => $processOwner->full_name ?? $processOwner->name ?? 'Admin',
                'user_code' => $processOwner->user_code ?? 'N/A',
                'type' => $processOwner->isSuperAdmin() ? 'Super Admin' : 'Admin'
            ];
        }

        return [
            'name' => 'Unknown User',
            'user_code' => 'N/A',
            'type' => 'Unknown'
        ];
    }

    public function getAllocatorDetailsAttribute()
    {
        if (is_null($this->allocated_by)) {
            // Fallback for legacy allocations
            if (!in_array($this->status, ['new', 'Pending', 'Rejected_by_Admin'])) {
                return [
                    'name' => 'Admin / Super Admin',
                    'user_code' => 'N/A',
                    'type' => 'Legacy'
                ];
            }

            return [
                'name' => 'N/A',
                'user_code' => 'N/A',
                'type' => 'N/A'
            ];
        }

        $processOwner = \App\Models\ProcessOwner::find($this->allocated_by);
        if ($processOwner) {
            return [
                'name' => $processOwner->full_name ?? $processOwner->name ?? 'Admin',
                'user_code' => $processOwner->user_code ?? 'N/A',
                'type' => $processOwner->isSuperAdmin() ? 'Super Admin' : 'Admin'
            ];
        }

        return [
            'name' => 'Unknown User',
            'user_code' => 'N/A',
            'type' => 'Unknown'
        ];
    }
}
