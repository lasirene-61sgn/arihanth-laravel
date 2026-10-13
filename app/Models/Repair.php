<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repair extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'buyer_id', // Updated from bp_code
        'product_name',
        'order_number',
        'repair_or_sample',
        'reference_number',
        'status',
        'craftman_code',
        'notes',
        'weight',
        'repair_details',
        'item_delivered_by',
        'item_received_by',
        'item_returned_by',
        'item_collected_by',
        'craftsman_status',
        'item_sent_by',
        'item_accepted_by',
        'craftsman_voucher_image',
        'craftsman_completion_proof_image',
        'repair_image',
        'completed_at',
    ];

    public function buyer()
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }

    public function craftsman()
    {
        return $this->belongsTo(Craftman::class, 'craftman_code', 'craftman_code');
    }

    public function craftman()
    {
        return $this->belongsTo(Craftman::class, 'craftman_code', 'craftman_code');
    }
}
