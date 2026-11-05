<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

/**
 * TEMPORARY role home pages shown after login.
 * Each one is replaced by the real screen in its phase:
 *   victim -> Phase 3, admin -> Phase 4, donor -> Phase 5,
 *   volunteer -> Phase 6, manager -> Phase 7.
 */
final class HomeController extends Controller
{
    private const PHASES = [
        'victim'    => ['Victim', 'Phase 3'],
        'admin'     => ['Admin', 'Phase 4'],
        'donor'     => ['Donator', 'Phase 5'],
        'volunteer' => ['Volunteer', 'Phase 6'],
        'manager'   => ['Manager', 'Phase 7'],
    ];

    public function placeholder(): void
    {
        [$roleName, $phase] = self::PHASES[Auth::role()];

        $this->view('home/placeholder', [
            'title'    => $roleName,
            'user'     => Auth::user(),
            'roleName' => $roleName,
            'phase'    => $phase,
        ]);
    }
}
