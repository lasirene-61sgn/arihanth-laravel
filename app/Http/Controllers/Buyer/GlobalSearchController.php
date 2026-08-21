<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\Product;
use App\Models\Design;
use App\Models\Repair;
use App\Models\StockOrder;
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

            $buyer = Auth::guard('buyer')->user();

            // Work Orders
            if ($buyer->hasPermission('work_order')) {
                $workOrders = WorkOrder::where('bp_code', $buyer->bp_code)
                    ->where(function($q) use ($query) {
                        $q->where('work_order_number', 'LIKE', "%{$query}%")
                            ->orWhere('customer_name', 'LIKE', "%{$query}%")
                            ->orWhere('reference_no', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('product_name', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Work Orders', $workOrders, 'buyer.work-order.show', 'work_order', 'work_order_number');
            }

            // Products
            if ($buyer->hasPermission('product')) {
                $products = Product::where('bp_code', $buyer->bp_code)
                    ->where(function($q) use ($query) {
                        $q->where('product_name', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('design_code', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Products', $products, 'buyer.product.show', 'product', 'product_name');
            }
            
            // Catalogue (Accepted Products with design code)
            if ($buyer->hasPermission('catalogue')) {
                $catalogues = Product::where('bp_code', $buyer->bp_code)
                    ->whereNotNull('design_code')
                    ->where('design_status', 'Accepted')
                    ->whereNotNull('type')
                    ->where('type', '!=', '')
                    ->where(function($q) use ($query) {
                        $q->where('product_name', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('design_code', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Catalogue', $catalogues, 'buyer.catalogue.show', 'catalogue', 'product_name');
            }

            // Designs (Global but Accepted Products with design code)
            if ($buyer->hasPermission('design')) {
                $designs = Product::whereNotNull('design_code')
                    ->where('design_status', 'Accepted')
                    ->whereNotNull('type')
                    ->where('type', '!=', '')
                    ->where(function($q) use ($query) {
                        $q->where('product_name', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('design_code', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Designs', $designs, 'buyer.design.show', 'design', 'design_code');
            }

            // Key Users
            if ($buyer->hasPermission('key_user')) {
                $keyUsers = \App\Models\KeyUser::where('bp_code', $buyer->bp_code)
                    ->where(function($q) use ($query) {
                        $q->where('full_name', 'LIKE', "%{$query}%")
                            ->orWhere('user_code', 'LIKE', "%{$query}%")
                            ->orWhere('email_id', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Key Users', $keyUsers, 'buyer.key-user-management.show', 'keyUser', 'full_name');
            }

            // Live Stock Orders
            if ($buyer->hasPermission('stock_order')) {
                $stockOrders = StockOrder::where('buyer_id', $buyer->id)
                    ->where(function($q) use ($query) {
                        $q->where('order_number', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Live Stock Orders', $stockOrders, 'buyer.stock-order.show', 'stock_order', 'order_number');
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'query' => $query,
                'results' => $results
            ]);
        }

        return view('buyer.global-search.index', compact('results', 'query'));
    }
}
