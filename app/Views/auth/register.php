<?php
/**
 * Sign Up screen — Figma "Sign Up".
 * The white box and blue button at the bottom of the Figma screen are the
 * OTP input and "Verify OTP" button; they appear after Sign Up succeeds.
 *
 * Variables: $pendingUser (?array) — account waiting for OTP verification, $flash
 */
$otpLength = (int) App\Core\Config::get('otp.length', 4);
$isPending = $pendingUser !== null;
?>
<?php component('page-header', ['backHref' => '']); ?>

<form class="register-form content-column" data-register-form data-validate novalidate>
    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <h1 class="visually-hidden">Sign Up</h1>

    <?php component('option-group', [
        'name'     => 'role',
        'legend'   => 'Select Role :',
        'required' => true,
        'options'  => [
            ['value' => 'donor',     'label' => 'Donor'],
            ['value' => 'victim',    'label' => 'Victim'],
            ['value' => 'volunteer', 'label' => 'Volunteer', 'width' => 84],
        ],
    ]); ?>

    <div class="form__fields">
        <?php component('form-field', [
            'name' => 'full_name', 'label' => 'Full Name :', 'required' => true,
            'attributes' => ['autocomplete' => 'name', 'minlength' => 2, 'maxlength' => 100],
        ]); ?>
        <?php component('form-field', [
            'name' => 'email', 'label' => 'Email Address :', 'type' => 'email', 'required' => true,
            'attributes' => ['autocomplete' => 'email', 'maxlength' => 150],
        ]); ?>
        <?php component('form-field', [
            'name' => 'phone', 'label' => 'Phone Number :', 'type' => 'tel', 'required' => true,
            'attributes' => ['autocomplete' => 'tel', 'inputmode' => 'tel', 'data-rule' => 'phone', 'maxlength' => 14],
        ]); ?>
        <?php component('form-field', [
            'name' => 'password', 'label' => 'Password:', 'type' => 'password', 'required' => true,
            'attributes' => ['autocomplete' => 'new-password', 'minlength' => 8, 'maxlength' => 72, 'data-rule' => 'password'],
        ]); ?>
    </div>

    <button class="btn btn--primary btn--text-dark register-form__submit" type="submit">Sign Up</button>
</form>

<section class="otp-panel content-column" data-otp-panel aria-labelledby="otp-heading" <?= $isPending ? '' : 'hidden' ?>>
    <h2 class="visually-hidden" id="otp-heading">OTP Verification</h2>
    <form class="otp-panel__form" data-otp-form data-validate novalidate>
        <?php component('otp-input', ['length' => $otpLength]); ?>
    </form>
    <button class="text-link otp-panel__resend" type="button" data-resend-otp>Resend OTP</button>
    <p class="otp-panel__notice" data-otp-notice aria-live="polite"><?= $isPending ? 'OTP sent to ' . e(App\Controllers\AuthController::maskPhone($pendingUser['phone'])) . '.' : '' ?></p>
</section>
