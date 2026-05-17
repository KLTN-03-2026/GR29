<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Cho phép so_nguoi_bi_anh_huong NULL: khi yêu cầu có ảnh, controller để NULL
     * và DetectVictimsJob (YOLO) sẽ ghi số người thực tế sau khi detect xong.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `yeu_cau_cuu_ho` MODIFY `so_nguoi_bi_anh_huong` INT NULL DEFAULT NULL');
    }

    public function down(): void
    {
        DB::statement('UPDATE `yeu_cau_cuu_ho` SET `so_nguoi_bi_anh_huong` = 1 WHERE `so_nguoi_bi_anh_huong` IS NULL');
        DB::statement('ALTER TABLE `yeu_cau_cuu_ho` MODIFY `so_nguoi_bi_anh_huong` INT NOT NULL DEFAULT 1');
    }
};
