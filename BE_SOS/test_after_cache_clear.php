<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST LOGIC CAPACITY SAU KHI CLEAR CACHE ===\n\n";

// Test AutoDispatchService moi
$dispatchService = new App\Services\AutoDispatchService(new App\Services\DistanceService());

// Kiem tra ACTIVE_STATUSES
$reflection = new ReflectionClass($dispatchService);
$activeStatuses = $reflection->getConstant('ACTIVE_STATUSES');

echo "ACTIVE_STATUSES hien tai: " . json_encode($activeStatuses) . "\n";

// Kiem tra doi 19
$doi19 = App\Models\DoiCuuHo::with(['thanhViens', 'phanCongs'])->find(19);

echo "\nDOI 19 - " . $doi19->ten_doi . ":\n";
echo "- So thanh vien: " . $doi19->thanhViens->count() . "\n";
echo "- Capacity (thanh_vien * 4): " . ($doi19->thanhViens->count() * 4) . "\n";

// Dem theo logic moi
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

// Test tinhDiemTaiInternal
echo "\n=== TEST TINH DIEM TAI INTERNAL ===\n";
$diemTai = $dispatchService->tinhDiemTai($doi19);
echo "Diem tai cua doi 19: " . $diemTai . "\n";

// Test layDanhSachDoiGanNhatInternal
echo "\n=== TEST LAY DANH SACH DOI GAN NHAT ===\n";
// Tao yeu cau gia de test
$yeuCauTest = new App\Models\YeuCauCuuHo();
$yeuCauTest->id_yeu_cau = 999;
$yeuCauTest->vi_tri_lat = 16.10295815;
$yeuCauTest->vi_tri_lng = 108.24691888;
$yeuCauTest->id_loai_su_co = 1;

$danhSachDoi = $dispatchService->layDanhSachDoiGanNhat($yeuCauTest);

echo "So doi duoc lay: " . $danhSachDoi->count() . "\n";
foreach ($danhSachDoi as $doi) {
    echo "- " . str_pad($doi->ten_doi, 30) . " | TV: " . str_pad($doi->thanhViens->count(), 2) . " | Cap: " . str_pad($doi->capacity, 2) . " | Active: " . str_pad($doi->active_count_real, 2) . " | Con: " . str_pad($doi->capacity - $doi->active_count_real, 2) . "\n";
}

echo "\n=== KET THUC TEST ===\n";
