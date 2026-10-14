<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shopify App Credentials
    |--------------------------------------------------------------------------
    */

    'api_key' => env('SHOPIFY_API_KEY'),
    'api_secret' => env('SHOPIFY_API_SECRET'),
    'api_version' => env('SHOPIFY_API_VERSION', '2026-07'),

    // Comma-separated in .env, exposed here as an array.
    'scopes' => array_filter(array_map('trim', explode(',', env(
        'SHOPIFY_SCOPES',
        'read_products,read_orders,read_returns'
    )))),

    /*
    |--------------------------------------------------------------------------
    | App URLs
    |--------------------------------------------------------------------------
    */

    // Public URL Shopify redirects to during OAuth and embeds in the Admin
    // iframe. Must match the App URL configured in the Partner Dashboard.
    'app_url' => env('SHOPIFY_APP_URL', env('APP_URL')),

    // The app's handle in Shopify's own URLs, e.g.
    // admin.shopify.com/store/{shop}/apps/{app_handle}. Needed to build
    // the link to Shopify's own hosted App Pricing plan-selection page.
    'app_handle' => env('SHOPIFY_APP_HANDLE', 'sendmyebook'),

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    |
    | Topics registered on every install/scope-update via the Admin GraphQL
    | API. "Mandatory compliance" topics (customers/data_request,
    | customers/redact, shop/redact) are configured separately in the
    | Partner Dashboard's "Compliance webhooks" section rather than here.
    |
    */

    'webhook_topics' => [
        'orders/paid' => '/api/webhooks/orders-paid',
        'orders/updated' => '/api/webhooks/orders-updated',
        'orders/cancelled' => '/api/webhooks/orders-cancelled',
        'orders/delete' => '/api/webhooks/orders-delete',
        'refunds/create' => '/api/webhooks/refunds-create',
        'app/uninstalled' => '/api/webhooks/app-uninstalled',
    ],

    /*
    |--------------------------------------------------------------------------
    | Billing
    |--------------------------------------------------------------------------
    |
    | Whether appSubscriptionCreate charges are marked `test: true` (no real
    | money moves; required for development stores, which silently refuse
    | non-test charges). This is about the SHOPIFY STORE being tested
    | against, not this Laravel app's own environment — our app runs with
    | APP_ENV=production on Railway even while every install so far has
    | been a development store, so this must not be tied to
    | app()->isProduction(). Flip to false once the app is actually
    | listed and being installed on real merchant stores.
    |
    */

    'billing_test_mode' => (bool) env('SHOPIFY_BILLING_TEST_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | Partner API (admin Revenue page)
    |--------------------------------------------------------------------------
    |
    | An organization-level credential (Partner Dashboard → Settings →
    | Partner API clients) — different from api_key/api_secret above,
    | which are per-app. This is the only way to see real historical
    | charge/payout data; the per-shop Admin API only ever shows a
    | shop's current subscription snapshot. Shared across every app in
    | the Partner org, so PartnerApiClient filters results down to this
    | app's own installed shops.
    |
    */

    'partner_api_token' => env('PARTNER_API_TOKEN'),
    'partner_org_id' => env('PARTNER_ORG_ID'),
    'partner_api_version' => env('PARTNER_API_VERSION', '2026-07'),

    // Developer revenue-share cut, computed off gross (pre-Shopify-fee)
    // revenue on the admin Revenue page.
    'revenue_share_pct' => (float) env('REVENUE_SHARE_PCT', 0.15),
];
