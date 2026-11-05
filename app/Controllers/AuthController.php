<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Validator;
use App\Models\LoginAttempt;
use App\Models\Otp;
use App\Models\User;

/**
 * Login, Sign Up with demo OTP, Forgot Password and Logout.
 *
 * Login is a normal form POST (works without JavaScript).
 * Sign Up, OTP verification and Forgot Password use JSON requests (fetch).
 */
final class AuthController extends Controller
{
    /** Roles that may create their own account on the Sign Up screen. */
    private const SELF_REGISTER_ROLES = ['donor', 'victim', 'volunteer'];

    private const ALL_ROLES = ['manager', 'admin', 'donor', 'volunteer', 'victim'];

    /** Session keys that remember which account is waiting for an OTP. */
    private const PENDING_REGISTRATION = '_pending_registration_user';
    private const PENDING_RESET = '_pending_reset_user';

    // ------------------------------------------------------------------
    // Login
    // ------------------------------------------------------------------

    /** GET / — the Home Page (login) screen. */
    public function showLogin(): void
    {
        $this->view('auth/login', [
            'title'  => 'Login',
            'theme'  => 'bright',
            'styles' => ['pages/auth.css'],
        ]);
    }

    /** POST /login */
    public function login(): never
    {
        $input = $this->request->all();
        $errors = Validator::validate($input, [
            'login_type' => 'required|in:' . implode(',', self::ALL_ROLES),
            'email'      => 'required|email|max:150',
            'password'   => 'required|max:72',
        ], ['login_type' => 'Login type', 'email' => 'Username / Email', 'password' => 'Password']);

        if ($errors !== []) {
            $this->backWithErrors('', $errors);
        }

        $email = User::normalizeEmail($input['email']);
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if (LoginAttempt::isBlocked($email, $ip)) {
            $minutes = (int) Config::get('login.window_minutes', 15);
            $this->backWithErrors('', [], "Too many failed login attempts. Please wait {$minutes} minutes and try again.");
        }

        $user = User::verifyCredentials($email, (string) $input['password']);
        if ($user === null) {
            LoginAttempt::record($email, $ip);
            $this->backWithErrors('', [], 'Incorrect email or password.');
        }

        if ($user['role'] !== $input['login_type']) {
            $this->backWithErrors('', ['login_type' => 'This account is a ' . $user['role_name'] . ' account.'],
                'Please choose the correct login type for this account.');
        }

        if ($user['status'] === 'inactive') {
            $this->backWithErrors('', [], 'This account has been deactivated. Please contact the platform manager.');
        }

        LoginAttempt::clear($email);

        // Registered but never verified: send a new OTP and continue on the Sign Up screen.
        if ($user['status'] === 'pending') {
            Session::set(self::PENDING_REGISTRATION, (int) $user['id']);
            $this->flashDemoOtp($this->issueOtpIfAllowed((int) $user['id'], 'registration'));
            Session::flash('success', 'Please verify your mobile number to finish creating your account.');
            $this->redirect('register');
        }

        Auth::login($user);
        $this->redirect(Auth::homePath());
    }

    /** POST /logout */
    public function logout(): never
    {
        Auth::logout();
        Session::start();
        Session::flash('success', 'You have been logged out.');
        $this->redirect('');
    }

    // ------------------------------------------------------------------
    // Sign Up + demo OTP
    // ------------------------------------------------------------------

    /** GET /register — Sign Up screen. Shows the OTP section if verification is pending. */
    public function showRegister(): void
    {
        $pendingUser = $this->pendingUser(self::PENDING_REGISTRATION);

        $this->view('auth/register', [
            'title'       => 'Sign Up',
            'styles'      => ['pages/auth.css'],
            'scripts'     => ['pages/register.js'],
            'pendingUser' => $pendingUser,
        ]);
    }

