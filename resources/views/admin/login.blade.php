<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin login · SendMyEbook</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-50 text-gray-900 antialiased">
    <div class="w-full max-w-sm rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
        <div class="mb-6 flex items-center gap-2 text-lg font-semibold">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white">📩</span>
            SendMyEbook admin
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.attempt') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input
                    id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                >
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input
                    id="password" name="password" type="password" required
                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                >
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="rounded border-gray-300">
                Remember me
            </label>
            <button type="submit" class="w-full rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
                Log in
            </button>
        </form>
    </div>
</body>
</html>
