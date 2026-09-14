@extends('admin.layout')

@section('title', 'Shops')

@section('content')
    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200 text-left text-gray-500">
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
                @forelse ($shops as $shop)
                    <tr class="border-b border-gray-100 last:border-0">
                        <td class="px-5 py-3 font-medium">{{ $shop->shop_domain }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $shop->activeSubscription?->plan?->name ?? '—' }}</td>
                        <td class="px-5 py-3">{{ $shop->digital_products_count }}</td>
                        <td class="px-5 py-3">{{ $shop->orders_count }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $shop->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $shop->is_active ? 'Active' : 'Inactive' }}
                            </span>
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
                @empty
                    <tr>
                        <td class="px-5 py-6 text-center text-gray-500" colspan="7">No shops installed yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $shops->links() }}</div>
@endsection
