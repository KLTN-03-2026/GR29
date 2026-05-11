<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\YeuCauCuuHo;
use App\Models\DoiCuuHo;
use App\Models\PhanCongCuuHo;
use App\Services\AutoDispatchService;

// Reset request #55 again
echo "=== Resetting Request #55 ===" . PHP_EOL;
PhanCongCuuHo::where('id_yeu_cau', 55)->delete();
$request55 = YeuCauCuuHo::find(55);
$request55->update(['trang_thai' => 'CHO_XU_LY']);

// Enable debug logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo PHP_EOL . "=== Running Auto Dispatch with Debug ===" . PHP_EOL;

// Let's manually call the internal method to see what teams are being considered
$autoDispatchService = new AutoDispatchService(new \App\Services\DistanceService(new \GuzzleHttp\Client()));

// Use reflection to access private method
$reflection = new ReflectionClass($autoDispatchService);
$method = $reflection->getMethod('layDanhSachDoiGanNhatInternal');
$method->setAccessible(true);

$teams = $method->invoke($autoDispatchService, $request55);

echo "Teams considered by AutoDispatchService:" . PHP_EOL;
foreach ($teams as $team) {
    echo sprintf(
        "Team %d (%s) - %.2fkm - Capacity: %d - Active: %d",
        $team->id_doi_cuu_ho,
        $team->ten_doi,
        $team->distance,
        $team->capacity,
        $team->active_count_real
    );
    echo ' - Types: ';
    foreach ($team->loaiSuCos as $lsc) {
        echo $lsc->id_loai_su_co . ' ';
    }
    echo PHP_EOL;
}

echo PHP_EOL . "=== Running Full Dispatch ===" . PHP_EOL;
$result = $autoDispatchService->xuLyDieuPhoiTuDong(55);
echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
