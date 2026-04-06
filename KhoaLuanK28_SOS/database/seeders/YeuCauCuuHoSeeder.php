<?php

namespace Database\Seeders;

use App\Models\YeuCauCuuHo;
use App\Models\NguoiDung;
use App\Models\LoaiSuCo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class YeuCauCuuHoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $users = NguoiDung::pluck('id_nguoi_dung')->toArray();
        $types = LoaiSuCo::pluck('id_loai_su_co')->toArray();

        $locations = [
            ['dia_chi' => '123 Đường Lê Duẩn, Hải Châu', 'lat' => 16.0678, 'lng' => 108.2208],
            ['dia_chi' => '456 Đường Nguyễn Văn Linh, Hải Châu', 'lat' => 16.0600, 'lng' => 108.2200],
            ['dia_chi' => 'Cầu Rồng, Hải Châu', 'lat' => 16.0617, 'lng' => 108.2270],
            ['dia_chi' => 'Biển Mỹ Khê, Sơn Trà', 'lat' => 16.0675, 'lng' => 108.2450],
            ['dia_chi' => 'Đường Điện Biên Phủ, Thanh Khê', 'lat' => 16.0750, 'lng' => 108.2000],
            ['dia_chi' => 'Chợ Hàn, Hải Châu', 'lat' => 16.0700, 'lng' => 108.2240],
            ['dia_chi' => 'Công viên 29/3, Thanh Khê', 'lat' => 16.0725, 'lng' => 108.2100],
            ['dia_chi' => 'Bán đảo Sơn Trà', 'lat' => 16.1200, 'lng' => 108.3000],
            ['dia_chi' => 'Ngũ Hành Sơn', 'lat' => 16.0037, 'lng' => 108.2630],
            ['dia_chi' => 'Đường Hoàng Sa, Sơn Trà', 'lat' => 16.1000, 'lng' => 108.2700],
        ];

        $statuses = ['CHO_XU_LY', 'DANG_XU_LY', 'HOAN_THANH', 'HUY_BO'];
        $urgencies = ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'];

        // Create 15 help requests
        for ($i = 0; $i < 15; $i++) {
            $location = $locations[$i % count($locations)];
            $status = $statuses[$i % count($statuses)];
            $urgency = $urgencies[rand(0, 3)];

            YeuCauCuuHo::create([
                'id_nguoi_dung' => $users[array_rand($users)],
                'id_loai_su_co' => $types[array_rand($types)],
                'vi_tri_lat' => $location['lat'],
                'vi_tri_lng' => $location['lng'],
                'vi_tri_dia_chi' => $location['dia_chi'],
                'chi_tiet' => "Chi tiết sự cố #{$i}",
                'mo_ta' => "Mô tả sự cố #{$i}: Cần cứu hộ ngay",
                'hinh_anh' => "image_{$i}.jpg",
                'so_nguoi_bi_anh_huong' => rand(1, 10),
                'muc_do_khan_cap' => $urgency,
                'diem_uu_tien' => rand(1, 10),
                'trang_thai' => $status
            ]);
        }

        echo "✅ Yêu Cầu Cứu Hộ Seeding: 15 yêu cầu\n";
    }
}
