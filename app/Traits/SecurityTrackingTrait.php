<?php

namespace App\Traits;

trait SecurityTrackingTrait
{
    /**
     * Boot the security tracking trait.
     */
    protected static function bootSecurityTrackingTrait()
    {
        static::saving(function ($model) {
            // Check if password column is dirty (changed)
            if ($model->isDirty('password')) {
                // Increment update count
                $model->password_update_count = ($model->password_update_count ?? 0) + 1;
            }
        });
    }
}
