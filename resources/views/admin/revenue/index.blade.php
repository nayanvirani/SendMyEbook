@extends('admin.layout')

@section('title', 'Revenue')

@section('content')
    <x-admin.page-header title="Revenue" description="Real transaction records from Shopify's Partner API — not an estimate from current subscriptions." />

    @if (! $configured)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
            ⚠ Partner API isn't configured — set <code class="rounded bg-amber-100 px-1 py-0.5">PARTNER_API_TOKEN</code>
            and <code class="rounded bg-amber-100 px-1 py-0.5">PARTNER_ORG_ID</code> to see real payment history here.
        </div>
    @else
        @php
            $totalCount = count($transactions);
            $totalGross = array_sum(array_column($transactions, 'gross_amount'));
            $totalNet = array_sum(array_column($transactions, 'net_amount'));
            $currency = $transactions[0]['currency'] ?? 'USD';

            $money = function (float $amount, string $cur = null) use ($currency) {
                $cur = $cur ?? $currency;
                $sign = $amount < 0 ? '-' : '';
                $formatted = $sign.'$'.number_format(abs($amount), 2);

                return $cur !== 'USD' ? "{$formatted} {$cur}" : $formatted;
            };

            $monthLabel = fn (string $month) => \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->format('F Y');
        @endphp

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-admin.stat-card label="Total payments" :value="$totalCount" icon="◎" tint="#eef2ff" accent="#4f46e5" />
            <x-admin.stat-card label="Gross revenue" :value="$money($totalGross)" icon="$" tint="#ecfdf5" accent="#059669" />
            <x-admin.stat-card label="Net revenue" :value="$money($totalNet)" icon="$" tint="#ecfdf5" accent="#059669" hint="After Shopify's fee." />
            <x-admin.stat-card
                :label="number_format($sharePct * 100, 0).'% share (all-time)'"
                :value="$money($totalGross * $sharePct)"
                icon="◈"
                tint="#faf5ff"
                accent="#7c3aed"
                hint="Of gross revenue."
            />
        </div>

        <div class="mt-6">
            <x-admin.card title="Monthly breakdown">
                <x-slot:actions>
                    <form method="GET" action="{{ route('admin.revenue.index') }}">
                        <select
                            name="month" onchange="this.form.submit()"
                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                        >
                            <option value="">All time</option>
                            @foreach ($months as $m)
                                <option value="{{ $m['month'] }}" {{ $m['month'] === $selectedMonth ? 'selected' : '' }}>
                                    {{ $monthLabel($m['month']) }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </x-slot:actions>

                @if (empty($months))
                    <x-admin.empty-state icon="◎" title="No payments recorded yet" />
                @else
                    <div class="-m-5 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-gray-100 text-left text-gray-400">
                                <tr>
                                    <th class="px-5 py-3 font-medium">Month</th>
                                    <th class="px-5 py-3 font-medium">Payments</th>
                                    <th class="px-5 py-3 font-medium">Gross</th>
                                    <th class="px-5 py-3 font-medium">Net</th>
                                    <th class="px-5 py-3 font-medium">{{ number_format($sharePct * 100, 0) }}% share</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($months as $m)
                                    <tr class="border-b border-gray-50 last:border-0 {{ $m['month'] === $selectedMonth ? 'bg-emerald-50/40' : '' }}">
                                        <td class="px-5 py-3">
                                            <a href="{{ route('admin.revenue.index', ['month' => $m['month']]) }}" class="font-medium text-emerald-700 hover:underline">
                                                {{ $monthLabel($m['month']) }}
                                            </a>
                                        </td>
                                        <td class="px-5 py-3 text-gray-500">{{ $m['count'] }}</td>
                                        <td class="px-5 py-3 text-gray-900">{{ $money($m['gross'], $m['currency']) }}</td>
                                        <td class="px-5 py-3 text-gray-900">{{ $money($m['net'], $m['currency']) }}</td>
                                        <td class="px-5 py-3 text-violet-700">{{ $money($m['gross'] * $sharePct, $m['currency']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-admin.card>
        </div>

        @if ($selectedMonth)
            @php
                $detail = collect($transactions)
                    ->filter(fn ($t) => substr($t['created_at'], 0, 7) === $selectedMonth)
                    ->sortByDesc('created_at')
                    ->values();
            @endphp
            <div class="mt-6">
                <x-admin.card :title="$monthLabel($selectedMonth).' — '.$detail->count().' payment(s)'">
                    <div class="-m-5 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-gray-100 text-left text-gray-400">
                                <tr>
                                    <th class="px-5 py-3 font-medium">Date</th>
                                    <th class="px-5 py-3 font-medium">Store</th>
                                    <th class="px-5 py-3 font-medium">Shop domain</th>
                                    <th class="px-5 py-3 font-medium">Billing</th>
                                    <th class="px-5 py-3 font-medium">Gross</th>
                                    <th class="px-5 py-3 font-medium">Net</th>
                                    <th class="px-5 py-3 font-medium">{{ number_format($sharePct * 100, 0) }}% share</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($detail as $t)
                                    <tr class="border-b border-gray-50 last:border-0">
                                        <td class="whitespace-nowrap px-5 py-3 text-xs text-gray-400">{{ \Illuminate\Support\Carbon::parse($t['created_at'])->format('M j, Y g:i A') }}</td>
                                        <td class="px-5 py-3 text-gray-900">{{ $t['shop_name'] ?? '—' }}</td>
                                        <td class="px-5 py-3">
                                            @if ($t['shop_domain'])
                                                <a href="https://{{ $t['shop_domain'] }}/admin" target="_blank" rel="noreferrer" class="text-emerald-700 hover:underline">{{ $t['shop_domain'] }}</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-gray-500">
                                            {{ $t['billing_interval'] === 'ANNUAL' ? 'Yearly' : ($t['billing_interval'] === 'EVERY_30_DAYS' ? 'Monthly' : '—') }}
                                        </td>
                                        <td class="px-5 py-3 text-gray-900">{{ $money($t['gross_amount'], $t['currency']) }}</td>
                                        <td class="px-5 py-3 text-gray-900">{{ $money($t['net_amount'], $t['currency']) }}</td>
                                        <td class="px-5 py-3 text-violet-700">{{ $money($t['gross_amount'] * $sharePct, $t['currency']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-admin.card>
            </div>
        @endif

        <p class="mt-4 text-center text-xs text-gray-400">
            Sourced from Shopify's Partner API, filtered to this app's own shops, cached for up to 5 minutes.
        </p>
    @endif
@endsection
