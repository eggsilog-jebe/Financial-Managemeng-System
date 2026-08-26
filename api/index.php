<?php

declare(strict_types=1);

define('LARAVEL_START', microtime(true));

// Root of the Laravel project (one level up from api/)
$root = dirname(__DIR__);
chdir($root);

// Ensure Vercel's read-only filesystem has writable tmp directories
$tmpStorage = '/tmp/storage';
$tmpDirs = [
    $tmpStorage . '/app/public',
    $tmpStorage . '/framework/cache/data',
    $tmpStorage . '/framework/sessions',
    $tmpStorage . '/framework/views',
    $tmpStorage . '/logs',
];

foreach ($tmpDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Point compiled views to writable /tmp storage
putenv("VIEW_COMPILED_PATH={$tmpStorage}/framework/views");
$_ENV['VIEW_COMPILED_PATH'] = "{$tmpStorage}/framework/views";

// If using SQLite, copy bundled SQLite database to /tmp if needed so it's writable
$dbConnection = getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? 'sqlite');
if ($dbConnection === 'sqlite') {
    $sqliteSrc = $root . '/database/database.sqlite';
    $sqliteDst = '/tmp/database.sqlite';

    if (!file_exists($sqliteDst) && file_exists($sqliteSrc)) {
        copy($sqliteSrc, $sqliteDst);
    }

    if (file_exists($sqliteDst) && !getenv('DB_DATABASE')) {
        putenv("DB_DATABASE={$sqliteDst}");
        $_ENV['DB_DATABASE'] = $sqliteDst;
    }
}

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

require $root . '/vendor/autoload.php';

/** @var Application $app */
$app = require_once $root . '/bootstrap/app.php';

// Direct all Laravel storage operations (logs, cache, views, sessions) to /tmp/storage
$app->useStoragePath($tmpStorage);

$app->handleRequest(Request::capture());

