<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class YeuCauCapPhatRealtime implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $hanhDong;

    public array $duLieu;

    public string $thoiDiemPhat;

    public function __construct(string $hanhDong, array $duLieu)
    {
        $this->hanhDong = $hanhDong;
        $this->duLieu = $duLieu;
        $this->thoiDiemPhat = now()->toISOString();
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('admin-yeu-cau-cap-phat'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'yeu-cau-cap-phat';
    }

    public function broadcastWith(): array
    {
        return [
            'hanh_dong' => $this->hanhDong,
            'du_lieu' => $this->duLieu,
            'thoi_diem' => $this->thoiDiemPhat,
        ];
    }
}
