<?php

use App\Http\Controllers\Admin\AdminMeetingController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;

// Process Owner Controllers
use App\Http\Controllers\ProcessOwner\RegistrationController;
use App\Http\Controllers\ProcessOwner\LoginController as ProcessOwnerLoginController;
use App\Http\Controllers\Auth\BuyerAuthController as BuyerLoginController;
use App\Http\Controllers\User\LoginController as UserLoginController;

// Admin Controllers
use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\ForgotPasswordController;
use App\Http\Controllers\Admin\BuyerController as AdminBuyerController;
use App\Http\Controllers\Admin\CraftmanController as AdminCraftmanController;
use App\Http\Controllers\Admin\KeyUserController as AdminKeyUserController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\DesignController as AdminDesignController;
use App\Http\Controllers\Admin\CatalogueController as AdminCatalogueController;
use App\Http\Controllers\Admin\WorkOrderController as AdminWorkOrderController;

// Super Admin Controllers
use App\Http\Controllers\SuperAdmin\LoginController as SuperAdminLoginController;
use App\Http\Controllers\SuperAdmin\BuyerController;
use App\Http\Controllers\SuperAdmin\CraftmanController;
use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\KeyUserController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\SuperAdmin\DesignController as SuperAdminDesignController;
use App\Http\Controllers\SuperAdmin\CatalogueController as SuperAdminCatalogueController;
use App\Http\Controllers\SuperAdmin\WorkOrderController;
use App\Http\Controllers\SuperAdmin\PurchaseOrderController as SuperAdminPurchaseOrderController;
use App\Http\Controllers\SuperAdmin\ProductController as SuperAdminProductController;
use App\Http\Controllers\SuperAdmin\UserCredentialController;

// Craftsman Controllers
use App\Http\Controllers\Craftsman\LoginController as CraftsmanLoginController;
use App\Http\Controllers\Craftsman\WorkOrderController as CraftsmanWorkOrderController;

// Key User Controllers
use App\Http\Controllers\KeyUser\LoginController as KeyUserLoginController;
use App\Http\Controllers\KeyUser\WorkOrderController as KeyUserWorkOrderController;
use App\Http\Controllers\KeyUser\ProductController as KeyUserProductController;

// Purchase Order Controllers
use App\Http\Controllers\Admin\PurchaseOrderController as AdminPurchaseOrderController;
use App\Http\Controllers\Buyer\BuyerMeetingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Craftsman\CraftsmanMeetingController;
use App\Http\Controllers\Craftsman\PurchaseOrderController as CraftsmanPurchaseOrderController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\SuperAdmin\SuperAdminMeetingController;
use App\Http\Controllers\Public\RegistrationController as PublicRegistrationController;
use App\Http\Controllers\SuperAdmin\NewUpdatesController;
use App\Http\Controllers\SuperAdmin\RegistrationController as SuperAdminRegistrationController;

Route::get('/ajlogo.svg', function () {
    $path = public_path('images/ajlogo.svg');

    if (!File::exists($path)) {
        abort(404);
    }

    return response()->file($path, [
        'Content-Type' => 'image/svg+xml',
    ]);
});

Route::middleware(['auth.any'])->get('/video-call/{room_id}', [MeetingController::class, 'join'])->name('video.join');
Route::get('/api/live-gold-rates', function () {
    try {
        // 1. Fetch the real website content
        $response = Http::get('https://thejewellersassociation.org/');
        $html = $response->body();

        // 2. Clean the HTML (remove extra spaces/tags) to make searching easier
        $html = strip_tags($html);
        $html = str_replace('&nbsp;', ' ', $html);

        // 3. Match 24K Gold (Look for "999" which is the purity code for 24K)
        // This regex looks for 999 followed by a number like 76,500
        preg_match('/999\s*([\d,]+)/', $html, $goldMatches);

        // 4. Match Silver (Look for "SILVER" followed by a number like 92,000)
        preg_match('/SILVER\s*([\d,]+)/', $html, $silverMatches);

        // 5. Convert strings to clean numbers
        $gold24k_10g = (float) str_replace(',', '', $goldMatches[1] ?? 0);
        $silver_1kg = (float) str_replace(',', '', $silverMatches[1] ?? 0);

        // 6. Return the correct Gram rates
        return response()->json([
            'gold24' => $gold24k_10g / 10,     // Convert 10g to 1g
            'silver' => $silver_1kg / 1000,   // Convert 1kg to 1g
            'source' => 'The Jewellers Association'
        ]);
    } catch (\Exception $e) {
        return response()->json(['gold24' => 0, 'silver' => 0, 'error' => $e->getMessage()]);
    }
});
// Language Switcher Route (global, no auth required)
Route::get('/language/{locale}', [\App\Http\Controllers\LanguageSwitchController::class, 'switch'])->name('language.switch');

// Account frozen page route
Route::get('/account-frozen', function () {
    return view('account-frozen');
})->name('account-frozen');

// --- BUYER PANEL ---
Route::middleware(['auth:buyer'])->prefix('buyer')->as('buyer.')->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/send', [ChatController::class, 'store'])->name('chat.store');

    // ADD THIS LINE
    Route::get('/chat/start/{receiver_id}/{type?}', [ChatController::class, 'startChat'])->name('chat.start');
    Route::get('/chat/search', [ChatController::class, 'searchUsers'])->name('chat.search');
});

// --- CRAFTSMAN PANEL ---
Route::middleware(['auth:craftsman'])->prefix('craftsman')->as('craftsman.')->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/send', [ChatController::class, 'store'])->name('chat.store');


    // ADD THIS LINE
    Route::get('/chat/start/{receiver_id}/{type?}', [ChatController::class, 'startChat'])->name('chat.start');
    Route::get('/chat/search', [ChatController::class, 'searchUsers'])->name('chat.search');
});
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [App\Http\Controllers\UnifiedLoginController::class, 'showLoginForm'])->name('home');
Route::post('/login', [App\Http\Controllers\UnifiedLoginController::class, 'login'])->name('unified.login');
Route::view('/privacy-policy', 'legal');
Route::view('/contact', 'contact');

// Quick debug route
Route::get('/quick-debug', function () {
    $products = \App\Models\Product::all();
    $result = [];
    foreach ($products as $p) {
        $result[] = [
            'id' => $p->id,
            'product_code' => $p->product_code,
            'product_name' => $p->product_name,
            'created_by' => $p->created_by,
            'design_status' => $p->design_status,
            'design_code' => $p->design_code
        ];
    }
    return response()->json($result);
});

// Craftsman catalogue test route
Route::get('/test-craftsman-catalogue', function () {
    $craftsman = \App\Models\Craftman::first();
    if (!$craftsman) {
        return response()->json(['error' => 'No craftsmen found']);
    }

    $theirProducts = \App\Models\Product::where('created_by', $craftsman->id)
        ->where('design_status', 'Accepted')
        ->whereNotNull('design_code')
        ->get();

    return response()->json([
        'craftsman_id' => $craftsman->id,
        'craftsman_name' => $craftsman->full_name ?? $craftsman->name,
        'their_accepted_products_count' => $theirProducts->count(),
        'products' => $theirProducts->map(function ($p) {
            return [
                'id' => $p->id,
                'product_code' => $p->product_code,
                'product_name' => $p->product_name,
                'design_code' => $p->design_code,
                'created_by' => $p->created_by
            ];
        })
    ]);
});

// Test route for design functionality
Route::get('/test-design', [SuperAdminDesignController::class, 'test']);

// Test route for debugging
Route::get('/test-category-products/{categoryId}', function ($categoryId) {
    $category = \App\Models\ProductCategory::find($categoryId);
    if (!$category) {
        return response()->json(['error' => 'Category not found']);
    }

    $products = \App\Models\Product::with('subcategory')
        ->whereHas('category', function ($query) use ($category) {
            $query->where('name', $category->name);
        })
        ->get();

    return response()->json([
        'category' => $category,
        'products' => $products,
        'product_count' => $products->count()
    ]);
});


// Pincode fetch route
Route::get('/fetch-pincode/{pincode}', [App\Http\Controllers\PincodeController::class, 'fetch'])->name('fetch-pincode');



