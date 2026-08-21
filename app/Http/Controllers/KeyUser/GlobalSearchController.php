<?php

namespace App\Http\Controllers\KeyUser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\Product;
use App\Models\Design;
use App\Models\Buyer;
use App\Models\KeyUser;
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

            $keyUser = Auth::guard('key_user')->user();

            if ($keyUser->hasPermission('work_order')) {
                $workOrders = WorkOrder::where('bp_code', $keyUser->bp_code)
                    ->where(function($q) use ($query) {
                        $q->where('work_order_number', 'LIKE', "%{$query}%")
                            ->orWhere('customer_name', 'LIKE', "%{$query}%")
                            ->orWhere('reference_no', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('product_name', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Work Orders', $workOrders, 'key-user.work-order.show', 'workOrder', 'work_order_number');
            }

            if ($keyUser->hasPermission('product')) {
                $products = Product::where('bp_code', $keyUser->bp_code)
                    ->where(function($q) use ($query) {
                        $q->where('product_name', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('design_code', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Products', $products, 'key-user.product.show', 'product', 'product_name');
            }

            if ($keyUser->hasPermission('catalogue')) {
                $catalogues = Product::where('bp_code', $keyUser->bp_code)
                    ->whereNotNull('design_code')
                    ->where('design_status', 'Accepted')
                    ->whereNotNull('type')
                    ->where('type', '!=', '')
                    ->where(function($q) use ($query) {
                        $q->where('product_name', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('design_code', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Catalogue', $catalogues, 'key-user.catalogue.show', 'catalogue', 'product_name');
            }

            if ($keyUser->hasPermission('design')) {
                $designs = Product::whereNotNull('design_code')
                    ->where('design_status', 'Accepted')
                    ->whereNotNull('type')
                    ->where('type', '!=', '')
                    ->where(function($q) use ($query) {
                        $q->where('product_name', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('design_code', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Designs', $designs, 'key-user.design.show', 'design', 'design_code');
            }

            if ($keyUser->hasPermission('business_partner')) {
                $buyers = Buyer::where(function($q) use ($query) {
                        $q->where('name', 'LIKE', "%{$query}%")
                            ->orWhere('bp_code', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Buyers', $buyers, 'key-user.business-partner.buyer.show', 'buyer', 'name');
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'query' => $query,
                'results' => $results
            ]);
        }

        return view('key-user.global-search.index', compact('results', 'query'));
    }
}
