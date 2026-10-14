@extends('admin.layout')

@section('title', $plan->exists ? 'Edit plan' : 'New plan')

@section('content')
    <form
        method="POST"
        action="{{ $plan->exists ? route('admin.plans.update', $plan) : route('admin.plans.store') }}"
        class="max-w-xl space-y-5 rounded-xl border border-gray-200 bg-white p-6"
    >
        @csrf
        @if ($plan->exists)
            @method('PUT')
        @endif

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input
                id="name" name="name" type="text" value="{{ old('name', $plan->name) }}" required
                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
            >
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="handle" class="block text-sm font-medium text-gray-700">Handle</label>
            <input
                id="handle" name="handle" type="text" value="{{ old('handle', $plan->handle) }}" required
                placeholder="pro"
                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
            >
            <p class="mt-1 text-xs text-gray-500">Used internally — letters, numbers, dashes and underscores only.</p>
            @error('handle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="price" class="block text-sm font-medium text-gray-700">Price (USD / month)</label>
            <input
                id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $plan->price) }}" required
                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
            >
            @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="max_digital_products" class="block text-sm font-medium text-gray-700">Max digital products</label>
                <input
                    id="max_digital_products" name="max_digital_products" type="number" min="1"
                    value="{{ old('max_digital_products', $plan->max_digital_products) }}"
                    placeholder="Leave blank = unlimited"
                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                >
            </div>
            <div>
                <label for="max_deliveries_per_month" class="block text-sm font-medium text-gray-700">Max deliveries / month</label>
                <input
                    id="max_deliveries_per_month" name="max_deliveries_per_month" type="number" min="1"
                    value="{{ old('max_deliveries_per_month', $plan->max_deliveries_per_month) }}"
                    placeholder="Leave blank = unlimited"
                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                >
                <p class="mt-1 text-xs text-gray-500">One delivery = one order's digital-download email sent, regardless of how many files or downloads follow.</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="max_file_size_mb" class="block text-sm font-medium text-gray-700">Max file size (MB)</label>
                <input
                    id="max_file_size_mb" name="max_file_size_mb" type="number" min="1"
                    value="{{ old('max_file_size_mb', $plan->max_file_size_mb) }}"
                    placeholder="Leave blank = use global default"
                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                >
                <p class="mt-1 text-xs text-gray-500">250 = 250MB, 10240 = 10GB.</p>
            </div>
            <div>
                <label for="max_downloads_per_product" class="block text-sm font-medium text-gray-700">Max downloads per product</label>
                <input
                    id="max_downloads_per_product" name="max_downloads_per_product" type="number" min="1"
                    value="{{ old('max_downloads_per_product', $plan->max_downloads_per_product) }}"
                    placeholder="Leave blank = unlimited"
                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                >
                <p class="mt-1 text-xs text-gray-500">The ceiling a store owner's own download-limit settings can't exceed, on this plan.</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="trial_days" class="block text-sm font-medium text-gray-700">Free trial (days)</label>
                <input
                    id="trial_days" name="trial_days" type="number" min="1"
                    value="{{ old('trial_days', $plan->trial_days) }}"
                    placeholder="Leave blank = no trial"
                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                >
                <p class="mt-1 text-xs text-gray-500">Display only — the actual trial is set in Shopify's Partner Dashboard Managed Pricing.</p>
            </div>
        </div>

        <div>
            <span class="block text-sm font-medium text-gray-700">Features</span>
            <p class="mt-1 text-xs text-gray-500">Turns the matching restriction on/off in the app — not just display text.</p>
            <div class="mt-2 space-y-2">
                @php $activeFeatures = old('features', is_array($plan->features) ? $plan->features : []); @endphp
                @foreach (\App\Models\Plan::KNOWN_FEATURES as $slug => $label)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input
                            type="checkbox" name="features[]" value="{{ $slug }}"
                            class="rounded border-gray-300"
                            {{ in_array($slug, $activeFeatures, true) ? 'checked' : '' }}
                        >
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <label for="marketing_bullets" class="block text-sm font-medium text-gray-700">Marketing bullets (one per line)</label>
            <textarea
                id="marketing_bullets" name="marketing_bullets" rows="6"
                placeholder="Shown on the merchant-facing Billing page, in this order."
                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
            >{{ old('marketing_bullets', is_array($plan->marketing_bullets) ? implode("\n", $plan->marketing_bullets) : '') }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Free text, shown exactly as written — doesn't need to match the limits/features above.</p>
        </div>

        <div>
            <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort order</label>
            <input
                id="sort_order" name="sort_order" type="number" min="0"
                value="{{ old('sort_order', $plan->sort_order ?? 0) }}" required
                class="mt-1 w-32 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
            >
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300" {{ old('is_active', $plan->exists ? $plan->is_active : true) ? 'checked' : '' }}>
            Active (visible on the pricing page and available to merchants)
        </label>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-md bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
                {{ $plan->exists ? 'Save changes' : 'Create plan' }}
            </button>
            <a href="{{ route('admin.plans.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
        </div>
    </form>
@endsection
