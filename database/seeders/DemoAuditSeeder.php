<?php

namespace Database\Seeders;

use App\Enums\CarStatus;
use App\Enums\CheckResult;
use App\Enums\FindingSeverity;
use App\Models\Audit;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Sample audits based on the "Isi Data Contoh" data of the reference prototype.
 */
class DemoAuditSeeder extends Seeder
{
    public function run(): void
    {
        $auditor = User::query()->where('username', 'auditor')->firstOrFail();

        $this->seedAudit($auditor, 'PT Nusantara Manufaktur Sejahtera', 'AUD-INT/2026/014', '2026-11-19', [
            [
                'name' => 'Produksi',
                'auditee' => 'Budi Santoso',
                'criteria' => ['ISO 9001:2015 Klausul 8.5', 'SOP-PRD-01 Pengendalian Proses'],
                'items' => [
                    ['Apakah instruksi kerja mesin tersedia dan dipahami oleh operator di lini produksi?', 'Instruksi kerja terpasang rapi di setiap stasiun kerja dan operator memahami parameter.', CheckResult::Sesuai, null],
                    ['Apakah catatan kalibrasi alat ukur dimensi masih berlaku dan terdokumentasi?', 'Ditemukan 2 unit jangka sorong di Line B melewati batas tanggal kalibrasi ulang.', CheckResult::TidakSesuai, FindingSeverity::Major],
                    ['Apakah area penyimpanan bahan baku terpisah dengan jelas antara status karantina dan lolos QC?', 'Label karantina pada 1 palet belum ditempelkan dengan jelas.', CheckResult::TidakSesuai, FindingSeverity::Minor],
                ],
            ],
            [
                'name' => 'Quality Control',
                'auditee' => 'Rina Marlina',
                'criteria' => ['ISO 9001:2015 Klausul 7.1.5'],
                'items' => [
                    ['Apakah timbangan analitik QC memiliki sertifikat kalibrasi yang masih berlaku?', 'Timbangan QC-TIM-03 melewati batas kalibrasi ulang sejak 1 November 2026.', CheckResult::TidakSesuai, FindingSeverity::Observasi],
                    ['Apakah hasil inspeksi produk akhir dicatat pada formulir FRM-QC-02?', 'Formulir terisi lengkap untuk 3 batch terakhir.', CheckResult::Sesuai, null],
                    ['Apakah produk tidak sesuai diberi label merah dan dipisahkan?', null, null, null],
                ],
            ],
        ], withCar: true);

        $this->seedAudit($auditor, 'PT Sinar Logistik Andalan', 'AUD-INT/2026/015', '2026-11-12', [
            [
                'name' => 'Gudang',
                'auditee' => 'Dewi Kartika',
                'criteria' => ['SOP-WH-03 Penerimaan Barang'],
                'items' => [
                    ['Apakah setiap barang masuk diperiksa terhadap dokumen surat jalan?', 'Pemeriksaan dilakukan dan ditandatangani petugas penerima.', CheckResult::Sesuai, null],
                    ['Apakah kartu stok diperbarui setiap ada mutasi barang?', 'Kartu stok rak A1 sampai A6 terbarui.', CheckResult::Sesuai, null],
                    ['Apakah APAR di area gudang dalam kondisi siap pakai?', 'Satu APAR di dekat dock 2 belum diperiksa bulan ini.', CheckResult::TidakSesuai, FindingSeverity::Observasi],
                ],
            ],
        ], withCar: false);
    }

    /**
     * @param  list<array{name: string, auditee: string, criteria: list<string>, items: list<array{0: string, 1: ?string, 2: ?CheckResult, 3: ?FindingSeverity}>}>  $departments
     */
    private function seedAudit(User $auditor, string $companyName, string $number, string $date, array $departments, bool $withCar): void
    {
        $company = Company::query()->firstOrCreate(['name' => $companyName]);

        $audit = Audit::query()->updateOrCreate(
            ['company_id' => $company->id, 'audit_number' => $number],
            [
                'user_id' => $auditor->id,
                'audit_date' => $date,
                'objective' => 'Memverifikasi kesesuaian implementasi Sistem Manajemen Mutu ISO 9001:2015 dan SOP operasional di seluruh unit kerja.',
            ],
        );

        $audit->departments()->delete();

        foreach ($departments as $position => $data) {
            $department = $audit->departments()->create([
                'position' => $position,
                'name' => $data['name'],
                'auditee' => $data['auditee'],
                'auditor' => $auditor->name,
                'criteria' => $data['criteria'],
            ]);

            foreach ($data['items'] as $itemPosition => [$question, $evidence, $result, $severity]) {
                $item = $department->items()->create([
                    'position' => $itemPosition,
                    'question' => $question,
                    'evidence' => $evidence,
                    'result' => $result,
                    'severity' => $severity,
                ]);

                if ($withCar && $severity === FindingSeverity::Major) {
                    $item->correctiveAction()->create([
                        'root_cause' => 'Jadwal kalibrasi ulang tidak dipantau karena belum ada pengingat otomatis.',
                        'correction' => 'Menarik 2 jangka sorong dari Line B dan mengirimkannya ke laboratorium kalibrasi.',
                        'correction_pic' => $data['auditee'],
                        'correction_due_date' => '2026-11-26',
                        'prevention' => 'Membuat daftar induk alat ukur dengan pengingat 30 hari sebelum jatuh tempo kalibrasi.',
                        'prevention_pic' => 'Kepala Produksi',
                        'prevention_due_date' => '2026-12-10',
                        'status' => CarStatus::Open,
                    ]);
                }
            }
        }
    }
}
