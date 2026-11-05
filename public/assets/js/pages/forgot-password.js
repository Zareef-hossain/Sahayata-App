/**
 * Forgot Password: request a demo OTP, then set a new password.
 */
import { $, showFlash } from '../modules/dom.js';
import { formValues, submitJsonForm } from '../modules/forms.js';
import { resetOtpInput, showOtpPanel } from '../modules/otp.js';

const requestForm = $('[data-reset-request-form]');
const otpPanel = $('[data-otp-panel]');
const resetForm = $('[data-reset-form]');

const requestOtp = async () => {
    const payload = await submitJsonForm(requestForm, 'api/auth/forgot-password');
    if (!payload) return;

    resetOtpInput(otpPanel);
    showFlash(payload.message, 'success');   // same text whether or not the email exists
    showOtpPanel(otpPanel, payload.data);
};

// Step 1: email -> OTP
requestForm.addEventListener('submit', (event) => {
    event.preventDefault();
    requestOtp();
});

// "Resend OTP" sends step 1 again with the same email.
$('[data-resend-otp]')?.addEventListener('click', requestOtp);

// Step 2: OTP + new password -> back to the login screen
resetForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const payload = await submitJsonForm(resetForm, 'api/auth/reset-password', formValues(resetForm));
    if (payload) window.location.assign(payload.data.redirect);
});
