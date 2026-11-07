/**
 * Forms that are sent as JSON with fetch():
 *
 *   <form data-api-form="api/victim/requests" data-validate> ... </form>
 *
 * validation.js checks the fields first (its listener runs before this one and
 * stops invalid forms). Server errors are shown under the fields; on success the
 * browser follows data.redirect or reloads the page to show the flash message.
 */
import { $$ } from './dom.js';
import { submitJsonForm } from './forms.js';

export const initApiForms = (root = document) => {
    $$('form[data-api-form]', root).forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const payload = await submitJsonForm(form, form.dataset.apiForm);
            if (!payload) {
                document.querySelector('[data-flash-area]')?.scrollIntoView({ block: 'center' });
                return;
            }
            const { redirect } = payload.data ?? {};
            if (redirect) {
                window.location.assign(redirect);
            } else {
                window.location.reload();
            }
        });
    });
};