    /** POST /api/auth/register (JSON) */
    public function register(): never
    {
        $input = $this->request->all();
        $errors = Validator::validate($input, [
            'role'      => 'required|in:' . implode(',', self::SELF_REGISTER_ROLES),
            'full_name' => 'required|min:2|max:100',
            'email'     => 'required|email|max:150',
            'phone'     => 'required|phone',
            'password'  => 'required|min:8|max:72|password',
        ], ['role' => 'role', 'full_name' => 'Full Name', 'email' => 'Email Address', 'phone' => 'Phone Number']);

        if (!isset($errors['email']) && User::emailExists((string) $input['email'])) {
            $errors['email'] = 'An account with this email already exists.';
        }
        if (!isset($errors['phone']) && User::phoneExists((string) $input['phone'])) {
            $errors['phone'] = 'An account with this phone number already exists.';
        }
        if ($errors !== []) {
            $this->jsonError('Please fix the highlighted fields.', $errors);
        }

        [$userId, $code] = Database::transaction(static function () use ($input): array {
            $userId = User::create([
                'role'      => $input['role'],
                'full_name' => $input['full_name'],
                'email'     => $input['email'],
                'phone'     => $input['phone'],
                'password'  => $input['password'],
                'status'    => 'pending',
            ]);
            return [$userId, Otp::issue($userId, 'registration')];
        });

        Session::set(self::PENDING_REGISTRATION, $userId);

        $this->json($this->otpResponseData($code, User::normalizePhone((string) $input['phone'])),
            'Account created. Enter the OTP to verify your mobile number.', 201);
    }

    /** POST /api/auth/verify-otp (JSON) — finish Sign Up and log in. */
    public function verifyRegistration(): never
    {
        $pendingUser = $this->pendingUser(self::PENDING_REGISTRATION);
        if ($pendingUser === null) {
            $this->jsonError('Please sign up first.', [], 400);
        }
        $userId = (int) $pendingUser['id'];

        $code = (string) $this->request->input('otp', '');
        $errors = Validator::validate(['otp' => $code], ['otp' => 'required|digits:' . (int) Config::get('otp.length', 4)], ['otp' => 'OTP']);
        if ($errors !== []) {
            $this->jsonError('Please enter the OTP.', $errors);
        }

        $result = Otp::verify($userId, 'registration', $code);
        if ($result !== Otp::VALID) {
            $this->jsonError(Otp::message($result), ['otp' => Otp::message($result)]);
        }

        User::setStatus($userId, 'active');
        Session::forget(self::PENDING_REGISTRATION);

        $user = User::find($userId);
        Auth::login($user);
        Session::flash('success', 'Welcome to Sahayata! Your account is verified.');

        $this->json(['redirect' => url(Auth::homePath())], 'Account verified.');
    }

    /** POST /api/auth/resend-otp (JSON) — new registration code. */
    public function resendRegistrationOtp(): never
    {
        $user = $this->pendingUser(self::PENDING_REGISTRATION);
        if ($user === null) {
            $this->jsonError('Please sign up first.', [], 400);
        }

        $code = $this->issueOtpIfAllowed((int) $user['id'], 'registration');
        if ($code === null) {
            $this->jsonError($this->resendWaitMessage((int) $user['id'], 'registration'), [], 429);
        }
        $this->json($this->otpResponseData($code, $user['phone']), 'A new OTP has been created.');
    }

    // ------------------------------------------------------------------
    // Forgot password
    // ------------------------------------------------------------------

    /** GET /forgot-password */
    public function showForgotPassword(): void
    {
        $this->view('auth/forgot-password', [
            'title'   => 'Forgot Password',
            'styles'  => ['pages/auth.css'],
            'scripts' => ['pages/forgot-password.js'],
        ]);
    }

