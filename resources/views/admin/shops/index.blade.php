@extends('admin.layout')

@section('title', 'Shops')

@section('content')
    <x-admin.page-header title="Shops" description="Every store that has installed SendMyEbook." />

    <x-admin.card :padded="false">
        @if ($shops->isEmpty())
            <x-admin.empty-state icon="▣" title="No shops installed yet" />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-100 text-left text-gray-400">
                        <tr>
                            <th class="px-5 py-3 font-medium">Shop</th>
                            <th class="px-5 py-3 font-medium">Plan</th>
                            <th class="px-5 py-3 font-medium">Digital products</th>
                            <th class="px-5 py-3 font-medium">Orders</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Installed</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($shops as $shop)
                            <tr class="border-b border-gray-50 last:border-0">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $shop->shop_domain }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $shop->activeSubscription?->plan?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $shop->digital_products_count }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $shop->orders_count }}</td>
                                <td class="px-5 py-3">
                                    <x-admin.badge :tone="$shop->is_active ? 'success' : 'neutral'">
                                        {{ $shop->is_active ? 'Active' : 'Inactive' }}
                                    </x-admin.badge>
                                </td>
                                <td class="px-5 py-3 text-gray-500">{{ $shop->installed_at?->format('M j, Y') ?? '—' }}</td>
                                <td class="px-5 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.shops.toggle-active', $shop) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="font-medium text-emerald-700 hover:underline">
                                            {{ $shop->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-admin.card>

    <div class="mt-4">{{ $shops->links() }}</div>
@endsection
