<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Navigation;
use App\Models\Delivery;
use App\Models\Donation;
use App\Models\HelpRequest;
use App\Models\User;
use App\Models\Volunteer;

/** Admin screens: Request Approvals (dashboard), Assign Delivery, Users, Volunteers, Donations. */
final class AdminController extends Controller
{
    /** GET /admin/dashboard */
    public function dashboard(): void
    {
        $this->view('admin/dashboard', [
            'title'      => 'Request Approvals',
            'styles'     => ['pages/screens.css'],
            'tabs'       => Navigation::tabs('admin', 'dashboard'),
            'pending'    => HelpRequest::listByStatus(['pending']),
            'approved'   => HelpRequest::listByStatus(['approved', 'assigned']),
            'deliveries' => Delivery::listAll(),
        ]);
    }

    /** POST /api/admin/requests/{id}/approve */
    public function approve(int $id): never
    {
        $this->attempt(static fn () => HelpRequest::review($id, Auth::id(), 'approved'));
        $this->jsonDone('Request ' . ref('REQ', $id) . ' approved. Donors can now see it.');
    }

    /** POST /api/admin/requests/{id}/reject */
    public function reject(int $id): never
    {
        $this->attempt(static fn () => HelpRequest::review($id, Auth::id(), 'rejected'));
        $this->jsonDone('Request ' . ref('REQ', $id) . ' rejected.');
    }

    /** GET /admin/requests/{id}/assign — "Assign Delivery" screen. */
    public function assignForm(int $id): void
    {
        $request = HelpRequest::find($id) ?? throw new HttpException(404, 'Request not found.');

        $this->view('admin/assign', [
            'title'      => 'Assign Delivery',
            'styles'     => ['pages/screens.css'],
            'request'    => $request,
            'delivery'   => Delivery::findByRequest($id),
            'donation'   => Donation::latestForRequest($id),
            'volunteers' => Volunteer::listForAssignment(),
            'canAssign'  => in_array($request['status'], ['approved', 'assigned'], true),
        ]);
    }

    /** POST /api/admin/requests/{id}/assign  { volunteer_id } */
    public function assign(int $id): never
    {
        $data = $this->validateOrFail(['volunteer_id' => 'required|integer'], ['volunteer_id' => 'volunteer']);
        $volunteerId = (int) $data['volunteer_id'];

        $this->attempt(static fn () => Delivery::assign($id, $volunteerId, Auth::id()));
        $volunteer = User::find($volunteerId);
        $this->jsonDone('Delivery for ' . ref('REQ', $id) . ' assigned to ' . $volunteer['full_name'] . '.', 'admin/dashboard');
    }

    /** GET /admin/users */
    public function users(): void
    {
        $this->view('admin/users', [
            'title'  => 'Users',
            'styles' => ['pages/screens.css'],
            'tabs'   => Navigation::tabs('admin', 'users'),
            'users'  => User::listByRoles(['donor', 'victim', 'volunteer']),
        ]);
    }

    /** GET /admin/volunteers */
    public function volunteers(): void
    {
        $this->view('admin/volunteers', [
            'title'      => 'Volunteers',
            'styles'     => ['pages/screens.css'],
            'tabs'       => Navigation::tabs('admin', 'volunteers'),
            'volunteers' => Volunteer::listAll(),
        ]);
    }

    /** GET /admin/donations */
    public function donations(): void
    {
        $this->view('admin/donations', [
            'title'     => 'Donations',
            'styles'    => ['pages/screens.css'],
            'tabs'      => Navigation::tabs('admin', 'donations'),
            'donations' => Donation::listAll(),
            'totals'    => Donation::totals(),
        ]);
    }
}
