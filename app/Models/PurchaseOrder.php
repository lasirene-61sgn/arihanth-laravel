<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_order_code',
        'due_date',
        'notes',
        'items',
        'status',
        'allocated_craftsman_code',
        'craftsman_status',
        'rejected_items',
        'craftsman_due_date',
        'created_by',
        'creator_type',
        'allocated_by',
        'allocated_at',
        'approved_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
        'craftsman_due_date' => 'date',
        'items' => 'array',
        'rejected_items' => 'array',
    ];

    protected $appends = ['items_with_image_urls', 'rejected_items_with_image_urls'];

    /**
     * Generate purchase order code automatically (PO0001, PO0002, etc.)
     */
    public static function generatePurchaseOrderCode()
    {
        $lastPurchaseOrder = self::orderBy('id', 'desc')->first();
        
        $number = 1;
        if ($lastPurchaseOrder && preg_match('/^PA(\d+)/', $lastPurchaseOrder->purchase_order_code, $matches)) {
            $number = intval($matches[1]) + 1;
        } else if ($lastPurchaseOrder) {
            $number = intval(substr($lastPurchaseOrder->purchase_order_code, 2)) + 1;
        }

        $code = 'PA' . str_pad($number, 4, '0', STR_PAD_LEFT);
        
        // Ensure uniqueness
        while (self::where('purchase_order_code', $code)->exists()) {
            $number++;
            $code = 'PA' . str_pad($number, 4, '0', STR_PAD_LEFT);
        }
        
        return $code;
    }

    /**
     * Get the craftsman associated with this purchase order
     */
    public function craftsman()
    {
        return $this->belongsTo(Craftman::class, 'allocated_craftsman_code', 'craftman_code');
    }

    /**
     * Check if purchase order is allocated to a craftsman
     */
    public function isAllocated()
    {
        return !is_null($this->allocated_craftsman_code);
    }

    /**
     * Check if craftsman has accepted the purchase order
     */
    public function isAcceptedByCraftsman()
    {
        return $this->craftsman_status === 'accepted';
    }

    /**
     * Check if craftsman has rejected the purchase order
     */
    public function isRejectedByCraftsman()
    {
        return $this->craftsman_status === 'rejected';
    }

    /**
     * Check if purchase order is in process by craftsman
     */
    public function isInProcess()
    {
        return $this->craftsman_status === 'in_process';
    }

    /**
     * Check if purchase order is completed by craftsman
     */
    public function isCompletedByCraftsman()
    {
        return $this->craftsman_status === 'completed';
    }

    /**
     * Check if purchase order is approved by super admin
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if purchase order is for approval
     */
    public function isForApproval()
    {
        return $this->status === 'for_approval';
    }

    /**
     * Check if purchase order has rejected items
     */
    public function hasRejectedItems()
    {
        return !empty($this->rejected_items) && count($this->rejected_items) > 0;
    }

    /**
     * Check if purchase order is overdue
     */
    public function isOverdue()
    {
        if ($this->status === 'completed' || $this->craftsman_status === 'rejected') {
            return false;
        }

        if (!$this->due_date) {
            return false;
        }

        $now = now();
        $dueDate = \Carbon\Carbon::parse($this->due_date);

        if ($now->toDateString() > $dueDate->toDateString()) {
            return true;
        }

        if ($now->toDateString() == $dueDate->toDateString() && $now->hour >= 12) {
            return true;
        }

        return false;
    }

    public function getItemsWithImageUrlsAttribute()
    {
        $items = $this->items ?? [];
        if (!is_array($items)) return [];
        
        return array_map(function ($item) {
            if (isset($item['image']) && $item['image']) {
                $item['image_url'] = filter_var($item['image'], FILTER_VALIDATE_URL) ? $item['image'] : asset($item['image']);
            } else {
                $item['image_url'] = null;
            }
            return $item;
        }, $items);
    }

    public function getRejectedItemsWithImageUrlsAttribute()
    {
        $items = $this->rejected_items ?? [];
        if (!is_array($items)) return [];
        
        return array_map(function ($item) {
            if (isset($item['image']) && $item['image']) {
                $item['image_url'] = filter_var($item['image'], FILTER_VALIDATE_URL) ? $item['image'] : asset($item['image']);
            } else {
                $item['image_url'] = null;
            }
            return $item;
        }, $items);
    }

    public function getCreatorDetailsAttribute()
    {
        if ($this->creator_type && $this->created_by) {
            if ($this->creator_type === "key_user") {
                $keyUser = \App\Models\KeyUser::find($this->created_by);
                if ($keyUser) return [
                    "name" => $keyUser->full_name ?? $keyUser->name ?? "Key User",
                    "bp_code" => $keyUser->bp_code ?? "N/A",
                    "user_code" => $keyUser->user_code ?? "N/A",
                    "type" => "Key User"
                ];
            } elseif ($this->creator_type === "user") {
                $user = \App\Models\User::find($this->created_by);
                if ($user) return [
                    "name" => $user->full_name ?? $user->name ?? "User",
                    "bp_code" => $user->bp_code ?? "N/A",
                    "user_code" => $user->user_code ?? "N/A",
                    "type" => "User"
                ];
            } elseif ($this->creator_type === "buyer") {
                $buyer = \App\Models\Buyer::find($this->created_by);
                if ($buyer) return [
                    "name" => $buyer->business_name ?? $buyer->name ?? "Buyer",
                    "bp_code" => $buyer->bp_code ?? "N/A",
                    "user_code" => null,
                    "type" => "Buyer"
                ];
            } elseif ($this->creator_type === "admin" || $this->creator_type === "super_admin") {
                $processOwner = \App\Models\ProcessOwner::find($this->created_by);
                if ($processOwner) return [
                    "name" => $processOwner->full_name ?? $processOwner->name ?? "Admin",
                    "bp_code" => "N/A",
                    "user_code" => $processOwner->user_code ?? "N/A",
                    "type" => $processOwner->isSuperAdmin() ? "Super Admin" : "Admin"
                ];
            }
        }
        
        return [
            "name" => "System",
            "bp_code" => "N/A",
            "user_code" => "N/A",
            "type" => "System"
        ];
    }

    public function getApproverDetailsAttribute()
    {
        if (is_null($this->approved_by)) {
            return [
                "name" => "N/A",
                "user_code" => "N/A",
                "type" => "N/A"
            ];
        }

        $processOwner = \App\Models\ProcessOwner::find($this->approved_by);
        if ($processOwner) {
            return [
                "name" => $processOwner->full_name ?? $processOwner->name ?? "Admin",
                "user_code" => $processOwner->user_code ?? "N/A",
                "type" => $processOwner->isSuperAdmin() ? "Super Admin" : "Admin"
            ];
        }

        return [
            "name" => "Unknown User",
            "user_code" => "N/A",
            "type" => "Unknown"
        ];
    }

    public function getAllocatorDetailsAttribute()
    {
        if (is_null($this->allocated_by)) {
            return [
                "name" => "N/A",
                "user_code" => "N/A",
                "type" => "N/A"
            ];
        }

        $processOwner = \App\Models\ProcessOwner::find($this->allocated_by);
        if ($processOwner) {
            return [
                "name" => $processOwner->full_name ?? $processOwner->name ?? "Admin",
                "user_code" => $processOwner->user_code ?? "N/A",
                "type" => $processOwner->isSuperAdmin() ? "Super Admin" : "Admin"
            ];
        }

        return [
            "name" => "Unknown User",
            "user_code" => "N/A",
            "type" => "Unknown"
        ];
    }
}