<?php

namespace App\Http\Controllers;

use App\Models\{KetQuaCuuHo, PhanCongCuuHo, YeuCauCuuHo};
use Illuminate\Http\Request;

class KetQuaCuuHoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $items = KetQuaCuuHo::with(['phanCong.yeuCau.nguoiDung', 'phanCong.yeuCau.loaiSuCo', 'phanCong.doiCuuHo'])->paginate($perPage);
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_phan_cong' => 'required|integer|exists:phan_cong_cuu_ho,id_phan_cong|unique:ket_qua_cuu_ho,id_phan_cong',
            'bao_cao_hien_truong' => 'nullable|string',
            'trang_thai_ket_qua' => 'nullable|string|max:30',
            'hinh_anh_minh_chung' => 'nullable|string|max:500',
            'thoi_gian_ket_thuc' => 'nullable|date'
        ]);
        $item = KetQuaCuuHo::create($validated);
        $item->load('phanCong.yeuCau.nguoiDung', 'phanCong.yeuCau.loaiSuCo', 'phanCong.doiCuuHo');
        return response()->json($item, 201);
    }

    public function show($id)
    {
        $item = KetQuaCuuHo::with(['phanCong.yeuCau.nguoiDung', 'phanCong.yeuCau.loaiSuCo', 'phanCong.doiCuuHo'])->findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = KetQuaCuuHo::findOrFail($id);
        $validated = $request->validate([
            'bao_cao_hien_truong' => 'nullable|string',
            'trang_thai_ket_qua' => 'nullable|string|max:30',
            'hinh_anh_minh_chung' => 'nullable|string|max:500',
            'thoi_gian_ket_thuc' => 'nullable|date'
        ]);
        $item->update($validated);
        $item->load('phanCong.yeuCau.nguoiDung', 'phanCong.yeuCau.loaiSuCo', 'phanCong.doiCuuHo');
        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = KetQuaCuuHo::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }

    /**
     * Create rescue result for a specific assignment
     * Route: POST post-ket-qua-cuu-ho/phan-cong/{id_phan_cong}
     */
    public function createForPhanCong(Request $request, $id_phan_cong)
    {
        $phanCong = PhanCongCuuHo::with('yeuCau')->findOrFail($id_phan_cong);

        $existing = KetQuaCuuHo::where('id_phan_cong', $id_phan_cong)->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Kết quả cho phân công này đã tồn tại',
                'data' => $existing
            ], 409);
        }

        $validated = $request->validate([
            'bao_cao_hien_truong' => 'nullable|string',
            'trang_thai_ket_qua' => 'nullable|string|max:30',
            'hinh_anh_minh_chung' => 'nullable|string|max:500',
            'thoi_gian_ket_thuc' => 'nullable|date'
        ]);

        // Handle file upload if provided
        $hinhAnhPath = null;
        if ($request->hasFile('hinh_anh_minh_chung')) {
            $file = $request->file('hinh_anh_minh_chung');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/hinh_anh'), $filename);
            $hinhAnhPath = 'uploads/hinh_anh/' . $filename;
            $validated['hinh_anh_minh_chung'] = $hinhAnhPath;
        }

        // If trang_thai_ket_qua is provided, also update phan_cong status
        if (isset($validated['trang_thai_ket_qua'])) {
            $phanCongStatus = strtoupper(trim($validated['trang_thai_ket_qua']));
            if (in_array($phanCongStatus, ['HOAN_THANH', 'THAT_BAI'])) {
                $phanCong->update(['trang_thai_nhiem_vu' => $phanCongStatus]);
            }
        }

        // Update yeu_cau status based on result
        $yeuCau = $phanCong->yeuCau;
        if ($yeuCau) {
            $trangThaiKetQua = strtoupper(trim($validated['trang_thai_ket_qua'] ?? ''));
            if ($trangThaiKetQua === 'HOAN_THANH') {
                $yeuCau->update(['trang_thai' => 'HOAN_THANH']);
            } elseif ($trangThaiKetQua === 'THAT_BAI') {
                $yeuCau->update(['trang_thai' => 'THAT_BAI']);
            }
        }

        $validated['id_phan_cong'] = $id_phan_cong;
        $item = KetQuaCuuHo::create($validated);
        $item->load('phanCong.yeuCau.nguoiDung', 'phanCong.yeuCau.loaiSuCo', 'phanCong.doiCuuHo');

        return response()->json([
            'success' => true,
            'message' => 'Tạo kết quả cứu hộ thành công',
            'data' => $item
        ], 201);
    }

    /**
     * Get rescue result by assignment
     * Route: GET get-ket-qua-cuu-ho/phan-cong/{id_phan_cong}
     */
    public function getByPhanCong($id_phan_cong)
    {
        $item = KetQuaCuuHo::with(['phanCong.yeuCau.nguoiDung', 'phanCong.yeuCau.loaiSuCo', 'phanCong.doiCuuHo'])
            ->where('id_phan_cong', $id_phan_cong)
            ->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa có kết quả cho phân công này'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $item
        ]);
    }
}