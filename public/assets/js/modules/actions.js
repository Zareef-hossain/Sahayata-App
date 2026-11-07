/**
 * Action buttons that call a JSON endpoint, e.g. Approve / Reject / Accept Task.
 *
 *   <button data-api-action="api/admin/requests/4/approve"
 *           data-confirm="Approve this request?"      (optional)
 *           data-body='{"volunteer_id":3}'>           (optional JSON body)
 *
 * On success the server stores a flash message; the page is reloaded (or the
 * browser follows data.redirect) so it always shows the current database state.
 * The server checks the user's role again — the button is only a convenience.
 */
import { ApiError, postJson } from './api.js';
import { showFlash } from './dom.js';

const runAction = async (button) => {
    const { apiAction, confirm: question, body } = button.dataset;
    if (question && !window.confirm(question)) return;

    button.disabled = true;
    try {
        const { data = {} } = await postJson(apiAction, body ? JSON.parse(body) : {});
        if (data.redirect) {
            window.location.assign(data.redirect);
        } else {
            window.location.reload();
        }
    } catch (error) {
        showFlash(error instanceof ApiError ? error.message : 'Something went wrong. Please try again.', 'error');
        document.querySelector('[data-flash-area]')?.scrollIntoView({ block: 'center' });
        button.disabled = false;
    }
};

/** One listener for the whole page (also works for buttons added later). */
export const initApiActions = (root = document) => {
    root.addEventListener('click', (event) => {
        const button = event.target.closest('[data-api-action]');
        if (button) runAction(button);
    });
};
