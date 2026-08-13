<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory;

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var array
     */
    protected $appends = ['creator_details', 'approver_details', 'allocator_details', 'product_image_url', 'file_type', 'preview_image_url', 'gallery_images'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($workOrder) {
            if (empty($workOrder->work_order_number)) {
                $workOrder->work_order_number = static::generateWorkOrderNumber();
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'work_order_number',
        'product_image',
        'priority',
        'customer_notes',
        'bp_code',
        'customer_name',
        'reference_no',
        'due_date',
        // 'product_id', // Removed
        'product_category',
        'product_category_id', // Added
        'subcategory_id',      // Added
        'quantity',
        'type',
        // 'order_type',
        'weight_from',
        'weight_to',
        'narration_craftsman',
        'narration_admin',
        'open_close',
        'hallmark',
        'rodium',
        'hook',
        'size',
        'stone',
        'enamel',
        'length',
        'subcategory',
        'product_code',
        'design_code',          // Added - to store the design code separately
        // 'relabel_code',
        'product_name',
        'craftsman_due_date',
        'allocated_craftsman_bp_code',
        'status',
        'craftsman_status', // New field to track craftsman-specific status
        'created_by',
        'creator_type',
        'creator_user_code',
        'approved_by',
        'allocated_by',
        'allocated_at',
        'admin_undo_count',
        'superadmin_undo_count',
        'admin_return_count',
        'superadmin_return_count',
        'return_due_date',
        'return_note',
        'rejection_reason',
        'damaged_image',
        'screw_name',
        'craftsman_staff_id',
        'accepted_by_staff_id',
        'staff_accepted_at',
        'staff_completed_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
        'craftsman_due_date' => 'date',
        'return_due_date' => 'date',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id'; // Explicitly use 'id' as the route key
    }

    /**
     * Get the user who created this work order
     */
    public function creator()
    {
        // Check if created_by refers to a KeyUser first
        $keyUser = \App\Models\KeyUser::find($this->created_by);
        if ($keyUser) {
            return $this->belongsTo(KeyUser::class, 'created_by');
        }

        // If not found in KeyUser, check regular User
        $user = \App\Models\User::find($this->created_by);
        if ($user) {
            // We'll use a manual lookup approach since Laravel doesn't support polymorphic relationships like this
            return null; // We'll handle this manually in the controller
        }

        return null;
    }

    /**
     * Get the creator's name regardless of user type
     */
    /**
     * Get the Process Owner that created this work order
     */
    public function processOwner()
    {
        return $this->belongsTo(\App\Models\ProcessOwner::class, 'created_by');
    }

    /**
     * Get the Key User that created this work order
     */
    public function keyUser()
    {
        return $this->belongsTo(\App\Models\KeyUser::class, 'created_by');
    }

    /**
     * Get the User that created this work order
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the Buyer that created this work order
     */
    public function buyerCreator()
    {
        return $this->belongsTo(\App\Models\Buyer::class, 'created_by');
    }

    /**
     * Get validated creator details (Name, BP Code, User Code, Type).
     * This logic accounts for ID collisions between User, KeyUser, and Buyer tables.
     * 
     * @return array
     */
    public function getCreatorDetailsAttribute()
    {
        // 0. Use new fields if available (High Performance & Correctness)
        if ($this->creator_type) {
            if ($this->creator_type === 'key_user') {
                $keyUser = \App\Models\KeyUser::where('user_code', $this->creator_user_code)->first() ?? \App\Models\KeyUser::find($this->created_by);
                if ($keyUser) return [
                    'name' => $keyUser->full_name ?? $keyUser->name ?? 'Key User',
                    'bp_code' => $keyUser->bp_code,
                    'user_code' => $keyUser->user_code ?? 'N/A',
                    'type' => 'Key User'
                ];
            } elseif ($this->creator_type === 'user') {
                $user = \App\Models\User::where('user_code', $this->creator_user_code)->first() ?? \App\Models\User::find($this->created_by);
                if ($user) return [
                    'name' => $user->full_name ?? $user->name ?? 'User',
                    'bp_code' => $user->bp_code ?? 'N/A',
                    'user_code' => $user->user_code ?? 'N/A',
                    'type' => 'User'
                ];
            } elseif ($this->creator_type === 'buyer') {
                $buyer = \App\Models\Buyer::find($this->created_by);
                if ($buyer) return [
                    'name' => $buyer->business_name ?? $buyer->name ?? 'Buyer',
                    'bp_code' => $buyer->bp_code,
                    'user_code' => 'N/A',
                    'type' => 'Buyer'
                ];
            } elseif (in_array($this->creator_type, ['admin', 'super_admin', 'superadmin'])) {
                $processOwner = \App\Models\ProcessOwner::find($this->created_by);
                if ($processOwner) return [
                    'name' => $processOwner->full_name ?? $processOwner->name ?? 'Admin',
                    'bp_code' => 'N/A',
                    'user_code' => $processOwner->user_code ?? 'N/A',
                    'type' => $this->creator_type === 'super_admin' ? 'Super Admin' : 'Admin'
                ];
            }
        }

        // 1. If created_by is null, it's likely Super Admin (via old logic) or System
        if (is_null($this->created_by)) {
            return [
                'name' => 'Super Admin',
                'bp_code' => 'N/A',
                'user_code' => 'N/A',
                'type' => 'Super Admin'
            ];
        }

        // 2. Try Key User - Match by ID AND BP Code (High Confidence)
        $keyUser = \App\Models\KeyUser::find($this->created_by);
        if ($keyUser) {
            // Strict check: Does the Key User's BP Code match the Work Order's BP Code?
            if ($this->bp_code && $keyUser->bp_code === $this->bp_code) {
                return [
                    'name' => $keyUser->full_name ?? $keyUser->name ?? 'Key User',
                    'bp_code' => $keyUser->bp_code,
                    'user_code' => $keyUser->user_code ?? 'N/A',
                    'type' => 'Key User'
                ];
            }
        }

        // 3. Try Buyer - Match by ID and BP Code (High Confidence)
        $buyer = \App\Models\Buyer::find($this->created_by);
        if ($buyer) {
            // Strict check: Does the Buyer's BP Code match the Work Order's BP Code?
            if ($this->bp_code && $buyer->bp_code === $this->bp_code) {
                return [
                    'name' => $buyer->business_name ?? $buyer->name ?? 'Buyer',
                    'bp_code' => $buyer->bp_code,
                    'user_code' => null, // Buyers don't have user_code
                    'type' => 'Buyer'
                ];
            }
        }

        // 4. Try Process Owner (Admin / Super Admin) - Fallback
        $processOwner = \App\Models\ProcessOwner::find($this->created_by);
        if ($processOwner) {
            return [
                'name' => $processOwner->full_name ?? $processOwner->name ?? 'Admin',
                'bp_code' => 'N/A',
                'user_code' => $processOwner->user_code ?? 'N/A',
                'type' => 'Admin'
            ];
        }

        // 5. Try Regular User - Last Resort
        $user = \App\Models\User::find($this->created_by);
        if ($user) {
            return [
                'name' => $user->full_name ?? $user->name ?? 'User',
                'bp_code' => $user->bp_code ?? 'N/A',
                'user_code' => $user->user_code ?? 'N/A',
                'type' => 'User'
            ];
        }

        return [
            'name' => 'Unknown User',
            'bp_code' => 'N/A',
            'user_code' => 'N/A',
            'type' => 'Unknown'
        ];
    }

    /**
     * Get validated approver details (Name, User Code, Type).
     * 
     * @return array
     */
    public function getApproverDetailsAttribute()
    {
        if (is_null($this->approved_by)) {
            return [
                'name' => 'N/A',
                'user_code' => 'N/A',
                'type' => 'N/A'
            ];
        }

        // Only Admins or Super Admins approve work orders
        $processOwner = \App\Models\ProcessOwner::find($this->approved_by);
        if ($processOwner) {
            return [
                'name' => $processOwner->full_name ?? $processOwner->name ?? 'Admin',
                'user_code' => $processOwner->user_code ?? 'N/A',
                'type' => $processOwner->isSuperAdmin() ? 'Super Admin' : 'Admin'
            ];
        }

        return [
            'name' => 'Unknown User',
            'user_code' => 'N/A',
            'type' => 'Unknown'
        ];
    }

    /**
     * Get validated allocator details (Name, User Code, Type).
     * 
     * @return array
     */
    public function getAllocatorDetailsAttribute()
    {
        if (is_null($this->allocated_by)) {
            // Fallback for legacy allocations
            if (!in_array($this->status, ['new', 'rejected'])) {
                return [
                    'name' => 'Admin / Super Admin',
                    'user_code' => 'N/A',
                    'type' => 'Legacy'
                ];
            }

            return [
                'name' => 'N/A',
                'user_code' => 'N/A',
                'type' => 'N/A'
            ];
        }

        // Only Admins or Super Admins allocate work orders
        $processOwner = \App\Models\ProcessOwner::find($this->allocated_by);
        if ($processOwner) {
            return [
                'name' => $processOwner->full_name ?? $processOwner->name ?? 'Admin',
                'user_code' => $processOwner->user_code ?? 'N/A',
                'type' => $processOwner->isSuperAdmin() ? 'Super Admin' : 'Admin'
            ];
        }

        return [
            'name' => 'Unknown User',
            'user_code' => 'N/A',
            'type' => 'Unknown'
        ];
    }

    /**
     * Get the creator's name regardless of user type (Deprecated - use getCreatorDetailsAttribute)
     */
    public function getCreatorNameAttribute()
    {
        return $this->creator_details['name'];
    }

    /**
     * Get the product relationship
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_code', 'product_code')
            ->orWhere('design_code', $this->product_code);
    }

    /**
     * Get the product category relationship
     */
    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * Get the multiple images associated with this work order
     */
    public function images()
    {
        return $this->hasMany(WorkOrderImage::class);
    }

    /**
     * Get the subcategory relationship
     */
    public function subcategoryRelation()
    {
        // If we have a subcategory_id column, use it; otherwise, match by name
        if (isset($this->attributes['subcategory_id']) && !empty($this->attributes['subcategory_id'])) {
            return $this->belongsTo(ProductSubcategory::class, 'subcategory_id');
        }

        // If we're storing subcategory names directly, we need to find by name
        return $this->belongsTo(ProductSubcategory::class, 'subcategory', 'name');
    }

    /**
     * Generate work order number automatically (WO00001, WO00002, etc.)
     */
    public static function generateWorkOrderNumber()
    {
        $lastWorkOrder = self::where('work_order_number', 'like', 'WA%')
            ->orderBy('id', 'desc')
            ->first();

        $number = 1;
        if ($lastWorkOrder && preg_match('/^WA(\d+)/', $lastWorkOrder->work_order_number, $matches)) {
            $number = intval($matches[1]) + 1;
        } else if ($lastWorkOrder) {
            $number = intval(substr($lastWorkOrder->work_order_number, 2)) + 1;
        }

        $code = 'WA' . str_pad($number, 4, '0', STR_PAD_LEFT);

        while (self::where('work_order_number', $code)->exists()) {
            $number++;
            $code = 'WA' . str_pad($number, 4, '0', STR_PAD_LEFT);
        }

        return $code;
    }

    /**
     * Get the buyer associated with this work order
     */
    public function buyer()
    {
        return $this->belongsTo(Buyer::class, 'bp_code', 'bp_code')
            ->select([
                'bp_code',
                'business_name',
                'mobile',
                'email',
                'city',
                'state'
            ]);
    }

    /**
     * Get the craftsman associated with this work order
     */
    public function craftsman()
    {
        return $this->belongsTo(Craftman::class, 'allocated_craftsman_bp_code', 'craftman_code');
    }

    public function craftsmanStaff()
    {
        return $this->belongsTo(CraftsmanStaff::class, 'craftsman_staff_id');
    }

    public function acceptedByStaff()
    {
        return $this->belongsTo(CraftsmanStaff::class, 'accepted_by_staff_id');
    }

    /**
     * Check if work order is allocated to a craftsman
     */
    public function isAllocated()
    {
        return !is_null($this->allocated_craftsman_bp_code);
    }

    /**
     * Check if craftsman has accepted the work order
     */
    public function isAcceptedByCraftsman()
    {
        return $this->craftsman_status === 'in_process';
    }

    /**
     * Check if craftsman has rejected the work order
     */
    public function isRejectedByCraftsman()
    {
        return $this->craftsman_status === 'rejected';
    }

    /**
     * Check if work order is in process by craftsman
     */
    public function isInProcess()
    {
        return $this->craftsman_status === 'in_process';
    }

    /**
     * Check if work order is completed by craftsman
     */
    public function isCompletedByCraftsman()
    {
        return $this->craftsman_status === 'completed';
    }

    /**
     * Check if work order is approved by super admin
     */
    public function isApproved()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if work order is for approval
     */
    public function isForApproval()
    {
        return $this->status === 'for_approval';
    }

    public function getFileTypeAttribute()
    {
        $previewUrl = $this->preview_image_url;
        if ($previewUrl) {
            $extension = strtolower(pathinfo(parse_url($previewUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
            return $extension === 'pdf' ? 'pdf' : 'image';
        }

        return null;
    }

    public function getPreviewImageUrlAttribute()
    {
        // 1. Check for images in the new work_order_images table (Multiple Images Support)
        if ($this->images->count() > 0) {
            $firstImage = $this->images->first();
            if ($firstImage && $firstImage->image_url) {
                return $firstImage->image_url;
            }
        }

        // 2. If no multi-images, check the single product_image (Legacy/Backward Compatibility)
        if ($this->product_image) {
            return $this->product_image_url;
        }

        // 3. If it's a PDF or missing, fallback to the related Product's first image
        if ($this->product && $this->product->images && $this->product->images->count() > 0) {
            $firstImage = $this->product->images->first();
            if ($firstImage && $firstImage->path) {
                // Ensure correct storage path
                return asset('storage/' . $firstImage->path);
            }
        }

        // 4. If we have a product_image that is a PDF, return it as a last resort
        if ($this->product_image) {
            return $this->product_image_url;
        }

        return null;
    }

    /**
     * Get images uploaded by craftsmen as proof of completion.
     */
    public function getCompletionProofImagesAttribute()
    {
        $images = [];
        if ($this->images->count() > 0) {
            foreach ($this->images as $woImage) {
                if ($woImage->image_url && !str_contains($woImage->image_path, '_multi_')) {
                    $images[] = $woImage->image_url;
                }
            }
        }
        return $images;
    }

    /**
     * Get original product images from the catalog or legacy product_image field.
     */
    public function getProductGalleryImagesAttribute()
    {
        $images = [];

        // 1. return ALL Product Images from the catalogue
        $skipProductImage = false;
        if ($this->product && $this->product->images && $this->product->images->count() > 0) {
            foreach ($this->product->images as $image) {
                if ($image->path) {
                    $url = asset('storage/' . $image->path);
                    if (!in_array($url, $images)) {
                        $images[] = $url;
                    }
                    if ($this->product_image && str_contains($this->product_image, basename($image->path))) {
                        $skipProductImage = true;
                    }
                }
            }
        }

        // 2. add WorkOrderImage records that were uploaded as product images (_multi_)
        if ($this->images && $this->images->count() > 0) {
            foreach ($this->images as $woImage) {
                if ($woImage->image_url && str_contains($woImage->image_path, '_multi_')) {
                    if (!in_array($woImage->image_url, $images)) {
                        $images[] = $woImage->image_url;
                    }
                }
            }
        }

        // 3. check the single product_image (Legacy/Backward Compatibility)
        if ($this->product_image && !$skipProductImage) {
            $url = $this->product_image_url;
            if (!in_array($url, $images)) {
                $images[] = $url;
            }
        }

        return $images;
    }

    /**
     * Get original product images for display (Gallery).
     * This no longer includes craftsman-uploaded proofs to avoid confusion.
     */
    public function getGalleryImagesAttribute()
    {
        return $this->product_gallery_images;
    }

    public function getProductImageUrlAttribute()
    {
        if ($this->product_image) {
            // Check if it's already a full URL
            if (filter_var($this->product_image, FILTER_VALIDATE_URL)) {
                return $this->product_image;
            }

            // If it starts with images/ (public) or uploads/ (public)
            if (str_starts_with($this->product_image, 'images/') || str_starts_with($this->product_image, 'uploads/')) {
                return asset($this->product_image);
            }

            return asset('storage/' . $this->product_image);
        }
        return null;
    }

    /**
     * Check if the work order is overdue based on craftsman due date.
     */
    public function isOverdue()
    {
        if ($this->status === 'completed' || $this->craftsman_status === 'rejected' || $this->craftsman_status === 'completed') {
            return false;
        }

        if (!$this->craftsman_due_date) {
            return false;
        }

        $now = now();
        $dueDate = \Carbon\Carbon::parse($this->craftsman_due_date);

        // Logic matches the overdue-orders tab filter in controller
        if ($now->toDateString() > $dueDate->toDateString()) {
            return true;
        }

        if ($now->toDateString() == $dueDate->toDateString() && $now->hour >= 12) {
            return true;
        }

        return false;
    }
}
