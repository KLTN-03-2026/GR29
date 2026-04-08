<?php

namespace App\Http\Controllers;

use App\Models\{PhanCongCuuHo};
use Illuminate\Http\Request;

class PhanCongCuuHoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $items = PhanCongCuuHo::with(['yeuCau', 'doiCuuHo', 'ketQua'])->paginate($perPage);
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
        $item = PhanCongCuuHo::create($validated);
        $item->load(['yeuCau', 'doiCuuHo', 'ketQua']);
        return response()->json($item, 201);
    }

    public function show($id)
    {
        $item = PhanCongCuuHo::with(['yeuCau', 'doiCuuHo', 'ketQua'])->findOrFail($id);
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
        $item->load(['yeuCau', 'doiCuuHo', 'ketQua']);
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
        $item = PhanCongCuuHo::findOrFail($id);
        $validated = $request->validate([
            'trang_thai_nhiem_vu' => 'required|string|max:20'
        ]);

        $newStatus = strtoupper(trim($validated['trang_thai_nhiem_vu']));

        if ($newStatus === 'DANG_XU_LY') {
            $otherActive = PhanCongCuuHo::where('id_doi_cuu_ho', $item->id_doi_cuu_ho)
                ->where('trang_thai_nhiem_vu', 'DANG_XU_LY')
                ->where('id_phan_cong', '!=', $item->id_phan_cong)
                ->exists();
            if ($otherActive) {
                return response()->json([
                    'message' => 'Đội đã có nhiệm vụ đang xử lý. Hoàn thành nhiệm vụ đó trước khi tiếp nhận thêm.',
                ], 422);
            }
        }

        $item->update(['trang_thai_nhiem_vu' => $newStatus]);
        $item->load(['yeuCau.nguoiDung', 'yeuCau.loaiSuCo', 'doiCuuHo', 'ketQua']);
        return response()->json($item);
    }

    /**
     * Get assignments by rescue request
     * Route: GET phan-cong-cuu-ho/theo-yeu-cau/{id_yeu_cau}
     */
    public function getByYeuCau(Request $request, $id_yeu_cau)
    {
        $perPage = $request->get('per_page', 15);
        $items = PhanCongCuuHo::with(['yeuCau', 'doiCuuHo', 'ketQua'])
            ->where('id_yeu_cau', $id_yeu_cau)
            ->paginate($perPage);
        return response()->json($items);
    }

    /**
     * Get assignments by team
     * Route: GET phan-cong-cuu-ho/theo-doi/{id_doi_cuu_ho}
     */
    public function getByDoi(Request $request, $id_doi_cuu_ho)
    {
        $perPage = $request->get('per_page', 15);
        $items = PhanCongCuuHo::with(['yeuCau.nguoiDung', 'yeuCau.loaiSuCo', 'doiCuuHo', 'ketQua'])
            ->where('id_doi_cuu_ho', $id_doi_cuu_ho)
            ->paginate($perPage);
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
        $items = PhanCongCuuHo::with(['yeuCau', 'doiCuuHo', 'ketQua'])
            ->where('trang_thai_nhiem_vu', $normalized)
            ->paginate($perPage);
        return response()->json($items);
    }
}