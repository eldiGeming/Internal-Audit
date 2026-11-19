@extends('pdf.layout')

@section('content')
    @include('pdf.partials.kop', [
        'kicker' => 'Laporan eksekutif hasil audit internal',
        'title' => 'Kesimpulan Audit Internal',
        'lines' => [
            'Client: <b>'.e($audit->company->name).'</b>, no. audit: <b>'.e($audit->audit_number ?: '-').'</b>, tanggal: <b>'.e($audit->audit_date?->translatedFormat('d F Y') ?? '-').'</b>',
            'Tujuan audit: '.e($audit->objective ?: '-'),
            'Criteria acuan: <b>'.e($criteria).'</b>',
        ],
        'logo' => $logo,
    ])

    <div class="heading"><span class="num">I</span> Rekapitulasi hasil audit seluruh departemen <span class="muted" style="text-transform: none; font-weight: normal;">(kepatuhan keseluruhan {{ $totals['compliance'] }}%)</span></div>
    @include('pdf.partials.recap-table', ['noteHeading' => 'Catatan departemen'])

    <div class="heading"><span class="num" style="background: #b91c1c;">II</span> Daftar CAR &amp; rencana tindakan ({{ $findings->count() }})</div>
    @forelse ($findings as $finding)
        @php
            $item = $finding['item'];
            $action = $finding['action'];
            $severityClass = ['major' => 'major', 'minor' => 'minor', 'observasi' => 'obs'][$finding['severity']->value];
        @endphp
        <div class="section">
            <div class="section-title">
                {{ $finding['number'] }}, {{ $finding['department']->displayName() }}
                <span class="pill {{ $severityClass }} {{ $severityClass }}" style="margin-left: 4px;">{{ strtoupper($finding['severity']->label()) }}</span>
                <span class="pill" style="margin-left: 2px;">{{ ($action?->status ?? \App\Enums\CarStatus::Open)->label() }}</span>
            </div>
            <div class="section-body">
                <div><b>Poin pemeriksaan:</b> {{ $item->question }}</div>
                <div><b>Bukti objektif:</b> {{ $item->evidence ?: '-' }}</div>
                <div><b>Akar masalah:</b> {{ $action?->root_cause ?: '-' }}</div>
                <table style="width: 100%; margin-top: 3px;"><tr>
                    <td style="width: 50%; vertical-align: top; padding-right: 6px;"><b>C. Koreksi:</b> {{ $action?->correction ?: '-' }}<br><span class="muted">PIC {{ $action?->correction_pic ?: '-' }}, target {{ $action?->correction_due_date?->translatedFormat('d M Y') ?? '-' }}</span></td>
                    <td style="width: 50%; vertical-align: top;"><b>D. Pencegahan:</b> {{ $action?->prevention ?: '-' }}<br><span class="muted">PIC {{ $action?->prevention_pic ?: '-' }}, target {{ $action?->prevention_due_date?->translatedFormat('d M Y') ?? '-' }}</span></td>
                </tr></table>
                <div style="margin-top: 3px;"><b>Verifikasi:</b> {{ $action?->verification_note ?: 'Menunggu verifikasi tindak lanjut.' }}</div>
            </div>
        </div>
    @empty
        <p class="muted">Tidak ada temuan CAR pada audit ini.</p>
    @endforelse

    <div class="heading"><span class="num" style="background: #047857;">III</span> Pernyataan kesimpulan akhir &amp; rekomendasi tim auditor</div>
    <div class="section"><div class="section-body pre" style="min-height: 50px;">{{ $audit->final_conclusion ?: '-' }}</div></div>

    <table class="signatures">
        <tr>
            <td>Disusun &amp; dilaporkan oleh (lead auditor),<div class="sign-space"></div><div class="sign-line"></div><span class="muted">Ketua tim auditor internal</span></td>
            <td>Diterima &amp; disahkan oleh (manajemen),<div class="sign-space"></div><div class="sign-line"></div><span class="muted">Management representative / pimpinan</span></td>
        </tr>
    </table>
@endsection
