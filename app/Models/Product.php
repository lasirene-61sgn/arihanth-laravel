<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_code',
        'relabel_code',
        'product_name',
        'product_category_id',
        'product_subcategory_id',
        'type',
        'order_type',
        'design_status',
        'design_code',
        'bp_code',
        'open_close',
        'size',
        'length',
        'weight_from',
        'weight_to',
        'hallmark',
        'rodium',
        'hook',
        'stone',
        'enamel',
        'product_image',
        'created_by',
        'design_view_unlocked_until',
        'is_locked', // Added for image locking feature
        'description', // Added if missing from store method in DesignController
        'craftsman_staff_id',
        'qr_code',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_design_locked',
    ];

    protected $casts = [
        'design_view_unlocked_until' => 'datetime',
    ];

    /**
     * Get the is_design_locked flag for the current authenticated user.
     *
     * @return bool
     */
    public function getIsDesignLockedAttribute()
    {
        return $this->isDesignLocked(\Illuminate\Support\Facades\Auth::user());
    }

    /**
     * Get the is_locked attribute, overriding it for admins and unlocked designs to always be 0.
     *
     * @param  mixed  $value
     * @return int
     */
    public function getIsLockedAttribute($value)
    {
        // Use the effective lock check logic which handles admins and temporary unlocks
        return $this->isDesignLocked(\Illuminate\Support\Facades\Auth::user()) ? 1 : 0;
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(ProductSubcategory::class, 'product_subcategory_id');
    }

    // Add an accessor for backward compatibility
    public function getSubcategoryIdAttribute()
    {
        return $this->product_subcategory_id;
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function designs()
    {
        return $this->hasMany(Design::class);
    }

// In app/Models/Product.php

    /**
     * Standardize the creator relationship to allow eager loading
     */
    public function creator()
    {
        // Point this to your base User model that contains both ProcessOwners and KeyUsers
        // If they are in separate tables, the blade logic below handles the name display.
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
    // Get creator name regardless of type
    public function getCreatorNameAttribute()
    {
        if (!$this->created_by) {
            return 'Unknown';
        }

        // Check ProcessOwner (Admin/Super Admin)
        $processOwner = ProcessOwner::find($this->created_by);
        if ($processOwner) {
            return $processOwner->full_name ?? $processOwner->name ?? 'Process Owner';
        }

        // Check KeyUser
        $keyUser = KeyUser::find($this->created_by);
        if ($keyUser) {
            return $keyUser->full_name ?? $keyUser->name ?? 'Key User';
        }

        // Check Regular User
        $user = User::find($this->created_by);
        if ($user) {
            return $user->full_name ?? $user->name ?? 'User';
        }

        return 'Unknown';
    }

    /**
     * Generate a unique product code
     */
    public static function generateProductCode()
    {
        // Find all product codes that follow the PRDxxxx pattern
        // We use a regex or a simple where with LIKE to get candidates
        $codes = self::where('product_code', 'LIKE', 'PRD%')
            ->pluck('product_code');

        $maxNumber = 0;
        foreach ($codes as $code) {
            // Extract the numeric part after 'PRD'
            if (preg_match('/PRD(\d+)/i', $code, $matches)) {
                $num = intval($matches[1]);
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            }
        }

        $nextNumber = $maxNumber + 1;
        return 'PRD' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope to exclude products from frozen accounts
     */
    public function scopeNotFromFrozenAccounts($query)
    {
        return $query->whereDoesntHave('buyer', function ($query) {
            $query->where('is_frozen', true);
        })->whereDoesntHave('craftsman', function ($query) {
            $query->where('is_frozen', true);
        });
    }

    /**
     * Relationship to buyer (for products created by buyers)
     */
    public function buyer()
    {
        return $this->belongsTo(Buyer::class, 'bp_code', 'bp_code');
    }

    /**
     * Relationship to craftsman (for products created by craftsmen)
     */
    public function craftsman()
    {
        return $this->belongsTo(Craftman::class, 'bp_code', 'craftman_code');
    }

    public function craftsmanStaff()
    {
        return $this->belongsTo(CraftsmanStaff::class, 'craftsman_staff_id');
    }

    /**
     * Check if the design is locked for non-admin viewers
     */
    /**
     * Check if the design view is currently locked for a specific user
     */
    public function isDesignLocked($user = null)
    {
        // 0. SuperAdmin / Admin always has full access
        if ($user) {
            // Check for ProcessOwner or User model with roles
            if (isset($user->role) && ($user->role === 'super_admin' || $user->role === 'admin')) {
                return false;
            }
            // If it's a ProcessOwner instance, check role too
            if ($user instanceof \App\Models\ProcessOwner && ($user->role === 'super_admin' || $user->role === 'admin')) {
                return false;
            }
        }

        // 1. Check Permanent Unlock (Admin/SuperAdmin toggled to Unlocked)
        // CRITICAL: Use getRawOriginal to avoid infinite recursion with getIsLockedAttribute accessor
        if (!$this->getRawOriginal('is_locked')) {
            return false;
        }

        // 2. Check Global Temporary Unlock
        // Even if is_locked is true (defaut), a valid temporary unlock overrides it.
        if ($this->design_view_unlocked_until && now()->isBefore($this->design_view_unlocked_until)) {
            return false;
        }

        // 2. Check User-Specific Unlock
        if ($user) {
            $userType = null;
            $userCode = null;

            if ($user instanceof \App\Models\Buyer) {
                $userType = 'buyer';
                $userCode = $user->bp_code;
            } elseif ($user instanceof \App\Models\KeyUser) {
                $userType = 'key_user';
                $userCode = $user->user_code;
            } elseif ($user instanceof \App\Models\User) {
                $userType = 'user';
                $userCode = $user->user_code;
            } elseif ($user instanceof \App\Models\Craftman) {
                $userType = 'craftsman';
                $userCode = $user->craftman_code;
            }

            if ($userType && $userCode) {
                // Check for valid access record
                $hasAccess = $this->userAccess()
                    ->where('user_type', $userType)
                    ->where('user_code', $userCode)
                    ->where('unlocked_until', '>=', now())
                    ->exists();

                if ($hasAccess) {
                    return false; // Unlocked specifically for this user
                }
            }
        }

        return true; // Locked
    }

    /**
     * Get all user-specific access records for this design.
     */
    public function userAccess()
    {
        return $this->hasMany(DesignUserAccess::class);
    }

    /**
     * Get all favorites for this product.
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'product_id');
    }

    /**
     * Generate auto-generated design code
     */
    public static function generateDesignCode($categoryId, $weightFrom)
    {
        $category = ProductCategory::find($categoryId);
        if (!$category) return null;

        $categoryName = strtoupper($category->name);
        $abbrev = '';

        // Strip spaces and take first two characters for any future categories
        $cleanName = str_replace(' ', '', $categoryName);
        $abbrev = substr($cleanName, 0, 2);


        // Remove decimals and trailing zeros from weight (e.g., 12.00 -> 12, 12.50 -> 12.5)
        $formattedWeight = (float)$weightFrom == (int)$weightFrom ? (int)$weightFrom : (float)$weightFrom;

        $prefix = $formattedWeight . $abbrev;

        // Get all codes with this prefix from both Product and Design tables
        $existingCodes = self::where('design_code', 'LIKE', $prefix . '%')->pluck('design_code')->toArray();
        $designTableCodes = Design::where('design_code', 'LIKE', $prefix . '%')->pluck('design_code')->toArray();

        $allCodes = array_merge($existingCodes, $designTableCodes);

        $maxSeq = 0;
        foreach ($allCodes as $code) {
            $suffix = substr($code, strlen($prefix));
            if (is_numeric($suffix)) {
                $maxSeq = max($maxSeq, intval($suffix));
            }
        }

        return $prefix . ($maxSeq + 1);
    }
}
