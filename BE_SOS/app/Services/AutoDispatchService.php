<?php

namespace App\Services;

use App\Jobs\AutoDispatchJob;
use App\Models\DoiCuuHo;
use App\Models\PhanCongCuuHo;
use App\Models\YeuCauCuuHo;
use App\Services\DistanceService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AutoDispatchService - Service xu ly dieu phoi tu dong doi cuu ho den yeu cau cuu ho.
 *
 * Quy trinh:
 * 1. Tinh diem nguy hiem tu chiTiet (max diem_uu_tien)
 * 2. Tinh diem khoang cach (dua tren km tu Google Distance Matrix)
 * 3. Tinh diem tai cua doi (capacity = so_thanh_vien * 4)
 * 4. Tinh diem thoi gian cho (anti-starvation)
 * 5. Tong hop diem, chon doi tot nhat
 * 6. Su dung DB transaction + lock de tran double assignment
 */
class AutoDispatchService
{
    private DistanceService $distanceService;

    /** Gioi han so doi gan nhat can danh gia */
    private const SO_DOI_TOI_DA = 5;

    /** Thoi gian cache vi tri doi (giay) */
    private const CACHE_GIOI_HAN_GIAY = 10;

    /** Thoi gian debounce dispatch lock (giay) */
    private const DISPATCH_LOCK_GIOAY = 5;

    /** So lan retry toi da */
    private const SO_LAN_RETRY_TOI_DA = 3;

    public function __construct(DistanceService $distanceService)
    {
        $this->distanceService = $distanceService;
    }

