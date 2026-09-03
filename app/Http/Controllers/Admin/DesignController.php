<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Buyer;
use App\Models\Craftman;
use App\Models\ProductSubcategory;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use App\Exports\AdminDesignExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Design;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DesignController extends Controller
{
    /**
     * Show Design Details
     */
    public function show(Product $design)
    {
        $product = $design;
        $product->load(['category', 'subcategory', 'images', 'creator', 'designs']);
        $categories = \App\Models\ProductCategory::orderBy('name')->get();
        
        $buyers = Buyer::orderBy('business_name')->get();
        $craftsmen = Craftman::orderBy('business_name')->get();
        $subCategories = ProductSubcategory::orderBy('name')->get();
        $categories = ProductCategory::orderBy('name')->get();
        
        return view('admin.design.show', compact('product', 'categories'));
    }

    /**
     * Display all products for design approval
     */
    public function index(Request $request)
    {
        $baseQuery = Product::with(['category', 'subcategory', 'images', 'creator', 'userAccess'])
            ->notFromFrozenAccounts()
            ->whereNotNull('type');

        $applyFilters = function ($q) use ($request) {
            if ($request->filled('search')) {
                $search = $request->search;
                $q->where(function ($subq) use ($search) {
                    $subq->where('product_name', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%")
                        ->orWhere('design_code', 'like', "%{$search}%")
                        ->orWhere('bp_code', 'like', "%{$search}%");
                });
            }

            if ($request->filled('filter_name')) {
                $q->where('product_name', 'like', '%' . $request->filter_name . '%');
            }
            if ($request->filled('filter_code')) {
                $q->where('product_code', 'like', '%' . $request->filter_code . '%');
            }
            if ($request->filled('filter_design_code')) {
                $q->where('design_code', 'like', '%' . $request->filter_design_code . '%');
            }
            if ($request->filled('filter_product_code')) {
                $q->where('product_code', 'like', '%' . $request->filter_product_code . '%');
            }
            if ($request->filled('filter_category')) {
                $q->where('product_category_id', $request->filter_category);
            }
            if ($request->filled('filter_subcategory')) {
                $q->where('product_subcategory_id', $request->filter_subcategory);
            }
            if ($request->filled('filter_bp_code')) {
                $q->where('bp_code', $request->filter_bp_code);
            }
            if (!$request->filled('filter_bp_code') && $request->filled('filter_craftsman')) {
                $q->where('bp_code', $request->filter_craftsman);
            }
        };

        // --- COUNT CALCULATION (Based on search/filters but before tab) ---
        $countQuery = clone $baseQuery;
        $applyFilters($countQuery);

        $statusCounts = [
            'all' => (clone $countQuery)->count(),
            'accepted' => (clone $countQuery)->where('design_status', 'Accepted')->count(),
            'rejected' => (clone $countQuery)->where('design_status', 'Rejected')->count(),
            'pending' => (clone $countQuery)->whereNotIn('design_status', ['Accepted', 'Rejected'])->count(),
        ];

        // --- MAIN QUERY ---
        $query = clone $baseQuery;
        $applyFilters($query);

        // --- TAB FILTERING ---
        $tab = $request->get('tab', 'all');
        switch ($tab) {
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
                // No additional filtering for 'all' tab
                break;
        }

        // --- SORTING ---
        $sort = $request->get('sort', 'latest');
        if ($sort == 'name_asc') $query->orderBy('product_name', 'asc');
        elseif ($sort == 'name_desc') $query->orderBy('product_name', 'desc');
        else $query->latest();

        $products = $query->paginate(15)->withQueryString();

        $categories = ProductCategory::with(['subcategories' => function($q) {
            $q->withCount('products');
        }])->withCount('products')->orderBy('name')->get();

        
        $buyers = Buyer::orderBy('business_name')->get();
        $craftsmen = Craftman::orderBy('business_name')->get();
        $subCategories = ProductSubcategory::orderBy('name')->get();
        $categories = ProductCategory::orderBy('name')->get();
        
        return view('admin.design.index', compact('products', 'statusCounts', 'buyers', 'craftsmen', 'subCategories', 'categories'));
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

        // Update the Product table
        $qrPath = null;
        try {
            if(!\Illuminate\Support\Facades\Storage::disk('public')->exists('qrcodes')){
                \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('qrcodes');
            }
            $qrUrl = rtrim(config('app.url'), '/') . route('admin.design.show', $product->id, false);

            // Try PNG format first (requires Imagick), fall back to SVG
            try {
                $qrPath = 'qrcodes/' . $designCode . '.png';
                \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(300)->margin(2)
                    ->generate($qrUrl, storage_path('app/public/' . $qrPath));
            } catch (\Exception $e) {
                // Imagick not available, try SVG format
                $qrPath = 'qrcodes/' . $designCode . '.svg';
                $svgContent = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(300)->margin(2)->generate($qrUrl);
                \Illuminate\Support\Facades\Storage::disk('public')->put($qrPath, $svgContent);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('QR code generation failed for ' . $designCode . ': ' . $e->getMessage());
            $qrPath = null;
        }

        $product->update([
            'design_code' => $designCode,
            'design_status' => 'Accepted',
            'qr_code' => $qrPath,
            'accepted_by' => auth()->guard('admin')->id() ?? auth()->id(),
        ]);

        // Create the Design record
        \App\Models\Design::updateOrCreate(
            ['product_id' => $product->id],
            [
                'design_code' => $designCode,
                'design_name' => $product->product_name,
                'image' => $product->images->first() ? $product->images->first()->path : null,
                'qr_code' => $qrPath,
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
                $adminName = \Illuminate\Support\Facades\Auth::user()->name ?? 'Admin';
                $recipient->notify(new \App\Notifications\DesignApproved($product, $adminName));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to notify design creator: ' . $e->getMessage());
        }

        return ['success' => true, 'message' => "Design accepted with code {$designCode}."];
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
    public function export(Request $request)
    {
        return Excel::download(new AdminDesignExport($request), 'AdminDesignExport_' . now()->format('d-m-Y') . '.xlsx');
    }

    public function printSelected(Request $request)
    {
        $ids = $request->input('selected_designs', []);
        $designs = Product::whereIn('id', $ids)->with(['category', 'subcategory', 'images'])->get();
        
        $buyers = Buyer::orderBy('business_name')->get();
        $craftsmen = Craftman::orderBy('business_name')->get();
        $subCategories = ProductSubcategory::orderBy('name')->get();
        $categories = ProductCategory::orderBy('name')->get();
        
        return view('admin.design.print-selected', compact('designs'));
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
            $ids = $request->input('selected_designs', []);
            
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
            \Log::error('Unlock Designs Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
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
            // Temporarily removed status check to debug visibility
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
    /**
     * Toggle lock status for Design (Product) image
     */
    public function toggleLock(\App\Models\Product $product)
    {
        $product->update(['is_locked' => !$product->is_locked]);

        $status = $product->is_locked ? 'locked' : 'unlocked';
        return redirect()->back()->with('success', "Design image has been {$status}.");
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
                    $qrUrl = rtrim(config('app.url'), '/') . route('admin.design.show', $product->id, false);

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
            $url = route('admin.design.show', $product->id);

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
        
        $buyers = Buyer::orderBy('business_name')->get();
        $craftsmen = Craftman::orderBy('business_name')->get();
        $subCategories = ProductSubcategory::orderBy('name')->get();
        $categories = ProductCategory::orderBy('name')->get();
        
        return view('admin.design.print-80x40', compact('designs'));
    }
    
}
