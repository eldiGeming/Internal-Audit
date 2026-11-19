{{-- Hasil pemeriksaan cell text. Expects $item. --}}
@if ($item->result === \App\Enums\CheckResult::Sesuai)
    <span class="pill ok">SESUAI</span>
@elseif ($item->result === \App\Enums\CheckResult::TidakSesuai)
    @php $severity = $item->findingSeverity(); @endphp
    <span class="pill {{ ['major' => 'major', 'minor' => 'minor', 'observasi' => 'obs'][$severity->value] }}">TIDAK SESUAI ({{ strtoupper($severity->label()) }})</span>
@else
    <span class="muted">Belum diperiksa</span>
@endif
