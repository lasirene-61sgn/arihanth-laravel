<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftmanPanDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'craftman_id',
        'pan_number',
        'pan_image',
    ];

    protected $appends = ['pan_image_url'];

    public function getPanImageUrlAttribute()
    {
        return $this->pan_image ? asset('storage/' . $this->pan_image) : null;
    }

    public function craftman()
    {
        return $this->belongsTo(Craftman::class);
    }
}