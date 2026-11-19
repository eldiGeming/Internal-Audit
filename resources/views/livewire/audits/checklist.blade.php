<div data-draft-scope="audit-{{ $audit->id }}:dept-{{ $departmentId }}">
    <x-audit.header :audit="$audit" current="checklist" :counts="$this->stepCounts">
        <x-slot:actions>
            <x-audit.saving />
        </x-slot:actions>
    </x-audit.header>

    @if ($department === null)
        <x-empty-state icon="buildings" title="Belum ada departemen untuk diberi checklist" class="card">
            Tambahkan departemen di sini atau di halaman Identitas Audit, lalu susun pertanyaan pemeriksaannya.
            <x-slot:actions>
                <button type="button" wire:click="openDepartmentModal" class="btn btn-add">
                    <x-icon name="plus" class="size-4" />
                    Departemen baru
                </button>
            </x-slot:actions>
        </x-empty-state>
    @else
        <div class="mb-5">
            @include('livewire.audits.partials.department-picker')
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-12">
            {{-- Identity & multi criteria --}}
            <section class="card p-5 sm:p-6 lg:col-span-7" aria-labelledby="chk-identity">
                <h2 id="chk-identity" class="flex items-center gap-2 text-base font-bold text-brand-900">
                    <x-icon name="target" class="size-5 text-brand-600" />
                    Identitas &amp; criteria audit
                </h2>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="chk-dept" class="field-label">Departemen</label>
                        <input id="chk-dept" type="text" wire:model.live.blur="departmentName" class="field">
                        @error('departmentName') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="chk-auditee" class="field-label">Auditee</label>
                        <input id="chk-auditee" type="text" wire:model.live.blur="auditee" class="field" placeholder="Penanggung jawab departemen">
                        @error('auditee') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="chk-auditor" class="field-label">Auditor</label>
                        <input id="chk-auditor" type="text" wire:model.live.blur="auditor" class="field" placeholder="Nama auditor">
                        @error('auditor') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <fieldset class="sm:col-span-2">
                        <legend class="field-label">Criteria <span class="font-normal text-slate-600">(bisa lebih dari satu)</span></legend>
                        <ol class="space-y-2">
                            @foreach ($criteria as $index => $value)
                                <li wire:key="criteria-{{ $departmentId }}-{{ $index }}" class="flex items-center gap-2">
                                    <span class="w-6 shrink-0 text-right text-sm font-semibold text-slate-600">{{ $index + 1 }}.</span>
                                    <label for="criteria-{{ $index }}" class="sr-only">Criteria {{ $index + 1 }}</label>
                                    <input id="criteria-{{ $index }}" type="text" wire:model.live.blur="criteria.{{ $index }}" class="field" placeholder="contoh: ISO 9001:2015 Klausul 8.5">
                                    <button type="button" wire:click="removeCriterion({{ $index }})" class="btn btn-icon btn-danger-ghost" aria-label="Hapus criteria {{ $index + 1 }}">
                                        <x-icon name="x" class="size-4" />
                                    </button>
                                </li>
                            @endforeach
                        </ol>
                        @error('criteria.*') <p class="field-error">{{ $message }}</p> @enderror
                        <button type="button" wire:click="addCriterion" class="btn btn-sm btn-add mt-3">
                            <x-icon name="plus" class="size-4" />
                            Tambah criteria
                        </button>
                    </fieldset>
                </div>
            </section>

            {{-- Import / export --}}
            <section class="card flex flex-col p-5 sm:p-6 lg:col-span-5" aria-labelledby="chk-files">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="chk-files" class="flex items-center gap-2 text-base font-bold text-brand-900">
                        <x-icon name="file-xls" class="size-5 text-brand-600" />
                        Import / export Excel
                    </h2>
                    <a href="{{ route('checklist.template') }}" class="btn btn-sm btn-secondary">
                        <x-icon name="download-simple" class="size-4" />
                        Template
                    </a>
                </div>

                <label class="group relative mt-4 flex min-h-36 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-white p-4 text-center transition hover:border-brand-700 hover:bg-slate-50">
                    <input type="file" wire:model="importFile" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" class="absolute inset-0 cursor-pointer opacity-0" aria-describedby="import-help">
                    <span wire:loading.flex wire:target="importFile" class="flex-col items-center gap-2 text-sm font-medium text-brand-700">
                        <x-icon name="arrows-clockwise" class="size-6 animate-spin" />
                        Membaca file...
                    </span>
                    <span wire:loading.remove wire:target="importFile" class="flex flex-col items-center">
                        <x-icon name="upload-simple" class="size-7 text-brand-700 transition-transform duration-300 group-hover:-translate-y-0.5" />
                        <span class="mt-2 text-sm font-semibold text-brand-900">Import file Excel</span>
                        <span id="import-help" class="mt-0.5 text-xs text-slate-600">Klik atau seret file .xlsx sesuai template (maks. 2 MB)</span>
                    </span>
                </label>
                @error('importFile') <p class="field-error">{{ $message }}</p> @enderror

                <div class="mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                    <p class="text-xs font-semibold text-slate-600">Export daftar item departemen ini</p>
                    <a href="{{ route('audits.checklist.export', [$audit, $department]) }}" class="btn btn-sm btn-secondary">
                        <x-icon name="file-xls" class="size-4 text-emerald-700" />
                        Export Excel
                    </a>
                </div>
            </section>
        </div>

        {{-- Items --}}
        <section class="card mt-5 p-4 sm:p-6" aria-labelledby="chk-items">
            <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 id="chk-items" class="flex flex-wrap items-baseline gap-x-3 gap-y-1 text-base font-bold text-brand-900">
                        Daftar item pemeriksaan
                        <span class="text-sm font-medium text-slate-500 tabular-nums">{{ $filledCount }} pertanyaan terisi</span>
                    </h2>
                    <p class="mt-1 text-sm text-slate-600">Tulis pertanyaannya dulu. Catatan dan hasil pemeriksaan diisi saat audit berlangsung.</p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <button type="button" wire:click="addItems(1)" class="btn btn-sm btn-add"><x-icon name="plus" class="size-4" />Tambah item</button>
                    <button type="button" wire:click="addItems(5)" class="btn btn-sm btn-add">+5 item</button>
                    <button type="button" wire:click="clearEmptyItems" class="btn btn-sm btn-secondary"><x-icon name="broom" class="size-4" />Rapikan kosong</button>
                </div>
            </div>

            @include('livewire.audits.partials.items-table', ['withCriteria' => false])
        </section>

        <x-audit.step-nav :audit="$audit" current="checklist" />
    @endif

    @include('livewire.audits.partials.department-modal')
</div>
