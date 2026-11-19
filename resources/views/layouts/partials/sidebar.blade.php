@php
    $user = auth()->user();

    $links = $user->isAdmin()
        ? [
            ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'gauge', 'label' => 'Dashboard'],
            ['route' => 'audits.index', 'match' => 'audits.*', 'icon' => 'clipboard-text', 'label' => 'Semua Audit'],
            ['route' => 'admin.auditors', 'match' => 'admin.auditors', 'icon' => 'users', 'label' => 'Akun Auditor'],
            ['route' => 'admin.cars', 'match' => 'admin.cars', 'icon' => 'files', 'label' => 'Dokumen CAR per PT'],
        ]
        : [
            ['route' => 'audits.index', 'match' => 'audits.*', 'icon' => 'clipboard-text', 'label' => 'Daftar Audit'],
        ];
@endphp

<div class="flex h-16 shrink-0 items-center justify-between gap-3 border-b border-brand-800 px-5">
    <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-3">
        <span class="grid size-9 place-items-center rounded-lg bg-white text-brand-900">
            <x-icon name="shield-check-fill" class="size-5" />
        </span>
        <span class="leading-tight">
            <span class="block text-[15px] font-extrabold tracking-wide">AUDIT INTERNAL</span>
            <span class="block text-xs text-brand-200">Sistem Manajemen Audit</span>
        </span>
    </a>
    <button type="button" x-on:click="navOpen = false" class="btn btn-icon text-white hover:bg-brand-800 lg:hidden" aria-label="Tutup menu navigasi">
        <x-icon name="x" class="size-5" />
    </button>
</div>

<nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5" aria-label="Navigasi utama">
    <p class="px-3 pb-2 text-xs font-semibold text-brand-300">{{ $user->isAdmin() ? 'Administrator' : 'Menu' }}</p>
    @foreach ($links as $link)
        @php $active = request()->routeIs($link['match']); @endphp
        <a href="{{ route($link['route']) }}" wire:navigate
           @class([
               'group flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-semibold transition',
               'bg-white text-brand-900 shadow-sm' => $active,
               'text-brand-100 hover:bg-brand-800 hover:text-white' => ! $active,
           ])
           @if ($active) aria-current="page" @endif>
            <x-icon :name="$link['icon']" @class(['size-5 transition-transform duration-200 group-hover:scale-110', 'text-brand-700' => $active]) />
            <span>{{ $link['label'] }}</span>
        </a>
    @endforeach
</nav>

<div class="shrink-0 border-t border-brand-800 p-4">
    <div class="flex items-center gap-3">
        <span class="grid size-10 place-items-center rounded-lg bg-brand-700 text-sm font-bold">{{ $user->initials() }}</span>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold break-words">{{ $user->name }}</p>
            <p class="text-xs break-words text-brand-200">{{ $user->role->label() }} · {{ $user->username }}</p>
        </div>
    </div>
    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button type="submit" class="btn btn-sm w-full bg-brand-800 text-brand-100 hover:bg-red-700 hover:text-white">
            <x-icon name="sign-out" class="size-4" />
            Keluar
        </button>
    </form>
</div>
