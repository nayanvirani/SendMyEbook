<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Privacy Policy · SendMyEbook</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-gray-900 antialiased">
    <div class="mx-auto max-w-2xl px-6 py-16">
        <a href="/" class="flex items-center gap-2 text-lg font-semibold">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white">📩</span>
            SendMyEbook
        </a>

        <h1 class="mt-10 text-3xl font-bold tracking-tight">Privacy Policy</h1>
        <p class="mt-2 text-sm text-gray-500">Last updated {{ $lastUpdated }}</p>

        <div class="prose prose-sm mt-8 max-w-none space-y-6 text-gray-700 [&_h2]:mt-8 [&_h2]:text-lg [&_h2]:font-semibold [&_h2]:text-gray-900 [&_p]:leading-relaxed [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:space-y-1 [&_li]:leading-relaxed">
            {!! $content->privacy_policy !!}
        </div>
    </div>
</body>
</html>
