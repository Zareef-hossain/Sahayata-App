<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Controller;
use App\Core\HttpException;
use App\Models\Lookup;

/**
 * Component preview page for checking the design foundation.
 * Only available when app.debug is true in config/config.php.
 */
final class DevController extends Controller
{
    public function components(): void
    {
        if (!Config::get('app.debug', false)) {
            throw new HttpException(404);
        }

        $this->view('dev/components', [
            'title'         => 'Component Preview',
            'disasterTypes' => Lookup::all('disaster_types'),
            'styles'        => ['pages/dev.css'],
            'scripts'       => ['pages/dev-components.js'],
        ]);
    }
}
