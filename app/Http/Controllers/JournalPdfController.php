<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Services\JournalEvaluationBreakdown;
use App\Support\DocumentBranding;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class JournalPdfController extends Controller
{
    /**
     * Certificado del sello editorial en PDF.
     * Solo descargable si la revista está certificada y el sello vigente.
     */
    public function certificate(Journal $journal, DocumentBranding $branding): Response
    {
        $this->authorizeJournalAccess($journal);

        abort_unless(
            $journal->status === 'certified'
            && $journal->seal_status === 'active'
            && $journal->seal_expires_at
            && $journal->seal_expires_at->isFuture(),
            404,
            __('Certificate not available — journal is not currently certified.')
        );

        $pdf = Pdf::loadView('pdf.certificate', [
            'journal' => $journal,
            'brand' => $branding->assemble($journal),
            'qr' => $branding->qrForJournal($journal),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($this->filename($journal, 'certificate'));
    }

    /**
     * Informe de evaluación completo en PDF (5 categorías + 18 indicadores).
     * Disponible para cualquier revista ya evaluada (evaluated_at presente).
     */
    public function report(Journal $journal, JournalEvaluationBreakdown $breakdownService, DocumentBranding $branding): Response
    {
        $this->authorizeJournalAccess($journal);

        abort_unless(
            $journal->evaluated_at !== null,
            404,
            __('Evaluation report not available — journal has not been evaluated yet.')
        );

        $pdf = Pdf::loadView('pdf.report', [
            'journal' => $journal,
            'breakdown' => $breakdownService->forJournal($journal),
            'brand' => $branding->assemble($journal),
        ])->setPaper('a4', 'portrait');

        return $pdf->download($this->filename($journal, 'report'));
    }

    /**
     * Editor dueño o staff (super_admin/admin/evaluator) puede descargar.
     */
    private function authorizeJournalAccess(Journal $journal): void
    {
        $user = auth()->user();
        abort_unless($user !== null, 401);

        $isOwner = (int) $journal->user_id === (int) $user->id;
        $isStaff = method_exists($user, 'hasAnyRole')
            ? $user->hasAnyRole(['super_admin', 'admin', 'evaluator'])
            : false;

        abort_unless($isOwner || $isStaff, 403);
    }

    private function filename(Journal $journal, string $kind): string
    {
        $slug = $journal->slug ?: 'journal-'.$journal->id;
        $date = now()->format('Y-m-d');

        return "editorial-standards-{$kind}-{$slug}-{$date}.pdf";
    }
}
