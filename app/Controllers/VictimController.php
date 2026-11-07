<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Delivery;
use App\Models\HelpRequest;
use App\Models\Lookup;
use App\Models\ReliefPoint;

/** Victim screens: "Request for Help" and "Track My Request". */
final class VictimController extends Controller
{
    /** GET /victim/request-help */
    public function requestHelp(): void
    {
        $this->view('victim/request-help', [
            'title'         => 'Request for Help',
            'styles'        => ['pages/screens.css'],
            'scripts'       => ['pages/request-help.js'],
            'disasterTypes' => Lookup::all('disaster_types'),
        ]);
    }

    /** POST /api/victim/requests (JSON) */
    public function submitRequest(): never
    {
        $data = $this->validateOrFail([
            'urgency'          => 'required|in:low,medium,high',
            'disaster_type_id' => 'required|integer',
            'location'         => 'required|min:3|max:255',
            'contact_phone'    => 'required|phone',
            'description'      => 'required|min:5|max:1000',
            'latitude'         => 'numeric|between:-90,90',
            'longitude'        => 'numeric|between:-180,180',
        ], [
            'urgency' => 'urgency level', 'disaster_type_id' => 'Disaster Type',
            'contact_phone' => 'Phone Number',
        ]);

        if (!Lookup::exists('disaster_types', (int) $data['disaster_type_id'])) {
            $this->jsonError('Please fix the highlighted fields.', ['disaster_type_id' => 'Please choose a valid Disaster Type.']);
        }
        // Coordinates are only saved as a pair.
        if (empty($data['latitude']) || empty($data['longitude'])) {
            $data['latitude'] = $data['longitude'] = null;
        }

        $requestId = HelpRequest::create(Auth::id(), $data);
        $this->jsonDone(
            'Your request ' . ref('REQ', $requestId) . ' has been submitted. An admin will review it soon.',
            'victim/track?request=' . $requestId
        );
    }

    /** GET /victim/track — status, map, nearest relief point and volunteer of one request. */
    public function track(): void
    {
        $victimId = Auth::id();
        $requests = HelpRequest::listForVictim($victimId);

        $selectedId = (int) $this->request->input('request', 0);
        // Another victim's ID (or a wrong ID) is ignored: show this victim's latest request instead.
        $request = ($selectedId > 0 ? HelpRequest::findForVictim($selectedId, $victimId) : null) ?? ($requests[0] ?? null);

        $hasLocation = $request !== null && $request['latitude'] !== null && $request['longitude'] !== null;

        $this->view('victim/track', [
            'title'        => 'Track My Request',
            'styles'       => ['pages/screens.css'],
            'requests'     => $requests,
            'request'      => $request,
            'history'      => $request ? HelpRequest::history((int) $request['id']) : [],
            'delivery'     => $request ? Delivery::findByRequest((int) $request['id']) : null,
            'reliefPoint'  => $hasLocation ? ReliefPoint::nearest((float) $request['latitude'], (float) $request['longitude']) : null,
        ]);
    }
}
