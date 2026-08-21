<?php

namespace App\Http\Controllers\Craftsman;

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

            $craftsman = Auth::guard('craftsman')->user();

            if ($craftsman->hasPermission('work_order')) {
                $workOrders = WorkOrder::where('allocated_craftsman_bp_code', $craftsman->craftman_code)
                    ->where(function($q) use ($query) {
                        $q->where('work_order_number', 'LIKE', "%{$query}%")
                            ->orWhere('customer_name', 'LIKE', "%{$query}%")
                            ->orWhere('reference_no', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('product_name', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Work Orders', $workOrders, 'craftsman.work-order.show', 'workOrder', 'work_order_number');
            }

            if ($craftsman->hasPermission('repair')) {
                // Assuming repairs are linked to craftsman
                $repairs = Repair::where('allocated_craftsman_code', $craftsman->craftman_code)
                    ->where(function($q) use ($query) {
                        $q->where('order_no', 'LIKE', "%{$query}%")
                            ->orWhere('product_name', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Repairs', $repairs, 'craftsman.repairs.show', 'id', 'order_no'); // using id since route is repairs.show
            }

            if ($craftsman->hasPermission('purchase_order')) {
                $purchaseOrders = PurchaseOrder::where('allocated_craftsman_code', $craftsman->craftman_code)
                    ->where(function($q) use ($query) {
                        $q->where('purchase_order_code', 'LIKE', "%{$query}%")
                            ->orWhere('notes', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Purchase Orders', $purchaseOrders, 'craftsman.purchase-order.show', 'purchaseOrder', 'purchase_order_code');
            }

            if ($craftsman->hasPermission('product')) {
                $products = Product::where('created_by', $craftsman->id)
                    ->where(function($q) use ($query) {
                        $q->where('product_name', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('design_code', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Products', $products, 'craftsman.product.show', 'product', 'product_name');
            }

            if ($craftsman->hasPermission('design')) {
                $designs = Product::where('created_by', $craftsman->id)
                    ->whereNotNull('design_code')
                    ->where('design_status', 'Accepted')
                    ->whereNotNull('type')
                    ->where('type', '!=', '')
                    ->where(function($q) use ($query) {
                        $q->where('product_name', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('design_code', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Designs', $designs, 'craftsman.design.show', 'design', 'design_code');
            }

            if ($craftsman->hasPermission('stock_order')) {
                // Not strictly sure if it filters by craftsman code, but assuming so
                $stockOrders = StockOrder::where('craftsman_id', $craftsman->id)
                    ->where(function($q) use ($query) {
                        $q->where('order_number', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Live Stock Orders', $stockOrders, 'craftsman.stock-order.show', 'stockOrder', 'order_number');
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'query' => $query,
                'results' => $results
            ]);
        }

        return view('craftsman.global-search.index', compact('results', 'query'));
    }
}
