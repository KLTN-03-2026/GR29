<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get issue #58
$yeuCau58 = App\Models\YeuCauCuuHo::find(58);

// Get the two teams
$lienChieu = App\Models\DoiCuuHo::where('ten_doi', 'Đội Lũ lụt Liên Chiểu')->first();
$thanhKhe = App\Models\DoiCuuHo::where('ten_doi', 'Đội Lũ lụt Thanh Khê')->first();

$autoDispatchService = app(App\Services\AutoDispatchService::class);

echo "=== PHÂN TÍCH SCORING CHO ISSUE #58 ===\n\n";
echo "Vị trí yêu cầu: " . $yeuCau58->vi_tri_lat . ", " . $yeuCau58->vi_tri_lng . "\n";
echo "Loại sự cố: " . ($yeuCau58->loaiSuCo->ten_loai_su_co ?? 'N/A') . "\n\n";

// Calculate distances
$distanceService = app(App\Services\DistanceService::class);
$viTriDoi = [
    ['key' => 'hq_' . $lienChieu->id_doi_cuu_ho, 'lat' => floatval($lienChieu->vi_tri_lat), 'lng' => floatval($lienChieu->vi_tri_lng)],
    ['key' => 'hq_' . $thanhKhe->id_doi_cuu_ho, 'lat' => floatval($thanhKhe->vi_tri_lat), 'lng' => floatval($thanhKhe->vi_tri_lng)],
];

$distances = $distanceService->calculateDistances(
    floatval($yeuCau58->vi_tri_lat),
    floatval($yeuCau58->vi_tri_lng),
    $viTriDoi
);

$lienChieu->distance = $distances['hq_' . $lienChieu->id_doi_cuu_ho];
$thanhKhe->distance = $distances['hq_' . $thanhKhe->id_doi_cuu_ho];

// Load relationships for scoring
$lienChieu->load(['thanhViens', 'phanCongs']);
$thanhKhe->load(['thanhViens', 'phanCongs']);

// Calculate scores for both teams
$teams = [$lienChieu, $thanhKhe];
$teamNames = ['Đội Lũ lụt Liên Chiểu', 'Đội Lũ lụt Thanh Khê'];

foreach ($teams as $index => $team) {
    echo "=== " . $teamNames[$index] . " ===\n";
    
    // Calculate real-time capacity
    $soThanhVien = $team->thanhViens->count();
    $capacity = $soThanhVien * 1;
    $activeCount = App\Models\PhanCongCuuHo::where('id_doi_cuu_ho', $team->id_doi_cuu_ho)
        ->whereIn('trang_thai_nhiem_vu', ['MOI', 'DANG_XU_LY', 'DA_PHAN_CONG', 'DA_DEN_HIEN_TRUONG'])
        ->count();
    
    $team->so_thanh_vien = $soThanhVien;
    $team->capacity = $capacity;
    $team->active_count_real = $activeCount;
    
    // Calculate individual scores
    $diemNguyHiem = $autoDispatchService->tinhDiemNguyHiem($yeuCau58);
    $diemKhoangCach = $autoDispatchService->tinhDiemKhoangCach($team);
    $diemTai = $autoDispatchService->tinhDiemTai($team);
    $diemThoiGian = $autoDispatchService->tinhDiemThoiGian($yeuCau58);
    $diemLoaiSuCo = $autoDispatchService->tinhDiemLoaiSuCo($yeuCau58, $team);
    
    $diemTong = $diemNguyHiem + $diemKhoangCach + $diemTai + $diemThoiGian + $diemLoaiSuCo;
    
    echo "Khoảng cách: " . round($team->distance, 2) . " km\n";
    echo "Số thành viên: " . $soThanhVien . " (Capacity: " . $capacity . ")\n";
    echo "Active tasks: " . $activeCount . "\n";
    echo "Available slots: " . ($capacity - $activeCount) . "\n";
    echo "Trạng thái: " . $team->trang_thai . "\n";
    echo "\n";
    echo "Điểm nguy hiểm: " . $diemNguyHiem . "\n";
    echo "Điểm khoảng cách: " . $diemKhoangCach . "\n";
    echo "Đõi tải: " . $diemTai . "\n";
    echo "Điểm thời gian: " . $diemThoiGian . "\n";
    echo "Điểm loại sự cố: " . $diemLoaiSuCo . "\n";
    echo "----------------------------------------\n";
    echo "TỔNG ĐIỂM: " . $diemTong . "\n\n";
}

echo "=== KẾT LUẬN ===\n";
echo "Đội Lũ lụt Liên Chiểu được chọn vì:\n";
echo "1. Khoảng cách gần hơn (1.39 km vs 3.09 km)\n";
echo "2. Điểm khoảng cách cao hơn (10 điểm vs 4 điểm)\n";
echo "3. Mặc dù có nhiều active tasks hơn nhưng vẫn còn capacity\n";
echo "4. Tổng điểm cao hơn nên được ưu tiên điều phối\n";
