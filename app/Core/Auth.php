<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Who is logged in. Only a few safe fields are kept in the session
 * (never the password hash).
 */
final class Auth
{
    private const SESSION_KEY = '_auth_user';

    /** Each role's home page after login. */
    public const ROLE_HOME = [
        'manager'   => 'manager/dashboard',
        'admin'     => 'admin/dashboard',
        'donor'     => 'donor/dashboard',
        'volunteer' => 'volunteer/tasks',
        'victim'    => 'victim/request-help',
    ];

    public static function login(array $user): void
    {
        Session::regenerate();
        Csrf::rotate();
        Session::set('_last_activity', time());
        Session::set(self::SESSION_KEY, [
            'id'        => (int) $user['id'],
            'full_name' => $user['full_name'],
            'email'     => $user['email'],
            'role'      => $user['role'],
        ]);
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    /**
     * Called on every request: logs the user out after config session.idle_minutes
     * without activity, otherwise records the current time as the last activity.
     */
    public static function enforceIdleTimeout(): void
    {
        if (!self::check()) {
            return;
        }
        $idleSeconds = (int) Config::get('session.idle_minutes', 60) * 60;
        $lastActivity = (int) Session::get('_last_activity', time());

        if (time() - $lastActivity > $idleSeconds) {
            self::logout();
            Session::start();
            Session::flash('error', 'You were logged out after a period of inactivity. Please log in again.');
            return;
        }
        Session::set('_last_activity', time());
    }

    /**
     * Re-check the logged-in account against the database (called for every
     * protected page). Logs the user out if the account was deactivated, deleted
     * or its role changed, so the session can never keep old permissions.
     */
    public static function refresh(): bool
    {
        $sessionUser = self::user();
        if ($sessionUser === null) {
            return false;
        }

        $user = \App\Models\User::find((int) $sessionUser['id']);
        if ($user === null || $user['status'] !== 'active' || $user['role'] !== $sessionUser['role']) {
            self::logout();
            Session::start();
            Session::flash('error', 'Your account is no longer active. Please contact the platform manager.');
            return false;
        }

        // Keep the displayed name up to date after profile changes.
        Session::set(self::SESSION_KEY, [...$sessionUser, 'full_name' => $user['full_name'], 'email' => $user['email']]);
        return true;
    }

    public static function user(): ?array
    {
        return Session::get(self::SESSION_KEY);
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function role(): ?string
    {
        return self::user()['role'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function hasRole(string ...$roles): bool
    {
        return in_array(self::role(), $roles, true);
    }

    public static function homePath(): string
    {
        return self::ROLE_HOME[self::role()] ?? '';
    }
}
