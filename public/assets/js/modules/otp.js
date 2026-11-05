/**
 * Shared behaviour of the OTP section (Sign Up and Forgot Password).
 *
 * DEMO MODE: real SMS delivery is not configured. When the server runs in demo
 * mode it returns the code as `demo_otp`, and it is shown inside the OTP section.
 */
import { $ } from './dom.js';

/** Show the OTP section with the "sent to ..." and demo-code message. */
export const showOtpPanel = (panel, { phone_masked: phone, demo_otp: demoCode } = {}) => {
    panel.hidden = false;

    const notice = $('[data-otp-notice]', panel);
    if (notice) {
        const parts = [];
        if (phone) parts.push(`OTP sent to ${phone}.`);
        if (demoCode) parts.push(`Demo OTP: ${demoCode} (SMS is not configured in this demo).`);
        if (parts.length) notice.textContent = parts.join(' ');
    }
    $('#field-otp', panel)?.focus();
};

/** Empty the OTP field, e.g. after a new code was requested. */
export const resetOtpInput = (panel) => {
    const input = $('#field-otp', panel);
    if (input) input.value = '';
};
