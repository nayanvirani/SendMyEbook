@extends('admin.layout')

@section('title', 'Subscriptions')

@section('content')
    <x-admin.page-header title="Subscriptions" description="Every subscription this app has ever learned about, via Shopify's app_subscriptions/update webhook." />

    <x-admin.card :padded="false">
        @if ($subscriptions->isEmpty())
            <x-admin.empty-state icon="↻" title="No subscriptions yet" />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-100 text-left text-gray-400">
                        <tr>
                            <th class="px-5 py-3 font-medium">Shop</th>
                            <th class="px-5 py-3 font-medium">Plan</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Current period ends</th>
                            <th class="px-5 py-3 font-medium">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subscriptions as $subscription)
                            <tr class="border-b border-gray-50 last:border-0">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $subscription->shop->shop_domain }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $subscription->plan->name }}</td>
                                <td class="px-5 py-3">
                                    <x-admin.badge :tone="$subscription->status === 'active' ? 'success' : 'neutral'">
                                        {{ ucfirst($subscription->status) }}
                                    </x-admin.badge>
                                </td>
                                <td class="px-5 py-3 text-gray-500">{{ $subscription->current_period_end?->format('M j, Y') ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $subscription->created_at->format('M j, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-admin.card>

    <div class="mt-4">{{ $subscriptions->links() }}</div>
@endsection
