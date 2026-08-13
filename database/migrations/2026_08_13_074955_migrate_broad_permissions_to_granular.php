<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $map = [
            'work_order' => ['wo_view', 'wo_accept', 'wo_reject'],
            'purchase_order' => ['po_view', 'po_accept', 'po_reject'],
            'repair' => ['repair_view', 'repair_accept', 'repair_reject'],
            'product' => ['product_view', 'product_create', 'product_edit'],
            'design' => ['design_view'],
            'catalogue' => ['catalogue_view'],
        ];

        // Migrate Craftmen
        $craftsmen = \App\Models\Craftman::all();
        foreach ($craftsmen as $craftsman) {
            $perms = $craftsman->getPermissionsArray();
            $newPerms = [];
            foreach ($perms as $perm) {
                if (isset($map[$perm])) {
                    $newPerms = array_merge($newPerms, $map[$perm]);
                } else {
                    $newPerms[] = $perm;
                }
            }
            $craftsman->permissions = array_values(array_unique($newPerms));
            $craftsman->save();
        }

        // Migrate CraftsmanStaff
        $staffMembers = \App\Models\CraftsmanStaff::all();
        foreach ($staffMembers as $staff) {
            $perms = $staff->permissions ?? [];
            if (is_string($perms)) $perms = json_decode($perms, true) ?? [];
            
            $newPerms = [];
            foreach ($perms as $perm) {
                if (isset($map[$perm])) {
                    $newPerms = array_merge($newPerms, $map[$perm]);
                } else {
                    $newPerms[] = $perm;
                }
            }
            $staff->permissions = array_values(array_unique($newPerms));
            $staff->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed
    }
};
