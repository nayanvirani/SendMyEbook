<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Download unavailable</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f5f7; margin: 0; padding: 2rem 1rem; color: #1a1a1a; }
        .card { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        @php
            $messages = [
                'invalid' => 'This download link is not valid.',
                'expired' => 'This download link has expired.',
                'limit_reached' => 'The download limit for this link has been reached.',
                'revoked' => 'This download link has been disabled.',
            ];
        @endphp
        <h1>Access denied</h1>
        <p>{{ $messages[$reason] ?? 'This download link cannot be used.' }}</p>
    </div>
</body>
</html>
