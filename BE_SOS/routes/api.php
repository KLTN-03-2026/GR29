<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ChucNangController;
use App\Http\Controllers\ChucVuController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\NguoiDungController;
use App\Http\Controllers\LoaiSuCoController;
use App\Http\Controllers\YeuCauCuuHoController;
use App\Http\Controllers\DoiCuuHoController;
use App\Http\Controllers\PhanCongCuuHoController;
use App\Http\Controllers\KetQuaCuuHoController;
use App\Http\Controllers\DanhGiaCuuHoController;
use App\Http\Controllers\ThanhVienDoiController;
use App\Http\Controllers\BaoCaoCuuHoController;

// =========================================
// PUBLIC ROUTES
// =========================================

// Chức năng (Functions/Features)
Route::apiResource('chuc-nang', ChucNangController::class);

// Chức vụ (Positions/Roles)
Route::apiResource('chuc-vu', ChucVuController::class);

// =========================================
// ADMIN AUTH
// =========================================
Route::post('admin/login', [AdminController::class, 'login']);
Route::get('/admin/check-token', [AdminController::class, 'checkAdmin']);
Route::get('/debug/token', function (Request $request) {
    return response()->json([
        'token' => $request->bearerToken(),
        'headers' => $request->headers->all(),
        'user' => Auth::guard('sanctum')->user(),
    ]);
});

// =========================================
// USER AUTH
// =========================================
Route::post('nguoi-dung/login', [NguoiDungController::class, 'login']);
Route::post('nguoi-dung/register', [NguoiDungController::class, 'register']);
Route::get('/nguoi-dung/check-client', [NguoiDungController::class, 'checkClient']);

// =========================================
// PASSWORD RESET
// =========================================
Route::post('password/send-otp', [PasswordResetController::class, 'guiMaOtp']);
Route::post('password/verify-otp', [PasswordResetController::class, 'xacNhanOtp']);
Route::post('password/reset', [PasswordResetController::class, 'datLaiMatKhau']);
Route::post('password/resend-otp', [PasswordResetController::class, 'guiLaiMaOtp']);

// =========================================
// RESCUER AUTH (public)
// =========================================
Route::post('thanh-vien-doi/login', [ThanhVienDoiController::class, 'login']);
Route::post('rescuer/login', [ThanhVienDoiController::class, 'login']);
Route::get('rescuer/check-token', [ThanhVienDoiController::class, 'checkToken']);

// =========================================
// PUBLIC - No Auth Required
// =========================================
Route::apiResource('loai-su-co', LoaiSuCoController::class);
Route::get('loai-su-co/{id}/chi-tiet', [LoaiSuCoController::class, 'getChiTiet']);
Route::get('loai-su-co/theo-trang-thai/{trang_thai}', [LoaiSuCoController::class, 'getByStatus']);
Route::get('loai-su-co/{id}/yeu-cau-cuu-ho', [LoaiSuCoController::class, 'getYeuCauCuuHo']);
Route::get('loai-su-co/{id}/doi-cuu-ho', [LoaiSuCoController::class, 'getDoiCuuHo']);
Route::get('tim-kiem/loai-su-co', [LoaiSuCoController::class, 'search']);
Route::put('loai-su-co/{id}/trang-thai', [LoaiSuCoController::class, 'updateStatus']);

Route::apiResource('yeu-cau-cuu-ho', YeuCauCuuHoController::class);
Route::get('yeu-cau-cuu-ho/{id}/phan-loai', [YeuCauCuuHoController::class, 'getPhanLoai']);
Route::get('yeu-cau-cuu-ho/{id}/hang-doi', [YeuCauCuuHoController::class, 'getHangDoi']);
Route::get('yeu-cau-cuu-ho/{id}/theo-doi', [YeuCauCuuHoController::class, 'theoDoi']);
Route::get('yeu-cau-cuu-ho/theo-doi/danh-sach', [YeuCauCuuHoController::class, 'theoDoiDanhSach']);
Route::get('yeu-cau-cuu-ho/theo-trang-thai/{trang_thai}', [YeuCauCuuHoController::class, 'getByStatus']);
Route::get('yeu-cau-cuu-ho/theo-muc-do-khan-cap/{muc_do}', [YeuCauCuuHoController::class, 'getByUrgency']);
Route::post('yeu-cau-cuu-ho/tim-doi-gan-nhat', [YeuCauCuuHoController::class, 'timDoiGanNhat']);

Route::get('hang-doi-xu-ly', [YeuCauCuuHoController::class, 'getHangDoiXuLy']);
Route::get('hang-doi-xu-ly/theo-trang-thai/{trang_thai}', [YeuCauCuuHoController::class, 'getHangDoiByStatus']);
Route::get('phan-loai-ais/{id_yeu_cau}', [YeuCauCuuHoController::class, 'getPhanLoaiAis']);
Route::post('phan-loai-ais/{id_yeu_cau}/tao-phan-loai', [YeuCauCuuHoController::class, 'createPhanLoaiAis']);

