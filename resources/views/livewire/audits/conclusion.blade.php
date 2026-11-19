<div data-draft-scope="audit-{{ $audit->id }}">
    <x-audit.header :audit="$audit" current="conclusion" :counts="$this->stepCounts">
        <x-slot:actions>
            <div class="flex items-center gap-3">
                <x-audit.saving />
                <a href="{{ route('audits.pdf.conclusion', $audit) }}" target="_blank" class="btn btn-primary">
                    <x-icon name="printer" class="size-4" />
                    Export PDF kesimpulan
                </a>
            </div>
        </x-slot:actions>
    </x-audit.header>

    <article class="sheet space-y-8 p-5 sm:p-8">
        <x-audit.doc-header :company="$audit->company" title="Kesimpulan Audit Internal">
            <p>Client: <strong class="text-slate-900">{{ $audit->company->name }}</strong>, no. audit: <strong class="text-slate-900">{{ $audit->audit_number ?: '-' }}</strong>, tanggal: <strong class="text-slate-900">{{ $audit->audit_date?->translatedFormat('d F Y') ?? '-' }}</strong></p>
            <p>Tujuan audit: {{ $audit->objective ?: '-' }}</p>
            <p>Criteria acuan: <strong class="text-brand-900">{{ $criteria }}</strong></p>
        </x-audit.doc-header>

        {{-- I. Recap --}}
        <section aria-labelledby="sec-1">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h3 id="sec-1" class="flex items-baseline gap-2.5 text-base font-extrabold text-brand-900">
                    <span class="w-6 shrink-0 text-slate-500">I.</span>
                    Rekapitulasi hasil audit seluruh departemen
                </h3>
                <p class="flex items-center gap-2 pl-8 text-sm font-semibold text-slate-600 sm:pl-0">
                    Kepatuhan keseluruhan
                    <x-compliance :value="$totals['compliance']" :empty="$totals['total'] === 0" />
                </p>
            </div>
            <div class="relative mt-3 overflow-x-auto rounded-xl ring-1 ring-slate-300">
                <table class="doc-table min-w-[820px]">
                    <thead>
                        <tr>
                            <th class="w-10 text-center">No</th>
                            <th>Departemen</th>
                            <th>Auditee</th>
                            <th>Auditor</th>
                            <th class="text-center">Total</th>
                            <th class="text-center text-emerald-800">Sesuai</th>
                            <th class="text-center text-red-800">Major</th>
                            <th class="text-center text-orange-800">Minor</th>
                            <th class="text-center text-amber-800">Obs</th>
                            <th class="text-center">Kepatuhan</th>
                            <th>Catatan departemen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr wire:key="c-{{ $row['key'] }}">
                                <td class="text-center font-bold">{{ $loop->iteration }}</td>
                                <td class="font-bold text-brand-900">{{ $row['department'] }}</td>
                                <td>{{ $row['auditee'] }}</td>
                                <td>{{ $row['auditor'] }}</td>
                                <td class="text-center font-bold tabular-nums">{{ $row['total'] }}</td>
                                <td class="text-center font-bold text-emerald-700 tabular-nums">{{ $row['sesuai'] }}</td>
                                <td class="text-center font-bold text-red-700 tabular-nums">{{ $row['major'] }}</td>
                                <td class="text-center font-bold text-orange-700 tabular-nums">{{ $row['minor'] }}</td>
                                <td class="text-center font-bold text-amber-700 tabular-nums">{{ $row['observasi'] }}</td>
                                <td class="text-center font-extrabold tabular-nums">{{ $row['compliance'] }}%</td>
                                <td class="text-xs">{{ $row['note'] ?: $row['auto_note'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="py-6 text-center text-sm text-slate-600">Belum ada data rekap.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-100 font-extrabold text-brand-900">
                            <td colspan="4" class="text-right text-xs uppercase">Total keseluruhan</td>
                            <td class="text-center tabular-nums">{{ $totals['total'] }}</td>
                            <td class="text-center text-emerald-800 tabular-nums">{{ $totals['sesuai'] }}</td>
                            <td class="text-center text-red-800 tabular-nums">{{ $totals['major'] }}</td>
                            <td class="text-center text-orange-800 tabular-nums">{{ $totals['minor'] }}</td>
                            <td class="text-center text-amber-800 tabular-nums">{{ $totals['observasi'] }}</td>
                            <td class="text-center tabular-nums">{{ $totals['compliance'] }}%</td>
                            <td class="text-xs font-semibold text-slate-600">Konsolidasi seluruh departemen</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        {{-- II. CAR --}}
        <section aria-labelledby="sec-2">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h3 id="sec-2" class="flex items-baseline gap-2.5 text-base font-extrabold text-brand-900">
                    <span class="w-6 shrink-0 text-slate-500">II.</span>
                    Daftar CAR &amp; rencana tindakan
                </h3>
                <p @class(['pl-8 text-sm font-semibold tabular-nums sm:pl-0', 'text-red-700' => $findings->isNotEmpty(), 'text-slate-500' => $findings->isEmpty()])>{{ $findings->count() }} CAR tercatat</p>
            </div>
            <div class="mt-3 space-y-4">
                @forelse ($findings as $finding)
                    <div wire:key="cf-{{ $finding['item']->id }}">
                        @include('partials.car-summary', ['finding' => $finding])
                    </div>
                @empty
                    <p class="rounded-xl bg-slate-50 p-6 text-center text-sm text-slate-600 ring-1 ring-slate-200 ring-inset">Tidak ada temuan CAR pada audit ini.</p>
                @endforelse
            </div>
        </section>

        {{-- III. Final statement --}}
        <section aria-labelledby="sec-3">
            <h3 id="sec-3" class="flex items-baseline gap-2.5 text-base font-extrabold text-brand-900">
                <span class="w-6 shrink-0 text-slate-500">III.</span>
                <label for="finalConclusion">Pernyataan kesimpulan akhir &amp; rekomendasi tim auditor</label>
            </h3>
            <textarea id="finalConclusion" rows="5" wire:model.live.blur="finalConclusion" class="field mt-3"
                      placeholder="Tuliskan kesimpulan menyeluruh hasil audit, tingkat efektivitas sistem manajemen, serta rekomendasi tindak lanjut untuk manajemen"></textarea>
            @error('finalConclusion') <p class="field-error">{{ $message }}</p> @enderror
        </section>

        <div class="grid grid-cols-2 gap-6 pt-2 text-center text-sm text-slate-800">
            <div>
                <p class="font-semibold">Disusun &amp; dilaporkan oleh (lead auditor),</p>
                <div class="mx-auto mt-14 w-full max-w-44 border-b border-slate-700"></div>
                <p class="mt-1 text-xs text-slate-600">Ketua tim auditor internal</p>
            </div>
            <div>
                <p class="font-semibold">Diterima &amp; disahkan oleh (manajemen),</p>
                <div class="mx-auto mt-14 w-full max-w-44 border-b border-slate-700"></div>
                <p class="mt-1 text-xs text-slate-600">Management representative / pimpinan</p>
            </div>
        </div>
    </article>

    <x-audit.step-nav :audit="$audit" current="conclusion" />
</div>
