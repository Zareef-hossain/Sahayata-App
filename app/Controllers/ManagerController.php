<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Navigation;
use App\Models\Delivery;
use App\Models\Donation;
use App\Models\User;

/** Manager screens: dashboard with live statistics and administrator management. */
final class ManagerController extends Controller
{
    /** GET /manager/dashboard */
    public function dashboard(): void
    {
        $roleCounts = User::countByRole();

        $this->view('manager/dashboard', [
            'title'  => 'Manager Dashboard',
            'styles' => ['pages/screens.css'],
            'tabs'   => Navigation::tabs('manager', 'dashboard'),
            'stats'  => [
                'users'     => array_sum($roleCounts),
                'admins'    => $roleCounts['admin'] ?? 0,
                'donations' => Donation::totals()['count'],
                'delivered' => Delivery::countDelivered(),
            ],
            'admins' => User::listByRoles(['admin']),
        ]);
    }

    /** GET /manager/admins/create */
    public function createAdminForm(): void
    {
        $this->view('manager/create-admin', [
            'title'  => 'Create New Admin',
            'styles' => ['pages/screens.css'],
        ]);
    }

    /** POST /api/manager/admins (JSON) */
    public function storeAdmin(): never
    {
        $data = $this->validateOrFail([
            'full_name' => 'required|min:2|max:100',
            'email'     => 'required|email|max:150',
            'phone'     => 'required|phone',
            'password'  => 'required|min:8|max:72|password',
        ], ['full_name' => 'Full Name', 'email' => 'Email Address', 'phone' => 'Phone Number']);

        $errors = [];
        if (User::emailExists($data['email'])) {
            $errors['email'] = 'An account with this email already exists.';
        }
        if (User::phoneExists($data['phone'])) {
            $errors['phone'] = 'An account with this phone number already exists.';
        }
        if ($errors !== []) {
            $this->jsonError('Please fix the highlighted fields.', $errors);
        }

        // Role and status are fixed here on the server — never taken from the request.
        User::create([...$data, 'role' => 'admin', 'status' => 'active', 'created_by' => Auth::id()]);
        $this->jsonDone('Administrator ' . trim($data['full_name']) . ' created.', 'manager/dashboard');
    }

    /** POST /api/manager/admins/{id}/status  { status: active|inactive } */
    public function setAdminStatus(int $id): never
    {
        $data = $this->validateOrFail(['status' => 'required|in:active,inactive'], ['status' => 'status']);
        $this->attempt(static fn () => User::setAdminStatus($id, $data['status']));

        $admin = User::find($id);
        $this->jsonDone($admin['full_name'] . ' is now ' . ($data['status'] === 'active' ? 'active' : 'deactivated') . '.');
    }
}
