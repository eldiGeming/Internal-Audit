<div>
    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-brand-900">Dashboard admin</h1>
            <p class="mt-1 text-sm text-slate-600">Ringkasan perusahaan, akun auditor, temuan, dan tindak lanjut CAR.</p>
        </div>
        <p class="text-sm font-medium text-slate-600">Data per {{ $today->translatedFormat('d F Y') }}</p>
    </div>

    <x-stat-strip class="mt-6 md:grid-cols-3 xl:grid-cols-6" :stats="[
        ['label' => 'Jumlah PT', 'value' => $companiesCount, 'hint' => $auditsCount.' audit tercatat'],
        ['label' => 'Akun auditor', 'value' => $auditorsCount, 'hint' => $activeAuditorsCount.' akun aktif'],
        ['label' => 'Rata-rata kepatuhan', 'value' => $averageCompliance === null ? '-' : $averageCompliance.'%', 'hint' => 'Audit terbaru setiap PT'],
        ['label' => 'CAR terbuka', 'value' => $openCount, 'tone' => 'text-brand-900', 'hint' => 'Dari '.$findingsCount.' CAR'],
        ['label' => 'CAR lewat target', 'value' => $overdueCount, 'tone' => 'text-red-700', 'hint' => 'Target sudah lewat'],
        ['label' => 'CAR selesai', 'value' => $closedCount, 'tone' => 'text-emerald-700', 'hint' => $findingsCount > 0 ? round($closedCount / $findingsCount * 100).'% dari semua CAR' : 'Belum ada CAR'],
    ]" />

    {{-- Two independent columns so no card is stretched to its neighbour's height. Phones read top to bottom: status, follow-up, workload, ranking. --}}
    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-12 xl:items-start">
        <div class="space-y-5 xl:col-span-7">
            {{-- CAR follow-up status + findings per category --}}
            <section class="card p-5 sm:p-6" aria-labelledby="progress-title">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <h2 id="progress-title" class="text-base font-bold text-brand-900">Status tindak lanjut CAR</h2>
                    <a href="{{ route('admin.cars') }}" wire:navigate class="inline-flex min-h-9 items-center gap-1 text-sm font-semibold text-brand-700 hover:underline">
                        Dokumen CAR per PT <x-icon name="arrow-right" class="size-4" />
                    </a>
                </div>

                @if ($findingsCount === 0)
                    <p class="mt-4 text-sm text-slate-600">Belum ada temuan Tidak Sesuai, jadi belum ada CAR untuk dipantau.</p>
                @else
                    {{-- One whole split into parts: a tall segmented bar, figures printed only where they fit. --}}
                    <div class="mt-4 flex h-7 w-full gap-0.5" role="img"
                         aria-label="{{ $progress->map(fn ($row) => $row['state']->label().' '.$row['count'])->implode(', ') }}">
                        @foreach ($progress as $row)
                            @continue($row['count'] === 0)
                            <div class="{{ $row['state']->fillClass() }} flex min-w-1 items-center justify-center rounded-[4px] text-xs font-bold text-white"
                                 style="flex: {{ $row['count'] }} 1 0%" title="{{ $row['state']->label() }}: {{ $row['count'] }} CAR">
                                @if ($row['count'] / $findingsCount >= 0.1) {{ $row['count'] }} @endif
                            </div>
                        @endforeach
                    </div>
                    <ul class="mt-3 grid grid-cols-1 gap-x-8 gap-y-2 text-sm sm:grid-cols-2">
                        @foreach ($progress as $row)
                            <li class="flex items-center gap-2">
                                <span class="{{ $row['state']->fillClass() }} size-3 shrink-0 rounded-[3px]" aria-hidden="true"></span>
                                <span class="min-w-0 text-slate-700">{{ $row['state']->label() }}</span>
                                <span class="ml-auto font-bold text-slate-900 tabular-nums">{{ $row['count'] }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <h3 class="mt-7 text-sm font-bold text-brand-900">Temuan per kategori</h3>
                    @php $maxSeverity = max(1, $severities->max('count')); @endphp
                    <ul class="mt-3 space-y-2.5">
                        @foreach ($severities as $row)
                            <li class="grid grid-cols-[6.5rem_minmax(0,1fr)_2.5rem] items-center gap-3">
                                <x-severity :severity="$row['severity']" large />
                                <span class="h-3" title="{{ $row['severity']->label() }}: {{ $row['count'] }} temuan">
                                    @if ($row['count'] > 0)
                                        <span class="{{ $row['severity']->barClass() }} block h-full w-(--bar) rounded-r-[4px]" style="--bar: {{ $row['count'] / $maxSeverity * 100 }}%"></span>
                                    @endif
                                </span>
                                <span class="text-right text-sm font-bold text-slate-900 tabular-nums">{{ $row['count'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- Nearest CAR target dates --}}
            <section class="card flex flex-col" aria-labelledby="followup-title">
                <div class="border-b border-slate-200 p-5">
                    <h2 id="followup-title" class="text-base font-bold text-brand-900">Tindak lanjut terdekat</h2>
                    <p class="mt-0.5 text-sm text-slate-600">CAR terbuka, diurutkan dari tanggal target paling awal.</p>
                </div>
                @if ($followUps->isEmpty())
                    <x-empty-state icon="calendar-blank" title="Tidak ada target yang perlu dipantau">
                        Tanggal target muncul di sini setelah auditor mengisinya di lembar CAR.
                    </x-empty-state>
                @else
                    <ol class="divide-y divide-slate-200">
                        @foreach ($followUps as $finding)
                            @php
                                $due = $finding['due'];
                                $daysLate = (int) $due->diffInDays($today, false);
                                $isOverdue = $daysLate > 0;
                                $action = $finding['action'];
                            @endphp
                            <li wire:key="follow-{{ $finding['item']->id }}">
                                <a href="{{ route('audits.car', $finding['audit']) }}#{{ Str::lower($finding['number']) }}" wire:navigate
                                   class="flex items-start gap-4 px-5 py-3.5 transition-colors hover:bg-slate-50">
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-sm">
                                            <span class="font-extrabold text-brand-900 tabular-nums">{{ $finding['number'] }}</span>
                                            <span class="font-semibold break-words text-slate-800">{{ $finding['audit']->company->name }}</span>
                                        </span>
                                        <span class="mt-0.5 block text-xs text-slate-600">
                                            {{ $finding['department']->displayName() }}, PIC {{ $action?->correction_pic ?: ($action?->prevention_pic ?: ($finding['department']->auditee ?: '-')) }}
                                        </span>
                                    </span>
                                    <span class="shrink-0 text-right">
                                        <span class="block text-sm font-bold text-slate-900 tabular-nums">{{ $due->translatedFormat('d M Y') }}</span>
                                        <span @class(['block text-xs font-semibold', 'text-red-700' => $isOverdue, 'text-slate-600' => ! $isOverdue])>
                                            {{ $daysLate === 0 ? 'Hari ini' : ($isOverdue ? 'Lewat '.$daysLate.' hari' : abs($daysLate).' hari lagi') }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>

            {{-- Auditor workload --}}
            <section class="card" aria-labelledby="workload-title">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 p-5">
                    <div>
                        <h2 id="workload-title" class="text-base font-bold text-brand-900">Beban kerja auditor</h2>
                        <p class="mt-0.5 text-sm text-slate-600">Yang paling banyak CAR lewat target di atas.</p>
                    </div>
                    <a href="{{ route('admin.auditors') }}" wire:navigate class="btn btn-sm btn-secondary shrink-0">Akun auditor</a>
                </div>
                @if ($workload->isEmpty())
                    <x-empty-state icon="users" title="Belum ada akun auditor" />
                @else
                    <div class="relative overflow-x-auto">
                        <table class="data-table min-w-[420px]">
                            <thead>
                                <tr>
                                    <th>Auditor</th>
                                    <th class="text-center">Audit</th>
                                    <th class="text-center">CAR terbuka</th>
                                    <th class="text-center">Lewat target</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($workload as $row)
                                    <tr wire:key="work-{{ $row['user']->id }}">
                                        <td>
                                            <p class="font-bold text-slate-900">{{ $row['user']->name }}</p>
                                            <p class="text-xs text-slate-600">
                                                {{ $row['user']->is_active ? 'Aktif' : 'Nonaktif' }},
                                                {{ $row['lastActivity'] ? 'terakhir '.$row['lastActivity']->diffForHumans() : 'belum ada audit' }}
                                            </p>
                                        </td>
                                        <td class="text-center font-semibold tabular-nums">{{ $row['audits'] }}</td>
                                        <td class="text-center font-semibold tabular-nums">{{ $row['open'] }}</td>
                                        <td @class(['text-center font-bold tabular-nums', 'text-red-700' => $row['overdue'] > 0, 'text-slate-500' => $row['overdue'] === 0])>{{ $row['overdue'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

        </div>

        <div class="space-y-5 xl:col-span-5">
            {{-- Ranking --}}
            <section class="card" aria-labelledby="rank-title">
                <div class="border-b border-slate-200 p-5">
                    <h2 id="rank-title" class="text-base font-bold text-brand-900">Peringkat kepatuhan PT</h2>
                    <p class="mt-0.5 text-sm text-slate-600">Berdasarkan audit terbaru setiap perusahaan.</p>
                </div>
                @if ($ranking->isEmpty())
                    <x-empty-state icon="chart-bar" title="Belum ada data peringkat">
                        Peringkat muncul setelah ada audit dengan item yang sudah diperiksa.
                    </x-empty-state>
                @else
                    <ol class="divide-y divide-slate-200">
                        @foreach ($ranking as $audit)
                            {{-- Phones: the meter drops under the name so the name keeps its width. --}}
                            <li wire:key="rank-{{ $audit->id }}" class="grid grid-cols-[1.5rem_minmax(0,1fr)_auto] items-center gap-x-4 gap-y-1.5 px-5 py-3 sm:grid-cols-[1.5rem_minmax(0,1fr)_auto_auto]">
                                <span @class([
                                    'row-span-2 text-right text-xl font-extrabold tabular-nums sm:row-span-1',
                                    'text-brand-900' => $loop->first,
                                    'text-slate-500' => ! $loop->first,
                                ])>{{ $loop->iteration }}</span>
                                <div class="min-w-0">
                                    <p class="font-bold break-words text-brand-900">{{ $audit->company->name }}</p>
                                    <p class="text-xs text-slate-600">{{ $audit->audit_number ?: 'Tanpa no. audit' }}, {{ $audit->items_findings }} CAR, {{ $audit->user?->name ?? 'akun dihapus' }}</p>
                                </div>
                                <x-compliance :value="$audit->compliance()" class="col-start-2 row-start-2 -ml-2 justify-self-start sm:col-start-3 sm:row-start-1 sm:ml-0" />
                                <a href="{{ route('audits.recap', $audit) }}" wire:navigate class="btn btn-sm btn-icon btn-ghost col-start-3 row-span-2 row-start-1 sm:col-start-4 sm:row-span-1" aria-label="Buka rekap {{ $audit->company->name }}">
                                    <x-icon name="caret-right" class="size-4" />
                                </a>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-1">
                {{-- Best company --}}
                <section class="rounded-xl bg-brand-900 p-6 text-white" aria-labelledby="best-title">
                    <p id="best-title" class="text-sm font-semibold text-brand-200">PT hasil audit terbaik</p>
                    @if ($best)
                        <p class="mt-3 text-xl leading-tight font-extrabold">{{ $best->company->name }}</p>
                        <p class="mt-1 text-sm text-brand-200">{{ $best->audit_number ?: 'Tanpa no. audit' }}, {{ $best->audit_date?->translatedFormat('d F Y') ?? 'tanpa tanggal' }}</p>
                        <div class="mt-5 flex flex-wrap items-end gap-x-6 gap-y-3">
                            <div>
                                <p class="text-5xl font-extrabold">{{ $best->compliance() }}<span class="text-2xl text-brand-200">%</span></p>
                                <p class="text-sm text-brand-200">Kepatuhan</p>
                            </div>
                            <dl class="grid grid-cols-2 gap-x-6 gap-y-1 pb-1 text-sm whitespace-nowrap">
                                <dt class="text-brand-200">Item diperiksa</dt><dd class="font-bold tabular-nums">{{ $best->items_total }}</dd>
                                <dt class="text-brand-200">Sesuai</dt><dd class="font-bold tabular-nums">{{ $best->items_sesuai }}</dd>
                                <dt class="text-brand-200">CAR</dt><dd class="font-bold tabular-nums">{{ $best->items_findings }}</dd>
                            </dl>
                        </div>
                        <a href="{{ route('audits.conclusion', $best) }}" wire:navigate class="btn btn-sm mt-5 bg-white text-brand-900 hover:bg-slate-100">
                            Lihat kesimpulan audit
                            <x-icon name="arrow-right" class="size-4" />
                        </a>
                    @else
                        <p class="mt-3 text-sm text-brand-100">Belum ada audit dengan item yang sudah diperiksa.</p>
                    @endif
                </section>

                {{-- Audits per month: one series, so the title names it and no legend is needed. --}}
                <section class="card p-5 sm:p-6" aria-labelledby="monthly-title">
                    <h2 id="monthly-title" class="text-base font-bold text-brand-900">Audit per bulan</h2>
                    <p class="mt-0.5 text-sm text-slate-600">Menurut tanggal audit, 6 bulan terakhir.</p>
                    @php $maxMonthly = max(1, $auditsPerMonth->max('count')); @endphp
                    <div class="mt-4 grid grid-cols-6 items-end gap-2 border-b border-slate-300" style="height: 8.5rem">
                        @foreach ($auditsPerMonth as $row)
                            <div class="flex h-full flex-col items-center justify-end gap-1" title="{{ $row['month']->translatedFormat('F Y') }}: {{ $row['count'] }} audit">
                                <span @class(['text-xs font-bold tabular-nums', 'text-slate-900' => $row['count'] > 0, 'text-slate-500' => $row['count'] === 0])>{{ $row['count'] }}</span>
                                @if ($row['count'] > 0)
                                    <span class="block h-(--bar) w-full max-w-9 rounded-t-[4px] bg-brand-700" style="--bar: {{ $row['count'] / $maxMonthly * 100 }}%"></span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-1.5 grid grid-cols-6 gap-2 text-center text-xs text-slate-600">
                        @foreach ($auditsPerMonth as $row)
                            <span @class(['font-bold text-slate-900' => $loop->last])>{{ $row['month']->translatedFormat('M') }}</span>
                        @endforeach
                    </div>
                </section>
            </div>

        </div>
    </div>

    {{-- Recent audits --}}
    <section class="card mt-5" aria-labelledby="recent-title">
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 p-5">
            <h2 id="recent-title" class="text-base font-bold text-brand-900">Audit terbaru diperbarui</h2>
            <a href="{{ route('audits.index') }}" wire:navigate class="btn btn-sm btn-secondary">Semua audit</a>
        </div>
        @if ($recentAudits->isEmpty())
            <x-empty-state icon="folder-open" title="Belum ada audit" />
        @else
            <div class="relative overflow-x-auto">
                <table class="data-table min-w-[640px]">
                    <thead><tr><th>Perusahaan</th><th>No. audit</th><th>Auditor</th><th class="text-center">CAR</th><th class="text-center">Kepatuhan</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($recentAudits as $audit)
                            <tr wire:key="recent-{{ $audit->id }}">
                                <td>
                                    <p class="font-bold text-brand-900">{{ $audit->company->name }}</p>
                                    <p class="text-xs text-slate-600">Diubah {{ $audit->updated_at->diffForHumans() }}</p>
                                </td>
                                <td>{{ $audit->audit_number ?: '-' }}</td>
                                <td>{{ $audit->user?->name ?? 'Akun dihapus' }}</td>
                                <td class="text-center font-bold tabular-nums {{ $audit->items_findings > 0 ? 'text-red-700' : 'text-slate-500' }}">{{ $audit->items_findings }}</td>
                                <td class="text-center"><x-compliance :value="$audit->compliance()" :empty="$audit->items_total === 0" /></td>
                                <td class="text-right"><a href="{{ route('audits.identity', $audit) }}" wire:navigate class="btn btn-sm btn-secondary">Buka</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
