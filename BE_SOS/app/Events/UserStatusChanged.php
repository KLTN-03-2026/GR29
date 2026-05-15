<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public int $trangThai;

    public function __construct(int $userId, int $trangThai)
    {
        $this->userId = $userId;
        $this->trangThai = $trangThai;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('user.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'user_status_changed';
    }

    public function broadcastWith(): array
    {
        return [
            'trang_thai' => $this->trangThai,
        ];
    }
}
