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
            ['dia_chi' => '123 Đường Lê Lợi, Hải Châu, Đà Nẵng', 'lat' => 16.0600, 'lng' => 108.2200],
            ['dia_chi' => '456 Đường Nguyễn Văn Linh, Hải Châu, Đà Nẵng', 'lat' => 16.0620, 'lng' => 108.2250],
            ['dia_chi' => '78 Đường Trần Phú, Hải Châu, Đà Nẵng', 'lat' => 16.0640, 'lng' => 108.2180],
            ['dia_chi' => '200 Đường Ông Ích Khiêm, Thanh Khê, Đà Nẵng', 'lat' => 16.0700, 'lng' => 108.2000],
            ['dia_chi' => '300 Đường Lê Đình Lĩnh, Thanh Khê, Đà Nẵng', 'lat' => 16.0680, 'lng' => 108.2050],
            ['dia_chi' => 'Đường Tôn Đức Thắng, Liên Chiểu, Đà Nẵng', 'lat' => 16.0900, 'lng' => 108.1500],
            ['dia_chi' => 'Khu Công Nghiệp Hòa Khánh, Liên Chiểu, Đà Nẵng', 'lat' => 16.0880, 'lng' => 108.1450],
            ['dia_chi' => '99 Đường Nguyễn Tất Thành, Sơn Trà, Đà Nẵng', 'lat' => 16.1000, 'lng' => 108.2500],
            ['dia_chi' => 'Bãi Biển Mỹ An, Ngũ Hành Sơn, Đà Nẵng', 'lat' => 16.0000, 'lng' => 108.2600],
            ['dia_chi' => 'Phố Cổ Hội An, Hội An, Quảng Nam', 'lat' => 15.8800, 'lng' => 108.3350],
            ['dia_chi' => 'Bến xe Trung tâm, Hải Châu, Đà Nẵng', 'lat' => 16.0590, 'lng' => 108.2210],
            ['dia_chi' => 'Sân bay Đà Nẵng, Hải Châu, Đà Nẵng', 'lat' => 16.0550, 'lng' => 108.1990],
            ['dia_chi' => 'Cầu Rồng, Sơn Trà, Đà Nẵng', 'lat' => 16.0920, 'lng' => 108.2350],
            ['dia_chi' => 'Bán đảo Sơn Trà, Sơn Trà, Đà Nẵng', 'lat' => 16.0950, 'lng' => 108.2600],
            ['dia_chi' => 'Hòa Liên, Hòa Vang, Đà Nẵng', 'lat' => 15.9800, 'lng' => 108.1200],
        ];

        $statuses = ['CHO_XU_LY', 'DANG_XU_LY', 'HOAN_THANH', 'HUY_BO'];
        $urgencies = ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'];

        for ($i = 0; $i < 15; $i++) {
            $location = $locations[$i % count($locations)];
            $status = $statuses[$i % count($statuses)];
            $urgency = $urgencies[rand(0, 3)];

            YeuCauCuuHo::create([
                'id_nguoi_dung' => $users[array_rand($users)],
                'id_loai_su_co' => $types[array_rand($types)],
                'vi_tri_lat' => $location['lat'] + (rand(-5, 5) / 1000),
                'vi_tri_lng' => $location['lng'] + (rand(-5, 5) / 1000),
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
