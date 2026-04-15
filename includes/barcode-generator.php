<?php
declare(strict_types=1);

/**
 * Assemble the machine-readable GS1-128 data string from a list of resolved AIs.
 *
 * Strategy:
 *   1. Order AIs so that all predefined-length AIs come first, variable-length
 *      AIs come last. This minimises FNC1 separators (variable-length AIs that
 *      are NOT the final AI need a trailing FNC1).
 *   2. Walk the ordered list and emit `(ai)value`-style segments separated by
 *      FNC1 only where the previous AI was variable-length and another AI follows.
 *
 * Returns:
 *   ['hri' => '(01)15901234123451(15)260115(10)LOT2024A',
 *    'machine' => '0115901234123451152601151010LOT2024A',
 *    'segments' => [['code'=>'01','value'=>'15901234123451','needs_fnc1'=>false], …]]
 *
 * The `machine` string here uses '<FNC1>' as a placeholder for the FNC1 character
 * before being passed to the renderer (which substitutes the library's marker).
 */
function assemble_gs1(array $resolved): array {
    // $resolved: [[code, value], ...]
    $with_meta = array_map(function ($pair) {
        [$code, $value] = $pair;
        return [
            'code' => (string)$code,
            'value' => (string)$value,
            'predefined' => ai_uses_predefined_length((string)$code),
        ];
    }, $resolved);

    // Stable sort: predefined first, then variable.
    usort($with_meta, function ($a, $b) {
        return ($a['predefined'] === $b['predefined']) ? 0 : ($a['predefined'] ? -1 : 1);
    });

    $segments = [];
    $hri = '';
    $machine = '';
    $n = count($with_meta);
    foreach ($with_meta as $i => $seg) {
        $isLast = ($i === $n - 1);
        $needs_fnc1 = (!$seg['predefined'] && !$isLast);
        $hri .= '(' . $seg['code'] . ')' . $seg['value'];
        $machine .= $seg['code'] . $seg['value'] . ($needs_fnc1 ? '<FNC1>' : '');
        $segments[] = $seg + ['needs_fnc1' => $needs_fnc1];
    }
    return ['hri' => $hri, 'machine' => $machine, 'segments' => $segments];
}

/**
 * Render a GS1-128 barcode as PNG bytes.
 *
 * The library expects an FNC1 marker in the input string. picqer/php-barcode-generator
 * supports CODE_128 with FNC1 inserted as ASCII 0xF1 in the encoded string when the
 * caller pre-encodes; for GS1-128 we pass the symbol with FNC1 placeholders to a
 * dedicated helper that returns the right format. Caller passes `assemble_gs1()` output.
 */
function render_png(string $machine_with_placeholders, int $widthFactor = 2, int $height = 60): string {
    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
    $payload = "\xf1" . str_replace('<FNC1>', "\xf1", $machine_with_placeholders);
    return $generator->getBarcode(
        $payload,
        \Picqer\Barcode\BarcodeGenerator::TYPE_CODE_128,
        $widthFactor,
        $height
    );
}

function render_svg(string $machine_with_placeholders, int $widthFactor = 2, int $height = 60): string {
    $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
    $payload = "\xf1" . str_replace('<FNC1>', "\xf1", $machine_with_placeholders);
    return $generator->getBarcode(
        $payload,
        \Picqer\Barcode\BarcodeGenerator::TYPE_CODE_128,
        $widthFactor,
        $height
    );
}
