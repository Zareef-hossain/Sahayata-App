<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Helpers for sending responses: JSON for the API, redirects for forms.
 */
final class Response
{
    /**
     * Send JSON. All API responses use the same shape:
     *   { "success": true,  "data": {...},  "message": "..." }
     *   { "success": false, "errors": {...}, "message": "..." }
     */
    public static function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function redirect(string $path): never
    {
        header('Location: ' . url($path));
        exit;
    }
}
