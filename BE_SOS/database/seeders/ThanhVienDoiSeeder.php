<?php

namespace Database\Seeders;

use App\Models\ThanhVienDoi;
use App\Models\DoiCuuHo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ThanhVienDoiSeeder extends Seeder
{
    use WithoutModelEvents;

    // Define role cho dễ dùng
    const ROLE_MANAGER = 0;
    const ROLE_LEADER = 1;
    const ROLE_MEMBER = 2;

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        ThanhVienDoi::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $dois = DoiCuuHo::all();
        $count = 0;
        $index = 1;

        $names = [
            'Nguyễn Văn An',
            'Trần Văn Bình',
            'Lê Minh Cường',
            'Phạm Quốc Dũng',
            'Hoàng Văn Em',
            'Nguyễn Thành Long',
            'Đỗ Minh Tuấn',
            'Phan Văn Hòa',
            'Võ Quốc Bảo',
            'Huỳnh Thanh Sơn',
        ];

        // 👉 Tạo 1 Manager tổng
        ThanhVienDoi::create([
            'id_doi_cuu_ho' => null,
            'ho_ten' => 'Manager',
            'so_dien_thoai' => '0900000000',
            'email' => 'manager@gmail.com',
            'mat_khau' => Hash::make('111111'),
            'vai_tro_trong_doi' => self::ROLE_MANAGER,
            'trang_thai' => 1
        ]);

        $count++;

        foreach ($dois as $doi) {
            for ($i = 1; $i <= rand(2, 3); $i++) {

                $name = $names[array_rand($names)];
                $emailName = Str::slug($name, '');

                if ($i == 1) {
                    $vaiTro = self::ROLE_LEADER;
                } else {
                    $vaiTro = self::ROLE_MEMBER;
                }

                ThanhVienDoi::create([
                    'id_doi_cuu_ho' => $doi->id_doi_cuu_ho,
                    'ho_ten' => $name,
                    'so_dien_thoai' => sprintf('091%07d', rand(100000, 9999999)),
                    'email' => $emailName . $index . '@gmail.com',
                    'mat_khau' => Hash::make('member123'),
                    'vai_tro_trong_doi' => $vaiTro,
                    'trang_thai' => 1
                ]);

                $count++;
                $index++;
            }
        }

        echo "✅ Thành Viên Đội Seeding: {$count} thành viên đội\n";
    }
}
