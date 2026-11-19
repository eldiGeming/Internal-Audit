@props(['value', 'empty' => false])

@php
    $classes = match (true) {
        $empty => 'bg-slate-100 text-slate-600 ring-slate-200',
        $value >= 85 => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
        $value >= 60 => 'bg-amber-50 text-amber-800 ring-amber-200',
        default => 'bg-red-50 text-red-800 ring-red-200',
    };
@endphp

<span {{ $attributes->class(['badge tabular-nums', $classes]) }}>{{ $empty ? '-' : $value.'%' }}</span>
