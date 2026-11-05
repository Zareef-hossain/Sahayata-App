/**
 * Sign Up screen: create the account, then verify the demo OTP.
 */
import { $, showFlash } from '../modules/dom.js';
import { ApiError, postJson } from '../modules/api.js';
import { submitJsonForm } from '../modules/forms.js';
import { resetOtpInput, showOtpPanel } from '../modules/otp.js';

const registerForm = $('[data-register-form]');
const otpPanel = $('[data-otp-panel]');
const otpForm = $('[data-otp-form]');

// Step 1: Sign Up (runs only when validation.js found no errors).
registerForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const payload = await submitJsonForm(registerForm, 'api/auth/register');
    if (!payload) return;

    registerForm.querySelectorAll('input, button').forEach((control) => { control.disabled = true; });
    showOtpPanel(otpPanel, payload.data);
});

// Step 2: Verify OTP -> logged in and sent to the role's home page.
otpForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const payload = await submitJsonForm(otpForm, 'api/auth/verify-otp');
    if (payload) window.location.assign(payload.data.redirect);
});

$('[data-resend-otp]')?.addEventListener('click', async () => {
    try {
        const { data } = await postJson('api/auth/resend-otp');
        resetOtpInput(otpPanel);
        showOtpPanel(otpPanel, data);
    } catch (error) {
        showFlash(error instanceof ApiError ? error.message : 'Could not send a new OTP.', 'error');
    }
});
