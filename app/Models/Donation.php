<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use DomainException;

/**
 * Donations. DEMO ONLY: payments are recorded in the database with a generated
 * reference; no real bKash, Nagad, Rocket or card payment is made.
 */
final class Donation extends Model
{
    public const MAX_AMOUNT = 1000000; // BDT, per donation

    private const SELECT = 'SELECT dn.*, dt.name AS donation_type, pm.name AS payment_method,
                                   donor.full_name AS donor_name,
                                   hr.location AS request_location, dis.name AS request_disaster
                            FROM donations dn
                            JOIN donation_types dt ON dt.id = dn.donation_type_id
                            JOIN payment_methods pm ON pm.id = dn.payment_method_id
                            JOIN users donor ON donor.id = dn.donor_id
                            LEFT JOIN help_requests hr ON hr.id = dn.help_request_id
                            LEFT JOIN disaster_types dis ON dis.id = hr.disaster_type_id';

    /**
     * Record a demo donation. If it is for a help request, the request must still
     * be open for donations, and a delivery is created for it (one transaction).
     * Returns ['id' => ..., 'reference' => ...].
     */
    public static function create(int $donorId, array $data): array
    {
        return Database::transaction(static function () use ($donorId, $data): array {
            $requestId = !empty($data['help_request_id']) ? (int) $data['help_request_id'] : null;

            if ($requestId !== null) {
                $request = self::fetchOne('SELECT status FROM help_requests WHERE id = :id FOR UPDATE', ['id' => $requestId]);
                if ($request === null || !in_array($request['status'], HelpRequest::OPEN_FOR_DONATION, true)) {
                    throw new DomainException('This request is no longer accepting donations.');
                }
            }

            $reference = 'DEMO-' . strtoupper(bin2hex(random_bytes(5)));
            self::execute(
                'INSERT INTO donations
                    (donor_id, help_request_id, donation_type_id, amount_bdt, payment_method_id, payer_mobile, demo_reference)
                 VALUES (:donor, :request, :type, :amount, :method, :mobile, :reference)',
                [
                    'donor'     => $donorId,
                    'request'   => $requestId,
                    'type'      => (int) $data['donation_type_id'],
                    'amount'    => number_format((float) $data['amount_bdt'], 2, '.', ''),
                    'method'    => (int) $data['payment_method_id'],
                    'mobile'    => User::normalizePhone($data['payer_mobile']),
                    'reference' => $reference,
                ]
            );
            $donationId = self::lastInsertId();

            if ($requestId !== null) {
                Delivery::attachDonation($requestId, $donationId);
            }
            return ['id' => $donationId, 'reference' => $reference];
        });
    }

    public static function listForDonor(int $donorId): array
    {
        return self::fetchAll(self::SELECT . ' WHERE dn.donor_id = :donor ORDER BY dn.created_at DESC, dn.id DESC', ['donor' => $donorId]);
    }

    public static function listAll(): array
    {
        return self::fetchAll(self::SELECT . ' ORDER BY dn.created_at DESC, dn.id DESC');
    }

    /** Most recent donation for a request (shown on "Assign Delivery"). */
    public static function latestForRequest(int $requestId): ?array
    {
        return self::fetchOne(self::SELECT . ' WHERE dn.help_request_id = :id ORDER BY dn.id DESC LIMIT 1', ['id' => $requestId]);
    }

    /** ['count' => number of donations, 'total' => sum in BDT] */
    public static function totals(?int $donorId = null): array
    {
        $row = $donorId === null
            ? self::fetchOne('SELECT COUNT(*) AS count, COALESCE(SUM(amount_bdt), 0) AS total FROM donations')
            : self::fetchOne('SELECT COUNT(*) AS count, COALESCE(SUM(amount_bdt), 0) AS total FROM donations WHERE donor_id = :donor', ['donor' => $donorId]);
        return ['count' => (int) $row['count'], 'total' => (float) $row['total']];
    }
}
