<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Read-only lists used in dropdowns: disaster types, donation types, payment methods.
 * Table names are fixed below (never taken from user input).
 */
final class Lookup extends Model
{
    private const TABLES = [
        'disaster_types'  => 'disaster_types',
        'donation_types'  => 'donation_types',
        'payment_methods' => 'payment_methods',
    ];

    public static function all(string $list): array
    {
        $table = self::TABLES[$list] ?? throw new \InvalidArgumentException("Unknown lookup list: {$list}");
        return self::fetchAll("SELECT id, name FROM {$table} ORDER BY id");
    }

    public static function exists(string $list, int $id): bool
    {
        $table = self::TABLES[$list] ?? throw new \InvalidArgumentException("Unknown lookup list: {$list}");
        return self::fetchOne("SELECT id FROM {$table} WHERE id = :id", ['id' => $id]) !== null;
    }
}
