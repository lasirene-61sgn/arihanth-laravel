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
            return $user->craftman_code;
        }
        if ($user instanceof \App\Models\CraftsmanStaff && $user->craftsman) {
            return $user->craftsman->craftman_code;
        }
        return $user->craftman_code ?? null;
    }

    private function getBuyerCode($user)
    {
        if ($user instanceof \App\Models\Buyer) {
            return $user->bp_code;
        }
        return $user->bp_code ?? null;
    }

    public function search(Request $request)
    {
        $query = $request->input('search');
        $hasImage = $request->hasFile('image_search');

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
            'catalogues' => [],
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
                    $woQuery = WorkOrder::whereIn('id', $matchedItems[WorkOrder::class]);
                    if (!$isAdmin) {
                        if ($isCraftsman) {
                            $woQuery->where('allocated_craftsman_bp_code', $craftsmanCode);
                        } elseif ($isBuyer) {
                            $woQuery->where('buyer_bp_code', $buyerCode);
                        } else {
                            $woQuery->where('id', 0);
                        }
                    }
                    $results['work_orders'] = $woQuery->get();
                }

                if (isset($matchedItems[PurchaseOrder::class])) {
                    $poQuery = PurchaseOrder::whereIn('id', $matchedItems[PurchaseOrder::class]);
                    if (!$isAdmin) {
                        if ($isCraftsman) {
                            $poQuery->where('allocated_craftsman_code', $craftsmanCode);
                        } else {
                            $poQuery->where('id', 0); // Buyers don't see POs
                        }
                    }
                    $results['purchase_orders'] = $poQuery->get();
                }

                if (isset($matchedItems[Product::class])) {
                    $prodQuery = Product::whereIn('id', $matchedItems[Product::class]);
                    if (!$isAdmin) {
                        if ($isCraftsman) {
                            $prodQuery->where('craftman_code', $craftsmanCode);
                        } elseif ($isBuyer) {
                            $prodQuery->where('bp_code', $buyerCode);
                        } else {
                            $prodQuery->where('id', 0);
                        }
                    }
                    $results['products'] = $prodQuery->get();
                }

                if (isset($matchedItems[Design::class])) {
                    $designQuery = Design::whereIn('id', $matchedItems[Design::class]);
                    $results['designs'] = $designQuery->get();
                }

                if (isset($matchedItems[Catalogue::class])) {
                    $catQuery = Catalogue::whereIn('id', $matchedItems[Catalogue::class]);
                    $results['catalogues'] = $catQuery->get();
                }

            } catch (\Exception $e) {
                Log::error('API Image Search Error: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Unable to process image.'], 422);
            }
        } elseif (!empty($query)) {
            // TEXT SEARCH
            
            // Work Orders
            $woQuery = WorkOrder::query()
                ->where(function($q) use ($query) {
                    $q->where('work_order_number', 'LIKE', "%{$query}%")
                      ->orWhere('product_name', 'LIKE', "%{$query}%")
                      ->orWhere('design_code', 'LIKE', "%{$query}%");
                });
            if (!$isAdmin) {
                if ($isCraftsman) {
                    $woQuery->where('allocated_craftsman_bp_code', $craftsmanCode);
                } elseif ($isBuyer) {
                    $woQuery->where('buyer_bp_code', $buyerCode);
                } else {
                    $woQuery->where('id', 0);
                }
            }
            $results['work_orders'] = $woQuery->limit(20)->get();

            // Purchase Orders
            $poQuery = PurchaseOrder::query()
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
            $results['purchase_orders'] = $poQuery->limit(20)->get();

            // Products
            $prodQuery = Product::query()
                ->where(function($q) use ($query) {
                    $q->where('product_name', 'LIKE', "%{$query}%")
                      ->orWhere('product_code', 'LIKE', "%{$query}%")
                      ->orWhere('design_code', 'LIKE', "%{$query}%");
                });
            if (!$isAdmin) {
                if ($isCraftsman) {
                    $prodQuery->where('craftman_code', $craftsmanCode);
                } elseif ($isBuyer) {
                    $prodQuery->where('bp_code', $buyerCode);
                } else {
                    $prodQuery->where('id', 0);
                }
            }
            $results['products'] = $prodQuery->limit(20)->get();

            // Designs
            $designQuery = Design::query()
                ->where(function($q) use ($query) {
                    $q->where('design_code', 'LIKE', "%{$query}%")
                      ->orWhere('design_name', 'LIKE', "%{$query}%");
                });
            $results['designs'] = $designQuery->limit(20)->get();

            // Repairs
            $repQuery = Repair::query()
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
            $results['repairs'] = $repQuery->limit(20)->get();
        }

        return response()->json([
            'success' => true,
            'query' => $hasImage ? 'Image Search' : $query,
            'data' => $results,
        ]);
    }
}
