<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tai_nguyen_cuu_ho')
            ->where('slug_tai_nguyen', 'dung_cu_thiet_bi')
            ->update([
                'slug_tai_nguyen' => 'dung_cu_thi_cong',
            ]);
    }

    public function down(): void
    {
        DB::table('tai_nguyen_cuu_ho')
            ->where('slug_tai_nguyen', 'dung_cu_thi_cong')
            ->update([
                'slug_tai_nguyen' => 'dung_cu_thiet_bi',
            ]);
    }
};
