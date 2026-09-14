<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the platform-owner admin panel (/admin/*), completely separate
 * from Shopify's own auth — this is a normal session-cookie login for the
 * app's own operator, not a merchant.
 */
class EnsureIsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_super_admin) {
            abort(403, 'Not authorized.');
        }

        return $next($request);
    }
}
