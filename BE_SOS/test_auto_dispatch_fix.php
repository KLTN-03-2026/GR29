<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\YeuCauCuuHo;
use App\Models\DoiCuuHo;
use App\Models\PhanCongCuuHo;
use App\Services\AutoDispatchService;

// First, let's reset the requests to CHO_XU_LY state for testing
echo "=== Resetting requests for testing ===" . PHP_EOL;

$request54 = YeuCauCuuHo::find(54);
if ($request54) {
    // Remove existing assignments
    PhanCongCuuHo::where('id_yeu_cau', 54)->delete();
    $request54->update(['trang_thai' => 'CHO_XU_LY']);
    echo "Reset request #54 to CHO_XU_LY" . PHP_EOL;
}

$request55 = YeuCauCuuHo::find(55);
if ($request55) {
    // Remove existing assignments
    PhanCongCuuHo::where('id_yeu_cau', 55)->delete();
    $request55->update(['trang_thai' => 'CHO_XU_LY']);
    echo "Reset request #55 to CHO_XU_LY" . PHP_EOL;
}

// Test auto dispatch
$autoDispatchService = new AutoDispatchService(new \App\Services\DistanceService(new \GuzzleHttp\Client()));

echo PHP_EOL . "=== Testing Auto Dispatch for Request #54 ===" . PHP_EOL;
$result54 = $autoDispatchService->xuLyDieuPhoiTuDong(54);
echo "Result: " . json_encode($result54, JSON_PRETTY_PRINT) . PHP_EOL;

echo PHP_EOL . "=== Testing Auto Dispatch for Request #55 ===" . PHP_EOL;
$result55 = $autoDispatchService->xuLyDieuPhoiTuDong(55);
echo "Result: " . json_encode($result55, JSON_PRETTY_PRINT) . PHP_EOL;

// Check final assignments
echo PHP_EOL . "=== Final Assignments ===" . PHP_EOL;

$assignment54 = PhanCongCuuHo::where('id_yeu_cau', 54)->first();
if ($assignment54) {
    $team54 = DoiCuuHo::find($assignment54->id_doi_cuu_ho);
    echo "Request #54 assigned to Team: " . $team54->ten_doi . " (ID: " . $team54->id_doi_cuu_ho . ")" . PHP_EOL;
    echo "Team incident types: ";
    foreach ($team54->loaiSuCos as $lsc) {
        echo $lsc->id_loai_su_co . " ";
    }
    echo PHP_EOL;
    echo "Request incident type: " . $request54->id_loai_su_co . PHP_EOL;
} else {
    echo "Request #54 not assigned" . PHP_EOL;
}

$assignment55 = PhanCongCuuHo::where('id_yeu_cau', 55)->first();
if ($assignment55) {
    $team55 = DoiCuuHo::find($assignment55->id_doi_cuu_ho);
    echo "Request #55 assigned to Team: " . $team55->ten_doi . " (ID: " . $team55->id_doi_cuu_ho . ")" . PHP_EOL;
    echo "Team incident types: ";
    foreach ($team55->loaiSuCos as $lsc) {
        echo $lsc->id_loai_su_co . " ";
    }
    echo PHP_EOL;
    echo "Request incident type: " . $request55->id_loai_su_co . PHP_EOL;
} else {
    echo "Request #55 not assigned" . PHP_EOL;
}
