@props(['icon', 'title'])

{{-- Left-aligned empty state: the icon sits beside the text instead of inside a tinted circle. --}}
<div {{ $attributes->class(['flex flex-col gap-4 px-6 py-10 sm:flex-row sm:items-start sm:gap-5 sm:px-8']) }}>
    <x-icon :name="$icon" class="size-8 shrink-0 text-brand-700" />
    <div class="min-w-0">
        <p class="text-base font-semibold text-slate-900">{{ $title }}</p>
        @if ($slot->isNotEmpty())
            <div class="mt-1 max-w-xl text-sm text-slate-600">{{ $slot }}</div>
        @endif
        @isset($actions)
            <div class="mt-4 flex flex-wrap gap-2">{{ $actions }}</div>
        @endisset
    </div>
</div>
