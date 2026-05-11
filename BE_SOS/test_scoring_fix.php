<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\YeuCauCuuHo;
use App\Models\DoiCuuHo;
use App\Services\AutoDispatchService;
use App\Services\DistanceService;

// Test request #54
echo "=== Testing Request #54 (Type 3 - Landslide) AFTER FIX ===" . PHP_EOL;
$request54 = YeuCauCuuHo::find(54);
echo 'Request Location: ' . $request54->vi_tri_lat . ', ' . $request54->vi_tri_lng . PHP_EOL;
echo 'Request Type: ' . $request54->id_loai_su_co . PHP_EOL;

// Get all teams
$teams = DoiCuuHo::where('trang_thai', 'san_sang')->get();

// Calculate distances
$reqLat = floatval($request54->vi_tri_lat);
$reqLng = floatval($request54->vi_tri_lng);

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
}

// Sort by distance and show top 10
$sortedTeams = $teams->sortBy('distance')->take(10);

echo PHP_EOL . "Top 10 Closest Teams (with new scoring):" . PHP_EOL;
foreach ($sortedTeams as $team) {
    $hasMatchingType = false;
    foreach ($team->loaiSuCos as $lsc) {
        if ($lsc->id_loai_su_co == $request54->id_loai_su_co) {
            $hasMatchingType = true;
            break;
        }
    }
    
    $distanceScore = 0;
    $km = $team->distance ?? 0;
    if ($km <= 1) $distanceScore = 10;
    elseif ($km <= 3) $distanceScore = 7;
    elseif ($km <= 5) $distanceScore = 4;
    else $distanceScore = 1;
    
    $typeScore = $hasMatchingType ? 20 : 0; // NEW: 20 points for matching type
    $totalScore = $distanceScore + $typeScore;
    
    echo sprintf(
        "Team %d (%s) - %.2fkm - Type Match: %s - Distance Score: %d - Type Score: %d - Total: %d%s",
        $team->id_doi_cuu_ho,
        $team->ten_doi,
        $km,
        $hasMatchingType ? 'YES' : 'NO',
        $distanceScore,
        $typeScore,
        $totalScore,
        PHP_EOL
    );
}

echo PHP_EOL . "=== Testing Request #55 (Type 4 - Earthquake) AFTER FIX ===" . PHP_EOL;
$request55 = YeuCauCuuHo::find(55);
echo 'Request Location: ' . $request55->vi_tri_lat . ', ' . $request55->vi_tri_lng . PHP_EOL;
echo 'Request Type: ' . $request55->id_loai_su_co . PHP_EOL;

// Calculate distances for request #55
$reqLat55 = floatval($request55->vi_tri_lat);
$reqLng55 = floatval($request55->vi_tri_lng);

$ketQuaKhoangCach55 = $distanceService->calculateDistances($reqLat55, $reqLng55, $viTriDoi);

foreach ($teams as $team) {
    $hqKey = 'hq_' . $team->id_doi_cuu_ho;
    $team->distance55 = $ketQuaKhoangCach55[$hqKey] ?? null;
}

// Sort by distance and show top 10
$sortedTeams55 = $teams->sortBy('distance55')->take(10);

echo PHP_EOL . "Top 10 Closest Teams for Request #55 (with new scoring):" . PHP_EOL;
foreach ($sortedTeams55 as $team) {
    $hasMatchingType = false;
    foreach ($team->loaiSuCos as $lsc) {
        if ($lsc->id_loai_su_co == $request55->id_loai_su_co) {
            $hasMatchingType = true;
            break;
        }
    }
    
    $distanceScore = 0;
    $km = $team->distance55 ?? 0;
    if ($km <= 1) $distanceScore = 10;
    elseif ($km <= 3) $distanceScore = 7;
    elseif ($km <= 5) $distanceScore = 4;
    else $distanceScore = 1;
    
    $typeScore = $hasMatchingType ? 20 : 0; // NEW: 20 points for matching type
    $totalScore = $distanceScore + $typeScore;
    
    echo sprintf(
        "Team %d (%s) - %.2fkm - Type Match: %s - Distance Score: %d - Type Score: %d - Total: %d%s",
        $team->id_doi_cuu_ho,
        $team->ten_doi,
        $km,
        $hasMatchingType ? 'YES' : 'NO',
        $distanceScore,
        $typeScore,
        $totalScore,
        PHP_EOL
    );
}
