<?php
declare(strict_types=1);

/**
 * Application Identifier definitions.
 *
 * Fields:
 *   code               2-4 digit AI code (e.g. '01', '310x' where x is the decimal-place indicator).
 *   name               Plain-English name shown in the UI.
 *   description        One-sentence explanation for help text / tooltip.
 *   group              Logical group for the wizard's AI selection screen.
 *   data_type          'N' numeric only, 'X' alphanumeric (Set 82), 'D' date YYMMDD.
 *   min_length         Minimum length in data characters.
 *   max_length         Maximum length in data characters.
 *   fixed_length       True if data length is fixed (== min == max).
 *   predefined_length  True if AI is in the GS1 predefined-length lookup table
 *                      (no FNC1 separator needed when followed by another AI).
 *   format_hint        Short user-facing format hint.
 *   example            Realistic example value.
 *   placeholder        HTML input placeholder.
 *   has_decimal        For 31xx/32xx — variable decimal-place indicator (last digit of code).
 *   unit               Optional measurement unit hint.
 */
function ai_definitions(): array {
    return [
        '01' => [
            'code' => '01', 'name' => 'GTIN', 'group' => 'Identification',
            'description' => 'Global Trade Item Number — the core 14-digit product identifier.',
            'data_type' => 'N', 'min_length' => 14, 'max_length' => 14,
            'fixed_length' => true, 'predefined_length' => true,
            'format_hint' => '14 digits', 'example' => '15901234123451',
            'placeholder' => '14 digits',
        ],
        '02' => [
            'code' => '02', 'name' => 'GTIN of contained items', 'group' => 'Identification',
            'description' => 'GTIN of the trade items contained inside a logistic unit (use with AI 37).',
            'data_type' => 'N', 'min_length' => 14, 'max_length' => 14,
            'fixed_length' => true, 'predefined_length' => true,
            'format_hint' => '14 digits', 'example' => '05901234123457',
            'placeholder' => '14 digits',
        ],
        '10' => [
            'code' => '10', 'name' => 'Batch / Lot number', 'group' => 'Batch & Serial',
            'description' => 'Batch or lot number for traceability and recalls.',
            'data_type' => 'X', 'min_length' => 1, 'max_length' => 20,
            'fixed_length' => false, 'predefined_length' => false,
            'format_hint' => 'Up to 20 characters (letters, digits, ! % & \' ( ) * + , - . / : ; < = > ? _ )',
            'example' => 'LOT2024A', 'placeholder' => 'e.g. LOT2024A',
        ],
        '11' => [
            'code' => '11', 'name' => 'Production date', 'group' => 'Dates',
            'description' => 'Date the item was produced.',
            'data_type' => 'D', 'min_length' => 6, 'max_length' => 6,
            'fixed_length' => true, 'predefined_length' => true,
            'format_hint' => 'YYMMDD (DD=00 means last day of month)',
            'example' => '260115', 'placeholder' => 'YYMMDD',
        ],
        '13' => [
            'code' => '13', 'name' => 'Packaging date', 'group' => 'Dates',
            'description' => 'Date the item was packaged.',
            'data_type' => 'D', 'min_length' => 6, 'max_length' => 6,
            'fixed_length' => true, 'predefined_length' => true,
            'format_hint' => 'YYMMDD', 'example' => '260120', 'placeholder' => 'YYMMDD',
        ],
        '15' => [
            'code' => '15', 'name' => 'Best before date', 'group' => 'Dates',
            'description' => 'Best-before-end date for shelf-life tracking.',
            'data_type' => 'D', 'min_length' => 6, 'max_length' => 6,
            'fixed_length' => true, 'predefined_length' => true,
            'format_hint' => 'YYMMDD', 'example' => '260115', 'placeholder' => 'YYMMDD',
        ],
        '17' => [
            'code' => '17', 'name' => 'Expiry date', 'group' => 'Dates',
            'description' => 'Expiration date — regulatory compliance for perishables/healthcare.',
            'data_type' => 'D', 'min_length' => 6, 'max_length' => 6,
            'fixed_length' => true, 'predefined_length' => true,
            'format_hint' => 'YYMMDD', 'example' => '260301', 'placeholder' => 'YYMMDD',
        ],
        '21' => [
            'code' => '21', 'name' => 'Serial number', 'group' => 'Batch & Serial',
            'description' => 'Unit-level serial number for individual item tracking.',
            'data_type' => 'X', 'min_length' => 1, 'max_length' => 20,
            'fixed_length' => false, 'predefined_length' => false,
            'format_hint' => 'Up to 20 characters', 'example' => 'SN000123',
            'placeholder' => 'e.g. SN000123',
        ],
        '310' => [
            'code' => '310', 'name' => 'Net weight (kg)', 'group' => 'Measurements',
            'description' => 'Net weight in kilograms. Decimal places encoded as last AI digit (310x).',
            'data_type' => 'N', 'min_length' => 6, 'max_length' => 6,
            'fixed_length' => true, 'predefined_length' => true,
            'format_hint' => 'Decimal kg (e.g. 1.250)', 'example' => '1.250',
            'placeholder' => '1.250', 'has_decimal' => true, 'unit' => 'kg',
        ],
        '320' => [
            'code' => '320', 'name' => 'Net weight (lb)', 'group' => 'Measurements',
            'description' => 'Net weight in pounds. Decimal places encoded as last AI digit (320x).',
            'data_type' => 'N', 'min_length' => 6, 'max_length' => 6,
            'fixed_length' => true, 'predefined_length' => true,
            'format_hint' => 'Decimal lb (e.g. 2.755)', 'example' => '2.755',
            'placeholder' => '2.755', 'has_decimal' => true, 'unit' => 'lb',
        ],
        '37' => [
            'code' => '37', 'name' => 'Count of trade items', 'group' => 'Identification',
            'description' => 'How many units are inside the logistic unit (used with AI 02).',
            'data_type' => 'N', 'min_length' => 1, 'max_length' => 8,
            'fixed_length' => false, 'predefined_length' => false,
            'format_hint' => 'Integer 1–99999999', 'example' => '24',
            'placeholder' => 'e.g. 24',
        ],
        '400' => [
            'code' => '400', 'name' => 'Customer purchase order', 'group' => 'Order info',
            'description' => 'Customer\'s purchase order number for cross-referencing.',
            'data_type' => 'X', 'min_length' => 1, 'max_length' => 30,
            'fixed_length' => false, 'predefined_length' => false,
            'format_hint' => 'Up to 30 characters', 'example' => 'PO-2026-0001',
            'placeholder' => 'e.g. PO-2026-0001',
        ],
    ];
}

