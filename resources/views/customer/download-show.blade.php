<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Your download</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f5f7; margin: 0; padding: 2rem 1rem; color: #1a1a1a; }
        .card { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .logo { display: block; max-height: 40px; margin: 0 auto 1.5rem; }
        h1 { font-size: 1.25rem; margin-top: 0; }
        .file { display: flex; align-items: center; justify-content: space-between; padding: .75rem 0; border-top: 1px solid #eee; }
        .file:first-of-type { border-top: none; }
        .btn { background: {{ $brandColor }}; color: #fff; text-decoration: none; padding: .5rem 1rem; border-radius: 6px; font-size: .9rem; }
        .meta { color: #6b7280; font-size: .85rem; margin-top: 1.5rem; }
        .license { margin-top: 1.5rem; padding: .75rem 1rem; background: #f4f5f7; border-radius: 8px; }
        .license code { font-size: 1rem; font-weight: 600; letter-spacing: .05em; }
    </style>
</head>
<body>
    <div class="card">
        @if ($logoUrl)
            <img class="logo" src="{{ $logoUrl }}" alt="">
        @endif

        <h1>{{ $downloadToken->digitalProduct->shopify_product_title }}</h1>
        <p>Order {{ $downloadToken->order->shopify_order_number }}</p>

        @foreach ($files as $file)
            <div class="file">
                <span>{{ $file->original_filename }}</span>
                <a class="btn" target="_blank" rel="noopener" href="{{ route('customer.downloads.file', ['token' => $downloadToken->token, 'file' => $file->id]) }}">Download</a>
            </div>
        @endforeach

        @if ($downloadToken->license_key)
            <div class="license">
                License key<br>
                <code>{{ $downloadToken->license_key }}</code>
            </div>
        @endif

        <p class="meta">
            @if ($downloadToken->max_downloads)
                {{ $downloadToken->download_count }} of {{ $downloadToken->max_downloads }} downloads used.
            @else
                Unlimited downloads.
            @endif
            @if ($downloadToken->expires_at)
                <br>Link expires {{ $downloadToken->expires_at->format('M j, Y g:i A') }}.
            @endif
        </p>
    </div>
</body>
</html>
