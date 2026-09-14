<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Shop;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Serves either the React + Polaris embedded app shell (when Shopify is
 * loading this URL inside the Admin iframe, indicated by the `shop`/`host`
 * query params it always attaches), the OAuth-redirect bounce page (when
 * the shop has no completed install yet), or the public marketing/pricing
 * page (when the URL is visited directly, e.g. someone opening the Railway
 * domain in a browser).
 */
class EmbeddedAppController extends Controller
{
    public function __invoke(Request $request): View
    {
        $shopDomain = (string) $request->query('shop', '');

        if (! $shopDomain && ! $request->query('host')) {
            return view('landing', [
                'plans' => Plan::query()->where('is_active', true)->orderBy('sort_order')->get(),
            ]);
        }

        // Shopify loads this URL directly inside the Admin iframe on every
        // visit — it does not run our OAuth flow for us. If this shop has
        // never completed install (or was uninstalled), the embedded shell
        // has nothing to authenticate against, so bounce the top-level
        // window out to /auth instead of rendering an app that can only
        // fail every API call.
        if ($shopDomain && ! Shop::query()->where('shop_domain', $shopDomain)->where('is_active', true)->exists()) {
            return view('embedded-install-redirect', ['shop' => $shopDomain]);
        }

        return view('embedded', [
            'apiKey' => config('shopify.api_key'),
            'host' => (string) $request->query('host', ''),
        ]);
    }
}
