@props(['severity', 'large' => false])

{{--
    Severity marker: three ascending bars filled by level (Major 3, Minor 2, Observasi 1) plus the label.
    The bar count carries the meaning, so it still reads without color.
--}}
<span {{ $attributes->class(['inline-flex items-center gap-1.5 font-bold whitespace-nowrap', $large ? 'text-sm' : 'text-xs', $severity->toneClass()]) }}>
    <span class="inline-flex items-end gap-px" aria-hidden="true">
        @foreach (['h-1.5', 'h-[9px]', 'h-3'] as $index => $height)
            <span @class(['w-[3px] rounded-[1px]', $height, $index < $severity->level() ? 'bg-current' : 'bg-slate-300'])></span>
        @endforeach
    </span>
    {{ $severity->label() }}
</span>
