@props(['value', 'empty' => false])

{{--
    Compliance figure with a short meter. The number is the value; the bar only shows magnitude,
    colored by threshold (85% and up good, 60% and up needs attention, below 60% poor).
--}}
@php
    $barClass = match (true) {
        $empty => 'bg-transparent',
        $value >= 85 => 'bg-emerald-600',
        $value >= 60 => 'bg-amber-500',
        default => 'bg-red-600',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-2 align-middle']) }}>
    <span class="w-9 text-right text-sm font-bold text-slate-900 tabular-nums">{{ $empty ? '-' : $value.'%' }}</span>
    <span class="h-1.5 w-12 overflow-hidden rounded-[2px] bg-slate-200" aria-hidden="true">
        <span class="block h-full w-(--fill) {{ $barClass }}" style="--fill: {{ $empty ? 0 : min(100, max(0, $value)) }}%"></span>
    </span>
</span>
