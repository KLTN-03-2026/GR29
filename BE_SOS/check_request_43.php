<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== KIEM TRA YEU CAU #43 ===\n\n";

// Kiem tra yeu cau #43
$yeuCau43 = App\Models\YeuCauCuuHo::with(['phanCongs.doiCuuHo', 'phanCongs.thanhVienTiepNhan'])->find(43);

if (!$yeuCau43) {
    echo "Khong tim thay yeu cau #43\n";
    exit;
}

echo "YEU CAU #43:\n";
echo "- ID: " . $yeuCau43->id_yeu_cau . "\n";
echo "- Trang thai: " . $yeuCau43->trang_thai . "\n";
echo "- Loai su co: " . $yeuCau43->id_loai_su_co . "\n";
echo "- Phan cong count: " . $yeuCau43->phanCongs->count() . "\n";

if ($yeuCau43->phanCongs->isNotEmpty()) {
    $phanCong = $yeuCau43->phanCongs->first();
    echo "- Doi duoc phan cong: " . $phanCong->doiCuuHo->ten_doi . " (ID: " . $phanCong->id_doi_cuu_ho . ")\n";
    echo "- Trang thai nhiem vu: " . $phanCong->trang_thai_nhiem_vu . "\n";
    echo "- ID phan cong: " . $phanCong->id_phan_cong . "\n";
    echo "- Mo ta: " . $phanCong->mo_ta . "\n";
    echo "- Thoi gian tao: " . $phanCong->created_at . "\n";
} else {
    echo "- CHUA CO PHAN CONG\n";
}

echo "\n=== KIEM TRA DOI 19 TRUOC KHI PHAN CONG #43 ===\n";

// Kiem tra doi 19
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

echo "\n=== THOI GIAN CAC PHAN CONG GAN NHAT CUA DOI 19 ===\n";
$recentAssignments = $doi19->phanCongs->sortByDesc('created_at')->take(10);

foreach ($recentAssignments as $phanCong) {
    echo "- YC#" . $phanCong->id_yeu_cau . " | " . $phanCong->trang_thai_nhiem_vu . " | " . $phanCong->created_at . " | " . $phanCong->mo_ta . "\n";
}

echo "\n=== KIEM TRA LOG CHO YEU CAU #43 ===\n";
$logFile = 'c:\xampp\htdocs\DATN\SOS\GR29\BE_SOS\storage\logs\laravel.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    
    foreach ($lines as $line) {
        if (strpos($line, 'id_yeu_cau.*43') !== false || strpos($line, 'yeu_cau.*43') !== false) {
            echo $line . "\n";
        }
    }
} else {
    echo "Khong tim thay file log\n";
}

echo "\n=== KET THUC KIEM TRA ===\n";
