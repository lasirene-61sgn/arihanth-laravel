<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationRequest extends Model
{
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'business_name',
        'address',
        'city',
        'state',
        'pincode',
        'gst_no',
        'password',
        'status',
        'admin_notes',
    ];
}
