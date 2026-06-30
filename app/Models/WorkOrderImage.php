<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'image_path',
    ];

    protected $appends = ['image_url'];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            // Check if it's already a full URL or starts with images/ (public)
            if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
                return $this->image_path;
            }
            
            // Work order images are stored in public/images/work-orders or public/uploads/
            if (str_starts_with($this->image_path, 'images/') || str_starts_with($this->image_path, 'uploads/')) {
                return asset($this->image_path);
            }
            
            return asset('storage/' . $this->image_path);
        }
        return null;
    }
}
