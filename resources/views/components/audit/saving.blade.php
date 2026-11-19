{{--
    Autosave status: every field is saved when it loses focus. While something typed is only held in
    the browser draft (see resources/js/drafts.js), say so instead of claiming it is saved.
--}}
<p {{ $attributes->class('inline-flex items-center gap-1.5 text-xs font-medium text-slate-600') }}
   x-data="{ pending: false }" x-on:audit-draft.window="pending = $event.detail.pending" aria-live="polite">
    <span wire:loading.delay.short class="inline-flex items-center gap-1.5 text-brand-700">
        <x-icon name="arrows-clockwise" class="size-3.5 animate-spin" />
        Menyimpan...
    </span>
    <span wire:loading.delay.short.remove class="inline-flex items-center gap-1.5">
        <span x-show="pending" x-cloak class="inline-flex items-center gap-1.5 text-amber-800">
            <x-icon name="floppy-disk" class="size-3.5" />
            Draft tersimpan di perangkat
        </span>
        <span x-show="! pending" class="inline-flex items-center gap-1.5">
            <x-icon name="check-circle" class="size-3.5 text-emerald-600" />
            Tersimpan otomatis
        </span>
    </span>
</p>
