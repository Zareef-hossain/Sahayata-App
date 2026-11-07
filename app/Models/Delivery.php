<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use DomainException;

/**
 * Delivery of relief for one approved help request.
 * Statuses: pending -> assigned -> picked_up -> in_transit -> delivered
 * The help request status follows the delivery status from "assigned" on.
 */
final class Delivery extends Model
{
    public const STATUS_LABELS = [
        'pending'    => 'Pending',
        'assigned'   => 'Assigned',
        'picked_up'  => 'Picked Up',
        'in_transit' => 'In Transit',
        'delivered'  => 'Delivered',
    ];

    /** Position of each status; a delivery may only move forward. */
    private const ORDER = ['pending' => 0, 'assigned' => 1, 'picked_up' => 2, 'in_transit' => 3, 'delivered' => 4];

    public const ACTIVE = ['assigned', 'picked_up', 'in_transit'];

    private const SELECT = 'SELECT d.*,
                                   hr.location, hr.urgency, hr.description, hr.contact_phone, hr.victim_id,
                                   hr.status AS request_status, dt.name AS disaster_type,
                                   victim.full_name AS victim_name,
                                   volunteer.full_name AS volunteer_name, volunteer.phone AS volunteer_phone,
                                   donation.amount_bdt, donation_type.name AS donation_type,
                                   donor.full_name AS donor_name
                            FROM deliveries d
                            JOIN help_requests hr ON hr.id = d.help_request_id
                            JOIN disaster_types dt ON dt.id = hr.disaster_type_id
                            JOIN users victim ON victim.id = hr.victim_id
                            LEFT JOIN users volunteer ON volunteer.id = d.volunteer_id
                            LEFT JOIN donations donation ON donation.id = d.donation_id
                            LEFT JOIN donation_types donation_type ON donation_type.id = donation.donation_type_id
                            LEFT JOIN users donor ON donor.id = donation.donor_id';

    public static function find(int $id): ?array
    {
        return self::fetchOne(self::SELECT . ' WHERE d.id = :id', ['id' => $id]);
    }

    public static function findByRequest(int $requestId): ?array
    {
        return self::fetchOne(self::SELECT . ' WHERE d.help_request_id = :id', ['id' => $requestId]);
    }

    /** Deliveries waiting for a volunteer ("Pending Deliveries near you"). */
    public static function listPending(): array
    {
        return self::fetchAll(self::SELECT . " WHERE d.status = 'pending' ORDER BY FIELD(hr.urgency, 'high', 'medium', 'low'), d.created_at");
    }

    public static function listActiveForVolunteer(int $volunteerId): array
    {
        return self::fetchAll(
            self::SELECT . " WHERE d.volunteer_id = :volunteer AND d.status IN ('assigned', 'picked_up', 'in_transit') ORDER BY d.assigned_at",
            ['volunteer' => $volunteerId]
        );
    }

    public static function listDeliveredForVolunteer(int $volunteerId): array
    {
        return self::fetchAll(
            self::SELECT . " WHERE d.volunteer_id = :volunteer AND d.status = 'delivered' ORDER BY d.delivered_at DESC",
            ['volunteer' => $volunteerId]
        );
    }

    /** All deliveries for the admin's monitoring list (unfinished first). */
    public static function listAll(): array
    {
        return self::fetchAll(self::SELECT . " ORDER BY d.status = 'delivered', d.updated_at DESC");
    }

    public static function countDelivered(): int
    {
        return (int) self::fetchOne("SELECT COUNT(*) AS total FROM deliveries WHERE status = 'delivered'")['total'];
    }

    /** Short text for what is being delivered, e.g. "Food (BDT 5,000.00)". */
    public static function itemsText(array $delivery): string
    {
        if (!empty($delivery['items_description'])) {
            return $delivery['items_description'];
        }
        if (!empty($delivery['donation_type'])) {
            return $delivery['donation_type'] . ' (' . money($delivery['amount_bdt']) . ')';
        }
        return 'Relief for ' . $delivery['disaster_type'];
    }

    /**
     * Called inside the donation transaction: make sure the request has a delivery
     * and link the donation to it if it has none yet.
     */
    public static function attachDonation(int $requestId, int $donationId): void
    {
        $delivery = self::fetchOne('SELECT id, donation_id FROM deliveries WHERE help_request_id = :id FOR UPDATE', ['id' => $requestId]);
        if ($delivery === null) {
            self::execute(
                "INSERT INTO deliveries (help_request_id, donation_id, status) VALUES (:request, :donation, 'pending')",
                ['request' => $requestId, 'donation' => $donationId]
            );
        } elseif ($delivery['donation_id'] === null) {
            self::execute('UPDATE deliveries SET donation_id = :donation WHERE id = :id', ['donation' => $donationId, 'id' => $delivery['id']]);
        }
    }