Route::prefix('process-owner')->name('process-owner.')->group(function () {
    // Registration Routes
    Route::get('/register', [RegistrationController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegistrationController::class, 'register']);

    // Login Routes
    Route::get('/login', [ProcessOwnerLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [ProcessOwnerLoginController::class, 'login']);

    // Protected Routes
    Route::middleware(['auth:process_owner', 'check.account.frozen'])->group(function () {
        
        // FIXED: Combined into one authoritative dashboard path that executes RegistrationController@index
        Route::get('/dashboard', [RegistrationController::class, 'index'])->name('dashboard');
        
        Route::post('/logout', [ProcessOwnerLoginController::class, 'logout'])->name('logout');

        // Super Admin Creation Route (only for process owners)
        Route::post('/create-super-admin', [RegistrationController::class, 'createSuperAdmin'])->name('create-super-admin');
    });
});

// User Public Routes
Route::prefix('user')->name('user.')->group(function () {
    Route::get('/login', [UserLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [UserLoginController::class, 'login']);

    // Forgot Password Routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request')->defaults('role', 'user');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'selectMethod'])->name('password.select-method')->defaults('role', 'user');
    Route::post('/forgot-password/send', [ForgotPasswordController::class, 'sendResetLink'])->name('password.send-reset')->defaults('role', 'user');
    Route::get('/verify-otp', [ForgotPasswordController::class, 'showVerifyForm'])->name('password.verify-form')->defaults('role', 'user');
    Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOTP'])->name('password.verify')->defaults('role', 'user');
    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset-form')->defaults('role', 'user');
    Route::post('/reset-password', [ForgotPasswordController::class, 'updatePassword'])->name('password.update')->defaults('role', 'user');
});

// Buyer Public Routes
Route::prefix('buyer')->name('buyer.')->group(function () {
    Route::get('/login', [BuyerLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [BuyerLoginController::class, 'login']);

    // Forgot Password Routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request')->defaults('role', 'buyer');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'selectMethod'])->name('password.select-method')->defaults('role', 'buyer');
    Route::post('/forgot-password/send', [ForgotPasswordController::class, 'sendResetLink'])->name('password.send-reset')->defaults('role', 'buyer');
    Route::get('/verify-otp', [ForgotPasswordController::class, 'showVerifyForm'])->name('password.verify-form')->defaults('role', 'buyer');
    Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOTP'])->name('password.verify')->defaults('role', 'buyer');
    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset-form')->defaults('role', 'buyer');
    Route::post('/reset-password', [ForgotPasswordController::class, 'updatePassword'])->name('password.update')->defaults('role', 'buyer');
});

// Key User Routes
Route::prefix('key-user')->name('key-user.')->group(function () {
    // Login Routes
    Route::get('/login', [KeyUserLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [KeyUserLoginController::class, 'login']);

    // Forgot Password Routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request')->defaults('role', 'key-user');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'selectMethod'])->name('password.select-method')->defaults('role', 'key-user');
    Route::post('/forgot-password/send', [ForgotPasswordController::class, 'sendResetLink'])->name('password.send-reset')->defaults('role', 'key-user');
    Route::get('/verify-otp', [ForgotPasswordController::class, 'showVerifyForm'])->name('password.verify-form')->defaults('role', 'key-user');
    Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOTP'])->name('password.verify')->defaults('role', 'key-user');
    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset-form')->defaults('role', 'key-user');
    Route::post('/reset-password', [ForgotPasswordController::class, 'updatePassword'])->name('password.update')->defaults('role', 'key-user');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Login Routes
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login']);

    // Forgot Password Routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request')->defaults('role', 'admin');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'selectMethod'])->name('password.select-method')->defaults('role', 'admin');
    Route::post('/forgot-password/send', [ForgotPasswordController::class, 'sendResetLink'])->name('password.send-reset')->defaults('role', 'admin');

    // OTP & Reset Routes
    Route::get('/verify-otp', [ForgotPasswordController::class, 'showVerifyForm'])->name('password.verify-form')->defaults('role', 'admin');
    Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOTP'])->name('password.verify')->defaults('role', 'admin');
    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset-form')->defaults('role', 'admin');
    Route::post('/reset-password', [ForgotPasswordController::class, 'updatePassword'])->name('password.update')->defaults('role', 'admin');

    // Protected Routes
    Route::middleware(['auth:admin', 'check.account.frozen'])->group(function () {
        // Craftsman Production Routes
        Route::prefix('craftsman-production')->name('craftsman-production.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\CraftsmanProductionController::class, 'index'])->name('index');
            Route::get('/{code}', [App\Http\Controllers\Admin\CraftsmanProductionController::class, 'show'])->name('show');
        });

        Route::get('/dashboard', [AdminLoginController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
        Route::post('/fcm-token', [App\Http\Controllers\Admin\FcmTokenController::class, 'saveAdminToken'])->name('fcm-token.save');

        // KYC Pending Route
        Route::get('/kyc-pending', [App\Http\Controllers\Admin\KycPendingController::class, 'index'])->name('kyc-pending.index');

        // Account Freeze/Unfreeze Routes (if admin has permission)
        Route::get('/freeze-account', [App\Http\Controllers\Admin\FreezeAccountController::class, 'index'])->name('freeze-account.index');
        Route::post('/freeze-account/toggle', [App\Http\Controllers\Admin\FreezeAccountController::class, 'toggleFreeze'])->name('freeze-account.toggle-freeze');

        // Business Partner Routes (Full CRUD for Admins)
        Route::prefix('business-partner')->group(function () {
            // Overview Route
            Route::get('/', [App\Http\Controllers\Admin\BusinessPartnerController::class, 'index'])->name('business-partner.index');

            // Buyer Routes (Full CRUD for Admins)
            Route::get('/buyer', [AdminBuyerController::class, 'index'])->name('business-partner.buyer');
            Route::get('/buyer/export', [App\Http\Controllers\Admin\BuyerController::class, 'export'])->name('business-partner.buyer.export');
            Route::get('/buyer/create', [AdminBuyerController::class, 'create'])->name('business-partner.buyer.create');
            Route::post('/buyer', [AdminBuyerController::class, 'store'])->name('business-partner.buyer.store');
            Route::get('/buyer/{buyer}', [AdminBuyerController::class, 'show'])->name('business-partner.buyer.show');
            Route::get('/buyer/{buyer}/edit', [AdminBuyerController::class, 'edit'])->name('business-partner.buyer.edit');
            Route::put('/buyer/{buyer}', [AdminBuyerController::class, 'update'])->name('business-partner.buyer.update');
            Route::delete('/buyer/{buyer}', [AdminBuyerController::class, 'destroy'])->name('business-partner.buyer.destroy');
            Route::post('/buyer/{buyer}/approve', [AdminBuyerController::class, 'approve'])->name('business-partner.buyer.approve');
            Route::post('/buyer/{buyer}/unlock', [AdminBuyerController::class, 'unlock'])->name('business-partner.buyer.unlock');
            Route::post('/buyer/print-selected', [AdminBuyerController::class, 'printSelected'])->name('business-partner.buyer.print-selected');

            // Craftman Routes (Full CRUD for Admins)

            Route::get('/craftman', [AdminCraftmanController::class, 'index'])->name('business-partner.craftman');
            Route::get('/craftman/index', [AdminCraftmanController::class, 'index'])->name('business-partner.craftman.index');
            Route::get('/craftman/export', [App\Http\Controllers\Admin\CraftmanController::class, 'export'])->name('business-partner.craftman.export');
            Route::get('/craftman/create', [AdminCraftmanController::class, 'create'])->name('business-partner.craftman.create');
            Route::post('/craftman', [AdminCraftmanController::class, 'store'])->name('business-partner.craftman.store');
            Route::get('/craftman/{craftman}', [AdminCraftmanController::class, 'show'])->name('business-partner.craftman.show');
            Route::get('/craftman/{craftman}/edit', [AdminCraftmanController::class, 'edit'])->name('business-partner.craftman.edit');
            Route::put('/craftman/{craftman}', [AdminCraftmanController::class, 'update'])->name('business-partner.craftman.update');
            Route::delete('/craftman/{craftman}', [AdminCraftmanController::class, 'destroy'])->name('business-partner.craftman.destroy');
            Route::post('/craftman/print-selected', [AdminCraftmanController::class, 'printSelected'])->name('business-partner.craftman.print-selected');

            // Craftsman Approval Routes
            Route::post('/craftman/{craftman}/approve', [AdminCraftmanController::class, 'approve'])->name('business-partner.craftman.approve');
            Route::post('/craftman/{craftman}/unlock', [AdminCraftmanController::class, 'unlock'])->name('business-partner.craftman.unlock');
        });

        // Key User Routes (Full CRUD for Admins)
        Route::prefix('key-user')->group(function () {
            Route::get('/export', [App\Http\Controllers\Admin\KeyUserController::class, 'export'])->name('key-user.export');
            Route::get('/', [AdminKeyUserController::class, 'index'])->name('key-user.index');
            Route::get('/create', [AdminKeyUserController::class, 'create'])->name('key-user.create');
            Route::post('/', [AdminKeyUserController::class, 'store'])->name('key-user.store');
            Route::get('/{keyUser}', [AdminKeyUserController::class, 'show'])->name('key-user.show');
            Route::get('/{keyUser}/edit', [AdminKeyUserController::class, 'edit'])->name('key-user.edit');
            Route::put('/{keyUser}', [AdminKeyUserController::class, 'update'])->name('key-user.update');
            Route::delete('/{keyUser}', [AdminKeyUserController::class, 'destroy'])->name('key-user.destroy');
            Route::post('/print-selected', [AdminKeyUserController::class, 'printSelected'])->name('key-user.print-selected');
        });

        // User Routes (Full CRUD for Admins)
        Route::prefix('user')->group(function () {
            Route::get('/export', [App\Http\Controllers\Admin\UserController::class, 'export'])->name('user.export');
            Route::get('/', [AdminUserController::class, 'index'])->name('user.index');
            Route::get('/create', [AdminUserController::class, 'create'])->name('user.create');
            Route::post('/', [AdminUserController::class, 'store'])->name('user.store');
            Route::get('/{user}', [AdminUserController::class, 'show'])->name('user.show');
            Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('user.edit');
            Route::put('/{user}', [AdminUserController::class, 'update'])->name('user.update');
            Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('user.destroy');
            Route::post('/print-selected', [AdminUserController::class, 'printSelected'])->name('user.print-selected');
        });

        // Product Routes (Full CRUD for Admins)
        Route::prefix('product')->group(function () {
            Route::post('products/bulk-upload', [AdminProductController::class, 'bulkUpload'])->name('product.bulk-upload');
            Route::get('/export', [App\Http\Controllers\Admin\ProductController::class, 'export'])->name('product.export');
            Route::get('products/get-all-json', [AdminProductController::class, 'getAllJson'])->name('product.get-all-json');
            Route::get('/{product}/json', [AdminProductController::class, 'getProductJson'])->name('product.get-json');
            Route::get('/', [AdminProductController::class, 'index'])->name('product.index');
            Route::get('/create', [AdminProductController::class, 'create'])->name('product.create');
            Route::post('/', [AdminProductController::class, 'store'])->name('product.store');
            Route::get('/get-subcategories', [AdminProductController::class, 'getSubcategories'])->name('product.get-subcategories');
            Route::get('/get-category-options', [AdminProductController::class, 'getCategoryOptions'])->name('product.get-category-options');
            Route::get('/{product}', [AdminProductController::class, 'show'])->name('product.show');
            Route::get('/{product}/edit', [AdminProductController::class, 'edit'])->name('product.edit');
            Route::put('/{product}', [AdminProductController::class, 'update'])->name('product.update');
            Route::delete('/{product}', [AdminProductController::class, 'destroy'])->name('product.destroy');
            Route::post('/print-selected', [AdminProductController::class, 'printSelected'])->name('product.print-selected');
        });

        // AJAX Category/Subcategory create
        Route::post('/product-category', [App\Http\Controllers\Admin\ProductCategoryController::class, 'store'])->name('product-category.store');

        // Design Routes (Read-only for Admins)
        Route::prefix('design')->group(function () {
            Route::get('export', [AdminDesignController::class, 'export'])->name('design.export');
            Route::get('/', [AdminDesignController::class, 'index'])->name('design.index');
            Route::get('/get-available-users', [AdminDesignController::class, 'getAvailableUsers'])->name('design.get-available-users');
            Route::get('/generate-missing-qrcodes', [AdminDesignController::class, 'generateMissingQRCodes'])->name('design.generate-missing-qrcodes');
            Route::get('/{design}', [AdminDesignController::class, 'show'])->name('design.show');
            Route::post('/accept/{product}', [AdminDesignController::class, 'accept'])->name('design.accept');
            Route::post('/reject/{product}', [AdminDesignController::class, 'reject'])->name('design.reject');
            Route::post('/bulk-accept', [AdminDesignController::class, 'bulkAccept'])->name('design.bulk-accept');
            Route::post('/bulk-reject', [AdminDesignController::class, 'bulkReject'])->name('design.bulk-reject');
            Route::post('/{product}/toggle-lock', [AdminDesignController::class, 'toggleLock'])->name('design.toggle-lock');
            Route::post('/print-selected', [AdminDesignController::class, 'printSelected'])->name('design.print-selected');
            Route::post('/unlock-designs', [AdminDesignController::class, 'unlockDesigns'])->name('design.unlock-designs');
            Route::post('/bulk-print-prn', [AdminDesignController::class, 'bulkPrintPRN'])->name('design.bulk-print-prn');
            Route::post('/bulk-print-80x40', [AdminDesignController::class, 'bulkPrint80x40'])->name('design.bulk-print-80x40');
        });


        Route::prefix('meetings')->name('meetings.')->group(function () {
            Route::get('/', [AdminMeetingController::class, 'index'])->name('index');
            Route::post('/', [AdminMeetingController::class, 'store'])->name('store');
            Route::post('/{meeting}/approve', [AdminMeetingController::class, 'approve'])->name('approve');
            Route::post('/{meeting}/cancel', [AdminMeetingController::class, 'cancel'])->name('cancel');
            Route::post('/{meeting_id}/answer', [AdminMeetingController::class, 'answerMeeting'])
         ->name('answer');
        });
        // Catalogue Routes (Read-only for Admins)
        Route::prefix('catalogue')->group(function () {
            Route::get('/export', [AdminCatalogueController::class, 'export'])->name('catalogue.export');
            Route::get('/', [AdminCatalogueController::class, 'index'])->name('catalogue.index');
            Route::get('/{catalogue}', [AdminCatalogueController::class, 'show'])->name('catalogue.show');
            Route::get('/{catalogue}/edit', [AdminCatalogueController::class, 'edit'])->name('catalogue.edit');
            Route::put('/{catalogue}', [AdminCatalogueController::class, 'update'])->name('catalogue.update');
            Route::delete('/{catalogue}', [AdminCatalogueController::class, 'destroy'])->name('catalogue.destroy');
            Route::post('/print-selected', [AdminCatalogueController::class, 'printSelected'])->name('catalogue.print-selected');
        });

        // Purchase Order Routes
        Route::prefix('purchase-order')->name('purchase-order.')->group(function () {

            // 1. AJAX & Bulk Routes (MUST be at the top)
            Route::get('/get-products-by-category', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'getProductsByCategory'])
                ->name('get-products-by-category');
            Route::get('/get-designs-by-product', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'getDesignsByProduct'])
                ->name('get-designs-by-product');
            Route::get('/get-product-by-design-code', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'getProductByDesignCode'])
                ->name('get-product-by-design-code');

            // NEW: Route for Bulk Allocation logic
            Route::post('/bulk-allocate', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'bulkAllocate'])
                ->name('bulk-allocate');
            Route::post('/{purchaseOrder}/reallocate', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'reallocate'])->name('reallocate');

            Route::get('/export', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'export'])->name('export');
            Route::get('/', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'store'])->name('store');

            // 2. Resource routes with {purchaseOrder} ID must stay below static routes
            Route::get('/{purchaseOrder}', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'show'])->name('show');
            Route::get('/{purchaseOrder}/print', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'print'])->name('print');
            Route::get('/{purchaseOrder}/edit', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'edit'])->name('edit');
            Route::put('/{purchaseOrder}', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'update'])->name('update');
            Route::delete('/{purchaseOrder}', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'destroy'])->name('destroy');
            Route::post('/{purchaseOrder}/complete-items', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'completeItems'])->name('complete-items');

            // Single Allocation & Approval
            Route::get('/{purchaseOrder}/allocate', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'allocate'])->name('allocate');
            Route::post('/{purchaseOrder}/allocate', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'allocateStore'])->name('allocate.store');
            Route::post('/{purchaseOrder}/approve', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'approve'])->name('approve');
            Route::post('/bulk-approve', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'bulkApprove'])->name('bulk-approve');
            Route::post('/bulk-complete', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'bulkComplete'])->name('bulk-complete');
            Route::post('/bulk-print', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'bulkPrint'])->name('bulk-print');
            Route::get('/{purchaseOrder}/copy', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'copy'])->name('copy');
        });

        // Work Order Routes
        Route::prefix('work-order')->group(function () {
            // Bulk Upload Routes
            Route::post('/bulk-allocate', [AdminWorkOrderController::class, 'bulkAllocate'])->name('work-order.bulk-allocate');
            Route::post('/bulk-print', [AdminWorkOrderController::class, 'bulkPrint'])->name('work-order.bulk-print');
            Route::get('/bulk-allocate-form', [AdminWorkOrderController::class, 'bulkAllocateForm'])->name('work-order.bulk-allocate-form');
            Route::get('/upload', [AdminWorkOrderController::class, 'showUploadForm'])->name('work-order.upload');
            Route::post('/import', [AdminWorkOrderController::class, 'import'])->name('work-order.import');
            Route::get('/download-template', [AdminWorkOrderController::class, 'downloadTemplate'])->name('work-order.download-template');

            // OrderList Bulk Upload Routes
            Route::get('/bulk-upload', [AdminWorkOrderController::class, 'showBulkUpload'])->name('work-order.bulk-upload');
            Route::post('/import-order-list', [AdminWorkOrderController::class, 'importOrderList'])->name('work-order.import-order-list');

            Route::get('/export', [AdminWorkOrderController::class, 'export'])->name('work-order.export');
            Route::get('/', [AdminWorkOrderController::class, 'index'])->name('work-order.index');
            Route::get('/load-orders', [AdminWorkOrderController::class, 'loadWorkOrdersAjax'])->name('work-order.load-orders');
            Route::get('/create', [AdminWorkOrderController::class, 'create'])->name('work-order.create');
            Route::post('/', [AdminWorkOrderController::class, 'store'])->name('work-order.store');
            Route::get('/get-product-details', [AdminWorkOrderController::class, 'getProductDetails'])->name('work-order.get-product-details');
            Route::get('/{workOrder}', [AdminWorkOrderController::class, 'show'])->name('work-order.show');
            Route::get('/{workOrder}/print', [AdminWorkOrderController::class, 'print'])->name('work-order.print');
            Route::get('/{workOrder}/edit', [AdminWorkOrderController::class, 'edit'])->name('work-order.edit');
            Route::put('/{workOrder}', [AdminWorkOrderController::class, 'update'])->name('work-order.update');
            Route::delete('/{workOrder}', [AdminWorkOrderController::class, 'destroy'])->name('work-order.destroy');
            Route::get('/{workOrder}/allocate', [AdminWorkOrderController::class, 'allocateForm'])->name('work-order.allocate.form');
            Route::post('/{workOrder}/allocate', [AdminWorkOrderController::class, 'allocate'])->name('work-order.allocate');
            Route::post('/{workOrder}/approve', [AdminWorkOrderController::class, 'approve'])->name('work-order.approve');
            Route::post('/bulk-approve', [AdminWorkOrderController::class, 'bulkApprove'])->name('work-order.bulk-approve');
            Route::post('/bulk-complete', [AdminWorkOrderController::class, 'bulkComplete'])->name('work-order.bulk-complete');
            Route::get('/{workOrder}/reallocate', [AdminWorkOrderController::class, 'reallocateForm'])->name('work-order.reallocate.form');
            Route::post('/{workOrder}/reallocate', [AdminWorkOrderController::class, 'reallocate'])->name('work-order.reallocate');
            Route::get('/{workOrder}/copy', [AdminWorkOrderController::class, 'copy'])->name('work-order.copy');
        });

        // Stock Order Routes (New Flow)
        Route::prefix('stock-order')->name('stock-order.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\StockOrderController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\StockOrderController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\StockOrderController::class, 'store'])->name('store');
            Route::get('/lookup/{code}', [\App\Http\Controllers\Admin\StockOrderController::class, 'lookup'])->name('lookup')->where('code', '.*');
            Route::get('/{stockOrder}', [\App\Http\Controllers\Admin\StockOrderController::class, 'show'])->name('show');
            Route::get('/{stockOrder}/edit', [\App\Http\Controllers\Admin\StockOrderController::class, 'edit'])->name('edit');
            Route::put('/{stockOrder}', [\App\Http\Controllers\Admin\StockOrderController::class, 'update'])->name('update');
            Route::post('/{stockOrder}/allocate', [\App\Http\Controllers\Admin\StockOrderController::class, 'allocate'])->name('allocate');
            Route::post('/{stockOrder}/allocate-items', [\App\Http\Controllers\Admin\StockOrderController::class, 'allocateItems'])->name('allocate-items');
            Route::post('/{stockOrder}/status', [\App\Http\Controllers\Admin\StockOrderController::class, 'updateStatus'])->name('status');
            Route::post('/{stockOrder}/item/{item}/status', [\App\Http\Controllers\Admin\StockOrderController::class, 'updateItemStatus'])->name('item-status');
            Route::post('/bulk-allocate', [\App\Http\Controllers\Admin\StockOrderController::class, 'bulkAllocate'])->name('bulk-allocate');
            Route::post('/bulk-complete', [\App\Http\Controllers\Admin\StockOrderController::class, 'bulkComplete'])->name('bulk-complete');
        });

        Route::prefix('repairs')->name('repairs.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\RepairController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\RepairController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\RepairController::class, 'store'])->name('store');
            Route::get('/{repair}', [App\Http\Controllers\Admin\RepairController::class, 'show'])->name('show');
            Route::get('/{repair}/edit', [App\Http\Controllers\Admin\RepairController::class, 'edit'])->name('edit');
            Route::put('/{repair}', [App\Http\Controllers\Admin\RepairController::class, 'update'])->name('update');
            Route::delete('/{repair}', [App\Http\Controllers\Admin\RepairController::class, 'destroy'])->name('destroy');
            Route::post('/{repair}/accept', [App\Http\Controllers\Admin\RepairController::class, 'accept'])->name('accept');
            Route::post('/{repair}/reject', [App\Http\Controllers\Admin\RepairController::class, 'reject'])->name('reject');
            Route::post('/{repair}/allocate', [App\Http\Controllers\Admin\RepairController::class, 'allocate'])->name('allocate');
            Route::post('/{repair}/complete', [App\Http\Controllers\Admin\RepairController::class, 'complete'])->name('complete');
            Route::post('/bulk-complete', [App\Http\Controllers\Admin\RepairController::class, 'bulkComplete'])->name('bulk-complete');
        });
        // Finance Routes (Dummy for now)
        Route::prefix('finance')->group(function () {
            Route::get('/', [AdminLoginController::class, 'finance'])->name('finance.index');
        });

        // Favorites Routes
        Route::prefix('favorites')->name('favorites.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\FavoriteController::class, 'index'])->name('index');
            Route::get('/show/{user_id}/{user_type}', [App\Http\Controllers\Admin\FavoriteController::class, 'show'])->name('show');
            Route::delete('/{id}', [App\Http\Controllers\Admin\FavoriteController::class, 'destroy'])->name('destroy');
        });

        // Chat Routes
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
        Route::post('/chat/send', [ChatController::class, 'store'])->name('chat.store');
        Route::get('/chat/start/{receiver_id}/{type?}', [ChatController::class, 'startChat'])->name('chat.start');
        Route::get('/chat/search', [ChatController::class, 'searchUsers'])->name('chat.search');
        Route::delete('/chat/message/{id}', [ChatController::class, 'destroy'])->name('chat.message.destroy');
        Route::delete('/chat/conversation/{id}', [ChatController::class, 'destroyConversation'])->name('chat.conversation.destroy');
    });
});

