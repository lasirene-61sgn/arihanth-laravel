<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\SecurityTrackingTrait;

class Buyer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SecurityTrackingTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bp_code',
        'dear',
        'business_name',
        'name',
        'mobile',
        'landline',
        'email',
        'business_email',
        'refered_by',
        'more',
        'door_no',
        'shop_no',
        'complex_name',
        'building_name',
        'street_name',
        'area',
        'pincode',
        'city',
        'state',
        'map_location',
        'location_guide',
        // KYC Fields
        'bis_no',
        'bis_attachment',
        'gst_no',
        'gst_attachment',
        'msme_no',
        'msme_attachment',
        'pan_no',
        'pan_attachment',
        'tan_no',
        'tan_attachment',
        'cin_no',
        'cin_attachment',
        'image',
        'aadhar_no',
        'aadhar_attach',
        'bank_name',
        'account_name',
        'account_no',
        'passbook',
        'ifsc_code',
        'branch',
        'bank_city',
        'bank_state',
        'note',
        'password',
        'password_plain',
        'permissions',
        'is_frozen',
        'kyc_status',
        'fcm_token',
        'brand_logo',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'permissions' => 'array',
    ];

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
     * Check if the buyer has a specific permission
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
     * Generate BP code automatically
     */
    /**
     * Generate BP code automatically based on business name
     * Format: B[First Letter of Business Name][001]
     * Example: Google -> BG001
     */
    public static function generateBpCode($businessName)
    {
        // Get the first letter of the business name, plain and uppercase
        $firstLetter = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $businessName), 0, 1));

        // Fallback if no letters found
        if (empty($firstLetter)) {
            $firstLetter = 'A';
        }

        $prefix = 'B' . $firstLetter;

        // Find the last code with this prefix
        $lastBuyer = self::where('bp_code', 'LIKE', $prefix . '%')
            ->orderByRaw('LENGTH(bp_code) DESC') // Ensure we get 010 before 009 if lengths differ (though usually fixed)
            ->orderBy('bp_code', 'desc')
            ->first();

        if (!$lastBuyer) {
            return $prefix . '001';
        }

        // Extract number and increment
        $lastNumber = intval(substr($lastBuyer->bp_code, 2));
        $newNumber = $lastNumber + 1;

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function aadharDetails()
    {
        return $this->hasMany(BuyerAadharDetail::class);
    }

    public function panDetails()
    {
        return $this->hasMany(BuyerPanDetail::class);
    }

    public function bankDetails()
    {
        return $this->hasMany(BuyerBankDetail::class);
    }

    /**
     * Check if the buyer has a specific permission
     */
    // public function hasPermission($permission)
    // {
    //     $permissions = $this->permissions ?? [];
    //     if (is_string($permissions)) {
    //         $permissions = json_decode($permissions, true) ?? [];
    //     }

    //     return in_array($permission, $permissions);
    // }

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
            'stock_order',
            'repairs',
            'favorites',
            'finance',
            'key_user',
            'user_management',
            'meetings',
            'messages',
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
     * Get all available buyer permissions
     */
    public static function getAllPermissions()
    {
        return array_merge(self::getWebPermissions(), self::getApiPermissions());
    }

    public function getFullNameAttribute()
    {
        return $this->name;
    }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'user');
    }

    protected $appends = [
        'image_url',
        'bis_attachment_url',
        'gst_attachment_url',
        'msme_attachment_url',
        'pan_attachment_url',
        'tan_attachment_url',
        'cin_attachment_url',
        'aadhar_attach_url',
        'passbook_url',
        'brand_logo_url'
    ];

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getBisAttachmentUrlAttribute()
    {
        return $this->bis_attachment ? asset('storage/' . $this->bis_attachment) : null;
    }

    public function getGstAttachmentUrlAttribute()
    {
        return $this->gst_attachment ? asset('storage/' . $this->gst_attachment) : null;
    }

    public function getMsmeAttachmentUrlAttribute()
    {
        return $this->msme_attachment ? asset('storage/' . $this->msme_attachment) : null;
    }

    public function getPanAttachmentUrlAttribute()
    {
        return $this->pan_attachment ? asset('storage/' . $this->pan_attachment) : null;
    }

    public function getTanAttachmentUrlAttribute()
    {
        return $this->tan_attachment ? asset('storage/' . $this->tan_attachment) : null;
    }

    public function getCinAttachmentUrlAttribute()
    {
        return $this->cin_attachment ? asset('storage/' . $this->cin_attachment) : null;
    }

    public function getAadharAttachUrlAttribute()
    {
        return $this->aadhar_attach ? asset('storage/' . $this->aadhar_attach) : null;
    }

    public function getPassbookUrlAttribute()
    {
        return $this->passbook ? asset('storage/' . $this->passbook) : null;
    }

    public function getBrandLogoUrlAttribute()
    {
        return $this->brand_logo ? asset('storage/' . $this->brand_logo) : null;
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

    public function hostedMeetings()
    {
        return $this->morphMany(Meeting::class, 'host');
    }
    public function joinedMeetings()
    {
        return $this->morphMany(Meeting::class, 'participant');
    }
}
