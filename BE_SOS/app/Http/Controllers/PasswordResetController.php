<?php

namespace App\Http\Controllers;

use App\Mail\GuiMaOtp;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Bước 1: Gửi mã OTP đến email
     */
    public function guiMaOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->email));

        // Tìm người dùng với email này
        $user = NguoiDung::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Email không tồn tại trong hệ thống',
            ], 404);
        }

        // Xóa các OTP cũ trước (nếu có)
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Tạo mã OTP 6 số ngẫu nhiên
        $maOtp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Lưu vào database với thời hạn 5 phút
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'ma_otp' => $maOtp,
            'ho_ten' => $user->ho_ten,
            'created_at' => now(),
            'expires_at' => now()->addMinutes(5),
        ]);

        // Gửi email
        try {
            Mail::to($email)->send(new GuiMaOtp($maOtp, $user->ho_ten));

            return response()->json([
                'status' => true,
                'message' => 'Mã xác nhận đã được gửi đến email của bạn',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể gửi email. Vui lòng thử lại sau.',
            ], 500);
        }
    }

    /**
     * Bước 2: Xác minh mã OTP
     */
    public function xacNhanOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'ma_otp' => 'required|string|size:6',
        ]);

        $email = strtolower(trim($request->email));

        // Tìm OTP trong database
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('ma_otp', $request->ma_otp)
            ->first();

        if (!$record) {
            return response()->json([
                'status' => false,
                'message' => 'Mã xác nhận không đúng',
            ], 400);
        }

        // Kiểm tra đã hết hạn chưa
        if (now()->greaterThan($record->expires_at)) {
            // Xóa OTP hết hạn
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return response()->json([
                'status' => false,
                'message' => 'Mã xác nhận đã hết hạn. Vui lòng yêu cầu mã mới.',
            ], 400);
        }

        // Tạo token tạm để đặt lại mật khẩu
        $resetToken = Str::random(64);

        // Cập nhật record với reset token
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->update([
                'ma_otp' => $resetToken,
                'expires_at' => now()->addMinutes(30),
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Mã xác nhận hợp lệ',
            'reset_token' => $resetToken,
        ]);
    }

    /**
     * Bước 3: Đặt lại mật khẩu mới
     */
    public function datLaiMatKhau(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'reset_token' => 'required|string',
            'mat_khau_moi' => 'required|string|min:6',
        ]);

        // Kiểm tra mật khẩu xác nhận (frontend đã validate, nhưng vẫn check ở đây)
        if ($request->has('mat_khau_moi_confirmation') && $request->mat_khau_moi !== $request->mat_khau_moi_confirmation) {
            return response()->json([
                'status' => false,
                'message' => 'Mật khẩu xác nhận không khớp',
            ], 400);
        }

        $email = strtolower(trim($request->email));

        // Tìm record với reset token
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('ma_otp', $request->reset_token)
            ->first();

        if (!$record) {
            return response()->json([
                'status' => false,
                'message' => 'Token không hợp lệ',
            ], 400);
        }

        // Kiểm tra đã hết hạn chưa
        if (now()->greaterThan($record->expires_at)) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return response()->json([
                'status' => false,
                'message' => 'Token đã hết hạn. Vui lòng yêu cầu mã mới.',
            ], 400);
        }

        // Cập nhật mật khẩu mới
        $user = NguoiDung::where('email', $email)->first();
        if ($user) {
            $user->mat_khau = \Illuminate\Support\Facades\Hash::make($request->mat_khau_moi);
            $user->save();
        }

        // Xóa record sau khi đặt lại thành công
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Đặt lại mật khẩu thành công. Bạn có thể đăng nhập ngay.',
        ]);
    }

    /**
     * Gửi lại mã OTP (nếu người dùng cần)
     */
    public function guiLaiMaOtp(Request $request)
    {
        return $this->guiMaOtp($request);
    }
}