<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Wraps PHP's $_SESSION with safe cookie settings and "flash" messages
 * (messages that are shown once on the next page, e.g. "Request submitted").
 */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE || PHP_SAPI === 'cli') {
            return;
        }

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        session_name((string) Config::get('session.name', 'sahayata_session'));
        session_set_cookie_params([
            'lifetime' => (int) Config::get('session.lifetime', 0),
            'path'     => '/',
            'secure'   => $isHttps,  // only send over HTTPS when the site uses HTTPS
            'httponly' => true,      // JavaScript cannot read the session cookie
            'samesite' => 'Lax',     // basic protection against cross-site requests
        ]);
        ini_set('session.use_strict_mode', '1');
        session_start();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /** Give the user a new session ID (call after login to stop session fixation). */
    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 3600,
                'path'     => $params['path'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'],
            ]);
            session_destroy();
        }
    }

    /** Store a one-time message: type is 'success' or 'error'. */
    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }

    public static function hasFlash(): bool
    {
        return !empty($_SESSION['_flash']);
    }

    /** Read and remove all one-time messages. */
    public static function pullFlash(): array
    {
        $messages = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $messages;
    }

    /** Remember submitted form values and errors so the form can be refilled. */
    public static function flashInput(array $input, array $errors = []): void
    {
        unset($input['_csrf'], $input['password'], $input['password_confirmation']);
        $_SESSION['_old'] = $input;
        $_SESSION['_errors'] = $errors;
    }

    /** Read and remove the remembered form values and errors. */
    public static function pullInput(): array
    {
        $data = ['old' => $_SESSION['_old'] ?? [], 'errors' => $_SESSION['_errors'] ?? []];
        unset($_SESSION['_old'], $_SESSION['_errors']);
        return $data;
    }
}
