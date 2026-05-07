<?php

use App\Http\Controllers\Api\AdminDigitalProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepositController;
use App\Http\Controllers\Api\EWalletController;
use App\Http\Controllers\Api\OkeConnectCallbackController;
use App\Http\Controllers\Api\DigitalProductCategoryController;
use App\Http\Controllers\Api\DigitalProductController;
use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\QrisMarkupController;
use App\Http\Controllers\Api\RedeemCodeController;
use App\Http\Controllers\Api\SmmPanelController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Prefix: /api
| All routes here are automatically prefixed with /api
|
*/

// ── Public routes (no auth required) ────────────────────────────
Route::middleware('throttle:3,1')->post('/register', [AuthController::class, 'register']);
Route::middleware('throttle:5,1')->post('/login', [AuthController::class, 'login']);
Route::middleware('throttle:10,1')->post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::middleware('throttle:5,1')->post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::middleware('throttle:5,1')->post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::middleware('throttle:5,1')->post('/reset-password', [AuthController::class, 'resetPassword']);

// Products - public (existing OkeConnect)
Route::get('/categories', [ProductController::class, 'categories']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{category}/{provider}', [ProductController::class, 'byProvider']);

// E-Wallet Nominal Bebas
Route::get('/ewallet/options', [EWalletController::class, 'options']);

// OkeConnect Callback (called by OkeConnect server — no auth needed)
Route::get('/okeconnect/callback', [OkeConnectCallbackController::class, 'handle']);

// ── Digital Products - Public ───────────────────────────────────
Route::prefix('digital')->group(function () {
    // Categories
    Route::get('/categories', [DigitalProductCategoryController::class, 'index']);
    Route::get('/categories/{slug}', [DigitalProductCategoryController::class, 'show']);

    // All products grouped by category
    Route::get('/all-products', [DigitalProductCategoryController::class, 'allProducts']);

    // Products: filtered listing & detail
    Route::get('/products', [DigitalProductController::class, 'index']);
    Route::get('/products/{kode_produk}', [DigitalProductController::class, 'show']);
});

// ── Shortcut Endpoints (sesuai spesifikasi) ─────────────────────
// GET /api/category/{nama_apps}  → daftar produk berdasarkan nama aplikasi
// GET /api/productv1             → list semua produk lokal (no category)
// GET /api/productv1/{kodeproduk} → detail satu produk berdasarkan kode
Route::get('/category/{nama_apps}', [DigitalProductCategoryController::class, 'byApp']);
Route::get('/productv1', [DigitalProductController::class, 'listV1']);
Route::get('/productv1/{kodeproduk}', [DigitalProductController::class, 'showV1']);

// ── SMM Panel Public Search ──────────────────────────────────────
Route::get('/smm/apps', [SmmPanelController::class, 'apps']);
Route::get('/smm/search', [SmmPanelController::class, 'searchByApp']);

// ── Protected routes (requires Sanctum token) ───────────────────
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profile
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateProfile']);
    Route::put('/me/password', [AuthController::class, 'changePassword']);

    // Transactions (OkeConnect) — rate limited: max 10 per minute
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/transactions', [TransactionController::class, 'store']);
        Route::post('/ewallet/topup', [EWalletController::class, 'topup']);
    });

    // Orders (history + polling)
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::get('/orders/{order}/status', [OrderController::class, 'status']);
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice']);

    // Deposits (top up saldo via QRIS)
    Route::get('/deposits', [DepositController::class, 'index']);
    Route::post('/deposits', [DepositController::class, 'store']);
    Route::get('/deposits/{deposit}', [DepositController::class, 'show']);
    Route::get('/deposits/{deposit}/check', [DepositController::class, 'checkStatus']);

    // SMM Panel
    Route::prefix('smm')->group(function () {
        Route::get('/balance', [SmmPanelController::class, 'balance']);
        Route::get('/services', [SmmPanelController::class, 'services']);
        Route::post('/order', [SmmPanelController::class, 'order']);
        Route::get('/status/{orderId}', [SmmPanelController::class, 'status']);
        Route::post('/refill/{orderId}', [SmmPanelController::class, 'refill']);
        Route::get('/refill/status/{refillId}', [SmmPanelController::class, 'refillStatus']);
    });

    // ── Redeem Code (User) ──────────────────────────────────────
    Route::prefix('digital/redeem')->group(function () {
        Route::post('/validate', [RedeemCodeController::class, 'validateCode']);
        Route::post('/apply', [RedeemCodeController::class, 'apply']);
    });
});

// ── Inquiry / CEK Endpoint (Payday TrueID) ──────────────────────
Route::prefix('inquiry')->group(function () {
    Route::get('/products', [InquiryController::class, 'products']);
    Route::get('/bank-codes', [InquiryController::class, 'bankCodes']);
    Route::post('/check', [InquiryController::class, 'check']);
});

// ── Admin: QRIS Markup Settings ─────────────────────────────────
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/qris-markup', [QrisMarkupController::class, 'show']);
    Route::put('/qris-markup', [QrisMarkupController::class, 'update']);
});

// ── Admin routes (Sanctum + admin role) ──────────────────────────
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin/digital')->group(function () {
    // Categories CRUD
    Route::get('/categories', [AdminDigitalProductController::class, 'listCategories']);
    Route::post('/categories', [AdminDigitalProductController::class, 'storeCategory']);
    Route::put('/categories/{id}', [AdminDigitalProductController::class, 'updateCategory']);
    Route::delete('/categories/{id}', [AdminDigitalProductController::class, 'destroyCategory']);

    // Products CRUD
    Route::get('/products', [AdminDigitalProductController::class, 'listProducts']);
    Route::post('/products', [AdminDigitalProductController::class, 'storeProduct']);
    Route::put('/products/{id}', [AdminDigitalProductController::class, 'updateProduct']);
    Route::delete('/products/{id}', [AdminDigitalProductController::class, 'destroyProduct']);

    // Restock (tambah stok manual)
    Route::post('/products/{id}/restock', [AdminDigitalProductController::class, 'restockProduct']);

    // Redeem Codes CRUD
    Route::get('/redeem-codes', [AdminDigitalProductController::class, 'listRedeemCodes']);
    Route::post('/redeem-codes', [AdminDigitalProductController::class, 'storeRedeemCode']);
    Route::put('/redeem-codes/{id}', [AdminDigitalProductController::class, 'updateRedeemCode']);
    Route::delete('/redeem-codes/{id}', [AdminDigitalProductController::class, 'destroyRedeemCode']);
});

