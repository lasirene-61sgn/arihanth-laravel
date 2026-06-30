<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_type',
        'product_id',
    ];

    /**
     * Get the product (design) that was favorited.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the user who favorited the design (Buyer or Craftsman).
     */
    public function user()
    {
        return $this->morphTo('user', 'user_type', 'user_id');
    }

    /**
     * Define the morph map for 'user_type'
     */
    protected static function booted()
    {
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'buyer' => \App\Models\Buyer::class,
            'craftsman' => \App\Models\Craftman::class,
        ]);
    }
}
