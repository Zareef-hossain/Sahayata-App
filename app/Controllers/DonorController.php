<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Navigation;
use App\Models\Donation;
use App\Models\HelpRequest;
use App\Models\Lookup;

/** Donor screens: dashboard, "Make Donation" (demo payment) and donation history. */
final class DonorController extends Controller
{
    /** GET /donor/dashboard — approved requests with "Donate Now". */
    public function dashboard(): void
    {
        $this->view('donor/dashboard', [
            'title'    => 'Donor Dashboard',
            'styles'   => ['pages/screens.css'],
            'tabs'     => Navigation::tabs('donor', 'home'),
            'requests' => HelpRequest::listByStatus(HelpRequest::OPEN_FOR_DONATION),
        ]);
    }

    /** GET /donor/donate?request=ID */
    public function donateForm(): void
    {
        $requestId = (int) $this->request->input('request', 0);
        $helpRequest = null;
        if ($requestId > 0) {
            $helpRequest = HelpRequest::find($requestId);
            if ($helpRequest === null || !in_array($helpRequest['status'], HelpRequest::OPEN_FOR_DONATION, true)) {
                throw new HttpException(404, 'This request is no longer accepting donations.');
            }
        }

        $this->view('donor/donate', [
            'title'          => 'Make Donation',
            'styles'         => ['pages/screens.css'],
            'helpRequest'    => $helpRequest,
            'donationTypes'  => Lookup::all('donation_types'),
            'paymentMethods' => Lookup::all('payment_methods'),
        ]);
    }

    /** POST /api/donor/donations (JSON) — DEMO payment: recorded only, no real money moves. */
    public function storeDonation(): never
    {
        $data = $this->validateOrFail([
            'donation_type_id'  => 'required|integer',
            'amount_bdt'        => 'required|numeric|gt:0|max_value:' . Donation::MAX_AMOUNT,
            'payment_method_id' => 'required|integer',
            'payer_mobile'      => 'required|phone',
            'help_request_id'   => 'integer',
        ], [
            'donation_type_id' => 'Donation Type', 'amount_bdt' => 'Amount',
            'payment_method_id' => 'Payment Method', 'payer_mobile' => 'Mobile Number',
        ]);

        $errors = [];
        if (!Lookup::exists('donation_types', (int) $data['donation_type_id'])) {
            $errors['donation_type_id'] = 'Please choose a valid Donation Type.';
        }
        if (!Lookup::exists('payment_methods', (int) $data['payment_method_id'])) {
            $errors['payment_method_id'] = 'Please choose a valid Payment Method.';
        }
        if ($errors !== []) {
            $this->jsonError('Please fix the highlighted fields.', $errors);
        }

        $donation = $this->attempt(static fn () => Donation::create(Auth::id(), $data));

        $this->jsonDone(
            'Demo payment recorded. Reference: ' . $donation['reference'] . '. No real money was charged.',
            'donor/donations'
        );
    }

    /** GET /donor/donations */
    public function donations(): void
    {
        $donorId = Auth::id();
        $this->view('donor/donations', [
            'title'     => 'My Donations',
            'styles'    => ['pages/screens.css'],
            'tabs'      => Navigation::tabs('donor', 'donations'),
            'donations' => Donation::listForDonor($donorId),
            'totals'    => Donation::totals($donorId),
        ]);
    }
}
