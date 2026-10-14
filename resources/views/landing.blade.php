<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SendMyEbook — Sell &amp; deliver digital downloads on Shopify</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sell ebooks, PDFs, audio, video and other digital products on Shopify with automatic, secure delivery after purchase.">
    @vite(['resources/css/app.css'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,680&display=swap" rel="stylesheet">
    <style>
        body { background: #F7F5F0; }
        .serif { font-family: "Fraunces", Georgia, serif; }
        details > summary { list-style: none; cursor: pointer; }
        details > summary::-webkit-details-marker { display: none; }
        details[open] .faq-chevron { transform: rotate(45deg); }
    </style>
</head>
<body class="text-[#16181A] antialiased" style="background:#F7F5F0;">

    {{-- Nav --}}
    <header class="border-b border-[#E4E1D8]">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-6 py-5 lg:px-16">
            <a href="/" class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0F6B4F] text-white shadow-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 4h16v16H4z" stroke="currentColor" stroke-width="1.6"/><path d="M8 9h8M8 13h8M8 17h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                </span>
                <span class="text-lg font-bold tracking-tight">SendMyEbook</span>
            </a>
            <nav class="hidden items-center gap-8 text-[15px] text-[#4B4A43] sm:flex">
                <a href="#how-it-works" class="hover:text-[#16181A]">How it works</a>
                <a href="#features" class="hover:text-[#16181A]">Features</a>
                <a href="#pricing" class="hover:text-[#16181A]">Pricing</a>
                <a href="#faq" class="hover:text-[#16181A]">FAQ</a>
            </nav>
            <a href="#pricing" class="rounded-lg bg-[#16181A] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#2A2C2E]">
                Add to Shopify
            </a>
        </div>
    </header>

    {{-- Hero --}}
    <section class="mx-auto flex max-w-6xl flex-wrap items-center gap-14 px-6 py-24 lg:px-16">
        <div class="min-w-0 flex-1 basis-[480px]">
            <p class="mb-7 inline-flex items-center gap-2 rounded-full bg-[#EAF3EE] px-3.5 py-1.5 text-sm font-semibold text-[#0F6B4F]">
                <span class="h-1.5 w-1.5 rounded-full bg-[#0F6B4F]"></span>
                Built for Shopify
            </p>
            <h1 class="serif text-5xl font-semibold leading-[1.05] tracking-tight text-[#16181A] sm:text-6xl">
                Every ebook, delivered the moment it's bought.
            </h1>
            <p class="mt-6 max-w-lg text-lg leading-relaxed text-[#55544B]">
                Upload once. SendMyEbook emails the download automatically after checkout and drops a link right on the Thank You page — no spreadsheets, no manual sending.
            </p>
            <div class="mt-9 flex flex-wrap items-center gap-5">
                <a href="#pricing" class="rounded-[9px] bg-[#0F6B4F] px-7 py-3.5 text-[15px] font-semibold text-white shadow-sm hover:bg-[#0B5A42]">
                    Add to Shopify — free
                </a>
                <a href="#pricing" class="border-b-[1.5px] border-[#16181A] pb-0.5 text-[15px] font-semibold text-[#16181A]">
                    See pricing
                </a>
            </div>
        </div>

        <div class="flex min-w-0 flex-1 basis-[420px] justify-center">
            <div class="w-full max-w-[440px] overflow-hidden rounded-2xl border border-[#E4E1D8] bg-white shadow-[0_24px_60px_-24px_rgba(22,24,26,0.18)]">
                <div class="flex items-center gap-2 border-b border-[#EFEDE5] px-5 py-4">
                    <span class="h-2.5 w-2.5 rounded-full bg-[#E4E1D8]"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-[#E4E1D8]"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-[#E4E1D8]"></span>
                    <span class="ml-2 text-xs text-[#9B998E]">Thank you page</span>
                </div>
                <div class="px-7 py-8">
                    <p class="text-[13px] text-[#9B998E]">Order #1032 confirmed</p>
                    <h3 class="mt-1 text-xl font-bold text-[#16181A]">Thanks, Jamie!</h3>
                    <div class="mt-5 flex items-center gap-3.5 rounded-xl border border-[#E4E1D8] p-4">
                        <div class="h-[52px] w-10 shrink-0 rounded bg-[#0F6B4F]"></div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-[#16181A]">The Garden Almanac</p>
                            <p class="text-xs text-[#9B998E]">PDF · 4.1MB</p>
                        </div>
                        <span class="shrink-0 rounded-md bg-[#16181A] px-3.5 py-2 text-xs font-semibold text-white">Download</span>
                    </div>
                    <p class="mt-4 text-xs text-[#9B998E]">A copy was also emailed to jamie@example.com</p>
                </div>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section id="how-it-works" class="border-t border-[#E4E1D8]">
        <div class="mx-auto max-w-6xl px-6 py-20 lg:px-16">
            <h2 class="serif text-3xl font-semibold tracking-tight">How it works</h2>
            <div class="mt-11 grid gap-10 sm:grid-cols-3">
                @foreach ([
                    ['01', 'Upload your files', 'Attach an ePub, PDF or MOBI to any product — license keys and watermarking optional.'],
                    ['02', 'Customer checks out', 'Nothing to configure per order — SendMyEbook listens for the order and takes it from there.'],
                    ['03', 'Instant delivery', 'A download link lands on the Thank You page and in their inbox, with expiry and download limits applied.'],
                ] as [$num, $title, $body])
                    <div>
                        <span class="serif text-4xl font-semibold text-[#D8D4C6]">{{ $num }}</span>
                        <h3 class="mt-3 text-lg font-bold text-[#16181A]">{{ $title }}</h3>
                        <p class="mt-2 text-[15px] leading-relaxed text-[#55544B]">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="features" class="border-t border-[#E4E1D8]">
        <div class="mx-auto max-w-6xl px-6 py-20 lg:px-16">
            <h2 class="serif max-w-lg text-3xl font-semibold tracking-tight">Everything a digital storefront needs, nothing it doesn't.</h2>

            <div class="mt-11 grid gap-px overflow-hidden rounded-2xl border border-[#E4E1D8] bg-[#E4E1D8] sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['M4 6h16v12H4z|M4 7l8 6 8-6', '', 'Automatic email delivery', 'Every order triggers a branded email with the download attached, no app visits required.'],
                    ['M12 3v12m0 0l-4-4m4 4l4-4|M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2', '', 'Thank You page download', 'Customers can grab their file immediately, even before the email arrives.'],
                    ['M9 3h10v14H9z|M5 21h6M8 17v4', '', 'Any format', 'ePub, PDF and MOBI are all supported out of the box.'],
                    ['M12 7v5l3.5 2', 'circle', 'Download limits & expiry', 'Cap how many times a link can be used and set it to expire after a set number of days.'],
                    ['M12 3l2.6 5.6 6.1.8-4.5 4.2 1.2 6-5.4-3-5.4 3 1.2-6-4.5-4.2 6.1-.8z', '', 'PDF watermarking', "Stamp each download with the buyer's order details to discourage sharing. Pro."],
                    ['M4 20V10l8-6 8 6v10|M9 20v-7h6v7', '', 'Custom branding', 'Your logo and brand color on the download page and delivery emails. Pro.'],
                ] as [$paths, $kind, $title, $body])
                    <div class="bg-white p-8">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            @if ($kind === 'circle')
                                <circle cx="12" cy="12" r="8" stroke="#0F6B4F" stroke-width="1.6"/>
                            @endif
                            @foreach (explode('|', $paths) as $path)
                                <path d="{{ $path }}" stroke="#0F6B4F" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            @endforeach
                        </svg>
                        <h3 class="mt-4 font-bold text-[#16181A]">{{ $title }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-[#55544B]">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Pricing --}}
    <section id="pricing" class="border-y border-[#E4E1D8] bg-white">
        <div class="mx-auto max-w-4xl px-6 py-20 lg:px-16">
            <h2 class="serif text-center text-3xl font-semibold tracking-tight">Simple pricing, no surprises</h2>

            <div class="mx-auto mt-11 grid gap-6 sm:grid-cols-2">
                @foreach ($plans as $plan)
                    @php($featured = (float) $plan->price > 0)
                    <div class="relative rounded-2xl p-9 {{ $featured ? 'border-[1.5px] border-[#0F6B4F] bg-[#FBFAF6]' : 'border border-[#E4E1D8]' }}">
                        @if ($featured && $plan->trial_days)
                            <span class="absolute -top-3.5 right-8 rounded-full bg-[#0F6B4F] px-3 py-1.5 text-xs font-semibold text-white">
                                {{ $plan->trial_days }}-day free trial
                            </span>
                        @endif
                        <h3 class="text-lg font-bold text-[#16181A]">{{ $plan->name }}</h3>
                        <p class="serif mt-3 text-4xl font-semibold">
                            {{ (float) $plan->price === 0.0 ? '$0' : '$'.number_format((float) $plan->price, 0) }}
                            @if ($featured)
                                <span class="serif text-base font-medium text-[#9B998E]">/month</span>
                            @endif
                        </p>
                        <ul class="mt-7 space-y-3">
                            @foreach ($plan->marketing_bullets ?? [] as $bullet)
                                <li class="flex gap-2.5 text-sm text-[#3A3A34]">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" class="mt-0.5 shrink-0"><path d="M5 13l4 4 10-10" stroke="#0F6B4F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    {{ $bullet }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="#"
                           class="mt-8 block rounded-lg py-3 text-center text-sm font-semibold {{ $featured ? 'bg-[#0F6B4F] text-white hover:bg-[#0B5A42]' : 'border border-[#16181A] text-[#16181A] hover:bg-[#F7F5F0]' }}">
                            {{ $featured ? 'Start free trial' : 'Choose '.$plan->name }}
                        </a>
                    </div>
                @endforeach
            </div>
            <p class="mt-8 text-center text-sm text-[#9B998E]">
                All plans billed monthly through Shopify. Cancel anytime from your Shopify admin.
            </p>
        </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="mx-auto max-w-[760px] px-6 py-20 lg:px-16">
        <h2 class="serif text-3xl font-semibold tracking-tight">Questions</h2>
        <div class="mt-8">
            @foreach ([
                ['Where are my files stored?', "In a private, S3-compatible object storage bucket. Files are never publicly accessible — every download goes through a signed, time-limited link generated at request time."],
                ['What happens if an order is refunded?', 'Download access for that order is automatically disabled, based on the rule you set on the digital product.'],
                ['Can I limit how many times a file is downloaded?', 'Yes — set a maximum download count and/or a link expiration (in hours or days) per digital product.'],
                ['What file types are supported?', 'ePub, PDF and MOBI, with a per-plan file size limit.'],
                ['Do I need to touch any code?', "No — everything is configured from inside your Shopify admin: map files to products, set rules, and you're done."],
            ] as $i => [$q, $a])
                <details class="border-t border-[#E4E1D8] py-[18px] {{ $loop->last ? 'border-b' : '' }}" {{ $i === 0 ? 'open' : '' }}>
                    <summary class="flex items-center justify-between gap-4 text-base font-semibold text-[#16181A]">
                        {{ $q }}
                        <span class="faq-chevron shrink-0 text-lg leading-none text-[#9B998E] transition-transform">+</span>
                    </summary>
                    <p class="mt-3 text-sm leading-relaxed text-[#55544B]">{{ $a }}</p>
                </details>
            @endforeach
        </div>
    </section>

    <footer class="bg-[#16181A] px-6 py-14 text-[#F7F5F0] lg:px-16">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-6">
            <a href="/" class="flex items-center gap-2.5">
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-[#0F6B4F]">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 4h16v16H4z" stroke="white" stroke-width="1.6"/><path d="M8 9h8M8 13h8M8 17h5" stroke="white" stroke-width="1.6" stroke-linecap="round"/></svg>
                </span>
                <span class="text-[15px] font-semibold">SendMyEbook</span>
            </a>
            <p class="text-[13px] text-[#9B998E]">&copy; {{ date('Y') }} SendMyEbook. Not affiliated with Shopify Inc.</p>
            <a href="{{ route('legal.privacy') }}" class="text-[13px] text-[#9B998E] hover:text-white">Privacy</a>
        </div>
    </footer>
</body>
</html>
