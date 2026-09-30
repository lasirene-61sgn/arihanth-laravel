<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\SecurityTrackingTrait;

class KeyUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SecurityTrackingTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'key_users';

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
        'permissions',
        'dob',
        'city',
        'state',
        'country',
        'pincode',
        'aadhar_photo',
        'aadhar_number',
        'is_frozen',
        'fcm_token',
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
        // No default permissions automatically assigned
    }

    /**
     * Generate user code automatically (KU0001, KU0002, etc.)
     */
    public static function generateUserCode()
    {
        $lastKeyUser = self::orderBy('id', 'desc')->first();

        if (!$lastKeyUser) {
            return 'KU0001';
        }

        $lastUserCode = $lastKeyUser->user_code;
        $number = intval(substr($lastUserCode, 2)) + 1;
        return 'KU' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if the key user has a specific permission
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
     * Set permissions for the key user
     */
    public function setPermissions($permissions)
    {
        $this->permissions = json_encode($permissions);
        return $this;
    }

    /**
     * Add a permission to the key user
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
     * Remove a permission from the key user
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
        $permissions = $this->permissions ?? [];
        if (is_string($permissions)) {
            return json_decode($permissions, true) ?? [];
        }
        return is_array($permissions) ? $permissions : [];
    }

    /**
     * Get all available key user permissions
     */
    public static function getAllPermissions()
    {
        return [
            'product',
            'design',
            'catalogue',
            'work_order',
            'user_management',
            'finance'
        ];
    }

    /**
     * Get the buyer that owns this key user
     */
    public function buyer()
    {
        return $this->belongsTo(Buyer::class, 'bp_code', 'bp_code');
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
