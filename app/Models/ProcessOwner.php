<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\SecurityTrackingTrait;

class ProcessOwner extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SecurityTrackingTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'process_owners';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'profile_picture',
        'user_code',
        'bp_code',
        'full_name',
        'email_id',
        'mobile_no',
        'password',
        'password_plain',
        'status',
        'dob',
        'city',
        'state',
        'country',
        'pincode',
        'aadhar_photo',
        'aadhar_number',
        'role', // Added role field to distinguish between process owner and super admin
        'permissions', // Added permissions field for admin access control
        'is_frozen',
        'dear',
        'fcm_token',
        'category',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'dob' => 'date',
            'permissions' => 'array',
        ];
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($admin) {
            if (empty($admin->permissions)) {
                $admin->permissions = ['work_order', 'product', 'design', 'catalogue'];
            }
        });
    }

    /**
     * Check if the process owner is a super admin
     */
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if the process owner has a specific permission
     */
    public function hasPermission($permission)
    {
        if ($this->isSuperAdmin()) {
            return true; // Super admins have all permissions
        }

        // Default permissions granted to all users
        $defaults = ['work_order', 'product', 'design', 'catalogue'];
        if (in_array($permission, $defaults)) {
            return true;
        }

        $permissions = $this->permissions ?? [];
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?? [];
        }

        return in_array($permission, $permissions);
    }

    /**
     * Set permissions for the admin
     */
    public function setPermissions($permissions)
    {
        $this->permissions = json_encode($permissions);
        return $this;
    }

    /**
     * Add a permission to the admin
     */
    public function addPermission($permission)
    {
        $permissions = $this->getPermissionsArray();
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->setPermissions($permissions);
        }
        return $this;
    }

    /**
     * Remove a permission from the admin
     */
    public function removePermission($permission)
    {
        $permissions = $this->getPermissionsArray();
        $permissions = array_diff($permissions, [$permission]);
        $this->setPermissions(array_values($permissions));
        return $this;
    }

    /**
     * Get permissions as array
     */
    public function getPermissionsArray()
    {
        if ($this->isSuperAdmin()) {
            return $this->getAllPermissions();
        }

        $defaults = ['work_order', 'product', 'design', 'catalogue'];
        $permissions = $this->permissions ?? [];
        if (is_string($permissions)) {
            return json_decode($permissions, true) ?? [];
        }
        $perms = is_array($permissions) ? $permissions : [];
        return array_unique(array_merge($defaults, $perms));
    }

    /**
     * Get all available permissions
     */
    public static function getAllPermissions()
    {
        return [
            'business_partner',
            'key_user_management',
            'user_management',
            'work_order',
            'purchase_order',
            'product',
            'design',
            'catalogue',
            'kyc_pending',
            'freeze_account',
            'finance',
            'meetings',
            'messages',
        ];
    }

    /**
     * Route notifications for the FCM channel.
     *
     * @return string|null
     */
    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }
}
