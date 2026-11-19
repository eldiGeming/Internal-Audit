<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.partials.head')
</head>
<body class="min-h-dvh">
    <div class="grid min-h-dvh lg:grid-cols-[minmax(0,1.05fr)_minmax(0,1fr)]">
        {{-- Brand panel --}}
        <section class="relative isolate overflow-hidden bg-brand-900 px-6 py-8 text-white sm:px-10 lg:flex lg:flex-col lg:justify-between lg:px-14 lg:py-12">
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_85%_10%,rgb(89_134_195/0.35),transparent_45%),radial-gradient(circle_at_0%_100%,rgb(59_104_171/0.3),transparent_50%)]"></div>
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 opacity-[0.07] [background-image:radial-gradient(#fff_1px,transparent_1px)] [background-size:24px_24px]"></div>

            <div>
                <div class="flex items-center gap-3.5">
                    <span class="grid size-12 place-items-center rounded-xl bg-white/10 ring-1 ring-white/20">
                        <x-icon name="shield-check" class="size-7" />
                    </span>
                    <span class="leading-tight">
                        <span class="block text-lg font-extrabold tracking-wide">AUDIT INTERNAL</span>
                        <span class="block text-sm text-brand-200">Sistem Manajemen &amp; Dokumentasi Audit</span>
                    </span>
                </div>

                <h1 class="mt-8 max-w-xl text-2xl leading-snug font-bold text-balance sm:text-3xl lg:mt-14">
                    Transparansi, akuntabilitas, dan integritas dalam tata kelola sistem manajemen.
                </h1>
                <p class="mt-3 max-w-lg text-sm leading-relaxed text-brand-100/90 sm:text-[15px]">
                    Rencanakan audit, periksa kepatuhan tiap departemen, dan pantau tindakan korektif dalam satu alur kerja.
                </p>

                <ul class="mt-8 hidden max-w-lg space-y-3 sm:block">
                    @foreach ([
                        ['icon' => 'list-checks', 'title' => 'Checklist & jadwal audit', 'body' => 'Susun daftar periksa per departemen, lengkap dengan import dan export spreadsheet.'],
                        ['icon' => 'chart-bar', 'title' => 'Rekap kepatuhan otomatis', 'body' => 'Persentase kesesuaian dan jumlah temuan dihitung langsung dari hasil pemeriksaan.'],
                        ['icon' => 'warning-octagon', 'title' => 'Pelacakan CAR', 'body' => 'Setiap temuan Tidak Sesuai menjadi dokumen tindakan korektif dan pencegahan.'],
                    ] as $feature)
                        <li class="group flex items-start gap-3.5 rounded-xl bg-white/[0.06] p-3.5 ring-1 ring-white/10 transition hover:bg-white/[0.09]">
                            <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-white/10 text-brand-100 ring-1 ring-white/15 transition-transform duration-300 group-hover:-rotate-6">
                                <x-icon :name="$feature['icon']" class="size-5" />
                            </span>
                            <span>
                                <span class="block text-sm font-bold">{{ $feature['title'] }}</span>
                                <span class="mt-0.5 block text-[13px] leading-relaxed text-brand-100/85">{{ $feature['body'] }}</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="mt-10 hidden text-xs text-brand-200/80 lg:block">&copy; {{ now()->year }} Audit Internal. Hak cipta dilindungi.</p>
        </section>

        {{-- Form panel --}}
        <section class="flex items-center justify-center px-4 py-10 sm:px-8">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </section>
    </div>

    <x-toasts />
</body>
</html>
