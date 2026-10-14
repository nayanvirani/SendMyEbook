@props(['title' => null, 'description' => null, 'padded' => true])

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
    @if ($title)
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
            <div>
                <h2 class="font-semibold text-gray-900">{{ $title }}</h2>
                @if ($description)
                    <p class="mt-0.5 text-xs text-gray-500">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div>{{ $actions }}</div>
            @endisset
        </div>
    @endif
    <div @class([$padded ? 'p-5' : ''])>
        {{ $slot }}
    </div>
</div>
