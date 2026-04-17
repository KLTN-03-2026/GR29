<?php

namespace App\Http\Controllers;

use App\Models\{PhanCongCuuHo, DoiCuuHo};
use Illuminate\Http\Request;

class PhanCongCuuHoController extends Controller
{
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
            'ketQua'
        ]);
        $this->appendNestedRelations($item);
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
            'thoi_gian_phan_cong' => 'nullable|date',
            'trang_thai_nhiem_vu' => 'nullable|string|max:20'
        ]);
        $item->update($validated);
        $item->load([
            'yeuCau.nguoiDung',
            'yeuCau.baoCao',
            'yeuCau.loaiSuCo',
            'doiCuuHo',
            'ketQua'
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
        $item->update(['trang_thai_nhiem_vu' => $newStatus]);

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
            'ketQua'
        ]);
        $this->appendNestedRelations($item);
        return response()->json($item);
    }

    /**
     * Release team after assignment completion.
     * Only sets team back to SAN_SANG if all its active assignments are finished.
     */
    private function releaseTeamAfterCompletion(int $teamId)
    {
        // Count remaining active assignments for this team (excluding the one we just completed)
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
     * Get assignments by status
     * Route: GET phan-cong-cuu-ho/theo-trang-thai/{trang_thai}
     */
    public function getByStatus(Request $request, $trang_thai)
    {
        $perPage = $request->get('per_page', 15);
        $normalized = strtoupper(trim((string) $trang_thai));
        $items = PhanCongCuuHo::with([
            'yeuCau.nguoiDung',
            'yeuCau.baoCao',
            'yeuCau.loaiSuCo',
            'doiCuuHo',
            'ketQua'
        ])
            ->where('trang_thai_nhiem_vu', $normalized)
            ->paginate($perPage);
        $items->setCollection($this->transformCollection($items->getCollection()));
        return response()->json($items);
    }
}