Route::apiResource('doi-cuu-ho', DoiCuuHoController::class);
Route::post('doi-cuu-ho/login', [DoiCuuHoController::class, 'login']);
Route::get('/doi-cuu-ho/check-token', [DoiCuuHoController::class, 'checkThanhVien']);
Route::get('get-doi-cuu-ho/{id}/thanh-vien', [DoiCuuHoController::class, 'getThanhVien']);
Route::post('post-doi-cuu-ho/{id}/thanh-vien', [DoiCuuHoController::class, 'addThanhVien']);
Route::delete('delete-doi-cuu-ho/{id}/thanh-vien/{id_thanh_vien}', [DoiCuuHoController::class, 'removeThanhVien']);
Route::get('get-doi-cuu-ho/{id}/tai-nguyen', [DoiCuuHoController::class, 'getTaiNguyen']);
Route::post('post-doi-cuu-ho/{id}/tai-nguyen', [DoiCuuHoController::class, 'addTaiNguyen']);
Route::put('put-doi-cuu-ho/{id}/tai-nguyen/{id_tai_nguyen}', [DoiCuuHoController::class, 'updateTaiNguyen']);
Route::get('get-doi-cuu-ho/{id}/vi-tri', [DoiCuuHoController::class, 'getViTri']);
Route::post('post-doi-cuu-ho/{id}/vi-tri', [DoiCuuHoController::class, 'addViTri']);
Route::get('get-doi-cuu-ho/{id}/nang-luc', [DoiCuuHoController::class, 'getNangLuc']);
Route::put('put-doi-cuu-ho/{id}/nang-luc', [DoiCuuHoController::class, 'updateNangLuc']);
Route::get('get-doi-cuu-ho/{id}/loai-su-co-dung-xu-ly', [DoiCuuHoController::class, 'getLoaiSuCoDungXuLy']);
Route::post('post-doi-cuu-ho/{id}/loai-su-co-dung-xu-ly', [DoiCuuHoController::class, 'addLoaiSuCoDungXuLy']);
Route::get('doi-cuu-ho/theo-trang-thai/{trang_thai}', [DoiCuuHoController::class, 'getByStatus']);
Route::get('doi-cuu-ho/theo-khu-vuc/{khu_vuc}', [DoiCuuHoController::class, 'getByKhuVuc']);

Route::apiResource('phan-cong-cuu-ho', PhanCongCuuHoController::class);
Route::apiResource('ket-qua-cuu-ho', KetQuaCuuHoController::class, ['except' => ['store', 'destroy']]);
Route::apiResource('danh-gia-cuu-ho', DanhGiaCuuHoController::class, ['except' => ['update', 'destroy']]);
Route::get('get-danh-gia-cuu-ho/yeu-cau/{id_yeu_cau}', [DanhGiaCuuHoController::class, 'getByYeuCau']);
Route::post('post-danh-gia-cuu-ho/yeu-cau/{id_yeu_cau}', [DanhGiaCuuHoController::class, 'createForYeuCau']);

// Statistics
Route::get('thong-ke/tong-so-yeu-cau', [YeuCauCuuHoController::class, 'getTotalRequests']);
Route::get('thong-ke/yeu-cau-theo-loai', [YeuCauCuuHoController::class, 'getRequestsByType']);
Route::get('thong-ke/yeu-cau-theo-muc-do-khan-cap', [YeuCauCuuHoController::class, 'getRequestsByUrgency']);
Route::get('thong-ke/trang-thai-xu-ly', [YeuCauCuuHoController::class, 'getProcessingStatus']);
Route::get('thong-ke/hieu-suat-doi-cuu-ho', [DoiCuuHoController::class, 'getTeamEfficiency']);
Route::get('thong-ke/danh-sach-doi-co-san', [DoiCuuHoController::class, 'getAvailableTeams']);
Route::get('thong-ke/dashboard', [YeuCauCuuHoController::class, 'getDashboardStats']);
Route::get('thong-ke/heatmap', [YeuCauCuuHoController::class, 'getHeatmapData']);
Route::get('tim-kiem/yeu-cau', [YeuCauCuuHoController::class, 'search']);
Route::get('tim-kiem/doi-cuu-ho', [DoiCuuHoController::class, 'search']);

