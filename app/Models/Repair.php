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
    ];

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function craftsman()
    {
        return $this->belongsTo(Craftman::class, 'allocated_craftsman_code', 'craftman_code');
    }
}