    /** Admin "Assign to this Task": give the request's delivery to a volunteer. */
    public static function assign(int $requestId, int $volunteerId, int $adminId): void
    {
        Database::transaction(static function () use ($requestId, $volunteerId, $adminId): void {
            $request = self::fetchOne('SELECT status FROM help_requests WHERE id = :id FOR UPDATE', ['id' => $requestId]);
            if ($request === null || !in_array($request['status'], ['approved', 'assigned'], true)) {
                throw new DomainException('Only approved requests that have not been picked up can be assigned.');
            }
            Volunteer::lockAvailable($volunteerId);

            $delivery = self::fetchOne('SELECT id, volunteer_id, status FROM deliveries WHERE help_request_id = :id FOR UPDATE', ['id' => $requestId]);
            if ($delivery === null) {
                self::execute(
                    "INSERT INTO deliveries (help_request_id, volunteer_id, assigned_by, status, assigned_at)
                     VALUES (:request, :volunteer, :admin, 'assigned', NOW())",
                    ['request' => $requestId, 'volunteer' => $volunteerId, 'admin' => $adminId]
                );
            } else {
                if (!in_array($delivery['status'], ['pending', 'assigned'], true)) {
                    throw new DomainException('This delivery is already on its way and cannot be reassigned.');
                }
                self::execute(
                    "UPDATE deliveries SET volunteer_id = :volunteer, assigned_by = :admin, status = 'assigned', assigned_at = NOW()
                     WHERE id = :id",
                    ['volunteer' => $volunteerId, 'admin' => $adminId, 'id' => $delivery['id']]
                );
                // The previously assigned volunteer becomes free again.
                if ($delivery['volunteer_id'] !== null && (int) $delivery['volunteer_id'] !== $volunteerId) {
                    Volunteer::refreshAvailability((int) $delivery['volunteer_id']);
                }
            }

            Volunteer::setAvailability($volunteerId, 'busy');
            if ($request['status'] !== 'assigned') {
                HelpRequest::setStatus($requestId, 'assigned', $adminId);
            }
        });
    }

    /** Volunteer "Accept Task" on a pending delivery. */
    public static function accept(int $deliveryId, int $volunteerId): void
    {
        Database::transaction(static function () use ($deliveryId, $volunteerId): void {
            $delivery = self::fetchOne('SELECT id, help_request_id, status FROM deliveries WHERE id = :id FOR UPDATE', ['id' => $deliveryId]);
            if ($delivery === null || $delivery['status'] !== 'pending') {
                throw new DomainException('This task has already been taken by another volunteer.');
            }
            Volunteer::lockAvailable($volunteerId);

            self::execute(
                "UPDATE deliveries SET volunteer_id = :volunteer, status = 'assigned', assigned_at = NOW() WHERE id = :id",
                ['volunteer' => $volunteerId, 'id' => $deliveryId]
            );
            Volunteer::setAvailability($volunteerId, 'busy');
            HelpRequest::setStatus((int) $delivery['help_request_id'], 'assigned', $volunteerId);
        });
    }

    /** Volunteer moves their delivery forward: picked_up -> in_transit -> delivered. */
    public static function updateStatus(int $deliveryId, int $volunteerId, string $newStatus): void
    {
        if (!in_array($newStatus, ['picked_up', 'in_transit', 'delivered'], true)) {
            throw new DomainException('Please choose a valid status.');
        }

        Database::transaction(static function () use ($deliveryId, $volunteerId, $newStatus): void {
            $delivery = self::fetchOne(
                'SELECT id, help_request_id, volunteer_id, status FROM deliveries WHERE id = :id FOR UPDATE',
                ['id' => $deliveryId]
            );
            if ($delivery === null || (int) $delivery['volunteer_id'] !== $volunteerId) {
                throw new DomainException('This delivery is not assigned to you.');
            }
            if (self::ORDER[$newStatus] <= self::ORDER[$delivery['status']]) {
                throw new DomainException('A delivery can only move forward. Current status: '
                    . self::STATUS_LABELS[$delivery['status']] . '.');
            }

            self::execute(
                'UPDATE deliveries SET status = :status, delivered_at = IF(:status2 = \'delivered\', NOW(), delivered_at) WHERE id = :id',
                ['status' => $newStatus, 'status2' => $newStatus, 'id' => $deliveryId]
            );
            HelpRequest::setStatus((int) $delivery['help_request_id'], $newStatus, $volunteerId);

            if ($newStatus === 'delivered') {
                Volunteer::refreshAvailability($volunteerId);
            }
        });
    }

    /** Statuses a volunteer may choose next, e.g. after 'assigned': picked_up, in_transit, delivered. */
    public static function nextStatuses(string $current): array
    {
        return array_filter(
            ['picked_up' => 'Picked Up', 'in_transit' => 'In Transit', 'delivered' => 'Delivered'],
            static fn (string $status) => self::ORDER[$status] > self::ORDER[$current],
            ARRAY_FILTER_USE_KEY
        );
    }
}
