<?php
declare(strict_types=1);

/**
 * Parse a CSV file into a list of validated rows.
 * Returns:
 *   ['rows' => [['raw' => assoc, 'resolved' => [[code,value],...], 'errors' => [], 'truncated' => bool], ...],
 *    'header' => assoc-keyed array, 'total' => int]
 *
 * Rows beyond BULK_LIMIT are still included but flagged truncated => true so the
 * UI can grey them out without fully discarding them (the brief asks for an
 * upload-time count message).
 */
function parse_csv_file(string $path): array {
    $fh = fopen($path, 'r');
    if (!$fh) return ['rows' => [], 'header' => [], 'total' => 0];
    $header = fgetcsv($fh);
    if (!$header) { fclose($fh); return ['rows' => [], 'header' => [], 'total' => 0]; }
    $header = array_map(fn($h) => strtolower(trim((string)$h)), $header);

    $aliases = ai_column_aliases();
    $rows = [];
    $i = 0;
    while (($cells = fgetcsv($fh)) !== false) {
        // Skip completely empty rows.
        if (count(array_filter($cells, fn($c) => trim((string)$c) !== '')) === 0) continue;
        $assoc = [];
        foreach ($header as $idx => $name) {
            $assoc[$name] = isset($cells[$idx]) ? trim((string)$cells[$idx]) : '';
        }
        $i++;
        $row = process_row($assoc, $aliases);
        $row['truncated'] = $i > BULK_LIMIT;
        $rows[] = $row;
    }
    fclose($fh);
    return ['rows' => $rows, 'header' => $header, 'total' => count($rows)];
}

/**
 * Convert a single CSV-derived associative row into a {raw, resolved, errors}
 * record by mapping column headers via $aliases and re-using the wizard
 * validation engine.
 */
function process_row(array $assoc, array $aliases): array {
    $errors = [];
    $resolved = [];

    // Establish a GTIN-14: prefer explicit, otherwise derive from EAN-13 (+ optional PI).
    $gtin14 = $assoc['gtin14'] ?? '';
    if ($gtin14 === '' && !empty($assoc['ean13'])) {
        $pi = isset($assoc['packaging_indicator']) && $assoc['packaging_indicator'] !== ''
            ? (int)$assoc['packaging_indicator'] : 0;
        $r = derive_gtin14_from_ean13($assoc['ean13'], $pi);
        if (!$r['ok']) { $errors[] = 'GTIN derivation: ' . $r['error']; }
        else { $gtin14 = $r['gtin14']; }
    }
    if ($gtin14 === '') {
        $errors[] = 'Row needs either ean13 or gtin14.';
    } else {
        $v = validate_gtin14($gtin14);
        if (!$v['ok']) $errors[] = 'GTIN: ' . $v['error'];
        else $resolved[] = ['01', $gtin14];
    }

    // Walk every other recognised column.
    foreach ($aliases as $col => $aiCode) {
        if (in_array($col, ['ean13', 'gtin14', 'packaging_indicator'], true)) continue;
        if (!isset($assoc[$col]) || $assoc[$col] === '') continue;
        $r = validate_ai_value($aiCode, $assoc[$col]);
        if (!$r['ok']) {
            $errors[] = "$col: " . $r['error'];
        } else {
            $resolved[] = [$r['resolved_code'], $r['encoded']];
        }
    }

    if (count($resolved) > 1 || ($gtin14 !== '' && empty($errors))) {
        $combo = validate_ai_combinations($resolved);
        if (!$combo['ok']) $errors = array_merge($errors, $combo['errors']);
    }

    return [
        'raw' => $assoc,
        'resolved' => $resolved,
        'errors' => $errors,
        'truncated' => false,
    ];
}
