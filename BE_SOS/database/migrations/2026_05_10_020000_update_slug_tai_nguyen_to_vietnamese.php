<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Map English slugs to Vietnamese slugs
        $slugMap = [
            'Vehicle'   => 'xe_cuu_ho',
            'Supply'    => 'nhu_yeu_pham',
            'Medical'   => 'vat_tu_y_te',
            'Equipment' => 'dung_cu_thiet_bi',
        ];

        foreach ($slugMap as $oldSlug => $newSlug) {
            DB::table('tai_nguyen_cuu_ho')
                ->where('slug_tai_nguyen', $oldSlug)
                ->update([
                    'slug_tai_nguyen' => $newSlug,
                    'ten_tai_nguyen' => DB::raw(
                        "CASE
                            WHEN slug_tai_nguyen = 'Vehicle' THEN 'Xe cứu hộ'
                            WHEN slug_tai_nguyen = 'Supply' THEN 'Nhu yếu phẩm'
                            WHEN slug_tai_nguyen = 'Medical' THEN 'Vật tư y tế'
                            WHEN slug_tai_nguyen = 'Equipment' THEN 'Dụng cụ thi công'
                            ELSE ten_tai_nguyen
                        END"
                    ),
                ]);
        }
    }

    public function down(): void
    {
        $reverseMap = [
            'xe_cuu_ho'     => 'Vehicle',
            'nhu_yeu_pham'  => 'Supply',
            'vat_tu_y_te'   => 'Medical',
            'dung_cu_thiet_bi' => 'Equipment',
        ];

        $tenMap = [
            'xe_cuu_ho'     => 'Xe cứu hộ',
            'nhu_yeu_pham'  => 'Nhu yếu phẩm',
            'vat_tu_y_te'   => 'Vật tư y tế',
            'dung_cu_thiet_bi' => 'Dụng cụ thi công',
        ];

        foreach ($reverseMap as $newSlug => $oldSlug) {
            DB::table('tai_nguyen_cuu_ho')
                ->where('slug_tai_nguyen', $newSlug)
                ->update([
                    'slug_tai_nguyen' => $oldSlug,
                    'ten_tai_nguyen' => $tenMap[$newSlug],
                ]);
        }
    }
};
