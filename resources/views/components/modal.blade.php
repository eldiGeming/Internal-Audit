@props(['show', 'title', 'icon' => null, 'maxWidth' => 'max-w-lg'])

{{--
    Modal bound to a boolean Livewire property, e.g. <x-modal show="showDepartmentModal" title="...">.
--}}
<div
    x-cloak
    x-show="$wire.{{ $show }}"
    x-transition.opacity.duration.150ms
    x-on:keydown.escape.window="$wire.{{ $show }} = false"
    class="fixed inset-0 z-[60] flex items-end justify-center bg-brand-950/50 p-0 backdrop-blur-[2px] sm:items-center sm:p-4"
>
    <div
        x-show="$wire.{{ $show }}"
        x-transition:enter="transition duration-200 ease-out-soft"
        x-transition:enter-start="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
        x-on:click.outside="$wire.{{ $show }} = false"
        x-trap.noscroll="$wire.{{ $show }}"
        class="card flex max-h-[92dvh] w-full {{ $maxWidth }} flex-col rounded-b-none sm:rounded-b-xl"
        role="dialog" aria-modal="true"
    >
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div class="flex items-center gap-2.5">
                @if ($icon)
                    <span class="grid size-9 place-items-center rounded-lg bg-brand-50 text-brand-700"><x-icon :name="$icon" class="size-5" /></span>
                @endif
                <h3 class="text-base font-bold text-brand-900">{{ $title }}</h3>
            </div>
            <button type="button" x-on:click="$wire.{{ $show }} = false" class="btn btn-icon btn-ghost" aria-label="Tutup">
                <x-icon name="x" class="size-5" />
            </button>
        </div>
        <div class="overflow-y-auto px-5 py-4">
            {{ $slot }}
        </div>
        @isset($footer)
            <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
