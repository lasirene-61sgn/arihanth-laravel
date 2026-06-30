<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'craftsman_id',
        'order_number',
        'status',
        'total_items',
        'notes',
    ];

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function craftsman()
    {
        return $this->belongsTo(Craftman::class, 'craftsman_id');
    }

    public function items()
    {
        return $this->hasMany(StockOrderItem::class);
    }

    protected static $lastGeneratedId = null;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $number = 1;
                $latestOrder = self::latest('id')->first();
                if ($latestOrder && preg_match('/-(\d{4})$/', $latestOrder->order_number, $matches)) {
                    $number = intval($matches[1]) + 1;
                } elseif ($latestOrder) {
                    $number = $latestOrder->id + 1;
                }
                
                $orderNumber = 'LSO-' . now()->format('Y') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
                
                while (self::where('order_number', $orderNumber)->exists()) {
                    $number++;
                    $orderNumber = 'LSO-' . now()->format('Y') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
                }
                
                $order->order_number = $orderNumber;
            }
        });
    }
}
