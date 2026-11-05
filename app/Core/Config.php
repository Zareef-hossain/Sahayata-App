<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Reads settings from config/config.php.
 * Usage: Config::get('db.host')
 */
final class Config
{
    private static array $settings = [];

    public static function load(string $file): void
    {
        if (!is_file($file)) {
            throw new RuntimeException(
                'Missing config/config.php. Copy config/config.example.php to config/config.php and edit it.'
            );
        }
        self::$settings = require $file;
    }

    /** Get a value using "dot" notation, e.g. 'app.debug'. */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::$settings;
        foreach (explode('.', $key) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }
        return $value;
    }
}
