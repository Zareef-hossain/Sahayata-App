<?php
/**
 * Home Page (login) screen — Figma "Home Page".
 * Variables: $old (previous input), $errors, $flash
 */
$old ??= [];
$errors ??= [];

$loginTypes = [
    ['value' => 'admin',     'label' => 'Admin',     'width' => 91, 'height' => 33],
    ['value' => 'manager',   'label' => 'Manager',   'width' => 84, 'height' => 33],
    ['value' => 'donor',     'label' => 'Donator',   'width' => 91],
    ['value' => 'volunteer', 'label' => 'Volunteer', 'width' => 84],
    ['value' => 'victim',    'label' => 'Victim',    'width' => 91],
];
?>
<?php component('page-header', ['logo' => 'primary']); ?>

<form class="login-form content-column" method="post" action="<?= e(url('login')) ?>" data-validate novalidate>
    <?= csrf_field() ?>

    <h1 class="login-form__welcome">Welcome back! Please login.</h1>

    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <?php component('option-group', [
        'name'     => 'login_type',
        'legend'   => 'Choose Login Type :',
        'modifier' => 'login',
        'options'  => $loginTypes,
        'selected' => $old['login_type'] ?? null,
        'required' => true,
        'error'    => $errors['login_type'] ?? null,
    ]); ?>

    <div class="login-form__fields">
        <?php component('form-field', [
            'name' => 'email', 'label' => 'Username / Email :', 'type' => 'email',
            'value' => $old['email'] ?? '', 'required' => true, 'error' => $errors['email'] ?? null,
            'attributes' => ['autocomplete' => 'username', 'maxlength' => 150],
        ]); ?>
        <?php component('form-field', [
            'name' => 'password', 'label' => 'Password:', 'type' => 'password',
            'required' => true, 'error' => $errors['password'] ?? null,
            'attributes' => ['autocomplete' => 'current-password', 'maxlength' => 72],
        ]); ?>
        <a class="text-link login-form__forgot" href="<?= e(url('forgot-password')) ?>">Forgot Password? (OTP)</a>
    </div>

    <button class="btn btn--primary btn--text-dark login-form__submit" type="submit">Login</button>

    <p class="login-form__signup-prompt">Don't have an account?</p>
    <a class="btn btn--primary btn--text-dark" href="<?= e(url('register')) ?>">Register Here</a>
</form>