    /**
     * Xu ly dieu phoi tu dong cho mot yeu cau cuu ho.
     *
     * @param int $idYeuCau ID cua yeu cau cuu ho
     * @return array Ket qua dieu phoi: ['thanh_cong' => bool, 'doi_id' => int|null, 'thong_diep' => string]
     */
    public function xuLyDieuPhoiTuDong(int $idYeuCau): array
    {
        Log::info('[AutoDispatch] Bat dau dieu phoi tu dong', ['id_yeu_cau' => $idYeuCau]);

        $dispatchLockKey = "dispatch_lock_{$idYeuCau}";
        if (Cache::has($dispatchLockKey)) {
            Log::info('[AutoDispatch] Bo qua: dang co dispatch dang chay', ['id_yeu_cau' => $idYeuCau]);
            return [
                'thanh_cong' => false,
                'doi_id' => null,
                'thong_diep' => 'Dang co dispatch dang chay cho yeu cau nay',
            ];
        }
        Cache::put($dispatchLockKey, true, self::DISPATCH_LOCK_GIOAY);

        try {
            $yeuCau = YeuCauCuuHo::with([
                'chiTiet',
                'phanCongs',
            ])->find($idYeuCau);

            if (!$yeuCau) {
                Log::warning('[AutoDispatch] Yeu cau khong ton tai', ['id_yeu_cau' => $idYeuCau]);
                return [
                    'thanh_cong' => false,
                    'doi_id' => null,
                    'thong_diep' => 'Yeu cau khong ton tai',
                ];
            }

            if ($yeuCau->phanCongs()->exists()) {
                Log::info('[AutoDispatch] Yeu cau da co phan cong', ['id_yeu_cau' => $idYeuCau]);
                return [
                    'thanh_cong' => false,
                    'doi_id' => null,
                    'thong_diep' => 'Yeu cau da co phan cong truoc do',
                ];
            }

            $trangThai = strtoupper(trim((string) ($yeuCau->trang_thai ?? '')));
            if (!in_array($trangThai, ['CHO_XU_LY', 'MOI', 'WAITING', 'CHO_PHAN_CONG'], true)) {
                Log::info('[AutoDispatch] Yeu cau khong o trang thai cho xu ly', [
                    'id_yeu_cau' => $idYeuCau,
                    'trang_thai' => $trangThai,
                ]);
                return [
                    'thanh_cong' => false,
                    'doi_id' => null,
                    'thong_diep' => "Yeu cau dang o trang thai {$trangThai}, khong the dieu phoi tu dong",
                ];
            }

            $danhSachDoi = $this->layDanhSachDoiGanNhatInternal($yeuCau);

            if ($danhSachDoi->isEmpty()) {
                Log::warning('[AutoDispatch] Khong tim thay doi cuu ho nao', ['id_yeu_cau' => $idYeuCau]);
                return [
                    'thanh_cong' => false,
                    'doi_id' => null,
                    'thong_diep' => 'Khong tim thay doi cuu ho nao trong khu vuc',
                ];
            }

            // Tinh diem chi phu thuoc yeu cau (khong phu thuoc doi) - chi tinh 1 lan
            $diemNguyHiem = $this->tinhDiemNguyHiemInternal($yeuCau);
            $diemThoiGian = $this->tinhDiemThoiGianInternal($yeuCau);

            Log::debug('[AutoDispatch] Diem phu thuoc yeu cau', [
                'id_yeu_cau' => $idYeuCau,
                'diem_nguy_hiem' => $diemNguyHiem,
                'diem_thoi_gian' => $diemThoiGian,
            ]);

            $doiTotNhat = null;
            $diemTotNhat = PHP_INT_MIN;

            foreach ($danhSachDoi as $doi) {
                $diemKhoangCach = $this->tinhDiemKhoangCachInternal($doi);
                $diemTai = $this->tinhDiemTaiInternal($doi);
                $diemLoaiSuCo = $this->tinhDiemLoaiSuCoInternal($yeuCau, $doi);

                Log::debug('[AutoDispatch] Tinh diem doi', [
                    'doi_id' => $doi->id_doi_cuu_ho,
                    'ten_doi' => $doi->ten_doi,
                    'diem_nguy_hiem' => $diemNguyHiem,
                    'diem_khoang_cach' => $diemKhoangCach,
                    'diem_tai' => $diemTai,
                    'diem_loai_su_co' => $diemLoaiSuCo,
                    'diem_thoi_gian' => $diemThoiGian,
                    'khoang_cach_km' => $doi->distance ?? null,
                    'loai_su_co_cua_doi' => $doi->loaiSuCos->map(fn($l) => $l->id_loai_su_co ?? $l->id)->toArray(),
                    'id_loai_su_co_yeu_cau' => $yeuCau->id_loai_su_co,
                ]);

                if ($diemTai === -100) {
                    Log::debug('[AutoDispatch] Bo qua doi qua tai', [
                        'doi_id' => $doi->id_doi_cuu_ho,
                        'ten_doi' => $doi->ten_doi,
                    ]);
                    continue;
                }

                $diemTong = $diemNguyHiem + $diemKhoangCach + $diemTai + $diemThoiGian + $diemLoaiSuCo;

                if ($diemTong > $diemTotNhat) {
                    $diemTotNhat = $diemTong;
                    $doiTotNhat = $doi;
                }
            }

            if (!$doiTotNhat) {
                Log::warning('[AutoDispatch] Khong co doi phu hop nao', ['id_yeu_cau' => $idYeuCau]);
                return [
                    'thanh_cong' => false,
                    'doi_id' => null,
                    'thong_diep' => 'Khong co doi cuu ho phu hop trong khu vuc',
                ];
            }

            return $this->ganDoiChoYeuCau($yeuCau, $doiTotNhat, $diemTotNhat);
        } finally {
            Cache::forget($dispatchLockKey);
        }
    }

    /**
     * Lay danh sach doi gan nhat (internal).
     */
    private function layDanhSachDoiGanNhatInternal(YeuCauCuuHo $yeuCau)
    {
        $cacheKey = "nearest_teams_{$yeuCau->id_yeu_cau}";

        return Cache::remember($cacheKey, self::CACHE_GIOI_HAN_GIAY, function () use ($yeuCau) {
            $reqLat = $yeuCau->vi_tri_lat ? floatval($yeuCau->vi_tri_lat) : null;
            $reqLng = $yeuCau->vi_tri_lng ? floatval($yeuCau->vi_tri_lng) : null;

            if ($reqLat === null || $reqLng === null) {
                return collect();
            }

            $tatCaDoi = DoiCuuHo::with([
                'thanhViens',
                'phanCongs',
                'loaiSuCos',
            ])->get();

            if ($tatCaDoi->isEmpty()) {
                return collect();
            }

            $viTriDoi = [];
            foreach ($tatCaDoi as $doi) {
                if ($doi->vi_tri_lat !== null && $doi->vi_tri_lng !== null) {
                    $viTriDoi[] = [
                        'key' => 'hq_' . $doi->id_doi_cuu_ho,
                        'lat' => floatval($doi->vi_tri_lat),
                        'lng' => floatval($doi->vi_tri_lng),
                    ];
                }
            }

            $ketQuaKhoangCach = $this->distanceService->calculateDistances($reqLat, $reqLng, $viTriDoi);

            foreach ($tatCaDoi as $doi) {
                $hqKey = 'hq_' . $doi->id_doi_cuu_ho;
                $doi->distance = $ketQuaKhoangCach[$hqKey] ?? null;

                $activeStatuses = ['DANG_XU_LY', 'DA_PHAN_CONG', 'DA_DEN_HIEN_TRUONG'];
                $soNhiemVu = $doi->phanCongs
                    ? $doi->phanCongs->filter(fn($pc) => in_array(strtoupper(trim($pc->trang_thai_nhiem_vu ?? '')), $activeStatuses, true))->count()
                    : 0;
                $doi->so_nhiem_vu_dang_xu_ly = $soNhiemVu;
            }

            $danhSach = $tatCaDoi->sort(function ($a, $b) {
                $distA = $a->distance;
                $distB = $b->distance;

                $coDistA = is_numeric($distA);
                $coDistB = is_numeric($distB);

                if ($coDistA && !$coDistB) return -1;
                if (!$coDistA && $coDistB) return 1;

                if ($coDistA && $coDistB) {
                    $diff = abs($distA - $distB);
                    if ($diff > 0.01) {
                        return $distA <=> $distB;
                    }
                }

                return 0;
            });

            return $danhSach->values()->take(self::SO_DOI_TOI_DA);
        });
    }

