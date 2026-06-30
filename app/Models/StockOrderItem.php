<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_order_id',
        'product_id',
        'craftsman_id',
        'design_code',
        'category_name',
        'subcategory_name',
        'weight_from',
        'weight_to',
        'size',
        'quantity',
        'grams',
        'item_notes',
        'status',
        'rejection_reason',
        'image_path',
    ];

    public function order()
    {
        return $this->belongsTo(StockOrder::class, 'stock_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function craftsman()
    {
        return $this->belongsTo(Craftman::class, 'craftsman_id');
    }
}
