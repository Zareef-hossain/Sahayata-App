<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Accounts of all roles. Passwords are only ever stored as password_hash() output.
 */
final class User extends Model
{
    /** Columns returned to controllers (never includes password_hash). */
    private const PUBLIC_COLUMNS = 'u.id, u.full_name, u.email, u.phone, u.status, u.created_at, r.slug AS role, r.name AS role_name';

    public static function find(int $id): ?array
    {
        return self::fetchOne(
            'SELECT ' . self::PUBLIC_COLUMNS . ' FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = :id',
            ['id' => $id]
        );
    }

    public static function findByEmail(string $email): ?array
    {
        return self::fetchOne(
            'SELECT ' . self::PUBLIC_COLUMNS . ' FROM users u JOIN roles r ON r.id = u.role_id WHERE u.email = :email',
            ['email' => self::normalizeEmail($email)]
        );
    }

    /**
     * Check an email + password pair.
     * Returns the user (without the hash) when correct, otherwise null.
     */
    public static function verifyCredentials(string $email, string $password): ?array
    {
        $row = self::fetchOne(
            'SELECT u.id, u.password_hash FROM users u WHERE u.email = :email',
            ['email' => self::normalizeEmail($email)]
        );

        // Always run password_verify, even for unknown emails, so the response
        // time does not reveal which emails are registered.
        $hash = $row['password_hash'] ?? '$2y$10$OZ5xY/ezNp.wAPz.sEhFFu.P5au44TQCR/ZJyM7P1Gojl9N.D2l5W';
        if (!password_verify($password, $hash) || $row === null) {
            return null;
        }

        if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
            self::updatePassword((int) $row['id'], $password);
        }
        return self::find((int) $row['id']);
    }

    public static function emailExists(string $email): bool
    {
        return self::fetchOne('SELECT id FROM users WHERE email = :email', ['email' => self::normalizeEmail($email)]) !== null;
    }

    public static function phoneExists(string $phone): bool
    {
        return self::fetchOne('SELECT id FROM users WHERE phone = :phone', ['phone' => self::normalizePhone($phone)]) !== null;
    }

    /**
     * Create an account and return its ID.
     * Volunteers also get a volunteer_profiles row (call inside a transaction).
     */
    public static function create(array $data): int
    {
        $role = Role::findBySlug($data['role']) ?? throw new \InvalidArgumentException('Unknown role.');

        self::execute(
            'INSERT INTO users (role_id, full_name, email, phone, password_hash, status, created_by)
             VALUES (:role_id, :full_name, :email, :phone, :password_hash, :status, :created_by)',
            [
                'role_id'       => $role['id'],
                'full_name'     => trim($data['full_name']),
                'email'         => self::normalizeEmail($data['email']),
                'phone'         => self::normalizePhone($data['phone']),
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'status'        => $data['status'] ?? 'pending',
                'created_by'    => $data['created_by'] ?? null,
            ]
        );
        $userId = self::lastInsertId();

        if ($data['role'] === 'volunteer') {
            self::execute('INSERT INTO volunteer_profiles (user_id) VALUES (:id)', ['id' => $userId]);
        }
        return $userId;
    }

    public static function setStatus(int $id, string $status): void
    {
        self::execute('UPDATE users SET status = :status WHERE id = :id', ['status' => $status, 'id' => $id]);
    }

    public static function updatePassword(int $id, string $password): void
    {
        self::execute(
            'UPDATE users SET password_hash = :hash WHERE id = :id',
            ['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $id]
        );
    }

    /** Users of the given roles (admin "Users" tab), newest first. */
    public static function listByRoles(array $roles): array
    {
        $params = [];
        foreach (array_values($roles) as $index => $role) {
            $params['r' . $index] = $role;
        }
        $placeholders = implode(', ', array_map(static fn (string $key) => ':' . $key, array_keys($params)));

        return self::fetchAll(
            'SELECT ' . self::PUBLIC_COLUMNS . " FROM users u JOIN roles r ON r.id = u.role_id
             WHERE r.slug IN ({$placeholders}) ORDER BY u.created_at DESC, u.id DESC",
            $params
        );
    }

    /** Number of accounts per role: ['manager' => 1, 'admin' => 2, ...] (all roles listed). */
    public static function countByRole(): array
    {
        $rows = self::fetchAll(
            'SELECT r.slug, COUNT(u.id) AS total FROM roles r LEFT JOIN users u ON u.role_id = r.id GROUP BY r.id, r.slug'
        );
        return array_column(array_map(static fn (array $row) => [$row['slug'], (int) $row['total']], $rows), 1, 0);
    }

    /** Manager: activate or deactivate an ADMIN account (never other roles). */
    public static function setAdminStatus(int $adminId, string $status): void
    {
        if (!in_array($status, ['active', 'inactive'], true)) {
            throw new \InvalidArgumentException('Invalid status.');
        }
        $changed = self::execute(
            "UPDATE users u JOIN roles r ON r.id = u.role_id AND r.slug = 'admin'
             SET u.status = :status WHERE u.id = :id",
            ['status' => $status, 'id' => $adminId]
        );
        if ($changed === 0 && self::fetchOne(
            "SELECT u.id FROM users u JOIN roles r ON r.id = u.role_id AND r.slug = 'admin' WHERE u.id = :id",
            ['id' => $adminId]
        ) === null) {
            throw new \DomainException('Administrator not found.');
        }
    }

    public static function updateProfile(int $id, string $fullName, string $phone): void
    {
        self::execute(
            'UPDATE users SET full_name = :name, phone = :phone WHERE id = :id',
            ['name' => trim($fullName), 'phone' => self::normalizePhone($phone), 'id' => $id]
        );
    }

    /** Is this phone number used by a DIFFERENT account? */
    public static function phoneTakenByOther(string $phone, int $userId): bool
    {
        return self::fetchOne(
            'SELECT id FROM users WHERE phone = :phone AND id <> :id',
            ['phone' => self::normalizePhone($phone), 'id' => $userId]
        ) !== null;
    }

    public static function passwordMatches(int $id, string $password): bool
    {
        $row = self::fetchOne('SELECT password_hash FROM users WHERE id = :id', ['id' => $id]);
        return $row !== null && password_verify($password, $row['password_hash']);
    }

    public static function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    /** "+8801712345678" or "8801712345678" -> "01712345678" */
    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';
        return str_starts_with($digits, '880') ? substr($digits, 2) : $digits;
    }
}
