<?php

namespace App\Http\Controllers;

use App\Events\AutoDispatchStatusChanged;
use App\Jobs\AutoDispatchJob;
use App\Models\YeuCauCuuHo;
use App\Services\AutoDispatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * AutoDispatchController - Controller điều phối tự động đội cứu hộ.
 *
 * Các tính năng:
 * 1. Toggle AutoDispatch ON/OFF
 * 2. Trigger dispatch thủ công cho một yêu cầu
 * 3. Kiểm tra trạng thái điều phối tự động
 * 4. Xem danh sách yêu cầu cần can thiệp admin
 */
class AutoDispatchController extends Controller
{
    private AutoDispatchService $dispatchService;

    public function __construct(AutoDispatchService $dispatchService)
    {
        $this->dispatchService = $dispatchService;
    }

    /**
     * Lấy trạng thái điều phối tự động hiện tại.
     *
     * Route: GET api/auto-dispatch/status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function layTrangThai()
    {
        $daBat = AutoDispatchService::daBat();

        return response()->json([
            'thanh_cong' => true,
            'du_lieu' => [
                'dieu_phoi_tu_dong' => $daBat,
                'thong_diep' => $daBat
                    ? 'Điều phối tự động đang BẬT'
                    : 'Điều phối tự động đang TẮT',
            ],
        ]);
    }

    /**
     * Toggle (bật/tắt) điều phối tự động.
     *
     * Route: POST api/auto-dispatch/toggle
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle()
    {
        $trangThaiMoi = AutoDispatchService::toggleTrangThai();

        Log::info('[AutoDispatchController] Toggle điều phối tự động', [
            'trang_thai_moi' => $trangThaiMoi ? 'BẬT' : 'TẮT',
        ]);

        try {
            event(new AutoDispatchStatusChanged($trangThaiMoi));
        } catch (\Throwable $e) {
            Log::warning('[AutoDispatchController] Broadcast toggle thất bại', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'thanh_cong' => true,
            'du_lieu' => [
                'dieu_phoi_tu_dong' => $trangThaiMoi,
                'thong_diep' => $trangThaiMoi
                    ? 'Đã bật điều phối tự động'
                    : 'Đã tắt điều phối tự động',
            ],
        ]);
    }

    /**
     * Bật điều phối tự động.
     *
     * Route: POST api/auto-dispatch/enable
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function batDieuPhoi()
    {
        AutoDispatchService::batDieuPhoi();

        Log::info('[AutoDispatchController] Bật điều phối tự động');

        try {
            event(new AutoDispatchStatusChanged(true));
        } catch (\Throwable $e) {
            Log::warning('[AutoDispatchController] Broadcast enable thất bại', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'thanh_cong' => true,
            'thong_diep' => 'Đã bật điều phối tự động',
            'dieu_phoi_tu_dong' => true,
            'du_lieu' => ['dieu_phoi_tu_dong' => true],
        ]);
    }

    /**
     * Tắt điều phối tự động.
     *
     * Route: POST api/auto-dispatch/disable
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function tatDieuPhoi()
    {
        AutoDispatchService::tatDieuPhoi();

        Log::info('[AutoDispatchController] Tắt điều phối tự động');

        try {
            event(new AutoDispatchStatusChanged(false));
        } catch (\Throwable $e) {
            Log::warning('[AutoDispatchController] Broadcast disable thất bại', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'thanh_cong' => true,
            'thong_diep' => 'Đã tắt điều phối tự động',
            'dieu_phoi_tu_dong' => false,
            'du_lieu' => ['dieu_phoi_tu_dong' => false],
        ]);
    }

    /**
     * Trigger điều phối thủ công cho một yêu cầu cứu hộ.
     *
     * Route: POST api/auto-dispatch/dispatch/{id}
     *
     * @param Request $request
     * @param int $id ID của yêu cầu cứu hộ
     * @return \Illuminate\Http\JsonResponse
     */
    public function kichHoatDieuPhoi(Request $request, int $id)
    {
        try {
            $yeuCau = YeuCauCuuHo::with('phanCongs')->find($id);

            if (!$yeuCau) {
                return response()->json([
                    'thanh_cong' => false,
                    'thong_diep' => 'Yêu cầu cứu hộ không tồn tại',
                ], 404);
            }

            if ($yeuCau->phanCongs()->exists()) {
                return response()->json([
                    'thanh_cong' => false,
                    'thong_diep' => 'Yêu cầu đã có phân công',
                ], 422);
            }

            $trangThai = strtoupper(trim((string) ($yeuCau->trang_thai ?? '')));
            $trangThaiChoPhep = ['CHO_XU_LY', 'MOI', 'WAITING', 'CHO_PHAN_CONG'];
            if (!in_array($trangThai, $trangThaiChoPhep, true)) {
                return response()->json([
                    'thanh_cong' => false,
                    'thong_diep' => "Yêu cầu đang ở trạng thái {$trangThai}, không thể điều phối",
                ], 422);
            }

            // Dispatch job vào queue để xử lý
            AutoDispatchJob::dispatch($id);

            Log::info('[AutoDispatchController] Đã dispatch job điều phối', [
                'id_yeu_cau' => $id,
            ]);

            return response()->json([
                'thanh_cong' => true,
                'thong_diep' => "Đã đưa yêu cầu #{$id} vào hàng đợi điều phối tự động",
            ], 202);
        } catch (\Throwable $e) {
            Log::error('[AutoDispatchController] Lỗi khi kích hoạt điều phối', [
                'id_yeu_cau' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'thanh_cong' => false,
                'thong_diep' => 'Lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger điều phối đồng bộ (không qua queue).
     * Dùng cho admin muốn xem kết quả ngay lập tức.
     *
     * Route: POST api/auto-dispatch/dispatch-sync/{id}
     *
     * @param int $id ID của yêu cầu cứu hộ
     * @return \Illuminate\Http\JsonResponse
     */
    public function kichHoatDieuPhoiDongBo(int $id)
    {
        try {
            $yeuCau = YeuCauCuuHo::with('phanCongs')->find($id);

            if (!$yeuCau) {
                return response()->json([
                    'thanh_cong' => false,
                    'thong_diep' => 'Yêu cầu cứu hộ không tồn tại',
                ], 404);
            }

            if ($yeuCau->phanCongs()->exists()) {
                return response()->json([
                    'thanh_cong' => false,
                    'thong_diep' => 'Yêu cầu đã có phân công',
                ], 422);
            }

            $ketQua = $this->dispatchService->xuLyDieuPhoiTuDong($id);

            return response()->json([
                'thanh_cong' => $ketQua['thanh_cong'],
                'thong_diep' => $ketQua['thong_diep'],
                'du_lieu' => [
                    'doi_id' => $ketQua['doi_id'],
                    'phan_cong_id' => $ketQua['phan_cong_id'] ?? null,
                    'diem_tong' => $ketQua['diem_tong'] ?? null,
                ],
            ], $ketQua['thanh_cong'] ? 200 : 422);
        } catch (\Throwable $e) {
            Log::error('[AutoDispatchController] Lỗi điều phối đồng bộ', [
                'id_yeu_cau' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'thanh_cong' => false,
                'thong_diep' => 'Lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy danh sách yêu cầu cần can thiệp admin
     * (những yêu cầu vượt quá số lần retry tự động).
     *
     * Route: GET api/auto-dispatch/admin-escalations
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function layDanhSachCanThiepAdmin()
    {
        try {
            // Lấy tất cả yêu cầu đang ở trạng thái chờ quá lâu (> 30 phút)
            $thoiGianCanhBao = now()->subMinutes(90);

            $yeuCauCanThiep = YeuCauCuuHo::with([
                'nguoiDung',
                'loaiSuCo',
                'phanCongs.doiCuuHo',
            ])
                ->whereIn('trang_thai', ['CHO_XU_LY', 'DA_PHAN_CONG'])
                ->whereDoesntHave('phanCongs', function ($query) {
                    $query->whereIn('trang_thai_nhiem_vu', ['DANG_XU_LY', 'DA_DEN_HIEN_TRUONG', 'HOAN_THANH']);
                })
                ->where('created_at', '<', $thoiGianCanhBao)
                ->orderBy('created_at', 'asc')
                ->get();

            $danhSach = $yeuCauCanThiep->map(function ($yc) {
                $phanCong = $yc->phanCongs->first();
                return [
                    'id_yeu_cau' => $yc->id_yeu_cau ?? $yc->id,
                    'trang_thai' => $yc->trang_thai,
                    'muc_do_khan_cap' => $yc->muc_do_khan_cap ?? 'MEDIUM',
                    'vi_tri_dia_chi' => $yc->vi_tri_dia_chi,
                    'thoi_gian_cho' => $yc->created_at->diffForHumans(),
                    'thoi_gian_cho_phut' => now()->diffInMinutes($yc->created_at),
                    'loai_su_co' => $yc->loaiSuCo
                        ? ($yc->loaiSuCo->ten_danh_muc ?? $yc->loaiSuCo->ten_loai_su_co ?? 'N/A')
                        : null,
                    'nguoi_yeu_cau' => $yc->nguoiDung
                        ? ($yc->nguoiDung->ho_ten ?? 'N/A')
                        : null,
                    'doi_da_phan' => $phanCong ? [
                        'id' => $phanCong->doiCuuHo?->id_doi_cuu_ho,
                        'ten' => $phanCong->doiCuuHo?->ten_doi ?? 'N/A',
                        'trang_thai_nhiem_vu' => $phanCong->trang_thai_nhiem_vu,
                    ] : null,
                    'can_thiep_admin' => true,
                ];
            });

            return response()->json([
                'thanh_cong' => true,
                'so_luong' => $danhSach->count(),
                'du_lieu' => $danhSach,
            ]);
        } catch (\Throwable $e) {
            Log::error('[AutoDispatchController] Lỗi lấy danh sách can thiệp admin', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'thanh_cong' => false,
                'thong_diep' => 'Lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xóa cảnh báo can thiệp admin cho một yêu cầu.
     *
     * Route: DELETE api/auto-dispatch/admin-escalations/{id}
     *
     * @param int $id ID của yêu cầu cứu hộ
     * @return \Illuminate\Http\JsonResponse
     */
    public function xoaCanhBaoCanThiep(int $id)
    {
        try {
            $cacheKey = "admin_escalation_{$id}";
            $daCo = \Illuminate\Support\Facades\Cache::has($cacheKey);
            \Illuminate\Support\Facades\Cache::forget($cacheKey);

            return response()->json([
                'thanh_cong' => true,
                'thong_diep' => $daCo
                    ? "Đã xóa cảnh báo can thiệp cho yêu cầu #{$id}"
                    : "Không có cảnh báo nào cho yêu cầu #{$id}",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'thanh_cong' => false,
                'thong_diep' => 'Lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xem chi tiết điểm chấm của một yêu cầu và các đội ứng viên.
     * Dùng cho debug và kiểm tra scoring.
     *
     * Route: GET api/auto-dispatch/debug/{id}
     *
     * @param int $id ID của yêu cầu cứu hộ
     * @return \Illuminate\Http\JsonResponse
     */
    public function xemDiemCham(int $id)
    {
        try {
            $yeuCau = YeuCauCuuHo::with([
                'chiTiet',
                'phanCongs.doiCuuHo',
            ])->find($id);

            if (!$yeuCau) {
                return response()->json([
                    'thanh_cong' => false,
                    'thong_diep' => 'Yêu cầu cứu hộ không tồn tại',
                ], 404);
            }

            $diemNguyHiem = $this->dispatchService->tinhDiemNguyHiem($yeuCau);
            $diemThoiGian = $this->dispatchService->tinhDiemThoiGian($yeuCau);

            // Lấy top 5 đội gần nhất để hiển thị điểm
            $reqLat = $yeuCau->vi_tri_lat ? floatval($yeuCau->vi_tri_lat) : null;
            $reqLng = $yeuCau->vi_tri_lng ? floatval($yeuCau->vi_tri_lng) : null;

            $danhSachDoi = [];
            if ($reqLat !== null && $reqLng !== null) {
                $danhSachDoi = $this->dispatchService->layDanhSachDoiGanNhat($yeuCau);
            }

            $diemDoi = $danhSachDoi->map(function ($doi) use ($yeuCau, $diemNguyHiem, $diemThoiGian) {
                $diemKhoangCach = $this->dispatchService->tinhDiemKhoangCach($doi);
                $diemTai = $this->dispatchService->tinhDiemTai($doi);
                $diemTong = $diemNguyHiem + $diemKhoangCach + $diemTai + $diemThoiGian;

                return [
                    'id' => $doi->id_doi_cuu_ho,
                    'ten_doi' => $doi->ten_doi,
                    'khoang_cach_km' => $doi->distance,
                    'so_thanh_vien' => $doi->thanhViens ? $doi->thanhViens->count() : 0,
                    'suc_chua' => ($doi->thanhViens ? $doi->thanhViens->count() : 0) * 1,
                    'so_nhiem_vu_hien_tai' => $doi->so_nhiem_vu_dang_xu_ly ?? 0,
                    'diem_nguy_hiem' => $diemNguyHiem,
                    'diem_khoang_cach' => $diemKhoangCach,
                    'diem_tai' => $diemTai,
                    'diem_thoi_gian' => $diemThoiGian,
                    'diem_tong' => round($diemTong, 2),
                    'da_loai_bo' => $diemTai === -100,
                ];
            })->sortByDesc('diem_tong')->values();

            return response()->json([
                'thanh_cong' => true,
                'yeu_cau' => [
                    'id' => $yeuCau->id_yeu_cau ?? $yeuCau->id,
                    'trang_thai' => $yeuCau->trang_thai,
                    'muc_do_khan_cap' => $yeuCau->muc_do_khan_cap,
                    'vi_tri' => [
                        'lat' => $reqLat,
                        'lng' => $reqLng,
                        'dia_chi' => $yeuCau->vi_tri_dia_chi,
                    ],
                    'thoi_gian_cho_phut' => now()->diffInMinutes($yeuCau->created_at),
                ],
                'diem_nguy_hiem_toi_da' => $diemNguyHiem,
                'diem_thoi_gian' => round($diemThoiGian, 2),
                'danh_sach_doi' => $diemDoi,
            ]);
        } catch (\Throwable $e) {
            Log::error('[AutoDispatchController] Lỗi xem điểm chấm', [
                'id_yeu_cau' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'thanh_cong' => false,
                'thong_diep' => 'Lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cập nhật cấu hình điều phối tự động (tùy chọn, mở rộng sau).
     *
     * Route: PUT api/auto-dispatch/config
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function capNhatCauHinh(Request $request)
    {
        try {
            $validated = $request->validate([
                'so_doi_toi_da' => 'nullable|integer|min:1|max:20',
                'thoi_gian_retry_phut' => 'nullable|integer|min:1|max:120',
                'so_lan_retry_toi_da' => 'nullable|integer|min:1|max:10',
            ]);

            if (isset($validated['so_doi_toi_da'])) {
                Cache::put('dispatch_config_so_doi', $validated['so_doi_toi_da'], now()->addYears(10));
            }
            if (isset($validated['thoi_gian_retry_phut'])) {
                Cache::put('dispatch_config_retry_phut', $validated['thoi_gian_retry_phut'], now()->addYears(10));
            }
            if (isset($validated['so_lan_retry_toi_da'])) {
                Cache::put('dispatch_config_so_retry', $validated['so_lan_retry_toi_da'], now()->addYears(10));
            }

            return response()->json([
                'thanh_cong' => true,
                'thong_diep' => 'Đã cập nhật cấu hình điều phối tự động',
                'cau_hinh' => [
                    'so_doi_toi_da' => Cache::get('dispatch_config_so_doi', 5),
                    'thoi_gian_retry_phut' => Cache::get('dispatch_config_retry_phut', 30),
                    'so_lan_retry_toi_da' => Cache::get('dispatch_config_so_retry', 3),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'thanh_cong' => false,
                'thong_diep' => 'Lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
