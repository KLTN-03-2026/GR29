<?php

namespace App\Http\Controllers;

use App\Models\ThanhVienDoi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ThanhVienDoiController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'mat_khau' => 'required',
        ]);

        $user = ThanhVienDoi::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->mat_khau, $user->mat_khau)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Đăng nhập thành công',
            'token' => $token,
            'token_type' => 'Bearer',
            'data' => $user->load('doiCuuHo'),
        ]);
    }

    public function index()
    {
        $thanhVien = ThanhVienDoi::with('doiCuuHo')->get();
        return response()->json($thanhVien);
    }

    public function createThanhVien(Request $request)
    {
        $request->validate([
            'ho_ten' => 'required',
            'email' => 'required|email|unique:thanh_vien_dois,email',
            'so_dien_thoai' => 'required',
            'vai_tro_trong_doi' => 'required',
            'mat_khau' => 'required',
            'id_doi_cuu_ho' => 'nullable|exists:doi_cuu_hos,id_doi_cuu_ho',
        ]);

        $data = $request->all();
        $data['mat_khau'] = Hash::make($data['mat_khau']);
        $data['trang_thai'] = 1;

        $thanhVien = ThanhVienDoi::create($data);
        return response()->json([
            'status' => true,
            'message' => 'Thêm thành viên thành công',
            'data' => $thanhVien
        ]);
    }

    public function update(Request $request, $id)
    {
        $thanhVien = ThanhVienDoi::findOrFail($id);

        $data = $request->only(['ho_ten', 'email', 'so_dien_thoai', 'vai_tro_trong_doi', 'id_doi_cuu_ho']);
        if ($request->has('mat_khau') && !empty($request->mat_khau)) {
            $data['mat_khau'] = Hash::make($request->mat_khau);
        }

        $thanhVien->update($data);
        return response()->json([
            'status' => true,
            'message' => 'Cập nhật thành công',
            'data' => $thanhVien
        ]);
    }

    public function destroy($id)
    {
        $thanhVien = ThanhVienDoi::findOrFail($id);
        $thanhVien->delete();
        return response()->json([
            'status' => true,
            'message' => 'Xóa thành công'
        ]);
    }

    public function updateStatus($id)
    {
        $thanhVien = ThanhVienDoi::findOrFail($id);
        $thanhVien->trang_thai = $thanhVien->trang_thai == 1 ? 0 : 1;
        $thanhVien->save();
        return response()->json([
            'status' => true,
            'message' => 'Cập nhật trạng thái thành công',
            'data' => $thanhVien
        ]);
    }

    public function checkToken()
    {
        $user = Auth::guard('thanh-vien-doi')->user();
        if ($user) {
            return response()->json([
                'status' => true,
                'ho_ten' => $user->ho_ten,
                'vai_tro_trong_doi' => $user->vai_tro_trong_doi,
                'id_doi_cuu_ho' => $user->id_doi_cuu_ho,
                'data' => $user->load('doiCuuHo'),
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Token không hợp lệ hoặc đã hết hạn.',
        ], 401);
    }

    public function rescuerIndex()
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user || !isset($user->id_doi_cuu_ho)) {
            return response()->json([], 200);
        }
        $thanhVien = ThanhVienDoi::where('id_doi_cuu_ho', $user->id_doi_cuu_ho)->get();
        return response()->json($thanhVien);
    }

    public function rescuerCreate(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user || !isset($user->id_doi_cuu_ho)) {
            return response()->json(['status' => false, 'message' => 'Lỗi xác thực'], 403);
        }

        $request->validate([
            'ho_ten' => 'required',
            'email' => 'required|email|unique:thanh_vien_doi,email',
            'so_dien_thoai' => 'required',
            'vai_tro_trong_doi' => 'required',
            'mat_khau' => 'required',
        ]);

        $data = $request->all();
        $data['mat_khau'] = Hash::make($data['mat_khau']);
        $data['trang_thai'] = 1;
        $data['id_doi_cuu_ho'] = $user->id_doi_cuu_ho;

        $thanhVien = ThanhVienDoi::create($data);
        return response()->json([
            'status' => true,
            'message' => 'Thêm thành viên thành công',
            'data' => $thanhVien
        ]);
    }

    public function rescuerUpdate(Request $request, $id)
    {
        $user = Auth::guard('sanctum')->user();
        $thanhVien = ThanhVienDoi::where('id_doi_cuu_ho', $user->id_doi_cuu_ho)->findOrFail($id);

        $data = $request->only(['ho_ten', 'email', 'so_dien_thoai', 'vai_tro_trong_doi']);
        if ($request->has('mat_khau') && !empty($request->mat_khau)) {
            $data['mat_khau'] = Hash::make($request->mat_khau);
        }

        $thanhVien->update($data);
        return response()->json([
            'status' => true,
            'message' => 'Cập nhật thành công',
            'data' => $thanhVien
        ]);
    }

    public function rescuerDestroy($id)
    {
        $user = Auth::guard('sanctum')->user();
        $thanhVien = ThanhVienDoi::where('id_doi_cuu_ho', $user->id_doi_cuu_ho)->findOrFail($id);
        
        // Remove from team instead of deleting
        $thanhVien->id_doi_cuu_ho = null;
        $thanhVien->vai_tro_trong_doi = 'Khách'; // Or unset
        $thanhVien->save();

        return response()->json([
            'status' => true,
            'message' => 'Xóa thành viên khỏi đội thành công'
        ]);
    }

    /**
     * Get members filtered by role.
     * MANAGER_TEAM (0): all members across all teams
     * TEAMLEAD (1): only members of the same team
     * MEMBER (2): only members of the same team
     */
    public function getMembersFiltered()
    {
        $currentUser = Auth::guard('thanh-vien-doi')->user();

        if (!$currentUser) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $role = $currentUser->vai_tro_trong_doi;

        // MANAGER_TEAM: return all members
        if ($role == 0) {
            $members = ThanhVienDoi::with('doiCuuHo')->get();
            return response()->json([
                'status' => true,
                'data' => $members,
            ]);
        }

        // TEAMLEAD or MEMBER: return only members of the same team
        if ($currentUser->id_doi_cuu_ho) {
            $members = ThanhVienDoi::with('doiCuuHo')
                ->where('id_doi_cuu_ho', $currentUser->id_doi_cuu_ho)
                ->get();
        } else {
            $members = ThanhVienDoi::with('doiCuuHo')
                ->whereNull('id_doi_cuu_ho')
                ->get();
        }

        return response()->json([
            'status' => true,
            'data' => $members,
        ]);
    }
}
