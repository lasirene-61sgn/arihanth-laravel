<?php

namespace App\Http\Controllers\KeyUser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\Product;
use App\Models\Design;
use App\Models\Buyer;
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

        $keyUser = Auth::guard('key_user')->user();

        $canAccess = function($permission) use ($keyUser) {
            if (!$keyUser) return true;
            if (method_exists($keyUser, 'hasPermission')) {
                return $keyUser->hasPermission($permission);
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

            // Work Orders (Scoped to Key User BP Code)
            if ($canAccess('work_order') && isset($matchedItems[WorkOrder::class])) {
                $workOrders = WorkOrder::where('bp_code', $keyUser->bp_code)
                    ->whereIn('id', $matchedItems[WorkOrder::class])
                    ->get();
                $addResults('Work Orders', $workOrders, 'key-user.work-order.show', 'workOrder', 'work_order_number');
            }

            // Products (Scoped to Key User BP Code)
            if ($canAccess('product') && isset($matchedItems[Product::class])) {
                $products = Product::where('bp_code', $keyUser->bp_code)
                    ->whereIn('id', $matchedItems[Product::class])
                    ->get();
                $addResults('Products', $products, 'key-user.product.show', 'product', 'product_name');
            }

            // Catalogue (Key User BP Code accepted products with design code)
            if ($canAccess('catalogue') && isset($matchedItems[Product::class])) {
                $catalogues = Product::where('bp_code', $keyUser->bp_code)
                    ->whereNotNull('design_code')
                    ->where('design_status', 'Accepted')
                    ->whereNotNull('type')
                    ->where('type', '!=', '')
                    ->whereIn('id', $matchedItems[Product::class])
                    ->get();
                $addResults('Catalogue', $catalogues, 'key-user.catalogue.show', 'catalogue', 'product_name');
            }

            // Designs
            if ($canAccess('design') && isset($matchedItems[Design::class])) {
                $designs = Design::whereIn('id', $matchedItems[Design::class])->get();
                $addResults('Designs', $designs, 'key-user.design.show', 'design', 'design_code');
            }

            // Buyers
            if ($canAccess('business_partner') && isset($matchedItems[Buyer::class])) {
                $buyers = Buyer::whereIn('id', $matchedItems[Buyer::class])->get();
                $addResults('Buyers', $buyers, 'key-user.business-partner.buyer.show', 'buyer', 'name');
            }

        } elseif (!empty($query)) {
            // Work Orders
            if ($canAccess('work_order')) {
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

            // Products
            if ($canAccess('product')) {
                $products = Product::where('bp_code', $keyUser->bp_code)
                    ->where(function($q) use ($query) {
                        $q->where('product_name', 'LIKE', "%{$query}%")
                            ->orWhere('product_code', 'LIKE', "%{$query}%")
                            ->orWhere('design_code', 'LIKE', "%{$query}%");
                    })->limit(20)->get();
                $addResults('Products', $products, 'key-user.product.show', 'product', 'product_name');
            }

            // Catalogue
            if ($canAccess('catalogue')) {
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

            // Designs
            if ($canAccess('design')) {
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

            // Buyers
            if ($canAccess('business_partner')) {
                $buyers = Buyer::where(function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('bp_code', 'LIKE', "%{$query}%")
                        ->orWhere('email', 'LIKE', "%{$query}%")
                        ->orWhere('mobile', 'LIKE', "%{$query}%");
                })->limit(20)->get();
                $addResults('Buyers', $buyers, 'key-user.business-partner.buyer.show', 'buyer', 'name');
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'query' => $hasImage ? 'Image Search' : $query,
                'results' => $results,
                'isImageSearch' => $hasImage
            ]);
        }

        return view('key-user.global-search.index', compact('results', 'query'));
    }
}