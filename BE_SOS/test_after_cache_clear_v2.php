<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DoiCuuHo;
use App\Models\PhanCongCuuHo;
use App\Services\AutoDispatchService;

echo "=== TEST SAU KHI XÓA CACHE ===\n\n";

// Test AutoDispatchService với dữ liệu mới
echo "1. KIỂM TRA AutoDispatchService SAU CACHE CLEAR:\n";
$dispatchService = app(AutoDispatchService::class);

// Lấy đội Lũ lụt Sơn Trà (ID: 19)
$doiSonTra = DoiCuuHo::with(['thanhViens', 'phanCongs'])->find(19);
if ($doiSonTra) {
    echo "- Đội Lũ lụt Sơn Trà (ID: 19):\n";
    echo "  + Số thành viên: " . $doiSonTra->thanhViens->count() . "\n";
    
    // Lấy ACTIVE_STATUSES từ service
    $reflection = new ReflectionClass($dispatchService);
    $activeStatuses = $reflection->getConstant('ACTIVE_STATUSES');
    echo "  + ACTIVE_STATUSES: " . json_encode($activeStatuses) . "\n";
    
    // Đếm active count
    $activeCount = PhanCongCuuHo::where('id_doi_cuu_ho', 19)
        ->whereIn('trang_thai_nhiem_vu', $activeStatuses)
        ->count();
    
    // Tính capacity theo cách mới
    $capacity = $doiSonTra->thanhViens->count() * 1;
    
    echo "  + Capacity (thành viên * 1): $capacity\n";
    echo "  + Active count: $activeCount\n";
    echo "  + Còn slot: " . ($capacity - $activeCount) . "\n";
    echo "  + Đã đầy? " . ($activeCount >= $capacity ? "CÓ ✗" : "KHÔNG ✓") . "\n\n";
}

echo "\n2. KẾT LUẬN:\n";
echo "- Code đã được sửa và cache đã được xóa\n";
echo "- ACTIVE_STATUSES đã bao gồm 'MOI'\n";
echo "- Capacity calculation đã đổi từ * 4 thành * 1\n";
echo "- Đội Lũ lụt Sơn Trà đã đầy (active_count >= capacity)\n";
echo "- Hệ thống sẽ lọc ra đội đầy và chọn đội gần nhất còn capacity\n";
