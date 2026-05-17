<?php

namespace App\Services;

use App\Jobs\AutoDispatchJob;
use App\Models\DoiCuuHo;
use App\Models\PhanCongCuuHo;
use App\Models\YeuCauCuuHo;
use App\Services\DistanceService;
use App\Events\SucChuaDoiDaCapNhat;
use App\Events\CoDoiTrongTroLai;
use App\Events\AssignmentDaHoanThanh;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AutoDispatchService - Service xu ly dieu phoi tu dong doi cuu ho den yeu cau cuu ho.
 *
 * NGUYEN TAC HOAT DONG (TUYET DOI):
 * 1. Capacity = thanhVien * 1 (dong nhat voi frontend)
 * 2. Nhiem vu tieu ton capacity: MOI, DANG_XU_LY, DA_PHAN_CONG, DA_DEN_HIEN_TRUONG
 * 3. MOI (da phan cong, chua tiep nhan) -> CO tieu ton capacity (vi da duoc gan cho doi)
 * 4. HOAN_THANH, THAT_BAI, HUY_BO -> KHONG tieu ton capacity
 * 5. Capacity tinh tu DATABASE thoi gian thuc, KHONG dung cache
 * 6. Kiem tra capacity ATOMIC TRUOC KHI assign
 * 7. Uu tien team gan nhat co capacity (distance first, capacity second)
 * 8. KHONG phu thuoc vao realtime render/UI/Reverb
 */
class AutoDispatchService
{
    private DistanceService $distanceService;

    private const SO_DOI_TOI_DA = 20;
    private const DISPATCH_LOCK_GIOAY = 5;
    private const SO_LAN_RETRY_TOI_DA = 3;

    /**
     * Cac trang thai nhiem vu duoc tinh la ACTIVE (dang xu ly, tieu ton capacity).
     * CHO_XU_LY: cho xu ly -> CO tieu ton capacity (vi da trong hang doi)
     * DA_DIEU_PHOI: da dieu phoi -> CO tieu ton capacity (vi da gan cho doi)
     * DANG_DI_CHUYEN: dang di chuyen -> CO tieu ton capacity
     * DANG_XU_LY: dang xu ly -> CO tieu ton capacity
     * MOI: moi (da phan cong, chua tiep nhan) -> CO tieu ton capacity (vi da duoc gan cho doi)
     * HOAN_THANH, DA_HUY, THAT_BAI -> KHONG tieu ton capacity
     */
    private const ACTIVE_STATUSES = ['CHO_XU_LY', 'DA_DIEU_PHOI', 'DANG_DI_CHUYEN', 'DANG_XU_LY', 'MOI'];

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

            // Lay danh sach doi gan nhat + tinh capacity theo thoi gian thuc
            $danhSachDoi = $this->layDanhSachDoiGanNhatInternal($yeuCau);

            if ($danhSachDoi->isEmpty()) {
                Log::warning('[AutoDispatch] Khong tim thay doi cuu ho nao', ['id_yeu_cau' => $idYeuCau]);
                return [
                    'thanh_cong' => false,
                    'doi_id' => null,
                    'thong_diep' => 'Khong tim thay doi cuu ho nao trong khu vuc',
                ];
            }

