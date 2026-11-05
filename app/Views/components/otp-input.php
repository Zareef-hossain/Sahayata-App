<?php
/**
 * OTP code input + "Verify OTP" button, as on the Sign Up screen.
 * Must be placed inside a <form>.
 *
 * Props:
 *   $length  int  Number of digits (config otp.length)
 */
$length = (int) ($length ?? 4);
?>
<div class="otp-input" data-field="otp">
    <label class="otp-input__label" for="field-otp">Enter <?= $length ?>-digit OTP sent to mobile:</label>
    <input class="otp-input__control" id="field-otp" name="otp" type="text"
           inputmode="numeric" autocomplete="one-time-code"
           maxlength="<?= $length ?>" data-digits="<?= $length ?>" required
           aria-describedby="error-otp">
    <p class="field__error" id="error-otp" role="alert"></p>
    <button class="btn btn--primary otp-input__submit" type="submit">Verify OTP</button>
</div>
