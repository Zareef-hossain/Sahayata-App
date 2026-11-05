<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Config;
use App\Core\Model;

/**
 * Counts failed logins so that passwords cannot be guessed endlessly.
 * Limits: config login.max_attempts per email, and 4x that per IP address,
 * within config login.window_minutes.
 */
final class LoginAttempt extends Model
{
    public static function isBlocked(string $email, string $ip): bool
    {
        $window = (int) Config::get('login.window_minutes', 15);
        $maxPerEmail = (int) Config::get('login.max_attempts', 5);

        $row = self::fetchOne(
            'SELECT
                SUM(email = :email) AS by_email,
                SUM(ip_address = :ip) AS by_ip
             FROM login_attempts
             WHERE attempted_at > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)
               AND (email = :email2 OR ip_address = :ip2)',
            ['email' => $email, 'ip' => $ip, 'email2' => $email, 'ip2' => $ip, 'minutes' => $window]
        );

        return (int) ($row['by_email'] ?? 0) >= $maxPerEmail
            || (int) ($row['by_ip'] ?? 0) >= $maxPerEmail * 4;
    }

    public static function record(string $email, string $ip): void
    {
        self::execute(
            'INSERT INTO login_attempts (email, ip_address) VALUES (:email, :ip)',
            ['email' => $email, 'ip' => $ip]
        );
    }

    /** Forget failed attempts for this email after a successful login. */
    public static function clear(string $email): void
    {
        self::execute('DELETE FROM login_attempts WHERE email = :email', ['email' => $email]);
    }
}
