<?php
/**
 * PHP Built-in server router
 */
declare(strict_types=1);

if (php_sapi_name() !== 'cli-server') {
    throw new RuntimeException('Router is required to run on CLI server');
}

if (is_file(__DIR__ . '/' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Require subdir
require_once __DIR__ . '/subdir/index.php';
