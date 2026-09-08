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
use App\Models\Favorite;
use App\Models\ImageHash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
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

                        try {
                            if (is_callable($routeParamName)) {
                                $url = route($routePrefix, $routeParamName($item));
                            } elseif (is_array($routeParamName)) {
                                $url = route($routePrefix, $routeParamName);
                            } else {
                                $url = route($routePrefix, [$routeParamName => $item->id]);
                            }
                        } catch (\Exception $e) {
                            $url = '#';
                        }

                        $image = null;
                        if (isset($item->product_image)) $image = $item->product_image;
                        elseif (isset($item->image)) $image = $item->image;
                        elseif (isset($item->profile_image)) $image = $item->profile_image;
                        elseif (isset($item->product) && isset($item->product->product_image)) {
                            $imgs = explode(',', $item->product->product_image);
                            $image = trim($imgs[0]);
                        }

                        if ($image) $image = asset($image);

                        $detailsText = 'No additional details available.';
                        if (isset($item->favorite_details) && !empty($item->favorite_details)) $detailsText = $item->favorite_details;
                        elseif (isset($item->notes) && !empty($item->notes)) $detailsText = strip_tags($item->notes);
                        elseif (isset($item->customer_name) && !empty($item->customer_name)) $detailsText = 'Customer: ' . $item->customer_name;
                        elseif (isset($item->repair_details) && !empty($item->repair_details)) $detailsText = strip_tags($item->repair_details);
                        elseif (isset($item->details) && !empty($item->details)) $detailsText = strip_tags($item->details);
                        elseif (isset($item->product_category) && !empty($item->product_category)) $detailsText = 'Category: ' . $item->product_category;

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

        // Determine current craftsman ID & Code safely (handles direct craftsman or staff login)
        $user = Auth::guard('craftsman')->user();
        $craftsmanId = $user->craftman_id ?? $user->craftsman_id ?? $user->id ?? null;
        $craftsmanCode = $user->craftman_code ?? $user->craftsman_code ?? $user->bp_code ?? null;

        $canAccess = function($permission) use ($user) {
            if (!$user) return true;
            if ($permission === 'favorite' || $permission === 'favorites') {
                return true; // Craftsmen can always search their favorites
            }
            if (method_exists($user, 'hasPermission')) {
                return $user->hasPermission($permission);
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

            // Work Orders (Only allocated to this Craftsman)
            if ($canAccess('work_order') && isset($matchedItems[WorkOrder::class])) {
                $workOrders = WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode)
                    ->whereIn('id', $matchedItems[WorkOrder::class])
                    ->get();
                $addResults('Work Orders', $workOrders, 'craftsman.work-order.show', 'workOrder', 'work_order_number');
            }

            // Repairs (Only allocated to this Craftsman)
            if ($canAccess('repair') && isset($matchedItems[Repair::class])) {
                $repairs = Repair::where('allocated_craftsman_code', $craftsmanCode)
                    ->whereIn('id', $matchedItems[Repair::class])
                    ->get();
                $addResults('Repairs', $repairs, 'craftsman.repairs.show', 'id', 'order_no');
            }

            // Purchase Orders (Only allocated to this Craftsman)
            if ($canAccess('purchase_order') && isset($matchedItems[PurchaseOrder::class])) {
                $purchaseOrders = PurchaseOrder::where('allocated_craftsman_code', $craftsmanCode)
                    ->whereIn('id', $matchedItems[PurchaseOrder::class])
                    ->get();
                $addResults('Purchase Orders', $purchaseOrders, 'craftsman.purchase-order.show', 'purchaseOrder', 'purchase_order_code');
            }

            // Products (Created by this Craftsman)
            if ($canAccess('product') && isset($matchedItems[Product::class])) {
                $products = Product::where('created_by', $craftsmanId)
                    ->whereIn('id', $matchedItems[Product::class])
                    ->get();
                $addResults('Products', $products, 'craftsman.product.show', 'product', 'product_name');
            }

            // Designs
            if ($canAccess('design') && isset($matchedItems[Design::class])) {
                $designs = Design::whereIn('id', $matchedItems[Design::class])->get();
                $addResults('Designs', $designs, 'craftsman.design.show', 'design', 'design_code');
            }

            // Favorites (Only this Craftsman's favorites matching image hashes of Product)
            if ($canAccess('favorite')) {
                $matchedProductIds = $matchedItems[Product::class] ?? [];
                
                $favMatches = Favorite::where('user_id', $craftsmanId)
                    ->where('user_type', 'craftsman')
                    ->whereIn('product_id', $matchedProductIds)
                    ->with(['product'])
                    ->limit(20)
                    ->get();

                $favMatches->each(function ($fav) {
                    $fav->custom_display = !empty($fav->design_name)
                        ? $fav->design_name . ' (' . ($fav->product->design_code ?? 'Design #' . $fav->product_id) . ')'
                        : ($fav->product->design_code ?? 'Design #' . $fav->product_id);

                    $fav->favorite_details = "In Your Favorites | Code: " . ($fav->product->design_code ?? 'N/A');

                    if ($fav->product && !empty($fav->product->product_image)) {
                        $imgs = explode(',', $fav->product->product_image);
                        $fav->image = trim($imgs[0]);
                    }
                });

                $addResults(
                    'My Favorites',
                    $favMatches,
                    'craftsman.design.show',
                    function ($fav) {
                        return ['design' => $fav->product_id];
                    },
                    'custom_display'
                );
            }

        } elseif (!empty($query)) {
            // Work Orders (Only allocated to this Craftsman)
            if ($canAccess('work_order')) {
                $workOrders = WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode)
                    ->where(function($q) use ($query) {
                        $q->where('work_order_number', 'LIKE', "%{$query}%")
                            ->orWhere('customer_name', 'LIKE', "%{$query}%")
                            ->orWhere('reference_no', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('product_name', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Work Orders', $workOrders, 'craftsman.work-order.show', 'workOrder', 'work_order_number');
            }

            // Repairs (Only allocated to this Craftsman)
            if ($canAccess('repair')) {
                $repairs = Repair::where('allocated_craftsman_code', $craftsmanCode)
                    ->where(function($q) use ($query) {
                        $q->where('order_no', 'LIKE', "%{$query}%")
                            ->orWhere('product_name', 'LIKE', "%{$query}%")
                            ->orWhere('repair_details', 'LIKE', "%{$query}%")
                            ->orWhere('notes', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Repairs', $repairs, 'craftsman.repairs.show', 'id', 'order_no');
            }

            // Purchase Orders (Only allocated to this Craftsman)
            if ($canAccess('purchase_order')) {
                $purchaseOrders = PurchaseOrder::where('allocated_craftsman_code', $craftsmanCode)
                    ->where(function($q) use ($query) {
                        $q->where('purchase_order_code', 'LIKE', "%{$query}%")
                            ->orWhere('notes', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Purchase Orders', $purchaseOrders, 'craftsman.purchase-order.show', 'purchaseOrder', 'purchase_order_code');
            }

            // Products (Created by this Craftsman)
            if ($canAccess('product')) {
                $products = Product::where('created_by', $craftsmanId)
                    ->where(function($q) use ($query) {
                        $q->where('product_name', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('design_code', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Products', $products, 'craftsman.product.show', 'product', 'product_name');
            }

            // Designs
            if ($canAccess('design')) {
                $designs = Product::where('created_by', $craftsmanId)
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

            // Live Stock Orders (Only this Craftsman's orders)
            if ($canAccess('stock_order')) {
                $stockOrders = StockOrder::where('craftsman_id', $craftsmanId)
                    ->where(function($q) use ($query) {
                        $q->where('order_number', 'LIKE', "%{$query}%")
                            ->orWhere('notes', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Live Stock Orders', $stockOrders, 'craftsman.stock-order.show', 'stockOrder', 'order_number');
            }

            // My Favorites (Scoped strictly to this Craftsman)
            if ($canAccess('favorite')) {
                $favorites = Favorite::select('favorites.*')
                    ->join('products', 'favorites.product_id', '=', 'products.id')
                    ->where('favorites.user_id', $craftsmanId)
                    ->where('favorites.user_type', 'craftsman')
                    ->where(function($q) use ($query) {
                        $q->where('favorites.design_name', 'LIKE', "%{$query}%")
                          ->orWhere('products.design_code', 'LIKE', "%{$query}%")
                          ->orWhere('products.product_name', 'LIKE', "%{$query}%");
                    })
                    ->with(['product'])
                    ->limit(20)
                    ->get();

                $favorites->each(function ($fav) {
                    $fav->custom_display = !empty($fav->design_name)
                        ? $fav->design_name . ' (' . ($fav->product->design_code ?? 'Design #' . $fav->product_id) . ')'
                        : ($fav->product->design_code ?? 'Design #' . $fav->product_id);

                    $fav->favorite_details = "In Your Favorites" . ($fav->product ? " | Code: " . $fav->product->design_code : "");

                    if ($fav->product && !empty($fav->product->product_image)) {
                        $imgs = explode(',', $fav->product->product_image);
                        $fav->image = trim($imgs[0]);
                    }
                });

                $addResults(
                    'My Favorites',
                    $favorites,
                    'craftsman.design.show',
                    function ($fav) {
                        return ['design' => $fav->product_id];
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

        return view('craftsman.global-search.index', compact('results', 'query'));
    }
}