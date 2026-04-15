<?php
declare(strict_types=1);

/**
 * JSON / file-download API endpoints used by the wizard and bulk export.
 *
 * Routes:
 *   POST api/generate        — Accepts {resolved: [[code,value],...], hri: '...', format: 'png|svg|pdf'}
 *                              Returns the rendered barcode as a download.
 *   POST api/validate        — Accepts {ai_code, value}; returns JSON validation result. (Reserved
 *                              for future use; client mirrors PHP rules in JS for now.)
 */

header('X-Content-Type-Options: nosniff');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '';
$endpoint = basename($path);

if ($endpoint === 'generate') {
    handle_generate();
} elseif ($endpoint === 'validate') {
    handle_validate();
} else {
    http_response_code(404);
    echo 'Unknown endpoint';
}

function handle_generate(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405); exit('POST required');
    }
    $format = strtolower((string)($_POST['format'] ?? ''));
    $payload = json_decode((string)($_POST['payload'] ?? ''), true);
    if (!is_array($payload) || !isset($payload['resolved']) || !is_array($payload['resolved'])) {
        http_response_code(400); exit('Bad payload');
    }
    if (!in_array($format, ['png', 'svg', 'pdf'], true)) {
        http_response_code(400); exit('Bad format');
    }

    // Server-side re-validation — never trust the client.
    $resolved = [];
    $defs = ai_definitions();
    foreach ($payload['resolved'] as $pair) {
        if (!is_array($pair) || count($pair) !== 2) { http_response_code(400); exit('Malformed pair'); }
        [$code, $value] = $pair;
        $code = (string)$code; $value = (string)$value;
        // Top-level check: code must be a known AI or a 4-digit weight variant.
        if (!isset($defs[$code]) && !preg_match('/^(31\d|32\d)\d$/', $code)) {
            http_response_code(400); exit('Unknown AI: ' . htmlspecialchars($code));
        }
        if ($code === '01' || $code === '02') {
            $v = validate_gtin14($value);
            if (!$v['ok']) { http_response_code(400); exit('Invalid GTIN value: ' . $v['error']); }
        }
        $resolved[] = [$code, $value];
    }
    $combo = validate_ai_combinations($resolved);
    if (!$combo['ok']) { http_response_code(400); exit('Combination error: ' . implode(' ', $combo['errors'])); }

    $assembled = assemble_gs1($resolved);

    $filename_base = 'gs1-128-' . date('Ymd-His');
    if ($format === 'png') {
        $bytes = render_png($assembled['machine'], 2, 80);
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="' . $filename_base . '.png"');
        echo $bytes;
        return;
    }
    if ($format === 'svg') {
        $svg = render_svg($assembled['machine'], 2, 80);
        header('Content-Type: image/svg+xml');
        header('Content-Disposition: attachment; filename="' . $filename_base . '.svg"');
        echo $svg;
        return;
    }
    if ($format === 'pdf') {
        $png = render_png($assembled['machine'], 3, 100);
        $tmp = tempnam(sys_get_temp_dir(), 'gs1');
        file_put_contents($tmp, $png);
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('GS1-128 Generator');
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'GS1-128 Barcode', 0, 1);
        $pdf->Ln(2);
        $pdf->Image($tmp, 15, $pdf->GetY(), 120, 0, 'PNG');
        $pdf->Ln(40);
        $pdf->SetFont('courier', '', 11);
        $pdf->MultiCell(0, 6, $assembled['hri'], 0, 'L');
        $pdf->Ln(4);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, 'Generated: ' . date('Y-m-d H:i'), 0, 1);
        unlink($tmp);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename_base . '.pdf"');
        echo $pdf->Output('', 'S');
        return;
    }
}

function handle_validate(): void {
    header('Content-Type: application/json');
    $code = (string)($_POST['ai_code'] ?? '');
    $value = (string)($_POST['value'] ?? '');
    echo json_encode(validate_ai_value($code, $value));
}