// Super Admin Routes
Route::prefix('super-admin')->name('super-admin.')->group(function () {
    // Login Routes
    Route::get('/login', [SuperAdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [SuperAdminLoginController::class, 'login']);

    // Forgot Password Routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request')->defaults('role', 'super-admin');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'selectMethod'])->name('password.select-method')->defaults('role', 'super-admin');
    Route::post('/forgot-password/send', [ForgotPasswordController::class, 'sendResetLink'])->name('password.send-reset')->defaults('role', 'super-admin');
    Route::get('/verify-otp', [ForgotPasswordController::class, 'showVerifyForm'])->name('password.verify-form')->defaults('role', 'super-admin');
    Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOTP'])->name('password.verify')->defaults('role', 'super-admin');
    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset-form')->defaults('role', 'super-admin');
    Route::post('/reset-password', [ForgotPasswordController::class, 'updatePassword'])->name('password.update')->defaults('role', 'super-admin');

    // Protected Routes
    Route::middleware(['auth:super_admin', 'check.account.frozen'])->group(function () {
        // Craftsman Production Routes
        Route::prefix('craftsman-production')->name('craftsman-production.')->group(function () {
            Route::get('/', [App\Http\Controllers\SuperAdmin\CraftsmanProductionController::class, 'index'])->name('index');
            Route::get('/{code}', [App\Http\Controllers\SuperAdmin\CraftsmanProductionController::class, 'show'])->name('show');
        });

        Route::get('/dashboard', [SuperAdminLoginController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard/stats', [SuperAdminLoginController::class, 'getDashboardStats'])->name('dashboard.stats');
        Route::get('/dashboard/calendar-data', [SuperAdminLoginController::class, 'getCalendarData'])->name('dashboard.calendar-data');
        Route::post('/logout', [SuperAdminLoginController::class, 'logout'])->name('logout');
        Route::post('/fcm-token', [App\Http\Controllers\Admin\FcmTokenController::class, 'saveSuperAdminToken'])->name('fcm-token.save');

        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
        Route::post('/chat/send', [ChatController::class, 'store'])->name('chat.store');
        Route::get('/chat/start/{receiver_id}/{type?}', [ChatController::class, 'startChat'])->name('chat.start');
        Route::get('/chat/search', [ChatController::class, 'searchUsers'])->name('chat.search');
        Route::delete('/chat/message/{id}', [ChatController::class, 'destroy'])->name('chat.message.destroy');
        Route::delete('/chat/conversation/{id}', [ChatController::class, 'destroyConversation'])->name('chat.conversation.destroy');

        // KYC Pending Route
        Route::get('/kyc-pending', [App\Http\Controllers\SuperAdmin\KycPendingController::class, 'index'])->name('kyc-pending.index');

        // Account Freeze/Unfreeze Routes
        Route::get('/freeze-account', [App\Http\Controllers\SuperAdmin\FreezeAccountController::class, 'index'])->name('freeze-account.index');
        Route::post('/freeze-account/toggle', [App\Http\Controllers\SuperAdmin\FreezeAccountController::class, 'toggleFreeze'])->name('freeze-account.toggle-freeze');

        // Test route for workorder product lookup
        Route::get('/test-workorder-lookup/{code}', function ($code) {
            $product = \App\Models\Product::with('images')
                ->where('product_code', $code)
                ->orWhere('design_code', $code)
                ->first();

            if (!$product) {
                return response()->json(['success' => false, 'message' => 'Product not found']);
            }

            // Handle image URL construction
            $firstImage = null;
            if ($product->images->first()) {
                $imagePath = $product->images->first()->path;

                if (!empty($imagePath)) {
                    if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                        $firstImage = $imagePath;
                    } elseif (strpos($imagePath, 'images/work-orders/') === 0) {
                        $firstImage = asset($imagePath);
                    } elseif (strpos($imagePath, 'images/') === 0) {
                        $firstImage = asset($imagePath);
                    } elseif (strpos($imagePath, 'storage/') === 0) {
                        $firstImage = asset($imagePath);
                    } else {
                        $firstImage = asset('storage/' . $imagePath);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'product' => [
                    'product_name' => $product->product_name,
                    'design_code' => $product->design_code,
                    'product_code' => $product->product_code,
                    'product_image_url' => $firstImage,
                    'image_path_raw' => $product->images->first() ? $product->images->first()->path : null,
                    'product_category_id' => $product->product_category_id,
                    'subcategory_id' => $product->product_subcategory_id,
                    'images_count' => $product->images->count(),
                ]
            ]);
        });

        // Business Partner Routes
        Route::prefix('business-partner')->group(function () {
            // Overview Route
            Route::get('/', [BuyerController::class, 'businessPartnerIndex'])->name('business-partner.index');

            // Buyer Routes
            Route::get('/buyer/export', [BuyerController::class, 'export'])->name('business-partner.buyer.export');
            Route::get('/buyer/print-all', [BuyerController::class, 'printAll'])->name('business-partner.buyer.print-all');
            Route::get('/buyer', [BuyerController::class, 'index'])->name('business-partner.buyer');
            Route::get('/buyer/create', [BuyerController::class, 'create'])->name('business-partner.buyer.create');
            Route::post('/buyer', [BuyerController::class, 'store'])->name('business-partner.buyer.store');
            Route::get('/buyer/{buyer}', [BuyerController::class, 'show'])->name('business-partner.buyer.show');
            Route::get('/buyer/{buyer}/print', [BuyerController::class, 'print'])->name('business-partner.buyer.print');
            Route::post('/buyer/print-selected', [BuyerController::class, 'printSelected'])->name('business-partner.buyer.print-selected');

            Route::get('/buyer/{buyer}/edit', [BuyerController::class, 'edit'])->name('business-partner.buyer.edit');
            Route::put('/buyer/{buyer}', [BuyerController::class, 'update'])->name('business-partner.buyer.update');
            Route::delete('/buyer/{buyer}', [BuyerController::class, 'destroy'])->name('business-partner.buyer.destroy');

            // Buyer Approval Routes
            Route::post('/buyer/{buyer}/approve', [BuyerController::class, 'approve'])->name('business-partner.buyer.approve');
            Route::post('/buyer/{buyer}/unlock', [BuyerController::class, 'unlock'])->name('business-partner.buyer.unlock');

            // Craftman Routes
            Route::get('/craftman', [CraftmanController::class, 'index'])->name('business-partner.craftman');
            Route::get('/craftman/create', [CraftmanController::class, 'create'])->name('business-partner.craftman.create');
            Route::post('/craftman', [CraftmanController::class, 'store'])->name('business-partner.craftman.store');
            Route::get('/craftman/{craftman}', [CraftmanController::class, 'show'])->name('business-partner.craftman.show');
            Route::get('/craftman/{craftman}/edit', [CraftmanController::class, 'edit'])->name('business-partner.craftman.edit');
            Route::put('/craftman/{craftman}', [CraftmanController::class, 'update'])->name('business-partner.craftman.update');
            Route::delete('/craftman/{craftman}', [CraftmanController::class, 'destroy'])->name('business-partner.craftman.destroy');
            Route::post('/craftman/print-selected', [CraftmanController::class, 'printSelected'])->name('business-partner.craftman.print-selected');

            // Craftsman Approval Routes
            Route::post('/craftman/{craftman}/approve', [CraftmanController::class, 'approve'])->name('business-partner.craftman.approve');
            Route::post('/craftman/{craftman}/unlock', [CraftmanController::class, 'unlock'])->name('business-partner.craftman.unlock');
        });

        // Admin Routes
        Route::prefix('admin')->group(function () {
            Route::get('/', [AdminController::class, 'index'])->name('admin.index');
            Route::get('/create', [AdminController::class, 'create'])->name('admin.create');
            Route::post('/category', [AdminController::class, 'storeCategory'])->name('admin.storeCategory');
            Route::post('/', [AdminController::class, 'store'])->name('admin.store');
            Route::get('/{admin}', [AdminController::class, 'show'])->name('admin.show');
            Route::get('/{admin}/edit', [AdminController::class, 'edit'])->name('admin.edit');
            Route::put('/{admin}', [AdminController::class, 'update'])->name('admin.update');
            Route::delete('/{admin}', [AdminController::class, 'destroy'])->name('admin.destroy');
            Route::post('/print-selected', [AdminController::class, 'printSelected'])->name('admin.print-selected');
        });

        // Key User Routes
        Route::prefix('key-user')->group(function () {
            Route::get('/', [KeyUserController::class, 'index'])->name('key-user.index');
            Route::get('/create', [KeyUserController::class, 'create'])->name('key-user.create');
            Route::post('/', [KeyUserController::class, 'store'])->name('key-user.store');
            Route::get('/{keyUser}', [KeyUserController::class, 'show'])->name('key-user.show');
            Route::get('/{keyUser}/edit', [KeyUserController::class, 'edit'])->name('key-user.edit');
            Route::put('/{keyUser}', [KeyUserController::class, 'update'])->name('key-user.update');
            Route::delete('/{keyUser}', [KeyUserController::class, 'destroy'])->name('key-user.destroy');
            Route::post('/print-selected', [KeyUserController::class, 'printSelected'])->name('key-user.print-selected');
        });

        // User Routes
        Route::prefix('user')->group(function () {
            Route::get('/', [SuperAdminUserController::class, 'index'])->name('user.index');
            Route::get('/create', [SuperAdminUserController::class, 'create'])->name('user.create');
            Route::post('/', [SuperAdminUserController::class, 'store'])->name('user.store');
            Route::get('/{user}', [SuperAdminUserController::class, 'show'])->name('user.show');
            Route::get('/{user}/edit', [SuperAdminUserController::class, 'edit'])->name('user.edit');
            Route::put('/{user}', [SuperAdminUserController::class, 'update'])->name('user.update');
            Route::delete('/{user}', [SuperAdminUserController::class, 'destroy'])->name('user.destroy');
            Route::post('/print-selected', [SuperAdminUserController::class, 'printSelected'])->name('user.print-selected');
        });

        // Design Routes (Read-only for Super Admins)
        Route::prefix('design')->group(function () {
            Route::get('/', [SuperAdminDesignController::class, 'index'])->name('design.index');
            Route::post('/search-by-image', [SuperAdminDesignController::class, 'searchByImage'])->name('design.search-by-image');
            Route::get('/get-available-users', [SuperAdminDesignController::class, 'getAvailableUsers'])->name('design.get-available-users');
            Route::get('/generate-missing-qrcodes', [SuperAdminDesignController::class, 'generateMissingQRCodes'])->name('design.generate-missing-qrcodes');
            Route::get('/{design}', [SuperAdminDesignController::class, 'show'])->name('design.show');
            Route::post('/accept/{product}', [SuperAdminDesignController::class, 'accept'])->name('design.accept');
            Route::post('/reject/{product}', [SuperAdminDesignController::class, 'reject'])->name('design.reject');
            Route::post('/bulk-accept', [SuperAdminDesignController::class, 'bulkAccept'])->name('design.bulk-accept');
            Route::post('/bulk-reject', [SuperAdminDesignController::class, 'bulkReject'])->name('design.bulk-reject');
            Route::post('/{product}/toggle-lock', [SuperAdminDesignController::class, 'toggleLock'])->name('design.toggle-lock');
            Route::post('/print-selected', [SuperAdminDesignController::class, 'printSelected'])->name('design.print-selected');
            Route::post('/unlock-designs', [SuperAdminDesignController::class, 'unlockDesigns'])->name('design.unlock-designs');
            Route::post('/bulk-print-prn', [SuperAdminDesignController::class, 'bulkPrintPRN'])->name('design.bulk-print-prn');
            Route::post('/bulk-print-80x40', [SuperAdminDesignController::class, 'bulkPrint80x40'])->name('design.bulk-print-80x40');
        });

        // Stock Order Routes (New Flow)
        Route::prefix('stock-order')->name('stock-order.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SuperAdmin\StockOrderController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\SuperAdmin\StockOrderController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\SuperAdmin\StockOrderController::class, 'store'])->name('store');
            Route::get('/lookup/{code}', [\App\Http\Controllers\SuperAdmin\StockOrderController::class, 'lookup'])->name('lookup')->where('code', '.*');
            Route::get('/{stockOrder}', [\App\Http\Controllers\SuperAdmin\StockOrderController::class, 'show'])->name('show');
            Route::get('/{stockOrder}/edit', [\App\Http\Controllers\SuperAdmin\StockOrderController::class, 'edit'])->name('edit');
            Route::put('/{stockOrder}', [\App\Http\Controllers\SuperAdmin\StockOrderController::class, 'update'])->name('update');
            Route::post('/{stockOrder}/allocate', [\App\Http\Controllers\SuperAdmin\StockOrderController::class, 'allocate'])->name('allocate');
            Route::post('/{stockOrder}/allocate-items', [\App\Http\Controllers\SuperAdmin\StockOrderController::class, 'allocateItems'])->name('allocate-items');
            Route::post('/{stockOrder}/status', [\App\Http\Controllers\SuperAdmin\StockOrderController::class, 'updateStatus'])->name('status');
            Route::post('/{stockOrder}/item/{item}/status', [\App\Http\Controllers\SuperAdmin\StockOrderController::class, 'updateItemStatus'])->name('item-status');
            Route::delete('/{stockOrder}', [\App\Http\Controllers\SuperAdmin\StockOrderController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-allocate', [\App\Http\Controllers\SuperAdmin\StockOrderController::class, 'bulkAllocate'])->name('bulk-allocate');
            Route::post('/bulk-complete', [\App\Http\Controllers\SuperAdmin\StockOrderController::class, 'bulkComplete'])->name('bulk-complete');
        });

        // User Credentials List
        Route::get('/user-credentials', [UserCredentialController::class, 'index'])->name('user-credentials.index');
        Route::get('/user-credentials/{role}/{id}', [UserCredentialController::class, 'show'])->name('user-credentials.show');


        // Catalogue Routes (Read-only for Super Admins)
        Route::prefix('catalogue')->name('catalogue.')->group(function () {
            Route::get('/', [SuperAdminCatalogueController::class, 'index'])->name('index');
            Route::get('/{catalogue}', [SuperAdminCatalogueController::class, 'show'])->name('show');
            Route::delete('/{catalogue}', [SuperAdminCatalogueController::class, 'destroy'])->name('destroy');
            Route::post('/print-selected', [SuperAdminCatalogueController::class, 'printSelected'])->name('print-selected');
        });

        // Purchase Order Routes
        Route::prefix('purchase-order')->name('purchase-order.')->group(function () {

            // 1. AJAX & Bulk Routes (MUST be at the top)
            Route::get('/get-products-by-category', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'getProductsByCategory'])
                ->name('get-products-by-category');
            Route::get('/get-designs-by-product', [SuperAdminPurchaseOrderController::class, 'getDesignsByProduct'])
                ->name('get-designs-by-product');
            Route::get('/get-product-by-design-code', [SuperAdminPurchaseOrderController::class, 'getProductByDesignCode'])
                ->name('get-product-by-design-code');

            // NEW: Route for Bulk Allocation logic
            Route::post('/bulk-allocate', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'bulkAllocate'])
                ->name('bulk-allocate');
            Route::post('/bulk-print', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'bulkPrint'])
                ->name('bulk-print');
            Route::post('/{purchaseOrder}/reallocate', [SuperAdminPurchaseOrderController::class, 'reallocate'])->name('reallocate');

            Route::get('/', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'store'])->name('store');

            // 2. Resource routes with {purchaseOrder} ID must stay below static routes
            Route::get('/{purchaseOrder}', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'show'])->name('show');
            Route::get('/{purchaseOrder}/print', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'print'])->name('print');
            Route::get('/{purchaseOrder}/edit', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'edit'])->name('edit');
            Route::put('/{purchaseOrder}', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'update'])->name('update');
            Route::delete('/{purchaseOrder}', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'destroy'])->name('destroy');
            Route::post('/{purchaseOrder}/complete-items', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'completeItems'])->name('complete-items');

            // Single Allocation & Approval
            Route::get('/{purchaseOrder}/allocate', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'allocate'])->name('allocate');
            Route::post('/{purchaseOrder}/allocate', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'allocateStore'])->name('allocate.store');
            Route::post('/{purchaseOrder}/approve', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'approve'])->name('approve');
            Route::post('/bulk-print', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'bulkPrint'])->name('bulk-print');
            Route::post('/bulk-approve', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'bulkApprove'])->name('bulk-approve');
            Route::post('/bulk-complete', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'bulkComplete'])->name('bulk-complete');
            Route::get('/{purchaseOrder}/copy', [App\Http\Controllers\SuperAdmin\PurchaseOrderController::class, 'copy'])->name('copy');
        });

        // Repairs Routes
        Route::prefix('repairs')->name('repairs.')->group(function () {
            Route::get('/', [App\Http\Controllers\SuperAdmin\RepairController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\SuperAdmin\RepairController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\SuperAdmin\RepairController::class, 'store'])->name('store');
            Route::get('/{repair}', [App\Http\Controllers\SuperAdmin\RepairController::class, 'show'])->name('show');
            Route::get('/{repair}/edit', [App\Http\Controllers\SuperAdmin\RepairController::class, 'edit'])->name('edit');
            Route::put('/{repair}', [App\Http\Controllers\SuperAdmin\RepairController::class, 'update'])->name('update');
            Route::delete('/{repair}', [App\Http\Controllers\SuperAdmin\RepairController::class, 'destroy'])->name('destroy');
            Route::post('/{repair}/accept', [App\Http\Controllers\SuperAdmin\RepairController::class, 'accept'])->name('accept');
            Route::post('/{repair}/reject', [App\Http\Controllers\SuperAdmin\RepairController::class, 'reject'])->name('reject');
            Route::post('/{repair}/allocate', [App\Http\Controllers\SuperAdmin\RepairController::class, 'allocate'])->name('allocate');
            Route::post('/{repair}/complete', [App\Http\Controllers\SuperAdmin\RepairController::class, 'complete'])->name('complete');
            Route::post('/bulk-complete', [App\Http\Controllers\SuperAdmin\RepairController::class, 'bulkComplete'])->name('bulk-complete');
        });

        // Finance Routes (Dummy for now)
        Route::prefix('finance')->group(function () {
            Route::get('/', [SuperAdminLoginController::class, 'finance'])->name('finance.index');
        });
        // Product Routes (Super Admin)
        Route::prefix('product')->group(function () {
            Route::post('products/bulk-upload', [SuperAdminProductController::class, 'bulkUpload'])->name('product.bulk-upload');
            Route::get('products/download-template', [SuperAdminProductController::class, 'downloadTemplate'])->name('product.download-template');
            Route::get('/', [SuperAdminProductController::class, 'index'])->name('product.index');
            Route::get('/create', [SuperAdminProductController::class, 'create'])->name('product.create');
            Route::post('/', [SuperAdminProductController::class, 'store'])->name('product.store');
            Route::get('/get-subcategories', [SuperAdminProductController::class, 'getSubcategories'])->name('product.get-subcategories');
            Route::get('/get-category-options', [SuperAdminProductController::class, 'getCategoryOptions'])->name('product.get-category-options');
            Route::get('/{product}', [SuperAdminProductController::class, 'show'])->name('product.show');
            Route::get('/{product}/edit', [SuperAdminProductController::class, 'edit'])->name('product.edit');
            Route::put('/{product}', [SuperAdminProductController::class, 'update'])->name('product.update');
            Route::delete('/{product}', [SuperAdminProductController::class, 'destroy'])->name('product.destroy');
            Route::post('/print-selected', [SuperAdminProductController::class, 'printSelected'])->name('product.print-selected');
        });

        Route::post('/product-category', [\App\Http\Controllers\SuperAdmin\ProductCategoryController::class, 'store'])->name('product-category.store');
        Route::put('/product-category/{category}', [\App\Http\Controllers\SuperAdmin\ProductCategoryController::class, 'update'])->name('product-category.update');
        Route::delete('/product-category/{category}', [\App\Http\Controllers\SuperAdmin\ProductCategoryController::class, 'destroy'])->name('product-category.destroy');
        Route::put('/product-subcategory/{subcategory}', [\App\Http\Controllers\SuperAdmin\ProductCategoryController::class, 'updateSubcategory'])->name('product-subcategory.update');
        Route::delete('/product-subcategory/{subcategory}', [\App\Http\Controllers\SuperAdmin\ProductCategoryController::class, 'destroySubcategory'])->name('product-subcategory.destroy');
        Route::post('/product-category/bulk-delete', [\App\Http\Controllers\SuperAdmin\ProductCategoryController::class, 'bulkDeleteCategories'])->name('product-category.bulk-delete');
        Route::post('/product-subcategory/bulk-delete', [\App\Http\Controllers\SuperAdmin\ProductCategoryController::class, 'bulkDeleteSubcategories'])->name('product-subcategory.bulk-delete');

        // Work Order Routes
        Route::prefix('work-order')->group(function () {
            // Bulk Upload Routes
            Route::get('/upload', [WorkOrderController::class, 'showUploadForm'])->name('work-order.upload');
            Route::post('/import', [WorkOrderController::class, 'import'])->name('work-order.import');
            Route::get('/download-template', [WorkOrderController::class, 'downloadTemplate'])->name('work-order.download-template');
            Route::post('/bulk-allocate', [WorkOrderController::class, 'bulkAllocate'])->name('work-order.bulk-allocate');
            Route::get('/bulk-allocate-form', [WorkOrderController::class, 'bulkAllocateForm'])->name('work-order.bulk-allocate-form');
            Route::post('/bulk-print', [WorkOrderController::class, 'bulkPrint'])->name('work-order.bulk-print');

            // NEW: OrderList Bulk Upload Routes
            Route::get('/bulk-upload', [WorkOrderController::class, 'showBulkUpload'])->name('work-order.bulk-upload');
            Route::post('/import-order-list', [WorkOrderController::class, 'importOrderList'])->name('work-order.import-order-list');

            Route::get('/', [WorkOrderController::class, 'index'])->name('work-order.index');
            Route::get('/load-orders', [WorkOrderController::class, 'loadWorkOrdersAjax'])->name('work-order.load-orders');
            Route::get('/create', [WorkOrderController::class, 'create'])->name('work-order.create');
            Route::post('/', [WorkOrderController::class, 'store'])->name('work-order.store');
            Route::get('/get-product-details', [WorkOrderController::class, 'getProductDetails'])->name('work-order.get-product-details');
            Route::get('/{workOrder}', [WorkOrderController::class, 'show'])->name('work-order.show');
            Route::get('/{workOrder}/print', [WorkOrderController::class, 'print'])->name('work-order.print');
            Route::get('/{workOrder}/edit', [WorkOrderController::class, 'edit'])->name('work-order.edit');
            Route::put('/{workOrder}', [WorkOrderController::class, 'update'])->name('work-order.update');
            Route::delete('/{workOrder}', [WorkOrderController::class, 'destroy'])->name('work-order.destroy');
            Route::get('/{workOrder}/allocate', [WorkOrderController::class, 'allocateForm'])->name('work-order.allocate.form');
            Route::post('/{workOrder}/allocate', [WorkOrderController::class, 'allocate'])->name('work-order.allocate');
            Route::post('/{workOrder}/approve', [WorkOrderController::class, 'approve'])->name('work-order.approve');
            Route::post('/bulk-approve', [WorkOrderController::class, 'bulkApprove'])->name('work-order.bulk-approve');
            Route::post('/bulk-complete', [WorkOrderController::class, 'bulkComplete'])->name('work-order.bulk-complete');
            Route::get('/{workOrder}/reallocate', [WorkOrderController::class, 'reallocateForm'])->name('work-order.reallocate.form');
            Route::post('/{workOrder}/reallocate', [WorkOrderController::class, 'reallocate'])->name('work-order.reallocate');
            Route::get('/{workOrder}/copy', [WorkOrderController::class, 'copy'])->name('work-order.copy');
        });

        // Superadmin Routes
        Route::prefix('meetings')->name('meetings.')->group(function () {
            Route::get('/', [SuperAdminMeetingController::class, 'index'])->name('index');
            Route::post('/', [SuperAdminMeetingController::class, 'store'])->name('store');
            Route::post('/{meeting}/approve', [SuperAdminMeetingController::class, 'approve'])->name('approve');
            Route::post('/{meeting}/cancel', [SuperAdminMeetingController::class, 'cancel'])->name('cancel');
            Route::delete('/{meeting}', [SuperAdminMeetingController::class, 'destroy'])->name('destroy');
        });

        // Company Contacts
        Route::resource('company-contacts', \App\Http\Controllers\SuperAdmin\CompanyContactController::class);

        // Favorites Routes
        Route::prefix('favorites')->name('favorites.')->group(function () {
            Route::get('/', [App\Http\Controllers\SuperAdmin\FavoriteController::class, 'index'])->name('index');
            Route::get('/show/{user_id}/{user_type}', [App\Http\Controllers\SuperAdmin\FavoriteController::class, 'show'])->name('show');
            Route::delete('/{id}', [App\Http\Controllers\SuperAdmin\FavoriteController::class, 'destroy'])->name('destroy');
        });
        Route::resource('updates', NewUpdatesController::class);
    });
});


