/**
 * Shared JavaScript, loaded on every page.
 * Page-specific code lives in assets/js/pages/ and is loaded after this file.
 */
import { initFormValidation } from './modules/validation.js';

initFormValidation();
