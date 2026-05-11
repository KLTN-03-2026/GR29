<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DoiCuuHo;
use App\Models\PhanCongCuuHo;
use App\Models\YeuCauCuuHo;
use Illuminate\Support\Facades\DB;

echo "=== KIỂM TRA VẤN ĐỀ YÊU CẦU #68 VÀ #69 ===\n\n";

// Kiểm tra Đội Lũ lụt Sơn Trà (id: 19)
$doiSonTra = DoiCuuHo::with(['thanhViens', 'phanCongs'])->find(19);
if ($doiSonTra) {
    echo "Đội Lũ lụt Sơn Trà (ID: 19):\n";
    echo "- Số thành viên: " . $doiSonTra->thanhViens->count() . "\n";
    echo "- Capacity (thành viên * 1): " . ($doiSonTra->thanhViens->count() * 1) . "\n";
    
    // Đếm các trạng thái active theo ACTIVE_STATUSES mới
    $activeStatuses = ['CHO_XU_LY', 'DA_DIEU_PHOI', 'DANG_DI_CHUYEN', 'DANG_XU_LY', 'MOI'];
    
    echo "\nChi tiết các phân công:\n";
    $phanCongs = PhanCongCuuHo::where('id_doi_cuu_ho', 19)->get();
    foreach ($phanCongs as $pc) {
        $isActive = in_array($pc->trang_thai_nhiem_vu, $activeStatuses);
        echo "- Yêu cầu #{$pc->id_yeu_cau}: {$pc->trang_thai_nhiem_vu} " . ($isActive ? "[ACTIVE]" : "[INACTIVE]") . "\n";
    }
    
    $activeCount = PhanCongCuuHo::where('id_doi_cuu_ho', 19)
        ->whereIn('trang_thai_nhiem_vu', $activeStatuses)
        ->count();
    
    echo "\nTổng số active count: $activeCount\n";
    echo "Capacity: " . ($doiSonTra->thanhViens->count() * 1) . "\n";
    echo "Đã đầy chưa? " . ($activeCount >= ($doiSonTra->thanhViens->count() * 1) ? "CÓ" : "CHƯA") . "\n\n";
}

// Kiểm tra yêu cầu #68 và #69
echo "=== KIỂM TRA YÊU CẦU #68 VÀ #69 ===\n";

foreach ([68, 69] as $idYeuCau) {
    $yeuCau = YeuCauCuuHo::find($idYeuCau);
    if ($yeuCau) {
        echo "\nYêu cầu #$idYeuCau:\n";
        echo "- Trạng thái: {$yeuCau->trang_thai}\n";
        echo "- Vị trí: {$yeuCau->vi_tri_lat}, {$yeuCau->vi_tri_lng}\n";
        echo "- Loại sự cố: {$yeuCau->id_loai_su_co}\n";
        
        $phanCong = PhanCongCuuHo::where('id_yeu_cau', $idYeuCau)->first();
        if ($phanCong) {
            $doiDuocGan = DoiCuuHo::find($phanCong->id_doi_cuu_ho);
            echo "- Được gán cho: {$doiDuocGan->ten_doi} (ID: {$doiDuocGan->id_doi_cuu_ho})\n";
            echo "- Trạng thái phân công: {$phanCong->trang_thai_nhiem_vu}\n";
        } else {
            echo "- Chưa được gán\n";
        }
    }
}

// Kiểm tra các đội khác gần vị trí của yêu cầu #69
echo "\n=== KIỂM TRA CÁC ĐỘI GẦN VỊ TRÍ YÊU CẦU #69 ===\n";

$yeuCau69 = YeuCauCuuHo::find(69);
if ($yeuCau69) {
    $tatCaDoi = DoiCuuHo::with(['thanhViens', 'phanCongs'])->get();
    
    foreach ($tatCaDoi as $doi) {
        $capacity = $doi->thanhViens->count() * 1;
        $activeCount = PhanCongCuuHo::where('id_doi_cuu_ho', $doi->id_doi_cuu_ho)
            ->whereIn('trang_thai_nhiem_vu', $activeStatuses)
            ->count();
        $conSlot = $capacity - $activeCount;
        
        echo "- {$doi->ten_doi} (ID: {$doi->id_doi_cuu_ho}): Capacity=$capacity, Active=$activeCount, Còn slot=$conSlot\n";
    }
}
