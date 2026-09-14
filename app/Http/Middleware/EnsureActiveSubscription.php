<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks every merchant-facing API route except billing itself until the
 * shop has an active subscription — there is no free plan, so a shop that
 * has never subscribed must not be able to use the app at all.
 */
class EnsureActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        if (! $shop->activeSubscription) {
            return response()->json([
                'error' => 'subscription_required',
                'message' => 'Choose a plan to start using SendMyEbook.',
            ], 402);
        }

        return $next($request);
    }
}
