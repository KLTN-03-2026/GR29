<?php

namespace App\Http\Controllers;

use App\Models\ThanhVienDoi;
use App\Services\YeuCauCapPhatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class YeuCauCapPhatController extends Controller
{
    public function __construct(
        protected YeuCauCapPhatService $dichVuYeuCauCapPhat
    ) {}

    /**
     * Rescuer: gửi yêu cầu cấp phát (chỉ thành viên đội, không phải admin)
     */
    public function guiYeuCauRescuer(Request $request)
    {
        try {
            $nguoi = Auth::guard('thanh-vien-doi')->user();
            if (! $nguoi instanceof ThanhVienDoi) {
                return Response::json([
                    'success' => false,
                    'message' => 'Chỉ tài khoản cứu hộ viên mới được gửi yêu cầu.',
                ], 403);
            }

            $validated = $request->validate([
                'slug_tai_nguyen' => 'required|string|max:100',
                'so_luong_yeu_cau' => 'required|integer|min:1|max:10000',
                'ghi_chu' => 'nullable|string|max:2000',
            ]);

            $banGhi = $this->dichVuYeuCauCapPhat->taoYeuCauTuRescuer(
                $nguoi,
                $validated['slug_tai_nguyen'],
                (int) $validated['so_luong_yeu_cau'],
                $validated['ghi_chu'] ?? null
            );

            return Response::json([
                'success' => true,
                'message' => 'Đã gửi yêu cầu cấp phát tài nguyên.',
                'data' => $this->dichVuYeuCauCapPhat->dongGoiBanGhi($banGhi->fresh(['doiCuuHo', 'nguoiYeuCau', 'nguoiDuyet'])),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rescuer: xem tồn kho theo loại (read-only)
     */
    public function xemTonKhoRescuer()
    {
        try {
            $loai = $this->dichVuYeuCauCapPhat->layDanhSachSlugKhoHopLe();
            $data = [];
            foreach ($loai as $slug) {
                $data[] = [
                    'slug_tai_nguyen' => $slug,
                    'ten_hien_thi' => $this->dichVuYeuCauCapPhat->layTenHienThiLoai($slug),
                    'tong_so_luong' => $this->dichVuYeuCauCapPhat->laySoTonKho($slug),
                ];
            }

            return Response::json([
                'success' => true,
                'message' => 'Tồn kho tài nguyên',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: danh sách yêu cầu (mặc định CHO_DUYET nếu truyền filter)
     */
    public function danhSachAdmin(Request $request)
    {
        try {
            $paginator = $this->dichVuYeuCauCapPhat->layDanhSachChoAdmin([
                'trang_thai' => $request->get('trang_thai'),
                'id_doi_cuu_ho' => $request->get('id_doi_cuu_ho'),
                'per_page' => $request->get('per_page', 30),
            ]);

            $items = collect($paginator->items())->map(function ($row) {
                return $this->dichVuYeuCauCapPhat->dongGoiBanGhi($row);
            })->values()->all();

            return Response::json([
                'success' => true,
                'message' => 'Danh sách yêu cầu cấp phát',
                'data' => [
                    'data' => $items,
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: cấp phát theo yêu cầu
     */
    public function capPhatTheoYeuCau(Request $request, int $id)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (! $admin) {
                return Response::json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $banGhi = $this->dichVuYeuCauCapPhat->capPhatBoiAdmin($id, $admin);

            return Response::json([
                'success' => true,
                'message' => 'Cấp phát tài nguyên thành công.',
                'data' => $this->dichVuYeuCauCapPhat->dongGoiBanGhi($banGhi),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Không thể cấp phát',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu',
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: từ chối yêu cầu
     */
    public function tuChoiYeuCau(Request $request, int $id)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (! $admin) {
                return Response::json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $validated = $request->validate([
                'ghi_chu_tu_choi' => 'nullable|string|max:1000',
            ]);

            $banGhi = $this->dichVuYeuCauCapPhat->tuChoiBoiAdmin(
                $id,
                $admin,
                $validated['ghi_chu_tu_choi'] ?? null
            );

            return Response::json([
                'success' => true,
                'message' => 'Đã từ chối yêu cầu.',
                'data' => $this->dichVuYeuCauCapPhat->dongGoiBanGhi($banGhi),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Không thể từ chối',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu',
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: lịch sử cấp phát / từ chối
     */
    public function lichSu(Request $request)
    {
        try {
            $paginator = $this->dichVuYeuCauCapPhat->layLichSuCapPhat([
                'id_doi_cuu_ho' => $request->get('id_doi_cuu_ho'),
                'trang_thai' => $request->get('trang_thai'),
                'tu_ngay' => $request->get('tu_ngay'),
                'den_ngay' => $request->get('den_ngay'),
                'per_page' => $request->get('per_page', 50),
            ]);

            $items = collect($paginator->items())->map(function ($row) {
                return $this->dichVuYeuCauCapPhat->dongGoiBanGhi($row);
            })->values()->all();

            return Response::json([
                'success' => true,
                'message' => 'Lịch sử cấp phát',
                'data' => [
                    'data' => $items,
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: '.$e->getMessage(),
            ], 500);
        }
    }
}
