<div>
    <x-audit.header :audit="$audit" current="car" :counts="$this->stepCounts">
        <x-slot:actions>
            <x-audit.saving />
        </x-slot:actions>
    </x-audit.header>

    <section class="card p-5 sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="flex flex-wrap items-baseline gap-x-3 gap-y-1 text-lg font-extrabold text-brand-900">
                    Corrective Action Request
                    <span @class(['text-sm font-semibold tabular-nums', 'text-red-700' => $findings->isNotEmpty(), 'text-slate-500' => $findings->isEmpty()])>{{ $findings->count() }} temuan</span>
                </h2>
                <p class="mt-1 max-w-2xl text-sm text-slate-600">Setiap item Tidak Sesuai (Major, Minor, Observasi) otomatis menjadi satu lembar CAR. Seluruh lembar diekspor menjadi satu file PDF dokumen tindakan korektif.</p>
            </div>
            @if ($findings->isNotEmpty())
                <a href="{{ route('audits.pdf.car', $audit) }}" target="_blank" class="btn btn-primary shrink-0">
                    <x-icon name="file-pdf" class="size-4" />
                    Export PDF semua CAR
                </a>
            @endif
        </div>

        @if ($findings->isNotEmpty())
            <div class="mt-5 grid gap-3 border-t border-slate-100 pt-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($findings as $finding)
                    <a href="#{{ Str::lower($finding['number']) }}" wire:key="chip-{{ $finding['item']->id }}"
                       class="group rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200 transition ring-inset hover:-translate-y-0.5 hover:bg-white hover:shadow-md hover:ring-brand-300">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-extrabold tracking-wide text-brand-900 tabular-nums group-hover:underline group-hover:underline-offset-4">{{ $finding['number'] }}</span>
                            <x-severity :severity="$finding['severity']" />
                        </div>
                        <p class="mt-2 text-sm font-bold text-brand-900">{{ $finding['department']->displayName() }}</p>
                        <p class="mt-0.5 line-clamp-2 text-sm text-slate-600">{{ $finding['item']->evidence ?: $finding['item']->question }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    @if ($findings->isEmpty())
        <x-empty-state icon="check-circle" title="Belum ada temuan Tidak Sesuai" class="card mt-5">
            Tandai item pemeriksaan sebagai Tidak Sesuai (Major, Minor, atau Observasi) di Checklist Audit atau Daftar Periksa untuk menerbitkan CAR.
            <x-slot:actions>
                <a href="{{ route('audits.inspection', $audit) }}" wire:navigate class="btn btn-primary">Buka Daftar Periksa</a>
            </x-slot:actions>
        </x-empty-state>
    @endif

    <div class="mt-5 space-y-6">
        @foreach ($findings as $finding)
            @php
                $item = $finding['item'];
                $department = $finding['department'];
                $key = "cars.{$item->id}";
            @endphp
            @continue(! isset($cars[$item->id]))
            <article id="{{ Str::lower($finding['number']) }}" wire:key="car-{{ $item->id }}" class="sheet scroll-mt-6 p-5 sm:p-8">
                <div class="flex flex-col-reverse gap-4 border-b-2 border-brand-900 pb-5 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-lg font-extrabold text-brand-900 uppercase sm:text-xl">Dokumen tindakan korektif &amp; pencegahan</h3>
                        <p class="mt-1 text-sm text-slate-700">No. audit: <strong>{{ $audit->audit_number ?: '-' }}</strong>, client: <strong>{{ $audit->company->name }}</strong></p>
                    </div>
                    <div class="flex items-center gap-3 self-end sm:self-auto">
                        <dl class="overflow-hidden rounded-lg text-right ring-1 ring-slate-400">
                            <dt class="border-b border-slate-300 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Nomor CAR</dt>
                            <dd class="px-3 py-1.5 text-lg font-extrabold text-brand-900 tabular-nums">{{ $finding['number'] }}</dd>
                        </dl>
                        <div class="grid h-16 w-28 place-items-center rounded-lg bg-slate-50 p-1.5 ring-1 ring-slate-200">
                            @if ($audit->company->logoUrl())
                                <img src="{{ $audit->company->logoUrl() }}" alt="Logo {{ $audit->company->name }}" class="max-h-full max-w-full object-contain" draggable="false">
                            @else
                                <span class="text-xs text-slate-500 italic">Logo</span>
                            @endif
                        </div>
                    </div>
                </div>

                <dl class="mt-5 grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-4 text-sm ring-1 ring-slate-200 ring-inset lg:grid-cols-4">
                    <div><dt class="text-slate-600">Departemen</dt><dd class="font-bold text-brand-900">{{ $department->displayName() }}</dd></div>
                    <div><dt class="text-slate-600">Auditee</dt><dd class="font-semibold">{{ $department->auditee ?: '-' }}</dd></div>
                    <div><dt class="text-slate-600">Auditor</dt><dd class="font-semibold">{{ $department->auditor ?: '-' }}</dd></div>
                    <div><dt class="text-slate-600">Kategori temuan</dt><dd class="mt-0.5"><x-severity :severity="$finding['severity']" large /></dd></div>
                </dl>

                <div class="mt-5 space-y-4">
                    <section class="overflow-hidden rounded-xl ring-1 ring-slate-300">
                        <h4 class="bg-slate-100 px-4 py-2.5 text-xs font-extrabold tracking-wide text-brand-900 uppercase">A. Uraian ketidaksesuaian &amp; bukti objektif</h4>
                        <dl class="space-y-2 p-4 text-sm">
                            <div><dt class="inline font-semibold text-slate-700">Criteria / standar: </dt><dd class="inline">{{ $finding['criteria'] }}</dd></div>
                            <div><dt class="inline font-semibold text-slate-700">Poin pemeriksaan: </dt><dd class="inline">{{ $item->question }}</dd></div>
                            <div><dt class="inline font-semibold text-slate-700">Bukti objektif temuan: </dt><dd class="inline">{{ $item->evidence ?: '-' }}</dd></div>
                        </dl>
                    </section>

                    <section class="overflow-hidden rounded-xl ring-1 ring-slate-300">
                        <h4 class="bg-slate-100 px-4 py-2.5 text-xs font-extrabold tracking-wide text-brand-900 uppercase"><label for="rc-{{ $item->id }}">B. Analisis akar masalah (root cause)</label></h4>
                        <div class="p-4">
                            <textarea id="rc-{{ $item->id }}" rows="3" wire:model.live.blur="{{ $key }}.root_cause" class="field" placeholder="Jelaskan penyebab utama ketidaksesuaian"></textarea>
                            @error("{$key}.root_cause") <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </section>

                    @foreach ([
                        ['prefix' => 'correction', 'letter' => 'C', 'title' => 'Tindakan koreksi', 'hint' => 'Perbaikan langsung atas temuan', 'placeholder' => 'Tuliskan perbaikan segera untuk mengatasi temuan ini'],
                        ['prefix' => 'prevention', 'letter' => 'D', 'title' => 'Tindakan pencegahan', 'hint' => 'Mencegah temuan serupa terulang', 'placeholder' => 'Tuliskan langkah sistemik agar masalah serupa tidak terulang'],
                    ] as $section)
                        <section class="overflow-hidden rounded-xl ring-1 ring-slate-300">
                            <h4 class="bg-slate-100 px-4 py-2.5 text-xs font-extrabold tracking-wide text-brand-900 uppercase">
                                <label for="{{ $section['prefix'] }}-{{ $item->id }}">{{ $section['letter'] }}. {{ $section['title'] }}</label>
                                <span class="font-semibold text-slate-600 normal-case tracking-normal">({{ $section['hint'] }})</span>
                            </h4>
                            <div class="space-y-3 p-4">
                                <textarea id="{{ $section['prefix'] }}-{{ $item->id }}" rows="3" wire:model.live.blur="{{ $key }}.{{ $section['prefix'] }}" class="field" placeholder="{{ $section['placeholder'] }}"></textarea>
                                @error("{$key}.{$section['prefix']}") <p class="field-error">{{ $message }}</p> @enderror
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label for="{{ $section['prefix'] }}-pic-{{ $item->id }}" class="field-label">PIC {{ Str::lower($section['title']) }}</label>
                                        <input id="{{ $section['prefix'] }}-pic-{{ $item->id }}" type="text" wire:model.live.blur="{{ $key }}.{{ $section['prefix'] }}_pic" class="field" placeholder="Penanggung jawab">
                                        @error("{$key}.{$section['prefix']}_pic") <p class="field-error">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="{{ $section['prefix'] }}-due-{{ $item->id }}" class="field-label">Target tanggal selesai</label>
                                        <input id="{{ $section['prefix'] }}-due-{{ $item->id }}" type="date" wire:model.live.change="{{ $key }}.{{ $section['prefix'] }}_due_date" class="field">
                                        @error("{$key}.{$section['prefix']}_due_date") <p class="field-error">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        </section>
                    @endforeach

                    <section class="overflow-hidden rounded-xl ring-1 ring-slate-300">
                        <h4 class="bg-slate-100 px-4 py-2.5 text-xs font-extrabold tracking-wide text-brand-900 uppercase">E. Verifikasi efektivitas &amp; status CAR</h4>
                        <div class="grid gap-3 p-4 sm:grid-cols-12">
                            <div class="sm:col-span-8">
                                <label for="vn-{{ $item->id }}" class="field-label">Catatan verifikasi auditor</label>
                                <input id="vn-{{ $item->id }}" type="text" wire:model.live.blur="{{ $key }}.verification_note" class="field" placeholder="Hasil verifikasi bukti perbaikan">
                                @error("{$key}.verification_note") <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-4">
                                <label for="st-{{ $item->id }}" class="field-label">Status CAR</label>
                                <select id="st-{{ $item->id }}" wire:model.live="{{ $key }}.status"
                                        @class(['field font-semibold', 'text-emerald-800' => $cars[$item->id]['status'] === 'closed', 'text-amber-800' => $cars[$item->id]['status'] !== 'closed'])>
                                    @foreach (\App\Enums\CarStatus::cases() as $status)
                                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </section>
                </div>
            </article>
        @endforeach
    </div>

    <x-audit.step-nav :audit="$audit" current="car" />
</div>
