<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\AuditDepartment;
use App\Support\ChecklistSpreadsheet;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ChecklistFileController extends Controller
{
    /**
     * Blank xlsx template with two example questions, same layout as the reference prototype.
     */
    public function template(): BinaryFileResponse
    {
        $path = ChecklistSpreadsheet::writeTemporary([
            [1, 'Apakah SOP operasional tersedia di area kerja?', '', '', ''],
            [2, 'Apakah catatan inspeksi harian terisi lengkap?', '', '', ''],
        ]);

        return response()->download($path, 'Template_Checklist_Audit.xlsx')->deleteFileAfterSend();
    }

    /**
     * Export the checklist of one department as xlsx, in the template layout so it can be imported again.
     */
    public function export(Audit $audit, AuditDepartment $department): BinaryFileResponse
    {
        $department->load('items');

        $path = ChecklistSpreadsheet::writeTemporary(ChecklistSpreadsheet::rowsFor($department));

        return response()
            ->download($path, 'Checklist_'.Str::slug($department->displayName(), '_').'.xlsx')
            ->deleteFileAfterSend();
    }
}
