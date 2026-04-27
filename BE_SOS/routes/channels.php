<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Register all the broadcast channels for your application.
|
*/

Broadcast::channel('rescue-requests', function ($user) {
    // Only authenticated users can listen to general rescue requests channel
    // This is mainly for admin users to monitor all requests
    return $user !== null;
});

Broadcast::channel('rescue-requests.{userId}', function ($user, $userId) {
    // Users can only listen to their own requests
    return $user && $user->id == $userId;
});

Broadcast::channel('rescuer-location.{teamId}', function ($user, $teamId) {
    // Users can listen to location updates of teams assigned to their requests
    // For now, allow authenticated users - can be refined based on team assignments
    return $user !== null;
});
