<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Download unavailable</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(180deg, #f6f7f9 0%, #eef0f3 100%);
            margin: 0;
            padding: 3rem 1rem;
            color: #1f2328;
        }
        .wrap { max-width: 440px; margin: 0 auto; }
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 1px 2px rgba(20,20,43,.04), 0 8px 24px rgba(20,20,43,.06);
            text-align: center;
        }
        .logo { display: block; max-height: 32px; margin: 0 auto 1.5rem; }
        .icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 1.25rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fdf1f0;
            color: #c0342b;
        }
        h1 { font-size: 1.25rem; margin: 0 0 .5rem; }
        p.desc { color: #6b7280; font-size: .9rem; margin: 0; line-height: 1.5; }
        .footer { text-align: center; margin-top: 1.75rem; font-size: .75rem; color: #9aa0a8; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            @if ($logoUrl ?? null)
                <img class="logo" src="{{ $logoUrl }}" alt="">
            @endif

            @php
                $copy = [
                    'invalid' => ['title' => 'Link not found', 'desc' => 'This download link is not valid. Double-check the link from your order confirmation email.'],
                    'expired' => ['title' => 'Link expired', 'desc' => 'This download link has passed its expiration date. Contact the store for help.'],
                    'limit_reached' => ['title' => 'Download limit reached', 'desc' => 'This link has already been used the maximum number of times allowed.'],
                    'revoked' => ['title' => 'Access revoked', 'desc' => 'This download link has been disabled, usually because the order was refunded or cancelled.'],
                ];
                $reasonCopy = $copy[$reason] ?? ['title' => 'Access denied', 'desc' => 'This download link cannot be used.'];
                $icons = [
                    'invalid' => '<path d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm0 2a6 6 0 1 1 0 12 6 6 0 0 1 0-12Zm-1 3a1 1 0 1 0 0 2h.01a1 1 0 1 0 0-2H9Zm.25 4a.75.75 0 0 0-.75.75v2.5a.75.75 0 0 0 1.5 0v-2.5a.75.75 0 0 0-.75-.75Z"/>',
                    'expired' => '<path fill-rule="evenodd" clip-rule="evenodd" d="M10 3a7 7 0 1 0 0 14 7 7 0 0 0 0-14ZM1 10a9 9 0 1 1 18 0 9 9 0 0 1-18 0Zm9-5a1 1 0 0 1 1 1v3.586l2.207 2.207a1 1 0 0 1-1.414 1.414l-2.5-2.5A1 1 0 0 1 9 10V6a1 1 0 0 1 1-1Z"/>',
                    'limit_reached' => '<path d="M10 2a1 1 0 0 1 1 1v8.586l2.293-2.293a1 1 0 1 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 1 1 1.414-1.414L9 11.586V3a1 1 0 0 1 1-1Z"/><path d="M4 15a1 1 0 0 1 1 1v1h10v-1a1 1 0 1 1 2 0v2a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-2a1 1 0 0 1 1-1Z"/>',
                    'revoked' => '<path fill-rule="evenodd" clip-rule="evenodd" d="M5 9V7a5 5 0 0 1 10 0v2a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2Zm2-2a3 3 0 1 1 6 0v2H7V7Z"/>',
                ];
                $iconPath = $icons[$reason] ?? '<path fill-rule="evenodd" clip-rule="evenodd" d="M10 3a7 7 0 1 0 0 14 7 7 0 0 0 0-14ZM1 10a9 9 0 1 1 18 0 9 9 0 0 1-18 0Zm10-4a1 1 0 1 0-2 0v5a1 1 0 1 0 2 0V6Zm-1 8a1.25 1.25 0 1 0 0 2.5 1.25 1.25 0 0 0 0-2.5Z"/>';
            @endphp

            <div class="icon">
                <svg width="26" height="26" viewBox="0 0 20 20" fill="currentColor">{!! $iconPath !!}</svg>
            </div>

            <h1>{{ $reasonCopy['title'] }}</h1>
            <p class="desc">{{ $reasonCopy['desc'] }}</p>
        </div>
        <p class="footer">Secured by SendMyEbook</p>
    </div>
</body>
</html>
