<?php
/**
 * Application settings.
 *
 * Copy this file to config/config.php and change the values for your computer.
 * config/config.php is ignored by Git so your passwords are never committed.
 *
 * The defaults below match a fresh XAMPP install (user "root", no password).
 */

return [
    'app' => [
        'name'  => 'Sahayata',
        // true  = show detailed error messages (use while developing)
        // false = show a friendly error page (use when the site is public)
        'debug' => true,
        // Leave empty to detect automatically. Set it only if links break,
        // e.g. '/sahayata-app' when the project is in htdocs/sahayata-app.
        'base_url' => '',
        'timezone' => 'Asia/Dhaka',
    ],

    'db' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'sahayata',
        'user'     => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],

    'session' => [
        'name'         => 'sahayata_session',
        'lifetime'     => 0,  // 0 = cookie lasts until the browser is closed
        'idle_minutes' => 60, // log out after this many minutes without activity
    ],

    'login' => [
        // Block logins for an email after this many wrong passwords...
        'max_attempts'   => 5,
        // ...within this many minutes.
        'window_minutes' => 15,
    ],

    'otp' => [
        // DEMO MODE: no SMS is sent. Real SMS delivery is NOT configured.
        // While demo_mode is true the code is shown on screen instead.
        'demo_mode'        => true,
        'length'           => 4,
        'ttl_minutes'      => 10,
        'max_attempts'     => 5,  // wrong guesses allowed per code
        'resend_seconds'   => 60, // wait time before a new code can be requested
    ],
];
