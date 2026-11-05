/**
 * Client-side form validation (instant feedback).
 * The server checks everything again in app/Core/Validator.php —
 * never rely on JavaScript alone for security.
 *
 * Usage: add data-validate to a <form>. Rules come from normal HTML attributes:
 *   required, type="email", minlength, maxlength, min,
 *   data-rule="phone", data-rule="password", data-match="other_field_name"
 */
import { $, $$ } from './dom.js';

const PHONE_PATTERN = /^(\+?88)?01[3-9]\d{8}$/; // same rule as Validator::isPhone()

/** Text shown above the field, without a trailing colon: "Phone Number". */
const labelOf = (control) => {
    const label = control.closest('.field')?.querySelector('.field__label')
        ?? control.closest('.option-group')?.querySelector('.option-group__legend');
    if (control.name === 'otp') return 'OTP';
    return (label?.textContent ?? control.name).replace(/\s*:\s*$/, '').replace(/\s+/g, ' ').trim();
};

/** Returns an error message for one control, or '' when it is valid. */
const messageFor = (control, form) => {
    const label = labelOf(control);

    if (control.type === 'radio') {
        const group = $$(`input[name="${control.name}"]`, form);
        const isRequired = group.some((input) => input.required);
        // "Choose Login Type" -> "Please choose a Login Type."
        const choice = label.replace(/^(choose|select)\s+/i, '');
        return isRequired && !group.some((input) => input.checked) ? `Please choose a ${choice}.` : '';
    }

    const value = control.value.trim();
    const { rule, match, digits } = control.dataset;

    if (control.required && value === '') return `${label} is required.`;
    if (value === '') return '';

    if (control.type === 'email' && !control.checkValidity()) return 'Please enter a valid email address.';
    if (rule === 'phone' && !PHONE_PATTERN.test(value)) {
        return 'Please enter a valid Bangladeshi mobile number (e.g. 017XXXXXXXX).';
    }
    if (digits && !new RegExp(`^\\d{${Number(digits)}}$`).test(value)) {
        return `${label} must be ${digits} digits.`;
    }
    if (rule === 'password' && !(/[A-Za-z]/.test(value) && /\d/.test(value))) {
        return `${label} must contain at least one letter and one number.`;
    }
    if (control.minLength > 0 && value.length < control.minLength) {
        return `${label} must be at least ${control.minLength} characters.`;
    }
    if (control.maxLength > 0 && value.length > control.maxLength) {
        return `${label} must be at most ${control.maxLength} characters.`;
    }
    if (control.type === 'number' && control.min !== '' && Number(value) < Number(control.min)) {
        return `${label} must be at least ${control.min}.`;
    }
    if (match && value !== (form.elements[match]?.value ?? '')) return `${label} does not match.`;
    return '';
};

/** Show (or clear) the message under one field. */
export const setFieldError = (form, name, message = '') => {
    const errorElement = $(`#error-${CSS.escape(name)}`, form);
    if (errorElement) errorElement.textContent = message;

    $$(`[name="${CSS.escape(name)}"]`, form).forEach((control) => {
        control.classList.toggle('is-invalid', message !== '' && control.type !== 'radio');
        if (message) control.setAttribute('aria-invalid', 'true');
        else control.removeAttribute('aria-invalid');
    });
};

/** Show errors returned by the server: { field_name: 'message', ... } */
export const showServerErrors = (form, errors = {}) => {
    Object.entries(errors).forEach(([name, message]) => setFieldError(form, name, message));
};

/** Check every field. Returns true when the whole form is valid. */
export const validateForm = (form) => {
    const checkedGroups = new Set();
    let firstInvalid = null;

    for (const control of form.elements) {
        if (!control.name || control.disabled || control.type === 'hidden' || control.type === 'submit') continue;
        if (control.type === 'radio') {
            if (checkedGroups.has(control.name)) continue;
            checkedGroups.add(control.name);
        }
        const message = messageFor(control, form);
        setFieldError(form, control.name, message);
        if (message && !firstInvalid) firstInvalid = control;
    }

    firstInvalid?.focus();
    return firstInvalid === null;
};

/** Turn on validation for every <form data-validate> on the page. */
export const initFormValidation = (root = document) => {
    $$('form[data-validate]', root).forEach((form) => {
        form.setAttribute('novalidate', ''); // use our messages instead of the browser's

        // Runs before other submit listeners; stops them when the form is invalid.
        form.addEventListener('submit', (event) => {
            if (!validateForm(form)) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        });

        // Clear a field's message as soon as the user changes it.
        const clearError = ({ target }) => {
            if (target.name) setFieldError(form, target.name, '');
        };
        form.addEventListener('input', clearError);
        form.addEventListener('change', clearError);
    });
};
