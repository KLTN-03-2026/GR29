<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\YeuCauCuuHo;
use App\Models\DoiCuuHo;
use App\Services\AutoDispatchService;
use App\Services\DistanceService;

echo "=== Debug Request #55 Assignment ===" . PHP_EOL;

$request55 = YeuCauCuuHo::find(55);
echo 'Request #55:' . PHP_EOL;
echo '  Type: ' . $request55->id_loai_su_co . PHP_EOL;
echo '  Location: ' . $request55->vi_tri_lat . ', ' . $request55->vi_tri_lng . PHP_EOL;

// Get all teams with their distances
$teams = DoiCuuHo::where('trang_thai', 'san_sang')->get();

$reqLat = floatval($request55->vi_tri_lat);
$reqLng = floatval($request55->vi_tri_lng);

$distanceService = new DistanceService(new \GuzzleHttp\Client());
$viTriDoi = [];
foreach ($teams as $team) {
    if ($team->vi_tri_lat !== null && $team->vi_tri_lng !== null) {
        $viTriDoi[] = [
            'key' => 'hq_' . $team->id_doi_cuu_ho,
            'lat' => floatval($team->vi_tri_lat),
            'lng' => floatval($team->vi_tri_lng),
        ];
    }
}

$ketQuaKhoangCach = $distanceService->calculateDistances($reqLat, $reqLng, $viTriDoi);

foreach ($teams as $team) {
    $hqKey = 'hq_' . $team->id_doi_cuu_ho;
    $team->distance = $ketQuaKhoangCach[$hqKey] ?? null;
    
    // Check capacity
    $soThanhVien = $team->thanhViens ? $team->thanhViens->count() : 0;
    $team->capacity = $soThanhVien * 1;
    
    // Check active assignments
    $activeCount = \App\Models\PhanCongCuuHo::where('id_doi_cuu_ho', $team->id_doi_cuu_ho)
        ->whereIn('trang_thai_nhiem_vu', ['MOI', 'DANG_XU_LY', 'DA_PHAN_CONG', 'DA_DEN_HIEN_TRUONG'])
        ->count();
    $team->active_count_real = $activeCount;
}

// Filter teams with capacity
$availableTeams = $teams->filter(function ($team) {
    $capacity = $team->capacity ?? 0;
    $active = $team->active_count_real ?? 0;
    return $capacity > 0 && $active < $capacity;
});

echo PHP_EOL . "Available teams (with capacity):" . PHP_EOL;
foreach ($availableTeams as $team) {
    $hasMatchingType = false;
    foreach ($team->loaiSuCos as $lsc) {
        if ($lsc->id_loai_su_co == $request55->id_loai_su_co) {
            $hasMatchingType = true;
            break;
        }
    }
    
    echo sprintf(
        "Team %d (%s) - %.2fkm - Capacity: %d/%d - Type Match: %s",
        $team->id_doi_cuu_ho,
        $team->ten_doi,
        $team->distance,
        $team->active_count_real,
        $team->capacity,
        $hasMatchingType ? 'YES' : 'NO'
    );
    echo ' - Types: ';
    foreach ($team->loaiSuCos as $lsc) {
        echo $lsc->id_loai_su_co . ' ';
    }
    echo PHP_EOL;
}

// Check specifically Team 13 (the one that got assigned)
$team13 = DoiCuuHo::find(13);
echo PHP_EOL . "=== Team 13 (assigned team) details ===" . PHP_EOL;
echo 'Name: ' . $team13->ten_doi . PHP_EOL;
echo 'Location: ' . $team13->vi_tri_lat . ', ' . $team13->vi_tri_lng . PHP_EOL;
echo 'Distance to request: ' . $team13->distance . 'km' . PHP_EOL;
echo 'Capacity: ' . $team13->capacity . PHP_EOL;
echo 'Active assignments: ' . $team13->active_count_real . PHP_EOL;
echo 'Incident types: ';
foreach ($team13->loaiSuCos as $lsc) {
    echo $lsc->id_loai_su_co . ' ';
}
echo PHP_EOL;
