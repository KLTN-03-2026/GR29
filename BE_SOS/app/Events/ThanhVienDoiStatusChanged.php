<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ThanhVienDoiStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $thanhVienId;
    public int $trangThai;

    public function __construct(int $thanhVienId, int $trangThai)
    {
        $this->thanhVienId = $thanhVienId;
        $this->trangThai = $trangThai;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('thanh-vien-doi.' . $this->thanhVienId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'thanh_vien_doi_status_changed';
    }

    public function broadcastWith(): array
    {
        return [
            'trang_thai' => $this->trangThai,
        ];
    }
}
