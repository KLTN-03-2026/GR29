<?php

namespace App\Events;

use App\Models\YeuCauCuuHo;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RescueRequestUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $requestId;
    public ?string $status;
    public ?int $userId;
    public ?int $teamId;
    public ?string $teamName;
    public ?string $assignmentStatus;
    public string $action;
    public array $data;
    public string $broadcastAt;

    public function __construct(YeuCauCuuHo $request, string $action = 'updated')
    {
        $request->loadMissing(['phanCongs.doiCuuHo', 'loaiSuCo']);

        $this->requestId = $request->id_yeu_cau ?? $request->id;
        $this->status = $request->trang_thai;
        $this->userId = $request->id_nguoi_dung ?? null;
        $this->action = $action;
        $this->broadcastAt = now()->toISOString();

        $phanCong = $request->phanCongs->first();
        $team = $phanCong?->doiCuuHo;

        $this->teamId = $phanCong?->id_doi_cuu_ho ?? null;
        $this->teamName = $team?->ten_co ?? null;
        $this->assignmentStatus = $phanCong?->trang_thai_nhiem_vu ?? null;

        $this->data = [
            'id' => $this->requestId,
            'id_yeu_cau' => $this->requestId,
            'trang_thai' => $this->status,
            'trang_thai_nhiem_vu' => $this->assignmentStatus,
            'id_nguoi_dung' => $this->userId,
            'id_doi_cuu_ho' => $this->teamId,
            'ten_doi' => $this->teamName,
            'action' => $action,
            'updated_at' => $this->broadcastAt,
            'mo_ta' => $request->mo_ta ?? null,
            'vi_tri_dia_chi' => $request->vi_tri_dia_chi ?? null,
            'muc_do_khan_cap' => $request->muc_do_khan_cap ?? null,
            'loai_su_co' => $request->loaiSuCo
                ? ($request->loaiSuCo->ten_danh_muc ?? $request->loaiSuCo->ten_loai_su_co ?? null)
                : null,
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('rescue-requests'),
            new Channel('rescue-requests.' . $this->userId),
            new Channel('rescue-requests.' . $this->requestId),
        ];
    }

    public function broadcastWith(): array
    {
        return $this->data;
    }
}
