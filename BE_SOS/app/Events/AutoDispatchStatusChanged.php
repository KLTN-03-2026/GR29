<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AutoDispatchStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public bool $enabled;

    public function __construct(bool $enabled)
    {
        $this->enabled = $enabled;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('admin.dispatch'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'auto_dispatch_status_changed';
    }

    public function broadcastWith(): array
    {
        return [
            'enabled' => $this->enabled,
            'timestamp' => now()->toISOString(),
        ];
    }
}
