{{--
    Read-only CAR card. Expects $finding (from AuditReport::findings()).
    Used on the Kesimpulan page and in the admin CAR register.
--}}
@php
    $item = $finding['item'];
    $action = $finding['action'];
    $status = $action?->status ?? \App\Enums\CarStatus::Open;
@endphp

<div class="overflow-hidden rounded-xl ring-1 ring-slate-300">
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 bg-slate-50 px-4 py-2.5">
        <span class="rounded-md bg-red-700 px-2 py-0.5 text-xs font-bold text-white">{{ $finding['number'] }}</span>
        <span class="text-sm font-bold text-brand-900">{{ $finding['department']->displayName() }}</span>
        <x-severity-badge :severity="$finding['severity']" />
        <span @class(['badge ml-auto', 'bg-emerald-50 text-emerald-800 ring-emerald-200' => $status === \App\Enums\CarStatus::Closed, 'bg-amber-50 text-amber-800 ring-amber-200' => $status === \App\Enums\CarStatus::Open])>{{ $status->label() }}</span>
    </div>
    <dl class="grid gap-x-6 gap-y-3 p-4 text-sm sm:grid-cols-2">
        <div class="sm:col-span-2">
            <dt class="font-semibold text-slate-700">Poin pemeriksaan</dt>
            <dd class="text-slate-800">{{ $item->question }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="font-semibold text-slate-700">Bukti objektif</dt>
            <dd class="text-slate-800">{{ $item->evidence ?: '-' }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="font-semibold text-slate-700">B. Akar masalah</dt>
            <dd class="whitespace-pre-line text-slate-800">{{ $action?->root_cause ?: '-' }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-slate-700">C. Tindakan koreksi</dt>
            <dd class="whitespace-pre-line text-slate-800">{{ $action?->correction ?: '-' }}</dd>
            <dd class="mt-1 text-xs text-slate-600">PIC: <strong>{{ $action?->correction_pic ?: '-' }}</strong>, target: <strong>{{ $action?->correction_due_date?->translatedFormat('d M Y') ?? '-' }}</strong></dd>
        </div>
        <div>
            <dt class="font-semibold text-slate-700">D. Tindakan pencegahan</dt>
            <dd class="whitespace-pre-line text-slate-800">{{ $action?->prevention ?: '-' }}</dd>
            <dd class="mt-1 text-xs text-slate-600">PIC: <strong>{{ $action?->prevention_pic ?: '-' }}</strong>, target: <strong>{{ $action?->prevention_due_date?->translatedFormat('d M Y') ?? '-' }}</strong></dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="font-semibold text-slate-700">E. Verifikasi</dt>
            <dd class="text-slate-800">{{ $action?->verification_note ?: 'Menunggu verifikasi tindak lanjut.' }}</dd>
        </div>
    </dl>
</div>
