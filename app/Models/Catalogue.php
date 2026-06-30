<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Catalogue extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'catalogue_code',
        'catalogue_name',
        'design_codes',
        'metal_type',
        'category',
        'add_image',
        'add_video',
        'created_by', // Add this field
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'design_codes' => 'array',
    ];

    protected $appends = ['image_url', 'video_url'];

    public function getImageUrlAttribute()
    {
        return $this->add_image ? asset('storage/' . $this->add_image) : null;
    }

    public function getVideoUrlAttribute()
    {
        return $this->add_video ? asset('storage/' . $this->add_video) : null;
    }

    /**
     * Generate catalogue code automatically (CTL0001, CTL0002, etc.)
     */
    public static function generateCatalogueCode()
    {
        $lastCatalogue = self::orderBy('id', 'desc')->first();
        
        if (!$lastCatalogue) {
            return 'CTL0001';
        }

        $lastCatalogueCode = $lastCatalogue->catalogue_code;
        $number = intval(substr($lastCatalogueCode, 3)) + 1;
        return 'CTL' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get the key user that created this catalogue.
     */
    public function creator()
    {
        return $this->belongsTo(KeyUser::class, 'created_by');
    }
    
    /**
     * Scope to exclude catalogues from frozen accounts
     */
    public function scopeNotFromFrozenAccounts($query)
    {
        return $query->whereDoesntHave('creator.buyer', function ($query) {
            $query->where('is_frozen', true);
        });
    }
}