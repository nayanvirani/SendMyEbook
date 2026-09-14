<?php

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

// Embedded Shopify Admin app shell. Anything not matched above renders the
// same shell so the client-side router can take over on a hard refresh.
Route::get('/{any?}', EmbeddedAppController::class)
    ->where('any', '^(?!api|auth|downloads|webhooks).*$')
    ->name('embedded.app');
