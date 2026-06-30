<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Craftman;
use App\Models\Buyer;
use App\Models\KeyUser;
use App\Models\User;
use App\Notifications\DesignApproved;
use App\Services\ImageWatermarkService;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;
use Dompdf\Options;

class DesignController extends Controller
{
    // =========================================================================
    // HELPERS — role detection
    // =========================================================================

    private function isAdmin($user): bool
    {
        return in_array($user->role ?? '', ['super_admin', 'admin']);
    }

    private function isCraftsman($user): bool
    {
        return ($user->role ?? '') === 'craftsman' || $user instanceof \App\Models\Craftman;
    }

    private function isKeyUser($user): bool
    {
        return ($user->role ?? '') === 'key_user' || $user instanceof \App\Models\KeyUser;
    }


    /**
     * List designs.
     *
     * - SuperAdmin/Admin : all designs (with optional approval-pending filter)
     * - Craftsman        : their own designs (pending + accepted)
     * - Buyer/KeyUser/User: global accepted designs only (not frozen)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $admin = $this->isAdmin($user);
        $tab     = $request->get('tab');
        $perPage = $request->get('per_page', 15);

        $sortBy = $request->get('sort_by', 'id');

        // 2. Prioritize 'sort' parameter, then 'sort_order', then default to 'asc'
        $sortOrder = strtolower($request->get('sort') ?: $request->get('sort_order', 'asc'));

        // 3. Strict Validation for Order
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        // 4. Validate Columns
        $allowedSortColumns = [
            'id',
            'design_code',
            'product_code',
            'product_name',
            'type',
            'size',
            'weight_from',
            'weight_to',
            'hallmark',
            'rodium',
            'hook',
            'stone',
            'enamel',
            'bp_code',
            'created_at',
        ];

        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'id';
        }
        $query = Product::with(['category', 'subcategory', 'images']);

        // ── Tab logic (moved initial detection before counts) ──
        if (!$tab) {
            if ($request->has('all')) {
                $tab = 'all';
            } elseif ($request->has('pending')) {
                $tab = 'pending';
            } elseif ($request->has('accepted')) {
                $tab = 'accepted';
            } elseif ($request->has('rejected')) {
                $tab = 'rejected';
            }
        }

        // ── Filter Application Helper ──
        $applyFilters = function ($query) use ($request, $user) {
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('design_code', 'LIKE', "%$search%")
                        ->orWhere('product_name', 'LIKE', "%$search%")
                        ->orWhere('bp_code', 'LIKE', "%$search%")
                        ->orWhere('product_code', 'LIKE', "%$search%");
                });
            }

            if ($request->filled('category_id')) $query->where('product_category_id', $request->category_id);
            if ($request->filled('category_name')) {
                $categoryName = $request->category_name;
                $query->whereHas('category', function ($q) use ($categoryName) {
                    $q->where('name', $categoryName);
                });
            }
            if ($request->filled('design_code')) $query->where('design_code', $request->design_code);
            if ($request->filled('subcategory_id')) $query->where('product_subcategory_id', $request->subcategory_id);
            if ($request->filled('subcategory_name') || $request->filled('subcategory')) {
                $subName = $request->subcategory_name ?: $request->subcategory;
                $query->whereHas('subcategory', function ($q) use ($subName) {
                    $q->where('name', $subName);
                });
            }
            if ($request->filled('type')) $query->where('type', $request->type);
            if ($request->filled('bp_code')) $query->where('bp_code', $request->bp_code);
            if ($request->filled('craftsman_code') || $request->filled('craftman_code')) {
                $query->where('bp_code', $request->craftsman_code ?: $request->craftman_code);
            }
            if ($request->filled('size')) $query->where('size', $request->size);
            if ($request->filled('weight_from')) $query->where('weight_from', '>=', $request->weight_from);
            if ($request->filled('weight_to')) $query->where('weight_to', '<=', $request->weight_to);
            if ($request->filled('hallmark')) $query->where('hallmark', $request->hallmark);
            if ($request->filled('rodium')) $query->where('rodium', $request->rodium);
            if ($request->filled('hook')) $query->where('hook', $request->hook);
            if ($request->filled('stone')) $query->where('stone', $request->stone);
            if ($request->filled('enamel')) $query->where('enamel', $request->enamel);
            if ($request->filled('ids')) {
                $ids = $request->ids;
                if (is_string($ids)) $ids = explode(',', $ids);
                if (is_array($ids)) $query->whereIn('id', $ids);
            }
            if ($request->boolean('is_favorite')) {
                $userType = null;
                if ($user instanceof Buyer) $userType = 'buyer';
                elseif ($this->isCraftsman($user)) $userType = 'craftsman';

                if ($userType) {
                    $query->whereHas('favorites', function ($q) use ($user, $userType) {
                        $q->where('user_id', $user->id)
                            ->where('user_type', $userType);
                    });
                }
            }
            return $query;
        };

        // ── Tab counts ──
        // To get accurate counts, we must apply the same base filters as the main query
        // $getBaseQuery = function ($targetTab = null) use ($user, $admin) {
        //     $query = Product::query();

        //     if ($admin) {
        //         $query->whereNotNull('bp_code');
        //         if ($targetTab === 'pending') {
        //             $query->where(function ($q) { $q->where('design_status', 'Pending')->orWhereNull('design_status'); });
        //         } elseif ($targetTab === 'accepted') {
        //             $query->where('design_status', 'Accepted');
        //         } elseif ($targetTab === 'rejected') {
        //             $query->where('design_status', 'Rejected');
        //         } else {
        //             $query->where(function ($q) { $q->whereNotNull('design_code')->orWhereNotNull('design_status'); });
        //         }
        //     } elseif ($this->isCraftsman($user)) {
        //         if ($targetTab === 'accepted') {
        //             // ALL accepted designs for catalog view
        //             $query->where('design_status', 'Accepted')->whereNotNull('bp_code')->notFromFrozenAccounts();
        //         } else {
        //             // Own designs for other views
        //             $query->where('bp_code', $user->craftman_code ?? $user->user_code);
        //             if ($targetTab === 'pending') {
        //                 $query->where('design_status', 'Pending');
        //             } elseif ($targetTab === 'rejected') {
        //                 $query->where('design_status', 'Rejected');
        //             } else {
        //                 $query->whereNotNull('design_code');
        //             }
        //         }
        //     } else {
        //         // Buyer / KeyUser / User — always public catalog
        //         $query->whereNotNull('design_code')
        //             ->where('design_status', 'Accepted')
        //             ->whereNotNull('bp_code')
        //             ->notFromFrozenAccounts();
        //     }
        //     return $query;
        // };

        // $counts = [
        //     'all'      => $applyFilters($getBaseQuery('all'))->count(),
        //     'pending'  => $applyFilters($getBaseQuery('pending'))->count(),
        //     'accepted' => $applyFilters($getBaseQuery('accepted'))->count(),
        //     'rejected' => $applyFilters($getBaseQuery('rejected'))->count(),
        // ];

        $getBaseQuery = function ($targetTab = null) use ($user, $admin) {
            $query = Product::query();

            if ($admin) {
                // Admin sees EVERYTHING. We only filter by the specific tab status.
                if ($targetTab === 'pending') {
                    $query->where(function ($q) {
                        $q->where('design_status', 'Pending')->orWhereNull('design_status')->orWhere('design_status', '');
                    });
                } elseif ($targetTab === 'accepted') {
                    $query->where('design_status', 'Accepted');
                } elseif ($targetTab === 'rejected') {
                    $query->where('design_status', 'Rejected');
                }
                // No whereNotNull('bp_code') here, so orphan records show up for Admin.
            } elseif ($this->isCraftsman($user)) {
                $myCode = $user->craftman_code ?? $user->user_code;

                if ($targetTab === 'accepted') {
                    // Public catalog view for Craftsman
                    $query->where('design_status', 'Accepted')->notFromFrozenAccounts();
                } else {
                    // Own designs view
                    $query->where('bp_code', $myCode);
                    if ($targetTab === 'pending') {
                        $query->whereIn('design_status', ['Pending', ''])->orWhereNull('design_status');
                    } elseif ($targetTab === 'rejected') {
                        $query->where('design_status', 'Rejected');
                    }
                }
            } else {
                // Buyer/General User: ONLY Accepted and NOT frozen.
                $query->where('design_status', 'Accepted')
                    ->notFromFrozenAccounts();
            }
            return $query;
        };

        // 4. Calculate counts using the same logic
        $counts = [
            'all'      => $applyFilters($getBaseQuery('all'))->count(),
            'pending'  => $applyFilters($getBaseQuery('pending'))->count(),
            'accepted' => $applyFilters($getBaseQuery('accepted'))->count(),
            'rejected' => $applyFilters($getBaseQuery('rejected'))->count(),
        ];

        $finalQuery = $getBaseQuery($tab);
        $finalQuery->with(['category', 'subcategory', 'images']);

        if ($admin) {
            $finalQuery->withCount('favorites');
        } else {
            $userType = null;
            if ($user instanceof Buyer) $userType = 'buyer';
            elseif ($this->isCraftsman($user)) $userType = 'craftsman';

            if ($userType) {
                $finalQuery->withExists(['favorites as is_favorite' => function ($q) use ($user, $userType) {
                    $q->where('user_id', $user->id)->where('user_type', $userType);
                }]);
            }
        }

        $applyFilters($finalQuery);

        $finalQuery->orderBy($sortBy, $sortOrder);


        // ── Export (CSV download) ──
        if ($request->has('export')) {
            $products = $query->get();

            $exportData = $products->map(function ($product) {
                return [
                    'Design Code'   => $product->design_code,
                    'Product Code'  => $product->product_code,
                    'Product Name'  => $product->product_name,
                    'Category'      => $product->category->name ?? '',
                    'Subcategory'   => $product->subcategory->name ?? '',
                    'Type'          => $product->type,
                    'Size'          => $product->size,
                    'Weight From'   => $product->weight_from,
                    'Weight To'     => $product->weight_to,
                    'Hallmark'      => $product->hallmark,
                    'Rodium'        => $product->rodium,
                    'Hook'          => $product->hook,
                    'Stone'         => $product->stone,
                    'Enamel'        => $product->enamel,
                    'BP Code'       => $product->bp_code,
                    'Status'        => $product->design_status,
                    'Created At'    => $product->created_at ? $product->created_at->format('Y-m-d') : '',
                ];
            });

            $filename = 'designs_' . now()->format('Y-m-d_H-i-s') . '.csv';
            $headers  = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            return response()->stream(function () use ($exportData) {
                $file = fopen('php://output', 'w');
                if ($exportData->isNotEmpty()) {
                    fputcsv($file, array_keys($exportData->first()));
                    foreach ($exportData as $row) {
                        fputcsv($file, $row);
                    }
                }
                fclose($file);
            }, 200, $headers);
        }

        // ── Print (full data, no pagination) ──
        if ($request->has('print')) {
            $products = $query->get();

            return response()->json([
                'success' => true,
                'data'    => $products,
            ]);
        }

        // ── Paginated list ──
        $paginatedResults = $finalQuery->paginate($perPage)->withQueryString();

        // Append QR URL to products in the current page
        $paginatedResults->getCollection()->transform(function ($product) {
            $product->qr_image_url = $product->qr_code ? asset('storage/' . $product->qr_code) : null;
            return $product;
        });

        return response()->json([
            'success' => true,
            'counts'  => $counts,
            'active_tab' => $tab,
            'data'    => $paginatedResults
        ]);
    }

    /**
     * Show a single design.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $productQuery = Product::with(['category', 'subcategory', 'images']);

        if ($this->isAdmin($user)) {
            $productQuery->withCount('favorites');
        } else {
            $userType = null;
            if ($user instanceof Buyer) $userType = 'buyer';
            elseif ($this->isCraftsman($user)) $userType = 'craftsman';

            if ($userType) {
                $productQuery->withExists(['favorites as is_favorite' => function ($q) use ($user, $userType) {
                    $q->where('user_id', $user->id)->where('user_type', $userType);
                }]);
            }
        }

        $product = $productQuery->find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Design not found'], 404);
        }

        // Only enforce design_code check for non-admins if it's not and admin/super_admin
        if (empty($product->design_code) && !($user->role === 'super_admin' || $user->role === 'admin')) {
            return response()->json(['success' => false, 'message' => 'Design not found'], 404);
        }

        // Buyers/KeyUsers/Users can only see accepted designs
        if (!($user->role === 'super_admin' || $user->role === 'admin') && !($user instanceof \App\Models\Craftman)) {
            if ($product->design_status !== 'Accepted') {
                return response()->json(['success' => false, 'message' => 'Design not available'], 404);
            }
        }

        // Craftsmen can only see their own, UNLESS it is an accepted design
        if ($user instanceof \App\Models\Craftman && $product->bp_code !== $user->craftman_code) {
            if ($product->design_status !== 'Accepted') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        }

        $product->qr_image_url = $product->qr_code ? asset('storage/' . $product->qr_code) : null;

        return response()->json(['success' => true, 'data' => $product]);
    }

    /**
     * Toggle favourite status for a design.
     */
    public function favourite(Request $request, $id)
    {
        $user = $request->user();

        $userType = null;
        if ($user instanceof Buyer) {
            $userType = 'buyer';
        } elseif ($this->isCraftsman($user)) {
            $userType = 'craftsman';
        }

        if (!$userType) {
            return response()->json(['success' => false, 'message' => 'Only buyers and craftsmen can favourite designs'], 403);
        }

        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Design not found'], 404);
        }

