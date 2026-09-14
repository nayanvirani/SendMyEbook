<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SendMyEbook — Sell &amp; deliver digital downloads on Shopify</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sell ebooks, PDFs, audio, video and other digital products on Shopify with automatic, secure delivery after purchase.">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-gray-900 antialiased">

    {{-- Nav --}}
    <header class="border-b border-gray-100">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
            <a href="/" class="flex items-center gap-2 text-lg font-semibold">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white">📩</span>
                SendMyEbook
            </a>
            <nav class="hidden items-center gap-8 text-sm font-medium text-gray-600 sm:flex">
                <a href="#features" class="hover:text-gray-900">Features</a>
                <a href="#pricing" class="hover:text-gray-900">Pricing</a>
                <a href="#faq" class="hover:text-gray-900">FAQ</a>
            </nav>
            <a href="#" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                Add to Shopify
            </a>
        </div>
    </header>

    {{-- Hero --}}
    <section class="mx-auto max-w-6xl px-6 pt-16 pb-20 text-center sm:pt-24">
        <p class="mb-4 inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-700">
            Built for Shopify merchants
        </p>
        <h1 class="mx-auto max-w-3xl text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
            Sell digital products. We handle delivery — automatically.
        </h1>
        <p class="mx-auto mt-5 max-w-2xl text-lg text-gray-600">
            Upload ebooks, PDFs, audio, video, templates or presets, map them to your Shopify products,
            and every paying customer gets a secure, time-limited download link by email — no manual work.
        </p>

        <a href="#" class="inline-block rounded-md bg-emerald-600 px-8 py-3 text-sm font-semibold text-white hover:bg-emerald-500">
            Add to Shopify
        </a>
        <p class="mt-3 text-xs text-gray-500">Installs from the Shopify App Store — no credit card required.</p>
    </section>

    {{-- Features --}}
    <section id="features" class="border-t border-gray-100 bg-gray-50 py-20">
        <div class="mx-auto max-w-6xl px-6">
            <h2 class="text-center text-3xl font-bold tracking-tight">Everything digital delivery needs</h2>
            <p class="mx-auto mt-3 max-w-2xl text-center text-gray-600">
                One app to attach files to products, protect them, and get them into customers' hands.
            </p>

            <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['🔒', 'Secure downloads', 'Files stay in a private bucket. Customers only ever get short-lived, signed download links — never a public URL.'],
                    ['📧', 'Automatic email delivery', 'The moment an order is paid, a branded email goes out with everything the customer needs to download their files.'],
                    ['⏱️', 'Limits & expiration', 'Cap downloads per order and set links to expire after a set number of hours or days — or leave them unlimited.'],
                    ['↩️', 'Refund protection', 'Access is automatically revoked the moment an order is refunded or cancelled.'],
                    ['📊', 'Built-in analytics', 'See downloads by day, top products, and recent activity without leaving Shopify admin.'],
                    ['🛍️', 'Shopify-native', 'Lives inside your Shopify admin, matches Polaris design, and uses your existing products and variants.'],
                ] as [$icon, $title, $body])
                    <div class="rounded-xl border border-gray-200 bg-white p-6">
                        <div class="text-2xl">{{ $icon }}</div>
                        <h3 class="mt-4 font-semibold">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-gray-600">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Pricing --}}
    <section id="pricing" class="py-20">
        <div class="mx-auto max-w-6xl px-6">
            <h2 class="text-center text-3xl font-bold tracking-tight">Simple, predictable pricing</h2>
            <p class="mx-auto mt-3 max-w-2xl text-center text-gray-600">
                Start free. Upgrade as your digital catalog and download volume grow.
            </p>

            <div class="mt-12 grid gap-8 sm:grid-cols-3">
                @foreach ($plans as $plan)
                    @php($featured = $plan->handle === 'growth')
                    <div class="relative rounded-2xl border p-8 {{ $featured ? 'border-emerald-600 shadow-lg' : 'border-gray-200' }}">
                        @if ($featured)
                            <span class="absolute -top-3 left-8 rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold text-white">
                                Most popular
                            </span>
                        @endif
                        <h3 class="text-lg font-semibold">{{ $plan->name }}</h3>
                        <p class="mt-4 flex items-baseline gap-1">
                            <span class="text-4xl font-bold">${{ number_format((float) $plan->price, 0) }}</span>
                            <span class="text-gray-500">/month</span>
                        </p>
                        <ul class="mt-6 space-y-3 text-sm text-gray-600">
                            <li class="flex gap-2">
                                <span>✔</span>
                                {{ $plan->max_digital_products ? "{$plan->max_digital_products} digital products" : 'Unlimited digital products' }}
                            </li>
                            <li class="flex gap-2">
                                <span>✔</span>
                                {{ $plan->max_downloads_per_month ? number_format($plan->max_downloads_per_month).' downloads / month' : 'Unlimited downloads' }}
                            </li>
                            <li class="flex gap-2"><span>✔</span> Automatic email delivery</li>
                            <li class="flex gap-2"><span>✔</span> Download limits &amp; expiration</li>
                            <li class="flex gap-2"><span>✔</span> Basic analytics</li>
                            @if (in_array('priority_support', $plan->features ?? []))
                                <li class="flex gap-2"><span>✔</span> Priority support</li>
                            @endif
                        </ul>
                        <a href="#"
                           class="mt-8 block rounded-md px-4 py-2.5 text-center text-sm font-semibold {{ $featured ? 'bg-emerald-600 text-white hover:bg-emerald-500' : 'bg-gray-100 text-gray-900 hover:bg-gray-200' }}">
                            Get started
                        </a>
                    </div>
                @endforeach
            </div>
            <p class="mt-8 text-center text-sm text-gray-500">
                All plans billed monthly through Shopify. Cancel anytime from your Shopify admin.
            </p>
        </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="border-t border-gray-100 bg-gray-50 py-20">
        <div class="mx-auto max-w-3xl px-6">
            <h2 class="text-center text-3xl font-bold tracking-tight">Frequently asked questions</h2>
            <dl class="mt-10 space-y-8">
                @foreach ([
                    ['Where are my files stored?', 'In a private, S3-compatible object storage bucket. Files are never publicly accessible — every download goes through a signed, time-limited link generated at request time.'],
                    ['What happens if an order is refunded?', 'Download access for that order is automatically disabled, based on the rule you set on the digital product.'],
                    ['Can I limit how many times a file is downloaded?', 'Yes — set a maximum download count and/or a link expiration (in hours or days) per digital product.'],
                    ['What file types are supported?', 'PDFs, ZIPs, ebooks (EPUB/MOBI), audio, video, and image files.'],
                ] as [$q, $a])
                    <div>
                        <dt class="font-semibold">{{ $q }}</dt>
                        <dd class="mt-2 text-sm text-gray-600">{{ $a }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <footer class="border-t border-gray-100 py-10">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 text-sm text-gray-500 sm:flex-row">
            <p>&copy; {{ date('Y') }} SendMyEbook. All rights reserved.</p>
            <p>Not affiliated with Shopify Inc.</p>
        </div>
    </footer>
</body>
</html>
