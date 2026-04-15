<?php
declare(strict_types=1);

const APP_NAME = 'GS1-128 Generator';
const APP_VERSION = '0.1.0';

// Demo limit: bulk import processes only the first N rows
const BULK_LIMIT = 6;

// File upload constraints
const MAX_UPLOAD_BYTES = 5 * 1024 * 1024; // 5 MB
const ALLOWED_UPLOAD_EXT = ['csv', 'xml'];

// Storage paths (absolute)
const PROJECT_ROOT = __DIR__ . '/..';
const STORAGE_UPLOADS = PROJECT_ROOT . '/storage/uploads';
const STORAGE_GENERATED = PROJECT_ROOT . '/storage/generated';

// Base URL — derived at request time so the app stays hosting-portable.
function base_url(): string {
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    return rtrim(str_replace('\\', '/', dirname($script)), '/') . '/';
}

// Session
if (session_status() === PHP_SESSION_NONE && PHP_SAPI !== 'cli') {
    session_start();
}
