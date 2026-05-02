<?php

namespace App\Events;

use App\Models\DoiCuuHo;
use App\Models\PhanCongCuuHo;
use App\Models\YeuCauCuuHo;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * AssignTeamEvent - Sự kiện được broadcast khi đội cứu hộ được gán tự động cho yêu cầu.
 *
 * Broadcast channels:
 * - rescue-requests: Tất cả admin theo dõi
 * - rescue-requests.{userId}: Người dùng gửi yêu cầu
 * - rescue-requests.{requestId}: Admin đang xem chi tiết yêu cầu
 * - team.{teamId}: Đội cứu hộ liên quan
 */
class AssignTeamEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $requestId;
    public ?int $teamId;
    public ?int $phanCongId;
    public ?string $teamName;
    public ?string $teamPhone;
    public string $action;
    public array $data;
    public string $broadcastAt;

    public function __construct(DoiCuuHo $doi, YeuCauCuuHo $yeuCau, ?int $phanCongId = null)
    {
        $doi->loadMissing(['thanhViens']);

        $this->requestId = $yeuCau->id_yeu_cau ?? $yeuCau->id;
        $this->teamId = $doi->id_doi_cuu_ho ?? $doi->id;
        $this->phanCongId = $phanCongId;
        $this->teamName = $doi->ten_doi ?? 'Đội cứu hộ';
        $this->teamPhone = $doi->so_dien_thoai_hotline ?? null;
        $this->action = 'auto_dispatched';
        $this->broadcastAt = now()->toISOString();

        $thanhVienCount = $doi->thanhViens ? $doi->thanhViens->count() : 0;

        $this->data = [
            'id_yeu_cau' => $this->requestId,
            'id' => $this->requestId,
            'trang_thai' => 'DA_PHAN_CONG',
            'trang_thai_nhiem_vu' => 'MOI',
            'id_doi_cuu_ho' => $this->teamId,
            'ten_doi' => $this->teamName,
            'sdt_hotline' => $this->teamPhone,
            'id_phan_cong' => $phanCongId,
            'phan_congs' => [
                [
                    'id_phan_cong' => $phanCongId,
                    'id_doi' => $this->teamId,
                    'ten_doi' => $this->teamName,
                    'trang_thai_nhiem_vu' => 'MOI',
                    'thoi_gian_phan_cong' => $this->broadcastAt,
                    'thanh_viens' => $doi->thanhViens ? $doi->thanhViens->map(fn($tv) => [
                        'id' => $tv->id_thanh_vien_doi ?? $tv->id,
                        'ho_ten' => $tv->ho_ten ?? $tv->name ?? 'N/A',
                    ])->toArray() : [],
                    'da_tiep_nhan' => false,
                ],
            ],
            'action' => $this->action,
            'updated_at' => $this->broadcastAt,
            'mo_ta' => $yeuCau->mo_ta ?? null,
            'vi_tri_dia_chi' => $yeuCau->vi_tri_dia_chi ?? null,
            'vi_tri_lat' => $yeuCau->vi_tri_lat ? floatval($yeuCau->vi_tri_lat) : null,
            'vi_tri_lng' => $yeuCau->vi_tri_lng ? floatval($yeuCau->vi_tri_lng) : null,
            'muc_do_khan_cap' => $yeuCau->muc_do_khan_cap ?? 'MEDIUM',
            'diem_uu_tien' => $yeuCau->diem_uu_tien ?? 0,
            'id_nguoi_dung' => $yeuCau->id_nguoi_dung ?? null,
            'so_thanh_vien' => $thanhVienCount,
            'phan_cong_tu_dong' => true,
            'message' => "Hệ thống đã tự động phân công đội {$this->teamName} đến xử lý yêu cầu #{$this->requestId}",
        ];
    }

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('rescue-requests'),
            new Channel('rescue-requests.' . $this->requestId),
        ];

        // Gửi thông báo đến người dùng gửi yêu cầu
        $yeuCau = YeuCauCuuHo::find($this->requestId);
        if ($yeuCau && $yeuCau->id_nguoi_dung) {
            $channels[] = new Channel('rescue-requests.' . $yeuCau->id_nguoi_dung);
        }

        // Gửi thông báo đến đội cứu hộ được gán
        if ($this->teamId) {
            $channels[] = new Channel('team.' . $this->teamId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'AssignTeamEvent';
    }

    public function broadcastWith(): array
    {
        return $this->data;
    }
}
