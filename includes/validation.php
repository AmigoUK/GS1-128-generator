<?php
declare(strict_types=1);

/**
 * GS1 modulo-10 check digit. Works for any even- or odd-length numeric body
 * (12-digit body for EAN-13, 13-digit body for GTIN-14, etc.). The rightmost
 * digit of the body is multiplied by 3, then weights alternate 1,3,1,3...
 * moving leftwards. The check digit is (10 - sum mod 10) mod 10.
 */
function gs1_check_digit(string $body): int {
    $sum = 0;
    $len = strlen($body);
    for ($i = 0; $i < $len; $i++) {
        $d = (int)$body[$len - 1 - $i];
        $sum += ($i % 2 === 0) ? $d * 3 : $d;
    }
    return (10 - ($sum % 10)) % 10;
}

function validate_ean13(string $s): array {
    if (!preg_match('/^\d{13}$/', $s)) {
        return ['ok' => false, 'error' => 'EAN-13 must be exactly 13 digits.'];
    }
    $expected = gs1_check_digit(substr($s, 0, 12));
    $actual = (int)$s[12];
    if ($expected !== $actual) {
        return ['ok' => false, 'error' => "Invalid check digit: should be $expected, you entered $actual."];
    }
    return ['ok' => true];
}

function validate_gtin14(string $s): array {
    if (!preg_match('/^\d{14}$/', $s)) {
        return ['ok' => false, 'error' => 'GTIN-14 must be exactly 14 digits.'];
    }
    $expected = gs1_check_digit(substr($s, 0, 13));
    $actual = (int)$s[13];
    if ($expected !== $actual) {
        return ['ok' => false, 'error' => "Invalid check digit: should be $expected, you entered $actual."];
    }
    return ['ok' => true];
}

/** Build a GTIN-14 from an EAN-13 by prefixing a packaging indicator (0-9) and
 *  recalculating the check digit. */
function derive_gtin14_from_ean13(string $ean13, int $packaging_indicator): array {
    $v = validate_ean13($ean13);
    if (!$v['ok']) return $v;
    if ($packaging_indicator < 0 || $packaging_indicator > 9) {
        return ['ok' => false, 'error' => 'Packaging indicator must be 0–9.'];
    }
    $body = $packaging_indicator . substr($ean13, 0, 12); // 13 digits
    $cd = gs1_check_digit($body);
    return ['ok' => true, 'gtin14' => $body . $cd];
}

/**
 * Validate a single AI value against its definition.
 * For 31xx/32xx (weights) the value is a decimal number; we determine the
 * decimal-place suffix and produce both the resolved AI code and the 6-digit
 * zero-padded encoded string.
 */
