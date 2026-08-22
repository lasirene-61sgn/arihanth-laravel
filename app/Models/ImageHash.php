<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageHash extends Model
{
    use HasFactory;

    protected $fillable = ['hashable_id', 'hashable_type', 'hash', 'file_path'];

    public function hashable()
    {
        return $this->morphTo();
    }
}
