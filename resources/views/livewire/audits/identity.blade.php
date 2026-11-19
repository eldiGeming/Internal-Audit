<div>
    <x-audit.header :audit="$audit" current="identity" :counts="$this->stepCounts">
        <x-slot:actions>
            <x-audit.saving />
        </x-slot:actions>
    </x-audit.header>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-12">
        {{-- General information --}}
        <section class="card p-5 sm:p-6 lg:col-span-7" aria-labelledby="general-title">
            <h2 id="general-title" class="flex items-center gap-2 text-base font-bold text-brand-900">
                <x-icon name="identification-card" class="size-5 text-brand-600" />
                Informasi umum audit
            </h2>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="companyName" class="field-label">Client / perusahaan <span class="text-red-600">*</span></label>
                    <input id="companyName" type="text" wire:model.live.blur="companyName" list="company-suggestions"
                           class="field" placeholder="contoh: PT Nusantara Manufaktur Sejahtera" autocomplete="off">
                    <datalist id="company-suggestions">
                        @foreach ($companySuggestions as $suggestion)
                            <option value="{{ $suggestion }}"></option>
                        @endforeach
                    </datalist>
                    @error('companyName') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="auditNumber" class="field-label">No. audit</label>
                    <input id="auditNumber" type="text" wire:model.live.blur="auditNumber" class="field" placeholder="contoh: AUD-INT/2026/001">
                    @error('auditNumber') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="auditDate" class="field-label">Tanggal audit</label>
                    <input id="auditDate" type="date" wire:model.live.change="auditDate" class="field">
                    @error('auditDate') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="objective" class="field-label">Tujuan audit</label>
                    <textarea id="objective" rows="3" wire:model.live.blur="objective" class="field"
                              placeholder="Tuliskan ruang lingkup dan tujuan pelaksanaan audit"></textarea>
                    @error('objective') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- Client logo --}}
        <section class="card flex flex-col p-5 sm:p-6 lg:col-span-5" aria-labelledby="logo-title">
            <div class="flex items-center justify-between gap-3">
                <h2 id="logo-title" class="flex items-center gap-2 text-base font-bold text-brand-900">
                    <x-icon name="image" class="size-5 text-brand-600" />
                    Logo client
                </h2>
                @if ($audit->company->logo_path)
                    <button type="button" wire:click="removeLogo" wire:confirm="Hapus logo client ini?" class="btn btn-sm btn-danger-ghost">
                        <x-icon name="trash" class="size-4" />
                        Hapus logo
                    </button>
                @endif
            </div>
            <p class="mt-1 text-sm text-slate-600">Tampil di kop Daftar Periksa, CAR, Rekap, dan Kesimpulan.</p>

            <label class="group relative mt-4 flex min-h-48 flex-1 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/60 p-5 text-center transition hover:border-brand-500 hover:bg-brand-50/50">
                <input type="file" wire:model="logo" accept="image/png,image/jpeg,image/webp" class="absolute inset-0 cursor-pointer opacity-0" aria-describedby="logo-help">

                <div wire:loading.flex wire:target="logo" class="flex-col items-center gap-2 text-sm font-medium text-brand-700">
                    <x-icon name="arrows-clockwise" class="size-6 animate-spin" />
                    Mengunggah logo...
                </div>

                <div wire:loading.remove wire:target="logo" class="flex flex-col items-center">
                    @if ($audit->company->logoUrl())
                        <img src="{{ $audit->company->logoUrl() }}" alt="Logo {{ $audit->company->name }}" class="max-h-32 max-w-full object-contain" draggable="false">
                        <span class="mt-3 text-xs font-medium text-slate-600 group-hover:text-brand-700">Klik atau seret gambar untuk mengganti</span>
                    @else
                        <x-icon name="cloud-arrow-up" class="size-8 text-brand-700 transition-transform duration-300 group-hover:-translate-y-0.5" />
                        <span class="mt-3 text-sm font-semibold text-slate-800">Unggah logo perusahaan</span>
                        <span class="mt-1 text-xs text-slate-600">Klik untuk memilih atau seret file ke sini</span>
                    @endif
                </div>
            </label>
            <p id="logo-help" class="field-hint">PNG, JPG, atau WEBP. Maksimal 2 MB.</p>
            @error('logo') <p class="field-error">{{ $message }}</p> @enderror
        </section>
    </div>

    {{-- Department schedule --}}
    <section class="card mt-5" aria-labelledby="schedule-title">
        <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div>
                <h2 id="schedule-title" class="flex items-center gap-2 text-base font-bold text-brand-900">
                    <x-icon name="buildings" class="size-5 text-brand-600" />
                    Departemen, auditee &amp; auditor
                </h2>
                <p class="mt-1 text-sm text-slate-600">Setiap departemen otomatis menjadi lembar Checklist, Daftar Periksa, dan baris Rekap.</p>
            </div>
            <button type="button" wire:click="addDepartment" class="btn btn-primary shrink-0">
                <x-icon name="plus" class="size-4" />
                Tambah departemen
            </button>
        </div>

        @if ($departmentRows->isEmpty())
            <x-empty-state icon="buildings" title="Belum ada departemen">
                Tambahkan departemen yang akan diaudit beserta auditee dan auditornya.
            </x-empty-state>
        @else
            <div class="hidden grid-cols-[3rem_minmax(0,1.2fr)_minmax(0,1fr)_minmax(0,1fr)_3.5rem] gap-3 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-bold tracking-wide text-brand-900 uppercase sm:grid sm:px-6">
                <span class="text-center">No</span><span>Departemen</span><span>Auditee</span><span>Auditor</span><span class="sr-only">Aksi</span>
            </div>
            <ol class="divide-y divide-slate-100">
                @foreach ($departmentRows as $row)
                    <li wire:key="dept-{{ $row->id }}" class="grid grid-cols-1 gap-3 px-5 py-4 sm:grid-cols-[3rem_minmax(0,1.2fr)_minmax(0,1fr)_minmax(0,1fr)_3.5rem] sm:items-start sm:px-6">
                        <div class="flex items-center justify-between sm:block sm:pt-2 sm:text-center">
                            <span class="text-sm font-bold text-slate-600"><span class="sm:hidden">Departemen </span>{{ $loop->iteration }}</span>
                            <button type="button" wire:click="removeDepartment({{ $row->id }})"
                                    wire:confirm="Hapus departemen ini beserta {{ $row->items_count }} item checklist dan CAR-nya?"
                                    class="btn btn-icon btn-danger-ghost sm:hidden" aria-label="Hapus departemen {{ $loop->iteration }}">
                                <x-icon name="trash" class="size-4" />
                            </button>
                        </div>
                        <div>
                            <label for="dept-name-{{ $row->id }}" class="field-label sm:sr-only">Departemen</label>
                            <input id="dept-name-{{ $row->id }}" type="text" wire:model.live.blur="departments.{{ $row->id }}.name" class="field" placeholder="contoh: Produksi">
                            @error("departments.{$row->id}.name") <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="dept-auditee-{{ $row->id }}" class="field-label sm:sr-only">Auditee</label>
                            <input id="dept-auditee-{{ $row->id }}" type="text" wire:model.live.blur="departments.{{ $row->id }}.auditee" class="field" placeholder="Penanggung jawab departemen">
                            @error("departments.{$row->id}.auditee") <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="dept-auditor-{{ $row->id }}" class="field-label sm:sr-only">Auditor</label>
                            <input id="dept-auditor-{{ $row->id }}" type="text" wire:model.live.blur="departments.{{ $row->id }}.auditor" class="field" placeholder="Nama auditor">
                            @error("departments.{$row->id}.auditor") <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="hidden justify-center sm:flex">
                            <button type="button" wire:click="removeDepartment({{ $row->id }})"
                                    wire:confirm="Hapus departemen ini beserta {{ $row->items_count }} item checklist dan CAR-nya?"
                                    class="btn btn-icon btn-danger-ghost" aria-label="Hapus departemen {{ $loop->iteration }}">
                                <x-icon name="trash" class="size-4" />
                            </button>
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </section>

    <x-audit.step-nav :audit="$audit" current="identity" />
</div>
