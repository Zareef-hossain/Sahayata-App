/**
 * Phase 1 component preview: buttons that test the backend foundation.
 */
import { $, showFlash } from '../modules/dom.js';
import { ApiError, getJson, postJson } from '../modules/api.js';

const output = $('[data-dev-output]');

const show = (label, result) => {
    output.textContent = `${label}\n${JSON.stringify(result, null, 2)}`;
};

const run = async (label, request) => {
    output.textContent = `${label}\nWorking...`;
    try {
        show(`${label}: OK`, await request());
    } catch (error) {
        const { message, status } = error instanceof ApiError ? error : { message: String(error), status: 0 };
        show(`${label}: FAILED`, { status, message });
    }
};

$('[data-health-check]')?.addEventListener('click', () =>
    run('Database check', () => getJson('api/health')));

$('[data-csrf-check]')?.addEventListener('click', () =>
    run('With CSRF token', () => postJson('api/echo', { message: 'hello' })));

$('[data-csrf-fail-check]')?.addEventListener('click', () =>
    run('Without CSRF token (should fail with 403)', () =>
        postJson('api/echo', { message: 'hello' }, { withCsrf: false })));

// The preview form only checks validation; nothing is saved.
$('[data-demo-form]')?.addEventListener('submit', (event) => {
    event.preventDefault();
    showFlash('All fields are valid. (Preview only — nothing was saved.)');
});
