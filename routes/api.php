<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DigitalProductController;
use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\StorefrontOrderController;
use App\Http\Controllers\Webhooks\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Merchant JSON API
|--------------------------------------------------------------------------
|
| Consumed by the embedded React app via App Bridge's authenticatedFetch,
| which attaches the current session token as a Bearer token. Resolved to
| a Shop by the shopify.session middleware.
|
*/
Route::middleware('shopify.session')->group(function () {
    // No free plan — a shop that has never subscribed can only reach
    // billing itself, plus this one status check the frontend uses to
    // decide whether to show the paywall before hitting anything else.
    Route::get('/billing/plans', [BillingController::class, 'plans']);
    Route::get('/subscription-status', [BillingController::class, 'status']);

    // Called by the Thank You page / Customer Account extensions — a
    // customer's access to files they already paid for must not depend on
    // whether the merchant's own subscription is currently active.
    Route::get('/storefront/orders/{shopifyOrderId}/downloads', [StorefrontOrderController::class, 'downloads']);

    Route::middleware('active_subscription')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::apiResource('digital-products', DigitalProductController::class);

        Route::post('/digital-products/{digitalProduct}/files', [FileController::class, 'store']);
        Route::patch('/files/{file}/complete', [FileController::class, 'complete']);
        Route::delete('/files/{file}', [FileController::class, 'destroy']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);

        Route::get('/downloads', [DownloadController::class, 'index']);

        Route::get('/analytics', [AnalyticsController::class, 'index']);

        Route::get('/settings', [SettingController::class, 'show']);
        Route::put('/settings', [SettingController::class, 'update']);
    });
});

/*
|--------------------------------------------------------------------------
| Shopify Webhooks
|--------------------------------------------------------------------------
|
| Verified by signature (shopify.webhook), never by session token — these
| requests come from Shopify's servers, not a merchant's browser.
|
*/
Route::middleware('shopify.webhook')->group(function () {
    Route::post('/webhooks/orders-paid', [WebhookController::class, 'ordersPaid']);
    Route::post('/webhooks/orders-updated', [WebhookController::class, 'ordersUpdated']);
    Route::post('/webhooks/refunds-create', [WebhookController::class, 'refundsCreate']);
    Route::post('/webhooks/app-uninstalled', [WebhookController::class, 'appUninstalled']);

    // Mandatory GDPR compliance topics, configured in the Partner
    // Dashboard's "Compliance webhooks" section rather than registered via
    // the Admin API like the topics above.
    Route::post('/webhooks/customers-data-request', [WebhookController::class, 'customersDataRequest']);
    Route::post('/webhooks/customers-redact', [WebhookController::class, 'customersRedact']);
    Route::post('/webhooks/shop-redact', [WebhookController::class, 'shopRedact']);

    // Shopify App Pricing: fires whenever a shop's subscription is
    // created, activated, cancelled, frozen, etc. on Shopify's own hosted
    // plan page — this app never creates the subscription itself.
    Route::post('/webhooks/app-subscriptions-update', [WebhookController::class, 'appSubscriptionsUpdate']);
});
