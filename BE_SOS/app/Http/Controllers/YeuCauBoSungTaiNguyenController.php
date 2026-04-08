<?php

namespace App\Http\Controllers;

use App\Models\YeuCauBoSungTaiNguyen;
use Illuminate\Http\Request;

class YeuCauBoSungTaiNguyenController extends Controller
{
    /**
     * UC20 — Admin: danh sách yêu cầu bổ sung tài nguyên (lọc theo trang_thai).
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 15), 100);
        $q = YeuCauBoSungTaiNguyen::with([
            'doiCuuHo',
            'phanCong.yeuCau',
            'yeuCau',
        ]);

        $tt = $request->get('trang_thai');
        if ($tt) {
            $q->where('trang_thai', strtoupper(trim((string) $tt)));
        }

        $items = $q->orderByDesc('created_at')->paginate($perPage);

        return response()->json($items);
    }

    /**
     * UC20 — Rescuer: gửi yêu cầu bổ sung tài nguyên khi đang nhiệm vụ.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_doi_cuu_ho' => 'required|integer|exists:doi_cuu_ho,id_doi_cuu_ho',
            'id_phan_cong' => 'nullable|integer|exists:phan_cong_cuu_ho,id_phan_cong',
            'id_yeu_cau' => 'nullable|integer|exists:yeu_cau_cuu_ho,id_yeu_cau',
            'noi_dung' => 'required|string|max:5000',
            'muc_do_khan_cap' => 'nullable|string|in:LOW,MEDIUM,HIGH,CRITICAL',
        ]);

        $validated['muc_do_khan_cap'] = $validated['muc_do_khan_cap'] ?? 'MEDIUM';
        $validated['trang_thai'] = 'CHO_XU_LY';

        $item = YeuCauBoSungTaiNguyen::create($validated);
        $item->load(['doiCuuHo', 'phanCong', 'yeuCau']);

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi yêu cầu bổ sung tài nguyên.',
            'data' => $item,
        ], 201);
    }

    /**
     * UC20 — Admin: cập nhật trạng thái / ghi chú điều phối.
     */
    public function update(Request $request, $id)
    {
        $item = YeuCauBoSungTaiNguyen::findOrFail($id);

        $validated = $request->validate([
            'trang_thai' => 'sometimes|string|in:CHO_XU_LY,DANG_XU_LY,HOAN_THANH,TU_CHOI',
            'ghi_chu_dieu_phoi' => 'nullable|string|max:2000',
        ]);

        $item->update($validated);
        $item->load(['doiCuuHo', 'phanCong', 'yeuCau']);

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật yêu cầu.',
            'data' => $item,
        ]);
    }
}
