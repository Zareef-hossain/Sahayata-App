/**
 * Helpers for forms that are sent with fetch() instead of a page reload.
 */
import { ApiError, postJson } from './api.js';
import { showFlash } from './dom.js';
import { showServerErrors } from './validation.js';

/** Form values as a plain object (the CSRF token is sent as a header instead). */
export const formValues = (form) => {
    const { _csrf, ...values } = Object.fromEntries(new FormData(form));
    return values;
};

/** Disable a form's submit buttons while a request is running. */
export const setBusy = (form, isBusy) => {
    form.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.disabled = isBusy;
        button.setAttribute('aria-busy', String(isBusy));
    });
};

/**
 * Send a form to a JSON endpoint and handle the common cases:
 * busy state, field errors and the flash message.
 * Resolves with the server payload on success, or null on failure.
 */
export const submitJsonForm = async (form, path, values = formValues(form)) => {
    setBusy(form, true);
    try {
        return await postJson(path, values);
    } catch (error) {
        if (error instanceof ApiError) {
            showServerErrors(form, error.errors);
            showFlash(error.message, 'error');
        } else {
            showFlash('Something went wrong. Please try again.', 'error');
        }
        return null;
    } finally {
        setBusy(form, false);
    }
};
