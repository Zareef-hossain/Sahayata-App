/**
 * Request for Help: "📍 My Location" button.
 * Uses the browser's Geolocation API to fill the hidden latitude/longitude fields.
 * The location text field stays required, so a request works without coordinates
 * (e.g. when the user refuses permission or the page is not on HTTPS/localhost).
 */
import { $ } from '../modules/dom.js';

const button = $('[data-my-location]');
const status = $('[data-location-status]');
const form = button?.closest('form');

const setStatus = (message) => {
    status.textContent = message;
};

const errorMessages = {
    1: 'Location permission was denied. Please type your location instead.',
    2: 'Your location is not available right now. Please type it instead.',
    3: 'Finding your location took too long. Please type it instead.',
};

const locate = () =>
    new Promise((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(resolve, reject, { enableHighAccuracy: true, timeout: 10000 });
    });

button?.addEventListener('click', async () => {
    if (!('geolocation' in navigator)) {
        setStatus('This browser cannot share your location. Please type it instead.');
        return;
    }

    button.disabled = true;
    setStatus('Finding your location...');
    try {
        const { coords: { latitude, longitude } } = await locate();
        form.elements.latitude.value = latitude.toFixed(6);
        form.elements.longitude.value = longitude.toFixed(6);
        setStatus(`📍 Location added (${latitude.toFixed(4)}, ${longitude.toFixed(4)}).`);
    } catch ({ code }) {
        setStatus(errorMessages[code] ?? 'Could not get your location. Please type it instead.');
    } finally {
        button.disabled = false;
    }
});
