<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SuperAdminApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Universal API Login (One endpoint for all roles)
Route::post('/login', [\App\Http\Controllers\API\UniversalAuthController::class, 'login']);
Route::post('/register', [\App\Http\Controllers\API\UniversalAuthController::class, 'register']);

// Universal Forgot Password API Routes
Route::post('/forgot-password', [\App\Http\Controllers\API\UniversalForgotPasswordController::class, 'sendOtp']);
Route::post('/verify-otp', [\App\Http\Controllers\API\UniversalForgotPasswordController::class, 'verifyOtp']);
Route::post('/reset-password', [\App\Http\Controllers\API\UniversalForgotPasswordController::class, 'resetPassword']);

// Company Contacts API (Public)
Route::get('/company-contacts', [\App\Http\Controllers\API\CompanyContactController::class, 'index']);

// Super Admin API Routes
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

Route::prefix('super-admin')->name('super-admin.')->group(function () {
    // Authentication
    Route::post('/login', [SuperAdminApiController::class, 'login'])->name('api.login');

    // Protected API Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [SuperAdminApiController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard/stats', [SuperAdminApiController::class, 'getDashboardStats'])->name('dashboard.stats');

        // Admin Management
        Route::get('/admins', [SuperAdminApiController::class, 'getAdmins'])->name('admins.index');
        Route::get('/admins/{admin}', [SuperAdminApiController::class, 'getAdmin'])->name('admins.show');
        Route::post('/admins', [SuperAdminApiController::class, 'createAdmin'])->name('admins.store');
        Route::post('/admins/{admin}', [SuperAdminApiController::class, 'updateAdmin'])->name('admins.update');
        Route::delete('/admins/{admin}', [SuperAdminApiController::class, 'deleteAdmin'])->name('admins.destroy');

        // Business Partners
        Route::get('/business-partners/overview', [SuperAdminApiController::class, 'getBusinessPartnerOverview'])->name('business-partners.overview');
        Route::get('/bp-code-list', [SuperAdminApiController::class, 'getBpCodeList'])->name('bp-code-list');
        Route::get('/craftsman-code-list', [SuperAdminApiController::class, 'getCraftsmanCodeList'])->name('craftsman-code-list');

        // Buyers 
        Route::get('/buyers', [SuperAdminApiController::class, 'getBuyers'])->name('buyers.index');
        Route::get('/buyers/generate-pdf', [SuperAdminApiController::class, 'generateBuyerPdf'])->name('buyers.generate-pdf');
        Route::post('/buyers', [SuperAdminApiController::class, 'createBuyer'])->name('buyers.store'); 
        Route::get('/buyers/{buyer}', [SuperAdminApiController::class, 'getBuyer'])->name('buyers.show');
        Route::post('/buyers/{buyer}', [SuperAdminApiController::class, 'updateBuyer'])->name('buyers.update');
        Route::delete('/buyers/{buyer}', [SuperAdminApiController::class, 'deleteBuyer'])->name('buyers.destroy');

        // Craftsmen
        Route::get('/craftsmen', [SuperAdminApiController::class, 'getCraftsmen'])->name('craftsmen.index');
        Route::get('/craftsmen/generate-pdf', [SuperAdminApiController::class, 'generateCraftsmanPdf'])->name('craftsmen.generate-pdf');
        Route::post('/craftsmen', [SuperAdminApiController::class, 'createCraftsman'])->name('craftsmen.store'); 
        Route::get('/craftsmen/{craftsman}', [SuperAdminApiController::class, 'getCraftsman'])->name('craftsmen.show');
        Route::post('/craftsmen/{craftsman}', [SuperAdminApiController::class, 'updateCraftsman'])->name('craftsmen.update');
        Route::delete('/craftsmen/{craftsman}', [SuperAdminApiController::class, 'deleteCraftsman'])->name('craftsmen.destroy');

        // Key Users
        Route::get('/key-users', [SuperAdminApiController::class, 'getKeyUsers'])->name('key-users.index');
        Route::get('/key-users/{keyUser}', [SuperAdminApiController::class, 'getKeyUser'])->name('key-users.show');
        Route::post('/key-users', [SuperAdminApiController::class, 'createKeyUser'])->name('key-users.store');
        Route::post('/key-users/{keyUser}', [SuperAdminApiController::class, 'updateKeyUser'])->name('key-users.update');
        Route::delete('/key-users/{keyUser}', [SuperAdminApiController::class, 'deleteKeyUser'])->name('key-users.destroy');

        // End Users
        Route::get('/users', [SuperAdminApiController::class, 'getUsers'])->name('users.index');
        Route::get('/users/{user}', [SuperAdminApiController::class, 'getUser'])->name('users.show');
        Route::post('/users', [SuperAdminApiController::class, 'createUser'])->name('users.store');
        Route::post('/users/{user}', [SuperAdminApiController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [SuperAdminApiController::class, 'deleteUser'])->name('users.destroy');

        // Pincode
        Route::get('/get-pincode/{pincode}', [SuperAdminApiController::class, 'fetchPincode'])->name('pincode.fetch');

        // Work Orders
        Route::get('/work-orders', [SuperAdminApiController::class, 'getWorkOrders'])->name('work-orders.index');
        Route::post('/work-orders', [SuperAdminApiController::class, 'createWorkOrder'])->name('work-orders.store');
        // ↓ Specific routes BEFORE {workOrder} wildcard
        Route::post('/work-orders/bulk-allocate', [SuperAdminApiController::class, 'bulkAllocateWorkOrder'])->name('work-orders.bulk-allocate');
        Route::post('/work-orders/bulk-approve', [SuperAdminApiController::class, 'bulkApproveWorkOrder'])->name('work-orders.bulk-approve');
        // ↓ Wildcard routes last
        Route::get('/work-orders/{workOrder}', [SuperAdminApiController::class, 'getWorkOrder'])->name('work-orders.show')->where('workOrder', '[0-9]+');
        Route::post('/work-orders/{workOrder}', [SuperAdminApiController::class, 'updateWorkOrder'])->name('work-orders.update')->where('workOrder', '[0-9]+');
        Route::delete('/work-orders/{workOrder}', [SuperAdminApiController::class, 'deleteWorkOrder'])->name('work-orders.destroy')->where('workOrder', '[0-9]+');
        Route::post('/work-orders/{workOrder}/allocate', [SuperAdminApiController::class, 'allocateWorkOrder'])->name('work-orders.allocate')->where('workOrder', '[0-9]+');
        Route::post('/work-orders/{workOrder}/reallocate', [SuperAdminApiController::class, 'reallocateWorkOrder'])->name('work-orders.reallocate')->where('workOrder', '[0-9]+');

        // Purchase Orders
        Route::get('/purchase-orders', [SuperAdminApiController::class, 'getPurchaseOrders'])->name('purchase-orders.index');
        Route::post('/purchase-orders', [SuperAdminApiController::class, 'createPurchaseOrder'])->name('purchase-orders.store');
        // ↓ Specific (non-wildcard) routes MUST come before {purchaseOrder} wildcard routes
        Route::post('/purchase-orders/bulk-allocate', [SuperAdminApiController::class, 'bulkAllocatePurchaseOrder'])->name('purchase-orders.bulk-allocate');
        Route::post('/purchase-orders/bulk-approve', [SuperAdminApiController::class, 'bulkApprovePurchaseOrder'])->name('purchase-orders.bulk-approve');
        // Purchase Order lookup helpers
        Route::get('/purchase-orders/helpers/products-by-category', [SuperAdminApiController::class, 'getPoProductsByCategory'])->name('purchase-orders.products-by-category');
        Route::get('/purchase-orders/helpers/designs-by-product', [SuperAdminApiController::class, 'getPoDesignsByProduct'])->name('purchase-orders.designs-by-product');
        Route::get('/purchase-orders/helpers/product-by-design-code', [SuperAdminApiController::class, 'getPoProductByDesignCode'])->name('purchase-orders.product-by-design-code');
        Route::get('/purchase-orders/helpers/subcategory-design', [SuperAdminApiController::class, 'getSubcategoryDesignInfo'])->name('purchase-orders.subcategory-design');
        // ↓ Wildcard routes last
        Route::get('/purchase-orders/{purchaseOrder}', [SuperAdminApiController::class, 'getPurchaseOrder'])->name('purchase-orders.show')->where('purchaseOrder', '[0-9]+');
        Route::post('/purchase-orders/{purchaseOrder}', [SuperAdminApiController::class, 'updatePurchaseOrder'])->name('purchase-orders.update')->where('purchaseOrder', '[0-9]+');
        Route::delete('/purchase-orders/{purchaseOrder}', [SuperAdminApiController::class, 'deletePurchaseOrder'])->name('purchase-orders.destroy')->where('purchaseOrder', '[0-9]+');
        Route::post('/purchase-orders/{purchaseOrder}/allocate', [SuperAdminApiController::class, 'allocatePurchaseOrder'])->name('purchase-orders.allocate')->where('purchaseOrder', '[0-9]+');
        Route::post('/purchase-orders/{purchaseOrder}/reallocate', [SuperAdminApiController::class, 'reallocatePurchaseOrder'])->name('purchase-orders.reallocate')->where('purchaseOrder', '[0-9]+');
        Route::post('/purchase-orders/{purchaseOrder}/approve', [SuperAdminApiController::class, 'approvePurchaseOrder'])->name('purchase-orders.approve')->where('purchaseOrder', '[0-9]+');
        Route::post('/purchase-orders/{purchaseOrder}/update-status', [SuperAdminApiController::class, 'updatePurchaseOrderStatus'])->name('purchase-orders.update-status')->where('purchaseOrder', '[0-9]+');

        // Products
        Route::get('/products', [SuperAdminApiController::class, 'getProducts'])->name('products.index');
        Route::get('/products/{product}', [SuperAdminApiController::class, 'getProduct'])->name('products.show');
        Route::post('/products', [SuperAdminApiController::class, 'createProduct'])->name('products.store');
        Route::post('/products/{product}', [SuperAdminApiController::class, 'updateProduct'])->name('products.update');
        Route::delete('/products/{product}', [SuperAdminApiController::class, 'deleteProduct'])->name('products.destroy');
        Route::get('/product-categories', [SuperAdminApiController::class, 'getProductCategories'])->name('product-categories.index');
        Route::post('/product-categories', [SuperAdminApiController::class, 'createProductCategory'])->name('product-categories.store');
        Route::post('/product-subcategories', [SuperAdminApiController::class, 'createProductSubcategory'])->name('product-subcategories.store');


        // Designs
        Route::get('/designs', [SuperAdminApiController::class, 'getDesigns'])->name('designs.index');
        Route::get('/designs/approval', [SuperAdminApiController::class, 'getDesignProducts'])->name('designs.approval');
        Route::get('/designs/{design}', [SuperAdminApiController::class, 'getDesign'])->name('designs.show');
        Route::post('/designs', [SuperAdminApiController::class, 'createDesign'])->name('designs.store');
        Route::post('/designs/{design}', [SuperAdminApiController::class, 'updateDesign'])->name('designs.update');
        Route::post('/designs/{product}/accept', [SuperAdminApiController::class, 'acceptDesign'])->name('designs.accept');
        Route::post('/designs/{product}/reject', [SuperAdminApiController::class, 'rejectDesign'])->name('designs.reject');

        // Catalogue
        Route::get('/catalogue-products', [SuperAdminApiController::class, 'getCatalogueProducts'])->name('api.catalogue.index');
        Route::get('/catalogue-products/{product}', [SuperAdminApiController::class, 'getCatalogueProduct'])->name('api.catalogue.show');
        // Finance & Account Control
        Route::get('/frozen-accounts', [SuperAdminApiController::class, 'getFrozenAccounts'])->name('frozen-accounts.index');
        Route::post('/freeze-account', [SuperAdminApiController::class, 'toggleFreezeAccount'])->name('freeze-account.toggle');

        // KYC Management
        Route::get('/kyc-pending', [SuperAdminApiController::class, 'getKycPending'])->name('kyc.pending');
        Route::post('/buyers/{buyer}/kyc-action', [SuperAdminApiController::class, 'manageBuyerKyc'])->name('buyers.kyc.action');
        Route::post('/craftsmen/{craftsman}/kyc-action', [SuperAdminApiController::class, 'manageCraftsmanKyc'])->name('craftsmen.kyc.action');
    });
});

