@props(['tone' => 'neutral'])

@php
$tones = [
    'success' => 'bg-emerald-50 text-emerald-700',
    'critical' => 'bg-red-50 text-red-700',
    'warning' => 'bg-amber-50 text-amber-700',
    'info' => 'bg-indigo-50 text-indigo-700',
    'neutral' => 'bg-gray-100 text-gray-500',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium '.($tones[$tone] ?? $tones['neutral'])]) }}>
    {{ $slot }}
</span>
