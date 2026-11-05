<?php
/**
 * Front controller: every page request comes through this file.
 */

declare(strict_types=1);

// PHP built-in server (php -S): let it serve real files such as CSS and images directly.
if (PHP_SAPI === 'cli-server') {
    $requested = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($requested)) {
        return false;
    }
}

require dirname(__DIR__) . '/app/bootstrap.php';

/** @var App\Core\Router $router */
$router = require APP_ROOT . '/app/routes.php';
$router->dispatch(new App\Core\Request());
