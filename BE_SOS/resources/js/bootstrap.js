import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.Pusher = Pusher;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const reverbAppKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = import.meta.env.VITE_REVERB_HOST ?? window.location.hostname;
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';
const reverbPort = Number(
    import.meta.env.VITE_REVERB_PORT ?? (reverbScheme === 'https' ? 443 : 80),
);

console.log('[Echo][Config]', {
    appKey: reverbAppKey,
    host: reverbHost,
    port: reverbPort,
    scheme: reverbScheme,
    wsUrl: `${reverbScheme}://${reverbHost}:${reverbPort}`,
});

if (!reverbAppKey) {
    console.warn('[Echo][Reverb] Missing VITE_REVERB_APP_KEY. Echo is not initialized.');
} else {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbAppKey,
        wsHost: reverbHost,
        wsPort: reverbPort,
        wssPort: reverbPort,
        forceTLS: reverbScheme === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    window.Echo.connector.pusher.connection.bind('state_change', (states) => {
        console.log('[Echo][Reverb] Connection state:', states);
    });

    window.Echo.connector.pusher.connection.bind('error', (error) => {
        console.error('[Echo][Reverb] Connection error:', error);
    });
}
