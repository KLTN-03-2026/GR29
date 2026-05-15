<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $adminId;
    public int $trangThai;

    public function __construct(int $adminId, int $trangThai)
    {
        $this->adminId = $adminId;
        $this->trangThai = $trangThai;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('admin.' . $this->adminId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'admin_status_changed';
    }

    public function broadcastWith(): array
    {
        return [
            'trang_thai' => $this->trangThai,
        ];
    }
}
