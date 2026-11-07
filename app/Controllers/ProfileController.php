<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Navigation;
use App\Models\User;

/** "Profile" / "My Profile" tab, shared by all roles. */
final class ProfileController extends Controller
{
    /** GET /profile */
    public function show(): void
    {
        $role = Auth::role();
        $this->view('profile/show', [
            'title'    => 'My Profile',
            'styles'   => ['pages/screens.css'],
            'user'     => User::find(Auth::id()),
            'tabs'     => Navigation::tabs($role, 'profile'),
            'homePath' => Auth::homePath(),
        ]);
    }

    /** POST /api/profile (JSON) — name and phone. Email and role cannot be changed here. */
    public function update(): never
    {
        $data = $this->validateOrFail([
            'full_name' => 'required|min:2|max:100',
            'phone'     => 'required|phone',
        ], ['full_name' => 'Full Name', 'phone' => 'Phone Number']);

        if (User::phoneTakenByOther($data['phone'], Auth::id())) {
            $this->jsonError('Please fix the highlighted fields.', ['phone' => 'This phone number is used by another account.']);
        }

        User::updateProfile(Auth::id(), $data['full_name'], $data['phone']);
        $this->jsonDone('Profile updated.');
    }

    /** POST /api/profile/password (JSON) */
    public function changePassword(): never
    {
        $data = $this->validateOrFail([
            'current_password' => 'required|max:72',
            'password'         => 'required|min:8|max:72|password',
        ], ['current_password' => 'Current Password', 'password' => 'New Password']);

        if (!User::passwordMatches(Auth::id(), $data['current_password'])) {
            $this->jsonError('Please fix the highlighted fields.', ['current_password' => 'The current password is incorrect.']);
        }

        User::updatePassword(Auth::id(), $data['password']);
        $this->jsonDone('Password changed.');
    }
}
