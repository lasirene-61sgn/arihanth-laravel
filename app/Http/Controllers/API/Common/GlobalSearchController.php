<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\WorkOrder;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Design;
use App\Models\Catalogue;
use App\Models\Repair;
use App\Models\ImageHash;
use Jenssegers\ImageHash\ImageHash as Hasher;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use Illuminate\Support\Facades\Log;

class GlobalSearchController extends Controller
{
    private function isAdmin($user): bool
    {
        return in_array($user->role ?? '', ['super_admin', 'admin']);
    }

    private function isCraftsman($user): bool
    {
        return ($user->role ?? '') === 'craftsman' 
            || $user instanceof \App\Models\Craftman
            || $user instanceof \App\Models\CraftsmanStaff;
    }

    private function isBuyerSide($user): bool
    {
        return $user instanceof \App\Models\Buyer
            || $user instanceof \App\Models\KeyUser
            || $user instanceof \App\Models\User
            || ($user->role ?? '') === 'buyer';
    }

    private function getCraftsmanCode($user)
    {
        if ($user instanceof \App\Models\Craftman) {
            return $user->craftsman_code;
        }
        if ($user instanceof \App\Models\CraftsmanStaff && $user->craftsman) {
            return $user->craftsman->craftman_code;
        }
        return $user->craftsman_code ?? null;
    }

    private function getBuyerCode($user)
    {
        if ($user instanceof \App\Models\Buyer) {
            return $user->bp_code;
        }
        return $user->bp_code ?? null;
    }

    private function getFullImageUrl($path)
    {
        if (empty($path)) return null;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        if (str_starts_with($path, 'images/') || str_starts_with($path, 'uploads/')) {
            return asset($path);
        }
        return asset('storage/' . $path);
    }

