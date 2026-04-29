import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});

// Connection status monitoring
window.realtimeConnectionStatus = 'connecting';

const updateConnectionStatus = (status) => {
    window.realtimeConnectionStatus = status;
    console.log('[Reverb] Connection status:', status);

    // Dispatch custom event for components to listen
    window.dispatchEvent(new CustomEvent('realtime-connection-change', {
        detail: { status }
    }));
};

// Monitor Pusher connection
if (window.Echo.connector?.pusher?.connection) {
    const connection = window.Echo.connector.pusher.connection;

    connection.bind('connected', () => {
        updateConnectionStatus('connected');
    });

    connection.bind('connecting', () => {
        updateConnectionStatus('connecting');
    });

    connection.bind('disconnected', () => {
        updateConnectionStatus('disconnected');
    });

    connection.bind('failed', () => {
        updateConnectionStatus('failed');
    });

    connection.bind('unavailable', () => {
        updateConnectionStatus('unavailable');
    });
}
