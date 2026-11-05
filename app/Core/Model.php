<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Base class for models. A model is the only place that talks to the database.
 * Always use prepared statements with placeholders — never put user input
 * directly into SQL.
 */
abstract class Model
{
    protected static function db(): PDO
    {
        return Database::connection();
    }

    /** Run a query and return all rows. */
    protected static function fetchAll(string $sql, array $params = []): array
    {
        $statement = self::db()->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    /** Run a query and return the first row, or null. */
    protected static function fetchOne(string $sql, array $params = []): ?array
    {
        $statement = self::db()->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch();
        return $row === false ? null : $row;
    }

    /** Run an INSERT / UPDATE / DELETE and return the number of changed rows. */
    protected static function execute(string $sql, array $params = []): int
    {
        $statement = self::db()->prepare($sql);
        $statement->execute($params);
        return $statement->rowCount();
    }

    protected static function lastInsertId(): int
    {
        return (int) self::db()->lastInsertId();
    }
}
