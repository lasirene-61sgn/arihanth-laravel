<?php

namespace App\Http\Controllers\Admin;

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
use App\Models\Favorite;
use App\Models\ImageHash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Jenssegers\ImageHash\ImageHash as Hasher;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

class GlobalSearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('search');
        $hasImage = $request->hasFile('image_search');
        $results = [];

        // Helper function to format items
        $addResults = function($label, $items, $routePrefix, $routeParamName, $displayField) use (&$results) {
            if ($items && $items->count() > 0) {
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

                        // Generate URL safely
                        try {
                            if (is_callable($routeParamName)) {
                                $params = $routeParamName($item);
                                if (Route::has($routePrefix)) {
                                    $url = route($routePrefix, $params);
                                } elseif (Route::has('admin.favorites.index')) {
                                    $url = route('admin.favorites.index', ['search' => $item->design_name ?? '']);
                                } else {
                                    $url = '#';
                                }
                            } elseif (is_array($routeParamName)) {
                                $url = Route::has($routePrefix) ? route($routePrefix, $routeParamName) : '#';
                            } else {
                                $url = Route::has($routePrefix) ? route($routePrefix, [$routeParamName => $item->id]) : '#';
                            }
                        } catch (\Exception $e) {
                            $url = '#';
                        }

                        $image = null;
                        if (isset($item->product_image)) $image = $item->product_image;
                        elseif (isset($item->image)) $image = $item->image;
                        elseif (isset($item->profile_image)) $image = $item->profile_image;
                        elseif (isset($item->product) && isset($item->product->product_image)) $image = $item->product->product_image;

                        if ($image) $image = asset($image);

                        $detailsText = 'No additional details available.';
                        if (isset($item->details) && !empty($item->details)) $detailsText = strip_tags($item->details);
                        elseif (isset($item->notes) && !empty($item->notes)) $detailsText = strip_tags($item->notes);
                        elseif (isset($item->customer_name) && !empty($item->customer_name)) $detailsText = 'Customer: ' . $item->customer_name;
                        elseif (isset($item->business_name) && !empty($item->business_name)) $detailsText = 'Business: ' . $item->business_name;
                        elseif (isset($item->email) && !empty($item->email)) $detailsText = 'Email: ' . $item->email;

                        return [
                            'id' => $item->id,
                            'display' => $display,
                            'url' => $url,
                            'image' => $image,
                            'details' => Str::limit($detailsText, 150)
                        ];
                    })->values()
                ];
            }
        };

        $admin = Auth::guard('admin')->user();
        
        // Permission check helper
        $canAccess = function($permission) use ($admin) {
            if (!$admin) return true;
            if (method_exists($admin, 'hasPermission')) {
                // If favorites permission doesn't exist separately, allow it by default
                if ($permission === 'favorite' || $permission === 'favorites') {
                    return $admin->hasPermission('favorite') || $admin->hasPermission('favorites') || true;
                }
                return $admin->hasPermission($permission);
            }
            return true;
        };

        if ($hasImage) {
            $file = $request->file('image_search');
            $hasher = new Hasher(new DifferenceHash());
            $uploadedHashHex = $hasher->hash($file->getRealPath())->toHex();

            $hexToBin = function($hex) {
                $bin = '';
                for ($i = 0; $i < strlen($hex); $i++) {
                    $bin .= str_pad(base_convert($hex[$i], 16, 2), 4, '0', STR_PAD_LEFT);
                }
                return str_pad($bin, 64, '0', STR_PAD_LEFT);
            };

            $uploadedHashBin = $hexToBin($uploadedHashHex);
            $allHashes = ImageHash::all();
            $matchedItems = [];

            foreach ($allHashes as $dbHash) {
                $dbHashBin = $hexToBin($dbHash->hash);

                $distance = 0;
                for ($i = 0; $i < 64; $i++) {
                    if (isset($uploadedHashBin[$i]) && isset($dbHashBin[$i]) && $uploadedHashBin[$i] !== $dbHashBin[$i]) {
                        $distance++;
                    }
                }

                if ($distance <= 10) {
                    $type = $dbHash->hashable_type;
                    if (!isset($matchedItems[$type])) {
                        $matchedItems[$type] = [];
                    }
                    $matchedItems[$type][] = $dbHash->hashable_id;
                }
            }

            // Products
            if ($canAccess('product') && isset($matchedItems[Product::class])) {
                $products = Product::whereIn('id', $matchedItems[Product::class])->get();
                $addResults('Products', $products, 'admin.product.show', 'product', 'product_name');

                // Match Favorites containing matching products
                if ($canAccess('favorite')) {
                    $matchingProductIds = $products->pluck('id')->toArray();
                    $productFavorites = Favorite::whereIn('product_id', $matchingProductIds)
                        ->with('product')
                        ->limit(20)
                        ->get();

                    $productFavorites->each(function ($fav) {
                        $fav->custom_display = !empty($fav->design_name)
                            ? $fav->design_name
                            : ($fav->product->design_code ?? 'Design #' . $fav->product_id);

                        $fav->details = "Assigned to " . ucfirst($fav->user_type) . " (User ID: {$fav->user_id})";
                        if ($fav->product && !empty($fav->product->product_image)) {
                            $fav->image = $fav->product->product_image;
                        }
                    });

                    $addResults(
                        'Favorites',
                        $productFavorites,
                        'admin.favorites.show',
                        function ($item) {
                            return ['user_id' => $item->user_id, 'user_type' => $item->user_type];
                        },
                        'custom_display'
                    );
                }
            }

            // Work Orders
            if ($canAccess('work_order') && isset($matchedItems[WorkOrder::class])) {
                $workOrders = WorkOrder::whereIn('id', $matchedItems[WorkOrder::class])->get();
                $addResults('Work Orders', $workOrders, 'admin.work-order.show', 'workOrder', 'work_order_number');
            }

            // Designs
            if ($canAccess('design') && isset($matchedItems[Design::class])) {
                $designs = Design::whereIn('id', $matchedItems[Design::class])->get();
                $addResults('Designs', $designs, 'admin.design.show', 'design', 'design_code');
            }

            // Purchase Orders
            if ($canAccess('purchase_order') && isset($matchedItems[PurchaseOrder::class])) {
                $purchaseOrders = PurchaseOrder::whereIn('id', $matchedItems[PurchaseOrder::class])->get();
                $addResults('Purchase Orders', $purchaseOrders, 'admin.purchase-order.show', 'purchaseOrder', 'purchase_order_code');
            }

            // Catalogues
            if ($canAccess('catalogue') && isset($matchedItems[Catalogue::class])) {
                try {
                    $catalogues = Catalogue::whereIn('id', $matchedItems[Catalogue::class])->get();
                    $addResults('Catalogues', $catalogues, 'admin.catalogue.show', 'catalogue', 'catalogue_name');
                } catch (\Exception $e) {}
            }

            // Craftsmen
            if ($canAccess('business_partner') && isset($matchedItems[Craftman::class])) {
                $craftsmen = Craftman::whereIn('id', $matchedItems[Craftman::class])->get();
                $addResults('Craftsmen', $craftsmen, 'admin.business-partner.craftman.show', 'craftman', 'name');
            }

            // Buyers
            if ($canAccess('business_partner') && isset($matchedItems[Buyer::class])) {
                $buyers = Buyer::whereIn('id', $matchedItems[Buyer::class])->get();
                $addResults('Buyers', $buyers, 'admin.business-partner.buyer.show', 'buyer', 'name');
            }

        } elseif (!empty($query)) {
            // Text search
            if ($canAccess('work_order')) {
                $workOrders = WorkOrder::where('work_order_number', 'LIKE', "%{$query}%")
                    ->orWhere('customer_name', 'LIKE', "%{$query}%")
                    ->orWhere('product_name', 'LIKE', "%{$query}%")
                    ->limit(20)->get();
                $addResults('Work Orders', $workOrders, 'admin.work-order.show', 'workOrder', 'work_order_number');
            }

            if ($canAccess('product')) {
                $products = Product::where('product_name', 'LIKE', "%{$query}%")
                    ->orWhere('product_code', 'LIKE', "%{$query}%")
                    ->limit(20)->get();
                $addResults('Products', $products, 'admin.product.show', 'product', 'product_name');
            }

            if ($canAccess('design')) {
                $designs = Design::where('design_code', 'LIKE', "%{$query}%")
                    ->orWhere('design_name', 'LIKE', "%{$query}%")
                    ->limit(20)->get();
                $addResults('Designs', $designs, 'admin.design.show', 'design', 'design_code');
            }

            if ($canAccess('purchase_order')) {
                $purchaseOrders = PurchaseOrder::where('purchase_order_code', 'LIKE', "%{$query}%")->limit(20)->get();
                $addResults('Purchase Orders', $purchaseOrders, 'admin.purchase-order.show', 'purchaseOrder', 'purchase_order_code');
            }

            // Favorites Search
            if ($canAccess('favorite')) {
                $craftmanTable = (new Craftman)->getTable();
                $buyerTable = (new Buyer)->getTable();

                $favorites = Favorite::select('favorites.*')
                    ->leftJoin('products', 'favorites.product_id', '=', 'products.id')
                    ->leftJoin($buyerTable, function($join) use ($buyerTable) {
                        $join->on('favorites.user_id', '=', "{$buyerTable}.id")
                             ->where('favorites.user_type', '=', 'buyer');
                    })
                    ->leftJoin($craftmanTable, function($join) use ($craftmanTable) {
                        $join->on('favorites.user_id', '=', "{$craftmanTable}.id")
                             ->where('favorites.user_type', '=', 'craftsman');
                    })
                    ->where(function($q) use ($query, $buyerTable, $craftmanTable) {
                        $q->where('favorites.design_name', 'LIKE', "%{$query}%")
                          ->orWhere('products.design_code', 'LIKE', "%{$query}%")
                          ->orWhere("{$buyerTable}.name", 'LIKE', "%{$query}%")
                          ->orWhere("{$buyerTable}.bp_code", 'LIKE', "%{$query}%")
                          ->orWhere("{$craftmanTable}.name", 'LIKE', "%{$query}%")
                          ->orWhere("{$craftmanTable}.craftman_code", 'LIKE', "%{$query}%");
                    })
                    ->with('product')
                    ->limit(20)
                    ->get();

                $favorites->each(function ($fav) {
                    $fav->custom_display = !empty($fav->design_name)
                        ? $fav->design_name
                        : ($fav->product->design_code ?? 'Design #' . $fav->product_id);

                    $assignedName = 'User #' . $fav->user_id;
                    if ($fav->user_type === 'buyer') {
                        $b = Buyer::find($fav->user_id);
                        $assignedName = $b ? ($b->name ?? $b->business_name ?? $assignedName) : $assignedName;
                    } elseif ($fav->user_type === 'craftsman') {
                        $c = Craftman::find($fav->user_id);
                        $assignedName = $c ? ($c->name ?? $c->business_name ?? $assignedName) : $assignedName;
                    }

                    $fav->details = "Assigned to: {$assignedName} (" . ucfirst($fav->user_type) . ")"
                        . ($fav->product && !empty($fav->product->design_code) ? " | Code: {$fav->product->design_code}" : "");

                    if (isset($fav->product) && !empty($fav->product->product_image)) {
                        $fav->image = $fav->product->product_image;
                    }
                });

                $addResults(
                    'Favorites',
                    $favorites,
                    'admin.favorites.show',
                    function ($item) {
                        return ['user_id' => $item->user_id, 'user_type' => $item->user_type];
                    },
                    'custom_display'
                );
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'query' => $hasImage ? 'Image Search' : $query,
                'results' => $results,
                'isImageSearch' => $hasImage
            ]);
        }

        return view('admin.global-search.index', compact('results', 'query'));
    }
}