<?php

declare(strict_types=1);

namespace App\Core;

/**
 * The light-blue tab rows of each role, defined once.
 * Widths (px) are taken from the Figma screens so the tabs keep their design size.
 */
final class Navigation
{
    private const TABS = [
        'admin' => [
            'dashboard'  => ['label' => 'Dashboard',  'href' => 'admin/dashboard',  'width' => 87],
            'users'      => ['label' => 'Users',      'href' => 'admin/users',      'width' => 67],
            'volunteers' => ['label' => 'Volunteers', 'href' => 'admin/volunteers', 'width' => 78],
            'donations'  => ['label' => 'Donations',  'href' => 'admin/donations',  'width' => 78],
        ],
        'donor' => [
            'home'      => ['label' => 'Home',         'href' => 'donor/dashboard', 'width' => 57],
            'donations' => ['label' => 'My Donations', 'href' => 'donor/donations', 'width' => 113],
            'profile'   => ['label' => 'Profile',      'href' => 'profile',         'width' => 69],
        ],
        'volunteer' => [
            'tasks'   => ['label' => 'Tasks',   'href' => 'volunteer/tasks',   'width' => 57],
            'history' => ['label' => 'History', 'href' => 'volunteer/history', 'width' => 113],
            'profile' => ['label' => 'Profile', 'href' => 'profile',           'width' => 69],
        ],
        'manager' => [
            'dashboard' => ['label' => 'Dashboard',  'href' => 'manager/dashboard', 'width' => 87],
            'profile'   => ['label' => 'My Profile', 'href' => 'profile',           'width' => 78],
        ],
    ];

    /** Tabs for a role with the given tab marked active. Empty for roles without tabs (victim). */
    public static function tabs(string $role, string $active): array
    {
        $tabs = [];
        foreach (self::TABS[$role] ?? [] as $key => $tab) {
            $tabs[] = [...$tab, 'active' => $key === $active];
        }
        return $tabs;
    }
}