// Craftsman Routes
Route::prefix('craftsman')->name('craftsman.')->group(function () {
    // Login Routes
    Route::get('/login', [CraftsmanLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [CraftsmanLoginController::class, 'login']);

    // Forgot Password Routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request')->defaults('role', 'craftsman');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'selectMethod'])->name('password.select-method')->defaults('role', 'craftsman');
    Route::post('/forgot-password/send', [ForgotPasswordController::class, 'sendResetLink'])->name('password.send-reset')->defaults('role', 'craftsman');
    Route::get('/verify-otp', [ForgotPasswordController::class, 'showVerifyForm'])->name('password.verify-form')->defaults('role', 'craftsman');
    Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOTP'])->name('password.verify')->defaults('role', 'craftsman');
    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset-form')->defaults('role', 'craftsman');
    Route::post('/reset-password', [ForgotPasswordController::class, 'updatePassword'])->name('password.update')->defaults('role', 'craftsman');

    // Protected Routes
    Route::middleware(['auth:craftsman', 'check.account.frozen'])->group(function () {
        Route::get('/dashboard', [CraftsmanLoginController::class, 'dashboard'])
            ->name('dashboard')
            ->middleware('craftsman.permission:dashboard');

        Route::post('/logout', [CraftsmanLoginController::class, 'logout'])->name('logout');

        // Profile Routes (No permission check needed as it's personal)
        Route::get('/profile', [App\Http\Controllers\Craftsman\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [App\Http\Controllers\Craftsman\ProfileController::class, 'update'])->name('profile.update');

        // Work Order Routes for Craftsmen
        Route::prefix('work-order')->middleware('craftsman.permission:work_order')->group(function () {
            Route::get('/', [App\Http\Controllers\Craftsman\WorkOrderController::class, 'index'])->name('work-order.index');
            Route::get('/export', [App\Http\Controllers\Craftsman\WorkOrderController::class, 'export'])->name('work-order.export');
            Route::post('/print-selected', [App\Http\Controllers\Craftsman\WorkOrderController::class, 'printSelected'])->name('work-order.print-selected');
            Route::get('/{workOrder}', [App\Http\Controllers\Craftsman\WorkOrderController::class, 'show'])->name('work-order.show');
            Route::get('/{workOrder}/print', [App\Http\Controllers\Craftsman\WorkOrderController::class, 'print'])->name('work-order.print');
            Route::post('/bulk-accept', [App\Http\Controllers\Craftsman\WorkOrderController::class, 'bulkAccept'])->name('work-order.bulk-accept');
            Route::post('/bulk-reject', [App\Http\Controllers\Craftsman\WorkOrderController::class, 'bulkReject'])->name('work-order.bulk-reject');
            Route::post('/bulk-complete', [App\Http\Controllers\Craftsman\WorkOrderController::class, 'bulkComplete'])->name('work-order.bulk-complete');
            Route::post('/{workOrder}/accept', [App\Http\Controllers\Craftsman\WorkOrderController::class, 'accept'])->name('work-order.accept');
            Route::post('/{workOrder}/reject', [App\Http\Controllers\Craftsman\WorkOrderController::class, 'reject'])->name('work-order.reject');
            Route::post('/{workOrder}/complete', [App\Http\Controllers\Craftsman\WorkOrderController::class, 'complete'])->name('work-order.complete');
        });

        // Purchase Order Routes for Craftsmen
        Route::prefix('purchase-order')->middleware('craftsman.permission:purchase_order')->group(function () {
            Route::get('/', [App\Http\Controllers\Craftsman\PurchaseOrderController::class, 'index'])->name('purchase-order.index');
            Route::get('/export', [App\Http\Controllers\Craftsman\PurchaseOrderController::class, 'export'])->name('purchase-order.export');
            Route::post('/print-selected', [App\Http\Controllers\Craftsman\PurchaseOrderController::class, 'printSelected'])->name('purchase-order.print-selected');
            Route::get('/{purchaseOrder}', [App\Http\Controllers\Craftsman\PurchaseOrderController::class, 'show'])->name('purchase-order.show');
            Route::get('/{purchaseOrder}/print', [App\Http\Controllers\Craftsman\PurchaseOrderController::class, 'print'])->name('purchase-order.print');
            Route::post('/bulk-accept', [App\Http\Controllers\Craftsman\PurchaseOrderController::class, 'bulkAccept'])->name('purchase-order.bulk-accept');
            Route::post('/bulk-reject', [App\Http\Controllers\Craftsman\PurchaseOrderController::class, 'bulkReject'])->name('purchase-order.bulk-reject');
            Route::post('/bulk-complete', [App\Http\Controllers\Craftsman\PurchaseOrderController::class, 'bulkComplete'])->name('purchase-order.bulk-complete');
            Route::post('/{purchaseOrder}/process-items', [App\Http\Controllers\Craftsman\PurchaseOrderController::class, 'processItems'])->name('purchase-order.process-items');
            Route::post('/{purchaseOrder}/complete', [App\Http\Controllers\Craftsman\PurchaseOrderController::class, 'complete'])->name('purchase-order.complete');
            Route::post('/{purchaseOrder}/complete-items', [App\Http\Controllers\Craftsman\PurchaseOrderController::class, 'completeItems'])->name('purchase-order.complete-items');
        });

        // Product Routes for Craftsmen
        Route::prefix('product')->middleware('craftsman.permission:product')->group(function () {
            Route::post('products/bulk-upload', [App\Http\Controllers\Craftsman\ProductController::class, 'bulkUpload'])->name('product.bulk-upload');
            Route::get('/export', [App\Http\Controllers\Craftsman\ProductController::class, 'export'])->name('product.export');
            Route::get('/', [App\Http\Controllers\Craftsman\ProductController::class, 'index'])->name('product.index');
            Route::post('/print-selected', [App\Http\Controllers\Craftsman\ProductController::class, 'printSelected'])->name('product.print-selected');
            Route::get('/create', [App\Http\Controllers\Craftsman\ProductController::class, 'create'])->name('product.create');
            Route::post('/', [App\Http\Controllers\Craftsman\ProductController::class, 'store'])->name('product.store');
            Route::get('/{product}', [App\Http\Controllers\Craftsman\ProductController::class, 'show'])->name('product.show');
            Route::get('/{product}/edit', [App\Http\Controllers\Craftsman\ProductController::class, 'edit'])->name('product.edit');
            Route::put('/{product}', [App\Http\Controllers\Craftsman\ProductController::class, 'update'])->name('product.update');
            Route::delete('/{product}', [App\Http\Controllers\Craftsman\ProductController::class, 'destroy'])->name('product.destroy');
            Route::get('/category/{category_id}/subcategories', [App\Http\Controllers\Craftsman\ProductController::class, 'getSubcategories']);
        });

        // Product Category Routes for Craftsmen (Linked to product permission)
        Route::prefix('product-category')->middleware('craftsman.permission:product')->group(function () {
            Route::post('/', [App\Http\Controllers\Craftsman\ProductCategoryController::class, 'store'])->name('product-category.store');
            Route::get('/get-category-options', [App\Http\Controllers\Craftsman\ProductCategoryController::class, 'getCategoryOptions']);
        });

        // Design Routes for Craftsmen
        Route::prefix('design')->middleware('craftsman.permission:design')->group(function () {
            Route::get('/export', [App\Http\Controllers\Craftsman\DesignController::class, 'export'])->name('design.export');
            Route::get('/', [App\Http\Controllers\Craftsman\DesignController::class, 'index'])->name('design.index');
            Route::get('/create', [App\Http\Controllers\Craftsman\DesignController::class, 'create'])->name('design.create');
            Route::post('/', [App\Http\Controllers\Craftsman\DesignController::class, 'store'])->name('design.store');
            Route::get('/{design}', [App\Http\Controllers\Craftsman\DesignController::class, 'show'])->name('design.show');
            Route::get('/{design}/edit', [App\Http\Controllers\Craftsman\DesignController::class, 'edit'])->name('design.edit');
            Route::put('/{design}', [App\Http\Controllers\Craftsman\DesignController::class, 'update'])->name('design.update');
            Route::delete('/{design}', [App\Http\Controllers\Craftsman\DesignController::class, 'destroy'])->name('design.destroy');
        });

        // Favorites Routes
        Route::prefix('favorites')->name('favorites.')->group(function () {
            Route::get('/', [App\Http\Controllers\Craftsman\FavoriteController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Craftsman\FavoriteController::class, 'store'])->name('store');
            Route::delete('/{id}', [App\Http\Controllers\Craftsman\FavoriteController::class, 'destroy'])->name('destroy');
        });

        // Catalogue Routes for Craftsmen
        Route::prefix('catalogue')->middleware('craftsman.permission:catalogue')->group(function () {
            Route::get('/export', [App\Http\Controllers\Craftsman\CatalogueController::class, 'export'])->name('catalogue.export');
            Route::get('/', [App\Http\Controllers\Craftsman\CatalogueController::class, 'index'])->name('catalogue.index');
            Route::post('/print-selected', [App\Http\Controllers\Craftsman\CatalogueController::class, 'printSelected'])->name('catalogue.print-selected');
            Route::get('/show/{id}', [App\Http\Controllers\Craftsman\CatalogueController::class, 'show'])->name('catalogue.show');
        });

        // Purchase Order Routes for Craftsmen
        Route::prefix('purchase-order')->middleware('craftsman.permission:purchase_order')->name('purchase-order.')->group(function () {
            Route::get('/', [CraftsmanPurchaseOrderController::class, 'index'])->name('index');
            Route::get('/export', [CraftsmanPurchaseOrderController::class, 'export'])->name('export');
            Route::post('/print-selected', [CraftsmanPurchaseOrderController::class, 'printSelected'])->name('print-selected');
            Route::post('/bulk-accept', [CraftsmanPurchaseOrderController::class, 'bulkAccept'])->name('bulk-accept');
            Route::post('/bulk-reject', [CraftsmanPurchaseOrderController::class, 'bulkReject'])->name('bulk-reject');
            Route::get('/{purchaseOrder}', [CraftsmanPurchaseOrderController::class, 'show'])->name('show');
            Route::get('/{purchaseOrder}/print', [CraftsmanPurchaseOrderController::class, 'print'])->name('print');
            Route::put('/{purchaseOrder}/process-items', [CraftsmanPurchaseOrderController::class, 'processItems'])->name('process-items');
            Route::post('/{purchaseOrder}/complete', [CraftsmanPurchaseOrderController::class, 'complete'])->name('complete');
        });

        // Repairs Routes for Craftsmen
        Route::prefix('repairs')->name('repairs.')->group(function () {
            Route::get('/', [App\Http\Controllers\Craftsman\RepairController::class, 'index'])->name('index');
            Route::post('/{repair}/accept', [App\Http\Controllers\Craftsman\RepairController::class, 'accept'])->name('accept');
            Route::post('/{repair}/reject', [App\Http\Controllers\Craftsman\RepairController::class, 'reject'])->name('reject');
            Route::post('/{repair}/complete', [App\Http\Controllers\Craftsman\RepairController::class, 'complete'])->name('complete');
        });

        // Stock Order Routes for Craftsmen
        Route::prefix('stock-order')->name('stock-order.')->group(function () {
            Route::get('/', [App\Http\Controllers\Craftsman\StockOrderController::class, 'index'])->name('index');
            Route::get('/{stockOrder}', [App\Http\Controllers\Craftsman\StockOrderController::class, 'show'])->name('show');
            Route::post('/{stockOrder}/status', [App\Http\Controllers\Craftsman\StockOrderController::class, 'updateStatus'])->name('status');
            Route::post('/{stockOrder}/item/{item}/status', [App\Http\Controllers\Craftsman\StockOrderController::class, 'updateItemStatus'])->name('item-status');
            Route::post('/bulk-accept', [App\Http\Controllers\Craftsman\StockOrderController::class, 'bulkAccept'])->name('bulk-accept');
            Route::post('/bulk-reject', [App\Http\Controllers\Craftsman\StockOrderController::class, 'bulkReject'])->name('bulk-reject');
        });


        // 4. CRAFTSMAN MEETINGS
        Route::prefix('meetings')->name('meetings.')->group(function () {
            Route::get('/', [CraftsmanMeetingController::class, 'index'])->name('index');
            Route::post('/', [CraftsmanMeetingController::class, 'store'])->name('store');
        });

        Route::prefix('finance')->group(function () {
            Route::get('/', [App\Http\Controllers\Craftsman\LoginController::class, 'finance'])->name('finance.index');
        });
    });
});
// Key User Routes
Route::prefix('key-user')->name('key-user.')->group(function () {
    Route::get('/login', [App\Http\Controllers\KeyUser\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\KeyUser\LoginController::class, 'login'])->name('login.post');

    Route::middleware(['check.buyer.or.keyuser', 'check.account.frozen'])->group(function () {
        Route::post('/logout', [App\Http\Controllers\KeyUser\LoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [App\Http\Controllers\KeyUser\LoginController::class, 'dashboard'])->name('dashboard');

        // Business Partner Routes (Buyer Management for Key Users)
        Route::prefix('business-partner')->group(function () {
            // Overview Route
            Route::get('/', [App\Http\Controllers\KeyUser\BuyerController::class, 'businessPartnerIndex'])->name('business-partner.index');

            // Buyer Routes
            Route::get('/buyer', [App\Http\Controllers\KeyUser\BuyerController::class, 'index'])->name('business-partner.buyer');
            Route::get('/buyer/create', [App\Http\Controllers\KeyUser\BuyerController::class, 'create'])->name('business-partner.buyer.create');
            Route::post('/buyer', [App\Http\Controllers\KeyUser\BuyerController::class, 'store'])->name('business-partner.buyer.store');
            Route::get('/buyer/{buyer}', [App\Http\Controllers\KeyUser\BuyerController::class, 'show'])->name('business-partner.buyer.show');
            Route::get('/buyer/{buyer}/edit', [App\Http\Controllers\KeyUser\BuyerController::class, 'edit'])->name('business-partner.buyer.edit');
            Route::put('/buyer/{buyer}', [App\Http\Controllers\KeyUser\BuyerController::class, 'update'])->name('business-partner.buyer.update');
            Route::delete('/buyer/{buyer}', [App\Http\Controllers\KeyUser\BuyerController::class, 'destroy'])->name('business-partner.buyer.destroy');
        });

        // Key User Management Routes (for managing other key users)
        Route::prefix('key-user-management')->group(function () {
            Route::get('/', [App\Http\Controllers\KeyUser\KeyUserController::class, 'index'])->name('key-user-management.index');
            Route::get('/create', [App\Http\Controllers\KeyUser\KeyUserController::class, 'create'])->name('key-user-management.create');
            Route::post('/', [App\Http\Controllers\KeyUser\KeyUserController::class, 'store'])->name('key-user-management.store');
            Route::get('/{keyUser}', [App\Http\Controllers\KeyUser\KeyUserController::class, 'show'])->name('key-user-management.show');
            Route::get('/{keyUser}/edit', [App\Http\Controllers\KeyUser\KeyUserController::class, 'edit'])->name('key-user-management.edit');
            Route::put('/{keyUser}', [App\Http\Controllers\KeyUser\KeyUserController::class, 'update'])->name('key-user-management.update');
            Route::delete('/{keyUser}', [App\Http\Controllers\KeyUser\KeyUserController::class, 'destroy'])->name('key-user-management.destroy');
        });

        // User Routes
        Route::prefix('user')->group(function () {
            Route::get('/export', [App\Http\Controllers\KeyUser\UserController::class, 'export'])->name('user.export');
            Route::get('/', [App\Http\Controllers\KeyUser\UserController::class, 'index'])->name('user.index');
            Route::get('/create', [App\Http\Controllers\KeyUser\UserController::class, 'create'])->name('user.create');
            Route::post('/', [App\Http\Controllers\KeyUser\UserController::class, 'store'])->name('user.store');
            Route::get('/{user}', [App\Http\Controllers\KeyUser\UserController::class, 'show'])->name('user.show');
            Route::get('/{user}/edit', [App\Http\Controllers\KeyUser\UserController::class, 'edit'])->name('user.edit');
            Route::put('/{user}', [App\Http\Controllers\KeyUser\UserController::class, 'update'])->name('user.update');
            Route::delete('/{user}', [App\Http\Controllers\KeyUser\UserController::class, 'destroy'])->name('user.destroy');
        });

        // Work Order Routes
        Route::prefix('work-order')->group(function () {
            Route::get('/', [App\Http\Controllers\KeyUser\WorkOrderController::class, 'index'])->name('work-order.index');
            Route::get('/load-orders', [App\Http\Controllers\KeyUser\WorkOrderController::class, 'loadWorkOrdersAjax'])->name('work-order.load-orders');
            Route::get('/create', [App\Http\Controllers\KeyUser\WorkOrderController::class, 'create'])->name('work-order.create');
            Route::post('/', [App\Http\Controllers\KeyUser\WorkOrderController::class, 'store'])->name('work-order.store');
            Route::get('/get-product-details', [App\Http\Controllers\KeyUser\WorkOrderController::class, 'getProductDetails'])->name('work-order.get-product-details');
            Route::get('/{workOrder}', [App\Http\Controllers\KeyUser\WorkOrderController::class, 'show'])->name('work-order.show');
            Route::get('/{workOrder}/print', [App\Http\Controllers\KeyUser\WorkOrderController::class, 'print'])->name('work-order.print');
            Route::get('/{workOrder}/edit', [App\Http\Controllers\KeyUser\WorkOrderController::class, 'edit'])->name('work-order.edit');
            Route::put('/{workOrder}', [App\Http\Controllers\KeyUser\WorkOrderController::class, 'update'])->name('work-order.update');
            Route::delete('/{workOrder}', [App\Http\Controllers\KeyUser\WorkOrderController::class, 'destroy'])->name('work-order.destroy');
            Route::post('/bulk-print', [App\Http\Controllers\KeyUser\WorkOrderController::class, 'bulkPrint'])->name('work-order.bulk-print');
        });

        // Product Routes
        Route::prefix('product')->group(function () {
            Route::get('/export', [App\Http\Controllers\KeyUser\ProductController::class, 'export'])->name('product.export');
            Route::get('/', [App\Http\Controllers\KeyUser\ProductController::class, 'index'])->name('product.index');
            Route::get('/create', [App\Http\Controllers\KeyUser\ProductController::class, 'create'])->name('product.create');
            Route::post('/', [App\Http\Controllers\KeyUser\ProductController::class, 'store'])->name('product.store');
            Route::get('/get-subcategories', [App\Http\Controllers\KeyUser\ProductController::class, 'getSubcategories'])->name('product.get-subcategories');
            Route::get('/get-category-options', [App\Http\Controllers\KeyUser\ProductController::class, 'getCategoryOptions'])->name('product.get-category-options');
            Route::get('/{product}', [App\Http\Controllers\KeyUser\ProductController::class, 'show'])->name('product.show');
            Route::get('/{product}/edit', [App\Http\Controllers\KeyUser\ProductController::class, 'edit'])->name('product.edit');
            Route::put('/{product}', [App\Http\Controllers\KeyUser\ProductController::class, 'update'])->name('product.update');
            Route::delete('/{product}', [App\Http\Controllers\KeyUser\ProductController::class, 'destroy'])->name('product.destroy');
            Route::post('/print-selected', [App\Http\Controllers\KeyUser\ProductController::class, 'printSelected'])->name('product.print-selected');
        });

        // Design Routes (Read-only for Key Users)
        Route::prefix('design')->group(function () {
            Route::get('/export', [App\Http\Controllers\KeyUser\DesignController::class, 'export'])->name('design.export');
            Route::get('/', [App\Http\Controllers\KeyUser\DesignController::class, 'index'])->name('design.index');
            Route::get('/{design}', [App\Http\Controllers\KeyUser\DesignController::class, 'show'])->name('design.show');
            Route::get('/{design}/edit', [App\Http\Controllers\KeyUser\DesignController::class, 'edit'])->name('design.edit');
            Route::put('/{design}', [App\Http\Controllers\KeyUser\DesignController::class, 'update'])->name('design.update');
            Route::delete('/{design}', [App\Http\Controllers\KeyUser\DesignController::class, 'destroy'])->name('design.destroy');
            Route::post('/print-selected', [App\Http\Controllers\KeyUser\DesignController::class, 'printSelected'])->name('design.print-selected');
        });

        // Catalogue Routes (Read-only for Key Users)
        Route::prefix('catalogue')->group(function () {
            Route::get('/export', [App\Http\Controllers\KeyUser\CatalogueController::class, 'export'])->name('catalogue.export');
            Route::get('/', [App\Http\Controllers\KeyUser\CatalogueController::class, 'index'])->name('catalogue.index');
            Route::get('/{catalogue}', [App\Http\Controllers\KeyUser\CatalogueController::class, 'show'])->name('catalogue.show');
            Route::get('/{catalogue}/edit', [App\Http\Controllers\KeyUser\CatalogueController::class, 'edit'])->name('catalogue.edit');
            Route::put('/{catalogue}', [App\Http\Controllers\KeyUser\CatalogueController::class, 'update'])->name('catalogue.update');
            Route::delete('/{catalogue}', [App\Http\Controllers\KeyUser\CatalogueController::class, 'destroy'])->name('catalogue.destroy');
            Route::post('/print-selected', [App\Http\Controllers\KeyUser\CatalogueController::class, 'printSelected'])->name('catalogue.print-selected');
        });

        // Finance Routes (Dummy for now)
        Route::prefix('finance')->group(function () {
            Route::get('/', [App\Http\Controllers\KeyUser\LoginController::class, 'finance'])->name('finance.index');
        });

        // AJAX Category/Subcategory create
        Route::post('/product-category', [App\Http\Controllers\KeyUser\ProductCategoryController::class, 'store'])->name('product-category.store');

        // Temporary debug route
        Route::get('/debug-work-orders', function () {
            $userId = auth()->guard('key_user')->user()->id ?? null;
            $newOrders = \App\Models\WorkOrder::where('created_by', $userId)->where('status', 'new')->get();
            return response()->json([
                'user_id' => $userId,
                'new_orders_count' => $newOrders->count(),
                'new_orders' => $newOrders->toArray()
            ]);
        })->name('debug-work-orders');

        // Diagnostic route for catalogue issue
        Route::get('/debug-catalogue', function () {
            $userId = auth()->guard('key_user')->id();
            $buyerId = auth()->guard('buyer')->id();

            $allProducts = \App\Models\Product::where('design_status', 'Accepted')
                ->whereNotNull('design_code')
                ->get();

            $userProducts = $allProducts->filter(function ($product) use ($userId, $buyerId) {
                return $product->created_by == ($userId ?? $buyerId);
            });

            return response()->json([
                'current_key_user_id' => $userId,
                'current_buyer_id' => $buyerId,
                'total_accepted_products' => $allProducts->count(),
                'user_products_count' => $userProducts->count(),
                'all_products' => $allProducts->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'design_code' => $p->design_code,
                        'created_by' => $p->created_by,
                        'product_name' => $p->product_name
                    ];
                }),
                'user_products' => $userProducts->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'design_code' => $p->design_code,
                        'created_by' => $p->created_by,
                        'product_name' => $p->product_name
                    ];
                })
            ]);
        })->name('debug-catalogue');

        // Chat Routes
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
        Route::post('/chat/send', [ChatController::class, 'store'])->name('chat.store');
        Route::get('/chat/start/{receiver_id}/{type?}', [ChatController::class, 'startChat'])->name('chat.start');
    });
});

