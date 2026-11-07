<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Navigation;
use App\Models\Delivery;
use App\Models\Volunteer;

/** Volunteer screens: Volunteer Tasks, Delivery Progress and History. */
final class VolunteerController extends Controller
{
    /** GET /volunteer/tasks */
    public function tasks(): void
    {
        $volunteerId = Auth::id();
        $this->view('volunteer/tasks', [
            'title'        => 'Volunteer Tasks',
            'styles'       => ['pages/screens.css'],
            'tabs'         => Navigation::tabs('volunteer', 'tasks'),
            'availability' => Volunteer::availability($volunteerId),
            'pending'      => Delivery::listPending(),
            'active'       => Delivery::listActiveForVolunteer($volunteerId),
        ]);
    }

    /** POST /api/volunteer/availability — switch between Available and Busy. */
    public function toggleAvailability(): never
    {
        $volunteerId = Auth::id();
        $next = Volunteer::availability($volunteerId) === 'available' ? 'busy' : 'available';
        Volunteer::setAvailability($volunteerId, $next);
        $this->jsonDone('Your status is now ' . ($next === 'available' ? 'Available' : 'Busy') . '.');
    }

    /** POST /api/volunteer/deliveries/{id}/accept */
    public function accept(int $id): never
    {
        $this->attempt(static fn () => Delivery::accept($id, Auth::id()));
        $this->jsonDone('Task ' . ref('DEL', $id) . ' accepted. Update its status as you go.', 'volunteer/deliveries/' . $id);
    }

    /** GET /volunteer/deliveries/{id} — "Delivery Progress". Only the assigned volunteer may open it. */
    public function delivery(int $id): void
    {
        $delivery = Delivery::find($id);
        if ($delivery === null || (int) $delivery['volunteer_id'] !== Auth::id()) {
            throw new HttpException(404, 'Delivery not found.');
        }

        $this->view('volunteer/delivery', [
            'title'        => 'Delivery Progress',
            'styles'       => ['pages/screens.css'],
            'delivery'     => $delivery,
            'nextStatuses' => Delivery::nextStatuses($delivery['status']),
        ]);
    }

    /** POST /api/volunteer/deliveries/{id}/status  { status } */
    public function updateStatus(int $id): never
    {
        $data = $this->validateOrFail(['status' => 'required|in:picked_up,in_transit,delivered'], ['status' => 'status']);
        $this->attempt(static fn () => Delivery::updateStatus($id, Auth::id(), $data['status']));

        $label = Delivery::STATUS_LABELS[$data['status']];
        $redirect = $data['status'] === 'delivered' ? 'volunteer/history' : 'volunteer/deliveries/' . $id;
        $this->jsonDone('Status updated to ' . $label . '.', $redirect);
    }

    /** GET /volunteer/history */
    public function history(): void
    {
        $this->view('volunteer/history', [
            'title'      => 'Delivery History',
            'styles'     => ['pages/screens.css'],
            'tabs'       => Navigation::tabs('volunteer', 'history'),
            'deliveries' => Delivery::listDeliveredForVolunteer(Auth::id()),
        ]);
    }
}
