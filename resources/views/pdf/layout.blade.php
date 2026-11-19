<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $documentTitle }}</title>
    <style>
        @page { margin: 15mm 13mm 17mm 13mm; }
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 8.6pt; color: #1e293b; line-height: 1.38; margin: 0; }
        .footer { position: fixed; bottom: -11mm; left: 0; right: 0; font-size: 7pt; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 3px; }
        .kop { width: 100%; border-bottom: 2px solid #0f294a; margin-bottom: 10px; }
        .kop td { vertical-align: middle; padding-bottom: 7px; }
        .kicker { font-size: 7pt; font-weight: bold; letter-spacing: 1.2px; color: #2c5291; text-transform: uppercase; }
        .kicker.red { color: #b91c1c; }
        h1 { font-size: 14pt; color: #0f294a; margin: 2px 0 3px; text-transform: uppercase; letter-spacing: 0.4px; }
        .meta { font-size: 8pt; color: #334155; }
        .logo-box { width: 112px; height: 52px; border: 1px solid #cbd5e1; text-align: center; vertical-align: middle; background: #f8fafc; }
        .logo-box img { max-width: 104px; max-height: 46px; }
        .logo-empty { font-size: 7pt; color: #94a3b8; font-style: italic; }
        table.grid { width: 100%; border-collapse: collapse; }
        table.grid th { background: #eef2f7; color: #0f294a; font-size: 7pt; text-transform: uppercase; letter-spacing: 0.3px; border: 1px solid #94a3b8; padding: 4px 5px; text-align: left; }
        table.grid td { border: 1px solid #94a3b8; padding: 4px 5px; vertical-align: top; }
        table.grid tfoot td { background: #eef2f7; font-weight: bold; color: #0f294a; }
        .info { width: 100%; border-collapse: collapse; background: #f8fafc; border: 1px solid #cbd5e1; margin-bottom: 9px; }
        .info td { padding: 5px 7px; vertical-align: top; font-size: 8pt; }
        .label { color: #64748b; font-size: 7pt; display: block; }
        .c { text-align: center; }
        .r { text-align: right; }
        .b { font-weight: bold; }
        .navy { color: #0f294a; }
        .ok { color: #047857; }
        .major { color: #b91c1c; }
        .minor { color: #c2410c; }
        .obs { color: #b45309; }
        .muted { color: #64748b; }
        /* Square marker with a severity bar on the left, same language as the screen (no rounded pills). */
        .pill { display: inline-block; padding: 1px 5px; border: 1px solid #cbd5e1; border-left: 3px solid #64748b; border-radius: 1px; font-size: 7pt; font-weight: bold; }
        .pill.major { border-left-color: #b91c1c; }
        .pill.minor { border-left-color: #c2410c; }
        .pill.obs { border-left-color: #b45309; }
        .pill.ok { border-left-color: #047857; }
        .section { border: 1px solid #94a3b8; margin-bottom: 7px; page-break-inside: avoid; }
        .section-title { background: #eef2f7; border-bottom: 1px solid #94a3b8; padding: 4px 7px; font-size: 7.4pt; font-weight: bold; color: #0f294a; text-transform: uppercase; }
        .section-title.blue { background: #e7eef8; }
        .section-title.green { background: #e8f6ef; }
        .section-body { padding: 6px 7px; }
        .pre { white-space: pre-line; }
        .heading { font-size: 9.5pt; font-weight: bold; color: #0f294a; text-transform: uppercase; margin: 12px 0 5px; }
        .num { background: #0f294a; color: #fff; font-size: 8pt; font-weight: bold; padding: 1px 6px; margin-right: 5px; }
        .signatures { width: 100%; margin-top: 24px; page-break-inside: avoid; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; font-size: 8.4pt; }
        .sign-space { height: 52px; }
        .sign-line { border-bottom: 1px solid #334155; width: 170px; margin: 0 auto; }
        .page-break { page-break-after: always; }
        .stat td { border: 1px solid #cbd5e1; padding: 5px 6px; width: 20%; }
        .stat .v { font-size: 12pt; font-weight: bold; }
    </style>
</head>
<body>
    <div class="footer">
        <table style="width: 100%;"><tr>
            <td>{{ $documentTitle }}, {{ $audit->company->name }}</td>
            <td class="r"></td>
        </tr></table>
    </div>

    @yield('content')
</body>
</html>
