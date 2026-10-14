@props(['icon' => '○', 'title', 'description' => null])

<div class="flex flex-col items-center justify-center px-6 py-14 text-center">
    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-xl text-gray-300">
        {{ $icon }}
    </div>
    <p class="mt-3 text-sm font-medium text-gray-700">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-gray-500">{{ $description }}</p>
    @endif
</div>
