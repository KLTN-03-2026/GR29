<?php

namespace Database\Seeders;

use App\Models\TaiNguyenCuuHo;
use App\Models\DoiCuuHo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaiNguyenCuuHoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $dois = DoiCuuHo::all();
        $resources = [
            'xe_cuu_ho' => 'Xe cứu hộ',
            'nhu_yeu_pham' => 'Nhu yếu phẩm',
            'vat_tu_y_te' => 'Vật tư y tế',
            'dung_cu_thi_cong' => 'Dụng cụ thi công',
        ];
        $count = 0;

        foreach ($dois as $doi) {
            $i = 0;
            foreach ($resources as $slug => $ten) {
                TaiNguyenCuuHo::create([
                    'id_doi_cuu_ho' => $doi->id_doi_cuu_ho,
                    'ten_tai_nguyen' => $ten,
                    'slug_tai_nguyen' => $slug,
                    'so_luong' => rand(1, 5),
                    'trang_thai' => 1
                ]);
                $count++;
                $i++;
            }
        }

        echo "✅ Tài Nguyên Cứu Hộ Seeding: {$count} tài nguyên\n";
    }
}
