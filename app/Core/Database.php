<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Creates one shared PDO connection for the whole request.
 * PDO + prepared statements protect against SQL injection.
 */
final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                Config::get('db.host'),
                (int) Config::get('db.port', 3306),
                Config::get('db.name'),
                Config::get('db.charset', 'utf8mb4')
            );

            self::$connection = new PDO($dsn, (string) Config::get('db.user'), (string) Config::get('db.password'), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw errors instead of failing silently
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // rows come back as ['column' => value]
                PDO::ATTR_EMULATE_PREPARES   => false,                  // use real prepared statements
            ]);
        }
        return self::$connection;
    }

    /**
     * Run several queries as one unit: either all succeed or none are saved.
     * Usage: Database::transaction(fn (PDO $db) => ...);
     *
     * If a transaction is already running (one model method calling another),
     * the work simply joins it, and the outer transaction commits or rolls back.
     */
    public static function transaction(callable $work): mixed
    {
        $db = self::connection();
        if ($db->inTransaction()) {
            return $work($db);
        }

        $db->beginTransaction();
        try {
            $result = $work($db);
            $db->commit();
            return $result;
        } catch (\Throwable $error) {
            $db->rollBack();
            throw $error;
        }
    }
}
