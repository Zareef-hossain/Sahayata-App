/**
 * Tiny DOM helpers used by the other modules.
 */

/** First element matching a CSS selector. */
export const $ = (selector, root = document) => root.querySelector(selector);

/** All elements matching a CSS selector, as a real array. */
export const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

/** Show a message in the page's flash area (same look as PHP flash messages). */
export const showFlash = (message, type = 'success', root = document) => {
    const area = $('[data-flash-area]', root);
    if (!area) return;
    const item = document.createElement('p');
    item.className = `flash flash--${type}`;
    item.textContent = message;
    area.replaceChildren(item);
};
