<?php

namespace App\Http\Controllers;

use App\Http\Requests\YeuCauCuuHoRequest;
use App\Models\{DoiCuuHo, YeuCauCuuHo, HangDoiXuLy, PhanLoaiAis, DuLieuHeatmap, PhanCongCuuHo, NguoiDung, LoaiSuCo};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class YeuCauCuuHoController extends Controller
{
    /**
     * Tính khoảng cách Haversine giữa 2 điểm (lat/lng) theo km.
     * Trả về số km (>= 0) nếu đủ tọa độ, hoặc null nếu thiếu dữ liệu.
     */
    private function haversineDistance(?float $lat1, ?float $lng1, ?float $lat2, ?float $lng2): ?float
    {
        if ($lat1 === null || $lng1 === null || $lat2 === null || $lng2 === null) {
            return null;
        }
        if ($lat1 == $lat2 && $lng1 == $lng2) {
            return 0.0;
        }
        $R = 6371; // Bán kính trái đất km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c = 2 * asin(sqrt($a));
        return round($R * $c, 2);
    }

    public function timDoiGanNhat(Request $request)
    {
        $idYeuCau = $request->id_yeu_cau;
        $idLoaiSuCo = $request->id_loai_su_co;

        // Lấy thông tin sự cố để biết khu vực
        $yeuCau = null;
        $khuVucSuCo = null;
        if ($idYeuCau) {
            $yeuCau = YeuCauCuuHo::find($idYeuCau);
            if ($yeuCau) {
                $khuVucSuCo = $yeuCau->vi_tri_dia_chi ?? '';
                if (!$idLoaiSuCo) {
                    $idLoaiSuCo = $yeuCau->id_loai_su_co;
                }
            }
        }

        // Lấy tọa độ sự cố
        $reqLat = $yeuCau ? floatval($yeuCau->vi_tri_lat ?? 0) : null;
        $reqLng = $yeuCau ? floatval($yeuCau->vi_tri_lng ?? 0) : null;

        $allTeams = DoiCuuHo::with(['thanhViens', 'taiNguyens', 'loaiSuCos', 'viTris'])
            ->whereIn('trang_thai', ['SAN_SANG', 'SanSang', 'Sẵn sàng'])
            ->get();

        // Hàm normalize quận
        $normalizeDistrict = function($value) {
            if (!$value) return '';
            $normalized = mb_strtolower(trim((string) $value));
            $normalized = preg_replace('/^q\.\s*/iu', '', $normalized);
            $normalized = preg_replace('/^quận\s+/iu', '', $normalized);
            $normalized = preg_replace('/\s+/', ' ', $normalized);
            return $normalized;
        };

        $reqDistrict = $normalizeDistrict($khuVucSuCo);

        // Ưu tiên type + quận, sort theo score rồi đến khoảng cách
        $allTeamsList = [];

        foreach ($allTeams as $team) {
            $trangThai = $this->normalizeTrangThaiDoi($team->trang_thai);

            $loaiSuCoList = $team->loaiSuCos ?? collect();
            $loaiSuCoNames = $loaiSuCoList->map(fn($lsc) => $lsc->ten_danh_muc ?? $lsc->ten_loai_su_co ?? $lsc->ten_loai ?? '')->filter()->values()->toArray();
            $loaiSuCoIds = $loaiSuCoList->map(fn($lsc) => $lsc->id_loai_su_co ?? $lsc->id ?? null)->filter()->values()->toArray();

            // Xác định cùng loại sự cố: true/false/null (null = chưa xác định)
            $cungLoaiSuCo = null;
            if ($idLoaiSuCo && is_numeric($idLoaiSuCo)) {
                $idNum = (int) $idLoaiSuCo;
                $cungLoaiSuCo = in_array($idNum, array_map('intval', $loaiSuCoIds), true);
            }

            // Tính cung_quan: so sánh quận của đội với quận của sự cố
            // Fix: so sánh bằng normalized string để tránh lỗi encoding/khoảng trắng
            $cungQuan = false;
            if ($reqDistrict) {
                $teamDistrict = $normalizeDistrict($team->khu_vuc_quan_ly ?? '');
                // Ép về cùng 1 dạng chuẩn: lowercase + remove dấu + khoảng trắng
                $normReq = $normalizeDistrict($reqDistrict);
                $normTeam = $normalizeDistrict($teamDistrict);
                $cungQuan = $normReq === $normTeam;
            }

            $teamLat = floatval($team->vi_tri_lat ?? null);
            $teamLng = floatval($team->vi_tri_lng ?? null);

            // Ưu tiên vị trí mới nhất từ bảng vi_tri_doi_cuu_ho (đội di chuyển)
            if ($team->viTris && $team->viTris->count() > 0) {
                $latestViTri = $team->viTris->sortByDesc('id')->first();
                $teamLat = floatval($latestViTri->vi_tri_lat ?? $teamLat);
                $teamLng = floatval($latestViTri->vi_tri_lng ?? $teamLng);
            }

            $teamData = [
                'id'                     => $team->id_doi_cuu_ho,
                'ten_doi'               => $team->ten_co,
                'khu_vuc_quan_ly'       => $team->khu_vuc_quan_ly,
                'so_dien_thoai_hotline' => $team->so_dien_thoai_hotline,
                'trang_thai'            => $trangThai,
                'trang_thai_goc'        => $team->trang_thai,
                'thanh_viens'           => $team->thanhViens,
                'tai_nguyens'           => $team->taiNguyens->map(fn($t) => [
                    'ten_tai_nguyen' => $t->ten_tai_nguyen,
                ]),
                'loai_su_co'      => $loaiSuCoNames,
                'cung_loai_su_co' => $cungLoaiSuCo,
                'cung_quan'       => $cungQuan,
                'khoang_cach_km'  => $this->haversineDistance($reqLat, $reqLng, $teamLat ?: null, $teamLng ?: null),
            ];

            $allTeamsList[] = $teamData;
        }

        // Sort ưu tiên thống nhất:
        // 1. Cùng type + cùng quận  (score 3)
        // 2. Cùng type + khác quận  (score 2)
        // 3. Khác type + cùng quận  (score 1)
        // 4. Khác type + khác quận  (score 0)
        // Trong cùng score: gần nhất trước
        usort($allTeamsList, function($a, $b) {
            $scoreA = ($a['cung_loai_su_co'] === true ? 2 : ($a['cung_loai_su_co'] === false ? 0 : 1))
                    + ($a['cung_quan'] ? 1 : 0);
            $scoreB = ($b['cung_loai_su_co'] === true ? 2 : ($b['cung_loai_su_co'] === false ? 0 : 1))
                    + ($b['cung_quan'] ? 1 : 0);

            if ($scoreA !== $scoreB) {
                return $scoreB - $scoreA; // cao hơn lên trước
            }

            // null distance = chưa xác định → đẩy xuống cuối
            $distA = $a['khoang_cach_km'] ?? PHP_FLOAT_MAX;
            $distB = $b['khoang_cach_km'] ?? PHP_FLOAT_MAX;
            return $distA <=> $distB; // gần hơn lên trước
        });

        $teams = collect($allTeamsList);

        if ($teams->isEmpty()) {
            return response()->json([
                'message' => 'Không có đội cứu hộ nào sẵn sàng'
            ], 404);
        }

        return response()->json([
            'teams' => $teams,
        ]);
    }

    private function normalizeTrangThaiDoi($trangThai): string
    {
        $v = strtoupper(trim((string) ($trangThai ?? '')));
        return match ($v) {
            'SAN_SANG', 'SANSANG'   => 'SanSang',
            'DANG_CUU_HO', 'SANGSANG' => 'DangCuuHo',
            'BAN_CHI_DINH'          => 'BanChiDinh',
            'TAM_NGUNG'             => 'TamNgung',
            default                  => $trangThai ?? '',
        };
    }


    private function urgencyToNumber($value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $v = strtoupper(trim((string) $value));
        return match ($v) {
            'LOW' => 1,
            'MEDIUM' => 2,
            'HIGH' => 3,
            'CRITICAL' => 4,
            default => 0,
        };
    }

    private function normalizeTrangThaiYeuCau(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $v = strtoupper(trim($value));

        // Backward-compatible mapping (old lowercase API)
        $map = [
            'MOI' => 'CHO_XU_LY',
            'DANG_XU_LY' => 'DANG_XU_LY',
            'DA_HOAN_THANH' => 'HOAN_THANH',
            'DA_HUY' => 'HUY_BO',
            'HUY' => 'HUY_BO',

            // Canonical statuses
            'CHO_XU_LY' => 'CHO_XU_LY',
            'DA_PHAN_CONG' => 'DA_PHAN_CONG',
            'DANG_XU_LY' => 'DANG_XU_LY',
            'DA_DEN_HIEN_TRUONG' => 'DA_DEN_HIEN_TRUONG',
            'HOAN_THANH' => 'HOAN_THANH',
            'THAT_BAI' => 'THAT_BAI',
            'HUY_BO' => 'HUY_BO',
        ];

        return $map[$v] ?? $v;
    }

    private function normalizeMucDoKhanCap($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Accept old numeric level (1-5) and map to canonical string
        if (is_numeric($value)) {
            $n = (int) $value;
            if ($n <= 1) {
                return 'LOW';
            }
            if ($n == 2) {
                return 'MEDIUM';
            }
            if ($n == 3) {
                return 'HIGH';
            }
            return 'CRITICAL';
        }

        $v = strtoupper(trim((string) $value));
        $allowed = ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'];
        return in_array($v, $allowed, true) ? $v : $v;
    }

    /**
     * Display paginated list of all rescue requests
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $sortBy = $request->get('sort_by', 'id_yeu_cau');
            $sortOrder = $request->get('sort_order', 'desc');

            $items = YeuCauCuuHo::with([
                'nguoiDung',
                'loaiSuCo.chiTiets',
                'hangDoiXuLy',
                'phanLoaiAis',
                'phanCongs',
                'danhGias'
            ])
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage);

            return Response::json([
                'success' => true,
                'message' => 'Danh sách yêu cầu cứu hộ',
                'data' => $items
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created rescue request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Parse id_loai_su_co if sent as JSON array string "[3]" or "3"
            if ($request->has('id_loai_su_co')) {
                $raw = $request->input('id_loai_su_co');
                // If it's a JSON array like "[3]", extract the first element
                if (is_string($raw) && preg_match('/^\[\s*(\d+)\s*\]$/', $raw, $matches)) {
                    $request->merge(['id_loai_su_co' => (int) $matches[1]]);
                }
            }

            // Validate basic fields manually (skip YeuCauCuuHoRequest to handle file upload separately)
            $baseValidated = $request->validate([
                'id_nguoi_dung' => 'required|exists:nguoi_dung,id_nguoi_dung',
                'id_loai_su_co' => 'required|exists:loai_su_co,id_loai_su_co',
                'vi_tri_lat' => 'required|numeric',
                'vi_tri_lng' => 'required|numeric',
                'vi_tri_dia_chi' => 'nullable|string|max:500',
                'chi_tiet' => 'nullable|string',
                'mo_ta' => 'required|string',
                'hinh_anh' => 'nullable|file|image|max:10240',
                'so_nguoi_bi_anh_huong' => 'nullable|integer|min:0',
                'muc_do_khan_cap' => 'nullable|string|max:20',
                'diem_uu_tien' => 'nullable|numeric',
                'trang_thai' => 'nullable|string|max:30',
            ]);

            if (is_array($request->input('chi_tiet'))) {
                $request->merge(['chi_tiet' => implode(', ', $request->input('chi_tiet'))]);
            }

            $validated = $baseValidated;

            if (array_key_exists('trang_thai', $validated)) {
                $validated['trang_thai'] = $this->normalizeTrangThaiYeuCau($validated['trang_thai']);
            }
            if (array_key_exists('muc_do_khan_cap', $validated)) {
                $validated['muc_do_khan_cap'] = $this->normalizeMucDoKhanCap($validated['muc_do_khan_cap']);
            }

            if (!array_key_exists('diem_uu_tien', $validated) || $validated['diem_uu_tien'] === null) {
                $validated['diem_uu_tien'] = 0;
            }
            if (!array_key_exists('so_nguoi_bi_anh_huong', $validated) || $validated['so_nguoi_bi_anh_huong'] === null) {
                $validated['so_nguoi_bi_anh_huong'] = 1;
            }
            if (!array_key_exists('muc_do_khan_cap', $validated) || $validated['muc_do_khan_cap'] === null) {
                $validated['muc_do_khan_cap'] = 'MEDIUM';
            }
            if (!array_key_exists('trang_thai', $validated) || $validated['trang_thai'] === null) {
                $validated['trang_thai'] = 'CHO_XU_LY';
            }

            // Handle file upload - store file and save filename to DB
            $hinhAnhPath = null;
            if ($request->hasFile('hinh_anh')) {
                $file = $request->file('hinh_anh');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/hinh_anh'), $filename);
                $hinhAnhPath = 'uploads/hinh_anh/' . $filename;
            }

            $item = YeuCauCuuHo::create([
                ...$validated,
                'hinh_anh' => $hinhAnhPath,
            ]);

            // Create processing queue entry
            HangDoiXuLy::create([
                'id_yeu_cau' => $item->id_yeu_cau,
                'diem_uu_tien' => $validated['diem_uu_tien'] ?? 0,
                'muc_khan_cap' => $validated['muc_do_khan_cap'] ?? 'MEDIUM',
                'trang_thai' => 'WAITING'
            ]);

            $item->load('nguoiDung', 'loaiSuCo.chiTiets', 'hangDoiXuLy');

            return Response::json([
                'success' => true,
                'message' => 'Tạo yêu cầu cứu hộ thành công',
                'data' => $item
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi tạo yêu cầu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified rescue request
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $item = YeuCauCuuHo::with([
                'nguoiDung',
                'loaiSuCo.chiTiets',
                'hangDoiXuLy',
                'phanLoaiAis',
                'phanCongs.doiCuuHo',
                'phanCongs.ketQua',
                'danhGias'
            ])->findOrFail($id);

            return Response::json([
                'success' => true,
                'message' => 'Chi tiết yêu cầu cứu hộ',
                'data' => $item
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Yêu cầu cứu hộ không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy chi tiết: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified rescue request
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $item = YeuCauCuuHo::findOrFail($id);

            $validated = $request->validate([
                'id_nguoi_dung' => 'nullable|integer|exists:nguoi_dung,id_nguoi_dung',
                'id_loai_su_co' => 'nullable|integer|exists:loai_su_co,id_loai_su_co',
                'vi_tri_lat' => 'nullable|numeric',
                'vi_tri_lng' => 'nullable|numeric',
                'vi_tri_dia_chi' => 'nullable|string|max:500',
                'chi_tiet' => 'nullable|string',
                'mo_ta' => 'nullable|string',
                'hinh_anh' => 'nullable|string',
                'so_nguoi_bi_anh_huong' => 'nullable|integer|min:0',
                'muc_do_khan_cap' => 'nullable',
                'diem_uu_tien' => 'nullable|numeric',
                'trang_thai' => 'nullable|string'
            ]);

            if (array_key_exists('trang_thai', $validated)) {
                $validated['trang_thai'] = $this->normalizeTrangThaiYeuCau($validated['trang_thai']);
            }
            if (array_key_exists('muc_do_khan_cap', $validated)) {
                $validated['muc_do_khan_cap'] = $this->normalizeMucDoKhanCap($validated['muc_do_khan_cap']);
            }

            $item->update($validated);

            // Update processing queue if priority or urgency changed
            if (isset($validated['diem_uu_tien']) || isset($validated['muc_do_khan_cap'])) {
                $item->hangDoiXuLy()->update([
                    'diem_uu_tien' => $validated['diem_uu_tien'] ?? $item->hangDoiXuLy->diem_uu_tien,
                    'muc_khan_cap' => $validated['muc_do_khan_cap'] ?? $item->hangDoiXuLy->muc_khan_cap
                ]);
            }

            $item->load('nguoiDung', 'loaiSuCo.chiTiets', 'hangDoiXuLy');

            return Response::json([
                'success' => true,
                'message' => 'Cập nhật yêu cầu cứu hộ thành công',
                'data' => $item
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Yêu cầu cứu hộ không tồn tại'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the specified rescue request
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $item = YeuCauCuuHo::findOrFail($id);

            // Cannot cancel/delete if request is already being processed
            $normalized = $this->normalizeTrangThaiYeuCau($item->trang_thai);
            if (in_array($normalized, ['DA_PHAN_CONG', 'DANG_XU_LY', 'DA_DEN_HIEN_TRUONG'], true)) {
                return Response::json([
                    'success' => false,
                    'message' => 'Yêu cầu đang được xử lý, không thể hủy'
                ], 422);
            }

            // Delete related processing queue entries
            HangDoiXuLy::where('id_yeu_cau', $id)->delete();

            // Delete AI classifications
            PhanLoaiAis::where('id_yeu_cau', $id)->delete();

            $item->delete();

            return Response::json([
                'success' => true,
                'message' => 'Xóa yêu cầu cứu hộ thành công'
            ], 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Yêu cầu cứu hộ không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi xóa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rescue requests by status
     *
     * @param string $status
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByStatus(Request $request, $status)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $normalized = $this->normalizeTrangThaiYeuCau($status);
            $validStatuses = ['CHO_XU_LY', 'DA_PHAN_CONG', 'DANG_XU_LY', 'DA_DEN_HIEN_TRUONG', 'HOAN_THANH', 'THAT_BAI', 'HUY_BO'];

            if (!in_array($normalized, $validStatuses, true)) {
                return Response::json([
                    'success' => false,
                    'message' => 'Trạng thái không hợp lệ'
                ], 422);
            }

            $items = YeuCauCuuHo::with([
                'nguoiDung',
                'loaiSuCo',
                'hangDoiXuLy'
            ])
                ->where('trang_thai', $normalized)
                ->paginate($perPage);

            return Response::json([
                'success' => true,
                'message' => "Danh sách yêu cầu với trạng thái: {$normalized}",
                'data' => $items
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the status of a rescue request
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $item = YeuCauCuuHo::with('phanCongs')->findOrFail($id);

            $validated = $request->validate([
                'trang_thai' => 'required|string'
            ]);

            $normalized = $this->normalizeTrangThaiYeuCau($validated['trang_thai']);
            $allowed = ['CHO_XU_LY', 'DA_PHAN_CONG', 'DANG_XU_LY', 'DA_DEN_HIEN_TRUONG', 'HOAN_THANH', 'THAT_BAI', 'HUY_BO'];
            if (!in_array($normalized, $allowed, true)) {
                return Response::json([
                    'success' => false,
                    'message' => 'Trạng thái không hợp lệ'
                ], 422);
            }

            // Cannot cancel if assignment has already been made
            if ($normalized === 'HUY_BO') {
                $hasAssignment = $item->phanCongs()
                    ->whereIn('trang_thai_nhiem_vu', ['MOI', 'DANG_XU_LY', 'DA_DEN_HIEN_TRUONG'])
                    ->exists();
                if ($hasAssignment) {
                    return Response::json([
                        'success' => false,
                        'message' => 'Đội cứu hộ đã được phân công, không thể hủy'
                    ], 422);
                }
            }

            $item->update(['trang_thai' => $normalized]);

            // Update processing queue status if applicable
            if ($item->hangDoiXuLy) {
                if ($normalized === 'HUY_BO') {
                    // When cancelled, remove from queue entirely
                    $item->hangDoiXuLy->delete();
                } else {
                    $queueStatus = $normalized === 'CHO_XU_LY' ? 'WAITING'
                        : ($normalized === 'DA_PHAN_CONG' || $normalized === 'DANG_XU_LY' || $normalized === 'DA_DEN_HIEN_TRUONG' ? 'PROCESSING' : 'DONE');
                    $item->hangDoiXuLy->update(['trang_thai' => $queueStatus]);
                }
            }

            $item->load('nguoiDung', 'loaiSuCo.chiTiets', 'hangDoiXuLy');

            return Response::json([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công',
                'data' => $item
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Yêu cầu cứu hộ không tồn tại'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật trạng thái: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rescue requests by urgency level
     *
     * @param Request $request
     * @param int $muc_do
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByUrgency(Request $request, $muc_do)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $normalized = $this->normalizeMucDoKhanCap($muc_do);
            $valid = ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'];

            if (!in_array($normalized, $valid, true)) {
                return Response::json([
                    'success' => false,
                    'message' => 'Mức độ khẩn cấp không hợp lệ'
                ], 422);
            }

            $items = YeuCauCuuHo::with([
                'nguoiDung',
                'loaiSuCo',
                'hangDoiXuLy'
            ])
                ->where('muc_do_khan_cap', $normalized)
                ->orderBy('diem_uu_tien', 'desc')
                ->paginate($perPage);

            return Response::json([
                'success' => true,
                'message' => "Danh sách yêu cầu mức độ khẩn cấp: {$normalized}",
                'data' => $items
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get processing queue with pagination
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHangDoiXuLy(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $sortBy = $request->get('sort_by', 'diem_uu_tien');
            $sortOrder = $request->get('sort_order', 'desc');

            $queue = HangDoiXuLy::with([
                'yeuCau.nguoiDung',
                'yeuCau.loaiSuCo'
            ])
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage);

            return Response::json([
                'success' => true,
                'message' => 'Hàng đợi xử lý',
                'data' => $queue
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy hàng đợi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get processing queue by status
     *
     * @param Request $request
     * @param string $trang_thai
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHangDoiByStatus(Request $request, $trang_thai)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $normalized = strtoupper(trim((string) $trang_thai));
            $validStatuses = ['WAITING', 'PROCESSING', 'DONE'];

            if (!in_array($normalized, $validStatuses, true)) {
                return Response::json([
                    'success' => false,
                    'message' => 'Trạng thái không hợp lệ'
                ], 422);
            }

            $queue = HangDoiXuLy::with([
                'yeuCau.nguoiDung',
                'yeuCau.loaiSuCo'
            ])
                ->where('trang_thai', $normalized)
                ->orderBy('diem_uu_tien', 'desc')
                ->paginate($perPage);

            return Response::json([
                'success' => true,
                'message' => "Hàng đợi với trạng thái: {$normalized}",
                'data' => $queue
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy hàng đợi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get processing queue info for a specific rescue request
     *
     * Route: GET yeu-cau-cuu-ho/{id}/hang-doi
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHangDoi($id)
    {
        try {
            $yeuCau = YeuCauCuuHo::with(['hangDoiXuLy'])->findOrFail($id);
            $queue = $yeuCau->hangDoiXuLy;

            if (!$queue) {
                return Response::json([
                    'success' => false,
                    'message' => 'Yêu cầu chưa có trong hàng đợi xử lý'
                ], 404);
            }

            $queue->load(['yeuCau.nguoiDung', 'yeuCau.loaiSuCo']);

            return Response::json([
                'success' => true,
                'message' => 'Thông tin hàng đợi xử lý',
                'data' => $queue
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Yêu cầu cứu hộ không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy hàng đợi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get AI classifications for a rescue request
     *
     * @param int $id_yeu_cau
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPhanLoaiAis($id_yeu_cau)
    {
        try {
            $yeuCau = YeuCauCuuHo::findOrFail($id_yeu_cau);

            $classifications = $yeuCau->phanLoaiAis()
                ->orderBy('do_tin_cay', 'desc')
                ->get();

            return Response::json([
                'success' => true,
                'message' => 'Danh sách phân loại AI',
                'data' => $classifications
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Yêu cầu cứu hộ không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create AI classification for a rescue request
     *
     * @param Request $request
     * @param int $id_yeu_cau
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPhanLoaiAis(Request $request, $id_yeu_cau)
    {
        try {
            $yeuCau = YeuCauCuuHo::findOrFail($id_yeu_cau);

            $validated = $request->validate([
                'so_nguoi' => 'nullable|integer|min:0',
                'ten_loai_su_co' => 'required|string|max:255',
                'muc_tu_bao_cao' => 'nullable|numeric',
                'thoi_gian_cho' => 'nullable|numeric',
                'diem_uu_tien' => 'nullable|numeric',
                'muc_khan_cap' => 'nullable|integer|between:1,5',
                'do_tin_cay' => 'required|numeric|between:0,100',
                'ly_do' => 'nullable|string',
                'model_version' => 'nullable|string'
            ]);

            $validated['id_yeu_cau'] = $id_yeu_cau;
            $classification = PhanLoaiAis::create($validated);

            return Response::json([
                'success' => true,
                'message' => 'Tạo phân loại AI thành công',
                'data' => $classification
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Yêu cầu cứu hộ không tồn tại'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi tạo phân loại: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific AI classification
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPhanLoai($id)
    {
        try {
            $classification = PhanLoaiAis::with('yeuCau.loaiSuCo', 'yeuCau.nguoiDung')
                ->findOrFail($id);

            return Response::json([
                'success' => true,
                'message' => 'Chi tiết phân loại AI',
                'data' => $classification
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Phân loại AI không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rescue team receives a request (TIẾP NHẬN)
     * Route: POST yeu-cau-cuu-ho/rescuer-nhan-yeu-cau
     */
    public function resNhanYeuCau(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_phan_cong' => 'required|integer|exists:phan_cong_cuu_ho,id_phan_cong',
            ]);

            $phanCong = PhanCongCuuHo::with('yeuCau')->findOrFail($validated['id_phan_cong']);

            // Verify assignment belongs to this rescuer team
            $teamId = $request->get('id_doi_cuu_ho');
            if ($teamId && $phanCong->id_doi_cuu_ho != $teamId) {
                return Response::json([
                    'success' => false,
                    'message' => 'Phân công không thuộc về đội của bạn'
                ], 403);
            }

            // Only accept if status is MOI/CHUA_TIEP_NHAN
            $currentStatus = strtoupper(trim($phanCong->trang_thai_nhiem_vu ?? ''));
            if (!in_array($currentStatus, ['MOI', 'CHUA_TIEP_NHAN'])) {
                return Response::json([
                    'success' => false,
                    'message' => 'Nhiệm vụ đã được tiếp nhận hoặc không còn khả dụng'
                ], 422);
            }

            // Update assignment status to DANG_XU_LY
            $phanCong->update(['trang_thai_nhiem_vu' => 'DANG_XU_LY']);

            // Update the rescue request status to DANG_XU_LY (if not already)
            $yeuCau = $phanCong->yeuCau;
            if ($yeuCau && $yeuCau->trang_thai !== 'DA_PHAN_CONG') {
                $yeuCau->update(['trang_thai' => 'DA_PHAN_CONG']);
            } elseif ($yeuCau) {
                $yeuCau->update(['trang_thai' => 'DANG_XU_LY']);
            }

            // Load relationships for response
            $phanCong->load([
                'yeuCau.nguoiDung',
                'yeuCau.loaiSuCo',
                'doiCuuHo',
                'ketQua'
            ]);

            return Response::json([
                'success' => true,
                'message' => 'Đã tiếp nhận nhiệm vụ',
                'data' => $phanCong
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Phân công không tồn tại'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi tiếp nhận: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get heatmap data for visualization
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHeatmapData(Request $request)
    {
        try {
            $minLat = $request->get('min_lat');
            $maxLat = $request->get('max_lat');
            $minLng = $request->get('min_lng');
            $maxLng = $request->get('max_lng');

            $query = YeuCauCuuHo::query();

            if ($minLat && $maxLat) {
                $query->whereBetween('vi_tri_lat', [$minLat, $maxLat]);
            }
            if ($minLng && $maxLng) {
                $query->whereBetween('vi_tri_lng', [$minLng, $maxLng]);
            }

            $heatmapData = $query->select([
                'id_yeu_cau',
                'vi_tri_lat',
                'vi_tri_lng',
                'muc_do_khan_cap',
                'diem_uu_tien',
                'trang_thai'
            ])->get();

            // Generate density data
            $densityData = $heatmapData->map(function ($item) {
                $urgency = $this->urgencyToNumber($item->muc_do_khan_cap);
                $priority = is_numeric($item->diem_uu_tien) ? (float) $item->diem_uu_tien : 0;
                return [
                    'lat' => floatval($item->vi_tri_lat),
                    'lng' => floatval($item->vi_tri_lng),
                    'intensity' => $urgency + $priority / 100,
                    'status' => $item->trang_thai
                ];
            });

            return Response::json([
                'success' => true,
                'message' => 'Dữ liệu heatmap',
                'data' => $densityData
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu heatmap: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search rescue requests with multiple filters
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $query = YeuCauCuuHo::with([
                'nguoiDung',
                'loaiSuCo',
                'hangDoiXuLy'
            ]);

            // Search by keyword in chi_tiet, mo_ta, vi_tri_dia_chi
            if ($request->has('keyword')) {
                $keyword = $request->get('keyword');
                $query->where(function ($q) use ($keyword) {
                    $q->where('chi_tiet', 'like', "%{$keyword}%")
                        ->orWhere('mo_ta', 'like', "%{$keyword}%")
                        ->orWhere('vi_tri_dia_chi', 'like', "%{$keyword}%");
                });
            }

            // Filter by status
            if ($request->has('trang_thai')) {
                $query->where('trang_thai', $request->get('trang_thai'));
            }

            // Filter by urgency
            if ($request->has('muc_do_khan_cap')) {
                $query->where('muc_do_khan_cap', $request->get('muc_do_khan_cap'));
            }

            // Filter by incident type
            if ($request->has('id_loai_su_co')) {
                $query->where('id_loai_su_co', $request->get('id_loai_su_co'));
            }

            // Filter by user
            if ($request->has('id_nguoi_dung')) {
                $query->where('id_nguoi_dung', $request->get('id_nguoi_dung'));
            }

            // Filter by date range
            if ($request->has('from_date') && $request->has('to_date')) {
                $query->whereBetween('created_at', [
                    $request->get('from_date'),
                    $request->get('to_date')
                ]);
            }

            // Filter by priority range
            if ($request->has('min_priority') && $request->has('max_priority')) {
                $query->whereBetween('diem_uu_tien', [
                    $request->get('min_priority'),
                    $request->get('max_priority')
                ]);
            }

            // Filter by affected people
            if ($request->has('min_people') && $request->has('max_people')) {
                $query->whereBetween('so_nguoi_bi_anh_huong', [
                    $request->get('min_people'),
                    $request->get('max_people')
                ]);
            }

            $perPage = $request->get('per_page', 15);
            $sortBy = $request->get('sort_by', 'id_yeu_cau');
            $sortOrder = $request->get('sort_order', 'desc');

            $results = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

            return Response::json([
                'success' => true,
                'message' => 'Kết quả tìm kiếm',
                'data' => $results
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi tìm kiếm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get total number of rescue requests
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalRequests(Request $request)
    {
        try {
            $query = YeuCauCuuHo::query();

            // Filter by date range if provided
            if ($request->has('from_date') && $request->has('to_date')) {
                $query->whereBetween('created_at', [
                    $request->get('from_date'),
                    $request->get('to_date')
                ]);
            }

            $total = $query->count();
            $byStatus = $query->groupBy('trang_thai')->selectRaw('trang_thai, COUNT(*) as count')->get();

            return Response::json([
                'success' => true,
                'message' => 'Tổng số yêu cầu cứu hộ',
                'data' => [
                    'total' => $total,
                    'by_status' => $byStatus
                ]
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get requests count by incident type
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRequestsByType(Request $request)
    {
        try {
            $query = YeuCauCuuHo::query();

            if ($request->has('from_date') && $request->has('to_date')) {
                $query->whereBetween('created_at', [
                    $request->get('from_date'),
                    $request->get('to_date')
                ]);
            }

            $requestsByType = $query->with('loaiSuCo')
                ->groupBy('id_loai_su_co')
                ->selectRaw('id_loai_su_co, COUNT(*) as count')
                ->get()
                ->map(function ($item) {
                    return [
                        'id_loai_su_co' => $item->id_loai_su_co,
                        'type_name' => $item->loaiSuCo->ten_danh_muc ?? 'Unknown',
                        'count' => $item->count
                    ];
                });

            return Response::json([
                'success' => true,
                'message' => 'Số lượng yêu cầu theo loại sự cố',
                'data' => $requestsByType
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get real-time tracking data for a specific rescue request.
     * Includes request details, assigned teams, their member statuses, and timeline.
     *
     * Route: GET api/yeu-cau-cuu-ho/{id}/theo-doi
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function theoDoi($id)
    {
        try {
            $yeuCau = YeuCauCuuHo::with([
                'nguoiDung',
                'loaiSuCo',
                'phanCongs.doiCuuHo.thanhViens',
                'phanCongs.ketQua',
            ])->findOrFail($id);

            $phanCongs = $yeuCau->phanCongs ?? collect();

            // Build timeline based on the rescue request's overall status
            $overallStatus = $this->normalizeTrangThaiYeuCau($yeuCau->trang_thai);
            $timeline = $this->buildTimeline($yeuCau, $phanCongs);

            // Build per-team data
            $teamsData = $phanCongs->map(function ($pc) {
                $team = $pc->doiCuuHo;
                $pcStatus = $this->normalizeTrangThaiNhiemVu($pc->trang_thai_nhiem_vu);

                $members = [];
                if ($team && $team->thanhViens) {
                    foreach ($team->thanhViens as $thanhVien) {
                        $members[] = [
                            'id'           => $thanhVien->id_thanh_vien ?? $thanhVien->id,
                            'ho_ten'       => $thanhVien->ho_ten ?? $thanhVien->name ?? 'N/A',
                            'so_dien_thoai' => $thanhVien->so_dien_thoai ?? '',
                            'trang_thai'   => $thanhVien->trang_thai ?? 'active',
                        ];
                    }
                }

                // Resolve location: use latest position if available
                $viTriLat = $team->vi_tri_lat ?? null;
                $viTriLng = $team->vi_tri_lng ?? null;
                if ($team->viTris && $team->viTris->count() > 0) {
                    $latest = $team->viTris->sortByDesc('id')->first();
                    $viTriLat = $latest->vi_tri_lat ?? $viTriLat;
                    $viTriLng = $latest->vi_tri_lng ?? $viTriLng;
                }

                return [
                    'id_phan_cong'         => $pc->id_phan_cong ?? $pc->id,
                    'id_doi'              => $team ? ($team->id_doi_cuu_ho ?? $team->id) : null,
                    'ten_doi'             => $team ? ($team->ten_co ?? 'Đội không tên') : 'Không xác định',
                    'sdt_hotline'         => $team ? ($team->so_dien_thoai_hotline ?? '') : '',
                    'khu_vuc'            => $team ? ($team->khu_vuc_quan_ly ?? '') : '',
                    'trang_thai_nhiem_vu' => $pcStatus,
                    'thoi_gian_phan_cong' => $pc->created_at ? $pc->created_at->toISOString() : null,
                    'thoi_gian_tiep_nhan' => $pc->thoi_gian_tiep_nhan ?? null,
                    'thanh_viens'         => $members,
                    'vi_tri_lat'          => $viTriLat,
                    'vi_tri_lng'          => $viTriLng,
                    'ket_qua'            => $pc->ketQua ? [
                        'noi_dung'      => $pc->ketQua->noi_dung ?? '',
                        'thoi_gian_hoan_thanh' => $pc->ketQua->thoi_gian_hoan_thanh ?? null,
                    ] : null,
                ];
            });

            // Determine current step in timeline
            $currentStepIndex = $this->getCurrentStepIndex($overallStatus, $phanCongs);

            $result = [
                'id'                          => $yeuCau->id_yeu_cau ?? $yeuCau->id,
                'id_yeu_cau'                  => $yeuCau->id_yeu_cau ?? $yeuCau->id,
                'loai_su_co'                  => $yeuCau->loaiSuCo ? [
                    'id'   => $yeuCau->loaiSuCo->id_loai_su_co ?? $yeuCau->loaiSuCo->id,
                    'ten'  => $yeuCau->loaiSuCo->ten_danh_muc ?? $yeuCau->loaiSuCo->ten_loai_su_co ?? $yeuCau->loaiSuCo->ten_loai ?? 'N/A',
                ] : null,
                'vi_tri_dia_chi'              => $yeuCau->vi_tri_dia_chi ?? '',
                'vi_tri_lat'                  => $yeuCau->vi_tri_lat ? floatval($yeuCau->vi_tri_lat) : null,
                'vi_tri_lng'                  => $yeuCau->vi_tri_lng ? floatval($yeuCau->vi_tri_lng) : null,
                'muc_do_khan_cap'            => $yeuCau->muc_do_khan_cap ?? 'MEDIUM',
                'mo_ta'                      => $yeuCau->mo_ta ?? '',
                'chi_tiet'                   => $yeuCau->chi_tiet ?? '',
                'trang_thai'                 => $overallStatus,
                'thoi_gian_tao'             => $yeuCau->created_at ? $yeuCau->created_at->toISOString() : null,
                'thoi_gian_cap_nhat'         => $yeuCau->updated_at ? $yeuCau->updated_at->toISOString() : null,
                'nguoi_yeu_cau'             => $yeuCau->nguoiDung ? [
                    'id'    => $yeuCau->nguoiDung->id_nguoi_dung ?? $yeuCau->nguoiDung->id,
                    'ho_ten' => $yeuCau->nguoiDung->ho_ten ?? $yeuCau->nguoiDung->name ?? 'N/A',
                    'so_dien_thoai' => $yeuCau->nguoiDung->so_dien_thoai ?? '',
                ] : null,
                'phan_congs'                  => $teamsData,
                'timeline'                   => $timeline,
                'current_step_index'         => $currentStepIndex,
            ];

            return Response::json([
                'success' => true,
                'message' => 'Dữ liệu theo dõi cứu hộ',
                'data'    => $result,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Yêu cầu cứu hộ không tồn tại',
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu theo dõi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get delta updates since a given timestamp.
     * Returns only items that have been created or modified after `since`.
     * Items that have disappeared (completed/cancelled) are flagged with `removed: true`.
     * Optimized for real-time UI sync with minimal bandwidth.
     *
     * Route: GET api/yeu-cau-cuu-ho/theo-doi/thay-doi
     *
     * @param Request $request  ?since=ISO8601 timestamp
     * @return \Illuminate\Http\JsonResponse
     */
    public function theoDoiCapNhat(Request $request)
    {
        try {
            $since = $request->get('since');

            // Full active list (for comparison / initial load fallback)
            $items = YeuCauCuuHo::with([
                'nguoiDung',
                'loaiSuCo',
                'phanCongs.doiCuuHo',
            ])
                ->whereIn('trang_thai', ['DA_PHAN_CONG', 'DANG_XU_LY', 'DA_DEN_HIEN_TRUONG'])
                ->orderBy('updated_at', 'desc')
                ->get();

            $data = $items->map(function ($item) {
                $phanCong = $item->phanCongs->first();
                $team = $phanCong?->doiCuuHo;

                return [
                    'id'                  => $item->id_yeu_cau ?? $item->id,
                    'id_yeu_cau'         => $item->id_yeu_cau ?? $item->id,
                    'loai_su_co'         => $item->loaiSuCo ? ($item->loaiSuCo->ten_danh_muc ?? 'N/A') : 'N/A',
                    'vi_tri_dia_chi'     => $item->vi_tri_dia_chi ?? '',
                    'trang_thai'         => $item->trang_thai,
                    'muc_do_khan_cap'    => $item->muc_do_khan_cap ?? 'MEDIUM',
                    'thoi_gian_tao'      => $item->created_at ? $item->created_at->toISOString() : null,
                    'thoi_gian_cap_nhat' => $item->updated_at ? $item->updated_at->toISOString() : null,
                    'nguoi_yeu_cau'    => $item->nguoiDung ? [
                        'ho_ten'       => $item->nguoiDung->ho_ten ?? $item->nguoiDung->name ?? 'N/A',
                        'so_dien_thoai' => $item->nguoiDung->so_dien_thoai ?? '',
                    ] : null,
                    'doi_cuu_ho'       => $team ? [
                        'id'    => $team->id_doi_cuu_ho ?? $team->id,
                        'ten_co' => $team->ten_co ?? 'N/A',
                        'sdt'   => $team->so_dien_thoai_hotline ?? '',
                    ] : null,
                    'phan_cong_count'   => $item->phanCongs->count(),
                ];
            });

            $serverTime = now()->toISOString();
            $currentIds = $data->pluck('id')->toArray();

            // If caller provided `since`, compute which items disappeared
            $removedIds = [];
            if ($since) {
                $beforeIds = YeuCauCuuHo::with('phanCongs.doiCuuHo')
                    ->whereIn('trang_thai', ['DA_PHAN_CONG', 'DANG_XU_LY', 'DA_DEN_HIEN_TRUONG'])
                    ->where('updated_at', '<', $since)
                    ->pluck('id_yeu_cau')
                    ->toArray();

                // Items that existed before but aren't in the current list → they completed/cancelled
                $currentIdSet = array_flip($currentIds);
                $removedIds = array_values(array_filter($beforeIds, fn($id) => !isset($currentIdSet[$id])));
            }

            return Response::json([
                'success' => true,
                'message' => 'Cập nhật theo dõi',
                'server_time' => $serverTime,
                'items' => $data,
                'removed_ids' => $removedIds,
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy cập nhật: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get rescue requests actively being processed (for the tracking list).
     *
     * Route: GET api/yeu-cau-cuu-ho/theo-doi/danh-sach
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function theoDoiDanhSach()
    {
        try {
            $items = YeuCauCuuHo::with([
                'nguoiDung',
                'loaiSuCo',
                'phanCongs.doiCuuHo',
            ])
                ->whereIn('trang_thai', ['DA_PHAN_CONG', 'DANG_XU_LY', 'DA_DEN_HIEN_TRUONG'])
                ->orderBy('updated_at', 'desc')
                ->get();

            $data = $items->map(function ($item) {
                $phanCong = $item->phanCongs->first();
                $team = $phanCong?->doiCuuHo;

                return [
                    'id'                => $item->id_yeu_cau ?? $item->id,
                    'id_yeu_cau'        => $item->id_yeu_cau ?? $item->id,
                    'loai_su_co'        => $item->loaiSuCo ? ($item->loaiSuCo->ten_danh_muc ?? 'N/A') : 'N/A',
                    'vi_tri_dia_chi'    => $item->vi_tri_dia_chi ?? '',
                    'trang_thai'        => $item->trang_thai,
                    'muc_do_khan_cap'   => $item->muc_do_khan_cap ?? 'MEDIUM',
                    'thoi_gian_tao'     => $item->created_at ? $item->created_at->toISOString() : null,
                    'thoi_gian_cap_nhat' => $item->updated_at ? $item->updated_at->toISOString() : null,
                    'nguoi_yeu_cau'    => $item->nguoiDung ? [
                        'ho_ten'       => $item->nguoiDung->ho_ten ?? $item->nguoiDung->name ?? 'N/A',
                        'so_dien_thoai' => $item->nguoiDung->so_dien_thoai ?? '',
                    ] : null,
                    'doi_cuu_ho'       => $team ? [
                        'id'    => $team->id_doi_cuu_ho ?? $team->id,
                        'ten_co' => $team->ten_co ?? 'N/A',
                        'sdt'   => $team->so_dien_thoai_hotline ?? '',
                    ] : null,
                    'phan_cong_count'   => $item->phanCongs->count(),
                ];
            });

            return Response::json([
                'success' => true,
                'message' => 'Danh sách yêu cầu đang theo dõi',
                'data'    => $data,
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách theo dõi: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function normalizeTrangThaiNhiemVu(?string $value): string
    {
        if ($value === null) return 'MOI';
        $v = strtoupper(trim($value));
        return match ($v) {
            'MOI'                => 'MOI',
            'DANG_XU_LY'        => 'DANG_XU_LY',
            'DA_DEN_HIEN_TRUONG' => 'DA_DEN_HIEN_TRUONG',
            'HOAN_THANH'        => 'HOAN_THANH',
            'HUY_BO'            => 'HUY_BO',
            default              => $value,
        };
    }

    private function buildTimeline($yeuCau, $phanCongs)
    {
        $createdAt = $yeuCau->created_at;
        $pc = $phanCongs->first();
        $pcCreatedAt = $pc?->created_at;
        $pcAcceptedAt = isset($pc->thoi_gian_tiep_nhan) ? new \DateTime($pc->thoi_gian_tiep_nhan) : null;
        $pcArriveAt = null;
        $pcCompleteAt = null;

        $pcStatus = $this->normalizeTrangThaiNhiemVu($pc?->trang_thai_nhiem_vu);

        if ($pcStatus === 'DA_DEN_HIEN_TRUONG' || $yeuCau->trang_thai === 'DA_DEN_HIEN_TRUONG') {
            $pcArriveAt = $pc?->updated_at;
        }
        if ($pcStatus === 'HOAN_THANH' || $yeuCau->trang_thai === 'HOAN_THANH') {
            $pcCompleteAt = $pc?->ketQua?->thoi_gian_hoan_thanh
                ? new \DateTime($pc->ketQua->thoi_gian_hoan_thanh)
                : $pc?->updated_at;
        }

        $f = fn($dt) => $dt ? $dt->format('H:i - d/m/Y') : '';

        return [
            ['title' => 'Tiếp nhận yêu cầu',   'time' => $f($createdAt),             'status' => 'done'],
            ['title' => 'Đã phân công cứu hộ',  'time' => $f($pcCreatedAt),           'status' => 'done'],
            ['title' => 'Đang di chuyển',       'time' => $pcAcceptedAt ? $f($pcAcceptedAt) : '',   'status' => 'current'],
            ['title' => 'Đã tới hiện trường',    'time' => $pcArriveAt ? $f($pcArriveAt) : '',      'status' => 'pending'],
            ['title' => 'Hoàn thành sự cố',      'time' => $pcCompleteAt ? $f($pcCompleteAt) : '',   'status' => 'pending'],
        ];
    }

    private function getCurrentStepIndex(string $overallStatus, $phanCongs)
    {
        $pcStatus = $this->normalizeTrangThaiNhiemVu($phanCongs->first()?->trang_thai_nhiem_vu);

        if ($overallStatus === 'HOAN_THANH' || $pcStatus === 'HOAN_THANH') return 4;
        if ($overallStatus === 'DA_DEN_HIEN_TRUONG' || $pcStatus === 'DA_DEN_HIEN_TRUONG') return 3;
        if ($pcStatus === 'DANG_XU_LY' || $overallStatus === 'DANG_XU_LY') return 2;
        if ($pcStatus === 'MOI' || $overallStatus === 'DA_PHAN_CONG') return 1;
        return 0;
    }

    /**
     * Get requests count by urgency level
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRequestsByUrgency(Request $request)
    {
        try {
            $query = YeuCauCuuHo::query();

            if ($request->has('from_date') && $request->has('to_date')) {
                $query->whereBetween('created_at', [
                    $request->get('from_date'),
                    $request->get('to_date')
                ]);
            }

            $requestsByUrgency = $query->groupBy('muc_do_khan_cap')
                ->selectRaw('muc_do_khan_cap, COUNT(*) as count')
                ->orderBy('muc_do_khan_cap')
                ->get();

            return Response::json([
                'success' => true,
                'message' => 'Số lượng yêu cầu theo mức độ khan cấp',
                'data' => $requestsByUrgency
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get processing status statistics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProcessingStatus(Request $request)
    {
        try {
            $baseQuery = YeuCauCuuHo::query();

            if ($request->has('from_date') && $request->has('to_date')) {
                $baseQuery->whereBetween('created_at', [
                    $request->get('from_date'),
                    $request->get('to_date')
                ]);
            }

            $total = (clone $baseQuery)->count();
            $new = (clone $baseQuery)->where('trang_thai', 'CHO_XU_LY')->count();
            $processing = (clone $baseQuery)->where('trang_thai', 'DANG_XU_LY')->count();
            $completed = (clone $baseQuery)->where('trang_thai', 'HOAN_THANH')->count();
            $cancelled = (clone $baseQuery)->where('trang_thai', 'HUY_BO')->count();

            $urgencies = (clone $baseQuery)->pluck('muc_do_khan_cap');
            $urgencyAvg = $urgencies->count() > 0
                ? $urgencies->map(fn($v) => $this->urgencyToNumber($v))->avg()
                : 0;
            $avgAffectedPeople = (clone $baseQuery)->avg('so_nguoi_bi_anh_huong') ?? 0;
            $totalAffectedPeople = (clone $baseQuery)->sum('so_nguoi_bi_anh_huong') ?? 0;

            return Response::json([
                'success' => true,
                'message' => 'Thống kê trạng thái xử lý',
                'data' => [
                    'total' => $total,
                    'new' => $new,
                    'processing' => $processing,
                    'completed' => $completed,
                    'cancelled' => $cancelled,
                    'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
                    'avg_urgency' => round($urgencyAvg, 2),
                    'avg_affected_people' => round($avgAffectedPeople, 2),
                    'total_affected_people' => $totalAffectedPeople
                ]
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard statistics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardStats(Request $request)
    {
        try {
            $totalRequestsToday = YeuCauCuuHo::whereDate('created_at', now()->toDateString())->count();
            $totalRequests = YeuCauCuuHo::count();
            $choXuLy = YeuCauCuuHo::where('trang_thai', 'CHO_XU_LY')->count();
            $dangXuLy = YeuCauCuuHo::where('trang_thai', 'DANG_XU_LY')->count();
            $hoanThanh = YeuCauCuuHo::where('trang_thai', 'HOAN_THANH')
                ->whereDate('created_at', now()->toDateString())
                ->count();
            $huyBo = YeuCauCuuHo::where('trang_thai', 'HUY_BO')->count();
            $criticalCount = YeuCauCuuHo::where('muc_do_khan_cap', 'CRITICAL')
                ->whereNotIn('trang_thai', ['HOAN_THANH', 'HUY_BO'])
                ->count();

            $avgWaitMinutes = YeuCauCuuHo::where('trang_thai', 'CHO_XU_LY')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, NOW())) as avg_wait')
                ->value('avg_wait') ?? 0;

            return Response::json([
                'success' => true,
                'data' => [
                    'total_requests_today' => $totalRequestsToday,
                    'total_requests' => $totalRequests,
                    'cho_xu_ly' => $choXuLy,
                    'dang_xu_ly' => $dangXuLy,
                    'hoan_thanh' => $hoanThanh,
                    'huy_bo' => $huyBo,
                    'critical_count' => $criticalCount,
                    'avg_wait_minutes' => round((float) $avgWaitMinutes, 2)
                ]
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu dashboard: ' . $e->getMessage()
            ], 500);
        }
    }
}
