<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewUpdates extends Model
{
    protected $table = 'new_updates';

    protected $fillable = [
        'newupdates',
        'title',
        'description',
        'duration',
        'media_path',
        'media_type',
        'target_audience',
        'target_buyers',
        'target_craftsmen'
    ];

    protected $casts = [
        'target_buyers' => 'array',
        'target_craftsmen' => 'array',
    ];
}
