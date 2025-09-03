<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/** folder master pbb */
define('OPENDK_FOLDER_FROM', '{$opendkFolderFrom}{$opendkFolder}');

/** folder pbb per masing masing desa*/
define('OPENDK_FOLDER_TO', '{$opendkFolderTo}');

/**
 * -------------------------------------------------------------------
 * LOAD SYMLINK
 * -------------------------------------------------------------------
 * */

if (!file_exists($app = OPENDK_FOLDER_TO . '{$directorySeparator}app')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}app', $app);
}

if (!file_exists($config = OPENDK_FOLDER_TO . '{$directorySeparator}config')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}config', $config);
}

if (!file_exists($database = OPENDK_FOLDER_TO . '{$directorySeparator}database')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}database', $database);
}

if (!file_exists($helpers = OPENDK_FOLDER_TO . '{$directorySeparator}helpers')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}helpers', $helpers);
}

if (!file_exists($lang = OPENDK_FOLDER_TO . '{$directorySeparator}lang')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}lang', $lang);
}

if (!file_exists($resources = OPENDK_FOLDER_TO . '{$directorySeparator}resources')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}resources', $resources);
}

if (!file_exists($routes = OPENDK_FOLDER_TO . '{$directorySeparator}routes')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}routes', $routes);
}

if (!file_exists($stubs = OPENDK_FOLDER_TO . '{$directorySeparator}stubs')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}stubs', $stubs);
}

if (!file_exists($tests = OPENDK_FOLDER_TO . '{$directorySeparator}tests')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}tests', $tests);
}

if (!file_exists($themes = OPENDK_FOLDER_TO . '{$directorySeparator}themes')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}themes', $themes);
}

if (!file_exists($vendor = OPENDK_FOLDER_TO . '{$directorySeparator}vendor')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}vendor', $vendor);
}

if (!file_exists($gitattributes = OPENDK_FOLDER_TO . '{$directorySeparator}.gitattributes')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}.gitattributes', $gitattributes);
}

if (!file_exists($gitignore = OPENDK_FOLDER_TO . '{$directorySeparator}.gitignore')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}.gitignore', $gitignore);
}

if (!file_exists($phpfixer = OPENDK_FOLDER_TO . '{$directorySeparator}.php-cs-fixer.php')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}.php-cs-fixer.php', $phpfixer);
}

if (!file_exists($prettierr = OPENDK_FOLDER_TO . '{$directorySeparator}.prettierr.json')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}.prettierr.json', $prettierr);
}

if (!file_exists($catatan_rilis = OPENDK_FOLDER_TO . '{$directorySeparator}catatan_rilis.md')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}catatan_rilis.md', $catatan_rilis);
}

if (!file_exists($composer = OPENDK_FOLDER_TO . '{$directorySeparator}composer.json')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}composer.json', $composer);
}

if (!file_exists($package = OPENDK_FOLDER_TO . '{$directorySeparator}package.json')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}package.json', $package);
}

// symlink folder public
if (!file_exists($bower_components = OPENDK_FOLDER_TO . '{$directorySeparator}public{$directorySeparator}bower_components')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}public{$directorySeparator}bower_components', $bower_components);
}

if (!file_exists($css = OPENDK_FOLDER_TO . '{$directorySeparator}public{$directorySeparator}css')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}public{$directorySeparator}css', $css);
}

if (!file_exists($installer = OPENDK_FOLDER_TO . '{$directorySeparator}public{$directorySeparator}installer')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}public{$directorySeparator}installer', $installer);
}

if (!file_exists($js = OPENDK_FOLDER_TO . '{$directorySeparator}public{$directorySeparator}js')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}public{$directorySeparator}js', $js);
}

if (!file_exists($themes = OPENDK_FOLDER_TO . '{$directorySeparator}public{$directorySeparator}themes')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}public{$directorySeparator}themes', $themes);
}

if (!file_exists($vendor = OPENDK_FOLDER_TO . '{$directorySeparator}public{$directorySeparator}vendor')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}public{$directorySeparator}vendor', $vendor);
}

if (!file_exists($public_htaccess = OPENDK_FOLDER_TO . '{$directorySeparator}public{$directorySeparator}.htaccess')) {
    symlink(OPENDK_FOLDER_FROM . '{$directorySeparator}public{$directorySeparator}.htaccess', $public_htaccess);
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

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
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

require __DIR__.'/../vendor/autoload.php';

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

$app = require_once __DIR__.'/../bootstrap/app.php';

// https://laracasts.com/discuss/channels/general-discussion/where-do-you-set-public-directory-laravel-5
$app->bind('path.public', function () {
    return __DIR__;
});

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
