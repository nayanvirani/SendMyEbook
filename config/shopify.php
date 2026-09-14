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
        'refunds/create' => '/api/webhooks/refunds-create',
        'app/uninstalled' => '/api/webhooks/app-uninstalled',
    ],
];
