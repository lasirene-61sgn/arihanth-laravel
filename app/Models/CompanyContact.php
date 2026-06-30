<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'data',
        'is_active',
    ];

    protected $casts = [
        'data' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get types of contacts allowed.
     */
    public static function getTypes()
    {
        return [
            'mobile' => 'Mobile Number',
            'centrix' => 'Centrix Number',
            'bank' => 'Bank Details',
            'location' => 'Location',
            'cin' => 'CIN Number',
            'gst' => 'GST Number',
            'hallmark' => 'Hallmark Number',
            'email' => 'Email ID',
        ];
    }
}
