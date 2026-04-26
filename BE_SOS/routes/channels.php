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

Broadcast::channel('rescue-requests', function () {
    return true;
});

Broadcast::channel('rescue-requests.{userId}', function ($user, $userId) {
    return true;
});

Broadcast::channel('rescue-requests.{requestId}', function ($user, $requestId) {
    return true;
});
