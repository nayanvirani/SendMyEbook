<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Serves either the React + Polaris embedded app shell (when Shopify is
 * loading this URL inside the Admin iframe, indicated by the `shop`/`host`
 * query params it always attaches) or the public marketing/pricing page
 * (when the URL is visited directly, e.g. someone opening the Railway
 * domain in a browser). Shopify re-sends these params on every navigation,
 * including a hard refresh of a client-side route, so this single check
 * covers both cases without a separate landing route.
 */
class EmbeddedAppController extends Controller
{
    public function __invoke(Request $request): View
    {
        if (! $request->query('shop') && ! $request->query('host')) {
            return view('landing', [
                'plans' => Plan::query()->where('is_active', true)->orderBy('sort_order')->get(),
            ]);
        }

        return view('embedded', [
            'apiKey' => config('shopify.api_key'),
            'host' => (string) $request->query('host', ''),
        ]);
    }
}
