<?php

namespace App\Events;

use App\Models\DanhGiaCuuHo;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DanhGiaCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $danhGia;
    public $id_doi_cuu_ho;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($danhGia, $id_doi_cuu_ho)
    {
        $this->danhGia = $danhGia;
        $this->id_doi_cuu_ho = $id_doi_cuu_ho;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('team.reviews.' . $this->id_doi_cuu_ho);
    }

    public function broadcastAs()
    {
        return 'ReviewCreated';
    }
}
