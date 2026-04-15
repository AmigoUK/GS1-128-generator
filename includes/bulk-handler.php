<?php
declare(strict_types=1);

function handle_bulk_upload(): void {
    $file = $_FILES['datafile'] ?? null;
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $_SESSION['bulk_error'] = 'No file uploaded.';
        redirect('bulk/upload'); return;
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        $_SESSION['bulk_error'] = 'File too large (limit ' . (MAX_UPLOAD_BYTES / 1024 / 1024) . ' MB).';
        redirect('bulk/upload'); return;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_UPLOAD_EXT, true)) {
        $_SESSION['bulk_error'] = 'Only CSV or XML files are allowed.';
        redirect('bulk/upload'); return;
    }

    if (!is_dir(STORAGE_UPLOADS)) @mkdir(STORAGE_UPLOADS, 0750, true);
    $stored = STORAGE_UPLOADS . '/' . bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $stored)) {
        $_SESSION['bulk_error'] = 'Could not save the uploaded file.';
        redirect('bulk/upload'); return;
    }

    $parsed = $ext === 'csv' ? parse_csv_file($stored) : parse_xml_file($stored);
    @unlink($stored);

    $_SESSION['bulk'] = [
        'parsed' => $parsed,
        'filename' => $file['name'],
        'format' => $ext,
        'uploaded_at' => time(),
    ];
    unset($_SESSION['bulk_error']);
    redirect('bulk/validate');
}

function handle_bulk_download(): void {
    $format = $_GET['format'] ?? '';
    $bulk = $_SESSION['bulk'] ?? null;
    if (!$bulk || empty($bulk['parsed']['rows'])) {
        http_response_code(400); echo 'No bulk session.'; return;
    }
    $rows = $bulk['parsed']['rows'];
    $base = 'gs1-128-bulk-' . date('Ymd-His');

    if ($format === 'zip-png' || $format === 'zip-svg') {
        $imgFmt = $format === 'zip-svg' ? 'svg' : 'png';
        $path = build_bulk_zip($rows, $imgFmt);
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $base . '.zip"');
        readfile($path);
        unlink($path);
        return;
    }
    if ($format === 'pdf') {
        $path = build_bulk_pdf($rows);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $base . '.pdf"');
        readfile($path);
        unlink($path);
        return;
    }
    if ($format === 'csv') {
        $csv = build_enriched_csv($bulk['parsed']['header'], $rows);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $base . '.csv"');
        echo $csv;
        return;
    }
    http_response_code(400); echo 'Unknown format.';
}

function redirect(string $route): void {
    header('Location: ' . base_url() . $route);
    exit;
}