// Buyer API Routes
Route::prefix('buyer')->name('api.buyer.')->group(function () {
    // Authentication
    Route::post('/login', [\App\Http\Controllers\API\BuyerApiController::class, 'login'])->name('login');

    // Protected Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [\App\Http\Controllers\API\BuyerApiController::class, 'logout'])->name('logout');

        // Profile
        Route::get('/profile', [\App\Http\Controllers\API\BuyerApiController::class, 'getProfile'])->name('profile');
        Route::post('/profile', [\App\Http\Controllers\API\BuyerApiController::class, 'updateProfile'])->name('profile.update');

        // Helpers

        // Work Orders
        Route::get('/work-orders', [\App\Http\Controllers\API\BuyerApiController::class, 'getWorkOrders'])->name('work-orders.index');
        Route::get('/work-orders/{id}', [\App\Http\Controllers\API\BuyerApiController::class, 'getWorkOrder'])->name('work-orders.show');
        Route::post('/work-orders', [\App\Http\Controllers\API\BuyerApiController::class, 'storeWorkOrder'])->name('work-orders.store');
        Route::post('/work-orders/{id}', [\App\Http\Controllers\API\BuyerApiController::class, 'updateWorkOrder'])->name('work-orders.update'); // Using POST for file upload support in Laravel
        Route::delete('/work-orders/{id}', [\App\Http\Controllers\API\BuyerApiController::class, 'deleteWorkOrder'])->name('work-orders.destroy');

        // Products
        Route::get('/products', [\App\Http\Controllers\API\BuyerApiController::class, 'getProducts'])->name('products.index');
        Route::get('/products/{id}', [\App\Http\Controllers\API\BuyerApiController::class, 'getProduct'])->name('products.show');
        Route::post('/products', [\App\Http\Controllers\API\BuyerApiController::class, 'storeProduct'])->name('products.store');
        Route::post('/products/{id}', [\App\Http\Controllers\API\BuyerApiController::class, 'updateProduct'])->name('products.update'); // POST for files
        Route::delete('/products/{id}', [\App\Http\Controllers\API\BuyerApiController::class, 'deleteProduct'])->name('products.destroy');

        // Designs (Global)
        Route::get('/designs', [\App\Http\Controllers\API\BuyerApiController::class, 'getDesigns'])->name('designs.index');
        Route::get('/designs/{id}', [\App\Http\Controllers\API\BuyerApiController::class, 'getDesign'])->name('designs.show');

        // Catalogue (Personal)
        Route::get('/catalogue', [\App\Http\Controllers\API\BuyerApiController::class, 'getCatalogue'])->name('catalogue.index');
        Route::get('/catalogue/{id}', [\App\Http\Controllers\API\BuyerApiController::class, 'getCatalogueItem'])->name('catalogue.show');

        // Key Users
        Route::get('/key-users', [\App\Http\Controllers\API\BuyerApiController::class, 'getKeyUsers'])->name('key-users.index');
        Route::post('/key-users', [\App\Http\Controllers\API\BuyerApiController::class, 'storeKeyUser'])->name('key-users.store');
        Route::post('/key-users/{id}', [\App\Http\Controllers\API\BuyerApiController::class, 'updateKeyUser'])->name('key-users.update');
        Route::delete('/key-users/{id}', [\App\Http\Controllers\API\BuyerApiController::class, 'deleteKeyUser'])->name('key-users.destroy');

        // Users
        Route::get('/users', [\App\Http\Controllers\API\BuyerApiController::class, 'getUsers'])->name('users.index');
        Route::post('/users', [\App\Http\Controllers\API\BuyerApiController::class, 'storeUser'])->name('users.store');
        Route::post('/users/{id}', [\App\Http\Controllers\API\BuyerApiController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [\App\Http\Controllers\API\BuyerApiController::class, 'deleteUser'])->name('users.destroy');
    });
});

