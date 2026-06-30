<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignUserAccess extends Model
{
    protected $table = 'design_user_access';

    protected $fillable = [
        'product_id',
        'user_type',
        'user_code',
        'unlocked_until',
    ];

    protected $casts = [
        'unlocked_until' => 'datetime',
    ];

    /**
     * Get the product that this access record belongs to.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

