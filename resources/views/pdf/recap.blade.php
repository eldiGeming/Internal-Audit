@extends('pdf.layout')

@section('content')
    @include('pdf.partials.kop', [
        'kicker' => 'Laporan konsolidasi lintas departemen',
        'title' => 'Rekap Hasil Audit',
        'lines' => [
            'Client: <b>'.e($audit->company->name).'</b>',
            'No. audit: <b>'.e($audit->audit_number ?: '-').'</b>, tanggal: <b>'.e($audit->audit_date?->translatedFormat('d F Y') ?? '-').'</b>',
        ],
        'logo' => $logo,
    ])

    <table class="grid stat" style="margin-bottom: 9px;">
        <tr>
            <td style="width: 25%;"><span class="label">Total departemen</span><span class="v navy">{{ $totals['departments'] }}</span></td>
            <td style="width: 25%;"><span class="label">Total item diperiksa</span><span class="v navy">{{ $totals['total'] }}</span></td>
            <td style="width: 25%;"><span class="label">Total sesuai</span><span class="v ok">{{ $totals['sesuai'] }}</span></td>
            <td style="width: 25%;"><span class="label">Total tidak sesuai</span><span class="v major">{{ $totals['findings'] }}</span></td>
        </tr>
    </table>

    @include('pdf.partials.recap-table', ['noteHeading' => 'Kesimpulan / catatan'])

    <table class="signatures">
        <tr>
            <td>Disusun oleh (lead auditor),<div class="sign-space"></div><div class="sign-line"></div><span class="muted">Lead auditor internal</span></td>
            <td>Diketahui &amp; disetujui oleh (manajemen),<div class="sign-space"></div><div class="sign-line"></div><span class="muted">Management representative / direktur</span></td>
        </tr>
    </table>
@endsection
