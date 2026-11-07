<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use DomainException;

/**
 * A victim's "Request for Help" and its status history.
 * Workflow: pending -> approved / rejected -> assigned -> picked_up -> in_transit -> delivered
 */
final class HelpRequest extends Model
{
    public const STATUS_LABELS = [
        'pending'    => 'Pending',
        'approved'   => 'Approved',
        'rejected'   => 'Rejected',
        'assigned'   => 'Assigned',
        'picked_up'  => 'Picked Up',
        'in_transit' => 'In Transit',
        'delivered'  => 'Delivered',
    ];

    public const URGENCY_LABELS = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'];

    /** Requests that donors can still support with "Donate Now". */
    public const OPEN_FOR_DONATION = ['approved', 'assigned'];

    private const SELECT = 'SELECT hr.*, dt.name AS disaster_type,
                                   victim.full_name AS victim_name, victim.phone AS victim_phone
                            FROM help_requests hr
                            JOIN disaster_types dt ON dt.id = hr.disaster_type_id
                            JOIN users victim ON victim.id = hr.victim_id';

    /** Most urgent first, then oldest first. */
    private const URGENCY_ORDER = "FIELD(hr.urgency, 'high', 'medium', 'low'), hr.created_at";

    public static function create(int $victimId, array $data): int
    {
        return Database::transaction(static function () use ($victimId, $data): int {
            self::execute(
                'INSERT INTO help_requests
                    (victim_id, disaster_type_id, urgency, location, latitude, longitude, contact_phone, description)
                 VALUES (:victim_id, :disaster_type_id, :urgency, :location, :latitude, :longitude, :contact_phone, :description)',
                [
                    'victim_id'        => $victimId,
                    'disaster_type_id' => (int) $data['disaster_type_id'],
                    'urgency'          => $data['urgency'],
                    'location'         => trim($data['location']),
                    'latitude'         => $data['latitude'] ?? null,
                    'longitude'        => $data['longitude'] ?? null,
                    'contact_phone'    => User::normalizePhone($data['contact_phone']),
                    'description'      => trim($data['description']),
                ]
            );
            $requestId = self::lastInsertId();
            self::addHistory($requestId, 'pending', $victimId);
            return $requestId;
        });
    }

    public static function find(int $id): ?array
    {
        return self::fetchOne(self::SELECT . ' WHERE hr.id = :id', ['id' => $id]);
    }

    /** A request only if it belongs to this victim (victims can never see others' requests). */
    public static function findForVictim(int $id, int $victimId): ?array
    {
        return self::fetchOne(self::SELECT . ' WHERE hr.id = :id AND hr.victim_id = :victim_id', ['id' => $id, 'victim_id' => $victimId]);
    }

    public static function listForVictim(int $victimId): array
    {
        return self::fetchAll(self::SELECT . ' WHERE hr.victim_id = :victim_id ORDER BY hr.created_at DESC, hr.id DESC', ['victim_id' => $victimId]);
    }

    /** Requests in any of the given statuses, most urgent first. */
    public static function listByStatus(array $statuses): array
    {
        [$placeholders, $params] = self::inList($statuses);
        return self::fetchAll(self::SELECT . " WHERE hr.status IN ({$placeholders}) ORDER BY " . self::URGENCY_ORDER, $params);
    }

    public static function countByStatus(string $status): int
    {
        return (int) self::fetchOne('SELECT COUNT(*) AS total FROM help_requests WHERE status = :status', ['status' => $status])['total'];
    }

    /**
     * Admin decision on a pending request: 'approved' or 'rejected'.
     * Locked with FOR UPDATE so two admins cannot decide the same request at once.
     */
    public static function review(int $id, int $adminId, string $decision): void
    {
        if (!in_array($decision, ['approved', 'rejected'], true)) {
            throw new \InvalidArgumentException('Invalid decision.');
        }

        Database::transaction(static function () use ($id, $adminId, $decision): void {
            $request = self::fetchOne('SELECT status FROM help_requests WHERE id = :id FOR UPDATE', ['id' => $id]);
            if ($request === null) {
                throw new DomainException('This request no longer exists.');
            }
            if ($request['status'] !== 'pending') {
                throw new DomainException('This request has already been reviewed.');
            }
            self::execute(
                'UPDATE help_requests SET status = :status, reviewed_by = :admin, reviewed_at = NOW() WHERE id = :id',
                ['status' => $decision, 'admin' => $adminId, 'id' => $id]
            );
            self::addHistory($id, $decision, $adminId);
        });
    }

    /** Change the status (used by the delivery workflow, inside its transaction). */
    public static function setStatus(int $id, string $status, ?int $changedBy): void
    {
        self::execute('UPDATE help_requests SET status = :status WHERE id = :id', ['status' => $status, 'id' => $id]);
        self::addHistory($id, $status, $changedBy);
    }

    /** Status changes, oldest first: [['status' => 'pending', 'created_at' => ...], ...] */
    public static function history(int $id): array
    {
        return self::fetchAll(
            'SELECT status, created_at FROM request_status_history WHERE help_request_id = :id ORDER BY id',
            ['id' => $id]
        );
    }

    private static function addHistory(int $requestId, string $status, ?int $changedBy): void
    {
        self::execute(
            'INSERT INTO request_status_history (help_request_id, status, changed_by) VALUES (:request, :status, :user)',
            ['request' => $requestId, 'status' => $status, 'user' => $changedBy]
        );
    }

    /** Build ":p0, :p1, ..." placeholders for an IN (...) list. */
    private static function inList(array $values): array
    {
        $params = [];
        foreach (array_values($values) as $index => $value) {
            $params['p' . $index] = $value;
        }
        return [implode(', ', array_map(static fn (string $key) => ':' . $key, array_keys($params))), $params];
    }
}
