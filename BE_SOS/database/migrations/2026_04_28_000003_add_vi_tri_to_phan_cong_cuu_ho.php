<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phan_cong_cuu_ho', function (Blueprint $table) {
            $table->double('vi_tri_lat')->nullable()->after('id_thanh_vien_tiep_nhan');
            $table->double('vi_tri_lng')->nullable()->after('vi_tri_lat');
        });
    }

    public function down(): void
    {
        Schema::table('phan_cong_cuu_ho', function (Blueprint $table) {
            $table->dropColumn(['vi_tri_lat', 'vi_tri_lng']);
        });
    }
};