    /** POST /api/auth/forgot-password (JSON) — step 1: request an OTP. */
    public function sendResetOtp(): never
    {
        $email = (string) $this->request->input('email', '');
        $errors = Validator::validate(['email' => $email], ['email' => 'required|email|max:150'], ['email' => 'Email Address']);
        if ($errors !== []) {
            $this->jsonError('Please fix the highlighted fields.', $errors);
        }

        // Same message whether or not the email exists, so accounts cannot be discovered.
        $message = 'If an account exists for this email, an OTP has been sent to its mobile number.';
        $user = User::findByEmail($email);

        if ($user === null || $user['status'] === 'inactive') {
            Session::forget(self::PENDING_RESET);
            $this->json(['otp_length' => (int) Config::get('otp.length', 4)], $message);
        }

        // Too soon for a new code: keep the pending reset so the previous code still works.
        $code = $this->issueOtpIfAllowed((int) $user['id'], 'password_reset');
        if ($code === null) {
            $this->jsonError($this->resendWaitMessage((int) $user['id'], 'password_reset'), [], 429);
        }
        Session::set(self::PENDING_RESET, (int) $user['id']);

        $this->json($this->otpResponseData($code, $user['phone']), $message);
    }

    /** POST /api/auth/reset-password (JSON) — step 2: OTP + new password. */
    public function resetPassword(): never
    {
        $input = $this->request->all();
        $errors = Validator::validate($input, [
            'otp'      => 'required|digits:' . (int) Config::get('otp.length', 4),
            'password' => 'required|min:8|max:72|password',
        ], ['otp' => 'OTP', 'password' => 'New Password']);
        if ($errors !== []) {
            $this->jsonError('Please fix the highlighted fields.', $errors);
        }

        $userId = (int) Session::get(self::PENDING_RESET, 0);
        // No pending reset (e.g. unknown email): answer like a wrong code.
        $result = $userId === 0 ? Otp::INVALID : Otp::verify($userId, 'password_reset', (string) $input['otp']);
        if ($result !== Otp::VALID) {
            $this->jsonError(Otp::message($result), ['otp' => Otp::message($result)]);
        }

        User::updatePassword($userId, (string) $input['password']);
        Session::forget(self::PENDING_RESET);
        $user = User::find($userId);
        LoginAttempt::clear($user['email']);

        Session::flash('success', 'Your password has been changed. Please log in with your new password.');
        $this->json(['redirect' => url('')], 'Password changed.');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /** The account stored under a "pending" session key, if it still needs it. */
    private function pendingUser(string $sessionKey): ?array
    {
        $userId = (int) Session::get($sessionKey, 0);
        $user = $userId > 0 ? User::find($userId) : null;

        if ($sessionKey === self::PENDING_REGISTRATION && ($user === null || $user['status'] !== 'pending')) {
            Session::forget($sessionKey);
            return null;
        }
        return $user;
    }

    /** Issue a code unless one was issued too recently. Returns the code or null. */
    private function issueOtpIfAllowed(int $userId, string $purpose): ?string
    {
        return Otp::secondsUntilResend($userId, $purpose) > 0 ? null : Otp::issue($userId, $purpose);
    }

    private function resendWaitMessage(int $userId, string $purpose): string
    {
        $seconds = Otp::secondsUntilResend($userId, $purpose);
        return "Please wait {$seconds} seconds before requesting a new OTP.";
    }

    /** Data sent to the browser after an OTP is created. */
    private function otpResponseData(string $code, string $phone): array
    {
        $data = [
            'phone_masked' => self::maskPhone($phone),
            'otp_length'   => (int) Config::get('otp.length', 4),
        ];
        // DEMO MODE ONLY: real SMS delivery is not configured, so the code is shown on screen.
        if (Config::get('otp.demo_mode', false)) {
            $data['demo_otp'] = $code;
        }
        return $data;
    }

    /** Show the demo code as a flash message on the next page (login -> Sign Up redirect). */
    private function flashDemoOtp(?string $code): void
    {
        if ($code !== null && Config::get('otp.demo_mode', false)) {
            Session::flash('success', "Demo OTP: {$code} (SMS is not configured in this demo).");
        }
    }

    /** "01712345678" -> "017******78" */
    public static function maskPhone(string $phone): string
    {
        return strlen($phone) < 6 ? $phone : substr($phone, 0, 3) . str_repeat('*', strlen($phone) - 5) . substr($phone, -2);
    }
}
