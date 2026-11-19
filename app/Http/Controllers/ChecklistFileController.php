<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\AuditDepartment;
use App\Support\ChecklistSpreadsheet;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ChecklistFileController extends Controller
{
    /**
     * Blank xlsx template with two example questions, same layout as the reference prototype.
     */
    public function template(): BinaryFileResponse
    {
        $path = ChecklistSpreadsheet::writeTemporary('xlsx', [
            [1, 'Apakah SOP operasional tersedia di area kerja?', '', '', ''],
            [2, 'Apakah catatan inspeksi harian terisi lengkap?', '', '', ''],
        ]);

        return response()->download($path, 'Template_Checklist_Audit.xlsx')->deleteFileAfterSend();
    }

    /**
     * Export the checklist of one department as xlsx, csv or json.
     */
    public function export(Audit $audit, AuditDepartment $department, string $format): BinaryFileResponse|JsonResponse
    {
        $department->load('items');
        $filename = 'Checklist_'.Str::slug($department->displayName(), '_').'.'.$format;

        if ($format === 'json') {
            return response()->json([
                'dept' => $department->name,
                'auditee' => $department->auditee,
                'auditor' => $department->auditor,
                'criteria' => $department->criteriaList(),
                'items' => array_map(fn (array $row): array => [
                    'question' => $row[1],
                    'evidence' => $row[2],
                    'result' => $row[3],
                    'severity' => $row[4],
                ], ChecklistSpreadsheet::rowsFor($department)),
            ], options: JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
        }

        $path = ChecklistSpreadsheet::writeTemporary($format, ChecklistSpreadsheet::rowsFor($department));

        return response()->download($path, $filename)->deleteFileAfterSend();
    }
}
