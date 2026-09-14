<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use App\Services\Shopify\SessionTokenVerifier;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates requests made from the embedded React app via App Bridge's
 * `authenticatedFetch`, which attaches the session token as a Bearer token.
 * Resolves the token to a Shop and binds it into the request for
 * controllers to use.
 */
class VerifyShopifySessionToken
{
    public function __construct(private readonly SessionTokenVerifier $verifier) {}

    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();
        $result = $this->verifier->verify($bearer);

        if (! $result) {
            abort(401, 'Missing or invalid session token.');
        }

        $shop = Shop::query()->where('shop_domain', $result['shop'])->where('is_active', true)->first();

        if (! $shop) {
            abort(401, 'Shop not installed.');
        }

        $request->attributes->set('shop', $shop);

        return $next($request);
    }
}
