<?php
/**
 * Forgot Password (OTP). There is no Figma frame for this screen; it is built
 * from the same components as Sign Up, using the Figma texts
 * "Forgot Password? (OTP)", "Enter 4-digit OTP sent to mobile:" and "Verify OTP".
 *
 * Step 1: enter email -> a demo OTP is created.
 * Step 2: enter OTP + new password.
 */
$otpLength = (int) App\Core\Config::get('otp.length', 4);
?>
<?php component('page-header', ['backHref' => '', 'title' => 'Forgot Password']); ?>

<form class="form content-column reset-request-form" data-reset-request-form data-validate novalidate>
    <?php component('flash', ['messages' => $flash ?? []]); ?>

    <?php component('form-field', [
        'name' => 'email', 'label' => 'Email  Address :', 'type' => 'email', 'required' => true,
        'attributes' => ['autocomplete' => 'email', 'maxlength' => 150],
    ]); ?>
    <button class="btn btn--primary btn--text-dark reset-request-form__submit" type="submit">Send OTP</button>
</form>

<section class="otp-panel content-column" data-otp-panel aria-labelledby="reset-otp-heading" hidden>
    <h2 class="visually-hidden" id="reset-otp-heading">OTP Verification</h2>
    <form class="otp-panel__form" data-reset-form data-validate novalidate>
        <?php component('form-field', [
            'name' => 'password', 'label' => 'New Password:', 'type' => 'password', 'required' => true,
            'attributes' => ['autocomplete' => 'new-password', 'minlength' => 8, 'maxlength' => 72, 'data-rule' => 'password'],
        ]); ?>
        <?php component('otp-input', ['length' => $otpLength]); ?>
    </form>
    <button class="text-link otp-panel__resend" type="button" data-resend-otp>Resend OTP</button>
    <p class="otp-panel__notice" data-otp-notice aria-live="polite"></p>
</section>
