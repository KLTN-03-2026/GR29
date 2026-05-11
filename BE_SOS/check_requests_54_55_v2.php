<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\YeuCauCuuHo;
use App\Models\DoiCuuHo;

echo "=== Rescue Request #54 ===" . PHP_EOL;
$request54 = YeuCauCuuHo::find(54);
if ($request54) {
    echo 'ID: ' . $request54->id_yeu_cau . PHP_EOL;
    echo 'Loai Su Co ID: ' . $request54->id_loai_su_co . PHP_EOL;
    echo 'Toa Do: ' . $request54->toa_do . PHP_EOL;
    echo 'Trang Thai: ' . $request54->trang_thai . PHP_EOL;
    echo 'Lat: ' . $request54->vi_tri_lat . PHP_EOL;
    echo 'Lng: ' . $request54->vi_tri_lng . PHP_EOL;
} else {
    echo 'Not found' . PHP_EOL;
}

echo PHP_EOL . "=== Rescue Request #55 ===" . PHP_EOL;
$request55 = YeuCauCuuHo::find(55);
if ($request55) {
    echo 'ID: ' . $request55->id_yeu_cau . PHP_EOL;
    echo 'Loai Su Co ID: ' . $request55->id_loai_su_co . PHP_EOL;
    echo 'Toa Do: ' . $request55->toa_do . PHP_EOL;
    echo 'Trang Thai: ' . $request55->trang_thai . PHP_EOL;
    echo 'Lat: ' . $request55->vi_tri_lat . PHP_EOL;
    echo 'Lng: ' . $request55->vi_tri_lng . PHP_EOL;
} else {
    echo 'Not found' . PHP_EOL;
}

echo PHP_EOL . "=== Available Teams ===" . PHP_EOL;
$teams = DoiCuuHo::where('trang_thai', 'san_sang')->get();
foreach ($teams as $team) {
    echo 'Team ID: ' . $team->id_doi_cuu_ho . ', Name: ' . $team->ten_doi . ', Loai Su Co: ' . $team->loai_su_co_id . ', Capacity: ' . $team->so_luong_thanh_vien . ', Location: ' . $team->vi_tri_hien_tai . PHP_EOL;
    echo '  Lat: ' . $team->vi_tri_lat . ', Lng: ' . $team->vi_tri_lng . PHP_EOL;
    
    // Get team's incident types
    $loaiSuCos = $team->loaiSuCos;
    echo '  Incident Types: ';
    foreach ($loaiSuCos as $lsc) {
        echo $lsc->id_loai_su_co . ' ';
    }
    echo PHP_EOL;
}
