<?php

/**
 * Test script để kiểm tra logic điều phối mới
 * Mô phỏng kịch bản: Yêu cầu động đất tại Ngũ Hành Sơn
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\AutoDispatchService;
use App\Models\YeuCauCuuHo;
use App\Models\DoiCuuHo;

echo "=== TEST LOGIC ĐIỀU PHỐI MỚI ===\n\n";

// Mô phỏng dữ liệu test
$testCases = [
    [
        'name' => 'Động đất Ngũ Hành Sơn - Đội cùng loại gần nhất',
        'yeu_cau' => [
            'id_loai_su_co' => 1, // Giả sử 1 = Động đất
            'vi_tri_lat' => 16.0000,
            'vi_tri_lng' => 108.2600,
            'dia_chi' => 'Ngũ Hành Sơn, Đà Nẵng',
            'muc_do_khan_cap' => 'HIGH'
        ],
        'doi_du_kien' => [
            [
                'ten_doi' => 'Đội Cứu Hộ Động Đất Ngũ Hành Sơn',
                'khu_vuc' => 'Ngũ Hành Sơn',
                'vi_tri_lat' => 16.0010,
                'vi_tri_lng' => 108.2610,
                'loai_su_co' => [1], // Có xử lý động đất
                'khoang_cach_km' => 0.15
            ],
            [
                'ten_doi' => 'Đội Cứu Hộ Cháy Nổ Hải Châu',
                'khu_vuc' => 'Hải Châu',
                'vi_tri_lat' => 16.0600,
                'vi_tri_lng' => 108.2200,
                'loai_su_co' => [2], // Không xử lý động đất
                'khoang_cach_km' => 8.5
            ]
        ]
    ]
];

foreach ($testCases as $index => $testCase) {
    echo "Test Case " . ($index + 1) . ": " . $testCase['name'] . "\n";
    echo str_repeat("-", 60) . "\n";
    
    // Tính điểm cho từng đội
    foreach ($testCase['doi_du_kien'] as $doi) {
        echo "Đội: " . $doi['ten_doi'] . "\n";
        echo "  - Khu vực: " . $doi['khu_vuc'] . "\n";
        echo "  - Khoảng cách: " . $doi['khoang_cach_km'] . " km\n";
        echo "  - Có xử lý động đất: " . (in_array(1, $doi['loai_su_co']) ? "Có" : "Không") . "\n";
        
        // Tính điểm theo logic mới
        $diemNguyHiem = 10; // Động đất có điểm nguy hiểm cao
        $diemKhoangCach = tinhDiemKhoangCachMoi($doi['khoang_cach_km']);
        $diemLoaiSuCo = in_array(1, $doi['loai_su_co']) ? 6 : 0;
        $diemTai = 2; // Giả sử đội có tải thấp
        $diemThoiGian = 1; // Giả sử chờ 5 phút
        
        $diemTong = $diemNguyHiem + $diemKhoangCach + $diemLoaiSuCo + $diemTai + $diemThoiGian;
        
        echo "  - Điểm nguy hiểm: " . $diemNguyHiem . "\n";
        echo "  - Điểm khoảng cách: " . $diemKhoangCach . "\n";
        echo "  - Điểm loại sự cố: " . $diemLoaiSuCo . "\n";
        echo "  - Điểm tải: " . $diemTai . "\n";
        echo "  - Điểm thời gian: " . $diemThoiGian . "\n";
        echo "  - === TỔNG ĐIỂM: " . $diemTong . " ===\n\n";
    }
    
    echo "KẾT LUẬN: ";
    // Xác định đội thắng
    $doiThang = null;
    $diemCaoNhat = -1;
    
    foreach ($testCase['doi_du_kien'] as $doi) {
        $diemNguyHiem = 10;
        $diemKhoangCach = tinhDiemKhoangCachMoi($doi['khoang_cach_km']);
        $diemLoaiSuCo = in_array(1, $doi['loai_su_co']) ? 6 : 0;
        $diemTai = 2;
        $diemThoiGian = 1;
        
        $diemTong = $diemNguyHiem + $diemKhoangCach + $diemLoaiSuCo + $diemTai + $diemThoiGian;
        
        if ($diemTong > $diemCaoNhat) {
            $diemCaoNhat = $diemTong;
            $doiThang = $doi['ten_doi'];
        }
    }
    
    echo $doiThang . " sẽ được chọn với " . $diemCaoNhat . " điểm\n\n";
}

function tinhDiemKhoangCachMoi($km) {
    if ($km <= 1) return 10;   // Rất gần
    if ($km <= 3) return 7;    // Gần
    if ($km <= 5) return 4;    // Trung bình
    return 1;                   // Xa nhưng vẫn có điểm
}

echo "=== KẾT THÚC TEST ===\n";
