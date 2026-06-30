<?php
$path = "e:/public_html/app/Models/PurchaseOrder.php";
$c = file_get_contents($path);

// Update $fillable
$old_fillable = "        'rejected_items',
    ];";
$new_fillable = "        'rejected_items',
        'craftsman_due_date',
        'created_by',
        'creator_type',
        'allocated_by',
        'approved_by',
    ];";
$c = str_replace($old_fillable, $new_fillable, $c);

// Add casts
$old_casts = "        'due_date' => 'date',
        'items' => 'array',
        'rejected_items' => 'array',
    ];";
$new_casts = "        'due_date' => 'date',
        'craftsman_due_date' => 'date',
        'items' => 'array',
        'rejected_items' => 'array',
    ];";
$c = str_replace($old_casts, $new_casts, $c);

// Add accessors at the end before last closing brace
$accessors = '
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
            } elseif ($this->creator_type === "admin") {
                $processOwner = \App\Models\ProcessOwner::find($this->created_by);
                if ($processOwner) return [
                    "name" => $processOwner->full_name ?? $processOwner->name ?? "Admin",
                    "bp_code" => "N/A",
                    "user_code" => $processOwner->user_code ?? "N/A",
                    "type" => "Admin"
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
}';
$c = preg_replace('/}[^}]*$/', $accessors, $c);
file_put_contents($path, $c);
echo "Updated PurchaseOrder model\n";