    /**
     * Tinh diem nguy hiem tu chi tiet loai su co.
     */
    private function tinhDiemNguyHiemInternal(YeuCauCuuHo $yeuCau): float
    {
        if ($yeuCau->chiTiet && $yeuCau->chiTiet->isNotEmpty()) {
            $maxDiem = $yeuCau->chiTiet->max('diem_uu_tien');
            return is_numeric($maxDiem) ? (float) $maxDiem : 0.0;
        }

        if ($yeuCau->id_loai_su_co) {
            $maxDiem = DB::table('chi_tiet_loai_su_co')
                ->where('id_loai_su_co', $yeuCau->id_loai_su_co)
                ->max('diem_uu_tien');
            return is_numeric($maxDiem) ? (float) $maxDiem : 0.0;
        }

        return 0.0;
    }

    /**
     * Tinh diem khoang cach dua tren km tu Google Distance Matrix.
     * Tang diem de uu tien khoang cach hon.
     */
    private function tinhDiemKhoangCachInternal(DoiCuuHo $doi): int
    {
        $km = $doi->distance ?? 0;

        if ($km <= 1) return 10;   // Rất gần
        if ($km <= 3) return 7;    // Gần
        if ($km <= 5) return 4;    // Trung bình
        return 1;                   // Xa nhưng vẫn có điểm
    }

    /**
     * Tinh diem khop loai su co giua yeu cau va doi cuu ho.
     * Tra ve 0 neu khong khop, 6 neu khop.
     * Uu tien doi cung loai su co nhung van de khoang cach quan trong.
     */
    private function tinhDiemLoaiSuCoInternal(YeuCauCuuHo $yeuCau, DoiCuuHo $doi): int
    {
        $idLoaiSuCoYeuCau = $yeuCau->id_loai_su_co;
        if (!$idLoaiSuCoYeuCau) {
            return 0;
        }

        $loaiSuCos = $doi->loaiSuCos ?? collect();
        $loaiSuCoIds = $loaiSuCos
            ->map(fn($lsc) => $lsc->id_loai_su_co ?? $lsc->id ?? null)
            ->filter()
            ->map(fn($id) => (int) $id)
            ->toArray();

        if (empty($loaiSuCoIds)) {
            return 0;
        }

        return in_array((int) $idLoaiSuCoYeuCau, $loaiSuCoIds, true) ? 6 : 0;
    }

    /**
     * Tinh diem tai (capacity) cua doi.
     */
    private function tinhDiemTaiInternal(DoiCuuHo $doi): int
    {
        $soThanhVien = $doi->thanhViens ? $doi->thanhViens->count() : 0;
        $sucChua = $soThanhVien * 4;
        $tai = $doi->so_nhiem_vu_dang_xu_ly ?? 0;

        if ($tai >= $sucChua && $sucChua > 0) {
            return -100;
        }

        if ($sucChua === 0) {
            return -100;
        }

        $tyLe = $tai / $sucChua;

        if ($tyLe <= 0.25) return 2;
        if ($tyLe <= 0.5) return 1;
        return 0;
    }

