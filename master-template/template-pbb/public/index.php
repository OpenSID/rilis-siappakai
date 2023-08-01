<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/** folder master pbb */
define('PBB_FOLDER_FROM', '{$pbbFolderFrom}{$pbbFolder}');

/** folder pbb per masing masing desa*/
define('PBB_FOLDER_TO', '{$pbbFolderTo}');

/**
 * -------------------------------------------------------------------
 * LOAD SYMLINK
 * -------------------------------------------------------------------
 * */

if (!file_exists($app = PBB_FOLDER_TO . '{$directorySeparator}app')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}app', $app);
}

if (!file_exists($config = PBB_FOLDER_TO . '{$directorySeparator}config')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}config', $config);
}

if (!file_exists($database = PBB_FOLDER_TO . '{$directorySeparator}database')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}database', $database);
}

if (!file_exists($resources = PBB_FOLDER_TO . '{$directorySeparator}resources')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}resources', $resources);
}

if (!file_exists($routes = PBB_FOLDER_TO . '{$directorySeparator}routes')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}routes', $routes);
}

if (!file_exists($tests = PBB_FOLDER_TO . '{$directorySeparator}tests')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}tests', $tests);
}

if (!file_exists($vendor = PBB_FOLDER_TO . '{$directorySeparator}vendor')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}vendor', $vendor);
}

if (!file_exists($editorconfig = PBB_FOLDER_TO . '{$directorySeparator}.editorconfig')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}.editorconfig', $editorconfig);
}

if (!file_exists($gitattributes = PBB_FOLDER_TO . '{$directorySeparator}.gitattributes')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}.gitattributes', $gitattributes);
}

if (!file_exists($gitignore = PBB_FOLDER_TO . '{$directorySeparator}.gitignore')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}.gitignore', $gitignore);
}

if (!file_exists($styleci = PBB_FOLDER_TO . '{$directorySeparator}.styleci.yml')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}.styleci.yml', $styleci);
}

if (!file_exists($catatan_rilis = PBB_FOLDER_TO . '{$directorySeparator}catatan_rilis.md')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}catatan_rilis.md', $catatan_rilis);
}

if (!file_exists($composer = PBB_FOLDER_TO . '{$directorySeparator}composer.json')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}composer.json', $composer);
}

if (!file_exists($package = PBB_FOLDER_TO . '{$directorySeparator}package.json')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}package.json', $package);
}

// symlink agar tidak dapat diakses folder pbb-app
if (!file_exists($htaccess = PBB_FOLDER_TO . '{$directorySeparator}.htaccess')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}public{$directorySeparator}.htaccess', $htaccess);
}

// symlink folder public
if (!file_exists($build = PBB_FOLDER_TO . '{$directorySeparator}public{$directorySeparator}build')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}public{$directorySeparator}build', $build);
}

if (!file_exists($fonts = PBB_FOLDER_TO . '{$directorySeparator}public{$directorySeparator}fonts')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}public{$directorySeparator}fonts', $fonts);
}

if (!file_exists($themes = PBB_FOLDER_TO . '{$directorySeparator}public{$directorySeparator}themes')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}public{$directorySeparator}themes', $themes);
}

if (!file_exists($vendor = PBB_FOLDER_TO . '{$directorySeparator}public{$directorySeparator}vendor')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}public{$directorySeparator}vendor', $vendor);
}

if (!file_exists($vendors = PBB_FOLDER_TO . '{$directorySeparator}public{$directorySeparator}vendors')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}public{$directorySeparator}vendors', $vendors);
}

if (!file_exists($public_htaccess = PBB_FOLDER_TO . '{$directorySeparator}public{$directorySeparator}.htaccess')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}public{$directorySeparator}.htaccess', $public_htaccess);
}

// symlink folder public/import
if (!file_exists($template = PBB_FOLDER_TO . '{$directorySeparator}public{$directorySeparator}import{$directorySeparator}template')) {
    symlink(PBB_FOLDER_FROM . '{$directorySeparator}public{$directorySeparator}import{$directorySeparator}template', $template);
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