    public function search(Request $request)
    {
        $query = $request->input('search');
        $hasImage = $request->hasFile('image_search');
        $perPage = $request->input('per_page', 10);

        $user = $request->user();
        $isAdmin = $this->isAdmin($user);
        $isCraftsman = $this->isCraftsman($user);
        $isBuyer = $this->isBuyerSide($user);

        $craftsmanCode = $this->getCraftsmanCode($user);
        $buyerCode = $this->getBuyerCode($user);
        
        $results = [
            'work_orders' => [],
            'purchase_orders' => [],
            'products' => [],
            'designs' => [],
            'repairs' => [],
        ];

        if ($hasImage) {
            try {
                $file = $request->file('image_search');
                $hasher = new Hasher(new DifferenceHash());
                $uploadedHashHex = $hasher->hash($file->getRealPath())->toHex();

                $allHashes = ImageHash::all();
                
                if ($allHashes->count() === 0) {
                    return response()->json(['success' => false, 'message' => 'Image search database is empty'], 400);
                }

                $matchedItems = [];

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

                    if ($distance <= 2) {
                        $type = $dbHash->hashable_type;
                        if (!isset($matchedItems[$type])) {
                            $matchedItems[$type] = [];
                        }
                        $matchedItems[$type][] = $dbHash->hashable_id;
                    }
                }

                if (isset($matchedItems[WorkOrder::class])) {
                    $woQuery = WorkOrder::select('id', 'work_order_number', 'product_name', 'design_code', 'product_image', 'status')->whereIn('id', $matchedItems[WorkOrder::class]);
                    if (!$isAdmin) {
                        if ($isCraftsman) {
                            $woQuery->where('allocated_craftsman_bp_code', $craftsmanCode);
                        } elseif ($isBuyer) {
                            $woQuery->where('bp_code', $buyerCode);
                        } else {
                            $woQuery->where('id', 0);
                        }
                    }
                    $results['work_orders'] = $woQuery->paginate($perPage, ['*'], 'work_orders_page')->withQueryString();
                }

                if (isset($matchedItems[PurchaseOrder::class])) {
                    $poQuery = PurchaseOrder::select('id', 'purchase_order_code', 'notes', 'status', 'items')->whereIn('id', $matchedItems[PurchaseOrder::class]);
                    if (!$isAdmin) {
                        if ($isCraftsman) {
                            $poQuery->where('allocated_craftsman_code', $craftsmanCode);
                        } else {
                            $poQuery->where('id', 0); // Buyers don't see POs
                        }
                    }
                    $results['purchase_orders'] = $poQuery->paginate($perPage, ['*'], 'purchase_orders_page')->withQueryString();
                }

                if (isset($matchedItems[Product::class])) {
                    $prodQuery = Product::select('id', 'product_name', 'product_code', 'design_code', 'product_image')->whereIn('id', $matchedItems[Product::class]);
                    if (!$isAdmin) {
                        if ($isCraftsman) {
                            $prodQuery->where('bp_code', $craftsmanCode);
                        } elseif ($isBuyer) {
                            $prodQuery->where('bp_code', $buyerCode);
                        } else {
                            $prodQuery->where('id', 0);
                        }
                    }
                    $results['products'] = $prodQuery->paginate($perPage, ['*'], 'products_page')->withQueryString();
                }

                if (isset($matchedItems[Design::class])) {
                    $designQuery = Design::select('id', 'design_code', 'design_name', 'image')->whereIn('id', $matchedItems[Design::class]);
                    $results['designs'] = $designQuery->paginate($perPage, ['*'], 'designs_page')->withQueryString();
                }



                if (isset($matchedItems[Repair::class])) {
                    $repQuery = Repair::select('id', 'order_no', 'product_name', 'image_proof')->whereIn('id', $matchedItems[Repair::class]);
                    if (!$isAdmin) {
                        if ($isCraftsman) {
                            $repQuery->whereHas('craftsman', function($q) use ($craftsmanCode) {
                                $q->where('craftman_code', $craftsmanCode);
                            });
                        } elseif ($isBuyer) {
                            $repQuery->whereHas('buyer', function($q) use ($buyerCode) {
                                $q->where('bp_code', $buyerCode);
                            });
                        } else {
                            $repQuery->where('id', 0);
                        }
                    }
                    $results['repairs'] = $repQuery->paginate($perPage, ['*'], 'repairs_page')->withQueryString();
                }

            } catch (\Exception $e) {
                Log::error('API Image Search Error: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Unable to process image.'], 422);
            }
        } elseif (!empty($query)) {
            // TEXT SEARCH
            
            // Work Orders
            $woQuery = WorkOrder::query()->select('id', 'work_order_number', 'product_name', 'design_code', 'product_image', 'status')
                ->where(function($q) use ($query) {
                    $q->where('work_order_number', 'LIKE', "%{$query}%")
                      ->orWhere('product_name', 'LIKE', "%{$query}%")
                      ->orWhere('design_code', 'LIKE', "%{$query}%");
                });
            if (!$isAdmin) {
                if ($isCraftsman) {
                    $woQuery->where('allocated_craftsman_bp_code', $craftsmanCode);
                } elseif ($isBuyer) {
                    $woQuery->where('bp_code', $buyerCode);
                } else {
                    $woQuery->where('id', 0);
                }
            }
            $results['work_orders'] = $woQuery->paginate($perPage, ['*'], 'work_orders_page')->withQueryString();

            // Purchase Orders
            $poQuery = PurchaseOrder::query()->select('id', 'purchase_order_code', 'notes', 'status', 'items')
                ->where(function($q) use ($query) {
                    $q->where('purchase_order_code', 'LIKE', "%{$query}%")
                      ->orWhere('notes', 'LIKE', "%{$query}%");
                });
            if (!$isAdmin) {
                if ($isCraftsman) {
                    $poQuery->where('allocated_craftsman_code', $craftsmanCode);
                } else {
                    $poQuery->where('id', 0);
                }
            }
            $results['purchase_orders'] = $poQuery->paginate($perPage, ['*'], 'purchase_orders_page')->withQueryString();

            // Products
            $prodQuery = Product::query()->select('id', 'product_name', 'product_code', 'design_code', 'product_image')
                ->where(function($q) use ($query) {
                    $q->where('product_name', 'LIKE', "%{$query}%")
                      ->orWhere('product_code', 'LIKE', "%{$query}%")
                      ->orWhere('design_code', 'LIKE', "%{$query}%");
                });
            if (!$isAdmin) {
                if ($isCraftsman) {
                    $prodQuery->where('bp_code', $craftsmanCode);
                } elseif ($isBuyer) {
                    $prodQuery->where('bp_code', $buyerCode);
                } else {
                    $prodQuery->where('id', 0);
                }
            }
            $results['products'] = $prodQuery->paginate($perPage, ['*'], 'products_page')->withQueryString();

            // Designs
            $designQuery = Design::query()->select('id', 'design_code', 'design_name', 'image')
                ->where(function($q) use ($query) {
                    $q->where('design_code', 'LIKE', "%{$query}%")
                      ->orWhere('design_name', 'LIKE', "%{$query}%");
                });
            $results['designs'] = $designQuery->paginate($perPage, ['*'], 'designs_page')->withQueryString();



            // Repairs
            $repQuery = Repair::query()->select('id', 'order_no', 'product_name', 'image_proof')
                ->where(function($q) use ($query) {
                    $q->where('order_no', 'LIKE', "%{$query}%")
                      ->orWhere('product_name', 'LIKE', "%{$query}%");
                });
            if (!$isAdmin) {
                if ($isCraftsman) {
                    $repQuery->whereHas('craftsman', function($q) use ($craftsmanCode) {
                        $q->where('craftman_code', $craftsmanCode);
                    });
                } elseif ($isBuyer) {
                    $repQuery->whereHas('buyer', function($q) use ($buyerCode) {
                        $q->where('bp_code', $buyerCode);
                    });
                } else {
                    $repQuery->where('id', 0);
                }
            }
            $results['repairs'] = $repQuery->paginate($perPage, ['*'], 'repairs_page')->withQueryString();
        }

        foreach ($results as $key => $paginator) {
            if ($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator || $paginator instanceof \Illuminate\Pagination\Paginator) {
                $paginator->getCollection()->transform(function ($item) {
                    $item->makeHidden([
                        'creator_details', 
                        'approver_details', 
                        'allocator_details', 
                        'items_with_image_urls', 
                        'rejected_items_with_image_urls',
                        'gallery_images'
                    ]);
                    
                    if (isset($item->product_image)) {
                        $item->product_image = $this->getFullImageUrl($item->product_image);
                        $item->product_image_url = $item->product_image;
                    }

                    $poImage = $item->image ?? null;

                    $itemsArray = $item->items;
                    if (is_string($itemsArray)) {
                        $itemsArray = json_decode($itemsArray, true);
                    }
                    if (is_array($itemsArray)) {
                        foreach ($itemsArray as $poItem) {
                            if (!empty($poItem['image'])) {
                                $poImage = $poItem['image'];
                                break;
                            } elseif (!empty($poItem['product_image'])) {
                                $poImage = $poItem['product_image'];
                                break;
                            } elseif (!empty($poItem['product_id'])) {
                                $product = \App\Models\Product::find($poItem['product_id']);
                                if ($product && !empty($product->product_image)) {
                                    $poImage = $product->product_image;
                                    break;
                                }
                            }
                        }
                    }
                    
                    $item->image = $this->getFullImageUrl($poImage);
                    $item->image_url = $item->image;
                    if (isset($item->design_image)) {
                        $item->design_image = $this->getFullImageUrl($item->design_image);
                        $item->design_image_url = $item->design_image;
                    }
                    if (isset($item->image_proof)) {
                        $item->image_proof = $this->getFullImageUrl($item->image_proof);
                        $item->image_proof_url = $item->image_proof;
                    }
                    return $item;
                });
            }
        }

        return response()->json([
            'success' => true,
            'query' => $hasImage ? 'Image Search' : $query,
            'data' => $results,
        ]);
    }
}