    /**
     * Tinh diem thoi gian (anti-starvation).
     */
    private function tinhDiemThoiGianInternal(YeuCauCuuHo $yeuCau): float
    {
        $phut = now()->diffInMinutes($yeuCau->created_at);
        return min($phut * 0.2, 3);
    }

    /**
     * Tinh tong diem.
     */
    private function tinhDiemTong(YeuCauCuuHo $yeuCau, DoiCuuHo $doi): float
    {
        return $this->tinhDiemNguyHiemInternal($yeuCau)
            + $this->tinhDiemKhoangCachInternal($doi)
            + $this->tinhDiemTaiInternal($doi)
            + $this->tinhDiemThoiGianInternal($yeuCau)
            + $this->tinhDiemLoaiSuCoInternal($yeuCau, $doi);
    }

    /**
     * Gan doi cho yeu cau.
     */
    private function ganDoiChoYeuCau(YeuCauCuuHo $yeuCau, DoiCuuHo $doi, float $diemTong): array
    {
        try {
            return DB::transaction(function () use ($yeuCau, $doi, $diemTong) {
                $yeuCau->refresh();
                $doi->refresh('phanCongs');

                if ($yeuCau->phanCongs()->exists()) {
                    Log::info('[AutoDispatch] Yeu cau da duoc gan boi tien trinh khac', [
                        'id_yeu_cau' => $yeuCau->id_yeu_cau,
                    ]);
                    return [
                        'thanh_cong' => false,
                        'doi_id' => null,
                        'thong_diep' => 'Yeu cau da duoc gan boi tien trinh khac',
                    ];
                }

                $phanCong = PhanCongCuuHo::create([
                    'id_yeu_cau' => $yeuCau->id_yeu_cau,
                    'id_doi_cuu_ho' => $doi->id_doi_cuu_ho,
                    'trang_thai_nhiem_vu' => 'MOI',
                    'mo_ta' => "Dieu phoi tu dong - Diem tong: " . round($diemTong, 2),
                ]);

                $yeuCau->update(['trang_thai' => 'DA_PHAN_CONG']);
                $doi->update(['trang_thai' => 'BAN_CHI_DINH']);

                // Cap nhat HangDoiXuLy: chuyen WAITING -> PROCESSING khi da phan cong
                if ($yeuCau->hangDoiXuLy) {
                    $yeuCau->hangDoiXuLy->update([
                        'trang_thai' => 'PROCESSING',
                        'thoi_gian_phan_cong' => now(),
                    ]);
                }

                // Broadcast su kien de FE cap nhat real-time
                try {
                    $yeuCau->load(['phanCongs.doiCuuHo', 'phanCongs.thanhVienTiepNhan', 'phanCongs.ketQua', 'loaiSuCo']);
                    event(new \App\Events\RescueRequestUpdated($yeuCau, 'auto_dispatched'));
                } catch (\Throwable $ex) {
                    Log::warning('[AutoDispatch] Broadcast that bai', [
                        'id_yeu_cau' => $yeuCau->id_yeu_cau,
                        'error' => $ex->getMessage(),
                    ]);
                }

                Log::info('[AutoDispatch] Gan doi thanh cong', [
                    'id_yeu_cau' => $yeuCau->id_yeu_cau,
                    'id_doi_cuu_ho' => $doi->id_doi_cuu_ho,
                    'ten_doi' => $doi->ten_doi,
                    'diem_tong' => round($diemTong, 2),
                ]);

                Cache::forget("nearest_teams_{$yeuCau->id_yeu_cau}");

                return [
                    'thanh_cong' => true,
                    'doi_id' => $doi->id_doi_cuu_ho,
                    'thong_diep' => "Da gan doi {$doi->ten_doi} cho yeu cau #{$yeuCau->id_yeu_cau}",
                    'phan_cong_id' => $phanCong->id_phan_cong,
                    'diem_tong' => round($diemTong, 2),
                ];
            });
        } catch (\Throwable $e) {
            Log::error('[AutoDispatch] Loi khi gan doi', [
                'id_yeu_cau' => $yeuCau->id_yeu_cau,
                'doi_id' => $doi->id_doi_cuu_ho,
                'error' => $e->getMessage(),
            ]);

            return [
                'thanh_cong' => false,
                'doi_id' => null,
                'thong_diep' => 'Loi khi gan doi: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Retry dieu phoi cho yeu cau.
     */
    public function thuLaiDieuPhoi(int $idYeuCau, int $soLanRetry = 1): array
    {
        if ($soLanRetry > self::SO_LAN_RETRY_TOI_DA) {
            Log::warning('[AutoDispatch] Da vuot qua so lan retry toi da', [
                'id_yeu_cau' => $idYeuCau,
                'so_lan_retry' => $soLanRetry,
            ]);
            return [
                'thanh_cong' => false,
                'doi_id' => null,
                'thong_diep' => 'Da vuot qua so lan thu lai toi da. Can can thiep thu cong tu admin.',
                'can_thiep_admin' => true,
            ];
        }

        Log::info('[AutoDispatch] Thu lai dieu phoi lan', [
            'id_yeu_cau' => $idYeuCau,
            'so_lan_retry' => $soLanRetry,
        ]);

        $ketQua = $this->xuLyDieuPhoiTuDong($idYeuCau);
        $ketQua['so_lan_retry'] = $soLanRetry;

        return $ketQua;
    }

    /**
     * Lay trang thai dieu phoi tu dong.
     */
    public static function layTrangThai(): bool
    {
        return Cache::get('auto_dispatch_enabled', true);
    }

    /**
     * Bat dieu phoi tu dong.
     * Dong thoi dispatch job cho tat ca yeu cau dang cho xu ly (chua phan cong).
     */
    public static function batDieuPhoi(): void
    {
        Cache::put('auto_dispatch_enabled', true, now()->addYears(10));

        $trangThaiYeuCau = ['CHO_XU_LY', 'MOI', 'WAITING', 'CHO_PHAN_CONG', 'DA_PHAN_CONG'];
        $danhSachYeuCau = YeuCauCuuHo::whereIn('trang_thai', $trangThaiYeuCau)
            ->whereDoesntHave('phanCongs')
            ->whereNotNull('vi_tri_lat')
            ->whereNotNull('vi_tri_lng')
            ->get();

        foreach ($danhSachYeuCau as $yeuCau) {
            Log::info('[AutoDispatch] Dispatch yeu cau co san khi bat auto dispatch', [
                'id_yeu_cau' => $yeuCau->id_yeu_cau,
            ]);
            AutoDispatchJob::dispatch($yeuCau->id_yeu_cau)->onQueue('auto-dispatch');
        }
    }

    /**
     * Tat dieu phoi tu dong.
     */
    public static function tatDieuPhoi(): void
    {
        Cache::put('auto_dispatch_enabled', false, now()->addYears(10));
    }

    /**
     * Toggle trang thai dieu phoi tu dong.
     */
    public static function toggleTrangThai(): bool
    {
        $hienTai = self::layTrangThai();
        if ($hienTai) {
            self::tatDieuPhoi();
        } else {
            self::batDieuPhoi();
        }
        return !$hienTai;
    }

    /**
     * Kiem tra dieu phoi tu dong da bat chua.
     */
    public static function daBat(): bool
    {
        return self::layTrangThai();
    }

    // === Public Accessor Methods (for Controller Debug) ===

    /**
     * Lay danh sach doi gan nhat (public accessor).
     */
    public function layDanhSachDoiGanNhat(YeuCauCuuHo $yeuCau)
    {
        return $this->layDanhSachDoiGanNhatInternal($yeuCau);
    }

    /**
     * Tinh diem nguy hiem (public accessor).
     */
    public function tinhDiemNguyHiem(YeuCauCuuHo $yeuCau): float
    {
        return $this->tinhDiemNguyHiemInternal($yeuCau);
    }

    /**
     * Tinh diem khoang cach (public accessor).
     */
    public function tinhDiemKhoangCach(DoiCuuHo $doi): int
    {
        return $this->tinhDiemKhoangCachInternal($doi);
    }

    /**
     * Tinh diem tai (public accessor).
     */
    public function tinhDiemTai(DoiCuuHo $doi): int
    {
        return $this->tinhDiemTaiInternal($doi);
    }

    /**
     * Tinh diem thoi gian (public accessor).
     */
    public function tinhDiemThoiGian(YeuCauCuuHo $yeuCau): float
    {
        return $this->tinhDiemThoiGianInternal($yeuCau);
    }

    /**
     * Tinh diem loai su co (public accessor).
     */
    public function tinhDiemLoaiSuCo(YeuCauCuuHo $yeuCau, DoiCuuHo $doi): int
    {
        return $this->tinhDiemLoaiSuCoInternal($yeuCau, $doi);
    }
}
