<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Your download{{ $downloadToken->digitalProduct->shopify_product_title ? ' — '.$downloadToken->digitalProduct->shopify_product_title : '' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root { --brand: {{ $brandColor }}; }
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(180deg, #f6f7f9 0%, #eef0f3 100%);
            margin: 0;
            padding: 3rem 1rem;
            color: #1f2328;
        }
        .wrap { max-width: 560px; margin: 0 auto; }
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 1px 2px rgba(20,20,43,.04), 0 8px 24px rgba(20,20,43,.06);
        }
        .logo { display: block; max-height: 36px; margin: 0 0 1.5rem; }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .375rem;
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: .03em;
            text-transform: uppercase;
            color: var(--brand);
            background: #f6f7f9;
            background: color-mix(in srgb, var(--brand) 12%, white);
            padding: .25rem .625rem;
            border-radius: 999px;
            margin-bottom: .75rem;
        }
        h1 { font-size: 1.375rem; line-height: 1.3; margin: 0 0 .375rem; }
        .order-ref { color: #6b7280; font-size: .875rem; margin: 0 0 1.75rem; }
        .files { border: 1px solid #edeef1; border-radius: 12px; overflow: hidden; }
        .file {
            display: flex;
            align-items: center;
            gap: .875rem;
            padding: 1rem 1.125rem;
            background: #fff;
        }
        .file + .file { border-top: 1px solid #edeef1; }
        .file-icon {
            flex: none;
            width: 40px;
            height: 40px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .625rem;
            font-weight: 700;
            letter-spacing: .02em;
            color: #fff;
        }
        .file-info { flex: 1; min-width: 0; }
        .file-name {
            font-size: .9rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .file-size { font-size: .75rem; color: #8a8f98; margin-top: .125rem; }
        .btn {
            flex: none;
            display: inline-flex;
            align-items: center;
            gap: .375rem;
            background: var(--brand);
            color: #fff;
            text-decoration: none;
            padding: .5rem .875rem;
            border-radius: 8px;
            font-size: .8125rem;
            font-weight: 600;
            transition: filter .15s ease;
        }
        .btn:hover { filter: brightness(0.92); }
        .info-row {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-top: 1.5rem;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: .375rem;
            font-size: .8125rem;
            color: #4b5563;
            background: #f6f7f9;
            padding: .375rem .75rem;
            border-radius: 999px;
        }
        .pill svg { flex: none; }
        .license {
            margin-top: 1.5rem;
            padding: 1rem 1.125rem;
            background: #f6f7f9;
            border-radius: 10px;
        }
        .license-label { font-size: .75rem; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; margin-bottom: .375rem; }
        .license code {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: .06em;
            color: #1f2328;
        }
        .footer { text-align: center; margin-top: 1.75rem; font-size: .75rem; color: #9aa0a8; }
        @media (max-width: 480px) {
            .card { padding: 1.75rem 1.25rem; border-radius: 12px; }
            .file { flex-wrap: wrap; }
            .btn { margin-left: auto; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            @if ($logoUrl)
                <img class="logo" src="{{ $logoUrl }}" alt="{{ $downloadToken->order->shop->shop_name }}">
            @endif

            <span class="eyebrow">
                <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a1 1 0 0 1 1 1v8.586l2.293-2.293a1 1 0 1 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 1 1 1.414-1.414L9 11.586V3a1 1 0 0 1 1-1Z"/><path d="M4 15a1 1 0 0 1 1 1v1h10v-1a1 1 0 1 1 2 0v2a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-2a1 1 0 0 1 1-1Z"/></svg>
                Ready to download
            </span>
            <h1>{{ $downloadToken->digitalProduct->shopify_product_title }}</h1>
            <p class="order-ref">Order #{{ $downloadToken->order->shopify_order_number }} · {{ $downloadToken->order->shop->shop_name }}</p>

            <div class="files">
                @foreach ($files as $file)
                    @php
                        $ext = strtoupper(pathinfo($file->original_filename, PATHINFO_EXTENSION)) ?: 'FILE';
                        $colors = ['PDF' => '#dc4a3d', 'ZIP' => '#c0862a', 'MP3' => '#7c5cbf', 'MP4' => '#7c5cbf', 'WAV' => '#7c5cbf', 'JPG' => '#2f7de1', 'PNG' => '#2f7de1', 'JPEG' => '#2f7de1'];
                        $color = $colors[$ext] ?? '#5c6470';
                    @endphp
                    <div class="file">
                        <div class="file-icon" style="background: {{ $color }};">{{ Str::limit($ext, 4, '') }}</div>
                        <div class="file-info">
                            <div class="file-name">{{ $file->original_filename }}</div>
                            <div class="file-size">{{ $file->size_bytes ? number_format($file->size_bytes / 1048576, 1).' MB' : '' }}</div>
                        </div>
                        <a class="btn" target="_blank" rel="noopener" href="{{ route('customer.downloads.file', ['token' => $downloadToken->token, 'file' => $file->id]) }}">
                            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a1 1 0 0 1 1 1v8.586l2.293-2.293a1 1 0 1 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 1 1 1.414-1.414L9 11.586V3a1 1 0 0 1 1-1Z"/><path d="M4 15a1 1 0 0 1 1 1v1h10v-1a1 1 0 1 1 2 0v2a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-2a1 1 0 0 1 1-1Z"/></svg>
                            Download
                        </a>
                    </div>
                @endforeach
            </div>

            @if ($downloadToken->license_key)
                <div class="license">
                    <div class="license-label">License key</div>
                    <code>{{ $downloadToken->license_key }}</code>
                </div>
            @endif

            <div class="info-row">
                <span class="pill">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a1 1 0 0 1 1 1v8.586l2.293-2.293a1 1 0 1 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 1 1 1.414-1.414L9 11.586V3a1 1 0 0 1 1-1Z"/></svg>
                    @if ($downloadToken->max_downloads)
                        {{ $downloadToken->download_count }} of {{ $downloadToken->max_downloads }} downloads used
                    @else
                        Unlimited downloads
                    @endif
                </span>
                @if ($downloadToken->expires_at)
                    <span class="pill">
                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M10 3a7 7 0 1 0 0 14 7 7 0 0 0 0-14ZM1 10a9 9 0 1 1 18 0 9 9 0 0 1-18 0Zm9-5a1 1 0 0 1 1 1v3.586l2.207 2.207a1 1 0 0 1-1.414 1.414l-2.5-2.5A1 1 0 0 1 9 10V6a1 1 0 0 1 1-1Z"/></svg>
                        Expires {{ $downloadToken->expires_at->format('M j, Y g:i A') }}
                    </span>
                @endif
            </div>
        </div>
        <p class="footer">Secured by SendMyEbook</p>
    </div>
</body>
</html>
