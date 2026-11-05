<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** The five account types (manager, admin, donor, volunteer, victim). */
final class Role extends Model
{
    public static function all(): array
    {
        return self::fetchAll('SELECT id, slug, name FROM roles ORDER BY id');
    }

    public static function findBySlug(string $slug): ?array
    {
        return self::fetchOne('SELECT id, slug, name FROM roles WHERE slug = :slug', ['slug' => $slug]);
    }
}
