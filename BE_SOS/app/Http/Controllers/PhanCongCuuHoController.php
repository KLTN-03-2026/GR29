<?php

namespace App\Http\Controllers;

use App\Events\RescueRequestUpdated;
use App\Events\RescuerLocationUpdated;
use App\Models\{PhanCongCuuHo, DoiCuuHo};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PhanCongCuuHoController extends Controller
{
    private function safeBroadcastRescueUpdate($yeuCau, string $action): void
    {
        try {
            event(new RescueRequestUpdated($yeuCau, $action));
        } catch (\Throwable $exception) {
            Log::warning('[broadcast] Failed to send RescueRequestUpdated', [
                'action' => $action,
                'request_id' => $yeuCau->id_yeu_cau ?? $yeuCau->id ?? null,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Append nested relations as root-level properties for frontend compatibility.
     * Maps:
     *   yeu_cau.nguoi_dung  -> nguoi_dung
     *   yeu_cau.baoCao      -> bao_cao
     *   yeu_cau.loaiSuCo    -> loai_su_co (via yeu_cau)
     */
    private function appendNestedRelations($item)
    {
        if ($item->yeuCau) {
            if ($item->yeuCau->nguoiDung) {
                $item->nguoi_dung = $item->yeuCau->nguoiDung;
            }
            if ($item->yeuCau->baoCao) {
                $item->bao_cao = $item->yeuCau->baoCao;
            }
        }
        return $item;
    }

    private function transformCollection($collection)
    {
        return $collection->map(fn($item) => $this->appendNestedRelations($item));
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $items = PhanCongCuuHo::with([
            'yeuCau.nguoiDung',
            'yeuCau.baoCao',
            'yeuCau.loaiSuCo',
            'doiCuuHo',
            'ketQua'
        ])->paginate($perPage);
        $items->setCollection($this->transformCollection($items->getCollection()));
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_yeu_cau' => 'required|integer|exists:yeu_cau_cuu_ho,id_yeu_cau',
            'id_doi_cuu_ho' => 'required|integer|exists:doi_cuu_ho,id_doi_cuu_ho',
            'id_chi_tiet_su_co' => 'nullable|integer|exists:chi_tiet_loai_su_co,id_chi_tiet',
            'mo_ta' => 'nullable|string',
            'nguoi_dieu_phoi' => 'nullable|string|max:255',
            'thoi_gian_phan_cong' => 'nullable|date',
            'trang_thai_nhiem_vu' => 'nullable|string|max:20'
        ]);

        $validated['trang_thai_nhiem_vu'] = $validated['trang_thai_nhiem_vu'] ?? 'MOI';
        $item = PhanCongCuuHo::create($validated);
        $item->load([
            'yeuCau.nguoiDung',
            'yeuCau.baoCao',
            'yeuCau.loaiSuCo',
            'doiCuuHo',
            'ketQua',
            'yeuCau.phanCongs.doiCuuHo',
        ]);
        $this->appendNestedRelations($item);

        // Broadcast to all connected clients that a rescue team was assigned
        if ($item->yeuCau) {
            $this->safeBroadcastRescueUpdate($item->yeuCau, 'assigned');
        }

        return response()->json($item, 201);
    }

    public function show($id)
    {
        $item = PhanCongCuuHo::with([
            'yeuCau.nguoiDung',
            'yeuCau.baoCao',
            'yeuCau.loaiSuCo',
            'doiCuuHo',
            'ketQua'
        ])->findOrFail($id);
        $this->appendNestedRelations($item);
        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = PhanCongCuuHo::findOrFail($id);
        $validated = $request->validate([
            'id_yeu_cau' => 'nullable|integer|exists:yeu_cau_cuu_ho,id_yeu_cau',
            'id_doi_cuu_ho' => 'nullable|integer|exists:doi_cuu_ho,id_doi_cuu_ho',
            'id_chi_tiet_su_co' => 'nullable|integer|exists:chi_tiet_loai_su_co,id_chi_tiet',
            'mo_ta' => 'nullable|string',
            'nguoi_dieu_phoi' => 'nullable|string|max:255',
            'thoi_gian_phan_cong' => 'nullable|date',
            'trang_thai_nhiem_vu' => 'nullable|string|max:20'
        ]);
        $item->update($validated);
        $item->load([
            'yeuCau.nguoiDung',
            'yeuCau.baoCao',
            'yeuCau.loaiSuCo',
            'doiCuuHo',
            'ketQua',
            'yeuCau.phanCongs.doiCuuHo',
        ]);
        $this->appendNestedRelations($item);
        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = PhanCongCuuHo::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }

    /**
     * Update assignment status
     * Route: PUT phan-cong-cuu-ho/{id}/trang-thai
     */
    public function updateStatus(Request $request, $id)
    {
        $item = PhanCongCuuHo::with('yeuCau')->findOrFail($id);
        $validated = $request->validate([
            'trang_thai_nhiem_vu' => 'required|string|max:20'
        ]);

        $newStatus = strtoupper(trim($validated['trang_thai_nhiem_vu']));

        // BUSINESS RULE: prevent rescuer from accepting a second active request
        // Only allow if transitioning from MOI/CHUA_TIEP_NHAN to DANG_XU_LY
        $currentStatus = strtoupper(trim($item->trang_thai_nhiem_vu ?? ''));
        if ($newStatus === 'DANG_XU_LY' && in_array($currentStatus, ['MOI', 'CHUA_TIEP_NHAN'])) {
            $teamId = $item->id_doi_cuu_ho;

            // Check if this team already has another active assignment
            $hasActiveRequest = PhanCongCuuHo::where('id_doi_cuu_ho', $teamId)
                ->whereIn('trang_thai_nhiem_vu', ['DANG_XU_LY', 'DA_DEN_HIEN_TRUONG'])
                ->where('id_phan_cong', '!=', $item->id_phan_cong)
                ->exists();

            if ($hasActiveRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đang có yêu cầu cần xử lí, hãy hoàn thành'
                ], 400);
            }
        }

        $item->update(['trang_thai_nhiem_vu' => $newStatus]);

        // When a team accepts (DANG_XU_LY), cancel all other pending assignments for the same request
        if ($newStatus === 'DANG_XU_LY') {
            PhanCongCuuHo::where('id_yeu_cau', $item->id_yeu_cau)
                ->where('id_phan_cong', '!=', $item->id_phan_cong)
                ->whereIn('trang_thai_nhiem_vu', ['MOI', 'CHUA_TIEP_NHAN', 'DA_PHAN_CONG'])
                ->update(['trang_thai_nhiem_vu' => 'HUY_BO']);
        }

        // Sync yeu_cau status based on assignment status transitions
        $yeuCau = $item->yeuCau;
        if ($yeuCau) {
            $statusMap = [
                'DANG_XU_LY' => 'DANG_XU_LY',
                'DA_DEN_HIEN_TRUONG' => 'DA_DEN_HIEN_TRUONG',
                'HOAN_THANH' => 'HOAN_THANH',
                'THAT_BAI' => 'THAT_BAI',
            ];
            if (isset($statusMap[$newStatus])) {
                $yeuCau->update(['trang_thai' => $statusMap[$newStatus]]);
            }
        }

        // Issue #3 fix: when assignment is completed (HOAN_THANH) or failed (THAT_BAI),
        // release the team so it becomes available again.
        if (in_array($newStatus, ['HOAN_THANH', 'THAT_BAI'], true)) {
            $this->releaseTeamAfterCompletion($item->id_doi_cuu_ho);
        }

        $item->load([
            'yeuCau.nguoiDung',
            'yeuCau.baoCao',
            'yeuCau.loaiSuCo',
            'doiCuuHo',
            'ketQua',
            'yeuCau.phanCongs.doiCuuHo',
        ]);
        $this->appendNestedRelations($item);

        // Broadcast rescue request status update to all connected clients
        if ($item->yeuCau) {
            $this->safeBroadcastRescueUpdate($item->yeuCau, 'assignment_updated');
        }

        return response()->json($item);
    }

    /**
     * Release team after assignment completion.
     * Only sets team back to SAN_SANG if all its active assignments are finished.
     */
    private function releaseTeamAfterCompletion(int $teamId)
    {
        // Count remaining active assignments for this team (excluding the one we just completed)
        // Active = any status NOT in the terminal/completed statuses
        // Terminal: MOI (mới phân công, chưa tiếp nhận), HOAN_THANH, THAT_BAI, HUY_BO
        // Active: DANG_XU_LY, DA_PHAN_CONG, DA_DEN_HIEN_TRUONG
        $activeCount = PhanCongCuuHo::where('id_doi_cuu_ho', $teamId)
            ->whereNotIn('trang_thai_nhiem_vu', ['MOI', 'HOAN_THANH', 'THAT_BAI', 'HUY_BO'])
            ->count();

        // If no more active assignments, mark team as available
        if ($activeCount === 0) {
            DoiCuuHo::where('id_doi_cuu_ho', $teamId)
                ->update(['trang_thai' => 'SAN_SANG']);
        }
    }

    /**
     * Check if rescuer team already has an active assignment.
     * Used by frontend to disable "Tiếp nhận" buttons when already handling a request.
     *
     * Route: GET phan-cong-cuu-ho/active/{teamId}
     */
    public function getActiveAssignment($teamId)
    {
        $active = PhanCongCuuHo::with([
            'yeuCau.nguoiDung',
            'yeuCau.loaiSuCo',
            'doiCuuHo',
            'ketQua'
        ])
            ->where('id_doi_cuu_ho', $teamId)
            ->whereIn('trang_thai_nhiem_vu', ['DANG_XU_LY', 'DA_DEN_HIEN_TRUONG'])
            ->first();

        return response()->json([
            'success' => true,
            'has_active' => $active !== null,
            'active' => $active
        ]);
    }

    /**
     * Get assignments by rescue request
     * Route: GET phan-cong-cuu-ho/theo-yeu-cau/{id_yeu_cau}
     */
    public function getByYeuCau(Request $request, $id_yeu_cau)
    {
        $perPage = $request->get('per_page', 15);
        $items = PhanCongCuuHo::with([
            'yeuCau.nguoiDung',
            'yeuCau.baoCao',
            'yeuCau.loaiSuCo',
            'doiCuuHo',
            'ketQua'
        ])
            ->where('id_yeu_cau', $id_yeu_cau)
            ->paginate($perPage);
        $items->setCollection($this->transformCollection($items->getCollection()));
        return response()->json($items);
    }

    /**
     * Get assignments by team
     * Route: GET phan-cong-cuu-ho/theo-doi/{id_doi_cuu_ho}
     */
    public function getByDoi(Request $request, $id_doi_cuu_ho)
    {
        $perPage = $request->get('per_page', 15);
        $items = PhanCongCuuHo::with([
            'yeuCau.nguoiDung',
            'yeuCau.baoCao',
            'yeuCau.loaiSuCo',
            'doiCuuHo',
            'ketQua'
        ])
            ->where('id_doi_cuu_ho', $id_doi_cuu_ho)
            ->paginate($perPage);
        $items->setCollection($this->transformCollection($items->getCollection()));
        return response()->json($items);
    }

    /**
     * Get suggested teams for assignment to a rescue request
     * Route: GET admin/assignments/suggested/{id_yeu_cau}
     */
    public function getSuggestedTeamsForRequest(Request $request, $id_yeu_cau)
    {
        try {
            // Call the timDoiGanNhat method from YeuCauCuuHoController
            $yeuCauController = app(\App\Http\Controllers\YeuCauCuuHoController::class);
            $response = $yeuCauController->timDoiGanNhat(new Request([
                'id_yeu_cau' => (int) $id_yeu_cau,
                'id_loai_su_co' => $request->id_loai_su_co,
            ]));

            // Extract the teams data from the response
            $data = json_decode($response->getContent(), true);
            $teams = $data['teams'] ?? [];

            return response()->json([
                'success' => true,
                'teams' => $teams,
            ]);
        } catch (\Throwable $e) {
            Log::error('[getSuggestedTeamsForRequest] Lỗi: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách đội gợi ý: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update rescuer team location for real-time tracking
     * Route: POST phan-cong-cuu-ho/{id}/location
     */
    public function updateLocation(Request $request, $id)
    {
        $assignment = PhanCongCuuHo::findOrFail($id);

        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $teamId = $assignment->id_doi_cuu_ho;

        // Broadcast location update to all clients tracking this team
        try {
            event(new RescuerLocationUpdated($teamId, $validated['lat'], $validated['lng']));
        } catch (\Throwable $exception) {
            Log::warning('[broadcast] Failed to send RescuerLocationUpdated', [
                'team_id' => $teamId,
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully'
        ]);
    }
}