function validate_ai_value(string $code, string $value, array $defs = null): array {
    $defs = $defs ?? ai_definitions();
    if (!isset($defs[$code])) {
        return ['ok' => false, 'error' => "Unknown AI ($code)."];
    }
    $def = $defs[$code];

    if ($value === '') {
        return ['ok' => false, 'error' => 'Value is required.'];
    }

    // Date type
    if ($def['data_type'] === 'D') {
        $yy = to_yymmdd($value);
        if ($yy === null || !preg_match('/^(\d{2})(\d{2})(\d{2})$/', $yy, $m)) {
            return ['ok' => false, 'error' => 'Date must be in YYMMDD or YYYY-MM-DD form.'];
        }
        $month = (int)$m[2]; $day = (int)$m[3];
        if ($month < 1 || $month > 12) {
            return ['ok' => false, 'error' => "Month must be 01-12, got " . $m[2] . "."];
        }
        if ($day < 0 || $day > 31) {
            return ['ok' => false, 'error' => "Day must be 00-31, got " . $m[3] . "."];
        }
        // 00 means "last day of month" — valid in non-healthcare contexts.
        if ($day !== 0) {
            $year = (int)$m[1]; $year += ($year >= 50) ? 1900 : 2000;
            if (!checkdate($month, $day, $year)) {
                return ['ok' => false, 'error' => "Date " . $m[3] . "/" . $m[2] . "/$year does not exist."];
            }
        }
        return ['ok' => true, 'encoded' => $yy, 'resolved_code' => $code];
    }

    // Weights with variable decimal place (310x, 320x)
    if (!empty($def['has_decimal'])) {
        if (!preg_match('/^\d+(\.\d{0,6})?$/', $value)) {
            return ['ok' => false, 'error' => 'Weight must be a decimal number (e.g. 1.250).'];
        }
        $parts = explode('.', $value, 2);
        $decimals = strlen($parts[1] ?? '');
        if ($decimals > 6) {
            return ['ok' => false, 'error' => 'Maximum 6 decimal places.'];
        }
        $intStr = ltrim($parts[0], '0'); if ($intStr === '') $intStr = '0';
        $fracStr = $parts[1] ?? '';
        $combined = $intStr . $fracStr;
        if (strlen($combined) > 6) {
            return ['ok' => false, 'error' => 'Weight value too large for 6-digit field.'];
        }
        $encoded = str_pad($combined, 6, '0', STR_PAD_LEFT);
        $resolved = $def['code'] . $decimals; // e.g. 310 + 3 = 3103
        return ['ok' => true, 'encoded' => $encoded, 'resolved_code' => $resolved];
    }

    // Numeric type
    if ($def['data_type'] === 'N') {
        if (!ctype_digit($value)) {
            return ['ok' => false, 'error' => 'Only digits are allowed.'];
        }
        $len = strlen($value);
        if ($len < $def['min_length'] || $len > $def['max_length']) {
            return ['ok' => false, 'error' => "Length must be between {$def['min_length']} and {$def['max_length']} digits."];
        }
        // Special case: GTIN-style AIs (01, 02) need a valid check digit.
        if (in_array($code, ['01', '02'], true)) {
            $v = validate_gtin14($value);
            if (!$v['ok']) return $v;
        }
        return ['ok' => true, 'encoded' => $value, 'resolved_code' => $code];
    }

    // Alphanumeric type — Set 82
    if ($def['data_type'] === 'X') {
        if (!in_charset_82($value)) {
            return ['ok' => false, 'error' => "Value contains characters outside GS1 Character Set 82."];
        }
        $len = strlen($value);
        if ($len < $def['min_length'] || $len > $def['max_length']) {
            return ['ok' => false, 'error' => "Length must be between {$def['min_length']} and {$def['max_length']} characters."];
        }
        return ['ok' => true, 'encoded' => $value, 'resolved_code' => $code];
    }

    return ['ok' => false, 'error' => 'Unknown data type.'];
}

/**
 * Apply combination-level rules:
 *   - AI 02 requires AI 37 and vice versa.
 *   - AI 01 and AI 02 cannot both appear.
 *   - No duplicate AI codes.
 *   - Total data characters across all AIs must not exceed 48.
 *
 * $resolved is a list of [resolved_code, encoded_value] pairs.
 */
function validate_ai_combinations(array $resolved): array {
    // Cast every code to string — callers may pass PHP array keys that got
    // auto-converted to int (e.g. '37' → 37), which would break strict
    // comparisons below.
    $codes = array_map('strval', array_column($resolved, 0));
    $errors = [];

    if (count($codes) !== count(array_unique($codes))) {
        $errors[] = 'Duplicate AIs are not allowed.';
    }
    if (in_array('01', $codes, true) && in_array('02', $codes, true)) {
        $errors[] = 'AI (01) and AI (02) cannot appear together.';
    }
    $has02 = in_array('02', $codes, true);
    $has37 = in_array('37', $codes, true);
    if ($has02 !== $has37) {
        $errors[] = 'AI (02) and AI (37) must be used together.';
    }
    $total = 0;
    foreach ($resolved as [$c, $v]) { $total += strlen((string)$c) + strlen((string)$v); }
    if ($total > 48) {
        $errors[] = "Total data characters ($total) exceed the GS1-128 maximum of 48.";
    }
    return ['ok' => empty($errors), 'errors' => $errors];
}
