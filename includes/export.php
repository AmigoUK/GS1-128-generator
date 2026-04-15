<?php
declare(strict_types=1);

/**
 * Build a ZIP of generated barcode images (one per valid row, up to BULK_LIMIT).
 * Returns the path to the temporary ZIP file. Caller is responsible for unlinking.
 */
function build_bulk_zip(array $rows, string $imageFormat = 'png'): string {
    $zipPath = tempnam(sys_get_temp_dir(), 'gs1zip');
    $zip = new ZipArchive();
    $zip->open($zipPath, ZipArchive::OVERWRITE);
    $i = 0;
    foreach ($rows as $idx => $row) {
        if ($row['truncated'] || !empty($row['errors'])) continue;
        if (++$i > BULK_LIMIT) break;
        $assembled = assemble_gs1($row['resolved']);
        $name = sprintf('barcode-%02d.%s', $i, $imageFormat);
        $bytes = $imageFormat === 'svg'
            ? render_svg($assembled['machine'], 2, 80)
            : render_png($assembled['machine'], 2, 80);
        $zip->addFromString($name, $bytes);
    }
    $zip->close();
    return $zipPath;
}

/**
 * Build a single PDF with all barcodes laid out 2-up per row on A4. The user can
 * print and cut along the gridlines for ad-hoc labels.
 */
function build_bulk_pdf(array $rows): string {
    $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8');
    $pdf->SetCreator('GS1-128 Generator');
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->AddPage();

    $i = 0; $col = 0;
    $cellW = 90; $cellH = 50;
    foreach ($rows as $row) {
        if ($row['truncated'] || !empty($row['errors'])) continue;
        if (++$i > BULK_LIMIT) break;
        $assembled = assemble_gs1($row['resolved']);
        $png = render_png($assembled['machine'], 3, 100);
        $tmp = tempnam(sys_get_temp_dir(), 'gs1');
        file_put_contents($tmp, $png);

        $x = 10 + $col * ($cellW + 5);
        $y = $pdf->GetY();
        $pdf->Image($tmp, $x, $y, $cellW - 5, 0, 'PNG');
        $pdf->SetXY($x, $y + 30);
        $pdf->SetFont('courier', '', 8);
        $pdf->MultiCell($cellW - 5, 4, $assembled['hri'], 0, 'L');
        unlink($tmp);

        $col++;
        if ($col >= 2) { $col = 0; $pdf->SetY($y + $cellH); }
        if ($pdf->GetY() > 270) $pdf->AddPage();
    }
    $path = tempnam(sys_get_temp_dir(), 'gs1pdf');
    $pdf->Output($path, 'F');
    return $path;
}

/**
 * Append generated machine-readable barcode strings to the original CSV rows
 * and return the enriched CSV as a string.
 */
function build_enriched_csv(array $header, array $rows): string {
    $out = fopen('php://temp', 'w+');
    $hdr = $header; $hdr[] = 'gs1_128_string';
    fputcsv($out, $hdr);
    $i = 0;
    foreach ($rows as $row) {
        if ($row['truncated']) break;
        $line = [];
        foreach ($header as $col) { $line[] = $row['raw'][$col] ?? ''; }
        if (empty($row['errors'])) {
            $assembled = assemble_gs1($row['resolved']);
            $line[] = $assembled['hri'];
        } else {
            $line[] = 'ERROR: ' . implode(' | ', $row['errors']);
        }
        fputcsv($out, $line);
        if (++$i >= BULK_LIMIT) break;
    }
    rewind($out);
    $data = stream_get_contents($out);
    fclose($out);
    return $data;
}
