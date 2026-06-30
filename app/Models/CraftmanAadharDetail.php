<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftmanAadharDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'craftman_id',
        'aadhar_name',
        'aadhar_number',
        'aadhar_image',
    ];

    protected $appends = ['aadhar_image_url'];

    public function getAadharImageUrlAttribute()
    {
        return $this->aadhar_image ? asset('storage/' . $this->aadhar_image) : null;
    }

    public function craftman()
    {
        return $this->belongsTo(Craftman::class);
    }
}