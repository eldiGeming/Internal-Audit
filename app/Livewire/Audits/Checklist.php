<?php

namespace App\Livewire\Audits;

use App\Livewire\Audits\Concerns\CreatesDepartments;
use App\Livewire\Audits\Concerns\EditsChecklistItems;
use App\Livewire\Audits\Concerns\ManagesSelectedDepartment;
use App\Support\ChecklistSpreadsheet;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Throwable;

/**
 * Step 2: prepare the question list per department (multi-criteria, Excel import/export).
 * Per Menu.docx the table starts empty except for the questions; "Hasil Pemeriksaan" is the last column.
 */
#[Title('Checklist Audit')]
class Checklist extends AuditStep
{
    use CreatesDepartments, EditsChecklistItems, ManagesSelectedDepartment, WithFileUploads;

    /** @var TemporaryUploadedFile|null */
    public $importFile = null;

    protected function mountStep(): void
    {
        $this->initDepartment();
    }

    public function updatedImportFile(): void
    {
        $this->authorizeEdit();
        $this->validate(
            ['importFile' => ['required', 'file', 'max:2048', 'extensions:xlsx']],
            ['importFile.extensions' => 'Import hanya menerima file Excel .xlsx. File .xls lama: buka di Excel lalu simpan ulang sebagai .xlsx.'],
            ['importFile' => 'file checklist'],
        );

        $department = $this->selectedDepartment();

        if ($department === null) {
            return;
        }

        try {
            $rows = ChecklistSpreadsheet::parse($this->importFile->getRealPath());
        } catch (Throwable $e) {
            report($e);
            $this->addError('importFile', 'File Excel tidak bisa dibaca. Unduh template, isi, lalu import lagi.');

            return;
        } finally {
            $this->importFile = null;
        }

        if ($rows === []) {
            $this->addError('importFile', 'Tidak ditemukan baris pertanyaan pada file. Isi kolom ke-2 (Pertanyaan) mulai baris ke-2.');

            return;
        }

        // Imported rows are appended; empty placeholder rows are dropped first so nothing filled in is lost.
        $department->items()
            ->where(fn ($q) => $q->whereNull('question')->orWhere('question', ''))
            ->where(fn ($q) => $q->whereNull('evidence')->orWhere('evidence', ''))
            ->whereNull('result')
            ->delete();

        $position = (int) $department->items()->max('position');

        foreach ($rows as $row) {
            $department->items()->create([...$row, 'position' => ++$position]);
        }

        $this->loadItems();
        $this->touchAudit();
        $this->toast(count($rows).' item berhasil diimpor ke '.$department->displayName().'.');
    }

    public function render(): View
    {
        $department = $this->selectedDepartment();

        return view('livewire.audits.checklist', [
            'department' => $department,
            'departmentOptions' => $this->departmentOptions(),
            'filledCount' => $department?->items->filter->hasQuestion()->count() ?? 0,
        ]);
    }
}
