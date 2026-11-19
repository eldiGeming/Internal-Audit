@props(['status'])

{{-- CAR status as an icon and plain text: a clock while open, a check once verified effective. --}}
@php
    $isClosed = $status === \App\Enums\CarStatus::Closed;
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1.5 text-xs font-semibold whitespace-nowrap', $isClosed ? 'text-emerald-800' : 'text-amber-800']) }}>
    <x-icon :name="$isClosed ? 'check-circle' : 'clock'" class="size-4" />
    {{ $status->label() }}
</span>