// =========================================
// ADMIN ONLY (requires admin auth)
// =========================================
Route::middleware(['auth:sanctum', 'check.admin'])->group(function () {
    Route::get('admin/profile', [AdminController::class, 'getProfile']);
    Route::post('admin/logout', [AdminController::class, 'logout']);
    Route::get('admin/list', [AdminController::class, 'index']);
    Route::get('admin/chi-tiet/{id}', [AdminController::class, 'show']);
    Route::post('admin/create', [AdminController::class, 'store']);
    Route::put('admin/update/{id}', [AdminController::class, 'update']);
    Route::delete('admin/delete/{id}', [AdminController::class, 'destroy']);
    Route::get('admin/search', [AdminController::class, 'search']);
    Route::put('admin/change-status/{id}', [AdminController::class, 'changeStatus']);
    Route::put('admin/active/{id}', [AdminController::class, 'active']);

    Route::get('/client/profile/data', [NguoiDungController::class, 'getProfile']);
    Route::post('/client/profile/update', [NguoiDungController::class, 'updateProfile']);
    Route::get('nguoi-dung/list', [NguoiDungController::class, 'index']);
    Route::get('nguoi-dung/chi-tiet/{id}', [NguoiDungController::class, 'show']);
    Route::post('nguoi-dung/create', [NguoiDungController::class, 'store']);
    Route::put('nguoi-dung/update/{id}', [NguoiDungController::class, 'update']);
    Route::delete('nguoi-dung/delete/{id}', [NguoiDungController::class, 'destroy']);
    Route::get('nguoi-dung/search', [NguoiDungController::class, 'search']);
    Route::put('nguoi-dung/change-status/{id}', [NguoiDungController::class, 'changeStatus']);

    Route::get('thanh-vien-doi/list', [ThanhVienDoiController::class, 'index']);
    Route::post('thanh-vien-doi/create', [ThanhVienDoiController::class, 'createThanhVien']);
    Route::put('thanh-vien-doi/update/{id}', [ThanhVienDoiController::class, 'update']);
    Route::delete('thanh-vien-doi/delete/{id}', [ThanhVienDoiController::class, 'destroy']);
    Route::put('thanh-vien-doi/change-status/{id}', [ThanhVienDoiController::class, 'updateStatus']);

    // Admin-only: update status, delete
    Route::put('yeu-cau-cuu-ho/{id}', [YeuCauCuuHoController::class, 'update']);
    Route::delete('yeu-cau-cuu-ho/{id}', [YeuCauCuuHoController::class, 'destroy']);
    Route::put('yeu-cau-cuu-ho/{id}/trang-thai', [YeuCauCuuHoController::class, 'updateStatus']);

    Route::put('phan-cong-cuu-ho/{id}', [PhanCongCuuHoController::class, 'update']);
    Route::delete('phan-cong-cuu-ho/{id}', [PhanCongCuuHoController::class, 'destroy']);
    Route::put('phan-cong-cuu-ho/{id}/trang-thai', [PhanCongCuuHoController::class, 'updateStatus']);
    Route::get('phan-cong-cuu-ho/theo-yeu-cau/{id_yeu_cau}', [PhanCongCuuHoController::class, 'getByYeuCau']);
    Route::get('phan-cong-cuu-ho/theo-doi/{id_doi_cuu_ho}', [PhanCongCuuHoController::class, 'getByDoi']);
    Route::get('phan-cong-cuu-ho/theo-trang-thai/{trang_thai}', [PhanCongCuuHoController::class, 'getByStatus']);

    Route::put('doi-cuu-ho/{id}', [DoiCuuHoController::class, 'update']);
    Route::delete('doi-cuu-ho/{id}', [DoiCuuHoController::class, 'destroy']);

    Route::put('ket-qua-cuu-ho/{id}', [KetQuaCuuHoController::class, 'update']);

    Route::get('loai-su-co/{id}', [LoaiSuCoController::class, 'show']);
    Route::put('loai-su-co/{id}', [LoaiSuCoController::class, 'update']);
    Route::delete('loai-su-co/{id}', [LoaiSuCoController::class, 'destroy']);
});

// =========================================
// RESCUER / TEAM AUTH (requires rescuer or admin)
// =========================================
Route::middleware(['auth:sanctum', 'check.rescuer'])->group(function () {
    // Rescue team operations
    Route::get('phan-cong-cuu-ho/theo-doi/{id_doi_cuu_ho}', [PhanCongCuuHoController::class, 'getByDoi']);

    // Rescuer tiếp nhận nhiệm vụ
    Route::post('yeu-cau-cuu-ho/rescuer-nhan-yeu-cau', [YeuCauCuuHoController::class, 'resNhanYeuCau']);

    // Kết quả cứu hộ
    Route::post('post-ket-qua-cuu-ho/phan-cong/{id_phan_cong}', [KetQuaCuuHoController::class, 'createForPhanCong']);
    Route::get('get-ket-qua-cuu-ho/phan-cong/{id_phan_cong}', [KetQuaCuuHoController::class, 'getByPhanCong']);

    // Báo cáo cứu hộ (Issue #4 - rescue reports)
    Route::post('rescuer/gui-bao-cao', [BaoCaoCuuHoController::class, 'guiBaoCao']);
    Route::get('rescuer/bao-cao/theo-doi/{id}', [BaoCaoCuuHoController::class, 'getByDoi']);
    Route::get('rescuer/bao-cao/{id}', [BaoCaoCuuHoController::class, 'show']);
});

// =========================================
// HEALTH CHECK
// =========================================
Route::get('/health', function () {
    return response()->json(['status' => 'OK', 'message' => 'API is running']);
});
