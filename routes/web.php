<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\ShopController as AdminShopController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer\DownloadAccessController;
use App\Http\Controllers\EmbeddedAppController;
use Illuminate\Support\Facades\Route;

// Shopify OAuth install flow.
Route::get('/auth', [AuthController::class, 'redirectToShopify'])->name('auth.redirect');
Route::get('/auth/callback', [AuthController::class, 'callback'])->name('auth.callback');

// Shopify redirects the merchant's browser here (a top-level navigation,
// not an authenticatedFetch) after they approve/decline a billing charge.
Route::get('/billing/callback', [BillingController::class, 'callback'])->name('billing.callback');

// Public, token-gated customer download pages (no Shopify auth involved).
Route::get('/downloads/{token}', [DownloadAccessController::class, 'show'])->name('customer.downloads.show');
Route::get('/downloads/{token}/files/{file}', [DownloadAccessController::class, 'download'])->name('customer.downloads.file');

/*
|--------------------------------------------------------------------------
| Platform Owner Admin Panel
|--------------------------------------------------------------------------
|
| Separate from everything above: a normal session-cookie login for the
| app's own operator (not a merchant, not Shopify-authenticated) to manage
| billing plans/pricing, and see installed shops and subscriptions.
|
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.attempt');

    Route::middleware(['auth', 'super_admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('plans', AdminPlanController::class)->except('show');

        Route::get('/shops', [AdminShopController::class, 'index'])->name('shops.index');
        Route::post('/shops/{shop}/toggle-active', [AdminShopController::class, 'toggleActive'])->name('shops.toggle-active');

        Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
    });
});

// Embedded Shopify Admin app shell. Anything not matched above renders the
// same shell so the client-side router can take over on a hard refresh.
Route::get('/{any?}', EmbeddedAppController::class)
    ->where('any', '^(?!api|auth|admin|downloads|webhooks).*$')
    ->name('embedded.app');
