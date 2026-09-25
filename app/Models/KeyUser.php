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
     * Get web UI permissions
     */
    public static function getWebPermissions()
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
     * Get API-specific permissions
     */
    public static function getApiPermissions()
    {
        return [
            'wo_create',
            'wo_edit',
            'wo_bulk_allocate',
            'wo_bulk_accept',
            'wo_bulk_reject',
            'wo_bulk_complete',
            'wo_approve',
            'wo_bulk_approve',
            'wo_reallocate',
        ];
    }

            public static function getGroupedApiPermissions()
    {
        return [
            'New Tab' => [
                'new_tab_wo_create',
                'new_tab_wo_edit',
                'new_tab_wo_view',
                'new_tab_wo_accept',
                'new_tab_wo_bulk_accept',
                'new_tab_wo_reject',
                'new_tab_wo_bulk_reject',
                'new_tab_wo_complete',
                'new_tab_wo_bulk_complete',
                'new_tab_wo_approve',
                'new_tab_wo_bulk_approve',
                'new_tab_wo_allocate',
                'new_tab_wo_bulk_allocate',
                'new_tab_wo_reallocate',
            ],
            'Allocated Tab' => [
                'allocated_tab_wo_create',
                'allocated_tab_wo_edit',
                'allocated_tab_wo_view',
                'allocated_tab_wo_accept',
                'allocated_tab_wo_bulk_accept',
                'allocated_tab_wo_reject',
                'allocated_tab_wo_bulk_reject',
                'allocated_tab_wo_complete',
                'allocated_tab_wo_bulk_complete',
                'allocated_tab_wo_approve',
                'allocated_tab_wo_bulk_approve',
                'allocated_tab_wo_allocate',
                'allocated_tab_wo_bulk_allocate',
                'allocated_tab_wo_reallocate',
            ],
            'In Process Tab' => [
                'in_process_tab_wo_create',
                'in_process_tab_wo_edit',
                'in_process_tab_wo_view',
                'in_process_tab_wo_accept',
                'in_process_tab_wo_bulk_accept',
                'in_process_tab_wo_reject',
                'in_process_tab_wo_bulk_reject',
                'in_process_tab_wo_complete',
                'in_process_tab_wo_bulk_complete',
                'in_process_tab_wo_approve',
                'in_process_tab_wo_bulk_approve',
                'in_process_tab_wo_allocate',
                'in_process_tab_wo_bulk_allocate',
                'in_process_tab_wo_reallocate',
            ],
            'For Approval Tab' => [
                'for_approval_tab_wo_create',
                'for_approval_tab_wo_edit',
                'for_approval_tab_wo_view',
                'for_approval_tab_wo_accept',
                'for_approval_tab_wo_bulk_accept',
                'for_approval_tab_wo_reject',
                'for_approval_tab_wo_bulk_reject',
                'for_approval_tab_wo_complete',
                'for_approval_tab_wo_bulk_complete',
                'for_approval_tab_wo_approve',
                'for_approval_tab_wo_bulk_approve',
                'for_approval_tab_wo_allocate',
                'for_approval_tab_wo_bulk_allocate',
                'for_approval_tab_wo_reallocate',
            ],
            'Completed Tab' => [
                'completed_tab_wo_create',
                'completed_tab_wo_edit',
                'completed_tab_wo_view',
                'completed_tab_wo_accept',
                'completed_tab_wo_bulk_accept',
                'completed_tab_wo_reject',
                'completed_tab_wo_bulk_reject',
                'completed_tab_wo_complete',
                'completed_tab_wo_bulk_complete',
                'completed_tab_wo_approve',
                'completed_tab_wo_bulk_approve',
                'completed_tab_wo_allocate',
                'completed_tab_wo_bulk_allocate',
                'completed_tab_wo_reallocate',
            ],
            'Overdue Tab' => [
                'overdue_tab_wo_create',
                'overdue_tab_wo_edit',
                'overdue_tab_wo_view',
                'overdue_tab_wo_accept',
                'overdue_tab_wo_bulk_accept',
                'overdue_tab_wo_reject',
                'overdue_tab_wo_bulk_reject',
                'overdue_tab_wo_complete',
                'overdue_tab_wo_bulk_complete',
                'overdue_tab_wo_approve',
                'overdue_tab_wo_bulk_approve',
                'overdue_tab_wo_allocate',
                'overdue_tab_wo_bulk_allocate',
                'overdue_tab_wo_reallocate',
            ],
            'Rejected Tab' => [
                'rejected_tab_wo_create',
                'rejected_tab_wo_edit',
                'rejected_tab_wo_view',
                'rejected_tab_wo_accept',
                'rejected_tab_wo_bulk_accept',
                'rejected_tab_wo_reject',
                'rejected_tab_wo_bulk_reject',
                'rejected_tab_wo_complete',
                'rejected_tab_wo_bulk_complete',
                'rejected_tab_wo_approve',
                'rejected_tab_wo_bulk_approve',
                'rejected_tab_wo_allocate',
                'rejected_tab_wo_bulk_allocate',
                'rejected_tab_wo_reallocate',
            ],
            'All Orders Tab' => [
                'all_orders_tab_wo_create',
                'all_orders_tab_wo_edit',
                'all_orders_tab_wo_view',
                'all_orders_tab_wo_accept',
                'all_orders_tab_wo_bulk_accept',
                'all_orders_tab_wo_reject',
                'all_orders_tab_wo_bulk_reject',
                'all_orders_tab_wo_complete',
                'all_orders_tab_wo_bulk_complete',
                'all_orders_tab_wo_approve',
                'all_orders_tab_wo_bulk_approve',
                'all_orders_tab_wo_allocate',
                'all_orders_tab_wo_bulk_allocate',
                'all_orders_tab_wo_reallocate',
            ],
        ];
    }

    public static function getDefaultApiPermissions()
    {
        return [
            'new_tab_wo_create', 'new_tab_wo_edit', 'new_tab_wo_allocate', 'new_tab_wo_bulk_allocate', 'new_tab_wo_view',
            'allocated_tab_wo_reallocate', 'allocated_tab_wo_view',
            'for_approval_tab_wo_approve', 'for_approval_tab_wo_bulk_approve', 'for_approval_tab_wo_view',
            'rejected_tab_wo_reallocate', 'rejected_tab_wo_view',
            'in_process_tab_wo_view', 'completed_tab_wo_view', 'all_orders_tab_wo_view',
        ];
    }

    /**
     * Get all available key user permissions
     */
    public static function getAllPermissions()
    {
        return array_merge(self::getWebPermissions(), self::getApiPermissions());
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
