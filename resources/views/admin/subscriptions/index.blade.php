@extends('admin.layout')

@section('title', 'Subscriptions')

@section('content')
    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200 text-left text-gray-500">
                <tr>
                    <th class="px-5 py-3 font-medium">Shop</th>
                    <th class="px-5 py-3 font-medium">Plan</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Current period ends</th>
                    <th class="px-5 py-3 font-medium">Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subscriptions as $subscription)
                    <tr class="border-b border-gray-100 last:border-0">
                        <td class="px-5 py-3 font-medium">{{ $subscription->shop->shop_domain }}</td>
                        <td class="px-5 py-3">{{ $subscription->plan->name }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $subscription->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ ucfirst($subscription->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-500">{{ $subscription->current_period_end?->format('M j, Y') ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $subscription->created_at->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-5 py-6 text-center text-gray-500" colspan="5">No subscriptions yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $subscriptions->links() }}</div>
@endsection
