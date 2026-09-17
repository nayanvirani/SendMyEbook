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
        <aside class="w-56 shrink-0 border-r border-gray-200 bg-white">
            <div class="flex items-center gap-2 px-5 py-5 text-lg font-semibold">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-600 text-white text-sm">📩</span>
                SendMyEbook
            </div>
            <nav class="mt-2 space-y-1 px-3 text-sm font-medium">
                @foreach ([
                    ['admin.dashboard', 'Dashboard'],
                    ['admin.plans.index', 'Plans & Pricing'],
                    ['admin.shops.index', 'Shops'],
                    ['admin.subscriptions.index', 'Subscriptions'],
                    ['admin.settings.edit', 'Email Settings'],
                ] as [$routeName, $label])
                    <a
                        href="{{ route($routeName) }}"
                        class="block rounded-md px-3 py-2 {{ request()->routeIs($routeName.'*') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-100' }}"
                    >{{ $label }}</a>
                @endforeach
            </nav>
            <form method="POST" action="{{ route('admin.logout') }}" class="mt-auto px-3 py-4">
                @csrf
                <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-sm font-medium text-gray-600 hover:bg-gray-100">
                    Log out
                </button>
            </form>
        </aside>

        <main class="flex-1 px-8 py-8">
            <div class="mx-auto max-w-5xl">
                @if (session('status'))
                    <div class="mb-6 rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-6 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                <h1 class="mb-6 text-2xl font-bold tracking-tight">@yield('title', 'Admin')</h1>

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
