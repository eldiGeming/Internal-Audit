<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\AuditDepartment;
use App\Models\ChecklistItem;
use App\Support\AuditReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * PDF exports. Opened inline in the browser so the user can print or save them.
 * Authorization happens in the route ("can:view,audit" + scoped department binding).
 */
class AuditPdfController extends Controller
{
    /**
     * Daftar Periksa: one department, one file.
     */
    public function inspection(Audit $audit, AuditDepartment $department): Response
    {
        $department->load('items');
        $items = $department->items->filter->hasQuestion()->values();

        return $this->render('pdf.inspection', $audit, 'Daftar Periksa Audit', 'Daftar_Periksa_'.$department->displayName(), [
            'department' => $department,
            'items' => $items,
            'stats' => AuditReport::countItems($items),
        ]);
    }

    /**
     * Every CAR of the audit combined into one "Dokumen Tindakan Korektif" file.
     */
    public function correctiveActions(Audit $audit): Response
    {
        return $this->render('pdf.corrective-actions', $audit, 'Dokumen Tindakan Korektif', 'CAR', [
            'findings' => (new AuditReport($audit))->findings(),
        ]);
    }

    /**
     * One CAR as its own file, numbered exactly as in the combined document.
     * The item is scoped to the audit by the route; an item without a finding has no CAR.
     */
    public function correctiveAction(Audit $audit, ChecklistItem $item): Response
    {
        $finding = (new AuditReport($audit))->findings()->first(fn (array $finding): bool => $finding['item']->is($item));

        abort_if($finding === null, 404);

        return $this->render('pdf.corrective-actions', $audit, 'Dokumen Tindakan Korektif '.$finding['number'], $finding['number'], [
            'findings' => collect([$finding]),
        ]);
    }

    public function recap(Audit $audit): Response
    {
        $report = new AuditReport($audit);

        return $this->render('pdf.recap', $audit, 'Rekap Hasil Audit', 'Rekap_Hasil_Audit', [
            'rows' => $report->rows(),
            'totals' => $report->totals(),
        ], 'landscape');
    }

    public function conclusion(Audit $audit): Response
    {
        $report = new AuditReport($audit);

        return $this->render('pdf.conclusion', $audit, 'Kesimpulan Audit Internal', 'Kesimpulan_Audit', [
            'rows' => $report->rows(),
            'totals' => $report->totals(),
            'findings' => $report->findings(),
            'criteria' => $report->criteriaSummary(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function render(string $view, Audit $audit, string $title, string $filePrefix, array $data, string $orientation = 'portrait'): Response
    {
        $audit->loadMissing('company');

        $filename = Str::slug($filePrefix.' '.$audit->company->name.' '.($audit->audit_number ?? ''), '_').'.pdf';

        $pdf = Pdf::loadView($view, [
            ...$data,
            'audit' => $audit,
            'documentTitle' => $title,
            'logo' => $audit->company->logoDataUri(),
        ])
            ->setPaper('a4', $orientation)
            ->setOption('enable_font_subsetting', true);

        // Page numbers are drawn after layout because dompdf cannot resolve counter(pages) in CSS.
        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $canvas->page_text(
            $canvas->get_width() - 118,
            $canvas->get_height() - 33,
            'Halaman {PAGE_NUM} dari {PAGE_COUNT}',
            $dompdf->getFontMetrics()->getFont('DejaVu Sans'),
            7,
            [0.39, 0.45, 0.55],
        );

        return $pdf->stream($filename);
    }
}
