<?php

namespace App\Http\Controllers;

use App\Models\RescueReport;
use App\Models\YeuCauCuuHo;
use App\Models\DoiCuuHo;
use App\Models\PhanCongCuuHo;
use App\Models\ThanhVienDoi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class BaoCaoCuuHoController extends Controller
{
    /**
     * Submit a rescue report
     * Route: POST rescuer/gui-bao-cao
     *
     * Validates the report based on result type:
     * - THAT_BAI: requires reason (ly_do_that_bai) AND image (hinh_anh)
     * - HOAN_THANH: requires image (hinh_anh)
     *
     * Updates:
     * - Rescue request status (HOAN_THANH or THAT_BAI)
     * - Assignment status (HOAN_THANH or THAT_BAI)
     * - Releases team members (marks them available again)
     */
    public function guiBaoCao(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_phan_cong' => 'required|integer|exists:phan_cong_cuu_ho,id_phan_cong',
                'ket_qua' => 'required|string|in:HOAN_THANH,THAT_BAI',
                'ly_do_that_bai' => 'nullable|string|max:1000',
                'hinh_anh' => 'nullable|file|image|max:10240',
                'bao_cao_hien_truong' => 'nullable|string|max:2000',
            ]);

            $phanCong = PhanCongCuuHo::with(['yeuCau', 'doiCuuHo'])->findOrFail($validated['id_phan_cong']);

            // Verify assignment belongs to this rescuer team
            $teamId = $request->get('id_doi_cuu_ho');
            if ($teamId && $phanCong->id_doi_cuu_ho != $teamId) {
                return Response::json([
                    'success' => false,
                    'message' => 'Phân công không thuộc về đội của bạn'
                ], 403);
            }

            // Only accept if assignment is in progress or arrived
            $currentStatus = strtoupper(trim($phanCong->trang_thai_nhiem_vu ?? ''));
            $allowedStatuses = ['DANG_XU_LY', 'DA_DEN_HIEN_TRUONG'];
            if (!in_array($currentStatus, $allowedStatuses)) {
                return Response::json([
                    'success' => false,
                    'message' => 'Nhiệm vụ không ở trạng thái có thể báo cáo'
                ], 422);
            }

            $ketQua = $validated['ket_qua'];

            // Validation: THAT_BAI requires reason AND image
            if ($ketQua === 'THAT_BAI') {
                if (empty($validated['ly_do_that_bai']) && empty($validated['bao_cao_hien_truong'])) {
                    return Response::json([
                        'success' => false,
                        'message' => 'Lý do thất bại là bắt buộc khi kết quả là THẤT BẠI'
                    ], 422);
                }
                if (!$request->hasFile('hinh_anh')) {
                    return Response::json([
                        'success' => false,
                        'message' => 'Hình ảnh minh chứng là bắt buộc khi kết quả là THẤT BẠI'
                    ], 422);
                }
            }

            // Validation: HOAN_THANH requires image
            if ($ketQua === 'HOAN_THANH') {
                if (!$request->hasFile('hinh_anh')) {
                    return Response::json([
                        'success' => false,
                        'message' => 'Hình ảnh minh chứng là bắt buộc khi kết quả là HOÀN THÀNH'
                    ], 422);
                }
            }

            // Handle file upload
            $hinhAnhPath = null;
            if ($request->hasFile('hinh_anh')) {
                $file = $request->file('hinh_anh');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/hinh_anh'), $filename);
                $hinhAnhPath = 'uploads/hinh_anh/' . $filename;
            }

            // Use bao_cao_hien_truong as reason if ly_do_that_bai is not set
            $lyDo = $validated['ly_do_that_bai'] ?? $validated['bao_cao_hien_truong'] ?? null;

            // Create the rescue report
            $report = RescueReport::create([
                'id_yeu_cau' => $phanCong->id_yeu_cau,
                'id_doi_cuu_ho' => $phanCong->id_doi_cuu_ho,
                'ket_qua' => $ketQua,
                'ly_do_that_bai' => $ketQua === 'THAT_BAI' ? $lyDo : null,
                'hinh_anh' => $hinhAnhPath,
                'mo_ta_hien_truong' => $validated['bao_cao_hien_truong'] ?? null,
            ]);

            // Update assignment status to match result
            $phanCong->update(['trang_thai_nhiem_vu' => $ketQua]);

            // Update rescue request status
            $yeuCau = $phanCong->yeuCau;
            if ($yeuCau) {
                $yeuCau->update(['trang_thai' => $ketQua]);

                // Update processing queue - remove from queue when completed/failed
                if ($yeuCau->hangDoiXuLy) {
                    $yeuCau->hangDoiXuLy->update(['trang_thai' => 'DONE']);
                }
            }

            // Release team members: mark them as available again
            $team = $phanCong->doiCuuHo;
            if ($team) {
                // Update team status back to SAN_SANG (available)
                $team->update(['trang_thai' => 'SAN_SANG']);

                // Release all members from this assignment
                $this->releaseTeamMembers($phanCong->id_doi_cuu_ho);
            }

            $report->load(['yeuCau.nguoiDung', 'doiCuuHo']);

            return Response::json([
                'success' => true,
                'message' => $ketQua === 'HOAN_THANH'
                    ? 'Báo cáo hoàn thành nhiệm vụ thành công'
                    : 'Báo cáo thất bại đã được ghi nhận',
                'data' => $report
            ], 201);
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
                'message' => 'Lỗi khi gửi báo cáo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Release team members when assignment is completed
     * This marks members as available so the team can take new assignments
     */
    private function releaseTeamMembers(int $teamId)
    {
        // Update team status to available
        DoiCuuHo::where('id_doi_cuu_ho', $teamId)->update(['trang_thai' => 'SAN_SANG']);
    }

    /**
     * Get reports by rescue request
     * Route: GET rescuer/bao-cao/theo-yeu-cau/{id_yeu_cau}
     */
    public function getByYeuCau(int $id_yeu_cau)
    {
        try {
            $reports = RescueReport::with(['doiCuuHo'])
                ->where('id_yeu_cau', $id_yeu_cau)
                ->orderBy('created_at', 'desc')
                ->get();

            return Response::json([
                'success' => true,
                'data' => $reports
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy báo cáo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reports by team
     * Route: GET rescuer/bao-cao/theo-doi/{id_doi_cuu_ho}
     */
    public function getByDoi(int $id_doi_cuu_ho)
    {
        try {
            $reports = RescueReport::with(['yeuCau'])
                ->where('id_doi_cuu_ho', $id_doi_cuu_ho)
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return Response::json([
                'success' => true,
                'data' => $reports
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy báo cáo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific report
     * Route: GET rescuer/bao-cao/{id}
     */
    public function show(int $id)
    {
        try {
            $report = RescueReport::with(['yeuCau', 'doiCuuHo'])->findOrFail($id);

            return Response::json([
                'success' => true,
                'data' => $report
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Báo cáo không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi lấy báo cáo: ' . $e->getMessage()
            ], 500);
        }
    }
}
