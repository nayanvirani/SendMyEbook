<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SendMyEbook — Sell &amp; deliver digital downloads on Shopify</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sell ebooks, PDFs, audio, video and other digital products on Shopify with automatic, secure delivery after purchase.">
    @vite(['resources/css/app.css'])
    <style>
        .bg-grid {
            background-image: radial-gradient(circle, #d1fae5 1px, transparent 1px);
            background-size: 24px 24px;
        }
        .hero-glow {
            background: radial-gradient(60% 50% at 50% 0%, rgba(16,185,129,0.12) 0%, rgba(16,185,129,0) 70%);
        }
        details > summary { list-style: none; cursor: pointer; }
        details > summary::-webkit-details-marker { display: none; }
        details[open] .faq-chevron { transform: rotate(180deg); }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased">

    {{-- Nav --}}
    <header class="sticky top-0 z-50 border-b border-gray-100 bg-white/80 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="/" class="flex items-center gap-2 text-lg font-semibold">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white shadow-sm">📩</span>
                SendMyEbook
            </a>
            <nav class="hidden items-center gap-8 text-sm font-medium text-gray-600 sm:flex">
                <a href="#how-it-works" class="hover:text-gray-900">How it works</a>
                <a href="#features" class="hover:text-gray-900">Features</a>
                <a href="#pricing" class="hover:text-gray-900">Pricing</a>
                <a href="#faq" class="hover:text-gray-900">FAQ</a>
            </nav>
            <a href="#pricing" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-700">
                Add to Shopify
            </a>
        </div>
    </header>

    {{-- Hero --}}
    <section class="hero-glow relative overflow-hidden">
        <div class="bg-grid absolute inset-0 -z-10 [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,black_40%,transparent_100%)]"></div>
        <div class="mx-auto max-w-6xl px-6 pb-20 pt-16 text-center sm:pt-24">
            <p class="mb-5 inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-700">
                <span>✦</span> Built for Shopify merchants
            </p>
            <h1 class="mx-auto max-w-3xl text-4xl font-bold leading-tight tracking-tight text-gray-900 sm:text-6xl">
                Sell digital products.<br class="hidden sm:block"> We handle delivery — automatically.
            </h1>
            <p class="mx-auto mt-5 max-w-2xl text-lg text-gray-600">
                Upload ebooks, PDFs, audio, video, templates or presets, map them to your Shopify products,
                and every paying customer gets a secure, time-limited download link by email — no manual work.
            </p>

            <div class="mt-8 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                <a href="#pricing" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-8 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                    Add to Shopify <span aria-hidden="true">→</span>
                </a>
                <a href="#how-it-works" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-8 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    See how it works
                </a>
            </div>
            <p class="mt-4 text-xs text-gray-500">Installs from the Shopify App Store · Free plan available · No credit card required</p>

            {{-- Preview card --}}
            <div class="mx-auto mt-16 max-w-3xl rounded-2xl border border-gray-200 bg-white p-2 shadow-xl shadow-emerald-900/5">
                <div class="rounded-xl bg-gray-50 p-6 sm:p-8">
                    <div class="flex items-center gap-2 border-b border-gray-200 pb-4">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-300"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-300"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-300"></span>
                        <span class="ml-3 text-xs text-gray-400">Order confirmed</span>
                    </div>
                    <div class="mt-4 rounded-lg border border-emerald-200 bg-white p-4 text-left">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">✓ Ready to download</p>
                        <p class="mt-1.5 font-semibold text-gray-900">The Complete Ebook Bundle</p>
                        <div class="mt-3 flex items-center justify-between rounded-md border border-gray-100 bg-gray-50 px-3 py-2">
                            <span class="text-sm text-gray-600">ebook-bundle.pdf · 48 MB</span>
                            <span class="rounded-md bg-emerald-600 px-3 py-1 text-xs font-semibold text-white">Download</span>
                        </div>
                        <p class="mt-3 text-xs text-gray-400">We've also emailed you this link.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section id="how-it-works" class="border-t border-gray-100 py-20">
        <div class="mx-auto max-w-6xl px-6">
            <h2 class="text-center text-3xl font-bold tracking-tight">Three steps to automatic delivery</h2>
            <p class="mx-auto mt-3 max-w-2xl text-center text-gray-600">No manual emailing files to customers, ever again.</p>

            <div class="mt-14 grid gap-10 sm:grid-cols-3">
                @foreach ([
                    ['1', 'Upload your files', 'Attach one or more files to any Shopify product — ebooks, PDFs, audio, video, templates, whatever you sell.'],
                    ['2', 'Set your rules', 'Choose a download limit, a link expiration, and whether access is revoked on refund — once, per product.'],
                    ['3', 'Customers get it instantly', 'The moment an order is paid, a secure download link goes out by email and shows up right on the Thank You page.'],
                ] as [$step, $title, $body])
                    <div class="relative text-center sm:text-left">
                        <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-emerald-600 text-base font-bold text-white sm:mx-0">
                            {{ $step }}
                        </div>
                        <h3 class="mt-4 font-semibold text-gray-900">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-gray-600">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="features" class="border-t border-gray-100 bg-gray-50 py-20">
        <div class="mx-auto max-w-6xl px-6">
            <h2 class="text-center text-3xl font-bold tracking-tight">Everything digital delivery needs</h2>
            <p class="mx-auto mt-3 max-w-2xl text-center text-gray-600">
                One app to attach files to products, protect them, and get them into customers' hands.
            </p>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['🔒', 'Secure downloads', 'Files stay in a private bucket. Customers only ever get short-lived, signed download links — never a public URL.'],
                    ['📧', 'Automatic email delivery', 'The moment an order is paid, a branded email goes out with everything the customer needs to download their files.'],
                    ['⏱️', 'Limits & expiration', 'Cap downloads per order and set links to expire after a set number of hours or days — or leave them unlimited.'],
                    ['↩️', 'Refund protection', 'Access is automatically revoked the moment an order is refunded or cancelled.'],
                    ['📊', 'Built-in analytics', 'See downloads by day, top products, and recent activity without leaving Shopify admin.'],
                    ['🛍️', 'Shopify-native', 'Lives inside your Shopify admin, matches Polaris design, and uses your existing products and variants.'],
                ] as [$icon, $title, $body])
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 transition-shadow hover:shadow-md">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-xl">{{ $icon }}</div>
                        <h3 class="mt-4 font-semibold text-gray-900">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-gray-600">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Pricing --}}
    <section id="pricing" class="border-t border-gray-100 py-20">
        <div class="mx-auto max-w-6xl px-6">
            <h2 class="text-center text-3xl font-bold tracking-tight">Simple, predictable pricing</h2>
            <p class="mx-auto mt-3 max-w-2xl text-center text-gray-600">
                Start free. Upgrade as your digital catalog and download volume grow.
            </p>

            <div class="mx-auto mt-12 grid max-w-3xl gap-8 sm:grid-cols-2">
                @foreach ($plans as $plan)
                    @php($featured = (float) $plan->price > 0)
                    <div class="relative rounded-2xl border bg-white p-8 {{ $featured ? 'border-emerald-600 shadow-xl shadow-emerald-900/5' : 'border-gray-200' }}">
                        @if ($featured)
                            <span class="absolute -top-3 left-8 rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold text-white shadow-sm">
                                Most popular
                            </span>
                        @endif
                        <h3 class="text-lg font-semibold">{{ $plan->name }}</h3>
                        <p class="mt-4 flex items-baseline gap-1">
                            <span class="text-4xl font-bold">${{ number_format((float) $plan->price, 0) }}</span>
                            <span class="text-gray-500">/month</span>
                        </p>
                        @if ($plan->trial_days)
                            <p class="mt-1 text-xs font-medium text-emerald-700">{{ $plan->trial_days }}-day free trial</p>
                        @endif
                        <ul class="mt-6 space-y-3 text-sm text-gray-600">
                            @foreach ($plan->marketing_bullets ?? [] as $bullet)
                                <li class="flex gap-2.5">
                                    <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-[10px] text-emerald-700">✔</span>
                                    {{ $bullet }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="#"
                           class="mt-8 block rounded-lg px-4 py-2.5 text-center text-sm font-semibold {{ $featured ? 'bg-emerald-600 text-white hover:bg-emerald-500' : 'bg-gray-100 text-gray-900 hover:bg-gray-200' }}">
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
            <div class="mt-10 space-y-3">
                @foreach ([
                    ['Where are my files stored?', 'In a private, S3-compatible object storage bucket. Files are never publicly accessible — every download goes through a signed, time-limited link generated at request time.'],
                    ['What happens if an order is refunded?', 'Download access for that order is automatically disabled, based on the rule you set on the digital product.'],
                    ['Can I limit how many times a file is downloaded?', 'Yes — set a maximum download count and/or a link expiration (in hours or days) per digital product.'],
                    ['What file types are supported?', 'PDFs, ZIPs, ebooks (EPUB/MOBI), audio, video, and image files.'],
                    ['Do I need to touch any code?', 'No — everything is configured from inside your Shopify admin: map files to products, set rules, and you\'re done.'],
                ] as $i => [$q, $a])
                    <details class="group rounded-xl border border-gray-200 bg-white px-5 py-4" {{ $i === 0 ? 'open' : '' }}>
                        <summary class="flex items-center justify-between gap-4 font-semibold text-gray-900">
                            {{ $q }}
                            <span class="faq-chevron shrink-0 text-gray-400 transition-transform">⌄</span>
                        </summary>
                        <p class="mt-3 text-sm text-gray-600">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="border-t border-gray-100 py-20">
        <div class="mx-auto max-w-4xl rounded-3xl bg-gray-900 px-8 py-14 text-center sm:px-16">
            <h2 class="text-3xl font-bold tracking-tight text-white">Start selling digital products today</h2>
            <p class="mx-auto mt-3 max-w-xl text-gray-300">Install SendMyEbook, map your first product, and deliveries run themselves from there.</p>
            <a href="#pricing" class="mt-8 inline-flex items-center gap-2 rounded-lg bg-emerald-500 px-8 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-400">
                Add to Shopify <span aria-hidden="true">→</span>
            </a>
        </div>
    </section>

    <footer class="border-t border-gray-100 py-10">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 text-sm text-gray-500 sm:flex-row">
            <a href="/" class="flex items-center gap-2 font-semibold text-gray-700">
                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-emerald-600 text-xs text-white">📩</span>
                SendMyEbook
            </a>
            <p>&copy; {{ date('Y') }} SendMyEbook. All rights reserved.</p>
            <div class="flex items-center gap-4">
                <a href="{{ route('legal.privacy') }}" class="hover:text-gray-900">Privacy</a>
                <span class="text-gray-300">·</span>
                <p>Not affiliated with Shopify Inc.</p>
            </div>
        </div>
    </footer>
</body>
</html>
