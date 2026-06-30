<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftmanBankDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'craftman_id',
        'bank_name',
        'account_holder_name',
        'account_number',
        'ifsc_code',
        'branch',
        'bank_city',
        'bank_state',
        'passbook_image',
    ];

    protected $appends = ['passbook_image_url'];

    public function getPassbookImageUrlAttribute()
    {
        return $this->passbook_image ? asset('storage/' . $this->passbook_image) : null;
    }

    public function craftman()
    {
        return $this->belongsTo(Craftman::class);
    }
}