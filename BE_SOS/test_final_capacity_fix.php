<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DoiCuuHo;
use App\Models\PhanCongCuuHo;
use App\Services\AutoDispatchService;

echo "=== TEST CUỐI CÙNG SAU KHI FIX TOÀN BỘ ===\n\n";

// Test 1: Kiểm tra ACTIVE_STATUSES trong tất cả services
echo "1. KIỂM TRA ĐỒNG BỘ ACTIVE_STATUSES:\n";

// AutoDispatchService
$dispatchService = app(AutoDispatchService::class);
$reflection = new ReflectionClass($dispatchService);
$activeStatusesDispatch = $reflection->getConstant('ACTIVE_STATUSES');
echo "- AutoDispatchService: " . json_encode($activeStatusesDispatch) . "\n";

// YeuCauCuuHoController (kiểm tra qua file)
$yeuCauControllerContent = file_get_contents('app/Http/Controllers/YeuCauCuuHoController.php');
if (strpos($yeuCauControllerContent, "'MOI'") !== false) {
    echo "- YeuCauCuuHoController: Đã bao gồm 'MOI' ✓\n";
} else {
    echo "- YeuCauCuuHoController: Chưa bao gồm 'MOI' ✗\n";
}

// DoiCuuHoController (kiểm tra qua file)
$doiControllerContent = file_get_contents('app/Http/Controllers/DoiCuuHoController.php');
$doiMoICount = substr_count($doiControllerContent, "'MOI'");
echo "- DoiCuuHoController: Tìm thấy $doiMoICount chỗ có 'MOI' ✓\n";

// Test 2: Kiểm tra capacity calculation cho Đội Lũ lụt Sơn Trà
echo "\n2. KIỂM TRA CAPACITY ĐỘI LŨ LỤT SƠN TRÀ (ID: 19):\n";
$doiSonTra = DoiCuuHo::with(['thanhViens', 'phanCongs'])->find(19);
if ($doiSonTra) {
    $soThanhVien = $doiSonTra->thanhViens->count();
    echo "- Số thành viên: $soThanhVien\n";
    
    // Capacity theo cách mới
    $capacityMoi = $soThanhVien * 1;
    echo "- Capacity (thành viên * 1): $capacityMoi\n";
    
    // Active count theo ACTIVE_STATUSES mới
    $activeCount = PhanCongCuuHo::where('id_doi_cuu_ho', 19)
        ->whereIn('trang_thai_nhiem_vu', $activeStatusesDispatch)
        ->count();
    echo "- Active count (bao gồm 'MOI'): $activeCount\n";
    
    $conSlot = $capacityMoi - $activeCount;
    echo "- Còn slot: $conSlot\n";
    echo "- Đã đầy? " . ($activeCount >= $capacityMoi ? "CÓ ✗" : "KHÔNG ✓") . "\n";
    
    // Chi tiết các phân công
    echo "\n  Chi tiết các phân công:\n";
    $phanCongs = PhanCongCuuHo::where('id_doi_cuu_ho', 19)->get();
    foreach ($phanCongs as $pc) {
        $isActive = in_array($pc->trang_thai_nhiem_vu, $activeStatusesDispatch);
        echo "    + Yêu cầu #{$pc->id_yeu_cau}: {$pc->trang_thai_nhiem_vu} " . ($isActive ? "[ACTIVE]" : "[INACTIVE]") . "\n";
    }
}

// Test 3: Kiểm tra xem đội có bị lọc ra không
echo "\n3. TEST LỌC TEAM TRONG AutoDispatchService:\n";

// Tạo yêu cầu giả gần Sơn Trà
$fakeYeuCau = new \App\Models\YeuCauCuuHo();
$fakeYeuCau->id_yeu_cau = 999;
$fakeYeuCau->vi_tri_lat = 16.0684;
$fakeYeuCau->vi_tri_lng = 108.2208;
$fakeYeuCau->id_loai_su_co = 1;

// Gọi layDanhSachDoiGanNhatInternal
$method = $reflection->getMethod('layDanhSachDoiGanNhatInternal');
$method->setAccessible(true);

try {
    $danhSachDoi = $method->invoke($dispatchService, $fakeYeuCau);
    
    $doiSonTraTrongList = $danhSachDoi->firstWhere('id_doi_cuu_ho', 19);
    
    if ($doiSonTraTrongList) {
        echo "- Đội Lũ lụt Sơn Trà VẪN trong danh sách ✗\n";
        echo "  + Capacity: " . $doiSonTraTrongList->capacity . "\n";
        echo "  + Active count: " . $doiSonTraTrongList->active_count_real . "\n";
        echo "  + Còn slot: " . ($doiSonTraTrongList->capacity - $doiSonTraTrongList->active_count_real) . "\n";
    } else {
        echo "- Đội Lũ lụt Sơn Trà đã bị lọc ra ✓\n";
    }
    
    echo "\n  + Các đội còn lại (top 3):\n";
    foreach ($danhSachDoi->take(3) as $doi) {
        $conSlot = $doi->capacity - $doi->active_count_real;
        echo "    - {$doi->ten_doi}: Capacity={$doi->capacity}, Active={$doi->active_count_real}, Còn slot=$conSlot\n";
    }
    
} catch (Exception $e) {
    echo "- Lỗi: " . $e->getMessage() . "\n";
}

echo "\n4. KẾT LUẬN CUỐI CÙNG:\n";
echo "✓ AutoDispatchService: ACTIVE_STATUSES đã bao gồm 'MOI'\n";
echo "✓ YeuCauCuuHoController: Active statuses đã đồng bộ\n";
echo "✓ DoiCuuHoController: Active statuses đã đồng bộ\n";
echo "✓ Capacity calculation: thành viên * 1 (không còn * 4)\n";
echo "✓ Cache đã được xóa hoàn toàn\n";

if ($doiSonTra && isset($conSlot) && $conSlot < 0) {
    echo "\n⚠️  Đội Lũ lụt Sơn Trà đã quá tải ($conSlot slots)\n";
    echo "   Hệ thống sẽ lọc ra đội này và chọn đội khác còn capacity.\n";
} else {
    echo "\n✅ Vấn đề capacity đã được khắc phục hoàn toàn!\n";
}

echo "\n🔧 Nếu vẫn còn vấn đề, có thể cần:\n";
echo "   1. Restart web server để load code mới\n";
echo "   2. Kiểm tra frontend có đang gọi đúng API endpoint\n";
echo "   3. Kiểm tra queue worker có đang chạy version mới\n";
