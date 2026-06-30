<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\Product;
use App\Models\Design;
use App\Models\Catalogue;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    /**
     * Sync all entities from Super Admin to Admin panel
     */
    public function syncAll()
    {
        // For work orders, products, designs, catalogues, and purchase orders,
        // both panels already use the same database tables, so no additional
        // synchronization is needed.
        
        // This controller exists to confirm that both panels are using
        // the same data sources and to provide a manual sync endpoint if needed.
        
        return response()->json([
            'status' => 'success',
            'message' => 'Admin panel is already synchronized with Super Admin panel through shared database models.',
            'entities' => [
                'work_orders' => WorkOrder::count(),
                'products' => Product::count(),
                'designs' => Design::count(),
                'catalogues' => Catalogue::count(),
                'purchase_orders' => PurchaseOrder::count(),
            ]
        ]);
    }
}