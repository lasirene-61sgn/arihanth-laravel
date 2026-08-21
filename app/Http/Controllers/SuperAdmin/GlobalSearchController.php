<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Design;
use App\Models\Catalogue;
use App\Models\Craftman;
use App\Models\Buyer;
use App\Models\KeyUser;
use App\Models\CraftsmanStaff;
use App\Models\StockOrder;
use App\Models\Repair;
use App\Models\ProcessOwner;

class GlobalSearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('search');
        $results = [];

        if (!empty($query)) {
            // Helper function to add results
            $addResults = function($label, $items, $routePrefix, $routeParamName, $displayField) use (&$results) {
                if ($items->count() > 0) {
                    $results[$label] = [
                        'count' => $items->count(),
                        'items' => $items->map(function($item) use ($routePrefix, $routeParamName, $displayField) {
                            $display = $item->{$displayField} ?? '';
                            // Fallback if the display field is empty or missing
                            if (empty($display)) {
                                if (isset($item->name)) $display = $item->name;
                                elseif (isset($item->title)) $display = $item->title;
                                elseif (isset($item->first_name) && isset($item->last_name)) $display = $item->first_name . ' ' . $item->last_name;
                                elseif (isset($item->first_name)) $display = $item->first_name;
                                else $display = 'ID: ' . $item->id;
                            }
                            
                            // Prevent error if route doesn't exist
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

            // Work Orders
            $workOrders = WorkOrder::where('work_order_number', 'LIKE', "%{$query}%")
                ->orWhere('customer_name', 'LIKE', "%{$query}%")
                ->orWhere('reference_no', 'LIKE', "%{$query}%")
                ->orWhere('product_category', 'LIKE', "%{$query}%")
                ->orWhere('subcategory', 'LIKE', "%{$query}%")
                ->orWhere('type', 'LIKE', "%{$query}%")
                ->orWhere('order_type', 'LIKE', "%{$query}%")
                ->orWhere('narration_craftsman', 'LIKE', "%{$query}%")
                ->orWhere('narration_admin', 'LIKE', "%{$query}%")
                ->orWhere('product_code', 'LIKE', "%{$query}%")
                ->orWhere('design_code', 'LIKE', "%{$query}%")
                ->orWhere('relabel_code', 'LIKE', "%{$query}%")
                ->orWhere('product_name', 'LIKE', "%{$query}%")
                ->limit(20)->get();
            $addResults('Work Orders', $workOrders, 'super-admin.work-order.show', 'work_order', 'work_order_number');

            // Purchase Orders
            $purchaseOrders = PurchaseOrder::where('purchase_order_code', 'LIKE', "%{$query}%")
                ->orWhere('notes', 'LIKE', "%{$query}%")
                ->limit(20)->get();
            $addResults('Purchase Orders', $purchaseOrders, 'super-admin.purchase-order.show', 'purchase_order', 'purchase_order_code');

            // Products
            $products = Product::where('product_name', 'LIKE', "%{$query}%")
                ->orWhere('product_code', 'LIKE', "%{$query}%")
                ->orWhere('relabel_code', 'LIKE', "%{$query}%")
                ->orWhere('type', 'LIKE', "%{$query}%")
                ->orWhere('order_type', 'LIKE', "%{$query}%")
                ->orWhere('design_code', 'LIKE', "%{$query}%")
                ->limit(20)->get();
            $addResults('Products', $products, 'super-admin.product.show', 'product', 'product_name');

            // Designs
            $designs = Design::where('design_code', 'LIKE', "%{$query}%")
                ->orWhere('design_name', 'LIKE', "%{$query}%")
                ->orWhere('design_type', 'LIKE', "%{$query}%")
                ->orWhere('category', 'LIKE', "%{$query}%")
                ->orWhere('sub_category', 'LIKE', "%{$query}%")
                ->orWhere('details', 'LIKE', "%{$query}%")
                ->limit(20)->get();
            $addResults('Designs', $designs, 'super-admin.design.show', 'design', 'design_code');

            // Craftsmen
            $craftsmen = Craftman::where('name', 'LIKE', "%{$query}%")
                ->orWhere('business_name', 'LIKE', "%{$query}%")
                ->orWhere('craftman_code', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
                ->orWhere('mobile', 'LIKE', "%{$query}%")
                ->orWhere('city', 'LIKE', "%{$query}%")
                ->limit(20)->get();
            $addResults('Craftsmen', $craftsmen, 'super-admin.business-partner.craftman.show', 'craftman', 'name');

            // Buyers
            $buyers = Buyer::where('name', 'LIKE', "%{$query}%")
                ->orWhere('business_name', 'LIKE', "%{$query}%")
                ->orWhere('bp_code', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
                ->orWhere('mobile', 'LIKE', "%{$query}%")
                ->orWhere('city', 'LIKE', "%{$query}%")
                ->limit(20)->get();
            $addResults('Buyers', $buyers, 'super-admin.business-partner.buyer.show', 'buyer', 'name');

            // Admins
            $admins = ProcessOwner::where('role', 'admin')
                ->where(function ($q) use ($query) {
                    $q->where('full_name', 'LIKE', "%{$query}%")
                      ->orWhere('user_code', 'LIKE', "%{$query}%")
                      ->orWhere('bp_code', 'LIKE', "%{$query}%")
                      ->orWhere('email_id', 'LIKE', "%{$query}%")
                      ->orWhere('mobile_no', 'LIKE', "%{$query}%")
                      ->orWhere('city', 'LIKE', "%{$query}%");
                })->limit(20)->get();
            $addResults('Admins', $admins, 'super-admin.admin.show', 'admin', 'full_name');

            // Key Users
            $keyUsers = KeyUser::where('full_name', 'LIKE', "%{$query}%")
                ->orWhere('user_code', 'LIKE', "%{$query}%")
                ->orWhere('email_id', 'LIKE', "%{$query}%")
                ->orWhere('mobile_no', 'LIKE', "%{$query}%")
                ->limit(20)->get();
            $addResults('Key Users', $keyUsers, 'super-admin.key-user.show', 'key_user', 'full_name');

            // Craftsman Staff
            $craftsmanStaff = CraftsmanStaff::where('name', 'LIKE', "%{$query}%")
                ->orWhere('staff_code', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
                ->orWhere('mobile', 'LIKE', "%{$query}%")
                ->limit(20)->get();
            $addResults('Craftsman Staff', $craftsmanStaff, 'super-admin.business-partner.craftsman-staff.show', 'staff', 'name');

            // Live Stock Orders
            $stockOrders = StockOrder::where('order_number', 'LIKE', "%{$query}%")
                ->orWhere('notes', 'LIKE', "%{$query}%")
                ->limit(20)->get();
            $addResults('Live Stock Orders', $stockOrders, 'super-admin.stock-order.show', 'stock_order', 'order_number');

            // Repairs
            $repairs = Repair::where('order_no', 'LIKE', "%{$query}%")
                ->orWhere('product_name', 'LIKE', "%{$query}%")
                ->orWhere('repair_details', 'LIKE', "%{$query}%")
                ->orWhere('sample_details', 'LIKE', "%{$query}%")
                ->orWhere('item_given_to', 'LIKE', "%{$query}%")
                ->orWhere('notes', 'LIKE', "%{$query}%")
                ->limit(20)->get();
            $addResults('Repairs', $repairs, 'super-admin.repairs.show', 'repair', 'order_no');
        }

        if ($request->ajax()) {
            return response()->json([
                'query' => $query,
                'results' => $results
            ]);
        }

        return view('super-admin.global-search.index', compact('results', 'query'));
    }
}
