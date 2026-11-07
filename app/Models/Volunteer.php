<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use DomainException;

/**
 * Volunteer availability ("Available" / "Busy") and volunteer lists.
 */
final class Volunteer extends Model
{
    private const SELECT = "SELECT u.id, u.full_name, u.email, u.phone, u.status,
                                   vp.availability,
                                   (SELECT COUNT(*) FROM deliveries d
                                     WHERE d.volunteer_id = u.id AND d.status IN ('assigned', 'picked_up', 'in_transit')) AS active_tasks,
                                   (SELECT COUNT(*) FROM deliveries d
                                     WHERE d.volunteer_id = u.id AND d.status = 'delivered') AS delivered_tasks
                            FROM users u
                            JOIN roles r ON r.id = u.role_id AND r.slug = 'volunteer'
                            JOIN volunteer_profiles vp ON vp.user_id = u.id";

    /** All volunteer accounts (admin "Volunteers" tab). */
    public static function listAll(): array
    {
        return self::fetchAll(self::SELECT . ' ORDER BY u.full_name');
    }

    /** Active volunteers for "Assign Delivery": available ones first. */
    public static function listForAssignment(): array
    {
        return self::fetchAll(self::SELECT . " WHERE u.status = 'active' ORDER BY vp.availability = 'busy', u.full_name");
    }

    public static function availability(int $userId): ?string
    {
        return self::fetchOne('SELECT availability FROM volunteer_profiles WHERE user_id = :id', ['id' => $userId])['availability'] ?? null;
    }

    public static function setAvailability(int $userId, string $availability): void
    {
        if (!in_array($availability, ['available', 'busy'], true)) {
            throw new \InvalidArgumentException('Invalid availability.');
        }
        self::execute('UPDATE volunteer_profiles SET availability = :availability WHERE user_id = :id', ['availability' => $availability, 'id' => $userId]);
    }

    /**
     * Inside a transaction: lock the volunteer and make sure they can take a task.
     * Throws a message for the user when they cannot.
     */
    public static function lockAvailable(int $userId): void
    {
        $volunteer = self::fetchOne(
            "SELECT vp.availability, u.status FROM volunteer_profiles vp
             JOIN users u ON u.id = vp.user_id
             JOIN roles r ON r.id = u.role_id AND r.slug = 'volunteer'
             WHERE vp.user_id = :id FOR UPDATE",
            ['id' => $userId]
        );
        if ($volunteer === null) {
            throw new DomainException('Please choose a valid volunteer.');
        }
        if ($volunteer['status'] !== 'active') {
            throw new DomainException('This volunteer account is not active.');
        }
        if ($volunteer['availability'] !== 'available') {
            throw new DomainException('The volunteer is busy. Only available volunteers can take a task.');
        }
    }

    /** Become "available" again when the volunteer has no unfinished deliveries. */
    public static function refreshAvailability(int $userId): void
    {
        $active = self::fetchOne(
            "SELECT COUNT(*) AS total FROM deliveries WHERE volunteer_id = :id AND status IN ('assigned', 'picked_up', 'in_transit')",
            ['id' => $userId]
        );
        if ((int) $active['total'] === 0) {
            self::setAvailability($userId, 'available');
        }
    }
}
