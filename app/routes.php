<?php
/**
 * All URLs of the application.
 * Format: $router->get|post(path, [Controller::class, 'method'], [middleware...])
 *
 * Middleware: 'guest' = logged-out users only, 'auth' = any logged-in user,
 *             'role:admin' = logged in with that role (checked on the server).
 * Every POST request also needs a valid CSRF token (checked by the Router).
 * {id} in a path only matches digits and is passed to the controller as an int.
 */

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\DevController;
use App\Controllers\DonorController;
use App\Controllers\ManagerController;
use App\Controllers\ProfileController;
use App\Controllers\SystemController;
use App\Controllers\VictimController;
use App\Controllers\VolunteerController;
use App\Core\Router;

$router = new Router();

// ---- Authentication ------------------------------------------------------
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

// ---- Profile (all roles) -------------------------------------------------
$router->get('/profile', [ProfileController::class, 'show'], ['auth']);
$router->post('/api/profile', [ProfileController::class, 'update'], ['auth']);
$router->post('/api/profile/password', [ProfileController::class, 'changePassword'], ['auth']);

// ---- Victim --------------------------------------------------------------
$router->get('/victim/request-help', [VictimController::class, 'requestHelp'], ['role:victim']);
$router->post('/api/victim/requests', [VictimController::class, 'submitRequest'], ['role:victim']);
$router->get('/victim/track', [VictimController::class, 'track'], ['role:victim']);

// ---- Donor ---------------------------------------------------------------
$router->get('/donor/dashboard', [DonorController::class, 'dashboard'], ['role:donor']);
$router->get('/donor/donate', [DonorController::class, 'donateForm'], ['role:donor']);
$router->post('/api/donor/donations', [DonorController::class, 'storeDonation'], ['role:donor']);
$router->get('/donor/donations', [DonorController::class, 'donations'], ['role:donor']);

// ---- Admin ---------------------------------------------------------------
$router->get('/admin/dashboard', [AdminController::class, 'dashboard'], ['role:admin']);
$router->post('/api/admin/requests/{id}/approve', [AdminController::class, 'approve'], ['role:admin']);
$router->post('/api/admin/requests/{id}/reject', [AdminController::class, 'reject'], ['role:admin']);
$router->get('/admin/requests/{id}/assign', [AdminController::class, 'assignForm'], ['role:admin']);
$router->post('/api/admin/requests/{id}/assign', [AdminController::class, 'assign'], ['role:admin']);
$router->get('/admin/users', [AdminController::class, 'users'], ['role:admin']);
$router->get('/admin/volunteers', [AdminController::class, 'volunteers'], ['role:admin']);
$router->get('/admin/donations', [AdminController::class, 'donations'], ['role:admin']);

// ---- Volunteer -----------------------------------------------------------
$router->get('/volunteer/tasks', [VolunteerController::class, 'tasks'], ['role:volunteer']);
$router->post('/api/volunteer/availability', [VolunteerController::class, 'toggleAvailability'], ['role:volunteer']);
$router->post('/api/volunteer/deliveries/{id}/accept', [VolunteerController::class, 'accept'], ['role:volunteer']);
$router->get('/volunteer/deliveries/{id}', [VolunteerController::class, 'delivery'], ['role:volunteer']);
$router->post('/api/volunteer/deliveries/{id}/status', [VolunteerController::class, 'updateStatus'], ['role:volunteer']);
$router->get('/volunteer/history', [VolunteerController::class, 'history'], ['role:volunteer']);

// ---- Manager -------------------------------------------------------------
$router->get('/manager/dashboard', [ManagerController::class, 'dashboard'], ['role:manager']);
$router->get('/manager/admins/create', [ManagerController::class, 'createAdminForm'], ['role:manager']);
$router->post('/api/manager/admins', [ManagerController::class, 'storeAdmin'], ['role:manager']);
$router->post('/api/manager/admins/{id}/status', [ManagerController::class, 'setAdminStatus'], ['role:manager']);

// ---- System / development ------------------------------------------------
$router->get('/api/health', [SystemController::class, 'health']);
$router->post('/api/echo', [SystemController::class, 'echo']);
$router->get('/dev/components', [DevController::class, 'components']); // debug mode only

return $router;
