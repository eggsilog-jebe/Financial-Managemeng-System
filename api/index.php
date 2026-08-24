<?php

define('LARAVEL_START', microtime(true));

// Ensure Vercel's read-only filesystem has writable tmp dirs for Laravel's cache/sessions/views
$tmpDirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/logs',
];
foreach ($tmpDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Root of the Laravel project (one level up from api/)
$root = __DIR__ . '/..';

// Set working directory so relative paths inside Laravel resolve correctly
chdir($root);

// public/index.php is excluded via .vercelignore so we bootstrap Laravel directly here
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

require $root . '/vendor/autoload.php';

/** @var Application $app */
$app = require_once $root . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
