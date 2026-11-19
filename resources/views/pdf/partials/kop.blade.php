{{-- Expects: $kicker, $title, $lines (list of HTML-safe strings), $logo (data URI or null), optional $carNumber --}}
<table class="kop">
    <tr>
        <td>
            <div class="kicker {{ isset($carNumber) ? 'red' : '' }}">{{ $kicker }}</div>
            <h1>{{ $title }}</h1>
            @foreach ($lines as $line)
                <div class="meta">{!! $line !!}</div>
            @endforeach
        </td>
        @isset($carNumber)
            <td style="width: 100px; text-align: right; padding-right: 8px;">
                <div class="kicker" style="color: #64748b;">Nomor CAR</div>
                <div style="font-size: 12pt; font-weight: bold; color: #fff; background: #0f294a; padding: 3px 7px; display: inline-block;">{{ $carNumber }}</div>
            </td>
        @endisset
        <td style="width: 112px;">
            <table style="border-collapse: collapse;"><tr>
                <td class="logo-box">
                    @if ($logo)
                        <img src="{{ $logo }}" alt="Logo">
                    @else
                        <span class="logo-empty">Logo client</span>
                    @endif
                </td>
            </tr></table>
        </td>
    </tr>
</table>
