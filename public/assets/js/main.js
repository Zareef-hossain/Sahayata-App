/**
 * Shared JavaScript, loaded on every page.
 * Page-specific code lives in assets/js/pages/ and is loaded after this file.
 *
 * Order matters: validation is registered first so invalid forms are stopped
 * before api-forms.js sends them.
 */
import { initFormValidation } from './modules/validation.js';
import { initApiForms } from './modules/api-forms.js';
import { initApiActions } from './modules/actions.js';

initFormValidation();
initApiForms();
initApiActions();
