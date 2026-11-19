<div>
    <x-audit.header :audit="$audit" current="recap" :counts="$this->stepCounts">
        <x-slot:actions>
            <x-audit.saving />
        </x-slot:actions>
    </x-audit.header>

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="max-w-2xl text-sm text-slate-600">Angka setiap departemen dihitung langsung dari hasil pemeriksaan. Gunakan baris manual untuk departemen yang diaudit di luar aplikasi.</p>
        <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="addManualEntry" class="btn btn-sm btn-secondary"><x-icon name="plus" class="size-4" />Tambah rekap manual</button>
            <a href="{{ route('audits.pdf.recap', $audit) }}" target="_blank" class="btn btn-sm btn-primary"><x-icon name="file-pdf" class="size-4" />Download PDF rekap</a>
        </div>
    </div>

    <x-stat-strip class="mb-5 lg:grid-cols-4" :stats="[
        ['label' => 'Departemen', 'value' => $totals['departments']],
        ['label' => 'Item diperiksa', 'value' => $totals['total']],
        ['label' => 'Sesuai', 'value' => $totals['sesuai'], 'tone' => 'text-emerald-700'],
        ['label' => 'Tidak sesuai', 'value' => $totals['findings'], 'tone' => 'text-red-700'],
    ]" />

    <article class="sheet p-5 sm:p-8">
        <x-audit.doc-header :company="$audit->company" title="Rekap Hasil Audit">
            <p>Client: <strong class="text-slate-900">{{ $audit->company->name }}</strong></p>
            <p>No. audit: <strong class="text-slate-900">{{ $audit->audit_number ?: '-' }}</strong>, tanggal: <strong class="text-slate-900">{{ $audit->audit_date?->translatedFormat('d F Y') ?? '-' }}</strong></p>
        </x-audit.doc-header>

        <div class="relative mt-6 overflow-x-auto rounded-xl ring-1 ring-slate-300">
            <table class="doc-table min-w-[900px]">
                <thead>
                    <tr>
                        <th class="w-10 text-center">No</th>
                        <th class="min-w-36">Departemen</th>
                        <th class="min-w-28">Auditee</th>
                        <th class="min-w-28">Auditor</th>
                        <th class="w-14 text-center">Total</th>
                        <th class="w-14 text-center text-emerald-800">Sesuai</th>
                        <th class="w-14 text-center text-red-800">Major</th>
                        <th class="w-14 text-center text-orange-800">Minor</th>
                        <th class="w-14 text-center text-amber-800">Obs</th>
                        <th class="w-24 text-center">Kepatuhan</th>
                        <th class="min-w-52">Kesimpulan / catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr wire:key="{{ $row['key'] }}" @class(['bg-brand-50/40' => $row['is_manual']])>
                            <td class="text-center font-bold text-slate-600">
                                {{ $loop->iteration }}
                                @if ($row['is_manual'])
                                    <button type="button" wire:click="removeManualEntry({{ $row['id'] }})" wire:confirm="Hapus baris rekap manual ini?" class="btn btn-icon btn-danger-ghost mx-auto mt-1 flex" aria-label="Hapus baris {{ $row['department'] }}">
                                        <x-icon name="trash" class="size-4" />
                                    </button>
                                @endif
                            </td>
                            @if ($row['is_manual'])
                                @php $base = "manual.{$row['id']}"; @endphp
                                @foreach (['department', 'auditee', 'auditor'] as $field)
                                    <td>
                                        <label for="{{ $base }}-{{ $field }}" class="sr-only">{{ $field }}</label>
                                        <input id="{{ $base }}-{{ $field }}" type="text" wire:model.live.blur="{{ $base }}.{{ $field }}" @class(['field min-h-9 px-2 py-1.5', 'font-bold text-brand-900' => $field === 'department'])>
                                        @error("{$base}.{$field}") <p class="field-error">{{ $message }}</p> @enderror
                                    </td>
                                @endforeach
                                @foreach (['total', 'sesuai', 'major', 'minor', 'observasi'] as $field)
                                    <td>
                                        <label for="{{ $base }}-{{ $field }}" class="sr-only">{{ $field }}</label>
                                        <input id="{{ $base }}-{{ $field }}" type="number" min="0" inputmode="numeric" wire:model.live.blur="{{ $base }}.{{ $field }}" class="field min-h-9 px-1.5 py-1.5 text-center tabular-nums">
                                        @error("{$base}.{$field}") <p class="field-error">{{ $message }}</p> @enderror
                                    </td>
                                @endforeach
                                <td class="text-center"><x-compliance :value="$row['compliance']" :empty="$row['total'] === 0" /></td>
                                <td>
                                    <label for="{{ $base }}-note" class="sr-only">Catatan</label>
                                    <textarea id="{{ $base }}-note" rows="2" wire:model.live.blur="{{ $base }}.note" class="field min-h-0 px-2 py-1.5 text-sm" placeholder="{{ $row['auto_note'] }}"></textarea>
                                </td>
                            @else
                                <td class="font-bold text-brand-900">{{ $row['department'] }}</td>
                                <td>{{ $row['auditee'] }}</td>
                                <td>{{ $row['auditor'] }}</td>
                                <td class="text-center font-bold tabular-nums">{{ $row['total'] }}</td>
                                <td class="text-center font-bold text-emerald-700 tabular-nums">{{ $row['sesuai'] }}</td>
                                <td class="text-center font-bold text-red-700 tabular-nums">{{ $row['major'] }}</td>
                                <td class="text-center font-bold text-orange-700 tabular-nums">{{ $row['minor'] }}</td>
                                <td class="text-center font-bold text-amber-700 tabular-nums">{{ $row['observasi'] }}</td>
                                <td class="text-center"><x-compliance :value="$row['compliance']" :empty="$row['total'] === 0" /></td>
                                <td x-data="{ editing: @js(filled($notes[$row['id']] ?? '')) }" wire:key="note-{{ $row['id'] }}-{{ filled($notes[$row['id']] ?? '') ? 'custom' : 'auto' }}">
                                    <div x-show="! editing" class="flex items-start justify-between gap-2">
                                        <p class="pt-1 text-sm text-slate-700">{{ $row['auto_note'] }}</p>
                                        <button type="button" x-on:click="editing = true; $nextTick(() => $refs.note.focus())" class="btn btn-icon btn-ghost -my-1" aria-label="Ubah catatan {{ $row['department'] }}">
                                            <x-icon name="pencil-simple" class="size-4" />
                                        </button>
                                    </div>
                                    <label for="note-{{ $row['id'] }}" class="sr-only">Catatan {{ $row['department'] }}</label>
                                    <textarea x-cloak x-show="editing" x-ref="note" id="note-{{ $row['id'] }}" rows="2" wire:model.live.blur="notes.{{ $row['id'] }}"
                                              class="field min-h-0 px-2 py-1.5 text-sm" placeholder="{{ $row['auto_note'] }}"></textarea>
                                    @error("notes.{$row['id']}") <p class="field-error">{{ $message }}</p> @enderror
                                    @if (filled($notes[$row['id']] ?? ''))
                                        <button type="button" wire:click="useAutomaticNote({{ $row['id'] }})" class="mt-1 inline-flex min-h-8 items-center gap-1 text-xs font-semibold text-brand-700 hover:underline">
                                            <x-icon name="arrows-clockwise" class="size-3.5" />
                                            Pakai catatan otomatis
                                        </button>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="11" class="py-8 text-center text-sm text-slate-600">Belum ada departemen. Tambahkan di Identitas Audit atau gunakan rekap manual.</td></tr>
                    @endforelse
                </tbody>
                @if ($rows->isNotEmpty())
                    <tfoot>
                        <tr class="bg-slate-100 font-extrabold text-brand-900">
                            <td colspan="4" class="text-right text-xs uppercase">Total keseluruhan</td>
                            <td class="text-center tabular-nums">{{ $totals['total'] }}</td>
                            <td class="text-center text-emerald-800 tabular-nums">{{ $totals['sesuai'] }}</td>
                            <td class="text-center text-red-800 tabular-nums">{{ $totals['major'] }}</td>
                            <td class="text-center text-orange-800 tabular-nums">{{ $totals['minor'] }}</td>
                            <td class="text-center text-amber-800 tabular-nums">{{ $totals['observasi'] }}</td>
                            <td class="text-center"><x-compliance :value="$totals['compliance']" :empty="$totals['total'] === 0" /></td>
                            <td class="text-xs font-semibold text-slate-600">Konsolidasi seluruh departemen</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <div class="mt-10 grid grid-cols-2 gap-6 text-center text-sm text-slate-800">
            <div>
                <p class="font-semibold">Disusun oleh (lead auditor),</p>
                <div class="mx-auto mt-14 w-full max-w-44 border-b border-slate-700"></div>
                <p class="mt-1 text-xs text-slate-600">Lead auditor internal</p>
            </div>
            <div>
                <p class="font-semibold">Diketahui &amp; disetujui oleh (manajemen),</p>
                <div class="mx-auto mt-14 w-full max-w-44 border-b border-slate-700"></div>
                <p class="mt-1 text-xs text-slate-600">Management representative / direktur</p>
            </div>
        </div>
    </article>

    <x-audit.step-nav :audit="$audit" current="recap" />
</div>
