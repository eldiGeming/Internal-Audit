<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-brand-900">Dokumen CAR per PT</h1>
            <p class="mt-1 text-sm text-slate-600">Lihat detail dokumen CAR (bagian A sampai E) atau hapus temuan CAR per perusahaan.</p>
        </div>
        <div class="w-full sm:w-72">
            <label for="pt-filter" class="field-label">Filter PT</label>
            <select id="pt-filter" wire:model.live="companyId" class="field">
                <option value="">Semua PT</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-6 space-y-5">
        @forelse ($groups as $companyAudits)
            @php $company = $companyAudits->first()['audit']->company; @endphp
            <section class="card overflow-hidden" wire:key="company-{{ $company->id }}" aria-labelledby="company-{{ $company->id }}">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
                    <h2 id="company-{{ $company->id }}" class="flex items-center gap-2 text-base font-extrabold text-brand-900">
                        <x-icon name="buildings" class="size-5 text-brand-600" />
                        {{ $company->name }}
                    </h2>
                    <span class="badge bg-red-50 text-red-800 ring-red-200">{{ $companyAudits->sum(fn ($entry) => $entry['findings']->count()) }} dokumen CAR</span>
                </div>

                @foreach ($companyAudits as $entry)
                    <div wire:key="audit-{{ $entry['audit']->id }}">
                        <div class="flex flex-wrap items-center justify-between gap-2 px-5 pt-4 text-sm">
                            <p class="font-semibold text-slate-700">No. audit {{ $entry['audit']->audit_number ?: '-' }}, {{ $entry['audit']->audit_date?->translatedFormat('d M Y') ?? 'tanpa tanggal' }}</p>
                            <a href="{{ route('audits.car', $entry['audit']) }}" wire:navigate class="inline-flex min-h-9 items-center gap-1 text-sm font-semibold text-brand-700 hover:underline">
                                Buka halaman CAR <x-icon name="arrow-square-out" class="size-4" />
                            </a>
                        </div>
                        <ul class="divide-y divide-slate-100">
                            @foreach ($entry['findings'] as $finding)
                                <li wire:key="finding-{{ $finding['item']->id }}" class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-md bg-red-700 px-2 py-0.5 text-xs font-bold text-white">{{ $finding['number'] }}</span>
                                            <span class="text-sm font-bold text-brand-900">{{ $finding['department']->displayName() }}</span>
                                            <x-severity-badge :severity="$finding['severity']" />
                                        </div>
                                        <p class="mt-1 line-clamp-2 text-sm text-slate-700">{{ $finding['item']->question }}</p>
                                    </div>
                                    <div class="flex shrink-0 gap-2">
                                        <button type="button" wire:click="view({{ $finding['item']->id }})" class="btn btn-sm btn-soft"><x-icon name="eye" class="size-4" />Detail</button>
                                        <button type="button" wire:click="deleteCorrectiveAction({{ $finding['item']->id }})"
                                                wire:confirm="Hapus {{ $finding['number'] }} ({{ $company->name }})? Dokumen CAR dihapus dan item pemeriksaannya ditandai Sesuai."
                                                class="btn btn-sm btn-secondary hover:text-red-700"><x-icon name="trash" class="size-4" />Hapus</button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </section>
        @empty
            <div class="card flex flex-col items-center px-6 py-14 text-center">
                <span class="grid size-14 place-items-center rounded-full bg-emerald-50 text-emerald-700"><x-icon name="check-circle" class="size-7" /></span>
                <p class="mt-4 text-base font-semibold text-slate-800">Tidak ada dokumen CAR</p>
                <p class="mt-1 text-sm text-slate-600">Belum ada temuan Tidak Sesuai{{ $companyId ? ' pada PT ini' : '' }}.</p>
            </div>
        @endforelse
    </div>

    <x-modal show="showDetail" :title="$viewing ? $viewing['audit']->company->name.', '.$viewing['number'] : 'Detail CAR'" icon="file-text" max-width="max-w-3xl">
        @if ($viewing)
            @include('partials.car-summary', ['finding' => $viewing])
            <p class="mt-3 text-xs text-slate-600">Mode pratinjau admin (read-only). Perubahan isi CAR dilakukan oleh auditor di halaman CAR.</p>
        @endif
        <x-slot:footer>
            <button type="button" x-on:click="$wire.showDetail = false" class="btn btn-secondary">Tutup</button>
            @if ($viewing)
                <a href="{{ route('audits.pdf.car', $viewing['audit']) }}" target="_blank" class="btn btn-primary"><x-icon name="file-pdf" class="size-4" />PDF CAR audit ini</a>
            @endif
        </x-slot:footer>
    </x-modal>
</div>
