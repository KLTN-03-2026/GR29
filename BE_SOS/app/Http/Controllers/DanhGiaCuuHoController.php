<?php

namespace App\Http\Controllers;

use App\Models\{DanhGiaCuuHo, YeuCauCuuHo};
use Illuminate\Http\Request;

class DanhGiaCuuHoController extends Controller
{
    public function index()
    {
        $items = DanhGiaCuuHo::with(['yeuCau', 'nguoiDung'])->paginate(15);
        return response()->json([
            'status' => true,
            'data' => $items->items(),
            'pagination' => [
                'total' => $items->total(),
                'per_page' => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_yeu_cau' => 'required|numeric',
            'id_nguoi_dung' => 'required|numeric',
            'diem_danh_gia' => 'required|numeric|between:1,5',
            'noi_dung_danh_gia' => 'nullable|string',
        ]);

        $item = DanhGiaCuuHo::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Tạo đánh giá thành công',
            'data' => $item
        ], 201);
    }

    public function show($id)
    {
        $item = DanhGiaCuuHo::with(['yeuCau', 'nguoiDung'])->findOrFail($id);
        return response()->json([
            'status' => true,
            'data' => $item
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = DanhGiaCuuHo::findOrFail($id);
        $validated = $request->validate([
            'diem_danh_gia' => 'sometimes|numeric|between:1,5',
            'noi_dung_danh_gia' => 'nullable|string',
            'tags' => 'nullable|string',
        ]);
        $item->update($validated);
        $item->load(['yeuCau', 'nguoiDung']);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật đánh giá thành công',
            'data' => $item
        ]);
    }

    public function destroy($id)
    {
        $item = DanhGiaCuuHo::findOrFail($id);
        $item->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa đánh giá thành công'
        ], 200);
    }

    /**
     * Get ratings for a help request
     */
    public function getByYeuCau($id_yeu_cau)
    {
        $yeuCau = YeuCauCuuHo::findOrFail($id_yeu_cau);
        $items = DanhGiaCuuHo::where('id_yeu_cau', $id_yeu_cau)
            ->with(['yeuCau', 'nguoiDung'])
            ->get();

        return response()->json([
            'status' => true,
            'data' => $items,
            'count' => $items->count()
        ]);
    }

    /**
     * Create rating for a help request
     */
    public function createForYeuCau(Request $request, $id_yeu_cau)
    {
        $yeuCau = YeuCauCuuHo::findOrFail($id_yeu_cau);

        $validated = $request->validate([
            'id_nguoi_dung' => 'required|numeric',
            'diem_danh_gia' => 'required|numeric|between:1,5',
            'noi_dung_danh_gia' => 'nullable|string',
            'tags' => 'nullable|string',
        ]);

        $tags = $validated['tags'] ?? null;
        $validated['id_yeu_cau'] = $id_yeu_cau;
        unset($validated['tags']);
        $validated['tags'] = $tags;

        // One user can only have one rating per rescue request — update if exists
        $existing = DanhGiaCuuHo::where('id_yeu_cau', $id_yeu_cau)
            ->where('id_nguoi_dung', $validated['id_nguoi_dung'])
            ->first();

        if ($existing) {
            $existing->update([
                'diem_danh_gia' => $validated['diem_danh_gia'],
                'noi_dung_danh_gia' => $validated['noi_dung_danh_gia'] ?? null,
                'tags' => $tags,
            ]);
            $existing->load(['yeuCau', 'nguoiDung']);

            // Lấy ID đội cứu hộ từ phân công mới nhất để broadcast
            $phanCong = $yeuCau->phanCongs()->orderBy('id_phan_cong', 'desc')->first();
            if ($phanCong && $phanCong->id_doi_cuu_ho) {
                event(new \App\Events\DanhGiaCreated($existing, $phanCong->id_doi_cuu_ho));
            }

            return response()->json([
                'status' => true,
                'message' => 'Cập nhật đánh giá thành công',
                'data' => $existing,
            ], 200);
        }

        $item = DanhGiaCuuHo::create($validated);
        $item->load(['yeuCau', 'nguoiDung']);

        // Lấy ID đội cứu hộ từ phân công mới nhất để broadcast
        $phanCong = $yeuCau->phanCongs()->orderBy('id_phan_cong', 'desc')->first();
        if ($phanCong && $phanCong->id_doi_cuu_ho) {
            event(new \App\Events\DanhGiaCreated($item, $phanCong->id_doi_cuu_ho));
        }

        return response()->json([
            'status' => true,
            'message' => 'Tạo đánh giá cho yêu cầu thành công',
            'data' => $item
        ], 201);
    }

    /**
     * Lấy các đánh giá của một đội cứu hộ (thông qua bảng phan_cong_cuu_ho)
     */
    public function getByTeam(Request $request, $id_doi_cuu_ho)
    {
        $perPage = $request->get('per_page', 50);

        $items = DanhGiaCuuHo::whereHas('yeuCau.phanCongs', function ($query) use ($id_doi_cuu_ho) {
                $query->where('id_doi_cuu_ho', $id_doi_cuu_ho);
            })
            ->with(['yeuCau', 'nguoiDung'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $items->items(),
            'pagination' => [
                'total' => $items->total(),
                'per_page' => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
            ]
        ]);
    }
}
