<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftmanWorker extends Model
{
    use HasFactory;

    protected $fillable = [
        'craftman_id',
        'worker_name',
        'worker_number',
        'worker_image',
    ];

    protected $appends = ['worker_image_url'];

    public function getWorkerImageUrlAttribute()
    {
        return $this->worker_image ? asset('storage/' . $this->worker_image) : null;
    }

    /**
     * Get the craftsman that owns the worker.
     */
    public function craftman()
    {
        return $this->belongsTo(Craftman::class);
    }
}
