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
        'notes',
        'weight',
        'repair_details',
        'item_delivered_by',
        'item_received_by',
    ];

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }
}
