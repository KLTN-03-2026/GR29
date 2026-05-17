<?php

namespace App\Jobs;

use App\Events\AssignTeamEvent;
use App\Models\DoiCuuHo;
use App\Models\PhanCongCuuHo;
use App\Models\YeuCauCuuHo;
use App\Services\AutoDispatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * AutoDispatchJob - Job xử lý điều phối tự động đội cứu hộ.
 *
 * Flow:
 * 1. Kiểm tra điều phối tự động có được bật không
 * 2. Gọi AutoDispatchService để xử lý
 * 3. Nếu gán thành công → broadcast AssignTeamEvent
 * 4. Nếu không thành công và chưa vượt retry → retry sau 30 phút
 * 5. Nếu vượt retry → gửi notification cho admin
 */
class AutoDispatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Số phút chờ trước khi retry */
    private const THOI_GIAN_CHO_RETRY_PHUT = 30;

    /** Số lần retry tối đa */
    private const SO_LAN_RETRY_TOI_DA = 3;

    public int $idYeuCau;
    public int $soLanRetry;

    public int $tries = 5;
    public int $maxExceptions = 3;
    public int $backoff = 30;

    /**
     * Create a new job instance.
     *
     * @param int $idYeuCau ID của yêu cầu cứu hộ
     * @param int $soLanRetry Số lần retry hiện tại (mặc định 0 = lần đầu)
     */
    public function __construct(int $idYeuCau, int $soLanRetry = 0)
    {
        $this->idYeuCau = $idYeuCau;
        $this->soLanRetry = $soLanRetry;
        // Chi chay sau khi transaction hien tai da commit,
        // tranh race condition voi store() dang tao HangDoiXuLy
        $this->afterCommit = true;
    }

    /**
     * Execute the job.
     */
    public function handle(AutoDispatchService $dispatchService): void
    {
        Log::info('[AutoDispatchJob] Bắt đầu xử lý', [
            'id_yeu_cau' => $this->idYeuCau,
            'so_lan_retry' => $this->soLanRetry,
        ]);

        // === Bước 1: Kiểm tra điều phối tự động có được bật không ===
        if (!AutoDispatchService::daBat()) {
            Log::info('[AutoDispatchJob] Điều phối tự động đang tắt, bỏ qua', [
                'id_yeu_cau' => $this->idYeuCau,
            ]);
            return;
        }

        // === Bước 2: Kiểm tra yêu cầu còn tồn tại ===
        $yeuCau = YeuCauCuuHo::find($this->idYeuCau);
        if (!$yeuCau) {
            Log::warning('[AutoDispatchJob] Yêu cầu không tồn tại, bỏ qua', [
                'id_yeu_cau' => $this->idYeuCau,
            ]);
            return;
        }

        // === Bước 3: Kiểm tra đã được phân công chưa ===
        $phanCong = PhanCongCuuHo::where('id_yeu_cau', $this->idYeuCau)->exists();
        if ($phanCong) {
            Log::info('[AutoDispatchJob] Yêu cầu đã được phân công, bỏ qua', [
                'id_yeu_cau' => $this->idYeuCau,
            ]);
            return;
        }

        // === Bước 4: Kiểm tra trạng thái yêu cầu ===
        $trangThai = strtoupper(trim((string) ($yeuCau->trang_thai ?? '')));
        $trangThaiChoPhep = ['CHO_XU_LY', 'MOI', 'WAITING', 'CHO_PHAN_CONG'];
        if (!in_array($trangThai, $trangThaiChoPhep, true)) {
            Log::info('[AutoDispatchJob] Yêu cầu không ở trạng thái cho phép, bỏ qua', [
                'id_yeu_cau' => $this->idYeuCau,
                'trang_thai' => $trangThai,
            ]);
            return;
        }

        // === Bước 5: Gọi service xử lý điều phối ===
        $ketQua = $dispatchService->xuLyDieuPhoiTuDong($this->idYeuCau);

        if ($ketQua['thanh_cong']) {
            $this->xuLyThanhCong($ketQua, $yeuCau);
        } else {
            $this->xuLyThatBai($ketQua, $yeuCau);
        }
    }

    /**
     * Xử lý khi điều phối thành công.
     * Broadcast sự kiện AssignTeamEvent.
     *
     * @param array $ketQua
     * @param YeuCauCuuHo $yeuCau
     */
    private function xuLyThanhCong(array $ketQua, YeuCauCuuHo $yeuCau): void
    {
        Log::info('[AutoDispatchJob] Điều phối thành công', [
            'id_yeu_cau' => $this->idYeuCau,
            'doi_id' => $ketQua['doi_id'],
            'phan_cong_id' => $ketQua['phan_cong_id'] ?? null,
        ]);

        // Lấy thông tin đội đã gán
        $doi = DoiCuuHo::with('phanCongs')->find($ketQua['doi_id']);
        if ($doi) {
            // Broadcast event đến tất cả client đang theo dõi
            $phanCongId = $ketQua['phan_cong_id'] ?? null;
            try {
                broadcast(new AssignTeamEvent($doi, $yeuCau, $phanCongId))->toOthers();
            } catch (\Throwable $e) {
                Log::warning('[AutoDispatchJob] Broadcast thất bại', [
                    'id_yeu_cau' => $this->idYeuCau,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Xử lý khi điều phối thất bại.
     * Quyết định có retry hay escalate lên admin.
     *
     * @param array $ketQua
     * @param YeuCauCuuHo $yeuCau
     */
    private function xuLyThatBai(array $ketQua, YeuCauCuuHo $yeuCau): void
    {
        Log::warning('[AutoDispatchJob] Điều phối thất bại', [
            'id_yeu_cau' => $this->idYeuCau,
            'thong_diep' => $ketQua['thong_diep'],
            'so_lan_retry_hien_tai' => $this->soLanRetry,
        ]);

        // Nếu tất cả đội phù hợp đều đầy capacity → dừng retry,
        // đưa vào hàng chờ "chờ đội trống" để tự dispatch lại khi có slot
        if (!empty($ketQua['tat_ca_day_capacity'])) {
            $this->daoVaoHangChoDoiTrong($yeuCau);
            return;
        }

        // Kiểm tra xem có thể retry hay không
        if ($this->soLanRetry < self::SO_LAN_RETRY_TOI_DA) {
            // Dispatch retry job sau 30 phút
            $retryJob = new self($this->idYeuCau, $this->soLanRetry + 1);
            $delaySeconds = self::THOI_GIAN_CHO_RETRY_PHUT * 60;

            Log::info('[AutoDispatchJob] Sẽ retry sau', [
                'id_yeu_cau' => $this->idYeuCau,
                'so_lan_retry_tiep' => $this->soLanRetry + 1,
                'delay_seconds' => $delaySeconds,
            ]);

            self::dispatch($retryJob)
                ->delay(now()->addMinutes(self::THOI_GIAN_CHO_RETRY_PHUT))
                ->onQueue('auto-dispatch');
        } else {
            // Vượt quá số lần retry → cảnh báo admin
            Log::error('[AutoDispatchJob] Vượt số lần retry, cần can thiệp admin', [
                'id_yeu_cau' => $this->idYeuCau,
                'so_lan_retry' => $this->soLanRetry,
                'thong_diep' => $ketQua['thong_diep'],
            ]);

            $this->thongBaoAdminCanThiep($yeuCau);
        }
    }

    /**
     * Đưa yêu cầu vào hàng chờ "chờ đội trống".
     * Khi có đội có slot trở lại (CoDoiTrongTroLai), sẽ tự dispatch lại.
     */
    private function daoVaoHangChoDoiTrong(YeuCauCuuHo $yeuCau): void
    {
        $idLoaiSuCo = $yeuCau->id_loai_su_co;
        $khoaCho = "cho_doi_trong_loai_{$idLoaiSuCo}";

        $danhSachCho = Cache::get($khoaCho, []);

        // Tránh trùng lặp
        if (!in_array($this->idYeuCau, $danhSachCho, true)) {
            $danhSachCho[] = $this->idYeuCau;
            Cache::put($khoaCho, $danhSachCho, now()->addHours(24));
        }

        Log::info('[AutoDispatchJob] Đưa vào hàng chờ đội trống', [
            'id_yeu_cau' => $this->idYeuCau,
            'id_loai_su_co' => $idLoaiSuCo,
            'so_luong_cho' => count($danhSachCho),
        ]);
    }

    /**
     * Dispatch lại tất cả yêu cầu đang chờ đội trống cho các loại sự cố mà đội vừa có slot.
     * Được gọi từ listener CoDoiTrongTroLai.
     *
     * @param array $cacLoaiSuCoId Danh sách id_loai_su_co của đội vừa có slot
     */
    public static function dispatchLaiYeuCauChoDoi(array $cacLoaiSuCoId): void
    {
        foreach ($cacLoaiSuCoId as $idLoaiSuCo) {
            $khoaCho = "cho_doi_trong_loai_{$idLoaiSuCo}";
            $danhSachCho = Cache::get($khoaCho, []);

            if (empty($danhSachCho)) {
                continue;
            }

            Log::info('[AutoDispatchJob] Dispatch lại yêu cầu chờ đội trống', [
                'id_loai_su_co' => $idLoaiSuCo,
                'so_luong' => count($danhSachCho),
                'danh_sach' => $danhSachCho,
            ]);

            // Xóa hàng chờ trước khi dispatch để tránh dispatch trùng
            Cache::forget($khoaCho);

            foreach ($danhSachCho as $idYeuCau) {
                // Kiểm tra yêu cầu còn hợp lệ không trước khi dispatch
                $yeuCau = YeuCauCuuHo::find($idYeuCau);
                if (!$yeuCau) continue;

                $trangThai = strtoupper(trim((string) ($yeuCau->trang_thai ?? '')));
                $trangThaiChoPhep = ['CHO_XU_LY', 'MOI', 'WAITING', 'CHO_PHAN_CONG'];
                if (!in_array($trangThai, $trangThaiChoPhep, true)) continue;

                if (PhanCongCuuHo::where('id_yeu_cau', $idYeuCau)->exists()) continue;

                self::dispatch($idYeuCau)->onQueue('auto-dispatch');

                Log::info('[AutoDispatchJob] Đã dispatch lại yêu cầu chờ', [
                    'id_yeu_cau' => $idYeuCau,
                    'id_loai_su_co' => $idLoaiSuCo,
                ]);
            }
        }
    }

    /**
     * Gửi thông báo đến admin khi cần can thiệp thủ công.
     *
     * @param YeuCauCuuHo $yeuCau
     */
    private function thongBaoAdminCanThiep(YeuCauCuuHo $yeuCau): void
    {
        // Đánh dấu yêu cầu là cần can thiệp thủ công
        // Có thể sử dụng notification, email, hoặc broadcast event riêng
        Cache::put("admin_escalation_{$this->idYeuCau}", [
            'id_yeu_cau' => $this->idYeuCau,
            'thoi_gian' => now()->toISOString(),
            'ly_do' => 'Vượt quá số lần retry điều phối tự động',
            'trang_thai' => $yeuCau->trang_thai,
            'vi_tri' => $yeuCau->vi_tri_dia_chi,
        ], now()->addHours(24));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('[AutoDispatchJob] Job thất bại nghiêm trọng', [
            'id_yeu_cau' => $this->idYeuCau,
            'so_lan_retry' => $this->soLanRetry,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Vẫn thông báo admin can thiệp
        $yeuCau = YeuCauCuuHo::find($this->idYeuCau);
        if ($yeuCau) {
            $this->thongBaoAdminCanThiep($yeuCau);
        }
    }
}
