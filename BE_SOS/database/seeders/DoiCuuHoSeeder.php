<?php

namespace Database\Seeders;

use App\Models\DoiCuuHo;
use App\Models\LoaiSuCo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DoiCuuHoSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DoiCuuHo::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $districts = [
            'Hải Châu' => [16.0600, 108.2200],
            'Thanh Khê' => [16.0700, 108.2000],
            'Liên Chiểu' => [16.0900, 108.1500],
            'Sơn Trà' => [16.1000, 108.2500],
            'Ngũ Hành Sơn' => [16.0000, 108.2600],
            'Hòa Vang' => [15.9800, 108.1200],
        ];

        $types = LoaiSuCo::all();

        $count = 0;

        foreach ($districts as $quan => $baseLocation) {

            foreach ($types as $type) {

                // random vị trí quanh trung tâm quận
                $lat = $baseLocation[0] + rand(-10, 10) / 1000;
                $lng = $baseLocation[1] + rand(-10, 10) / 1000;

                // 👉 tạo team
                $team = DoiCuuHo::create([
                    'ten_co' => "Đội {$type->ten} {$quan}",
                    'khu_vuc_quan_ly' => $quan,
                    'so_dien_thoai_hotline' => '09' . rand(100000000, 999999999),
                    'vi_tri_lat' => $lat,
                    'vi_tri_lng' => $lng,
                    'trang_thai' => 'SAN_SANG',
                    'mo_ta' => "Đội cứu hộ {$type->ten} tại {$quan}"
                ]);

                // 👉 gán đúng 1 loại sự cố
                $team->loaiSuCos()->syncWithoutDetaching([$type->id_loai_su_co]);

                $count++;
            }
        }

        echo "✅ Seeding {$count} đội cứu hộ\n";
    }
}
