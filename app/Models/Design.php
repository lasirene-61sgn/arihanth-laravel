<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Design extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'design_code',
        'design_type',
        'image',
        'category',
        'sub_category',
        'design_name',
        'select_product',
        'select_order',
        'weight',
        'delivery_date',
        'details',
        'product_id',
        'created_by', // Add this field
        'design_status',
        'qr_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'select_product' => 'array',
        'select_order' => 'array',
        'delivery_date' => 'date',
        'scheduled_at' => 'datetime',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    /**
     * Generate design code automatically (DS0001, DS0002, etc.)
     */
    public static function generateDesignCode()
    {
        // Get all existing design codes that start with 'DS'
        $existingCodes = self::whereNotNull('design_code')
            ->where('design_code', 'LIKE', 'DS%')
            ->pluck('design_code')
            ->toArray();
        
        $counter = 1;
        while (true) {
            $newCode = 'DS' . str_pad($counter, 4, '0', STR_PAD_LEFT);
            if (!in_array($newCode, $existingCodes)) {
                return $newCode;
            }
            $counter++;
        }
    }
    
    /**
     * Get the product that owns this design.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Get the key user that created this design.
     */
    public function creator()
    {
        return $this->belongsTo(KeyUser::class, 'created_by');
    }
    
    /**
     * Scope to exclude designs from frozen accounts
     */
    public function scopeNotFromFrozenAccounts($query)
    {
        return $query->whereDoesntHave('product.buyer', function ($query) {
            $query->where('is_frozen', true);
        })->whereDoesntHave('product.craftsman', function ($query) {
            $query->where('is_frozen', true);
        });
    }
}