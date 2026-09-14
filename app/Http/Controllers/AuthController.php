<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Services\Shopify\ShopifyAuthService;
use App\Services\Shopify\ShopifyGraphQLClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Classic Shopify OAuth install flow. On first install (or a scope
 * upgrade), Shopify directs the merchant through /auth, which redirects to
 * Shopify's consent screen; /auth/callback exchanges the returned code for
 * an offline access token and stores the shop.
 */
class AuthController extends Controller
{
    public function __construct(private readonly ShopifyAuthService $authService) {}

    public function redirectToShopify(Request $request): RedirectResponse
    {
        $shop = (string) $request->query('shop');

        abort_unless($this->authService->isValidShopDomain($shop), 400, 'Invalid shop parameter.');

        $state = $this->authService->generateState();
        $request->session()->put('shopify_oauth_state', $state);

        return redirect()->away($this->authService->buildAuthorizeUrl($shop, $state));
    }

    public function callback(Request $request): RedirectResponse
    {
        $query = $request->query();

        abort_unless($this->authService->verifyHmac($query), 401, 'Invalid HMAC.');

        $shop = (string) $request->query('shop');
        abort_unless($this->authService->isValidShopDomain($shop), 400, 'Invalid shop parameter.');

        $state = $request->query('state');
        abort_unless($state && $state === $request->session()->pull('shopify_oauth_state'), 401, 'Invalid state.');

        $code = (string) $request->query('code');
        $tokenResponse = $this->authService->exchangeCodeForToken($shop, $code);

        $shopRecord = Shop::query()->updateOrCreate(
            ['shop_domain' => $shop],
            [
                'access_token' => $tokenResponse['access_token'],
                'scope' => $tokenResponse['scope'] ?? null,
                'is_active' => true,
                'installed_at' => now(),
                'uninstalled_at' => null,
            ]
        );

        (new ShopifyGraphQLClient($shopRecord))->registerWebhooks();

        return redirect()->to("https://admin.shopify.com/store/{$this->shopHandle($shop)}/apps/".config('shopify.api_key'));
    }

    private function shopHandle(string $shopDomain): string
    {
        return str_replace('.myshopify.com', '', $shopDomain);
    }
}
