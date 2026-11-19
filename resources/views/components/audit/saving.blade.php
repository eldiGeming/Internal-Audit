{{-- Autosave status: every field is saved when it loses focus. --}}
<p {{ $attributes->class('inline-flex items-center gap-1.5 text-xs font-medium text-slate-600') }} aria-live="polite">
    <span wire:loading.delay.short class="inline-flex items-center gap-1.5 text-brand-700">
        <x-icon name="arrows-clockwise" class="size-3.5 animate-spin" />
        Menyimpan...
    </span>
    <span wire:loading.delay.short.remove class="inline-flex items-center gap-1.5">
        <x-icon name="check-circle" class="size-3.5 text-emerald-600" />
        Tersimpan otomatis
    </span>
</p>
