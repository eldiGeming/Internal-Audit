@props(['audit', 'current'])

@php
    $previous = \App\Support\AuditSteps::neighbour($current, -1);
    $next = \App\Support\AuditSteps::neighbour($current, 1);
@endphp

<div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        @if ($previous)
            <a href="{{ route($previous['route'], $audit) }}" wire:navigate class="btn btn-secondary group w-full sm:w-auto">
                <x-icon name="arrow-left" class="size-4 transition-transform duration-200 group-hover:-translate-x-0.5" />
                {{ $previous['number'] }}. {{ $previous['label'] }}
            </a>
        @endif
    </div>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        {{ $slot }}
        @if ($next)
            <a href="{{ route($next['route'], $audit) }}" wire:navigate class="btn btn-primary group w-full sm:w-auto">
                Lanjut ke {{ $next['number'] }}. {{ $next['label'] }}
                <x-icon name="arrow-right" class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" />
            </a>
        @endif
    </div>
</div>
