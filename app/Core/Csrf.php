<?php

declare(strict_types=1);

namespace App\Core;

/**
 * CSRF protection: every form and every JavaScript POST must send a secret token
 * that only our own pages know. The Router checks it on every POST request.
 */
final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        $token = Session::get(self::SESSION_KEY);
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set(self::SESSION_KEY, $token);
        }
        return $token;
    }

    public static function isValid(?string $submittedToken): bool
    {
        $expected = Session::get(self::SESSION_KEY);
        return is_string($expected)
            && is_string($submittedToken)
            && hash_equals($expected, $submittedToken); // timing-safe comparison
    }

    /** Create a fresh token (used after login/logout). */
    public static function rotate(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}
