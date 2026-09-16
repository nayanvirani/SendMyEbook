<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | The checkout and customer-account UI extensions call this app's API
    | from a Shopify-controlled sandbox origin, not from this app's own
    | domain, so those requests need real CORS headers to succeed in the
    | browser — the embedded admin app doesn't (it fetches same-origin
    | relative paths). Every route under api/* is already authorized by
    | its own session-token/shop-scoping check regardless of origin, so
    | allowing any origin here doesn't widen what a request can actually
    | do — it only affects whether the browser lets the caller read the
    | response.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
