<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-brand-900">{{ $isAdmin ? 'Semua audit' : 'Daftar audit' }}</h1>
            <p class="mt-1 text-sm text-slate-600">
                {{ $isAdmin ? 'Seluruh audit dari semua auditor dan perusahaan.' : 'Audit yang Anda kelola. Buka salah satu untuk melanjutkan 6 tahap audit.' }}
            </p>
        </div>
        <button type="button" wire:click="openCreateModal" class="btn btn-add group">
            <x-icon name="plus" class="size-4 transition-transform duration-300 group-hover:rotate-90" />
            Audit baru
        </button>
    </div>

    <div class="card mt-6">
        <div class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full sm:max-w-sm">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-500" />
                <label for="search" class="sr-only">Cari audit</label>
                <input id="search" type="search" wire:model.live.debounce.350ms="search" class="field pl-9" placeholder="Cari nama PT atau no. audit">
            </div>
            <p class="text-sm text-slate-600">{{ $audits->total() }} audit</p>
        </div>

        @if ($audits->isEmpty())
            @if ($search !== '')
                <x-empty-state icon="magnifying-glass" :title="'Tidak ada audit yang cocok dengan “'.$search.'”'">
                    Periksa ejaan nama PT atau no. audit.
                </x-empty-state>
            @else
                <x-empty-state icon="folder-open" title="Belum ada audit">
                    Mulai audit baru, lalu isi identitas, checklist, dan hasil pemeriksaan setiap departemen.
                    <x-slot:actions>
                        <button type="button" wire:click="openCreateModal" class="btn btn-add">
                            <x-icon name="plus" class="size-4" />
                            Audit baru
                        </button>
                    </x-slot:actions>
                </x-empty-state>
            @endif
        @else
            {{-- Desktop table --}}
            <div class="relative hidden overflow-x-auto md:block">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Perusahaan</th>
                            <th>No. audit</th>
                            <th>Tanggal</th>
                            @if ($isAdmin) <th>Auditor</th> @endif
                            <th class="text-center">Dept</th>
                            <th class="text-center">Item</th>
                            <th class="text-center">CAR</th>
                            <th class="text-center">Kepatuhan</th>
                            <th class="text-right"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($audits as $audit)
                            <tr wire:key="audit-{{ $audit->id }}" class="transition hover:bg-slate-50">
                                <td>
                                    <a href="{{ route('audits.identity', $audit) }}" wire:navigate class="font-bold text-brand-900 hover:underline">{{ $audit->company->name }}</a>
                                    <p class="text-xs text-slate-600">Diubah {{ $audit->updated_at->diffForHumans() }}</p>
                                </td>
                                <td class="text-slate-700">{{ $audit->audit_number ?: '-' }}</td>
                                <td class="whitespace-nowrap text-slate-700">{{ $audit->audit_date?->translatedFormat('d M Y') ?? '-' }}</td>
                                @if ($isAdmin) <td class="text-slate-700">{{ $audit->user?->name ?? 'Akun dihapus' }}</td> @endif
                                <td class="text-center tabular-nums">{{ $audit->departments_count }}</td>
                                <td class="text-center tabular-nums">{{ $audit->items_total }}</td>
                                <td class="text-center tabular-nums">
                                    <span @class(['font-bold', 'text-red-700' => $audit->items_findings > 0, 'text-slate-500' => $audit->items_findings === 0])>{{ $audit->items_findings }}</span>
                                </td>
                                <td class="text-center"><x-compliance :value="$audit->compliance()" :empty="$audit->items_total === 0" /></td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('audits.identity', $audit) }}" wire:navigate class="btn btn-sm btn-secondary group">
                                            Buka
                                            <x-icon name="arrow-right" class="size-3.5 transition-transform duration-200 group-hover:translate-x-0.5" />
                                        </a>
                                        <button type="button" wire:click="deleteAudit({{ $audit->id }})"
                                                wire:confirm="Hapus audit {{ $audit->company->name }} ({{ $audit->displayTitle() }}) beserta seluruh checklist dan CAR-nya? Tindakan ini tidak bisa dibatalkan."
                                                class="btn btn-sm btn-icon btn-danger-ghost" aria-label="Hapus audit {{ $audit->company->name }}">
                                            <x-icon name="trash" class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <ul class="divide-y divide-slate-100 md:hidden">
                @foreach ($audits as $audit)
                    <li wire:key="audit-m-{{ $audit->id }}" class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <a href="{{ route('audits.identity', $audit) }}" wire:navigate class="font-bold text-brand-900">{{ $audit->company->name }}</a>
                                <p class="mt-0.5 text-sm text-slate-600">{{ $audit->audit_number ?: 'Tanpa no. audit' }}, {{ $audit->audit_date?->translatedFormat('d M Y') ?? 'tanpa tanggal' }}</p>
                                @if ($isAdmin) <p class="text-sm text-slate-600">{{ $audit->user?->name ?? 'Akun dihapus' }}</p> @endif
                            </div>
                            <x-compliance :value="$audit->compliance()" :empty="$audit->items_total === 0" />
                        </div>
                        <dl class="mt-3 grid grid-cols-3 gap-2 text-center text-xs">
                            <div class="rounded-lg bg-slate-50 py-2"><dt class="text-slate-600">Dept</dt><dd class="font-bold tabular-nums">{{ $audit->departments_count }}</dd></div>
                            <div class="rounded-lg bg-slate-50 py-2"><dt class="text-slate-600">Item</dt><dd class="font-bold tabular-nums">{{ $audit->items_total }}</dd></div>
                            <div class="rounded-lg bg-slate-50 py-2"><dt class="text-slate-600">CAR</dt><dd @class(['font-bold tabular-nums', 'text-red-700' => $audit->items_findings > 0])>{{ $audit->items_findings }}</dd></div>
                        </dl>
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('audits.identity', $audit) }}" wire:navigate class="btn btn-sm btn-secondary flex-1">Buka audit</a>
                            <button type="button" wire:click="deleteAudit({{ $audit->id }})"
                                    wire:confirm="Hapus audit {{ $audit->company->name }} ({{ $audit->displayTitle() }}) beserta seluruh checklist dan CAR-nya?"
                                    class="btn btn-sm btn-icon btn-secondary" aria-label="Hapus audit {{ $audit->company->name }}">
                                <x-icon name="trash" class="size-4" />
                            </button>
                        </div>
                    </li>
                @endforeach
            </ul>

            @if ($audits->hasPages())
                <div class="border-t border-slate-100 p-4">{{ $audits->links() }}</div>
            @endif
        @endif
    </div>

    <x-modal show="showCreateModal" title="Audit baru" icon="clipboard-text">
        <form wire:submit="createAudit" id="create-audit-form" class="space-y-4">
            <div>
                <label for="new-company" class="field-label">Client / perusahaan <span class="text-red-600">*</span></label>
                <input id="new-company" type="text" wire:model="companyName" list="create-company-suggestions" class="field" placeholder="contoh: PT Nusantara Manufaktur Sejahtera" autocomplete="off">
                <datalist id="create-company-suggestions">
                    @foreach ($companySuggestions as $suggestion)
                        <option value="{{ $suggestion }}"></option>
                    @endforeach
                </datalist>
                @error('companyName') <p class="field-error">{{ $message }}</p> @enderror
                <p class="field-hint">Pilih dari daftar bila perusahaan sudah pernah diaudit.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="new-number" class="field-label">No. audit</label>
                    <input id="new-number" type="text" wire:model="auditNumber" class="field" placeholder="AUD-INT/2026/001">
                    @error('auditNumber') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="new-date" class="field-label">Tanggal audit</label>
                    <input id="new-date" type="date" wire:model="auditDate" class="field">
                    @error('auditDate') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </form>
        <x-slot:footer>
            <button type="button" x-on:click="$wire.showCreateModal = false" class="btn btn-secondary">Batal</button>
            <button type="submit" form="create-audit-form" class="btn btn-add" wire:loading.attr="disabled" wire:target="createAudit">
                Buat audit
                <x-icon name="arrow-right" class="size-4" />
            </button>
        </x-slot:footer>
    </x-modal>
</div>
