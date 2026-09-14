@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Total shops', $totalShops],
            ['Active shops', $activeShops],
            ['Active subscriptions', $activeSubscriptions],
            ['Estimated MRR', '$'.number_format($estimatedMrr, 2)],
        ] as [$label, $value])
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <p class="mt-1 text-2xl font-bold">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-8 rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="font-semibold">Recently installed shops</h2>
        </div>
        <table class="w-full text-sm">
            <tbody>
                @forelse ($recentShops as $shop)
                    <tr class="border-b border-gray-100 last:border-0">
                        <td class="px-5 py-3">{{ $shop->shop_domain }}</td>
                        <td class="px-5 py-3 text-gray-500">
                            {{ $shop->is_active ? 'Active' : 'Inactive' }}
                        </td>
                        <td class="px-5 py-3 text-right text-gray-500">
                            {{ $shop->installed_at?->format('M j, Y') ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-5 py-6 text-center text-gray-500" colspan="3">No shops installed yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
