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

putenv('APP_NAME=HIMS');
$_ENV['APP_NAME'] = 'HIMS';
$_SERVER['APP_NAME'] = 'HIMS';

putenv('SESSION_DRIVER=database');
$_ENV['SESSION_DRIVER'] = 'database';
$_SERVER['SESSION_DRIVER'] = 'database';

putenv('SESSION_COOKIE=hims_session');
$_ENV['SESSION_COOKIE'] = 'hims_session';
$_SERVER['SESSION_COOKIE'] = 'hims_session';

putenv('APP_MAINTENANCE_DRIVER=file');
$_ENV['APP_MAINTENANCE_DRIVER'] = 'file';
$_SERVER['APP_MAINTENANCE_DRIVER'] = 'file';

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

// Ensure non-existent dev packages (e.g. pail, collision) are not loaded in production
if (file_exists("{$tmpBootstrap}/packages.php")) {
    require_once $root . '/vendor/autoload.php';
    $packages = require "{$tmpBootstrap}/packages.php";
    $changed = false;
    foreach ($packages as $pkg => $cfg) {
        if (!empty($cfg['providers'])) {
            $filtered = array_values(array_filter($cfg['providers'], fn ($p) => class_exists($p)));
            if (count($filtered) !== count($cfg['providers'])) {
                $packages[$pkg]['providers'] = $filtered;
                $changed = true;
            }
            if (empty($packages[$pkg]['providers'])) {
                unset($packages[$pkg]);
                $changed = true;
            }
        }
    }
    if ($changed) {
        file_put_contents("{$tmpBootstrap}/packages.php", '<?php return ' . var_export($packages, true) . ';');
    }
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

