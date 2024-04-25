<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/** folder master template */
define('MASTER_TEMPLATE', '/../master-template');

/** folder root */
define('FOLDER_ROOT', '{$dirRoot}');

/**
 * -------------------------------------------------------------------
 * LOAD SYMLINK MASTER TEMPLATE
 * -------------------------------------------------------------------
 * */

if (!file_exists($master_template = __DIR__ . '/..' . MASTER_TEMPLATE)) {
    symlink(__DIR__ . MASTER_TEMPLATE, $master_template);
}

/**
 * -------------------------------------------------------------------
 * MEMBUAT FOLDER MASTER
 * -------------------------------------------------------------------
 * */

if (!file_exists($master_api = __DIR__ . '/../../master-api')) {
    mkdir($master_api);
}

if (!file_exists($master_opensid = __DIR__ . '/../../master-opensid')) {
    mkdir($master_opensid);
}

if (!file_exists($master_pbb = __DIR__ . '/../../master-pbb')) {
    mkdir($master_pbb);
}

if (!file_exists($master_tema = __DIR__ . '/../../master-tema-gratis')) {
    mkdir($master_tema);
}

if (!file_exists($master_tema = __DIR__ . '/../../master-tema-pro')) {
    mkdir($master_tema);
}

if (!file_exists($multisite = __DIR__ . '/../../multisite')) {
    mkdir($multisite);
}

// symlink phpmyadmin
if (!file_exists($phpmyadmin = __DIR__ . '/../../phpmyadmin')) {
    symlink('/usr/share/phpmyadmin', FOLDER_ROOT . 'phpmyadmin');
}

// symlink public_html
if (!file_exists($public_html = __DIR__ . '/../../public_html')) {
    symlink(FOLDER_ROOT . 'dashboard-saas/public', FOLDER_ROOT . 'public_html');
}

if (!file_exists($public_html = __DIR__ . '/../../public_html/opensid-premium')) {
    symlink(FOLDER_ROOT . 'master-opensid/premium', FOLDER_ROOT . 'public_html/opensid-premium');
}

if (!file_exists($public_html = __DIR__ . '/../../public_html/pbb')) {
    symlink(FOLDER_ROOT . 'master-pbb/pbb_desa/public', FOLDER_ROOT . 'public_html/pbb');
}

if (!file_exists($public_html = __DIR__ . '/../../public_html/opensid-api')) {
    symlink(FOLDER_ROOT . 'master-api/opensid-api/public', FOLDER_ROOT . 'public_html/opensid-api');
}

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__ . '/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