// Buyer Routes (Business Partners)
Route::prefix('buyer')->name('buyer.')->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\BuyerAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\BuyerAuthController::class, 'login']);
    Route::post('/logout', [App\Http\Controllers\Auth\BuyerAuthController::class, 'logout'])->name('logout');

    // Protected Routes
    Route::middleware(['auth:buyer', 'check.account.frozen'])->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Buyer\DashboardController::class, 'index'])->name('dashboard');

        // Profile Routes
        Route::get('/profile', [App\Http\Controllers\Buyer\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [App\Http\Controllers\Buyer\ProfileController::class, 'update'])->name('profile.update');

        // Repairs Routes
        Route::prefix('repairs')->name('repairs.')->group(function () {
            Route::get('/', [App\Http\Controllers\Buyer\RepairController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Buyer\RepairController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Buyer\RepairController::class, 'store'])->name('store');
            Route::post('/{repair}/accept-completed', [App\Http\Controllers\Buyer\RepairController::class, 'acceptCompleted'])->name('accept-completed');
            Route::post('/{repair}/reject-completed', [App\Http\Controllers\Buyer\RepairController::class, 'rejectCompleted'])->name('reject-completed');
        });

        // Key User Management Routes
        Route::prefix('key-user-management')->name('key-user-management.')->group(function () {
            // Keep EXPORT at the top
            Route::get('/export', [App\Http\Controllers\Buyer\KeyUserController::class, 'export'])->name('export');

            Route::get('/', [App\Http\Controllers\Buyer\KeyUserController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Buyer\KeyUserController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Buyer\KeyUserController::class, 'store'])->name('store');
            Route::get('/{keyUser}', [App\Http\Controllers\Buyer\KeyUserController::class, 'show'])->name('show');
            Route::get('/{keyUser}/edit', [App\Http\Controllers\Buyer\KeyUserController::class, 'edit'])->name('edit');
            Route::put('/{keyUser}', [App\Http\Controllers\Buyer\KeyUserController::class, 'update'])->name('update');
            Route::delete('/{keyUser}', [App\Http\Controllers\Buyer\KeyUserController::class, 'destroy'])->name('destroy');
        });

        // User Management Routes (Standard Users)
        Route::prefix('user-management')->name('user-management.')->group(function () {
            Route::get('/', [App\Http\Controllers\Buyer\UserController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Buyer\UserController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Buyer\UserController::class, 'store'])->name('store');
            Route::get('/{user}', [App\Http\Controllers\Buyer\UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [App\Http\Controllers\Buyer\UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [App\Http\Controllers\Buyer\UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [App\Http\Controllers\Buyer\UserController::class, 'destroy'])->name('destroy');
        });

        // Product Routes
        Route::prefix('product')->group(function () {
            Route::post('products/bulk-upload', [App\Http\Controllers\Buyer\ProductController::class, 'bulkUpload'])->name('product.bulk-upload');
            Route::get('/export', [App\Http\Controllers\Buyer\ProductController::class, 'export'])->name('product.export');
            Route::get('/', [App\Http\Controllers\Buyer\ProductController::class, 'index'])->name('product.index');
            Route::post('/print-selected', [App\Http\Controllers\Buyer\ProductController::class, 'printSelected'])->name('product.print-selected');
            Route::get('/create', [App\Http\Controllers\Buyer\ProductController::class, 'create'])->name('product.create');
            Route::post('/', [App\Http\Controllers\Buyer\ProductController::class, 'store'])->name('product.store');
            Route::get('/get-subcategories', [App\Http\Controllers\Buyer\ProductController::class, 'getSubcategories'])->name('product.get-subcategories');
            Route::get('/get-category-options', [App\Http\Controllers\Buyer\ProductController::class, 'getCategoryOptions'])->name('product.get-category-options');
            Route::get('/{product}', [App\Http\Controllers\Buyer\ProductController::class, 'show'])->name('product.show');
            Route::get('/{product}/edit', [App\Http\Controllers\Buyer\ProductController::class, 'edit'])->name('product.edit');
            Route::put('/{product}', [App\Http\Controllers\Buyer\ProductController::class, 'update'])->name('product.update');
            Route::delete('/{product}', [App\Http\Controllers\Buyer\ProductController::class, 'destroy'])->name('product.destroy');
        });

        // Work Order Routes
        Route::prefix('work-order')->group(function () {
            Route::post('/bulk-print', [App\Http\Controllers\Buyer\WorkOrderController::class, 'bulkPrint'])->name('work-order.bulk-print');
            Route::get('/', [App\Http\Controllers\Buyer\WorkOrderController::class, 'index'])->name('work-order.index');
            Route::get('/load-orders', [App\Http\Controllers\Buyer\WorkOrderController::class, 'loadWorkOrdersAjax'])->name('work-order.load-orders');
            Route::get('/create', [App\Http\Controllers\Buyer\WorkOrderController::class, 'create'])->name('work-order.create');
            Route::post('/', [App\Http\Controllers\Buyer\WorkOrderController::class, 'store'])->name('work-order.store');
            Route::get('/get-product-details', [App\Http\Controllers\Buyer\WorkOrderController::class, 'getProductDetails'])->name('work-order.get-product-details');
            Route::get('/{workOrder}', [App\Http\Controllers\Buyer\WorkOrderController::class, 'show'])->name('work-order.show');
            Route::get('/{workOrder}/print', [App\Http\Controllers\Buyer\WorkOrderController::class, 'print'])->name('work-order.print');
            Route::get('/{workOrder}/edit', [App\Http\Controllers\Buyer\WorkOrderController::class, 'edit'])->name('work-order.edit');
            Route::put('/{workOrder}', [App\Http\Controllers\Buyer\WorkOrderController::class, 'update'])->name('work-order.update');
            Route::delete('/{workOrder}', [App\Http\Controllers\Buyer\WorkOrderController::class, 'destroy'])->name('work-order.destroy');
        });

        // Design Routes (Index-only like KeyUser panel)
        Route::prefix('design')->group(function () {
            Route::get('/export', [App\Http\Controllers\Buyer\DesignController::class, 'export'])->name('design.export');
            Route::get('/', [App\Http\Controllers\Buyer\DesignController::class, 'index'])->name('design.index');
            Route::get('/{design}', [App\Http\Controllers\Buyer\DesignController::class, 'show'])->name('design.show');
        });

        // Stock Order Routes (New Flow)
        Route::prefix('stock-order')->name('stock-order.')->group(function () {
            Route::get('/', [App\Http\Controllers\Buyer\StockOrderController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Buyer\StockOrderController::class, 'create'])->name('create');
            Route::get('/{stockOrder}', [App\Http\Controllers\Buyer\StockOrderController::class, 'show'])->name('show');
            Route::get('/lookup/{code}', [App\Http\Controllers\Buyer\StockOrderController::class, 'getProductByCode'])->name('lookup')->where('code', '.*');
            Route::post('/', [App\Http\Controllers\Buyer\StockOrderController::class, 'store'])->name('store');
        });

        // Catalogue Routes
        Route::prefix('catalogue')->group(function () {
            Route::get('/', [App\Http\Controllers\Buyer\CatalogueController::class, 'index'])->name('catalogue.index');
            Route::get('/create', [App\Http\Controllers\Buyer\CatalogueController::class, 'create'])->name('catalogue.create');
            Route::post('/', [App\Http\Controllers\Buyer\CatalogueController::class, 'store'])->name('catalogue.store');
            Route::get('/{catalogue}', [App\Http\Controllers\Buyer\CatalogueController::class, 'show'])->name('catalogue.show');
            Route::get('/{catalogue}/edit', [App\Http\Controllers\Buyer\CatalogueController::class, 'edit'])->name('catalogue.edit');
            Route::put('/{catalogue}', [App\Http\Controllers\Buyer\CatalogueController::class, 'update'])->name('catalogue.update');
            Route::delete('/{catalogue}', [App\Http\Controllers\Buyer\CatalogueController::class, 'destroy'])->name('catalogue.destroy');
            Route::post('/print-selected', [App\Http\Controllers\Buyer\CatalogueController::class, 'printSelected'])->name('catalogue.print-selected');
        });

        // Product Category Routes for Buyers
        Route::prefix('product-category')->group(function () {
            Route::post('/', [App\Http\Controllers\Buyer\ProductCategoryController::class, 'store'])->name('product-category.store');
            Route::get('/get-category-options', [App\Http\Controllers\Buyer\ProductCategoryController::class, 'getCategoryOptions']);
        });

        // Finance Routes
        Route::prefix('finance')->group(function () {
            Route::get('/', [App\Http\Controllers\Buyer\DashboardController::class, 'finance'])->name('finance.index');
        });
        Route::prefix('meetings')->name('meetings.')->group(function () {
            Route::get('/', [BuyerMeetingController::class, 'index'])->name('index');
            Route::post('/', [BuyerMeetingController::class, 'store'])->name('store');
        });


        // Favorites Routes
        Route::prefix('favorites')->name('favorites.')->group(function () {
            Route::get('/', [App\Http\Controllers\Buyer\FavoriteController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Buyer\FavoriteController::class, 'store'])->name('store');
            Route::delete('/{id}', [App\Http\Controllers\Buyer\FavoriteController::class, 'destroy'])->name('destroy');
        });
    });
});

