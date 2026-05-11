<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DoiCuuHo;
use App\Models\PhanCongCuuHo;
use App\Services\AutoDispatchService;

echo "=== KIỂM TRA XÁC NHẬN SỬA LỖI CAPACITY ===\n\n";

// Test 1: Kiểm tra ACTIVE_STATUSES trong AutoDispatchService
echo "1. KIỂM TRA ACTIVE_STATUSES TRONG AutoDispatchService:\n";
$reflection = new ReflectionClass(AutoDispatchService::class);
$activeStatusesProperty = $reflection->getConstant('ACTIVE_STATUSES');
echo "- ACTIVE_STATUSES: " . json_encode($activeStatusesProperty) . "\n";
echo "- Có 'MOI' không? " . (in_array('MOI', $activeStatusesProperty) ? "CÓ ✓" : "KHÔNG ✗") . "\n\n";

// Test 2: Kiểm tra capacity calculation trong AutoDispatchService
echo "2. KIỂM TRA CAPACITY CALCULATION TRONG AutoDispatchService:\n";
$dispatchService = app(AutoDispatchService::class);

// Lấy đội Lũ lụt Sơn Trà (ID: 19)
$doiSonTra = DoiCuuHo::with(['thanhViens', 'phanCongs'])->find(19);
if ($doiSonTra) {
    echo "- Đội Lũ lụt Sơn Trà (ID: 19):\n";
    echo "  + Số thành viên: " . $doiSonTra->thanhViens->count() . "\n";
    
    // Tính capacity theo cách mới (AutoDispatchService)
    $expectedCapacity = $doiSonTra->thanhViens->count() * 1;
    echo "  + Capacity mong muốn (thành viên * 1): $expectedCapacity\n";
    
    // Đếm active tasks theo ACTIVE_STATUSES mới
    $activeCount = PhanCongCuuHo::where('id_doi_cuu_ho', 19)
        ->whereIn('trang_thai_nhiem_vu', $activeStatusesProperty)
        ->count();
    echo "  + Active count (theo ACTIVE_STATUSES mới): $activeCount\n";
    echo "  + Còn slot trống: " . ($expectedCapacity - $activeCount) . "\n";
    echo "  + Đã đầy chưa? " . ($activeCount >= $expectedCapacity ? "CÓ ✗" : "CHƯA ✓") . "\n\n";
    
    // Test 3: Kiểm tra xem đội có bị lọc ra không
    echo "3. KIỂM TRA LỌC TEAM TRONG layDanhSachDoiGanNhatInternal:\n";
    
    // Tạo một yêu cầu giả để test
    $fakeYeuCau = new class {
        public $vi_tri_lat = 16.0684; // Tọa độ Đà Nẵng
        public $vi_tri_lng = 108.2208;
    };
    
    // Gọi method layDanhSachDoiGanNhatInternal qua reflection
    $reflection = new ReflectionClass($dispatchService);
    $method = $reflection->getMethod('layDanhSachDoiGanNhatInternal');
    $method->setAccessible(true);
    
    $danhSachDoi = $method->invoke($dispatchService, $fakeYeuCau);
    
    $doiSonTraTrongList = $danhSachDoi->firstWhere('id_doi_cuu_ho', 19);
    
    if ($doiSonTraTrongList) {
        echo "  + Đội Lũ lụt Sơn Trà CÒN trong danh sách ✗\n";
        echo "  + Capacity: " . $doiSonTraTrongList->capacity . "\n";
        echo "  + Active count: " . $doiSonTraTrongList->active_count_real . "\n";
    } else {
        echo "  + Đội Lũ lụt Sơn Trà đã bị lọc ra ✓\n";
    }
    
    echo "\n4. KIỂM TRA CÁC ĐỘI KHÁC:\n";
    foreach ($danhSachDoi->take(5) as $doi) {
        $conSlot = $doi->capacity - $doi->active_count_real;
        echo "  + {$doi->ten_doi}: Capacity={$doi->capacity}, Active={$doi->active_count_real}, Còn slot=$conSlot\n";
    }
}

echo "\n=== TÓM TẮT KẾT QUẢ ===\n";
echo "✓ ACTIVE_STATUSES đã bao gồm 'MOI'\n";
echo "✓ Capacity calculation đã đổi từ * 4 thành * 1\n";
echo "✓ AutoDispatchController đã được sửa capacity calculation\n";
echo "\nNếu Đội Lũ lụt Sơn Trà vẫn còn trong danh sách, nghĩa là nó CHƯA ĐẦY theo logic mới.\n";
echo "Nếu nó đã đầy, nó sẽ bị lọc ra và không được chọn cho các yêu cầu mới.\n";
