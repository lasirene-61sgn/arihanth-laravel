<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;
use App\Traits\SecurityTrackingTrait;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SecurityTrackingTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_code',
        'bp_code',
        'name',
        'full_name',
        'email',
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
        'profile_picture',
        'aadhar_photo',
        'aadhar_number',
        'created_by',
        'is_frozen',
        'fcm_token',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'email_verified_at' => 'datetime',
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
        // No default permissions automatically assigned
    }

    /**
     * Generate user code automatically (UR0001, UR0002, etc.)
     */
    public static function generateUserCode()
    {
        $lastUser = self::orderBy('id', 'desc')->first();

        if (!$lastUser || !$lastUser->user_code) {
            return 'UR0001';
        }

        $lastUserCode = $lastUser->user_code;
        $number = intval(substr($lastUserCode, 2)) + 1;
        return 'UR' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the user that created this user.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the users created by this user.
     */
    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * Get the buyer associated with this user.
     */
    public function buyer()
    {
        return $this->belongsTo(Buyer::class, 'bp_code', 'bp_code');
    }

    /**
     * Check if the user has a specific permission
     */
    public function hasPermission($permission)
    {
        $permissions = $this->permissions ?? [];
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?? [];
        }

        return in_array($permission, $permissions);
    }

    /**
     * Set permissions for the user
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * Add a permission to the user
     */
    public function addPermission($permission)
    {
        $permissions = $this->getPermissionsArray();
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
        }
        return $this;
    }

    /**
     * Remove a permission from the user
     */
    public function removePermission($permission)
    {
        $permissions = $this->getPermissionsArray();
        $permissions = array_filter($permissions, function ($perm) use ($permission) {
            return $perm !== $permission;
        });
        $this->permissions = array_values($permissions);
        return $this;
    }

    /**
     * Get permissions as an array
     */
    public function getPermissionsArray()
    {
        $permissions = $this->permissions ?? [];
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?? [];
        }
        return is_array($permissions) ? $permissions : [];
    }

    /**
     * Get all available user permissions
     */
    public static function getAllPermissions()
    {
        return [
            'work_order',
            'product',
            'design',
            'catalogue',
            'profile_management',
            'reports',
            'settings',
            'freeze_account'
        ];
    }

    protected $appends = ['business_name'];

    public function getBusinessNameAttribute()
    {
        return $this->buyer->business_name ?? null;
    }

    public function getProfilePictureAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    public function getAadharPhotoAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
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
