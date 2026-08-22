<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Design;
use App\Models\Catalogue;
use Illuminate\Support\Str;
use App\Models\Craftman;
use App\Models\Buyer;
use App\Models\KeyUser;
use App\Models\CraftsmanStaff;
use App\Models\StockOrder;
use App\Models\Repair;
use App\Models\ProcessOwner;
use App\Models\ImageHash;
use Jenssegers\ImageHash\ImageHash as Hasher;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

class GlobalSearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('search');
        $hasImage = $request->hasFile('image_search');
        $results = [];

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

                        // Determine image if available
                        $image = null;
                        if (isset($item->product_image)) $image = $item->product_image;
                        elseif (isset($item->image)) $image = $item->image;
                        elseif (isset($item->profile_image)) $image = $item->profile_image;
                        if ($image) $image = asset($image);

                        // Determine generic details
                        $detailsText = 'No additional details available.';
                        if (isset($item->notes) && !empty($item->notes)) $detailsText = strip_tags($item->notes);
                        elseif (isset($item->customer_name) && !empty($item->customer_name)) $detailsText = 'Customer: ' . $item->customer_name;
                        elseif (isset($item->business_name) && !empty($item->business_name)) $detailsText = 'Business: ' . $item->business_name;
                        elseif (isset($item->details) && !empty($item->details)) $detailsText = strip_tags($item->details);
                        elseif (isset($item->email) && !empty($item->email)) $detailsText = 'Email: ' . $item->email;
                        elseif (isset($item->category) && !empty($item->category)) $detailsText = 'Category: ' . $item->category;

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

        if ($hasImage) {
            $file = $request->file('image_search');
            $hasher = new Hasher(new DifferenceHash());
            $uploadedHashHex = $hasher->hash($file->getRealPath())->toHex();
            
            // Get all hashes from DB and find matches (distance <= 10 usually means very similar)
            $allHashes = ImageHash::all();
            $matchedItems = [];
            
            // Helper to convert hex to 64-bit binary string
            $hexToBin = function($hex) {
                $bin = '';
                for ($i = 0; $i < strlen($hex); $i++) {
                    $bin .= str_pad(base_convert($hex[$i], 16, 2), 4, '0', STR_PAD_LEFT);
                }
                return str_pad($bin, 64, '0', STR_PAD_LEFT);
            };

            $uploadedHashBin = $hexToBin($uploadedHashHex);
            
            foreach ($allHashes as $dbHash) {
                $dbHashBin = $hexToBin($dbHash->hash);
                
                $distance = 0;
                for ($i = 0; $i < 64; $i++) {
                    if (isset($uploadedHashBin[$i]) && isset($dbHashBin[$i]) && $uploadedHashBin[$i] !== $dbHashBin[$i]) {
                        $distance++;
                    }
                }
                
                if ($distance <= 10) { // threshold for similarity
                    $type = $dbHash->hashable_type;
                    if (!isset($matchedItems[$type])) {
                        $matchedItems[$type] = [];
                    }
                    $matchedItems[$type][] = $dbHash->hashable_id;
                }
            }
            
            // Now populate the results based on matched items
            if (isset($matchedItems[WorkOrder::class])) {
                $workOrders = WorkOrder::whereIn('id', $matchedItems[WorkOrder::class])->get();
                $addResults('Work Orders', $workOrders, 'super-admin.work-order.show', 'workOrder', 'work_order_number');
            }
            if (isset($matchedItems[PurchaseOrder::class])) {
                $purchaseOrders = PurchaseOrder::whereIn('id', $matchedItems[PurchaseOrder::class])->get();
                $addResults('Purchase Orders', $purchaseOrders, 'super-admin.purchase-order.show', 'purchaseOrder', 'purchase_order_code');
            }
            if (isset($matchedItems[Product::class])) {
                $products = Product::whereIn('id', $matchedItems[Product::class])->get();
                $addResults('Products', $products, 'super-admin.product.show', 'product', 'product_name');
            }
            if (isset($matchedItems[Design::class])) {
                $designs = Design::whereIn('id', $matchedItems[Design::class])->get();
                $addResults('Designs', $designs, 'super-admin.design.show', 'design', 'design_code');
            }
            if (isset($matchedItems[Catalogue::class])) {
                try {
                    $catalogues = Catalogue::whereIn('id', $matchedItems[Catalogue::class])->get();
                    $addResults('Catalogues', $catalogues, 'super-admin.catalogue.show', 'catalogue', 'catalogue_name');
                } catch (\Exception $e) {
                    // Ignore if catalogues table does not exist yet
                }
            }
            if (isset($matchedItems[Craftman::class])) {
                $craftsmen = Craftman::whereIn('id', $matchedItems[Craftman::class])->get();
                $addResults('Craftsmen', $craftsmen, 'super-admin.business-partner.craftman.show', 'craftman', 'name');
            }
            if (isset($matchedItems[Buyer::class])) {
                $buyers = Buyer::whereIn('id', $matchedItems[Buyer::class])->get();
                $addResults('Buyers', $buyers, 'super-admin.business-partner.buyer.show', 'buyer', 'name');
            }

        } elseif (!empty($query)) {
            // Text Search Mode
            
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
            $addResults('Work Orders', $workOrders, 'super-admin.work-order.show', 'workOrder', 'work_order_number');

            // Purchase Orders
            $purchaseOrders = PurchaseOrder::where('purchase_order_code', 'LIKE', "%{$query}%")
                ->orWhere('notes', 'LIKE', "%{$query}%")
                ->limit(20)->get();
            $addResults('Purchase Orders', $purchaseOrders, 'super-admin.purchase-order.show', 'purchaseOrder', 'purchase_order_code');

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
            $addResults('Key Users', $keyUsers, 'super-admin.key-user.show', 'keyUser', 'full_name');

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
            $addResults('Live Stock Orders', $stockOrders, 'super-admin.stock-order.show', 'stockOrder', 'order_number');

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
                'query' => $hasImage ? 'Image Search' : $query,
                'results' => $results,
                'isImageSearch' => $hasImage
            ]);
        }

        return view('super-admin.global-search.index', compact('results', 'query'));
    }
}
