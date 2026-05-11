<?php

/**
 * Giới hạn gửi yêu cầu cứu hộ (store) — logic trong YeuCauCuuHoController.
 *
 * ── Khi test / dev ─────────────────────────────────────────────
 * 1) Tắt hẳn giới hạn: .env đặt RESCUE_STORE_RATE_LIMIT_ENABLED=false rồi php artisan config:clear
 *
 * 2) Xóa bộ đếm cache cho đúng thiết bị (chuỗi guest_device_id trong localStorage trình duyệt):
 *    php artisan rescue:clear-rate-limit --device="device_xxx..."
 *
 * 3) Xóa bộ đếm theo tài khoản đã đăng nhập (id_nguoi_dung):
 *    php artisan rescue:clear-rate-limit --user=5
 *
 * 4) Xóa toàn bộ cache Laravel (mọi rate limit + cache khác):
 *    php artisan cache:clear
 *
 * Khóa nội bộ RateLimiter: rescue-store:device:{sha256(device_id)} | rescue-store:user:{id}
 */
return [

    'enabled' => env('RESCUE_STORE_RATE_LIMIT_ENABLED', true),

];
