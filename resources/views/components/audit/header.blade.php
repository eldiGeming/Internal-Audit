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

    {{-- Step rail: one ruled strip, the current step marked by a bar along its bottom edge. Counts are plain figures. --}}
    <nav class="card relative mt-5 overflow-x-auto [scrollbar-width:thin]" aria-label="Tahapan audit"
         x-data x-init="const step = $el.querySelector('[aria-current=step]'); if (step && $el.scrollWidth > $el.clientWidth) { $el.scrollLeft = step.offsetLeft - ($el.clientWidth - step.offsetWidth) / 2 }">
        <ol class="flex min-w-max divide-x divide-slate-200">
            @foreach ($steps as $step)
                @php
                    $isCurrent = $step['key'] === $current;
                    $count = $step['count'] ? ($counts[$step['count']] ?? 0) : null;
                    $hasFindings = $step['count'] === 'findings' && $count > 0;
                @endphp
                <li class="flex-1">
                    <a href="{{ route($step['route'], $audit) }}" wire:navigate
                       @if ($isCurrent) aria-current="step" @endif
                       @if ($count !== null) title="{{ $count }} {{ $step['unit'] }}" @endif
                       @class([
                           'group relative flex min-h-12 items-center justify-center gap-2 px-4 text-sm whitespace-nowrap transition-colors',
                           'bg-brand-50/70 font-bold text-brand-900' => $isCurrent,
                           'font-semibold text-slate-600 hover:bg-slate-50 hover:text-brand-900' => ! $isCurrent,
                       ])>
                        <span @class([
                            'text-xs font-bold tabular-nums',
                            'text-brand-700' => $isCurrent,
                            'text-slate-500 group-hover:text-brand-700' => ! $isCurrent,
                        ])>{{ $step['number'] }}</span>
                        <span>{{ $step['label'] }}</span>
                        @if ($count !== null)
                            <span @class([
                                'text-xs font-bold tabular-nums',
                                'text-red-700' => $hasFindings,
                                'text-slate-500' => ! $hasFindings,
                            ])>{{ $count }}<span class="sr-only"> {{ $step['unit'] }}</span></span>
                        @endif
                        @if ($isCurrent)
                            <span class="absolute inset-x-0 bottom-0 h-[3px] bg-brand-900" aria-hidden="true"></span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ol>
    </nav>
</header>
