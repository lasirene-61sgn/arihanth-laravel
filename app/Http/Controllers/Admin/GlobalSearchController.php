<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Design;
use App\Models\Craftman;
use App\Models\Buyer;
use App\Models\KeyUser;
use App\Models\CraftsmanStaff;
use App\Models\StockOrder;
use App\Models\Repair;
use Illuminate\Support\Facades\Auth;

class GlobalSearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('search');
        $results = [];

        if (!empty($query)) {
            $addResults = function($label, $items, $routePrefix, $routeParamName, $displayField) use (&$results) {
                if ($items->count() > 0) {
                    $results[$label] = [
                        'count' => $items->count(),
                        'items' => $items->map(function($item) use ($routePrefix, $routeParamName, $displayField) {
                            $display = $item->{$displayField} ?? '';
                            if (empty($display)) {
                                if (isset($item->name)) $display = $item->name;
                                elseif (isset($item->title)) $display = $item->title;
                                elseif (isset($item->first_name) && isset($item->last_name)) $display = $item->first_name . ' ' . $item->last_name;
                                elseif (isset($item->first_name)) $display = $item->first_name;
                                else $display = 'ID: ' . $item->id;
                            }
                            
                            try {
                                $url = route($routePrefix, [$routeParamName => $item->id]);
                            } catch (\Exception $e) {
                                $url = '#';
                            }

                            return [
                                'id' => $item->id,
                                'display' => $display,
                                'url' => $url
                            ];
                        })
                    ];
                }
            };

            $admin = Auth::guard('admin')->user();

            if ($admin->hasPermission('work_order')) {
                $workOrders = WorkOrder::where(function($q) use ($query) {
                    $q->where('work_order_number', 'LIKE', "%{$query}%")
                        ->orWhere('customer_name', 'LIKE', "%{$query}%")
                        ->orWhere('reference_no', 'LIKE', "%{$query}%")
                        ->orWhere('product_code', 'LIKE', "%{$query}%")
                        ->orWhere('product_name', 'LIKE', "%{$query}%");
                })->limit(20)->get();
                $addResults('Work Orders', $workOrders, 'admin.work-order.show', 'workOrder', 'work_order_number');
                
                $repairs = Repair::where(function($q) use ($query) {
                    $q->where('order_no', 'LIKE', "%{$query}%")
                        ->orWhere('product_name', 'LIKE', "%{$query}%");
                })->limit(20)->get();
                $addResults('Repairs', $repairs, 'admin.repairs.show', 'repair', 'order_no');
            }

            if ($admin->hasPermission('purchase_order')) {
                $purchaseOrders = PurchaseOrder::where(function($q) use ($query) {
                    $q->where('purchase_order_code', 'LIKE', "%{$query}%")
                        ->orWhere('notes', 'LIKE', "%{$query}%");
                })->limit(20)->get();
                $addResults('Purchase Orders', $purchaseOrders, 'admin.purchase-order.show', 'purchaseOrder', 'purchase_order_code');
            }

            if ($admin->hasPermission('product')) {
                $products = Product::where(function($q) use ($query) {
                    $q->where('product_name', 'LIKE', "%{$query}%")
                        ->orWhere('product_code', 'LIKE', "%{$query}%")
                        ->orWhere('design_code', 'LIKE', "%{$query}%");
                })->limit(20)->get();
                $addResults('Products', $products, 'admin.product.show', 'product', 'product_name');
            }

            if ($admin->hasPermission('catalogue')) {
                $catalogues = Product::whereNotNull('design_code')
                    ->where('design_status', 'Accepted')
                    ->whereNotNull('type')
                    ->where('type', '!=', '')
                    ->where(function($q) use ($query) {
                        $q->where('product_name', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('design_code', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Catalogue', $catalogues, 'admin.catalogue.show', 'catalogue', 'product_name');
            }

            if ($admin->hasPermission('design')) {
                $designs = Product::whereNotNull('design_code')
                    ->where('design_status', 'Accepted')
                    ->whereNotNull('type')
                    ->where('type', '!=', '')
                    ->where(function($q) use ($query) {
                        $q->where('product_name', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('design_code', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Designs', $designs, 'admin.design.show', 'design', 'design_code');
            }

            if ($admin->hasPermission('business_partner')) {
                $craftsmen = Craftman::where(function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('craftman_code', 'LIKE', "%{$query}%");
                })->limit(20)->get();
                $addResults('Craftsmen', $craftsmen, 'admin.business-partner.craftman.show', 'craftman', 'name');

                $buyers = Buyer::where(function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('bp_code', 'LIKE', "%{$query}%");
                })->limit(20)->get();
                $addResults('Buyers', $buyers, 'admin.business-partner.buyer.show', 'buyer', 'name');
            }

            if ($admin->hasPermission('key_user_management')) {
                $keyUsers = KeyUser::where(function($q) use ($query) {
                    $q->where('full_name', 'LIKE', "%{$query}%")
                        ->orWhere('user_code', 'LIKE', "%{$query}%");
                })->limit(20)->get();
                $addResults('Key Users', $keyUsers, 'admin.key-user.show', 'keyUser', 'full_name');
            }

            if ($admin->hasPermission('can_create_staff')) {
                $craftsmanStaff = CraftsmanStaff::where(function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('staff_code', 'LIKE', "%{$query}%");
                })->limit(20)->get();
                $addResults('Craftsman Staff', $craftsmanStaff, 'admin.business-partner.craftsman-staff.show', 'staff', 'name');
            }

            if ($admin->hasPermission('stock_order')) {
                $stockOrders = StockOrder::where(function($q) use ($query) {
                    $q->where('order_number', 'LIKE', "%{$query}%");
                })->limit(20)->get();
                $addResults('Live Stock Orders', $stockOrders, 'admin.stock-order.show', 'stockOrder', 'order_number');
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'query' => $query,
                'results' => $results
            ]);
        }

        return view('admin.global-search.index', compact('results', 'query'));
    }
}
