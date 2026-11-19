@extends('pdf.layout')

@section('content')
    @forelse ($findings as $finding)
        @php
            $item = $finding['item'];
            $department = $finding['department'];
            $action = $finding['action'];
            $severityClass = ['major' => 'major', 'minor' => 'minor', 'observasi' => 'obs'][$finding['severity']->value];
        @endphp

        @include('pdf.partials.kop', [
            'kicker' => 'Corrective Action Request (CAR)',
            'title' => 'Dokumen Tindakan Korektif & Pencegahan',
            'lines' => [
                'Client: <b>'.e($audit->company->name).'</b>',
                'No. audit: <b>'.e($audit->audit_number ?: '-').'</b>, tanggal audit: <b>'.e($audit->audit_date?->translatedFormat('d F Y') ?? '-').'</b>',
            ],
            'logo' => $logo,
            'carNumber' => $finding['number'],
        ])

        <table class="info">
            <tr>
                <td style="width: 28%;"><span class="label">Departemen</span><b class="navy">{{ $department->displayName() }}</b></td>
                <td style="width: 24%;"><span class="label">Auditee</span><b>{{ $department->auditee ?: '-' }}</b></td>
                <td style="width: 24%;"><span class="label">Auditor</span><b>{{ $department->auditor ?: '-' }}</b></td>
                <td><span class="label">Kategori temuan</span><span class="pill {{ $severityClass }} {{ $severityClass }}">TIDAK SESUAI ({{ strtoupper($finding['severity']->label()) }})</span></td>
            </tr>
        </table>

        <div class="section">
            <div class="section-title">A. Uraian ketidaksesuaian &amp; bukti objektif (diisi auditor)</div>
            <div class="section-body">
                <div><b>Criteria / standar:</b> {{ $finding['criteria'] }}</div>
                <div><b>Poin pemeriksaan:</b> {{ $item->question }}</div>
                <div><b>Bukti objektif temuan:</b> {{ $item->evidence ?: '-' }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">B. Analisis akar masalah (root cause analysis)</div>
            <div class="section-body pre" style="min-height: 34px;">{{ $action?->root_cause ?: '-' }}</div>
        </div>

        <div class="section">
            <div class="section-title blue">C. Tindakan koreksi (perbaikan langsung atas temuan)</div>
            <div class="section-body">
                <div class="pre" style="min-height: 30px;">{{ $action?->correction ?: '-' }}</div>
                <table style="width: 100%; margin-top: 5px; border-top: 1px solid #e2e8f0;"><tr>
                    <td style="padding-top: 4px;"><span class="muted">PIC:</span> <b>{{ $action?->correction_pic ?: ($department->auditee ?: '-') }}</b></td>
                    <td style="padding-top: 4px;"><span class="muted">Target selesai:</span> <b>{{ $action?->correction_due_date?->translatedFormat('d F Y') ?? '-' }}</b></td>
                </tr></table>
            </div>
        </div>

        <div class="section">
            <div class="section-title green">D. Tindakan pencegahan (mencegah terulangnya ketidaksesuaian)</div>
            <div class="section-body">
                <div class="pre" style="min-height: 30px;">{{ $action?->prevention ?: '-' }}</div>
                <table style="width: 100%; margin-top: 5px; border-top: 1px solid #e2e8f0;"><tr>
                    <td style="padding-top: 4px;"><span class="muted">PIC:</span> <b>{{ $action?->prevention_pic ?: ($department->auditee ?: '-') }}</b></td>
                    <td style="padding-top: 4px;"><span class="muted">Target selesai:</span> <b>{{ $action?->prevention_due_date?->translatedFormat('d F Y') ?? '-' }}</b></td>
                </tr></table>
            </div>
        </div>

        <div class="section">
            <div class="section-title">E. Verifikasi efektivitas &amp; status CAR (diisi auditor)</div>
            <div class="section-body">
                <div>{{ $action?->verification_note ?: 'Menunggu verifikasi tindak lanjut.' }}</div>
                <div style="margin-top: 4px;"><b>Status CAR:</b> <span class="pill">{{ ($action?->status ?? \App\Enums\CarStatus::Open)->label() }}</span></div>
            </div>
        </div>

        <table class="signatures" style="margin-top: 14px;">
            <tr>
                <td>Auditor,<div class="sign-space"></div><b>{{ $department->auditor ?: '' }}</b><div class="sign-line"></div></td>
                <td>Auditee,<div class="sign-space"></div><b>{{ $department->auditee ?: '' }}</b><div class="sign-line"></div></td>
            </tr>
        </table>

        @unless ($loop->last)
            <div class="page-break"></div>
        @endunless
    @empty
        @include('pdf.partials.kop', [
            'kicker' => 'Corrective Action Request (CAR)',
            'title' => 'Dokumen Tindakan Korektif & Pencegahan',
            'lines' => ['Client: <b>'.e($audit->company->name).'</b>'],
            'logo' => $logo,
        ])
        <p class="muted">Tidak ada temuan Tidak Sesuai pada audit ini, sehingga tidak ada CAR yang diterbitkan.</p>
    @endforelse
@endsection
