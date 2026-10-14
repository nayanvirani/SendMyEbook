@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <x-admin.page-header title="Dashboard" description="A snapshot of installs, plans and revenue across every shop." />

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <x-admin.stat-card label="Total shops" :value="$totalShops" icon="▣" tint="#eef2ff" accent="#4f46e5" />
        <x-admin.stat-card label="Active shops" :value="$activeShops" icon="✓" tint="#ecfdf5" accent="#059669" />
        <x-admin.stat-card label="Active subscriptions" :value="$activeSubscriptions" icon="↻" tint="#fdf4ff" accent="#a21caf" />
        <x-admin.stat-card label="Estimated MRR" :value="'$'.number_format($estimatedMrr, 2)" icon="$" tint="#eff6ff" accent="#2563eb" hint="Sum of current active subscriptions' plan price." />
        <x-admin.stat-card
            label="Total payments"
            :value="$partnerConfigured ? $totalPayments : '—'"
            icon="◎"
            tint="#fff7ed"
            accent="#c2410c"
            :hint="$partnerConfigured ? '<a href=\''.route('admin.revenue.index').'\' class=\'font-medium text-emerald-700 hover:underline\'>See revenue →</a>' : 'Partner API not configured'"
        />
    </div>

    <div class="mt-6">
        <x-admin.card title="Recently installed shops">
            @if ($recentShops->isEmpty())
                <x-admin.empty-state icon="▣" title="No shops installed yet" />
            @else
                <div class="-m-5 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-gray-100 text-left text-gray-400">
                            <tr>
                                <th class="px-5 py-3 font-medium">Shop</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                                <th class="px-5 py-3 text-right font-medium">Installed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentShops as $shop)
                                <tr class="border-b border-gray-50 last:border-0">
                                    <td class="px-5 py-3 font-medium text-gray-900">{{ $shop->shop_domain }}</td>
                                    <td class="px-5 py-3">
                                        <x-admin.badge :tone="$shop->is_active ? 'success' : 'neutral'">
                                            {{ $shop->is_active ? 'Active' : 'Inactive' }}
                                        </x-admin.badge>
                                    </td>
                                    <td class="px-5 py-3 text-right text-gray-500">{{ $shop->installed_at?->format('M j, Y') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin.card>
    </div>
@endsection
