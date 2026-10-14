<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Admin') · SendMyEbook</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
    <div class="flex min-h-screen">
        <aside class="flex w-60 shrink-0 flex-col border-r border-gray-200 bg-white">
            <div class="flex items-center gap-2.5 px-5 py-5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-sm text-white shadow-sm">📩</span>
                <div>
                    <p class="text-sm font-semibold leading-tight">SendMyEbook</p>
                    <p class="text-xs leading-tight text-gray-400">Operator console</p>
                </div>
            </div>

            <nav class="mt-2 flex-1 space-y-0.5 px-3 text-sm font-medium">
                @foreach ([
                    ['admin.dashboard', 'Dashboard', '◧'],
                    ['admin.revenue.index', 'Revenue', '◎'],
                    ['admin.plans.index', 'Plans & Pricing', '◆'],
                    ['admin.shops.index', 'Shops', '▣'],
                    ['admin.subscriptions.index', 'Subscriptions', '↻'],
                    ['admin.settings.edit', 'Email Settings', '✉'],
                ] as [$routeName, $label, $icon])
                    @php $isCurrent = request()->routeIs($routeName.'*'); @endphp
                    <a
                        href="{{ route($routeName) }}"
                        class="flex items-center gap-2.5 rounded-lg px-3 py-2 transition-colors {{ $isCurrent ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                    >
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center text-sm {{ $isCurrent ? 'text-emerald-600' : 'text-gray-400' }}">{{ $icon }}</span>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <form method="POST" action="{{ route('admin.logout') }}" class="border-t border-gray-100 px-3 py-4">
                @csrf
                <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm font-medium text-gray-500 hover:bg-gray-50 hover:text-gray-900">
                    <span class="flex h-5 w-5 items-center justify-center text-sm text-gray-400">⏻</span>
                    Log out
                </button>
            </form>
        </aside>

        <main class="flex-1 px-8 py-8">
            <div class="mx-auto max-w-6xl">
                @if (session('status'))
                    <div class="mb-6 flex items-start gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        <span class="mt-0.5">✓</span>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-6 flex items-start gap-2 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
                        <span class="mt-0.5">⚠</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
