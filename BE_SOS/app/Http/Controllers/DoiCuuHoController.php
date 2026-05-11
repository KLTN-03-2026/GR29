<?php

namespace App\Http\Controllers;

use App\Models\{DoiCuuHo, ThanhVienDoi, TaiNguyenCuuHo, ViTriDoiCuuHo, NangLucDoi, DoiCuuHoLoaiSuCo, LoaiSuCo};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DoiCuuHoController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'mat_khau' => 'required',
        ]);

        $user = DoiCuuHo::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->mat_khau, $user->mat_khau)) {
            return response()->json([
                'status' => false,
                'message' => 'Tài khoản sai email hoặc password',
            ], 401);
        }

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Đăng nhập thành công',
            'token' => $token,
            'token_type' => 'Bearer',
            'data' => $user->load(['thanhViens', 'taiNguyens', 'viTris', 'nangLuc', 'loaiSuCos']),
        ]);
    }

    public function checkThanhVien()
    {
        $user = Auth::guard('doi-cuu-ho')->user();
        if ($user) {
            return response()->json([
                'status' => true,
                'ho_ten' => $user->ho_ten ?? $user->name ?? 'N/A',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền truy cập.',
            ]);
        }
    }

    /**
     * Display paginated list of all rescue teams
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $getAll = $request->get('get_all', false);
            $sortBy = $request->get('sort_by', 'id_doi_cuu_ho');
            $sortOrder = $request->get('sort_order', 'desc');

            $query = DoiCuuHo::with(['thanhViens', 'taiNguyens', 'viTris', 'nangLuc', 'loaiSuCos', 'phanCongs'])
                ->orderBy($sortBy, $sortOrder);

            // Nếu yêu cầu lấy tất cả (get_all=true hoặc per_page >= 100)
            if ($getAll || $perPage >= 100) {
                $items = $query->get()->map(function ($team) {
                    return $this->appendCapacityFields($team);
                });
                return Response::json([
                    'success' => true,
                    'message' => 'Danh sách tất cả đội cứu hộ',
                    'data' => $items
                ], 200);
            }

            $items = $query->paginate($perPage);

            return Response::json([
                'success' => true,
                'message' => 'Danh sách đội cứu hộ',
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
     * Append real-time capacity fields to a team object.
     * CAPACITY = thanhVien * 4 (dong nhat voi AutoDispatchService + frontend Assignments)
     * Active statuses: DANG_XU_LY, DA_PHAN_CONG, DA_DEN_HIEN_TRUONG
     * MOI = pending (chua tieu ton capacity)
     */
    private function appendCapacityFields($team)
    {
        $soThanhVien = $team->thanhViens ? $team->thanhViens->count() : 0;
        // capacity = soThanhVien * 4 (dong nhat voi AutoDispatchService + frontend)
        $capacity = $soThanhVien * 4;

        // Dem nhiem vu active: DANG_XU_LY, DA_PHAN_CONG, DA_DEN_HIEN_TRUONG
        // MOI = pending, chua tieu ton capacity
        $activeStatuses = ['DANG_XU_LY', 'DA_PHAN_CONG', 'DA_DEN_HIEN_TRUONG'];
        $phanCongList = $team->phanCongs ?? collect();
        $soYeuCauDangXuLy = $phanCongList
            ->filter(fn($pc) => in_array(strtoupper(trim($pc->trang_thai_nhiem_vu ?? '')), $activeStatuses, true))
            ->count();

        // MOI: da duoc phan cong nhung chua tiep nhan (pending, chua tieu ton capacity)
        $pendingCount = $phanCongList
            ->filter(fn($pc) => strtoupper(trim($pc->trang_thai_nhiem_vu ?? '')) === 'MOI')
            ->count();

        $team->active_count = $soYeuCauDangXuLy;
        $team->pending_count = $pendingCount;
        $team->capacity = $capacity;
        // overload khi active >= capacity (>=, khong phai >)
        $team->trang_thai_theo_nang_luc = ($soYeuCauDangXuLy >= $capacity && $capacity > 0) ? 'overload' : 'available';

        \Log::channel('daily')->info('[CAPACITY] teamId=' . ($team->id_doi_cuu_ho ?? $team->id)
            . ' | soThanhVien=' . $soThanhVien
            . ' | capacity=' . $capacity
            . ' | activeCount=' . $soYeuCauDangXuLy
            . ' | pendingCount=' . $pendingCount
            . ' | trang_thai=' . $team->trang_thai_theo_nang_luc);

        return $team;
    }

    /**
     * Get simple list for dropdown (no pagination)
     */
    public function getAllForDropdown()
    {
        try {
            $items = DoiCuuHo::where('trang_thai', 1)
                ->orderBy('ten_doi', 'asc')
                ->get(['id_doi_cuu_ho', 'ten_doi']);

            return Response::json([
                'success' => true,
                'data' => $items
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created rescue team
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'ten_doi' => 'required|string|max:255',
                'khu_vuc_quan_ly' => 'required|string|max:255',
                'so_dien_thoai_hotline' => 'nullable|string|max:20',
                'vi_tri_lat' => 'nullable|numeric',
                'vi_tri_lng' => 'nullable|numeric',
                'trang_thai' => 'nullable|string|max:30',
                'mo_ta' => 'nullable|string'
            ]);

            $item = DoiCuuHo::create($validated);
            $item->load('thanhViens', 'taiNguyens', 'viTris', 'nangLuc', 'loaiSuCos');

            return Response::json([
                'success' => true,
                'message' => 'Tạo đội cứu hộ thành công',
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
                'message' => 'Lỗi khi tạo đội: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified rescue team
     */
    public function show($id)
    {
        try {
            $item = DoiCuuHo::with(['thanhViens', 'taiNguyens', 'viTris', 'nangLuc', 'loaiSuCos', 'phanCongs'])
                ->findOrFail($id);

            $this->appendCapacityFields($item);

            return Response::json([
                'success' => true,
                'message' => 'Chi tiết đội cứu hộ',
                'data' => $item
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Đội cứu hộ không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified rescue team
     */
    public function update(Request $request, $id)
    {
        try {
            $item = DoiCuuHo::findOrFail($id);

            $validated = $request->validate([
                'ten_doi' => 'sometimes|string|max:255',
                'khu_vuc_quan_ly' => 'sometimes|string|max:255',
                'so_dien_thoai_hotline' => 'nullable|string|max:20',
                'vi_tri_lat' => 'nullable|numeric',
                'vi_tri_lng' => 'nullable|numeric',
                'trang_thai' => 'nullable|string|max:30',
                'mo_ta' => 'nullable|string'
            ]);

            $item->update($validated);
            $item->load('thanhViens', 'taiNguyens', 'viTris', 'nangLuc', 'loaiSuCos');

            return Response::json([
                'success' => true,
                'message' => 'Cập nhật đội cứu hộ thành công',
                'data' => $item
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Đội cứu hộ không tồn tại'
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
     * Delete the specified rescue team
     */
    public function destroy($id)
    {
        try {
            $item = DoiCuuHo::findOrFail($id);
            $item->delete();

            return Response::json([
                'success' => true,
                'message' => 'Xóa đội cứu hộ thành công'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Đội cứu hộ không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi khi xóa: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== THÀNH VIÊN ĐỘI ==========

    /**
     * Get team members
     */
    public function getThanhVien($id)
    {
        try {
            DoiCuuHo::findOrFail($id);
            $items = ThanhVienDoi::where('id_doi_cuu_ho', $id)->paginate(15);

            return Response::json([
                'success' => true,
                'message' => 'Danh sách thành viên đội',
                'data' => $items
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Đội cứu hộ không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add team member
     */
    public function addThanhVien(Request $request, $id)
    {
        try {
            DoiCuuHo::findOrFail($id);

            $validated = $request->validate([
                'ho_ten' => 'required|string|max:255',
                'so_dien_thoai' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'mat_khau' => 'nullable|string|min:6',
                'vai_tro_trong_doi' => 'required|string|max:255',
                'trang_thai' => 'nullable|integer|in:0,1'
            ]);

            $validated['id_doi_cuu_ho'] = $id;
            $item = ThanhVienDoi::create($validated);

            return Response::json([
                'success' => true,
                'message' => 'Thêm thành viên thành công',
                'data' => $item
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Đội cứu hộ không tồn tại'
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
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove team member
     */
    public function removeThanhVien($id, $id_thanh_vien)
    {
        try {
            DoiCuuHo::findOrFail($id);
            $item = ThanhVienDoi::where('id_thanh_vien_doi', $id_thanh_vien)
                ->where('id_doi_cuu_ho', $id)
                ->firstOrFail();

            $item->delete();

            return Response::json([
                'success' => true,
                'message' => 'Xóa thành viên thành công'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Thành viên hoặc đội không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== TÀI NGUYÊN ĐỘI ==========

    /**
     * Get team resources
     */
    public function getTaiNguyen($id)
    {
        try {
            DoiCuuHo::findOrFail($id);
            $items = TaiNguyenCuuHo::where('id_doi_cuu_ho', $id)->paginate(15);

            return Response::json([
                'success' => true,
                'message' => 'Danh sách tài nguyên đội',
                'data' => $items
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Đội cứu hộ không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add team resource
     */
    public function addTaiNguyen(Request $request, $id)
    {
        try {
            DoiCuuHo::findOrFail($id);

            $validated = $request->validate([
                'ten_tai_nguyen' => 'required|string|max:255',
                'slug_tai_nguyen' => 'required|string|max:100',
                'so_luong' => 'required|integer|min:1',
                'trang_thai' => 'nullable|integer|in:0,1'
            ]);

            $validated['id_doi_cuu_ho'] = $id;
            $item = TaiNguyenCuuHo::create($validated);

            return Response::json([
                'success' => true,
                'message' => 'Thêm tài nguyên thành công',
                'data' => $item
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Đội cứu hộ không tồn tại'
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
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update team resource
     */
    public function updateTaiNguyen(Request $request, $id, $id_tai_nguyen)
    {
        try {
            DoiCuuHo::findOrFail($id);
            $item = TaiNguyenCuuHo::where('id_tai_nguyen', $id_tai_nguyen)
                ->where('id_doi_cuu_ho', $id)
                ->firstOrFail();

            $validated = $request->validate([
                'ten_tai_nguyen' => 'sometimes|string|max:255',
                'slug_tai_nguyen' => 'sometimes|string|max:100',
                'so_luong' => 'sometimes|integer|min:1',
                'trang_thai' => 'nullable|integer|in:0,1'
            ]);

            $item->update($validated);

            return Response::json([
                'success' => true,
                'message' => 'Cập nhật tài nguyên thành công',
                'data' => $item
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Tài nguyên hoặc đội không tồn tại'
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
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== VỊ TRÍ ĐỘI ==========

    /**
     * Get team locations
     */
    public function getViTri($id)
    {
        try {
            DoiCuuHo::findOrFail($id);
            $items = ViTriDoiCuuHo::where('id_doi_cuu_ho', $id)->paginate(15);

            return Response::json([
                'success' => true,
                'message' => 'Danh sách vị trí đội',
                'data' => $items
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Đội cứu hộ không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add team location
     */
    public function addViTri(Request $request, $id)
    {
        try {
            DoiCuuHo::findOrFail($id);

            $validated = $request->validate([
                'vi_tri_lat' => 'required|numeric',
                'vi_tri_lng' => 'required|numeric'
            ]);

            $validated['id_doi_cuu_ho'] = $id;
            $item = ViTriDoiCuuHo::create($validated);

            return Response::json([
                'success' => true,
                'message' => 'Thêm vị trí thành công',
                'data' => $item
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Đội cứu hộ không tồn tại'
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
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== NĂNG LỰC ĐỘI ==========

    /**
     * Get team capabilities
     */
    public function getNangLuc($id)
    {
        try {
            DoiCuuHo::findOrFail($id);
            $item = NangLucDoi::where('id_doi_cuu_ho', $id)->first();

            if (!$item) {
                return Response::json([
                    'success' => false,
                    'message' => 'Năng lực đội chưa được thiết lập'
                ], 404);
            }

            return Response::json([
                'success' => true,
                'message' => 'Thông tin năng lực đội',
                'data' => $item
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Đội cứu hộ không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update team capabilities
     */
    public function updateNangLuc(Request $request, $id)
    {
        try {
            DoiCuuHo::findOrFail($id);

            $validated = $request->validate([
                'so_viec_dang_xu_ly' => 'sometimes|integer|min:0',
                'so_viec_toi_da' => 'sometimes|integer|min:0',
                'ty_le_hoan_thanh' => 'sometimes|numeric|between:0,100',
                'thoi_gian_xu_ly_tb' => 'sometimes|numeric|min:0'
            ]);

            $item = NangLucDoi::firstOrCreate(
                ['id_doi_cuu_ho' => $id],
                $validated
            );

            $item->update($validated);

            return Response::json([
                'success' => true,
                'message' => 'Cập nhật năng lực thành công',
                'data' => $item
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Đội cứu hộ không tồn tại'
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
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== LOẠI SỰ CỐ ĐỘI XỬ LÝ ==========

    /**
     * Get incident types handled by team
     */
    public function getLoaiSuCoDungXuLy($id)
    {
        try {
            DoiCuuHo::findOrFail($id);
            $items = LoaiSuCo::whereHas('doiCuuHos', function ($query) use ($id) {
                $query->where('id_doi_cuu_ho', $id);
            })->paginate(15);

            return Response::json([
                'success' => true,
                'message' => 'Danh sách loại sự cố xử lý',
                'data' => $items
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Đội cứu hộ không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add incident type capability to team
     */
    public function addLoaiSuCoDungXuLy(Request $request, $id)
    {
        try {
            DoiCuuHo::findOrFail($id);

            $validated = $request->validate([
                'id_loai_su_co' => 'required|integer|exists:loai_su_co,id_loai_su_co'
            ]);

            // Check if already exists
            $existing = DoiCuuHoLoaiSuCo::where('id_doi_cuu_ho', $id)
                ->where('id_loai_su_co', $validated['id_loai_su_co'])
                ->first();

            if ($existing) {
                return Response::json([
                    'success' => false,
                    'message' => 'Loại sự cố này đã có trong danh sách'
                ], 409);
            }

            $item = DoiCuuHoLoaiSuCo::create([
                'id_doi_cuu_ho' => $id,
                'id_loai_su_co' => $validated['id_loai_su_co']
            ]);

            $item->load('loaiSuCo');

            return Response::json([
                'success' => true,
                'message' => 'Thêm loại sự cố thành công',
                'data' => $item
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Đội cứu hộ hoặc loại sự cố không tồn tại'
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
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== FILTER & SEARCH ==========

    /**
     * Get teams by status
     */
    public function getByStatus($trang_thai)
    {
        try {
            $items = DoiCuuHo::where('trang_thai', $trang_thai)
                ->with(['thanhViens', 'taiNguyens', 'nangLuc'])
                ->paginate(15);

            return Response::json([
                'success' => true,
                'message' => 'Danh sách đội theo trạng thái',
                'data' => $items
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teams by region
     */
    public function getByKhuVuc($khu_vuc)
    {
        try {
            $items = DoiCuuHo::where('khu_vuc_quan_ly', 'like', '%' . $khu_vuc . '%')
                ->with(['thanhViens', 'taiNguyens', 'nangLuc'])
                ->paginate(15);

            return Response::json([
                'success' => true,
                'message' => 'Danh sách đội theo khu vực',
                'data' => $items
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get team efficiency statistics
     */
    public function getTeamEfficiency(Request $request)
    {
        try {
            $data = DoiCuuHo::with('nangLuc', 'phanCongs')
                ->get()
                ->map(function ($team) {
                    return [
                        'id_doi_cuu_ho' => $team->id_doi_cuu_ho,
                        'ten_doi' => $team->ten_doi,
                        'so_nhan_su' => $team->thanhViens()->count(),
                        'so_tai_nguyen' => $team->taiNguyens()->count(),
                        'so_nhiem_vu_dang_xy_ly' => $team->phanCongs()
                            ->where('trang_thai_nhiem_vu', 'DANG_XU_LY')->count(),
                        'ty_le_hoan_thanh' => $team->nangLuc?->ty_le_hoan_thanh ?? 0,
                        'thoi_gian_xu_ly_tb' => $team->nangLuc?->thoi_gian_xu_ly_tb ?? 0
                    ];
                });

            return Response::json([
                'success' => true,
                'message' => 'Thống kê hiệu suất đội cứu hộ',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available teams
     */
    public function getAvailableTeams(Request $request)
    {
        try {
            $items = DoiCuuHo::whereIn('trang_thai', ['SAN_SANG', 'SanSang', 'Sẵn sàng'])
                ->with(['thanhViens', 'taiNguyens', 'nangLuc', 'phanCongs'])
                ->get()
                ->filter(function ($team) {
                    $soThanhVien = $team->thanhViens ? $team->thanhViens->count() : 0;
                    // capacity = soThanhVien * 4 (dong nhat voi AutoDispatchService + frontend)
                    $capacity = $soThanhVien * 4;

                    // active: DANG_XU_LY, DA_PHAN_CONG, DA_DEN_HIEN_TRUONG
                    // MOI = pending, chua tieu ton capacity
                    $activeStatuses = ['DANG_XU_LY', 'DA_PHAN_CONG', 'DA_DEN_HIEN_TRUONG'];
                    $soYeuCauDangXuLy = $team->phanCongs()
                        ->whereIn('trang_thai_nhiem_vu', $activeStatuses)
                        ->count();

                    // available if below capacity; teams with 0 members are excluded
                    return $soThanhVien > 0 && $soYeuCauDangXuLy < $capacity;
                })
                ->map(function ($team) {
                    $this->appendCapacityFields($team);
                    return $team;
                });

            return Response::json([
                'success' => true,
                'message' => 'Danh sách đội có sẵn',
                'data' => $items->values()
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search teams
     */
    public function search(Request $request)
    {
        try {
            $keyword = $request->get('noi_dung_tim', '');

            $items = DoiCuuHo::where('ten_doi', 'like', '%' . $keyword . '%')
                ->orWhere('khu_vuc_quan_ly', 'like', '%' . $keyword . '%')
                ->with(['thanhViens', 'taiNguyens', 'nangLuc'])
                ->paginate(15);

            return Response::json([
                'success' => true,
                'message' => 'Kết quả tìm kiếm',
                'data' => $items
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== ADMIN CRUD DOI CUU HO ==========

    /**
     * List all rescue teams for admin (full details)
     */
    public function listDoiCuuHo(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $items = DoiCuuHo::with(['thanhViens', 'taiNguyens'])
                ->orderBy('id_doi_cuu_ho', 'desc')
                ->paginate($perPage);

            return Response::json([
                'success' => true,
                'message' => 'Danh sách đội cứu hộ',
                'data' => $items
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin create rescue team
     */
    public function createDoiCuuHo(Request $request)
    {
        try {
            $validated = $request->validate([
                'ten_doi' => 'required|string|max:255',
                'khu_vuc_quan_ly' => 'required|string|max:255',
                'so_dien_thoai_hotline' => 'nullable|string|max:20',
                'vi_tri_lat' => 'nullable|numeric',
                'vi_tri_lng' => 'nullable|numeric',
                'trang_thai' => 'nullable|string|max:30',
                'mo_ta' => 'nullable|string',
                'email' => 'nullable|email|max:255',
                'mat_khau' => 'nullable|string|min:6',
            ]);

            if (isset($validated['mat_khau'])) {
                $validated['mat_khau'] = Hash::make($validated['mat_khau']);
            }

            $item = DoiCuuHo::create($validated);
            $item->load('thanhViens', 'taiNguyens', 'viTris', 'nangLuc', 'loaiSuCos');

            return Response::json([
                'success' => true,
                'message' => 'Tạo đội cứu hộ thành công',
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
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin update rescue team
     */
    public function updateDoiCuuHo(Request $request)
    {
        try {
            $id = $request->get('id');
            $item = DoiCuuHo::findOrFail($id);

            $validated = $request->validate([
                'ten_doi' => 'sometimes|string|max:255',
                'khu_vuc_quan_ly' => 'sometimes|string|max:255',
                'so_dien_thoai_hotline' => 'nullable|string|max:20',
                'vi_tri_lat' => 'nullable|numeric',
                'vi_tri_lng' => 'nullable|numeric',
                'trang_thai' => 'nullable|string|max:30',
                'mo_ta' => 'nullable|string',
                'email' => 'nullable|email|max:255',
                'mat_khau' => 'nullable|string|min:6',
            ]);

            if (isset($validated['mat_khau'])) {
                $validated['mat_khau'] = Hash::make($validated['mat_khau']);
            }

            $item->update($validated);
            $item->load('thanhViens', 'taiNguyens', 'viTris', 'nangLuc', 'loaiSuCos');

            return Response::json([
                'success' => true,
                'message' => 'Cập nhật đội cứu hộ thành công',
                'data' => $item
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Đội cứu hộ không tồn tại'
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
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin delete rescue team
     */
    public function deleteDoiCuuHo(Request $request)
    {
        try {
            $id = $request->get('id');
            $item = DoiCuuHo::findOrFail($id);
            $item->delete();

            return Response::json([
                'success' => true,
                'message' => 'Xóa đội cứu hộ thành công'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Đội cứu hộ không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== ADMIN CRUD TAI NGUYEN ==========

    /**
     * Admin create resource
     */
    public function themTaiNguyen(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_doi_cuu_ho' => 'required|integer|exists:doi_cuu_ho,id_doi_cuu_ho',
                'ten_tai_nguyen' => 'required|string|max:255',
                'slug_tai_nguyen' => 'required|string|max:100',
                'so_luong' => 'required|integer|min:0',
                'trang_thai' => 'nullable|integer|in:0,1',
            ]);

            $item = TaiNguyenCuuHo::create($validated);

            return Response::json([
                'success' => true,
                'message' => 'Thêm tài nguyên thành công',
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
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin update resource
     */
    public function suaTaiNguyen(Request $request)
    {
        try {
            $id = $request->get('id');
            $item = TaiNguyenCuuHo::findOrFail($id);

            $validated = $request->validate([
                'ten_tai_nguyen' => 'sometimes|string|max:255',
                'slug_tai_nguyen' => 'sometimes|string|max:100',
                'so_luong' => 'sometimes|integer|min:0',
                'trang_thai' => 'nullable|integer|in:0,1',
            ]);

            $item->update($validated);

            return Response::json([
                'success' => true,
                'message' => 'Cập nhật tài nguyên thành công',
                'data' => $item
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Tài nguyên không tồn tại'
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
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin delete resource
     */
    public function xoaTaiNguyen(Request $request)
    {
        try {
            $id = $request->get('id');
            $item = TaiNguyenCuuHo::findOrFail($id);
            $item->delete();

            return Response::json([
                'success' => true,
                'message' => 'Xóa tài nguyên thành công'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Tài nguyên không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== KHO TAI NGUYEN & CAP PHAT ==========

    /**
     * Get warehouse summary: aggregate all resources by type across all teams
     */
    public function getKhoTaiNguyen(Request $request)
    {
        try {
            $loaiTaiNguyen = ['xe_cuu_ho', 'nhu_yeu_pham', 'vat_tu_y_te', 'dung_cu_thi_cong'];
            $tenHienThi = [
                'xe_cuu_ho' => 'Xe cứu hộ',
                'nhu_yeu_pham' => 'Nhu yếu phẩm',
                'vat_tu_y_te' => 'Vật tư y tế',
                'dung_cu_thi_cong' => 'Dụng cụ thi công',
            ];

            $result = [];
            foreach ($loaiTaiNguyen as $loai) {
                $kho = \App\Models\KhoTaiNguyen::where('slug_tai_nguyen', $loai)->first();
                $result[] = [
                    'slug_tai_nguyen' => $loai,
                    'ten_hien_thi' => $tenHienThi[$loai] ?? $loai,
                    'tong_so_luong' => $kho ? (int) $kho->so_luong : 0,
                ];
            }

            return Response::json([
                'success' => true,
                'message' => 'Dữ liệu kho tài nguyên',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cap nhat kho tai nguyen (global warehouse update)
     */
    public function nhapKho(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug_tai_nguyen' => 'required|string|max:100',
                'so_luong' => 'required|integer|min:1',
                'ghi_chu' => 'nullable|string|max:500',
            ]);

            $loai = $validated['slug_tai_nguyen'];
            $soLuongNhap = (int) $validated['so_luong'];

            $tenLoai = [
                'xe_cuu_ho' => 'Xe cứu hộ',
                'nhu_yeu_pham' => 'Nhu yếu phẩm',
                'vat_tu_y_te' => 'Vật tư y tế',
                'dung_cu_thi_cong' => 'Dụng cụ thi công',
            ];

            $kho = \App\Models\KhoTaiNguyen::where('slug_tai_nguyen', $loai)->first();

            if ($kho) {
                $kho->update(['so_luong' => $kho->so_luong + $soLuongNhap]);
            } else {
                \App\Models\KhoTaiNguyen::create([
                    'slug_tai_nguyen' => $loai,
                    'ten_tai_nguyen' => $tenLoai[$loai] ?? $loai,
                    'so_luong' => $soLuongNhap,
                ]);
            }

            return Response::json([
                'success' => true,
                'message' => 'Nhập kho thành công'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get resources by team
     */
    public function getTaiNguyenByDoi(Request $request, $id)
    {
        try {
            DoiCuuHo::findOrFail($id);
            $items = TaiNguyenCuuHo::where('id_doi_cuu_ho', $id)->get();

            return Response::json([
                'success' => true,
                'message' => 'Tài nguyên theo đội',
                'data' => $items
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Đội cứu hộ không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cap phat tai nguyen cho team
     */
    public function capPhatTaiNguyen(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_doi_cuu_ho' => 'required|integer|exists:doi_cuu_ho,id_doi_cuu_ho',
                'slug_tai_nguyen' => 'required|string|max:100',
                'so_luong_cap' => 'required|integer|min:1',
                'ghi_chu' => 'nullable|string|max:500',
            ]);

            $doi = DoiCuuHo::findOrFail($validated['id_doi_cuu_ho']);
            $loai = $validated['slug_tai_nguyen'];

            $taiNguyen = TaiNguyenCuuHo::where('id_doi_cuu_ho', $doi->id_doi_cuu_ho)
                    ->where('slug_tai_nguyen', $loai)
                ->first();

            if ($taiNguyen) {
                $taiNguyen->update([
                    'so_luong' => $taiNguyen->so_luong + $validated['so_luong_cap']
                ]);
            } else {
                $tenHienThi = [
                    'xe_cuu_ho' => 'Xe cứu hộ',
                    'nhu_yeu_pham' => 'Nhu yếu phẩm',
                    'vat_tu_y_te' => 'Vật tư y tế',
                    'dung_cu_thi_cong' => 'Dụng cụ thi công',
                ];
                TaiNguyenCuuHo::create([
                    'id_doi_cuu_ho' => $doi->id_doi_cuu_ho,
                    'ten_tai_nguyen' => $tenHienThi[$loai] ?? $loai,
                        'slug_tai_nguyen' => $loai,
                    'so_luong' => $validated['so_luong_cap'],
                    'trang_thai' => 1,
                ]);
            }

            return Response::json([
                'success' => true,
                'message' => 'Cấp phát tài nguyên thành công cho đội ' . $doi->ten_doi,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get lich su cap phat (placeholder - returns empty for now, can be expanded with a lich_su_cap_phat table)
     */
    public function getLichSuCapPhat(Request $request)
    {
        try {
            $idDoi = $request->get('id_doi_cuu_ho');

            $query = TaiNguyenCuuHo::with('doiCuuHo')
                ->whereNotNull('id_doi_cuu_ho');

            if ($idDoi) {
                $query->where('id_doi_cuu_ho', $idDoi);
            }

            $items = $query->orderBy('id_tai_nguyen', 'desc')->paginate(50);

            return Response::json([
                'success' => true,
                'message' => 'Lịch sử cấp phát',
                'data' => $items
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Checkout / Lấy tài nguyên cho nhiệm vụ
     */
    public function checkout(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'id_phan_cong' => 'required|integer',
                'so_luong' => 'nullable|integer|min:1',
            ]);

            $taiNguyen = TaiNguyenCuuHo::findOrFail($id);
            $soLuong = $validated['so_luong'] ?? 1;

            if ($taiNguyen->so_luong < $soLuong) {
                return Response::json([
                    'success' => false,
                    'message' => 'Số lượng tài nguyên không đủ. Hiện có: ' . $taiNguyen->so_luong
                ], 400);
            }

            $taiNguyen->dang_su_dung_cho_nhiem_vu = $validated['id_phan_cong'];
            $taiNguyen->so_luong_dang_su_dung = ($taiNguyen->so_luong_dang_su_dung ?? 0) + $soLuong;
            $taiNguyen->so_luong = $taiNguyen->so_luong - $soLuong;
            $taiNguyen->save();

            return Response::json([
                'success' => true,
                'message' => 'Đã lấy tài nguyên thành công',
                'data' => $taiNguyen
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ: ' . collect($e->errors())->flatten()->first()
            ], 422);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return / Trả lại tài nguyên từ nhiệm vụ
     * Hỗ trợ trả một phần hoặc toàn bộ số lượng đang sử dụng
     */
    public function returnResource(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'id_phan_cong' => 'required|integer',
                'so_luong'     => 'nullable|integer|min:1',
            ]);

            $taiNguyen = TaiNguyenCuuHo::findOrFail($id);
            $soLuongDaLay = $taiNguyen->so_luong_dang_su_dung ?? 0;

            if ($soLuongDaLay <= 0) {
                return Response::json([
                    'success' => false,
                    'message' => 'Tài nguyên này chưa được lấy cho nhiệm vụ nào'
                ], 400);
            }

            // Số lượng muốn trả lại, mặc định trả toàn bộ
            $soLuongTra = $validated['so_luong'] ?? $soLuongDaLay;

            // Không cho trả vượt số đang dùng
            if ($soLuongTra > $soLuongDaLay) {
                return Response::json([
                    'success' => false,
                    'message' => 'Số lượng trả vượt quá số lượng đang sử dụng. Hiện đang dùng: ' . $soLuongDaLay
                ], 400);
            }

            $taiNguyen->so_luong = $taiNguyen->so_luong + $soLuongTra;
            $taiNguyen->so_luong_dang_su_dung = $soLuongDaLay - $soLuongTra;

            // Nếu trả hết thì xóa gán nhiệm vụ
            if ($taiNguyen->so_luong_dang_su_dung <= 0) {
                $taiNguyen->so_luong_dang_su_dung = 0;
                $taiNguyen->dang_su_dung_cho_nhiem_vu = null;
            }

            $taiNguyen->save();

            $message = $soLuongTra === $soLuongDaLay
                ? 'Đã trả toàn bộ tài nguyên'
                : "Đã trả $soLuongTra / $soLuongDaLay tài nguyên";

            return Response::json([
                'success' => true,
                'message' => $message,
                'data'    => $taiNguyen
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ: ' . collect($e->errors())->flatten()->first()
            ], 422);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
}
