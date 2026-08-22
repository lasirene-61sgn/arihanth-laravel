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

                        $image = null;
                        if (isset($item->product_image)) $image = $item->product_image;
                        elseif (isset($item->image)) $image = $item->image;
                        elseif (isset($item->profile_image)) $image = $item->profile_image;
                        if ($image) $image = asset($image);

                        $detailsText = 'No additional details available.';
                        if (isset($item->notes) && !empty($item->notes)) $detailsText = strip_tags($item->notes);
                        elseif (isset($item->customer_name) && !empty($item->customer_name)) $detailsText = 'Customer: ' . $item->customer_name;
                        elseif (isset($item->business_name) && !empty($item->business_name)) $detailsText = 'Business: ' . $item->business_name;
                        elseif (isset($item->details) && !empty($item->details)) $detailsText = strip_tags($item->details);
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
        
        // Permission check helper (handles missing method safely)
        $canAccess = function($permission) use ($admin) {
            if (!$admin) return true;
            if (method_exists($admin, 'hasPermission')) {
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

                // Distance <= 10 indicates high similarity
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
            // Standard text search logic...
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