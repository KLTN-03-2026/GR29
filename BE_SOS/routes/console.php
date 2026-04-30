<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * AutoDispatch Queue Worker Commands
 *
 * Chạy các lệnh sau trong terminal riêng:
 *
 * Development:
 *   php artisan queue:work                   # Chạy tất cả queues
 *   php artisan queue:work --queue=auto-dispatch  # Chỉ auto-dispatch queue
 *
 * Production (Linux):
 *   Supervisor config đặt trong /etc/supervisor/conf.d/
 */
