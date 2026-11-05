<?php
/**
 * All URLs of the application.
 * Format: $router->get|post(path, [Controller::class, 'method'], [middleware...])
 *
 * Middleware: 'guest' = logged-out users only, 'auth' = any logged-in user,
 *             'role:admin' = logged in with that role. POST always needs a CSRF token.
 */

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DevController;
use App\Controllers\HomeController;
use App\Controllers\SystemController;
use App\Core\Auth;
use App\Core\Router;

$router = new Router();

// ---- Authentication (Phase 2) -------------------------------------------
$router->get('/', [AuthController::class, 'showLogin'], ['guest']);
$router->post('/login', [AuthController::class, 'login'], ['guest']);
$router->post('/logout', [AuthController::class, 'logout'], ['auth']);

$router->get('/register', [AuthController::class, 'showRegister'], ['guest']);
$router->post('/api/auth/register', [AuthController::class, 'register'], ['guest']);
$router->post('/api/auth/verify-otp', [AuthController::class, 'verifyRegistration'], ['guest']);
$router->post('/api/auth/resend-otp', [AuthController::class, 'resendRegistrationOtp'], ['guest']);

$router->get('/forgot-password', [AuthController::class, 'showForgotPassword'], ['guest']);
$router->post('/api/auth/forgot-password', [AuthController::class, 'sendResetOtp'], ['guest']);
$router->post('/api/auth/reset-password', [AuthController::class, 'resetPassword'], ['guest']);

// ---- Role home pages (TEMPORARY until Phases 3-7) -----------------------
foreach (Auth::ROLE_HOME as $role => $path) {
    $router->get('/' . $path, [HomeController::class, 'placeholder'], ['role:' . $role]);
}

// ---- System / development ------------------------------------------------
$router->get('/api/health', [SystemController::class, 'health']);
$router->post('/api/echo', [SystemController::class, 'echo']);
$router->get('/dev/components', [DevController::class, 'components']);

return $router;
