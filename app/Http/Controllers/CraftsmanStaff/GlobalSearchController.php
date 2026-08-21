<?php

namespace App\Http\Controllers\CraftsmanStaff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Design;
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

            $staff = Auth::guard('craftsman_staff')->user();
            // Assuming staff share their craftsman's ID or have their own ID. We use their craftsman_id if applicable.
            $craftsmanId = $staff->craftsman_id ?? $staff->id;

            if ($staff->hasPermission('work_order')) {
                // CraftsmanStaff does not have an allocated_craftsman_code directly on the model, they are linked to a craftsman.
                // We'll use their craftsman_id to get the craftsman's code.
                $craftsmanCode = \App\Models\Craftman::find($staff->craftsman_id)?->craftman_code;
                
                $workOrders = WorkOrder::where('allocated_craftsman_code', $craftsmanCode)
                    ->where(function($q) use ($query) {
                        $q->where('work_order_number', 'LIKE', "%{$query}%")
                            ->orWhere('customer_name', 'LIKE', "%{$query}%")
                            ->orWhere('reference_no', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('product_name', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Work Orders', $workOrders, 'craftsman_staff.work-order.show', 'work_order', 'work_order_number');
            }

            if ($staff->hasPermission('repair')) {
                $craftsmanCode = \App\Models\Craftman::find($staff->craftsman_id)?->craftman_code;
                $repairs = Repair::where('allocated_craftsman_code', $craftsmanCode)
                    ->where(function($q) use ($query) {
                        $q->where('order_no', 'LIKE', "%{$query}%")
                            ->orWhere('product_name', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Repairs', $repairs, 'craftsman_staff.repairs.show', 'id', 'order_no');
            }

            if ($staff->hasPermission('purchase_order')) {
                $craftsmanCode = \App\Models\Craftman::find($staff->craftsman_id)?->craftman_code;
                $purchaseOrders = PurchaseOrder::where('allocated_craftsman_code', $craftsmanCode)
                    ->where(function($q) use ($query) {
                        $q->where('purchase_order_code', 'LIKE', "%{$query}%")
                            ->orWhere('notes', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Purchase Orders', $purchaseOrders, 'craftsman_staff.purchase-order.show', 'purchase_order', 'purchase_order_code');
            }

            if ($staff->hasPermission('product')) {
                $craftsmanCode = \App\Models\Craftman::find($staff->craftsman_id)?->craftman_code;
                $products = Product::where('allocated_craftsman_code', $craftsmanCode)
                    ->where(function($q) use ($query) {
                        $q->where('product_name', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('design_code', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Products', $products, 'craftsman_staff.product.show', 'product', 'product_name');
            }

            if ($staff->hasPermission('design')) {
                $designs = Product::whereNotNull('design_code')
                    ->where('design_status', 'Accepted')
                    ->whereNotNull('type')
                    ->where('type', '!=', '')
                    ->where(function($q) use ($query) {
                        $q->where('product_name', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('design_code', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Designs', $designs, 'craftsman_staff.design.show', 'design', 'design_code');
            }

            if ($staff->hasPermission('stock_order')) {
                $stockOrders = StockOrder::where('craftsman_id', $staff->craftsman_id)
                    ->where(function($q) use ($query) {
                        $q->where('order_number', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Live Stock Orders', $stockOrders, 'craftsman_staff.stock-order.show', 'stockOrder', 'order_number');
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'query' => $query,
                'results' => $results
            ]);
        }

        return view('craftsman_staff.global-search.index', compact('results', 'query'));
    }
}
