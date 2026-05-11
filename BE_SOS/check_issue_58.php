<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check issue #58 details
echo "=== ISSUE #58 DETAILS ===\n";
$yeuCau58 = App\Models\YeuCauCuuHo::where('id_yeu_cau', 58)
    ->with(['phanCongs.doiCuuHo', 'loaiSuCo'])
    ->first();

if ($yeuCau58) {
    echo "ID: " . $yeuCau58->id_yeu_cau . "\n";
    echo "Trạng thái: " . $yeuCau58->trang_thai . "\n";
    echo "Loại sự cố: " . ($yeuCau58->loaiSuCo->ten_loai_su_co ?? 'N/A') . "\n";
    echo "Vị trí: " . $yeuCau58->vi_tri_lat . ", " . $yeuCau58->vi_tri_lng . "\n";
    echo "Địa chỉ: " . ($yeuCau58->dia_chi ?? 'N/A') . "\n";
    echo "Thời gian tạo: " . $yeuCau58->created_at . "\n";
    
    echo "\n=== PHÂN CÔNG ===\n";
    foreach ($yeuCau58->phanCongs as $phanCong) {
        echo "Đội: " . $phanCong->doiCuuHo->ten_doi . "\n";
        echo "Trạng thái nhiệm vụ: " . $phanCong->trang_thai_nhiem_vu . "\n";
        echo "Thời gian phân công: " . $phanCong->created_at . "\n";
        echo "Mô tả: " . $phanCong->mo_ta . "\n";
    }
} else {
    echo "Không tìm thấy yêu cầu #58\n";
}

echo "\n\n=== TEAM DETAILS ===\n";
// Check both teams
$teams = App\Models\DoiCuuHo::whereIn('ten_doi', ['Đội Lũ lụt Liên Chiểu', 'Đội Lũ lụt Thanh Khê'])
    ->with(['thanhViens', 'phanCongs' => function($q) {
        $q->whereIn('trang_thai_nhiem_vu', ['MOI', 'DANG_XU_LY', 'DA_PHAN_CONG', 'DA_DEN_HIEN_TRUONG']);
    }])
    ->get();

foreach ($teams as $team) {
    echo "\n--- " . $team->ten_doi . " ---\n";
    echo "ID: " . $team->id_doi_cuu_ho . "\n";
    echo "Vị trí: " . $team->vi_tri_lat . ", " . $team->vi_tri_lng . "\n";
    echo "Số thành viên: " . $team->thanhViens->count() . "\n";
    echo "Capacity: " . ($team->thanhViens->count() * 1) . "\n";
    echo "Active tasks: " . $team->phanCongs->count() . "\n";
    echo "Trạng thái: " . $team->trang_thai . "\n";
    
    // Calculate distance to issue #58
    if ($yeuCau58 && $team->vi_tri_lat && $team->vi_tri_lng) {
        $distanceService = app(App\Services\DistanceService::class);
        $viTriDoi = [[
            'key' => 'hq_' . $team->id_doi_cuu_ho,
            'lat' => floatval($team->vi_tri_lat),
            'lng' => floatval($team->vi_tri_lng),
        ]];
        
        $distances = $distanceService->calculateDistances(
            floatval($yeuCau58->vi_tri_lat),
            floatval($yeuCau58->vi_tri_lng),
            $viTriDoi
        );
        
        $distance = $distances['hq_' . $team->id_doi_cuu_ho] ?? null;
        echo "Khoảng cách đến yêu cầu #58: " . ($distance ? round($distance, 2) . ' km' : 'N/A') . "\n";
    }
}

echo "\n\n=== ALL TEAMS WITH DISTANCES ===\n";
// Get all teams with their distances to issue #58
if ($yeuCau58) {
    $allTeams = App\Models\DoiCuuHo::with(['thanhViens', 'phanCongs' => function($q) {
        $q->whereIn('trang_thai_nhiem_vu', ['MOI', 'DANG_XU_LY', 'DA_PHAN_CONG', 'DA_DEN_HIEN_TRUONG']);
    }])->get();
    
    $distanceService = app(App\Services\DistanceService::class);
    $viTriDoi = [];
    
    foreach ($allTeams as $team) {
        if ($team->vi_tri_lat && $team->vi_tri_lng) {
            $viTriDoi[] = [
                'key' => 'hq_' . $team->id_doi_cuu_ho,
                'lat' => floatval($team->vi_tri_lat),
                'lng' => floatval($team->vi_tri_lng),
            ];
        }
    }
    
    $distances = $distanceService->calculateDistances(
        floatval($yeuCau58->vi_tri_lat),
        floatval($yeuCau58->vi_tri_lng),
        $viTriDoi
    );
    
    $teamsWithDistance = [];
    foreach ($allTeams as $team) {
        $hqKey = 'hq_' . $team->id_doi_cuu_ho;
        $team->distance = $distances[$hqKey] ?? null;
        
        $capacity = $team->thanhViens->count() * 1;
        $active = $team->phanCongs->count();
        $available = $capacity - $active;
        
        $teamsWithDistance[] = [
            'name' => $team->ten_doi,
            'distance' => $team->distance,
            'capacity' => $capacity,
            'active' => $active,
            'available' => $available,
        ];
    }
    
    // Sort by distance
    usort($teamsWithDistance, function($a, $b) {
        if ($a['distance'] === null) return 1;
        if ($b['distance'] === null) return -1;
        return $a['distance'] <=> $b['distance'];
    });
    
    foreach ($teamsWithDistance as $team) {
        if ($team['distance'] !== null) {
            echo sprintf(
                "%-30s | Khoảng cách: %6.2f km | Capacity: %d | Active: %d | Available: %d\n",
                $team['name'],
                $team['distance'],
                $team['capacity'],
                $team['active'],
                $team['available']
            );
        }
    }
}