// KeyUser API Routes
Route::prefix('key-user')->name('api.key-user.')->group(function () {
    // Authentication
    Route::post('/login', [\App\Http\Controllers\API\KeyUserApiController::class, 'login'])->name('login');

    // Protected Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [\App\Http\Controllers\API\KeyUserApiController::class, 'logout'])->name('logout');

        // Profile
        Route::get('/profile', [\App\Http\Controllers\API\KeyUserApiController::class, 'getProfile'])->name('profile');

        // Helpers

        // Work Orders
        Route::get('/work-orders', [\App\Http\Controllers\API\KeyUserApiController::class, 'getWorkOrders'])->name('work-orders.index');
        Route::get('/work-orders/{id}', [\App\Http\Controllers\API\KeyUserApiController::class, 'getWorkOrder'])->name('work-orders.show');
        Route::post('/work-orders', [\App\Http\Controllers\API\KeyUserApiController::class, 'storeWorkOrder'])->name('work-orders.store');
        Route::post('/work-orders/{id}', [\App\Http\Controllers\API\KeyUserApiController::class, 'updateWorkOrder'])->name('work-orders.update');
        Route::delete('/work-orders/{id}', [\App\Http\Controllers\API\KeyUserApiController::class, 'deleteWorkOrder'])->name('work-orders.destroy');

        // Products
        Route::get('/products', [\App\Http\Controllers\API\KeyUserApiController::class, 'getProducts'])->name('products.index');
        Route::get('/products/{id}', [\App\Http\Controllers\API\KeyUserApiController::class, 'getProduct'])->name('products.show');
        Route::post('/products', [\App\Http\Controllers\API\KeyUserApiController::class, 'storeProduct'])->name('products.store');
        Route::post('/products/{id}', [\App\Http\Controllers\API\KeyUserApiController::class, 'updateProduct'])->name('products.update');
        Route::delete('/products/{id}', [\App\Http\Controllers\API\KeyUserApiController::class, 'deleteProduct'])->name('products.destroy');

        // Designs (Global - Read Only)
        Route::get('/designs', [\App\Http\Controllers\API\KeyUserApiController::class, 'getDesigns'])->name('designs.index');
        Route::get('/designs/{id}', [\App\Http\Controllers\API\KeyUserApiController::class, 'getDesign'])->name('designs.show');

        // Catalogue (Personal - Read Only)
        Route::get('/catalogue', [\App\Http\Controllers\API\KeyUserApiController::class, 'getCatalogue'])->name('catalogue.index');
        Route::get('/catalogue/{id}', [\App\Http\Controllers\API\KeyUserApiController::class, 'getCatalogueItem'])->name('catalogue.show');

        // Users
        Route::get('/users', [\App\Http\Controllers\API\KeyUserApiController::class, 'getUsers'])->name('users.index');
        Route::post('/users', [\App\Http\Controllers\API\KeyUserApiController::class, 'storeUser'])->name('users.store');
        Route::post('/users/{id}', [\App\Http\Controllers\API\KeyUserApiController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [\App\Http\Controllers\API\KeyUserApiController::class, 'deleteUser'])->name('users.destroy');
    });
});

