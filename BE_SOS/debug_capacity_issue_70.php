<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DoiCuuHo;
use App\Models\PhanCongCuuHo;
use App\Services\AutoDispatchService;

echo "=== DEBUG CAPACITY ISSUE YÊU CẦU #70 ===\n\n";

// Kiểm tra trực tiếp trong AutoDispatchService
echo "1. KIỂM TRA TRỰC TIẾP AutoDispatchService:\n";
$dispatchService = app(AutoDispatchService::class);

// Lấy đội Lũ lụt Sơn Trà (ID: 19)
$doiSonTra = DoiCuuHo::with(['thanhViens', 'phanCongs'])->find(19);
if ($doiSonTra) {
    echo "- Đội Lũ lụt Sơn Trà (ID: 19):\n";
    echo "  + Số thành viên: " . $doiSonTra->thanhViens->count() . "\n";
    
    // Tính capacity thủ công
    $expectedCapacity = $doiSonTra->thanhViens->count() * 1;
    echo "  + Capacity mong muốn (thành viên * 1): $expectedCapacity\n";
    
    // Kiểm tra ACTIVE_STATUSES
    $reflection = new ReflectionClass($dispatchService);
    $activeStatuses = $reflection->getConstant('ACTIVE_STATUSES');
    echo "  + ACTIVE_STATUSES: " . json_encode($activeStatuses) . "\n";
    
    // Đếm active count
    $activeCount = PhanCongCuuHo::where('id_doi_cuu_ho', 19)
        ->whereIn('trang_thai_nhiem_vu', $activeStatuses)
        ->count();
    echo "  + Active count: $activeCount\n";
    echo "  + Còn slot: " . ($expectedCapacity - $activeCount) . "\n";
    echo "  + Đã đầy? " . ($activeCount >= $expectedCapacity ? "CÓ" : "KHÔNG") . "\n\n";
}

// Kiểm tra xem có method nào khác tính capacity không
echo "2. KIỂM TRA CÁC METHOD TÍNH CAPACITY KHÁC:\n";

// Tìm tất cả file có chứa "* 4"
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (strpos($content, '* 4') !== false && strpos($content, 'capacity') !== false) {
            echo "- File: " . $file->getPathname() . "\n";
            $lines = file($file->getPathname());
            foreach ($lines as $lineNum => $line) {
                if (strpos($line, '* 4') !== false && strpos($line, 'capacity') !== false) {
                    echo "  + Dòng " . ($lineNum + 1) . ": " . trim($line) . "\n";
                }
            }
        }
    }
}

// Kiểm tra cache
echo "\n3. KIỂM TRA CACHE:\n";
$cacheKeys = [
    'auto_dispatch_enabled',
    'dispatch_lock_70',
    'teams_capacity_cache',
    'doi_list_cache'
];

foreach ($cacheKeys as $key) {
    $value = cache()->get($key);
    echo "- $key: " . ($value ? 'CÓ giá trị' : 'KHÔNG có') . "\n";
}

echo "\n4. KIỂM TRA YÊU CẦU #70:\n";
$yeuCau70 = \App\Models\YeuCauCuuHo::find(70);
if ($yeuCau70) {
    echo "- Trạng thái: {$yeuCau70->trang_thai}\n";
    $phanCong = PhanCongCuuHo::where('id_yeu_cau', 70)->first();
    if ($phanCong) {
        $doiDuocGan = DoiCuuHo::find($phanCong->id_doi_cuu_ho);
        echo "- Được gán cho: {$doiDuocGan->ten_doi} (ID: {$doiDuocGan->id_doi_cuu_ho})\n";
        echo "- Trạng thái phân công: {$phanCong->trang_thai_nhiem_vu}\n";
    }
}
