<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DoiCuuHoLoaiSuCoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('doi_cuu_ho_loai_su_co')->delete();

        DB::table('doi_cuu_ho_loai_su_co')->insert([

            // ================= HÒA VANG =================
            ['id_doi_cuu_ho' => 1, 'id_loai_su_co' => 1], // Lũ lụt
            ['id_doi_cuu_ho' => 1, 'id_loai_su_co' => 2], // Bão
            ['id_doi_cuu_ho' => 1, 'id_loai_su_co' => 3], // Sạt lở đất
            ['id_doi_cuu_ho' => 1, 'id_loai_su_co' => 6], // Hạn hán

            // ================= HẢI CHÂU =================
            ['id_doi_cuu_ho' => 2, 'id_loai_su_co' => 1], // Lũ lụt
            ['id_doi_cuu_ho' => 2, 'id_loai_su_co' => 2], // Bão
            ['id_doi_cuu_ho' => 2, 'id_loai_su_co' => 6], // Hạn hán

            // ================= THANH KHÊ =================
            ['id_doi_cuu_ho' => 3, 'id_loai_su_co' => 1], // Lũ lụt
            ['id_doi_cuu_ho' => 3, 'id_loai_su_co' => 2], // Bão

            // ================= LIÊN CHIỂU =================
            ['id_doi_cuu_ho' => 4, 'id_loai_su_co' => 1], // Lũ lụt
            ['id_doi_cuu_ho' => 4, 'id_loai_su_co' => 3], // Sạt lở đất

            // ================= SƠN TRÀ =================
            ['id_doi_cuu_ho' => 5, 'id_loai_su_co' => 1], // Lũ lụt
            ['id_doi_cuu_ho' => 5, 'id_loai_su_co' => 2], // Bão
            ['id_doi_cuu_ho' => 5, 'id_loai_su_co' => 5], // Sóng thần

            // ================= NGŨ HÀNH SƠN =================
            ['id_doi_cuu_ho' => 6, 'id_loai_su_co' => 1], // Lũ lụt
            ['id_doi_cuu_ho' => 6, 'id_loai_su_co' => 5], // Sóng thần
        ]);

        echo "✅ Seed bảng doi_cuu_ho_loai_su_co OK\n";
    }
}
