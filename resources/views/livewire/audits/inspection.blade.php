<div data-draft-scope="audit-{{ $audit->id }}:dept-{{ $departmentId }}">
    <x-audit.header :audit="$audit" current="inspection" :counts="$this->stepCounts">
        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-3">
                <x-audit.saving />
                @if ($department)
                    <a href="{{ route('audits.pdf.inspection', [$audit, $department]) }}" target="_blank" class="btn btn-primary">
                        <x-icon name="file-pdf" class="size-4" />
                        PDF departemen ini
                    </a>
                @endif
            </div>
        </x-slot:actions>
    </x-audit.header>

    @if ($department === null)
        <x-empty-state icon="file-text" title="Belum ada lembar daftar periksa" class="card">
            Setiap departemen menjadi satu lembar daftar periksa. Tambahkan departemen untuk memulai.
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

        <article class="sheet p-5 sm:p-8">
            <x-audit.doc-header :company="$audit->company" title="Daftar Periksa Audit">
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
                @if ($department->items->isNotEmpty())
                    <button type="button" wire:click="addItems(1)" class="btn btn-sm btn-add-ghost mt-2">
                        <x-icon name="plus" class="size-4" />
                        Tambah baris
                    </button>
                @endif
            </div>

            <x-stat-strip class="mt-6 sm:grid-cols-5" :stats="[
                ['label' => 'Total item', 'value' => $stats['total']],
                ['label' => 'Sesuai', 'value' => $stats['sesuai'], 'tone' => 'text-emerald-700'],
                ['severity' => \App\Enums\FindingSeverity::Major, 'value' => $stats['major'], 'tone' => 'text-red-700'],
                ['severity' => \App\Enums\FindingSeverity::Minor, 'value' => $stats['minor'], 'tone' => 'text-orange-700'],
                ['severity' => \App\Enums\FindingSeverity::Observasi, 'value' => $stats['observasi'], 'tone' => 'text-amber-700'],
            ]" />

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
