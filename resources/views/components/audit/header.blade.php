@props(['audit', 'current', 'counts'])

@php
    $steps = \App\Support\AuditSteps::all();
@endphp

<header class="mb-6">
    <nav class="flex items-center gap-1.5 text-sm text-slate-600" aria-label="Breadcrumb">
        <a href="{{ route('audits.index') }}" wire:navigate class="inline-flex min-h-8 items-center gap-1.5 font-medium hover:text-brand-800">
            <x-icon name="arrow-left" class="size-4" />
            Daftar Audit
        </a>
    </nav>

    <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex min-w-0 items-center gap-3.5">
            <span class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-xl bg-white ring-1 ring-slate-200">
                @if ($audit->company->logoUrl())
                    <img src="{{ $audit->company->logoUrl() }}" alt="" class="size-full object-contain p-1" draggable="false">
                @else
                    <x-icon name="buildings" class="size-6 text-brand-700" />
                @endif
            </span>
            <div class="min-w-0">
                <h1 class="text-xl leading-tight font-extrabold tracking-tight text-balance break-words text-brand-900 sm:text-2xl">{{ $audit->company->name }}</h1>
                <p class="mt-0.5 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-600">
                    <span class="inline-flex items-center gap-1.5"><x-icon name="clipboard-text" class="size-4 text-slate-500" />{{ $audit->audit_number ?: 'No. audit belum diisi' }}</span>
                    <span class="inline-flex items-center gap-1.5"><x-icon name="calendar-blank" class="size-4 text-slate-500" />{{ $audit->audit_date?->translatedFormat('d F Y') ?? 'Tanggal belum diisi' }}</span>
                    @if (auth()->user()->isAdmin() && $audit->user)
                        <span class="inline-flex items-center gap-1.5"><x-icon name="user-circle" class="size-4 text-slate-500" />{{ $audit->user->name }}</span>
                    @endif
                </p>
            </div>
        </div>
        {{ $actions ?? '' }}
    </div>

    <nav class="card relative mt-5 overflow-x-auto p-1.5 [scrollbar-width:thin]" aria-label="Tahapan audit">
        <ol class="flex min-w-max gap-1">
            @foreach ($steps as $step)
                @php
                    $isCurrent = $step['key'] === $current;
                    $badge = $step['badge'] ? ($counts[$step['badge']] ?? 0) : null;
                @endphp
                <li class="flex-1">
                    <a href="{{ route($step['route'], $audit) }}" wire:navigate
                       @if ($isCurrent) aria-current="step" @endif
                       @class([
                           'group flex min-h-11 items-center justify-center gap-2 rounded-lg px-3 text-sm font-semibold whitespace-nowrap transition',
                           'bg-brand-900 text-white shadow-sm' => $isCurrent,
                           'text-slate-600 hover:bg-slate-100 hover:text-brand-900' => ! $isCurrent,
                       ])>
                        <span @class([
                            'grid size-6 place-items-center rounded-full text-xs font-bold transition-transform duration-200 group-hover:scale-110',
                            'bg-white text-brand-900' => $isCurrent,
                            'bg-slate-100 text-slate-700 ring-1 ring-slate-200' => ! $isCurrent,
                        ])>{{ $step['number'] }}</span>
                        <span>{{ $step['label'] }}</span>
                        @if ($badge !== null)
                            <span @class([
                                'min-w-5 rounded-full px-1.5 text-center text-[11px] leading-5 font-bold',
                                'bg-red-600 text-white' => $step['badge'] === 'findings' && $badge > 0,
                                'bg-white/20 text-white' => $isCurrent && ! ($step['badge'] === 'findings' && $badge > 0),
                                'bg-slate-200 text-slate-700' => ! $isCurrent && ! ($step['badge'] === 'findings' && $badge > 0),
                            ])>{{ $badge }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ol>
    </nav>
</header>
