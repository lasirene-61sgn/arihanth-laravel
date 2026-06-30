<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'authenticatable_type',
        'authenticatable_id',
        'ip_address',
        'country',
        'location',
        'user_agent',
    ];

    public function authenticatable()
    {
        return $this->morphTo();
    }
}
