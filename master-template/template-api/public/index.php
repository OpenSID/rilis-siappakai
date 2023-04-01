<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/** folder master api */
define('API_FOLDER_FROM', '{$apiFolderFrom}{$apiFolder}');

/** folder api per masing masing desa*/
define('API_FOLDER_TO', '{$apiFolderTo}');

/**
 * -------------------------------------------------------------------
 * LOAD SYMLINK
 * -------------------------------------------------------------------
 * */

if (!file_exists($app = API_FOLDER_TO . '{$directorySeparator}app')) {
    symlink(API_FOLDER_FROM . '{$directorySeparator}app', $app);
}

if (!file_exists($config = API_FOLDER_TO . '{$directorySeparator}config')) {
    symlink(API_FOLDER_FROM . '{$directorySeparator}config', $config);
}

if (!file_exists($database = API_FOLDER_TO . '{$directorySeparator}database')) {
    symlink(API_FOLDER_FROM . '{$directorySeparator}database', $database);
}

if (!file_exists($resources = API_FOLDER_TO . '{$directorySeparator}resources')) {
    symlink(API_FOLDER_FROM . '{$directorySeparator}resources', $resources);
}

if (!file_exists($routes = API_FOLDER_TO . '{$directorySeparator}routes')) {
    symlink(API_FOLDER_FROM . '{$directorySeparator}routes', $routes);
}

if (!file_exists($tests = API_FOLDER_TO . '{$directorySeparator}tests')) {
    symlink(API_FOLDER_FROM . '{$directorySeparator}tests', $tests);
}

if (!file_exists($vendor = API_FOLDER_TO . '{$directorySeparator}vendor')) {
    symlink(API_FOLDER_FROM . '{$directorySeparator}vendor', $vendor);
}

if (!file_exists($editorconfig = API_FOLDER_TO . '{$directorySeparator}.editorconfig')) {
    symlink(API_FOLDER_FROM . '{$directorySeparator}.editorconfig', $editorconfig);
}

if (!file_exists($gitattributes = API_FOLDER_TO . '{$directorySeparator}.gitattributes')) {
    symlink(API_FOLDER_FROM . '{$directorySeparator}.gitattributes', $gitattributes);
}

if (!file_exists($gitignore = API_FOLDER_TO . '{$directorySeparator}.gitignore')) {
    symlink(API_FOLDER_FROM . '{$directorySeparator}.gitignore', $gitignore);
}

if (!file_exists($styleci = API_FOLDER_TO . '{$directorySeparator}.styleci.yml')) {
    symlink(API_FOLDER_FROM . '{$directorySeparator}.styleci.yml', $styleci);
}

if (!file_exists($catatan_rilis = API_FOLDER_TO . '{$directorySeparator}catatan_rilis.md')) {
    symlink(API_FOLDER_FROM . '{$directorySeparator}catatan_rilis.md', $catatan_rilis);
}

if (!file_exists($composer = API_FOLDER_TO . '{$directorySeparator}composer.json')) {
    symlink(API_FOLDER_FROM . '{$directorySeparator}composer.json', $composer);
}

if (!file_exists($package = API_FOLDER_TO . '{$directorySeparator}package.json')) {
    symlink(API_FOLDER_FROM . '{$directorySeparator}package.json', $package);
}

// symlink agar tidak dapat diakses folder pbb-app
if (!file_exists($htaccess = API_FOLDER_TO . '{$directorySeparator}.htaccess')) {
    symlink(API_FOLDER_FROM . '{$directorySeparator}public{$directorySeparator}.htaccess', $htaccess);
}

// symlink folder public
if (!file_exists($public_htaccess = API_FOLDER_TO . '{$directorySeparator}public{$directorySeparator}.htaccess')) {
    symlink(API_FOLDER_FROM . '{$directorySeparator}public{$directorySeparator}.htaccess', $public_htaccess);
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
