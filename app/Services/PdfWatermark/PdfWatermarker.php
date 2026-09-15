<?php

namespace App\Services\PdfWatermark;

use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Stamps a footer line of text onto every page of an existing PDF, without
 * regenerating its content — used to mark a downloaded copy with the
 * purchasing customer's details as a mild deterrent against sharing.
 */
class PdfWatermarker
{
    public function watermark(string $sourcePath, string $destinationPath, string $text): void
    {
        $pdf = new Fpdi;
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pageCount = $pdf->setSourceFile($sourcePath);

        for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
            $templateId = $pdf->importPage($pageNumber);
            $size = $pdf->getTemplateSize($templateId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetTextColor(150, 150, 150);
            $pdf->SetXY(5, $size['height'] - 10);
            $pdf->Cell($size['width'] - 10, 5, $text, 0, 0, 'C');
        }

        $pdf->Output($destinationPath, 'F');
    }
}
