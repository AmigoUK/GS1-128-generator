<?php
declare(strict_types=1);

/**
 * Parse + schema-validate an XML file of barcode rows.
 * Returns the same shape as parse_csv_file().
 *
 * Schema validation is best-effort: if the schema fails to load we still try
 * to parse and let per-row validation catch errors.
 */
function parse_xml_file(string $path): array {
    $rows = [];
    $aliases = ai_column_aliases();

    $prev = libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $loaded = $doc->load($path, LIBXML_NONET | LIBXML_NOENT);
    if (!$loaded) {
        $errors = array_map(fn($e) => trim($e->message), libxml_get_errors());
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        return ['rows' => [['raw' => [], 'resolved' => [], 'errors' => ['XML parse error: ' . implode('; ', $errors)], 'truncated' => false]], 'header' => [], 'total' => 1];
    }

    $schemaPath = PROJECT_ROOT . '/downloads/schema.xsd';
    $schemaErrors = [];
    if (file_exists($schemaPath)) {
        if (!@$doc->schemaValidate($schemaPath)) {
            foreach (libxml_get_errors() as $e) $schemaErrors[] = trim($e->message) . ' (line ' . $e->line . ')';
            libxml_clear_errors();
        }
    }
    libxml_use_internal_errors($prev);

    $i = 0;
    foreach ($doc->getElementsByTagName('barcode') as $node) {
        $assoc = [];
        foreach ($node->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;
            $assoc[strtolower($child->nodeName)] = trim($child->textContent);
        }
        $i++;
        $row = process_row($assoc, $aliases);
        if ($i === 1 && $schemaErrors) {
            $row['errors'] = array_merge(['Schema warning: ' . implode(' | ', $schemaErrors)], $row['errors']);
        }
        $row['truncated'] = $i > BULK_LIMIT;
        $rows[] = $row;
    }

    return ['rows' => $rows, 'header' => array_keys($aliases), 'total' => count($rows)];
}
