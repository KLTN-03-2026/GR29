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
        $request->loadMissing([
            'phanCongs.doiCuuHo',
            'phanCongs.thanhVienTiepNhan',
            'phanCongs.ketQua',
            'loaiSuCo',
        ]);

        $this->requestId = $request->id_yeu_cau ?? $request->id;
        $this->status = $request->trang_thai;
        $this->userId = $request->id_nguoi_dung ?? null;
        $this->action = $action;
        $this->broadcastAt = now()->toISOString();

        $phanCong = $request->phanCongs->first();
        $team = $phanCong?->doiCuuHo;
        $rescuer = $phanCong?->thanhVienTiepNhan;

        $this->teamId = $phanCong?->id_doi_cuu_ho ?? null;
        $this->teamName = $team?->ten_doi ?? null;
        $this->assignmentStatus = $phanCong?->trang_thai_nhiem_vu ?? null;

        $this->data = [
            'id' => $this->requestId,
            'id_yeu_cau' => $this->requestId,
            'trang_thai' => $this->status,
            'trang_thai_nhiem_vu' => $this->assignmentStatus,
            'id_nguoi_dung' => $this->userId,
            'id_doi_cuu_ho' => $this->teamId,
            'ten_doi' => $this->teamName,
            'id_phan_cong' => $phanCong?->id_phan_cong ?? null,
            'id_thanh_vien_tiep_nhan' => $rescuer ? ($rescuer->id_thanh_vien_doi ?? $rescuer->id) : null,
            'ten_thanh_vien_tiep_nhan' => $rescuer
                ? ($rescuer->ho_ten ?? $rescuer->name ?? null)
                : null,
            'phan_congs' => $request->phanCongs->map(fn($pc) => [
                'id_phan_cong' => $pc->id_phan_cong ?? $pc->id,
                'id_doi' => $pc->id_doi_cuu_ho ?? $pc->doiCuuHo?->id,
                'ten_doi' => $pc->doiCuuHo?->ten_doi ?? null,
                'trang_thai_nhiem_vu' => $pc->trang_thai_nhiem_vu ?? null,
                'thoi_gian_tiep_nhan' => $pc->thoi_gian_tiep_nhan ?? null,
                'thanh_viens' => $pc->thanhVienTiepNhan ? [[
                    'id' => $pc->thanhVienTiepNhan->id_thanh_vien_doi ?? $pc->thanhVienTiepNhan->id,
                    'ho_ten' => $pc->thanhVienTiepNhan->ho_ten ?? $pc->thanhVienTiepNhan->name ?? 'N/A',
                ]] : [],
                'da_tiep_nhan' => $pc->thanhVienTiepNhan !== null,
            ])->toArray(),
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
