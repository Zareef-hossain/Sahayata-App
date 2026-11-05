<?php
/**
 * Bootstrap: runs once at the start of every request.
 * Sets up class autoloading, configuration, error handling and the session.
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

// Autoload classes: App\Core\Router -> app/Core/Router.php
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relativePath = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = APP_ROOT . '/app/' . $relativePath . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require APP_ROOT . '/app/helpers.php';

use App\Core\Config;
use App\Core\ErrorHandler;
use App\Core\Session;

$configFile = APP_ROOT . '/config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit("Setup needed: copy config/config.example.php to config/config.php, then edit the database settings.\n");
}

Config::load($configFile);
date_default_timezone_set((string) Config::get('app.timezone', 'UTC'));

ErrorHandler::register((bool) Config::get('app.debug', false));
Session::start();
App\Core\Auth::enforceIdleTimeout();
