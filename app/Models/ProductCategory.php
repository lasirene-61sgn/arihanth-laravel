<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'has_hook',
        'has_enamel',
        'has_rodium',
        'has_open_close',
        'has_stone',
    ];

    protected $casts = [
        'has_hook' => 'boolean',
        'has_enamel' => 'boolean',
        'has_rodium' => 'boolean',
        'has_open_close' => 'boolean',
        'has_stone' => 'boolean',
    ];

    public function subcategories()
    {
        return $this->hasMany(ProductSubcategory::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'product_category_id');
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class, 'product_category_id');
    }
}
