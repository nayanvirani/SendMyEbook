@extends('admin.layout')

@section('title', 'Plans & Pricing')

@section('content')
    <x-admin.page-header title="Plans & Pricing" description="Every editable aspect of a plan — limits, feature gates, trial length and the exact copy merchants see.">
        <x-slot:actions>
            <a href="{{ route('admin.plans.index', $showRetired ? [] : ['retired' => 1]) }}" class="text-sm font-medium text-gray-500 hover:underline">
                {{ $showRetired ? 'Hide retired plans' : 'Show retired plans' }}
            </a>
            <a href="{{ route('admin.plans.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                New plan
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.card :padded="false">
        @if ($plans->isEmpty())
            <x-admin.empty-state icon="◆" title="No plans yet" description="Create one to start offering it to merchants." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-100 text-left text-gray-400">
                        <tr>
                            <th class="px-5 py-3 font-medium">Name</th>
                            <th class="px-5 py-3 font-medium">Handle</th>
                            <th class="px-5 py-3 font-medium">Price</th>
                            <th class="px-5 py-3 font-medium">Products</th>
                            <th class="px-5 py-3 font-medium">Deliveries / mo</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($plans as $plan)
                            <tr class="border-b border-gray-50 last:border-0">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $plan->name }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $plan->handle }}</td>
                                <td class="px-5 py-3 text-gray-900">${{ number_format((float) $plan->price, 2) }}/mo</td>
                                <td class="px-5 py-3 text-gray-500">{{ $plan->max_digital_products ?? 'Unlimited' }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $plan->max_deliveries_per_month ?? 'Unlimited' }}</td>
                                <td class="px-5 py-3">
                                    <x-admin.badge :tone="$plan->is_active ? 'success' : 'neutral'">
                                        {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                    </x-admin.badge>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('admin.plans.edit', $plan) }}" class="font-medium text-emerald-700 hover:underline">Edit</a>
                                    @php $hasHistory = $plan->subscriptions_count > 0; @endphp
                                    <form
                                        method="POST"
                                        action="{{ route('admin.plans.destroy', $plan) }}"
                                        class="inline"
                                        onsubmit="return confirm('{{ $hasHistory ? 'This plan has subscription history and can\'t be deleted — deactivate and hide it instead?' : 'Delete this plan?' }}');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-3 font-medium text-red-600 hover:underline">
                                            {{ $hasHistory ? 'Deactivate' : 'Delete' }}
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
@endsection
