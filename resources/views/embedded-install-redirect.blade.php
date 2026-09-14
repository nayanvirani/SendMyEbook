<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Redirecting…</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- App Bridge needs to be present even on this bounce page so Shopify
         doesn't treat the top-level navigation below as an unexpected
         iframe break-out. --}}
    <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js" data-api-key="{{ config('shopify.api_key') }}"></script>
</head>
<body>
    <script>
        // Shopify Admin embeds this URL in an iframe; OAuth has to happen
        // in the top-level window, not inside the iframe.
        window.top.location.href = "{{ route('auth.redirect', ['shop' => $shop]) }}";
    </script>
</body>
</html>
