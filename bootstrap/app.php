<?php

use App\Http\Middleware\EnsureActiveSubscription;
use App\Http\Middleware\EnsureEmbeddedResponseHeaders;
use App\Http\Middleware\EnsureIsSuperAdmin;
use App\Http\Middleware\VerifyShopifySessionToken;
use App\Http\Middleware\VerifyShopifyWebhook;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Railway terminates TLS at its edge and forwards plain HTTP to
        // this container, so without this every generated URL (route(),
        // asset(), the Vite manifest) comes out as http:// instead of
        // https:// — trust the single upstream hop and read its
        // X-Forwarded-Proto/Host headers instead.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'shopify.webhook' => VerifyShopifyWebhook::class,
            'shopify.session' => VerifyShopifySessionToken::class,
            'super_admin' => EnsureIsSuperAdmin::class,
            'active_subscription' => EnsureActiveSubscription::class,
        ]);

        // The only session-cookie login in this app is the platform-owner
        // admin panel (merchants authenticate via Shopify, never via this
        // guard) — so an unauthenticated hit on an `auth`-protected route
        // always means "send them to the admin login".
        $middleware->redirectGuestsTo('/admin/login');

        // The embedded app is rendered inside Shopify Admin's iframe, so the
        // default clickjacking protection has to be relaxed for our own
        // routes (Shopify's Content-Security-Policy frame-ancestors is set
        // separately in EnsureEmbeddedResponseHeaders).
        $middleware->web(append: [
            EnsureEmbeddedResponseHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
