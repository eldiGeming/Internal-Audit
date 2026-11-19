<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.partials.head')
</head>
<body class="min-h-dvh">
    <div class="grid min-h-dvh lg:grid-cols-[minmax(0,1.05fr)_minmax(0,1fr)]">
        {{-- Brand panel --}}
        <section class="bg-brand-900 px-6 py-8 text-white sm:px-10 lg:flex lg:flex-col lg:justify-between lg:px-14 lg:py-12">

            <div>
                <div class="flex items-center gap-3.5">
                    <span class="grid size-12 place-items-center rounded-xl bg-white text-brand-900">
                        <x-icon name="shield-check-fill" class="size-7" />
                    </span>
                    <span class="leading-tight">
                        <span class="block text-lg font-extrabold tracking-wide">AUDIT INTERNAL</span>
                        <span class="block text-sm text-brand-200">Sistem Manajemen &amp; Dokumentasi Audit</span>
                    </span>
                </div>

                <h1 class="mt-8 max-w-xl text-2xl leading-snug font-bold text-balance sm:text-3xl lg:mt-14">
                    Transparansi, akuntabilitas, dan integritas dalam tata kelola sistem manajemen.
                </h1>
                <p class="mt-3 max-w-lg text-sm leading-relaxed text-brand-100 sm:text-[15px]">
                    Rencanakan audit, periksa kepatuhan tiap departemen, dan pantau tindakan korektif dalam satu alur kerja.
                </p>

                <ul class="mt-8 hidden max-w-lg divide-y divide-brand-700 border-y border-brand-700 sm:block">
                    @foreach ([
                        ['icon' => 'list-checks', 'title' => 'Checklist & jadwal audit', 'body' => 'Susun daftar periksa per departemen, lengkap dengan import dan export Excel.'],
                        ['icon' => 'chart-bar', 'title' => 'Rekap kepatuhan otomatis', 'body' => 'Persentase kesesuaian dan jumlah temuan dihitung langsung dari hasil pemeriksaan.'],
                        ['icon' => 'warning-octagon', 'title' => 'Pelacakan CAR', 'body' => 'Setiap temuan Tidak Sesuai menjadi dokumen tindakan korektif dan pencegahan.'],
                    ] as $feature)
                        <li class="group flex items-start gap-3.5 py-3.5">
                            <x-icon :name="$feature['icon']" class="mt-0.5 size-6 shrink-0 text-brand-200 transition-transform duration-300 group-hover:-rotate-6" />
                            <span>
                                <span class="block text-sm font-bold">{{ $feature['title'] }}</span>
                                <span class="mt-0.5 block text-[13px] leading-relaxed text-brand-100">{{ $feature['body'] }}</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="mt-10 hidden text-xs text-brand-200 lg:block">&copy; {{ now()->year }} Audit Internal. Hak cipta dilindungi.</p>
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