/**
 * AIs whose first two digits put them in the GS1 predefined-length lookup table.
 * (Per GS1 General Specifications §7.8.4.) Used to decide whether an FNC1 separator
 * is needed when the AI is followed by another AI in the symbol.
 *
 * For every AI starting with one of these two-digit prefixes, the data length is
 * determined by the prefix and no separator is required.
 */
function predefined_length_prefixes(): array {
    return ['00','01','02','03','04','11','12','13','14','15','16','17','18','19','20','31','32','33','34','35','36','41'];
}

function ai_uses_predefined_length(string $code): bool {
    $prefix = substr($code, 0, 2);
    return in_array($prefix, predefined_length_prefixes(), true);
}

/** Map a column header from a CSV/XML row to the AI code it represents. */
function ai_column_aliases(): array {
    return [
        'ean13'              => 'EAN13',
        'gtin14'             => '01',
        'packaging_indicator'=> 'PI',
        'batch'              => '10',
        'lot'                => '10',
        'production_date'    => '11',
        'packaging_date'     => '13',
        'best_before'        => '15',
        'expiry_date'        => '17',
        'expiration_date'    => '17',
        'serial'             => '21',
        'net_weight_kg'      => '310',
        'net_weight_lb'      => '320',
        'count'              => '37',
        'gtin_contained'     => '02',
        'purchase_order'     => '400',
    ];
}
