<?php

namespace App\Jobs;

use App\Events\RescueRequestUpdated;
use App\Models\YeuCauCuuHo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DetectVictimsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $idYeuCau;
    public int $tries = 1;
    public int $timeout = 15;

    public function __construct(int $idYeuCau)
    {
        $this->idYeuCau = $idYeuCau;
        $this->afterCommit = true;
    }

    public function handle(): void
    {
        $yeuCau = YeuCauCuuHo::find($this->idYeuCau);
        if (!$yeuCau || !$yeuCau->hinh_anh) {
            return;
        }

        $imagePath = public_path($yeuCau->hinh_anh);
        if (!is_file($imagePath)) {
            Log::warning('[DetectVictimsJob] Khong tim thay anh', [
                'id_yeu_cau' => $this->idYeuCau,
                'path' => $imagePath,
            ]);
            return;
        }

        $aiUrl = config('services.ai_service.url');
        if (!$aiUrl) {
            Log::warning('[DetectVictimsJob] AI_SERVICE_URL chua cau hinh');
            return;
        }

        try {
            $response = Http::timeout(10)
                ->attach('image', file_get_contents($imagePath), basename($imagePath))
                ->post(rtrim($aiUrl, '/') . '/detect');

            if (!$response->successful()) {
                Log::warning('[DetectVictimsJob] AI service tra ve loi', [
                    'id_yeu_cau' => $this->idYeuCau,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return;
            }

            $count = $response->json('count');
            if (!is_numeric($count) || $count < 0) {
                Log::warning('[DetectVictimsJob] AI tra ve count khong hop le', [
                    'id_yeu_cau' => $this->idYeuCau,
                    'count' => $count,
                ]);
                return;
            }

            $yeuCau->so_nguoi_bi_anh_huong = (int) $count;
            $yeuCau->save();

            Log::info('[DetectVictimsJob] Cap nhat so nguoi bi anh huong', [
                'id_yeu_cau' => $this->idYeuCau,
                'count' => (int) $count,
            ]);

            // Broadcast de FE (admin + rescuer) nhan realtime so nguoi YOLO vua dem duoc.
            try {
                $yeuCau->load(['phanCongs.doiCuuHo', 'phanCongs.thanhVienTiepNhan', 'phanCongs.ketQua', 'loaiSuCo.chiTiets', 'nguoiDung']);
                event(new RescueRequestUpdated($yeuCau, 'victims_detected'));
            } catch (\Throwable $ex) {
                Log::warning('[DetectVictimsJob] Broadcast that bai', [
                    'id_yeu_cau' => $this->idYeuCau,
                    'error' => $ex->getMessage(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('[DetectVictimsJob] Loi khi goi AI service', [
                'id_yeu_cau' => $this->idYeuCau,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
