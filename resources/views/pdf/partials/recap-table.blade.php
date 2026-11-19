{{-- Expects $rows, $totals, $noteHeading --}}
<table class="grid">
    <thead>
        <tr>
            <th class="c" style="width: 22px;">No</th>
            <th>Departemen</th>
            <th>Auditee</th>
            <th>Auditor</th>
            <th class="c" style="width: 34px;">Total</th>
            <th class="c ok" style="width: 40px;">Sesuai</th>
            <th class="c major" style="width: 36px;">Major</th>
            <th class="c minor" style="width: 36px;">Minor</th>
            <th class="c obs" style="width: 30px;">Obs</th>
            <th class="c" style="width: 54px;">Kepatuhan</th>
            <th>{{ $noteHeading }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td class="c b">{{ $loop->iteration }}</td>
                <td class="b navy">{{ $row['department'] }}</td>
                <td>{{ $row['auditee'] }}</td>
                <td>{{ $row['auditor'] }}</td>
                <td class="c b">{{ $row['total'] }}</td>
                <td class="c b ok">{{ $row['sesuai'] }}</td>
                <td class="c b major">{{ $row['major'] }}</td>
                <td class="c b minor">{{ $row['minor'] }}</td>
                <td class="c b obs">{{ $row['observasi'] }}</td>
                <td class="c b">{{ $row['compliance'] }}%</td>
                <td>{{ $row['note'] ?: $row['auto_note'] }}</td>
            </tr>
        @empty
            <tr><td colspan="11" class="c muted">Belum ada data rekap.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="r">TOTAL KESELURUHAN</td>
            <td class="c">{{ $totals['total'] }}</td>
            <td class="c ok">{{ $totals['sesuai'] }}</td>
            <td class="c major">{{ $totals['major'] }}</td>
            <td class="c minor">{{ $totals['minor'] }}</td>
            <td class="c obs">{{ $totals['observasi'] }}</td>
            <td class="c">{{ $totals['compliance'] }}%</td>
            <td class="muted">Konsolidasi seluruh departemen</td>
        </tr>
    </tfoot>
</table>
