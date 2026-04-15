<?php
declare(strict_types=1);

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/ai-definitions.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/barcode-generator.php';
require_once __DIR__ . '/includes/import-csv.php';
require_once __DIR__ . '/includes/import-xml.php';
require_once __DIR__ . '/includes/export.php';
require_once __DIR__ . '/includes/seo.php';

$base = base_url();
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$route = trim(substr($path, strlen(rtrim($base, '/'))), '/');
if ($route === '') $route = 'home';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Normalise route alias for SEO metadata lookup.
$seoRoute = $route === 'wizard/input' ? 'wizard' : ($route === 'bulk/upload' ? 'bulk' : $route);

switch ($route) {
    case 'home':
        $view = __DIR__ . '/templates/home.php';
        break;

    case 'wizard':
    case 'wizard/input':
        $view = __DIR__ . '/templates/wizard/step-input.php';
        break;
    case 'wizard/select-ai':
        $view = __DIR__ . '/templates/wizard/step-select-ai.php';
        break;
    case 'wizard/ai-data':
        $view = __DIR__ . '/templates/wizard/step-ai-data.php';
        break;
    case 'wizard/review':
        $view = __DIR__ . '/templates/wizard/step-review.php';
        break;
    case 'wizard/export':
        $view = __DIR__ . '/templates/wizard/step-export.php';
        break;

    case 'bulk':
    case 'bulk/upload':
        if ($method === 'POST') {
            require __DIR__ . '/includes/bulk-handler.php';
            handle_bulk_upload();
            exit;
        }
        $view = __DIR__ . '/templates/bulk/upload.php';
        break;
    case 'bulk/validate':
        $view = __DIR__ . '/templates/bulk/validation.php';
        break;
    case 'bulk/results':
        $view = __DIR__ . '/templates/bulk/results.php';
        break;
    case 'bulk/download':
        require __DIR__ . '/includes/bulk-handler.php';
        handle_bulk_download();
        exit;

    case 'api/validate':
        require __DIR__ . '/includes/api.php';
        exit;
    case 'api/generate':
        require __DIR__ . '/includes/api.php';
        exit;

    case 'download/template.csv':
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="gs1-128-template.csv"');
        readfile(__DIR__ . '/downloads/template.csv');
        exit;
    case 'download/template.xml':
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="gs1-128-template.xml"');
        readfile(__DIR__ . '/downloads/template.xml');
        exit;
    case 'download/schema.xsd':
        header('Content-Type: application/xml');
        readfile(__DIR__ . '/downloads/schema.xsd');
        exit;

    case 'robots.txt':
        render_robots();
        exit;
    case 'sitemap.xml':
        render_sitemap();
        exit;

    default:
        http_response_code(404);
        $seoRoute = '404';
        $view = null;
}

$meta = seo_meta_for($seoRoute);
$title = $meta['title'];

require __DIR__ . '/templates/layout.php';
