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

        // Khu vực Đà Nẵng (trung tâm ~ 16.0544, 108.2022)
        $locations = [
            ['dia_chi' => 'Cầu Rồng, Hải Châu', 'lat' => 16.0610, 'lng' => 108.2275],
            ['dia_chi' => 'Chợ Hàn, Hải Châu', 'lat' => 16.0675, 'lng' => 108.2220],
            ['dia_chi' => 'Bãi biển Mỹ Khê, Sơn Trà', 'lat' => 16.0900, 'lng' => 108.2460],
            ['dia_chi' => 'Cầu Thuận Phước, Hải Châu', 'lat' => 16.0950, 'lng' => 108.2100],
            ['dia_chi' => 'Công viên Biển Đông, Sơn Trà', 'lat' => 16.1180, 'lng' => 108.2520],
            ['dia_chi' => 'Ga Đà Nẵng, Thanh Khê', 'lat' => 16.0560, 'lng' => 108.1720],
            ['dia_chi' => 'Cầu Sông Hàn, Hải Châu', 'lat' => 16.0680, 'lng' => 108.2250],
            ['dia_chi' => 'Làng Vân, Ngũ Hành Sơn', 'lat' => 15.9950, 'lng' => 108.2650],
            ['dia_chi' => 'Cầu Nguyễn Văn Trỗi, Hải Châu', 'lat' => 16.0480, 'lng' => 108.2180],
            ['dia_chi' => 'ĐH Đà Nẵng, Liên Chiểu', 'lat' => 16.0475, 'lng' => 108.1580],
            ['dia_chi' => 'Helio Center, Hải Châu', 'lat' => 16.0400, 'lng' => 108.2120],
            ['dia_chi' => 'Big C Đà Nẵng, Cẩm Lệ', 'lat' => 16.0150, 'lng' => 108.2050],
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
