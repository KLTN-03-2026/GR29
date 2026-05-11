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
            'loaiSuCo.chiTiets',
            'nguoiDung',
            'guestSession',
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

        $loai = $request->loaiSuCo;
        $loaiSuCoPayload = null;
        if ($loai) {
            $chiTiets = $loai->relationLoaded('chiTiets')
                ? $loai->chiTiets->map(fn ($c) => [
                    'ten_chi_tiet' => $c->ten_chi_tiet ?? null,
                    'ten' => $c->ten_chi_tiet ?? null,
                    'name' => $c->ten_chi_tiet ?? null,
                ])->values()->toArray()
                : [];

            $loaiSuCoPayload = [
                'id_loai_su_co' => $loai->id_loai_su_co,
                'ten_danh_muc' => $loai->ten_danh_muc ?? null,
                'ten_loai_su_co' => $loai->ten_danh_muc ?? $loai->ten_loai_su_co ?? null,
                'chi_tiets' => $chiTiets,
            ];
        }

        $nguoiDungPayload = null;
        if ($request->nguoiDung) {
            $u = $request->nguoiDung;
            $nguoiDungPayload = [
                'id_nguoi_dung' => $u->id_nguoi_dung ?? $u->id ?? null,
                'ho_ten' => $u->ho_ten ?? $u->name ?? null,
                'name' => $u->ho_ten ?? $u->name ?? null,
                'so_dien_thoai' => $u->so_dien_thoai ?? $u->phone ?? null,
            ];
        } elseif ($request->guest_session_id && $request->guestSession) {
            $gs = $request->guestSession;
            $guestName = $gs->guest_name ?: 'Khách hàng';
            $nguoiDungPayload = [
                'ho_ten' => $guestName,
                'name' => $guestName,
                'so_dien_thoai' => $gs->so_dien_thoai ?? null,
            ];
        }

        $hinhAnhRaw = $request->getAttributes()['hinh_anh'] ?? $request->hinh_anh ?? null;

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
            'created_at' => $request->created_at?->toISOString(),
            'thoi_gian_gui' => $request->thoi_gian_gui
                ? (is_string($request->thoi_gian_gui) ? $request->thoi_gian_gui : $request->thoi_gian_gui->toISOString())
                : null,
            'mo_ta' => $request->mo_ta ?? null,
            'chi_tiet' => $request->chi_tiet ?? null,
            'vi_tri_lat' => $request->vi_tri_lat ?? null,
            'vi_tri_lng' => $request->vi_tri_lng ?? null,
            'vi_tri_dia_chi' => $request->vi_tri_dia_chi ?? null,
            'dia_chi' => $request->vi_tri_dia_chi ?? null,
            'hinh_anh' => $hinhAnhRaw,
            'imageUrl' => $request->hinhAnhUrl ?? null,
            'so_nguoi_bi_anh_huong' => $request->so_nguoi_bi_anh_huong ?? null,
            'diem_uu_tien' => $request->diem_uu_tien ?? null,
            'id_loai_su_co' => $request->id_loai_su_co ?? null,
            'muc_do_khan_cap' => $request->muc_do_khan_cap ?? null,
            'loai_su_co' => $loaiSuCoPayload,
            'nguoi_dung' => $nguoiDungPayload,
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
