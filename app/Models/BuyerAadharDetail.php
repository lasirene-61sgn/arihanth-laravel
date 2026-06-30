<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuyerAadharDetail extends Model
{
    protected $fillable = [
        'buyer_id',
        'aadhar_name',
        'aadhar_number',
        'aadhar_image'
    ];
    
    protected $appends = ['aadhar_image_url'];

    public function getAadharImageUrlAttribute()
    {
        return $this->aadhar_image ? asset('storage/' . $this->aadhar_image) : null;
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }
}
