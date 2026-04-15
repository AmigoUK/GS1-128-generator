<?php
declare(strict_types=1);

/**
 * Convert any accepted date input into GS1 YYMMDD form.
 * Accepts YYMMDD (returned as-is if valid), YYYY-MM-DD, YYYY/MM/DD, DD/MM/YYYY.
 * DD=00 (last day of month) is preserved when input is already YYMMDD.
 * Returns null if the value cannot be parsed.
 */
function to_yymmdd(string $value): ?string {
    $value = trim($value);
    if ($value === '') return null;

    // Native YYMMDD
    if (preg_match('/^\d{6}$/', $value)) {
        return $value;
    }
    // ISO YYYY-MM-DD
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
        return substr($m[1], 2) . $m[2] . $m[3];
    }
    // YYYY/MM/DD
    if (preg_match('/^(\d{4})\/(\d{2})\/(\d{2})$/', $value, $m)) {
        return substr($m[1], 2) . $m[2] . $m[3];
    }
    // DD/MM/YYYY
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $m)) {
        return substr($m[3], 2) . $m[2] . $m[1];
    }
    return null;
}

/**
 * Format YYMMDD for human display ("15 Jan 2026", "End of Jan 2026" if DD=00).
 */
function format_yymmdd(string $yymmdd): string {
    if (!preg_match('/^(\d{2})(\d{2})(\d{2})$/', $yymmdd, $m)) return $yymmdd;
    [, $yy, $mm, $dd] = $m;
    $year = (int)$yy;
    // Sliding window: 50-99 → 19xx, 00-49 → 20xx
    $year += ($year >= 50) ? 1900 : 2000;
    $monthName = date('M', mktime(0, 0, 0, max(1, (int)$mm), 1, $year));
    if ($dd === '00') return "End of $monthName $year";
    return "$dd $monthName $year";
}

/** GS1 Character Set 82 — alphanumeric AIs allow these characters only. */
function gs1_charset_82(): string {
    return "!\"%&'()*+,-./0123456789:;<=>?ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz";
}

function in_charset_82(string $s): bool {
    $allowed = gs1_charset_82();
    for ($i = 0, $n = strlen($s); $i < $n; $i++) {
        if (strpos($allowed, $s[$i]) === false) return false;
    }
    return true;
}
