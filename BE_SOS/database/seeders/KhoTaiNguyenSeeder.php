<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KhoTaiNguyen;

class KhoTaiNguyenSeeder extends Seeder
{
    public function run(): void
    {
        $loaiTaiNguyen = [
            ['slug' => 'xe_cuu_ho', 'ten' => 'Xe cứu hộ'],
            ['slug' => 'nhu_yeu_pham', 'ten' => 'Nhu yếu phẩm'],
            ['slug' => 'vat_tu_y_te', 'ten' => 'Vật tư y tế'],
            ['slug' => 'dung_cu_thi_cong', 'ten' => 'Dụng cụ thi công'],
        ];

        foreach ($loaiTaiNguyen as $loai) {
            KhoTaiNguyen::updateOrCreate(
                ['slug_tai_nguyen' => $loai['slug']],
                ['ten_tai_nguyen' => $loai['ten'], 'so_luong' => 0]
            );
        }

        echo "✅ Kho Tai Nguyen seeded.\n";
    }
}