// User Routes (Regular Users)
Route::prefix('user')->name('user.')->group(function () {
    Route::get('/login', [App\Http\Controllers\User\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\User\LoginController::class, 'login'])->name('login.post');

    Route::middleware(['auth:web', 'check.account.frozen'])->group(function () {
        Route::post('/logout', [App\Http\Controllers\User\LoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [App\Http\Controllers\User\LoginController::class, 'dashboard'])->name('dashboard');

        // Work Order Routes
        Route::prefix('work-order')->group(function () {
            Route::get('/', [App\Http\Controllers\User\WorkOrderController::class, 'index'])->name('work-order.index');
            Route::get('/load-orders', [App\Http\Controllers\User\WorkOrderController::class, 'loadWorkOrdersAjax'])->name('work-order.load-orders');
            Route::get('/create', [App\Http\Controllers\User\WorkOrderController::class, 'create'])->name('work-order.create');
            Route::post('/', [App\Http\Controllers\User\WorkOrderController::class, 'store'])->name('work-order.store');
            Route::get('/get-product-details', [App\Http\Controllers\User\WorkOrderController::class, 'getProductDetails'])->name('work-order.get-product-details');
            Route::post('/print-selected', [App\Http\Controllers\User\WorkOrderController::class, 'bulkPrint'])->name('work-order.print-selected');
            Route::get('/{workOrder}', [App\Http\Controllers\User\WorkOrderController::class, 'show'])->name('work-order.show');
            Route::get('/{workOrder}/edit', [App\Http\Controllers\User\WorkOrderController::class, 'edit'])->name('work-order.edit');
            Route::put('/{workOrder}', [App\Http\Controllers\User\WorkOrderController::class, 'update'])->name('work-order.update');
            Route::delete('/{workOrder}', [App\Http\Controllers\User\WorkOrderController::class, 'destroy'])->name('work-order.destroy');
            Route::get('/{workOrder}/print', [App\Http\Controllers\User\WorkOrderController::class, 'print'])->name('work-order.print');
        });

        // Product Routes
        Route::prefix('product')->group(function () {
            Route::get('/export', [App\Http\Controllers\User\ProductController::class, 'export'])->name('product.export');
            Route::get('/', [App\Http\Controllers\User\ProductController::class, 'index'])->name('product.index');
            Route::get('/create', [App\Http\Controllers\User\ProductController::class, 'create'])->name('product.create');
            Route::post('/', [App\Http\Controllers\User\ProductController::class, 'store'])->name('product.store');
            Route::get('/get-subcategories', [App\Http\Controllers\User\ProductController::class, 'getSubcategories'])->name('product.get-subcategories');
            Route::get('/get-category-options', [App\Http\Controllers\User\ProductController::class, 'getCategoryOptions'])->name('product.get-category-options');
            Route::get('/{product}', [App\Http\Controllers\User\ProductController::class, 'show'])->name('product.show');
            Route::get('/{product}/edit', [App\Http\Controllers\User\ProductController::class, 'edit'])->name('product.edit');
            Route::put('/{product}', [App\Http\Controllers\User\ProductController::class, 'update'])->name('product.update');
            Route::delete('/{product}', [App\Http\Controllers\User\ProductController::class, 'destroy'])->name('product.destroy');
            Route::post('/print-selected', [App\Http\Controllers\User\ProductController::class, 'printSelected'])->name('product.print-selected');
        });

        // AJAX Category/Subcategory create (reuse Key User controller)
        Route::post('/product-category', [App\Http\Controllers\KeyUser\ProductCategoryController::class, 'store'])->name('product-category.store');

        // Design Routes (View-only for Users)
        Route::prefix('design')->group(function () {
            Route::get('/export', [App\Http\Controllers\User\DesignController::class, 'export'])->name('design.export');
            Route::get('/', [App\Http\Controllers\User\DesignController::class, 'index'])->name('design.index');
            Route::get('/{design}', [App\Http\Controllers\User\DesignController::class, 'show'])->name('design.show');
        });

        Route::prefix('catalogue')->group(function () {
            Route::get('/export', [App\Http\Controllers\User\CatalogueController::class, 'export'])->name('catalogue.export');
            Route::get('/', [App\Http\Controllers\User\CatalogueController::class, 'index'])->name('catalogue.index');
            Route::post('/print-selected', [App\Http\Controllers\User\CatalogueController::class, 'printSelected'])->name('catalogue.print-selected');
        });

        // Diagnostic route for user catalogue issue
        Route::get('/debug-user-catalogue', function () {
            $userId = Auth::id();

            $allProducts = \App\Models\Product::where('design_status', 'Accepted')
                ->whereNotNull('design_code')
                ->get();

            $userProducts = $allProducts->filter(function ($product) use ($userId) {
                return $product->created_by == $userId;
            });

            return response()->json([
                'current_user_id' => $userId,
                'total_accepted_products' => $allProducts->count(),
                'user_products_count' => $userProducts->count(),
                'all_products' => $allProducts->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'design_code' => $p->design_code,
                        'created_by' => $p->created_by,
                        'product_name' => $p->product_name
                    ];
                }),
                'user_products' => $userProducts->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'design_code' => $p->design_code,
                        'created_by' => $p->created_by,
                        'product_name' => $p->product_name
                    ];
                })
            ]);
        })->name('debug-user-catalogue');
    });
});
// Public Registration Routes
Route::get('/register', [PublicRegistrationController::class, 'index'])->name('register');
Route::post('/register', [PublicRegistrationController::class, 'store'])->name('register.store');

// Super Admin Registration Management
Route::middleware(['auth:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/registrations', [SuperAdminRegistrationController::class, 'index'])->name('registrations.index');
    Route::get('/registrations/{id}', [SuperAdminRegistrationController::class, 'show'])->name('registrations.show');
    Route::post('/registrations/{id}/approve', [SuperAdminRegistrationController::class, 'approve'])->name('registrations.approve');
    Route::post('/registrations/{id}/reject', [SuperAdminRegistrationController::class, 'reject'])->name('registrations.reject');
});
