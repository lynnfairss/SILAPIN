<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Entry point untuk shared hosting (InfinityFree)
|--------------------------------------------------------------------------
|
| Struktur folder di server:
|
|   htdocs/   -> isi folder public/ + file ini + folder storage/
|   silapin/  -> aplikasi Laravel (app, config, vendor, .env, storage, ...)
|
| Aplikasi sengaja diletakkan di luar htdocs supaya .env, vendor, dan
| storage tidak bisa diakses langsung lewat browser.
|
*/

$appPath = dirname(__DIR__).'/silapin';

if (! is_dir($appPath)) {
    http_response_code(500);
    exit('Folder aplikasi "silapin" tidak ditemukan di '.$appPath.'. Ikuti README.md di folder deploy/infinityfree.');
}

if (file_exists($maintenance = $appPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $appPath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $appPath.'/bootstrap/app.php';

// Folder publik aplikasi adalah htdocs itu sendiri, bukan silapin/public.
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
