<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductExport;
use App\Models\Design;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

class DesignController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = Product::with(['category', 'subcategory', 'images', 'creator', 'userAccess'])->notFromFrozenAccounts()->whereNotNull('type');

        // Status Counts (based on current filters but BEFORE tab filtering)
        $countQuery = clone $baseQuery;
        if ($request->filled('search')) {
            $countQuery->where(function ($q) use ($request) {
                $q->where('product_name', 'like', '%' . $request->search . '%')
                    ->orWhere('product_code', 'like', '%' . $request->search . '%')
                    ->orWhere('design_code', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('category_filter')) {
            $countQuery->where('product_category_id', $request->category_filter);
        }

        $allCount = (clone $countQuery)->count();
        $acceptedCount = (clone $countQuery)->where('design_status', 'Accepted')->count();
        $rejectedCount = (clone $countQuery)->where('design_status', 'Rejected')->count();
        $pendingCount = (clone $countQuery)->whereNotIn('design_status', ['Accepted', 'Rejected'])->count();

        $statusCounts = [
            'all' => $allCount,
            'accepted' => $acceptedCount,
            'rejected' => $rejectedCount,
            'pending' => $pendingCount,
        ];

        // --- Now apply filters to the main query ---
        $query = clone $baseQuery;

        // Search Logic
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('product_name', 'like', '%' . $request->search . '%')
                    ->orWhere('product_code', 'like', '%' . $request->search . '%')
                    ->orWhere('design_code', 'like', '%' . $request->search . '%');
            });
        }

        // Category filter
        if ($request->filled('category_filter')) {
            $query->where('product_category_id', $request->category_filter);
        }

        // Image Search Filter
        if ($request->filled('matched_ids')) {
            $matchedIds = explode(',', $request->matched_ids);
            $query->whereIn('id', $matchedIds);
        }

        // Tab Filtering
        $activeTab = $request->get('tab', 'all');
        switch ($activeTab) {
            case 'accepted':
                $query->where('design_status', 'Accepted');
                break;
            case 'rejected':
                $query->where('design_status', 'Rejected');
                break;
            case 'pending':
                $query->whereNotIn('design_status', ['Accepted', 'Rejected']);
                break;
            case 'all':
            default:
                break;
        }

        // Sort Logic
        $sortBy = $request->get('sort_by', 'created_at');
        $allowedSortColumns = ['created_at', 'product_code', 'product_name', 'design_code'];

        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, 'desc');

        // Export functionality (Updated to respect current query state)
        if ($request->has('export')) {
            return Excel::download(new ProductExport($request), 'designs_' . $activeTab . '_' . date('Y-m-d_H-i-s') . '.xlsx');
        }

        $products = $query->paginate(15)->withQueryString();

        // Get all categories for the dropdown
        $categories = ProductCategory::with(['subcategories' => function($q) {
            $q->withCount('products');
        }])->withCount('products')->orderBy('name')->get();

        return view('super-admin.design.index', compact('products', 'categories', 'statusCounts', 'activeTab'));
    }

    /**
     * Show Design Details
     */
    public function show(Product $design)
    {
        $product = $design;
        $product->load(['category', 'subcategory', 'images', 'creator', 'designs']);
        $categories = \App\Models\ProductCategory::orderBy('name')->get();
        return view('super-admin.design.show', compact('product', 'categories'));
    }

    /**
     * Accept a product and generate design code
     */
    public function accept(Request $request, Product $product)
    {
        $result = $this->performAcceptance($product);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()
            ->back()
            ->with('success', $result['message']);
    }

    /**
     * Bulk Accept products
     */
    public function bulkAccept(Request $request)
    {
        $ids = $request->input('selected_designs', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No designs selected.']);
        }

        $products = Product::whereIn('id', $ids)->get();
        $count = 0;

        foreach ($products as $product) {
            if ($product->design_status !== 'Accepted') {
                $result = $this->performAcceptance($product);
                if ($result['success']) {
                    $count++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$count} designs accepted successfully."
        ]);
    }

    /**
     * Internal logic for accepting a design
     */
    private function performAcceptance(Product $product)
    {
        // Auto-generate the code
        $designCode = $this->generateDesignCode($product);

        if (!$designCode) {
            return ['success' => false, 'message' => "Could not generate design code automatically for {$product->product_code}."];
        }

        $qrPath = null;
        try {
            if(!Storage::disk('public')->exists('qrcodes')){
                Storage::disk('public')->makeDirectory('qrcodes');
            }
            $qrUrl = rtrim(config('app.url'), '/') . route('super-admin.design.show', $product->id, false);

            // Try PNG format first (requires Imagick), fall back to SVG
            try {
                $qrPath = 'qrcodes/' . $designCode . '.png';
                QrCode::format('png')->size(300)->margin(2)
                    ->generate($qrUrl, storage_path('app/public/' . $qrPath));
            } catch (\Exception $e) {
                // Imagick not available, try SVG format
                $qrPath = 'qrcodes/' . $designCode . '.svg';
                $svgContent = QrCode::format('svg')->size(300)->margin(2)->generate($qrUrl);
                Storage::disk('public')->put($qrPath, $svgContent);
            }
        } catch (\Exception $e) {
            Log::warning('QR code generation failed for ' . $designCode . ': ' . $e->getMessage());
            $qrPath = null;
        }

        // Update product with design code and status
        $product->update([
            'design_code' => $designCode,
            'design_status' => 'Accepted',
            'qr_code' => $qrPath,
        ]);

        $imagePath = $product->images->first() ? $product->images->first()->path : null;
        $imageHash = null;
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            try {
                $hasher = new ImageHash(new DifferenceHash());
                $hash = $hasher->hash(storage_path('app/public/' . $imagePath));
                $imageHash = $hash->toHex();
            } catch (\Exception $e) {
                Log::warning('Image hash generation failed for ' . $designCode . ': ' . $e->getMessage());
            }
        }

        // Create the Design record
        \App\Models\Design::updateOrCreate(
            ['product_id' => $product->id],
            [
                'design_code' => $designCode,
                'design_name' => $product->product_name,
                'image' => $imagePath,
                'qr_code' => $qrPath,
                'image_hash' => $imageHash,
            ]
        );

        // Send Notification
        try {
            $recipient = null;
            if ($product->bp_code) {
                $recipient = \App\Models\Buyer::where('bp_code', $product->bp_code)->first();
            } elseif ($product->created_by) {
                $recipient = \App\Models\KeyUser::find($product->created_by) ?? \App\Models\User::find($product->created_by);
            }

            if ($recipient && $recipient->fcm_token) {
                $adminName = \Illuminate\Support\Facades\Auth::user()->name ?? 'SuperAdmin';
                $recipient->notify(new \App\Notifications\DesignApproved($product, $adminName));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to notify design creator: ' . $e->getMessage());
        }

        return ['success' => true, 'message' => "Product accepted! Design code {$designCode} assigned."];
    }

    /**
     * Reject a product
     */
    public function reject(Product $product)
    {
        $product->update([
            'design_status' => 'Rejected'
        ]);

        return redirect()
            ->back()
            ->with('success', 'Product rejected successfully.');
    }

    /**
     * Bulk Reject products
     */
    public function bulkReject(Request $request)
    {
        $ids = $request->input('selected_designs', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No designs selected.']);
        }

        Product::whereIn('id', $ids)->update([
            'design_status' => 'Rejected'
        ]);

        return response()->json([
            'success' => true,
            'message' => count($ids) . " designs rejected successfully."
        ]);
    }

    /**
     * Generate unique design code
     */
    private function generateDesignCode($product)
    {
        return Product::generateDesignCode($product->product_category_id, $product->weight_from);
    }

    public function printSelected(Request $request)
    {
        $ids = $request->input('selected_designs', []);
        $designs = Product::whereIn('id', $ids)->with(['category', 'subcategory', 'images'])->get();
        return view('super-admin.design.print-selected', compact('designs'));
    }

    public function unlockDesigns(Request $request)
    {
        try {
            $request->validate([
                'unlock_type' => 'required|in:selected,category',
                'ids' => 'nullable|array',
                'category_ids' => 'required_if:unlock_type,category|array',
                'subcategory_ids' => 'nullable|array',
                'duration' => 'required|integer',
                'duration_unit' => 'required|in:minutes,hours,months,years,permanent',
                'user_scope' => 'required|in:all,specific',
                'selected_users' => 'nullable|array',
            ]);

            $unlockType = $request->input('unlock_type');
            $ids = $request->input('ids', []);
            
            if ($unlockType === 'category') {
                $categoryIds = $request->input('category_ids', []);
                $subcategoryIds = $request->input('subcategory_ids', []);

                $query = Product::where('is_locked', true);
                
                $query->where(function($q) use ($categoryIds, $subcategoryIds) {
                    $q->whereIn('product_category_id', $categoryIds);
                    if (!empty($subcategoryIds)) {
                        $q->orWhereIn('product_subcategory_id', $subcategoryIds);
                    }
                });

                $ids = $query->pluck('id')->toArray();
                
                if (empty($ids)) {
                    return response()->json(['success' => false, 'message' => 'No locked designs found in selected categories/subcategories.']);
                }
            }

            $duration = $request->duration;
            $durationUnit = $request->duration_unit;
            $userScope = $request->user_scope;
            $selectedUsers = $request->selected_users ?? [];
            
            $now = now();
            $unlockUntil = null;
            
            if ($durationUnit !== 'permanent') {
                if ($durationUnit === 'minutes') {
                    $unlockUntil = $now->addMinutes($duration);
                } elseif ($durationUnit === 'hours') {
                    $unlockUntil = $now->addHours($duration);
                } elseif ($durationUnit === 'months') {
                    $unlockUntil = $now->addMonths($duration);
                } elseif ($durationUnit === 'years') {
                    $unlockUntil = $now->addYears($duration);
                }
            } else {
                $unlockUntil = now()->addYears(100);
            }

            if ($userScope === 'all') {
                if ($durationUnit === 'permanent') {
                    Product::whereIn('id', $ids)->update([
                        'is_locked' => false,
                        'design_view_unlocked_until' => null
                    ]);
                    $message = count($ids) . " designs PERMANENTLY unlocked for ALL users.";
                } else {
                    Product::whereIn('id', $ids)->update([
                        'design_view_unlocked_until' => $unlockUntil
                    ]);
                    $message = count($ids) . " designs unlocked for ALL users until " . $unlockUntil->format('d M Y H:i');
                }
            } else {
                foreach ($ids as $productId) {
                    foreach ($selectedUsers as $userString) {
                        [$userType, $userCode] = explode(':', $userString, 2);

                        \App\Models\DesignUserAccess::updateOrCreate(
                            [
                                'product_id' => $productId,
                                'user_type' => $userType,
                                'user_code' => $userCode,
                            ],
                            [
                                'unlocked_until' => $unlockUntil,
                            ]
                        );
                    }
                }

                $msgType = $durationUnit === 'permanent' ? 'PERMANENTLY' : "until " . $unlockUntil->format('d M Y H:i');
                $message = count($ids) . " designs unlocked for " . count($selectedUsers) . " specific users " . $msgType;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            \Log::error('SuperAdmin Unlock Designs Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle lock status for Design (Product) image
     */
    public function toggleLock(\App\Models\Product $product)
    {
        $product->update(['is_locked' => !$product->is_locked]);

        $status = $product->is_locked ? 'locked' : 'unlocked';
        return redirect()->back()->with('success', "Design image has been {$status}.");
    }

    public function getAvailableUsers()
    {
        $buyers = \App\Models\Buyer::select('bp_code', 'business_name', 'name')
            ->orderBy('business_name')
            ->get();

        $keyUsers = \App\Models\KeyUser::select('user_code', 'full_name')
            ->where('status', 1)
            ->orderBy('full_name')
            ->get();

        $users = \App\Models\User::select('user_code', 'full_name')
            // Temporarily removed status check to match Admin logic and ensure visibility
            // ->where('status', 'active') 
            ->orderBy('full_name')
            ->get();

        $craftsmen = \App\Models\Craftman::select('craftman_code', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'buyers' => $buyers,
            'key_users' => $keyUsers,
            'users' => $users,
            'craftsmen' => $craftsmen,
        ]);
    }

    public function generateMissingQRCodes()
    {
        $products = Product::where('design_status', 'Accepted')->whereNull('qr_code')->get();

        $count = 0;
        foreach ($products as $product) {
            if ($product->design_code) {
                $designCode = $product->design_code;
                $qrPath = null;
                try {
                    if (!Storage::disk('public')->exists('qrcodes')) {
                        Storage::disk('public')->makeDirectory('qrcodes');
                    }
                    $qrUrl = rtrim(config('app.url'), '/') . route('super-admin.design.show', $product->id, false);

                    // Try PNG format first (requires Imagick), fall back to SVG
                    try {
                        $qrPath = 'qrcodes/' . $designCode . '.png';
                        QrCode::format('png')->size(300)->margin(2)
                            ->generate($qrUrl, storage_path('app/public/' . $qrPath));
                    } catch (\Exception $e) {
                        // Imagick not available, try SVG format
                        $qrPath = 'qrcodes/' . $designCode . '.svg';
                        $svgContent = QrCode::format('svg')->size(300)->margin(2)->generate($qrUrl);
                        Storage::disk('public')->put($qrPath, $svgContent);
                    }

                    if ($qrPath) {
                        $product->update(['qr_code' => $qrPath]);
                        Design::where('product_id', $product->id)->update(['qr_code' => $qrPath]);
                        $count++;
                    }
                } catch (\Exception $e) {
                    Log::warning('QR code generation failed for ' . $designCode . ': ' . $e->getMessage());
                }
            }
        }
        return redirect()->back()->with('success', "Generated {$count} missing QR codes.");
    }

    public function bulkPrintPRN(Request $request)
    {
        $ids = $request->input('selected_designs', []);
        if (empty($ids)) {
            return back()->with('error', 'No designs selected.');
        }

        $products = Product::whereIn('id', $ids)->with(['category', 'subcategory'])->get();
        
        $prnContent = "";
        
        foreach ($products as $product) {
            $designCode = $product->design_code ?? $product->product_code;
            $name = substr($product->product_name, 0, 20);
            $weight = $product->weight_from . '-' . $product->weight_to . 'g';
            $category = $product->category->name ?? 'N/A';
            $url = route('super-admin.design.show', $product->id);

            // Generic ZPL II Template for Jewelry Labels
            $prnContent .= "^XA\n";
            $prnContent .= "^CF0,30\n";
            $prnContent .= "^FO50,50^FD" . $designCode . "^FS\n";
            $prnContent .= "^CF0,20\n";
            $prnContent .= "^FO50,100^FD" . $name . "^FS\n";
            $prnContent .= "^FO50,130^FDWt: " . $weight . "^FS\n";
            $prnContent .= "^FO50,160^FDCat: " . $category . "^FS\n";
            $prnContent .= "^FO300,50^BQN,2,4^FDQA," . $url . "^FS\n"; // QR Code
            $prnContent .= "^XZ\n\n";
        }

        $fileName = 'designs_' . date('Ymd_His') . '.prn';
        
        return response($prnContent)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    public function bulkPrint80x40(Request $request)
    {
        $ids = $request->input('selected_designs', []);
        if (empty($ids)) {
            return back()->with('error', 'No designs selected.');
        }

        $designs = Product::whereIn('id', $ids)->get();
        return view('super-admin.design.print-80x40', compact('designs'));
    }

    public function searchByImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // Max 10MB
        ]);

        $uploadedImage = $request->file('image');
        $tempPath = $uploadedImage->store('temp', 'public');
        $absolutePath = storage_path('app/public/' . $tempPath);

        try {
            $hasher = new ImageHash(new DifferenceHash());
            $uploadedHash = $hasher->hash($absolutePath);
            
            $uploadedHex = $uploadedHash->toHex();
            
            // Get all designs that have a hash
            $allDesigns = Design::whereNotNull('image_hash')->get();
            $matchedProductIds = [];
            
            foreach ($allDesigns as $design) {
                try {
                    $dbHex = $design->image_hash;
                    
                    // Pad strings if necessary
                    $len = max(strlen($uploadedHex), strlen($dbHex));
                    $h1 = str_pad($uploadedHex, $len, '0', STR_PAD_LEFT);
                    $h2 = str_pad($dbHex, $len, '0', STR_PAD_LEFT);
                    
                    $distance = 0;
                    if (extension_loaded('gmp')) {
                        $distance = gmp_hamdist('0x' . $h1, '0x' . $h2);
                    } else {
                        for ($i = 0; $i < $len; $i++) {
                            $xor = hexdec($h1[$i]) ^ hexdec($h2[$i]);
                            while ($xor > 0) {
                                $distance += $xor & 1;
                                $xor >>= 1;
                            }
                        }
                    }

                    // Distance <= 10 usually means identical or slightly resized/cropped
                    if ($distance <= 10) {
                        $matchedProductIds[] = $design->product_id;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            // Cleanup temp file
            Storage::disk('public')->delete($tempPath);
            
            if (empty($matchedProductIds)) {
                return redirect()->route('super-admin.design.index')->with('error', 'No matching designs found.');
            }
            
            // Redirect to index with the matched IDs to filter
            return redirect()->route('super-admin.design.index', ['matched_ids' => implode(',', $matchedProductIds)]);

        } catch (\Exception $e) {
            Storage::disk('public')->delete($tempPath);
            return redirect()->route('super-admin.design.index')->with('error', 'Error analyzing image: ' . $e->getMessage());
        }
    }
}