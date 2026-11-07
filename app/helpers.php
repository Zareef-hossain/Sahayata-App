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

/** Readable reference number: ref('REQ', 5) -> "#REQ-5" */
function ref(string $prefix, int|string $id): string
{
    return '#' . $prefix . '-' . (int) $id;
}

/** Amount in Bangladeshi Taka: money(5000) -> "BDT 5,000.00" */
function money(float|int|string $amount): string
{
    return 'BDT ' . number_format((float) $amount, 2);
}

/** Short date: "05 Nov 2026, 3:40 PM" */
function short_date(?string $datetime): string
{
    return $datetime ? date('d M Y, g:i A', strtotime($datetime)) : '';
}

/** Render a reusable component from app/Views/components. */
function component(string $name, array $props = []): void
{
    echo View::component($name, $props);
}
