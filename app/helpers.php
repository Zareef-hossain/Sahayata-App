<?php
/**
 * Small global helper functions used in views and controllers.
 */

declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\View;

/** Escape text before printing it in HTML (prevents XSS). */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Build a link inside the app, e.g. url('login') -> /sahayata-app/login */
function url(string $path = ''): string
{
    $base = rtrim((string) Config::get('app.base_url', ''), '/');
    if ($base === '') {
        $base = Request::basePath();
    }
    return $base . '/' . ltrim($path, '/');
}

/** Link to a file in public/assets, with a version so browsers reload changes. */
function asset(string $path): string
{
    $file = APP_ROOT . '/public/assets/' . ltrim($path, '/');
    $version = is_file($file) ? (string) filemtime($file) : '1';
    return url('assets/' . ltrim($path, '/')) . '?v=' . $version;
}

/** Hidden form input that carries the CSRF token. */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">';
}

/** Render a reusable component from app/Views/components. */
function component(string $name, array $props = []): void
{
    echo View::component($name, $props);
}
