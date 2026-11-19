# Audit Internal

Aplikasi web untuk mengelola audit internal: identitas audit, checklist per departemen, daftar periksa, CAR (Corrective Action Request), rekap kepatuhan, dan kesimpulan audit, lengkap dengan export PDF dan dashboard admin.

Acuan: prototype `Requirement_Auditor/Internal_audit.txt` (sama dengan [halaman referensi](https://sites.google.com/view/audit-internal-inheren/full-page-embed)) dan daftar perubahan klien `Requirement_Auditor/Menu.docx`. Bila keduanya berbeda, `Menu.docx` yang dipakai. Folder `Requirement_Auditor` tidak ikut di-commit karena repo ini publik.

## Fitur

| Menu | Isi |
| --- | --- |
| Login | Username **atau** email. Hanya 2 role: Admin dan Auditor. Dibatasi 5 percobaan per menit, akun nonaktif ditolak. |
| Daftar Audit | Auditor melihat audit miliknya, admin melihat semua. Cari PT / no. audit, buat audit baru, hapus audit. |
| 1. Identitas Audit | Client/PT, no. audit, tanggal, tujuan, upload logo, tabel Departemen / Auditee / Auditor. |
| 2. Checklist Audit | Pilih departemen, criteria bisa lebih dari satu, tabel awal hanya berisi pertanyaan, Hasil Pemeriksaan di kolom paling kanan (Sesuai / Tidak Sesuai lalu Major, Minor, Observasi). Import dan export **hanya Excel (.xlsx)** dengan layout template, download template. |
| 3. Daftar Periksa | Lembar formal per departemen (1 departemen = 1 PDF), criteria per item, ringkasan, tanda tangan. |
| 4. CAR | Otomatis dari setiap temuan Tidak Sesuai. Bagian A sampai E, dengan **C Tindakan Koreksi** dan **D Tindakan Pencegahan** dipisah. Logo tampil. Export PDF **per CAR** (tombol di atas tiap lembar) atau semua CAR dalam 1 file. |
| 5. Rekap Audit | Hitung otomatis per departemen (Total, Sesuai, Major, Minor, Obs, % kepatuhan) + baris rekap manual. PDF landscape. |
| 6. Kesimpulan Audit | Pengganti menu Arsip: tabel rekap, semua CAR yang sudah diisi, pernyataan akhir tim auditor. PDF. |
| Admin | Dashboard (jumlah PT, jumlah user, total CAR, rata-rata kepatuhan, **PT hasil audit terbaik**, peringkat), CRUD akun auditor, dokumen CAR per PT (lihat detail dan hapus). |

Semua isian tersimpan otomatis ke server saat kursor pindah dari kolom (indikator "Tersimpan otomatis" di kanan atas). Selama mengetik, isian juga disimpan sebagai **draft di browser** (localStorage, `resources/js/drafts.js`). Bila halaman di-reload, tab tertutup, atau koneksi putus sebelum tersimpan, draft dipulihkan saat halaman dibuka lagi lalu dikirim ke server; indikator berubah menjadi "Draft tersimpan di perangkat" selama ada isian yang baru tersimpan lokal. Draft hanya ada di browser yang sama, dihapus begitu server menerimanya, dan kedaluwarsa setelah 7 hari. Kolom password dan pencarian tidak pernah disimpan.

**Rumus kepatuhan** (sama dengan prototype): `Sesuai / jumlah pertanyaan terisi x 100`, dibulatkan. Pertanyaan yang belum diperiksa tetap dihitung. Nomor CAR berurutan lintas departemen (CAR-001, CAR-002, ...) mengikuti urutan departemen dan item. Peringkat PT memakai audit **terbaru** tiap PT.

## Arsitektur

- **Laravel 13** (PHP 8.3+), **Livewire 4** (class-based component) dengan Alpine bawaan Livewire, **Tailwind CSS 4** lewat Vite.
- **MariaDB/MySQL**. Tabel: `users`, `companies` (PT + logo), `audits`, `audit_departments`, `checklist_items`, `corrective_actions`, `recap_entries`.
- PDF: `barryvdh/laravel-dompdf` (dibuat di server, font DejaVu Sans dengan subsetting, sekitar 30 sampai 50 KB per file).
- Spreadsheet: `spatie/simple-excel` (OpenSpout).
- Ikon: Phosphor (MIT), file SVG disalin ke `resources/svg/ph`, dipanggil dengan `<x-icon name="..." />`. Font Plus Jakarta Sans di-host sendiri lewat plugin font Vite.
- Laravel Boost terpasang (dev) untuk panduan AI: `CLAUDE.md`, `AGENTS.md`, `.claude/skills`, `.mcp.json`.

File kunci:

| Lokasi | Isi |
| --- | --- |
| `app/Support/AuditReport.php` | Satu-satunya sumber angka rekap, kepatuhan, dan nomor CAR (dipakai halaman dan PDF). |
| `app/Support/AuditSteps.php` | Urutan 6 menu audit. |
| `app/Support/ChecklistSpreadsheet.php` | Import/export checklist Excel (.xlsx) dan template. |
| `resources/js/drafts.js` | Draft isian di localStorage agar tidak hilang saat reload. |
| `app/Livewire/Audits/*` | Halaman 6 tahap audit + daftar audit. `AuditStep.php` = base class (otorisasi, data dibatasi per audit). |
| `app/Livewire/Admin/*` | Dashboard admin, akun auditor, register CAR. |
| `app/Policies/AuditPolicy.php` | Admin boleh semua, auditor hanya audit miliknya. |
| `resources/views/livewire/**` | Tampilan halaman. |
| `resources/views/pdf/**` | Template PDF. |
| `resources/css/app.css` | Design token (warna navy `brand-900` = `#0f294a`, radius, komponen tombol/field/tabel). |
| `resources/views/components/{severity,compliance,car-status,stat-strip,empty-state}.blade.php` | Penanda kategori temuan (3 bar), meter kepatuhan, status CAR, baris angka ringkasan, tampilan kosong. |
| `lang/id/validation.php` | Pesan validasi Bahasa Indonesia. |

## Menjalankan di lokal (XAMPP)

Kebutuhan: PHP 8.3+ dengan ekstensi `pdo_mysql, mbstring, gd, zip, intl, fileinfo, openssl`, Composer, Node 20+, MariaDB/MySQL.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# buat database kosong "internal_audit" (utf8mb4) di MariaDB/MySQL, sesuaikan DB_* di .env
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve          # buka http://localhost:8000
```

Saat mengembangkan tampilan, jalankan `npm run dev` (atau `composer run dev`) agar CSS langsung diperbarui.

Akun demo (hanya dibuat di environment selain production):

| Role | Username | Password |
| --- | --- | --- |
| Admin | `admin` | `admin123` |
| Auditor | `auditor` | `auditor123` |

Tombol "Isi cepat akun demo" di halaman login hanya muncul bila `APP_ENV=local`.

Perintah lain:

```bash
php artisan test --compact        # 87 test
vendor/bin/pint --dirty           # rapikan format PHP
php artisan migrate:fresh --seed  # reset database ke data demo (menghapus semua data!)
```

## Deploy ke hosting

Aplikasi ini butuh server PHP (bukan hosting statis). Pilihan umum: shared hosting cPanel dengan PHP 8.3+, VPS, atau Laravel Cloud.

1. Siapkan PHP 8.3+ dengan ekstensi di atas dan database MySQL 8 / MariaDB 10.6+.
2. Jalankan `npm run build` di lokal, lalu upload project (termasuk `public/build`, tanpa `node_modules`). Atau `git clone` di server lalu `composer install --no-dev --optimize-autoloader`.
3. Arahkan document root domain ke folder `public/`.
4. Buat `.env` produksi dari `.env.example`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-klien`, isi `DB_*`, lalu `php artisan key:generate`.
5. Jalankan:
   ```bash
   php artisan migrate --force
   php artisan db:seed --force      # membuat akun admin dengan password acak, dicetak sekali di terminal
   php artisan storage:link
   php artisan optimize
   ```
6. Login sebagai admin, buat akun auditor dari menu **Akun Auditor**.

Di production, seeder **tidak** membuat akun demo dan data contoh.

## Mengubah konten

- Teks halaman: file Blade di `resources/views/livewire/...` (Bahasa Indonesia langsung di template).
- Label hasil, kategori temuan, status CAR: `app/Enums/*.php`.
- Nama dan urutan menu audit: `app/Support/AuditSteps.php`.
- Kalimat catatan otomatis rekap: `AuditReport::autoNote()`.
- Kop dan isi PDF: `resources/views/pdf/*.blade.php`.
- Warna dan gaya: token di `resources/css/app.css` lalu `npm run build`.
- Kolom template Excel: `ChecklistSpreadsheet::HEADER` (kolom dibaca berdasarkan posisi: No, Pertanyaan, Catatan, Hasil, Kategori).

## Penyimpangan dari prototype / referensi (dan alasannya)

1. **Urutan menu** mengikuti `Menu.docx`: 4 = CAR, 5 = Rekap (prototype: 4 Rekap, 5 CAR).
2. **Banyak audit, tersimpan di database.** Prototype hanya satu sesi di localStorage browser. Karena itu "Kosongkan Form", "Isi Data Contoh", dan "Sinkron Sesi PT Aktif" diganti dengan Daftar Audit (buat/hapus audit) dan seeder data demo.
3. **Tombol akun demo** di login hanya tampil di mode lokal, dan pilihan "Auditee" dihapus (`Menu.docx`: hanya 2 role).
4. **Lupa password** menampilkan petunjuk menghubungi admin. Admin mengganti password dari menu Akun Auditor. Reset lewat email belum dibuat karena SMTP belum ada.
5. **Password tidak pernah ditampilkan** di tabel admin (prototype menampilkannya sebagai teks biasa). Minimal 8 karakter (prototype 4).
6. **Logo** hanya PNG/JPG/WEBP maksimal 2 MB (SVG ditolak demi keamanan). Logo melekat ke PT, jadi otomatis dipakai lagi di audit berikutnya untuk PT yang sama.
7. **Import/export checklist hanya Excel (.xlsx)** sesuai permintaan (20 November 2026); CSV dan JSON dihapus. File hasil export bisa langsung diimpor lagi. Format lama `.xls` (Excel 97-2003) ditolak dengan pesan untuk menyimpan ulang sebagai .xlsx. Hasil import **ditambahkan** ke item yang sudah ada, tidak menimpa (prototype menimpa), supaya pekerjaan yang sudah terisi tidak hilang.
8. **PDF dibuat di server**, bukan lewat dialog print browser, sehingga hasilnya sama di semua perangkat. Export CAR = 1 file berisi 1 CAR per halaman.
9. **Rekap selalu terhitung otomatis**, sehingga tombol "Hitung Ulang Otomatis" tidak diperlukan. Catatan per departemen bisa diubah dan dikembalikan ke catatan otomatis.
10. **Criteria per item** bisa diisi di Daftar Periksa. Bila kosong, memakai criteria departemen.
11. **Auditor hanya melihat audit miliknya**, termasuk saran nama PT saat mengetik. Admin melihat semua.
12. **Hapus CAR oleh admin** mengikuti prototype: dokumen CAR dihapus dan itemnya ditandai Sesuai (ada konfirmasi yang menjelaskan hal ini).
13. Tampilan hanya **mode terang** dan hanya **Bahasa Indonesia**. Ini aplikasi internal yang dokumennya untuk dicetak, jadi tidak ada mode gelap atau pengalih EN/ID.
14. UI disesuaikan, tidak meniru referensi 100%: sidebar navigasi, stepper 6 tahap dengan angka jumlah, kartu pemilih temuan CAR, font Plus Jakarta Sans, dan ikon Phosphor.
15. **Tanpa pill/badge kapsul** (20 November 2026). Label berwarna berbentuk kapsul diganti bentuk yang membawa arti sendiri:
    - Kategori temuan: 3 bar naik (Major 3, Minor 2, Observasi 1) plus teks, tetap terbaca tanpa warna.
    - Kepatuhan: angka % plus meter pendek (hijau ≥85%, kuning ≥60%, merah di bawahnya).
    - Status CAR: ikon jam (Open) atau centang (Closed) plus teks.
    - Jumlah (temuan, pertanyaan, CAR): teks biasa di samping judul.
    - Status akun auditor: switch sungguhan (`role="switch"`), karena kolom itu memang tombol on/off.
    - Kartu KPI berikon diganti satu baris angka bergaris pemisah. Lingkaran ikon di tampilan kosong, avatar bulat, dan label kecil huruf kapital di atas judul dokumen dihapus.
    - Pemilih departemen menjadi tab bergaris bawah, stepper menjadi rel dengan garis penanda langkah aktif.
16. **Warna tombol berdasarkan jenis aksi**: navy untuk lanjut ke langkah berikutnya, export, dan simpan; hijau (`.btn-add`, `.btn-add-soft`, `.btn-add-ghost`) untuk semua aksi tambah data (audit, departemen, item, baris, criteria, rekap manual, akun auditor).

## Hasil verifikasi (19 dan 20 November 2026)

- **87 test otomatis lolos** (20 November 2026): login, otorisasi per audit (termasuk ID milik audit lain), perhitungan rekap/kepatuhan/nomor CAR, alur tiap halaman, import Excel (file lain ditolak), export Excel yang bisa diimpor ulang, keempat PDF plus PDF per CAR (item milik audit lain atau item Sesuai ditolak 404), fitur admin, seeder produksi.
- **Draft browser** (Chrome headless, 20 November 2026): mengetik di Kesimpulan, pertanyaan Checklist, dan akar masalah CAR lalu langsung reload tanpa keluar kolom: isian kembali, terkirim ke server, draft terhapus. Simulasi offline: simpan gagal, draft bertahan, setelah online dan reload isian tersimpan.
- **Browser sungguhan** (Chrome headless): login, buat audit lewat modal, autosave saat blur (dicek dengan reload), ubah hasil menjadi Tidak Sesuai lalu CAR muncul, isi CAR tampil di Kesimpulan, upload logo tampil di kop, modal bisa ditutup dengan Escape dan fokus terkunci di dalam modal.
- **Responsif**: semua halaman auditor dan admin di lebar 360, 390, 768, 1024, 1440 tanpa scroll horizontal dan tanpa error console (diulang 20 November 2026 setelah penggantian pill). Tabel lebar (rekap) di-scroll di dalam kotaknya sendiri. Di ponsel, stepper otomatis menggulir ke langkah yang sedang dibuka.
- **Kontras WCAG AA**: semua pasangan warna teks/latar yang dipakai lolos 4.5:1 (terendah 4.76:1 untuk placeholder).
- **PDF**: keempat dokumen dicek visual, nomor halaman "Halaman X dari Y" benar.
- Animasi menghormati `prefers-reduced-motion`.

## Belum teruji / batasan

- Safari/iOS asli dan Firefox belum dicoba (hanya Chrome).
- Belum dideploy ke hosting sungguhan, dan Lighthouse belum diukur.
- Aplikasi butuh JavaScript (Livewire), tanpa JS form tidak berfungsi.
- Dua orang mengedit kolom yang sama pada waktu bersamaan: yang terakhir disimpan yang dipakai. Draft browser yang dipulihkan juga ikut aturan ini (isian lokal yang belum terkirim menang saat dipulihkan).
- Draft hanya di browser dan perangkat yang sama; pindah perangkat sebelum kolom tersimpan tetap kehilangan ketikan itu. Mode private/incognito atau storage yang diblokir: draft tidak tersimpan, autosave server tetap jalan.
- Form di dalam modal (buat audit, departemen baru, akun auditor) tidak memakai draft.
- Database lokal XAMPP adalah MariaDB 10.4 (sudah end-of-life). Untuk produksi gunakan MariaDB 10.6+ atau MySQL 8.
- Lupa password via email belum ada (lihat penyimpangan no. 4).
