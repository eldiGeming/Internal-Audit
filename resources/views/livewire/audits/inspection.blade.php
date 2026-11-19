<div>
    <x-audit.header :audit="$audit" current="inspection" :counts="$this->stepCounts">
        <x-slot:actions>
            <x-audit.saving />
        </x-slot:actions>
    </x-audit.header>

    @if ($department === null)
        <div class="card flex flex-col items-center px-6 py-14 text-center">
            <span class="grid size-14 place-items-center rounded-full bg-brand-50 text-brand-700"><x-icon name="file-text" class="size-7" /></span>
            <p class="mt-4 text-base font-semibold text-slate-800">Belum ada lembar daftar periksa</p>
            <p class="mt-1 max-w-md text-sm text-slate-600">Setiap departemen menjadi satu lembar daftar periksa. Tambahkan departemen untuk memulai.</p>
            <button type="button" wire:click="openDepartmentModal" class="btn btn-primary mt-5">
                <x-icon name="plus" class="size-4" />
                Departemen baru
            </button>
        </div>
    @else
        <div class="mb-5 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            @include('livewire.audits.partials.department-picker')
            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="addItems(1)" class="btn btn-sm btn-secondary"><x-icon name="plus" class="size-4" />Tambah baris</button>
                <a href="{{ route('audits.pdf.inspection', [$audit, $department]) }}" target="_blank" class="btn btn-sm btn-primary">
                    <x-icon name="file-pdf" class="size-4" />
                    Download PDF departemen ini
                </a>
            </div>
        </div>

        <article class="sheet p-5 sm:p-8">
            <x-audit.doc-header :company="$audit->company" kicker="Formulir pemeriksaan lapangan" title="Daftar Periksa Audit">
                <p>Client: <strong class="text-slate-900">{{ $audit->company->name }}</strong></p>
                <p>No. audit: <strong class="text-slate-900">{{ $audit->audit_number ?: '-' }}</strong></p>
            </x-audit.doc-header>

            <div class="mt-6 grid gap-4 rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200 ring-inset sm:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label for="p3-dept" class="field-label">Departemen</label>
                    <input id="p3-dept" type="text" wire:model.live.blur="departmentName" class="field font-bold text-brand-900">
                    @error('departmentName') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="p3-auditee" class="field-label">Auditee</label>
                    <input id="p3-auditee" type="text" wire:model.live.blur="auditee" class="field">
                </div>
                <div>
                    <label for="p3-auditor" class="field-label">Auditor</label>
                    <input id="p3-auditor" type="text" wire:model.live.blur="auditor" class="field">
                </div>
                <div>
                    <p class="field-label">Criteria / standar</p>
                    <p class="min-h-10 rounded-lg bg-white px-3 py-2 text-sm text-slate-800 ring-1 ring-slate-200 ring-inset">{{ $department->criteriaText() ?: '-' }}</p>
                    <a href="{{ route('audits.checklist', ['audit' => $audit, 'dept' => $department->id]) }}" wire:navigate class="mt-1 inline-flex min-h-8 items-center text-xs font-semibold text-brand-700 hover:underline">Ubah di Checklist Audit</a>
                </div>
            </div>

            <div class="mt-6">
                @include('livewire.audits.partials.items-table', ['withCriteria' => true])
            </div>

            <dl class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-5">
                @foreach ([
                    ['label' => 'Total item', 'value' => $stats['total'], 'class' => 'bg-slate-50 ring-slate-200 text-brand-900'],
                    ['label' => 'Sesuai', 'value' => $stats['sesuai'], 'class' => 'bg-emerald-50 ring-emerald-200 text-emerald-800'],
                    ['label' => 'Tidak sesuai, Major', 'value' => $stats['major'], 'class' => 'bg-red-50 ring-red-200 text-red-800'],
                    ['label' => 'Tidak sesuai, Minor', 'value' => $stats['minor'], 'class' => 'bg-orange-50 ring-orange-200 text-orange-800'],
                    ['label' => 'Tidak sesuai, Observasi', 'value' => $stats['observasi'], 'class' => 'bg-amber-50 ring-amber-200 text-amber-800'],
                ] as $stat)
                    <div @class(['rounded-xl p-3 ring-1 ring-inset', $stat['class']])>
                        <dt class="text-xs font-semibold">{{ $stat['label'] }}</dt>
                        <dd class="mt-0.5 text-2xl font-extrabold tabular-nums">{{ $stat['value'] }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="mt-10 grid grid-cols-2 gap-6 text-center text-sm text-slate-800">
                <div>
                    <p class="font-semibold">Diperiksa oleh (auditor),</p>
                    <p class="mt-14 font-bold underline decoration-slate-400 underline-offset-4">{{ $auditor ?: '...................................' }}</p>
                    <p class="mt-1 text-xs text-slate-600">Auditor internal</p>
                </div>
                <div>
                    <p class="font-semibold">Diterima oleh (auditee),</p>
                    <p class="mt-14 font-bold underline decoration-slate-400 underline-offset-4">{{ $auditee ?: '...................................' }}</p>
                    <p class="mt-1 text-xs text-slate-600">Perwakilan departemen</p>
                </div>
            </div>
        </article>

        <x-audit.step-nav :audit="$audit" current="inspection" />
    @endif

    @include('livewire.audits.partials.department-modal')
</div>
