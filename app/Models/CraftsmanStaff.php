<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class CraftsmanStaff extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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

    public function getMappedPermissionsArray(): array
    {
        $existingPermissions = $this->getPermissionsArray();
        $finalPermissions = [];
        
        foreach ($existingPermissions as $key => $val) {
            $permName = is_numeric($key) ? $val : $key;
            $hasPerm = is_numeric($key) ? true : ($val == true);
            
            if ($hasPerm) {
                if (str_starts_with($permName, 'wo_')) $finalPermissions[] = 'work_order';
                elseif (str_starts_with($permName, 'po_')) $finalPermissions[] = 'purchase_order';
                elseif (str_starts_with($permName, 'product_')) $finalPermissions[] = 'product';
                elseif (str_starts_with($permName, 'design_')) $finalPermissions[] = 'design';
                elseif (str_starts_with($permName, 'catalogue_')) $finalPermissions[] = 'catalogue';
                elseif (str_starts_with($permName, 'repair_')) $finalPermissions[] = 'repairs';
                else $finalPermissions[] = $permName;
            }
        }
        
        return array_values(array_unique($finalPermissions));
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
        $perms = $this->getPermissionsArray();
        
        // Handle flat array: ['work_order', 'product']
        if (in_array($permission, $perms, true)) {
            return true;
        }
        
        // Handle associative array: ['work_order' => true, 'product' => false]
        if (array_key_exists($permission, $perms) && $perms[$permission] == true) {
            return true;
        }
        
        return false;
    }
}
