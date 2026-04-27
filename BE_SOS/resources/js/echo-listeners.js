// Real-time listeners for rescue request updates
// This file handles automatic UI updates when rescue requests are modified

// Global rescue request listeners
window.Echo.channel('rescue-requests')
    .listen('RescueRequestUpdated', (event) => {
        console.log('[Real-time] Global rescue request updated (RescueRequestUpdated):', event);

        // Dispatch custom event for components to handle
        window.dispatchEvent(new CustomEvent('rescueRequestUpdated', {
            detail: event
        }));

        // Auto-refresh rescue requests if on relevant page
        if (window.location.pathname.includes('/admin') ||
            window.location.pathname.includes('/rescuer') ||
            window.location.pathname.includes('/dashboard')) {
            // Trigger page refresh or specific component update
            window.dispatchEvent(new CustomEvent('refreshRescueRequests'));
        }
    })
    .listen('.rescue-request-updated', (event) => {
        console.log('[Real-time] Global rescue request updated (.rescue-request-updated):', event);
    })
    .listen('rescue-request-updated', (event) => {
        console.log('[Real-time] Global rescue request updated (rescue-request-updated):', event);
    });

// Listen for user-specific updates (for client dashboard)
if (window.currentUserId) {
    window.Echo.channel(`rescue-requests.${window.currentUserId}`)
        .listen('.rescue-request-updated', (event) => {
            console.log('[Real-time] User-specific rescue request updated:', event);

            // Show notification to user
            showNotification('Yêu cầu cứu hộ của bạn đã được cập nhật!', 'info');

            // Refresh user's rescue requests
            window.dispatchEvent(new CustomEvent('refreshUserRequests'));
        });
}

// Listen for specific request updates (for detailed view)
if (window.currentRequestId) {
    window.Echo.channel(`rescue-requests.${window.currentRequestId}`)
        .listen('.rescue-request-updated', (event) => {
            console.log('[Real-time] Specific request updated:', event);

            // Refresh request details
            window.dispatchEvent(new CustomEvent('refreshRequestDetails', {
                detail: { requestId: window.currentRequestId }
            }));
        });
}

// Helper function to show notifications (you can implement this)
function showNotification(message, type = 'info') {
    // Simple browser notification
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification('SOS App', { body: message });
    }

    // You can also dispatch event for your UI notification system
    window.dispatchEvent(new CustomEvent('showNotification', {
        detail: { message, type }
    }));
}

// Connection status monitoring
window.Echo.connector.pusher.connection.bind('state_change', (states) => {
    console.log('[Echo] Connection state changed:', states);

    if (states.current === 'connected') {
        console.log('✅ Real-time connection established');
        window.dispatchEvent(new CustomEvent('echoConnected'));
    } else if (states.current === 'disconnected') {
        console.log('❌ Real-time connection lost');
        window.dispatchEvent(new CustomEvent('echoDisconnected'));
    }
});