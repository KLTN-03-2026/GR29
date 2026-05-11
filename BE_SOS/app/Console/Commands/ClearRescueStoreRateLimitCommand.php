<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\RateLimiter;

class ClearRescueStoreRateLimitCommand extends Command
{
    protected $signature = 'rescue:clear-rate-limit
                            {--device= : Chuỗi device_id (giống guest_device_id trong localStorage)}
                            {--user= : id_nguoi_dung khi gửi đã đăng nhập}';

    protected $description = 'Xóa bộ đếm giới hạn gửi yêu cầu cứu hộ (3/15 phút) để tiện test';

    public function handle(): int
    {
        $device = $this->option('device');
        $user = $this->option('user');

        if ($device === null && $user === null) {
            $this->warn('Chưa truyền tham số. Ví dụ:');
            $this->line('  php artisan rescue:clear-rate-limit --device="device_1730000000_abc123xyz"');
            $this->line('  php artisan rescue:clear-rate-limit --user=3');
            $this->line('Hoặc tắt giới hạn trong .env: RESCUE_STORE_RATE_LIMIT_ENABLED=false');

            return self::FAILURE;
        }

        if ($device !== null && $device !== '') {
            $key = 'rescue-store:device:'.hash('sha256', (string) $device);
            RateLimiter::clear($key);
            $this->info("Đã xóa bộ đếm thiết bị: {$key}");
        }

        if ($user !== null && $user !== '') {
            $key = 'rescue-store:user:'.(int) $user;
            RateLimiter::clear($key);
            $this->info("Đã xóa bộ đếm user: {$key}");
        }

        return self::SUCCESS;
    }
}