            // Tinh diem chi phu thuoc yeu cau (chi tinh 1 lan)
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
                // KHONG can kiem tra capacity nua vi da duoc loc o layDanhSachDoiGanNhatInternal
                // Tranh race condition giua 2 truy van DB tai thoi diem khac nhau

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
                    'capacity' => $doi->capacity,
                    'active_count' => $doi->active_count_real,
                ]);

                if ($diemTai === -100) {
                    Log::debug('[AutoDispatch] Bo qua doi qua tai (scoring)', [
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
                Log::warning('[AutoDispatch] Khong co doi phu hop nao (tat ca deu qua tai)', ['id_yeu_cau' => $idYeuCau]);
                return [
                    'thanh_cong' => false,
                    'doi_id' => null,
                    'thong_diep' => 'Khong co doi cuu ho phu hop trong khu vuc (tat ca deu qua tai)',
                ];
            }

            return $this->ganDoiChoYeuCau($yeuCau, $doiTotNhat, $diemTotNhat);
        } finally {
            Cache::forget($dispatchLockKey);
        }
    }


    /**
     * Lay danh sach doi gan nhat (internal).
     * BO CACHE - luon tinh capacity tu DB thoi gian thuc.
     * Chi lay top 5 doi gan nhat, da loai bo team qua tai.
     */
    private function layDanhSachDoiGanNhatInternal(YeuCauCuuHo $yeuCau)
    {
        $reqLat = $yeuCau->vi_tri_lat ? floatval($yeuCau->vi_tri_lat) : null;
        $reqLng = $yeuCau->vi_tri_lng ? floatval($yeuCau->vi_tri_lng) : null;

        if ($reqLat === null || $reqLng === null) {
            Log::warning('[AutoDispatch] Yeu cau khong co toa do', ['id_yeu_cau' => $yeuCau->id_yeu_cau]);
            return collect();
        }

        // Lay ALL doi cuu ho (khong co cache)
        $tatCaDoi = DoiCuuHo::with([
            'thanhViens',
            'phanCongs',
            'loaiSuCos',
        ])->get();

        if ($tatCaDoi->isEmpty()) {
            return collect();
        }

        // Tinh khoang cach toi tat ca doi
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

            // Tinh capacity REAL-TIME tu DB
            $soThanhVien = $doi->thanhViens ? $doi->thanhViens->count() : 0;
            $doi->so_thanh_vien = $soThanhVien;
            $doi->capacity = $soThanhVien * 1;

            // Tinh active count tu DB truc tiep - DUNG TIEN CHUAN MOI
            $activeCount = PhanCongCuuHo::where('id_doi_cuu_ho', $doi->id_doi_cuu_ho)
                ->whereIn('trang_thai_nhiem_vu', self::ACTIVE_STATUSES)
                ->count();
            $doi->active_count_real = $activeCount;

            // Danh gia: MOI khong duoc tinh vao active nua (da chuyen sang CHO_XU_LY)
            // MOI chi la trang thai cu, khong con su dung
            $doi->pending_count_real = 0;
        }

        // Loc bo team qua tai TRUOC KHI sort - DUNG logic duy nhat
        $danhSach = $tatCaDoi->filter(function ($doi) use ($yeuCau) {
            $capacity = $doi->capacity ?? 0;
            if ($capacity <= 0) {
                Log::debug('[AutoDispatch] Loai bo doi khong co thanh vien', [
                    'doi_id' => $doi->id_doi_cuu_ho,
                    'ten_doi' => $doi->ten_doi,
                    'so_thanh_vien' => $doi->so_thanh_vien ?? 0,
                ]);
                return false;
            }

            // === FIX: Loai bo doi khong phu hop loai su co ===
            $idLoaiSuCoYeuCau = $yeuCau->id_loai_su_co;
            if ($idLoaiSuCoYeuCau) {
                $loaiSuCos = $doi->loaiSuCos ?? collect();
                $loaiSuCoIds = $loaiSuCos
                    ->map(fn($lsc) => $lsc->id_loai_su_co ?? $lsc->id ?? null)
                    ->filter()
                    ->map(fn($id) => (int) $id)
                    ->toArray();

                if (!in_array((int) $idLoaiSuCoYeuCau, $loaiSuCoIds, true)) {
                    Log::debug('[AutoDispatch] Loai bo doi khong khop loai su co', [
                        'doi_id' => $doi->id_doi_cuu_ho,
                        'ten_doi' => $doi->ten_doi,
                        'loai_su_co_doi' => $loaiSuCoIds,
                        'loai_su_co_yeu_cau' => $idLoaiSuCoYeuCau,
                    ]);
                    return false;
                }
            }
            // ==================================================

            $active = $doi->active_count_real ?? 0;
            $conSlot = $capacity - $active;

            if ($active >= $capacity) {
                Log::debug('[AutoDispatch] Loai bo doi qua tai', [
                    'doi_id' => $doi->id_doi_cuu_ho,
                    'ten_doi' => $doi->ten_doi,
                    'so_thanh_vien' => $doi->so_thanh_vien ?? 0,
                    'capacity' => $capacity,
                    'active_count' => $active,
                    'con_slot' => $conSlot,
                ]);
                return false;
            }

            Log::debug('[AutoDispatch] Giu lai doi con slot', [
                'doi_id' => $doi->id_doi_cuu_ho,
                'ten_doi' => $doi->ten_doi,
                'so_thanh_vien' => $doi->so_thanh_vien ?? 0,
                'capacity' => $capacity,
                'active_count' => $active,
                'con_slot' => $conSlot,
            ]);

            return true;
        });

        // Sort theo khoang cach (uu tien gan nhat)
        $sorted = $danhSach->sort(function ($a, $b) {
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

        return $sorted->values()->take(self::SO_DOI_TOI_DA);
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

        if ($km <= 1) return 10;
        if ($km <= 3) return 7;
        if ($km <= 5) return 4;
        return 1;
    }

    /**
     * Tinh diem khop loai su co giua yeu cau va doi cuu ho.
     * Da duoc loc nghiem ngat o layDanhSachDoiGanNhatInternal,
     * nhung van giu diem so de tuong thich.
     */
    private function tinhDiemLoaiSuCoInternal(YeuCauCuuHo $yeuCau, DoiCuuHo $doi): int
    {
        $idLoaiSuCoYeuCau = $yeuCau->id_loai_su_co;
        if (!$idLoaiSuCoYeuCau) {
            return 0;
        }

        // Teams remaining here are guaranteed to match the incident type 
        // due to the filter in layDanhSachDoiGanNhatInternal.
        return 20;
    }

    /**
     * Tinh diem tai (capacity) cua doi.
     * Su dung REAL-TIME data: active_count_real (tinh tu DB truc tiep trong layDanhSachDoiGanNhatInternal).
     *
     * Capacity = soThanhVien * 1 (dong nhat voi frontend Assignments/index.vue)
     * Tinh tat ca trang thai dang xu ly: MOI, DANG_XU_LY, DA_PHAN_CONG, DA_DEN_HIEN_TRUONG
     * MOI = da phan cong, CO tieu ton capacity (vi da duoc gan cho doi)
     */
    private function tinhDiemTaiInternal(DoiCuuHo $doi): int
    {
        // Su dung gia tri da duoc tinh san trong layDanhSachDoiGanNhatInternal
        // Neu khong co, tinh real-time
        $soThanhVien = $doi->so_thanh_vien ?? ($doi->thanhViens ? $doi->thanhViens->count() : 0);
        $capacity = $doi->capacity ?? ($soThanhVien * 1);

        if (isset($doi->active_count_real)) {
            $tai = $doi->active_count_real;
        } else {
            // Tinh real-time khi khong co gia tri duoc tinh san
            $tai = PhanCongCuuHo::where('id_doi_cuu_ho', $doi->id_doi_cuu_ho)
                ->whereIn('trang_thai_nhiem_vu', self::ACTIVE_STATUSES)
                ->count();
        }

        if ($capacity === 0) {
            Log::debug('[AutoDispatch] Doi khong co thanh vien', [
                'doi_id' => $doi->id_doi_cuu_ho,
                'ten_doi' => $doi->ten_doi,
                'so_thanh_vien' => $soThanhVien,
            ]);
            return -100;
        }

        // Double-check - khong nen xay ra vi da duoc loc o layDanhSachDoiGanNhatInternal
        if ($tai >= $capacity) {
            Log::warning('[AutoDispatch] tinhDiemTaiInternal: Phat hien doi qua tai (sau khi loc)', [
                'doi_id' => $doi->id_doi_cuu_ho,
                'ten_doi' => $doi->ten_doi,
                'capacity' => $capacity,
                'active_count' => $tai,
            ]);
            return -100;
        }

        $tyLe = $tai / $capacity;
        $diem = 0;

        if ($tyLe <= 0.25) {
            $diem = 2;
        } elseif ($tyLe <= 0.5) {
            $diem = 1;
        }

        Log::debug('[AutoDispatch] Tinh diem tai', [
            'doi_id' => $doi->id_doi_cuu_ho,
            'ten_doi' => $doi->ten_doi,
            'so_thanh_vien' => $soThanhVien,
            'capacity' => $capacity,
            'active_count' => $tai,
            'ty_le' => round($tyLe, 2),
            'diem_tai' => $diem,
        ]);

        return $diem;
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
     * Gan doi cho yeu cau.
     * Su dung DB transaction de dam bao atomicity.
     * Re-check capacity TRUOC KHI insert de tranh race condition.
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

                // === RACE CONDITION FIX ===
                // Step 1: Lock team row to serialize concurrent dispatches to the same team
                $lockedDoi = DoiCuuHo::where('id_doi_cuu_ho', $doi->id_doi_cuu_ho)
                    ->lockForUpdate()
                    ->first();

                if (!$lockedDoi) {
                    return [
                        'thanh_cong' => false,
                        'doi_id' => null,
                        'thong_diep' => 'Doi cuu ho khong ton tai',
                    ];
                }

                $soThanhVien = $lockedDoi->thanhViens ? $lockedDoi->thanhViens->count() : 0;
                $capacity = $soThanhVien * 1;

                if ($capacity > 0) {
                    // Step 2: Count active assignments FOR UPDATE (acquires gap lock on the range)
                    // This serializes with any concurrent insert into phan_cong_cuu_ho
                    $activeCount = PhanCongCuuHo::where('id_doi_cuu_ho', $doi->id_doi_cuu_ho)
                        ->whereIn('trang_thai_nhiem_vu', self::ACTIVE_STATUSES)
                        ->lockForUpdate()
                        ->count();

                    if ($activeCount >= $capacity) {
                        Log::warning('[AutoDispatch] Race condition ngan chan: team da full', [
                            'id_yeu_cau' => $yeuCau->id_yeu_cau,
                            'id_doi_cuu_ho' => $doi->id_doi_cuu_ho,
                            'capacity' => $capacity,
                            'active_count' => $activeCount,
                        ]);
                        return [
                            'thanh_cong' => false,
                            'doi_id' => null,
                            'thong_diep' => "Doi {$doi->ten_doi} da duoc phan cong boi tien trinh khac",
                        ];
                    }
                }
                // === END RACE CONDITION FIX ===

                $phanCong = PhanCongCuuHo::create([
                    'id_yeu_cau' => $yeuCau->id_yeu_cau,
                    'id_doi_cuu_ho' => $doi->id_doi_cuu_ho,
                    'trang_thai_nhiem_vu' => 'MOI',
                    'mo_ta' => "Dieu phoi tu dong - Diem tong: " . round($diemTong, 2),
                ]);

                $yeuCau->update(['trang_thai' => 'DA_PHAN_CONG']);
                $doi->update(['trang_thai' => 'BAN_CHI_DINH']);

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

                    // Broadcast capacity update realtime
                    $this->capNhatSucChuaRealtime($doi->id_doi_cuu_ho);
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
        return (bool) Cache::get('auto_dispatch_enabled', false);
    }

    /**
     * Bat dieu phoi tu dong.
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

    // === Public Accessor Methods ===

    public function layDanhSachDoiGanNhat(YeuCauCuuHo $yeuCau)
    {
        return $this->layDanhSachDoiGanNhatInternal($yeuCau);
    }

    public function tinhDiemNguyHiem(YeuCauCuuHo $yeuCau): float
    {
        return $this->tinhDiemNguyHiemInternal($yeuCau);
    }

    public function tinhDiemKhoangCach(DoiCuuHo $doi): int
    {
        return $this->tinhDiemKhoangCachInternal($doi);
    }

    public function tinhDiemTai(DoiCuuHo $doi): int
    {
        return $this->tinhDiemTaiInternal($doi);
    }

    public function tinhDiemThoiGian(YeuCauCuuHo $yeuCau): float
    {
        return $this->tinhDiemThoiGianInternal($yeuCau);
    }

    public function tinhDiemLoaiSuCo(YeuCauCuuHo $yeuCau, DoiCuuHo $doi): int
    {
        return $this->tinhDiemLoaiSuCoInternal($yeuCau, $doi);
    }

    /**
     * Cap nhat suc chua realtime qua Reverb
     */
    public function capNhatSucChuaRealtime(int $doiId): void
    {
        try {
            $doi = DoiCuuHo::with(['thanhViens', 'phanCongs', 'loaiSuCos'])->find($doiId);
            if (!$doi) {
                return;
            }

            $soThanhVienToiDa = $doi->thanhViens ? $doi->thanhViens->count() : 0;
            $soNhiemVuHienTai = PhanCongCuuHo::where('id_doi_cuu_ho', $doiId)
                ->whereIn('trang_thai_nhiem_vu', self::ACTIVE_STATUSES)
                ->count();
            $soSlotConTrong = $soThanhVienToiDa - $soNhiemVuHienTai;

            // Lấy danh sách id_loai_su_co của đội để listener có thể dispatch lại đúng loại
            $cacIdLoaiSuCo = ($doi->loaiSuCos ?? collect())
                ->map(fn($lsc) => $lsc->id_loai_su_co ?? $lsc->id ?? null)
                ->filter()
                ->map(fn($id) => (int) $id)
                ->values()
                ->toArray();

            $payload = [
                'team_id'              => $doiId,
                'team_name'            => $doi->ten_doi,
                'current_assignments'  => $soNhiemVuHienTai,
                'max_assignments'      => $soThanhVienToiDa,
                'available_capacity'   => $soSlotConTrong,
                'loai_su_co_ids'       => $cacIdLoaiSuCo,
                'timestamp'            => now()->toISOString(),
            ];

            event(new SucChuaDoiDaCapNhat($payload));

            if ($soSlotConTrong > 0) {
                event(new CoDoiTrongTroLai($payload));
            }

            Log::info('[AutoDispatch] Cập nhật sức chứa realtime', $payload);

        } catch (\Throwable $e) {
            Log::error('[AutoDispatch] Lỗi cập nhật sức chứa realtime', [
                'doi_id' => $doiId,
                'error'  => $e->getMessage(),
            ]);
        }
    }
}