// User API Routes (End Users)
Route::prefix('user')->name('api.user.')->group(function () {
    // Authentication
    Route::post('/login', [\App\Http\Controllers\API\UserApiController::class, 'login'])->name('login');

    // Protected Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [\App\Http\Controllers\API\UserApiController::class, 'logout'])->name('logout');

        // Profile
        Route::get('/profile', [\App\Http\Controllers\API\UserApiController::class, 'getProfile'])->name('profile');

        // Helpers

        // Work Orders
        Route::get('/work-orders', [\App\Http\Controllers\API\UserApiController::class, 'getWorkOrders'])->name('work-orders.index');
        Route::get('/work-orders/{id}', [\App\Http\Controllers\API\UserApiController::class, 'getWorkOrder'])->name('work-orders.show');
        Route::post('/work-orders', [\App\Http\Controllers\API\UserApiController::class, 'storeWorkOrder'])->name('work-orders.store');
        Route::post('/work-orders/{id}', [\App\Http\Controllers\API\UserApiController::class, 'updateWorkOrder'])->name('work-orders.update');
        Route::delete('/work-orders/{id}', [\App\Http\Controllers\API\UserApiController::class, 'deleteWorkOrder'])->name('work-orders.destroy');

        // Products
        Route::get('/products', [\App\Http\Controllers\API\UserApiController::class, 'getProducts'])->name('products.index');
        Route::get('/products/{id}', [\App\Http\Controllers\API\UserApiController::class, 'getProduct'])->name('products.show');
        Route::post('/products', [\App\Http\Controllers\API\UserApiController::class, 'storeProduct'])->name('products.store');
        Route::post('/products/{id}', [\App\Http\Controllers\API\UserApiController::class, 'updateProduct'])->name('products.update');
        Route::delete('/products/{id}', [\App\Http\Controllers\API\UserApiController::class, 'deleteProduct'])->name('products.destroy');

        // Designs (Global - Read Only)
        Route::get('/designs', [\App\Http\Controllers\API\UserApiController::class, 'getDesigns'])->name('designs.index');
        Route::get('/designs/{id}', [\App\Http\Controllers\API\UserApiController::class, 'getDesign'])->name('designs.show');

        // Catalogue (Personal - Read Only)
        Route::get('/catalogue', [\App\Http\Controllers\API\UserApiController::class, 'getCatalogue'])->name('catalogue.index');
        Route::get('/catalogue/{id}', [\App\Http\Controllers\API\UserApiController::class, 'getCatalogueItem'])->name('catalogue.show');
    });
});

// Craftsman API Routes
Route::prefix('craftsman')->name('api.craftsman.')->group(function () {
    // Authentication
    Route::post('/login', [\App\Http\Controllers\API\CraftsmanApiController::class, 'login'])->name('login');

    // Protected Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [\App\Http\Controllers\API\CraftsmanApiController::class, 'logout'])->name('logout');

        // Profile
        Route::get('/profile', [\App\Http\Controllers\API\Common\CraftsmanController::class, 'getProfile'])->name('profile');
        Route::post('/profile', [\App\Http\Controllers\API\Common\CraftsmanController::class, 'updateProfile'])->name('profile.update');

        // Helpers

        // Work Orders (Allocated)
        Route::get('/work-orders', [\App\Http\Controllers\API\CraftsmanApiController::class, 'getWorkOrders'])->name('work-orders.index');
        Route::get('/work-orders/{id}', [\App\Http\Controllers\API\CraftsmanApiController::class, 'getWorkOrder'])->name('work-orders.show');

        // Work Order Actions
        Route::post('/work-orders/{id}/accept', [\App\Http\Controllers\API\CraftsmanApiController::class, 'acceptWorkOrder'])->name('work-orders.accept');
        Route::post('/work-orders/{id}/reject', [\App\Http\Controllers\API\CraftsmanApiController::class, 'rejectWorkOrder'])->name('work-orders.reject');
        Route::post('/work-orders/{id}/complete', [\App\Http\Controllers\API\CraftsmanApiController::class, 'completeWorkOrder'])->name('work-orders.complete');

        // Bulk Actions
        Route::post('/work-orders/bulk-accept', [\App\Http\Controllers\API\CraftsmanApiController::class, 'bulkAcceptWorkOrders'])->name('work-orders.bulk-accept');
        Route::post('/work-orders/bulk-reject', [\App\Http\Controllers\API\CraftsmanApiController::class, 'bulkRejectWorkOrders'])->name('work-orders.bulk-reject');

        // Products
        Route::get('/products', [\App\Http\Controllers\API\CraftsmanApiController::class, 'getProducts'])->name('products.index');
        Route::get('/products/{id}', [\App\Http\Controllers\API\CraftsmanApiController::class, 'getProduct'])->name('products.show');
        Route::post('/products', [\App\Http\Controllers\API\CraftsmanApiController::class, 'storeProduct'])->name('products.store');
        Route::post('/products/{id}', [\App\Http\Controllers\API\CraftsmanApiController::class, 'updateProduct'])->name('products.update');
        Route::delete('/products/{id}', [\App\Http\Controllers\API\CraftsmanApiController::class, 'deleteProduct'])->name('products.destroy');

        // Designs
        Route::get('/designs', [\App\Http\Controllers\API\CraftsmanApiController::class, 'getDesigns'])->name('designs.index');
        Route::post('/designs', [\App\Http\Controllers\API\CraftsmanApiController::class, 'storeDesign'])->name('designs.store');

        // Catalogue
        Route::get('/catalogue', [\App\Http\Controllers\API\CraftsmanApiController::class, 'getCatalogue'])->name('catalogue.api.index');

        // Purchase Orders (Placeholder)
        Route::get('/purchase-orders', [\App\Http\Controllers\API\CraftsmanApiController::class, 'getPurchaseOrders'])->name('purchase-orders.index');
    });
});

