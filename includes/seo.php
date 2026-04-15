<?php
declare(strict_types=1);

/**
 * Per-route SEO metadata. Each route returns:
 *   title       — full <title> ("Page name — Brand")
 *   description — <meta name="description"> (~150 chars, action-oriented)
 *   noindex     — true to add <meta name="robots" content="noindex">
 *
 * Stateful intermediate steps (wizard/select-ai…export, bulk/validate…results)
 * are noindex'd because they require sessionStorage state and would be useless
 * landing pages for search-engine visitors.
 */
function seo_meta_for(string $route): array {
    $brand = 'GS1-128 Generator';
    $map = [
        'home' => [
            'title' => 'GS1-128 Barcode Generator — Free Online Tool with Wizard & Bulk Import',
            'description' => 'Free online GS1-128 (EAN-128) barcode generator. Convert EAN-13 or GTIN-14 into supply-chain barcodes with batch, expiry, weight and serial fields. Guided wizard or bulk CSV/XML import.',
        ],
        'wizard' => [
            'title' => 'Step 1: Enter EAN-13 or GTIN-14 — ' . $brand,
            'description' => 'Begin building a GS1-128 barcode by entering an EAN-13 or GTIN-14. The wizard auto-detects format and validates the check digit instantly.',
        ],
        'wizard/select-ai' => [
            'title' => 'Step 2: Choose Application Identifiers — ' . $brand,
            'description' => 'Pick the GS1 Application Identifiers (batch, expiry, weight, serial, purchase order…) to encode alongside your GTIN.',
            'noindex' => true,
        ],
        'wizard/ai-data' => [
            'title' => 'Step 3: Enter AI Values — ' . $brand,
            'description' => 'Fill in each Application Identifier with format-aware validation and instant feedback.',
            'noindex' => true,
        ],
        'wizard/review' => [
            'title' => 'Step 4: Review Your Barcode — ' . $brand,
            'description' => 'Preview your assembled GS1-128 barcode with the human-readable and machine-readable strings before downloading.',
            'noindex' => true,
        ],
        'wizard/export' => [
            'title' => 'Step 5: Download PNG, SVG or PDF — ' . $brand,
            'description' => 'Download the finished GS1-128 barcode as a print-ready PNG, scalable SVG or label-friendly PDF.',
            'noindex' => true,
        ],
        'bulk' => [
            'title' => 'Bulk GS1-128 Barcode Generator — CSV / XML Import',
            'description' => 'Generate up to 6 GS1-128 barcodes at once from a CSV or XML upload. Free templates and XSD schema included; outputs ZIP, label-sheet PDF or enriched CSV.',
        ],
        'bulk/validate' => [
            'title' => 'Bulk Import — Validation Report — ' . $brand,
            'description' => 'Per-row validation report for your bulk barcode upload, with explicit error messages.',
            'noindex' => true,
        ],
        'bulk/results' => [
            'title' => 'Bulk Import — Download Results — ' . $brand,
            'description' => 'Download your generated GS1-128 barcodes as a ZIP, label-sheet PDF, or enriched CSV.',
            'noindex' => true,
        ],
        '404' => [
            'title' => 'Page Not Found — ' . $brand,
            'description' => 'The page you were looking for could not be found.',
            'noindex' => true,
        ],
    ];
    return $map[$route] ?? $map['404'];
}

/** Absolute URL for the current request, derived from server vars (portable). */
function current_absolute_url(): string {
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = $_SERVER['REQUEST_URI'] ?? '/';
    // Strip query string for canonical
    $path = strtok($path, '?');
    return $proto . '://' . $host . $path;
}

function absolute_base_url(): string {
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $proto . '://' . $host . base_url();
}

/** Static OG image URL (uses the parent attv.uk brand image). */
function og_image_url(): string {
    return 'https://attv.uk/img/tl.png';
}

/** Render the dynamic robots.txt with the right Sitemap line for this install. */
function render_robots(): void {
    header('Content-Type: text/plain; charset=UTF-8');
    $base = base_url();
    $abs = absolute_base_url();
    echo "User-agent: *\n";
    echo "Allow: {$base}\n";
    foreach (['wizard/select-ai', 'wizard/ai-data', 'wizard/review', 'wizard/export',
              'bulk/validate', 'bulk/results', 'api/', 'bulk/download'] as $p) {
        echo "Disallow: {$base}{$p}\n";
    }
    echo "\nSitemap: {$abs}sitemap.xml\n";
}

/** Render the dynamic sitemap.xml — only the indexable landing pages. */
function render_sitemap(): void {
    header('Content-Type: application/xml; charset=UTF-8');
    $abs = absolute_base_url();
    $today = date('Y-m-d');
    $entries = [
        ['', '1.0', 'weekly'],
        ['wizard', '0.8', 'monthly'],
        ['bulk', '0.8', 'monthly'],
    ];
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($entries as [$loc, $prio, $freq]) {
        echo "  <url>\n";
        echo "    <loc>{$abs}{$loc}</loc>\n";
        echo "    <lastmod>{$today}</lastmod>\n";
        echo "    <changefreq>{$freq}</changefreq>\n";
        echo "    <priority>{$prio}</priority>\n";
        echo "  </url>\n";
    }
    echo '</urlset>' . "\n";
}
