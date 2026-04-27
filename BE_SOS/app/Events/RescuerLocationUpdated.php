<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RescuerLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $teamId;
    public $lat;
    public $lng;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct($teamId, $lat, $lng, $timestamp = null)
    {
        $this->teamId = $teamId;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->timestamp = $timestamp ?: now();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("rescuer-location.{$this->teamId}"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'location.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'team_id' => $this->teamId,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'timestamp' => $this->timestamp->toISOString(),
        ];
    }
}
