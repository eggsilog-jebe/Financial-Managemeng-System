<?php

declare(strict_types=1);

define('LARAVEL_START', microtime(true));

// Root of the Laravel project (one level up from api/)
$root = dirname(__DIR__);
chdir($root);

// Ensure Vercel's read-only filesystem has writable tmp directories
$tmpStorage = '/tmp/storage';
$tmpBootstrap = '/tmp/bootstrap/cache';

$tmpDirs = [
    $tmpStorage . '/app/public',
    $tmpStorage . '/framework/cache/data',
    $tmpStorage . '/framework/cache',
    $tmpStorage . '/framework/sessions',
    $tmpStorage . '/framework/views',
    $tmpStorage . '/logs',
    $tmpBootstrap,
];

foreach ($tmpDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Point compiled views to writable /tmp storage
putenv("VIEW_COMPILED_PATH={$tmpStorage}/framework/views");
$_ENV['VIEW_COMPILED_PATH'] = "{$tmpStorage}/framework/views";

// Point bootstrap cache files to /tmp/bootstrap/cache
putenv("APP_PACKAGES_CACHE={$tmpBootstrap}/packages.php");
$_ENV['APP_PACKAGES_CACHE'] = "{$tmpBootstrap}/packages.php";
putenv("APP_SERVICES_CACHE={$tmpBootstrap}/services.php");
$_ENV['APP_SERVICES_CACHE'] = "{$tmpBootstrap}/services.php";
putenv("APP_CONFIG_CACHE={$tmpBootstrap}/config.php");
$_ENV['APP_CONFIG_CACHE'] = "{$tmpBootstrap}/config.php";
putenv("APP_ROUTES_CACHE={$tmpBootstrap}/routes.php");
$_ENV['APP_ROUTES_CACHE'] = "{$tmpBootstrap}/routes.php";
putenv("APP_EVENTS_CACHE={$tmpBootstrap}/events.php");
$_ENV['APP_EVENTS_CACHE'] = "{$tmpBootstrap}/events.php";

// Copy pre-existing package and service manifests if available
if (file_exists($root . '/bootstrap/cache/packages.php') && !file_exists("{$tmpBootstrap}/packages.php")) {
    copy($root . '/bootstrap/cache/packages.php', "{$tmpBootstrap}/packages.php");
}
if (file_exists($root . '/bootstrap/cache/services.php') && !file_exists("{$tmpBootstrap}/services.php")) {
    copy($root . '/bootstrap/cache/services.php', "{$tmpBootstrap}/services.php");
}

// Fallback APP_KEY if not configured in Vercel environment variables
if (empty(getenv('APP_KEY')) && empty($_ENV['APP_KEY'])) {
    $fallbackAppKey = 'base64:94hD0B9520SllpUQqbmizcIlFw0xahZVzvYBKMQXyeo=';
    putenv("APP_KEY={$fallbackAppKey}");
    $_ENV['APP_KEY'] = $fallbackAppKey;
    $_SERVER['APP_KEY'] = $fallbackAppKey;
}

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
        $_SERVER['DB_DATABASE'] = $sqliteDst;
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

