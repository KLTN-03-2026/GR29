<?php

namespace App\Providers;

use App\Events\CoDoiTrongTroLai;
use App\Jobs\AutoDispatchJob;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Broadcast::routes();

        require base_path('routes/channels.php');

        // Khi có đội cứu hộ có slot trống trở lại,
        // tự động dispatch lại các yêu cầu đang chờ cùng loại sự cố
        Event::listen(CoDoiTrongTroLai::class, function (CoDoiTrongTroLai $suKien) {
            $cacIdLoaiSuCo = $suKien->payload['loai_su_co_ids'] ?? [];
            if (!empty($cacIdLoaiSuCo)) {
                AutoDispatchJob::dispatchLaiYeuCauChoDoi($cacIdLoaiSuCo);
            }
        });
    }
}
