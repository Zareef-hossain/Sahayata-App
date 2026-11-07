<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Maps URLs to controller methods.
 *
 *   $router->get('/donor/dashboard', [DonorController::class, 'dashboard'], ['role:donor']);
 *   $router->post('/api/requests/{id}/approve', [AdminController::class, 'approve'], ['role:admin']);
 *
 * Middleware (checked before the controller runs):
 *   'auth'        - must be logged in
 *   'guest'       - must NOT be logged in (login / register pages)
 *   'role:a,b'    - must be logged in with one of these roles
 * Every POST request is automatically checked for a valid CSRF token.
 */
final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, array $handler, array $middleware): void
    {
        // Turn "/requests/{id}" into a regular expression: "#^/requests/(?P<id>[0-9]+)$#"
        $pattern = preg_replace('#\{([a-z_]+)\}#', '(?P<$1>[0-9]+)', rtrim($path, '/') ?: '/');
        $this->routes[] = [
            'method'     => $method,
            'pattern'    => '#^' . $pattern . '$#',
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): void
    {
        $path = $request->path();
        $pathMatched = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }
            $pathMatched = true;
            if ($route['method'] !== $request->method()) {
                continue;
            }

            if ($request->method() === 'POST') {
                $this->verifyCsrf($request);
            }
            foreach ($route['middleware'] as $middleware) {
                $this->runMiddleware($middleware, $request);
            }

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            [$controllerClass, $method] = $route['handler'];
            (new $controllerClass($request))->$method(...array_map('intval', $params));
            return;
        }

        throw new HttpException($pathMatched ? 405 : 404, $pathMatched ? 'Method not allowed.' : '');
    }

    private function verifyCsrf(Request $request): void
    {
        $token = $request->input('_csrf') ?? $request->header('X-CSRF-Token');
        if (!Csrf::isValid(is_string($token) ? $token : null)) {
            // 403 (not the unofficial 419) because Apache turns unknown codes into 500.
            throw new HttpException(403, 'Your session expired. Please reload the page and try again.');
        }
    }

    private function runMiddleware(string $middleware, Request $request): void
    {
        [$name, $argument] = array_pad(explode(':', $middleware, 2), 2, '');

        match ($name) {
            'auth'  => $this->requireLogin($request),
            'guest' => $this->requireGuest($request),
            'role'  => $this->requireRole($request, explode(',', $argument)),
            default => throw new \LogicException("Unknown middleware: {$middleware}"),
        };
    }

    private function requireLogin(Request $request): void
    {
        // refresh() also logs out accounts that were deactivated since login.
        if (Auth::check() && Auth::refresh()) {
            return;
        }
        if ($request->wantsJson()) {
            throw new HttpException(401, 'Please log in first.');
        }
        if (!Session::hasFlash()) { // e.g. keep the "logged out after inactivity" message instead
            Session::flash('error', 'Please log in first.');
        }
        Response::redirect('');
    }

    private function requireGuest(Request $request): void
    {
        if (!Auth::check()) {
            return;
        }
        if ($request->wantsJson()) {
            throw new HttpException(403, 'You are already logged in.');
        }
        Response::redirect(Auth::homePath());
    }

    private function requireRole(Request $request, array $roles): void
    {
        $this->requireLogin($request);
        if (!Auth::hasRole(...$roles)) {
            throw new HttpException(403);
        }
    }
}
