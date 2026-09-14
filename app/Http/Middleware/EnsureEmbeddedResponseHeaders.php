<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shopify embeds the app in an iframe inside Admin, so this app must allow
 * framing from Shopify's own domains rather than the browser default deny.
 */
class EnsureEmbeddedResponseHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set(
            'Content-Security-Policy',
            'frame-ancestors https://*.myshopify.com https://admin.shopify.com;'
        );

        return $response;
    }
}