        $existing = \App\Models\Favorite::where('user_id', $user->id)
            ->where('user_type', $userType)
            ->where('product_id', $id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'success'     => true,
                'is_favorite' => false,
                'message'     => 'Design removed from favourites',
            ]);
        }

        $favorite = \App\Models\Favorite::create([
            'user_id'    => $user->id,
            'user_type'  => $userType,
            'product_id' => $id,
        ]);

        return response()->json([
            'success'     => true,
            'is_favorite' => true,
            'message'     => 'Design added to favourites',
            'data'        => $favorite,
        ]);
    }

    /**
     * Create a new design (Craftsmen only).
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!($user instanceof \App\Models\Craftman)) {
            return response()->json(['success' => false, 'message' => 'Only craftsmen can submit designs'], 403);
        }

        $validator = Validator::make($request->all(), [
            'design_code'         => 'required|string|max:255|unique:products,design_code',
            'product_name'        => 'required|string|max:255',
            'product_category_id' => 'required|exists:product_categories,id',
            'subcategory_id'      => 'nullable|exists:product_subcategories,id',
            'type'                => 'required|string|in:Piece,Pair',
            'description'         => 'nullable|string',
            'images.*'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $product = Product::create([
            'product_code'           => $request->design_code,
            'design_code'            => $request->design_code,
            'bp_code'                => $user->craftman_code,
            'product_name'           => $request->product_name,
            'product_category_id'    => $request->product_category_id,
            'product_subcategory_id' => $request->subcategory_id,
            'type'                   => $request->type,
            'description'            => $request->description,
            'design_status'          => 'Pending',
            'created_by'             => $user->id,
        ]);

        if ($request->hasFile('images')) {
            $watermarkService = new ImageWatermarkService();
            foreach ($request->file('images') as $image) {
                $path = $image->store('designs', 'public');
                $watermarkService->addWatermark($path);
                ProductImage::create(['product_id' => $product->id, 'path' => $path]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Design submitted successfully',
            'data'    => $product->load(['category', 'subcategory', 'images'])
        ], 201);
    }

    /**
     * Update a design (SuperAdmin only, for edits).
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'super_admin' && $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Design not found'], 404);
        }

        $product->update($request->only([
            'product_name',
            'product_category_id',
            'product_subcategory_id',
            'type',
            'description',
            'design_status'
        ]));

        return response()->json(['success' => true, 'message' => 'Design updated', 'data' => $product]);
    }

    public function accept(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'super_admin' && $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Support finding by ID or design_code
        $product = Product::where('id', $id)->orWhere('design_code', $id)->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Design not found'], 404);
        }

        // Auto-generate the code if not already assigned
        $designCode = $product->design_code;
        if (empty($designCode)) {
            $designCode = Product::generateDesignCode($product->product_category_id, $product->weight_from);
        }

        if (!$designCode) {
            return response()->json(['success' => false, 'message' => 'Could not generate design code automatically'], 422);
        }

        $product->update([
            'design_code'   => $designCode,
            'design_status' => 'Accepted'
        ]);

        // Create or Update the Design record for catalog linkage
        \App\Models\Design::updateOrCreate(
            ['product_id' => $product->id],
            [
                'design_code'   => $designCode,
                'design_name'   => $product->product_name,
                'image'         => $product->images->first() ? $product->images->first()->path : null,
                'design_status' => 'Accepted'
            ]
        );

        // Notify all relevant parties on design approval
        try {
            $approverName = $user->name ?? 'Admin';
            $notified = []; // track already-notified IDs to avoid duplicates

            // 1. Notify craftsman if bp_code matches a craftsman
            if ($product->bp_code) {
                $craftsman = Craftman::where('craftman_code', $product->bp_code)->first();
                if ($craftsman && $craftsman->fcm_token && !in_array('craftsman_' . $craftsman->id, $notified)) {
                    $craftsman->notify(new DesignApproved($product, $approverName));
                    $notified[] = 'craftsman_' . $craftsman->id;
                }

                // 2. Notify buyer if bp_code matches a buyer
                $buyer = Buyer::where('bp_code', $product->bp_code)->first();
                if ($buyer && $buyer->fcm_token && !in_array('buyer_' . $buyer->id, $notified)) {
                    $buyer->notify(new DesignApproved($product, $approverName));
                    $notified[] = 'buyer_' . $buyer->id;
                }
            }

            // 3. Notify the user who created it (KeyUser / User), if different from above
            if ($product->created_by) {
                $creator = KeyUser::find($product->created_by) ?? User::find($product->created_by);
                if ($creator && $creator->fcm_token && !in_array('user_' . $creator->id, $notified)) {
                    $creator->notify(new DesignApproved($product, $approverName));
                    $notified[] = 'user_' . $creator->id;
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify on design acceptance: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => "Design accepted with code {$designCode}", 'data' => $product]);
    }

    /**
     * Reject a design (SuperAdmin / Admin only).
     */
    public function reject(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'super_admin' && $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Support finding by ID or design_code
        $product = Product::where('id', $id)->orWhere('design_code', $id)->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Design not found'], 404);
        }

        // Auto-generate the code if not already assigned
        $designCode = $product->design_code;
        if (empty($designCode)) {
            $designCode = Product::generateDesignCode($product->product_category_id, $product->weight_from);
        }

        if (!$designCode) {
            return response()->json(['success' => false, 'message' => 'Could not generate design code automatically'], 422);
        }

        $product->update([
            'design_code'   => $designCode,
            'design_status' => 'Rejected'
        ]);

        // Create or Update the Design record
        \App\Models\Design::updateOrCreate(
            ['product_id' => $product->id],
            [
                'design_code'   => $designCode,
                'design_name'   => $product->product_name,
                'image'         => $product->images->first() ? $product->images->first()->path : null,
                'design_status' => 'Rejected'
            ]
        );

        // Notify Submitter (Rejected)
        try {
            $submitter = null;
            $approverName = $user->name ?? 'Admin';

            if ($product->bp_code) {
                $submitter = Craftman::where('craftman_code', $product->bp_code)->first();
                if (!$submitter) {
                    $submitter = Buyer::where('bp_code', $product->bp_code)->first();
                }
            }

            if ($submitter && $submitter->fcm_token) {
                // We can use the same notification but maybe with a rejected status if we had one.
                // For now, I'll follow the request which only specifically asked for "approved" 
                // but usually notifications for rejection are good too. 
                // However, I'll stick to the user's specific "approved" request to keep it simple as they didn't explicitly ask for rejection notifications.
                // Wait, user said: "accepted the desing they shwo notofication you desing has been approved"
                // I'll skip rejection notification unless I have a specific class for it. 
                // Actually, I'll notify them anyway using DesignApproved but maybe I should have made it DesignStatusChanged.
                // I'll stick to accepted for now as per user's prompt.
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify submitter on design rejection: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => "Design rejected with code {$designCode}", 'data' => $product]);
    }

    public function bulkAccept(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'super_admin' && $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $ids = $request->input('ids');
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No design IDs provided'], 400);
        }

        $processedCount = 0;
        $failedIds = [];

        foreach ($ids as $id) {
            try {
                $product = Product::where('id', $id)->orWhere('design_code', $id)->first();

                if (!$product) {
                    $failedIds[] = $id;
                    continue;
                }

                $designCode = $product->design_code;
                if (empty($designCode)) {
                    $designCode = Product::generateDesignCode($product->product_category_id, $product->weight_from);
                }

                if (!$designCode) {
                    $failedIds[] = $id;
                    continue;
                }

                $product->update([
                    'design_code'   => $designCode,
                    'design_status' => 'Accepted'
                ]);

                \App\Models\Design::updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'design_code'   => $designCode,
                        'design_name'   => $product->product_name,
                        'image'         => $product->images->first() ? $product->images->first()->path : null,
                        'design_status' => 'Accepted'
                    ]
                );

                // Notify relevant parties
                try {
                    $approverName = $user->name ?? 'Admin';
                    $notified = [];

                    if ($product->bp_code) {
                        $craftsman = Craftman::where('craftman_code', $product->bp_code)->first();
                        if ($craftsman && $craftsman->fcm_token && !in_array('craftsman_' . $craftsman->id, $notified)) {
                            $craftsman->notify(new DesignApproved($product, $approverName));
                            $notified[] = 'craftsman_' . $craftsman->id;
                        }

                        $buyer = Buyer::where('bp_code', $product->bp_code)->first();
                        if ($buyer && $buyer->fcm_token && !in_array('buyer_' . $buyer->id, $notified)) {
                            $buyer->notify(new DesignApproved($product, $approverName));
                            $notified[] = 'buyer_' . $buyer->id;
                        }
                    }

                    if ($product->created_by) {
                        $creator = KeyUser::find($product->created_by) ?? User::find($product->created_by);
                        if ($creator && $creator->fcm_token && !in_array('user_' . $creator->id, $notified)) {
                            $creator->notify(new DesignApproved($product, $approverName));
                            $notified[] = 'user_' . $creator->id;
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Bulk Accept: Failed to notify for product {$id}: " . $e->getMessage());
                }

                $processedCount++;
            } catch (\Exception $e) {
                Log::error("Bulk Accept Error for ID {$id}: " . $e->getMessage());
                $failedIds[] = $id;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully accepted {$processedCount} designs.",
            'failed_ids' => $failedIds
        ]);
    }

    public function bulkReject(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'super_admin' && $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $ids = $request->input('ids');
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No design IDs provided'], 400);
        }

        $processedCount = 0;
        $failedIds = [];

        foreach ($ids as $id) {
            try {
                $product = Product::where('id', $id)->orWhere('design_code', $id)->first();

                if (!$product) {
                    $failedIds[] = $id;
                    continue;
                }

                $designCode = $product->design_code;
                if (empty($designCode)) {
                    $designCode = Product::generateDesignCode($product->product_category_id, $product->weight_from);
                }

                if (!$designCode) {
                    $failedIds[] = $id;
                    continue;
                }

                $product->update([
                    'design_code'   => $designCode,
                    'design_status' => 'Rejected'
                ]);

                \App\Models\Design::updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'design_code'   => $designCode,
                        'design_name'   => $product->product_name,
                        'image'         => $product->images->first() ? $product->images->first()->path : null,
                        'design_status' => 'Rejected'
                    ]
                );

                $processedCount++;
            } catch (\Exception $e) {
                Log::error("Bulk Reject Error for ID {$id}: " . $e->getMessage());
                $failedIds[] = $id;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully rejected {$processedCount} designs.",
            'failed_ids' => $failedIds
        ]);
    }

    public function approvalQueue(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'super_admin' && $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $designs = Product::with(['category', 'subcategory', 'images', 'creator'])
            ->where(function ($query) {
                $query->whereNull('design_status')
                    ->orWhere('design_status', 'Pending');
            })
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json(['success' => true, 'data' => $designs]);
    }

    /**
     * Get auto-generated design code for a product
     */
    public function getGeneratedCode(Product $product)
    {
        $generatedCode = Product::generateDesignCode($product->product_category_id, $product->weight_from);

        return response()->json([
            'success' => true,
            'design_code' => $generatedCode
        ]);
    }

    /**
     * Generate PDF for selected designs.
     */
    public function generatePdf(Request $request)
    {
        $user = $request->user();
        $query = Product::with(['category', 'subcategory', 'images']);

        // ── Scoping (mirrors index logic) ──
        if ($user->role === 'super_admin' || $user->role === 'admin') {
            $query->whereNotNull('bp_code');
        } elseif ($user instanceof \App\Models\Craftman) {
            // Craftsmen can see their own OR any accepted designs
            $query->where(function ($q) use ($user) {
                $q->where('bp_code', $user->craftman_code)
                    ->orWhere('design_status', 'Accepted');
            });
        } else {
            // Buyer / KeyUser / User — public catalogue only
            $query->whereNotNull('design_code')
                ->where('design_status', 'Accepted')
                ->whereNotNull('bp_code')
                ->notFromFrozenAccounts();
        }

        // ── Selected IDs ──
        if ($request->filled('ids')) {
            $ids = $request->ids;
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            if (is_array($ids)) {
                $query->whereIn('id', $ids);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'No design IDs provided'], 400);
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No designs found'], 404);
        }

        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'sans-serif');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(view('api.common.designs.generate-pdf', compact('products'))->render());
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = count($products) === 1
                ? "Design_" . ($products->first()->design_code ?? $products->first()->id) . ".pdf"
                : "Design_Catalog_" . now()->format('Ymd_His') . ".pdf";

            return response($dompdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Access-Control-Expose-Headers', 'Content-Disposition');
        } catch (\Exception $e) {
            Log::error('Design PDF Generation Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate PDF. ' . $e->getMessage()], 500);
        }
    }
}
