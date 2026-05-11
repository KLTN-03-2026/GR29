<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== KIEM TRA LOGIC CAPACITY DA SUA ===\n\n";

// Kiem tra doi 19 voi logic moi
$doi19 = App\Models\DoiCuuHo::with(['thanhViens', 'phanCongs'])->find(19);

echo "DOI 19 - " . $doi19->ten_doi . ":\n";
echo "- So thanh vien: " . $doi19->thanhViens->count() . "\n";
echo "- Capacity (thanh_vien * 4): " . ($doi19->thanhViens->count() * 4) . "\n";

// Dem theo logic MOI (ACTIVE_STATUSES = ['MOI', 'DANG_XU_LY', 'DA_PHAN_CONG', 'DA_DEN_HIEN_TRUONG'])
$activeStatuses = ['MOI', 'DANG_XU_LY', 'DA_PHAN_CONG', 'DA_DEN_HIEN_TRUONG'];
$activeCount = 0;

foreach ($activeStatuses as $status) {
    $count = $doi19->phanCongs->where('trang_thai_nhiem_vu', $status)->count();
    if ($count > 0) {
        echo "- Phan cong " . $status . ": " . $count . "\n";
        $activeCount += $count;
    }
}

$capacity = $doi19->thanhViens->count() * 4;
$conLai = $capacity - $activeCount;
echo "- Tong ACTIVE tasks: " . $activeCount . "\n";
echo "- Con lai: " . $conLai . " slots\n";

if ($activeCount >= $capacity) {
    echo "=> DOI DA DAY (KHONG THE NHAN THEM)\n";
} else {
    echo "=> DOI CON SLOT (CO THE NHAN THEM)\n";
}

echo "\n=== KIEM TRA CAC DOI KHAC ===\n";
$allTeams = App\Models\DoiCuuHo::with(['thanhViens', 'phanCongs'])->get();

foreach ($allTeams as $team) {
    $capacity = $team->thanhViens->count() * 4;
    $activeCount = 0;
    
    foreach ($activeStatuses as $status) {
        $activeCount += $team->phanCongs->where('trang_thai_nhiem_vu', $status)->count();
    }
    
    $conLai = $capacity - $activeCount;
    $status = ($activeCount >= $capacity) ? "DAY" : "CON";
    
    echo "- " . str_pad($team->ten_doi, 30) . " | TV: " . str_pad($team->thanhViens->count(), 2) . " | Cap: " . str_pad($capacity, 2) . " | Active: " . str_pad($activeCount, 2) . " | Con: " . str_pad($conLai, 2) . " | " . $status . "\n";
}

echo "\n=== TEST AUTO DISPATCH VOI LOGIC MOI ===\n";

// Test auto dispatch cho yeu cau #43 (neu co)
$yeuCauTest = App\Models\YeuCauCuuHo::find(43);
if ($yeuCauTest && !$yeuCauTest->phanCongs()->exists()) {
    echo "Test auto dispatch cho yeu cau #43...\n";
    
    $dispatchService = new App\Services\AutoDispatchService(new App\Services\DistanceService());
    $ketQua = $dispatchService->xuLyDieuPhoiTuDong(43);
    
    echo "Ket qua: " . ($ketQua['thanh_cong'] ? "THANH CONG" : "THAT BAI") . "\n";
    echo "Thong diep: " . $ketQua['thong_diep'] . "\n";
    if ($ketQua['thanh_cong']) {
        echo "Doi duoc phan cong: " . $ketQua['doi_id'] . "\n";
    }
} else {
    echo "Khong co yeu cau #43 de test (hoi da co phan cong)\n";
}

echo "\n=== KET THUC KIEM TRA ===\n";
