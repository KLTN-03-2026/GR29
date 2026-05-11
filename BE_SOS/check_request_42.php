<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== KIEM TRA YEU CAU #42 ===\n\n";

// Kiem tra yeu cau #42
$yeuCau = App\Models\YeuCauCuuHo::with(['phanCongs.doiCuuHo', 'phanCongs.thanhVienTiepNhan'])->find(42);

if (!$yeuCau) {
    echo "Khong tim thay yeu cau #42\n";
    exit;
}

echo "YEU CAU #42:\n";
echo "- ID: " . $yeuCau->id_yeu_cau . "\n";
echo "- Trang thai: " . $yeuCau->trang_thai . "\n";
echo "- Loai su co: " . $yeuCau->id_loai_su_co . "\n";
echo "- Phan cong count: " . $yeuCau->phanCongs->count() . "\n";

if ($yeuCau->phanCongs->isNotEmpty()) {
    $phanCong = $yeuCau->phanCongs->first();
    echo "- Doi duoc phan cong: " . $phanCong->doiCuuHo->ten_doi . " (ID: " . $phanCong->id_doi_cuu_ho . ")\n";
    echo "- Trang thai nhiem vu: " . $phanCong->trang_thai_nhiem_vu . "\n";
    echo "- ID phan cong: " . $phanCong->id_phan_cong . "\n";
}

echo "\n=== KIEM TRA DOI 19 (DOI LU LUT SON TRA) ===\n";

// Kiem tra doi 19
$doi19 = App\Models\DoiCuuHo::with(['thanhViens', 'phanCongs'])->find(19);

if (!$doi19) {
    echo "Khong tim thay doi 19\n";
    exit;
}

echo "DOI 19 - " . $doi19->ten_doi . ":\n";
echo "- So thanh vien: " . $doi19->thanhViens->count() . "\n";
echo "- Capacity (thanh_vien * 4): " . ($doi19->thanhViens->count() * 4) . "\n";

// Dem so luong phan cong theo trang thai
$trangThais = ['MOI', 'DANG_XU_LY', 'DA_PHAN_CONG', 'DA_DEN_HIEN_TRUONG', 'HOAN_THANH', 'THAT_BAI', 'HUY_BO'];

foreach ($trangThais as $trangThai) {
    $count = $doi19->phanCongs->where('trang_thai_nhiem_vu', $trangThai)->count();
    if ($count > 0) {
        echo "- Phan cong " . $trangThai . ": " . $count . "\n";
    }
}

// Dem ACTIVE tasks (DANG_XU_LY, DA_PHAN_CONG, DA_DEN_HIEN_TRUONG)
$activeCount = $doi19->phanCongs->whereIn('trang_thai_nhiem_vu', ['DANG_XU_LY', 'DA_PHAN_CONG', 'DA_DEN_HIEN_TRUONG'])->count();
echo "- ACTIVE tasks (DANG_XU_LY, DA_PHAN_CONG, DA_DEN_HIEN_TRUONG): " . $activeCount . "\n";

// Dem MOI tasks (pending)
$moiCount = $doi19->phanCongs->where('trang_thai_nhiem_vu', 'MOI')->count();
echo "- MOI tasks (pending): " . $moiCount . "\n";

$capacity = $doi19->thanhViens->count() * 4;
$conLai = $capacity - $activeCount;
echo "- Con lai: " . $conLai . " slots\n";

echo "\n=== KIEM TRA TRANG THAI CAC PHAN CONG CUA DOI 19 ===\n";
foreach ($doi19->phanCongs as $phanCong) {
    echo "- YC#" . $phanCong->id_yeu_cau . " | " . $phanCong->trang_thai_nhiem_vu . " | " . $phanCong->mo_ta . "\n";
}

echo "\n=== KIEM TRA TOAN BO DOI TRONG HE THONG ===\n";
$allTeams = App\Models\DoiCuuHo::with(['thanhViens', 'phanCongs'])->get();

foreach ($allTeams as $team) {
    $capacity = $team->thanhViens->count() * 4;
    $activeCount = $team->phanCongs->whereIn('trang_thai_nhiem_vu', ['DANG_XU_LY', 'DA_PHAN_CONG', 'DA_DEN_HIEN_TRUONG'])->count();
    $moiCount = $team->phanCongs->where('trang_thai_nhiem_vu', 'MOI')->count();
    
    echo "- " . str_pad($team->ten_doi, 30) . " | TV: " . str_pad($team->thanhViens->count(), 2) . " | Cap: " . str_pad($capacity, 2) . " | Active: " . str_pad($activeCount, 2) . " | MOI: " . str_pad($moiCount, 2) . " | Con: " . str_pad($capacity - $activeCount, 2) . "\n";
}

echo "\n=== KET THUC KIEM TRA ===\n";
