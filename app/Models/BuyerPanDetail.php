<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuyerPanDetail extends Model
{
    protected $fillable = [
        'buyer_id',
        'pan_number',
        'pan_image'
    ];
    
    protected $appends = ['pan_image_url'];

    public function getPanImageUrlAttribute()
    {
        return $this->pan_image ? asset('storage/' . $this->pan_image) : null;
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }
}
