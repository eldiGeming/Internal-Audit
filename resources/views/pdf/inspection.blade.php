@extends('pdf.layout')

@section('content')
    @include('pdf.partials.kop', [
        'kicker' => 'Formulir pemeriksaan lapangan',
        'title' => 'Daftar Periksa Audit',
        'lines' => [
            'Client: <b>'.e($audit->company->name).'</b>',
            'No. audit: <b>'.e($audit->audit_number ?: '-').'</b>, tanggal: <b>'.e($audit->audit_date?->translatedFormat('d F Y') ?? '-').'</b>',
        ],
        'logo' => $logo,
    ])

    <table class="info">
        <tr>
            <td style="width: 25%;"><span class="label">Departemen</span><b class="navy">{{ $department->displayName() }}</b></td>
            <td style="width: 22%;"><span class="label">Auditee</span><b>{{ $department->auditee ?: '-' }}</b></td>
            <td style="width: 22%;"><span class="label">Auditor</span><b>{{ $department->auditor ?: '-' }}</b></td>
            <td><span class="label">Criteria / standar</span><b>{{ $department->criteriaText() ?: '-' }}</b></td>
        </tr>
    </table>

    <table class="grid">
        <thead>
            <tr>
                <th class="c" style="width: 26px;">No</th>
                <th>Item pemeriksaan / pertanyaan audit</th>
                <th style="width: 96px;">Criteria</th>
                <th style="width: 150px;">Bukti objektif / catatan</th>
                <th class="c" style="width: 100px;">Hasil pemeriksaan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                <tr>
                    <td class="c b">{{ $loop->iteration }}</td>
                    <td>{{ $item->question }}</td>
                    <td>{{ $item->effectiveCriteria($department) ?: '-' }}</td>
                    <td>{{ $item->evidence ?: '-' }}</td>
                    <td class="c">@include('pdf.partials.result', ['item' => $item])</td>
                </tr>
            @empty
                <tr><td colspan="5" class="c muted">Belum ada item pemeriksaan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="grid stat" style="margin-top: 9px;">
        <tr>
            <td><span class="label">Total item</span><span class="v navy">{{ $stats['total'] }}</span></td>
            <td><span class="label">Sesuai</span><span class="v ok">{{ $stats['sesuai'] }}</span></td>
            <td><span class="label">Tidak sesuai, Major</span><span class="v major">{{ $stats['major'] }}</span></td>
            <td><span class="label">Tidak sesuai, Minor</span><span class="v minor">{{ $stats['minor'] }}</span></td>
            <td><span class="label">Tidak sesuai, Observasi</span><span class="v obs">{{ $stats['observasi'] }}</span></td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td>Diperiksa oleh (auditor),<div class="sign-space"></div><b>{{ $department->auditor ?: '' }}</b><div class="sign-line"></div><span class="muted">Auditor internal</span></td>
            <td>Diterima oleh (auditee),<div class="sign-space"></div><b>{{ $department->auditee ?: '' }}</b><div class="sign-line"></div><span class="muted">Perwakilan departemen</span></td>
        </tr>
    </table>
@endsection
