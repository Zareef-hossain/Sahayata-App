<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Information about the current HTTP request (method, path, form data, JSON body).
 */
final class Request
{
    private ?array $jsonBody = null;

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /** Path inside the app, without the folder prefix: "/login", "/api/health". */
    public function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = self::basePath();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        $path = '/' . trim($path, '/');
        return $path === '/index.php' ? '/' : $path;
    }

    /**
     * Folder the app is served from, e.g. "/sahayata-app" or "/sahayata-app/public".
     * Works with XAMPP (htdocs subfolder) and with PHP's built-in server.
     */
    public static function basePath(): string
    {
        $configured = rtrim((string) Config::get('app.base_url', ''), '/');
        if ($configured !== '') {
            return $configured;
        }

        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        // Visited as /sahayata-app/public/...
        if ($scriptDir !== '' && str_starts_with($uriPath . '/', $scriptDir . '/')) {
            return $scriptDir;
        }
        // Visited as /sahayata-app/... (the root .htaccess forwards to public/)
        if (str_ends_with($scriptDir, '/public')) {
            $projectDir = substr($scriptDir, 0, -strlen('/public'));
            if ($projectDir === '' || str_starts_with($uriPath . '/', $projectDir . '/')) {
                return $projectDir;
            }
        }
        return '';
    }

    /** A value from the form (POST), the query string (GET) or the JSON body. */
    public function input(string $key, mixed $default = null): mixed
    {
        $value = $this->all()[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    /** All submitted values. */
    public function all(): array
    {
        return [...$_GET, ...$_POST, ...$this->json()];
    }

    public function json(): array
    {
        if ($this->jsonBody === null) {
            $this->jsonBody = [];
            if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
                $decoded = json_decode((string) file_get_contents('php://input'), true);
                $this->jsonBody = is_array($decoded) ? $decoded : [];
            }
        }
        return $this->jsonBody;
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$key] ?? null;
    }

    /** True for fetch() calls from our JavaScript and for /api/ routes. */
    public function wantsJson(): bool
    {
        return str_starts_with($this->path(), '/api/')
            || str_contains($this->header('Accept') ?? '', 'application/json');
    }
}
