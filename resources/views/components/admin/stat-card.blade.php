@props(['label', 'value', 'icon' => '◆', 'tint' => '#eef2ff', 'accent' => '#4f46e5', 'hint' => null])

<div class="rounded-2xl border border-gray-200 bg-white p-5 transition-shadow hover:shadow-sm">
    <div class="flex items-start gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-base" style="background:{{ $tint }}; color:{{ $accent }};">
            {{ $icon }}
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ $label }}</p>
            <p class="mt-1 text-2xl font-bold tabular-nums text-gray-900">{{ $value }}</p>
            @if ($hint)
                <p class="mt-1 text-xs text-gray-500">{!! $hint !!}</p>
            @endif
        </div>
    </div>
</div>
