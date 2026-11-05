<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Role;

/** Technical endpoints used to check that the installation works. */
final class SystemController extends Controller
{
    /** GET /api/health — confirms PHP and the database connection are working. */
    public function health(): never
    {
        try {
            Database::connection()->query('SELECT 1');
            $roles = array_column(Role::all(), 'slug');
            $this->json([
                'php'      => PHP_VERSION,
                'database' => 'connected',
                'roles'    => $roles,
            ], 'Sahayata is running.');
        } catch (\PDOException $error) {
            error_log((string) $error);
            $this->jsonError('Database connection failed. Check config/config.php and that MySQL is running.', [], 503);
        }
    }

    /** POST /api/echo — test endpoint that only works with a valid CSRF token. */
    public function echo(): never
    {
        $this->json(['received' => $this->request->input('message', '')], 'CSRF token accepted.');
    }
}
