/**
 * Talks to the PHP backend with fetch() and JSON.
 *
 *   const { data } = await getJson('api/health');
 *   await postJson('api/requests/4/approve', { note: 'ok' });
 *
 * POST requests automatically send the CSRF token from the
 * <meta name="csrf-token"> tag in the page layout.
 */

const readMeta = (name) => document.querySelector(`meta[name="${name}"]`)?.content ?? '';

/** Build a URL inside the app, e.g. appUrl('api/health') -> /sahayata-app/api/health */
export const appUrl = (path = '') => `${readMeta('base-url')}/${path.replace(/^\/+/, '')}`;

/** Error thrown when the server answers with success: false. */
export class ApiError extends Error {
    constructor(message, { status = 0, errors = {} } = {}) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.errors = errors; // field name -> message, for showing under form fields
    }
}

export const apiRequest = async (path, { method = 'GET', body, withCsrf = true } = {}) => {
    const headers = { Accept: 'application/json' };
    const options = { method, headers, credentials: 'same-origin' };

    if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(body);
    }
    if (method !== 'GET' && withCsrf) {
        headers['X-CSRF-Token'] = readMeta('csrf-token');
    }

    let response;
    try {
        response = await fetch(appUrl(path), options);
    } catch {
        throw new ApiError('Could not reach the server. Check your connection.');
    }

    const payload = await response.json().catch(() => ({
        success: false,
        message: 'The server sent an unexpected response.',
    }));

    if (!response.ok || !payload.success) {
        const { message = 'Request failed.', errors = {} } = payload;
        throw new ApiError(message, { status: response.status, errors });
    }
    return payload;
};

export const getJson = (path) => apiRequest(path);

export const postJson = (path, body = {}, options = {}) =>
    apiRequest(path, { ...options, method: 'POST', body });
