<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Config;
use App\Core\Database;
use App\Core\Model;

/**
 * Demo one-time passwords for registration and password reset.
 *
 * DEMO ONLY: no SMS is sent. Real SMS delivery is not configured.
 * Only a hash of the code is stored, each code expires, and wrong guesses are limited.
 */
final class Otp extends Model
{
    public const VALID = 'valid';
    public const INVALID = 'invalid';
    public const EXPIRED = 'expired';
    public const TOO_MANY_ATTEMPTS = 'too_many_attempts';

    /** Seconds the user must still wait before a new code can be issued (0 = can issue now). */
    public static function secondsUntilResend(int $userId, string $purpose): int
    {
        $row = self::fetchOne(
            'SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) AS age
             FROM otp_codes WHERE user_id = :user_id AND purpose = :purpose
             ORDER BY id DESC LIMIT 1',
            ['user_id' => $userId, 'purpose' => $purpose]
        );
        if ($row === null) {
            return 0;
        }
        $wait = (int) Config::get('otp.resend_seconds', 60) - (int) $row['age'];
        return max(0, $wait);
    }

    /**
     * Create a new code (older unused codes stop working) and return it in plain text.
     * The plain code is only used to show it in demo mode; it is never stored.
     */
    public static function issue(int $userId, string $purpose): string
    {
        $length = (int) Config::get('otp.length', 4);
        $code = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);

        Database::transaction(static function () use ($userId, $purpose, $code): void {
            self::execute(
                'UPDATE otp_codes SET consumed_at = NOW()
                 WHERE user_id = :user_id AND purpose = :purpose AND consumed_at IS NULL',
                ['user_id' => $userId, 'purpose' => $purpose]
            );
            self::execute(
                'INSERT INTO otp_codes (user_id, purpose, code_hash, expires_at)
                 VALUES (:user_id, :purpose, :code_hash, DATE_ADD(NOW(), INTERVAL :minutes MINUTE))',
                [
                    'user_id'   => $userId,
                    'purpose'   => $purpose,
                    'code_hash' => password_hash($code, PASSWORD_DEFAULT),
                    'minutes'   => (int) Config::get('otp.ttl_minutes', 10),
                ]
            );
        });

        return $code;
    }

    /**
     * Check a code. On success the code is used up and cannot be used again.
     * Returns one of the constants above.
     */
    public static function verify(int $userId, string $purpose, string $code): string
    {
        return Database::transaction(static function () use ($userId, $purpose, $code): string {
            // FOR UPDATE locks the row so two requests cannot use the same code at once.
            $otp = self::fetchOne(
                'SELECT id, code_hash, attempts, expires_at < NOW() AS is_expired
                 FROM otp_codes
                 WHERE user_id = :user_id AND purpose = :purpose AND consumed_at IS NULL
                 ORDER BY id DESC LIMIT 1
                 FOR UPDATE',
                ['user_id' => $userId, 'purpose' => $purpose]
            );

            if ($otp === null || (int) $otp['is_expired'] === 1) {
                return self::EXPIRED;
            }
            if ((int) $otp['attempts'] >= (int) Config::get('otp.max_attempts', 5)) {
                return self::TOO_MANY_ATTEMPTS;
            }
            if (!password_verify($code, $otp['code_hash'])) {
                self::execute('UPDATE otp_codes SET attempts = attempts + 1 WHERE id = :id', ['id' => $otp['id']]);
                return self::INVALID;
            }

            self::execute('UPDATE otp_codes SET consumed_at = NOW() WHERE id = :id', ['id' => $otp['id']]);
            return self::VALID;
        });
    }

    /** User-friendly text for a verify() result. */
    public static function message(string $result): string
    {
        return match ($result) {
            self::VALID             => 'OTP verified.',
            self::INVALID           => 'The OTP is incorrect. Please try again.',
            self::EXPIRED           => 'The OTP has expired. Please request a new one.',
            self::TOO_MANY_ATTEMPTS => 'Too many wrong attempts. Please request a new OTP.',
            default                 => 'The OTP could not be checked.',
        };
    }
}