// ============================================================================
// COMMON API ROUTES (Unified for all 5 panels — role-based filtering inside)
// ============================================================================
Route::prefix('common')->name('api.common.')->middleware(['auth:sanctum'])->group(function () {

    // Dashboard Stats- Role-based statistics for all panels
   Route::get('/dashboard/stats', [\App\Http\Controllers\API\Common\DashboardController::class, 'getDashboardStats'])->name('dashboard.stats');

    // --- Work Orders ---
   Route::get('/work-orders', [\App\Http\Controllers\API\Common\WorkOrderController::class, 'index'])->name('work-orders.index');
    Route::post('/work-orders', [\App\Http\Controllers\API\Common\WorkOrderController::class, 'store'])->name('work-orders.store');
    // Helpers
    Route::get('/work-orders/helpers/bp-codes', [\App\Http\Controllers\API\Common\WorkOrderController::class, 'getBpCodes'])->name('work-orders.bp-codes');
    Route::get('/work-orders/helpers/craftman-codes', [\App\Http\Controllers\API\Common\WorkOrderController::class, 'getCraftmanCodes'])->name('work-orders.craftman-codes');
    Route::get('/work-orders/generate-pdf/{id?}', [\App\Http\Controllers\API\Common\WorkOrderController::class, 'generatePdf'])->name('work-orders.generate-pdf');
    // Bulk actions (before wildcard)
    Route::post('/work-orders/bulk-allocate', [\App\Http\Controllers\API\Common\WorkOrderController::class, 'bulkAllocate'])->name('work-orders.bulk-allocate');
    Route::post('/work-orders/bulk-approve', [\App\Http\Controllers\API\Common\WorkOrderController::class, 'bulkApprove'])->name('work-orders.bulk-approve');
    Route::post('/work-orders/bulk-accept', [\App\Http\Controllers\API\Common\WorkOrderController::class, 'bulkAcceptWorkOrders'])->name('work-orders.bulk-accept');
    Route::post('/work-orders/bulk-reject', [\App\Http\Controllers\API\Common\WorkOrderController::class, 'bulkRejectWorkOrders'])->name('work-orders.bulk-reject');
    Route::post('/work-orders/bulk-complete', [\App\Http\Controllers\API\Common\WorkOrderController::class, 'bulkCompleteWorkOrders'])->name('work-orders.bulk-complete');
    // Wildcard
    Route::get('/work-orders/{id}', [\App\Http\Controllers\API\Common\WorkOrderController::class, 'show'])->name('work-orders.show');
    Route::post('/work-orders/{id}', [\App\Http\Controllers\API\Common\WorkOrderController::class, 'update'])->name('work-orders.update');
    Route::delete('/work-orders/{id}', [\App\Http\Controllers\API\Common\WorkOrderController::class, 'destroy'])->name('work-orders.destroy');
    // Admin actions
    Route::post('/work-orders/{id}/allocate', [\App\Http\Controllers\API\Common\WorkOrderController::class, 'allocate'])->name('work-orders.allocate');
    Route::post('/work-orders/{id}/reallocate', [\App\Http\Controllers\API\Common\WorkOrderController::class, 'reallocate'])->name('work-orders.reallocate');
    // Craftsman actions
    Route::post('/work-orders/{id}/accept', [\App\Http\Controllers\API\Common\WorkOrderController::class, 'acceptWorkOrder'])->name('work-orders.accept');
    Route::post('/work-orders/{id}/reject', [\App\Http\Controllers\API\Common\WorkOrderController::class, 'rejectWorkOrder'])->name('work-orders.reject');
    Route::post('/work-orders/{id}/complete', [\App\Http\Controllers\API\Common\WorkOrderController::class, 'completeWorkOrder'])->name('work-orders.complete');

    // --- Purchase Orders ---
    Route::get('/purchase-orders', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::post('/purchase-orders', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    // Bulk actions (before wildcard)
    Route::post('/purchase-orders/bulk-allocate', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'bulkAllocate'])->name('purchase-orders.bulk-allocate');
    Route::post('/purchase-orders/bulk-approve', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'bulkApprove'])->name('purchase-orders.bulk-approve');
    Route::post('/purchase-orders/bulk-accept', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'bulkAccept'])->name('purchase-orders.bulk-accept');
    Route::post('/purchase-orders/bulk-reject', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'bulkReject'])->name('purchase-orders.bulk-reject');
    Route::post('/purchase-orders/bulk-complete', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'bulkComplete'])->name('purchase-orders.bulk-complete');
    // AJAX Helpers
    Route::get('/purchase-orders/helpers/products-by-category', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'getPoProductsByCategory'])->name('purchase-orders.products-by-category');
    Route::get('/purchase-orders/helpers/designs-by-product', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'getPoDesignsByProduct'])->name('purchase-orders.designs-by-product');
    Route::get('/purchase-orders/helpers/product-by-design-code', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'getPoProductByDesignCode'])->name('purchase-orders.product-by-design-code');
    Route::get('/purchase-orders/helpers/subcategory-design', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'getPoSubcategoryDesign'])->name('purchase-orders.subcategory-design');
    // PDF Generation
    Route::get('/purchase-orders/generate-pdf/{id?}', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'generatePdf'])->name('purchase-orders.generate-pdf');
    // Wildcard routes last
    Route::get('/purchase-orders/{id}', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'show'])->name('purchase-orders.show')->where('id', '[0-9]+');
    Route::post('/purchase-orders/{id}', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'update'])->name('purchase-orders.update')->where('id', '[0-9]+');
    Route::delete('/purchase-orders/{id}', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy')->where('id', '[0-9]+');
    // Admin actions
    Route::post('/purchase-orders/{id}/allocate', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'allocate'])->name('purchase-orders.allocate')->where('id', '[0-9]+');
    Route::post('/purchase-orders/{id}/reallocate', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'reallocate'])->name('purchase-orders.reallocate')->where('id', '[0-9]+');
    Route::post('/purchase-orders/{id}/approve', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve')->where('id', '[0-9]+');
    Route::post('/purchase-orders/{id}/update-status', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.update-status')->where('id', '[0-9]+');
    // Craftsman actions
    Route::post('/purchase-orders/{id}/process-items', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'processItems'])->name('purchase-orders.process-items')->where('id', '[0-9]+');
    Route::post('/purchase-orders/{id}/complete-items', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'completeItems'])->name('purchase-orders.complete-items')->where('id', '[0-9]+');
    Route::post('/purchase-orders/{id}/items/{index}/accept', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'acceptItem'])->name('purchase-orders.items.accept')->where('id', '[0-9]+');
    Route::post('/purchase-orders/{id}/items/{index}/reject', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'rejectItem'])->name('purchase-orders.items.reject')->where('id', '[0-9]+');
    Route::post('/purchase-orders/{id}/complete', [\App\Http\Controllers\API\Common\PurchaseOrderController::class, 'complete'])->name('purchase-orders.complete')->where('id', '[0-9]+');

    // --- Repairs ---
    Route::get('/repairs', [\App\Http\Controllers\API\Common\RepairController::class, 'index'])->name('repairs.index');
    Route::get('/repairs/generate-pdf', [\App\Http\Controllers\API\Common\RepairController::class, 'generatePdf'])->name('repairs.generate-pdf');
    Route::post('/repairs', [\App\Http\Controllers\API\Common\RepairController::class, 'store'])->name('repairs.store');
    Route::get('/repairs/{id}', [\App\Http\Controllers\API\Common\RepairController::class, 'show'])->name('repairs.show')->where('id', '[0-9]+');
    Route::post('/repairs/{id}', [\App\Http\Controllers\API\Common\RepairController::class, 'update'])->name('repairs.update')->where('id', '[0-9]+');
    Route::delete('/repairs/{id}', [\App\Http\Controllers\API\Common\RepairController::class, 'destroy'])->name('repairs.destroy')->where('id', '[0-9]+');
    // Actions
    Route::post('/repairs/{id}/accept', [\App\Http\Controllers\API\Common\RepairController::class, 'accept'])->name('repairs.accept')->where('id', '[0-9]+');
    Route::post('/repairs/{id}/reject', [\App\Http\Controllers\API\Common\RepairController::class, 'reject'])->name('repairs.reject')->where('id', '[0-9]+');
    Route::post('/repairs/{id}/allocate', [\App\Http\Controllers\API\Common\RepairController::class, 'allocate'])->name('repairs.allocate')->where('id', '[0-9]+');
    Route::post('/repairs/{id}/complete', [\App\Http\Controllers\API\Common\RepairController::class, 'complete'])->name('repairs.complete')->where('id', '[0-9]+');
    Route::post('/repairs/{id}/buyer-accept', [\App\Http\Controllers\API\Common\RepairController::class, 'buyerAccept'])->name('repairs.buyer-accept')->where('id', '[0-9]+');
    Route::post('/repairs/{id}/buyer-reject', [\App\Http\Controllers\API\Common\RepairController::class, 'buyerReject'])->name('repairs.buyer-reject')->where('id', '[0-9]+');

    // --- Stock Orders ---
    Route::get('/stock-orders', [\App\Http\Controllers\API\Common\StockOrderController::class, 'index'])->name('stock-orders.index');
    Route::post('/stock-orders', [\App\Http\Controllers\API\Common\StockOrderController::class, 'store'])->name('stock-orders.store');
    Route::get('/stock-orders/lookup/{code}', [\App\Http\Controllers\API\Common\StockOrderController::class, 'lookup'])->name('stock-orders.lookup');
    Route::post('/stock-orders/bulk-allocate', [\App\Http\Controllers\API\Common\StockOrderController::class, 'bulkAllocate'])->name('stock-orders.bulk-allocate');
    Route::post('/stock-orders/bulk-complete', [\App\Http\Controllers\API\Common\StockOrderController::class, 'bulkComplete'])->name('stock-orders.bulk-complete');
    Route::post('/stock-orders/bulk-accept', [\App\Http\Controllers\API\Common\StockOrderController::class, 'bulkAccept'])->name('stock-orders.bulk-accept');
    Route::post('/stock-orders/bulk-reject', [\App\Http\Controllers\API\Common\StockOrderController::class, 'bulkReject'])->name('stock-orders.bulk-reject');
    Route::get('/stock-orders/{id}', [\App\Http\Controllers\API\Common\StockOrderController::class, 'show'])->name('stock-orders.show')->where('id', '[0-9]+');
    Route::post('/stock-orders/{id}', [\App\Http\Controllers\API\Common\StockOrderController::class, 'update'])->name('stock-orders.update')->where('id', '[0-9]+');
    Route::delete('/stock-orders/{id}', [\App\Http\Controllers\API\Common\StockOrderController::class, 'destroy'])->name('stock-orders.destroy')->where('id', '[0-9]+');
    Route::post('/stock-orders/{id}/allocate', [\App\Http\Controllers\API\Common\StockOrderController::class, 'allocate'])->name('stock-orders.allocate')->where('id', '[0-9]+');
    Route::post('/stock-orders/{id}/reallocate', [\App\Http\Controllers\API\Common\StockOrderController::class, 'reallocate'])->name('stock-orders.reallocate')->where('id', '[0-9]+');
    Route::post('/stock-orders/{id}/status', [\App\Http\Controllers\API\Common\StockOrderController::class, 'updateStatus'])->name('stock-orders.status')->where('id', '[0-9]+');
    Route::post('/stock-orders/{id}/items/{itemId}/status', [\App\Http\Controllers\API\Common\StockOrderController::class, 'updateItemStatus'])->name('stock-orders.items.status')->where('id', '[0-9]+');
    Route::post('/stock-orders/{id}/items/{itemId}/accept', [\App\Http\Controllers\API\Common\StockOrderController::class, 'acceptItem'])->name('stock-orders.items.accept')->where('id', '[0-9]+');
    Route::post('/stock-orders/{id}/items/{itemId}/reject', [\App\Http\Controllers\API\Common\StockOrderController::class, 'rejectItem'])->name('stock-orders.items.reject')->where('id', '[0-9]+');
    Route::post('/stock-orders/{id}/items/{itemId}/finish', [\App\Http\Controllers\API\Common\StockOrderController::class, 'finishItem'])->name('stock-orders.items.finish')->where('id', '[0-9]+');
    Route::post('/stock-orders/{id}/items/{itemId}/complete', [\App\Http\Controllers\API\Common\StockOrderController::class, 'completeItem'])->name('stock-orders.items.complete')->where('id', '[0-9]+');

    // --- Products ---
    Route::get('/products', [\App\Http\Controllers\API\Common\ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [\App\Http\Controllers\API\Common\ProductController::class, 'store'])->name('products.store');
    Route::post('/products/bulk-upload', [\App\Http\Controllers\API\Common\ProductController::class, 'bulkUpload'])->name('products.bulk-upload');
    Route::get('/products/categories', [\App\Http\Controllers\API\Common\ProductController::class, 'categories'])->name('products.categories');
    Route::post('/products/categories', [\App\Http\Controllers\API\Common\ProductController::class, 'storeCategory'])->name('products.categories.store');
    Route::get('/products/subcategories', [\App\Http\Controllers\API\Common\ProductController::class, 'subcategories'])->name('products.subcategories');
    Route::post('/products/subcategories', [\App\Http\Controllers\API\Common\ProductController::class, 'storeSubcategory'])->name('products.subcategories.store');
    Route::get('/products/subcategories/{id}', [\App\Http\Controllers\API\Common\ProductController::class, 'showSubcategory'])->name('products.subcategories.show');
    Route::get('/products/category-options', [\App\Http\Controllers\API\Common\ProductController::class, 'getCategoryOptions'])->name('products.category-options');
    Route::get('/get-product-details', [\App\Http\Controllers\API\Common\ProductController::class, 'getProductDetails'])->name('get-product-details');
    Route::get('/products/generate-pdf', [\App\Http\Controllers\API\Common\ProductController::class, 'generatePdf'])->name('products.generate-pdf');
    Route::get('/products/{id}', [\App\Http\Controllers\API\Common\ProductController::class, 'show'])->name('products.show')->where('id', '[0-9]+');
    Route::post('/products/{id}', [\App\Http\Controllers\API\Common\ProductController::class, 'update'])->name('products.update')->where('id', '[0-9]+');
    Route::delete('/products/{id}', [\App\Http\Controllers\API\Common\ProductController::class, 'destroy'])->name('products.destroy')->where('id', '[0-9]+');

    // --- Designs ---
    Route::get('/designs', [\App\Http\Controllers\API\Common\DesignController::class, 'index'])->name('designs.index');
    Route::post('/designs', [\App\Http\Controllers\API\Common\DesignController::class, 'store'])->name('designs.store');
    Route::get('/designs/approval-queue', [\App\Http\Controllers\API\Common\DesignController::class, 'approvalQueue'])->name('designs.approval-queue');
    Route::get('/designs/generate-pdf', [\App\Http\Controllers\API\Common\DesignController::class, 'generatePdf'])->name('designs.generate-pdf');
    Route::get('/designs/generate-code/{product}', [\App\Http\Controllers\API\Common\DesignController::class, 'getGeneratedCode'])->name('designs.generate-code');
    Route::post('/designs/bulk-accept', [\App\Http\Controllers\API\Common\DesignController::class, 'bulkAccept'])->name('designs.bulk-accept');
    Route::post('/designs/bulk-reject', [\App\Http\Controllers\API\Common\DesignController::class, 'bulkReject'])->name('designs.bulk-reject');
    Route::get('/designs/{id}', [\App\Http\Controllers\API\Common\DesignController::class, 'show'])->name('designs.show')->where('id', '[0-9]+');
    Route::post('/designs/{id}', [\App\Http\Controllers\API\Common\DesignController::class, 'update'])->name('designs.update')->where('id', '[0-9]+');
    Route::post('/designs/{id}/accept', [\App\Http\Controllers\API\Common\DesignController::class, 'accept'])->name('designs.accept')->where('id', '[0-9]+');
    Route::post('/designs/{id}/reject', [\App\Http\Controllers\API\Common\DesignController::class, 'reject'])->name('designs.reject')->where('id', '[0-9]+');
    Route::post('/designs/{id}/favourite', [\App\Http\Controllers\API\Common\DesignController::class, 'favourite'])->name('designs.favourite')->where('id', '[0-9]+');

    // --- Catalogue ---
    Route::get('/catalogue', [\App\Http\Controllers\API\Common\CatalogueController::class, 'index'])->name('catalogue.index');
    Route::get('/catalogue/generate-pdf', [\App\Http\Controllers\API\Common\CatalogueController::class, 'generatePdf'])->name('catalogue.generate-pdf');
    Route::get('/catalogue/{id}', [\App\Http\Controllers\API\Common\CatalogueController::class, 'show'])->name('catalogue.show')->where('id', '[0-9]+');

    // --- Key Users ---
    Route::get('/key-users', [\App\Http\Controllers\API\Common\KeyUserController::class, 'index'])->name('key-users.index');
    Route::get('/key-users/generate-pdf', [\App\Http\Controllers\API\Common\KeyUserController::class, 'generatePdf'])->name('key-users.generate-pdf');
    Route::post('/key-users', [\App\Http\Controllers\API\Common\KeyUserController::class, 'store'])->name('key-users.store');
    Route::get('/key-users/{id}', [\App\Http\Controllers\API\Common\KeyUserController::class, 'show'])->name('key-users.show')->where('id', '[0-9]+');
    Route::post('/key-users/{id}', [\App\Http\Controllers\API\Common\KeyUserController::class, 'update'])->name('key-users.update')->where('id', '[0-9]+');
    Route::delete('/key-users/{id}', [\App\Http\Controllers\API\Common\KeyUserController::class, 'destroy'])->name('key-users.destroy')->where('id', '[0-9]+');

    // --- Users (End Users) ---
    Route::get('/users', [\App\Http\Controllers\API\Common\UserController::class, 'index'])->name('users.index');
    Route::get('/users/generate-pdf', [\App\Http\Controllers\API\Common\UserController::class, 'generatePdf'])->name('users.generate-pdf');
    Route::post('/users', [\App\Http\Controllers\API\Common\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}', [\App\Http\Controllers\API\Common\UserController::class, 'show'])->name('users.show')->where('id', '[0-9]+');
    Route::post('/users/{id}', [\App\Http\Controllers\API\Common\UserController::class, 'update'])->name('users.update')->where('id', '[0-9]+');
    Route::delete('/users/{id}', [\App\Http\Controllers\API\Common\UserController::class, 'destroy'])->name('users.destroy')->where('id', '[0-9]+');

    // --- Favorites ---
    Route::get('/favorites', [\App\Http\Controllers\API\Common\FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites', [\App\Http\Controllers\API\Common\FavoriteController::class, 'store'])->name('favorites.store');
    Route::post('/favorites/{productId}/toggle', [\App\Http\Controllers\API\Common\FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::post('/favorites/{productId}', [\App\Http\Controllers\API\Common\FavoriteController::class, 'destroy'])->name('favorites.destroy');
    Route::get('/admin/favorites', [\App\Http\Controllers\API\Common\FavoriteController::class, 'adminIndex'])->name('favorites.admin-index');

    // --- Notifications ---
    Route::post('/store-fcm-token', [\App\Http\Controllers\API\Common\NotificationController::class, 'storeFcmToken'])->name('notifications.store-fcm-token');

    // --- Meetings / Video Call ---
    Route::get('/meetings', [\App\Http\Controllers\API\Common\MeetingApiController::class, 'index'])->name('meetings.index');
    Route::get('/meetings/participants', [\App\Http\Controllers\API\Common\MeetingApiController::class, 'getParticipants'])->name('meetings.participants');
    Route::post('/meetings', [\App\Http\Controllers\API\Common\MeetingApiController::class, 'store'])->name('meetings.store');
    Route::post('/meetings/{id}/approve', [\App\Http\Controllers\API\Common\MeetingApiController::class, 'approve'])->name('meetings.approve');
    Route::post('/meetings/{id}/cancel', [\App\Http\Controllers\API\Common\MeetingApiController::class, 'cancel'])->name('meetings.cancel');
    Route::get('/meetings/{room_id}/token', [\App\Http\Controllers\API\Common\MeetingApiController::class, 'getAgoraToken'])->name('meetings.token');
    Route::post('/meetings/{room_id}/notify-join', [\App\Http\Controllers\API\Common\MeetingApiController::class, 'notifyJoin'])->name('meetings.notify-join');

    // --- Chat ---
    Route::get('/chat', [\App\Http\Controllers\API\Common\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/search', [\App\Http\Controllers\API\Common\ChatController::class, 'search'])->name('chat.search');
    Route::post('/chat/start', [\App\Http\Controllers\API\Common\ChatController::class, 'start'])->name('chat.start');
    Route::get('/chat/{conversation}', [\App\Http\Controllers\API\Common\ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat', [\App\Http\Controllers\API\Common\ChatController::class, 'store'])->name('chat.store');
    Route::delete('/chat/message/{id}', [\App\Http\Controllers\API\Common\ChatController::class, 'destroy'])->name('chat.message.destroy');
    Route::delete('/chat/conversation/{id}', [\App\Http\Controllers\API\Common\ChatController::class, 'destroyConversation'])->name('chat.conversation.destroy');

    // --- Profile Unified (Optional redirection or direct) ---
    Route::get('/buyer/profile', [\App\Http\Controllers\API\Common\BuyerController::class, 'getProfile'])->name('buyer.profile');
    Route::post('/buyer/profile', [\App\Http\Controllers\API\Common\BuyerController::class, 'updateProfile'])->name('buyer.profile.update');
    Route::get('/craftsman/profile', [\App\Http\Controllers\API\Common\CraftsmanController::class, 'getProfile'])->name('craftsman.profile');
    Route::post('/craftsman/profile', [\App\Http\Controllers\API\Common\CraftsmanController::class, 'updateProfile'])->name('craftsman.profile.update');

    // --- New Updates ---
    Route::get('/new-updates', [\App\Http\Controllers\API\Common\NewUpdatesApiController::class, 'index'])->name('new-updates.index');
    Route::post('/new-updates/{id}/mark-as-seen', [\App\Http\Controllers\API\Common\NewUpdatesApiController::class, 'markAsSeen'])->name('new-updates.mark-as-seen');
});
