<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CraftsmanStaff extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'craftsman_staff';

    protected $fillable = [
        'craftsman_id',
        'staff_code',
        'name',
        'email',
        'mobile',
        'password',
        'password_plain',
        'aadhar_number',
        'image',
        'aadhar_image',
        'is_active',
        'permissions',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    public function craftsman()
    {
        return $this->belongsTo(Craftman::class, 'craftsman_id');
    }

    public function getPermissionsArray(): array
    {
        if (is_array($this->permissions)) {
            return $this->permissions;
        }

        if (is_string($this->permissions)) {
            return json_decode($this->permissions, true) ?: [];
        }

        return [];
    }

    public function hasPermission($permission)
    {
        $perms = $this->permissions ?? [];
        return in_array($permission, $perms);
    }
}
