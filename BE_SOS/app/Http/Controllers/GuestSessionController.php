<?php

namespace App\Http\Controllers;

use App\Models\GuestSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GuestSessionController extends Controller
{
    /**
     * Tạo mới hoặc cập nhật guest session
     * POST /api/guest/session
     */
    public function storeOrUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|max:100',
            'so_dien_thoai' => 'nullable|string|regex:/^0\d{9,10}$/',
            'guest_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors(),
            ], 422);
        }

        $deviceId = $request->input('device_id');
        $soDienThoai = $request->input('so_dien_thoai');
        $guestName = $request->input('guest_name');

        $session = GuestSession::where('device_id', $deviceId)->first();

        if ($session) {
            $session->update([
                'so_dien_thoai' => $soDienThoai ?? $session->so_dien_thoai,
                'guest_name' => $guestName ?? $session->guest_name,
                'last_active_at' => now(),
            ]);
        } else {
            $session = GuestSession::create([
                'device_id' => $deviceId,
                'so_dien_thoai' => $soDienThoai,
                'guest_name' => $guestName,
                'is_linked' => false,
                'last_active_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Guest session da duoc tao hoac cap nhat',
            'data' => $session,
        ], 200);
    }
}
