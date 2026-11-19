<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.partials.head')
</head>
<body class="min-h-dvh">
    <div x-data="{ navOpen: false }" x-on:keydown.escape.window="navOpen = false" class="min-h-dvh lg:pl-64">
        {{-- Mobile top bar --}}
        <header class="sticky top-0 z-30 flex h-14 items-center justify-between gap-3 bg-brand-900 px-4 text-white lg:hidden">
            <a href="{{ route('home') }}" wire:navigate class="flex min-h-11 items-center gap-2.5">
                <span class="grid size-8 place-items-center rounded-lg bg-white text-brand-900">
                    <x-icon name="shield-check-fill" class="size-5" />
                </span>
                <span class="text-sm font-extrabold tracking-wide">AUDIT INTERNAL</span>
            </a>
            <button type="button" x-on:click="navOpen = true" class="btn btn-icon text-white hover:bg-brand-800" aria-label="Buka menu navigasi">
                <x-icon name="list" class="size-6" />
            </button>
        </header>

        {{-- Mobile drawer backdrop --}}
        <div x-cloak x-show="navOpen" x-transition.opacity x-on:click="navOpen = false"
             class="fixed inset-0 z-40 bg-brand-950/50 backdrop-blur-[2px] lg:hidden"></div>

        {{-- Sidebar --}}
        <aside x-bind:class="navOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-brand-900 text-white transition-transform duration-300 ease-out-soft lg:w-64 lg:translate-x-0">
            @include('layouts.partials.sidebar')
        </aside>

        <main class="mx-auto w-full max-w-[1400px] px-4 pt-5 pb-16 sm:px-6 lg:px-8 lg:pt-8">
            {{ $slot }}
        </main>
    </div>

    <x-toasts />
</body>
</html>
