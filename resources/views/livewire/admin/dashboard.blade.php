<div>
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-brand-900">Dashboard admin</h1>
        <p class="mt-1 text-sm text-slate-600">Ringkasan seluruh perusahaan, akun auditor, dan temuan audit.</p>
    </div>

    <x-stat-strip class="mt-6 lg:grid-cols-4" :stats="[
        ['label' => 'Jumlah PT', 'value' => $companiesCount, 'hint' => $auditsCount.' audit tercatat'],
        ['label' => 'Akun auditor', 'value' => $auditorsCount, 'hint' => $activeAuditorsCount.' akun aktif'],
        ['label' => 'Total CAR', 'value' => $findingsCount, 'tone' => 'text-red-700', 'hint' => 'Major, Minor, dan Observasi'],
        ['label' => 'Rata-rata kepatuhan', 'value' => $averageCompliance === null ? '-' : $averageCompliance.'%', 'hint' => 'Audit terbaru setiap PT'],
    ]" />

    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-12">
        {{-- Best company --}}
        <section class="rounded-xl bg-brand-900 p-6 text-white xl:col-span-5" aria-labelledby="best-title">
            <p id="best-title" class="text-sm font-semibold text-brand-200">PT hasil audit terbaik</p>
            @if ($best)
                <p class="mt-4 text-2xl leading-tight font-extrabold">{{ $best->company->name }}</p>
                <p class="mt-1 text-sm text-brand-200">{{ $best->audit_number ?: 'Tanpa no. audit' }}, {{ $best->audit_date?->translatedFormat('d F Y') ?? 'tanpa tanggal' }}</p>
                <div class="mt-6 flex items-end gap-6">
                    <div>
                        <p class="text-5xl font-extrabold tabular-nums">{{ $best->compliance() }}<span class="text-2xl text-brand-200">%</span></p>
                        <p class="text-sm text-brand-200">Kepatuhan</p>
                    </div>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-1 pb-1 text-sm whitespace-nowrap">
                        <dt class="text-brand-200">Item diperiksa</dt><dd class="font-bold tabular-nums">{{ $best->items_total }}</dd>
                        <dt class="text-brand-200">Sesuai</dt><dd class="font-bold tabular-nums">{{ $best->items_sesuai }}</dd>
                        <dt class="text-brand-200">CAR</dt><dd class="font-bold tabular-nums">{{ $best->items_findings }}</dd>
                    </dl>
                </div>
                <a href="{{ route('audits.conclusion', $best) }}" wire:navigate class="btn btn-sm mt-6 bg-white text-brand-900 hover:bg-brand-50">
                    Lihat kesimpulan audit
                    <x-icon name="arrow-right" class="size-4" />
                </a>
            @else
                <p class="mt-4 text-sm text-brand-100">Belum ada audit dengan item yang sudah diperiksa.</p>
            @endif
        </section>

        {{-- Ranking --}}
        <section class="card xl:col-span-7" aria-labelledby="rank-title">
            <div class="border-b border-slate-100 p-5">
                <h2 id="rank-title" class="text-base font-bold text-brand-900">Peringkat kepatuhan PT</h2>
                <p class="mt-0.5 text-sm text-slate-600">Berdasarkan audit terbaru setiap perusahaan.</p>
            </div>
            @if ($ranking->isEmpty())
                <p class="p-6 text-center text-sm text-slate-600">Belum ada data.</p>
            @else
                <ol class="divide-y divide-slate-100">
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
    </div>

    {{-- Recent audits --}}
    <section class="card mt-5" aria-labelledby="recent-title">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-5">
            <h2 id="recent-title" class="text-base font-bold text-brand-900">Audit terbaru diperbarui</h2>
            <a href="{{ route('audits.index') }}" wire:navigate class="btn btn-sm btn-secondary">Semua audit</a>
        </div>
        @if ($recentAudits->isEmpty())
            <p class="p-6 text-center text-sm text-slate-600">Belum ada audit.</p>
        @else
            <div class="relative overflow-x-auto">
                <table class="data-table min-w-[640px]">
                    <thead><tr><th>Perusahaan</th><th>No. audit</th><th>Auditor</th><th class="text-center">CAR</th><th class="text-center">Kepatuhan</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($recentAudits as $audit)
                            <tr wire:key="recent-{{ $audit->id }}">
                                <td class="font-bold text-brand-900">{{ $audit->company->name }}</td>
                                <td>{{ $audit->audit_number ?: '-' }}</td>
                                <td>{{ $audit->user?->name ?? 'Akun dihapus' }}</td>
                                <td class="text-center font-bold tabular-nums {{ $audit->items_findings > 0 ? 'text-red-700' : 'text-slate-500' }}">{{ $audit->items_findings }}</td>
                                <td class="text-center"><x-compliance :value="$audit->compliance()" :empty="$audit->items_total === 0" /></td>
                                <td class="text-right"><a href="{{ route('audits.identity', $audit) }}" wire:navigate class="btn btn-sm btn-soft">Buka</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
