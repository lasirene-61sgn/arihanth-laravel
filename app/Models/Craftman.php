<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;
use App\Traits\SecurityTrackingTrait;

class Craftman extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SecurityTrackingTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'craftman_code',
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
        'password', // Add password field for authentication
        'password_plain',
        'permissions',
        'is_frozen',
        'kyc_status', // pending, approved, rejected
        'cin_no',
        'cin_attachment',
        'fcm_token',
        'brand_logo',
    ];

    /**
     * Route notifications for the FCM channel.
     *
     * @return string|null
     */
    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'password_plain',
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
     * Generate Craftman code automatically
     */
    public static function generateCraftmanCode()
    {
        $lastCraftman = self::orderBy('id', 'desc')->first();

        if (!$lastCraftman) {
            return 'CA001';
        }

        $lastCraftmanCode = $lastCraftman->craftman_code;
        $number = intval(substr($lastCraftmanCode, 2)) + 1;
        return 'CA' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get work orders allocated to this craftsman
     */
    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class, 'allocated_craftsman_bp_code', 'craftman_code');
    }

    // Multiple entry relationships
    public function aadharDetails()
    {
        return $this->hasMany(CraftmanAadharDetail::class);
    }

    public function panDetails()
    {
        return $this->hasMany(CraftmanPanDetail::class);
    }

    public function bankDetails()
    {
        return $this->hasMany(CraftmanBankDetail::class);
    }

    public function workers()
    {
        return $this->hasMany(CraftmanWorker::class);
    }

    /**
     * Check if the craftman has a specific permission
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
     * Set permissions for the craftman
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * Add a permission to the craftman
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
     * Remove a permission from the craftman
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
     * Get web UI permissions
     */
    public static function getWebPermissions()
    {
        return [
            'dashboard',
            'product',
            'design',
            'design_accept',
            'design_reject',
            'catalogue',
            'purchase_order',
            'work_order',
            'stock_order',
            'repair',
            'finance',
            'craftsman_creation',
            'craftsman_staff',
            'favorites',
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
            'wo_view',
            'wo_accept',
            'wo_reject',
            'wo_complete',
            'wo_bulk_accept',
            'wo_bulk_reject',
            'wo_bulk_complete',
            'edit_workorder',
            'po_view',
            'po_accept',
            'po_reject',
            'po_complete',
            'po_bulk_accept',
            'po_bulk_reject',
            'po_bulk_complete',
        ];
    }

            public static function getGroupedApiPermissions()
    {
        return [
            'Work Orders - New Tab' => [
                'new_tab_wo_view',
                'new_tab_po_view',
            ],
            'Work Orders - Allocated Tab' => [
                'allocated_tab_wo_view',
                'allocated_tab_po_view',
                'allocated_tab_wo_accept',
                'allocated_tab_po_accept',
                'allocated_tab_wo_bulk_accept',
                'allocated_tab_po_bulk_accept',
                'allocated_tab_wo_reject',
                'allocated_tab_po_reject',
                'allocated_tab_wo_bulk_reject',
                'allocated_tab_po_bulk_reject',
            ],
            'Work Orders - In Process Tab' => [
                'in_process_tab_wo_view',
                'in_process_tab_po_view',
                'in_process_tab_wo_complete',
                'in_process_tab_po_complete',
                'in_process_tab_wo_bulk_complete',
                'in_process_tab_po_bulk_complete',
            ],
            'Work Orders - For Approval Tab' => [
                'for_approval_tab_wo_view',
                'for_approval_tab_po_view',
            ],
            'Work Orders - Completed Tab' => [
                'completed_tab_wo_view',
                'completed_tab_po_view',
            ],
            'Work Orders - Overdue Tab' => [
                'overdue_tab_wo_view',
                'overdue_tab_po_view',
                'overdue_tab_wo_accept',
                'overdue_tab_po_accept',
                'overdue_tab_wo_bulk_accept',
                'overdue_tab_po_bulk_accept',
                'overdue_tab_wo_reject',
                'overdue_tab_po_reject',
                'overdue_tab_wo_bulk_reject',
                'overdue_tab_po_bulk_reject',
                'overdue_tab_wo_complete',
                'overdue_tab_po_complete',
                'overdue_tab_wo_bulk_complete',
                'overdue_tab_po_bulk_complete',
            ],
            'Work Orders - Rejected Tab' => [
                'rejected_tab_wo_view',
                'rejected_tab_po_view',
            ],
            'Work Orders - All Orders Tab' => [
                'all_orders_tab_wo_view',
                'all_orders_tab_po_view',
            ],
            'Purchase Orders - New Tab' => [
                'new_tab_po_view',
            ],
            'Purchase Orders - Allocated Tab' => [
                'allocated_tab_po_view',
                'allocated_tab_po_accept',
                'allocated_tab_po_bulk_accept',
                'allocated_tab_po_reject',
                'allocated_tab_po_bulk_reject',
            ],
            'Purchase Orders - In Process Tab' => [
                'in_process_tab_po_view',
                'in_process_tab_po_complete',
                'in_process_tab_po_bulk_complete',
            ],
            'Purchase Orders - For Approval Tab' => [
                'for_approval_tab_po_view',
            ],
            'Purchase Orders - Completed Tab' => [
                'completed_tab_po_view',
            ],
            'Purchase Orders - Overdue Tab' => [
                'overdue_tab_po_view',
                'overdue_tab_po_accept',
                'overdue_tab_po_bulk_accept',
                'overdue_tab_po_reject',
                'overdue_tab_po_bulk_reject',
                'overdue_tab_po_complete',
                'overdue_tab_po_bulk_complete',
            ],
            'Purchase Orders - Rejected Tab' => [
                'rejected_tab_po_view',
            ],
            'Purchase Orders - All Orders Tab' => [
                'all_orders_tab_po_view',
            ],
        ];
    }

        public static function getDefaultApiPermissions()
    {
        return [
            'allocated_tab_wo_accept',
            'allocated_tab_po_accept',
            'allocated_tab_wo_bulk_accept',
            'allocated_tab_po_bulk_accept',
            'allocated_tab_wo_reject',
            'allocated_tab_po_reject',
            'allocated_tab_wo_bulk_reject',
            'allocated_tab_po_bulk_reject',
            'allocated_tab_wo_view',
            'allocated_tab_po_view',
            'in_process_tab_wo_complete',
            'in_process_tab_po_complete',
            'in_process_tab_wo_bulk_complete',
            'in_process_tab_po_bulk_complete',
            'in_process_tab_wo_view',
            'in_process_tab_po_view',
            'overdue_tab_wo_accept',
            'overdue_tab_po_accept',
            'overdue_tab_wo_bulk_accept',
            'overdue_tab_po_bulk_accept',
            'overdue_tab_wo_reject',
            'overdue_tab_po_reject',
            'overdue_tab_wo_bulk_reject',
            'overdue_tab_po_bulk_reject',
            'overdue_tab_wo_complete',
            'overdue_tab_po_complete',
            'overdue_tab_wo_bulk_complete',
            'overdue_tab_po_bulk_complete',
            'overdue_tab_wo_view',
            'overdue_tab_po_view',
            'for_approval_tab_wo_view',
            'for_approval_tab_po_view',
            'completed_tab_wo_view',
            'completed_tab_po_view',
            'rejected_tab_wo_view',
            'rejected_tab_po_view',
            'all_orders_tab_wo_view',
            'all_orders_tab_po_view',
            'new_tab_wo_view',
            'new_tab_po_view'
        ];
    }

    /**
     * Get all available craftman permissions
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
        'aadhar_attach_url',
        'passbook_url',
        'cin_attachment_url',
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

    public function getAadharAttachUrlAttribute()
    {
        return $this->aadhar_attach ? asset('storage/' . $this->aadhar_attach) : null;
    }

    public function getPassbookUrlAttribute()
    {
        return $this->passbook ? asset('storage/' . $this->passbook) : null;
    }

    public function getCinAttachmentUrlAttribute()
    {
        return $this->cin_attachment ? asset('storage/' . $this->cin_attachment) : null;
    }

    public function getBrandLogoUrlAttribute()
    {
        return $this->brand_logo ? asset('storage/' . $this->brand_logo) : null;
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
