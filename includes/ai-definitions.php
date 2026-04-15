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
            'description' => 'The 14-digit product identifier — tells scanners *what* this is.',
            'when_to_use' => 'Always. Every GS1-128 barcode starts with a GTIN; it\'s the key that links your label back to the product record in any ERP, WMS or retail system. The wizard takes care of this in Step 1 so you don\'t need to add it again here.',
            'data_type' => 'N', 'min_length' => 14, 'max_length' => 14,
            'fixed_length' => true, 'predefined_length' => true,
            'format_hint' => '14 digits', 'example' => '15901234123454',
            'placeholder' => '14 digits',
        ],
        '02' => [
            'code' => '02', 'name' => 'GTIN of contained items', 'group' => 'Identification',
            'description' => 'The GTIN of the items *inside* a case or pallet (used with AI 37).',
            'when_to_use' => 'For logistic-unit labels on mixed or generic outer packaging — e.g. a shipper that contains 24 units of product X. Pair with AI 37 (count) so receiving systems know the exact quantity. This is only meaningful on the outer label; most users don\'t need it. Available via bulk import.',
            'data_type' => 'N', 'min_length' => 14, 'max_length' => 14,
            'fixed_length' => true, 'predefined_length' => true,
            'format_hint' => '14 digits', 'example' => '05901234123457',
            'placeholder' => '14 digits',
        ],
        '10' => [
            'code' => '10', 'name' => 'Batch / Lot number', 'group' => 'Batch & Serial',
            'description' => 'Groups units made or packed together so you can trace them later.',
            'when_to_use' => 'Use this whenever you\'d need to answer "which units came from run X?" — recalls, quality complaints, allergen contamination, customer returns, regulator audits. Food, cosmetics, supplements and most FMCG producers add a batch to every label. If a supplier delivers you a bad ingredient, the batch number is what lets you pull only the affected finished goods off the shelf, not your entire stock.',
            'data_type' => 'X', 'min_length' => 1, 'max_length' => 20,
            'fixed_length' => false, 'predefined_length' => false,
            'format_hint' => 'Up to 20 characters (letters, digits, ! % & \' ( ) * + , - . / : ; < = > ? _ )',
            'example' => 'LOT2024A', 'placeholder' => 'e.g. LOT2024A',
        ],
        '11' => [
            'code' => '11', 'name' => 'Production date', 'group' => 'Dates',
            'description' => 'The day the goods were made.',
            'when_to_use' => 'Useful for first-in-first-out stock rotation, age-of-stock proof for customs, warranty start dates, and process-improvement analytics (does product made on Mondays really fail more often?). Different from packaging and best-before dates — use those if you need shelf life, not manufacturing history.',
            'data_type' => 'D', 'min_length' => 6, 'max_length' => 6,
            'fixed_length' => true, 'predefined_length' => true,
            'format_hint' => 'YYMMDD (DD=00 means last day of month)',
            'example' => '260115', 'placeholder' => 'YYMMDD',
        ],
        '13' => [
            'code' => '13', 'name' => 'Packaging date', 'group' => 'Dates',
            'description' => 'The day the goods went into the packaging on the label.',
            'when_to_use' => 'Use this when production and packing happen on different days — common for frozen goods, fresh cut-and-wrap, made-to-order retail packs, and co-packing operations. It\'s also what some retailers require on fresh-case labels as the "pack date" even when a best-before is printed separately.',
            'data_type' => 'D', 'min_length' => 6, 'max_length' => 6,
            'fixed_length' => true, 'predefined_length' => true,
            'format_hint' => 'YYMMDD', 'example' => '260120', 'placeholder' => 'YYMMDD',
        ],
        '15' => [
            'code' => '15', 'name' => 'Best before date', 'group' => 'Dates',
            'description' => 'Last day of peak quality — *not* a safety deadline.',
            'when_to_use' => 'The default date AI for food, drinks, cosmetics, household goods and supplements. After this date the product isn\'t dangerous, just past its best. Retailers use it to decide what to mark down and what to pull; customers use it to choose the freshest pack on the shelf. Use Expiry (AI 17) instead if the product genuinely becomes unsafe after the date.',
            'data_type' => 'D', 'min_length' => 6, 'max_length' => 6,
            'fixed_length' => true, 'predefined_length' => true,
            'format_hint' => 'YYMMDD', 'example' => '260115', 'placeholder' => 'YYMMDD',
        ],
        '17' => [
            'code' => '17', 'name' => 'Expiry date', 'group' => 'Dates',
            'description' => 'Last day the product is safe to use or sell.',
            'when_to_use' => 'Mandatory on medicines, medical devices, infant formula, some chemicals, and many pharmacy-dispensed products. Retail and pharmacy systems will actively *refuse* to sell or dispense an expired item scanned at the till. Stricter than best-before — if there\'s any regulatory consequence for using past-date, this is the AI you want.',
            'data_type' => 'D', 'min_length' => 6, 'max_length' => 6,
            'fixed_length' => true, 'predefined_length' => true,
            'format_hint' => 'YYMMDD', 'example' => '260301', 'placeholder' => 'YYMMDD',
        ],
        '21' => [
            'code' => '21', 'name' => 'Serial number', 'group' => 'Batch & Serial',
            'description' => 'A unique ID for this *individual* unit, not the product type.',
            'when_to_use' => 'For anything you need to identify as one specific item: warranty-registered electronics, track-and-trace pharmaceuticals (EU FMD compliance uses AI 21 + 17 + 10 + 01), high-value tools, serialised returnable assets. Batch says "which production run"; serial says "which specific unit in that run". Use both together if regulation or traceability needs it.',
            'data_type' => 'X', 'min_length' => 1, 'max_length' => 20,
            'fixed_length' => false, 'predefined_length' => false,
            'format_hint' => 'Up to 20 characters', 'example' => 'SN000123',
            'placeholder' => 'e.g. SN000123',
        ],
        '310' => [
            'code' => '310', 'name' => 'Net weight (kg)', 'group' => 'Measurements',
            'description' => 'Actual weight without packaging, in kilograms.',
            'when_to_use' => 'Required for variable-measure retail goods — meat, cheese, fish, bakery, loose produce weighed at the till. Also useful on case and pallet labels so warehouses can calculate shipping weights without re-weighing. The decimal place is encoded into the AI automatically (1.250 kg becomes AI 3103, 12.5 kg becomes AI 3101), so you can just type the natural weight.',
            'data_type' => 'N', 'min_length' => 6, 'max_length' => 6,
            'fixed_length' => true, 'predefined_length' => true,
            'format_hint' => 'Decimal kg (e.g. 1.250)', 'example' => '1.250',
            'placeholder' => '1.250', 'has_decimal' => true, 'unit' => 'kg',
        ],
        '320' => [
            'code' => '320', 'name' => 'Net weight (lb)', 'group' => 'Measurements',
            'description' => 'Actual weight without packaging, in pounds.',
            'when_to_use' => 'Use this instead of kg when shipping to or labelling for US supply chains, where lb/oz dominates. Same mechanics as the kg version — the decimal place is baked into the AI digit automatically.',
            'data_type' => 'N', 'min_length' => 6, 'max_length' => 6,
            'fixed_length' => true, 'predefined_length' => true,
            'format_hint' => 'Decimal lb (e.g. 2.755)', 'example' => '2.755',
            'placeholder' => '2.755', 'has_decimal' => true, 'unit' => 'lb',
        ],
        '37' => [
            'code' => '37', 'name' => 'Count of trade items', 'group' => 'Identification',
            'description' => 'How many units are inside a logistic unit (used with AI 02).',
            'when_to_use' => 'Pair with AI 02 on outer-pack labels: "this case contains 24 units of GTIN X". Receiving systems can credit the correct stock-in count from a single scan instead of counting by hand. Only meaningful alongside AI 02 — available through bulk import.',
            'data_type' => 'N', 'min_length' => 1, 'max_length' => 8,
            'fixed_length' => false, 'predefined_length' => false,
            'format_hint' => 'Integer 1–99999999', 'example' => '24',
            'placeholder' => 'e.g. 24',
        ],
        '400' => [
            'code' => '400', 'name' => 'Customer purchase order', 'group' => 'Order info',
            'description' => 'Your customer\'s PO number, printed straight onto the label.',
            'when_to_use' => 'Dramatically speeds up goods-in at the destination: the receiving clerk scans the label and the ERP instantly pulls up the matching open order — no paperwork lookup. Use this if you ship B2B against purchase orders, especially to supermarkets, hospitals or any buyer that asked you to add the PO to ASNs or pallet labels.',
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
