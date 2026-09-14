<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use App\Services\Shopify\SessionTokenVerifier;
use App\Services\Shopify\ShopifyAuthService;
use App\Services\Shopify\ShopifyGraphQLClient;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Authenticates requests made from the embedded React app via App Bridge's
 * `authenticatedFetch`, which attaches the session token as a Bearer token.
 * Resolves the token to a Shop and binds it into the request for
 * controllers to use.
 *
 * Because shopify.app.toml has use_legacy_install_flow = false, Shopify
 * grants scopes and embeds the app itself ("managed installation") without
 * ever calling our classic /auth/callback — the first this app ever hears
 * about a shop is a session token on a request like this one. So on first
 * contact with an unknown (or previously uninstalled) shop, this
 * middleware performs Token Exchange itself to obtain an offline access
 * token and provisions the Shop record right here, instead of redirecting
 * anywhere.
 */
class VerifyShopifySessionToken
{
    public function __construct(
        private readonly SessionTokenVerifier $verifier,
        private readonly ShopifyAuthService $authService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();
        $result = $this->verifier->verify($bearer);

        if (! $result) {
            abort(401, 'Missing or invalid session token.');
        }

        $shop = Shop::query()->where('shop_domain', $result['shop'])->first();

        if (! $shop || ! $shop->is_active || ! $shop->access_token) {
            $shop = $this->provisionViaTokenExchange($result['shop'], $bearer);
        }

        $request->attributes->set('shop', $shop);

        return $next($request);
    }

    private function provisionViaTokenExchange(string $shopDomain, string $sessionToken): Shop
    {
        try {
            $tokenResponse = $this->authService->exchangeSessionTokenForOfflineToken($shopDomain, $sessionToken);
        } catch (Throwable $e) {
            Log::error('Shopify token exchange failed', ['shop' => $shopDomain, 'error' => $e->getMessage()]);
            abort(401, 'Shop not installed.');
        }

        $shop = Shop::query()->updateOrCreate(
            ['shop_domain' => $shopDomain],
            [
                'access_token' => $tokenResponse['access_token'],
                'scope' => $tokenResponse['scope'] ?? null,
                'is_active' => true,
                'installed_at' => now(),
                'uninstalled_at' => null,
            ]
        );

        (new ShopifyGraphQLClient($shop))->registerWebhooks();

        return $shop;
    }
}
